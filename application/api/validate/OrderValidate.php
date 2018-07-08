<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class OrderValidate extends BaseValidate{

    //字段验证规则
    protected $rule = [
        'orderInfo'=>'require|isNotEmpty',
        'pay'=>'require|isNotEmpty',
        'id'=>'require|isNotEmpty|number',
        'product'=>'require|isNotEmpty',
        'token'=>'require|isNotEmpty',
        'thirdapp_id'=>'require|isNotEmpty|isPositiveInteger',
        
    ];

    protected $scene = [

        'addOrder'  =>  ['token','orderInfo','pay','product'],
        
        
    ];

}
