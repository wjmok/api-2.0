<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/23
 * Time: 11:09
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\BookOrder as OrderModel;
use app\api\service\BookOrder as OrderService;
use think\Request as Request;
use think\Cache;

/**
 * 后端预约订单模块
 */
class BookOrder extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editOrder,delOrder,getInfo']
    ];

    public function editOrder(){
        $data = Request::instance()->param();
        $orderservice=new OrderService();
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
        $res = OrderModel::updateOrder($data['id'],$data);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改预约订单成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改预约订单失败',
                'solelyCode' => 217004
            ]);
        }
    }

    public function delOrder()
    {
        $data = Request::instance()->param();
        $orderservice=new OrderService();
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
        $res = OrderModel::delOrder($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除预约订单成功',
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
        $data=Request::instance()->param();
        $orderservice=new OrderService();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo=Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id']=$userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = OrderModel::getOrderListToPaginate($info);
            $list = $orderservice->formatPaginateList($list);
        }else{
            $list = OrderModel::getOrderList($info);
            $list = $orderservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取预约订单列表失败',
                'solelyCode' => 217006
            ]);
        }
    }

    public function getInfo()
    {
        $data=Request::instance()->param();
        $userinfo=Cache::get('info'.$data['token']);
        $res=OrderModel::getOrderInfo($data['id']);
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
            $orderservice=new OrderService();
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