<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use App\Core\Traits\HasPermissions;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class AdministratorApi extends Model implements AuthenticatableContract, Authorizable
{
    use Authenticatable,
        HasPermissions,
        HasDateTimeFormatter;

    const DEFAULT_ID = 1;

    protected $primaryKey = 'admin_id';

    protected $fillable = ['admin_id', 'class_id', 'post', 'mobile', 'realname', 'username', 'password', 'salt', 'last_login_time', 'ctime'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->init();

        parent::__construct($attributes);
    }

    protected function init()
    {
        // 模型使用 database.api 配置
        $this->setConnection('api');

        $this->setTable('list');
    }

    /**
     * Get avatar attribute.
     *
     * @return mixed|string
     */
    public function getAvatar()
    {
        $avatar = $this->avatar;

        if ($avatar) {
            if (! URL::isValidUrl($avatar)) {
                $avatar = Storage::disk(config('admin.upload.disk'))->url($avatar);
            }

            return $avatar;
        }

        return admin_asset(config('admin.default_avatar') ?: '@admin/images/default-avatar.jpg');
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * 判断是否允许查看菜单.
     *
     * @param  array|Menu  $menu
     * @return bool
     */
    public function canSeeMenu($menu)
    {
        return true;
    }

    /**
     * 获取所有管理员姓名，以“id=>姓名”返回
     *
     * @return array
     * @author linhongtong
     * date: 2024/8/16 15:22
     */
    public static function getName($update = false)
    {
        $cacheKey = 'admin_names'; // 缓存键名

        if ($update) {
            $getAll = false;
        } else {
            // 尝试从缓存中获取数据
            $getAll = Cache::get($cacheKey);
        }

        // 如果缓存中没有数据，则从数据库查询并存储到缓存中
        if (empty($getAll)) {
            $getAll = static::query()->pluck('realname', 'admin_id');

            $getAll = !empty($getAll) ? $getAll->toArray() : [];
            // 存储到缓存中，并设置过期时间（例如 24 小时）
            Cache::put($cacheKey, $getAll, now()->addHours(24));
        }

        return $getAll;
    }

    /**
     * 获取所有内部人员信息
     *
     * @param false $update
     * @return array|false|mixed
     * @author linhongtong
     * date: 2024/9/20 14:54
     */
    public static function getAllInfo($update = false)
    {
        $cacheKey = 'all_admin_info'; // 缓存键名

        if ($update) {
            $getAll = false;
        } else {
            // 尝试从缓存中获取数据
            $getAll = Cache::get($cacheKey);
        }

        // 如果缓存中没有数据，则从数据库查询并存储到缓存中
        if (empty($getAll)) {
            $all = static::query()->get();
            if ($all) {
                $getAll = [];
                $all = $all->toArray();
                foreach ($all as $key => $val) {
                    $getAll[$val['admin_id']] = $val;
                }
            }

            // 存储到缓存中，并设置过期时间（例如 24 小时）
            Cache::put($cacheKey, $getAll, now()->addHours(24));
        }

        return $getAll;
    }
}
