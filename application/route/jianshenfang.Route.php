<?php

use think\Route;
//健身房项目接口


//jsf_challenge挑战赛接口

//cms端
//添加挑战赛
Route::post('api/:version/JsfChallenge/Add','api/:version.JsfChallenge/addChallenge');
//修改挑战赛
Route::post('api/:version/JsfChallenge/Edit','api/:version.JsfChallenge/editChallenge');
//软删除挑战赛
Route::post('api/:version/JsfChallenge/Del','api/:version.JsfChallenge/delChallenge');
//获取挑战赛列表
Route::post('api/:version/JsfChallenge/GetList','api/:version.JsfChallenge/getList');
//获取指定挑战赛信息
Route::post('api/:version/JsfChallenge/GetInfo','api/:version.JsfChallenge/getInfo');

//客户端
//获取挑战赛列表
Route::post('api/:version/JsfUserChallenge/GetList','api/:version.JsfUserChallenge/getList');
//获取指定挑战赛信息
Route::post('api/:version/JsfUserChallenge/GetInfo','api/:version.JsfUserChallenge/getInfo');



//jsf_clock打卡榜

//cms端
//获取打卡记录列表
Route::post('api/:version/JsfClock/GetList','api/:version.JsfClock/getList');
//获取指定打卡记录
Route::post('api/:version/JsfClock/GetInfo','api/:version.JsfClock/getInfo');
//软删除打卡记录
Route::post('api/:version/JsfClock/Del','api/:version.JsfClock/delClock');
//设置打卡参数
Route::post('api/:version/JsfCms/SetCustomRule','api/:version.JsfCms/setCustomRule');

//客户端
//打卡
Route::post('api/:version/JsfUserClock/Add','api/:version.JsfUserClock/addClock');
//获取我的打卡记录
Route::post('api/:version/JsfUserClock/GetList','api/:version.JsfUserClock/getList');