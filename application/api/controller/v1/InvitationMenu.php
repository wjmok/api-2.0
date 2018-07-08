<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/3/28
 * Time: 20:21
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\InvitationMenu as InvitationMenuModel;
use app\api\service\InvitationMenu as InvitationMenuService;
use think\Request as Request;
use think\Cache;

/**
 * 后端帖子类型模块
 */
class InvitationMenu extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editMenu,delMenu,getInfo']
    ];

    public function addMenu(){
        $data = Request::instance()->param();
        $menuservice = new InvitationMenuService();
        $info = $menuservice->addinfo($data);
        $res = InvitationMenuModel::addMenu($info); 
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '新增帖子类型失败',
                'solelyCode'=>221001
            ]);
        }
    }

    public function editMenu(){
        $data = Request::instance()->param();
        $menuservice = new InvitationMenuService();
        $checkinfo = $menuservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '帖子类型不属于本商户',
                    'solelyCode' => 221003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '帖子类型已删除',
                    'solelyCode' => 221002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '帖子类型信息不存在',
                    'solelyCode' => 221000
                ]);
        }
        if (isset($data['cmenuid'])) {
            $change = $this->changeClass($data['id'],$data['cmenuid'],$data['token']);
            if ($change) {
                unset($data['cmenuid']);
            }
        }
        $updateinfo = $menuservice->formatImg($data);
        $res = InvitationMenuModel::updateMenu($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改帖子类型信息成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改帖子类型信息失败',
                'solelyCode' => 221004
            ]);
        }
    }

    public function delMenu()
    {
        $data = Request::instance()->param();
        $menuservice = new InvitationMenuService();
        $checkinfo = $menuservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '帖子类型不属于本商户',
                    'solelyCode' => 221003
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '帖子类型已删除',
                    'solelyCode' => 221002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '帖子类型信息不存在',
                    'solelyCode' => 221000
                ]);
        }
        $res = InvitationMenuModel::delMenu($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除帖子类型成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除帖子类型失败',
                'solelyCode' => 221005
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $menuservice = new InvitationMenuService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = InvitationMenuModel::getMenuListToPaginate($info);
            $list = $menuservice->formatPaginateList($list);
        }else{
            $list=InvitationMenuModel::getMenuList($info);
            $list = $menuservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取帖子类型列表失败',
                'solelyCode' => 221006
            ]);
        }
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $res = InvitationMenuModel::getMenuInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '帖子类型已删除',
                    'solelyCode' => 221002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '帖子类型不属于本商户',
                    'solelyCode' => 221003
                ]);
            }
            $menuservice = new InvitationMenuService();
            $res = $menuservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '帖子类型信息不存在',
                'solelyCode' => 221000
            ]);
        }
    }

    //获取菜单层级树
    public function getTree(){
        $data = Request::instance()->param();
        $menuservice = new InvitationMenuService();
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        //自定义排序起点
        if (isset($data['searchItem']['parentid'])) {
            $info['parentid'] = $data['searchItem']['parentid'];
        }
        $tree = InvitationMenuModel::getMenuTree($info);
        return $tree;
    }

    //更换两个菜单的子父级关系
    public function changeClass($id,$cmenuid,$token)
    {
        $res = InvitationMenuService::changeClass($id,$cmenuid,$token);
        switch ($res) {
            case 1:
                return true;
            case 2:
                throw new TokenException([
                    'msg' => 'ID或子级菜单ID缺失',
                    'solelyCode' => 221007
                ]);
            case 3:
                throw new TokenException([
                    'msg' => '帖子类型不存在',
                    'solelyCode' => 221000
                ]);
            case 4:
                throw new TokenException([
                    'msg' => '帖子类型已删除',
                    'solelyCode' => 221002
                ]);
            case 5:
                throw new TokenException([
                    'msg' => '帖子类型不属于本商户',
                    'solelyCode' => 221003
                ]);
            case 6:
                throw new TokenException([
                    'msg' => '提交的父级菜单不是提交的子级菜单的父级',
                    'solelyCode' => 221008
                ]);
            default:
                break;
        }
    }
}