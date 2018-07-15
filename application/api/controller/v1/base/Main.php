<?php
namespace app\api\controller\v1\base;

use think\Request as Request; 
use think\Controller;
use think\Cache;
use think\Loader;

use app\api\controller\BaseController;


//模板相关
class Main extends BaseController{


    protected $beforeActionList = [
        /*'checkID' => ['only' => 'UpdateAdmin,DelAdmin,getInfo'],
        'checkPrimary' => ['only' => 'AddAdmin,UpdateAdmin,DelAdmin,getAllAdmin,getAdminInfo']*/
    ];


     //主方法
    public static function Base(){
        $data = trim(Request::instance()->param());
        $url  = "app\api\service\base\\".$data['serviceName'];
        $service = new $url($data);
        return $service::$data['serviceFuncName']($data);
    }

    //主方法
    public static function Func(){
        $data = Request::instance()->param();
        $url  = "app\api\service\\func\\".$data['serviceName'];
        $service = new $url($data);
        return $service::$data['serviceFuncName']($data);
    }

    //common
    public static function Common(){
        $data = Request::instance()->param();
        $url  = "app\api\service\base\\Common";
        $service = new $url($data);
        return $service::$data['FuncName']($data);
    }


    
}