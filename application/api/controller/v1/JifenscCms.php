<?php
namespace app\api\controller\v1;
use think\Request as Request;
use think\Controller;
use app\api\controller\BaseController;
use app\api\model\ThirdApp as ThirdModel;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

//亿港积分商城项目
class JifenscCms extends BaseController{

    public function setDiscount()
    {
        $data = Request::instance()->param();
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        if ($userinfo['primary_scope']<30) {
            throw new TokenException([
                'msg'=>'您权限不足，不能执行该操作',
                'solelyCode'=>201009
            ]);
        }
        if (isset($data['custom_rule']['discount'])) {
            $data['custom_rule']['discount'] = (float)$data['custom_rule']['discount']*10;
        }
        if (isset($data['custom_rule']['signin'])) {
            $distributionRule['signin'] = $data['custom_rule']['signin'];
            $update['distributionRule'] = json_encode($distributionRule);
            unset($data['custom_rule']['signin']);
        }
        $update['custom_rule'] = json_encode($data['custom_rule']);
        $res = ThirdModel::upTuser($userinfo['thirdapp_id'],$update);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'修改成功'
            ]);
        }else{
            throw new TokenException([
                'msg'=>'修改商户信息失败',
                'solelyCode'=>201012
            ]);
        }
    }

    public function setDistribution()
    {

        $data = Request::instance()->param();
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        if ($userinfo['primary_scope']<30) {
            throw new TokenException([
                'msg'=>'您权限不足，不能执行该操作',
                'solelyCode'=>201009
            ]);
        }
        $update['distributionRule'] = json_encode($data['distributionRule']);
        $res = ThirdModel::upTuser($userinfo['thirdapp_id'],$update);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'修改成功'
            ]);
        }else{
            throw new TokenException([
                'msg'=>'修改商户信息失败',
                'solelyCode'=>201012
            ]);
        }
    }
}