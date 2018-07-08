<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018-3-6
 * Time: 10:29
 */

namespace app\api\service;

use app\api\model\Product as ProductModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\Order as OrderModel;
use app\api\model\UserAddress as AddressModel;
use app\api\model\User as UserModel;
use think\Db;
use think\Cache;
use think\Exception;
use app\lib\exception\OrderException;

class Order
{

    /**
     * @param $data
     * @return 整理数据
     */
    public function updateinfo($data)
    {
        if (!isset($data['id'])) {
           $data['status'] = -1;
           return $data;
        }
        //获取地址信息
        if (isset($data['orderinfo']['snap_address']))
        {
            $address = AddressModel::getAddressInfo($data['orderinfo']['snap_address']);
            $data['orderinfo']['snap_address'] = $address['name'].$address['moblie'].$address['province'].$address['city'].$address['country'].$address['detail'];
        }
        //获取订单信息
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (empty($orderinfo)) {
            $data['status'] = -2;
            return $data;
        }
        if ($orderinfo['status']==-1) {
            $data['status'] = -3;
            return $data;
        }
        //获取操作者信息
        $userinfo=Cache::get('info'.$data['token']);
        if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
           if ($userinfo['promary_scope']<40) {
               $data['status'] = -4;
               return $data;
           }
        }
        if (isset($data['orderinfo']['products'])) {
            //是否允许后台编辑客户已经下单的商品与数量信息？TODO...
            unset($data['orderinfo']['products']);
        }
        $data['status'] = 1;
        return $data;
    }

    public function delorder($data)
    {
        if (!isset($data['id'])) {
           $data['status'] = -1;
           return $data;
        }
        //获取订单信息
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (empty($orderinfo)) {
            $data['status'] = -2;
            return $data;
        }
        if ($orderinfo['status']==-1) {
            $data['status'] = -3;
            return $data;
        }
        //获取操作者信息
        $userinfo=Cache::get('info'.$data['token']);
        if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
           if ($userinfo['promary_scope']<40) {
               $data['status'] = -4;
               return $data;
           }
        }
        $data['status'] = 1;
        $data['update']['status'] = -1;
        $data['update']['delete_time'] = time();
        return $data;
    }

    //检查库存
    public function getOrderStatus($oProducts)
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
        //获取库存型号列表
        $products = ProductModelModel::getModelList($map);
        foreach ($oProducts as $oProduct) {
            $res = $this->getModelStatus($oProduct['model_id'],$oProduct['count'],$products);
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

    //检查单个型号是否满足库存
    private function getModelStatus($oPID,$oCount,$products)
    {
        $pIndex = -1;
        for ($i=0; $i < count($products); $i++) { 
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }
        if ($pIndex == -1) {
            //下单的产品型号不存在或已下架
            $res['status'] = -1;
            $res['id'] = $oPID;
            return $res;
        }else{
            $product = $products[$pIndex];
            if ($product['stock_num']-$oCount >= 0) {
                $res['status'] = 1;
                $res['count'] = $oCount;
                $res['totalPrice'] = $product['price'] * $oCount;
                return $res;
            }else{
                $res['status'] = -2;
                $res['id'] = $oPID;
                return $res;
            }
        }
    }

    //批量保存订单详情信息
    public function saveItem($id,$thirdapp_id,$itemlist,$product_type)
    {
        foreach ($itemlist as $key=>$value) {
            $modelinfo = ProductModelModel::getModelById($value['model_id']);
            $itemlist[$key]['order_id'] = $id;
            $itemlist[$key]['model_id'] = $value['model_id'];
            $itemlist[$key]['product_type'] = $product_type;
            $itemlist[$key]['count'] = $value['count'];
            $itemlist[$key]['passage1'] = isset($value['passage1'])?$value['passage1']:'';
            $itemlist[$key]['passage2'] = isset($value['passage2'])?$value['passage2']:'';
            $itemlist[$key]['snap_product'] = json_encode($modelinfo);
            $itemlist[$key]['is_remark'] = 0;
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

    //扣减库存
    private function reduceStock($id,$count)
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
}