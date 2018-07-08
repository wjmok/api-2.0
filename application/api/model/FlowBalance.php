<?php
namespace app\api\model;
use think\Model;

/*余额流水*/
class FlowBalance extends BaseModel{

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //关联order表
    public function order()
    {
        return $this->hasOne('Order', 'id', 'order_id');
    }

	//记录流水
    public static function addFlow($data){
    	$flow = new FlowBalance();
        $res = $flow->allowField(true)->save($data);
        return $res;
    }

    //软删除流水
    public static function delFlow($id)
    {
        $data['status'] = -1;
        $data['delete_time'] = time();
        $flow = new FlowBalance();
        $res = $flow->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }
    
    //获取流水列表
    public static function getFlowList($data){
        $flow = new FlowBalance();
        $map = $data['map'];
        $map['status'] = 1;
        $res = $flow->where($map)->with('user')->with('order')->order('create_time','desc')->select();
        return $res;
    }
    
    //获取流水列表(分页)
    public static function getFlowListToPaginate($data){
        $flow = new FlowBalance();
        $map = $data['map'];
        $map['status'] = 1;
        $res = $flow->where($map)->with('user')->with('order')->order('create_time','desc')->paginate($data['pagesize']);
        return $res;
    }

    public static function getFlowInfo($id)
    {
        $flow = new FlowBalance();
        $res = $flow->where('id','=',$id)->find();
        return $res;
    }
}