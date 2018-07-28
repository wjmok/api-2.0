<?php

namespace app\api\model;


use think\Model;


class Image extends Model
{

    public static function dealAdd($data)
    {   
        
        $standard = ['title'=>'','path'=>'','thirdapp_id'=>'','size'=>0,'create_time'=>time(),'type'=>1,'user_no'=>'','relation_table'=>'','relation'=>'','status'=>1,'relation_id'=>'','prefix'=>''];
        
        $data = chargeBlank($standard,$data);
        /*if(isset($data['relation_table'])&&isset($data['relation_id'])){
            $model =Loader::model($data['relation_table']);
            $map['id'] = $data['relation_id'];
            $res = $model->where($map)->find();
            if(!$res){
                throw new ErrorMessage([
                    'msg' => '关联信息有误',
                ]);
            }
        };*/
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