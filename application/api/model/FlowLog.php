<?php

namespace app\api\model;


use think\Model;
use think\Loader;

class FlowLog extends BaseModel
{

    public static function dealAdd($data)
    {   
        
        $standard = ['type'=>'','count'=>'','relation_id'=>'','create_time'=>time(),'delete_time'=>'','status'=>1,'trade_info'=>'','relation_table'=>'','thirdapp_id'=>'','order_no'=>'','trade_type'=>'','user_no'=>''];

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