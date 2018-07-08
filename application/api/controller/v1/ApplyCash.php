<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/4/12
 * Time: 21:23
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\ApplyCash as ApplyCashModel;
use app\api\service\ApplyCash as ApplyCashService;
use think\Request as Request;
use think\Cache;

/**
 * 后端提现申请模块
 */
class ApplyCash extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'agreeApply,refuseApply,delApply,getInfo']
    ];

    public function agreeApply(){
        $data = Request::instance()->param();
        $admininfo = Cache::get('info'.$data['token']);
        $applyservice = new ApplyCashService();
        $checkinfo = $applyservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '提现申请不属于本商户',
                    'solelyCode' => 227003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '提现申请已删除',
                    'solelyCode' => 227002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '提现申请信息不存在',
                    'solelyCode' => 227000
                ]);
            case -4:
                throw new TokenException([
                    'msg' => '提现申请已处理，请勿重复操作',
                    'solelyCode' => 227012
                ]);
        }
        $updateinfo['apply_status'] = 1;
        $updateinfo['deal_user_id'] = $admininfo['id'];
        $res = ApplyCashModel::updateApply($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'同意提现申请成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '同意提现申请失败',
                'solelyCode' => 227009
            ]);
        }
    }

    public function refuseApply()
    {
        $data = Request::instance()->param();
        $admininfo = Cache::get('info'.$data['token']);
        $applyservice = new ApplyCashService();
        $checkinfo = $applyservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '提现申请不属于本商户',
                    'solelyCode' => 227003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '提现申请已删除',
                    'solelyCode' => 227002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '提现申请信息不存在',
                    'solelyCode' => 227000
                ]);
            case -4:
                throw new TokenException([
                    'msg' => '提现申请已处理，请勿重复操作',
                    'solelyCode' => 227012
                ]);
        }
        $updateinfo['apply_status'] = 2;
        $updateinfo['deal_user_id'] = $admininfo['id'];
        $res = ApplyCashModel::updateApply($data['id'],$updateinfo);
        if($res==1){
            $returnBalance = $applyservice->returnBalance($data);
            throw new SuccessMessage([
                'msg'=>'拒绝提现申请成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '拒绝提现申请失败',
                'solelyCode' => 227010
            ]);
        }
    }

    public function delApply()
    {
        $data = Request::instance()->param();
        $applyservice = new ApplyCashService();
        $checkinfo = $applyservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '提现申请不属于本商户',
                    'solelyCode' => 227003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '提现申请已删除',
                    'solelyCode' => 227002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '提现申请信息不存在',
                    'solelyCode' => 227000
                ]);
            case -4:
                break;
        }
        $res = ApplyCashModel::delApply($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除提现申请成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除提现申请失败',
                'solelyCode' => 227005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $applyservice = new ApplyCashService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ApplyCashModel::getApplyListToPaginate($info);
            $list = $applyservice->formatPaginateList($list);
        }else{
            $list=ApplyCashModel::getApplyList($info);
            $list = $applyservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取提现申请列表失败',
                'solelyCode' => 227006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = ApplyCashModel::getApplyInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '提现申请已删除',
                    'solelyCode' => 227002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '提现申请不属于本商户',
                    'solelyCode' => 227003
                ]);
            }
            $applyservice = new ApplyCashService();
            $res = $applyservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '提现申请不存在',
                'solelyCode' => 227000
            ]);
        }
    }
}