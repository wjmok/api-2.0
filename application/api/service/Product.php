<?php
namespace app\api\service;

use app\api\model\Category as CategoryModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\Product as ProductModel;
use think\Cache;

class Product{

    public function getThirdAppId($data){

        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        //查询category_id
        if (isset($data['category_id'])) {
            $categorymodel = new CategoryModel();
            $categoryInfo = CategoryModel::getCategoryInfo($data['category_id']);
            if($categoryInfo['status']==-1){
                return -1;
            }
            if($categoryInfo['thirdapp_id']!=$userinfo->thirdapp_id){
                return -2;
            }
        }
        return $userinfo->thirdapp_id;
    }

    public function addinfo($data){

        if (isset($data['mainImg'])) {
            $res['mainImg']=json_encode($data['mainImg']);
        }else{
            $res['mainImg']=json_encode([]);
        }
        if (isset($data['bannerImg'])) {
            $res['bannerImg']=json_encode($data['bannerImg']);
        }else{
            $res['bannerImg']=json_encode([]);
        }
        $res['name'] = $data['name'];
        $res['category_id'] = $data['category_id'];
        $res['content'] = $data['content'];
        $res['view_count'] = 0;
        $res['listorder'] = 0;
        $res['create_time'] = time();
        $res['status'] = 1;
        
        return $res;
    }

    public function updateinfo($data){
        unset($data['version']);
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg'] = initimg($data['bannerImg']);
        }
        $data['update_time']=time();
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取预约信息
        $productinfo = ProductModel::getProductInfo($id);
        if ($productinfo) {
            if ($userinfo['thirdapp_id']!=$productinfo['thirdapp_id']) {
                return -1;
            }
            if ($productinfo['status']==-1) {
                return -2;
            }
        }else{
            return -3;
        }
        return 1;
    }
}