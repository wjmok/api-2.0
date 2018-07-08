<?php
namespace app\api\service;
use app\api\model\InvitationFavor as FavorModel;
use app\api\model\Invitation as InvitationModel;
use app\api\service\Token as TokenService;

class UserInvitationFavor{

    public function addinfo($data){

        //判断是否已有点赞记录
        $userinfo = TokenService::getUserinfo($data['token']);
        $map['user_id'] = $userinfo['id'];
        $map['invitation_id'] = $data['invitation_id'];
        $favorinfo = FavorModel::getFavorInfoByMap($map);
        if ($favorinfo) {
            $data['id'] = $favorinfo['id'];
        }
        $data['favor_status'] = 1;
        $data['favor_time'] = time();
        $data['user_id'] = $userinfo['id'];
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        return $data;
    }

    public function cancelFavor($data)
    {
        //判断是否已有点赞记录
        $userinfo = TokenService::getUserinfo($data['token']);
        $map['user_id'] = $userinfo['id'];
        $map['invitation_id'] = $data['invitation_id'];
        $favorinfo = FavorModel::getFavorInfoByMap($map);
        if ($favorinfo) {
            $data['id'] = $favorinfo['id'];
        }
        $data['favor_status'] = 0;
        $data['cancel_time'] = time();
        return $data;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
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
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
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
        return $info;
    }

    //获取指定用户帖子的获赞记录
    public function getRelationByUser($token)
    {
        $userinfo = TokenService::getUserinfo($token);
        $map['map']['user_id'] = $userinfo['id'];
        $invitationlist = InvitationModel::getInvitationList($map);
        $invitationIDs = array();
        foreach ($invitationlist as $item) {
            array_push($invitationIDs, $item['id']);
        }
        return array('in',$invitationIDs);
    }
}