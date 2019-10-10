<?php

namespace Achais\Permission\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Achais\Permission\Exceptions\MenuDoesNotExist;
use Achais\Permission\Exceptions\MenuAlreadyExists;
use Achais\Permission\Contracts\Menu as MenuContract;
use Achais\Permission\Traits\HasRoles;

/**
 * Class Menu
 *
 * @property int $id
 * @property int $level
 * @property int|null $parent_id
 * @property string $parent_path
 * @property string $name
 * @property string $url
 * @property string|null $route
 * @property string|null $method
 * @property int $type
 * @property string|null $sign
 * @property string|null $icon
 * @property string|null $remark
 * @property int $sort
 * @package Achais\Permission\Models
 */
class Menu extends Model implements MenuContract
{
    use HasRoles;

    const TYPE_DIRECTORY = 1;
    const TYPE_MENU = 2;
    const TYPE_BUTTON = 3;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.menus'));
    }

    protected static function boot()
    {
        parent::boot();

        // 监听 Menu 的创建事件，用于初始化 path 和 level 字段值
        static::creating(function (Menu $menu) {
            // 如果创建的是一个根菜单
            if (is_null($menu->parent_id)) {
                $menu->level = 0;
                $menu->parent_path = '-';
            } else {
                // 将层级设为父菜单的层级 + 1
                $menu->level = $menu->parent->level + 1;
                // 将 parent_path 值设为父菜单的 parent_path 追加父菜单 ID 以及最后跟上一个 - 分隔符
                $menu->parent_path = $menu->parent->parent_path . $menu->parent_id . '-';
            }
        });

        // 监听 Menu 的保存事件，用于初始化 path 和 level 字段值
        static::saving(function (Menu $menu) {
            // 如果创建的是一个根菜单
            if (is_null($menu->parent_id)) {
                $menu->level = 0;
                $menu->parent_path = '-';
            } else {
                // 将层级设为父菜单的层级 + 1
                $menu->level = $menu->parent->level + 1;
                // 将 parent_path 值设为父菜单的 parent_path 追加父菜单 ID 以及最后跟上一个 - 分隔符
                $menu->parent_path = $menu->parent->parent_path . $menu->parent_id . '-';
            }
        });
    }

    public static function create(array $attributes = [])
    {
        if (static::where('name', $attributes['name'])->first()) {
            throw MenuAlreadyExists::create($attributes['name']);
        }

        if (isNotLumen() && app()::VERSION < '5.4') {
            return parent::create($attributes);
        }

        return static::query()->create($attributes);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_menus'),
            'menu_id',
            'role_id'
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            config('permission.models.menu'),
            'parent_id',
            'id'
        );
    }


    public function children(): HasMany
    {
        return $this->hasMany(
            config('permission.models.menu'),
            'parent_id',
            'id'
        );
    }

    public static function findByName(string $name): MenuContract
    {
        $menu = static::where('name', $name)->first();

        if (!$menu) {
            throw MenuDoesNotExist::named($name);
        }

        return $menu;
    }

    public static function findById(int $id): MenuContract
    {
        $menu = static::where('id', $id)->first();

        if (!$menu) {
            throw MenuDoesNotExist::withId($id);
        }

        return $menu;
    }

    // 定一个一个访问器，获取所有祖先菜单的 ID 值
    public function getParentIdsAttribute()
    {
        // trim($str, '-') 将字符串两端的 - 符号去除
        // explode() 将字符串以 - 为分隔切割为数组
        // 最后 array_filter 将数组中的空值移除
        return array_filter(explode('-', trim($this->parent_path, '-')));
    }

    // 定义一个访问器，获取所有祖先菜单并按层级排序
    public function getAncestorsAttribute()
    {
        return self::query()
            // 使用上面的访问器获取所有祖先菜单 ID
            ->whereIn('id', $this->parent_ids)
            // 按层级排序
            ->orderBy('level')
            ->get();
    }

    public static function getMenuTree($parentId = null, $allMenus = null, $showButton = false)
    {
        if (is_null($allMenus)) {
            // 从数据库中一次性取出所有菜单
            //$allMenus = Menu::all();
            $allMenus = Menu::query()->orderBy('level')->orderBy('sort')->get();
        } else {
            $allMenus = collect($allMenus)->sortBy('sort');
        }

        return $allMenus
            // 从所有菜单中挑选出父菜单 ID 为 $parentId 的菜单
            ->where('parent_id', $parentId)
            // 遍历这些菜单，并用返回值构建一个新的集合
            ->map(function (Menu $menu) use ($allMenus, $showButton) {
                // 按钮不显示
                if (!$showButton && $menu->type == Menu::TYPE_BUTTON) {
                    return null;
                }

                $data = [
                    'id' => $menu->id,
                    'level' => $menu->level,
                    'name' => $menu->name,
                    'url' => $menu->url,
                    'route' => $menu->route,
                    'method' => $menu->method,
                    'type' => $menu->type,
                    'icon' => $menu->icon,
                    'sign' => $menu->sign,
                    'sort' => $menu->sort,
                    'remark' => $menu->remark,
                ];

                // 如果当前菜单不是父菜单，则直接返回
                if ($menu->type == self::TYPE_BUTTON) {
                    return $data;
                }
                // 否则递归调用本方法，将返回值放入 children 字段中
                $data['children'] = self::getMenuTree($menu->id, $allMenus, $showButton)->filter()->values();

                return $data;
            })->values();
    }

    /**
     * 递归菜单下的按钮
     *
     * @param $menus
     * @param bool $recursive
     * @return array
     */
    public static function recursiveButtons($menus, $recursive = true)
    {
        $buttons = [];
        foreach ($menus as $index => $menu) {
            if ($menu['type'] == Menu::TYPE_BUTTON) {
                array_push($buttons, $menu);
            }

            if ($recursive && isset($menu['children'])) {
                $buttons = array_merge($buttons, self::recursiveButtons($menu['children']));
            }
        }
        return $buttons;
    }
}
