<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// 页面配置 - Schema API
Route::group('api/admin', function () {
    Route::get('page-config/schema', 'app\\controller\\admin\\PageConfig@schema');
})->middleware([
    \Thinkrix\Middleware\HandleApiException::class,
    \Thinkrix\Middleware\Authenticate::class,
]);
