<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * Date: 2018/3/29
 * Time: 9:34
 */

namespace app\api\service;


use app\api\model\Order as OrderModel;
use app\api\model\Thirdapp as ThirdappModel;
use app\api\model\Admin as AdminModel;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');


class MerchantPay
{
    private $orderNo;
    private $orderID;
    private $adminID;

    function __construct($orderID,$adminID)
    {
        $this->orderID = $orderID;
        $this->adminID = $adminID;
    }

    public function refund()
    {
        $orderinfo = OrderModel::getOrderInfo($this->orderID);
        return $this->makeWxRefund($orderinfo);
    }

    // 构建微信退款信息
    private function makeWxRefund($orderinfo)
    {
        $admininfo = AdminModel::getAdminInfo($this->adminID);
        $wxRefundData = new \WxPayRefund();
        $wxRefundData->SetOut_trade_no($orderinfo['order_no']);
        // $wxRefundData->SetTransaction_id($orderinfo['transaction_id']);
        $wxRefundData->SetOut_refund_no($orderinfo['order_no']);
        $wxRefundData->SetTotal_fee($orderinfo['total_price']*100);
        $wxRefundData->SetRefund_fee($orderinfo['total_price']*100);
        $wxRefundData->SetOp_user_id($this->adminID);

        //获取项目信息
        $thirdappinfo = ThirdappModel::getThirdUserInfo($orderinfo['thirdapp_id']);
        if ($thirdappinfo) {
            if ($thirdappinfo['status']==-1) {
                return $res['status'] = -2;
            }
        }else{
            return $res['status'] = -1;
        }
        return $this->getRefundSignature($wxRefundData,$thirdappinfo);
    }

    //向微信请求退款
    private function getRefundSignature($wxRefundData,$thirdappinfo)
    {
        $wxOrder = \WxPayApi::refund($wxRefundData,$thirdappinfo);
        return $wxOrder;
        // 失败时不会返回result_code
        // if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] !='SUCCESS'){
        //     Log::record($wxOrder,'error');
        //     Log::record('获取预支付订单失败','error');
        //     throw new Exception('获取预支付订单失败');
        // }
        // $signature = $this->sign($wxOrder,$thirdappinfo['appid']);
        // $res['status'] = 1;
        // $res['info'] = $signature;
        // return $res;
    }

    // 签名
    private function sign($wxOrder,$appid)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid($appid);
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);
        return $rawValues;
    }

    //检查订单
    public function checkOrderValid()
    {
        $orderinfo = OrderModel::getOrderInfo($this->orderID);
        if ($orderinfo) {
            if ($orderinfo['status']==-1) {
                return -2;
            }
            if ($orderinfo['pay_status']!=1) {
                return -3;
            }
            return 1;
        }else{
            return -1;
        }
    }
}