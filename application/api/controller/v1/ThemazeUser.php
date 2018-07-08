<?php
namespace app\api\controller\v1;
use think\Controller;
use app\api\controller\CommonController;
use app\api\model\BookOrder as OrderModel;
use app\api\model\UserCard as UserCardModel;
use think\Request as Request;
use app\api\service\Token as TokenService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

//themaze项目特有功能
class ThemazeUser extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'cancelBook'],
        'checkToken' => ['only' => 'cancelBook']
    ];

    //取消预约
    public function cancelBook(){

        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $orderinfo = OrderModel::getOrderInfo($data['id']);
        $cardinfo = json_decode($orderinfo['card'],true);
        $bookinfo = json_decode($orderinfo['book'],true);
        //提前5小时才可以取消预约
        if ((time()+18000)>$bookinfo['start_time']) {
            throw new TokenException([
                'msg' => '距离活动开始不足5小时，不能取消预约',
                'solelyCode'=>217000
            ]);
        }
        $updateorder['cancel_time'] = time();
        $updateorder['book_status'] = 3;
        $uporder = OrderModel::updateOrder($data['id'],$updateorder);
        //返还积分到卡
        switch ($cardinfo['cardType']) {
            case 1:
                $upinfo['score'] = $cardinfo['score']+$orderinfo['score'];
                $update = UserCardModel::updateCard($orderinfo['card_id'],$upinfo);
                break;
            case 2:
                //时间卡不做操作
                break;
            case 3:
                //单次卡还原
                $upinfo['card_status'] = 0;
                $update = UserCardModel::updateCard($orderinfo['card_id'],$upinfo);
                break;
        }
        throw new SuccessMessage([
            'msg'=>'取消成功',
        ]);
}