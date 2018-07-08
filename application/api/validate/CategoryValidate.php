<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class CategoryValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'require|isNotEmpty',
        'parentid'=>'require|isInteger',
        'description'=>'require|isNotEmpty',
        'thirdapp_id'=>'isPositiveInteger'       
    ];
}
