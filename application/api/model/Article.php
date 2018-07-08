<?php

namespace app\api\model;


use think\Model;

class Article extends BaseModel
{

    public static function dealBlank($data)
    {   
        $standard = ['name'=>'','small_name'=>'','content'=>'','menu_id'=>'','mainImg'=>[],'bannerImg'=>[],'contactPhone'=>'','keywords'=>'','description'=>'','listorder'=>'','view_count'=>'','thirdapp_id'=>'','passage_array'=>[],'passage1'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'user_id'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}
