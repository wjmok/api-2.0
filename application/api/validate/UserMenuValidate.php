<?php
/**
 * Created by wsgt
 */

namespace app\api\validate;
class UserMenuValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'thirdapp_id'=>'require|isPositiveInteger'
    ];
}
