<?php

namespace app\api\model;


use think\Model;

class UserInfo extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['mainImg'=>[],'name'=>'','phone'=>'','gender'=>'','email'=>'','city'=>'','score'=>'','balance'=>'','level'=>'','deadline'=>'','passage_array'=>[],'passage2'=>'','signin_time'=>'','thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}