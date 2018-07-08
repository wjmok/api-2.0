<?php
/**
 * Created by 董博明.
 * User: 董博明
 * Date: 2018/5/9
 * Time: 15:28
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\controller\BaseController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;
use app\api\model\XgdBookmenu as XgdBookmenuModel;
use app\api\service\XgdBookmenu as XgdBookmenuService;
use think\Request as Request;
use think\Cache;

/**
 * 后端西工大项目菜单
 */
class XgdMenu extends BaseController{

    protected $beforeActionList = [
        'checkID' => ['only' => 'editMenu,delMenu,getInfo']
    ];

    public function addMenu(){
        $data = Request::instance()->param();
        $menuservice = new XgdBookmenuService();
        $info = $menuservice->addinfo($data);
        $res = XgdBookmenuModel::addMenu($info); 
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'添加成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '添加菜单失败',
                'solelyCode'=>203000
            ]);
        }
    }

    public function editMenu(){
        $data = Request::instance()->param();
        $menuservice = new XgdBookmenuService();
        $checkinfo = $menuservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '菜单不属于该商户',
                    'solelyCode' => 203001
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '菜单已被删除',
                    'solelyCode' => 203002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '菜单信息不存在',
                    'solelyCode' => 203007
                ]);
        }
        $updateinfo = $menuservice->formatImg($data);
        $res = XgdBookmenuModel::updateMenu($data['id'],$updateinfo);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改菜单失败',
                'solelyCode' => 203003
            ]);
        }
    }

    public function delMenu()
    {
        $data = Request::instance()->param();
        $menuservice = new XgdBookmenuService();
        $checkinfo = $menuservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '菜单不属于该商户',
                    'solelyCode' => 203001
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '菜单已被删除',
                    'solelyCode' => 203002
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '菜单信息不存在',
                    'solelyCode' => 203007
                ]);
        }
        $res = XgdBookmenuModel::delMenu($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除成功',
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除菜单失败',
                'solelyCode' => 203010
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
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = XgdBookmenuModel::getMenuListToPaginate($info);
            $list = $menuservice->formatPaginateList($list);
        }else{
            $list=XgdBookmenuModel::getMenuList($info);
            $list = $menuservice->formatList($list);
        }
        return $list;
    }

    public function getInfo()
    {
        $data = Request::instance()->param();
        $userinfo = Cache::get('info'.$data['token']);
        $res = XgdBookmenuModel::getMenuInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '菜单已被删除',
                    'solelyCode' => 203002
                ]);
            }
            if ($res['thirdapp_id']!=$userinfo['thirdapp_id']) {
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

    //获取菜单层级树
    public function getTree(){
        $data = Request::instance()->param();
        $menuservice = new XgdBookmenuService();
        $userinfo = Cache::get('info'.$data['token']);
        $info['map']['thirdapp_id'] = $userinfo->thirdapp_id;
        //自定义排序起点
        if (isset($data['searchItem']['parentid'])) {
            $info['parentid'] = $data['searchItem']['parentid'];
        }
        $tree = XgdBookmenuModel::getMenuTree($info);
        return $tree;
    }
}