<?php
namespace app\api\model;
use think\Model;
use app\api\model\Theme as ThemeModel;
use app\api\model\Article as ArticleModel;
use app\api\model\ProductModel as ProductModel;

class ThemeContent extends BaseModel{

    public function theme(){
        return $this->belongsTo('Theme', 'theme_id', 'id');
    }

    //新增内容
    public static function addContent($data){
        $theme = new ThemeContent();
        $res=$theme->allowField(true)->save($data);
        return $res;
    }

    //更新内容
    public static function updateContent($id,$data){
        $theme = new ThemeContent();
        $data['update_time']=time();
        $res=$theme->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除内容
    public static function delContent($id){
        $theme = new ThemeContent();
        $data['delete_time']=time();          
        $data['status']=-1;          
        $res=$theme->allowField(true)->save($data,['id'=>$id]);                  
        return $res; 
    }

    //获取内容列表
    public static function getContentList($data){
        $theme = new ThemeContent();
        $map=$data['map'];
        $map['status']=1;
        $list=$theme->where($map)->with('theme')->order('listorder','desc')->order('create_time','desc')->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['theme']['mainImg']=json_decode($value['theme']['mainImg'],true);
            $list[$key]['relationinfo']=self::getRelationInfo($value['theme']['themeType'],$value['relation_id']);
        }
        return $list;
    }
    
    //获取内容列表（带分页）
    public static function getContentListToPaginate($data){
        $theme = new ThemeContent();
        $map=$data['map'];
        $map['status']=1;
        $list=$theme->where($map)->with('theme')->order('listorder','desc')->order('create_time','desc')->paginate($data['pagesize']);
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['mainImg']=json_decode($value['mainImg'],true);
            $list['data'][$key]['theme']['mainImg']=json_decode($value['theme']['mainImg'],true);
            $list['data'][$key]['relationinfo']=self::getRelationInfo($value['theme']['themeType'],$value['relation_id']);
        }
        return $list;
    }

    //获取指定内容信息
    public static function getContentInfo($id){
        $theme = new ThemeContent();
        $res=$theme->where('id','=',$id)->with('theme')->find();
        if ($res) {
            $res['mainImg']=json_decode($res['mainImg'],true);
            if ($res['theme']) {
                $res['theme']['mainImg']=json_decode($res['theme']['mainImg'],true);
            }
            $res['relationinfo']=self::getRelationInfo($res['theme']['themeType'],$res['relation_id']);
        }
        return $res;
    }

    //获取指定类别指定数量的主题内容
    public static function getContentByNum($thirdapp_id,$theme_id,$no)
    {
        $theme = new ThemeContent();
        $map['thirdapp_id'] = $thirdapp_id;
        $map['status'] = 1;
        $map['theme_id'] = $theme_id;
        $list=$theme->with('theme')->order('listorder','desc')->order('create_time','desc')->limit($no)->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['mainImg']=json_decode($value['mainImg'],true);
            $list[$key]['theme']['mainImg']=json_decode($value['theme']['mainImg'],true);
            $list[$key]['relationinfo']=self::getRelationInfo($value['theme']['themeType'],$value['relation_id']);
        }
        return $list;
    }

    //获取关联文章信息
    public static function getRelationInfo($themeType,$id)
    {
        switch ($themeType) {
            case 'article':
                $info = ArticleModel::getArticleInfo($id);
                if ($info['status']==-1||empty($info)) {
                    $info = array();
                }
                return $info;
            case 'product':
                $info = ProductModel::getModelById($id);
                if ($info['status']==-1||empty($info)) {
                    $info = array();
                }
                return $info;
            default:
                $info = array();
                return $info;
        }
    }
}