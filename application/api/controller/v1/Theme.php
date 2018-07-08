<?php
namespace app\api\controller\v1;
use app\api\model\Theme as ThemeModel;
use think\Request as Request;
use think\Controller;
use app\api\service\Theme as ThemeService;
use app\api\controller\BaseController;
use app\lib\exception\ThemeException;
use app\lib\exception\SuccessMessage;
use think\Cache;

class Theme extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editTheme,delTheme,getThemeinfo']
    ];

    //新增主题
    public function addTheme(){
        $data=Request::instance()->param();
        if (isset($data['parentid'])&&$data['parentid']!=0) {
            $themeinfo = ThemeModel::getThemeInfo($data['parentid']);
            if ($themeinfo['themeType']!=$data['themeType']) {
                throw new ThemeException([
                    'msg' => '与父级主题类别不一致',
                    'solelyCode'=>213007
                ]);
            }
        }
        $themeservice=new ThemeService();
        $info=$themeservice->addinfo($data);
        $res=ThemeModel::addTheme($info); 
        if($res>0){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new ThemeException([
                'msg' => '添加主题失败',
                'solelyCode'=>213001
            ]);
        }
    }

    //修改主题
    public function editTheme(){
        $data=Request::instance()->param();
        $themeservice=new ThemeService();
        $info=$themeservice->editinfo($data);
        switch ($info['status']) {
            case 1:
                $updateinfo = $info['data'];
                break;
            case -1:
                throw new ThemeException([
                    'msg' => '主题不属于本商户',
                    'solelyCode' => 213002
                ]);
            case -2:
                throw new ThemeException([
                    'msg' => '主题已删除',
                    'solelyCode' => 213003
                ]);
            case -3:
                throw new ThemeException();
        }
        $res=ThemeModel::updateTheme($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改主题成功',
            ]);
        }elseif ($res==0) {
            throw new SuccessMessage([
                'msg'=>'修改主题成功',
            ]);
        }else{
            throw new ThemeException([
                'msg' => '修改主题失败',
                'solelyCode' => 203004
            ]);
        }
    }

    //软删除
    public function delTheme(){
        $data=Request::instance()->param();
        $themeservice=new ThemeService();
        $info=$themeservice->deltheme($data);
        switch ($info) {
             case -1:
                throw new ThemeException([
                    'msg' => '主题不属于本商户',
                    'solelyCode' => 213002
                ]);
            case -2:
                throw new ThemeException([
                    'msg' => '主题已删除',
                    'solelyCode' => 213003
                ]);
            case -3:
                throw new ThemeException();
            default:
                break;
        }
        $res=ThemeModel::delTheme($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除主题成功',
            ]);
        }else{
            throw new SuccessMessage([
                'msg'=>'删除主题失败',
                'solelyCode' => 213006
            ]);
        }
    }

    //获取主题层级树
    public function getThemeTree(){
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        //自定义排序起点
        if (isset($data['searchItem']['parentid'])) {
            $info['parentid'] = $data['searchItem']['parentid'];
        }
        $tree=ThemeModel::getThemeTree($info);
        return $tree;
    }

    //获取主题列表
    public function getThemeList(){
        $data=Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        $userinfo=Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id']=$userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list=ThemeModel::getThemeListToPaginate($info);
        }else{
            $list=ThemeModel::getThemeList($info);
        }
        if(is_array($list)){
            return $list;
        }else{
            throw new ThemeException();
        }
    }

    //获取主题信息
    public function getThemeinfo(){
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $info['thirdapp_id'] = $userinfo->thirdapp_id;
        $res = ThemeModel::getThemeInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new ThemeException([
                    'msg' => '主题已删除',
                    'solelyCode' => 213003
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
                throw new ThemeException([
                    'msg' => '主题不属于本商户',
                    'solelyCode' => 213002
                ]);
            }
            return $res;
        }else{
            throw new ThemeException();
        }
    }
}