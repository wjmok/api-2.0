<?php
namespace app\api\service;
use app\api\model\MemberCard as MemberCardModel;
use think\Cache;

class MemberCard{

    public function addinfo($data){
        $data['status']=1;
        $data['create_time']=time();
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
        if(isset($data['interests'])){
            $data['interests']=json_encode($data['interests']);
        }else{
            $data['interests']=json_encode([]);
        }
        $userinfo=Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        if(isset($data['bannerImg'])) {
            $data['bannerImg'] = initimg($data['bannerImg']);
        }
        if(isset($data['interests'])) {
            $data['interests'] = initimg($data['interests']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取会员卡信息
        $cardinfo = MemberCardModel::getCardInfo($id);
        if ($cardinfo) {
            if ($userinfo['thirdapp_id']!=$cardinfo['thirdapp_id']) {
                return -1;
            }
            if ($cardinfo['status']==-1) {
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
            $list['data'][$key]['interests']=json_decode($value['interests'],true);
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
            $list[$key]['interests']=json_decode($value['interests'],true);
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg'] = json_decode($info['mainImg'],true);
        $info['bannerImg'] = json_decode($info['bannerImg'],true);
        $info['interests'] = json_decode($info['interests'],true);
        return $info;
    }
}