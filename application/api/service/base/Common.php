<?php
namespace app\api\service\base;
use app\api\model\Common as CommonModel;
use think\Exception;
use think\Model;
use think\Cache;

use app\api\validate\CommonValidate;


use app\lib\exception\SuccessMessage;
use app\lib\exception\ErrorMessage;


class Common{

    
    
    

    function __construct($data){
        
        
    }

    //添加admin时判断name是否重复
    
    public static function add($data,$inner=false){


        $data = preSearch($data);
        $scopeArr = ['order'=>['scope'=>[20,90],'isMe'=>['user_type'=>[1,2],'scope'=>[0,20]]],'label'=>['scope'=>[20,90]],'article'=>['scope'=>[20,90]],'product'=>['scope'=>[20,90]]];
        if(isset($scope[$data['modelName']])){
            $scope = $scope[$data['modelName']];
            (new CommonValidate())->goCheck('two',$data);
            $data = checkTokenAndScope($data,$scope);
        };


        $data = preAdd($data);
        $res =  CommonModel::CommonSave($data['modelName'],$data['data']);
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

        
        
        $scopeArr = ['order'=>5,];
        if(isset($scope[$data['modelName']])){
            $scope = $scope[$data['modelName']];
            (new CommonValidate())->goCheck('one',$data);
            $data = checkTokenAndScope($data,20);
        };

        
        $data = preGet($data);
        
        $res =  CommonModel::CommonGet($data['modelName'],$data);
        
        return $res;
        
        if(isset($res['data'])&&count($res['data'])>0){

            $res['data'] = clist_to_tree($res['data']);

        }else if(!isset($res['data'])&&count($res)>0){
            $res = clist_to_tree($res);
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

        $data = preSearch($data);

        $scopeArr = ['order'=>5,'label'=>20,'article'=>20,'product'=>20];
        if(isset($scope[$data['modelName']])){
            $scope = $scope[$data['modelName']];
            (new CommonValidate())->goCheck('two',$data);
            $data = checkTokenAndScope($data,$scope);
        };

        $revise = CommonModel::CommonGet($data['modelName'],$data['map']);
        
        if($revise){
            $revise = $revise[0];
            $data['map']['thirdapp_id'] = $revise['thirdapp_id'];
            $data['map']['user_no'] = $revise['user_no'];
            $data = checkTokenAndScope($data,0);
        }else{
            throw new ErrorMessage([
                'msg'=>'您所'.$key.'的信息不存在'
            ]);
        };

        
        $data = preUpdate($data);
        $res =  CommonModel::CommonSave($data['modelName'],$data['data'],$data['map']);
        if($inner){
            return $res;
        }else{
            dealUpdateRes($res,$key); 
        };
           
    }

    public static function delete($data,$inner=false){

        $scopeArr = ['order'=>5,'label'=>20,'article'=>20,'product'=>20];
        if(isset($scope[$data['modelName']])){
            $scope = $scope[$data['modelName']];
            (new CommonValidate())->goCheck('one',$data);
            checkTokenAndScope($data,$scope);
        };

        
        $data['data'] = [];
        $data['data']['status'] = -1;
        

        
        return self::update($data,"删除",$inner);

    }

    public static function realDelete($data,$key='真实删除',$inner=false){
        $data = preSearch($data);

        $scopeArr = ['order'=>5,'label'=>20,'article'=>20,'product'=>20];
        if(isset($scope[$data['modelName']])){
            $scope = $scope[$data['modelName']];
            (new CommonValidate())->goCheck('one',$data);
            checkTokenAndScope($data,$scope);
        };
        
        
        $res =  CommonModel::CommonDelete($data['modelName'],$data);
        if($inner){
            return $res;
        }else{
            dealUpdateRes($res,$key); 
        };
        
    }



    


    


}