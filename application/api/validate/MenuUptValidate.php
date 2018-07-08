<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class MenuUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'id'=>'require|isPositiveInteger',
        'name'=>'isNotEmpty',
        'parentid'=>'isInteger',
        'type'=>'between:1,2',
        'template_id'=>'isPositiveInteger'
    ];
}
