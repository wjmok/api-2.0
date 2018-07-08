<?php
namespace app\api\service;
use app\api\model\ApplyCash as ApplyCashModel;
use app\api\model\User as UserModel;
use app\api\model\FlowBalance as BalanceModel;
use app\api\service\Token as TokenService;
use think\Cache;

class ApplyCash{

    public function addinfo($data){
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['apply_status'] = 0;
        $data['apply_no'] = $this->makeOrderNo();
        $userinfo = TokenService::getUserinfo($data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['apply_user_id'] = $userinfo['id'];
        //处理人员信息默认为空
        $data['deal_user_id'] = 0;
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        $admininfo = Cache::get('info'.$token);
        //获取提现申请信息
        $applyinfo = ApplyCashModel::getApplyInfo($id);
        if ($applyinfo) {
            if ($admininfo['thirdapp_id']!=$applyinfo['thirdapp_id']) {
                return -1;
            }
            if ($applyinfo['status']==-1) {
                return -2;
            }
            if ($applyinfo['apply_status']!=0) {
                return -4;
            }
        }else{
            return -3;
        }
        return 1;
    }

    public function saveFlow($data)
    {
        $userinfo = TokenService::getUserinfo($data['token']);
        $upuserinfo['user_balance'] = $userinfo['user_balance']-$data['apply_count'];
        $upuser = UserModel::updateUserinfo($userinfo['id'],$upuserinfo);
        //记录流水
        $flow = array(
            'thirdapp_id'  =>$userinfo['thirdapp_id'],
            'user_id'      =>$userinfo['id'],
            'balanceAmount'=>$data['apply_count'],
            'order_id'     =>0,//未关联订单
            'order_type'   =>'cash',
            'trade_type'   =>2,
            'trade_info'   =>'用户提现',
            'create_time'  =>time(),
            'status'       =>1,
        );
        $saveflow = BalanceModel::addFlow($flow);
    }

    //拒绝提现，退还余额
    public function returnBalance($data)
    {
        //获取提现申请信息
        $applyinfo = ApplyCashModel::getApplyInfo($data['id']);
        $userinfo = UserModel::getUserInfo($applyinfo['apply_user_id']);
        $upuserinfo['user_balance'] = $userinfo['user_balance']+$applyinfo['apply_count'];
        $upuser = UserModel::updateUserinfo($userinfo['id'],$upuserinfo);
        //记录流水
        $flow = array(
            'thirdapp_id'  =>$userinfo['thirdapp_id'],
            'user_id'      =>$userinfo['id'],
            'balanceAmount'=>$applyinfo['apply_count'],
            'order_id'     =>0,//未关联订单
            'order_type'   =>'refund',
            'trade_type'   =>1,
            'trade_info'   =>'用户提现未通过，退还余额',
            'create_time'  =>time(),
            'status'       =>1,
        );
        $saveflow = BalanceModel::addFlow($flow);
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }

    //生成申请单号
    public function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
}