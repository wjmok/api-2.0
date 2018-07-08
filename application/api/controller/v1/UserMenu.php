<?php
namespace app\api\controller\v1;
use think\Request as Request;
use app\api\validate\UserMenuValidate;
use app\api\validate\UserModelUptValidate;
use app\api\service\UserMenu as UserMenuService;
use app\api\model\UserMenu as UserMenuModel;
use app\api\controller\CommonController;
use app\lib\exception\MenuException;

class UserMenu extends CommonController
{

    //获取menu层级
    public function getMenuTree(){
        $data = Request::instance()->param();
        $validate=new UserMenuValidate();
        $validate->goCheck();
        $info = preAll2($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        $tree = UserMenuModel::getMenuTree($info);
        return $tree;
    }

    //获取指定menu信息
     public function getMenuinfo(){
        $data = Request::instance()->param();
        $validate = new UserModelUptValidate();
        $validate->goCheck();
        $info['id'] = $data['menu_id'];
        $info['thirdapp_id'] = $data['thirdapp_id'];
        $res=UserMenuModel::getMenuInfo($info);
        if(is_object($res)){
            $res=$res->toArray();
        }
        if($res==-3){
            throw new MenuException([
                'msg' => '该menu已被禁用',
                'solelyCode'=>203010
            ]);
        }else if($res==-2){
            throw new MenuException([
                'msg' => 'thirdapp_id参数与menu_id参数不匹配',
                'solelyCode'=>203011
            ]);
        }else{
            return $res;
        }
    }
}