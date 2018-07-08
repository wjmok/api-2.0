<?php

namespace app\api\model;


use think\Model;

class Image extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['name'=>'','path'=>'','thirdapp_id'=>'','size'=>0,'create_time'=>time(),'type'=>1,'user_id'=>'','relation'=>'','status'=>1,'relation_id'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}