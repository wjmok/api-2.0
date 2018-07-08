<?php
namespace app\api\model;
use think\Model;

//商家模块
class Merchant extends BaseModel{

    //新增商家
    public static function addMerchant($data){
        $merchant = new Merchant();
        $res = $merchant->allowField(true)->save($data);
        return $merchant->id;
    }

    //更新商家
    public static function updateMerchant($id,$data){
        $merchant = new Merchant();
        $data['update_time'] = time();
        $res = $merchant->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除商家
    public static function delMerchant($id){
        $merchant = new Merchant();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $merchant->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取商家列表
    public static function getMerchantList($data){
        $merchant = new Merchant();
        $map = $data['map'];
        $map['status'] = 1;
        if(isset($map['name'])){
            $name = $map['name'];
            unset($map['name']);
            $list = $merchant->where($map)->where('name','like','%'.$name.'%')->order('listorder','desc')->select();
        }else{
            $list = $merchant->where($map)->order('listorder','desc')->select();
        }
        return $list;
    }
    
    //获取商家列表（带分页）
    public static function getMerchantListToPaginate($data){
        $merchant = new Merchant();
        $map = $data['map'];
        $map['status'] = 1;
        if(isset($map['name'])){
            $name = $map['name'];
            unset($map['name']);
            $list = $merchant->where($map)->where('name','like','%'.$name.'%')->order('listorder','desc')->paginate($data['pagesize']);
        }else{
            $list = $merchant->where($map)->order('listorder','desc')->paginate($data['pagesize']);
        }
        return $list;
    }

    //获取指定商家信息
    public static function getMerchantInfo($id){
        $merchant = new Merchant();
        $res = $merchant->where('id','=',$id)->find();
        return $res;
    }
}