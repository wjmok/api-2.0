<?php
namespace app\api\controller\v1;
use app\api\model\Menu as MenuModel;
use think\Request as Request;
use think\Controller;
use app\api\service\Menu as MenuService;
use app\api\validate\MenuValidate;
use app\api\validate\MenuUptValidate;
use app\api\controller\BaseController;
use app\lib\exception\MenuException;
use app\lib\exception\ImageException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class Menu extends BaseController{
    //新增菜单
    public function addMenu(){
        $data=Request::instance()->param();
        $validate=new MenuValidate();
        $validate->goCheck();
        $menuservice=new MenuService();
        $info=$menuservice->addinfo($data);
        $res=MenuModel::addMenu($info); 
        if($res>0){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new MenuException([
                'msg' => '添加菜单失败',
                'solelyCode'=>203000
            ]);
        }     
    }

    //修改菜单
    public function editMenu(){
        $data = Request::instance()->param();
        $validate = new MenuUptValidate();
        $validate->goCheck();
        if (isset($data['cmenuid'])) {
            $change = $this->changeClass($data['id'],$data['cmenuid'],$data['token']);
            if ($change) {
                unset($data['cmenuid']);
            }
        }
        $res = MenuModel::updateMenu($data['id'],$data);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改菜单成功',
            ]);
        }elseif ($res==0) {
            throw new SuccessMessage([
                'msg'=>'修改菜单成功',
            ]);
        }else if($res==-1){
            throw new MenuException([
                'msg' => '此菜单不属于该商户',
                'solelyCode' => 203001
            ]);
        }else{
            throw new MenuException([
                'msg' => '修改失败',
                'solelyCode' => 203003
            ]);
        }
    }

    //软删除
    public function delMenu(){
        $data=Request::instance()->param();
        $validate=new MenuUptValidate();
        $validate->goCheck();
        $menuservice=new MenuService();
        $info=$menuservice->delmenu($data);
        $res=MenuModel::delMenu($data['id'],$info);
        if($res==0){
            throw new MenuException([
                'msg' => '此菜单已删除，勿重新操作',
                'solelyCode' => 203004
            ]);
        }else if($res==-1){
            throw new MenuException([
                'msg' => '此菜单不属于该商户',
                'solelyCode' => 203001
            ]);
            
        }else{
            throw new SuccessMessage([
                'msg'=>'修改菜单成功',
            ]);
        }
    }

    //获取菜单层级树
    public function getMenuTree(){
        $data = Request::instance()->param();
        $info = preAll2($data);
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        $tree = MenuModel::getMenuTree($info);
        return $tree;
    }

    //获取菜单列表
    public function getMenuList(){
        $data = Request::instance()->param();
        $menuservice = new MenuService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = MenuModel::getMenuListToPaginate($info,$data['token']);
        }else{
            $list = MenuModel::getMenuList($info,$data['token']);
        }
        if(is_array($list)){
            return $list;
        }else{
            throw new MenuException([
                'msg'=>'id参数权限不符',
                'solelyCode'=>203005
            ]);
        }
    }

    //获取菜单信息
    public function getMenuinfo(){
        $data=Request::instance()->param();
        $validate=new MenuUptValidate();
        $validate->goCheck();
        if(!isset($data['token'])){
            throw new MenuException([
                'msg'=>'必须设置token参数',
                'solelyCode'=>203006
            ]); 
        }
        $userinfo=Cache::get('info'.$data['token']);   
        $info['thirdapp_id']=$userinfo->thirdapp_id;
        $info['id']=$data['id'];
        $res=MenuModel::getMenuInfo($info);
        if(is_object($res)){
            return $res;
        }else if($res==-1){
            throw new MenuException([
                'msg'=>'菜单数据不存在',
                'solelyCode'=>203007
            ]); 
        }else if($res==-2){
            throw new MenuException([
                'msg'=>'菜单已被删除',
                'solelyCode'=>203002
            ]); 
        }else{
            throw new MenuException([
                'msg'=>'此菜单不属于该商户',
                'solelyCode'=>203001
            ]);
        }
    }

    //一键复制项目menu
    /*@string thirdapp_id 本项目ID
     *@string target_id 目标项目ID
     */
    public function copyMenu()
    {
        $data=Request::instance()->param();
        $menumodel = new MenuModel();
        $menulist = $menumodel->getMenuListByThirdID($data['target_id']);
        if (!$menulist) {
            throw new MenuException([
                'msg'=>'目标项目菜单不存在',
                'solelyCode'=>203008
            ]);
        }
        $menuservice = new MenuService();
        $res = $menuservice->copymenu($menulist,$data['thirdapp_id'],$data['token']);
        switch ($res) {
            case 1:
                throw new SuccessMessage([
                    'msg'=>'复制菜单成功',
                ]);
                break;
            case 2:
                throw new MenuException([
                    'msg'=>'菜单生成失败',
                    'solelyCode'=>203009
                ]);
                break;
            default:
                # code...
                break;
        }
    }

    //更换两个菜单的子父级关系
    public function changeClass($id,$cmenuid,$token)
    {
        $res = MenuService::changeClass($id,$cmenuid,$token);
        switch ($res) {
            case 1:
                return true;
            case 2:
                throw new MenuException([
                    'msg' => 'ID或子级菜单ID缺失',
                    'solelyCode' => 203012
                ]);
            case 3:
                throw new MenuException([
                    'msg' => '菜单数据不存在',
                    'solelyCode' => 203007
                ]);
            case 4:
                throw new MenuException([
                    'msg' => '操作菜单已被删除',
                    'solelyCode' => 203002
                ]);
            case 5:
                throw new MenuException([
                    'msg' => '此菜单不属于该商户',
                    'solelyCode' => 203001
                ]);
            case 6:
                throw new MenuException([
                    'msg' => '提交的父级菜单不是提交的子级菜单的父级',
                    'solelyCode' => 203013
                ]);
            default:
                break;
        }
    }
}