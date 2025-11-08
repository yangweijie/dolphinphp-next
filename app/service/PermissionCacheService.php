<?php

namespace app\service;

use think\facade\Cache;

/**
 * 权限缓存服务类
 * Class PermissionCacheService
 * @package app\service
 */
class PermissionCacheService
{
    /**
     * 缓存前缀
     * @var string
     */
    protected $prefix = 'permission:';
    
    /**
     * 缓存过期时间（秒）
     * @var int
     */
    protected $expire = 3600;
    
    /**
     * 获取用户权限缓存
     * @param int $userId 用户ID
     * @return array|null
     */
    public function getUserPermissions($userId)
    {
        $key = $this->prefix . 'user:' . $userId;
        return Cache::get($key);
    }
    
    /**
     * 设置用户权限缓存
     * @param int $userId 用户ID
     * @param array $permissions 权限列表
     * @return bool
     */
    public function setUserPermissions($userId, $permissions)
    {
        $key = $this->prefix . 'user:' . $userId;
        return Cache::set($key, $permissions, $this->expire);
    }
    
    /**
     * 清除用户权限缓存
     * @param int $userId 用户ID
     * @return bool
     */
    public function clearUserPermissions($userId)
    {
        $key = $this->prefix . 'user:' . $userId;
        return Cache::delete($key);
    }
    
    /**
     * 获取角色权限缓存
     * @param string $role 角色
     * @return array|null
     */
    public function getRolePermissions($role)
    {
        $key = $this->prefix . 'role:' . $role;
        return Cache::get($key);
    }
    
    /**
     * 设置角色权限缓存
     * @param string $role 角色
     * @param array $permissions 权限列表
     * @return bool
     */
    public function setRolePermissions($role, $permissions)
    {
        $key = $this->prefix . 'role:' . $role;
        return Cache::set($key, $permissions, $this->expire);
    }
    
    /**
     * 清除角色权限缓存
     * @param string $role 角色
     * @return bool
     */
    public function clearRolePermissions($role)
    {
        $key = $this->prefix . 'role:' . $role;
        return Cache::delete($key);
    }
    
    /**
     * 清除所有权限缓存
     * @return bool
     */
    public function clearAllPermissions()
    {
        // 这里可以实现清除所有权限相关的缓存
        // 由于ThinkPHP的Cache类没有直接的前缀清除方法，我们需要遍历所有可能的键
        return Cache::clear();
    }
}