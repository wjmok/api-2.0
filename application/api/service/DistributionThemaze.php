<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * theMaze项目分销逻辑
 * Date: 2018/4/27
 * Time: 17:01
 */

namespace app\api\service;
use app\api\model\User as UserModel;
use app\api\model\Member as MemberModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\MemberCard as MemberCardModel;
use app\api\model\Coupon as CouponModel;

class DistributionThemaze{
    
    public static function payReturn($orderinfo,$thirdinfo)
    {
    	//解析分销逻辑-一级返现比例
    	$distributionRule = json_decode($thirdinfo['distributionRule'],true);
    	//再次检验订单是否付款
    	if ($orderinfo['pay_status']!=1) {
    		return -1;
    	}
    	$userinfo = UserModel::getUserInfo($orderinfo['user_id']);
    	$memberinfo = MemberModel::getMemberInfoByUser($orderinfo['user_id']);
    	//判断是否有父级并执行分销逻辑
    	if ($memberinfo['parent_id']==0) {
    		return -2;
    	}
    	$pmember = MemberModel::getMemberInfo($memberinfo['parent_id']);
    	$puser = $pmember['user'];
    	
        $map['order_id'] = $orderinfo['id'];
        $iteminfo = OrderItemModel::getItemByMap($map);
        $cardinfo = MemberCardModel::getCardInfo($iteminfo['model_id']);
        $couponinfo = CouponModel::getCouponInfo($cardinfo['passage1']);
        if (empty($couponinfo)) {
            return -3;
        }else{
            //优惠券已删除
            if ($couponinfo['status']==-1) {
                return -4;
            }
            //记录父级coupon
            $usercoupon = array(
                'user_id'      => $puser['id'],
                'coupon_id'    => $couponinfo['id'],
                'receive_time' => time(),
                'deadline'     => $couponinfo['deadline']+time(),
                'use_time'     => 0,
                'delete_time'  => 0,
                'discount'     => $couponinfo['discount'],
                'deduction'    => $couponinfo['deduction'],
                'passage1'     => $couponinfo['passage1'],
                'passage2'     => $couponinfo['passage2'],
                'passage3'     => $couponinfo['passage3'],
                'passage4'     => $couponinfo['passage4'],
                'thirdapp_id'  => $puser['thirdapp_id'],
                'coupon_status'=> 0,
                'status'       => 1
            );
            $res = UserCouponModel::addCoupon($usercoupon);
            //开启个人优惠
            $update['rebateSwitch'] = "on";
            $upuser = MemberModel::updateMember($memberinfo['id'],$update);
        }
    	return 1;
    }
}