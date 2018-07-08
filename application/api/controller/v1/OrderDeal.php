<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2017-11-24
 * Time: 21:56
 */

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use think\Controller;
use think\Request as Request;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class OrderDeal extends BaseController
{
    /**
     * 修改订单内容
     * @author 董博明
     * @POST data
     * @time 2017.11.24
     */
    public function editOrder()
    {
        $data = Request::instance()->param();
        $orderservice = new OrderService();
        $info = $orderservice->updateinfo($data);
        switch ($info['status']) {
            case 1:
                break;
            case -1:
                throw new OrderException([
                    'msg' => '缺少关键参数订单id',
                    'solelyCode' => 209005
                ]);
            case -2:
                throw new OrderException([
                    'msg' => '订单不存在，请检查ID',
                    'solelyCode' => 209000
                ]);
            case -3:
                throw new OrderException([
                    'msg' => '订单已删除',
                    'solelyCode' => 209006
                ]);
            case -4:
                throw new OrderException([
                    'msg' => '您权限不足，不能执行该操作',
                    'solelyCode' => 209007
                ]);
        }
        $res = OrderModel::updateinfo($data['id'],$data['orderinfo']);
        if ($res==1||$res==0) {
            throw new SuccessMessage([
                'msg' => '订单信息修改成功'
            ]);
        }else{
            throw new OrderException([
                'msg' => '订单信息修改失败',
                'solelyCode' => 209008
            ]);
        }
    }

    /**
     * 软删除订单
     * @author 董博明
     * @POST id
     * @time 2017.11.24
     */
    public function delOrder()
    {
        $data = Request::instance()->param();
        $orderservice = new OrderService();
        $info = $orderservice->delorder($data);
        switch ($info['status']) {
            case 1:
                $update = $info['update'];
                break;
            case -1:
                throw new OrderException([
                    'msg' => '缺少关键参数订单id',
                    'solelyCode' => 209005
                ]);
            case -2:
                throw new OrderException();
            case -3:
                throw new OrderException([
                    'msg' => '订单已删除',
                    'solelyCode' => 209006
                ]);
            case -4:
                throw new OrderException([
                    'msg' => '您权限不足，不能执行该操作',
                    'solelyCode' => 209007
                ]);
        }

        $res = OrderModel::updateinfo($data['id'],$update);
        if ($res==1||$res==0) {
            throw new SuccessMessage([
                'msg' => '删除订单成功'
            ]);
        }else{
            throw new OrderException([
                'msg' => '删除订单失败',
                'solelyCode' => 209009
            ]);
        }
    }

    /**
     * 获取订单列表
     * @author 董博明
     * @time 2018.3.6
     */
    public function getAll()
    {
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];

        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = OrderModel::getOrderListToPaginate($info);
        }else{
            $list = OrderModel::getOrderList($info);
        }
        return $list;
    }

    //获取单条订单信息
    public function getOrderinfo()
    {
        $data = Request::instance()->param();
        if (!isset($data['id'])) {
            throw new OrderException([
                'msg' => '缺少关键参数订单id',
                'solelyCode' => 209005
            ]);
        }
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if (empty($orderinfo)) {
            throw new OrderException();
        }
        if ($orderinfo['status']==-1) {
            throw new OrderException([
                'msg' => '订单已删除',
                'solelyCode' => 209006
            ]);
        }
        $userinfo=Cache::get('info'.$data['token']);
        if ($userinfo['thirdapp_id']!=$orderinfo['thirdapp_id']) {
           if ($userinfo['promary_scope']<40) {
                throw new OrderException([
                    'msg' => '您权限不足，不能执行该操作',
                    'solelyCode' => 209007
                ]);
           }
        }
        return $orderinfo;
    }
}