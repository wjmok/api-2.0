<?php
namespace app\api\service;
use app\api\model\Book as BookModel;
use think\Cache;

class Book{

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
        $data['book_stime'] = isset($data['book_stime'])?$data['book_stime']:0;
        $data['book_etime'] = isset($data['book_etime'])?$data['book_etime']:$data['start_time'];
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
        $userinfo=Cache::get('info'.$token);
        //获取预约信息
        $bookinfo = BookModel::getBookInfo($id);
        if ($bookinfo) {
            if ($userinfo['thirdapp_id']!=$bookinfo['thirdapp_id']) {
                return -1;
            }
            if ($bookinfo['status']==-1) {
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

    //格式化前端预约搜索参数
    public function initSearchTime($info)
    {
        if (isset($info['map']['book_stime'])) {
            $info['map']['book_stime'] = array('elt',$info['map']['book_stime']);
        }
        if (isset($info['map']['book_etime'])) {
            $info['map']['book_etime'] = array('egt',$info['map']['book_etime']);
        }
        if (isset($info['map']['start_time'])) {
            $info['map']['start_time'] = array('egt',$info['map']['start_time']);
        }
        if (isset($info['map']['end_time'])) {
            $info['map']['start_time'] = array('elt',$info['map']['end_time']);
        }
        return $info;
    }
}