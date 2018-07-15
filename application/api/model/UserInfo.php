<?php

namespace app\api\model;


use think\Model;

use app\api\model\User;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

class UserInfo extends Model
{

    public static function dealBlank($data)
    {   
        
        $standard = ['mainImg'=>[],'name'=>'','phone'=>'','gender'=>'','email'=>'','city'=>'','score'=>'','balance'=>'','level'=>'','deadline'=>'','passage_array'=>[],'passage2'=>'','signin_time'=>'','thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','user_no'=>''];

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