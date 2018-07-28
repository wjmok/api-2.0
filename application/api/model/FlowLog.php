<?php

namespace app\api\model;


use think\Model;


class FlowLog extends Model
{

    public static function dealAdd($data)
    {   
        
        $standard = ['type'=>'','count'=>'','relation_id'=>'','create_time'=>time(),'delete_time'=>'','status'=>1,'trade_info'=>'','relation_table'=>'','thirdapp_id'=>'','product_no'=>'','order_no'=>'','user_no'=>''];
        $data = chargeBlank($standard,$data);
        return $data;

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