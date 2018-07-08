<?php

namespace app\api\model;


use think\Model;

class Message extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['user_id'=>'','content'=>'','class'=>'','score'=>'','passage1'=>'','thirdapp_id'=>'','relation_id'=>'','type'=>'','create_time'=>time(),'delete_time'=>'','status'=>1,'mainImg'=>[],'passage_array'=>[],'passage2'=>'','behavior'=>'',];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}