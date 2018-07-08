<?php

use think\Route;

//cms端接口
//修改分销比例
Route::post('api/:version/Muyaju/SetDistribution','api/:version.MuyajuCms/setDistribution');
//商家接单
Route::post('api/:version/Muyaju/ReceiveOrder','api/:version.MuyajuCms/receiveOrder');
//商家核销订单
Route::post('api/:version/Muyaju/UseOrder','api/:version.MuyajuCms/useOrder');
//商家退单-拒接
Route::post('api/:version/Muyaju/RefuseOrder','api/:version.MuyajuCms/refuseOrder');
//商家取消订单-扣除用户30%费用
Route::post('api/:version/Muyaju/CancelOrder','api/:version.MuyajuCms/cancelOrder');
//设置预约时间
Route::post('api/:version/Muyaju/SetBookTime','api/:version.MuyajuCms/setBookTime');


//客户端接口
//记录商品分享次数
Route::post('api/:version/Muyaju/countShare','api/:version.MuyajuUser/countProductShare');
//前端获取预约时间
Route::post('api/:version/Muyaju/GetBookTime','api/:version.MuyajuUser/getBookTime');