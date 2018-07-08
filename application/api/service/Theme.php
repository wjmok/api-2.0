<?php
namespace app\api\service;
use app\api\model\Theme as ThemeModel;
use think\Cache;

class Theme{

    public function addinfo($data){
        $data['status']=1;
        $data['create_time']=time();
        if(isset($data['mainImg'])){
            $data['mainImg']=json_encode($data['mainImg']);
        }else{
            $data['mainImg']=json_encode([]);
        }
        $userinfo=Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        return $data;
    }

    public function editinfo($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        //获取用户信息
        $userinfo=Cache::get('info'.$data['token']);
        //获取主题信息
        $themeinfo = ThemeModel::getThemeInfo($data['id']);
        if ($themeinfo) {
            if ($userinfo['thirdapp_id']!=$themeinfo['thirdapp_id']) {
                $res['status'] = -1;
                return $res;
            }
            if ($themeinfo['status']==-1) {
                $res['status'] = -2;
                return $res;
            }
        }else{
            $res['status'] = -3;
            return $res;
        }
        $res['data'] = $data;
        $res['status'] = 1;
        return $res;
    }

    public function deltheme($data) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$data['token']);
        //获取主题信息
        $themeinfo = ThemeModel::getThemeInfo($data['id']);
        if ($themeinfo) {
            if ($userinfo['thirdapp_id']!=$themeinfo['thirdapp_id']) {
                $res['status'] = -1;
                return $res;
            }
            if ($themeinfo['status']==-1) {
                $res['status'] = -2;
                return $res;
            }
        }else{
            $res['status'] = -3;
            return $res;
        }
        $res['status'] = 1;
        return $res;
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