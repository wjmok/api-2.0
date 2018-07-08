<?php
namespace app\api\service\func;
use app\api\model\Common as CommonModel;
use think\Exception;
use think\Model;
use think\Cache;

use app\api\service\base\Common as CommonService;


use app\api\validate\OrderValidate;

use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;


class Common{

 



    function __construct($data){
        
    }



    public static function addOrder($data){

        (new OrderValidate())->goCheck('addOrder',$data);
        checkTokenAndScope($data,0);

        $user = Cache::get($data['token']);
        if($user['type']>1){
            throw new ErrorMessage([
                'msg' => '用户类型不符',
            ]);
        };


        $totalPrice = 0;
        $type = 0;
        $order_no = makeOrderNo();
        foreach ($data['product'] as $key => $value) {
            self::checkAndReduceStock($value,$totalPrice,$type,$order_no,$user);
        };


        $difference = $totalPrice - self::checkAndReduceStock($data['pay']);
        if($difference>0.1||$difference<-0.1){
            throw new ErrorMessage([
                'msg' => '金额核算有误',
            ]);
        };




        $modelData = [];
        $modelData['order_no'] = $order_no;
        $modelData['product_type'] = $type;
        $modelData['price'] = $totalPrice;
        $modelData['pay'] = json_encode($data['pay']);
        $modelData['thirdapp_id'] = $user['thirdapp_id'];
        $modelData['user_no'] = $user['user_no'];
        $orderRes =  CommonModel::CommonSave('order',$modelData);

        

    }


    public static function checkAndReduceStock($data,$totalPrice,$type,$order_no,$user){
        $modelData = [];
        $modelData['searchItem']['id'] = $data['id'];
        $modelData['modelName'] = "product";
        $product =  CommonService::get($modelData,true);
        if($type==0){
            $type = $product['type'];
        }else{
            if($type!=$product['type']){
                throw new ErrorMessage([
                    'msg' => '产品类型不匹配',
                    'info'=>$product
                ]);
            }
        };
        if($product['stock']>$data['count']){

            $modelData = [];
            $modelData['map']['id'] = $product['id'];
            $modelData['map']['stock'] = $product['stock'];
            $modelData['data']['stock'] = $product['stock']-$data['count'];
            $res =  CommonModel::CommonSave('product',$modelData['data'],$modelData['map']);
            if(!$res>0){
                self::checkAndReduceStock($data);
            };

            $modelData = [];
            $modelData['order_no'] = $order_no;
            $modelData['product_id'] = $product['id'];
            $modelData['count'] = $data['count'];
            $modelData['snap_product'] = json_encode($product);
            $modelData['thirdapp_id'] = $user['thirdapp_id'];
            $modelData['user_no'] = $user['user_no'];
            $orderItemRes =  CommonModel::CommonSave('orderItem',$modelData);
            if(!$orderItemRes>0){
                throw new ErrorMessage([
                    'msg' => '写入产品失败',
                    'info'=>$product
                ]);  
            };
            $totalPrice += $product['price']; 
        }else{

            throw new ErrorMessage([
                'msg' => '库存不足',
                'info'=>$product
            ]);
        };

    }


    public static function computePrice($data){
        $price = $data['balance'] + $data['score'] + $data['wx_pay'];



        foreach ($data['coupon'] as $key => $value) {
            $modelData = [];
            $modelData['searchItem']['id'] = $value;
            $modelData['modelName'] = "product";
            $coupon =  CommonService::get($modelData,true);
            if($coupon['type'] == 3){
                $price += $coupon['discount'];
            }else if($coupon['type'] == 4){
                $price = $price*100/$coupon['discount'];
            };
        };
        
        return $price;

    }



}