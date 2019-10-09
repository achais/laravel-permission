<?php

namespace App\Http\Middleware;

use Closure;

class AuthCheckRoles
{
    /**
     * 检验该接口用户是否有权限
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $confirm = false;
        $route = '/' . $request->path();
        $method = $request->method();
        $guardName = config('permission.role_guard_name');

        //替换里面的路由的变量
        $route = $this->replaceVariables($route);
        if (\Auth::guard($guardName)->check()) {
            $currentUser = \Auth::guard($guardName)->user();
            $menus = $currentUser->getMenuTree(null, true);
            $confirm = $this->hasConfirm($menus, $route, $method);
        }
        if (!$confirm) {
            $content = [
                'errmsg' => '未经授权',
                'errcode' => '4001'
            ];
            return \Response::make($content, 500);
        }

        return $next($request);
    }

    private function hasConfirm($menus, $route, $method)
    {
        foreach ($menus as $menu) {
            if ($menu['method'] == $method && $menu['route'] == $route) {
                return true;
            }

            if (isset($menu['children']) && !empty($menu['children'])) {
                if ($this->hasConfirm($menu['children'], $route, $method)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 替换里面的路由的变量
     * @param $route
     * @return string
     */
    private function replaceVariables($route)
    {
        $routeArray = explode('/', $route);
        foreach ($routeArray as &$value) {
            if (is_numeric($value)) {
                $value = ':num';
            }
        }
        return implode('/', $routeArray);
    }
}
