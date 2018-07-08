<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/22
 * Time: 18:13
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\Member as MemberModel;
use app\api\service\UserMember as UserMemberService;
use app\api\service\Token as TokenService;
use think\Request as Request;

/**
 * 前端会员模块
 */
class UserMember extends CommonController{

    protected $beforeActionList = [
        'checkToken' => ['only' => 'addMember,editMember,getInfo,getMyTeam,getMyTree']
    ];

    public function addMember(){
        $data = Request::instance()->param();
        $memberservice=new UserMemberService();
        $info=$memberservice->addinfo($data);
        $res=MemberModel::addMember($info); 
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增会员失败',
                'solelyCode'=>214001
            ]);
        }
    }

    public function editMember(){
        $data = Request::instance()->param();
        $memberservice=new UserMemberService();
        $checkinfo = $memberservice->checkinfo($data['token']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '会员不属于本商户',
                    'solelyCode' => 214003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '会员已删除',
                    'solelyCode' => 214002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '会员不存在',
                    'solelyCode' => 214000
                ]);
        }
        $updateinfo = $memberservice->formatImg($data);
        // 获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $memberinfo = MemberModel::getMemberInfoByUser($userinfo['id']);
        $res = MemberModel::updateMember($memberinfo['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改会员信息成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改会员信息失败',
                'solelyCode' => 214004
            ]);
        }
    }

    public function getInfo()
    {
        $data=Request::instance()->param();
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['thirdapp_id']=$userinfo['thirdapp_id'];
        $res = MemberModel::getMemberInfoByUser($userinfo['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '会员已删除',
                    'solelyCode' => 214002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '会员不属于本商户',
                    'solelyCode' => 214003
                ]);
            }
            $memberservice=new UserMemberService();
            $res = $memberservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '会员信息不存在',
                'solelyCode' => 214000
            ]);
        }
    }

    public function getMyTeam()
    {
        $data = Request::instance()->param();
        $memberservice = new UserMemberService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //获取用户信息
        $userinfo = TokenService::getUserinfo($data['token']);
        //获取用户会员信息
        $memberinfo = MemberModel::getMemberInfoByUser($userinfo['id']);
        $info['map']['parent_id'] = $memberinfo['id'];
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = MemberModel::getMemberListToPaginate($info);
            $list = $memberservice->formatPaginateList($list);
        }else{
            $list=MemberModel::getMemberList($info);
            $list = $memberservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取会员列表失败',
                'solelyCode' => 214006
            ]);
        }
    }

    public function getMyTree()
    {
        $data = Request::instance()->param();
        $userinfo = TokenService::getUserinfo($data['token']);
        $memberinfo = MemberModel::getMemberInfoByUser($userinfo['id']);
        $data['searchItem']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info = preAll2($data);
        $info['startid'] = $memberinfo['id'];
        $tree = MemberModel::getMemberTree($info);
        return $tree;
    }
}