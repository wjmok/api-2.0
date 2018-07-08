<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class ProductValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'require|isNotEmpty',
        // 'price'=>'require|number',
        'category_id'=>'require|isNotEmpty|isPositiveInteger',
        // 'mainImg'=>'require|array',
        'content'=>'require|isNotEmpty',
    ];
}
