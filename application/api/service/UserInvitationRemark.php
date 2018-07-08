<?php
namespace app\api\service;
use app\api\model\InvitationRemark as RemarkModel;
use app\api\model\Invitation as InvitationModel;
use app\api\service\Token as TokenService;

class UserInvitationRemark{

    public function addinfo($data){
        $data['status'] = 1;
        $data['create_time'] = time();
        if(isset($data['mainImg'])){
            $data['mainImg']=json_encode($data['mainImg']);
        }else{
            $data['mainImg']=json_encode([]);
        }
        $userinfo = TokenService::getUserinfo($data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['user_id'] = $userinfo['id'];
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = TokenService::getUserinfo($token);
        //获取帖子回复信息
        $remarkinfo = RemarkModel::getRemarkInfo($id);
        if ($remarkinfo) {
            if ($remarkinfo['status']==-1) {
                return -2;
            }
            if ($userinfo['thirdapp_id']!=$remarkinfo['thirdapp_id']) {
                return -1;
            }
            if ($userinfo['id']!=$remarkinfo['user_id']) {
                return -4;
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
            if ($list['data'][$key]['user']) {
                if ($list['data'][$key]['user_id']==0) {
                    unset($list['data'][$key]['user']);
                }else{
                    $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
                }
            }
            if ($list['data'][$key]['touser']) {
                if ($list['data'][$key]['to_user_id']==0) {
                    unset($list['data'][$key]['touser']);
                }else{
                    $list['data'][$key]['touser']['headimgurl']=json_decode($value['touser']['headimgurl'],true);
                }
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
            if ($list[$key]['user']) {
                if ($list[$key]['user_id']==0) {
                    unset($list[$key]['user']);
                }else{
                    $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
                }
            }
            if ($list[$key]['touser']) {
                if ($list[$key]['to_user_id']==0) {
                    unset($list[$key]['touser']);
                }else{
                    $list[$key]['touser']['headimgurl']=json_decode($value['touser']['headimgurl'],true);
                }
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg']=json_decode($info['mainImg'],true);
        if ($info['user_id']==0) {
            unset($info['user']);
        }else{
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        if ($info['to_user_id']==0) {
            unset($info['touser']);
        }else{
            $info['touser']['headimgurl']=json_decode($info['touser']['headimgurl'],true);
        }
        return $info;
    }

    //获取指定用户帖子的评论记录
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