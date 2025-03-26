<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    // 数据中心员工管理
    $router->get('/admin-api', 'AdminApiController@index');
    $router->get('/admin-api/{id}/edit', 'AdminApiController@edit');
    $router->put('/admin-api/{id}', 'AdminApiController@update');
});
