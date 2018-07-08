<?php
namespace app\api\model;
use think\Model;

class XgdBookOrder extends BaseModel{

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

    //关联user信息
    public function user()
    {
        return $this->hasOne('User','id','user_id');
    }

    //关联book预约信息
    public function book()
    {
        return $this->hasOne('XgdBook','id','book_id');
    }

    //新增报名
    public static function addOrder($data){
        $order = new XgdBookOrder();
        $res = $order->allowField(true)->save($data);
        return $res;
    }

    //更新报名
    public static function updateOrder($id,$data){
        $order = new XgdBookOrder();
        $data['update_time'] = time();
        $res = $order->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除报名
    public static function delOrder($id){
        $order = new XgdBookOrder();
        $data['delete_time'] = time();
        $data['status'] = -1;
        $res = $order->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取报名列表
    public static function getOrderList($data){
        $order = new XgdBookOrder();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $order->where($map)->order('book_time','desc')->with('menu')->with('campus')->with('user')->with('book')->select();
        return $list;
    }
    
    //获取报名列表（带分页）
    public static function getOrderListToPaginate($data){
        $order = new XgdBookOrder();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $order->where($map)->order('book_time','desc')->with('menu')->with('campus')->with('user')->with('book')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定报名信息
    public static function getOrderInfo($id){
        $order = new XgdBookOrder();
        $res = $order->where('id','=',$id)->with('menu')->with('campus')->with('user')->with('book')->find();
        return $res;
    }

    //通过条件获取报名信息
    public static function getOrderByMap($map)
    {
        $order = new XgdBookOrder();
        $res = $order->where($map)->with('menu')->with('campus')->with('user')->with('book')->find();
        return $res;
    }

    //获取最近的一条失约记录
    public static function getRecentBadOrder($map)
    {
        $order = new XgdBookOrder();
        $res = $order->where($map)->order('end_time','desc')->find();
        return $res;
    }
}