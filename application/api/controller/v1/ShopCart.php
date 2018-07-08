<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/6
 * Time: 16:42
 */

namespace app\api\controller\v1;
use app\api\controller\CommonController;
use app\api\model\ShopCart as ShopCartModel;
use app\api\model\ProductModel as ProductModel;
use think\Request as Request;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

/**
 * 购物车相关
 */
class ShopCart extends CommonController{
    
    protected $beforeActionList = [
        'checktoken' => ['only' => 'saveCart,getShopCart']
    ];

    public function checktoken()
    {
        $data = Request::instance()->param();
        if (!isset($data['token'])) {
            throw new TokenException();
        }
        $userinfo = Cache::get($token);
        if (empty($userinfo)) {
            throw new TokenException();
        }
    }
    
    public function saveCart()
    {
    	$data = Request::instance()->param();
    	$userinfo=Cache::get($data['token']);
    	$uid = $userinfo['id'];
    	if (isset($data['item'])) {
    		$info['item'] = json_encode($data['item']);
    	}
    	$info['update_time'] = time();
    	$res = ShopCartModel::saveCart($uid,$info);
    	if ($res==1||$res==0) {
    		throw new SuccessMessage([
    			'msg' => '保存购物车成功',
    		]);
    	}else{
    		throw new OrderException([
    			'msg' => '保存购物车失败',
                'solelyCode' => 209011
    		]);
    	}
    }

    public function getShopCart()
    {
    	$data = Request::instance()->param();
    	$userinfo=Cache::get($data['token']);
    	$uid = $userinfo['id'];
    	$res = ShopCartModel::getShopCart($uid);
    	if (empty($res)) {
    		$newshop['user_id'] = $uid;
    		$newshop['item'] = json_encode([]);
    		$newshop['thirdapp_id'] = 2;
    		$newshop['update_time'] = time();
    		$addnew = ShopCartModel::addCart($newshop);
    		$shopcart = ShopCartModel::getShopCart($uid);
    		$shopcart['item'] = json_decode($shopcart['item'],true);
    		return $shopcart;
    	}else{
    		if (!empty($res['item'])) {
    			$res['item'] = json_decode($res['item'],true);
                $res['iteminfo'] = $this->getModelInfo($res['item']);
    		}
            unset($res['item']);
    		return $res;
    	}
    }

    private function getModelInfo($data)
    {   
        $info = [];
        foreach ($data as $value) {
            $modelinfo = ProductModel::getModelById($value['model_id']);
            $modelinfo['count'] = $value['count'];
            array_push($info,$modelinfo);
        }
        return $info;
    }
}