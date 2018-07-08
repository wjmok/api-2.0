<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/30
 * Time: 15:52
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\InvitationFavor as FavorModel;
use app\api\model\Invitation as InvitationModel;
use think\Request as Request;
use app\api\service\Token as TokenService;
use app\api\service\UserInvitationFavor as FavorService;

/**
 * 前端帖子点赞模块
 */
class UserInvitationFavor extends CommonController{

    protected $beforeActionList = [
        'checkToken' => ['only' => 'addFavor,cancelFavor,getFavorForMe']
    ];

    public function addFavor(){
        $data = Request::instance()->param();
        if (!isset($data['invitation_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数InvitationID',
                'solelyCode'=>224004
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
        $favorservice = new FavorService();
        $info = $favorservice->addinfo($data);
        if (isset($info['id'])) {
            $res = FavorModel::updateFavor($info['id'],$info);
        }else{
            $res = FavorModel::addFavor($info);
        }
        if ($res==1) {
            throw new SuccessMessage([
                'msg'=>'点赞成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '点赞失败',
                'solelyCode'=>224001
            ]);
        }
    }

    public function cancelFavor()
    {
        $data=Request::instance()->param();
        if (!isset($data['invitation_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数InvitationID',
                'solelyCode'=>224004
            ]);
        }
        $favorservice = new FavorService();
        $updateinfo = $favorservice->cancelFavor($data);
        if (isset($updateinfo['id'])) {
            $res = FavorModel::updateFavor($updateinfo['id'],$updateinfo);
        }else{
            throw new TokenException([
                'msg' => '您还没有赞过此篇帖子',
                'solelyCode' => 224005
            ]);
        }
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'取消点赞成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '取消点赞失败',
                'solelyCode' => 224002
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $favorservice = new FavorService();
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
                    'solelyCode'=>224004
                ]);
            }
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = FavorModel::getFavorListToPaginate($info);
            $list = $favorservice->formatPaginateList($list);
        }else{
            $list = FavorModel::getFavorList($info);
            $list = $favorservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取点赞列表失败',
                'solelyCode' => 224003
            ]);
        }
    }

    //获取赞我的记录
    public function getFavorForMe()
    {
        $data = Request::instance()->param();
        $favorservice = new FavorService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['invitation_id'] = $favorservice->getRelationByUser($data['token']);
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = FavorModel::getFavorListToPaginate($info);
            $list = $favorservice->formatPaginateList($list);
        }else{
            $list = FavorModel::getFavorList($info);
            $list = $favorservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取点赞列表失败',
                'solelyCode' => 224003
            ]);
        }
    }
}