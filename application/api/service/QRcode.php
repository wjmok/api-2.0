<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/5/9
 * Time: 18:28
 */

namespace app\api\service;

use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\Image as ImageModel;

// 七牛云SDK
use Qiniu\Auth as Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

/**
 * 后台获取场景二维码
 */
class QRcode{

    public function getMiniCode($thirdapp_id,$scene)
    {
        $thirdappinfo = ThirdAppModel::getThirdUserInfo($thirdapp_id);
        if (!isset($thirdappinfo['appid'])&&!isset($thirdappinfo['appsecret'])) {
            throw new TokenException([
                'msg' => '未设置关键参数appid/appsecret',
                'solelyCode' => 200012
            ]);
        }
        $tokenurl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$thirdappinfo['appid'].'&secret='.$thirdappinfo['appsecret'];
        $result = curl_get($tokenurl);
        if ($result) {
            $result = json_decode($result,true);
            $access_token = $result['access_token'];
            $codeurl = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token;
            $codeinfo = array(
                'scene'=>$scene,
                'path'=>'pages/index/index',
                'width'=>430,
                'auto_color'=>false,
                'line_color'=>array('r'=>'0','g'=>'0','b'=>'0'),
            );
            $getcode = curl_post($codeurl,$codeinfo);
            $codelen = strlen($getcode);
            //判断图片二进制流大小
            if ($codelen<4194304) {
                // 上传到七牛后保存的文件名
                $key = 'qrcode'.substr(md5($scene),0,5).date('YmdHis').rand(0, 9999).'.png';
                require_once APP_PATH . '/../vendor/qiniu/autoload.php';
                // 需要填写你的 Access Key 和 Secret Key
                $accessKey = config('qiniu.ACCESSKEY');
                $secretKey = config('qiniu.SECRETKEY');
                // 构建鉴权对象
                $auth = new Auth($accessKey, $secretKey);
                // 要上传的空间
                $bucket = config('qiniu.BUCKET');
                $domain = config('qiniu.DOMAIN');
                //自定义返回值
                $returnBody = '{"key":"$(key)","hash":"$(etag)","fsize":$(fsize),"name":"$(x:name)"}';
                $policy = array(
                    'returnBody' => $returnBody
                );
                $upToken = $auth->uploadToken($bucket,null,3600,$policy,true);//获取上传所需的token
                // 初始化 UploadManager 对象并进行文件的上传
                $uploadMgr = new UploadManager();
                // 调用 UploadManager 的 putFile 方法进行文件的上传
                list($ret, $err) = $uploadMgr->QRputFile($upToken, $key, $getcode);
                if ($err !== null) {
                    throw new TokenException([
                        'msg' => '图片服务器错误：'.$err,
                        'solelyCode' => 204002
                    ]);
                } else {
                    //记录img表
                    $data = array(
                        "thirdapp_id" => $thirdapp_id,
                        "name"        => $ret['key'],
                        "path"        => $domain.$ret['key'],
                        "ownership"   => 1,
                        "size"        => $ret['fsize'],
                        "create_time" => time(),
                    );
                    $res = ImageModel::addImg($data);
                    if ($res == 1) {
                        $upinfo['picstorage'] = $thirdappinfo['picstorage'] + $ret['fsize'];
                        $update = ThirdAppModel::upTuser($thirdapp_id,$upinfo);
                        return $domain.$ret['key'];
                    }else{
                        throw new TokenException([
                            'msg' => '图片信息写入数据库失败',
                            'solelyCode' => 204005
                        ]);
                    }
                }
            }else{
                throw new TokenException([
                    'msg' => '图片大小超过限制',
                    'solelyCode' => 204008
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '获取access_token失败',
                'solelyCode' => 200012
            ]);
        }
    }

    public function getWxCode($thirdapp_id,$scene)
    {
        $thirdappinfo = ThirdAppModel::getThirdUserInfo($thirdapp_id);
        if (!isset($thirdappinfo['wx_appid'])&&!isset($thirdappinfo['wx_appsecret'])) {
            throw new TokenException([
                'msg' => '未设置关键参数appid/appsecret',
                'solelyCode' => 200012
            ]);
        }
        if (isset($thirdappinfo['access_token_expire'])&&$thirdappinfo['access_token_expire']>time()) {
            $access_token = $thirdappinfo['access_token'];
        }else{
            $tokenurl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$thirdappinfo['wx_appid'].'&secret='.$thirdappinfo['wx_appsecret'];
            $result = curl_get($tokenurl);
            if ($result) {
                $result = json_decode($result,true);
                $access_token = $result['access_token'];
            }else{
                throw new TokenException([
                    'msg' => '获取access_token失败',
                    'solelyCode' => 200012
                ]);
            }
        }
        $codeurl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        $codeinfo = array(
            'action_name'=>'QR_LIMIT_SCENE',
            'action_info'=> array('scene' => array('scene_id' => $scene)),
        );
        $getcode = curl_post($codeurl,$codeinfo);
        if ($getcode) {
            $json = json_decode($getcode, true);
            $ticketurl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$json['ticket'];
            $imagecode = curl_get($ticketurl);
        }else{
            return false;
        }
        $codelen = strlen($imagecode);
        //判断图片二进制流大小
        if ($codelen<4194304) {
            // 上传到七牛后保存的文件名
            $key = 'qrcode'.substr(md5($scene),0,5).date('YmdHis').rand(0, 9999).'.png';
            require_once APP_PATH . '/../vendor/qiniu/autoload.php';
            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = config('qiniu.ACCESSKEY');
            $secretKey = config('qiniu.SECRETKEY');
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config('qiniu.BUCKET');
            $domain = config('qiniu.DOMAIN');
            //自定义返回值
            $returnBody = '{"key":"$(key)","hash":"$(etag)","fsize":$(fsize),"name":"$(x:name)"}';
            $policy = array(
                'returnBody' => $returnBody
            );
            $upToken = $auth->uploadToken($bucket,null,3600,$policy,true);//获取上传所需的token
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->QRputFile($upToken, $key, $imagecode);
            if ($err !== null) {
                throw new TokenException([
                    'msg' => '图片服务器错误：'.$err,
                    'solelyCode' => 204002
                ]);
            } else {
                //记录img表
                $data = array(
                    "thirdapp_id" => $thirdapp_id,
                    "name"        => $ret['key'],
                    "path"        => $domain.$ret['key'],
                    "ownership"   => 1,
                    "size"        => $ret['fsize'],
                    "create_time" => time(),
                );
                $res = ImageModel::addImg($data);
                if ($res == 1) {
                    $upinfo['picstorage'] = $thirdappinfo['picstorage'] + $ret['fsize'];
                    $update = ThirdAppModel::upTuser($thirdapp_id,$upinfo);
                    return $domain.$ret['key'];
                }else{
                    throw new TokenException([
                        'msg' => '图片信息写入数据库失败',
                        'solelyCode' => 204005
                    ]);
                }
            }
        }else{
            throw new TokenException([
                'msg' => '图片大小超过限制',
                'solelyCode' => 204008
            ]);
        }
    }
}