<?php
namespace app\api\model;
use think\Model;

class MemberCard extends BaseModel{

    //新增会员卡
    public static function addCard($data){
        $card = new MemberCard();
        $res = $card->allowField(true)->save($data);
        return $res;
    }

    //更新会员卡
    public static function updateCard($id,$data){
        $card = new MemberCard();
        $data['update_time'] = time();
        $res = $card->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //软删除会员卡
    public static function delCard($id){
        $card = new MemberCard();
        $data['delete_time']=time();
        $data['status']=-1; 
        $res=$card->allowField(true)->save($data,['id'=>$id]);
        return $res; 
    }

    //获取会员卡列表
    public static function getCardList($data){
        $card = new MemberCard();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $card->where($map)->order('create_time','desc')->select();
        return $list;
    }
    
    //获取会员卡列表（带分页）
    public static function getCardListToPaginate($data){
        $card = new MemberCard();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $card->where($map)->order('create_time','desc')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定会员卡信息
    public static function getCardInfo($id){
        $card = new MemberCard();
        $res = $card->where('id','=',$id)->find();
        return $res;
    }
}