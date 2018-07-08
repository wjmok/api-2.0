<?php
namespace app\api\service;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\Product as ProductInfoModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\model\Order as OrderModel;
use app\api\model\Wxpaylog as WxpaylogModel;
use app\api\service\Pay as PayService;
use think\Cache;

class ProductModel{

    public function addinfo($data)
    {
        //获取product信息
        $productinfo = ProductInfoModel::getProductInfo($data['product_id']);
        $data['category_id'] = $productinfo['category_id'];
        $data['status'] = 1;
        $data['create_time'] = time();
        if(isset($data['mainImg'])){
            $data['mainImg'] = json_encode($data['mainImg']);
        }else{
            $data['mainImg'] = json_encode([]);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg'] = json_encode($data['bannerImg']);
        }else{
            $data['bannerImg'] = json_encode([]);
        }
        $userinfo = Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id'] = $userinfo->thirdapp_id;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg'] = initimg($data['bannerImg']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = Cache::get('info'.$token);
        //获取预约信息
        $modelinfo = ProductModelModel::getModelById($id);
        if ($modelinfo) {
            if ($userinfo['thirdapp_id']!=$modelinfo['thirdapp_id']) {
                return -1;
            }
            if ($modelinfo['status']==-1) {
                return -2;
            }
        }else{
            return -3;
        }
        return 1;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
            $list['data'][$key]['bannerImg']=json_decode($value['bannerImg'],true);
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg'] = json_decode($info['mainImg'],true);
        $info['bannerImg'] = json_decode($info['bannerImg'],true);
        return $info;
    }

    //结算团购商品
    public function settlementGroup()
    {
        $modelmap['map']['end_time'] = array('elt',time());
        $modelmap['map']['group_valid'] = "false";
        $modelmap['map']['isgroup'] = "true";
        $modellist = ProductModelModel::getModelList($modelmap);
        if ($modellist) {
            foreach ($modellist as $key => $value) {
                $itemmap['model_id'] = $value['id'];
                $itemmap['price_type'] = "normal";
                $itemmap['status'] = 1;
                $itemlist = OrderItemModel::getItemListByMap($itemmap);
                if ($itemlist) {
                    $orderIDs = array();
                    foreach ($itemlist as $item) {
                        array_push($orderIDs, $item['order_id']);
                    }
                    //团购只能购买单种商品，没有去重操作
                    $ordermap['map']['id'] = array('in',$orderIDs);
                    $ordermap['map']['start_time'] = $value['start_time'];
                    $ordermap['map']['price_type'] = "group";
                    $orderlist = OrderModel::getOrderList($ordermap);
                    if ($orderlist) {
                        if (count($orderlist)<$value['group_minimum']) {
                            //开团数量未达到，退款
                            foreach ($orderlist as $k => $v) {
                                //需要生成并记录订单退单号
                                $updateorder['refund_no'] = makeOrderNo();
                                $uporder = OrderModel::updateinfo($v['id'],$updateorder);
                                $pay = new PayService($v['id']);
                                $refund = $pay->refundOrder();
                                if ($refund['return_code']!="SUCCESS") {
                                    //退款失败，记录日志
                                    $log = array(
                                        'title'=>'微信退款',
                                        'result'=>$refund['return_code'],
                                        'content'=>$refund['return_msg'],
                                        'user_id'=>$v['user_id'],
                                        'thirdapp_id'=>$v['thirdapp_id'],
                                        'create_time'=>time(),
                                    );
                                    $saveLog = WxpaylogModel::addLog($log);
                                }else{
                                    //退款成功，记录日志
                                    $log = array(
                                        'title'=>'微信退款',
                                        'result'=>$refund['return_code'],
                                        'content'=>json_encode($refund),
                                        'user_id'=>$v['user_id'],
                                        'thirdapp_id'=>$v['thirdapp_id'],
                                        'create_time'=>time(),
                                    );
                                    $saveLog = WxpaylogModel::addLog($log);
                                    $upinfo['isrefund'] = "true";
                                    $uprefund = OrderModel::updateinfo($v['id'],$upinfo);
                                }
                            }
                        }
                        //无论成功与否，该团购到时间需结算
                        $update['group_valid'] = "true";
                        $upmodel = ProductModelModel::updateProductModel($value['id'],$update);
                    }
                }
            }
        }
    }
}