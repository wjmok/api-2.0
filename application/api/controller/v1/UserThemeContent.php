<?php
namespace app\api\controller\v1;
use app\api\model\ThemeContent as ThemeContentModel;
use think\Request as Request;
use think\Controller;
use app\api\service\ThemeContent as ThemeContentService;
use app\api\controller\CommonController;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ThemeException;
use app\lib\exception\TokenException;
use think\Cache;

class UserThemeContent extends CommonController
{ 
    protected $beforeActionList = [
        'checkthirdapp' => ['only' => 'getContentList,getContentinfo,getHomeTheme']
    ];

    public function checkthirdapp()
    {
        $data = Request::instance()->param();
        if (!isset($data['thirdapp_id'])) {
            throw new ThemeException([
                'msg' => '缺少关键参数thirdID',
                'solelyCode' => 213005
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
        $preinfo=preAll($data);
        $preinfo['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($preinfo['pagesize'])&&isset($preinfo['currentPage'])){
            $list=ThemeContentModel::getContentListToPaginate($preinfo);
        }else{
            $list=ThemeContentModel::getContentList($preinfo);
        }
        if(is_array($list)){
            return $list;
        }else{
            throw new ThemeException();
        }
    }

    //获取指定内容信息
    public function getContentinfo(){
        $data=Request::instance()->param();
        if (!isset($data['id'])) {
            throw new ThemeException([
                'msg' => '缺少关键参数ID',
                'solelyCode' => 213005
            ]);
        }
        $res=ThemeContentModel::getContentInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new ThemeException([
                    'msg' => '内容已删除',
                    'solelyCode' => 213010
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new ThemeException([
                    'msg' => '内容不属于本商户',
                    'solelyCode' => 213009
                ]);
            }
            return $res;
        }else{
            throw new ThemeException();
        }
    }

    //获取指定数量的指定类别的主题内容
    public function getHomeTheme()
    {
        $data = Request::instance()->param();
        $info = array();
        $thirdapp_id = $data['thirdapp_id'];
        if (!isset($data['searchItem'])) {
            throw new ThemeException([
                'msg'=>'参数异常，缺少searchItem',
                'solelyCode' => 213005
            ]);
        }
        foreach ($data['searchItem'] as $k => $v) {
            if (!isset($v['theme_id'])||!isset($v['num'])) {
                throw new ThemeException([
                    'msg'=>'参数异常，缺少theme_id或num',
                    'solelyCode' => 213005
                ]);
            }
            $info[$k] = ThemeContentModel::getContentByNum($thirdapp_id,$v['theme_id'],$v['num']);
        }
        return $info;
    }
}