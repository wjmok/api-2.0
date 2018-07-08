<?php

namespace app\api\service;
use think\Model;
use app\api\model\Admin;
//admin用户token获取
class AdminToken extends Token{
    public static function getToken($name,$password){
        
        $token=md5($name.$password.time());
        return $token;
    }
}
