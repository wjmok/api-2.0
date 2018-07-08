<?php
namespace app\api\model;
use think\Model;
use app\api\model\Admin as AdminModel;
use think\Cache;
use app\lib\exception\AdminException;
class Auth extends Model{
    //检查权限
    public static function check($controller,$action,$data,$userInfo){
        if($controller=='ThirdApp'){
            throw new AdminException([
                'msg' => '您无权操作此控制器',
                'solelyCode' => 202015
            ]);
        }
        //判断admin权限//只能修改自身和下级信息//只能添加小于自己权限的admin用户
        if($action=='DelAdmin'||$action=='UpdateAdmin'){
            $information=AdminModel::getAdminInfo($data['id']);
            if($userInfo['thirdapp_id']!=$information->thirdapp_id){
                throw new AdminException([
                    'msg' => '您没有操作此用户权限。',
                    'solelyCode' => 202013
                ]);
            }
            if($userInfo['primary_scope']<$information->primary_scope){
                throw new AdminException([
                    'msg' => '账号没有操作权限',
                    'solelyCode' => 202015
                ]);
            }
        }
        //判断功能权限
        $functionScope=explode(",",$userInfo['function_scope']);
        if(in_array($action,$functionScope)){
            throw new AdminException([
                'msg'=>'您不能操作'.$action.'方法',
                'solelyCode' => 202015
            ]);   
        }else{
            if($action=='addProduct'){
                if(isset($data['thirdapp_id'])){
                    if($data['thirdapp_id']!=$userInfo['thirdapp_id']){
                        throw new AdminException([
                            'msg'=>'thirdapp_id参数有误',
                            'solelyCode'=>202016
                        ]);
                    }
                }
            }
        }
        //判断商城权限
        if($action=='editCategory'||$action=='getCategoryinfo'||$action=='delCategory'){
            $categoryScope=explode(",",$userInfo['category_scope']);
            if(in_array($data['id'],$categoryScope)){
                throw new AdminException([
                    'msg'=>'您不能操作id为'.$data['id'].'分类',
                    'solelyCode'=>202015
                ]);   
            }
        }
        //判断前端权限
        if($action=='editMenu'||$action=='delMenu'||$action=='getMenuinfo'){
            $menuScope=explode(",",$userInfo['menu_scope']);
            if(in_array($data['id'],$menuScope)){
                throw new AdminException([
                    'msg'=>'您不能操作id为'.$data['id'].'菜单',
                    'solelyCode'=>202015
                ]);    
            }
        }
        return 1;
    }
}