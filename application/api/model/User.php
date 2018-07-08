<?php

namespace app\api\model;


use think\Model;

class User extends BaseModel
{

    public static function dealBlank($data)
    {   
        $standard = ['login_name','password','wx_mainImg','create_time','update_time','delete_time','lastlogintime','thirdapp_id','primary_scope','scope','info_id','status','type','behavior','openid','parent_no'];

        $data = chargeBlank($standard,$data);
        return $data;
        
    }













    //old
    protected $autoWriteTimestamp = true;
//    protected $createTime = ;

    public function orders()
    {
        return $this->hasMany('Order', 'user_id', 'id');
    }

    public function address()
    {
        return $this->hasMany('UserAddress', 'user_id', 'id');
    }

    public static function getByOpenID($openid)
    {
        $user = User::where('openid', '=', $openid)->find();
        return $user;
    }

    public static function getByUnionID($unionid)
    {
        $user = User::where('unionid', '=', $unionid)->find();
        return $user;
    }

    /**
     * 增加新用户
     */
    public static function addUserinfo($data)
    {
        $user = new User();
        $res = $user->allowField(true)->save($data);
        return $user->id;
    }

    /**
     * 更新用户信息
     */
    public static function updateUserinfo($id,$data)
    {
        $user = new User();
        $data['update_time'] = time();
        $res = $user->allowField(true)->save($data,['id' => $id]);
        return $res;
    }

    /**
     * 软删除用户信息
     */
    public static function delUser($id)
    {
        $user = new User();
        $data['delete_time'] = time();
        $data['status'] = -1;
        $res = $user->allowField(true)->save($data,['id' => $id]);
        return $res;
    }

    /**
     * 彻底删除用户信息
     */
    public static function delUserinfo($openid)
    {
        $res = User::destroy(['openid' => $openid]);
        return $res;
    }

    /**
     * 获取用户列表（不分页）
     */
    public static function getUserList($info)
    {
        $user = new User();
        $map = $info['map'];
        $map['status'] = 1;
        if (isset($map['username'])) {
            $username = $map['username'];
            unset($map['username']);
            $list = $user->where($map)->where('username','like','%'.$username.'%')->order('create_time','desc')->select();
        }else{
            $list = $user->where($map)->order('create_time','desc')->select();
        }
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['headimgurl']=json_decode($value['headimgurl'],true);
        }
        return $list;
    }

    /**
     * 获取用户列表（分页）
     */
    public static function getUserListToPaginate($info)
    {
        $user = new User();
        $map = $info['map'];
        $map['status'] = 1;
        if (isset($map['username'])) {
            $username = $map['username'];
            unset($map['username']);
            $list = $user->where($map)->where('username','like','%'.$username.'%')->order('create_time','desc')->paginate($info['pagesize']);
        }else{
            $list = $user->where($map)->order('create_time','desc')->paginate($info['pagesize']);
        }
        $list = $list->toArray();
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['headimgurl']=json_decode($value['headimgurl'],true);
        }
        return $list;
    }

    /**
     * 获取指定单个用户信息
     */
    public static function getUserInfo($uid)
    {
        $user = new User();
        $res = $user->where('id','=',$uid)->find();
        if ($res) {
            $res['headimgurl']=json_decode($res['headimgurl'],true);
        }
        return $res;
    }
}
