<?php
namespace app\api\model;
use think\Model;

//健身房项目打卡记录
class JsfClock extends BaseModel{

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //关联challenge表
    public function challenge()
    {
        return $this->hasOne('JsfChallenge', 'id', 'act_id');
    }

    //关联user表
    public function parent()
    {
        return $this->hasOne('User', 'id', 'parent_id');
    }

    //新增打卡记录
    public static function addClock($data){
        $clock = new JsfClock();
        $res = $clock->allowField(true)->save($data);
        return $res;
    }

    //更新打卡记录
    public static function updateClock($id,$data){
        $clock = new JsfClock();
        $data['update_time'] = time();
        $res = $clock->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除打卡记录
    public static function delClock($id){
        $clock = new JsfClock();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $clock->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取打卡记录列表
    public static function getClockList($data){
        $clock = new JsfClock();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $clock->where($map)->with('user')->with('parent')->with('challenge')->select();
        return $list;
    }
    
    //获取打卡记录列表（带分页）
    public static function getClockListToPaginate($data){
        $clock = new JsfClock();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $clock->where($map)->with('user')->with('parent')->with('challenge')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定打卡记录信息
    public static function getClockInfo($id){
        $clock = new JsfClock();
        $res = $clock->where('id','=',$id)->with('user')->with('parent')->with('challenge')->find();
        return $res;
    }

    public static function getClockByMap($map){
        $clock = new JsfClock();
        $res = $clock->where($map)->find();
        return $res;
    }
}