<?php
/**
 * Created by 七月.
 * Author: 七月
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 七月在夏天
 * Date: 2017/2/26
 * Time: 16:02
 */

namespace app\api\service;


use app\api\model\Order as OrderModel;
use app\api\model\ThirdApp as ThirdappModel;
use app\api\model\User as UserModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\FlowBalance as FlowBalanceModel;
use app\api\service\UserOrder as OrderService;
use app\api\service\CouponUseMain as CouponUseMainService;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');


class Pay
{
    private $orderNo;
    private $orderID;

    function __construct($orderID)
    {
        $this->orderID = $orderID;
    }

    public function pay()
    {
        $orderinfo = OrderModel::getOrderInfo($this->orderID);
        $userinfo = UserModel::getUserInfo($orderinfo['user_id']);
        //过滤余额支付
        if ((string)$orderinfo['actual_price']=="0") {
            $balancepay = $this->balancepay();
            $res['status'] = 1;
            $res['info'] = "余额支付成功";
            return $res;
        }
        //检测余额是否足够支付
        if ($orderinfo['balance_price']>$userinfo['user_balance']) {
            $res['status'] = -5;
            return $res;
        }
        //检测优惠券是否有效
        $orderinfo['couponList'] = json_decode($orderinfo['couponList'],true);
        if ($orderinfo['couponList']!==[]) {
            $userCouponList = array();
            foreach ($orderinfo['couponList'] as $item) {
                $coupon = UserCouponModel::getCouponInfo($item);
                array_push($userCouponList, $coupon);
            }
            foreach ($userCouponList as $item) {
                if ($item['user_id']!=$userinfo['id']) {
                    $res['status'] = -6;
                    return $res;
                }
                if ($item['deadline']<time()) {
                    $res['status'] = -7;
                    return $res;
                }
                if ($item['coupon']['status']==-1) {
                    $res['status'] = -8;
                    return $res;
                }
            }
        }
        if ($orderinfo['product_type']=="product") {
            $orderservice = new OrderService();
            $orderitem = $orderservice->getOrderItem($this->orderID);
            if ($orderinfo['price_type']=="normal") {
                //检查库存
                $checkstock = $orderservice->getOrderStatus($orderitem,$orderinfo['thirdapp_id']);
                if ($checkstock['status']!=1) {
                    if ($checkstock['status']==-7) {
                        $checkstock['status'] = -9;
                    }
                    return $checkstock;
                }
            }elseif ($orderinfo['price_type']=="group") {
               $checkgroup = $orderservice->getGroupStatus($orderitem,$orderinfo['thirdapp_id']);
               if ($checkgroup['status']!=1) {
                    if ($checkgroup['status']==-7) {
                        $checkgroup['status'] = -9;
                    }
                    if ($checkgroup['status']==-8) {
                        $checkgroup['status'] = -10;
                    }
                    return $checkgroup;
               }
            }
        }
        return $this->makeWxPreOrder($orderinfo);
    }

    // 构建微信支付订单信息
    private function makeWxPreOrder($orderinfo)
    {
        $userinfo = UserModel::getUserInfo($orderinfo['user_id']);
        $openid = $userinfo['openid'];
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($orderinfo['order_no']);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($orderinfo['actual_price']*100);
        $wxOrderData->SetBody('solelyService');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));

        //获取项目信息
        $thirdappinfo = ThirdappModel::getThirdUserInfo($orderinfo['thirdapp_id']);
        if ($thirdappinfo) {
            if ($thirdappinfo['status']==-1) {
                return $res['status'] = -4;
            }
        }else{
            return $res['status'] = -3;
        }
        return $this->getPaySignature($wxOrderData,$thirdappinfo);
    }

    //向微信请求订单号并生成签名
    private function getPaySignature($wxOrderData,$thirdappinfo)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData,$thirdappinfo);
        // 失败时不会返回result_code
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] !='SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
            throw new Exception('获取预支付订单失败');
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder,$thirdappinfo['appid'],$thirdappinfo['wxkey']);
        $res['status'] = 1;
        $res['info'] = $signature;
        return $res;
    }

    private function recordPreOrder($wxOrder)
    {
        // 必须是update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
        // OrderModel::where('id', '=', $this->orderID)
        // ->update(['prepay_id' => $wxOrder['prepay_id']]);
        $data['prepay_id'] = $wxOrder['prepay_id'];
        OrderModel::updateinfo($this->orderID,$data);
    }

    // 签名
    private function sign($wxOrder,$appid,$key)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid($appid);
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSignByThird($key);
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);
        return $rawValues;
    }

    /**
     * @return bool
     * @throws OrderException
     * @throws TokenException
     */
    private function checkOrderValid()
    {
        $order = OrderModel::where('id', '=', $this->orderID)
            ->find();
        if (!$order)
        {
            throw new OrderException();
        }
        if(!Token::isValidOperate($order->user_id))
        {
            throw new TokenException(
                [
                    'msg' => '你不是下单的人，请不要代付！',
                    'solelyCode' => 209012
                ]);
        }
        if($order->pay_status != 1){
            throw new OrderException([
                'msg' => '订单已支付',
                 'solelyCode' => 209017,
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }

    private function balancepay()
    {
        $orderinfo = OrderModel::getOrderInfo($this->orderID);
        //优惠券核销
        if ($orderinfo['coupon_price']!=0) {
            $couponlist = json_decode($orderinfo['couponList'],true);
            foreach ($couponlist as $value) {
                $updateinfo['coupon_status'] = 1;
                $updateinfo['use_time'] = time();
                $res = UserCouponModel::updateCoupon($value,$updateinfo);
            }
        }
        //使用余额并记录流水
        if ($orderinfo['balance_price']!=0) {
            $userinfo = UserModel::getUserInfo($orderinfo['user_id']);
            $updateuser = $userinfo['user_balance']-$orderinfo['balance_price'];
            $update = UserModel::updateUserinfo($orderinfo['user_id'],$updateuser);
            //记录余额流水
            $flowBalance = array(
                'create_time' => time(),
                'thirdapp_id'=>$userinfo['thirdapp_id'],
                'user_id'=>$userinfo['id'],
                'balanceAmount'=>$orderinfo['balance_price'],
                'order_id'=>$orderinfo['id'],
                'order_type'=>$orderinfo['product_type'],
                'trade_type'=>2,
                'trade_info'=>'支付'.$orderinfo['product_type'],
                'status'=>1
                );
            $BalanceAdd = FlowBalanceModel::addFlow($flowBalance);
        }
    }

    //关闭微信订单
    public function closeorder()
    {
        $orderinfo = OrderModel::getOrderInfo($this->orderID);
        //获取项目信息
        $thirdappinfo = ThirdappModel::getThirdUserInfo($orderinfo['thirdapp_id']);
        $wxOrderClose = new \WxPayCloseOrder();
        $wxOrderClose->SetOut_trade_no($orderinfo['order_no']);
        $wxOrder = \WxPayApi::closeOrder($wxOrderClose,$thirdappinfo);
        return $wxOrder;
    }

    //订单退款
    public function refundOrder()
    {
        $orderinfo = OrderModel::getOrderInfo($this->orderID);
        //获取项目信息
        $thirdappinfo = ThirdappModel::getThirdUserInfo($orderinfo['thirdapp_id']);
        $wxOrderRefund = new \WxPayRefund();
        $wxOrderRefund->SetTransaction_id($orderinfo['transaction_id']);
        $wxOrderRefund->SetOut_trade_no($orderinfo['order_no']);
        $wxOrderRefund->SetOut_refund_no($orderinfo['refund_no']);
        $wxOrderRefund->SetTotal_fee($orderinfo['actual_price']*100);
        $wxOrderRefund->SetRefund_fee($orderinfo['actual_price']*100);
        $wxOrderRefund->SetOp_user_id($thirdappinfo['mchid']);
        $wxOrder = \WxPayApi::refund($wxOrderRefund,$thirdappinfo);
        return $wxOrder;
    }
}