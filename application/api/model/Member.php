<?php
namespace app\api\model;
use think\Model;
use app\api\service\UserMember as UserMemberService;

class Member extends BaseModel{

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //新增会员
    public static function addMember($data){
        $member = new Member();
        $res=$member->allowField(true)->save($data);
        return $res;
    }

    //更新会员
    public static function updateMember($id,$data){
        $member = new Member();
        $data['update_time']=time();
        $res=$member->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除会员
    public static function delMember($id){
        $member = new Member();
        $data['delete_time']=time();
        $data['status']=-1; 
        $res=$member->allowField(true)->save($data,['id'=>$id]);
        return $res; 
    }

    //获取会员列表
    public static function getMemberList($data){
        $member = new Member();
        $map=$data['map'];
        $map['status']=1;
        $list=$member->where($map)->order('create_time','desc')->with('user')->select();
        return $list;
    }
    
    //获取会员列表（带分页）
    public static function getMemberListToPaginate($data){
        $member = new Member();
        $map=$data['map'];
        $map['status']=1;
        $list=$member->where($map)->order('create_time','desc')->with('user')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定会员信息
    public static function getMemberInfo($id){
        $member = new Member();
        $res=$member->where('id','=',$id)->with('user')->find();
        return $res;
    }

    //通过user_id获取指定会员信息
    public static function getMemberInfoByUser($id){
        $member = new Member();
        $res=$member->where('user_id','=',$id)->with('user')->find();
        return $res;
    }

    public static function getMemberTree($data)
    {
        $member = new Member();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $member->where($map)->order('create_time','desc')->with('user')->select();
        $listTree = array();
        foreach($list as $key => $v) {
            $listTree[$key]['id'] = $v['id'];
            $listTree[$key]['parent_id'] = $v['parent_id'];
            $listTree[$key]['userid'] = $v['user']['id'];
            $listTree[$key]['nickname'] = $v['user']['nickname'];
            $listTree[$key]['description'] = $v['description'];
            $listTree[$key]['rebateMoney'] = $v['rebateMoney'];
            $listTree[$key]['withdrawCash'] = $v['withdrawCash'];
            $listTree[$key]['QRcode'] = json_decode($v['QRcode'],true);
        }
        if (isset($data['startid'])) {
            $root = $data['startid'];
        }else{
            $root = 0;
        }
        //生成tree
        $tree = clist_to_tree($listTree,'id','parent_id','child',$root);
        return $tree;
    }
}