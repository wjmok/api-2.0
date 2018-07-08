<?php
namespace app\api\model;
use think\Model;

class Book extends BaseModel{

    //新增预约
    public static function addBook($data){
        $book = new Book();
        $res = $book->allowField(true)->save($data);
        return $res;
    }

    //更新预约
    public static function updateBook($id,$data){
        $book = new Book();
        $data['update_time'] = time();
        $res = $book->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除预约
    public static function delBook($id){
        $book = new Book();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $book->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取预约列表
    public static function getBookList($data){
        $book = new Book();
        $map = $data['map'];
        $map['status']=1;
        $list = $book->where($map)->order('start_time','asc')->select();
        return $list;
    }
    
    //获取预约列表（带分页）
    public static function getBookListToPaginate($data){
        $book = new Book();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $book->where($map)->order('start_time','asc')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定预约信息
    public static function getBookInfo($id){
        $book = new Book();
        $res = $book->where('id','=',$id)->find();
        return $res;
    }
}