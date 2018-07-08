<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class ProductUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'name'=>'isNotEmpty',
        'price'=>'number',
        'category_id'=>'isNotEmpty|isPositiveInteger',
        //'img'=>'array',
        'content'=>'isNotEmpty',
        'stock_num'=>'isNotEmpty',      
        'id'=>'require|isPositiveInteger'
    ];
}
