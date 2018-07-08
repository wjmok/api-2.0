<?php
namespace app\api\model;
use think\Model;

class ApplyCash extends BaseModel{

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'apply_user_id');
    }

    //关联admin表
    //不能暴漏过多admin信息，需要限制关联字段
    public function admin()
    {
        return $this->hasOne('Admin', 'id', 'deal_user_id')->field('id,name,phone');
    }

    //新增提现申请
    public static function addApply($data){
        $apply = new ApplyCash();
        $res = $apply->allowField(true)->save($data);
        return $res;
    }

    //更新提现申请
    public static function updateApply($id,$data){
        $apply = new ApplyCash();
        $data['update_time'] = time();
        $res = $apply->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除提现申请
    public static function delApply($id){
        $apply = new ApplyCash();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $apply->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取提现申请列表
    public static function getApplyList($data){
        $apply = new ApplyCash();
        $map = $data['map'];
        $map['status']=1;
        $list = $apply->where($map)->order('create_time','desc
            ')->with('user')->with('admin')->select();
        return $list;
    }
    
    //获取提现申请列表（带分页）
    public static function getApplyListToPaginate($data){
        $apply = new ApplyCash();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $apply->where($map)->order('create_time','desc
            ')->with('user')->with('admin')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定提现申请信息
    public static function getApplyInfo($id){
        $apply = new ApplyCash();
        $res = $apply->where('id','=',$id)->with('user')->with('admin')->find();
        return $res;
    }
}