<?php
/**
 * Created by 七月.
 * Author: 七月
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 七月在夏天
 * Date: 2017/2/26
 * Time: 14:15
 */

namespace app\api\controller\v1;

use think\Db;
use think\Controller;
use app\api\controller\CommonController;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use app\api\model\Order as OrderModel;
use app\api\model\User as UserModel;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\model\FlowBalance as FlowBalanceModel;
use app\api\service\Token as TokenService;
use think\Request as Request;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class Pay extends CommonController
{
    protected $beforeActionList = [
        'checkToken' => ['only' => 'getPreOrder']
    ];
    
    public function getPreOrder()
    {
        $data = Request::instance()->param();
        if (!isset($data['id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数订单id',
                'solelyCode' => 209005
            ]);
        }
        //获取订单信息
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (empty($orderinfo)) {
            throw new TokenException([
                'msg' => '订单不存在，请检查ID',
                'solelyCode' => 209000
            ]);
        }
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        if ($userinfo['id']!=$orderinfo['user_id']) {
            throw new TokenException([
                'msg' => '你不是下单的人，请不要代付！',
                'solelyCode' => 209012
            ]);
        }
        if($orderinfo['pay_status']==1){
            throw new TokenException([
                'msg' => '订单已支付',
                 'solelyCode' => 209017,
            ]);
        }
        //实际支付金额小于0,使用余额支付，记录流水
        if ($orderinfo['actual_price']<=0) {
            $update['pay_status'] = 1;
            $uporder = OrderModel::updateinfo($data['id'],$update);
            //更新用户余额
            $updateuserinfo['user_balance'] = $userinfo['user_balance']-$info['actual_price'];
            if ($updateuserinfo['user_balance']<0) {
                throw new TokenException([
                    'msg' => '余额不足支付',
                    'solelyCode' => 209020
                ]);
            }
            $updateuser = UserModel::updateUserinfo($userinfo['id'],$updateuserinfo);
            if ($updateuser==1) {
                //记录积分流水
                $balanceflow = array(
                    'thirdapp_id'=>$userinfo['thirdapp_id'],
                    'user_id'=>$userinfo['id'],
                    'balanceAmount'=>$info['actual_price'],
                    'order_id'=>$data['id'],
                    'order_type'=>'product',
                    'trade_type'=>2,
                    'trade_info'=>'购买商品',
                    'create_time'=>time(),
                );
                $addflow = FlowBalanceModel::addFlow($balanceflow);
                throw new SuccessMessage([
                    'msg' => '余额支付成功'
                ]);
            }else{
                throw new TokenException([
                    'msg' => '余额支付失败',
                    'solelyCode' => 209031
                ]);
            }
        }
        //订单-积分兑换
        if ($orderinfo['price_type']=="score") {
            $update['pay_status'] = 1;
            $uporder = OrderModel::updateinfo($orderID,$update);
            //更新用户积分
            $updateuserinfo['user_score'] = $userinfo['user_score']-$info['actual_price'];
            $updateuser = UserModel::updateUserinfo($userinfo['id'],$updateuserinfo);
            if ($updateuser==1) {
                //记录积分流水
                $scoreflow = array(
                    'thirdapp_id'=>$userinfo['thirdapp_id'],
                    'user_id'=>$userinfo['id'],
                    'scoreAmount'=>$info['actual_price'],
                    'order_id'=>$orderID,
                    'order_type'=>'product',
                    'trade_type'=>2,
                    'trade_info'=>'积分商城购买',
                    'create_time'=>time(),
                );
                $addflow = FlowScoreModel::addFlow($scoreflow);
                return ['msg'=>'积分支付成功','solely_code'=>100000];
            }else{
                throw new TokenException([
                    'msg' => '积分支付失败',
                    'solelyCode' => 209030
                ]);
            }
        }
        /*
         *判断订单是否失效
         *主要是校验prepay_id有2小时有效期
         */
        if ($orderinfo['invalid_time']!=0&&$orderinfo['wx_prepay_info']!=="[]") {
            if ($orderinfo['invalid_time']>time()) {
                $payinfo = json_decode($orderinfo['wx_prepay_info'],true);
                return $payinfo;
            }else{
                //订单失效，执行微信撤单流程
                $pay = new PayService($data['id']);
                $closeorder = $pay->closeorder();
                throw new TokenException([
                    'msg' => '订单已失效',
                     'solelyCode' => 209000,
                ]);
            }
        }
        $pay = new PayService($data['id']);
        $res = $pay->pay();
        switch ($res['status']) {
            case 1:
                //将微信预支付信息存入数据库
                if (is_array($res['info'])) {
                    $update['wx_prepay_info'] = json_encode($res['info']);
                    $update['invalid_time'] = time()+7000;;
                    $uporder = OrderModel::updateinfo($data['id'],$update);
                }
                return $res['info'];
            case -1:
                throw new TokenException([
                    'msg' => '下单的商品型号'.$res['id'].'不存在或已下架',
                    'solelyCode' => 209001
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '下单的商品型号'.$res['id'].'库存不足',
                    'solelyCode' => 209002
                ]);
            case -3:
                throw new TokenException();
            case -4:
                throw new TokenException([
                    'msg' => '该商户信息被删除',
                    'solelyCode' => 201003
                ]);
            case -5:
                throw new TokenException([
                    'msg' => '余额不足支付，请重新下单',
                    'solelyCode' => 209020
                ]);
            case -6:
                throw new TokenException([
                    'msg' => '用户优惠券不存在',
                    'solelyCode' => 220000
                ]);
            case -7:
                throw new TokenException([
                    'msg' => '优惠券已过期',
                    'solelyCode' => 220009
                ]);
            case -8:
                throw new TokenException([
                    'msg' => '商家优惠券已删除',
                    'solelyCode' => 219002
                ]);
            case -9:
                throw new TokenException([
                    'msg' => '下单的商品型号'.$res['id'].'不属于本商家',
                    'solelyCode' => 211004
                ]);
            case -10:
                throw new TokenException([
                    'msg' => '下单的商品型号'.$res['id'].'未开启团购',
                    'solelyCode' => 211015
                ]);
        }
    }

    public function redirectNotify()
    {
        $notify = new WxNotify();
        $notify->handle();
    }

    public function notifyConcurrency()
    {
        $notify = new WxNotify();
        $notify->handle();
    }
}