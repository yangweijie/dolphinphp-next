# API开发指南

## 概述

海豚PHP提供了一套完整的RESTful API开发框架，支持快速构建高性能的API接口。

## API设计原则

### RESTful原则
- 使用HTTP方法表示操作（GET、POST、PUT、DELETE）
- 使用资源URI表示数据实体
- 使用HTTP状态码表示操作结果
- 使用统一的响应格式

### 版本控制
所有API都应该包含版本号，例如：
- `/api/v1/users`
- `/api/v2/products`

### 响应格式
统一使用JSON格式响应：
```json
{
    "success": true,
    "code": 200,
    "message": "操作成功",
    "data": {
        // 业务数据
    },
    "meta": {
        // 分页等元数据
    }
}
```

## 目录结构

```
app/
├── api/                 # API控制器目录
│   ├── v1/             # API版本1
│   │   ├── User.php    # 用户API
│   │   └── Product.php # 产品API
│   └── v2/             # API版本2
├── service/            # 服务层
├── model/              # 数据模型
└── middleware/         # 中间件
```

## 基础API控制器

```php
<?php
namespace app\api\v1;

use think\Request;
use think\Response;
use app\BaseController;
use app\service\UserService;

class User extends BaseController
{
    protected $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    // 获取用户列表
    public function index(Request $request)
    {
        $page = $request->param('page', 1);
        $pageSize = $request->param('pageSize', 15);
        
        $result = $this->userService->getUsers($page, $pageSize);
        
        return $this->success($result);
    }
    
    // 获取单个用户
    public function read($id)
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return $this->error('用户不存在', 404);
        }
        
        return $this->success($user);
    }
    
    // 创建用户
    public function save(Request $request)
    {
        $data = $request->param();
        
        // 数据验证
        $validate = new \app\validate\User();
        if (!$validate->check($data)) {
            return $this->error($validate->getError(), 400);
        }
        
        $user = $this->userService->createUser($data);
        
        return $this->success($user, '创建成功', 201);
    }
    
    // 更新用户
    public function update(Request $request, $id)
    {
        $data = $request->param();
        
        $user = $this->userService->updateUser($id, $data);
        
        if (!$user) {
            return $this->error('用户不存在', 404);
        }
        
        return $this->success($user, '更新成功');
    }
    
    // 删除用户
    public function delete($id)
    {
        $result = $this->userService->deleteUser($id);
        
        if (!$result) {
            return $this->error('用户不存在', 404);
        }
        
        return $this->success(null, '删除成功');
    }
    
    // 成功响应
    protected function success($data = null, $message = '操作成功', $code = 200)
    {
        return json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    // 错误响应
    protected function error($message = '操作失败', $code = 400)
    {
        return json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => null
        ]);
    }
}
```

## 服务层示例

```php
<?php
namespace app\service;

use app\BaseService;
use think\facade\Db;

class UserService extends BaseService
{
    // 获取用户列表（带分页）
    public function getUsers($page = 1, $pageSize = 15)
    {
        $query = Db::name('users')
            ->where('status', 1)
            ->field('id,username,email,created_at');
        
        $total = $query->count();
        $users = $query->page($page, $pageSize)->select();
        
        return [
            'items' => $users,
            'pagination' => [
                'total' => $total,
                'currentPage' => $page,
                'pageSize' => $pageSize,
                'totalPages' => ceil($total / $pageSize)
            ]
        ];
    }
    
    // 根据ID获取用户
    public function getUserById($id)
    {
        return Db::name('users')
            ->where('id', $id)
            ->where('status', 1)
            ->field('id,username,email,created_at')
            ->find();
    }
    
    // 创建用户
    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        $id = Db::name('users')->insertGetId($data);
        
        return $this->getUserById($id);
    }
    
    // 更新用户
    public function updateUser($id, $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $result = Db::name('users')
            ->where('id', $id)
            ->update($data);
        
        if ($result) {
            return $this->getUserById($id);
        }
        
        return false;
    }
    
    // 删除用户（软删除）
    public function deleteUser($id)
    {
        return Db::name('users')
            ->where('id', $id)
            ->update([
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
```

## 数据验证

```php
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username' => 'require|length:3,20|unique:users',
        'email'    => 'require|email|unique:users',
        'password' => 'require|length:6,20',
    ];
    
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.length'  => '用户名长度必须在3到20个字符之间',
        'username.unique'  => '用户名已存在',
        'email.require'    => '邮箱不能为空',
        'email.email'      => '邮箱格式不正确',
        'email.unique'     => '邮箱已存在',
        'password.require' => '密码不能为空',
        'password.length'  => '密码长度必须在6到20个字符之间',
    ];
    
    // 场景验证
    protected $scene = [
        'create' => ['username', 'email', 'password'],
        'update' => ['username', 'email'],
        'login'  => ['username', 'password'],
    ];
}
```

## API路由配置

```php
// route/api.php

use think\facade\Route;

// 用户API
Route::group('api/v1', function () {
    // 用户资源
    Route::resource('users', 'api/v1.User');
    
    // 自定义路由
    Route::post('users/:id/activate', 'api/v1.User/activate');
    Route::post('users/:id/deactivate', 'api/v1.User/deactivate');
    
    // 批量操作
    Route::post('users/batch-delete', 'api/v1.User/batchDelete');
    Route::post('users/batch-update', 'api/v1.User/batchUpdate');
    
})->allowCrossDomain();

// 产品API
Route::group('api/v1', function () {
    Route::resource('products', 'api/v1.Product');
})->allowCrossDomain();

// 认证相关
Route::group('api/v1', function () {
    Route::post('auth/login', 'api/v1.Auth/login');
    Route::post('auth/logout', 'api/v1.Auth/logout');
    Route::post('auth/refresh', 'api/v1.Auth/refresh');
    Route::get('auth/user', 'api/v1.Auth/user');
})->allowCrossDomain();
```

## 中间件

### 跨域中间件

```php
<?php
namespace app\middleware;

use think\Request;
use think\Response;

class AllowCrossDomain
{
    public function handle(Request $request, \Closure $next)
    {
        $response = $next($request);
        
        if ($response instanceof Response) {
            $response->header([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }
        
        return $response;
    }
}
```

### 认证中间件

```php
<?php
namespace app\middleware;

use think\Request;
use think\Response;
use app\service\AuthService;

class AuthToken
{
    public function handle(Request $request, \Closure $next)
    {
        $token = $request->header('Authorization');
        
        if (!$token || !preg_match('/^Bearer\s+(.+)$/i', $token, $matches)) {
            return $this->unauthorized('缺少认证令牌');
        }
        
        $token = $matches[1];
        $authService = app(AuthService::class);
        
        if (!$authService->validateToken($token)) {
            return $this->unauthorized('认证令牌无效');
        }
        
        return $next($request);
    }
    
    protected function unauthorized($message)
    {
        return json([
            'success' => false,
            'code' => 401,
            'message' => $message,
            'data' => null
        ]);
    }
}
```

## 错误处理

### 全局异常处理

```php
// app/ExceptionHandle.php

public function render($request, Throwable $e): Response
{
    // API请求统一返回JSON格式错误
    if ($request->isAjax() || strpos($request->pathinfo(), 'api/') === 0) {
        $code = $e->getCode();
        $message = $e->getMessage();
        
        // 数据库错误
        if ($e instanceof \think\db\exception\DbException) {
            $code = 500;
            $message = '数据库操作失败';
        }
        
        // 验证错误
        if ($e instanceof \think\exception\ValidateException) {
            $code = 400;
            $message = $e->getError();
        }
        
        return json([
            'success' => false,
            'code' => $code ?: 500,
            'message' => $message,
            'data' => null
        ]);
    }
    
    return parent::render($request, $e);
}
```

## API文档生成

### 使用OpenAPI/Swagger

```php
/**
 * @OA\Info(
 *     title="用户管理API",
 *     version="1.0.0",
 *     description="用户管理相关接口"
 * )
 */

class User extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="获取用户列表",
     *     tags={"用户"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="pageSize",
     *         in="query",
     *         description="每页数量",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(property="pagination", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // 实现代码
    }
}
```

## 性能优化

### 1. 数据库优化
```php
// 使用索引优化查询
$query->where('status', 1)
      ->field('id,username,email') // 只选择需要的字段
      ->order('created_at DESC')
      ->page($page, $pageSize);
```

### 2. 缓存策略
```php
// 使用Redis缓存
$users = Cache::remember('users:list:' . $page, 3600, function () use ($page, $pageSize) {
    return $this->userService->getUsers($page, $pageSize);
});
```

### 3. 批量操作
```php
// 批量插入
Db::name('users')->insertAll($userData);

// 批量更新
foreach ($userIds as $userId) {
    Db::name('users')->where('id', $userId)->update($data);
}
```

## 测试

### 单元测试示例

```php
<?php
namespace tests\api\v1;

use tests\TestCase;
use think\facade\Db;

class UserTest extends TestCase
{
    public function testGetUsers()
    {
        $response = $this->get('/api/v1/users');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'items',
                'pagination'
            ]
        ]);
    }
    
    public function testCreateUser()
    {
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123'
        ];
        
        $response = $this->post('/api/v1/users', $data);
        
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => '创建成功'
        ]);
        
        // 验证数据是否插入数据库
        $this->seeInDatabase('users', ['username' => 'testuser']);
    }
}
```

## 部署与监控

### 1. Nginx配置
```nginx
server {
    listen 80;
    server_name api.example.com;
    
    root /path/to/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    # API缓存配置
    location ~* \.(json)$ {
        expires 1h;
        add_header Cache-Control "public";
    }
}
```

### 2. 监控指标
- 响应时间
- 错误率
- 请求频率
- 数据库查询性能

## 安全最佳实践

### 1. 输入验证
```php
// 使用验证器
$validate = new UserValidate();
if (!$validate->scene('create')->check($data)) {
    throw new ValidateException($validate->getError());
}
```

### 2. SQL注入防护
```php
// 使用参数绑定
Db::name('users')->where('username', $username)->find();

// 避免直接拼接SQL
// 错误示例: Db::query("SELECT * FROM users WHERE username = '" . $username . "'");
```

### 3. XSS防护
```php
// 输出转义
htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### 4. CSRF防护
```php
// 启用CSRF保护
'middleware' => [
    \think\middleware\Csrf::class
]
```

## 常见问题

### Q: 如何处理API版本升级？
A: 保持向后兼容，逐步弃用旧版本，提供迁移指南。

### Q: 如何实现API限流？
A: 使用中间件实现基于IP、用户或接口的限流。

### Q: 如何调试API问题？
A: 启用详细错误日志，使用Postman等工具测试接口。

### Q: 如何保证API性能？
A: 使用缓存、数据库优化、代码优化等手段提升性能。