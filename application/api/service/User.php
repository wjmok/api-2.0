<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2017-11-03
 * Time: 15:52
 */

namespace app\api\service;

use app\api\model\User as UserModel;
use think\Cache;

/**
 * 用户相关
 * 管理平台用户信息与操作
 */

class User
{
    /**
     * @param 更新的数据
     * @throws Exception
     */
    public function updateinfo($data)
    {
        $admininfo = Cache::get('info'.$data['token']);
        $userinfo = UserModel::getUserInfo($data['id']);
        if (empty($userinfo)) {
            $data['status'] = -1;
        }else{
            if ($userinfo['status']==-1) {
                $data['status'] = -2;
            }else{
                if ($userinfo['thirdapp_id']!=$admininfo['thirdapp_id']) {
                    $data['status'] = -3;
                }else{
                    if(isset($data['headimgurl'])){
                        $data['headimgurl'] = initimg($data['headimgurl']);
                    }
                    $data['status'] = 1;
                }
            }
        }
        return $data;
    }

    /**
     * @param  软删除用户
     * @throws Exception
     */
    public function deluser($data)
    {
        $admininfo = Cache::get('info'.$data['token']);
        $userinfo = UserModel::getUserInfo($data['id']);
        if (empty($userinfo)) {
            $data['status'] = -1;
        }else{
            if ($userinfo['status']==-1) {
                $data['status'] = -2;
            }else{
                if ($userinfo['thirdapp_id']!=$admininfo['thirdapp_id']) {
                    $data['status'] = -3;
                }else{
                    $data['status'] = 1;
                }
            }
        }
        return $data;
    }
}