<?php
namespace app\api\service;
use app\api\model\Wxmenu as WxmenuModel;
use think\Cache;

class Wxmenu{

    public function addinfo($data){
        unset($data['version']);
        $userinfo = Cache::get('info'.$data['token']);
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['status'] = 1;
        $data['create_time'] = time();
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo = Cache::get('info'.$token);
        //获取预约信息
        $menuinfo = WxmenuModel::getMenuInfo($id);
        if ($menuinfo) {
            // if ($userinfo['thirdapp_id']!=$menuinfo['thirdapp_id']) {
            //     return -1;
            // }
            if ($menuinfo['status']==-1) {
                return -2;
            }
        }else{
            return -3;
        }
        return 1;
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