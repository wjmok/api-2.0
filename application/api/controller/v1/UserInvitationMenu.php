<?php
namespace app\api\controller\v1;
use think\Request as Request;
use app\api\service\InvitationMenu as InvitationMenuService;
use app\api\model\InvitationMenu as InvitationMenuModel;
use app\api\controller\CommonController;
use app\lib\exception\TokenException;
use app\lib\exception\SuccessMessage;

class UserInvitationMenu extends CommonController
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
        $tree = InvitationMenuModel::getMenuTree($info);
        return $tree;
    }

    //获取指定menu信息
    public function getInfo(){
        $data=Request::instance()->param();
        $res = InvitationMenuModel::getMenuInfo($data['id']);
        if(is_object($res)){
            if ($res['status']==-1) {
                throw new TokenException([
                    'msg' => '帖子类型已删除',
                    'solelyCode' => 221002
                ]);
            }
            if ($res['thirdapp_id']!=$data['thirdapp_id']) {
                throw new TokenException([
                    'msg' => '帖子类型不属于本商户',
                    'solelyCode' => 221003
                ]);
            }
            $menuservice = new InvitationMenuService();
            $res = $menuservice->formatInfo($res);
            return $res;
        }else{
            throw new TokenException([
                'msg' => '帖子类型信息不存在',
                'solelyCode' => 221000
            ]);
        }
    }

    //获取帖子列表（关联主题数）
    public function getList()
    {
        $data = Request::instance()->param();
        $menuservice = new InvitationMenuService();
        $checkData = checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info = preAll($data);
        $info['map']['thirdapp_id'] = $data['thirdapp_id'];
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $list = InvitationMenuModel::getMenuListToPaginate($info);
            $list = $menuservice->formatPaginateList($list);
        }else{
            $list=InvitationMenuModel::getMenuList($info);
            $list = $menuservice->formatList($list);
        }
        if(!empty($list)){
            return $list;
        }else{
            throw new TokenException([
                'msg' => '获取帖子类型列表失败',
                'solelyCode' => 221006
            ]);
        }
    }
}