<?php

namespace app\api\model;


use think\Model;

class Log extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['title'=>'','result'=>'','content'=>'','create_time'=>time(),'user_id'=>'','status'=>1,'thirdapp_id'=>'','type'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}