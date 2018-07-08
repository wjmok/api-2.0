<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/22
 * Time: 18:13
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\Member as MemberModel;
use app\api\service\Member as MemberService;
use think\Request as Request;
use think\Cache;

/**
 * 后端会员模块
 */
class Member extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editMember,delMember,getInfo']
    ];

    public function editMember(){
        $data = Request::instance()->param();
        $memberservice=new MemberService();
        $checkinfo = $memberservice->checkinfo($data['token'],$data['id']);
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
                    'msg' => '会员信息不存在',
                    'solelyCode' => 214000
                ]);
        }
        $updateinfo = $memberservice->formatImg($data);
        $res = MemberModel::updateMember($data['id'],$updateinfo);
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

    public function delMember()
    {
        $data = Request::instance()->param();
        $memberservice=new MemberService();
        $checkinfo = $memberservice->checkinfo($data['token'],$data['id']);
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
                    'msg' => '会员信息不存在',
                    'solelyCode' => 214000
                ]);
        }
        $res = MemberModel::delMember($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除会员成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除会员失败',
                'solelyCode' => 214005
            ]);
        }
    }

    public function getList()
    {
        $data=Request::instance()->param();
        $memberservice=new MemberService();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo=Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id']=$userinfo->thirdapp_id;
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

    public function getInfo()
    {
        $data=Request::instance()->param();
        $userinfo=Cache::get('info'.$data['token']);
        $info['thirdapp_id']=$userinfo->thirdapp_id;
        $res=MemberModel::getMemberInfo($data['id']);
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
            $memberservice=new MemberService();
            $res = $memberservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '会员信息不存在',
                'solelyCode' => 214000
            ]);
        }
    }

    public function getTree()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $data['searchItem']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info = preAll2($data);
        if (isset($data['parentid'])) {
            $info['startid'] = $data['parentid'];
        }
        $tree = MemberModel::getMemberTree($info);
        return $tree;
    }
}