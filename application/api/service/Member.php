<?php
namespace app\api\service;
use app\api\model\Member as MemberModel;
use think\Cache;

class Member{

    public function addinfo($data){
        $data['status']=1;
        $data['create_time']=time();
        if(isset($data['rebateRule'])){
            $data['rebateRule']=json_encode($data['rebateRule']);
        }else{
            $data['rebateRule']=json_encode([]);
        }
        $data['QRcode']=json_encode([]);
        $userinfo=Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['rebateRule'])){
            $data['rebateRule'] = initimg($data['rebateRule']);
        }
        if(isset($data['QRcode'])){
            $data['QRcode'] = initimg($data['QRcode']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取会员卡信息
        $memberinfo = MemberModel::getMemberInfo($id);
        if ($memberinfo) {
            if ($userinfo['thirdapp_id']!=$memberinfo['thirdapp_id']) {
                return -1;
            }
            if ($memberinfo['status']==-1) {
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
            $list['data'][$key]['rebateRule']=json_decode($value['rebateRule'],true);
            $list['data'][$key]['QRcode']=json_decode($value['QRcode'],true);
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
            $list[$key]['rebateRule']=json_decode($value['rebateRule'],true);
            $list[$key]['QRcode']=json_decode($value['QRcode'],true);
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['rebateRule'] = json_decode($info['rebateRule'],true);
        $info['QRcode'] = json_decode($info['QRcode'],true);
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }
}