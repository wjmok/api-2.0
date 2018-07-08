<?php
namespace app\api\service;
use app\api\model\ThirdApp as ThirdAppModel;
use think\Exception;
use think\Model;
use think\Cache;

class ThirdApp{

    //添加商户是判断name，appid ,appsecret是否重复
    public function checkIsRepeat($data){
        $thr=new ThirdAppModel();
        if(isset($data['name'])){
            $name=$thr->where('name',$data['name'])->find();
            if(!empty($name)){
                return -3;//名称重复
            }
        }
        if(isset($data['appid'])){
            $appid=$thr->where('appid',$data['appid'])->find();
            if(!empty($appid)){
                return -2;//appid重复
            }
        }
        if(isset($data['appsecret'])){
            $appsecret=$thr->where('appsecret',$data['appsecret'])->find();
            if(!empty($appsecret)){
                return -1;//appsecret重复
            }
        }
    }

    public function addinfo($data)
    {
        if(isset($data['headImg'])){
            $data['headImg'] = json_encode($data['headImg']);
        }else{
            $data['headImg'] = json_encode([]);
        }
        if(isset($data['distributionRule'])){
            $data['distributionRule'] = json_encode($data['distributionRule']);
        }else{
            $data['distributionRule'] = json_encode([]);
        }
        if(isset($data['custom_rule'])){
            $data['custom_rule'] = json_encode($data['custom_rule']);
        }else{
            $data['custom_rule'] = json_encode([]);
        }
        $data['status'] = 1;
        $data['create_time'] = time();
        //项目失效时间默认设置为项目启动后一年
        $data['invalid_time'] = time()+31536000;
        return $data;
    }

    public function formatImg($data)
    {
        if(isset($data['haedImg'])){
            $data['haedImg'] = initimg($data['haedImg']);
        }
        if(isset($data['distributionRule'])){
            $data['distributionRule'] = initimg($data['distributionRule']);
        }
        return $data;
    }

    public function checkinfo($token,$id) 
    {
        //获取用户信息
        $userinfo=Cache::get('info'.$token);
        //获取预约信息
        $thirdinfo = ThirdAppModel::getThirdUserInfo($id);
        if ($thirdinfo) {
            if ($userinfo['primary_scope']!=40) {
                return -1;
            }
            if ($thirdinfo['status']==-1) {
                return -2;
            }
        }else{
            return -3;
        }
        return 1;
    }
}