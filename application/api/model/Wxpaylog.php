<?php
namespace app\api\model;
use think\Model;

class Wxpaylog extends BaseModel{

    //新增日志
    public static function addLog($data){
        $wxpaylog = new Wxpaylog();
        $res = $wxpaylog ->allowField(true)->save($data);
        return $res;
    }

    //获取日志列表
    public static function getLogList(){
        $wxpaylog = new Wxpaylog();
        $list = $wxpaylog ->order('create_time','desc')->select();
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['result'] = json_decode($value['result'],true);
        }
        return $list;
    }
    
    //获取日志列表（带分页）
    public static function getLogListToPaginate($data){
        $wxpaylog = new Wxpaylog();
        $list = $wxpaylog->order('create_time','desc')->paginate($data['pagesize']);
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list['data'][$key]['result'] = json_decode($value['result'],true);
        }
        return $list;
    }

    //获取指定日志信息
    public static function getLogInfo($id){
        $wxpaylog = new Wxpaylog();
        $res = $wxpaylog ->where('id','=',$id)->find();
        $res['result'] = json_decode($res['result'],true);
        return $res;
    }
}