<?php
namespace app\api\controller\v1;
use think\Controller;
use app\api\controller\CommonController;
use app\api\model\Collection as CollectionModel;
use app\api\service\Collection as CollectionService;
use app\api\service\Token as TokenService;
use think\Request as Request;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;

class Collection extends CommonController
{
    protected $beforeActionList = [
        'checkToken' => ['only' => 'addCollection,cancelCollection,getList']
    ];

    //收藏
    public function addCollection(){
        $data = Request::instance()->param();
        $collectionservice = new CollectionService();
        if(!isset($data['art_id'])&&!isset($data['model_id'])){
            throw new TokenException([
                'msg'=>'缺少关键参数art_id或model_id',
                'solelyCode'=>229000
            ]);
        }
        $info = $collectionservice->addinfo($data);
        switch ($info['status']) {
            case 2:
                throw new SuccessMessage([
                    'msg'=>'收藏成功'
                ]);
            case 1:
                $res = CollectionModel::addCollection($info);
                if ($res==1) {
                    throw new SuccessMessage([
                        'msg'=>'收藏成功'
                    ]);
                }else{
                    throw new TokenException([
                        'msg'=>'收藏失败',
                        'solelyCode'=>229001
                    ]);
                }
            case -1:
                throw new TokenException([
                    'msg'=>'收藏的信息不存在',
                    'solelyCode'=>229002
                ]);
            case -2:
                throw new TokenException([
                    'msg'=>'收藏的信息不属于本商家',
                    'solelyCode'=>229003
                ]);
            case -3:
                throw new TokenException([
                    'msg'=>'收藏的信息已删除',
                    'solelyCode'=>229004
                ]);
            case -4:
                throw new TokenException([
                    'msg'=>'已收藏，请勿重复收藏',
                    'solelyCode'=>229005
                ]);
            case -5:
                throw new TokenException([
                    'msg'=>'收藏失败',
                    'solelyCode'=>229001
                ]);
        }
    }

    //获取文章信息
    public function cancelCollection(){
        $data = Request::instance()->param();
        $collectionservice = new CollectionService();
        if(!isset($data['art_id'])&&!isset($data['model_id'])){
            throw new TokenException([
                'msg'=>'缺少关键参数art_id或model_id',
                'solelyCode'=>229000
            ]);
        }
        $res = $collectionservice->cancelCollection($data);
        switch ($res['status']) {
            case 1:
                throw new SuccessMessage([
                    'msg'=>'取消收藏成功'
                ]);
            case -1:
                throw new TokenException([
                    'msg'=>'未收藏该信息',
                    'solelyCode'=>229006
                ]);
            case -2:
                throw new TokenException([
                    'msg'=>'取消收藏失败',
                    'solelyCode'=>229007
                ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $collectionservice = new CollectionService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $userinfo = TokenService::getUserinfo($data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        $info['map']['user_id'] = $userinfo['id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = CollectionModel::getCollectionListToPaginate($info);
            $list = $collectionservice->formatPaginateList($list);
        }else{
            $list = CollectionModel::getCollectionList($info);
            $list = $collectionservice->formatList($list);
        }
        return $list;
    }
}