<?php
namespace app\api\service;
use app\api\model\UserCard as UserCardModel;
use app\api\model\MemberCard as MemberCardModel;
use app\api\model\User as UserModel;
use app\api\service\Token as TokenService;

class UserCard{

    public function addinfo($data){
        $data['status'] = 1;
        $data['pay_time'] = time();
        $userinfo = TokenService::getUserinfo($data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['user_id'] = $userinfo['id'];
        $cardinfo = MemberCardModel::getCardInfo($data['card_id']);
        $data['cardType'] = $cardinfo['cardType'];
        $data['score'] = $cardinfo['scoreValue'];
        if ($cardinfo['deadline']!=0) {
            $data['deadline'] = $cardinfo['deadline']+time();
        }
        return $data;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {   
        $totalscore = 0;
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            if ($list['data'][$key]['card']) {
                $list['data'][$key]['card']['mainImg']=json_decode($value['card']['mainImg'],true);
                $list['data'][$key]['card']['bannerImg']=json_decode($value['card']['bannerImg'],true);
            }
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
            if ($list['data'][$key]['cardType']==1) {
                $totalscore += $list['data'][$key]['score'];
            }
        }
        $list['totalscore'] = $totalscore;
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list = $list->toArray();
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
            $info['card']['mainImg']=json_decode($info['card']['mainImg'],true);
            $info['card']['bannerImg']=json_decode($info['card']['bannerImg'],true);
        }
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }

    //检查用户全部会员卡是否失效
    public function checkdeadline($userinfo)
    {   
        $info['map']['user_id'] = $userinfo['id'];
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $list = UserCardModel::getCardList($info);
        $list = $list->toArray();
        foreach ($list as $value) {
            //确定该会员卡是否有有效期
            if ($value['deadline']!=0) {
                $isvalidity = $value['deadline'] - time();
                //判断有效期是否小于当时的时间戳
                if ($isvalidity<=0) {
                    $upinfo['card_status'] = -1;
                    $update = UserCardModel::updateCard($value['id'],$upinfo);
                }
            }
            //判断分值卡是否还有余分
            if ($value['cardType']==1&&$value['score']==0) {
                $upinfo['card_status'] = 1;
                $update = UserCardModel::updateCard($value['id'],$upinfo);
            }
        }
    }

    //检查卡是否有效
    public function checkcard($id,$uid)
    {
        $userinfo = UserModel::getUserInfo($uid);
        $cardinfo = UserCardModel::getCardInfo($id);
        if ($cardinfo) {
            if ($cardinfo['status']==-1) {
                return -2;
            }
            if ($cardinfo['card_status']!==0) {
                return -3;
            }
            if ($cardinfo['deadline']<time()) {
                $upinfo['card_status'] = -1;
                $update = UserCardModel::updateCard($id,$upinfo);
                return -3;
            }
        }else{
            return -1;
        }
    }
}