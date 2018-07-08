<?php
namespace app\api\controller\v1;
use think\Controller;
use app\api\controller\CommonController;
use app\api\model\ThirdApp as ThirdModel;
use think\Request as Request;
use app\api\service\Token as TokenService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

//亿港积分商城项目
class JifenscUser extends CommonController{

    protected $beforeActionList = [
        'checkToken' => ['only' => 'getDiscount']
    ];

    //获取超级会员折扣
    public function getDiscount(){
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $thirdinfo = ThirdModel::getThirdUserInfo($userinfo['thirdapp_id']);
        if ($thirdinfo) {
            if (isset($thirdinfo['custom_rule'])&&$thirdinfo['custom_rule']!="[]") {
                $customRule = json_decode($thirdinfo['custom_rule'],true);
                $discount = (float)$customRule['discount']/10;
                $userinfo['discount'] = $discount;
                return $userinfo;
            }else{
                throw new TokenException([
                    'msg' => '未设置相关参数',
                    'solelyCode' => 201000
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '项目不存在或异常',
                'solelyCode' => 201000
            ]);
        }
    }
}