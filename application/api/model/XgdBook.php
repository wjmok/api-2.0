<?php
namespace app\api\model;
use think\Model;

class XgdBook extends BaseModel{

    //关联活动类别
    public function menu()
    {
        return $this->hasOne('XgdBookmenu', 'id', 'menu_id');
    }

    //关联校区信息
    public function campus()
    {
        return $this->hasOne('XgdBookmenu', 'id', 'campus_id');
    }

    //新增预约
    public static function addBook($data){
        $book = new XgdBook();
        $res = $book->allowField(true)->save($data);
        return $book->id;
    }

    //更新预约
    public static function updateBook($id,$data){
        $book = new XgdBook();
        $res = $book->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除预约
    public static function delBook($id){
        $book = new XgdBook();
        $data['delete_time'] = time();
        $data['status'] = -1;
        $res = $book->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取预约列表
    public static function getBookList($data){
        $book = new XgdBook();
        if (isset($data['sort'])) {
            $order='start_time'.' '.$data['sort'];
        }else{
            $order='start_time asc';
        }
        $map = $data['map'];
        $map['status'] = 1;
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $book->where($map)->where('name','like','%'.$name.'%')->order('listorder','desc')->order($order)->with('menu')->with('campus')->select();
        }else{
            $list = $book->where($map)->order('listorder','desc')->order($order)->with('menu')->with('campus')->select();
        }
        return $list;
    }
    
    //获取预约列表（带分页）
    public static function getBookListToPaginate($data){
        $book = new XgdBook();
        if (isset($data['sort'])) {
            $order='start_time'.' '.$data['sort'];
        }else{
            $order='start_time asc';
        }
        $map = $data['map'];
        $map['status'] = 1;
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list = $book->where($map)->where('name','like','%'.$name.'%')->order('listorder','desc')->order($order)->with('menu')->with('campus')->paginate($data['pagesize']);
        }else{
            $list = $book->where($map)->order('listorder','desc')->order($order)->with('menu')->with('campus')->paginate($data['pagesize']);
        }
        return $list;
    }

    //获取指定预约信息
    public static function getBookInfo($id){
        $book = new XgdBook();
        $res = $book->where('id','=',$id)->with('menu')->with('campus')->find();
        return $res;
    }
}