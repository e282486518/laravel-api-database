
### 使用API作为数据源构建模型

本扩展使用API作为数据源构建模型，在项目中犹如使用MySQL一样。

### 登录时使用数仓API登录

本扩展不包括在laravel登录中调用API，如需调用参考guard配置。

需添加 `自定义Guard` 配置

创建一个新的Guard类，在app/Providers/AuthServiceProvider.php文件中注册该Guard类。

使用新的Guard：在需要使用新的Guard的地方，可以通过auth()->guard(‘guardName’)方法来指定使用哪个Guard进行认证。
