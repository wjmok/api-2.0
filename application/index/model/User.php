<?php
namespace app\index\model;

use think\Model;

class User extends Model
{
	/**
     * 获取用户列表
     */
    public static function getUserList($map)
    {
        $user = new User();
        $list = $user->where($map)->paginate(
                4, true, [
                'page' => 1
            ]);
        return $list;
    }
}