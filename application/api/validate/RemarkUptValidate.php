<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class RemarkUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'id'=>'require|isPositiveInteger'
    ];
}
