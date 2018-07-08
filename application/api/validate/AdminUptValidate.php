<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class AdminUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'id'=>'require|isPositiveInteger',
        'primary_scope'=>'between:10,40'
    ];
}
