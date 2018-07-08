<?php
namespace app\api\model;

use think\Model;

class ShopCart extends BaseModel{

	//关联用户表
    public function user(){
        return $this->hasOne('User', 'id', 'user_id');
    }

	public static function addCart($data)
	{
		$shopcart = new ShopCart();
		$res = $shopcart->allowField(true)->save($data);
        return $res;
	}
    
    public static function saveCart($uid,$info)
    {
    	$shopcart = new ShopCart();
		$res = $shopcart->where('user_id', $uid)->update($info);
		return $res;
    }

    public static function getShopCart($uid)
    {
    	$shopcart = new ShopCart();
    	$res = $shopcart->where('user_id',$uid)->with('user')->find();
        if ($res) {
            $res['user']['headimgurl'] = json_decode($res['user']['headimgurl'],true);
        }
    	return $res;
    }
}