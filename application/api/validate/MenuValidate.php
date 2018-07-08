<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class MenuValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'require|isNotEmpty',
        'parentid'=>'require|isInteger',
        // 'type'=>'require|isNotEmpty|between:1,2',
    ];
}
