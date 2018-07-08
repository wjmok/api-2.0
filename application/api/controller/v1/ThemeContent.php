<?php
namespace app\api\controller\v1;
use app\api\model\Theme as ThemeModel;
use app\api\model\ThemeContent as ThemeContentModel;
use app\api\model\Article as ArticleModel;
use app\api\model\ProductModel as ProductModelModel;
use think\Request as Request;
use think\Controller;
use app\api\service\ThemeContent as ThemeContentService;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class ThemeContent extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editContent,delContent,getContentinfo']
    ];

    //新增内容
    public function addContent(){
        $data = Request::instance()->param();
        if (!isset($data['relation_id'])||!isset($data['theme_id'])) {
            throw new TokenException([
                'msg' => '缺少关键参数relation_id或theme_id',
                'solelyCode'=>213013
            ]);
        }
        $themeinfo = ThemeModel::getThemeInfo($data['theme_id']);
        if ($themeinfo) {
            switch ($themeinfo['themeType']) {
                case 'article':
                    $info = ArticleModel::getArticleInfo($data['relation_id']);
                case 'product':
                    $info = ProductModelModel::getModelById($data['relation_id']);
            }
            if (empty($info)) {
                throw new ThemeException([
                    'msg' => '关联的信息不存在',
                    'solelyCode'=>213017
                ]);
            }
        }else{
            throw new TokenException([
                'msg' => '指定主题不存在',
                'solelyCode'=>213000
            ]);
        }
        $themeservice = new ThemeContentService();
        $info = $themeservice->addinfo($data);
        $res = ThemeContentModel::addContent($info); 
        if($res>0){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '添加内容失败',
                'solelyCode'=>213008
            ]);
        }     
    }

    //修改内容
    public function editContent(){
        $data=Request::instance()->param();
        $themeservice=new ThemeContentService();
        $info=$themeservice->editinfo($data);
        switch ($info['status']) {
            case 1:
                $updateinfo = $info['data'];
                break;
            case -1:
                throw new TokenException([
                    'msg' => '内容不属于本商户',
                    'solelyCode' => 213009
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '内容已删除',
                    'solelyCode' => 213010
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定主题不存在',
                    'solelyCode'=>213000
                ]);
        }
        $res=ThemeContentModel::updateContent($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改内容成功',
            ]);
        }elseif ($res==0) {
            throw new SuccessMessage([
                'msg'=>'修改内容成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改内容失败',
                'solelyCode' => 203011
            ]);
        }
    }

    //软删除
    public function delContent(){
        $data=Request::instance()->param();
        $themeservice=new ThemeContentService();
        $info=$themeservice->delcontent($data);
        switch ($info['status']) {
             case -1:
                throw new TokenException([
                    'msg' => '内容不属于本商户',
                    'solelyCode' => 213009
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '内容已删除',
                    'solelyCode' => 213010
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '指定主题不存在',
                    'solelyCode'=>213000
                ]);
            default:
                break;
        }
        $res=ThemeContentModel::delContent($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除内容成功',
            ]);
        }else{
            throw new TokenException([
                'msg'=>'删除内容失败',
                'solelyCode' => 213012
            ]);
        }
    }

    //获取内容列表
    public function getContentList(){
        $data=Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo=Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id']=$userinfo->thirdapp_id;
        if (isset($data['searchItem']['themeType'])&&!isset($data['searchItem']['theme_id'])) {
            $thememap['map']['themeType'] = $data['searchItem']['themeType'];
            $themelist = ThemeModel::getThemeList($thememap);
            if (empty($themelist)) {
                throw new TokenException([
                    'msg'=>'没有该themeType',
                    'solelyCode' => 213015
                ]);
            }
            $themeIDs = array();
            foreach ($themelist as $item) {
                array_push($themeIDs, $item['id']);
            }
            $info['map']['theme_id'] = array('in',$themeIDs);
        }elseif (!isset($data['searchItem']['themeType'])&&isset($data['searchItem']['theme_id'])) {
            $info['map']['theme_id'] = $data['searchItem']['theme_id'];
        }elseif (isset($data['searchItem']['themeType'])&&isset($data['searchItem']['theme_id'])) {
            throw new TokenException([
                'msg'=>'参数themeType与themeID冲突',
                'solelyCode' => 213014
            ]);
        }else{
            throw new TokenException([
                'msg'=>'缺少关键参数themeType或themeID',
                'solelyCode' => 213013
            ]);
        }
        unset($info['map']['themeType']);
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list=ThemeContentModel::getContentListToPaginate($info);
        }else{
            $list=ThemeContentModel::getContentList($info);
        }
        if(is_array($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '指定主题不存在',
                'solelyCode'=>213000
            ]);
        }
    }

    //获取内容信息
    public function getContentinfo(){
        $data=Request::instance()->param();
        $userinfo=Cache::get('info'.$data['token']);
        $info['thirdapp_id']=$userinfo->thirdapp_id;
        $res=ThemeContentModel::getContentInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '内容已删除',
                    'solelyCode' => 213010
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '内容不属于本商户',
                    'solelyCode' => 213009
                ]);
            }
            return $res;
        }else{
            throw new TokenException([
                'msg' => '指定主题不存在',
                'solelyCode'=>213000
            ]);
        }
    }
}