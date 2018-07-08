<?php

use think\Route;

//cms端接口


//客户端接口
//取消预约
Route::post('api/:version/theMaze/CancelBook','api/:version.ThemazeUser/cancelBook');