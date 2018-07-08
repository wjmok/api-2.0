<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * 果行育德项目分销逻辑
 * Date: 2018/5/1
 * Time: 21:14
 */

namespace app\api\service;
use app\api\model\User as UserModel;
use app\api\model\Member as MemberModel;
use app\api\model\FlowScore as ScoreModel;

class DistributionGuoxingyd{
    
    public static function subscribe($userinfo,$thirdinfo)
    {
    	//解析分销逻辑
    	$distributionRule = json_decode($thirdinfo['distributionRule'],true);
    	$memberinfo = MemberModel::getMemberInfoByUser($userinfo['id']);
    	//判断是否有父级并执行分销逻辑
    	if ($memberinfo['parent_id']==0) {
    		return -2;
    	}
    	$pmember = MemberModel::getMemberInfo($memberinfo['parent_id']);
    	$puser = $pmember['user'];
    	//增加积分
    	$upuser['user_score'] = $puser['user_score']+$distributionRule['subscribe'];
    	$updateuser = UserModel::updateUserinfo($puser['id'],$upuser);
    	//记录积分流水
    	$data = array(
    		'thirdapp_id' =>$puser['thirdapp_id'],
    		'user_id'     =>$puser['id'],
    		'scoreAmount' =>$distributionRule['subscribe'],
    		'order_id'    =>0,
    		'order_type'  =>"subscribe",
    		'trade_type'  =>1,
    		'trade_info'  =>"ID为".$memberinfo['id']."好友关注获得积分",
    		'create_time' =>time(),
    	);
    	$saveflow = ScoreModel::addFlow($data);
    	return 1;
    }

    public static function completeInfo($userinfo,$thirdinfo)
    {   
        //判断是否是第一次补全信息
        if ($userinfo['compinfo']=="true") {
            return -1;
        }
        //解析分销逻辑
        $distributionRule = json_decode($thirdinfo['distributionRule'],true);
        //增加积分
        $upuser['user_score'] = $userinfo['user_score']+$distributionRule['info'];
        $upuser['compinfo'] = "true";
        $updateuser = UserModel::updateUserinfo($userinfo['id'],$upuser);
        //记录积分流水
        $data = array(
            'thirdapp_id' =>$userinfo['thirdapp_id'],
            'user_id'     =>$userinfo['id'],
            'scoreAmount' =>$distributionRule['info'],
            'order_id'    =>0,
            'order_type'  =>"info",
            'trade_type'  =>1,
            'trade_info'  =>"补充资料奖励",
            'create_time' =>time(),
        );
        $saveflow = ScoreModel::addFlow($data);
        return 1;
    }
}