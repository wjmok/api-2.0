<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/5/10
 * Time: 10:53
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\XgdBookOrder as XgdOrderModel;
use app\api\service\XgdBookOrder as XgdOrderService;
use think\Request as Request;
use think\Cache;

/**
 * 后端西工大报名
 */
class XgdBookOrder extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editOrder,delOrder,getInfo']
    ];

    public function editOrder(){
        $data = Request::instance()->param();
        $orderservice = new XgdOrderService();
        $checkinfo = $orderservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '预约订单不属于本商户',
                    'solelyCode' => 217003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '预约订单已删除',
                    'solelyCode' => 217002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '预约订单不存在',
                    'solelyCode' => 217000
                ]);
        }
        $updateinfo = $orderservice->formatImg($data);
        $res = XgdOrderModel::updateOrder($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改预约订单信息失败',
                'solelyCode' => 217004
            ]);
        }
    }

    public function delOrder()
    {
        $data = Request::instance()->param();
        $orderservice = new XgdOrderService();
        $checkinfo = $orderservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '预约订单不属于本商户',
                    'solelyCode' => 217003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '预约订单已删除',
                    'solelyCode' => 217002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '预约订单不存在',
                    'solelyCode' => 217000
                ]);
        }
        $res = XgdOrderModel::delOrder($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除预约订单失败',
                'solelyCode' => 217005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $orderservice = new XgdOrderService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        if (isset($info['map']['gtime'])) {
            $info['map']['end_time'] = array('egt',$info['map']['gtime']);
            unset($info['map']['gtime']);
        }
        if (isset($info['map']['ltime'])) {
            $info['map']['end_time'] = array('elt',$info['map']['ltime']);
            unset($info['map']['ltime']);
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = XgdOrderModel::getOrderListToPaginate($info);
            $list = $orderservice->formatPaginateList($list);
        }else{
            $list=XgdOrderModel::getOrderList($info);
            $list = $orderservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $res = XgdOrderModel::getOrderInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '预约订单已删除',
                    'solelyCode' => 217002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '预约订单不属于本商户',
                    'solelyCode' => 217003
                ]);
            }
            $orderservice = new XgdOrderService();
            $res = $orderservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '预约订单不存在',
                'solelyCode' => 217000
            ]);
        }
    }
}