<?php

namespace app\api\service;
use think\Cache;
use think\Db;
use app\api\model\Category as CategoryModel;

class Category{

    public function addinfo($data)
    {
        unset($data['version']);
        if(isset($data['img'])){
            $data['img']=json_encode($data['img']);
        }else{
            $data['img']=json_encode([]);
        }
        $data['status']=1;
        $data['create_time']=time();
        return $data;
    }

    public function delCategory($data)
    {
        $data['status'] = -1;
        return $data;
    }

    public function initTree($data)
    {
        $userInfo = Cache::get('info'.$data['token'])->toArray();
        if (isset($data['searchItem'])) {
            $map = $data['searchItem'];
        }
        $map['thirdapp_id'] = $userInfo['thirdapp_id'];
        $map['status'] = 1;
        return $map;
    }

    //获取分类列表条件筛选
    public static function getCategoryList($data,$token)
    {
         if(isset($data['id'])){
            $userInfo=Cache::get('info'.$token)->toArray();
            $categoryscope=$userInfo['category_scope'];
            $categoryscopea=explode(",",$userInfo['category_scope']);
            $mapa=explode(",",$data['id']);
            if(!empty(array_intersect($categoryscopea,$mapa))){
                return 0;//搜索条件不符
            }
        }else{
            $userInfo=Cache::get('info'.$token)->toArray();
            $data['id']=['not in',$userInfo['category_scope']];
        }
        //$data['id']=['in',$data['id']];
        return $data;
    }

    //变更两个类别的子父级
    public static function changeClass($id,$cCategoryid,$token)
    {
        $userinfo = Cache::get('info'.$token);
        if (!isset($id)||!isset($cCategoryid)) {
            return 2;
        }
        //获取两个菜单的信息并校验
        $pcategoryinfo = CategoryModel::getCategoryInfo($id);
        if (is_object($pcategoryinfo)){
            if ($pcategoryinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                return 5;
            }
        }elseif($pcategoryinfo==-1) {
            return 3;
        }elseif ($pcategoryinfo==-2) {
            return 4;
        }
        $ccategoryinfo = CategoryModel::getCategoryInfo($cCategoryid);
        if (is_object($ccategoryinfo)){
            if ($ccategoryinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                return 5;
            }
        }elseif($ccategoryinfo==-1) {
            return 3;
        }elseif ($ccategoryinfo==-2) {
            return 4;
        }
        //判断是否重复提交
        if ($ccategoryinfo['parentid']!=$pcategoryinfo['id']) {
            return 6;
        }
        //变更操作，设置事务
        Db::startTrans();
        try{
            //子级记录父级信息
            $fathercategoryinfo['parentid'] = $pcategoryinfo['parentid'];
            $fathercategoryinfo['update_time'] = time();
            $sparentinfo = CategoryModel::updateCategory($ccategoryinfo['id'],$fathercategoryinfo);
            //父级记录子级信息
            $childcategoryinfo['parentid'] = $ccategoryinfo['id'];
            $childcategoryinfo['update_time'] = time();
            $schildinfo = CategoryModel::updateCategory($pcategoryinfo['id'],$childcategoryinfo);
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