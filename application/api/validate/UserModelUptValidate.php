<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class UserModelUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'menu_id'=>'require|isPositiveInteger',
        'thirdapp_id'=>'require|isPositiveInteger',
    ];
}
