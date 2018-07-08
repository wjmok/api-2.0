<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/7
 * Time: 19:50
 */

namespace app\api\controller\v1;

use think\Controller;
use think\Request as Request;
use think\Exception;
use think\Cache;
use app\api\controller\BaseController;
use app\api\model\Product as ProductInfoModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\Order as OrderModel;
use app\api\model\OrderItem as OrderItemModel;
use app\api\service\ProductModel as ProductModelService;
use app\api\service\ThemeContent as ThemeContentService;
use app\api\service\Pay as PayService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

/**
 * 商品型号相关
 */
class ProductModel extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editModel,delModel,getInfo,setGroup,cancelGroup,refundGroup']
    ];

    public function addModel()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        if (!isset($data['product_id'])) {
        	throw new TokenException([
                'msg' => '缺少关键参数ID,产品ID不能缺少',
                'solelyCode' => 211005
            ]);
        }else{
            //检验product信息
            $productinfo = ProductInfoModel::getProductInfo($data['product_id']);
            if ($productinfo) {
                if ($productinfo['status']==-1) {
                    throw new TokenException([
                        'msg' => '指定商品已删除',
                        'solelyCode' => 211001
                    ]);
                }
                if ($productinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                    throw new TokenException([
                        'msg' => '该商品不属于本商家',
                        'solelyCode' => 211004
                    ]);
                }
            }else{
                throw new TokenException([
                    'msg' => '指定商品不存在',
                    'solelyCode' => 211000
                ]);
            }
        }
        $modelservice = new ProductModelService();
        $info = $modelservice->addinfo($data);
        $res = ProductModelModel::addModel($info);
        if ($res==1) {
        	throw new SuccessMessage([
                'msg' => '新增型号成功'
            ]);
        }else{
        	throw new TokenException([
                'msg' => '新增型号失败',
                'solelyCode' => 211002
            ]);
        }
    }

    public function editModel()
    {
    	$data = Request::instance()->param();
        $modelservice = new ProductModelService();
        $checkinfo = $modelservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该型号不属于本商家',
                    'solelyCode' => 211011
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定型号已删除',
                    'solelyCode' => 211008
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定型号不存在',
                    'solelyCode' => 211007
                ]);
        }
        $data = $modelservice->formatImg($data);
        $res = ProductModelModel::updateProductModel($data['id'],$data);
        if ($res==1) {
    		throw new SuccessMessage([
    			'msg' => '修改型号信息成功'
    		]);
        }else{
            throw new TokenException([
                'msg' => '保存商品型号信息失败',
                'solelyCode' => 211010
            ]);
        }
    }

    public function delModel()
    {
    	$data=Request::instance()->param();
        $modelservice = new ProductModelService();
        $checkinfo = $modelservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该型号不属于本商家',
                    'solelyCode' => 211011
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定型号已删除',
                    'solelyCode' => 211008
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定型号不存在',
                    'solelyCode' => 211007
                ]);
        }
        $res = ProductModelModel::delModel($data['id']);
        if ($res==1) {
            //删除关联的theme内容
            $themeservice = new ThemeContentService;
            $del = $themeservice->delrelation($data['id'],"product");
    		throw new SuccessMessage([
    			'msg' => '删除型号信息成功'
    		]);
        }else{
            throw new TokenException([
                'msg' => '删除型号失败',
                'solelyCode' => 211012
            ]);
        }
    }

    public function getList()
    {
    	$data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //补充信息
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list=ProductModelModel::getModelListToPaginate($info);
        }else{
            $list=ProductModelModel::getModelList($info);
        }
        return $list;
    }

    public function getInfo()
    {
    	$data = Request::instance()->param();
        $res = ProductModelModel::getModelById($data['id']);
        if($res){
            if($res['status']==-1){
                throw new TokenException([
                    'msg' => '指定商品已删除',
                    'solelyCode' => 211001
                ]);
            }
            return $res;
        }else{
            throw new TokenException([
                'msg' => '指定商品不存在',
                'solelyCode' => 211000
            ]);
        }
    }

    //开启团购功能
    public function setGroup()
    {
        $data = Request::instance()->param();
        $modelservice = new ProductModelService();
        $checkinfo = $modelservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                $modelinfo = ProductModelModel::getModelById($data['id']);
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该型号不属于本商家',
                    'solelyCode' => 211011
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定型号已删除',
                    'solelyCode' => 211008
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定型号不存在',
                    'solelyCode' => 211007
                ]);
        }
        if ($modelinfo['isgroup']=="true") {
            throw new TokenException([
                'msg' => '该商品已开启团购',
                'solelyCode' => 211013
            ]);
        }
        if (isset($data['start_time'])||isset($data['end_time'])) {
            //设置新的开始/结束时间代表开启新的团购
            if ($data['start_time']!=$modelinfo['start_time']||$data['end_time']!=$modelinfo['end_time']) {
                $data['group_valid'] = "false";
            }
        }
        $data['isgroup'] = "true";
        $update = ProductModelModel::updateProductModel($data['id'],$data);
        if ($update==1) {
            //更改商品的团购标签
            $upinfo['isgroup'] = "true";
            $upproduct = ProductInfoModel::updateinfo($modelinfo['product_id'],$upinfo);
            throw new SuccessMessage([
                'msg' => '开启团购成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '开启团购失败',
                'solelyCode' => 211014
            ]);
        }
    }

    //关闭团购功能
    public function cancelGroup()
    {
        $data = Request::instance()->param();
        $modelservice = new ProductModelService();
        $checkinfo = $modelservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                $modelinfo = ProductModelModel::getModelById($data['id']);
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该型号不属于本商家',
                    'solelyCode' => 211011
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定型号已删除',
                    'solelyCode' => 211008
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定型号不存在',
                    'solelyCode' => 211007
                ]);
        }
        if ($modelinfo['isgroup']=="false") {
            throw new TokenException([
                'msg' => '该商品未开启团购',
                'solelyCode' => 211015
            ]);
        }
        $data['isgroup'] = "false";
        $update = ProductModelModel::updateProductModel($data['id'],$data);
        if ($update==1) {
            //判断商品是否关闭团购
            $map['map']['product_id'] = $modelinfo['product_id'];
            $modellist = ProductModelModel::getModelList($map);
            $group_num = 0;
            foreach ($modellist as $key => $value) {
                if ($value['isgroup']=="true") {
                    $group_num += 1;
                }
            }
            if ($group_num==0) {
                $upinfo['isgroup'] = "false";
                $upproduct = ProductInfoModel::updateinfo($modelinfo['product_id'],$upinfo); 
            }
            throw new SuccessMessage([
                'msg' => '关闭团购成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '关闭团购失败',
                'solelyCode' => 211016
            ]);
        }
    }

    //团购退款
    public function refundGroup()
    {
        $data = Request::instance()->param();
        $modelservice = new ProductModelService();
        $checkinfo = $modelservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                $modelinfo = ProductModelModel::getModelById($data['id']);
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该型号不属于本商家',
                    'solelyCode' => 211011
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定型号已删除',
                    'solelyCode' => 211008
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定型号不存在',
                    'solelyCode' => 211007
                ]);
        }
        if ($modelinfo['isgroup']=="false") {
            throw new TokenException([
                'msg' => '该商品未开启团购',
                'solelyCode' => 211015
            ]);
        }
        //搜索本期的团购订单
        $itemmap['model_id'] = $data['id'];
        $itemmap['price_type'] = "normal";
        $itemmap['status'] = 1;
        $itemlist = OrderItemModel::getItemListByMap($itemmap);
        if ($itemlist) {
            $orderIDs = array();
            foreach ($itemlist as $item) {
                array_push($orderIDs, $item['order_id']);
            }
            $ordermap['map']['id'] = array('in',$orderIDs);
            $ordermap['map']['start_time'] = $modelinfo['start_time'];
            $ordermap['map']['price_type'] = "group";
            $orderlist = OrderModel::getOrderList($ordermap);
            if ($orderlist) {
                foreach ($orderlist as $k => $v) {
                    //需要生成并记录订单退单号
                    $update['refund_no'] = makeOrderNo();
                    $uporder = OrderModel::updateinfo($v['id'],$update);
                    $pay = new PayService($v['id']);
                    $refund = $pay->refundOrder();
                    if ($refund['return_code']!="SUCCESS") {
                        throw new TokenException([
                            'msg' => $refund['return_msg'].',订单'.$v['order_no'].'退款失败',
                            'solelyCode' => 209025
                        ]);
                    }else{
                        $upinfo['isrefund'] = "true";
                        $uprefund = OrderModel::updateinfo($v['id'],$upinfo);
                    }
                }
                throw new SuccessMessage([
                    'msg' => '退款成功'
                ]);
            }else{
                throw new TokenException([
                    'msg' => '本商品没有相关的团购订单',
                    'solelyCode' => 209000
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '本商品没有相关的团购订单',
                'solelyCode' => 209000
            ]);
        }
    }
}