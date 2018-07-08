<?php
namespace app\api\model;
use think\Model;
use app\api\service\XgdBookmenu as MenuService;

class XgdBookmenu extends BaseModel{

    //新增菜单
    public static function addMenu($data){
        $menu = new XgdBookmenu();
        $res = $menu->allowField(true)->save($data);
        return $res;
    }

    //更新菜单
    public static function updateMenu($id,$data){
        $menu = new XgdBookmenu();
        $data['update_time'] = time();
        $res = $menu->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除菜单
    public static function delMenu($id){
        $menu = new XgdBookmenu();
        $data['delete_time'] = time();
        $data['status'] = -1;
        $res = $menu->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取菜单层级树
    public static function getMenuTree($info){
        $menu = new XgdBookmenu();
        $info['map']['status'] = 1;
        $list = $menu->where($info['map'])->order('listorder','desc')->select();
        $listTree = array();
        foreach($list as $key => $v) {
            $listTree[$key]['id'] = $v['id'];
            $listTree[$key]['parentid'] = $v['parentid'];
            $listTree[$key]['listorder'] = $v['listorder'];
            $listTree[$key]['name'] = $v['name'];
            $listTree[$key]['description'] = $v['description'];
            $listTree[$key]['mainImg'] = json_decode($v['mainImg'],true);
            $listTree[$key]['bannerImg'] = json_decode($v['bannerImg'],true);
        }
        if (isset($info['parentid'])) {
            $rootid = $info['parentid'];
        }else{
            $rootid = 0;
        }
        //生成tree
        $menuservice = new MenuService();
        $tree = $menuservice->clist_to_tree($listTree,$pk='id',$pid='parentid',$child='child',$root=$rootid);
        return $tree;
    }

    //获取菜单列表
    public static function getMenuList($data){
        $menu = new XgdBookmenu();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $menu->where($map)->order('listorder','desc')->order('create_time','desc')->select();
        return $list;
    }
    
    //获取菜单列表（带分页）
    public static function getMenuListToPaginate($data){
        $menu = new XgdBookmenu();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $menu->where($map)->order('listorder','desc')->order('create_time','desc')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定菜单信息
    public static function getMenuInfo($id){
        $menu = new XgdBookmenu();
        $res = $menu->where('id','=',$id)->find();
        return $res;
    }
}