<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class CategoryUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'isNotEmpty',
        'parentid'=>'isInteger',
        'description'=>'isNotEmpty',
        'thirdapp_id'=>'isPositiveInteger',
        'id'=>'require|isPositiveInteger'
    ];
}
