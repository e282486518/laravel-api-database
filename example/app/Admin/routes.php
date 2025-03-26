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
    $router->get('/staff-api', 'AdminApiController@index');
    $router->get('/staff-api/{id}/edit', 'AdminApiController@edit');
    $router->put('/staff-api/{id}', 'AdminApiController@update');
});
