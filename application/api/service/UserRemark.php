<?php
namespace app\api\service;
use app\api\model\Article as ArticleModel;
use app\api\model\ProductModel as ProductModel;
use app\api\service\Token as TokenService;

class UserRemark{

    public function checkArt($data)
    {
        $userinfo = TokenService::getUserinfo($data['token']);
        $articleinfo = ArticleModel::getArticleInfo($data['art_id']);
        if ($articleinfo) {
            if ($articleinfo['status']==-1) {
                return -2;
            }
            if ($articleinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                return -3;
            }
        }else{
            return -1;
        }
    }

    public function checkProduct($data)
    {
        $userinfo = TokenService::getUserinfo($data['token']);
        $modelinfo = ProductModel::getModelById($data['model_id']);
        if ($modelinfo) {
            if ($modelinfo['status']==-1) {
                return -2;
            }
            if ($modelinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                return -3;
            }
        }else{
            return -1;
        }
    }

}