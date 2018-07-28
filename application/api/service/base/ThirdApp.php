<?php
namespace app\api\service\base;
use app\api\model\Common as CommonModel;
use think\Exception;
use think\Model;
use think\Cache;

use app\api\validate\CommonValidate;

use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;


use app\api\service\base\User as UserService;

class ThirdApp{

    

    

    function __construct($data){
        
    }

    
    
    public static function add($data,$inner=false){

            (new CommonValidate())->goCheck('one',$data);
            $data = checkTokenAndScope($data,config('scope.two'));
            unset($data['searchItem']['thirdapp_id']);

            
            

            //判断用户名是否重复
            $modelData = [];
            $modelData['searchItem'] = [];
            $modelData['searchItem']['name'] = $data['data']['name'];
            $modelData['token'] = $data['token'];
            $modelData = preGet($modelData);
            $res =  CommonModel::CommonGet("ThirdApp",$modelData);
            
            if(!empty($res['data'])){
                throw new ErrorMessage([
                    'msg' => '用户名重复',
                ]);
            };

            
            $data = preAdd($data);
            unset($data['thirdapp_id']);
            $data['data']['parentid'] = Cache::get($data['token'])['thirdApp']['id'];
            $data['data']['child_array'] = json_encode([],true);
            $data['data']['status'] = 1;

            $MainRes =  CommonModel::CommonSave("ThirdApp",$data);
            
            

            if($MainRes>0){

                $modelData = [];
                $modelData['data'] = [];
                $modelData['data']['child_array'] = Cache::get($data['token'])['thirdApp']['child_array'];
                array_push($modelData['data']['child_array'],intval($MainRes));
                $modelData['searchItem']['id'] = Cache::get($data['token'])['thirdApp']['id'];
                $modelData['token'] = $data['token'];
                $modelData['FuncName'] = 'update';
                
                $res = self::update($modelData,'更新',true);
                
                if($res>0){

                    $modelData = [];
                    $modelData['data']['login_name'] = $data['data']['name'];
                    $modelData['data']['password'] = '111111';
                    $modelData['data']['thirdapp_id'] = $MainRes;
                    $modelData['token'] = $data['token'];
                    $modelData['FuncName'] = 'add';
                    
                    
                    $res = UserService::add($modelData,true);

                    if($res>0){
                        throw new SuccessMessage([
                            'msg'=>'添加成功',
                            'info'=>['id'=>$MainRes]
                        ]);
                    }else{
                        throw new ErrorMessage([
                            'msg'=>'创建相关登录用户失败'
                        ]);
                    };

                }else{
                    throw new ErrorMessage([
                        'msg'=>'更新相关父级失败'
                    ]);
                };

            }else{
                throw new ErrorMessage([
                    'msg'=>'添加失败'
                ]);
            };
    }


    public static function get($data,$inner=false){

        (new CommonValidate())->goCheck('one',$data);
        $data = checkTokenAndScope($data,config('scope.two'));

        $data = preGet($data);
        unset($data['searchItem']['thirdapp_id']);
        
        
        try{
            $res =  CommonModel::CommonGet("ThirdApp",$data);
            
        }catch(Exception $e){
            throw new ErrorMessage([
                'msg'=>'查询失败',
                'info'=>$e->getMessage()
            ]); 
        };

        if(isset($res['data'])&&count($res['data'])>0){

            $res['data'] = clist_to_tree($res['data']);

        }else{
            throw new SuccessMessage([
                'msg'=>'查询结果为空',
                'info'=>$res
            ]);
        };

         
        if($inner){
            return $res;
        }else{
            throw new SuccessMessage([
                'msg'=>'查询成功',
                'info'=>$res
            ]);   
        };  

    }

    public static function update($data,$key='更新',$inner=false){
        
        (new CommonValidate())->goCheck('one',$data);

        $data = checkTokenAndScope($data,config('scope.two'));
        unset($data['searchItem']['thirdapp_id']);
        

        if($key=='更新'&&isset($data['name'])){

            $modelData = [];
            $modelData['searchItem']['name'] = $data['name'];
            $modelData['token'] = $data['token'];

            $revise = self::get($modelData,true);
            
            if($revise){
                
                throw new ErrorMessage([
                    'msg'=>'name重复'
                ]);
            };
        };
        



        $data = preUpdate($data);

        $res =  CommonModel::CommonSave('ThirdApp',$data);
        
        if($inner){
            return $res;
        }else{
            dealUpdateRes($res,$key); 
        };
    }

    public static function delete($data,$inner=false){

        (new CommonValidate())->goCheck('one',$data);
        $data = checkTokenAndScope($data,config('scope.two'));
        
        $modelData = [];
        $modelData['token'] = $data['token'];
        $modelData['searchItem']['id'] = $data['searchItem']['id'];
        $modelData['data']['status'] = -1;
        $modelData['FuncName'] = 'update';
        
        return self::update($modelData,"删除",$inner);

    }

    public static function realDelete($data,$key='真实删除',$inner=false){

        (new CommonValidate())->goCheck('one',$data);
        $data = checkTokenAndScope($data,config('scope.two'));
        
        $data = preSearch($data);
        $res =  CommonModel::CommonDelete("ThirdApp",$data);
        if($inner){
            return $res;
        }else{
            dealUpdateRes($res,$key); 
        };

    }





















    //old
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