<?php

namespace app\api\model;


use think\Model;
use app\api\model\Label;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

class Product extends BaseModel
{

    public static function dealAdd($data)
    {   
        $standard = ['title'=>'','category_id'=>'','mainImg'=>'','bannerImg'=>'','content'=>[],'passage1'=>[],'passage2'=>'','passage3'=>'','passage4'=>'','view_count'=>'','onShelf'=>'','isgroup'=>'','listorder'=>[],'thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'description'=>'','parentid'=>'','price'=>'','type'=>1,'user_no'=>'','discount'=>''];

        $data = chargeBlank($standard,$data);
        $res = Label::get(['id' => $data['category_id']]);
        if(!$res){
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