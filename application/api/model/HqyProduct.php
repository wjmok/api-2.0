<?php
namespace app\api\model;
use think\Model;

//惠权益项目用户提交商品
class HqyProduct extends BaseModel{

    //关联商品类别
    public function category()
    {
        return $this->hasOne('Category', 'id', 'category_id');
    }

    //关联提交用户信息
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //关联操作者信息
    public function admin()
    {
        return $this->hasOne('Admin', 'id', 'check_user')->field('id,name,phone');
    }

    //新增发布商品
    public static function addProduct($data){
        $product = new HqyProduct();
        $res = $product->allowField(true)->save($data);
        return $res;
    }

    //更新发布商品
    public static function updateProduct($id,$data){
        $product = new HqyProduct();
        $res = $product->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除发布商品
    public static function delProduct($id){
        $product = new HqyProduct();
        $data['delete_time'] = time();
        $data['status'] = -1;
        $res = $product->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取发布商品列表
    public static function getProductList($data){
        $product = new HqyProduct();
        $map = $data['map'];
        $map['status'] = 1;
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $product->where($map)->where('name','like','%'.$name.'%')->with('user')->with('category')->select();
        }else{
            $list = $product->where($map)->with('user')->with('category')->select();
        }
        return $list;
    }
    
    //获取发布商品列表（带分页）
    public static function getProductListToPaginate($data){
        $product = new HqyProduct();
        $map = $data['map'];
        $map['status'] = 1;
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $product->where($map)->where('name','like','%'.$name.'%')->with('user')->with('category')->paginate($data['pagesize']);
        }else{
            $list = $product->where($map)->with('user')->with('category')->paginate($data['pagesize']);
        }
        return $list;
    }

    //获取指定发布商品信息
    public static function getProductInfo($id){
        $product = new HqyProduct();
        $res = $product->where('id','=',$id)->with('user')->with('category')->find();
        return $res;
    }
}