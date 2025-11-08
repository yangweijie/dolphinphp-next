<?php

namespace app\middleware;

use app\service\UserService;
use think\facade\Request;
use think\Response;

/**
 * 权限验证中间件
 * Class Permission
 * @package app\middleware
 */
class Permission
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @param string $permission 权限标识
     * @return Response
     */
    public function handle($request, \Closure $next, $permission = '')
    {
        // 检查是否已通过认证
        if (!isset($request->userData)) {
            return json(['code' => 401, 'msg' => '请先登录']);
        }
        
        // 获取用户数据
        $userData = $request->userData;
        
        // 创建用户服务实例
        $userService = new UserService();
        
        // 检查权限
        if (!$userService->checkPermission($permission)) {
            return json(['code' => 403, 'msg' => '权限不足']);
        }
        
        return $next($request);
    }
}