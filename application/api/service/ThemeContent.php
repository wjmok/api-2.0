<?php
namespace app\api\service;
use app\api\model\ThemeContent as ThemeContentModel;
use think\Cache;

class ThemeContent{

    public function addinfo($data){
        $data['status']=1;
        $data['create_time']=time();
        if(isset($data['mainImg'])){
            $data['mainImg']=json_encode($data['mainImg']);
        }else{
            $data['mainImg']=json_encode([]);
        }
        $userinfo=Cache::get('info'.$data['token']);
        //获取thirdapp_id
        $data['thirdapp_id']=$userinfo->thirdapp_id;
        return $data;
    }

    public function editinfo($data)
    {
        if(isset($data['mainImg'])){
            $data['mainImg'] = initimg($data['mainImg']);
        }
        //获取用户信息
        $userinfo=Cache::get('info'.$data['token']);
        //获取主题信息
        $contentinfo = ThemeContentModel::getContentInfo($data['id']);
        if ($contentinfo) {
            if ($userinfo['thirdapp_id']!=$contentinfo['thirdapp_id']) {
                $res['status'] = -1;
                return $res;
            }
            if ($contentinfo['status']==-1) {
                $res['status'] = -2;
                return $res;
            }
        }else{
            $res['status'] = -3;
            return $res;
        }
        $res['data'] = $data;
        $res['status'] = 1;
        return $res;
    }

    public function delcontent($data) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$data['token']);
        //获取内容信息
        $contentinfo = ThemeContentModel::getContentInfo($data['id']);
        if ($contentinfo) {
            if ($userinfo['thirdapp_id']!=$contentinfo['thirdapp_id']) {
                $res['status'] = -1;
                return $res;
            }
            if ($contentinfo['status']==-1) {
                $res['status'] = -2;
                return $res;
            }
        }else{
            $res['status'] = -3;
            return $res;
        }
        $res['status'] = 1;
        return $res;
    }

    public function delrelation($id,$type)
    {
        $search['map']['relation_id'] = $id;
        $contentlist = ThemeContentModel::getContentList($search);
        foreach ($contentlist as $key => $value) {
            if ($contentlist[$key]['theme']['themeType']==$type&&$contentlist[$key]['relation_id']==$id) {
                $delcontent = ThemeContentModel::delContent($value['id']);
            }
        }
    }
}