<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/29
 * Time: 20:38
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\Invitation as InvitationModel;
use app\api\model\InvitationMenu as InvitationMenuModel;
use app\api\service\UserInvitation as InvitationService;
use think\Request as Request;
use app\api\service\Token as TokenService;

/**
 * 前端帖子模块
 */
class UserInvitation extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editInvitation,delInvitation,getInfo'],
        'checkThirdID' => ['only' => 'getList,getInfo'],
        'checkToken' => ['only' => 'addInvitation,editInvitation,delInvitation,getUserList']
    ];

    public function addInvitation(){
        $data=Request::instance()->param();
        if (!isset($data['invitation_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数InvitationID',
                'solelyCode'=>222008
            ]);
        }
        //获取帖子类别信息
        $menuinfo = InvitationMenuModel::getMenuInfo($data['invitation_id']);
        if (empty($menuinfo)) {
            throw new TokenException([
                'msg' => '帖子类型不存在',
                'solelyCode'=>221000
            ]);
        }
        $invitationservice = new InvitationService();
        $info = $invitationservice->addinfo($data);
        $res = InvitationModel::addInvitation($info);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'新增帖子成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增帖子失败',
                'solelyCode'=>222001
            ]);
        }
    }

    public function editInvitation()
    {
        $data=Request::instance()->param();
        $invitationservice = new InvitationService();
        $checkinfo = $invitationservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该帖子不属于本商户',
                    'solelyCode' => 222003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '帖子已删除',
                    'solelyCode' => 222002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '帖子不存在',
                    'solelyCode' => 222000
                ]);
            case -4:
                throw new TokenException([
                    'msg' => '该帖子不属于你',
                    'solelyCode' => 222007
                ]);
        }
        $updateinfo = $invitationservice->formatImg($data);
        $res = InvitationModel::updateInvitation($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'更新帖子成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '更新帖子失败',
                'solelyCode' => 222004
            ]);
        }
    }

    public function delInvitation()
    {
        $data = Request::instance()->param();
        $invitationservice = new InvitationService();
        $checkinfo = $invitationservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该帖子不属于本商户',
                    'solelyCode' => 222003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '帖子已删除',
                    'solelyCode' => 222002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '帖子不存在',
                    'solelyCode' => 222000
                ]);
            case -4:
                throw new TokenException([
                    'msg' => '该帖子不属于你',
                    'solelyCode' => 222007
                ]);
        }
        $res = InvitationModel::delInvitation($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除帖子成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除帖子失败',
                'solelyCode' => 222005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $invitationservice = new InvitationService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = InvitationModel::getInvitationListToPaginate($info);
            $list = $invitationservice->formatPaginateList($list);
        }else{
            $list = InvitationModel::getInvitationList($info);
            $list = $invitationservice->formatList($list);
        }
        //关联类别评论总数
        if(isset($data['searchItem']['invitation_id']))
        {
            $list['remarknum'] = $invitationservice->getMenuRemark($data['searchItem']['invitation_id']);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取帖子列表失败',
                'solelyCode' => 222006
            ]);
        }
    }

    public function getUserList()
    {
        $data = Request::instance()->param();
        $invitationservice = new InvitationService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = InvitationModel::getInvitationListToPaginate($info);
            $list = $invitationservice->formatPaginateList($list);
        }else{
            $list = InvitationModel::getInvitationList($info);
            $list = $invitationservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取帖子列表失败',
                'solelyCode' => 222006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $res = InvitationModel::getInvitationInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '帖子已删除',
                    'solelyCode' => 222002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该帖子不属于本商户',
                    'solelyCode' => 222003
                ]);
            }
            $invitationservice = new InvitationService();
            $res = $invitationservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '帖子不存在',
                'solelyCode' => 222000
            ]);
        }
    }
}