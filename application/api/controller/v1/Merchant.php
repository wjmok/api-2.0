<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/6/14
 * Time: 18:43
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\Merchant as MerchantModel;
use app\api\model\Admin as AdminModel;
use app\api\service\Merchant as MerchantService;
use app\api\service\Admin as AdminService;
use think\Request as Request;
use think\Cache;

/**
 * 后端商家模块
 */
class Merchant extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editMerchant,delMerchant,getInfo'],
        'checkPrimary' => ['only' => 'addMerchant,editMerchant,delMerchant,getList,getInfo']
    ];

    public function addMerchant()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $checkmerchant = MerchantService::checkIsRepeat($data);
        if ($checkmerchant==-1) {
            throw new TokenException([
                'msg' => '此名称已有商家使用',
                'solelyCode'=>230006
            ]);
        }
        $checkadmin = AdminService::checkIsRepeat($data);
        if ($checkadmin==-3) {
            throw new TokenException([
                'msg' => '此名称已经注册过',
                'solelyCode'=>202001
            ]);
        }
        $merchantService = new MerchantService();
        $info = $merchantService->addinfo($data);
        $res = MerchantModel::addMerchant($info); 
        if($res>0){
            //新增商户账号
            $admininfo = array(
                'name'         => $info['name'],
                'password'     => md5('00000000'),
                'img'          => json_encode([]),
                'merchant_id'  => $res,
                'primary_scope'=> 20,
                'thirdapp_id'  => $userinfo['thirdapp_id'],
                'status'       => 1,
                'create_time'  => time(),
            );
            $addadmin = AdminModel::addFristAdminUser($admininfo);
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增商家失败',
                'solelyCode'=>230001
            ]);
        }
    }

    public function editMerchant()
    {
        $data = Request::instance()->param();
        $merchantService = new MerchantService();
        $checkinfo = $merchantService->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该商家不属于本商户',
                    'solelyCode' => 230003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '商家信息已删除',
                    'solelyCode' => 230002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '商家信息不存在',
                    'solelyCode' => 230000
                ]);
        }
        $updateinfo = $merchantService->formatImg($data);
        $res = MerchantModel::updateMerchant($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改商家信息成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改商家信息失败',
                'solelyCode' => 230004
            ]);
        }
    }

    public function delMerchant()
    {
        $data = Request::instance()->param();
        $merchantService = new MerchantService();
        $checkinfo = $merchantService->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该商家不属于本商户',
                    'solelyCode' => 230003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '商家信息已删除',
                    'solelyCode' => 230002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '商家信息不存在',
                    'solelyCode' => 230000
                ]);
        }
        $res = MerchantModel::delMerchant($data['id']);
        if($res==1){
            //关联删除商家账户
            $adminmap['map']['merchant_id'] = $data['id'];
            $adminlist = AdminModel::getAllAdmin($adminmap);
            if ($adminlist) {
                foreach ($adminlist as $key => $value) {
                    $deladmin = AdminModel::delAdmin($value['id']);
                }
            }
            throw new SuccessMessage([
                'msg'=>'删除商家成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除商家失败',
                'solelyCode' => 230005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $merchantService = new MerchantService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = MerchantModel::getMerchantListToPaginate($info);
            $list = $merchantService->formatPaginateList($list);
        }else{
            $list = MerchantModel::getMerchantList($info);
            $list = $merchantService->formatList($list);
        }
         return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = MerchantModel::getMerchantInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '商家信息已删除',
                    'solelyCode' => 230002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该商家不属于本商户',
                    'solelyCode' => 230003
                ]);
            }
            $merchantService = new MerchantService();
            $res = $merchantService->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '商家信息不存在',
                'solelyCode' => 230000
            ]);
        }
    }
}