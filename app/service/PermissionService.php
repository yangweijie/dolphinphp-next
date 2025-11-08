<?php

namespace app\service;

/**
 * 权限服务类
 * Class PermissionService
 * @package app\service
 */
class PermissionService
{
    /**
     * 权限列表
     * @var array
     */
    protected $permissions = [
        // 表单相关权限
        'form.create' => '创建表单',
        'form.view' => '查看表单',
        'form.edit' => '编辑表单',
        'form.delete' => '删除表单',
        'form.submit' => '提交表单',
        
        // 表格相关权限
        'table.create' => '创建表格',
        'table.view' => '查看表格',
        'table.edit' => '编辑表格',
        'table.delete' => '删除表格',
        'table.edit.data' => '编辑表格数据',
        
        // 用户相关权限
        'user.create' => '创建用户',
        'user.view' => '查看用户',
        'user.edit' => '编辑用户',
        'user.delete' => '删除用户',
        
        // 系统相关权限
        'system.config' => '系统配置',
        'system.log' => '查看日志',
    ];
    
    /**
     * 角色权限映射
     * @var array
     */
    protected $rolePermissions = [
        'admin' => [
            'form.*',
            'table.*',
            'user.*',
            'system.*',
        ],
        'user' => [
            'form.view',
            'form.submit',
            'table.view',
        ],
        'editor' => [
            'form.view',
            'form.submit',
            'table.view',
            'table.edit.data',
        ],
        'manager' => [
            'form.*',
            'table.*',
            'user.view',
        ],
    ];
    
    /**
     * 权限缓存服务
     * @var PermissionCacheService
     */
    protected $cacheService;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->cacheService = new PermissionCacheService();
    }
    
    /**
     * 获取所有权限
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
    
    /**
     * 获取角色权限
     * @param string $role 角色
     * @return array
     */
    public function getRolePermissions($role)
    {
        // 尝试从缓存获取
        $permissions = $this->cacheService->getRolePermissions($role);
        if ($permissions !== null) {
            return $permissions;
        }
        
        // 从配置获取并缓存
        $permissions = $this->rolePermissions[$role] ?? [];
        $this->cacheService->setRolePermissions($role, $permissions);
        
        return $permissions;
    }
    
    /**
     * 检查角色是否有权限
     * @param string $role 角色
     * @param string $permission 权限
     * @return bool
     */
    public function checkRolePermission($role, $permission)
    {
        $permissions = $this->getRolePermissions($role);
        
        // 检查是否有完全匹配的权限
        if (in_array($permission, $permissions)) {
            return true;
        }
        
        // 检查是否有通配符权限
        foreach ($permissions as $perm) {
            if (substr($perm, -1) === '*' && strpos($permission, rtrim($perm, '*')) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取权限描述
     * @param string $permission 权限标识
     * @return string
     */
    public function getPermissionDescription($permission)
    {
        return $this->permissions[$permission] ?? $permission;
    }
    
    /**
     * 获取所有角色
     * @return array
     */
    public function getRoles()
    {
        return array_keys($this->rolePermissions);
    }
    
    /**
     * 清除角色权限缓存
     * @param string $role 角色
     * @return bool
     */
    public function clearRolePermissionCache($role)
    {
        return $this->cacheService->clearRolePermissions($role);
    }
    
    /**
     * 清除所有权限缓存
     * @return bool
     */
    public function clearAllPermissionCache()
    {
        return $this->cacheService->clearAllPermissions();
    }
}