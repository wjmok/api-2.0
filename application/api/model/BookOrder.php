<?php
namespace app\api\model;
use think\Model;

class BookOrder extends BaseModel{

    //关联book表
    public function book()
    {
        return $this->hasOne('Book', 'id', 'book_id');
    }

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //关联user_card表
    public function card()
    {
        return $this->hasOne('UserCard', 'id', 'card_id');
    }

    //新增预约订单
    public static function addOrder($data){
        $order = new BookOrder();
        $res=$order->allowField(true)->save($data);
        return $res;
    }

    //更新预约订单
    public static function updateOrder($id,$data){
        $order = new BookOrder();
        $res=$order->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除预约订单
    public static function delOrder($id){
        $order = new BookOrder();
        $data['delete_time']=time();
        $data['status']=-1; 
        $res=$order->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取预约订单列表
    public static function getOrderList($data){
        $order = new BookOrder();
        $map=$data['map'];
        $map['status']=1;
        $list=$order->where($map)->order('book_time','desc')->with('book')->with('user')->with('card')->select();
        return $list;
    }
    
    //获取预约订单列表（带分页）
    public static function getOrderListToPaginate($data){
        $order = new BookOrder();
        $map=$data['map'];
        $map['status']=1;
        $list=$order->where($map)->order('book_time','desc')->with('book')->with('user')->with('card')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定预约订单信息
    public static function getOrderInfo($id){
        $order = new BookOrder();
        $res=$order->where('id','=',$id)->with('book')->with('user')->with('card')->find();
        return $res;
    }

    //获取指定用户的指定预约订单
    public static function getOrderInfoByUser($book_id,$user_id)
    {
        $order = new BookOrder();
        $map['book_id'] = $book_id;
        $map['user_id'] = $user_id;
        $res=$order->where($map)->with('book')->with('user')->with('card')->find();
        return $res;
    }
}