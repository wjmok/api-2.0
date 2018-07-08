<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class TemplateValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'require|isNotEmpty',
        'description'=>'require|isNotEmpty',
        'img'=>'require|array'
    ];
}
