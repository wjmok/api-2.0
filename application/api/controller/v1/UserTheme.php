<?php
namespace app\api\controller\v1;
use app\api\model\Theme as ThemeModel;
use think\Request as Request;
use think\Controller;
use app\api\controller\CommonController;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use think\Cache;

class UserTheme extends CommonController
{ 
    protected $beforeActionList = [
        'checkThirdID' => ['only' => 'getThemeinfo'],
        'checkID' => ['only' => 'getThemeinfo']
    ];

    //获取内容列表
    public function getThemeinfo(){
        $data = Request::instance()->param();
        $res = ThemeModel::getThemeInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '主题已删除',
                    'solelyCode' => 213003
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '主题不属于本商户',
                    'solelyCode' => 213002
                ]);
            }
            return $res;
        }else{
            throw new TokenException([
                'msg' => '指定主题不存在',
                'solelyCode' => 213000
            ]);
        }
    }
}