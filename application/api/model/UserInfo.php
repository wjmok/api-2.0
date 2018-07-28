<?php

namespace app\api\model;


use think\Model;

use app\api\model\User;
use app\api\model\FlowLog;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

class UserInfo extends Model
{

    public static function dealAdd($data)
    {   
        
        $standard = ['mainImg'=>[],'name'=>'','phone'=>'','gender'=>'','email'=>'','city'=>'','level'=>'','deadline'=>'','passage_array'=>[],'passage2'=>'','signin_time'=>'','thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','user_no'=>''];

        $data = chargeBlank($standard,$data);
        $res = User::get(['user_no' => $data['user_no']]);

        if($res){
        	$res = UserInfo::get(['user_no' => $data['user_no']]);
            if($res){
                throw new ErrorMessage([
                    'msg' => '已经存在UserInfo',
                ]); 
            };
        }else{
        	throw new ErrorMessage([
                'msg' => '关联信息有误',
            ]);
    	};
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