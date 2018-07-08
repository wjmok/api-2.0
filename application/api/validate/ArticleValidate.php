<?php
/**
 * Created by wsgt
 */
namespace app\api\validate;
class ArticleValidate extends BaseValidate{
	//字段验证规则

    protected $rule = [
        'title'=>'require|isNotEmpty',
        // 'content'=>'require|isNotEmpty',
        // 'small_title'=>'require|isNotEmpty',
        'menu_id'=>'require|isPositiveInteger',
        // 'keywords'=>'require|isNotEmpty',
        // 'description'=>'require|isNotEmpty',
    ];
}
