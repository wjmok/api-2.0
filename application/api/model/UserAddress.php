<?php

namespace app\api\model;


use think\Model;

use app\api\model\User;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

class UserAddress extends BaseModel
{

    public static function dealAdd($data)
    {   
        
        $standard = ['name'=>'','phone'=>'','province'=>'','city'=>'','country'=>'','detail'=>'','longitude'=>'','latitude'=>'','user_no'=>'','thirdapp_id'=>'','isdefault'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1];

        $data = chargeBlank($standard,$data);
        $res = User::get(['user_no' => $data['user_no']]);

        if($res){
        	return $data;
        }else{
        	throw new ErrorMessage([
                'msg' => '关联信息有误',
            ]);
    	}

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