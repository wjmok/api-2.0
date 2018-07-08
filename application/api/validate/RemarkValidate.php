<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class RemarkValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'art_id'=>'require|isPositiveInteger',
        'thirdapp_id'=>'require|isPositiveInteger',
        'content'=>'require|isNotEmpty'
    ];
}
