<?php
namespace app\api\service;
use app\api\model\InvitationMenu as MenuModel;
use app\api\model\Invitation as InvitationModel;
use think\Cache;
use think\Db;

class InvitationMenu{

    public function addinfo($data){
        $data['status'] = 1;
        $data['create_time'] = time();
        if(isset($data['mainImg'])){
            $data['mainImg']=json_encode($data['mainImg']);
        }else{
            $data['mainImg']=json_encode([]);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg']=json_encode($data['bannerImg']);
        }else{
            $data['bannerImg']=json_encode([]);
        }
        $userinfo=Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        if(isset($data['bannerImg'])){
            $data['bannerImg'] = initimg($data['bannerImg']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取预约信息
        $menuinfo = MenuModel::getMenuInfo($id);
        if ($menuinfo) {
            if ($userinfo['thirdapp_id']!=$menuinfo['thirdapp_id']) {
                return -1;
            }
            if ($menuinfo['status']==-1) {
                return -2;
            }
        }else{
            return -3;
        }
        return 1;
    }

    //格式化分页信息
    public function formatPaginateList($list)
    {
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
            $list['data'][$key]['bannerImg']=json_decode($value['bannerImg'],true);
            $map['map']['invitation_id'] = $value['id'];
            $map['map']['invitation_status'] = 1;
            $invitationlist = InvitationModel::getInvitationList($map);
            if ($invitationlist) {
                $invitationlist = $invitationlist->toArray();
                $list['data'][$key]['invitationNum'] = count($invitationlist);
            }else{
                $list['data'][$key]['invitationNum'] = 0;
            }
        }
        return $list;
    }

    //格式化列表信息
    public function formatList($list)
    {
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
            $map['map']['invitation_id'] = $value['id'];
            $map['map']['invitation_status'] = 1;
            $invitationlist = InvitationModel::getInvitationList($map);
            if ($invitationlist) {
                $invitationlist = $invitationlist->toArray();
                $list[$key]['invitationNum'] = count($invitationlist);
            }else{
                $list[$key]['invitationNum'] = 0;
            }
        }
        return $list;
    }

    //格式化单条信息
    public function formatInfo($info)
    {
        $info['mainImg'] = json_decode($info['mainImg'],true);
        $info['bannerImg'] = json_decode($info['bannerImg'],true);
        $map['map']['invitation_id'] = $info['id'];
        $map['map']['invitation_status'] = 1;
        $invitationlist = InvitationModel::getInvitationList($map);
        if ($invitationlist) {
            $invitationlist = $invitationlist->toArray();
            $info['invitationNum'] = count($invitationlist);
        }else{
            $info['invitationNum'] = 0;
        }
        return $info;
    }

    //变更两个菜单的子父级
    public static function changeClass($id,$cmenuid,$token)
    {
        $userinfo=Cache::get('info'.$token);
        if (!isset($id)||!isset($cmenuid)) {
            return 2;
        }
        //获取两个菜单的信息并校验
        $pmenuinfo = MenuModel::getMenuInfo($id);
        if ($pmenuinfo){
            if ($pmenuinfo['status']==-1) {
                return 4;
            }
            if ($pmenuinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                return 5;
            }
        }else{
            return 3;
        }
        $cmenuinfo = MenuModel::getMenuInfo($cmenuid);
        if ($cmenuinfo){
            if ($cmenuinfo['status']==-1) {
                return 4;
            }
            if ($cmenuinfo['thirdapp_id']!=$userinfo['thirdapp_id']) {
                return 5;
            }
        }else{
            return 3;
        }
        //判断是否重复提交
        if ($cmenuinfo['parentid']!=$pmenuinfo['id']) {
            return 6;
        }
        //变更操作，设置事务
        Db::startTrans();
        try{
            //子级记录父级信息
            $fathermenuinfo['parentid'] = $pmenuinfo['parentid'];
            $fathermenuinfo['update_time'] = time();
            $sparentinfo = MenuModel::updateMenu($cmenuinfo['id'],$fathermenuinfo);
            //父级记录子级信息
            $childmenuinfo['parentid'] = $cmenuinfo['id'];
            $childmenuinfo['update_time'] = time();
            $schildinfo = MenuModel::updateMenu($pmenuinfo['id'],$childmenuinfo);
            Db::commit();
            return 1;
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }
    }

    /**
     * 把返回的数据集转换成Tree
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @return array
     */
    function clist_to_tree($list, $pk='id', $pid = 'parentid', $child = 'child', $root = 0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId =  $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
}