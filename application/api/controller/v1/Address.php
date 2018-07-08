<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2017-11-25
 * Time: 00:06
 */

namespace app\api\controller\v1;

use app\api\model\UserAddress as AddressModel;
use app\api\model\User as UserModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use app\api\service\Address as AddressService;
use app\api\service\Token as TokenService;
use think\Exception;
use app\lib\exception\AddressException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class Address extends CommonController
{   
    protected $beforeActionList = [
        'checkToken' => ['only' => 'addAddress,editAddress,delAddress,getAllByUser,getAddressinfo,setAddress,getDefaultAddress'],
        'checkID' => ['only' => 'editAddress,delAddress,getAddressInfo,setAddress']
    ];

    /**
     * 新增地址
     * @author 董博明
     * @POST data
     * @time 2017.11.25
     */
    public function addAddress()
    {
        $data = Request::instance()->param();
        $addressservice = new AddressService();
        $info = $addressservice->addinfo($data);
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['user_id'] = $userinfo['id'];
        $info['thirdapp_id'] = $userinfo['thirdapp_id'];
        $res = AddressModel::addAddress($info);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '新增地址成功'
            ]);
        }else{
            throw new AddressException([
                'msg' => '保存地址失败',
                'solelyCode' => 210001
            ]);
        }
    }

    /**
     * 修改地址信息
     * @author 董博明
     * @POST data
     * @time 2017.11.25
     */
    public function editAddress()
    {
        $data = Request::instance()->param();
        $id = $data['id'];
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        //校验地址所属
        $checkowner = $this->checkOwnership($id,$userinfo);
        $data['update_time'] = time();
        $res = AddressModel::updateinfo($id,$data);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '修改地址信息成功'
            ]);
        }elseif ($res==0) {
            throw new SuccessMessage([
                'msg' => '未修改任何信息'
            ]);
        }else{
            throw new AddressException([
                'msg' => '保存地址失败',
                'solelyCode' => 210001
            ]);
        }
    }

    /**
     * 软删除地址
     * @author 董博明
     * @POST id
     * @time 2017.11.25
     */
    public function delAddress()
    {
        $data = Request::instance()->param();
        $id = $data['id'];
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        //校验地址所属
        $checkowner = $this->checkOwnership($id,$userinfo);
        $addressservice = new AddressService();
        $info = $addressservice->deladdress($id);
        $res = AddressModel::updateinfo($id,$info);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '删除地址成功'
            ]);
        }elseif ($res==0) {
            throw new SuccessMessage([
                'msg' => '未修改任何信息'
            ]);
        }else{
            throw new AddressException([
                'msg' => '删除地址失败',
                'solelyCode' => 210001
            ]);
        }
    }

    /**
     * 获取指定用户地址列表
     * @author 董博明
     * @time 2017.11.28
     */
    public function getAllByUser()
    {
        $data = Request::instance()->param();
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = AddressModel::getAddressListToPaginate($info);
        }else{
            $list = AddressModel::getAddressList($info);
        }
        return $list;
    }

    /**
     * 获取指定地址信息
     * @author 董博明
     * @get id
     * @time 2017.11.25
     */
    public function getAddressInfo()
    {
        $data = Request::instance()->param();
        $id = $data['id'];
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        //校验地址所属
        $checkowner = $this->checkOwnership($id,$userinfo);
        $res = AddressModel::getAddressInfo($id);
        return $res;
    }

    /**
     * 设置默认地址
     * @author 董博明
     * @get id
     * @time 2017.11.25
     */
    public function setAddress()
    {
        $data = Request::instance()->param();
        $id = $data['id'];
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        //校验地址所属
        $checkowner = $this->checkOwnership($id,$userinfo);
        $addressservice = new AddressService();
        //删除原默认地址
        $cancel = $addressservice->canceldefault($userinfo['id']);
        $res = AddressModel::setDefaultAddress($id);
        if ($res==1) {
            throw new SuccessMessage([
                'msg' => '设置默认地址成功'
            ]);
        }elseif ($res==0) {
            throw new SuccessMessage([
                'msg' => '未修改任何信息'
            ]);
        }else{
            throw new AddressException([
                'msg' => '设置默认地址失败',
                'solelyCode' => 211006
            ]);
        }
    }

    /**
     * 获取指定用户的默认地址
     * @author 董博明
     * @get user_id
     * @time 2017.11.25
     */
    public function getDefaultAddress()
    {
        $data = Request::instance()->param();
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $res = AddressModel::getDefaultAddress($userinfo['id']);
        if (is_object($res)) {
            return $res;
        }else{
            throw new AddressException();
        }
    }

    //校验地址所属
    private function checkOwnership($id,$userinfo){
        $addressinfo = AddressModel::getAddressInfo($id);
        if (is_object($addressinfo)) {
            if ($addressinfo['status']==-1) {
                throw new AddressException([
                    'msg' => '该地址已删除',
                    'solelyCode' => 210005
                ]);
            }
        }else{
            throw new AddressException();
        }
        if ($addressinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
            throw new AddressException([
                'msg' => '修改的项目与您使用的不一致',
                'solelyCode' => 210004
            ]);
        }
        if ($addressinfo['user_id']!=$userinfo['id']) {
            throw new AddressException([
                'msg' => '抱歉，你不能修改别人的地址信息',
                'solelyCode' => 210003
            ]);
        }
    }
}