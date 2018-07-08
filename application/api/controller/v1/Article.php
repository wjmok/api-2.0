<?php
namespace app\api\controller\v1;
use app\api\model\Article as ArticleModel;
use think\Request as Request;
use think\Controller;
use app\api\service\Article as ArticleService;
use app\api\service\ThemeContent as ThemeContentService;
use app\api\validate\ArticleValidate;
use app\api\validate\ArticleUptValidate;
use app\api\controller\BaseController;
use app\lib\exception\ArticleException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class Article extends BaseController{
    //新增文章
    public function addArticle(){
        $data=Request::instance()->param();
        $validate=new ArticleValidate();
        $validate->goCheck();
        $articleservice=new ArticleService();
        $info=$articleservice->addinfo($data);
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo['thirdapp_id'];
        $res=ArticleModel::addArticle($info);
        if($res>0){
            throw new SuccessMessage([
                'msg'=>'添加成功'
            ]);
        }else if($res==-2){
            throw new ArticleException([
                'msg'=>'menu_id参数有误',
                'solelyCode'=>206000
            ]);
        }else{
            throw new ArticleException([
                'msg'=>'添加失败',
                'solelyCode'=>206001
            ]);
        }    
    }

    //修改文章
    public function editArticle(){
        $data=Request::instance()->param();
        $validate=new ArticleUptValidate();
        $validate->goCheck();
        $articleservice=new ArticleService();
        $info=$articleservice->editinfo($data);
        switch ($info['status']) {
            case 1:
                $updateinfo = $info['data'];
                break;
            case -1:
                throw new ArticleException([
                    'msg' => '此文章不属于本商户',
                    'solelyCode' => 206002
                ]);
            case -2:
                throw new ArticleException([
                    'msg' => '该文章已被删除',
                    'solelyCode' => 206003
                ]);
            case -3:
                throw new ArticleException([
                    'msg' => '文章数据不存在',
                    'solelyCode' => 206004
                ]);
        }
        $res=ArticleModel::updateArticle($data['id'],$updateinfo);
        if($res==1||$res==0){
            throw new SuccessMessage([
                'msg'=>'修改文章成功',
            ]);
        }else{
            throw new ArticleException([
                'msg' => '修改文章失败',
                'solelyCode' => 203004
            ]);
        }
    }

    //软删除文章
    public function delArticle(){
        $data=Request::instance()->param();
        $validate=new ArticleUptValidate();
        $validate->goCheck();
        $articleservice=new ArticleService();
        $info=$articleservice->delArticle($data);
        switch ($info['status']) {
            case 1:
                break;
            case -1:
                throw new ArticleException([
                    'msg' => '此文章不属于本商户',
                    'solelyCode' => 206002
                ]);
            case -2:
                throw new ArticleException([
                    'msg' => '该文章已被删除',
                    'solelyCode' => 206003
                ]);
            case -3:
                throw new ArticleException([
                    'msg' => '文章数据不存在',
                    'solelyCode' => 206004
                ]);
        }
        $res=ArticleModel::delArticle($data['id']);
        if($res==1){
            //删除关联的theme内容
            $themeService = new ThemeContentService();
            $del = $themeService->delrelation($data['id'],"article");
            throw new SuccessMessage([
                'msg'=>'删除成功'
            ]);
        }else{
            throw new ArticleException([
                'msg' => '文章数据不存在',
                'solelyCode' => 206004
            ]);
        }
    }

    //获取文章列表
    public function getArticleList(){
        $data = Request::instance()->param();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = ArticleModel::getArticleListToPaginate($info);
        }else{
            $list = ArticleModel::getArticleList($info);
        }
        return $list;
    }

    //获取文章信息
    public function getArticleinfo(){
        $data = Request::instance()->param();
        $validate = new ArticleUptValidate();
        $validate->goCheck();
        $id = $data['id'];
        $res = ArticleModel::getArticleInfo($id);
        //获取thirdapp_id
        $userinfo = Cache::get('info'.$data['token']);
        if(is_object($res)){
            if($res['thirdapp_id']!=$userinfo->thirdapp_id){
                throw new ArticleException([
                    'msg'=>'此文章不属于本商户',
                    'solelyCode'=>206002
                ]);
            }
            if($res['status']==-1){
                throw new ArticleException([
                    'msg'=>'该文章已被删除',
                    'solelyCode'=>206003
                ]);
            }
            return $res;
        }else{
            throw new ArticleException([
                'msg'=>'文章数据不存在',
                'solelyCode'=>206004
            ]);
        }
    }
}