<?php
namespace app\api\model;
use think\Model;

class UserCard extends BaseModel{

    //关联member_card表
    public function card()
    {
        return $this->hasOne('MemberCard', 'id', 'card_id');
    }

    //关联user表
    public function user()
    {
        return $this->hasOne('User', 'id', 'user_id');
    }

    //购买会员卡
    public static function addCard($data)
    {
        $card = new UserCard();
        $res = $card->allowField(true)->save($data);
        return $res;
    }

    //更新会员卡
    public static function updateCard($id,$data)
    {
        $card = new UserCard();
        $data['update_time']=time();
        $res = $card->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //删除会员卡
    public static function delCard($id)
    {
        $card = new UserCard();
        $data['delete_time'] = time();
        $data['status'] = -1;
        $res = $card->allowField(true)->save($data,['id'=>$id]);
        return $res;
    }

    //获取会员卡列表
    public static function getCardList($data)
    {
        $card = new UserCard();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $card->where($map)->order('status','desc')->order('pay_time','desc')->with('card')->with('user')->select();
        return $list;
    }
    
    //获取会员卡列表（带分页）
    public static function getCardListToPaginate($data)
    {
        $card = new UserCard();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $card->where($map)->order('status','desc')->order('pay_time','desc')->with('card')->with('user')->paginate($data['pagesize']);
        return $list;
    }

    //获取指定会员卡信息
    public static function getCardInfo($id)
    {
        $card = new UserCard();
        $res = $card->where('id','=',$id)->with('card')->with('user')->find();
        return $res;
    }
}