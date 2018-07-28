<?php
namespace app\api\service\base;
use app\api\model\Common as CommonModel;
use think\Exception;
use think\Model;
use think\Cache;

use app\api\validate\CommonValidate;


use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;


class User{

    
    
    private static $filterArr = ['wx_mainImg'];
        function __construct($data){   
    }

    //添加admin时判断name是否重复
    
    public static function add($data,$inner=false){

            (new CommonValidate())->goCheck('one',$data);



            $data = checkTokenAndScope($data,config('scope.two'));
            
            
            
            if(!isset($data['data']['parent_no'])){
                $data['data']['parent_no'] = Cache::get($data['token'])['user_no'];
            }
           
            //判断用户名是否重复
            $modelData['searchItem']['login_name'] = $data['data']['login_name'];
            $res =  CommonModel::CommonGet("user",$modelData);

            if(!empty($res['data'])){
                throw new ErrorMessage([
                    'msg' => '用户名重复',
                ]);
            };

            
            if(!isset($data['data']['primary_scope'])){
                $data['data']['primary_scope'] = 30;
            };
            $data['data']['user_no'] = makeUserNo();
            $data['data']['status'] = 1;
            $data['data']['create_time'] = time();
            
            

            $res =  CommonModel::CommonSave("user",$data);

            



            if($inner){
                return $res;
            }else{
                if($res>0){
                    throw new SuccessMessage([
                        'msg'=>'添加成功',
                        'info'=>['id'=>$res]
                    ]);
                }else{
                    throw new ErrorMessage([
                        'msg'=>'添加失败'
                    ]);
                };
            };
            


    }


    public static function get($data,$inner=false){

        (new CommonValidate())->goCheck('one',$data);
        $data = checkTokenAndScope($data,config('scope.two'));
        $res =  CommonModel::CommonGet("user",$data);

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
        $res =  CommonModel::CommonSave("user",$data);
        if($inner){
            return $res;
        }else{
            dealUpdateRes($res,$key); 
        };
           
    }

    public static function delete($data,$inner=false){

        (new CommonValidate())->goCheck('one',$data);
        $data = checkTokenAndScope($data,config('scope.two'));
        
        $data['FuncName'] = 'update';
        $data['data'] = [];
        $data['data']['status'] = -1;
        
        return self::update($data,"删除",$inner);

    }

    public static function realDelete($data,$key='真实删除',$inner=false){

        (new CommonValidate())->goCheck('one',$data);
        $data = checkTokenAndScope($data,config('scope.two'));
        
        
        $res =  CommonModel::CommonDelete("user",$data);
        if($inner){
            return $res;
        }else{
            dealUpdateRes($res,$key); 
        };
        
    }



    


    


}