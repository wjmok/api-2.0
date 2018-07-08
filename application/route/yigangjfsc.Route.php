<?php

use think\Route;
/**
 * 西安亿港网络科技-积分商城项目
 */

//cms端接口
/*设置会员折扣*/
Route::post('api/:version/Jifensc/SetDiscount','api/:version.JifenscCms/setDiscount');
/*设置签到参数*/
Route::post('api/:version/Jifensc/SetDistribution','api/:version.JifenscCms/setDistribution');

//客户端接口
/*获取会员折扣*/
Route::post('api/:version/Jifensc/GetDiscount','api/:version.JifenscUser/getDiscount');