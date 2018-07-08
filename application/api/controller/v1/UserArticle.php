<?php
namespace app\api\controller\v1;
use app\api\model\Article as ArticleModel;
use think\Request as Request;
use think\Controller;
use app\api\service\Article as ArticleService;
use app\api\service\UserArticle as UserArticleService;
use app\api\validate\ArticleValidate;
use app\api\validate\ArticleUptValidate;
use app\api\validate\ThirdAppIdMustBePositiveInt;
use app\api\model\UserArticle as UserArticleModel;
use app\api\service\Token as TokenService;
use app\api\controller\CommonController;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ArticleException;
use app\lib\exception\TokenException;

class UserArticle extends CommonController
{ 
    //获取文章列表
    public function getArticleList(){
        $data=Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $tokenservice = new TokenService();
        if(!isset($data['searchItem']['thirdapp_id'])){
            throw new ArticleException([
                'msg'=>'必须有thirdapp_id参数',
                'solelyCode'=>206006
            ]);
        }else{
            $num = UserArticleService::checkThirdAppId($data['searchItem']['thirdapp_id']);
            if($num==1){
            }else{
                throw new ArticleException([
                    'msg'=>'搜索条件thirdapp_id参数必须为正整数',
                    'solelyCode'=>206007
                ]);
            }
        }
        $preinfo = preAll($data);
        if(isset($preinfo['pagesize'])&&isset($preinfo['currentPage'])){
            $list = UserArticleModel::getArticleListToPaginate($preinfo);
        }else{
            $list = UserArticleModel::getArticleList($preinfo);
        }
        return $list;
    }

    //获取文章信息
    public function getArticleinfo(){
        $data=Request::instance()->param();
        $validate=new ArticleUptValidate();
        $validate->goCheck();
        $tokenservice=new TokenService();
        if(!isset($data['thirdapp_id'])){
            throw new ArticleException([
                'msg'=>'搜索条件必须有thirdapp_id参数',
                'solelyCode'=>206006
            ]);
        }else{
            $num=UserArticleService::checkThirdAppId($data['thirdapp_id']);
            if($num==1){
            }else{
                throw new ArticleException([
                    'msg'=>'搜索条件thirdapp_id参数必须为正整数',
                    'solelyCode'=>206007
                ]);
            }
        }
        $map['id']=$data['id'];
        $map['thirdapp_id']=$data['thirdapp_id'];
        $res=UserArticleModel::getArticleInfo($map);
        if(is_object($res)){
            //浏览次数加一
            $this->setCount($data['id']);
            return $res;
        }else{
            throw new ArticleException([
                'msg'=>'thirdapp_id参数与文章id不匹配',
                'solelyCode'=>206008
            ]);
        }
    }
    //文章浏览次数
    public function setCount($id){
       /*$data=Request::instance()->param();
        $id=$data['id'];*/
        $res=UserArticleModel::setCountInc($id);
        if($res){
            // throw new SuccessMessage([
            //     'msg'=>'浏览次数加1成功'
            // ]);
        }else{
            throw new ArticleException([
                'msg'=>'文章数据不存在',
                'solelyCode'=>206004
            ]);
        }
    }

    //获取指定数量的指定类别的文章
    public function getHomeArticle()
    {
        $data = Request::instance()->param();
        $res = UserArticleService::initHomeInfo($data);
        if ($res==1) {
            throw new ArticleException([
                'msg'=>'搜索条件必须有thirdapp_id参数',
                'solelyCode'=>206006
            ]);
        }
        $info = array();
        $thirdapp_id = $res['thirdapp_id'];
        foreach ($res['searchItem'] as $k => $v) {
            if (!isset($v['menu_id'])||!isset($v['num'])) {
                throw new ArticleException([
                    'msg'=>'参数异常，缺少menu_id或num',
                    'solelyCode'=>206009
                ]);
            }
            $info[$k] = UserArticleModel::getAritcleByNum($thirdapp_id,$v['menu_id'],$v['num']);
        }
        return $info;
    }
}