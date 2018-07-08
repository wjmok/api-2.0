<?php

namespace app\api\model;


use think\Model;

class FlowLog extends BaseModel
{

    public static function dealBlank($data)
    {   
        
        $standard = ['type'=>'','count'=>'','relation_id'=>'','create_time'=>time(),'delete_time'=>'','status'=>1,'trade_info'=>'','relation'=>[],'thirdapp_id'=>'','user_id'=>''];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}