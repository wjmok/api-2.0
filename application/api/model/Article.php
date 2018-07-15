<?php

namespace app\api\model;


use think\Model;
use think\Loader;
use app\api\model\Label;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;

class Article extends BaseModel
{

    public static function dealAdd($data)
    {   
        $standard = ['title'=>'','small_title'=>'','content'=>'','menu_id'=>'','mainImg'=>[],'bannerImg'=>[],'contactPhone'=>'','keywords'=>'','description'=>'','listorder'=>'','view_count'=>'','thirdapp_id'=>'','passage_array'=>[],'passage1'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'user_no'=>'','relation_table'=>'','relation_id'=>''];

        $data = chargeBlank($standard,$data);
        $res = Label::get(['id' => $data['menu_id']]);
        if(!$res){
        	throw new ErrorMessage([
                'msg' => '关联信息有误',
            ]);
    	};

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
