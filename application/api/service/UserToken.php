<?php

namespace app\api\service;

use app\api\model\User as UserModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\Member as MemberModel;
use app\api\service\DistributionMain as DistributionService;
use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use think\Model;

/**
 * 微信登录
 * 如果担心频繁被恶意调用，请限制ip
 * 以及访问频率
 */
class UserToken extends Token{
    protected $code;
    protected $wxLoginUrl;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $thirdapp_id;
    protected $parent_id;
    public $nickname = '';
    public $headimgurl = '';

    function __construct($data){
        //获取app_id，app_secret
        $ThirdAppInfo=ThirdAppModel::getThirdUserInfo($data['thirdapp_id']);
        $this->code = $data['code'];
        $this->thirdapp_id = $data['thirdapp_id'];
        $this->parent_id = isset($data['openid'])?$data['openid']:'';
        $this->wxAppID = $ThirdAppInfo['appid'];
        $this->wxAppSecret = $ThirdAppInfo['appsecret'];
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);
        if(isset($data['nickname'])){
            $this->nickname = $data['nickname'];
        }
        if (isset($data['headimgurl'])){
            $this->headimgurl = $data['headimgurl'];
        }
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
        if (empty($wxResult)) {
            // 为什么以empty判断是否错误，这是根据微信返回
            // 规则摸索出来的
            // 这种情况通常是由于传入不合法的code
            // throw new Exception('获取session_key及openID时异常，微信内部错误');
        }
        else {
            // 建议用明确的变量来表示是否成功
            // 微信服务器并不会将错误标记为400，无论成功还是失败都标记成200
            // 这样非常不好判断，只能使用errcode是否存在来判断
            $loginFail = array_key_exists('errcode', $wxResult);
            if ($loginFail) {
                $this->processLoginError($wxResult);
            }
            else {
                return $this->grantToken($wxResult);
            }
        }
        return $this->grantToken($wxResult);
    }

    // 判断是否重复获取
    private function duplicateFetch(){
       //TODO:目前无法简单的判断是否重复获取，还是需要去微信服务器去openid
        //TODO: 这有可能导致失效行为 
    }

    // 处理微信登陆异常
    // 那些异常应该返回客户端，那些异常不应该返回客户端
    // 需要认真思考
    private function processLoginError($wxResult)
    {
        throw new WeChatException(
            [
                'msg' => $wxResult['errmsg'],
                'errorCode' => $wxResult['errcode']
            ]);
    }

    // 写入缓存
    private function saveToCache($wxResult)
    {
        $key = self::generateToken();
        $value = json_encode($wxResult);
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

    // 颁发令牌
    // 只要调用登陆就颁发新令牌
    // 但旧的令牌依然可以使用
    // 所以通常令牌的有效时间比较短
    // 目前微信的express_in时间是7200秒
    // 在不设置刷新令牌（refresh_token）的情况下
    // 只能延迟自有token的过期时间超过7200秒（目前还无法确定，在express_in时间到期后
    // 还能否进行微信支付
    // 没有刷新令牌会有一个问题，就是用户的操作有可能会被突然中断
    private function grantToken($wxResult)
    {
        // 此处生成令牌使用的是TP5自带的令牌
        // 如果想要更加安全可以考虑自己生成更复杂的令牌
        // 比如使用JWT并加入盐，如果不加入盐有一定的几率伪造令牌
        //        $token = Request::instance()->token('token', 'md5');
        $openid = $wxResult['openid'];
        $user = UserModel::getByOpenID($openid);
        if (!$user)
            // 借助微信的openid作为用户标识
            // 但在系统中的相关查询还是使用自己的uid
        {
             if (isset($wxResult['unionid'])) {
                $unionid = $wxResult['unionid'];
                //判定unionid是否存在
                $checkunionid = UserModel::getByUnionID($unionid);
                if ($checkunionid) {
                    if ($checkunionid['status']==-1) {
                        throw new TokenException([
                            'msg' => '用户不存在或被禁用',
                            'errorCode' => 205000
                        ]);
                    }
                    $data['nickname'] = $this->nickname;
                    $data['headimgurl'] = json_encode(["0"=>['name'=>'headimg','url'=>$this->headimgurl]]);
                    $data['openid'] = $openid;
                    $updateinfo = UserModel::updateUserinfo($checkunionid->id,$data);
                    $uid = $checkunionid->id;
                }else{
                    $uid = $this->newUser($openid,$unionid);
                }
            }else{
                $uid = $this->newUser($openid);
            }
        }
        else {
            if ($user['status']==-1) {
                throw new TokenException([
                    'msg' => '用户不存在或被禁用',
                    'errorCode' => 205000
                ]);
            }
            $data['nickname'] = $this->nickname;
            $data['headimgurl'] =json_encode(["0"=>['name'=>'headimg','url'=>$this->headimgurl]]);
            $updateinfo = UserModel::updateUserinfo($user->id,$data);
            $uid = $user->id;
        }
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        $token = $this->saveToCache($cachedValue);
        $info['token'] = $token;
        $info['openid'] = $openid;
        return $info;
    }


    private function prepareCachedValue($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        $cachedValue['scope'] = ScopeEnum::User;
        return $cachedValue;
    }

    // 创建新用户
    private function newUser($openid,$unionid=''){
        // 有可能会有异常，如果没有特别处理
        // 这里不需要try——catch
        // 全局异常处理会记录日志
        // 并且这样的异常属于服务器异常
        // 也不应该定义BaseException返回到客户端
        $data['openid'] = $openid;
        $data['unionid'] = $unionid;
        $data['nickname'] = $this->nickname;
        $data['headimgurl'] = json_encode(["0"=>['name'=>'headimg','url'=>$this->headimgurl]]);
        $data['thirdapp_id'] = $this->thirdapp_id;
        //西工大项目初始具有信誉分100
        if ($this->thirdapp_id==19) {
            $data['user_score'] = 100;
        }
        $userid = UserModel::addUserinfo($data);
        //分销项目
        $thirdinfo = ThirdAppModel::getThirdUserInfo($data['thirdapp_id']);
        if ($thirdinfo['distribution']=="true") 
        {
            if (empty($this->parent_id)) {
                $parent_id = 0;
            }else{
                //获取父级ID
                $userinfo = UserModel::getByOpenID($this->parent_id);
                $memberinfo = MemberModel::getMemberInfoByUser($userinfo['id']);
                if ($memberinfo) {
                    $parent_id = $memberinfo['id'];
                }else{
                    $parent_id = 0;
                }
            }
            $memberinfo = array(
                'parent_id'=>$parent_id,
                'user_id'=>$userid,
                'rebateRule'=>json_encode([]),//个人分销规则，默认为空
                'QRcode'=>json_encode([]),//分销二维码，默认为空
                'thirdapp_id'=>$data['thirdapp_id'],
                'create_time'=>time(),
                'status'=>1
            );
            $addmember = MemberModel::addMember($memberinfo);
            //关注分销逻辑
            $distribution = new DistributionService();
            $doDistribution = $distribution->SubscribeDistribution($userid);
        }
        return $userid;
    }
}
