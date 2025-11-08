<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2023 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// 定义默认的应用
Route::get('/', 'Index/index');

// 用户相关路由
Route::group('user', function () {
    Route::post('login', 'User/login');
    Route::get('logout', 'User/logout');
    Route::get('current', 'User/getCurrentUser');
    Route::get('list', 'User/getList');
    Route::get('info/:id', 'User/getInfo');
    Route::post('create', 'User/create');
    Route::put('update/:id', 'User/update');
    Route::delete('delete/:id', 'User/delete');
    Route::post('batch_delete', 'User/batchDelete');
    Route::put('status/:id', 'User/modifyStatus');
    Route::post('change_password', 'User/changePassword');
    Route::get('roles', 'User/getRoles');
    Route::get('status_list', 'User/getStatusList');
    Route::get('statistics', 'User/getStatistics');
});

// 表单构建器路由
Route::group('form', function () {
    Route::get('list', 'Form/getList');
    Route::get('info/:id', 'Form/getInfo');
    Route::get('by_name/:name', 'Form/getByName');
    Route::post('create', 'Form/create');
    Route::put('update/:id', 'Form/update');
    Route::delete('delete/:id', 'Form/delete');
    Route::post('batch_delete', 'Form/batchDelete');
    Route::put('status/:id', 'Form/modifyStatus');
    Route::post('copy/:id', 'Form/copy');
    Route::get('field_types', 'Form/getFieldTypes');
    Route::get('statistics', 'Form/getStatistics');
});

// 表格构建器路由
Route::group('table', function () {
    Route::get('list', 'Table/getList');
    Route::get('info/:id', 'Table/getInfo');
    Route::get('by_name/:name', 'Table/getByName');
    Route::post('create', 'Table/create');
    Route::put('update/:id', 'Table/update');
    Route::delete('delete/:id', 'Table/delete');
    Route::post('batch_delete', 'Table/batchDelete');
    Route::put('status/:id', 'Table/modifyStatus');
    Route::post('copy/:id', 'Table/copy');
    Route::get('column_types', 'Table/getColumnTypes');
    Route::get('data_source_types', 'Table/getDataSourceTypes');
    Route::get('data/:name', 'Table/getData');
    Route::get('statistics', 'Table/getStatistics');
});

// Yoyo 集成端点
Route::any('ui/yoyo', 'Ui/yoyo');
Route::get('ui/component/:name', 'Ui/component')->pattern(['name' => '[a-zA-Z0-9-]+']);
