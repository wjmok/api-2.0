<?php
namespace app\api\service;
use app\api\model\BookOrder as OrderModel;
use think\Cache;

class BookOrder{

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取预约信息
        $orderinfo = OrderModel::getOrderInfo($id);
        if ($orderinfo) {
            if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
                return -1;
            }
            if ($orderinfo['status']==-1) {
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
            if ($list['data'][$key]['book']) {
                $list['data'][$key]['book']['mainImg']=json_decode($value['book']['mainImg'],true);
                $list['data'][$key]['book']['bannerImg']=json_decode($value['book']['bannerImg'],true);
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
            if ($list[$key]['book']) {
                $list[$key]['book']['mainImg']=json_decode($value['book']['mainImg'],true);
                $list[$key]['book']['bannerImg']=json_decode($value['book']['bannerImg'],true);
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
        if ($info['book']) {
            $info['book']['mainImg']=json_decode($info['book']['mainImg'],true);
            $info['book']['bannerImg']=json_decode($info['book']['bannerImg'],true);
        }
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }
}