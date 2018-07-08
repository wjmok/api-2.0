<?php
namespace app\api\controller\v1;
use think\Request as Request;
use app\api\model\XgdBookOrder as XgdOrderModel;
use app\api\model\XgdUnbookOrder as XgdUnbookOrderModel;
use app\api\model\XgdBook as XgdBookModel;
use app\api\service\XgdUserBookOrder as XgdUserOrderService;
use app\api\service\Token as TokenService;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class XgdUserBookOrder extends CommonController
{
    protected $beforeActionList = [
        'checkToken' => ['only' => 'addOrder,joinOrder,cancelOrder,editOrder,getList,getInfo,getStatistics'],
        'checkID'=>['only' => 'joinOrder,cancelOrder,editOrder,getInfo']
    ];

    public function addOrder()
    {
        $data = Request::instance()->param();
        $orderservice = new XgdUserOrderService();
        $docheck = $orderservice->checkOrder($data['token']);
        $checkValid = $orderservice->checkValid($data['token']);
        if (!$checkValid) {
            throw new TokenException([
                'msg' => '信誉值不足，不能报名',
                'solelyCode'=>216000
            ]);
        }
        if (!isset($data['book_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数BookID',
                'solelyCode'=>217008
            ]);
        }
        $userinfo = TokenService::getUserinfo($data['token']);
        $checkbook = XgdBookModel::getBookInfo($data['book_id']);
        if (!$checkbook) {
            throw new TokenException([
                'msg' => '活动预约信息不存在',
                'solelyCode'=>216000
            ]);
        }else{
            if ($checkbook['status']==-1) {
                throw new TokenException([
                    'msg' => '活动预约已删除',
                    'solelyCode'=>216002
                ]);
            }
            if ($checkbook['end_time']<time()) {
                throw new TokenException([
                    'msg' => '活动已结束',
                    'solelyCode'=>217014
                ]);
            }
            // if ($checkbook['start_time']<time()) {
            //     throw new TokenException([
            //         'msg' => '活动已开始，不能报名',
            //         'solelyCode'=>217011
            //     ]);
            // }
            if ($checkbook['bookNum']>=$checkbook['limitNum']) {
                throw new TokenException([
                    'msg' => '活动已报满',
                    'solelyCode'=>217012
                ]);
            }
        }
        //检测是否重复报名
        $map['user_id'] = $userinfo['id'];
        $map['book_id'] = $data['book_id'];
        $checkorder = XgdOrderModel::getOrderByMap($map);
        if ($checkorder) {
            if ($checkorder['book_status']==1) {
                throw new TokenException([
                    'msg' => '您已报名参加过此活动，请勿重复操作',
                    'solelyCode'=>217009
                ]);
            }
            if ($checkorder['book_status']==2) {
                throw new TokenException([
                    'msg' => '您已参与了活动',
                    'solelyCode'=>217015
                ]);
            }
            //重复报名更新信息
            if ($checkorder['book_status']==3) {
                //参与活动
                $addinfo['book_status'] = 1;
                $addinfo['book_time'] = time();
                $uporder = XgdOrderModel::updateOrder($checkorder['id'],$addinfo);
                if ($uporder==1) {
                    $bookinfo = XgdBookModel::getBookInfo($data['book_id']);
                    $update['bookNum'] = $bookinfo['bookNum']+1;
                    $upbook = XgdBookModel::updateBook($data['book_id'],$update);
                    throw new SuccessMessage([
                        'msg'=>'报名成功',
                    ]);
                }else{
                    throw new TokenException([
                        'msg' => '新增预约订单失败',
                        'solelyCode'=>217001
                    ]);
                }
            }
        }
        $info = $orderservice->addinfo($data);
        $res = XgdOrderModel::addOrder($info); 
        if($res==1){
            //更新book信息
            $bookinfo = XgdBookModel::getBookInfo($data['book_id']);
            $update['bookNum'] = $bookinfo['bookNum']+1;
            $upbook = XgdBookModel::updateBook($data['book_id'],$update);
            throw new SuccessMessage([
                'msg'=>'报名成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增预约订单失败',
                'solelyCode'=>217001
            ]);
        }
    }

    //参与活动,扫描活动二维码进入此接口
    public function joinOrder()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $bookinfo = XgdBookModel::getBookInfo($data['id']);
        if (!$bookinfo) {
            throw new TokenException([
                'msg' => '活动预约信息不存在',
                'solelyCode'=>216000
            ]);
        }else{
            if ($bookinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '活动不属于本商户',
                    'solelyCode'=>216003
                ]);
            }
            if ($bookinfo['status']==-1) {
                throw new TokenException([
                    'msg' => '活动预约已删除',
                    'solelyCode'=>216002
                ]);
            }
            if ($bookinfo['end_time']<time()) {
                throw new TokenException([
                    'msg' => '活动已结束',
                    'solelyCode'=>217014
                ]);
            }
        }
        //检测是否有报名
        $map['user_id'] = $userinfo['id'];
        $map['book_id'] = $data['id'];
        $orderinfo = XgdOrderModel::getOrderByMap($map);
        if (!$orderinfo) {
            // throw new TokenException([
            //     'msg' => '您还未报名，不能参加活动',
            //     'solelyCode'=>217013
            // ]);
            $unbookorder = XgdUnbookOrderModel::getOrderByMap($map);
            if ($unbookorder) {
                throw new TokenException([
                    'msg' => '您已参与了活动',
                    'solelyCode'=>217015
                ]);
            }
            //未报名的记录未预约订单
            $unbookinfo = array(
                'user_id'    =>$userinfo['id'],
                'menu_id'    =>$bookinfo['menu_id'],
                'campus_id'  =>$bookinfo['campus_id'],
                'book_id'    =>$bookinfo['id'],
                'start_time' =>$bookinfo['start_time'],
                'end_time'   =>$bookinfo['end_time'],
                'join_time'  =>time(),
                'thirdapp_id'=>$userinfo['thirdapp_id'],
            );
            $addunbook = XgdUnbookOrderModel::addOrder($unbookinfo);
            if ($addunbook==1) {
                throw new SuccessMessage([
                    'msg'=>'站票',
                ]);
            }else{
                throw new TokenException([
                    'msg' => '参与活动失败',
                    'solelyCode'=>217016
                ]);
            }
        }else{
            if ($orderinfo['book_status']==3) {
                throw new TokenException([
                    'msg' => '您已取消了活动',
                    'solelyCode'=>217010
                ]);
            }
            if ($orderinfo['book_status']==2) {
                throw new TokenException([
                    'msg' => '您已参与了活动',
                    'solelyCode'=>217015
                ]);
            }
        }
        //参与活动
        $joininfo['book_status'] = 2;
        $joininfo['join_time'] = time();
        $uporder = XgdOrderModel::updateOrder($orderinfo['id'],$joininfo);
        if ($uporder==1) {
            throw new SuccessMessage([
                'msg'=>'坐票',
            ]);
        }else{
            throw new TokenException([
                'msg' => '参与活动失败',
                'solelyCode'=>217016
            ]);
        }
    }

    //编辑订单
    public function editOrder()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $orderservice = new XgdUserOrderService();
        $checkinfo = $orderservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                $res = XgdOrderModel::updateOrder($data['id'],$data);
                if ($res==1) {
                    throw new SuccessMessage([
                        'msg' => '修改成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg' => '编辑订单信息失败',
                        'solelyCode' => 217004
                    ]);
                }
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
            case -4:
                throw new TokenException([
                    'msg' => '预约订单不属于你',
                    'solelyCode' => 217007
                ]);
        }
    }

    //取消报名
    public function cancelOrder()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $orderservice = new XgdUserOrderService();
        $checkinfo = $orderservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                $orderinfo = XgdOrderModel::getOrderInfo($data['id']);
                if ($orderinfo['book_status']==3) {
                    throw new TokenException([
                        'msg' => '报名已取消',
                        'solelyCode' => 217010
                    ]);
                }
                if ($orderinfo['book_status']==2) {
                    throw new TokenException([
                        'msg' => '已参与活动',
                        'solelyCode' => 217015
                    ]);
                }
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
            case -4:
                throw new TokenException([
                    'msg' => '预约订单不属于你',
                    'solelyCode' => 217007
                ]);
        }
        $bookinfo = XgdBookModel::getBookInfo($orderinfo['book_id']);
        if (!$bookinfo) {
            throw new TokenException([
                'msg' => '预约活动不存在',
                'solelyCode' => 216000
            ]);
        }else{
            //默认已结束不能取消
            if ($bookinfo['end_time']<time()) {
                throw new TokenException([
                    'msg' => '活动已开始，不能取消',
                    'solelyCode' => 217014
                ]);
            }
        }
        //取消报名
        $update['book_status'] = 3;
        $update['cancel_time'] = time();
        $uporder = XgdOrderModel::updateOrder($data['id'],$update);
        if ($uporder==1) {
            //更新book信息
            $updateinfo['bookNum'] = $bookinfo['bookNum']-1;
            $updateinfo['update_time'] = $bookinfo['update_time'];
            $upbook = XgdBookModel::updateBook($orderinfo['book_id'],$updateinfo);
            throw new SuccessMessage([
                'msg'=>'取消成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '取消报名失败',
                'solelyCode'=>217017
            ]);
        }
    }

    //获取指定报名信息
    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
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
            if ($res['user_id']!=$userinfo['id']) {
                throw new TokenException([
                    'msg' => '预约订单不属于你',
                    'solelyCode' => 217007
                ]);
            }
            $orderservice = new XgdUserOrderService();
            $res = $orderservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '预约订单不存在',
                'solelyCode' => 217000
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $orderservice = new XgdUserOrderService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
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
            $list = XgdOrderModel::getOrderList($info);
            $list = $orderservice->formatList($list);
        }
        return $list;
    }

    //获取报名统计信息
    public function getStatistics()
    {
        $data = Request::instance()->param();
        $orderservice = new XgdUserOrderService();
        $list = $orderservice->getStatistics($data);
        return $list;
    }
}