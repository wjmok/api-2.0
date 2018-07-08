<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class ThirdAppValidate extends BaseValidate{
	//字段验证规则
    
    protected $rule = [
        'appid'=>'require|isNotEmpty',
        'appsecret'=>'require|isNotEmpty',
        'app_description'=>'require|isNotEmpty',
        'name'=>'require|isNotEmpty',
        'phone'=>'require|isMobile',
        'token'=>'require|isNotEmpty',
        'primary_scope'=>'between:10,40',
    ];


    protected $scene = [
    	
        'add'  =>  ['name','password','token'],
        'get'  =>  ['token'],
        'update'  =>  ['id','token'],
        'delete'  =>  ['id','token'],
        'realDelete'  =>  ['token'],
        
    ];

}
