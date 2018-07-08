<?php
namespace app\api\service;

use app\api\model\HqyProduct as HqyProductModel;
use app\api\model\User as UserModel;
use app\api\model\Product as ProdcutInfoModel;
use app\api\service\Token as TokenService;
use think\Cache;

class HqyProduct{

    public function addinfo($data){

        if (isset($data['mainImg'])) {
            $data['mainImg']=json_encode($data['mainImg']);
        }else{
            $data['mainImg']=json_encode([]);
        }
        $data['category_id'] = $data['category_id'];
        $data['create_time'] = time();
        $data['status'] = 1;
        // $userinfo = TokenService::getUserinfo($data['token']);
        // $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        // $data['user_id'] = $userinfo['id'];
        $data['thirdapp_id'] = 23;
        $data['user_id'] = 2;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $admininfo = Cache::get('info'.$token);
        //获取预约信息
        $productinfo = HqyProductModel::getProductInfo($id);
        if ($productinfo) {
            if ($admininfo['thirdapp_id']!=$productinfo['thirdapp_id']) {
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

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
            if ($list['data'][$key]['category']) {
                $list['data'][$key]['category']['img']=json_decode($value['category']['img'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
            if ($list[$key]['category']) {
                $list[$key]['category']['img']=json_decode($value['category']['img'],true);
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg'] = json_decode($info['mainImg'],true);
        if ($info['user']) {
            $info['user']['headimgurl'] = json_decode($info['user']['headimgurl'],true);
        }
        if ($info['category']) {
            $info['category']['img'] = json_decode($info['category']['img'],true);
        }
        return $info;
    }

    public function getProductList($info)
    {
        if (isset($info['map']['username'])) {
            $usermap['map']['username'] = $info['map']['username'];
            $usermap['map']['thirdapp_id'] = $info['map']['thirdapp_id'];
            $userlist = UserModel::getUserList($usermap);
            if ($userlist) {
                $userIDs = array();
                foreach ($userlist as $item) {
                    array_push($userIDs, $item['id']);
                }
                $info['map']['passage2'] = array('in',$userIDs);
            }
            unset($info['map']['username']);
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ProdcutInfoModel::getProductListToPaginate($info);
            foreach ($list['data'] as $key => $value) {
                if ($list['data'][$key]['passage2']!='') {
                    $list['data'][$key]['userinfo'] = UserModel::getUserInfo($value['passage2']);
                }
            }
        }else{
            $list = ProdcutInfoModel::getProductList($info);
            foreach ($list['data'] as $key => $value) {
                if ($list[$key]['passage2']!='') {
                    $list[$key]['userinfo'] = UserModel::getUserInfo($value['passage2']);
                }
            }
        }
        return $list;
    }
}