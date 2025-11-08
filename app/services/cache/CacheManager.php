<?php

declare(strict_types=1);

namespace app\services\cache;

use think\facade\Config;
use think\facade\Log;

/**
 * 缓存管理器
 * Cache manager for configuration caching service
 */
class CacheManager
{
    /**
     * 缓存驱动实例
     * @var CacheInterface|null
     */
    protected static ?CacheInterface $driver = null;

    /**
     * 配置数组
     * @var array
     */
    protected static array $config = [];

    /**
     * 获取缓存驱动实例
     *
     * @return CacheInterface
     * @throws \Exception
     */
    public static function getDriver(): CacheInterface
    {
        if (self::$driver === null) {
            self::$driver = self::createDriver();
        }

        return self::$driver;
    }

    /**
     * 创建缓存驱动
     *
     * @return CacheInterface
     * @throws \Exception
     */
    protected static function createDriver(): CacheInterface
    {
        $config = self::getConfig();
        $driverType = $config['default'] ?? 'file';

        switch ($driverType) {
            case 'file':
                return new FileCache($config['stores']['file'] ?? []);
            
            case 'redis':
                if (!extension_loaded('redis')) {
                    Log::warning('Redis extension not loaded, falling back to file cache');
                    return new FileCache($config['stores']['file'] ?? []);
                }
                return new RedisCache($config['stores']['redis'] ?? []);
            
            default:
                throw new \Exception("Unsupported cache driver: {$driverType}");
        }
    }

    /**
     * 获取缓存配置
     *
     * @return array
     */
    protected static function getConfig(): array
    {
        if (empty(self::$config)) {
            // 获取运行时路径 - 使用绝对路径
            $runtimePath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
            
            self::$config = Config::get('cache', [
                'default' => 'file',
                'stores' => [
                    'file' => [
                        'path' => $runtimePath,
                        'ttl' => 3600
                    ],
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'password' => '',
                        'database' => 0,
                        'ttl' => 3600
                    ]
                ]
            ]);
        }

        return self::$config;
    }

    /**
     * 获取缓存值（快捷方法）
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        try {
            return self::getDriver()->get($key, $default);
        } catch (\Exception $e) {
            Log::error('Cache get error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * 设置缓存值（快捷方法）
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public static function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            return self::getDriver()->set($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::error('Cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 删除缓存（快捷方法）
     *
     * @param string $key
     * @return bool
     */
    public static function delete(string $key): bool
    {
        try {
            return self::getDriver()->delete($key);
        } catch (\Exception $e) {
            Log::error('Cache delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 清空缓存（快捷方法）
     *
     * @return bool
     */
    public static function clear(): bool
    {
        try {
            return self::getDriver()->clear();
        } catch (\Exception $e) {
            Log::error('Cache clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 检查缓存是否存在（快捷方法）
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        try {
            return self::getDriver()->has($key);
        } catch (\Exception $e) {
            Log::error('Cache has error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取表单配置缓存键
     *
     * @param string $formId
     * @return string
     */
    public static function getFormConfigKey(string $formId): string
    {
        return 'form_config:' . $formId;
    }

    /**
     * 获取表格配置缓存键
     *
     * @param string $tableId
     * @return string
     */
    public static function getTableConfigKey(string $tableId): string
    {
        return 'table_config:' . $tableId;
    }

    /**
     * 获取权限配置缓存键
     *
     * @param string $permissionId
     * @return string
     */
    public static function getPermissionConfigKey(string $permissionId): string
    {
        return 'permission_config:' . $permissionId;
    }
}