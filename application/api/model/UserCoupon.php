<?php
namespace app\api\model;
use think\Model;

class UserCoupon extends BaseModel{

    //关联coupon表
    public function coupon()
    {
        return $this->hasOne('Coupon', 'id', 'coupon_id');
    }

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //领取优惠券
    public static function addCoupon($data){
        $coupon = new UserCoupon();
        $res = $coupon->allowField(true)->save($data);
        return $res;
    }

    //更新优惠券
    public static function updateCoupon($id,$data){
        $coupon = new UserCoupon();
        $res = $coupon->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除优惠券
    public static function delCoupon($id){
        $coupon = new UserCoupon();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $coupon->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取优惠券列表
    public static function getCouponList($data){
        $coupon = new UserCoupon();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $coupon->where($map)->order('receive_time','desc')->with('coupon')->with('user')->select();
        return $list;
    }
    
    //获取优惠券列表（带分页）
    public static function getCouponListToPaginate($data){
        $coupon = new UserCoupon();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $coupon->where($map)->order('receive_time','desc')->with('coupon')->with('user')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定优惠券信息
    public static function getCouponInfo($id){
        $coupon = new UserCoupon();
        $res = $coupon->where('id','=',$id)->with('coupon')->with('user')->find();
        return $res;
    }

    //获取指定优惠券信息
    public static function getCouponInfoByMap($map){
        $coupon = new UserCoupon();
        $res = $coupon->where($map)->with('coupon')->with('user')->find();
        return $res;
    }
}