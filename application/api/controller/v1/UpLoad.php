<?php
/**
 * Created by 董博明
 * Author: 董博明
 * Date: 2018/1/2
 * Time: 12:23
 */

namespace app\api\controller\v1;

use think\Cache;
use app\api\model\Image as ImageModel;
use app\api\model\ThirdApp as ThirdModel;
use app\lib\exception\ImageException;
use app\api\model\User as UserModel;
use app\api\service\base\Token as TokenService;
use app\lib\exception\TokenException;
use app\lib\exception\UserException;
use app\api\controller\CommonController;

use think\Request as Request; 
use think\Controller;

// 七牛云SDK
use Qiniu\Auth as Auth;
use Qiniu\Config as Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

// SAE
use think\sae\Storage as SAEStorage;

class UpLoad extends CommonController
{
	 /**
     * 单张图片上传
     * @return String 图片的完整URL
     */
    public function upload()
    {		
    	if(request()->isPost()){

    		$data=Request::instance()->param();
    		if (!isset($data['token'])) {
    			throw new TokenException();
    		}
            //根据token机制判定来源
            
            if (Cache::get('info'.$data['token'])) {
                $userinfo = Cache::get('info'.$data['token']);
                $ownership = 1;
            }else {
                $tokenservice = new TokenService();
                $userinfo = TokenService::getUserinfo($data['token']);
                if (!$userinfo||$userinfo['status']==-1) {
                    throw new UserException([
                        'msg' => '用户不存在或被禁用',
                        'solelyCode' => 205000
                    ]);
                }
                $ownership = 2;
            }
	    	
    		// $_FILE接受单图数据
            $filePath = $_FILES['file']['tmp_name'];
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);  //后缀
            // 上传到七牛后保存的文件名
            $key =substr(md5($filePath) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
            $key = 'api/ThirdApp/'.$userinfo['id'].'/'.$key;
            
            
            
            
            
            require_once APP_PATH . '/../vendor/qiniu/autoload.php';
            // 需要填写你的 Access Key 和 Secret Key
            /*$accessKey = config('qiniu.ACCESSKEY');
            $secretKey = config('qiniu.SECRETKEY');*/
            // 构建鉴权对象
            $auth = new Auth();
            // 要上传的空间
            $bucket = config('qiniu.BUCKET');
            $domain = config('qiniu.DOMAIN');


            /*$bucketManager = new BucketManager($auth);
            $prefix = "ThirdApp/6/";
            // 上次列举返回的位置标记，作为本次列举的起点信息。
            $marker = '';
            // 本次列举的条目数
            $limit = 100;
            $delimiter = '/';
            
            
            list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
            if ($err !== null) {
                echo "\n====> list file err: \n";
                var_dump($err);
            } else {
                if (array_key_exists('marker', $ret)) {
                    echo "Marker:" . $ret["marker"] . "\n";
                }
                echo "\nList Iterms====>\n";
                var_dump($ret['items']);
            };
            return 'finsh';*/








            //自定义返回值
			$returnBody = '{"key":"$(key)","hash":"$(etag)","fsize":$(fsize),"name":"$(x:name)"}';
			$policy = array(
			    'returnBody' => $returnBody
			);
            $token = $auth->uploadToken($bucket,null,3600,$policy,true);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                throw new ImageException([
                    'msg' => '图片服务器错误：'.$err,
                    'solelyCode' => 204002
                ]);
            } else {
            	//记录img表
            	$data = array(
            		"thirdapp_id" => $userinfo['thirdapp_id'],
            		"name"        => $ret['key'],
            		"path"        => $domain.$ret['key'],
            		"ownership"   => $ownership,
            		"size"        => $ret['fsize'],
            		"create_time" => time(),
            	);
            	$res = ImageModel::addImg($data);
            	if ($res == 1) {
            		$admininfo = ThirdModel::getThirdUserInfo($userinfo['thirdapp_id']);
            		$upinfo['picstorage'] = $admininfo['picstorage'] + $ret['fsize'];
            		$update = ThirdModel::upTuser($userinfo['thirdapp_id'],$upinfo);
            		// 返回图片的完整URL
                	echo $domain.$ret['key'];
            	}else{
            		throw new ImageException([
                    	'msg' => '图片信息写入数据库失败',
                    	'solelyCode' => 204005
                	]);
            	}
            }
        }
    }

    /**
     * FTP本地上传
     */
    public function FTPupload(){
        //获取表单上传文件
        $request=Request::instance();
        $files=request()->file();
        foreach($files as $file){
            $info = $file->move(ROOT_PATH.'public'.DS.'uploads');
            if($info){
                $url = $request->domain().'/public/uploads/'.$info->getSavename();
                $data = array(
                    "thirdapp_id" => 1,
                    "name"        => $info->getSavename(),
                    "path"        => $url,
                    "ownership"   => 0,
                    "size"        => $info->getSize(),
                    "create_time" => time(),
                );
                $res = ImageModel::addImg($data);
                echo $url;
            }else{
                throw new ImageException([
                    'msg' => '图片上传失败',
                    'solelyCode' => 204000
                ]);
            }    
        }
    }

    /**
     * SAE storage图片上传
     */
    public function SAEupload()
    {       
        $data = Request::instance()->param();
        // $_FILE接受单图数据
        $filePath = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);  //后缀
        $key =substr(md5($filePath) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;
        //实例化SAE类
        $s = new SAEStorage();
        $res = $s->putObjectFile($filePath, "public", $key);//视实际项目修改storage的名字，例子中的名字是"public"
        if ($res) {
            //记录img表
            $data = array(
                "thirdapp_id" => 1,
                "name"        => $key,
                "path"        => 'http://yisen-public.stor.sinaapp.com/'.$key,//视实际项目修改项目的名字，例子中的名字"yisen"
                "ownership"   => 0,
                "size"        => $_FILES['file']['size'],
                "create_time" => time(),
            );
            $res = ImageModel::addImg($data);
            if ($res==1) {
                echo $data['path'];
            }else{
                throw new ImageException([
                    'msg' => '图片信息写入数据库失败',
                    'solelyCode' => 204005
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '图片上传失败',
                'solelyCode' => 204009
            ]);
        }
    }
    
}