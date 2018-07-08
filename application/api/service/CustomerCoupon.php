<?php
namespace app\api\service;
use app\api\model\UserCoupon as UserCouponModel;
use think\Cache;

class CustomerCoupon{

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取优惠券信息
        $couponinfo = UserCouponModel::getCouponInfo($id);
        if ($couponinfo) {
            if ($userinfo['thirdapp_id']!=$couponinfo['thirdapp_id']) {
                return -1;
            }
            if ($couponinfo['status']==-1) {
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
            $list['data'][$key]['discount'] = $list['data'][$key]['discount']/10;
            if ($list['data'][$key]['coupon']) {
                $list['data'][$key]['coupon']['mainImg']=json_decode($value['coupon']['mainImg'],true);
                $list['data'][$key]['coupon']['bannerImg']=json_decode($value['coupon']['bannerImg'],true);
                $list['data'][$key]['coupon']['discount'] = $list['data'][$key]['coupon']['discount']/10;
            }
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list = $list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['coupon']['discount'] = $list[$key]['coupon']['discount']/10;
            if ($list[$key]['coupon']) {
                $list[$key]['coupon']['mainImg']=json_decode($value['coupon']['mainImg'],true);
                $list[$key]['coupon']['bannerImg']=json_decode($value['coupon']['bannerImg'],true);
                $list[$key]['coupon']['discount'] = $list[$key]['coupon']['discount']/10;
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
        $info['discount'] = $info['discount']/10;
        if ($info['coupon']) {
            $info['coupon']['mainImg'] = json_decode($info['coupon']['mainImg'],true);
            $info['coupon']['bannerImg'] = json_decode($info['coupon']['bannerImg'],true);
            $info['coupon']['discount'] = $info['coupon']['discount']/10;
        }
        if ($info['user']) {
            $info['user']['headimgurl']=json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }
}