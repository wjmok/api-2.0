<?php

namespace app\api\service;

use app\api\model\User as UserModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\Common as CommonModel;
use app\api\service\DistributionMain as DistributionService;

use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use think\Model;






/**
 * 微信登录
 * 如果担心频繁被恶意调用，请限制ip
 * 以及访问频率
 */






class ProgrameToken {
    protected $code;
    protected $wxLoginUrl;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $thirdapp_id;
    protected $parent_no;
    public $nickname = '';
    public $headimgurl = '';

    function __construct($data){
        //获取app_id，app_secret
        $modelData = [];
        $modelData['map']['thirdapp_id'] = $data['thirdapp_id'];
        $ThirdAppInfo=CommonModel::get('thirdapp',$modelData);
        $this->code = $data['code'];
        $this->thirdapp_id = $data['thirdapp_id'];
        $this->parent_no = isset($data['openid'])?$data['openid']:'';
        $this->wxAppID = $ThirdAppInfo['appid'];
        $this->wxAppSecret = $ThirdAppInfo['appsecret'];
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);
        if(isset($data['nickname'])){
            $this->nickname = $data['nickname'];
        }
        if (isset($data['headimgurl'])){
            $this->headimgurl = $data['headimgurl'];
        };
    }

    /**
     * 登陆
     * 思路1：每次调用登录接口都去微信刷新一次session_key，生成新的Token，不删除久的Token
     * 思路2：检查Token有没有过期，没有过期则直接返回当前Token
     * 思路3：重新去微信刷新session_key并删除当前Token，返回新的Token
     */
    
    public function get()
    {
        $result = curl_get($this->wxLoginUrl);

        // 注意json_decode的第一个参数true
        // 这将使字符串被转化为数组而非对象

        $wxResult = json_decode($result, true);
        if (empty($wxResult)||array_key_exists('errcode', $wxResult)) {
            // 为什么以empty判断是否错误，这是根据微信返回
            // 规则摸索出来的
            // 这种情况通常是由于传入不合法的code
            // throw new Exception('获取session_key及openID时异常，微信内部错误');
            throw new ErrorMessage([
                'msg' => $wxResult['errmsg'],
                'errorCode' => $wxResult['errcode']
            ]);
            
        };
        return $this->grantToken($wxResult);
    }

    

        


    // 颁发令牌
    // 只要调用登陆就颁发新令牌
    // 但旧的令牌依然可以使用
    // 所以通常令牌的有效时间比较短
    // 目前微信的express_in时间是7200秒
    // 在不设置刷新令牌（refresh_token）的情况下
    // 只能延迟自有token的过期时间超过7200秒（目前还无法确定，在express_in时间到期后
    // 还能否进行微信支付
    // 没有刷新令牌会有一个问题，就是用户的操作有可能会被突然中断
    private function grantToken($wxResult){
        // 此处生成令牌使用的是TP5自带的令牌
        // 如果想要更加安全可以考虑自己生成更复杂的令牌
        // 比如使用JWT并加入盐，如果不加入盐有一定的几率伪造令牌
        //        $token = Request::instance()->token('token', 'md5');
        $openid = $wxResult['openid'];

        $modelData = [];
        $modelData['map']['openid'] = $data['openid'];
        $modelData['map']['status'] = 1;
        $user=CommonModel::CommonGet('user',$modelData);

        if(isset($wxResult['unionid'])){ 
            $modelData = [];
            $modelData['map']['unionid'] = $wxResult['unionid'];
            $unionUser=CommonModel::CommonGet('user',$modelData);
        };


        if(count($user)>0){
            $uid = $user[0]['id'];
            $modelData = [];
            $modelData['nickname'] = $this->nickname;
            $modelData['headimgurl'] = json_encode(["0"=>['name'=>'headimg','url'=>$this->headimgurl]]);
            $search = ['id'=>$checkunionid['id']];
            $uid = CommonModel::CommonSave('user',$modelData,$search);

        }else{

            $modelData = [];
            $modelData['nickname'] = $this->nickname;
            $modelData['headimgurl'] = json_encode(["0"=>['name'=>'headimg','url'=>$this->headimgurl]]);
            $modelData['openid'] = $openid;
            $modelData['type'] = 0;
            $modelData['thirdapp_id'] = $this->thirdapp_id;
            if(isset($wxResult['unionid'])){
                $modelData['unionid'] = $wxResult['unionid'];
            };
            if(isset($unionUser)&&count($unionUser)>0){
                $modelData['user_no'] = $unionUser[0]['user_no'];
                $modelData['parent_no'] = $unionUser[0]['parent_no'];
            }else{
                $modelData['user_no'] = makeUserNo();
                $modelData['parent_no'] = $this->parent_no;
            };

            $uid = CommonModel::CommonSave('user',$modelData);
            if($uid>0){
                
            }else{
                throw new ErrorMessage([
                    'msg'=>'新添加用户失败'
                ]);
            };

            
        };



        $modelData = [];
        $modelData['map']['id'] = $uid;
        $user=CommonModel::CommonGet('user',$modelData);
        $userNo=$user[0]['user_no'];

        $token = generateToken();
        $cacheResult = Cache::set($res,$user,3600);
        if (!$cacheResult){
            throw new ErrorMessage([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        };


        $modelData = [];
        $modelData['map']['user_no'] = $userNo;
        $userInfo=CommonModel::CommonGet('user_info',$modelData);


        throw new SuccessMessage([
            'msg'=>'登录成功',
            'token'=>$token,
            'info'=>$userInfo
        ]);



    };








}
