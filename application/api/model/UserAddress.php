<?php

namespace app\api\model;


use think\Model;

class UserAddress extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['name'=>'','phone'=>'','province'=>'','city'=>'','country'=>'','detail'=>'','longitude'=>'','latitude'=>'','user_id'=>'','thirdapp_id'=>'','isdefault'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}