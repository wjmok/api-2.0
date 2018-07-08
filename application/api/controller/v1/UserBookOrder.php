<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/23
 * Time: 11:09
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\BookOrder as OrderModel;
use app\api\model\Book as BookModel;
use app\api\model\UserCard as UserCardModel;
use app\api\service\UserBookOrder as OrderService;
use app\api\service\UserCard as CardService;
use think\Request as Request;
use app\api\service\Token as TokenService;
use think\Db;

/**
 * 前端预约订单模块
 */
class UserBookOrder extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'cancelOrder,getInfo'],
        'checkToken' => ['only' => 'addOrder,cancelOrder,getList,getInfo']
    ];

    public function addOrder(){
        $data = Request::instance()->param();
        if (!isset($data['book_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数BookID',
                'solelyCode'=>217008
            ]);
        }
        if (!isset($data['card_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数CardID',
                'solelyCode'=>217008
            ]);
        }
        //获取预约信息
        $bookinfo = BookModel::getBookInfo($data['book_id']);
        if (empty($bookinfo)) {
            throw new TokenException([
                'msg' => '预约信息不存在',
                'solelyCode'=>216000
            ]);
        }
        //判断是否重复预约
        $userinfo = TokenService::getUserinfo($data['token']);
        $checkbook = OrderModel::getOrderInfoByUser($data['book_id'],$userinfo['id']);
        if ($checkbook) {
            throw new TokenException([
                'msg' => '您已预约过，请勿重新预约',
                'solelyCode'=>217009
            ]);
        }
        $orderservice = new OrderService();
        $info = $orderservice->addinfo($data);
        $info['score'] = (int)$bookinfo['price'];
        Db::startTrans();
        try{
            //判断会员卡是否能支付
            $cardinfo = UserCardModel::getCardInfo($data['card_id']);
            $cardservice = new CardService();
            $check = $cardservice->checkcard($data['card_id'],$userinfo['id']);
            switch ($check) {
                case 1:
                    break;
                case -1:
                    throw new TokenException([
                        'msg' => '用户会员卡不存在',
                        'solelyCode'=>218000
                    ]);
                case -2:
                    throw new TokenException([
                        'msg' => '会员卡已删除',
                        'solelyCode'=>218002
                    ]);
                case -3:
                    throw new TokenException([
                        'msg' => '会员卡已使用或已失效',
                        'solelyCode'=>218009
                    ]);
            }
            if ($cardinfo['score']<$info['score']) {
                throw new TokenException([
                    'msg' => '会员卡余额不足以支付',
                    'solelyCode'=>218010
                ]);
            }
            switch ($cardinfo['cardType']) {
                case 1:
                    $upinfo['score'] = $cardinfo['score']-$info['score'];
                    $update = UserCardModel::updateCard($data['card_id'],$upinfo);
                    break;
                case 2:
                    //时间卡，未失效即可使用
                    break;
                case 3:
                    //单次卡使用即核销
                    $upinfo['card_status'] = 1;
                    $update = UserCardModel::updateCard($data['card_id'],$upinfo);
                    break;
            }
            //抓取数据库信息判定是否预约成功
            $map['map']['book_id'] = $data['book_id'];
            $map['map']['book_status'] = 1;
            $booklist = OrderModel::getOrderList($map);
            if ($booklist) {
                $booklist = $booklist->toArray();
                $bookcount = count($booklist);
            }else{
                $bookcount = 0;
            }
            $booklimit = $bookinfo['limitNum']-$bookcount-1;
            if ($booklimit >= 0) {
                $info['book_status'] = 1;
            }else{
                $info['book_status'] = 2;
            }
            $res = OrderModel::addOrder($info);
            if ($res==1) {
                Db::commit();
            }else{
                throw new TokenException([
                    'msg' => '新增预约订单失败',
                    'solelyCode'=>217001
                ]);
            }
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }
        throw new SuccessMessage([
            'msg'=>'添加成功',
        ]);
    }

    public function cancelOrder()
    {
        $data = Request::instance()->param();
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        if ($orderinfo['book_status']==3) {
            throw new TokenException([
                'msg' => '订单已取消，请勿重复操作',
                'solelyCode' => 217010
            ]);
        }
        $orderservice = new OrderService();
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
            case -4:
                throw new TokenException([
                    'msg' => '预约订单不属于你',
                    'solelyCode' => 217007
                ]);
        }
        $updateinfo['book_status'] = 3;
        $updateinfo['cancel_time'] = time();
        $res = OrderModel::updateOrder($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'取消预约成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '取消预约失败',
                'solelyCode' => 217005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $orderservice = new OrderService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = OrderModel::getOrderListToPaginate($info);
            $list = $orderservice->formatPaginateList($list);
        }else{
            $list = OrderModel::getOrderList($info);
            $list = $orderservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $res = OrderModel::getOrderInfo($data['id']);
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
            $orderservice = new OrderService();
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