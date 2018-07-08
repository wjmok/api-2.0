<?php
/**
 * Created by 七月
 * Author: 七月
 * Date: 2017/2/18
 * Time: 15:44
 */

namespace app\lib\exception;

/**
 * 创建成功（如果不需要返回任何消息）
 * 201 创建成功，202需要一个异步的处理才能完成请求
 */
class ErrorMessage extends BaseException
{
    // public $code = 200;
    public $msg = '网络故障';
    public $solelyCode = 300000;
}