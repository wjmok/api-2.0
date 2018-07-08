<?php
namespace app\api\model;
use think\Model;
use app\api\service\Article as ArticleService;
use think\Db;
class UserArticle extends BaseModel{

    //获取文章列表（带分页）
    public static function getArticleListToPaginate($data){
        $article=new Article();
        $map=$data['map'];
        $map['status']=1;
        if(isset($map['menu_id'])){        
            $map['menu_id']=['in',$map['menu_id']];
        }
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list=$article->where($map)->with('articleContent')->with('menu')->order('listorder','desc')->order('create_time','desc')->where('title','like','%'.$name.'%')->paginate($data['pagesize']);
        }else{
            $list=$article->where($map)->with('articleContent')->with('menu')->order('listorder','desc')->order('create_time','desc')->paginate($data['pagesize']);
        }
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['img']=json_decode($value['img'],true);
            $list['data'][$key]['bannerImg']=json_decode($value['bannerImg'],true);
            $list['data'][$key]['menu']['banner']=json_decode($value['menu']['banner'],true);
        }
        return $list;
    }

    //获取文章列表
    public static function getArticleList($data){
        $article=new Article();
        $map=$data['map'];
        $map['status']=1;
        if(isset($map['menu_id'])){
            $map['menu_id']=['in',$map['menu_id']];
        }
        if (isset($map['name'])) {
            $name = $map['name'];
            unset($map['name']);
            $list=$article->where($map)->with('articleContent')->with('menu')->order('listorder','desc')->order('create_time','desc')->where('title','like','%'.$name.'%')->select();
        }else{
            $list=$article->where($map)->with('articleContent')->with('menu')->order('listorder','desc')->order('create_time','desc')->select();
        }
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['img']=json_decode($value['img'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
            $list[$key]['menu']['banner']=json_decode($value['menu']['banner'],true);
        }
        return $list;
    }

    //获取指定文章信息
    public static function getArticleInfo($data){
        $article=new Article();
        $res=$article->where('id','=',$data['id'])->where('thirdapp_id','=',$data['thirdapp_id'])->with('menu')->with('articleContent')->find();
        if(empty($res)){
            return 0;
        }else{
            $res['img']=json_decode($res['img'],true);
            $res['bannerImg']=json_decode($res['bannerImg'],true);
            $res['menu']['banner']=json_decode($res['menu']['banner'],true);
            return $res;
        }
    }

    //文章浏览次数
    public static function setCountInc($id){
        $count=Db::table('article')->where('id',$id)->where('status','=',1)->setInc('view_count');
        return $count;
    }

    //验证thirdapp_id
    public static function checkThirdAppId($info,$data){
        $user=new User();
        $info=$user->where('id','=',$info['id'])->find();
        if($info['thirdapp_id']==$data['thirdapp_id']){
            return 1;//正确
        }else{
            return -1;
        }
    }

    //获取指定类别指定数量的文章
    public static function getAritcleByNum($thirdapp_id,$menu_id,$no)
    {
        $article=new Article();
        $list=$article->where('status','=',1)->where('thirdapp_id','=',$thirdapp_id)->where('menu_id','=',$menu_id)->with('menu')->with('articleContent')->order('listorder','desc')->order('create_time','desc')->limit($no)->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['img']=json_decode($value['img'],true);
            $list[$key]['bannerImg']=json_decode($value['bannerImg'],true);
            $list[$key]['menu']['banner']=json_decode($value['menu']['banner'],true);
        }
        return $list;
    }
}