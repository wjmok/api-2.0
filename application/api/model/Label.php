<?php

namespace app\api\model;


use think\Model;

class Label extends BaseModel
{

    public static function dealBlank($data)
    {   
        $standard = ['name'=>'','description'=>'','parentid'=>'','listorder'=>'','type'=>'','bannerImg'=>[],'preid'=>'','preparentid'=>'','thirdapp_id'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','status'=>1,'mainImg'=>[]];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}
