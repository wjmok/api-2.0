<?php
namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\User as UserModel;
use app\api\model\FlowBalance as FlowBalanceModel;
use app\api\service\Token as TokenService;
use think\Cache;

class HqyOrder{

    public function repay($order_id){

        $orderinfo = OrderModel::getOrderInfo($order_id);
        if ($orderinfo) {
            if($orderinfo['pay_status']==1&&$orderinfo['status']==1){
                $itemmap['status'] = 1;
                $itemmap['order_id'] = $order_id;
                $itemlist = OrderItemModel::getItemListByMap($itemmap);
                if ($itemlist) {
                    foreach ($itemlist as $key => $value) {
                        $snapinfo = json_decode($value['snap_product'],true);
                        //检查是否关联推荐者
                        if (isset($$itemlist[$key]['passage3'])) {
                            $userinfo = UserModel::getUserInfo($value['passage3']);
                            if ($userinfo&&$userinfo['status']==1) {
                                $upinfo['user_balance'] = $userinfo['user_balance']+(float)$snapinfo['o_price'];
                                $upuser = UserModel::updateUserinfo($userinfo['id'],$upinfo);
                                //记录余额流水
                                $flowBalance = array(
                                    'thirdapp_id'  =>$userinfo['thirdapp_id'],
                                    'user_id'      =>$userinfo['id'],
                                    'balanceAmount'=>$snapinfo['o_price'],
                                    'order_id'     =>$orderinfo['id'],
                                    'order_type'   =>$orderinfo['product_type'],
                                    'trade_type'   =>1,
                                    'trade_info'   =>'商品出售收入',
                                    'create_time'  => time(),
                                    'status'       =>1
                                    );
                                $BalanceAdd = FlowBalanceModel::addFlow($flowBalance);
                            }
                        }
                    }
                }
            }else{
                //订单状态异常
                return false；
            }
        }else{
            //订单不存在
            return false;
        }
    }
}