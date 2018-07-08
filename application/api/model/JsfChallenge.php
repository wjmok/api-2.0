<?php
namespace app\api\model;
use think\Model;

//健身房项目挑战赛
class JsfChallenge extends BaseModel{

    //新增挑战赛
    public static function addChallenge($data){
        $challenge = new JsfChallenge();
        $res = $challenge->allowField(true)->save($data);
        return $res;
    }

    //更新挑战赛
    public static function updateChallenge($id,$data){
        $challenge = new JsfChallenge();
        $data['update_time'] = time();
        $res = $challenge->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除挑战赛
    public static function delChallenge($id){
        $challenge = new JsfChallenge();
        $data['delete_time'] = time();
        $data['status'] = -1; 
        $res = $challenge->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取挑战赛列表
    public static function getChallengeList($data){
        $challenge = new JsfChallenge();
        $map = $data['map'];
        $map['status'] = 1;
        if(isset($map['name'])){
            $name = $map['name'];
            unset($map['name']);
            $list = $challenge->where($map)->where('name','like','%'.$name.'%')->order('listorder','desc')->select();
        }else{
            $list = $challenge->where($map)->order('listorder','desc')->select();
        }
        return $list;
    }
    
    //获取挑战赛列表（带分页）
    public static function getChallengeListToPaginate($data){
        $challenge = new JsfChallenge();
        $map = $data['map'];
        $map['status'] = 1;
        if(isset($map['name'])){
            $name = $map['name'];
            unset($map['name']);
            $list = $challenge->where($map)->where('name','like','%'.$name.'%')->order('listorder','desc')->paginate($data['pagesize']);
        }else{
            $list = $challenge->where($map)->order('listorder','desc')->paginate($data['pagesize']);
        }
        return $list;
    }

    //获取指定挑战赛信息
    public static function getChallengeInfo($id){
        $challenge = new JsfChallenge();
        $res = $challenge->where('id','=',$id)->find();
        return $res;
    }
}