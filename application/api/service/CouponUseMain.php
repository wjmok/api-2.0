<?php
/**
 * Created by 董博明.
 * Author: 董博明
 * Date: 2018/4/4
 * Time: 18:51
 */

namespace app\api\service;

use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\Coupon as CouponModel;
use app\api\service\Token as TokenService;
use app\lib\exception\TokenException;

$couponList = [];

//优惠券逻辑主函数
class CouponUseMain
{

    protected $total_price;
    protected $token;

    public function construct($total_price,$list,$token)
    {
        $this->total_price = $total_price;
        $GLOBALS['couponList'] = $list;
        $this->token = $token;
    }


    //检查优惠券
    public function check()
    {
        $userinfo = TokenService::getUserinfo($this->token);
        foreach ($GLOBALS['couponList'] as $item) {
            if ($item['user_id']!=$userinfo['id']) {
                return -1;
            }
            if ($item['deadline']<time()) {
                return -2;
            }
            if ($item['coupon']['status']==-1) {
                return -3;
            }
        }
        return 1;
    }

    //计算通用单张优惠券折扣
    public function getPriceByOne()
    {
        $actual_price = $this->total_price;
        foreach ($GLOBALS['couponList'] as $item) {
            if ($item['discount']!=100) {
                $actual_price = $this->total_price*$item['discount']/100;
            }else{
                $actual_price = $this->total_price-$item['deduction'];
                if ($actual_price<=0) {
                    throw new TokenException([
                        'msg' => '优惠券优惠金额大于订单金额',
                        'solelyCode' => 220011
                    ]);
                }
            }
        }
        $price['actual_price'] = $actual_price;
        $price['coupon_price'] = $this->total_price-$actual_price;
        return $price;
    }
}