<?php
namespace app\api\service;
use app\api\model\Menu as MenuModel;
use app\api\controller\v1\Image as ImageController;
use think\Exception;
use think\Cache;
use think\Db;

class Menu{

    /**
     * @return array数据表空字段过滤
     */
    public function initinfo(){
        $menudome = array(
            "parentid"    => 0,
            "listorder"   => 0,
            "type"        => 1,
            "status"      => 1,
        );
        return $menudome;
    }

    public function addinfo($data){
        unset($data['version']);
        if(isset($data['banner'])){
            $data['banner']=json_encode($data['banner']);
        }else{
            $data['banner']=json_encode([]);
        }
        $userinfo=Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        $data['status']=1;
        $data['create_time']=time();
        return $data;
    }

    //获取menu列表条件筛选
    public static function getMenuList($data,$token){
        if(isset($data['id'])){
            $userInfo=Cache::get('info'.$token)->toArray();
            $menuscope=$userInfo['menu_scope'];
            $menuscopea=explode(",",$userInfo['menu_scope']);
            $mapa=explode(",",$data['id']);
            if(!empty(array_intersect($menuscopea,$mapa))){
                return 0;//搜索条件不符
            }
        }else{
            $userInfo=Cache::get('info'.$token)->toArray();
            $data['id']=['not in',$userInfo['menu_scope']];
        }
        return $data;
    }

    //软删除菜单
    public function delmenu($data){
        $data['status']=-1;
        return $data;
    }

    //一键复制菜单
    public function copymenu($list,$thirdapp_id,$token)
    {   
        $menumodel = new MenuModel();
        $imagecontroller = new ImageController();
        //第一，复制menu基本信息
        foreach ($list as $k => $v) {
            //复制banner图
            foreach ($v['banner'] as $b => $value) {
                $v['banner'][$b]['url'] = $imagecontroller->copypic($value['url'],$thirdapp_id,$token);
            }
            $newbanner = json_encode($v['banner']);

            $menu = array(
                'name'         =>  $v['name'],
                'parentid'     =>  0,
                'preid'        =>  $v['id'],
                'preparentid'  =>  $v['parentid'],
                'listorder'    =>  $v['listorder'],
                'type'         =>  $v['type'],
                'banner'       =>  $newbanner,
                'thirdapp_id'  =>  $thirdapp_id,
                'create_time'  =>  time(),
                'status'       =>  1,
            );
            $res = $menumodel->addMenu($menu);
            if ($res!=1) {
                return 2;
            }
        }
        //第二，复制原menu的子父级关系
        $newlist = $menumodel->getMenuListByThirdID($thirdapp_id);
        foreach ($newlist as $n => $lvalue) {
            if ($lvalue['preparentid']!=0) {
                $search['preid'] = $lvalue['preparentid'];
                $parentinfo = $menumodel->getSingleMenu($search);
                $update['parentid'] = $parentinfo['id'];
                $updateinfo = $menumodel->updateMenu($lvalue['id'],$update);
            }
        }
        return 1;
    }

    //变更两个菜单的子父级
    public static function changeClass($id,$cmenuid,$token)
    {
        $userinfo=Cache::get('info'.$token);
        if (!isset($id)||!isset($cmenuid)) {
            return 2;
        }
        //获取两个菜单的信息并校验
        $pmenu['id'] = $id;
        $cmenu['id'] = $cmenuid;
        $pmenu['thirdapp_id'] = $cmenu['thirdapp_id'] = $userinfo['thirdapp_id'];
        $pmenuinfo = MenuModel::getMenuInfo($pmenu);
        if (is_object($pmenuinfo)){
        }elseif($pmenuinfo==-1) {
            return 3;
        }elseif ($pmenuinfo==-2) {
            return 4;
        }elseif ($pmenuinfo==-3) {
            return 5;
        }
        $cmenuinfo = MenuModel::getMenuInfo($cmenu);
        if (is_object($cmenuinfo)){
        }elseif($cmenuinfo==-1) {
            return 3;
        }elseif ($cmenuinfo==-2) {
            return 4;
        }elseif ($cmenuinfo==-3) {
            return 5;
        }
        //判断是否重复提交
        if ($cmenuinfo['parentid']!=$pmenuinfo['id']) {
            return 6;
        }
        //变更操作，设置事务
        Db::startTrans();
        try{
            //子级记录父级信息
            $fathermenuinfo['parentid'] = $pmenuinfo['parentid'];
            $fathermenuinfo['update_time'] = time();
            $sparentinfo = MenuModel::updateMenu($cmenuinfo['id'],$fathermenuinfo);
            //父级记录子级信息
            $childmenuinfo['parentid'] = $cmenuinfo['id'];
            $childmenuinfo['update_time'] = time();
            $schildinfo = MenuModel::updateMenu($pmenuinfo['id'],$childmenuinfo);
            Db::commit();
            return 1;
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @return array
     */
    function clist_to_tree($list, $pk='id', $pid = 'parentid', $child = 'child', $root = 0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}