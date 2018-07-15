<?php

namespace app\api\model;

use think\Model;

use app\api\model\Order;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

class OrderItem extends BaseModel
{

    public static function dealAdd($data)
    {   
        
        $standard = ['order_no'=>'','product_id'=>'','count'=>'','snap_product'=>[],'isremark'=>'','thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','passage1'=>'','passage2'=>'','passage_array'=>[],'status'=>1,'user_no'=>''];

        $data = chargeBlank($standard,$data);
        $res = Order::get(['order_no' => $data['order_no']]);
        if($res){
        	return $data;
        }else{
        	throw new ErrorMessage([
                'msg' => '关联信息有误',
            ]);
    	}
        
        
    }

    public static function dealGet($data)
    {   

        
        return $data;
        
    }

    public static function dealUpdate($data)
    {   

    	return $data;
        
    }

    public static function dealRealDelete($data)
    {   

    	return $data;
        
    }


}