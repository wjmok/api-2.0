<?php
namespace app\api\controller\v1;
use app\api\model\Remark as RemarkModel;
use think\Request as Request;
use think\Controller;
use app\api\model\UserRemark as UserRemarkModel;
use app\api\model\XgdBook as XgdBookModel;
use app\api\model\XgdBookOrder as XgdOrderModel;
use app\api\service\Token as TokenService;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class XgdUserRemark extends CommonController
{ 

    protected $beforeActionList = [
        'checkToken' => ['only' => 'addRemark'],
        'checkThirdID' => ['only' => 'getList']
    ];

    //增加评论
    public function addRemark()
    {
        $data = Request::instance()->param();
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        if (!isset($data['id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数id',
                'solelyCode' => 208005
            ]);
        }
        $orderinfo = XgdOrderModel::getOrderInfo($data['id']);
        if ($orderinfo) {
            if ($orderinfo['book_status']!=2) {
                throw new TokenException([
                    'msg' => '未参与活动，不能评价',
                    'solelyCode' => 217018
                ]);
            }
            if ($orderinfo['user_id']!=$userinfo['id']) {
                throw new TokenException([
                    'msg' => '预约订单不属于你',
                    'solelyCode' => 217007
                ]);
            }
            if ($orderinfo['is_remark']=="true") {
                throw new TokenException([
                    'msg' => '已评论，请勿重复评论',
                    'solelyCode' => 208011
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '预约订单不存在',
                'solelyCode' => 217000
            ]);
        }
        $bookinfo = XgdBookModel::getBookInfo($orderinfo['book_id']);
        if ($bookinfo) {
            if ($bookinfo['status']==-1) {
                throw new TokenException([
                    'msg' => '活动预约已删除',
                    'solelyCode'=>216002
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '活动预约信息不存在',
                'solelyCode'=>216000
            ]);
        }
        unset($data['id']);
        $data['user_id'] = $userinfo['id'];
        $data['create_time'] = time();
        $data['user_name'] = $userinfo['nickname'];
        $data['user_headimg'] = json_encode($userinfo['headimgurl']);
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['book_id'] = $orderinfo['book_id'];
        $add = RemarkModel::addRemark($data);
        if($add==1){
            //更新订单状态
            $update['is_remark'] = "true";
            $uporder = XgdOrderModel::updateOrder($orderinfo['id'],$update);
            throw new SuccessMessage([
                'msg'=>'评论成功'
            ]);
        }else{
            throw new RemarkException([
                'msg' => '评论失败',
                'solelyCode' => 208003
            ]);
        }
    }

    //获取指定商品/文章评论
    public function getList()
    {
        $data=Request::instance()->param();
        if (!isset($data['searchItem']['book_id'])) {
            throw new RemarkException([
                'msg' => '缺少关键参数,没有评论对象的ID信息',
                'solelyCode' => 208005
            ]);
        }
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        $list = RemarkModel::getRelationRemark($info);
        if(!empty($list)){
            return $list;
        }else{
            throw new RemarkException([
                'msg' => '获取评论列表失败',
                'solelyCode' => 208006
            ]);
        }
    }
}