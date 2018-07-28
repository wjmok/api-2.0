<?php

namespace app\api\model;


use think\Model;

use app\api\model\Label;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;



class Article extends Model
{

    public static function dealAdd($data)
    {   
        $standard = ['title'=>'','small_title'=>'','content'=>'','menu_id'=>'','mainImg'=>[],'bannerImg'=>[],'contactPhone'=>'','keywords'=>'','description'=>'','listorder'=>'','view_count'=>'','thirdapp_id'=>'','passage_array'=>[],'passage1'=>'','passage2'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'user_no'=>'','spu_array'=>[],'spu_item'=>[]];

        $data = chargeBlank($standard,$data);
        $res = Label::get(['id' => $data['menu_id']]);
        if(!$res){
        	throw new ErrorMessage([
                'msg' => '关联信息有误',
            ]);
    	};      
        return $data;
        
    }

    public static function dealGet($data)
    {   

        
        foreach ($data as $key => $value) {

            $label = array();
            array_push($label,$value['menu_id']);
            
            $label = array_merge($label,$value['spu_array'],$value['spu_item']);
            $map['id'] = ['in',$label];
            $res = resDeal((new Label())->where($map)->select());
            $res = clist_to_tree($res);
            $res = changeIndexArray('id',$res);
            $data[$key]['label'] = $res;

        };

        
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
