<?php

namespace app\api\model;


use think\Model;

class Product extends BaseModel
{

    public static function dealBlank($data)
    {   
        $standard = ['name'=>'','category_id'=>'','mainImg'=>'','bannerImg'=>'','content'=>[],'passage1'=>[],'passage2'=>'','passage3'=>'','passage4'=>'','view_count'=>'','onShelf'=>'','isgroup'=>'','listorder'=>[],'thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'description'=>'','parentid'=>'','price'=>'','type'=>0,'user_id'=>'','discount'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}