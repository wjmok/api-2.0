<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/27
 * Time: 15:00
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\Coupon as CouponModel;
use app\api\service\UserCoupon as CouponService;
use think\Request as Request;
use app\api\service\Token as TokenService;

/**
 * 前端用户优惠券模块
 */
class UserCoupon extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'getInfo'],
        'checkToken' => ['only' => 'receiveCoupon,getList,getInfo']
    ];

    public function receiveCoupon(){
        $data = Request::instance()->param();
        if (!isset($data['coupon_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数CouponID',
                'solelyCode'=>220008
            ]);
        }
        //获取优惠券信息
        $couponinfo = CouponModel::getCouponInfo($data['coupon_id']);
        if (empty($couponinfo)) {
            throw new TokenException([
                'msg' => '优惠券不存在',
                'solelyCode'=>219000
            ]);
        }else{
            if ($couponinfo['status']==-1) {
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode'=>219002
                ]);
            }
            if ($couponinfo['deadline']<=time()) {
                throw new TokenException([
                    'msg' => '优惠券已过期',
                    'solelyCode'=>219007
                ]);
            }
        }
        //检查是否重复领取
        $userinfo = TokenService::getUserinfo($data['token']);
        $map['coupon_id'] = $data['coupon_id'];
        $map['user_id'] = $userinfo['id'];
        $check = UserCouponModel::getCouponInfoByMap($map);
        if ($check) {
            throw new TokenException([
                'msg' => '已领取过该优惠券',
                'solelyCode'=>220010
            ]);
        }
        $couponservice = new CouponService();
        $info = $couponservice->addinfo($data);
        $res = UserCouponModel::addCoupon($info);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'领取成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '领取优惠券失败',
                'solelyCode'=>220001
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $couponservice = new CouponService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        //检查更新过期优惠券
        $check = $couponservice->checkdeadline($userinfo);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = UserCouponModel::getCouponListToPaginate($info);
            $list = $couponservice->formatPaginateList($list);
        }else{
            $list = UserCouponModel::getCouponList($info);
            $list = $couponservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取优惠券列表失败',
                'solelyCode' => 220006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $res = UserCouponModel::getCouponInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 220002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该优惠券不属于本商户',
                    'solelyCode' => 220003
                ]);
            }
            if ($res['user_id']!=$userinfo['id']) {
                throw new TokenException([
                    'msg' => '该优惠券不属于你',
                    'solelyCode' => 220007
                ]);
            }
            $couponservice = new CouponService();
            $res = $couponservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '用户优惠券不存在',
                'solelyCode' => 220000
            ]);
        }
    }
}