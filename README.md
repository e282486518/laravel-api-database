
### 使用API作为数据源构建模型

本扩展使用API作为数据源构建模型，在项目中犹如使用MySQL一样。

其原理是将数据库连接改成API查询, 最终将SQL语句转化成类似`https://api.xxxx.cn/path/数据表名?查询条件1=值1&查询条件2=值2&order=排序` 从api中获取数据并构建`Model`.

### 登录时使用数仓API登录

本扩展不包括在laravel登录中调用API，如需调用参考guard配置。

需添加 `自定义Guard` 配置

创建一个新的Guard类，在app/Providers/AuthServiceProvider.php文件中注册该Guard类。

使用新的Guard：在需要使用新的Guard的地方，可以通过auth()->guard(‘guardName’)方法来指定使用哪个Guard进行认证。


### 设置使用数仓API登录

修改文件: config/database.php
```php
    'connections' => [
        'api' => [
            'driver' => 'api',
            'database' => 'https://api.xxxxx.cn/api/v1/admin',

            // You can define headers that will be sent to the API service in each request.
            // You might need to put your authentication token in a header.
            'headers' => [
                'Authorization: Bearer 7NOxxxxxxxxxxxxx502',
            ],

            // If the API service has Laravel Passport Client Credentials authentication,
            // you can define client ID and client secret here:
            /*'auth' => [
                'type' => 'passport_client_credentials',
                'url' => 'https://example.com/oauth/token',
                'client_id' => 1,
                'client_secret' => 'SECRET_HERE',
            ],*/

            // Define default query parameters.
            'default_params' => [
                'per_page' => 1000,
                'page' => 1,  // this parameter is required
                'size' => 200,
            ],

            // If the generated URL is longer than **max_url_length**,
            // its query string will be split into several parts, and the data will be retrieved for each part separately.
            'max_url_length' => 8000,  // default: 4000

            // The following configuration will generate the following query string
            // for ->whereIn('id', [1,3]):  ids[]=1&ids[]=3
            'pluralize_array_query_params' => true,  // default: false
            'pluralize_except' => ['meta'],  // pluralization skips these query params

            // If the API service provides its clients with time values in a different time zone,
            // you can define the following configuration, which will enable automatic time zone conversion.
            'timezone' => 'Europe/Kiev',
            'datetime_keys' => ['ctime'],
        ],
    ],

```


修改文件: config/admin.php
```php
    'auth' => [
        'providers' => [
            'admin' => [
                //'driver' => 'eloquent',
                //'model'  => Dcat\Admin\Models\Administrator::class,
                'driver' => 'api',
                'model'  => App\Models\AdministratorApi::class,
            ],
        ],
    ],

```


修改文件: app/Admin/routes.php
```php
    // 数据中心员工管理
    $router->get('/staff-api', 'AdminApiController@index');
    $router->get('/staff-api/{id}/edit', 'AdminApiController@edit');
    $router->put('/staff-api/{id}', 'AdminApiController@update');

```


修改文件: app/Providers/AuthServiceProvider.php
```php
    public function boot()
    {
        // xxxxxxxxxxx
        Auth::provider('api', function ($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...
            return new ApiUserProvider($config['model']);
        });
        //
    }

```


- 新增文件: app/Providers/ApiUserProvider.php
- 新增文件: app/Models/AdministratorApi.php
- 新增文件: app/Core/Traits/HasPermissions.php
- 新增文件: app/Admin/Controllers/AdminApiController.php

登录后台, 在`系统->菜单`中修改管理员的`/admin/auth/users`为`/admin/admin-api`


