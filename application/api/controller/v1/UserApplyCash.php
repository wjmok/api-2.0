<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/4/12
 * Time: 22:38
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\ApplyCash as ApplyCashModel;
use app\api\service\ApplyCash as ApplyCashService;
use think\Request as Request;
use app\api\service\Token as TokenService;

/**
 * 前端申请提现
 */
class UserApplyCash extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'getInfo'],
        'checkToken' => ['only' => 'addApply,getList,getInfo']
    ];

    public function addApply(){
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        if ($data['apply_count']>$userinfo['user_balance']) {
            throw new TokenException([
                'msg' => '余额不足提现',
                'solelyCode'=>227011
            ]);
        }
        $applyservice = new ApplyCashService();
        $info = $applyservice->addinfo($data);
        $res = ApplyCashModel::addApply($info);
        if ($res==1) {
            $saveflow = $applyservice->saveFlow($data);
            throw new SuccessMessage([
                'msg'=>'成功申请提现',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增提现申请失败',
                'solelyCode'=>227001
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
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['apply_user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ApplyCashModel::getApplyListToPaginate($info);
            $list = $applyservice->formatPaginateList($list);
        }else{
            $list = ApplyCashModel::getApplyList($info);
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
        $userinfo = TokenService::getUserinfo($data['token']);
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
            if ($res['apply_user_id']!=$userinfo['id']) {
                throw new TokenException([
                    'msg' => '提现申请不属于你',
                    'solelyCode' => 227007
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