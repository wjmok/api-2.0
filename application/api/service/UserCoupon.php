<?php
namespace app\api\service;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\Coupon as CouponModel;
use app\api\service\Token as TokenService;

class UserCoupon{

    public function addinfo($data){
        $data['status'] = 1;
        $data['coupon_status'] = 0;
        $data['receive_time'] = time();
        $userinfo = TokenService::getUserinfo($data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['user_id'] = $userinfo['id'];
        $couponinfo = CouponModel::getCouponInfo($data['coupon_id']);
        $data['discount'] = $couponinfo['discount'];
        $data['deduction'] = $couponinfo['deduction'];
        $data['passage1'] = $couponinfo['passage1'];
        $data['passage2'] = $couponinfo['passage2'];
        $data['passage3'] = $couponinfo['passage3'];
        $data['passage4'] = $couponinfo['passage4'];
        if ($couponinfo['deadline']!=0) {
            $data['deadline'] = $couponinfo['deadline'];
        }
        return $data;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['discount'] = $list['data'][$key]['discount']/10;
            if ($list['data'][$key]['coupon']) {
                $list['data'][$key]['coupon']['mainImg'] = json_decode($value['coupon']['mainImg'],true);
                $list['data'][$key]['coupon']['bannerImg'] = json_decode($value['coupon']['bannerImg'],true);
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
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['coupon']['discount'] = $list[$key]['coupon']['discount']/10;
            if ($list[$key]['coupon']) {
                $list[$key]['coupon']['mainImg'] = json_decode($value['coupon']['mainImg'],true);
                $list[$key]['coupon']['bannerImg'] = json_decode($value['coupon']['bannerImg'],true);
                $list[$key]['coupon']['discount'] = $list[$key]['coupon']['discount']/10;
            }
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['discount'] = $info['discount']/10;
        if ($info['coupon']) {
            $info['coupon']['discount'] = $info['coupon']['discount']/10;
            $info['coupon']['mainImg'] = json_decode($info['coupon']['mainImg'],true);
            $info['coupon']['bannerImg'] = json_decode($info['coupon']['bannerImg'],true);
        }
        if ($info['user']) {
            $info['user']['headimgurl'] = json_decode($info['user']['headimgurl'],true);
        }
        return $info;
    }

    //检查优惠券是否失效
    public function checkdeadline($userinfo)
    {   
        $info['map']['user_id'] = $userinfo['id'];
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $list = UserCouponModel::getCouponList($info);
        $list = $list->toArray();
        foreach ($list as $value) {
            //确定该优惠券是否有有效期
            if ($value['deadline']!=0) {
                $isvalidity = $value['deadline'] - time();
                //判断有效期是否小于当时的时间戳
                if ($isvalidity<=0) {
                    $upinfo['coupon_status'] = -1;
                    $update = UserCouponModel::updateCoupon($value['id'],$upinfo);
                }
            }
        }
    }
}