<?php
namespace app\api\model;
use think\Model;

class InvitationRemark extends BaseModel{

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //关联user表
    public function touser()
    {
        return $this->hasOne('User', 'id', 'to_user_id');
    }

    //关联invitation表
    public function invitation(){
        return $this->hasOne('Invitation','id','invitation_id')->field('id,title,description');
    }

    //新增回帖
    public static function addRemark($data){
        $remark = new InvitationRemark();
        $res = $remark->allowField(true)->save($data);
        return $res;
    }

    //更新回帖
    public static function updateRemark($id,$data){
        $remark = new InvitationRemark();
        $data['update_time'] = time();
        $res = $remark->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除回帖
    public static function delRemark($id){
        $remark = new InvitationRemark();
        $data['delete_time'] = time();       
        $data['status'] = -1;       
        $res = $remark->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取回帖列表
    public static function getRemarkList($data)
    {
        $remark = new InvitationRemark();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $remark->where($map)->order('listorder','desc')->order('create_time','desc')->with('user')->with('touser')->with('invitation')->select();
        return $list;
    }
    
    //获取回帖列表（带分页）
    public static function getRemarkListToPaginate($data)
    {
        $remark = new InvitationRemark();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $remark->where($map)->order('listorder','desc')->order('create_time','desc')->with('user')->with('touser')->with('invitation')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定回帖信息
    public static function getRemarkInfo($id)
    {
        $remark = new InvitationRemark();
        $res = $remark->where('id','=',$id)->with('user')->with('touser')->with('invitation')->find();
        return $res;
    }
}