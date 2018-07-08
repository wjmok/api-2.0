<?php
namespace app\api\controller\v1;
use app\api\model\Remark as RemarkModel;
use think\Request as Request;
use think\Controller;
use app\api\service\UserRemark as RemarkService;
use app\api\model\UserRemark as UserRemarkModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\Order as OrderModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\service\Token as TokenService;
use app\api\validate\RemarkValidate;
use app\api\controller\CommonController;
use app\lib\exception\RemarkException;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class UserRemark extends CommonController
{ 

    protected $beforeActionList = [
        'checkToken' => ['only' => 'addRemark'],
        'checkThirdID' => ['only' => 'getList']
    ];

    //增加评论
    public function addRemark()
    {
        $data=Request::instance()->param();
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $remarkservice = new RemarkService();
        if (isset($data['art_id'])) {
            $checkArt = $remarkservice->checkArt($data);
            switch ($checkArt) {
                case -1:
                    throw new TokenException([
                        'msg' => '文章数据不存在',
                        'solelyCode' => 206004
                    ]);
                case -2:
                    throw new TokenException([
                        'msg' => '该文章已被删除',
                        'solelyCode' => 206003
                    ]);
                case -3:
                    throw new TokenException([
                        'msg' => '此文章不属于本商户',
                        'solelyCode' => 206002
                    ]);
            }
        }elseif (isset($data['model_id'])) {
            if (!isset($data['order_id'])) {
                throw new TokenException([
                    'msg' => '缺少关键参数orderID',
                    'solelyCode' => 208005
                ]);
            }
            $checkPro = $remarkservice->checkProduct($data);
            switch ($checkPro) {
                case -1:
                    throw new TokenException([
                        'msg' => '指定商品不存在',
                        'solelyCode' => 211000
                    ]);
                case -2:
                    throw new TokenException([
                        'msg' => '指定商品已删除',
                        'solelyCode' => 211001
                    ]);
                case -3:
                    throw new TokenException([
                        'msg' => '该商品不属于本商家',
                        'solelyCode' => 211004
                    ]);
            }
            //评论关联产品信息
            $modelinfo = ProductModelModel::getModelById($data['model_id']);
            $data['product_id'] = $modelinfo['product_id'];
            //校验是否已评论
            $itemmap['order_id'] = $data['order_id'];
            $itemmap['model_id'] = $data['model_id'];
            $iteminfo = OrderItemModel::getItemByMap($itemmap);
            if ($iteminfo['isremark']==1) {
                throw new TokenException([
                    'msg' => '已评论，请勿重复评论',
                    'solelyCode' => 208011
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '缺少关键参数ID',
                'solelyCode' => 208001
            ]);
        }
        $data['user_id'] = $userinfo['id'];
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['user_name'] = $userinfo['nickname'];
        $data['user_headimg'] = json_encode($userinfo['headimgurl']);
        $data['create_time'] = time();
        $add = RemarkModel::addRemark($data);
        if($add==1){
            //更新评论状态
            if (isset($data['order_id'])) {
                if ($iteminfo) {
                    $updateitem['isremark'] = 1;
                    $updateItem = OrderItemModel::updateItem($iteminfo['id'],$updateitem);
                }
                $orderinfo = OrderModel::getOrderInfo($data['order_id']);
                $updateorder['remark_num'] = $orderinfo['remark_num'] + 1;
                //检测订单是否完成评论
                if ($updateorder['remark_num']==$orderinfo['sort_count']) {
                    $updateorder['remark_status'] = "true";
                }
                $updateOrder = OrderModel::updateinfo($data['order_id'],$updateorder);
            }
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
        if (!isset($data['searchItem']['art_id'])&&!isset($data['searchItem']['product_id'])) {
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
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = RemarkModel::getRelationRemark($info);
        }else{
            $list = RemarkModel::getRemarkList($info);
        }
        return $list;
    }
}