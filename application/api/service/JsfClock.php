<?php
namespace app\api\service;
use app\api\model\JsfClock as ClockModel;
use think\Cache;

class JsfClock{

    public function addinfo($data){
        $data['create_time'] = time();
        $userinfo = Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo->thirdapp_id;
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = Cache::get('info'.$token);
        $clcokinfo = ClockModel::getClockInfo($id);
        if ($clcokinfo) {
            if ($userinfo['thirdapp_id']!=$clcokinfo['thirdapp_id']) {
                return -1;
            }
            if ($clcokinfo['status']==-1) {
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
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
            if ($list['data'][$key]['parent']) {
                $list['data'][$key]['parent']['headimgurl']=json_decode($value['parent']['headimgurl'],true);
            }
            if ($list['data'][$key]['challenge']) {
                $list['data'][$key]['challenge']['mainImg']=json_decode($value['challenge']['mainImg'],true);
                $list['data'][$key]['challenge']['bannerImg']=json_decode($value['challenge']['bannerImg'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            if ($list['data']['user']) {
                $list['data']['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
            if ($list['data']['parent']) {
                $list['data']['parent']['headimgurl']=json_decode($value['parent']['headimgurl'],true);
            }
            if ($list['data']['challenge']) {
                $list['data']['challenge']['mainImg']=json_decode($value['challenge']['mainImg'],true);
                $list['data']['challenge']['bannerImg']=json_decode($value['challenge']['bannerImg'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        if ($info['parent']) {
            $info['parent']['headimgurl']=json_decode($info['parent']['headimgurl'],true);
        }
        if ($info['challenge']) {
            $info['challenge']['mainImg']=json_decode($info['challenge']['mainImg'],true);
            $info['challenge']['bannerImg']=json_decode($info['challenge']['bannerImg'],true);
        }
        return $info;
    }
}