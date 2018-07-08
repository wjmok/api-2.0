<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class AdminValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'require|isNotEmpty',
        'id'=>'require|isNotEmpty|number',
        'password'=>'require|isNotEmpty',
        'searchItem'=>'require|isNotEmpty',
        'thirdapp_id'=>'require|isNotEmpty',
        'token'=>'require|isNotEmpty',
        'thirdapp_id'=>'require|isNotEmpty|isPositiveInteger',
        'primary_scope'=>'between:10,40',
    ];

    protected $scene = [
    	
        'login'  =>  ['name','password'], 
        'add'  =>  ['name','password','token'],
        'get'  =>  ['token'],
        'update'  =>  ['id','token'],
        'delete'  =>  ['id','token'],
        'realDelete'  =>  ['token'],

    ];

}
