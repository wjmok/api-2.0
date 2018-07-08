<?php
namespace app\api\model;
use think\Model;

class Collection extends BaseModel{

    //关联article
    public function article()
    {
        return $this->hasOne('Article', 'id', 'art_id');
    }

    //关联model
    public function productmodel()
    {
        return $this->hasOne('ProductModel', 'id', 'model_id');
    }

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //新增收藏
    public static function addCollection($data)
    {
        $collection = new Collection();
        $res = $collection->allowField(true)->save($data);
        return $res;
    }

    //更新收藏
    public static function updateCollection($id,$data)
    {
        $collection = new Collection();
        $data['update_time'] = time();
        $res = $collection->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除收藏
    public static function delCollection($id)
    {
        $collection = new Collection();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $collection->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取收藏列表
    public static function getCollectionList($data)
    {
        $collection = new Collection();
        $map = $data['map'];
        $map['status']=1;
        $list = $collection->where($map)->order('create_time','desc')->with('article')->with('productmodel')->with('user')->select();
        return $list;
    }
    
    //获取收藏列表（带分页）
    public static function getCollectionListToPaginate($data)
    {
        $collection = new Collection();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $collection->where($map)->order('create_time','desc')->with('article')->with('productmodel')->with('user')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定收藏信息
    public static function getCollectionInfo($id)
    {
        $collection = new Collection();
        $res = $collection->where('id','=',$id)->with('article')->with('productmodel')->with('user')->find();
        return $res;
    }

    public static function getCollectionByMap($map)
    {
        $collection = new Collection();
        $res = $collection->where($map)->with('article')->with('productmodel')->with('user')->find();
        return $res;
    }
}