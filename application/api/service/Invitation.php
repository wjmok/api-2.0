<?php
namespace app\api\service;
use app\api\model\Invitation as InvitationModel;
use app\api\model\InvitationRemark as RemarkModel;
use app\api\model\InvitationFavor as FavorModel;
use think\Cache;

class Invitation{

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
        if (isset($data['invitation_status'])&&$data['invitation_status']==1) {
            $data['publish_time'] = time();
        }
        $userinfo = Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['user_id'] = 0;
        return $data;
    }

    public function formatImg($data)
    {
        if (isset($data['invitation_status'])&&$data['invitation_status']==1) {
            $data['publish_time'] = time();
        }
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
        //获取帖子信息
        $invitationinfo = InvitationModel::getInvitationInfo($id);
        if ($invitationinfo) {
            if ($invitationinfo['status']==-1) {
                return -2;
            }
            if ($userinfo['thirdapp_id']!=$invitationinfo['thirdapp_id']) {
                return -1;
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
            if ($list['data'][$key]['menu']) {
                $list['data'][$key]['menu']['mainImg']=json_decode($value['menu']['mainImg'],true);
                $list['data'][$key]['menu']['bannerImg']=json_decode($value['menu']['bannerImg'],true);
            }
            if ($list['data'][$key]['user']) {
                if ($list['data'][$key]['user_id']==0) {
                    unset($list['data'][$key]['user']);
                }else{
                    $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
                }
            }
            $map['map']['invitation_id'] = $value['id'];
            $remarklist = RemarkModel::getRemarkList($map);
            if ($remarklist) {
                $remarklist = $remarklist->toArray();
                $list['data'][$key]['remarkNum'] = count($remarklist);
            }else{
                $list['data'][$key]['remarkNum'] = 0;
            }
            $favorlist = FavorModel::getFavorList($map);
            if ($favorlist) {
                $favorlist = $favorlist->toArray();
                $list['data'][$key]['favorNum'] = count($favorlist);
            }else{
                $list['data'][$key]['favorNum'] = 0;
            }
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
            if ($list[$key]['menu']) {
                $list[$key]['menu']['mainImg']=json_decode($value['menu']['mainImg'],true);
                $list[$key]['menu']['bannerImg']=json_decode($value['menu']['bannerImg'],true);
            }
            if ($list[$key]['user']) {
                if ($list[$key]['user_id']==0) {
                    unset($list[$key]['user']);
                }else{
                    $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
                }
            }
            $map['map']['invitation_id'] = $value['id'];
            $remarklist = RemarkModel::getRemarkList($map);
            if ($remarklist) {
                $remarklist = $remarklist->toArray();
                $list[$key]['remarkNum'] = count($remarklist);
            }else{
                $list[$key]['remarkNum'] = 0;
            }
            $favorlist = FavorModel::getFavorList($map);
            if ($favorlist) {
                $favorlist = $favorlist->toArray();
                $list[$key]['favorNum'] = count($favorlist);
            }else{
                $list[$key]['favorNum'] = 0;
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg']=json_decode($info['mainImg'],true);
        $info['bannerImg']=json_decode($info['bannerImg'],true);
        if ($info['menu']) {
            $info['menu']['mainImg']=json_decode($info['menu']['mainImg'],true);
            $info['menu']['bannerImg']=json_decode($info['menu']['bannerImg'],true);
        }
        if ($info['user_id']==0) {
            unset($info['user']);
        }else{
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        $map['map']['invitation_id'] = $info['id'];
        $remarklist = RemarkModel::getRemarkList($map);
        if ($remarklist) {
            $remarklist = $remarklist->toArray();
            $info['remarkNum'] = count($remarklist);
        }else{
            $info['remarkNum'] = 0;
        }
        $favorlist = FavorModel::getFavorList($map);
        if ($favorlist) {
            $favorlist = $favorlist->toArray();
            $info['favorNum'] = count($favorlist);
        }else{
            $info['favorNum'] = 0;
        }
        return $info;
    }
}