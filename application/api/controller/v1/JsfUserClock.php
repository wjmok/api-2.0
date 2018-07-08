<?php
namespace app\api\controller\v1;
use app\api\model\JsfClock as ClockModel;
use app\api\model\User as UserModel;
use app\api\model\JsfChallenge as ChallengeModel;
use app\api\model\ThirdApp as ThirdModel;
use app\api\model\FlowScore as FlowScoreModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use think\Exception;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\service\JsfClock as ClockService;
use app\api\service\Token as TokenService;

//健身房项目客户打卡接口
class JsfUserClock extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'addClock'],
        // 'checkToken' => ['only' => 'addClock,getList']
    ];

    public function addClock()
    {
        $data = Request::instance()->param();
        // $userinfo = TokenService::getUserinfo($data['token']);
        $userinfo['id'] = 2;
        $userinfo['thirdapp_id'] = 24;
        $userinfo['user_score'] = 0;
        if (isset($data['parent_id'])) {
            $parentinfo = UserModel::getUserInfo($data['parent_id']);
            if (empty($parentinfo)) {
                throw new TokenException([
                    'msg' => '推荐人信息不存在',
                    'solelyCode'=>216001
                ]);
            }else{
                if ($parentinfo['status']==-1) {
                    throw new TokenException([
                        'msg' => '推荐人已删除',
                        'solelyCode'=>216002
                    ]);
                }
                if ($parentinfo['thirdapp_id']!=$userinfo['userinfo']) {
                    throw new TokenException([
                        'msg' => '推荐人不属于本商家',
                        'solelyCode'=>216008
                    ]);
                }
            }
        }

        $challengeinfo = ChallengeModel::getChallengeInfo($data['id']);
        if (empty($challengeinfo)) {
            throw new TokenException([
                'msg' => '打卡的挑战赛不存在',
                'solelyCode'=>216003
            ]);
        }else{
            if ($challengeinfo['status']==-1) {
                throw new TokenException([
                    'msg' => '打卡的挑战赛已删除',
                    'solelyCode'=>216004
                ]);
            }
            if ($challengeinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '打卡的挑战赛不属于本商家',
                    'solelyCode'=>216005
                ]);
            }
        }

        //检测是否已参与
        $map['user_id'] = $userinfo['id'];
        $map['act_id'] = $data['id'];
        $check = ClockModel::getClockByMap($map);
        if ($check) {
            throw new TokenException([
                'msg' => '您已参与过此次挑战赛',
                'solelyCode'=>216006
            ]);
        }
        $data['user_id'] = $userinfo['id'];
        $data['act_id'] = $data['id'];
        unset($data['id']);
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $res = ClockModel::addClock($data);
        if ($res==1) {
            //记录积分
            $thirdinfo = ThirdModel::getThirdUserInfo($userinfo['thirdapp_id']);
            if ($thirdinfo['custom_rule']!="[]") {
                $customRule = json_decode($thirdinfo['custom_rule'],true);
                if (isset($customRule['clock'])) {
                    $clock = (int)$customRule['clock'];
                }
                $upuser['user_score'] = (int)$userinfo['user_score']+$clock;
                $updateuser = UserModel::updateUserinfo($userinfo['id'],$upuser);
                //记录流水
                $flow = array(
                    'user_id'    => $userinfo['id'],
                    'thirdapp_id'=> $userinfo['thirdapp_id'],
                    'scoreAmount'=> $clock,
                    'order_type' => 'clock',
                    'trade_type' => 1,
                    'trade_info' => "参与挑战赛".$challengeinfo['name'].'打卡',
                    'create_time'=> time(),
                );
                $addFlow = FlowScoreModel::addFlow($flow);
                //父级奖励
                if (isset($data['parent_id'])&&isset($customRule['reward'])) {
                    $reward = (int)$customRule['reward'];
                    $upparent['user_score'] = (int)$parentinfo['user_score']+$reward;
                    $updateparent = UserModel::updateUserinfo($parentinfo['id'],$upparent);
                    //记录流水
                    $rewardflow = array(
                        'user_id'    => $parentinfo['id'],
                        'thirdapp_id'=> $parentinfo['thirdapp_id'],
                        'scoreAmount'=> $reward,
                        'order_type' => 'reward',
                        'trade_type' => 1,
                        'trade_info' => "推荐".$userinfo['id']."参与挑战赛".$challengeinfo['name'].'奖励',
                        'create_time'=> time(),
                    );
                    $addRewardFlow = FlowScoreModel::addFlow($rewardflow);
                }
            }
            throw new SuccessMessage([
                'msg'=>'打卡成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '打卡失败',
                'solelyCode'=>216007
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $clockservice = new ClockService();
        // $userinfo = TokenService::getUserinfo($data['token']);
        $userinfo['id'] = 2;
        $userinfo['thirdapp_id'] = 24;
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ClockModel::getClockListToPaginate($info);
            $list = $clockservice->formatPaginateList($list);
        }else{
            $list = ClockModel::getClockList($info);
            $list = $clockservice->formatList($list);
        }
        return $list;
    }
}