<?php
namespace app\api\service;
use app\api\model\Collection as CollectionModel;
use app\api\model\ProductModel as ProductModelModel;
use app\api\model\Article as ArticleModel;
use app\api\service\Token as TokenService;

class Collection{
    
    public function addinfo($data)
    {
        unset($data['version']);
        $userinfo = TokenService::getUserinfo($data['token']);
        if (isset($data['model_id'])) {
            $modelinfo = ProdcutModelModel::getModelById($data['model_id']);
            if (empty($modelinfo)) {
                $data['status'] = -1;
                return $data;
            }else{
                if ($modelinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                    $data['status'] = -2;
                    return $data;
                }
                if ($modelinfo['status']==-1) {
                    $data['status'] = -3;
                    return $data;
                }
                //判断是否已收藏
                $mmap['user_id'] = $userinfo['id'];
                $mmap['model_id'] = $data['model_id'];
                $searchModel = CollectionModel::getCollectionByMap($mmap);
                if ($searchModel) {
                    if ($searchModel['status']==1) {
                        $data['status'] = -4;
                        return $data;
                    }
                    if ($searchModel['status']==0) {
                        $update['status'] = 1;
                        $upcollection = CollectionModel::updateCollection($searchModel['id'],$update);
                        if ($upcollection==1) {
                            $data['status'] = 2;
                            return $data;
                        }else{
                            $data['status'] = -5;
                            return $data;
                        }
                    }
                }
                $data['name'] = $modelinfo['name'];
                $data['mainImg'] = json_encode($modelinfo['mainImg']);
            }
        }elseif (isset($data['art_id'])) {
            $artinfo = ArticleModel::getArticleInfo($data['art_id']);
            if (empty($artinfo)) {
                $data['status'] = -1;
                return $data;
            }else{
                if ($artinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                    $data['status'] = -2;
                    return $data;
                }
                if ($artinfo['status']==-1) {
                    $data['status'] = -3;
                    return $data;
                }
                //判断是否已收藏
                $amap['user_id'] = $userinfo['id'];
                $amap['art_id'] = $data['art_id'];
                $searchArt = CollectionModel::getCollectionByMap($amap);
                if ($searchArt) {
                    if ($searchArt['status']==1) {
                        $data['status'] = -4;
                        return $data;
                    }
                    if ($searchArt['status']==0) {
                        $update['status'] = 1;
                        $upcollection = CollectionModel::updateCollection($searchArt['id'],$update);
                        if ($upcollection==1) {
                            $data['status'] = 2;
                            return $data;
                        }else{
                            $data['status'] = -5;
                            return $data;
                        }
                    }
                }
                $data['name'] = $artinfo['title'];
                $data['mainImg'] = json_encode($artinfo['img']);
            }
        }
        $data['user_id'] = $userinfo['id'];
        $data['thirdapp_id'] = $userinfo['thirdapp_id'];
        $data['create_time'] = time();
        $data['status'] = 1;
        return $data;
    }

    public function cancelCollection($data)
    {
        $userinfo = TokenService::getUserinfo($data['token']);
        if (isset($data['model_id'])) {
            //判断是否已收藏
            $mmap['user_id'] = $userinfo['id'];
            $mmap['model_id'] = $data['model_id'];
            $searchModel = CollectionModel::getCollectionByMap($mmap);
            if ($searchModel) {
                if ($searchModel['status']==0) {
                    $data['status'] = -1;
                    return $data;
                }
                if ($searchModel['status']==1) {
                    $update['status'] = 0;
                    $upcollection = CollectionModel::updateCollection($searchModel['id'],$update);
                    if ($upcollection==1) {
                        $data['status'] = 1;
                        return $data;
                    }else{
                        $data['status'] = -2;
                        return $data;
                    }
                }
            }else{
                $data['status'] = -1;
                return $data;
            }
        }
        if (isset($data['art_id'])) {
            //判断是否已收藏
            $amap['user_id'] = $userinfo['id'];
            $amap['art_id'] = $data['art_id'];
            $searchArt = CollectionModel::getCollectionByMap($amap);
            if ($searchArt) {
                if ($searchArt['status']==0) {
                    $data['status'] = -1;
                    return $data;
                }
                if ($searchArt['status']==1) {
                    $update['status'] = 0;
                    $upcollection = CollectionModel::updateCollection($searchArt['id'],$update);
                    if ($upcollection==1) {
                        $data['status'] = 1;
                        return $data;
                    }else{
                        $data['status'] = -2;
                        return $data;
                    }
                }
            }else{
                $data['status'] = -1;
                return $data;
            }
        }
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            if ($list['data'][$key]['productmodel']) {
                $list['data'][$key]['productmodel']['mainImg']=json_decode($value['productmodel']['mainImg'],true);
                $list['data'][$key]['productmodel']['bannerImg']=json_decode($value['productmodel']['bannerImg'],true);
            }
            if ($list['data'][$key]['article']) {
                $list['data'][$key]['article']['img']=json_decode($value['article']['img'],true);
                $list['data'][$key]['article']['bannerImg']=json_decode($value['article']['bannerImg'],true);
            }
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list = $list->toArray();
        foreach($list as $key=>$value){
            if ($list[$key]['productmodel']) {
                $list[$key]['productmodel']['mainImg']=json_decode($value['productmodel']['mainImg'],true);
                $list[$key]['productmodel']['bannerImg']=json_decode($value['productmodel']['bannerImg'],true);
            }
            if ($list[$key]['article']) {
                $list[$key]['article']['img']=json_decode($value['article']['img'],true);
                $list[$key]['article']['bannerImg']=json_decode($value['article']['bannerImg'],true);
            }
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl']=json_decode($value['user']['headimgurl'],true);
            }
        }
        return $list;
    }
}