<?php
namespace app\api\model;
use think\Model;

class Invitation extends BaseModel{

    //关联invitation_menu表
    public function menu()
    {
        return $this->hasOne('InvitationMenu', 'id', 'invitation_id');
    }

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //新增帖子
    public static function addInvitation($data){
        $invitation = new Invitation();
        $res = $invitation->allowField(true)->save($data);
        return $res;
    }

    //更新帖子
    public static function updateInvitation($id,$data){
        $invitation = new Invitation();
        $data['update_time'] = time();
        $res = $invitation->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除帖子
    public static function delInvitation($id){
        $invitation = new Invitation();
        $data['delete_time'] = time();       
        $data['status'] = -1;       
        $res = $invitation->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取帖子列表
    public static function getInvitationList($data)
    {
        $invitation = new Invitation();
        $map = $data['map'];
        $map['status'] = 1;
        if (isset($map['title'])) {
            $title = $map['title'];
            unset($map['title']);
            $list = $invitation->where($map)->where('title','like','%'.$title.'%')->order('listorder','desc')->order('create_time','desc')->with('menu')->with('user')->select();
        }else{
            $list = $invitation->where($map)->order('listorder','desc')->order('create_time','desc')->with('menu')->with('user')->select();
        }
        
        return $list;
    }
    
    //获取帖子列表（带分页）
    public static function getInvitationListToPaginate($data)
    {
        $invitation = new Invitation();
        $map = $data['map'];
        $map['status'] = 1;
        if (isset($map['title'])) {
            $title = $map['title'];
            unset($map['title']);
            $list = $invitation->where($map)->where('title','like','%'.$title.'%')->order('listorder','desc')->order('create_time','desc')->with('menu')->with('user')->paginate($data['pagesize']);
        }else{
            $list = $invitation->where($map)->order('listorder','desc')->order('create_time','desc')->with('menu')->with('user')->paginate($data['pagesize']);
        }
        return $list;
    }

    //获取指定帖子信息
    public static function getInvitationInfo($id)
    {
        $invitation = new Invitation();
        $res = $invitation->where('id','=',$id)->with('menu')->with('user')->find();
        return $res;
    }
}