<?php

namespace App\Providers;

use e282486518\LaravelApiDatabase\Str;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ApiUserProvider implements UserProvider
{

    // GuzzleHttp 的url部分
    protected $uri;

    // GuzzleHttp 的header部分
    protected $headers;

    // 数据model
    protected $model;

    // 构造函数
    public function __construct($model) {
        $this->model = $model;
        $this->uri = config('database.connections.api.database') . '/detail';
        $this->headers = config('database.connections.api.headers');
    }

    /**
     * 通过用户的唯一标识符检索用户。
     *
     * @param  mixed  $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // 函数通常接收代表用户的键，例如 MySQL 数据库中自增的 ID。
        return $this->fetchUsers(['admin_id' => $identifier, 'pwd' => 1]);
    }

    /**
     * 通过用户的唯一标识符和“记住我”令牌检索用户。
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        // 函数通过其唯一的 $identifier 来检索用户，并将「记住我」 $token 存储在 remember_token 字段中。
    }

    /**
     * 更新存储中给定用户的“记住我”令牌。
     *
     * @param Authenticatable $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // 使用新的 $token 更新了 $user 的 remember_token 字段。
    }

    /**
     * 根据给定的凭据检索用户。
     *
     * @param  array  $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // 用$credentials里面的用户名密码去获取用户信息，然后返回Illuminate\Contracts\Auth\Authenticatable对象
        $credentials = array_filter(
            $credentials,
            fn ($key) => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($credentials)) {
            return null;
        }
        return $this->fetchUsers($credentials);
    }

    /**
     * 根据给定的凭据验证用户。
     *
     * @param Authenticatable $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // 用$credentials里面的用户名密码校验用户，返回true或false
        if (is_null($plain = $credentials['password'])) {
            return false;
        }
        //dd($this->validatePassword($plain, $user->password));
        if ($this->validatePassword($plain, $user->password)) {
            return true;
        }
        return false;
    }

    /**
     * 从api中取数据
     *
     * @param array $options
     *
     * @return Authenticatable
     */
    public function fetchUsers(array $options)
    {
        $client = new \GuzzleHttp\Client([
            'verify' => false // 禁用 SSL 证书验证
        ]);

        try {
            if (!empty($options['username'])) {
                $response = $client->get($this->uri . '?pwd=1&mobile=' . $options['username'], [
                        'headers' => Str::convertHeaders($this->headers, 'array'),
                    ]
                );
            } else if (!empty($options['admin_id'])) {
                $response = $client->get($this->uri . '?pwd=1&admin_id=' . $options['admin_id'], [
                        'headers' => Str::convertHeaders($this->headers, 'array'),
                    ]
                );
            }

        } catch (GuzzleException $exception) {
            logger()->critical($exception->getMessage(), $this->headers);

            return null;
        }

        $data = json_decode($response->getBody(), true);
        $data = $data['obj'];
        $data = array_key_exists('data', $data) ? $data['data'] : $data;
        if (empty($data)) {
            return null;
        }

        return new $this->model($data);
    }

    // 明码->密码
    public function validatePassword($password, $hash) {
        if (!is_string($password) || $password === '') {
            return false;
        }

        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            return false;
        }

        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }
        return false;
    }
}
