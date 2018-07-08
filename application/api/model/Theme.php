<?php
namespace app\api\model;
use think\Model;
use app\api\service\Theme as ThemeService;

class Theme extends BaseModel{

    public function content(){
        return $this->hasMany('ThemeContent', 'theme_id', 'id');
    }

    //新增主题
    public static function addTheme($data){
        $theme = new Theme();
        $res=$theme->allowField(true)->save($data);
        return $res;
    }

    //更新主题
    public static function updateTheme($id,$data){
        $theme = new Theme();
        $data['update_time']=time();
        $res=$theme->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除主题
    public static function delTheme($id){
        $theme = new Theme();
        $data['delete_time']=time();          
        $data['status']=-1;          
        $res=$theme->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取主题层级树
    public static function getThemeTree($info){
        $theme = new Theme();
        $info['map']['status']=1;
        $list=$theme->where($info['map'])->order('listorder','desc')->select();
        $listTree=array();
        foreach($list as $key => $v) {
            $listTree[$key]['id']=$v['id'];
            $listTree[$key]['parentid']=$v['parentid'];
            $listTree[$key]['listorder']=$v['listorder'];
            $listTree[$key]['name']=$v['name'];
            $listTree[$key]['description']=$v['description'];
            $listTree[$key]['themeType']=$v['themeType'];
            $listTree[$key]['description']=$v['description'];
            $listTree[$key]['mainImg']=json_decode($v['mainImg'],true);
        }
        if (isset($info['parentid'])) {
            $rootid = $info['parentid'];
        }else{
            $rootid = 0;
        }
        //生成tree
        $themeservice = new ThemeService();
        $tree = $themeservice->clist_to_tree($listTree,$pk='id',$pid='parentid',$child='child',$root=$rootid);
        return $tree;
    }

    //获取主题列表
    public static function getThemeList($data){
        $theme = new Theme();
        $map=$data['map'];
        $map['status']=1;
        $list=$theme->where($map)->order('listorder','desc')->order('create_time','desc')->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
        }
        return $list;
    }
    
    //获取主题列表（带分页）
    public static function getThemeListToPaginate($data){
        $theme = new Theme();
        $map=$data['map'];
        $map['status']=1;
        $list=$theme->where($map)->order('listorder','desc')->order('create_time','desc')->paginate($data['pagesize']);
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
        }
        return $list;
    }

    //获取指定主题信息
    public static function getThemeInfo($id){
        $theme = new Theme();
        $res = $theme->where('id','=',$id)->find();
        if ($res) {
            $res['mainImg']=json_decode($res['mainImg'],true);
        }
        return $res;
    }
}