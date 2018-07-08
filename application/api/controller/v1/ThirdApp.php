<?php
namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\model\ThirdApp as ThirdAppModel;
use app\api\model\Admin as AdminModel;
use think\Request as Request; 
use think\Controller;
use app\api\service\Token as TokenService;
use app\api\service\ThirdApp as ThirdAppService;
use app\lib\exception\MissException;
use app\api\validate\ThirdAppValidate;
use app\api\validate\ThirdAppUptValidate;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\ThirdappException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\TokenException;
use app\api\controller\v1\Image as ImageController;

//商户信息相关
class ThirdApp extends BaseController{
   
    //获取商户列表
    public function getAllThirdUser(){
        $data=Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $user=ThirdAppModel::getAllTuserToPaginate($info);
        }else{
            $user=ThirdAppModel::getAllTuser($info);
        }
        return $user;
    }

    //获取当前商户的所有admin用户信息
    public function getAdminThirdUser(){
        $data=Request::instance()->param();
        $checkData=checkData($data);
        if($checkData['code']!==2){
            return $checkData;
        }
        $info=preAll($data);
        if(!isset($info['map']['id'])||empty($info['map']['id'])){
            throw new ThirdappException([
                'msg'=>'id必须为搜索条件',
                'solelyCode'=>201001
            ]);
        }
        if(isset($info['pagesize'])&&isset($info['currentPage'])){
            $res=ThirdAppModel::getAdminThirdUserToPaginate($info,$data['token']);
        }else{
            $res=ThirdAppModel::getAdminThirdUser($info,$data['token']);
        }
        if($res){
            return $res;
        }else{
            throw new ThirdappException([
                'msg'=>'查询失败',
                'solelyCode'=>201002
            ]);
        }
    }

    //测试跨域问题
    public function test(){
        $url='http://mt2t.5xlm.com:91/ifr/api';
        $data=Request::instance()->param(); 
        unset($data['version']); 
        $param=$data;  
        $header[]="User-Agent:1";
        $ch=curl_init(); 
        curl_setopt($ch, CURLOPT_HTTPHEADER ,$header); 
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_POST, true);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
        $ret=curl_exec($ch);  
        $retinfo=curl_getinfo($ch);  
        curl_close($ch);  
        if($retinfo['http_code']==200){  
            $data=json_decode($ret, true);  
            return $data;  
        }else{  
            echo '失败';  
        }   
    }

    //获取当前商户的所有user用户信息
    public function getUserThirdUser(){
        $data=Request::instance()->param();
        $validate=new IDMustBePositiveInt();
        $validate->goCheck();
        $res=ThirdAppModel::getUserThirdUser($data);
        if($res){
            return $res;
        }else{
            throw new ThirdappException([
                'msg'=>'查询失败',
                'solelyCode'=>201002
            ]);
        }
    }

    //获取指定商户信息
    public function getThirdUserInfo(){
        $data=Request::instance()->param();
        $validate=new IDMustBePositiveInt();
        $validate->goCheck();
        $res=ThirdAppModel::getThirdUserInfo($data['id']);
        if(is_object($res)){
            return $res;
        }else if($res==-2){
            throw new ThirdappException([
                'msg' => '该商户信息被删除',
                'solelyCode'=>201003
            ]);
        }else if($res==-1){
            throw new ThirdappException([
                'msg' => '商户信息未找到',
                'errorCode' =>201004
            ]);
        }else{
            throw new ThirdappException([
                'msg'=>'查询失败',
                'solelyCode'=>201002
            ]);
        }
    }

    //添加
    public function AddThirdUser(){
        $data=Request::instance()->param();
        $validate=new ThirdAppValidate();
        $validate->goCheck();
        $thirdservice = new ThirdAppService();
        //判断商户名，appid ,appsecret是否重复
        $result = $thirdservice->checkIsRepeat($data);
        if($result==-3){
            throw new ThirdappException([
                'msg' => '此名称已经注册过啦',
                'solelyCode'=>201005
            ]);
        }
        if($result==-2){
            throw new ThirdappException([
                'msg' => '请核对appid参数值',
                'solelyCode'=>201006
            ]);
        }
        if($result==-1){
            throw new ThirdappException([
                'msg' => '请核对appsecret参数值',
                'solelyCode'=>201007
            ]);
        }
        $info = $thirdservice->addinfo($data);
        $res = ThirdAppModel::addTuser($info);
        if($res){
            //生成admin初始用户
            $admininfo = array(
                'thirdapp_id'=>$res,
                'status'=>1,
                'name'=>$data['name'],
                'primary_scope'=>30,
                'img'=>json_encode([]),
                'password'=>md5('11111111'),
                'create_time'=>time()
            );
            $result = AdminModel::addFristAdminUser($admininfo);
            if($result){
                throw new SuccessMessage([
                    'msg'=>'添加成功'
                ]);
            }else{
                throw new TokenException([
                    'msg' => '添加admin初始用户失败',
                    'solelyCode'=>202006
                ]);
            }
        }else{
            throw new ThirdappException([
                'msg' => '添加thirdapp失败',
                'solelyCode'=>201008
            ]);
        }
    }

    //修改
    public function UpdateThirdUser(){
        $data = Request::instance()->param();
        $validate = new ThirdAppUptValidate();
        $validate->goCheck();
        $thirdservice = new ThirdAppService();
        //判断商户名，appid ,appsecret是否重复
        $result = $thirdservice->checkIsRepeat($data);
        if($result==-3){
            throw new ThirdappException([
                'msg'=>'此名称已经注册过啦',
                'solelyCode'=>201005
            ]);
        }
        if($result==-2){
            throw new ThirdappException([
                'msg'=>'请核对appid参数值',
                'solelyCode'=>201006
            ]);
        }
        if($result==-1){
            throw new ThirdappException([
                'msg'=>'请核对appsecret参数值',
                'solelyCode'=>201007
            ]);
        }
        $checkinfo = $thirdservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '您权限不足，不能执行该操作',
                    'solelyCode' => 201009
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '商户信息已删除',
                    'solelyCode' => 201003
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '项目不存在或异常',
                    'solelyCode' => 201000
                ]);
            }
        $info = $thirdservice->formatImg($data);
        $res = ThirdAppModel::upTuser($data['id'],$info);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'修改成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '修改商户信息失败',
                'solelyCode' => 201012
            ]);
        }
    }

    //软删除
    public function DelThirdUser(){
        $data = Request::instance()->param();
        $validate = new ThirdAppUptValidate();
        $validate->goCheck();
        $thirdservice = new ThirdAppService();
        $checkinfo = $thirdservice->checkinfo($data['token'],$data['id']);
        switch ($checkinfo) {
            case 1:
                break;
            case -1:
                throw new TokenException([
                    'msg' => '您权限不足，不能执行该操作',
                    'solelyCode' => 201009
                ]);
            case -2:
                throw new TokenException([
                    'msg' => '商户信息已删除',
                    'solelyCode' => 201003
                ]);
            case -3:
                throw new TokenException([
                    'msg' => '项目不存在或异常',
                    'solelyCode' => 201000
                ]);
            }
        $res = $thirdservice->delTuser($data['id']);
        if($res==1){
            throw new SuccessMessage([
                'msg'=>'删除成功'
            ]);
        }else{
            throw new TokenException([
                'msg' => '删除商户信息失败',
                'solelyCode' => 201010
            ]);
        }
    }

    //物理删除商户所有信息
    public  function TrueDelThirdUser(){
        $data=Request::instance()->param();
        $validate=new ThirdAppUptValidate();
        $validate->goCheck();
        $s=new ImageController();

        $info=['token'=>$data['token'],'delItem'=>['thirdapp_id'=>$data['id']]];
        $result=$s->thdeletepic($info);
        if($result['code']==1){
            $res=ThirdAppModel::TruedelTuser($data);
            if($res==0){
                throw new ThirdappException([
                    'msg' => '删除失败',
                    'solelyCode'=>201010
                ]);
            }else if($res==1){
                throw new SuccessMessage([
                    'msg'=>'删除成功'
                ]);
            }else{
                throw new ThirdappException([
                    'msg' => '服务端错误，删除失败',
                    'solelyCode'=>201011
                ]);
            }
        }
    }
}