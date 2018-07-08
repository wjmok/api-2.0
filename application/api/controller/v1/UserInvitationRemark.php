<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/30
 * Time: 11:28
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\InvitationRemark as RemarkModel;
use app\api\model\Invitation as InvitationModel;
use app\api\service\UserInvitationRemark as RemarkService;
use think\Request as Request;
use app\api\service\Token as TokenService;

/**
 * 前端帖子回复模块
 */
class UserInvitationRemark extends CommonController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editRemark,delRemark,getInfo'],
        'checkThirdID' => ['only' => 'getInfo'],
        'checkToken' => ['only' => 'addRemark,editRemark,delRemark']
    ];

    public function addRemark(){
        $data = Request::instance()->param();
        if (!isset($data['invitation_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数InvitationID',
                'solelyCode'=>223006
            ]);
        }
        //获取帖子信息
        $invitationinfo = InvitationModel::getInvitationInfo($data['invitation_id']);
        if (empty($invitationinfo)) {
            throw new TokenException([
                'msg' => '帖子不存在',
                'solelyCode'=>222000
            ]);
        }
        $remarkservice = new RemarkService();
        $info = $remarkservice->addinfo($data);
        $res = RemarkModel::addRemark($info);
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'回复帖子成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '回复帖子失败',
                'solelyCode'=>223001
            ]);
        }
    }

    public function editRemark()
    {
        $data=Request::instance()->param();
        $remarkservice = new RemarkService();
        $checkinfo = $remarkservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该帖子回复不属于本商户',
                    'solelyCode' => 223003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '帖子回复已删除',
                    'solelyCode' => 223002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '帖子回复不存在',
                    'solelyCode' => 223000
                ]);
            case -4:
                throw new TokenException([
                    'msg' => '该回复不属于你',
                    'solelyCode' => 223007
                ]);
        }
        $updateinfo = $remarkservice->formatImg($data);
        $res = RemarkModel::updateRemark($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'更新回复成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '更新帖子回复失败',
                'solelyCode' => 223008
            ]);
        }
    }

    public function delRemark()
    {
        $data = Request::instance()->param();
        $remarkservice = new RemarkService();
        $checkinfo = $remarkservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '该帖子回复不属于本商户',
                    'solelyCode' => 223003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '帖子回复已删除',
                    'solelyCode' => 223002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '帖子回复不存在',
                    'solelyCode' => 223000
                ]);
            case -4:
                throw new TokenException([
                    'msg' => '该回复不属于你',
                    'solelyCode' => 223007
                ]);
        }
        $res = RemarkModel::delRemark($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除帖子回复成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除帖子回复失败',
                'solelyCode' => 222004
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $remarkservice = new RemarkService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        if(isset($data['token'])){
            $userinfo = TokenService::getUserinfo($data['token']);
            if ($userinfo) {
                $info['map']['user_id'] = $userinfo['id'];
                $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
            }else{
                throw new TokenException();
            }
        }else{
            if (isset($data['thirdapp_id'])) {
                $info['map']['thirdapp_id'] = $data['thirdapp_id'];
            }else{
                throw new TokenException([
                    'msg' => '缺少关键参数ThirdAppID',
                    'solelyCode'=>223006
                ]);
            }
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = RemarkModel::getRemarkListToPaginate($info);
            $list = $remarkservice->formatPaginateList($list);
        }else{
            $list = RemarkModel::getRemarkList($info);
            $list = $remarkservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取帖子回复列表失败',
                'solelyCode' => 223005
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $res = RemarkModel::getRemarkInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '帖子回复已删除',
                    'solelyCode' => 223002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '该帖子回复不属于本商户',
                    'solelyCode' => 223003
                ]);
            }
            $remarkservice = new RemarkService();
            $res = $remarkservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '帖子回复不存在',
                'solelyCode' => 223000
            ]);
        }
    }

    //获取评论我的帖子记录
    public function getRemarkForMe()
    {
        $data = Request::instance()->param();
        $remarkservice = new RemarkService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['invitation_id'] = $remarkservice->getRelationByUser($data['token']);
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = RemarkModel::getRemarkListToPaginate($info);
            $list = $remarkservice->formatPaginateList($list);
        }else{
            $list = RemarkModel::getRemarkList($info);
            $list = $remarkservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取帖子回复列表失败',
                'solelyCode' => 223005
            ]);
        }
    }
}