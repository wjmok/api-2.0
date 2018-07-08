<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/26
 * Time: 22:14
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\Coupon as CouponModel;
use app\api\service\Coupon as CouponService;
use think\Request as Request;
use think\Cache;

/**
 * 后端优惠券模块
 */
class Coupon extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editCoupon,delCoupon,getInfo']
    ];

    public function addCoupon(){
        $data = Request::instance()->param();
        $couponservice = new CouponService();
        $info = $couponservice->addinfo($data);
        $res = CouponModel::addCoupon($info); 
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增优惠券失败',
                'solelyCode'=>219001
            ]);
        }
    }

    public function editCoupon(){
        $data = Request::instance()->param();
        $couponservice = new CouponService();
        $checkinfo = $couponservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该优惠券不属于本商户',
                    'solelyCode' => 219003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 219002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '优惠券不存在',
                    'solelyCode' => 219000
                ]);
        }
        $updateinfo = $couponservice->formatImg($data);
        $res = CouponModel::updateCoupon($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改优惠券成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改优惠券信息失败',
                'solelyCode' => 219004
            ]);
        }
    }

    public function delCoupon()
    {
        $data = Request::instance()->param();
        $couponservice = new CouponService();
        $checkinfo = $couponservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该优惠券不属于本商户',
                    'solelyCode' => 219003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 219002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '优惠券不存在',
                    'solelyCode' => 219000
                ]);
        }
        $res = CouponModel::delCoupon($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除预约成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除预约失败',
                'solelyCode' => 219005
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
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = CouponModel::getCouponListToPaginate($info);
            $list = $couponservice->formatPaginateList($list);
        }else{
            $list = CouponModel::getCouponList($info);
            $list = $couponservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取优惠券列表失败',
                'solelyCode' => 219006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = CouponModel::getCouponInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 219002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '优惠券不属于本商户',
                    'solelyCode' => 219003
                ]);
            }
            $couponservice = new CouponService();
            $res = $couponservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '优惠券不存在',
                'solelyCode' => 219000
            ]);
        }
    }
}