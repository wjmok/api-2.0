<?php

use think\Route;
//西工大项目接口


//bookmenu菜单接口

//cms端
//添加菜单
Route::post('api/:version/XgdMenu/Add','api/:version.XgdMenu/addMenu');
//修改菜单
Route::post('api/:version/XgdMenu/Edit','api/:version.XgdMenu/editMenu');
//软删除菜单
Route::post('api/:version/XgdMenu/Del','api/:version.XgdMenu/delMenu');
//获取菜单层级
Route::post('api/:version/XgdMenu/GetTree','api/:version.XgdMenu/getTree');
//获取菜单列表
Route::post('api/:version/XgdMenu/GetList','api/:version.XgdMenu/getList');
//获取指定菜单信息
Route::post('api/:version/XgdMenu/GetInfo','api/:version.XgdMenu/getInfo');

//客户端
//获取menu列表
Route::post('api/:version/XgdUserMenu/GetList','api/:version.XgdUserMenu/getList');
//获取menu层级树
Route::post('api/:version/XgdUserMenu/GetTree','api/:version.XgdUserMenu/getTree');
//获取指定menu信息
Route::post('api/:version/XgdUserMenu/GetInfo','api/:version.XgdUserMenu/getInfo');



//book预约信息

//cms端
//新增预约
Route::post('api/:version/XgdBook/Add','api/:version.XgdBook/addBook');
//修改预约
Route::post('api/:version/XgdBook/Edit','api/:version.XgdBook/editBook');
//软删除预约
Route::post('api/:version/XgdBook/Del','api/:version.XgdBook/delBook');
//获取预约列表
Route::post('api/:version/XgdBook/GetList','api/:version.XgdBook/getList');
//获取指定预约信息
Route::post('api/:version/XgdBook/GetInfo','api/:version.XgdBook/getInfo');
//报名信息Excel导出
Route::get('api/:version/XgdBook/GetExcel/:token','api/:version.XgdBook/getExcel');
//学生信息Excel导出
Route::get('api/:version/XgdBook/GetStudentExcel/:token','api/:version.XgdBook/getStudentExcel');

//客户端
//获取预约列表
Route::post('api/:version/XgdUserBook/GetList','api/:version.XgdUserBook/getList');
//获取指定预约信息
Route::post('api/:version/XgdUserBook/GetInfo','api/:version.XgdUserBook/getInfo');



//book_order预约报名

//cms
//修改报名信息
Route::post('api/:version/XgdBookOrder/Edit','api/:version.XgdBookOrder/editOrder');
//删除报名信息
Route::post('api/:version/XgdBookOrder/Del','api/:version.XgdBookOrder/delOrder');
//获取报名列表
Route::post('api/:version/XgdBookOrder/GetList','api/:version.XgdBookOrder/getList');
//获取指定预约信息
Route::post('api/:version/XgdBookOrder/GetInfo','api/:version.XgdBookOrder/getInfo');

//客户端
//报名活动
Route::post('api/:version/XgdUserBookOrder/Add','api/:version.XgdUserBookOrder/addOrder');
//参加活动-小程序端扫描二维码调用此接口
Route::post('api/:version/XgdUserBookOrder/Join','api/:version.XgdUserBookOrder/joinOrder');
//取消报名
Route::post('api/:version/XgdUserBookOrder/Cancel','api/:version.XgdUserBookOrder/cancelOrder');
//获取个人报名列表
Route::post('api/:version/XgdUserBookOrder/GetList','api/:version.XgdUserBookOrder/getList');
//获取指定报名信息
Route::post('api/:version/XgdUserBookOrder/GetInfo','api/:version.XgdUserBookOrder/getInfo');
//获取活动统计信息
Route::post('api/:version/XgdUserBookOrder/GetStatistics','api/:version.XgdUserBookOrder/getStatistics');
//修改报名信息
Route::post('api/:version/XgdUserBookOrder/Edit','api/:version.XgdUserBookOrder/editOrder');



//remark
//客户端
//评论活动
Route::post('api/:version/XgdUserRemark/Add','api/:version.XgdUserRemark/addRemark');
//获取评论列表
Route::post('api/:version/XgdUserRemark/GetList','api/:version.XgdUserRemark/getList');