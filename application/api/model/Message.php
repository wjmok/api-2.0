<?php

namespace app\api\model;


use think\Model;
use think\Loader;
use app\lib\exception\ErrorMessage;

class Message extends BaseModel
{

    public static function dealAdd($data)
    {   
        
        $standard = ['title'=>'','content'=>'','class'=>'','score'=>'','mainImg'=>[],'relation_table'=>'','relation_id'=>'','type'=>'','user_no'=>'','thirdapp_id'=>'','create_time'=>time(),'delete_time'=>'','passage1'=>'','passage_array'=>[],'status'=>1];

        $data = chargeBlank($standard,$data);

        if(isset($data['relation_table'])&&isset($data['relation_id'])){
        	$model =Loader::model($data['relation_table']);
	        $map['id'] = $data['relation_id'];
	        $res = $model->where($map)->find();
	        if(!$res){
	        	throw new ErrorMessage([
	                'msg' => '关联信息有误',
	            ]);
	        }
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