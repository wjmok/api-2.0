<?php

namespace app\api\validate;

class ThirdAppIdMustBePositiveInt extends BaseValidate
{
    protected $rule = [
        'thirdapp_id' => 'require|isPositiveInteger',
    ];
}
