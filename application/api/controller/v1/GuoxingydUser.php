<?php
namespace app\api\controller\v1;
use think\Controller;
use app\api\controller\CommonController;
use app\api\model\User as UserModel;
use think\Request as Request;
use app\api\service\Token as TokenService;
use app\api\service\DistributionMain as DistributionService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

//果行育德项目特有功能
class GuoxingydUser extends CommonController{

    protected $beforeActionList = [
        'checkToken' => ['only' => 'editUserinfo']
    ];

    //修改个人信息
    public function editUserinfo(){
        $data = Request::instance()->param();
        if (isset($data['headimgurl'])) {
            $data['headimgurl'] = initimg($data['headimgurl']);
        }
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $user = UserModel::updateUserinfo($userinfo['id'],$data);
        if ($user==1) {
            //补充资料奖励
            $distribution = new DistributionService();
            $doDistribution = $distribution->CompleteInfo($userinfo['id']);
            throw new SuccessMessage([
                'msg' => '修改信息成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改信息失败',
                'solelyCode' => 205002
            ]);
        }
    }
}