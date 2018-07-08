<?php
namespace app\api\model;
use think\Model;
use app\api\service\Menu as MenuService;
use think\Cache;

class Menu extends BaseModel{

    public function article(){
        return $this->hasMany('Article', 'menu_id', 'id');
    }

    //新增菜单
    public static function addMenu($data){
        $menu=new Menu();
        $res=$menu->allowField(true)->save($data);
        return $res;
    }

    //更新菜单
    public static function updateMenu($id,$data){
        $menu=new Menu();
        if(isset($data['banner'])){
            if($data['banner']=='empty'){
                $data['banner']=json_encode([]);
            }else{
                $data['banner']=json_encode($data['banner']);
            }
        }
        $data['update_time']=time();
        $res=$menu->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除菜单
    public static function delMenu($id,$data){
        $menu=Menu::get($id);
        $userinfo=Cache::get('info'.$data['token']);
        if($menu->thirdapp_id!=$userinfo->thirdapp_id){
            return -1;
        }
        if($menu->status==-1){
            return 0;
        }else{
            $m=new Menu();
            $data['delete_time']=time();            
            $res=$m->allowField(true)->save($data,['id'=>$id]);                  
            return $res; 
        } 
    }

    //获取菜单层级树
    public static function getMenuTree($info){
        $menu = new Menu();
        $info['map']['status'] = 1;
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
        $menuservice = new MenuService();
        $tree = $menuservice->clist_to_tree($listTree,$pk='id',$pid='parentid',$child='child',$root=$rootid);
        return $tree;
    }

   //获取菜单列表
    public static function getMenuList($data,$token){
        $menu=new Menu();
        $map=$data['map'];
        $map['status']=1;
        $map=MenuService::getMenuList($map,$token);
        if($map['id']==0){
            return 0;//搜索条件不符
        }
        $userinfo=Cache::get('info'.$token);        
        $map['thirdapp_id']=$userinfo->thirdapp_id;
        $list=$menu->where($map)->order('listorder','desc')->order('create_time','desc')->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['banner']=json_decode($value['banner'],true);
        }
        return $list;
    }
    
    //获取菜单列表（带分页）
    public static function getMenuListToPaginate($data,$token){
        $menu=new Menu();
        $map=$data['map'];
        $map['status']=1;
        $map=MenuService::getMenuList($map,$token);
        if($map['id']==0){
            return 0;//搜索条件不符
        }
        $userinfo=Cache::get('info'.$token);    
        $map['thirdapp_id']=$userinfo->thirdapp_id;
        $list=$menu->where($map)->order('listorder','desc')->order('create_time','desc')->paginate($data['pagesize']);
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['banner']=json_decode($value['banner'],true);
        }
        return $list;
    }

    //获取指定菜单信息
    public static function getMenuInfo($data){
        $menu=new Menu();    
        $res=$menu->where('id','=',$data['id'])->find();
        if(empty($res)){
            return -1;
        }else if($res['status']==-1){
            return -2;
        }else if($res['thirdapp_id']!=$data['thirdapp_id']){
            return -3;
        }else{
            $res['banner']=json_decode($res['banner'],true);
        }
        return $res;
    }

    public function getSingleMenu($map)
    {
        $menu = new Menu();
        $res = $menu->where($map)->find();
        $res['banner']=json_decode($res['banner'],true);
        return $res;
    }

    public function getMenuListByThirdID($id)
    {
        $menu=new Menu();
        $map['thirdapp_id'] = $id;
        $map['status']=1;
        $list=$menu->where($map)->order('listorder','desc')->order('create_time','desc')->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['banner']=json_decode($value['banner'],true);
        }
        return $list;
    }
}