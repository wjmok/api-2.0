<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018-3-6
 * Time: 10:08
 */

namespace app\api\service;

use app\api\model\Product as ProductModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\Order as OrderModel;
use app\api\model\UserAddress as AddressModel;
use app\api\model\User as UserModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\MemberCard as MemberCardModel;
use app\api\model\Book as BookModel;
use app\api\model\ThirdAPP as ThirdAPPModel;
use app\api\model\Member as MemberModel;
use app\api\service\CouponUseMain as CouponUseMainService;
use app\api\service\Token as TokenService;
use app\api\service\MemberCard as MemberCardService;
use app\api\service\Book as BookService;
use think\Exception;
use app\lib\exception\TokenException;

class UserOrder
{
    //新增商品订单
    public function addProductOrder($data)
    {
        $userinfo = TokenService::getUserinfo($data['token']);
        if (isset($data['isgroup'])&&$data['isgroup']=="true") {
            if (count($data['products'])>1) {
                throw new TokenException([
                    'msg' => '暂不支持一次团购多种商品',
                    'solelyCode' => 209000
                ]);
            }
            $modelinfo = ProductModelModel::getModelById($data['products'][0]['model_id']);
            //团购价格和库存核算逻辑
            $itemmap['model_id'] = $data['products'][0]['model_id'];
            $itemmap['price_type'] = "normal";
            $itemmap['status'] = 1;
            $itemlist = OrderItemModel::getItemListByMap($itemmap);
            if ($itemlist) {
                $orderIDs = array();
                foreach ($itemlist as $item) {
                    array_push($orderIDs, $item['order_id']);
                }
                $ordermap['map']['id'] = array('in',$orderIDs);
                $ordermap['map']['start_time'] = $modelinfo['start_time'];
                $ordermap['map']['price_type'] = "group";
                $ordermap['map']['user_id'] = $userinfo['id'];
                $orderlist = OrderModel::getOrderList($ordermap);
                if ($orderlist) {
                    $res['status'] = -10;
                    return $res;
                }
            }
            $checkstock = $this->getGroupStatus($data['products'],$userinfo['thirdapp_id']);
            switch ($checkstock['status']) {
                case 1:
                    $res['total_price'] = $checkstock['orderPrice'];
                    $res['total_count'] = $checkstock['total_count'];
                    $res['start_time'] = $checkstock['start_time'];
                    $res['end_time'] = $checkstock['end_time'];
                    $res['status'] = 1;
                    break;
                default:
                    return $checkstock;
                    break;
            }
            $res['price_type'] = "group";
        }else{
            //检查库存
            $checkstock = $this->getOrderStatus($data['products'],$userinfo['thirdapp_id']);
            switch ($checkstock['status']) {
                case 1:
                    $res['total_price'] = $checkstock['orderPrice'];
                    $res['total_count'] = $checkstock['total_count'];
                    $res['status'] = 1;
                    break;
                default:
                    return $checkstock;
                    break;
            }
        }
        $res['order_no'] = $this->makeOrderNo();
        $res['sort_count'] = count($data['products']);
        if ($data['address_id']!=0) {
            $address=AddressModel::getAddressInfo($data['address_id']);
            if (!is_object($address)) {
                $res['status'] = -3;
                return $res;
            }
            $res['snap_address'] = json_encode(['name'=>$address['name'],'phone'=>$address['phone'],'province'=>$address['province'],'city'=>$address['city'],'country'=>$address['country'],'detail'=>$address['detail'],'passage1'=>$address['passage1'],'passage2'=>$address['passage2']]);
        }else{
            $res['snap_address'] = json_encode([]);
        }
        $res['remark_num'] = 0;
        //抓取数据库电话信息
        if (!empty($userinfo['phone'])&&!is_null($userinfo['phone'])) {
            $res['phone'] = $userinfo['phone'];
        }else{
            $res['phone'] = "0";
        }
        $res['pay_status'] = isset($data['pay_status'])?$data['pay_status']:0;
        $res['pay_method'] = isset($data['pay_method'])?$data['pay_method']:1;
        $res['pay_type'] = isset($data['pay_type'])?$data['pay_type']:1;
        $res['couponList'] = isset($data['couponList'])?json_encode($data['couponList']):json_encode([]);
        $res['passage1'] = isset($data['passage1'])?$data['passage1']:'';
        $res['passage2'] = isset($data['passage2'])?$data['passage2']:'';
        $res['product_type'] = $data['product_type'];
        $res['order_step'] = 0;
        $res['transport_status'] = 0;
        $res['create_time'] = time();
        //优惠券逻辑
        if (isset($data['couponList'])) {
            $userCouponList = array();
            foreach ($data['couponList'] as $item) {
                $coupon = UserCouponModel::getCouponInfo($item);
                array_push($userCouponList, $coupon);
            }
            $mainservice = new CouponUseMainService();
            $init = $mainservice->construct($checkstock['orderPrice'],$userCouponList,$data['token']);
            //校验优惠券
            $check = $mainservice->check();
            switch ($check) {
                case 1:
                    break;
                case -1:
                    $checkres['status'] = -4;
                    return $checkres;
                case -2:
                    $checkres['status'] = -5;
                    return $checkres;
                case -3:
                    $checkres['status'] = -6;
                    return $checkres;
            }
            $couponprice = $mainservice->getPriceByOne();
            $res['actual_price'] = $couponprice['actual_price'];
            $res['coupon_price'] = $couponprice['coupon_price'];
        }else{
            $res['actual_price'] = $checkstock['orderPrice'];
        }
        //项目特殊功能
        $thirdinfo = ThirdAPPModel::getThirdUserInfo($userinfo['thirdapp_id']);
        $memberinfo = MemberModel::getMemberInfo($userinfo['id']);
        switch ($thirdinfo['codeName']) {
            case 'themaze':
                //判断用户是否享有9折优惠
                if ($memberinfo['rebateSwitch']=="on") {
                    $res['actual_price'] = $res['actual_price']*0.9;
                }
                break;
            case 'oppojfsc':
                //超级会员享有折扣
                if (isset($userinfo['user_level'])&&$userinfo['user_level']=="super") {
                    if ($thirdinfo['custom_rule']!="[]") {
                        $customRule = json_decode($thirdinfo['custom_rule'],true);
                        if (isset($customRule['discount'])) {
                            $res['actual_price'] = $res['actual_price']*(int)$customRule['discount']/100;
                        }
                    }
                }
                break;
            default:
                //默认不执行操作
                break;
        }
        //余额逻辑
        if (isset($data['usebalance'])&&$data['usebalance']=="true") {
            $checkbalance = $userinfo['user_balance']-$res['actual_price'];
            if ($checkbalance>=0) {
                $res['balance_price'] = $res['actual_price'];
                $res['actual_price'] = 0;
            }else{
                $res['balance_price'] = $userinfo['user_balance'];
                $res['actual_price'] = $res['actual_price']-$userinfo['user_balance'];
            }
        }
        if (isset($data['isscore'])&&$data['isscore']=="true") {
            if ($userinfo['user_score']-$res['actual_price']<0) {
                $res['status'] = -11;
                return $res;
            }
            $res['price_type'] = "score";
        }else{
            $res['price_type'] = "normal";
        }
        return $res;
    }

    //新增打赏订单
    public function addRewardOrder($data)
    {
        $modelinfo = ProductModelModel::getModelById($data['productinfo']['id']);
        if ($modelinfo) {
            if ($modelinfo['status']==-1) {
                $data['status'] = -2;
            }
            $data['total_price'] = $data['productinfo']['count'];
            $data['actual_price'] = $data['productinfo']['count'];
            $data['order_no'] = $this->makeOrderNo();
            $data['sort_count'] = 1;
            $data['snap_address'] = json_encode([]);
            $data['remark_num'] = 0;
            $data['pay_status'] = 0;
            $data['pay_method'] = 1;
            $data['pay_type'] = 1;
            $data['couponList'] = json_encode([]);
            $data['order_step'] = 0;
            $data['transport_status'] = 2;
            $data['create_time'] = time();
            $data['status'] = 1;
        }else{
            $data['status'] = -1;
        }
        return $data;
    }

    //购买会员卡
    public function addCardOrder($data)
    {
        if (!isset($data['productinfo']['id'])||!isset($data['productinfo']['count'])) {
            $data['status'] = -3;
        }else{
            $cardinfo = MemberCardModel::getCardInfo($data['productinfo']['id']);
            if ($cardinfo) {
                if ($cardinfo['status']==-1) {
                    $data['status'] = -2;
                }
                $data['total_price'] = $data['productinfo']['count'];
                $data['actual_price'] = $data['productinfo']['count'];
                $data['order_no'] = $this->makeOrderNo();
                $data['sort_count'] = 1;
                $data['snap_address'] = json_encode([]);
                $data['remark_num'] = 0;
                $data['pay_status'] = 0;
                $data['pay_method'] = 1;
                $data['pay_type'] = 1;
                $data['couponList'] = json_encode([]);
                $data['order_step'] = 0;
                $data['transport_status'] = 2;
                $data['create_time'] = time();
                $data['status'] = 1;
            }else{
                $data['status'] = -1;
            }
        }
        return $data;
    }

    //检查绑定手机信息
    public function checkphone($uid)
    {
        $phone = UserModel::getUserInfo($uid)->value('phone');
        if (!empty($phone)){
            return 1;
        }else{
            return 0;
        }
    }

    public function decodesnap($list)
    {
        foreach ($list as $k => $v) {
            $list[$k]['snap_item'] = json_decode($v['snap_item']);
        }
        return $list;
    }

    //生成订单号
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

    //检查库存
    public function getOrderStatus($oProducts,$thirdapp_id)
    {
        $status = [
            "status"=>0,
            "orderPrice"=>0,
            "total_count"=>0,
        ];
        $oPIDs = array();
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['model_id']);
        }
        $map['id'] = array('in',$oPIDs);
        $map['status'] = 1;
        $data['map'] = $map;
        //获取库存型号列表
        $products = ProductModelModel::getModelList($data);
        foreach ($products as $value) {
            if ($value['thirdapp_id']!=$thirdapp_id) {
                $res['status'] = -7;
                $res['id'] = $value['id'];
                return $res;
            }
        }
        foreach ($oProducts as $oProduct) {
            $res = $this->getModelStatus($oProduct,$products);
            switch ($res['status']) {
                case 1:
                    $status['orderPrice'] += $res['totalPrice'];
                    $status['total_count'] += $res['count'];
                    $status['status'] = 1;
                    break;
                case -1:
                    return $res;
                case -2:
                    return $res;
            }
        }
        return $status;
    }

    public function getGroupStatus($Products,$thirdapp_id)
    {
        $modelinfo = ProductModelModel::getModelById($Products[0]['model_id']);
        if ($modelinfo) {
            if ($modelinfo['status']==-1) {
                $res['status'] = -1;
                $res['id'] = $Products[0]['model_id'];
                return $res;
            }
            if ($modelinfo['isgroup']=="false") {
                $res['status'] = -8;
                $res['id'] = $Products[0]['model_id'];
                return $res;
            }
            if ($modelinfo['group_num']<$Products[0]['count']) {
                $res['status'] = -2;
                $res['id'] = $Products[0]['model_id'];
                return $res;
            }
            if ($modelinfo['thirdapp_id']!=$thirdapp_id) {
                $res['status'] = -7;
                return $res;
            }
            // if ($modelinfo['start_time']>time()) {
            //     $res['status'] = -9;
            //     return $res;
            // }
            $res['status'] = 1;
            $res['orderPrice'] = $modelinfo['groupPrice'];
            $res['start_time'] = $modelinfo['start_time'];
            $res['end_time'] = $modelinfo['end_time'];
            $res['total_count'] = $Products[0]['count'];
            return $res;
        }else{
            $res['status'] = -1;
            $res['id'] = $Products[0]['model_id'];
            return $res;
        }
    }

    //检查单个型号是否满足库存
    private function getModelStatus($oProduct,$products)
    {
        $pIndex = -1;
        for ($i=0; $i < count($products); $i++) { 
            if ($oProduct['model_id'] == $products[$i]['id']) {
                $pIndex = $i;
            }
        }
        if ($pIndex == -1) {
            //下单的产品型号不存在或已下架
            $res['status'] = -1;
            $res['id'] = $oProduct['model_id'];
            return $res;
        }else{
            $product = $products[$pIndex];
            if ($product['stock_num']-$oProduct['count'] >= 0) {
                $res['status'] = 1;
                $res['count'] = $oProduct['count'];
                if (isset($oProduct['price_type'])&&$oProduct['price_type']=="instalment") {
                    $res['totalPrice'] = $product['instalment'] * $oProduct['count'];
                }else{
                    $res['totalPrice'] = $product['price'] * $oProduct['count'];
                }
                return $res;
            }else{
                $res['status'] = -2;
                $res['id'] = $oProduct['model_id'];
                return $res;
            }
        }
    }

    //批量保存订单详情信息
    public function saveItemlist($id,$thirdapp_id,$itemlist,$product_type)
    {
        foreach ($itemlist as $key=>$value) {
            $modelinfo = ProductModelModel::getModelById($value['model_id']);
            $itemlist[$key]['order_id'] = $id;
            $itemlist[$key]['model_id'] = $value['model_id'];
            $itemlist[$key]['product_type'] = $product_type;
            $itemlist[$key]['price_type'] = isset($value['price_type'])?$value['price_type']:'normal';
            $itemlist[$key]['count'] = $value['count'];
            $itemlist[$key]['passage1'] = isset($value['passage1'])?$value['passage1']:'';
            $itemlist[$key]['passage2'] = isset($value['passage2'])?$value['passage2']:'';
            $itemlist[$key]['passage3'] = isset($value['passage3'])?$value['passage3']:'';
            $itemlist[$key]['snap_product'] = json_encode($modelinfo);
            $itemlist[$key]['isremark'] = 0;
            $itemlist[$key]['thirdapp_id'] = $thirdapp_id;
            $itemlist[$key]['create_time'] = time();
            $itemlist[$key]['status'] = 1;
        }
        $res = OrderItemModel::saveItemList($itemlist);
        if (is_array($res)) {
            $info['status'] = 1;
        }else{
            $info['status'] = 2;
        }
        return $info;
    }

    //保存订单详情信息
    public function saveItem($id,$thirdapp_id,$productinfo,$product_type)
    {
        switch ($product_type) {
            case 'card':
                $cardinfo = MemberCardModel::getCardInfo($productinfo['id']);
                $membercardservice = new MemberCardService();
                $cardinfo = $membercardservice->formatInfo($cardinfo);
                $snapinfo = json_encode($cardinfo);
                break;
            case 'book':
                $bookinfo = BookModel::getBookInfo($productinfo['id']);
                $bookservice = new BookService();
                $bookinfo = $bookservice->formatInfo($bookinfo);
                $snapinfo = json_encode($bookinfo);
                break;
            case 'reward':
                $modelinfo = ProductModelModel::getModelById($productinfo['id']);
                $snapinfo = json_encode($snapinfo);
                break;
            default:
                //默认关联型号表，如reward打赏
                $modelinfo = ProductModelModel::getModelById($productinfo['id']);
                $snapinfo = json_encode($snapinfo);
                break;
        }
        $iteminfo = array(
            'order_id'=>$id,
            'model_id'=>$productinfo['id'],
            'product_type'=>$product_type,
            'price_type'=>isset($productinfo['price_type'])?$productinfo['price_type']:'normal',
            'count'=>$productinfo['count'],
            'passage1'=>isset($productinfo['passage1'])?$productinfo['passage1']:'',
            'passage2'=>isset($productinfo['passage2'])?$productinfo['passage2']:'',
            'snap_product'=>$snapinfo,
            'isremark'=>0,
            'thirdapp_id'=>$thirdapp_id,
            'create_time'=>time(),
            'status'=>1
        );
        $res = OrderItemModel::addItem($iteminfo);
    }

    //扣减库存
    public function reduceStock($id,$count)
    {
        $modelinfo = ProductModelModel::getModelById($id);
        if (!$modelinfo) {
            $res['status'] = -1;
            $res['id'] = $id;
            return $res;
        }
        if ($modelinfo['stock_num']-$count>=0) {
            $updateinfo['stock_num'] = $modelinfo['stock_num']-$count;
            $updateinfo['sales_num'] = $modelinfo['sales_num']+$count;
            $update = ProductModelModel::updateProductModel($id,$updateinfo);
            if ($update==1) {
                $res['status'] = 1;
            }else{
                $res['status'] = -3;
                $res['id'] = $id;
            }
            return $res;
        }else{
            $res['status'] = -2;
            $res['id'] = $id;
            return $res;
        }
    }

    //扣减团库存
    public function reduceGroupStock($id,$count)
    {
        $modelinfo = ProductModelModel::getModelById($id);
        if (!$modelinfo) {
            $res['status'] = -1;
            $res['id'] = $id;
            return $res;
        }
        if ($modelinfo['group_num']-$count>=0) {
            $updateinfo['group_num'] = $modelinfo['group_num']-$count;
            $updateinfo['group_sales_num'] = $modelinfo['group_sales_num']+$count;
            $update = ProductModelModel::updateProductModel($id,$updateinfo);
            if ($update==1) {
                $res['status'] = 1;
            }else{
                $res['status'] = -3;
                $res['id'] = $id;
            }
            return $res;
        }else{
            $res['status'] = -2;
            $res['id'] = $id;
            return $res;
        }
    }

    //通过orderID获取订单item
    public function getOrderItem($id)
    {
        $map['order_id'] = $id;
        $map['status'] = 1;
        $itemlist = OrderItemModel::getItemListByMap($map);
        return $itemlist;
    }
}