<?php
namespace app\api\service;
use app\api\model\User as UserkModel;
use app\api\model\Order as OrderModel;
use app\api\model\OrderItem as ItemModel;
use app\api\model\ProductModel as ProductModelModel;

class FlowCommon{

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            if ($list['data'][$key]['user']) {
                if ($list['data'][$key]['user_id']==0) {
                    unset($list['data'][$key]['user']);
                }else{
                    $list['data'][$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl'],true);
                }
            }
            if ($list['data'][$key]['order']) {
                $list['data'][$key]['order']['couponList'] = json_decode($value['order']['couponList'],true);
                $list['data'][$key]['order']['snap_address'] = json_decode($value['order']['snap_address'],true);
                //关联订单商品详情
                if ($value['order_type']=="product") {
                    $map['order_id'] = $value['order_id'];
                    $itemlist = ItemModel::getItemListByMap($map);
                    if (!empty($itemlist)) {
                        $iteminfo = array();
                        foreach ($itemlist as $v) {
                            $item = ProductModelModel::getModelById($v['model_id']);
                            $item = $item->toArray();
                            $iteminfo = array_merge($iteminfo, $item);
                        }
                        $list['data'][$key]['order']['iteminfo'] = $iteminfo;
                    }
                }
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list = $list->toArray();
        foreach($list as $key=>$value){
            if ($list[$key]['user']) {
                if ($list[$key]['user_id']==0) {
                    unset($list[$key]['user']);
                }else{
                    $list[$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl'],true);
                }
            }
            if ($list[$key]['order']) {
                $list[$key]['order']['couponList'] = json_decode($value['order']['couponList'],true);
                $list[$key]['order']['snap_address'] = json_decode($value['order']['snap_address'],true);
                //关联订单商品详情
                if ($value['order_type']=="product") {
                    $map['order_id'] = $value['order_id'];
                    $itemlist = ItemModel::getItemListByMap($map);
                    if (!empty($itemlist)) {
                        $iteminfo = array();
                        foreach ($itemlist as $v) {
                            $item = ProductModelModel::getModelById($v['model_id']);
                            $iteminfo = array_push($iteminfo, $item);
                        }
                        $list[$key]['order']['iteminfo'] = $iteminfo;
                    }
                }
            }
        }
        return $list;
    }
}