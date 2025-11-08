<?php

namespace app\middleware;

use app\service\JwtService;
use think\facade\Request;
use think\Response;

/**
 * 认证中间件
 * Class Auth
 * @package app\middleware
 */
class Auth
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 获取JWT Token
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return json(['code' => 401, 'msg' => '未提供认证令牌']);
        }
        
        // 验证Token
        $jwtService = new JwtService();
        if (!$jwtService->verifyToken($token)) {
            return json(['code' => 401, 'msg' => '认证令牌无效或已过期']);
        }
        
        // 获取用户数据并设置到请求中
        $userData = $jwtService->getUserDataFromToken($token);
        $request->userData = $userData;
        
        return $next($request);
    }
    
    /**
     * 从请求中获取Token
     * @param \think\Request $request
     * @return string|null
     */
    protected function getTokenFromRequest($request)
    {
        // 从Header中获取
        $token = $request->header('Authorization');
        if ($token && strpos($token, 'Bearer ') === 0) {
            return substr($token, 7);
        }
        
        // 从查询参数中获取
        $token = $request->param('token');
        if ($token) {
            return $token;
        }
        
        // 从Cookie中获取
        $token = $request->cookie('token');
        if ($token) {
            return $token;
        }
        
        return null;
    }
}