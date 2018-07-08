<?php
namespace app\api\service;
use app\api\model\Merchant as MerchantModel;
use think\Cache;

//商家模块
class Merchant{

    public static function checkIsRepeat($data){
        $merchant = new MerchantModel();
        if(isset($data['name'])){
            $name = $merchant->where('name',$data['name'])->find();
            if(!empty($name)){
                return -1;//名称重复
            }else{
                return 1;
            }
        }   
    }

    public function addinfo($data){
        $data['status'] = 1;
        $data['create_time'] = time();
        if(isset($data['mainImg'])){
            $data['mainImg']=json_encode($data['mainImg']);
        }else{
            $data['mainImg']=json_encode([]);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg']=json_encode($data['bannerImg']);
        }else{
            $data['bannerImg']=json_encode([]);
        }
        $userinfo = Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg'] = initimg($data['bannerImg']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = Cache::get('info'.$token);
        //获取预约信息
        $merchantinfo = MerchantModel::getMerchantInfo($id);
        if ($merchantinfo) {
            if ($userinfo['thirdapp_id']!=$merchantinfo['thirdapp_id']) {
                return -1;
            }
            if ($merchantinfo['status']==-1) {
                return -2;
            }
        }else{
            return -3;
        }
        return 1;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
            $list['data'][$key]['bannerImg']=json_decode($value['bannerImg'],true);
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg'] = json_decode($info['mainImg'],true);
        $info['bannerImg'] = json_decode($info['bannerImg'],true);
        return $info;
    }
}