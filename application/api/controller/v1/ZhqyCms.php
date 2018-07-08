<?php
namespace app\api\controller\v1;
use think\Request as Request;
use think\Controller;
use app\api\controller\BaseController;
use app\api\model\HqyProduct as HqyProductModel;
use app\api\model\Product as ProductInfoModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\service\HqyProduct as HqyProductService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

//智惠权益项目
class ZhqyCms extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'passProduct,rejectProduct,getInfo']
    ];

    public function passProduct()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $productservice = new HqyProductService();
        $checkinfo = $productservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该商品不属于本商家',
                    'solelyCode' => 211004
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定商品已删除',
                    'solelyCode' => 211001
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定商品不存在',
                    'solelyCode' => 211000
                ]);
        }
        $zhqyprodcut = HqyProductModel::getProductInfo($data['id']);
        if ($zhqyprodcut['check_status']!=1) {
            throw new TokenException([
                'msg' => '商品已审核，请勿重复操作',
                'solelyCode' => 211000
            ]);
        }
        //更新审核状态
        $upinfo['check_status'] = 2;
        $upinfo['check_user'] = $userinfo['id'];
        $upinfo['check_time'] = time();
        $upinfo['update_time'] = time();
        $upproduct = HqyProductModel::updateProduct($data['id'],$upinfo);
        //重构商品数组
        $productinfo = array(
            'name'       => $zhqyprodcut['name'],
            'category_id'=> $zhqyprodcut['category_id'],
            'mainImg'    => json_encode([]),
            'bannerImg'  => json_encode([]),
            'content'    => $zhqyprodcut['content'],
            'thirdapp_id'=> $zhqyprodcut['thirdapp_id'],
            'create_time'=> time(),
            'passage1'   => $zhqyprodcut['source'],//商品来源
            'passage2'   => $zhqyprodcut['user_id'],//推荐人
            'onShelf'    => 'true',
        );
        $addproduct = ProductInfoModel::addProductID($productinfo);
        if ($addproduct>0) {
            //增加型号
            $modelinfo = array(
                'name'       => $zhqyprodcut['name'],
                'product_id' => $addproduct,
                'category_id'=> $zhqyprodcut['category_id'],
                'thirdapp_id'=> $zhqyprodcut['thirdapp_id'],
                'mainImg'    => $zhqyprodcut['mainImg'],
                'bannerImg'  => json_encode([]),
                'price'      => $zhqyprodcut['price'],
                'oprice'     => $zhqyprodcut['price'],
                'passage1'   => $zhqyprodcut['redeem_code'],//兑换码
                'passage2'   => $zhqyprodcut['deadline'],//有效期
                'passage3'   => $zhqyprodcut['user_id'],//推荐人
                'stock_num'  => 1,
                'onShelf'    => 'false',
                'create_time'=> time(), 
            );
            $addmodel = ProductModelModel::addModel($modelinfo);
            if ($addmodel==1) {
                throw new SuccessMessage([
                    'msg'=>'审核通过，请到商品页面补充信息并上架'
                ]);
            }else{
                throw new TokenException([
                    'msg'=>'审核失败',
                    'solelyCode'=>211000
                ]);
            }
        }
    }

    public function rejectProduct()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $productservice = new HqyProductService();
        $checkinfo = $productservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该商品不属于本商家',
                    'solelyCode' => 211004
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '指定商品已删除',
                    'solelyCode' => 211001
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定商品不存在',
                    'solelyCode' => 211000
                ]);
        }
        $zhqyprodcut = HqyProductModel::getProductInfo($data['id']);
        if ($zhqyprodcut['check_status']!=1) {
            throw new TokenException([
                'msg' => '商品已审核，请勿重复操作',
                'solelyCode' => 211000
            ]);
        }
        //更新审核状态
        $upinfo['check_status'] = -1;
        $upinfo['check_user'] = $userinfo['id'];
        $upinfo['check_time'] = time();
        $upinfo['update_time'] = time();
        $upproduct = HqyProductModel::updateProduct($data['id'],$upinfo);
        if ($upproduct==1) {
            throw new SuccessMessage([
                'msg'=>'审核成功'
            ]);
        }else{
            throw new TokenException([
                'msg'=>'审核失败',
                'solelyCode'=>211000
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $productservice = new HqyProductService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = HqyProductModel::getProductListToPaginate($info);
            $list = $productservice->formatPaginateList($list);
        }else{
            $list = HqyProductModel::getProductList($info);
            $list = $productservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = HqyProductModel::getProductInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '指定商品已删除',
                    'solelyCode' => 211001
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该商品不属于本商家',
                    'solelyCode' => 211004
                ]);
            }
            $productservice = new HqyProductService();
            $res = $productservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '指定商品不存在',
                'solelyCode' => 211000
            ]);
        }
    }
}