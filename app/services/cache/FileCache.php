<?php

declare(strict_types=1);

namespace app\services\cache;

use think\facade\Log;

/**
 * 文件缓存驱动
 * File cache driver implementation
 */
class FileCache implements CacheInterface
{
    /**
     * 缓存目录路径
     * @var string
     */
    protected string $cachePath;

    /**
     * 默认缓存时间（秒）
     * @var int
     */
    protected int $defaultTtl;

    /**
     * 构造函数
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->cachePath = $config['path'] ?? runtime_path() . 'cache' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        $this->defaultTtl = $config['ttl'] ?? 3600;
        
        // 确保缓存目录存在
        $this->ensureCacheDirectory();
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }

        $content = file_get_contents($filename);
        $data = unserialize($content);

        // 检查是否过期
        if ($data['expire'] > 0 && $data['expire'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $filename = $this->getCacheFilename($key);
        $expire = $ttl > 0 ? time() + $ttl : 0;

        $data = [
            'value' => $value,
            'expire' => $expire,
            'create_time' => time()
        ];

        try {
            $content = serialize($data);
            $dir = dirname($filename);
            
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            return file_put_contents($filename, $content, LOCK_EX) !== false;
        } catch (\Exception $e) {
            Log::error('File cache set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        try {
            $files = glob($this->cachePath . '*.cache');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('File cache clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }

        $content = file_get_contents($filename);
        $data = unserialize($content);

        // 检查是否过期
        if ($data['expire'] > 0 && $data['expire'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverType(): string
    {
        return 'file';
    }

    /**
     * 获取缓存文件名
     *
     * @param string $key
     * @return string
     */
    protected function getCacheFilename(string $key): string
    {
        return $this->cachePath . md5($key) . '.cache';
    }

    /**
     * 确保缓存目录存在
     *
     * @return void
     */
    protected function ensureCacheDirectory(): void
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
}