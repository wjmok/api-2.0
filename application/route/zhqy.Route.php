<?php

use think\Route;
/**
 * 智慧权益项目
 */

//cms端接口
/*获取用户发布列表*/
Route::post('api/:version/ZhqyCms/GetList','api/:version.ZhqyCms/getList');
/*获取指定商品*/
Route::post('api/:version/ZhqyCms/GetInfo','api/:version.ZhqyCms/getInfo');
/*通过商品*/
Route::post('api/:version/ZhqyCms/PassProduct','api/:version.ZhqyCms/passProduct');
/*审核未通过商品*/
Route::post('api/:version/ZhqyCms/RejectProduct','api/:version.ZhqyCms/rejectProduct');

//客户端接口
/*发布商品*/
Route::post('api/:version/ZhqyUser/SubmitProduct','api/:version.ZhqyUser/submitProduct');
/*获取我的发布列表*/
Route::post('api/:version/ZhqyUser/GetList','api/:version.ZhqyUser/getList');
/*获取指定的我的发布信息*/
Route::post('api/:version/ZhqyUser/GetInfo','api/:version.ZhqyUser/getInfo');