<?php
namespace app\api\controller\v1;
use think\Request as Request;
use app\api\service\XgdBookmenu as XgdBookmenuService;
use app\api\model\XgdBookmenu as XgdBookmenuModel;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class XgdUserMenu extends CommonController
{
    protected $beforeActionList = [
        'checkThirdID' => ['only' => 'getTree,getList,getInfo'],
        'checkID'=>['only' => 'getInfo']
    ];

    //获取menu层级树
    public function getTree(){
        $data = Request::instance()->param();
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        //自定义排序起点
        if (isset($data['searchItem']['parentid'])) {
            $info['parentid'] = $data['searchItem']['parentid'];
        }
        $tree = XgdBookmenuModel::getMenuTree($info);
        return $tree;
    }

    //获取指定menu信息
    public function getInfo(){
        $data=Request::instance()->param();
        $res = XgdBookmenuModel::getMenuInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '菜单已被删除',
                    'solelyCode' => 203002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '菜单不属于该商户',
                    'solelyCode' => 203001
                ]);
            }
            $menuservice = new XgdBookmenuService();
            $res = $menuservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '菜单信息不存在',
                'solelyCode' => 203007
            ]);
        }
    }

    public function getList()
    {
        $data = Request::instance()->param();
        $menuservice = new XgdBookmenuService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = XgdBookmenuModel::getMenuListToPaginate($info);
            $list = $menuservice->formatPaginateList($list);
        }else{
            $list=XgdBookmenuModel::getMenuList($info);
            $list = $menuservice->formatList($list);
        }
        return $list;
    }
}