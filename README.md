<h1 align="center"> laravel-permission </h1>

<p align="center"> 📚 基于 spatie/laravel-permission 二次开发的按钮级权限管理 Laravel 扩展包。(角色、权限、菜单、按钮) </p>

## 环境
- php >= 7.0
- laravel/framework >= 5.5

## 安装

```shell
$ composer require achais/laravel-permission -vvv
```

## Laravel
生成配置文件
```shell
# 如果你安装过 spatie/laravel-permission 请忽略这步
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="config"
```

在 permission.php 对应位置中加入菜单配置信息

```php
<?php

return [
    'models' => [
        // ...
        'menu' => Achais\Permission\Models\Menu::class,
    ],

    'table_names' => [
        // ...
        'menus' => 'menus',
        'role_has_menus' => 'role_has_menus',
    ]
];
```

生成迁移文件 
```shell
# 如果你安装过 spatie/laravel-permission 请忽略这步
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"

# 这是本包提供的生成 menus 和 role_has_menus 数据库表的 migrations
php artisan vendor:publish --provider="Achais\Permission\PermissionServiceProvider" --tag="migrations"
```

接下来在使用 migrations 生成数据库表
```shell
php artisan migrate
```

## 使用

TODO

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/achais/laravel-admin-permission/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/achais/laravel-admin-permission/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT
