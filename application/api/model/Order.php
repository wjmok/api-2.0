<?php

namespace app\api\model;


use think\Model;

class Order extends BaseModel
{

    public static function dealBlank($data)
    {   
        $standard = ['order_no'=>'','user_id'=>'','price'=>'','total_count'=>'','snap_address'=>[],'express'=>[],'prepay_id'=>'','remark_num'=>'','remark_status'=>'','pay_status'=>'','pay_type'=>'','pay'=>[],'order_step'=>0,'transport_status'=>0,'transaction_id'=>'','refund_no'=>'','isrefund'=>'','thirdapp_id'=>1,'create_time'=>time(),'invalid_time'=>'','wx_prepay_info'=>'','start_time'=>'','end_time'=>'','update_time'=>'','finish_time'=>'','delete_time'=>'','passage_array'=>[],'passage2'=>'','status'=>1];

        $data = chargeBlank($standard,$data);

        return $data;
        
    }


}