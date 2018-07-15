<?php

namespace app\api\model;


use think\Model;

class Log extends BaseModel
{

    public static function dealAdd($data)
    {   
        
        $standard = ['title'=>'','result'=>'','content'=>'','create_time'=>time(),'user_id'=>'','status'=>1,'thirdapp_id'=>'','type'=>'','user_no'=>''];

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