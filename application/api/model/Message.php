<?php

namespace app\api\model;


use think\Model;

use app\lib\exception\ErrorMessage;

class Message extends BaseModel
{

    public static function dealAdd($data)
    {   
        
        $standard = ['title'=>'','content'=>'','class'=>'','score'=>'','mainImg'=>[],'relation_table'=>'','relation_id'=>'','type'=>'','user_no'=>'','thirdapp_id'=>'','create_time'=>time(),'delete_time'=>'','passage1'=>'','passage_array'=>[],'status'=>1,'product_no'=>'','order_no'=>''];

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