<?php
namespace app\api\model;
use think\Model;

class Wxlog extends BaseModel{

    //新增日志
    public static function addLog($data){
        $wxlog = new Wxlog();
        $res = $wxlog ->allowField(true)->save($data);
        return $res;
    }

    //获取日志列表
    public static function getLogList(){
        $wxlog = new Wxlog();
        $list = $wxlog ->order('create_time','desc')->select();
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list[$key]['result'] = json_decode($value['result'],true);
        }
        return $list;
    }
    
    //获取日志列表（带分页）
    public static function getLogListToPaginate($data){
        $wxlog = new Wxlog();
        $list = $wxlog->order('create_time','desc')->paginate($data['pagesize']);
        $list = $list->toArray();
        foreach ($list as $key => $value) {
            $list['data'][$key]['result'] = json_decode($value['result'],true);
        }
        return $list;
    }

    //获取指定日志信息
    public static function getLogInfo($id){
        $wxlog = new Wxlog();
        $res = $wxlog ->where('id','=',$id)->find();
        $res['result'] = json_decode($res['result'],true);
        return $res;
    }
}