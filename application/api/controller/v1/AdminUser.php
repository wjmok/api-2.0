<?php
/**
 * 后台用户管理
 * Created by 董博明
 * Author: 董博明
 * Date: 2018/3/20
 * Time: 18:37
 */

namespace app\api\controller\v1;
use app\api\model\User as UserModel;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\service\User as UserService;
use think\Request as Request; 
use think\Controller;
use app\api\service\Token as TokenService;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use think\Cache;

class AdminUser extends BaseController
{
    protected $beforeActionList = [
        'checkID' => ['only' => 'editUser,delUser,getUserinfo']
    ];

    public function checkID()
    {
        $data = Request::instance()->param();
        if (!isset($data['id'])) {
            throw new UserException([
                'msg' => '缺少关键参数用户ID',
                'solelyCode' => 205003
            ]);
        }
    }

    /**
     * 修改用户信息
     * @author 董博明
     * @POST data
     * @time 2017.11.4
     */
    public function editUser(){
        $data = Request::instance()->param();
        $userservice = new UserService;
        $info = $userservice->updateinfo($data);
        switch ($info['status']) {
            case 1:
                break;
            case -1:
                throw new UserException();
            case -2:
                throw new UserException([
                    'msg' => '用户已删除',
                    'solelyCode' => 205004
                ]);
            case -3:
                throw new UserException([
                    'msg' => '该用户不属于本商家',
                    'solelyCode' => 205005
                ]);
        }
        $res = UserModel::updateUserinfo($data['id'],$info);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '修改用户信息成功'
            ]);
        }else{
            throw new UserException([
                'msg' => '修改信息失败',
                'solelyCode' => 205002
            ]);
        }
    }

    /**
     * 软删除用户信息
     * @author 董博明
     * @POST userID
     * @time 2017.11.7
     */
    public function delUser()
    {
        $data = Request::instance()->param();
        $userservice = new UserService;
        $info = $userservice->deluser($data);
        switch ($info['status']) {
            case 1:
                break;
            case -1:
                throw new UserException();
            case -2:
                throw new UserException([
                    'msg' => '用户已删除',
                    'solelyCode' => 205004
                ]);
            case -3:
                throw new UserException([
                    'msg' => '该用户不属于本商家',
                    'solelyCode' => 205005
                ]);
        }
        $res = UserModel::delUser($data['id']);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '删除用户信息成功'
            ]);
        }else{
            throw new UserException([
                'msg' => '删除信息失败',
                'solelyCode' => 205006
            ]);
        }
    }

    /**
     * 删除用户信息
     * @author 董博明
     * @POST userID
     * @time 2017.11.7
     */
    public function truedelUser()
    {
        $data = Request::instance()->param();
        $openid = $data['openid'];

        $user = UserModel::delUserinfo($openid);
        return $user;
    }

    /**
     * 获取用户列表
     * @author 董博明
     * @time 2017.11.7
     */
    public function getUserList()
    {
        $data = Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];

        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = UserModel::getUserListToPaginate($info);
        }else{
            $list = UserModel::getUserList($info);
        }
        return $list;
    }

    /**
     * 获取指定用户信息
     * @author 董博明
     * @post token
     * @time 2017.11.7
     */
    public function getUserinfo()
    {
        $data = Request::instance()->param();
        $userinfo = UserModel::getUserInfo($data['id']);
        if (empty($userinfo)) {
            throw new UserException();
        }
        if ($userinfo['status']==-1) {
            throw new UserException([
                'msg' => '用户已删除',
                'solelyCode' => 205004
            ]);
        }
        $admininfo=Cache::get('info'.$data['token']);
        if ($admininfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
           if ($admininfo['promary_scope']<40) {
                throw new OrderException([
                    'msg' => '该用户不属于本商家',
                    'solelyCode' => 205005
                ]);
           }
        }
        return $userinfo;
    }
}