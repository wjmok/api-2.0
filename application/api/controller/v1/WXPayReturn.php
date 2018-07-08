<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * Date: 2018/4/9
 * Time: 19:23
 */

namespace app\api\controller\v1;

use think\Db;
use think\Controller;
use app\api\model\Order as OrderModel;
use app\api\model\User as UserModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\FlowMoney as FlowMoneyModel;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\model\FlowBalance as FlowBalanceModel;
use app\api\model\Wxpaylog as WxpaylogModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\MemberCard as MemberCardModel;
use app\api\model\UserCard as UserCardModel;
use app\api\service\UserOrder as OrderService;
use app\api\service\DistributionMain as DistributionService;
use app\api\service\HqyOrder as HqyOrderService;
use think\Request as Request;
use app\lib\exception\TokenException;

class WXPayReturn extends Controller
{
    //支付回调
    public function receiveNotify(){
        $xmlData = file_get_contents('php://input');
        $data = $this->xml2array($xmlData);
        //开始支付回调逻辑....
        if($data['RESULT_CODE']=='SUCCESS'){
            $orderNo = $data['OUT_TRADE_NO'];
            
            //test date
            // $orderNo = "B428270515900389";
            // $data['OPENID'] = "ooxGP4rYlSMPC_KojJAyMs9U-FzM";
            // $data['TOTAL_FEE'] = 1;

            //根据订单号查询订单信息
            $orderinfo = OrderModel::getOrderByID($orderNo);
            //记录微信支付回调日志
            $log = array(
                'title'=>'微信支付',
                'result'=>$data['RESULT_CODE'],
                'content'=>json_encode($data),
                'user_id'=>$orderinfo['user_id'],
                'thirdapp_id'=>$orderinfo['thirdapp_id'],
                'create_time'=>time(),
            );
            $saveLog = WxpaylogModel::addLog($log);
            $userinfo = UserModel::getUserInfo($orderinfo['user_id']);
            //如果状态更新，阻止微信再次请求
            if($orderinfo['pay_status']==1){
                return true;
            }
            if ($orderinfo['product_type']=="product") {
                //设置事务
                Db::startTrans();
                try{
                    $orderservice = new OrderService();
                    $orderitem = $orderservice->getOrderItem($orderinfo['id']);
                    if ($orderinfo['price_type']=="normal") {
                        //检查库存
                        $checkstock = $orderservice->getOrderStatus($orderitem,$orderinfo['thirdapp_id']);
                        if ($checkstock['status']!=1) {
                            switch ($checkstock['status']) {
                                case -1:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$checkstock['id'].'不存在或已下架',
                                        'solelyCode' => 209001
                                    ]);
                                case -2:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$checkstock['id'].'库存不足',
                                        'solelyCode' => 209002
                                    ]);
                            }
                        }
                        //扣除库存
                        foreach ($orderitem as $item) {
                            $reduce = $orderservice->reduceStock($item['model_id'],$item['count']);
                            switch ($reduce['status']) {
                                case 1:
                                    break;
                                case -1:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$reduce['id'].'不存在或已下架',
                                        'solelyCode' => 209001
                                    ]);
                                case -2:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$reduce['id'].'库存不足',
                                        'solelyCode' => 209002
                                    ]);
                                case -3:
                                    throw new TokenException([
                                        'msg' => '商品型号'.$saveItem['id'].'扣除库存失败',
                                        'solelyCode' => 209004
                                    ]);
                            }
                        }
                    }elseif ($orderinfo['price_type']=="group") {
                        //检查库存
                        $checkgroup = $orderservice->getOrderStatus($orderitem,$orderinfo['thirdapp_id']);
                        if ($checkgroup['status']!=1) {
                            switch ($checkgroup['status']) {
                                case -1:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$checkgroup['id'].'不存在或已下架',
                                        'solelyCode' => 209001
                                    ]);
                                case -2:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$checkgroup['id'].'库存不足',
                                        'solelyCode' => 209002
                                    ]);
                                case -8:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$checkgroup['id'].'未开启团购',
                                        'solelyCode' => 211015
                                    ]);
                            }
                        }
                        //扣除库存
                        foreach ($orderitem as $item) {
                            $reduce = $orderservice->reduceGroupStock($item['model_id'],$item['count']);
                            switch ($reduce['status']) {
                                case 1:
                                    break;
                                case -1:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$reduce['id'].'不存在或已下架',
                                        'solelyCode' => 209001
                                    ]);
                                case -2:
                                    throw new TokenException([
                                        'msg' => '下单的商品型号'.$reduce['id'].'库存不足',
                                        'solelyCode' => 209002
                                    ]);
                                case -3:
                                    throw new TokenException([
                                        'msg' => '商品型号'.$saveItem['id'].'扣除库存失败',
                                        'solelyCode' => 209004
                                    ]);
                            }
                        }
                    }
                    //更新订单状态
                    $orderUpt = Db::table('order')
                    ->where('order_no',$orderNo)
                    ->update([
                        'pay_status' => 1,
                        'transaction_id' => $data['TRANSACTION_ID'],
                    ]);
                    //添加现金流水信息
                    $flowMoney = array(
                        'openid' =>$data['OPENID'],
                        'create_time'=>time(),
                        'moneyAmount'=>$data['TOTAL_FEE']/100,
                        'order_no'=>$orderNo,
                        'order_id'=>$orderinfo['id'],
                        'order_type'=>'product',
                        'user_id'=>$orderinfo['user_id'],
                        'thirdapp_id'=>$orderinfo['thirdapp_id'],
                        'trade_type'=>1,
                        'app_type'=>1,
                        'status'=>1
                        );
                    $MoneyAdd = FlowMoneyModel::addFlow($flowMoney);
                    //使用余额并记录流水
                    if ($orderinfo['balance_price']!=0) {
                        $updateuser['user_balance'] = $userinfo['user_balance']-$orderinfo['balance_price'];
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
                    //判断是否需要执行分销逻辑
                    $thirdinfo = ThirdAppModel::getThirdUserInfo($orderinfo['thirdapp_id']);
                    if ($thirdinfo['distribution']=="true") 
                    {
                        $distribution = new DistributionService();
                        $doDistribution = $distribution->OrderDistribution($orderinfo['id']);
                        switch ($doDistribution) {
                            case 1:
                                break;
                            case -1:
                                throw new TokenException([
                                    'msg' => '订单未支付',
                                    'solelyCode' => 209021
                                ]);
                            case -2:
                                break;
                        }
                    }
                    //更新用户支付状态
                    if ($userinfo['ispurchase']=="false") {
                        $uppurchase['ispurchase'] = "true";
                        $up = UserModel::updateUserinfo($userinfo['id'],$uppurchase);
                    }
                    //项目特殊功能
                    switch ($thirdinfo['codeName']) {
                        case 'oppojfsc':
                            $customRule = json_decode($thirdinfo['custom_rule'],true);
                            //更新用户级别
                            foreach ($orderitem as $key => $value) {
                                if ($value['model_id']=="138") {
                                    $uplevel['user_level'] = "super";
                                    if (isset($customRule['deadline'])) {
                                        $deadline = (int)$customRule['deadline'];
                                    }else{
                                        $deadline = 0;
                                    }
                                    $uplevel['deadline'] = time()+$deadline;
                                    $upuser = UserModel::updateUserinfo($userinfo['id'],$uplevel);
                                    break;
                                }
                            }
                            //购物返积分
                            if ($thirdinfo['custom_rule']!='[]') {
                                if ($userinfo['user_level']=="super"&&$userinfo['deadline']>time()) {
                                    if (isset($customRule['superScore'])) {
                                        $scoreadd = $orderinfo['actual_price']*(float)$customRule['superScore'];
                                    }
                                }else{
                                    if (isset($customRule['score'])) {
                                        $scoreadd = $orderinfo['actual_price']*(float)$customRule['score'];
                                    }
                                }
                                $scoreadd = (int)$scoreadd;
                                if ($scoreadd>0) {
                                    $upscore['user_score'] = $userinfo['user_score']+$scoreadd;
                                    $upuserscore = UserModel::updateUserinfo($userinfo['id'],$upscore);
                                    //记录积分流水
                                    $scoreflow = array(
                                        'thirdapp_id'=>$userinfo['thirdapp_id'],
                                        'user_id'=>$userinfo['id'],
                                        'scoreAmount'=>$scoreadd,
                                        'order_id'=>$orderinfo['id'],
                                        'order_type'=>'product',
                                        'trade_type'=>1,
                                        'trade_info'=>'购买商品奖励积分',
                                        'create_time'=>time(),
                                    );
                                    $addflow = FlowScoreModel::addFlow($scoreflow);
                                }
                            }
                            break;
                        case 'zhqy':
                            //按订单商品金额给提交用户返余额
                            $HqyOrder = new HqyOrderService();
                            $HqyOrder->repay($orderinfo['id']);
                            break;
                        default:
                            //默认不执行操作
                            break;
                    }
                    Db::commit();
                    return true;
                }catch (Exception $ex){
                    Db::rollback();
                    //支付金额退回到用户余额
                    $update['user_balance'] = $userinfo['user_balance']+$orderinfo['actual_price'];
                    $updateUser = UserModel::updateUserinfo($userinfo['id'],$update);
                    //记录余额流水
                    $flowBalance = array(
                        'create_time' => time(),
                        'thirdapp_id'=>$orderinfo['thirdapp_id'],
                        'user_id'=>$orderinfo['user_id'],
                        'balanceAmount'=>$data['TOTAL_FEE']/100,
                        'order_id'=>$order['id'],
                        'order_type'=>'product',
                        'trade_type'=>1,
                        'trade_info'=>'支付失败，金额退回余额',
                        'status'=>1
                        );
                    $BalanceAdd = FlowBalanceModel::addFlow($flowBalance);
                    throw $ex;
                }
            }
            if ($orderinfo['product_type']=="reward") {
                //更新订单状态
                $orderUpt = Db::table('order')
                ->where('order_no',$orderNo)
                ->update([
                    'pay_status' => 1,
                    'transaction_id' => $data['TRANSACTION_ID'],
                ]);
                //添加流水信息
                $flowMoney = array('openid' =>$data['OPENID'],
                    'create_time'=>time(),
                    'moneyAmount'=>$data['TOTAL_FEE']/100,
                    'order_no'=>$orderNo,
                    'order_id'=>$orderinfo['id'],
                    'order_type'=>'reward',
                    'user_id'=>$orderinfo['user_id'],
                    'thirdapp_id'=>$orderinfo['thirdapp_id'],
                    'trade_type'=>1,
                    'app_type'=>1,
                    'status'=>1
                    );
                $MoneyAdd = FlowMoneyModel::addFlow($flowMoney);
                return true;
            }
            if ($orderinfo['product_type']=="card") {
                //更新订单状态
                $orderUpt = Db::table('order')
                ->where('order_no',$orderNo)
                ->update([
                    'pay_status' => 1,
                    'transaction_id' => $data['TRANSACTION_ID'],
                ]);
                //添加现金流水信息
                $flowMoney = array(
                    'openid' =>$data['OPENID'],
                    'create_time'=>time(),
                    'moneyAmount'=>$data['TOTAL_FEE']/100,
                    'order_no'=>$orderNo,
                    'order_id'=>$orderinfo['id'],
                    'order_type'=>'product',
                    'user_id'=>$orderinfo['user_id'],
                    'thirdapp_id'=>$orderinfo['thirdapp_id'],
                    'trade_type'=>1,
                    'app_type'=>1,
                    'status'=>1
                    );
                $MoneyAdd = FlowMoneyModel::addFlow($flowMoney);
                //使用余额并记录流水
                if ($orderinfo['balance_price']!=0) {
                    $updateuser['user_balance'] = $userinfo['user_balance']-$orderinfo['balance_price'];
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
                //记录card信息到user
                $map['order_id'] = $orderinfo['id'];
                $iteminfo = OrderItemModel::getItemByMap($map);
                $cardinfo = MemberCardModel::getCardInfo($iteminfo['model_id']);
                $usercard = array(
                    'user_id'     => $orderinfo['user_id'],
                    'card_id'     => $cardinfo['id'],
                    'pay_time'    => time(),
                    'deadline'    => $cardinfo['deadline']+time(),
                    'score'       => $cardinfo['scoreValue'],
                    'cardType'    => $cardinfo['cardType'],
                    'interests'    => $cardinfo['interests'],
                    'thirdapp_id' => $orderinfo['thirdapp_id'],
                    'card_status' => 0,
                    'status'      => 1
                );
                $addcard = UserCardModel::addCard($usercard);
                //判断是否需要执行分销逻辑
                $thirdinfo = ThirdAppModel::getThirdUserInfo($orderinfo['thirdapp_id']);
                if ($thirdinfo['distribution']=="true") 
                {
                    $distribution = new DistributionService();
                    $doDistribution = $distribution->OrderDistribution($orderinfo['id']);
                    switch ($doDistribution) {
                        case 1:
                            break;
                        case -1:
                            throw new TokenException([
                                'msg' => '订单未支付',
                                'solelyCode' => 209021
                            ]);
                        case -2:
                            break;
                    }
                }
                //更新用户支付状态
                if ($userinfo['ispurchase']=="false") {
                    $uppurchase['ispurchase'] = "true";
                    $up = UserModel::updateUserinfo($userinfo['id'],$uppurchase);
                }
                return true;
            }
        }else{
            //记录微信支付回调日志
            $log = array(
                'title'=>'微信支付',
                'result'=>$data['RESULT_CODE'],
                'content'=>$data['RETURN_MSG'],
                'create_time'=>time(),
            );
            $saveLog = WxpaylogModel::addLog($log);
        }
    }

    //xml格式转化成数组
    private function xml2array($xml){
        $p=xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $data = "";
        foreach($index as $key=>$value){
            if($key=='xml'||$key =='XML') continue;
            $tag=$vals[$value[0]]['tag'];
            $value=$vals[$value[0]]['value'];
            $data[$tag]=$value;
        }
        return $data;
    }
}