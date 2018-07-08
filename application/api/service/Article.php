<?php
namespace app\api\service;
use app\api\model\Article as ArticleModel;
use think\Cache;
use think\Exception;

class Article{

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

    //更新数据
    public function editinfo($data)
    {
        if(isset($data['img'])){
            $data['img'] = initimg($data['img']);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg'] = initimg($data['bannerImg']);
        }
        $userinfo=Cache::get('info'.$data['token']);
        //获取文章信息
        $articleinfo = ArticleModel::getArticleInfo($data['id']);
        if ($articleinfo) {
            if ($userinfo['thirdapp_id']!=$articleinfo['thirdapp_id']) {
                $res['status'] = -1;
                return $res;
            }
            if ($articleinfo['status']==-1) {
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

    //软删除菜单
    public function delArticle($data){
        $userinfo=Cache::get('info'.$data['token']);
        //获取文章信息
        $articleinfo = ArticleModel::getArticleInfo($data['id']);
        if ($articleinfo) {
            if ($userinfo['thirdapp_id']!=$articleinfo['thirdapp_id']) {
                $res['status'] = -1;
                return $res;
            }
            if ($articleinfo['status']==-1) {
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

    public function initTree($data){
        unset($data['version']);
        $data['status'] = 1;
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