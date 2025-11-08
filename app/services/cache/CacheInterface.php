<?php

declare(strict_types=1);

namespace app\services\cache;

/**
 * 缓存服务接口
 * Interface for configuration caching service
 */
interface CacheInterface
{
    /**
     * 获取缓存值
     * Get cached value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * 设置缓存值
     * Set cached value
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl 过期时间（秒）
     * @return bool
     */
    public function set(string $key, $value, int $ttl = 3600): bool;

    /**
     * 删除缓存
     * Delete cached value
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * 清空所有缓存
     * Clear all cached values
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * 检查缓存是否存在
     * Check if cache exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * 获取缓存驱动类型
     * Get cache driver type
     *
     * @return string
     */
    public function getDriverType(): string;
}