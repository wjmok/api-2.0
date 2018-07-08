<?php
/**
 * Created by 董博明
 * Author: 董博明
 * Date: 2018/1/5
 * Time: 9:50
 */

namespace app\api\controller\v1;


use app\api\model\Image as ImageModel;
use app\api\model\ThirdApp as ThirdModel;
use app\api\service\Image as ImageService;
use app\lib\exception\ImageException;
use app\lib\exception\AdminException;
use app\lib\exception\ThirdappException;
use app\lib\exception\TokenException;
use app\api\controller\BaseController;

use think\Request as Request; 
use think\Controller;

// 七牛云SDK
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Cache;
class Image extends BaseController
{   

    /**
     * 删除图片
     * @return [type] [description]
     */
    public function deletepic()
    {

    	$data=Request::instance()->param();
        $ImageService = new ImageService();
        //验证信息并返回商户信息
        $custominfo = $ImageService->initinfo($data);
        switch ($custominfo['status']) {
            case 1:
                $custominfo = $custominfo['info'];
                break;
            case 2:
                throw new AdminException([
                    'msg' => '账号没有操作权限',
                    'solelyCode' => 202015
                ]);
                break;
            case 3:
                throw new AdminException([
                    'msg' => '该用户已被禁用',
                    'solelyCode' => 202008
                ]);
                break;
            case 4:
                throw new AdminException();
                break;
            case 5:
                throw new ThirdappException();
                break;
            default:
                # code...
                break;
        }
        $checkdelItem = $ImageService->checkdelItem($data);
        if ($checkdelItem) {
            throw new ImageException([
                'msg' => '没有删除条件',
                'solelyCode' => 204006
            ]);
        }
        $thirdapp_id = $custominfo['id'];
        
    	require_once APP_PATH . '/../vendor/qiniu/autoload.php';
    	// 需要填写你的 Access Key 和 Secret Key
        $accessKey = config('qiniu.ACCESSKEY');
       	$secretKey = config('qiniu.SECRETKEY'); 
		$bucket = config('qiniu.BUCKET');
		$auth = new Auth($accessKey, $secretKey);
		$config = new \Qiniu\Config();
		$bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

		$keys = $ImageService->initkey($data);
        if (!is_array($keys)) {
            switch ($keys) {
                case 1:
                    throw new ImageException([
                      'msg' => '选择的图片不存在',
                      'solelyCode' => 204003
                    ]);
                    break;
                case 2:
                    throw new ImageException([
                      'msg' => '该客户没有上传图片',
                      'solelyCode' => 204004
                    ]);
                    break;
                default:
                    # code...
                    break;
            }
        }
        if (isset($data['delItem']['thirdapp_id'])) {
            foreach ($keys['imgname'] as $k => $v) {
                $err = $bucketManager->delete($bucket, $v);
                if ($err) {
                    throw new ImageException([
                        'msg' => '图片服务器错误：'.$err,
                        'solelyCode' => 204002
                    ]);
                }
            }            
        }else{
            //批量删除每次最多不能超过1000个
            $count = count($keys['keys']);
            if ($count>1000) {
                throw new ImageException([
                    'msg' => '一次删除的图片不得多于1000张',
                    'solelyCode' => 204001
                ]);
            }
            $ops = $bucketManager->buildBatchDelete($bucket, $keys);
            list($ret, $err) = $bucketManager->batch($ops);
            if ($err) {
                throw new ImageException([
                    'msg' => '图片服务器错误：'.$err,
                    'solelyCode' => 204002
                ]);
            }
        }
        // 删除数据库文件
        $imagemodel = new ImageModel;
        $map['id'] = array('in',$keys['ids']);
        $del = $imagemodel->deletepic($map);
        $upinfo['id'] = $thirdapp_id;
        $upinfo['picstorage'] = $custominfo['picstorage'] - $keys['size'];
        $update = ThirdModel::upTuser($upinfo);
        throw new SuccessMessage([
            'msg'=>'删除成功'
        ]);
    }


     public function thdeletepic($data)
    {

        $ImageService = new ImageService();
        //验证信息并返回商户信息
        $custominfo = $ImageService->initinfo($data);
        $checkdelItem = $ImageService->checkdelItem($data);
        $thirdapp_id = $custominfo['id'];
        
        require_once APP_PATH . '/../vendor/qiniu/autoload.php';
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = config('qiniu.ACCESSKEY');
        $secretKey = config('qiniu.SECRETKEY'); 
        $bucket = config('qiniu.BUCKET');
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        
        $keys = $ImageService->initkey($data);
        if (isset($data['delItem']['thirdapp_id'])) {
            foreach ($keys['imgname'] as $k => $v) {
                $err = $bucketManager->delete($bucket, $v);
                if ($err) {
                    throw new ImageException([
                        'msg' => '图片服务器错误：'.$err,
                        'solelyCode' => 204002
                    ]);
                }
            }            
        }else{
            //批量删除每次最多不能超过1000个
            $count = count($keys['keys']);
            if ($count>1000) {
                throw new ImageException([
                    'msg' => '一次删除的图片不得多于1000张',
                    'solelyCode' => 204001
                ]);
            }
            $ops = $bucketManager->buildBatchDelete($bucket, $keys);
            list($ret, $err) = $bucketManager->batch($ops);
            if ($err) {
                throw new ImageException([
                    'msg' => '图片服务器错误：'.$err,
                    'solelyCode' => 204002
                ]);
            }
        }
        // 删除数据库文件
        $imagemodel = new ImageModel;
        $map['id'] = array('in',$keys['ids']);
        $del = $imagemodel->deletepic($map);
        $upinfo['id'] = $thirdapp_id;
        $upinfo['picstorage'] = $custominfo['picstorage'] - $keys['size'];
        $update = ThirdModel::upTuser($upinfo);
        throw new SuccessMessage([
            'msg'=>'删除成功'
        ]);
    }


    /**
     * 复制图片
     * @return [type] [description]
     */
    public function copypic($url,$thirdapp_id,$token)
    {   
        $urlinfo = pathinfo($url);
        $key = $urlinfo['basename'];
        $ext = $urlinfo['extension'];
        require_once APP_PATH . '/../vendor/qiniu/autoload.php';
        // 需要填写你的 Access Key 和 Secret Key
        $accessKey = config('qiniu.ACCESSKEY');
        $secretKey = config('qiniu.SECRETKEY'); 
        $bucket = config('qiniu.BUCKET');
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        $srcBucket = $bucket;
        $destBucket = $bucket;
        $srcKey = $key;
        $destKey = basename($key,".".$ext)."_copy".date('YmdHis').rand(0, 9999).".".$ext;
        $err = $bucketManager->copy($srcBucket, $srcKey, $destBucket, $destKey, true);
        if ($err) {
            throw new ImageException([
                'msg' => '复制图片错误：'.$err,
                'solelyCode' => 204007
            ]);
        }
        //记录img表
        $imagemodel = new ImageModel;
        $search['path'] = $url;
        $preimginfo = $imagemodel->getSingleImg($search);
        $data = array(
            "thirdapp_id" => $thirdapp_id,
            "name"        => $destKey,
            "path"        => $urlinfo['dirname'].'/'.$destKey,
            "ownership"   => 1,
            "size"        => $preimginfo['size'],
            "create_time" => time(),
        );
        $res = ImageModel::addImg($data);
        if ($res == 1) {
            $admininfo = ThirdModel::getThirdUserInfo($thirdapp_id);
            $upinfo['id'] = $thirdapp_id;
            $upinfo['picstorage'] = $admininfo['picstorage'] + $preimginfo['size'];
            $update = ThirdModel::upTuser($upinfo);
            // 返回图片的完整URL
            return $urlinfo['dirname'].'/'.$destKey;
        }else{
            throw new ImageException([
                'msg' => '图片信息写入数据库失败',
                'solelyCode' => 204005
            ]);
        }       
    }
}