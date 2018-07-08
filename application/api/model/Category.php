<?php
namespace app\api\model;
use app\api\service\Category as CategoryService;
use think\Model;
use think\Cache;
class Category extends BaseModel{

    public function products(){
        return $this->hasMany('Product', 'category_id', 'id');
    }

    //添加类别
    public static function addCategry($data){
        $category=new Category();
        //获取thirdapp_+id
        $userinfo=Cache::get('info'.$data['token']);
        $data['thirdapp_id']=$userinfo->thirdapp_id;

        $res=$category->allowField(true)->save($data);
        return $res;
    }

    //修改分类
    public static function updateCategory($id,$data){
        $category=new Category();
        if(isset($data['img'])){
            if($data['img']=='deleteimg'){
                $data['img'] = json_encode([]);
            }else{
                $data['img'] = json_encode($data['img']);
            }
        }
        $data['update_time'] = time();
        $info = $category->where('id','=',$id)->find();
        if($info){
            if($info['status']==-1){
                return 0;//已删除
            }else{
                $res=$category->allowField(true)->save($data,['id'=>$id]);
                return 1;
            }
        }else{
            return -1;//未查询到此条信息
        } 
    }

    //软删除分类
    public static function delCategory($id,$data){
        $category=new Category();
        $data['delete_time']=time();
        $info=$category->where('id','=',$id)->find();
        $userinfo=Cache::get('info'.$data['token']);
        if($info['thirdapp_id']!=$userinfo->thirdapp_id){
            return -2;
        }
        if($info){
            if($info['status']==-1){
                return 0;//已删除
            }else{
                $res=$category->allowField(true)->save($data,['id'=>$id]);
                return 1;//删除成功
            }
        }else{
            return -1;//未查询到此条信息
        } 
    }

    /**
     * 获取类别层级树
     */
    public static function getCategoryTree($info){
        $category = new Category();
        if (isset($info['parentid'])) {
            $rootid = $info['parentid'];
            unset($info['parentid']);
        }else{
            $rootid = 0;
        }
        $list = $category->where($info)->order('listorder','desc')->select();
        $catelist = array();
        foreach($list as $key=>$v) {
            $catelist[$key]['id'] = $v['id'];
            $catelist[$key]['parentid'] = $v['parentid'];
            $catelist[$key]['name'] = $v['name'];
            $catelist[$key]['listorder'] = $v['listorder'];
            $catelist[$key]['img'] = json_decode($v['img'],true);
            $catelist[$key]['description'] = $v['description'];
        }
        //生成tree
        $categoryservice = new CategoryService();
        $tree = $categoryservice->clist_to_tree($catelist,$pk='id',$pid='parentid',$child='child',$root=$rootid);
        return $tree;
    }

    //获取类别列表
    public static function getCategoryList($data,$token){
        $Category=new Category();
        $map=$data['map'];
        $map['status']=1; 
        $map=CategoryService::getCategoryList($map,$token);
        if($map['id']==0){
            return 0;//搜索条件不符
        }
        $userinfo=Cache::get('info'.$token);
        $map['thirdapp_id']=$userinfo->thirdapp_id;

        $list=$Category->where($map)->order('create_time','desc')->select();
        $list=$list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['img']=json_decode($value['img'],true);
        }
        return $list;    
    }
    //获取分类列表(带分页)
    public static function getCategoryListToPaginate($data,$token){
        $Category=new Category();
        $map=$data['map'];
        $map['status']=1;
        $map=CategoryService::getCategoryList($map,$token);
        if($map['id']==0){
            return 0;//搜索条件不符
        }
        $userinfo=Cache::get('info'.$token);
        $map['thirdapp_id']=$userinfo->thirdapp_id;
        $list=$Category->where($map)->order('create_time','desc')->paginate($data['pagesize']);
        $list=$list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['img']=json_decode($value['img'],true);
        }
        return $list;
    }

    //获取指定分类信息
    public static function getCategoryInfo($id){

        $category = new Category();
        $res = $category->where('id','=',$id)->find();
        if(empty($res)){
            return -3;
        }
        if($res['status']==-1){
            return -2;
        }
        $res['img']=json_decode($res['img'],true);
        return $res;
    }
}
