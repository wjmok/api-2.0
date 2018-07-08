<?php
namespace app\api\controller\v1;
use think\Request as Request;
use app\api\model\Category as CategoryModel;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;

class UserCategory extends CommonController
{   
    protected $beforeActionList = [
        'checkThirdID' => ['only' => 'getTree']
    ];

    //获取tree
    public function getTree(){
        $data = Request::instance()->param();
        $list = preAll2($data);
        $list['map']['thirdapp_id'] = $data['thirdapp_id'];
        $list = CategoryModel::getCategoryTree($list['map']);
        return $list;
    }
}