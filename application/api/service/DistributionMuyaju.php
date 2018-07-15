<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * 沐雅居项目分销逻辑
 * Date: 2018/4/11
 * Time: 19:46
 */

namespace app\api\service;
use app\api\model\User as UserModel;
use app\api\model\Member as MemberModel;
use app\api\model\FlowBalance as BalanceModel;

class DistributionMuyaju{
    
    public static function payReturn($orderinfo,$thirdinfo)
    {
    	//解析分销逻辑-一级返现比例
    	$distributionRule = json_decode($thirdinfo['distributionRule'],true);
    	$retio = $distributionRule['repay']['one']/100;
    	$moneycount = $orderinfo['actual_price']*$retio;
    	//保留两位小数
    	$moneycount = sprintf("%.2f", $moneycount);
    	//再次检验订单是否付款
    	if ($orderinfo['pay_status']!=1) {
    		return -1;
    	};
    	$userinfo = UserModel::getUserInfo($orderinfo['user_id']);
    	$memberinfo = MemberModel::getMemberInfoByUser($orderinfo['user_id']);
    	//判断是否有父级并执行分销逻辑
    	if ($memberinfo['parent_id']==0) {
    		return -2;
    	};
    	$pmember = MemberModel::getMemberInfo($memberinfo['parent_id']);
    	$puser = $pmember['user'];
    	//记录累计佣金
    	$upmember['rebateMoney'] = $pmember['rebateMoney']+$moneycount;
    	$updatemember = MemberModel::updateMember($pmember['id'],$upmember);
    	//返现到余额
    	$upuser['user_balance'] = $puser['user_balance']+$moneycount;
    	$updateuser = UserModel::updateUserinfo($pmember['user_id'],$upuser);
    	//记录余额流水
    	$data = array(
    		'thirdapp_id'  =>$userinfo['thirdapp_id'],
    		'user_id'      =>$pmember['user_id'],
    		'balanceAmount'=>$moneycount,
    		'order_id'     =>$orderinfo['id'],
    		'order_type'   =>"rebate",
    		'trade_type'   =>1,
    		'trade_info'   =>"ID为".$memberinfo['id']."下级消费返利",
    		'create_time'  =>time(),
    	);
    	$saveflow = BalanceModel::addFlow($data);
    	return 1;
    }
}