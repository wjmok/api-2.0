<?php
namespace app\api\service\base;
use app\api\model\QiniuImage as QiniuImageModel;
use app\api\model\Common as CommonModel;
use think\Exception;
use think\Model;
use think\Cache;
use think\Request as Request; 

use app\api\model\Image as ImageModel;
use app\api\model\ThirdApp as ThirdModel;
use app\api\model\User as UserModel;



use app\api\service\AdminToken as AdminTokenService;
use app\api\service\base\Common as CommonService;
use app\api\validate\CommonValidate;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;


// 七牛云SDK
use Qiniu\Auth as Auth;
use Qiniu\Config as Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;


class QiniuImage{

    public static $bucket;
    public static $domain;

    function __construct(){
        self::$bucket = config('qiniu.BUCKET');
        self::$domain = config('qiniu.DOMAIN');
        $data = Request::instance()->param();
        $arr = ['upload'];
        if(in_array($data['serviceName'],$arr)){
            checkToken();
        };
        
    }
    
    public static function upload($data)
    {       
        require_once APP_PATH . '/../vendor/qiniu/autoload.php';

        (new CommonValidate())->goCheck('one',$data);
        checkTokenAndScope($data,20);
        
        $userinfo = Cache::get($data['token']);
        
        // $_FILE接受单图数据
        $filePath = $_FILES['file']['tmp_name'];
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);  //后缀
        // 上传到七牛后保存的文件名
        $key =substr(md5($filePath) , 0, 5). date('YmdHis') . rand(0, 100) . '.' . $ext;
        $key = 'api/ThirdApp/'.$userinfo['id'].'/'.$key;
        

        $auth = new Auth();    
        $returnBody = '{"key":"$(key)","hash":"$(etag)","fsize":$(fsize),"name":"$(x:name)"}';
        $policy = array(
            'returnBody' => $returnBody
        );
        $token = $auth->uploadToken(self::$bucket,null,3600,$policy,true);
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err !== null) {
            throw new ErrorMessage([
                'msg' => '图片服务器错误：'.$err,
            ]);
        } else {
            //记录img表
            $data = array(
                "thirdapp_id" => $userinfo['thirdapp_id'],
                "user_id" => $userinfo['id'],
                "name"        => $ret['key'],
                "path"        => self::$domain.$ret['key'],
                "prefix"   => 'api/ThirdApp/'.$userinfo['id'],
                "size"        => $ret['fsize'],
                "create_time" => time(),
                "modelName" => 'image',
            );


            $res = CommonService::add($data,true);
            
            if ($res>0) {
                /*$admininfo = ThirdModel::getThirdUserInfo($userinfo['thirdapp_id']);
                $upinfo['picstorage'] = $admininfo['picstorage'] + $ret['fsize'];
                $update = ThirdModel::upTuser($userinfo['thirdapp_id'],$upinfo);
                // 返回图片的完整URL
                echo self::$domain.$ret['key'];*/


                throw new SuccessMessage([
                    'msg'=>'图片上传成功',
                    'info'=>['url'=>self::$domain.$ret['key']]
                ]);

            }else{
                throw new ErrorMessage([
                    'msg' => '图片信息写入数据库失败',
                ]);
            }
        }
        
    }


    public static function get($data)
    {
        require_once APP_PATH . '/../vendor/qiniu/autoload.php';

        (new CommonValidate())->goCheck('one',$data);
        checkTokenAndScope($data,20);


        $auth = new Auth();
        $bucketManager = new BucketManager($auth);
        // 要列取文件的公共前缀
        $prefix = 'api/ThirdApp/'.$data['thirdapp_id'].'/';
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $marker = '';
        // 本次列举的条目数
        $limit = 1000;
        $delimiter = '/';
        // 列举文件
        list($ret, $err) = $bucketManager->listFiles(self::$bucket, $prefix, $marker, $limit, $delimiter);
        if ($err !== null) {
            echo "\n====> list file err: \n";
            var_dump($err);
        } else {
            if (array_key_exists('marker', $ret)) {
                echo "Marker:" . $ret["marker"] . "\n";
            }
            
            
            return $ret['items'];
        }

    }


    public static function delete($data)
    {
        require_once APP_PATH . '/../vendor/qiniu/autoload.php';

        (new CommonValidate())->goCheck('one',$data);
        checkTokenAndScope($data,20);


        
        
        $auth = new Auth();
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        //每次最多不能超过1000个
        
        $keys = $data['keys'];
        $ops = $bucketManager->buildBatchDelete(self::$bucket, $keys);
        list($ret, $err) = $bucketManager->batch($ops);
        if ($err) {
            
            return $err;

        } else {
            
            return $ret;
        }

    }

    public static function deleteAll($data)
    {
        require_once APP_PATH . '/../vendor/qiniu/autoload.php';

        (new CommonValidate())->goCheck('one',$data);
        checkTokenAndScope($data,20);


        
        $res = self::get($data);
        foreach ($res as $key => $value) {
            $res[$key] = $value['key'];
        };
        $data['keys'] = $res;
        return self::delete($data);
        
    }


    

    


    


    


}