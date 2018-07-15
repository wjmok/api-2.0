<?php

namespace app\api\model;


use think\Model;
use app\api\model\OrderItem;
class Order extends BaseModel
{

    public static function dealAdd($data)
    {   
        $standard = ['order_no'=>'','pay'=>[],'price'=>'','snap_address'=>[],'express'=>[],'pay_status'=>'','product_type'=>'','prepay_id'=>'','wx_prepay_info'=>[],'order_step'=>0,'transport_status'=>0,'transaction_id'=>'','refund_no'=>'','isrefund'=>'','create_time'=>time(),'invalid_time'=>'','start_time'=>'','end_time'=>'','update_time'=>'','finish_time'=>'','delete_time'=>'','passage1'=>'','passage_array'=>[],'status'=>1,'thirdapp_id'=>1,'user_no'=>'',];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


    public static function dealGet($data)
    {   

        foreach ($data as $key => $value) {

            $res = resDeal($res);
            $data[$key]['products'] =  resDeal(OrderItem::get(['order_no' => $value['order_no']]));

        };
        
        return $data;
        
    }

    public static function dealUpdate($data)
    {   

    	$res = (new Order())->where($data['map'])->select();
        foreach ($res as $key => $value) {

        	$relationRes = (new OrderItem())->save(
        		['status'  => $data['map']['status']],
				['order_no' => $res[$key]['order_no']]
			);
        };
        
    }

    public static function dealRealDelete($data)
    {   

    	$res = (new Order())->where($data['map'])->select();
        foreach ($res as $key => $value) {
			OrderItem::destroy(['order_no' => $res[$key]['order_no']]);
        };
        
    }


}