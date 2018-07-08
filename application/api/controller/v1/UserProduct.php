<?php
namespace app\api\controller\v1;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\Product as ProductInfoModel;
use app\api\model\Category as CategoryModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use think\Exception;
use app\lib\exception\ProductException;

class UserProduct extends CommonController{

    protected $beforeActionList = [
        'checkThirdID' => ['only' => 'getList,getInfo,sortByAttr,getSortList']
    ];

    //获取商品列表
    public function getList()
    {

        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = $this->preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        //前端过滤未上架的商品
        $info['map']['onShelf'] = "true";
        if (isset($data['sortby'])&&isset($data['sort'])&&$data['sortby']!=''&&$data['sort']!='') {
            if(isset($info['pagesize'])&&isset($info['currentPage'])){
                $list = ProductModelModel::getModelSortToPaginate($info);
            }else{
                $list = ProductModelModel::getModelSort($info);
            }
        }else{
            if(isset($info['pagesize'])&&isset($info['currentPage'])){
                $list = ProductModelModel::getModelListToPaginate($info);
            }else{
                $list = ProductModelModel::getModelList($info);
            }
        }
        return $list;
    }

    //获取指定商品信息
    public function getInfo(){
       $data=Request::instance()->param();
        if (!isset($data['id'])) {
            throw new ProductException([
                'msg' => '缺少关键参数ID',
                'solelyCode' => 211005
            ]);
        }
        $res=ProductModelModel::getModelById($data['id']);
        if(is_object($res)){
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new ProductException([
                    'msg' => '该商品不属于本商家',
                    'solelyCode' => 211004
                ]);
            }
            return $res;
        }else if($res['status']==-1){
            throw new ProductException([
                'msg' => '指定商品已删除',
                'solelyCode' => 211001
            ]);
        }else{
            throw new ProductException();
        }
    }

    //根据价格，销量排序，模糊搜索，分页与不分页控制
    public function sortByAttr(){
        $data=Request::instance()->param();
        if (!isset($data['sortby'])) {
            throw new ProductException([
                'msg'=>'缺少关键参数sortby',
                'solelyCode' => 211005
            ]);
        }
        if (!isset($data['sort'])) {
            throw new ProductException([
                'msg'=>'缺少关键参数sort',
                'solelyCode' => 211005
            ]);
        }
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $data['searchItem']['thirdapp_id'] = $data['thirdapp_id'];
        //排序依据sortby：价格为price;销量为sales;综合为multi;
        //顺序sort:acs顺序,desc降序
        $info = $this->preAll($data);
        //前端过滤未上架的商品
        $info['map']['onShelf'] = "true";
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ProductModelModel::getModelSortToPaginate($info);
        }else{
            $list = ProductModelModel::getModelSort($info);
        }
        return $list;
    }

    //排序分页处理
    public function preAll($data){

        if (isset($data['searchItem'])) {
            if (isset($data['searchItem']['gprice'])) {
                $data['searchItem']['price'] = array('gt',$data['searchItem']['gprice']);
                unset($data['searchItem']['gprice']);
            }
            if (isset($data['searchItem']['lprice'])) {
                $data['searchItem']['price'] = array('lt',$data['searchItem']['lprice']);
                unset($data['searchItem']['lprice']);
            }
            if (isset($data['searchItem']['pcategory_id'])) {
                $catemap['status'] = 1;
                $catemap['parentid'] = $data['searchItem']['pcategory_id'];
                $cagegorytree = CategoryModel::getCategoryTree($catemap);
                if ($cagegorytree) {
                    $categorylist = ctree_to_list($cagegorytree,$child='child',$order = 'id');
                    $categoryIDs = array();
                    foreach ($categorylist as $item) {
                        array_push($categoryIDs, $item['id']);
                    }
                    $data['searchItem']['category_id'] = array('in',$categoryIDs);
                }
                unset($data['searchItem']['pcategory_id']);
            }
            $search = $data['searchItem'];
        }else{
            $search = '';
        }
        $isPage = $data['is_page'];
        if (isset($data['sortby'])&&isset($data['sort'])&&$data['sortby']!=''&&$data['sort']!='') {
            if($isPage=='true'){
                $res = array(
                    'pagesize'=>$data['pagesize'],
                    'currentPage'=>$data['currentPage'],
                    'map'=>$search,
                    'sortby'=>$data['sortby'],
                    'sort'=>$data['sort'],
                );
            }else{
                $res=array(
                    'map'=>$search,
                    'sortby'=>$data['sortby'],
                    'sort'=>$data['sort'],
                );
            }
        }else{
            if($isPage=='true'){
                $res=[
                    'pagesize'=>$data['pagesize'],
                    'currentPage'=>$data['currentPage'],
                    'map'=>$search,
                ];                
            }else{      
                $res=['map'=>$search];
            }
        }
        return $res;
    }

    //获取商品列表
    public function getSortList()
    {
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //前端过滤未上架的商品
        $info['map']['onShelf'] = "true";
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ProductInfoModel::getProductListToPaginate($info);
        }else{
            $list = ProductInfoModel::getProductList($info);
        }
        return $list;
    }
}