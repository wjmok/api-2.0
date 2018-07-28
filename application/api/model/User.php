<?php

namespace app\api\model;
use think\Model;
use app\api\model\UserInfo;
use app\api\model\UserAddress;
use app\api\model\FlowLog;




class User extends Model{

    public static function dealAdd($data)
    {   
        
        $standard = ['login_name'=>'','password'=>'','wx_mainImg'=>'','create_time'=>time(),'update_time'=>'','delete_time'=>'','lastlogintime'=>'','thirdapp_id'=>'','primary_scope'=>'','scope'=>'','status'=>'','type'=>'','behavior'=>'','user_no'=>'','openid'=>'','parent_no'=>''];
        $data = chargeBlank($standard,$data);
        return $data;
        
    }

    public static function dealGet($data)
    {   

        
        $user_no = [];
        foreach ($data as $key => $value) {
            array_push($user_no,$value['user_no']);
        };

        

        $info = resDeal((new UserInfo())->where('user_no','in',$user_no)->select());
        

        
        $info['balance'] = 
        $address = resDeal((new UserAddress())->where('user_no','in',$user_no)->select());
        $info = changeIndexArray('user_no',$info);


        foreach ($data as $key => $value) {

            $balanceMap = array(
                'user_no'=>['in',$value['user_no']],
                'type'=>2
            );

            $scoreMap = array(
                'user_no'=>['in',$value['user_no']],
                'type'=>3
            );

            if(isset($info[$value['user_no']])){
                $data[$key]['info'] = $info[$value['user_no']];
                $data[$key]['info']['balance'] = (new FlowLog())->where($balanceMap)->sum('count');
                $data[$key]['info']['score'] = (new FlowLog())->where($scoreMap)->sum('count');
            }else{
                $data[$key]['address'] = [];
            };


            if(isset($address[$value['user_no']])){
                $data[$key]['address'] = $address[$value['user_no']];
            }else{
                $data[$key]['address'] = [];
            };


        };
        
        
        return $data;
        
    }

    public static function dealUpdate($data)
    {   

        
        if(isset($data['data'])&&isset($data['data']['status'])){
            $UserInfo = (new UserInfo())->where($data['searchItem'])->select();
            foreach ($UserInfo as $key => $value) {

                $relationRes = (new UserInfo())->save(
                    ['status'  => $data['searchItem']['status']],
                    ['order_no' => $UserInfo[$key]['order_no']]
                );
            };

            $UserAddress = (new UserAddress())->where($data['searchItem'])->select();
            foreach ($UserAddress as $key => $value) {

                $relationRes = (new UserAddress())->save(
                    ['status'  => $data['searchItem']['status']],
                    ['order_no' => $UserAddress[$key]['order_no']]
                );

            };
        };
        

    }

    public static function dealRealDelete($data)
    {   

        $UserInfo = (new UserInfo())->where($data['searchItem'])->select();
        foreach ($UserInfo as $key => $value) {
            OrderItem::destroy(['order_no' => $UserInfo[$key]['order_no']]);
        };

        $UserAddress = (new UserAddress())->where($data['searchItem'])->select();
        foreach ($UserAddress as $key => $value) {
            OrderItem::destroy(['order_no' => $UserAddress[$key]['order_no']]);
        };
        
    }


    
}
