<?php
namespace app\api\model;
use think\Model;
use app\api\service\InvitationMenu as InvitationMenuService;

class InvitationMenu extends BaseModel{

    //新增帖子类型
    public static function addMenu($data){
        $invitationmenu = new InvitationMenu();
        $res = $invitationmenu->allowField(true)->save($data);
        return $res;
    }

    //更新帖子类型
    public static function updateMenu($id,$data){
        $invitationmenu = new InvitationMenu();
        $data['update_time'] = time();
        $res = $invitationmenu->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除帖子类型
    public static function delMenu($id){
        $invitationmenu = new InvitationMenu();
        $data['delete_time'] = time();       
        $data['status'] = -1;       
        $res = $invitationmenu->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取帖子类型层级树
    public static function getMenuTree($info){
        $invitationmenu = new InvitationMenu();
        $info['map']['status'] = 1;
        $list = $invitationmenu->where($info['map'])->order('listorder','desc')->select();
        $listTree = array();
        foreach($list as $key => $v) {
            $listTree[$key]['id']=$v['id'];
            $listTree[$key]['parentid']=$v['parentid'];
            $listTree[$key]['listorder']=$v['listorder'];
            $listTree[$key]['name']=$v['name'];
            $listTree[$key]['description']=$v['description'];
            $listTree[$key]['description']=$v['description'];
            $listTree[$key]['mainImg']=json_decode($v['mainImg'],true);
            $listTree[$key]['bannerImg']=json_decode($v['bannerImg'],true);
        }
        if (isset($info['parentid'])) {
            $rootid = $info['parentid'];
        }else{
            $rootid = 0;
        }
        //生成tree
        $invitationservice = new InvitationMenuService();
        $tree = $invitationservice->clist_to_tree($listTree,$pk='id',$pid='parentid',$child='child',$root=$rootid);
        return $tree;
    }

    //获取帖子类型列表
    public static function getMenuList($data)
    {
        $invitationmenu = new InvitationMenu();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $invitationmenu->where($map)->order('listorder','desc')->order('create_time','desc')->select();
        return $list;
    }
    
    //获取帖子类型列表（带分页）
    public static function getMenuListToPaginate($data)
    {
        $invitationmenu = new InvitationMenu();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $invitationmenu->where($map)->order('listorder','desc')->order('create_time','desc')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定帖子类型信息
    public static function getMenuInfo($id)
    {
        $invitationmenu = new InvitationMenu();    
        $res = $invitationmenu->where('id','=',$id)->find();
        return $res;
    }
}