<?php
/**
 * Created by 七月
 * Author: 七月
 * Date: 2017/2/18
 * Time: 13:47
 */

namespace app\lib\exception;


class ThirdappException extends BaseException
{
    // public $code = 404;
    public $msg = '商户不存在';
    public $solelyCode = 201000;
}