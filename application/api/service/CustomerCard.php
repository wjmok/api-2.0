<?php
namespace app\api\service;
use app\api\model\UserCard as UserCardModel;
use think\Cache;

class CustomerCard{

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取会员卡信息
        $cardinfo = UserCardModel::getCardInfo($id);
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
            if ($list['data'][$key]['card']) {
                $list['data'][$key]['card']['mainImg']=json_decode($value['card']['mainImg'],true);
                $list['data'][$key]['card']['bannerImg']=json_decode($value['card']['bannerImg'],true);
            }
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            if ($list[$key]['card']) {
                $list[$key]['card']['mainImg']=json_decode($value['card']['mainImg'],true);
                $list[$key]['card']['bannerImg']=json_decode($value['card']['bannerImg'],true);
            }
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        if ($info['card']) {
            $info['card']['mainImg'] = json_decode($info['card']['mainImg'],true);
            $info['card']['bannerImg'] = json_decode($info['card']['bannerImg'],true);
        }
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }
}