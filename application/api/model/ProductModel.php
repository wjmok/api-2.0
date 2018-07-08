<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018-2-28
 * Time: 12:47
 */

namespace app\api\model;

use think\Model;

class ProductModel extends BaseModel
{
    protected $hidden = ['delete_time'];

	//关联商品表
    public function product(){
        return $this->belongsTo('Product', 'product_id', 'id');
    }

    //关联category表
    public function category()
    {
        return $this->belongsTo('Category','category_id','id');
    }

    public static function addModel($data)
    {
        $productmodel = new ProductModel();
        $res = $productmodel->allowField(true)->save($data);
        return $res;
    }

    //批量新增商品型号
    public function saveModelList($data)
    {
    	$productmodel = new ProductModel();
    	$res = $productmodel->allowField(true)->saveAll($data);
        return $res;
    }

    //修改单项型号信息
    public static function updateProductModel($id,$data)
    {
    	$productmodel = new ProductModel();
    	$data['update_time'] = time();
        $res = $productmodel->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除型号
    public static function delModel($id){
        $productmodel = new ProductModel();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $productmodel->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取型号信息（不分页）
    public static function getModelList($data)
    {
        $map = $data['map'];
        $map['status']=1;
        $productmodel = new ProductModel();
        if(isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $productmodel->where($map)->order('listorder','desc')->where('name','like','%'.$name.'%')->with('product')->with('category')->select();
        }else{
            $list = $productmodel->where($map)->order('listorder','desc')->order('create_time','desc')->with('product')->with('category')->select();
        }
        $list = $list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
            if ($list[$key]['category']) {
                $list[$key]['category']['img']=json_decode($value['category']['img'],true);
            }
            if ($list[$key]['product']) {
                $list[$key]['product']['mainImg']=json_decode($value['product']['mainImg'],true);
                $list[$key]['product']['bannerImg']=json_decode($value['product']['bannerImg'],true);
            }
            //关联型号
            $list[$key]['relationmodel'] = self::getRelationModel($value['thirdapp_id'],$value['product_id']);
        }
        return $list;
    }

    //获取型号信息（分页）
    public static function getModelListToPaginate($data)
    {
        $map=$data['map'];
        $map['status']=1;
        $productmodel = new ProductModel();
        if(isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $productmodel->where($map)->order('listorder','desc')->where('name','like','%'.$name.'%')->with('product')->with('category')->paginate($data['pagesize']);
        }else{
            $list = $productmodel->where($map)->order('listorder','desc')->order('create_time','desc')->with('product')->with('category')->paginate($data['pagesize']);
        }
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
            $list['data'][$key]['bannerImg']=json_decode($value['bannerImg'],true);
            if ($list['data'][$key]['category']) {
                $list['data'][$key]['category']['img']=json_decode($value['category']['img'],true);
            }
            if ($list['data'][$key]['product']) {
                $list['data'][$key]['product']['mainImg']=json_decode($value['product']['mainImg'],true);
                $list['data'][$key]['product']['bannerImg']=json_decode($value['product']['bannerImg'],true);
            }
            //关联型号
            $list['data'][$key]['relationmodel'] = self::getRelationModel($value['thirdapp_id'],$value['product_id']);
        }
        return $list;  
    }

    //获取单个型号信息
    public static function getModelById($id)
    {
        $productmodel = new ProductModel();
        $info = $productmodel->where('id','=',$id)->with('product')->with('category')->find();
        //图片信息转数组格式
        if ($info) {
            $info['mainImg']=json_decode($info['mainImg'],true);
            $info['bannerImg']=json_decode($info['bannerImg'],true);
            if ($info['category']) {
                $info['category']['img']=json_decode($info['category']['img'],true);
            }
            if ($info['product']) {
                $info['product']['mainImg']=json_decode($info['product']['mainImg'],true);
                $info['product']['bannerImg']=json_decode($info['product']['bannerImg'],true);
            }
            //关联型号
            $info['relationmodel'] = self::getRelationModel($info['thirdapp_id'],$info['product_id']);
        }
        return $info;
    }

    //获取关联型号信息
    public static function getRelationModel($thirdapp_id,$product_id)
    {
        $productmodel = new ProductModel();
        $map['status'] = 1;
        $map['thirdapp_id'] = $thirdapp_id;
        $map['product_id'] = $product_id;
        $list = $productmodel->where($map)->order('listorder','desc')->select();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
        }
        return $list;
    }

    //根据价格，销量排序，模糊搜索，不分页
    public static function getModelSort($data){
        if($data['sortby']=='multi'){
            $order=[
                'sales_num'=>$data['sort'],
                'price'=>$data['sort']
            ];
        }else if($data['sortby']=='sales'){
            $order='sales_num'.' '.$data['sort'];
        }else if ($data['sortby']=='price') {
            $order='price'.' '.$data['sort'];
        }
        $map = $data['map'];
        $map['status']=1;
        $productmodel = new ProductModel();
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $productmodel->where($map)->order($order)->order('listorder','desc')->where('name','like','%'.$name.'%')->with('product')->with('category')->select();
        }else{
            $list = $productmodel->where($map)->order($order)->order('listorder','desc')->with('product')->with('category')->select();
        }   
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['mainImg']=json_decode($value['mainImg']);
            $list[$key]['bannerImg']=json_decode($value['bannerImg']);
            if ($list[$key]['category']) {
                $list[$key]['category']['img']=json_decode($value['category']['img'],true);
            }
            if ($list[$key]['product']) {
                $list[$key]['product']['mainImg']=json_decode($value['product']['mainImg'],true);
                $list[$key]['product']['bannerImg']=json_decode($value['product']['bannerImg'],true);
            }
        }
        return $list;
    }

    //根据价格，销量排序，模糊搜索，分页
    public static function getModelSortToPaginate($data)
    {
        if($data['sortby']=='multi'){
            $order=[
                'sales_num'=>$data['sort'],
                'price'=>$data['sort']
            ];
        }else if($data['sortby']=='sales'){
            $order='sales_num'.' '.$data['sort'];
        }else if ($data['sortby']=='price') {
            $order='price'.' '.$data['sort'];
        }
        $map = $data['map'];
        $map['status']=1;
        $productmodel = new ProductModel();
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $productmodel->where($map)->order($order)->order('listorder','desc')->where('name','like','%'.$name.'%')->with('product')->with('category')->paginate($data['pagesize']);
        }else{
            $list = $productmodel->where($map)->order($order)->order('listorder','desc')->with('product')->with('category')->paginate($data['pagesize']);
        }   
        $list = $list->toArray();
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['mainImg']=json_decode($value['mainImg']);
            $list['data'][$key]['bannerImg']=json_decode($value['bannerImg']);
            if ($list['data'][$key]['category']) {
                $list['data'][$key]['category']['img']=json_decode($value['category']['img'],true);
            }
            if ($list['data'][$key]['product']) {
                $list['data'][$key]['product']['mainImg']=json_decode($value['product']['mainImg'],true);
                $list['data'][$key]['product']['bannerImg']=json_decode($value['product']['bannerImg'],true);
            }
        }
        return $list;
    }
}