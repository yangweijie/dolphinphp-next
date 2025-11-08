# 权限管理开发指南

## 概述

海豚PHP的权限管理系统基于RBAC（基于角色的访问控制）模型，提供灵活的权限管理和访问控制功能。

## 核心概念

### 用户 (User)
系统使用者，可以分配一个或多个角色。

### 角色 (Role)
权限的集合，用户通过角色获得权限。

### 权限 (Permission)
具体的操作权限，如"用户管理"、"文章编辑"等。

### 资源 (Resource)
被保护的系统资源，如页面、API接口、数据等。

## 数据库设计

### 用户表 (users)
```sql
CREATE TABLE `dp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
);
```

### 角色表 (roles)
```sql
CREATE TABLE `dp_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);
```

### 权限表 (permissions)
```sql
CREATE TABLE `dp_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `resource` varchar(100) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
);
```

### 用户角色关联表 (user_roles)
```sql
CREATE TABLE `dp_user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`)
);
```

### 角色权限关联表 (role_permissions)
```sql
CREATE TABLE `dp_role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`)
);
```

## 核心服务

### 认证服务 (AuthService)

```php
<?php
namespace app\service;

use app\BaseService;
use think\facade\Session;
use think\facade\Db;

class AuthService extends BaseService
{
    // 用户登录
    public function login($username, $password)
    {
        $user = Db::name('users')
            ->where('username', $username)
            ->where('status', 1)
            ->find();
            
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        // 保存用户信息到session
        Session::set('user_id', $user['id']);
        Session::set('user_info', $user);
        
        return $user;
    }
    
    // 用户退出
    public function logout()
    {
        Session::delete('user_id');
        Session::delete('user_info');
        return true;
    }
    
    // 获取当前用户
    public function getUser()
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return null;
        }
        
        return Db::name('users')->where('id', $userId)->find();
    }
    
    // 检查是否登录
    public function isLoggedIn()
    {
        return Session::has('user_id');
    }
}
```

### 权限服务 (PermissionService)

```php
<?php
namespace app\service;

use app\BaseService;
use think\facade\Db;

class PermissionService extends BaseService
{
    // 检查用户是否有权限
    public function can($userId, $permissionName)
    {
        $permission = Db::name('permissions')
            ->where('name', $permissionName)
            ->find();
            
        if (!$permission) {
            return false;
        }
        
        // 检查用户是否直接拥有该权限
        $directPermission = Db::name('user_permissions')
            ->where('user_id', $userId)
            ->where('permission_id', $permission['id'])
            ->find();
            
        if ($directPermission) {
            return true;
        }
        
        // 检查用户角色是否拥有该权限
        $rolePermission = Db::name('user_roles ur')
            ->join('role_permissions rp', 'ur.role_id = rp.role_id')
            ->where('ur.user_id', $userId)
            ->where('rp.permission_id', $permission['id'])
            ->find();
            
        return (bool)$rolePermission;
    }
    
    // 获取用户所有权限
    public function getUserPermissions($userId)
    {
        // 直接权限
        $directPermissions = Db::name('user_permissions up')
            ->join('permissions p', 'up.permission_id = p.id')
            ->where('up.user_id', $userId)
            ->column('p.name');
            
        // 角色权限
        $rolePermissions = Db::name('user_roles ur')
            ->join('role_permissions rp', 'ur.role_id = rp.role_id')
            ->join('permissions p', 'rp.permission_id = p.id')
            ->where('ur.user_id', $userId)
            ->column('p.name');
            
        return array_unique(array_merge($directPermissions, $rolePermissions));
    }
    
    // 获取用户角色
    public function getUserRoles($userId)
    {
        return Db::name('user_roles ur')
            ->join('roles r', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->column('r.name');
    }
}
```

## 中间件

### 认证中间件

```php
<?php
namespace app\middleware;

use think\Request;
use think\Response;
use app\service\AuthService;

class AuthMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        $authService = app(AuthService::class);
        
        if (!$authService->isLoggedIn()) {
            if ($request->isAjax()) {
                return json([
                    'success' => false,
                    'message' => '请先登录',
                    'code' => 401
                ]);
            } else {
                return redirect('/login');
            }
        }
        
        return $next($request);
    }
}
```

### 权限中间件

```php
<?php
namespace app\middleware;

use think\Request;
use think\Response;
use app\service\PermissionService;

class PermissionMiddleware
{
    public function handle(Request $request, \Closure $next, $permission)
    {
        $authService = app(AuthService::class);
        $permissionService = app(PermissionService::class);
        
        $user = $authService->getUser();
        if (!$user) {
            return $this->unauthorized($request);
        }
        
        if (!$permissionService->can($user['id'], $permission)) {
            return $this->forbidden($request);
        }
        
        return $next($request);
    }
    
    protected function unauthorized($request)
    {
        if ($request->isAjax()) {
            return json([
                'success' => false,
                'message' => '请先登录',
                'code' => 401
            ]);
        } else {
            return redirect('/login');
        }
    }
    
    protected function forbidden($request)
    {
        if ($request->isAjax()) {
            return json([
                'success' => false,
                'message' => '权限不足',
                'code' => 403
            ]);
        } else {
            return redirect('/403');
        }
    }
}
```

## API接口

### 用户登录

```http
POST /api/auth/login
Content-Type: application/json

{
    "username": "admin",
    "password": "password123"
}
```

响应示例：
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "username": "admin",
            "email": "admin@example.com"
        },
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
}
```

### 获取当前用户信息

```http
GET /api/auth/user
Authorization: Bearer {token}
```

响应示例：
```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "admin",
        "email": "admin@example.com",
        "roles": ["admin"],
        "permissions": ["user.manage", "content.edit"]
    }
}
```

### 检查权限

```http
GET /api/auth/can?permission=user.manage
Authorization: Bearer {token}
```

响应示例：
```json
{
    "success": true,
    "data": {
        "can": true
    }
}
```

## 前端集成

### 登录页面示例

```html
<!-- public/static/pages/login.html -->
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                登录系统
            </h2>
        </div>
        
        <form class="mt-8 space-y-6" 
              hx-post="/api/auth/login"
              hx-target="#login-result"
              hx-swap="innerHTML">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <input id="username" name="username" type="text" 
                           required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="用户名">
                </div>
                <div>
                    <input id="password" name="password" type="password" 
                           required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="密码">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    登录
                </button>
            </div>
            
            <div id="login-result"></div>
        </form>
    </div>
</div>
```

### 权限检查组件

```javascript
// public/static/js/auth.js
class Auth {
    constructor() {
        this.user = null;
        this.permissions = [];
        this.loadUserInfo();
    }
    
    // 加载用户信息
    async loadUserInfo() {
        try {
            const response = await fetch('/api/auth/user', {
                headers: {
                    'Authorization': `Bearer ${this.getToken()}`
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                this.user = result.data;
                this.permissions = result.data.permissions || [];
            }
        } catch (error) {
            console.error('Failed to load user info:', error);
        }
    }
    
    // 检查权限
    can(permission) {
        return this.permissions.includes(permission);
    }
    
    // 检查角色
    hasRole(role) {
        return this.user && this.user.roles && this.user.roles.includes(role);
    }
    
    // 获取token
    getToken() {
        return localStorage.getItem('auth_token');
    }
    
    // 保存token
    setToken(token) {
        localStorage.setItem('auth_token', token);
    }
    
    // 清除token
    clearToken() {
        localStorage.removeItem('auth_token');
    }
    
    // 是否已登录
    isLoggedIn() {
        return !!this.getToken();
    }
}

// 全局实例
globalThis.auth = new Auth();

// 权限指令 (用于HTML)
function authDirective() {
    document.addEventListener('DOMContentLoaded', () => {
        // 隐藏无权限的元素
        document.querySelectorAll('[data-auth]').forEach(element => {
            const permission = element.getAttribute('data-auth');
            if (!globalThis.auth.can(permission)) {
                element.style.display = 'none';
            }
        });
        
        // 隐藏无权限的按钮
        document.querySelectorAll('[data-role]').forEach(element => {
            const role = element.getAttribute('data-role');
            if (!globalThis.auth.hasRole(role)) {
                element.style.display = 'none';
            }
        });
    });
}

authDirective();
```

## 最佳实践

### 1. 最小权限原则
只授予用户完成工作所需的最小权限。

### 2. 定期审计
定期审查用户权限，确保权限分配的合理性。

### 3. 权限分组
将相关权限分组到角色中，便于管理。

### 4. 日志记录
记录所有权限相关的操作，便于审计和故障排查。

### 5. 测试覆盖
编写测试用例，确保权限控制逻辑的正确性。

## 安全考虑

### 密码安全
- 使用强密码策略
- 密码加密存储（使用password_hash）
- 定期要求更改密码

### 会话安全
- 使用安全的Session配置
- 设置合理的Session过期时间
- 防止Session固定攻击

### API安全
- 使用HTTPS加密传输
- 实现速率限制
- 验证输入数据

## 常见问题

### Q: 如何实现细粒度的权限控制？
A: 可以使用资源级别的权限控制，为每个资源定义具体的操作权限。

### Q: 如何处理权限缓存？
A: 可以使用Redis等缓存系统缓存用户权限信息，提高性能。

### Q: 如何实现多租户权限？
A: 可以在权限表中添加租户字段，实现基于租户的权限隔离。

### Q: 如何备份和恢复权限数据？
A: 定期导出权限相关的表数据，确保数据安全。