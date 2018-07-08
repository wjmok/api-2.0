<?php
namespace app\api\controller\v1;
use think\Request as Request;
use think\Controller;
use app\api\controller\BaseController;
use app\api\model\ThirdApp as ThirdModel;
use app\api\model\Order as OrderModel;
use app\api\model\User as UserModel;
use app\api\model\FlowBalance as BalanceModel;
use app\api\model\ThemeContent as ThemeContentModel;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

//沐雅居项目特有功能
class MuyajuCms extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'receiveOrder,useOrder,refuseOrder,cancelOrder'],
    ];

    //设计分销奖励
    public function setDistribution(){

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

    //接收订单
    public function receiveOrder()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (!empty($orderinfo)) {
            if ($orderinfo['status']==-1) {
                throw new TokenException([
                    'msg'=>'订单已删除',
                    'solelyCode'=>209006
                ]);
            }else{
                if ($orderinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                    throw new TokenException([
                        'msg'=>'订单不属于本商户',
                        'solelyCode'=>209016
                    ]);
                }
                if ($orderinfo['pay_status']!=1) {
                    throw new TokenException([
                        'msg'=>'订单未支付',
                        'solelyCode'=>209021
                    ]);
                }
                if ($orderinfo['order_step']!=0) {
                    throw new TokenException([
                        'msg'=>'订单已退，不能接单',
                        'solelyCode'=>209021
                    ]);
                }
                $update['transport_status'] = 1;
                $res = OrderModel::updateinfo($data['id'],$update);
                if ($res==1) {
                    throw new SuccessMessage([
                        'msg'=>'接单成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg'=>'接单失败',
                        'solelyCode'=>209019
                    ]);
                }
            }
        }else{
            throw new TokenException([
                'msg'=>'订单不存在，请检查ID',
                'solelyCode'=>209000
            ]);
        }
    }

    //到店核销订单
    public function useOrder()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (!empty($orderinfo)) {
            if ($orderinfo['status']==-1) {
                throw new TokenException([
                    'msg'=>'订单已删除',
                    'solelyCode'=>209006
                ]);
            }else{
                if ($orderinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                    throw new TokenException([
                        'msg'=>'订单不属于本商户',
                        'solelyCode'=>209016
                    ]);
                }
                if ($orderinfo['pay_status']!=1) {
                    throw new TokenException([
                        'msg'=>'订单未支付',
                        'solelyCode'=>209021
                    ]);
                }
                if ($orderinfo['order_step']!=0) {
                    throw new TokenException([
                        'msg'=>'订单已退，不能核销',
                        'solelyCode'=>209021
                    ]);
                }
                if ($orderinfo['transport_status']!=1) {
                    throw new TokenException([
                        'msg'=>'订单未接单，不能核销',
                        'solelyCode'=>209021
                    ]);
                }
                $update['transport_status'] = 2;
                $res = OrderModel::updateinfo($data['id'],$update);
                if ($res==1) {
                    throw new SuccessMessage([
                        'msg'=>'核销订单成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg'=>'核销订单成功',
                        'solelyCode'=>209019
                    ]);
                }
            }
        }else{
            throw new TokenException([
                'msg'=>'订单不存在，请检查ID',
                'solelyCode'=>209000
            ]);
        }
    }

    //商家退单-拒绝接单
    public function refuseOrder()
    {
        $data = Request::instance()->param();
        $admininfo = Cache::get('info'.$data['token']);
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (!empty($orderinfo)) {
            if ($orderinfo['status']==-1) {
                throw new TokenException([
                    'msg'=>'订单已删除',
                    'solelyCode'=>209006
                ]);
            }else{
                if ($orderinfo['thirdapp_id']!=$admininfo['thirdapp_id']) {
                    throw new TokenException([
                        'msg'=>'订单不属于本商户',
                        'solelyCode'=>209016
                    ]);
                }
                if ($orderinfo['order_step']!=0) {
                    throw new TokenException([
                        'msg'=>'订单已退，请勿重复请求',
                        'solelyCode'=>209023
                    ]);
                }
                $update['order_step'] = 1;
                $res = OrderModel::updateinfo($data['id'],$update);
                if ($res==1) {
                    if ($orderinfo['pay_status']=1) {
                        //退还实付金额与抵扣余额到余额，优惠券不恢复
                        $refund = $orderinfo['actual_price']+$orderinfo['balance_price'];
                        $userinfo = UserModel::getUserInfo($orderinfo['user_id']);
                        $upuserinfo['user_balance'] = $userinfo['user_balance']+$refund;
                        $upuser = UserModel::updateUserinfo($orderinfo['user_id'],$upuserinfo);
                        //记录流水
                        $flow = array(
                            'thirdapp_id'  =>$orderinfo['thirdapp_id'],
                            'user_id'      =>$userinfo['id'],
                            'balanceAmount'=>$refund,
                            'order_id'     =>$orderinfo['id'],
                            'order_type'   =>'refund',
                            'trade_type'   =>1,
                            'trade_info'   =>'订单退款',
                            'create_time'  =>time(),
                            'status'       =>1,
                        );
                        $saveflow = BalanceModel::addFlow($flow);
                    }
                    throw new SuccessMessage([
                        'msg'=>'退单成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg'=>'退单失败',
                        'solelyCode'=>209022
                    ]);
                }
            }
        }else{
            throw new TokenException([
                'msg'=>'订单不存在，请检查ID',
                'solelyCode'=>209000
            ]);
        }
    }

    //商家取消订单-客户预约未到，扣除30%费用
    public function cancelOrder()
    {
        $data = Request::instance()->param();
        $admininfo = Cache::get('info'.$data['token']);
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (!empty($orderinfo)) {
            if ($orderinfo['status']==-1) {
                throw new TokenException([
                    'msg'=>'订单已删除',
                    'solelyCode'=>209006
                ]);
            }else{
                if ($orderinfo['thirdapp_id']!=$admininfo['thirdapp_id']) {
                    throw new TokenException([
                        'msg'=>'订单不属于本商户',
                        'solelyCode'=>209016
                    ]);
                }
                if ($orderinfo['order_step']!=0) {
                    throw new TokenException([
                        'msg'=>'订单已退，请勿重复请求',
                        'solelyCode'=>209023
                    ]);
                }
                if ($orderinfo['pay_status']!=1) {
                    throw new TokenException([
                        'msg'=>'订单未支付，不能退款',
                        'solelyCode'=>209018
                    ]);
                }
                $update['order_step'] = 2;
                $update['balance_price'] = 0;
                $update['actual_price'] = ($orderinfo['actual_price']+$orderinfo['balance_price'])*0.3;
                $res = OrderModel::updateinfo($data['id'],$update);
                if ($res==1) {
                    //退还实付金额与抵扣余额到余额，扣除30%费用
                    $refund = ($orderinfo['actual_price']+$orderinfo['balance_price'])*0.7;
                    $userinfo = UserModel::getUserInfo($orderinfo['user_id']);
                    $upuserinfo['user_balance'] = $userinfo['user_balance']+$refund;
                    $upuser = UserModel::updateUserinfo($orderinfo['user_id'],$upuserinfo);
                    //记录流水
                    $flow = array(
                        'thirdapp_id'  =>$orderinfo['thirdapp_id'],
                        'user_id'      =>$userinfo['id'],
                        'balanceAmount'=>$refund,
                        'order_id'     =>$orderinfo['id'],
                        'order_type'   =>'refund',
                        'trade_type'   =>1,
                        'trade_info'   =>'未赴约订单退款',
                        'create_time'  =>time(),
                        'status'       =>1,
                    );
                    $saveflow = BalanceModel::addFlow($flow);
                    throw new SuccessMessage([
                        'msg'=>'撤单成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg'=>'撤单失败',
                        'solelyCode'=>209022
                    ]);
                }
            }
        }else{
            throw new TokenException([
                'msg'=>'订单不存在，请检查ID',
                'solelyCode'=>209000
            ]);
        }
    }

    //设置预约时间
    public function setBookTime()
    {
        $data = Request::instance()->param();
        if (!isset($data['booktime'])) {
            throw new TokenException([
                'msg'=>'缺少预约时间参数',
                'solelyCode'=>213005
            ]);
        }
        $update['description'] = $data['booktime'];
        //关联指定内容
        $res = ThemeContentModel::updateContent(2,$update);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'修改预约时间成功'
            ]);
        }else{
            throw new TokenException([
                'msg'=>'修改预约时间失败',
                'solelyCode'=>213004
            ]);
        }
    }
}