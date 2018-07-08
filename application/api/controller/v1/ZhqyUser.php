<?php
namespace app\api\controller\v1;
use think\Controller;
use app\api\controller\CommonController;
use think\Request as Request;
use app\api\model\Category as CategoryModel;
use app\api\model\HqyProduct as HqyProductModel;
use app\api\service\Token as TokenService;
use app\api\service\HqyProduct as HqyProductService;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

//智惠权益项目
class ZhqyUser extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'getInfo'],
    //     'checkToken' => ['only' => 'submitProduct,getList']
    ];

    //用户提交商品信息
    public function submitProduct()
    {
        $data = Request::instance()->param();
        // $userinfo = TokenService::getUserinfo($data['token']);
        $userinfo['thirdapp_id'] = 23;
        $productservice = new HqyProductService();
        if (!isset($data['category_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数category_id',
                'solelyCode' => 211005
            ]);
        }else{
            $categoryinfo = CategoryModel::getCategoryInfo($data['category_id']);
            if ($categoryinfo) {
                if ($categoryinfo['status']==-1) {
                    throw new TokenException([
                        'msg' => '指定分类已删除',
                        'solelyCode' => 212001
                    ]);
                }
                if ($categoryinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                    throw new TokenException([
                        'msg' => '此分类不属于本商家',
                        'solelyCode' => 212002
                    ]);
                }
            }else{
                throw new TokenException([
                    'msg' => '指定分类不存在',
                    'solelyCode' => 212000
                ]);
            }
        }
        $data = $productservice->addinfo($data);
        if (isset($data['info'])) {
            if (!is_array($data['info'])) {
                throw new TokenException([
                    'msg' => '商品信息非数组',
                    'solelyCode' => 211000
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '未传递商品信息',
                'solelyCode' => 211000
            ]);
        }
        $info = $data['info'];
        unset($data['info']);
        foreach ($info as $key => $value) {
            $data['mainImg'] = json_encode($value['mainImg']);
            $data['redeem_code'] = $value['redeem_code'];
            $data['deadline'] = $value['deadline'];
            $res = HqyProductModel::addProduct($data);
            if ($res!=1) {
                throw new TokenException([
                    'msg' => '提交失败',
                    'solelyCode' => 211000
                ]);
            }
        }
        throw new SuccessMessage([
            'msg' => '提交成功'
        ]);
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
        $userinfo = TokenService::getUserinfo($data['token']);
        // $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        // $info['map']['user_id'] = $userinfo['id'];
        $info['map']['user_id'] = 2;
        $info['map']['thirdapp_id'] = 23;
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
        $userinfo = TokenService::getUserinfo($data['token']);
        $userinfo['thirdapp_id'] = 23;
        $userinfo['id'] = 2;
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
            if ($res['user_id']!=$userinfo['id']) {
                throw new TokenException([
                    'msg' => '该商品不是你提交的',
                    'solelyCode' => 211000
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