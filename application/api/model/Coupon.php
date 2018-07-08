<?php

namespace app\api\model;


use think\Model;

class Coupon extends BaseModel
{

    public static function dealBlank($data)
    {   
        $standard = ['name'=>'','description'=>'','content'=>'','mainImg'=>[],'bannerImg'=>[],'deadline'=>'','discount'=>100,'deduction'=>'','passage_array'=>[],'passage2'=>'','passage3'=>'','passage4'=>'','thirdapp_id'=>'','listorder'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'type'=>'','user_id'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}