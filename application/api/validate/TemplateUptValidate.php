<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class TemplateUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'id'=>'require|isPositiveInteger',
        'img'=>'array'
    ];
}
