<?php
namespace app\api\controller\v1;
use app\api\model\Coupon as CouponModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use think\Exception;
use app\lib\exception\TokenException;
use app\api\service\Coupon as CouponService;

//前端优惠券模块
class CouponInfo extends CommonController{

    //获取优惠券列表
    public function getList(){

        $data = Request::instance()->param();
        $couponservice = new CouponService;
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = CouponModel::getCouponListToPaginate($info);
            $list = $couponservice->formatPaginateList($list);
        }else{
            $list = CouponModel::getCouponList($info);
            $list = $couponservice->formatList($list);
        }
        return $list;
    }

    //获取指定优惠券信息
    public function getInfo(){

        $data=Request::instance()->param();
        if (!isset($data['id'])) {
            throw new TokenException([
                'msg' => '缺少参数ID',
                'solelyCode' => 200001
            ]);
        }
        if (!isset($data['thirdapp_id'])) {
            throw new TokenException([
                'msg'=>'缺少参数ThirdID',
                'solelyCode' => 200002
            ]);
        }
        $res = CouponModel::getCouponInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '优惠券已删除',
                    'solelyCode' => 219002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该优惠券不属于本商户',
                    'solelyCode' => 219003
                ]);
            }
            $couponservice = new CouponService;
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