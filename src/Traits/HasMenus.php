<?php

namespace Achais\Permission\Traits;

use Achais\Permission\Models\Menu;

trait HasMenus
{
    private $menuClass;

    public function getMenuClass()
    {
        if (!isset($this->menuClass)) {
            $this->menuClass = config('permission.models.menu');
        }

        return $this->menuClass;
    }

    public function giveMenuTo(...$menus)
    {
        $menus = collect($menus)
            ->flatten()
            ->map(function ($menu) {
                if (empty($menu)) {
                    return false;
                }

                return $this->getStoredMenu($menu);
            })
            ->filter(function ($menu) {
                return $menu instanceof Menu;
            })
            ->each(function ($menu) {
            })
            ->map->id
            ->all();

        $model = $this->getModel();

        if ($model->exists) {
            $this->menus()->sync($menus, false);
            $model->load('menus');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($menus, $model) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $model) {
                        return;
                    }
                    $object->menus()->sync($menus, false);
                    $object->load('menus');
                    $modelLastFiredOn = $object;
                }
            );
        }

        return $this;
    }

    protected function getStoredMenu($menus)
    {
        $menuClass = $this->getMenuClass();

        if (is_numeric($menus)) {
            return $menuClass->findById($menus);
        }

        if (is_string($menus)) {
            return $menuClass->findByName($menus);
        }

        if (is_array($menus)) {
            return $menuClass
                ->whereIn('name', $menus)
                ->get();
        }

        return $menus;
    }
}
