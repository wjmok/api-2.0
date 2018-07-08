<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/27
 * Time: 15:22
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\service\CustomerCoupon as CouponService;
use think\Request as Request; 
use think\Cache;

/**
 * 后端用户优惠券模块
 */
class CustomerCoupon extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editCoupon,delCoupon,getInfo']
    ];

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
                    'solelyCode' => 220003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 220002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '用户优惠券不存在',
                    'solelyCode' => 220000
                ]);
        }
        $res = UserCouponModel::updateCoupon($data['id'],$data);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改优惠券成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '更新优惠券信息失败',
                'solelyCode' => 220004
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
                    'solelyCode' => 220003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 220002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '用户优惠券不存在',
                    'solelyCode' => 220000
                ]);
        }
        $res = UserCouponModel::delCoupon($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除优惠券成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除优惠券失败',
                'solelyCode' => 220005
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
                'msg' => '获取用户优惠券列表失败',
                'solelyCode' => 220006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
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
                    'msg' => '优惠券不属于本商户',
                    'solelyCode' => 220003
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