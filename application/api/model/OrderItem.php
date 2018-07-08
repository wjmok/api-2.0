<?php

namespace app\api\model;


use think\Model;

class OrderItem extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['order_id'=>'','product_id'=>'','count'=>'','snap_product'=>'','isremark'=>'','thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','passage1'=>'','passage2'=>'','passage_array'=>[],'status'=>1,];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}