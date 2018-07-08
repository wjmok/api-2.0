<?php
namespace app\api\model;
use think\Model;
use think\Db;
use app\api\service\UserMenu as UserMenuService;

class UserMenu extends BaseModel{

	public static function getMenuTree($info){
        $menu = new Menu();
        if (isset($info['map']['parentid'])) {
            $rootid = $info['map']['parentid'];
            unset($info['map']['parentid']);
        }else{
            $rootid = 0;
        }
        $list = $menu->where($info['map'])->order('listorder','desc')->select();
        $listTree = array();
        foreach($list as $key => $v) {
            $listTree[$key]['id']=$v['id'];
            $listTree[$key]['parentid']=$v['parentid'];
            $listTree[$key]['listorder']=$v['listorder'];
            $listTree[$key]['name']=$v['name'];
            $listTree[$key]['type']=$v['type'];
            $listTree[$key]['description']=$v['description'];
            $listTree[$key]['banner']=json_decode($v['banner'],true);
        }
        //生成tree
        $menuservice = new UserMenuService();
        $tree = $menuservice->clist_to_tree($listTree,$pk='id',$pid='parentid',$child='child',$root=$rootid);
        return $tree;
    }

     //获取指定菜单信息
    public static function getMenuInfo($data){
        $menu = new Menu();
        $res = $menu->where($data)->find();
        if($res){
        	if($res['status']==-1){
        		return -3;
        	}
        	$res['banner']=json_decode($res['banner'],true);
        	return $res;
        }else{
        	return -2;
        } 
    }
}