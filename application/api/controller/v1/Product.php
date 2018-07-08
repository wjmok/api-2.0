<?php
namespace app\api\controller\v1;
use think\Controller;
use think\Exception;
use think\Cache;
use think\Request as Request;
use app\api\controller\BaseController;
use app\api\model\Product as ProdcutInfoModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\ThirdApp as ThirdModel;
use app\api\service\Product as ProductService;
use app\api\service\ThemeContent as ThemeContentService;
use app\api\service\HqyProduct as HqyProductService;
use app\api\validate\ProductValidate;
use app\api\validate\ProductUptValidate;
use app\lib\exception\ProductException;
use app\lib\exception\CategoryException;
use app\lib\exception\SuccessMessage;


class Product extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editProduct,delProduct,getProductinfo']
    ];

    //添加商品
    public function addProduct()
    {
        $data=Request::instance()->param();
        $validate=new ProductValidate();
        $validate->goCheck();
        $ProductService=new ProductService();
        //过滤数据，整理字段信息
        $info=$ProductService->addinfo($data);
        //检查权限
        $check = $ProductService->getThirdAppId($data);
        if($check==-2){
            throw new CategoryException([
                'msg' => 'category_id参数有误，此分类不属于该商户',
                'solelyCode' => 212002
            ]);
        }else if($check==-1){
            throw new CategoryException([
                'msg' => 'category_id参数有误，该分类已被删除',
                'solelyCode' => 212001
            ]);
        }else{
            $info['thirdapp_id'] = $check;
        }
        $res=ProdcutInfoModel::addProduct($info);
        if (!$res) {
            throw new ProductException([
                'msg' => '新增商品失败',
                'solelyCode' => 211002
            ]);
        }else{
            throw new SuccessMessage([
                'msg' => '添加商品成功',
            ]);
        }
    }

    //修改商品信息
    public function editProduct()
    {
        $data=Request::instance()->param();
        $validate=new ProductUptValidate();
        $validate->goCheck();
        $ProductService=new ProductService();
        //检查权限
        $check = $ProductService->getThirdAppId($data);
        if($check==-2){
            throw new CategoryException([
                'msg' => 'category_id参数有误，此分类不属于该商户',
                'solelyCode' => 212002
            ]);
        }else if($check==-1){
            throw new CategoryException([
                'msg' => 'category_id参数有误，该分类已被删除',
                'solelyCode' => 212001
            ]);
        }else{
            $data['thirdapp_id'] = $check;
        }
        //校验数据
        $data = $ProductService->updateinfo($data);
        $checkinfo = $ProductService->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                $productinfo = ProdcutInfoModel::getProductInfo($data['id']);
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该商品不属于本商家',
                    'solelyCode' => 211004
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '商品已删除',
                    'solelyCode' => 211001
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '商品信息不存在',
                    'solelyCode' => 211000
                ]);
        }
        $res = ProdcutInfoModel::updateinfo($data['id'],$data);
        if($res==1){
            //如果更改了父级的category_id,同时修改子级的category_id
            if (isset($data['category_id'])) {
                if ($data['category_id']!=$productinfo['category_id']) {
                    $map['map']['product_id'] = $data['id'];
                    $map['map']['thirdapp_id'] = $productinfo['thirdapp_id'];
                    $modellist = ProductModelModel::getModelList($map);
                    foreach ($modellist as $key => $value) {
                        $update['category_id'] = $data['category_id'];
                        $upmodel = ProductModelModel::updateProductModel($value['id'],$update);
                    }
                }
            }
            throw new SuccessMessage([
                'msg' => '修改商品信息成功',
            ]);
        }else{
            throw new ProductException([
                'msg' => '修改商品信息失败',
                'solelyCode' => 211000
            ]);
        }
    }

    //软删除商品
    public function delProduct()
    {
        $data = Request::instance()->param();
        $ProductService = new ProductService();
        $checkinfo = $ProductService->checkinfo($data['token'],$data['id']);
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
                    'msg' => '商品已删除',
                    'solelyCode' => 211001
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '商品信息不存在',
                    'solelyCode' => 211000
                ]);
        }
        $res = ProdcutInfoModel::delProduct($data['id']);
        if($res==1){
            //关联删除型号信息
            $map['map']['product_id'] = $data['id'];
            $modellist = ProductModelModel::getModelList($map);
            foreach ($modellist as $value) {
                $del = ProductModelModel::delModel($value['id']);
                //删除型号关联theme信息
                $themeservice = new ThemeContentService;
                $deltheme = $themeservice->delrelation($value['id'],"product");
            }
            throw new SuccessMessage([
                'msg' => '删除商品成功'
            ]);
        }else{
            throw new ProductException();
        }
    }
    
    //获取商品列表
    public function getAll()
    {
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        //特殊项目
        $thirdinfo = ThirdModel::getThirdUserInfo($userinfo['thirdapp_id']);
        if (isset($thirdinfo['codeName'])) {
            switch ($thirdinfo['codeName']) {
                case 'zhqy':
                    $hqyservice = new HqyProductService();
                    $list = $hqyservice->getProductList($info);
                    return $list;
                default:
                    break;
            }
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ProdcutInfoModel::getProductListToPaginate($info);
        }else{
            $list = ProdcutInfoModel::getProductList($info);
        }
        return $list;
    }

    //获取指定商品信息
    public function getProductinfo()
    {
        $data = Request::instance()->param();
        $validate = new ProductUptValidate();
        $validate->goCheck();
        $id = $data['id'];
        $res = ProdcutInfoModel::getProductInfo($id);
        $userinfo = Cache::get('info'.$data['token']);
        if(is_object($res)){
            if($res['thirdapp_id']!=$userinfo->thirdapp_id){
                throw new ProductException([
                    'msg'=>'该商品不属于本商家',
                    'solelyCode' => 211004
                ]);
            }
            if ($res['status']==-1) {
                throw new ProductException([
                    'msg' => '指定商品已删除',
                    'solelyCode' => 211001
                ]);
            }
            return $res;
        }else{
            throw new ProductException();
        }
    }

    //根据价格，销量排序，模糊搜索，分页与不分页控制
    public function sortByAttr()
    {
        $data=Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        //定义价格为 by:price 销量为by:sales 综合为 by:priceSales 升降序分别为sort:acs ,sort:desc
        $info = $this->preAll($data);
        $list = ProdcutInfoModel::getProductSort($info,$data['token']);
        return $list;
    }

    //排序分页处理
    public function preAll($data)
    {
        if(isset($data['searchItem'])&&!empty($data['searchItem'])){
            if(isset($data['searchItem']['content'])&&!empty($data['searchItem']['content'])){
                $map['content']=$data['searchItem']['content'];
            }else{
                $map['content']='0';
            }
            if(isset($data['searchItem']['by'])&&!empty($data['searchItem']['by'])){
                $map['by']=$data['searchItem']['by'];
            }else{
                $map['by']="";
            }
            if(isset($data['searchItem']['sort'])&&!empty($data['searchItem']['sort'])){
                $map['sort']=$data['searchItem']['sort'];
            }else{
                $map['sort']="";
            }
            $isPage=$data['is_page'];
            if($isPage=='true'){
                $res=array(
                    'pagesize'=>$data['pagesize'],
                    'currentPage'=>$data['currentPage'],
                    'content'=>$map['content'],
                    'by'=>$map['by'],
                    'sort'=>$map['sort'],
                );
            }else{
                $res=array(
                    'content'=>$map['content'],
                    'by'=>$map['by'],
                    'sort'=>$map['sort'],
                );
            }
            return $res;
        }
    }
}