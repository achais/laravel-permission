<?php

namespace Achais\Permission\Models;

use Achais\Permission\Contracts\Role as ContractRole;
use Achais\Permission\Traits\HasMenus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole implements ContractRole
{
    use HasMenus;

    public function menus(): BelongsToMany
    {
        return self::belongsToMany(
            config('permission.models.menu'),
            config('permission.table_names.role_has_menus'),
            'role_id',
            'menu_id'
        );
    }
}
