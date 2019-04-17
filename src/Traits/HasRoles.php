<?php

namespace Achais\Permission\Traits;

use Achais\Permission\Models\Role;
use Achais\Permission\Models\Menu;
use Spatie\Permission\Traits\HasRoles as SpatieHasRole;

trait HasRoles
{
    use SpatieHasRole;

    public function getMenuTree($parentId = null, $showButton = false)
    {
        $allMenuIds = $this->roles
            ->map(function ($role) {
                return $role->menus;
            })
            ->collapse()
            ->map(function ($menu) {
                return array_merge(array_filter(explode('-', trim($menu->parent_path, '-'))), [$menu->id]);
            })
            ->collapse()
            ->unique();

        $allMenus = Menu::all()->whereIn('id', $allMenuIds);
        return Menu::getMenuTree($parentId, $allMenus, $showButton);
    }
}
