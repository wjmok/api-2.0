<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2017-11-24
 * Time: 19:34
 */

namespace app\api\controller\v1;

use app\api\controller\CommonController;
use app\api\model\Order as OrderModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\User as UserModel;
use app\api\model\FlowScore as FlowScoreModel;
use app\api\model\FlowBalance as FlowBalanceModel;
use app\api\service\UserOrder as OrderService;
use app\api\service\Token as TokenService;
use app\api\service\Pay as PayService;
use think\Controller;
use think\Request as Request;
use app\lib\exception\TokenException;
use app\lib\exception\UserException;
use app\lib\exception\OrderException;
use app\lib\exception\RemarkException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\AddressException;
use app\api\validate\OrderPlace;
use think\Db;
use think\Cache;

class Order extends CommonController
{

    protected $beforeActionList = [
        'checkToken' => ['only' => 'addOrder,getOrderinfo,receiveOrder,cancelOrder,delOrder,getReceiveOrder'],
        'checkID' => ['only' => 'getOrderinfo,receiveOrder,cancelOrder,delOrder']
    ];

    /**
     * 下订单
     * @author 董博明
     * @POST data
     * @time 2017.11.24
     */
    public function addOrder()
    {
        $data = Request::instance()->param();
        //公共引用
        $orderservice = new OrderService();
        $userinfo = TokenService::getUserinfo($data['token']);

        if (!isset($data['product_type'])) {
            throw new OrderException([
                'msg' => '缺少关键参数商品类型',
                'solelyCode' => 209014
            ]);
        }
        if ($data['product_type']=="product") {
            if (!isset($data['address_id'])) {
                throw new OrderException([
                    'msg' => '缺少地址信息参数ID',
                    'solelyCode' => 209013
                ]);
            }
            $validate = new OrderPlace();
            $validate->goCheck();
            $info = $orderservice->addProductOrder($data);
            switch ($info['status']) {
                case 1:
                    break;
                case -1:
                    throw new OrderException([
                        'msg' => '下单的商品型号'.$info['id'].'不存在或已下架',
                        'solelyCode' => 209001
                    ]);
                case -2:
                    throw new OrderException([
                        'msg' => '下单的商品型号'.$info['id'].'库存不足',
                        'solelyCode' => 209002
                    ]);
                case -3:
                    throw new AddressException();
                case -4:
                    throw new TokenException([
                        'msg' => '用户优惠券不存在',
                        'solelyCode' => 220000
                    ]);
                case -5:
                    throw new TokenException([
                        'msg' => '优惠券已过期',
                        'solelyCode' => 220009
                    ]);
                case -6:
                    throw new TokenException([
                        'msg' => '商家优惠券已删除',
                        'solelyCode' => 219002
                    ]);
                case -7:
                    throw new TokenException([
                        'msg' => '下单的商品型号'.$info['id'].'不属于本商家',
                        'solelyCode' => 211004
                    ]);
                case -8:
                    throw new TokenException([
                        'msg' => '下单的商品型号'.$info['id'].'未开启团购',
                        'solelyCode' => 211015
                    ]);
                case -9:
                    throw new TokenException([
                        'msg' => '未到开团时间',
                        'solelyCode' => 211017
                    ]);
                case -10:
                    throw new TokenException([
                        'msg' => '已参与此项团购',
                        'solelyCode' => 211018
                    ]);
                case -11:
                    throw new TokenException([
                        'msg' => '积分不足支付',
                        'solelyCode' => 209029
                    ]);
                default:
                    break;
            }
            // if (empty($userinfo['phone'])) {
            //     throw new UserException([
            //         'msg' => '用户未绑定手机',
            //         'solelyCode' => 210002
            //     ]);
            // }
            $info['user_id'] = $userinfo['id'];
            $info['thirdapp_id'] = $userinfo['thirdapp_id'];
            Db::startTrans();
            try{
                $orderID = OrderModel::addOrder($info);
                //优惠券核销-订单生成就使用了优惠券
                $orderinfo = OrderModel::getOrderInfo($orderID);
                if ($orderinfo['coupon_price']!=0) {
                    $couponlist = json_decode($orderinfo['couponList'],true);
                    foreach ($couponlist as $value) {
                        $updateinfo['coupon_status'] = 1;
                        $updateinfo['use_time'] = time();
                        $usecoupon = UserCouponModel::updateCoupon($value,$updateinfo);
                    }
                }
                //记录item
                $saveItem = $orderservice->saveItemlist($orderID,$userinfo['thirdapp_id'],$data['products'],$data['product_type']);
                switch ($saveItem['status']) {
                    case 1:
                        // 生成订单后给前端返回订单id，便于执行下一步操作
                        Db::commit();
                        //付款金额为0，不调起支付
                        if ($info['actual_price']>0){
                            if (isset($data['isscore'])&&$data['isscore']=="true") {
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
                            if (isset($data['solely_paytype'])) {
                                $pay = new PayService($orderID);
                                $topay = $pay->pay();
                                switch ($topay['status']) {
                                    case 1:
                                        if (is_array($topay['info'])) {
                                            $update['wx_prepay_info'] = json_encode($topay['info']);
                                            $update['invalid_time'] = time()+7000;;
                                            $uporder = OrderModel::updateinfo($orderID,$update);
                                        }
                                        return $topay['info'];
                                    case -1:
                                        throw new OrderException([
                                            'msg' => '下单的商品型号'.$topay['id'].'不存在或已下架',
                                            'solelyCode' => 209001
                                        ]);
                                    case -2:
                                        throw new OrderException([
                                            'msg' => '下单的商品型号'.$topay['id'].'库存不足',
                                            'solelyCode' => 209002
                                        ]);
                                    case -3:
                                        throw new ThirdappException();
                                    case -4:
                                        throw new ThirdappException([
                                            'msg' => '该商户信息被删除',
                                            'solelyCode' => 201003
                                        ]);
                                    case -5:
                                        throw new OrderException([
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
                                            'msg' => '下单的商品型号'.$topay['id'].'不属于本商家',
                                            'solelyCode' => 211004
                                        ]);
                                    case -10:
                                        throw new TokenException([
                                            'msg' => '下单的商品型号'.$topay['id'].'未开启团购',
                                            'solelyCode' => 211015
                                        ]);
                                }
                            }else{
                                return $orderID;
                            }
                        }else{
                            $update['pay_status'] = 1;
                            $uporder = OrderModel::updateinfo($orderID,$update);
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
                                    'order_id'=>$orderID,
                                    'order_type'=>'product',
                                    'trade_type'=>2,
                                    'trade_info'=>'购买商品',
                                    'create_time'=>time(),
                                );
                                $addflow = FlowBalanceModel::addFlow($balanceflow);
                                return ['msg'=>'余额支付成功','solely_code'=>100000];
                            }else{
                                throw new TokenException([
                                    'msg' => '余额支付失败',
                                    'solelyCode' => 209031
                                ]);
                            }
                        }
                    case 2:
                        throw new OrderException([
                            'msg' => '保存订单详情失败',
                            'solelyCode' => 209003
                        ]);
                        break;
                    case -1:
                        throw new OrderException([
                            'msg' => '下单的商品型号'.$saveItem['id'].'不存在或已下架',
                            'solelyCode' => 209001
                        ]);
                        break;
                    case -2:
                        throw new OrderException([
                            'msg' => '下单的商品型号'.$saveItem['id'].'库存不足',
                            'solelyCode' => 209002
                        ]);
                    case -3:
                        throw new OrderException([
                            'msg' => '商品型号'.$saveItem['id'].'扣除库存失败',
                            'solelyCode' => 209004
                        ]);
                    default:
                        break;
                }
            }catch (Exception $ex){
                Db::rollback();
                throw $ex;
            }
        }
        elseif ($data['product_type']=="reward") {
            $info = $orderservice->addRewardOrder($data);
            switch ($info['status']) {
                case 1:
                    break;
                case -1:
                    throw new OrderException([
                        'msg' => '打赏的对象不存在或已下架',
                        'solelyCode' => 209001
                    ]);
                case -2:
                    throw new OrderException([
                        'msg' => '打赏的对象已删除',
                        'solelyCode' => 211001
                    ]);
            }
            $info['user_id'] = $userinfo['id'];
            $info['thirdapp_id'] = $userinfo['thirdapp_id'];
            $res = OrderModel::addOrder($info);
            //记录item
            $saveitem = $orderservice->saveItem($res,$userinfo['thirdapp_id'],$info['productinfo'],$data['product_type']);
            if (isset($data['solely_paytype'])) {
                $pay = new PayService($res);
                $topay = $pay->pay();
                switch ($topay['status']) {
                    case 1:
                        return $topay['info'];
                    case -1:
                        throw new OrderException([
                            'msg' => '下单的商品型号'.$topay['id'].'不存在或已下架',
                            'solelyCode' => 209001
                        ]);
                    case -2:
                        throw new OrderException([
                            'msg' => '下单的商品型号'.$topay['id'].'库存不足',
                            'solelyCode' => 209002
                        ]);
                    case -3:
                        throw new ThirdappException();
                    case -4:
                        throw new ThirdappException([
                            'msg' => '该商户信息被删除',
                            'solelyCode' => 201003
                        ]);
                }
            }else{
                return $res;
            }
        }
        elseif ($data['product_type']=="card") {
            $info = $orderservice->addCardOrder($data);
            switch ($info['status']) {
                case 1:
                    break;
                case -1:
                    throw new TokenException([
                        'msg' => '会员卡不存在',
                        'solelyCode' => 215000
                    ]);
                case -2:
                    throw new TokenException([
                        'msg' => '会员卡已删除',
                        'solelyCode' => 215002
                    ]);
                case -3:
                    throw new TokenException([
                        'msg' => '订单参数错误，请检查后再下单',
                        'solelyCode' => 209024
                    ]);
                    break;
            }
            $info['user_id'] = $userinfo['id'];
            $info['thirdapp_id'] = $userinfo['thirdapp_id'];
            $res = OrderModel::addOrder($info);
            //优惠券核销-订单生成就使用了优惠券
            $orderinfo = OrderModel::getOrderInfo($res);
            if ($orderinfo['coupon_price']!=0) {
                $couponlist = json_decode($orderinfo['couponList'],true);
                foreach ($couponlist as $value) {
                    $updateinfo['coupon_status'] = 1;
                    $updateinfo['use_time'] = time();
                    $usecoupon = UserCouponModel::updateCoupon($value,$updateinfo);
                }
            }
            //记录item
            $saveitem = $orderservice->saveItem($res,$userinfo['thirdapp_id'],$info['productinfo'],$data['product_type']);
            if (isset($data['solely_paytype'])) {
                $pay = new PayService($res);
                $topay = $pay->pay();
                switch ($topay['status']) {
                    case 1:
                        $update['wx_prepay_info'] = json_encode($topay['info']);
                        $update['invalid_time'] = time()+7000;;
                        $uporder = OrderModel::updateinfo($res,$update);
                        return $topay['info'];
                    default:
                        break;
                }
            }else{
                return $res;
            }
        }
    }

    /**
     * 获取指定订单
     * @author 董博明
     * @post id
     * @time 2017.11.24
     */
    public function getOrderinfo()
    {
        $data = Request::instance()->param();
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (empty($orderinfo)) {
            throw new OrderException();
        }
        if ($orderinfo['status']==-1) {
            throw new OrderException([
                'msg' => '订单已删除',
                'solelyCode' => 209006
            ]);
        }
        $userinfo = TokenService::getUserinfo($data['token']);
        if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
            if ($userinfo['promary_scope']<40) {
                throw new OrderException([
                    'msg' => '您权限不足，不能执行该操作',
                    'solelyCode' => 209007
                ]);
            }
        }
        return $orderinfo;
    }

    public function receiveOrder()
    {
        $data = Request::instance()->param();
        // 验证token信息
        $tokenservice = new TokenService();
        $userinfo = TokenService::getUserinfo($data['token']);
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if ($orderinfo) {
            if ($orderinfo['status']==-1) {
                throw new OrderException([
                    'msg' => '订单已删除',
                    'solelyCode' => 209006
                ]);
            }
            if ($orderinfo['user_id']!=$userinfo['id']) {
                throw new OrderException([
                    'msg' => '该订单不属于你',
                    'solelyCode' => 209012
                ]);
            }
            if ($orderinfo['transport_status']==0) {
                throw new OrderException([
                    'msg' => '订单未发货',
                    'solelyCode' => 209027
                ]);
            }
            if ($orderinfo['transport_status']==2) {
                throw new OrderException([
                    'msg' => '订单已收货',
                    'solelyCode' => 209032
                ]);
            }
            if ($orderinfo['order_step']!=0) {
                throw new OrderException([
                    'msg' => '订单状态异常',
                    'solelyCode' => 209028
                ]);
            }
            if ($orderinfo['pay_status']==0) {
                if ($orderinfo['pay_type']!=2) {
                    throw new OrderException([
                        'msg' => '订单未支付',
                        'solelyCode' => 209021
                    ]);
                }
            }
        }else{
            throw new TokenException([
                'msg' => '订单不存在',
                'solelyCode' => 209000
            ]);
        }
        $upinfo['transport_status'] = 2;
        $uporder = OrderModel::updateinfo($data['id'],$upinfo);
        if ($uporder==1) {
            throw new SuccessMessage([
                'msg' => '收货成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '收货失败',
                'solelyCode' => 209026
            ]);
        }
    }

    /**
     * 取消指定订单
     * @author 董博明
     * @post id
     * @time 2017.11.24
     */
    public function cancelOrder()
    {
        $data = Request::instance()->param();
        // 验证token信息
        $tokenservice = new TokenService();
        $userid = $tokenservice->getCurrentUid($data['token']);
        if ($userid&&$userid!=0)
        {
            $id = $data['id'];
            $info['order_step'] = 1;
            if (isset($data['passage1'])) {
                $info['passage1'] = $data['passage1'];
            }
            if (isset($data['passage2'])) {
                $info['passage2'] = $data['passage2'];
            }
            $res = OrderModel::updateinfo($id,$info);
            if ($res==1) {
                throw new SuccessMessage([
                    'msg' => '申请成功'
                ]);
            }else{
                throw new OrderException([
                    'msg' => '申请撤销失败',
                    'solelyCode' => 209010
                ]);
            }
        }else{
            throw new TokenException();
        }
    }

    /**
     * 删除指定订单
     * @author 董博明
     * @post id
     * @time 2018.3.26
     */
    public function delOrder()
    {
        $data = Request::instance()->param();
        // 验证token信息
        $tokenservice = new TokenService();
        $userid = $tokenservice->getCurrentUid($data['token']);
        if ($userid&&$userid!=0)
        {
            $orderinfo = OrderModel::getOrderInfo($data['id']);
            if ($orderinfo) {
                if ($orderinfo['status']==-1) {
                    throw new OrderException([
                        'msg' => '订单已删除',
                        'solelyCode' => 209006
                    ]);
                }else{
                    if ($orderinfo['user_id']!=$userid) {
                        throw new OrderException([
                            'msg' => '该订单不属于你',
                            'solelyCode' => 209012
                        ]);
                    }
                    if ($orderinfo['pay_status']==1) {
                        throw new OrderException([
                            'msg' => '订单已支付，不能删除',
                            'solelyCode' => 209015
                        ]);
                    }
                    $res = OrderModel::delOrder($data['id']);
                    if ($res==1) {
                        throw new SuccessMessage([
                            'msg' => '删除成功'
                        ]);
                    }else{
                        throw new OrderException([
                            'msg' => '删除订单失败',
                            'solelyCode' => 209009
                        ]);
                    }
                }
            }else{
                throw new OrderException();
            }
        }else{
            throw new TokenException();
        }
    }

    /**
     * 获取用户订单列表
     * @author 董博明
     * @post token
     * @time 2017.11.29
     */
    public function getAllByUser()
    {
        $data = Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        // 验证token信息
        $tokenservice = new TokenService();
        $uid = $tokenservice->getCurrentUid($data['token']);
        if($uid&&$uid!=0)
        {
            //删除失效订单
            $this->delInvalidOrder($uid);
            $info['map']['user_id'] = $uid;
            if(isset($info['pagesize'])&&isset($info['currentPage'])){
                $list = OrderModel::getOrderListToPaginate($info);
            }else{
                $list = OrderModel::getOrderList($info);
            }
            return $list;
        }else{
            throw new TokenException();
        }
    }

    /**
     * 获取待收货订单列表
     * @author 董博明
     * @post token
     * 因待收货状态包含在线支付已支付和货到付款未支付，条件交叉，故由后端处理
     * @time 2018.3.27
     */
    public function getReceiveOrder()
    {
        $data = Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['user_id'] = $userinfo['id'];
        $list = OrderModel::getReceiveOrderList($info);
        return $list;
    }

    /**
     * 删除失效订单
     * 微信prepay_id过期
     */
    private function delInvalidOrder($uid)
    {   
        $info['map']['user_id'] = $uid;
        $info['map']['pay_status'] = 0;
        $info['map']['invalid_time'] = array('lt',time());
        $list = OrderModel::getOrderList($info);
        //删除失效订单
        foreach ($list as $k => $v) {
            if ($list[$k]['invalid_time']!=0) {
                $delorder = OrderModel::delOrder($v['id']);
            }
        }
    }
}