<?php
namespace app\api\model;
use think\Model;
use think\Cache;
class Remark extends BaseModel{

    //关联user信息
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

	//新增评论
    public static function addRemark($data){
        $remark = new Remark();
        $res = $remark->allowField(true)->save($data);
        return $res;
    }
    
    //获取评论列表(分页)
    public static function getRelationRemark($data){
    	$remark = new Remark();
        $map = $data['map'];
        $map['status'] = 1;
    	$list = $remark->where($map)->with('user')->paginate($data['pagesize']);
        $list = $list->toArray();
    	foreach ($list['data'] as $key => $value) {
    		$list['data'][$key]['user_headimg']=json_decode($value['user_headimg']);
            if ($list['data'][$key]['user']) {
                $list['data'][$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl']);
            }
    	}
    	return $list;
    }

    //获取评论列表(不分页)
    public static function getRemarkList($data)
    {
        $remark = new Remark();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $remark->where($map)->with('user')->select();
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['user_headimg'] = json_decode($value['user_headimg']);
            if ($list[$key]['user']) {
                $list[$key]['user']['headimgurl'] = json_decode($value['user']['headimgurl']);
            }
        }
        return $list;
    }

    //软删除评论
    public static function delRemark($id)
    {
        $data['status'] = -1;
        $data['delete_time'] = time();
        $remark = new Remark();
        $res = $remark->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    public static function getRemarkInfo($id)
    {
        $remark = new Remark();
        $res = $remark->where('id','=',$id)->with('user')->find();
        $res['user_headimg'] = json_decode($res['user_headimg']);
        if ($res['user']) {
            $res['user']['headimgurl'] = json_decode($res['user']['headimgurl']);
        }
        return $res;
    }
}