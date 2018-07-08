<?php 
/**
 * Created by wjm.
 * User: wjm
 * Date: 2018-05-02
 * Time: 10:36
 */

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
use think\Request as Request;
use think\Loader;
use app\api\service\Token as TokenService;


class WxAuth 
{

	public $stime = 1233;

    public function index($time="$this->")
    {
    	
    	$param = Request::instance()->param();
    	$this->thirdapp_id = $param['thirdapp_id'];
    	if($param['code']){
    		$this->code = $param['code'];
	    	$this->config = $this->getThirdConfig();
	    	// $this->config['appid'] = 'wx8852e44a45e13ce3';
	    	// $this->config['appsecret'] = '775e33235e5a8953f56f1cdd0bd6d855';
	    	if($this->config){
	    		$this->access_token = $this->getOpenId();
	    		$userRes = Db::query('select * from user where wx_openid=?',[$this->access_token['openid']]);
	    		if($userRes){
	    			$id = $userRes[0]['id'];
	    			$token = $this->getToken($id);
	    			$info['token'] = $token;
        			$info['openid'] = $this->access_token['openid'];
	    			return $info;
	    		}else{
	    			$userInfo = $this->getUserInfo();
			    	if($userInfo){
			    		$res = $this->saveUser($userInfo);
			    		if($res){
			    			$userRes = Db::query('select * from user where wx_openid=?',[$this->access_token['openid']]);
			    			$id = $userRes[0]['id'];
			    			$token = $this->getToken($id);
			    			$info['token'] = $token;
		        			$info['openid'] = $this->access_token['openid'];
			    			return $info;
			    		}else{
			    			return 'no_res';
			    		}
			    	}else{
			    		return 'no_userInfo';
			    	}
	    		};
	    	}else{
	    		return 'no_config';
	    	}
    	}else{	
	    	return 'no_code';
    	}
    }


    public function getCode()  
    {  
        if (isset($_GET["code"])) {  
            return $_GET["code"];  
        } else {  
            $str = "location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->appid . "&redirect_uri=" . $this->index_url . "&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";  
            header($str);  
            exit;  
        }  
    }


    public function getOpenId()  
    {  
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->config['appid'] . "&secret=" . $this->config['appsecret'] . "&code=" . $this->code . "&grant_type=authorization_code";  
        $access_token_json = $this->https_request($access_token_url);  
        $access_token_array = json_decode($access_token_json, TRUE);  
        return $access_token_array;  
    }


    public function getUserInfo()  
    {   
        $userinfo_url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$this->access_token['access_token'] ."&openid=" . $this->access_token['openid']."&lang=zh_CN";  
        $userinfo_json = $this->https_request($userinfo_url);  
        $userinfo_array = json_decode($userinfo_json, TRUE);  
        return $userinfo_array;  
    }


    public function getThirdConfig()
    {
		
		$thirdRes = Db::query('select * from third_app where id=?',[$this->thirdapp_id]);
		if($thirdRes){
			$config['token'] = $thirdRes[0]['wx_token'];
			$config['appid'] = $thirdRes[0]['wx_appid'];
			$config['appsecret'] = $thirdRes[0]['wx_appsecret'];
			$config['encodingaeskey'] = $thirdRes[0]['encodingaeskey'];
			$config['access_token'] = $thirdRes[0]['access_token'];
			$config['access_token_expire'] = $thirdRes[0]['access_token_expire'];
			return $config;
		}else{
			return false;
		}
	}


	public function saveUser($userInfo)
	{
		//return $userInfo;
		$headimgurl['name'] = 'headimg';
		$headimgurl['url'] = $userInfo['headimgurl'];
		$headimgurl = json_encode($headimgurl);
		$adduserRes = Db::execute('insert into user (wx_openid,headimgurl,nickname ) values (?, ?, ?)',[$userInfo['openid'],$headimgurl,$userInfo['nickname']]);
		if($adduserRes){
			return $adduserRes;
		}else{
			return false;
		}
	}


	public function updateThirdApp($thirdInfo)
	{
		
		$sqlString = 'update third_app set ';
		foreach ($thirdInfo as $key => $value) {  
		    if($key!="id"){
		    	$sqlString .= $key." =' ".$value."',";
		    };
		};
		$sqlString = rtrim($sqlString, ',');
		$sqlString .= " where id =".$this->thirdapp_id;
		//return $sqlString;
		$updateThirdAppRes = Db::execute($sqlString);
		if($updateThirdAppRes){
			return $updateThirdAppRes;
		}else{
			return false;
		}
	}


	public function https_request($url, $data = null)  
    {
        $curl = curl_init();  
        curl_setopt($curl, CURLOPT_URL, $url);  
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);  
        if (!empty($data)) {  
            curl_setopt($curl, CURLOPT_POST, 1);  
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  
        }  
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
        $output = curl_exec($curl);  
        curl_close($curl);  
        return $output;  
    }

    public function getToken($id)
    {
    	$info['uid'] = $id;
    	$tokenservice = new TokenService();
    	$key = $tokenservice::generateToken();
        $value = json_encode($info);
        $expire_in = config('setting.token_expire_in');
        $result = cache($key, $value, $expire_in);
        if (!$result){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }
}
?>