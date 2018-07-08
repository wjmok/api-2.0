<?php
namespace app\api\model;
use think\Model;

class InvitationFavor extends BaseModel{

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //关联invitation表
    public function invitation(){
        return $this->hasOne('Invitation','id','invitation_id')->field('id,title,description');
    }

    //新增点赞
    public static function addFavor($data){
        $favor = new InvitationFavor();
        $res = $favor->allowField(true)->save($data);
        return $res;
    }

    //更新点赞
    public static function updateFavor($id,$data){
        $favor = new InvitationFavor();
        $res = $favor->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取点赞列表
    public static function getFavorList($data)
    {
        $favor = new InvitationFavor();
        $map = $data['map'];
        $map['favor_status'] = 1;
        $list = $favor->where($map)->order('favor_time','desc')->with('user')->with('invitation')->select();
        return $list;
    }
    
    //获取点赞列表（带分页）
    public static function getFavorListToPaginate($data)
    {
        $favor = new InvitationFavor();
        $map = $data['map'];
        $map['favor_status'] = 1;
        $list = $favor->where($map)->order('favor_time','desc')->with('user')->with('invitation')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定点赞信息
    public static function getFavorInfo($id)
    {
        $favor = new InvitationFavor();
        $res = $favor->where('id','=',$id)->with('user')->with('invitation')->find();
        return $res;
    }

    //通过条件获取指定点赞信息
    public static function getFavorInfoByMap($map)
    {
        $favor = new InvitationFavor();
        $res = $favor->where($map)->with('user')->with('invitation')->find();
        return $res;
    }
}