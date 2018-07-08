<?php
namespace app\api\service;
use app\api\model\XgdBook as BookModel;
use app\api\model\Remark as RemarkModel;
use think\Cache;

class XgdBook{

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
        $data['QRcode']=json_encode([]);
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
        if(isset($data['QRcode'])){
            $data['QRcode'] = initimg($data['QRcode']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = Cache::get('info'.$token);
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
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg'] = json_decode($value['mainImg'],true);
            $list['data'][$key]['bannerImg'] = json_decode($value['bannerImg'],true);
            $list['data'][$key]['QRcode'] = json_decode($value['QRcode'],true);
            if ($list['data'][$key]['menu']) {
                $list['data'][$key]['menu']['mainImg'] = json_decode($value['menu']['mainImg'],true);
                $list['data'][$key]['menu']['bannerImg'] = json_decode($value['menu']['bannerImg'],true);
            }
            if ($list['data'][$key]['campus']) {
                $list['data'][$key]['campus']['mainImg'] = json_decode($value['campus']['mainImg'],true);
                $list['data'][$key]['campus']['bannerImg'] = json_decode($value['campus']['bannerImg'],true);
            }
            //加载评分
            $map['map']['book_id'] = $value['id'];
            $remarklist = RemarkModel::getRemarkList($map);
            $totalscore = 0;
            foreach ($remarklist as $key => $value) {
                $totalscore += $value['remarkScore'];
            }
            if ($totalscore!=0) {
                $averagescore = $totalscore/count($remarklist);
                $list['data'][$key]['remarkScore'] = round($averagescore,1);
            }else{
                $list['data'][$key]['remarkScore'] = 0;
            }
            
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list = $list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg'] = json_decode($value['mainImg'],true);
            $list[$key]['bannerImg'] = json_decode($value['bannerImg'],true);
            $list[$key]['QRcode'] = json_decode($value['QRcode'],true);
            if ($list[$key]['menu']) {
                $list[$key]['menu']['mainImg'] = json_decode($value['menu']['mainImg'],true);
                $list[$key]['menu']['bannerImg'] = json_decode($value['menu']['bannerImg'],true);
            }
            if ($list[$key]['campus']) {
                $list[$key]['campus']['mainImg'] = json_decode($value['campus']['mainImg'],true);
                $list[$key]['campus']['bannerImg'] = json_decode($value['campus']['bannerImg'],true);
            }
            //加载评分
            $map['map']['book_id'] = $value['id'];
            $remarklist = RemarkModel::getRemarkList($map);
            $totalscore = 0;
            foreach ($remarklist as $key => $value) {
                $totalscore += $value['remarkScore'];
            }
            if ($totalscore!=0) {
                $averagescore = $totalscore/count($remarklist);
                $list[$key]['remarkScore'] = round($averagescore,1);
            }else{
                $list[$key]['remarkScore'] = 0;
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg'] = json_decode($info['mainImg'],true);
        $info['bannerImg'] = json_decode($info['bannerImg'],true);
        $info['QRcode'] = json_decode($info['QRcode'],true);
        if ($info['menu']) {
            $info['menu']['mainImg'] = json_decode($info['menu']['mainImg'],true);
            $info['menu']['bannerImg'] = json_decode($info['menu']['bannerImg'],true);
        }
        if ($info['campus']) {
            $info['campus']['mainImg'] = json_decode($info['campus']['mainImg'],true);
            $info['campus']['bannerImg'] = json_decode($info['campus']['bannerImg'],true);
        }
        //加载评分
        $map['map']['book_id'] = $info['id'];
        $remarklist = RemarkModel::getRemarkList($map);
        $totalscore = 0;
        foreach ($remarklist as $key => $value) {
            $totalscore += $value['remarkScore'];
        }
        if ($totalscore!=0) {
            $averagescore = $totalscore/count($remarklist);
            $info['remarkScore'] = round($averagescore,1);
        }else{
            $info['remarkScore'] = 0;
        }
        return $info;
    }
}