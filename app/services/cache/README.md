# 配置缓存服务

这是一个为 DolphinPHP 框架设计的配置缓存服务，提供高性能的配置数据缓存功能。

## 功能特性

- **多驱动支持**：支持文件缓存和 Redis 缓存
- **自动管理**：智能缓存管理器，自动处理缓存生命周期
- **类型安全**：完整的类型提示和错误处理
- **高性能**：优化的缓存算法，减少数据库查询
- **易于使用**：简洁的 API 接口，快速集成

## 安装

该服务已集成到 DolphinPHP 框架中，无需额外安装。

## 基本使用

### 1. 使用缓存管理器

```php
use app\services\cache\CacheManager;

// 获取缓存管理器实例
$cacheManager = new CacheManager();

// 缓存表单配置
$formConfig = [
    'title' => '用户注册表单',
    'fields' => [
        ['name' => 'username', 'type' => 'text', 'required' => true],
        ['name' => 'email', 'type' => 'email', 'required' => true],
    ]
];

$cacheManager->setFormConfig('user_registration', $formConfig, 3600); // 缓存1小时

// 获取缓存的表单配置
$cachedConfig = $cacheManager->getFormConfig('user_registration');
```

### 2. 直接使用文件缓存

```php
use app\services\cache\FileCache;

$fileCache = new FileCache();

// 设置缓存
$fileCache->set('user_preferences', ['theme' => 'dark', 'language' => 'zh-cn'], 1800); // 缓存30分钟

// 获取缓存
$preferences = $fileCache->get('user_preferences');

// 检查缓存是否存在
if ($fileCache->has('user_preferences')) {
    // 缓存存在
}

// 删除缓存
$fileCache->delete('user_preferences');

// 清空所有缓存
$fileCache->clear();
```

### 3. 使用 Redis 缓存（可选）

```php
use app\services\cache\RedisCache;

$redisCache = new RedisCache([
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => '',
    'database' => 0
]);

// 使用方式与文件缓存相同
$redisCache->set('session_data', $sessionData, 7200); // 缓存2小时
```

## 高级功能

### 缓存键生成

缓存管理器提供了专门的缓存键生成方法：

```php
// 生成表单配置缓存键
$formKey = CacheManager::getFormConfigKey('user_registration');
// 输出: form_config:user_registration

// 生成表格配置缓存键
$tableKey = CacheManager::getTableConfigKey('user_list');
// 输出: table_config:user_list

// 生成权限配置缓存键
$permissionKey = CacheManager::getPermissionConfigKey('admin_access');
// 输出: permission_config:admin_access
```

### 配置缓存最佳实践

```php
class ConfigService
{
    private $cacheManager;
    
    public function __construct()
    {
        $this->cacheManager = new CacheManager();
    }
    
    public function getFormConfig($formName)
    {
        // 尝试从缓存获取
        $config = $this->cacheManager->getFormConfig($formName);
        
        if ($config === null) {
            // 缓存中没有，从数据库获取
            $config = $this->loadFormConfigFromDatabase($formName);
            
            // 缓存配置，设置适当的过期时间
            $this->cacheManager->setFormConfig($formName, $config, 3600); // 1小时
        }
        
        return $config;
    }
    
    public function updateFormConfig($formName, $config)
    {
        // 更新数据库
        $this->saveFormConfigToDatabase($formName, $config);
        
        // 更新缓存
        $this->cacheManager->setFormConfig($formName, $config, 3600);
    }
    
    public function clearFormConfig($formName)
    {
        // 清除特定表单配置的缓存
        $this->cacheManager->deleteFormConfig($formName);
    }
}
```

## 缓存策略建议

### 不同类型配置的缓存时间建议：

| 配置类型 | 建议缓存时间 | 说明 |
|---------|-------------|------|
| 表单配置 | 1小时 | 相对稳定，不经常变化 |
| 表格配置 | 1小时 | 相对稳定，不经常变化 |
| 权限配置 | 30分钟 | 可能会频繁更新 |
| 系统设置 | 2小时 | 很少变化 |
| 用户偏好 | 30分钟 | 用户可能随时修改 |

### 缓存清理策略：

1. **主动清理**：配置更新时立即清理相关缓存
2. **定时清理**：设置合理的缓存过期时间
3. **批量清理**：使用 `clear()` 方法清理所有缓存

## 错误处理

服务包含完整的错误处理机制：

```php
try {
    $cache = new FileCache();
    $result = $cache->get('some_key');
} catch (\Exception $e) {
    // 处理缓存错误
    // 记录日志，返回默认值等
    $result = $defaultValue;
}
```

## 性能优化

1. **合理设置缓存时间**：避免过短或过长的缓存时间
2. **使用适当的缓存驱动**：生产环境建议使用 Redis
3. **批量操作**：尽量使用批量操作减少 I/O
4. **缓存键命名规范**：使用清晰的命名规范便于管理

## API 参考

### CacheInterface

```php
interface CacheInterface
{
    public function get(string $key, $default = null);
    public function set(string $key, $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function has(string $key): bool;
    public function getDriverType(): string;
}
```

### CacheManager

```php
class CacheManager
{
    public function get(string $key, $default = null);
    public function set(string $key, $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function has(string $key): bool;
    
    // 配置缓存专用方法
    public function getFormConfig(string $formName);
    public function setFormConfig(string $formName, $config, int $ttl = 3600): bool;
    public function deleteFormConfig(string $formName): bool;
    
    public function getTableConfig(string $tableName);
    public function setTableConfig(string $tableName, $config, int $ttl = 3600): bool;
    public function deleteTableConfig(string $tableName): bool;
    
    public function getPermissionConfig(string $permissionName);
    public function setPermissionConfig(string $permissionName, $config, int $ttl = 3600): bool;
    public function deletePermissionConfig(string $permissionName): bool;
}
```

## 测试

服务包含完整的单元测试，位于 `tests/` 目录：

- `CacheManagerTest.php` - 缓存管理器测试
- `FileCacheTest.php` - 文件缓存驱动测试

运行测试：
```bash
# 使用 ThinkPHP 测试命令（如果可用）
php think test tests/CacheManagerTest.php
php think test tests/FileCacheTest.php

# 或者直接运行 PHP 测试
php tests/CacheManagerTest.php
php tests/FileCacheTest.php
```

## 更新日志

### v1.0.0
- ✨ 初始版本发布
- ✨ 支持文件缓存和 Redis 缓存
- ✨ 完整的缓存管理器实现
- ✨ 配置缓存专用 API
- ✨ 完整的单元测试覆盖