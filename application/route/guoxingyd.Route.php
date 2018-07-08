<?php

use think\Route;

//cms端接口
/*设置分销比例*/
Route::post('api/:version/Guoxingyd/SetDistribution','api/:version.GuoxingydCms/setDistribution');
/*报名信息Excel导出*/
Route::get('api/:version/Guoxingyd/GetExcel/:token','api/:version.GuoxingydCms/getExcel');

//客户端接口
/*修改客户信息*/
Route::post('api/:version/GuoxingydUser/EditUser','api/:version.GuoxingydUser/editUserinfo');