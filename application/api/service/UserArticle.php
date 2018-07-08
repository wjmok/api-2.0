<?php
namespace app\api\service;
use app\api\model\Article as ArticleModel;

class UserArticle{
    
    public function addinfo($data){
        unset($data['version']);
        if(isset($data['img'])){
            $data['img']=json_encode($data['img']);
        }else{
            $data['img']=json_encode([]);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg']=json_encode($data['bannerImg']);
        }else{
            $data['bannerImg']=json_encode([]);
        }
        $data['status']=1;
        $data['create_time']=time();
        return $data;
    }

    public function initTree($data){
        unset($data['version']);
        $data['status'] = 1;
        return $data;
    }

    //验证thirdapp_id类型
    public static function checkThirdAppId($num){
        if(is_numeric($num)&&is_int($num+0)&&($num+0)>0) {
            return 1;
        }else{
            return -1;
        }
    }

    public static function initHomeInfo($data)
    {
        if (!isset($data['thirdapp_id'])) {
            return 1;
        }
        unset($data['version']);
        return $data;
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