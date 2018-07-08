<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class ArticleUptValidate extends BaseValidate{
	//字段验证规则
    protected $rule = [
        'title'=>'isNotEmpty',
        'content'=>'isNotEmpty',
        'small_title'=>'isNotEmpty',
        'menu_id'=>'isPositiveInteger',
        'keywords'=>'isNotEmpty',
        'description'=>'isNotEmpty',
        'id'=>'require|isPositiveInteger'
    ];
}
