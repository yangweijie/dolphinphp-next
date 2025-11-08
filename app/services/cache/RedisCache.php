<?php

declare(strict_types=1);

namespace app\services\cache;

use think\facade\Log;

/**
 * Redis缓存驱动
 * Redis cache driver implementation
 */
class RedisCache implements CacheInterface
{
    /**
     * Redis连接实例
     * @var \Redis|null
     */
    protected ?\Redis $redis = null;

    /**
     * 配置数组
     * @var array
     */
    protected array $config;

    /**
     * 默认缓存时间（秒）
     * @var int
     */
    protected int $defaultTtl;

    /**
     * 构造函数
     *
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $this->config = [
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? 6379,
            'password' => $config['password'] ?? '',
            'database' => $config['database'] ?? 0,
            'timeout' => $config['timeout'] ?? 2.0,
            'prefix' => $config['prefix'] ?? 'dolphinphp:config:',
        ];
        
        $this->defaultTtl = $config['ttl'] ?? 3600;
        
        $this->connect();
    }

    /**
     * 连接Redis服务器
     *
     * @throws \Exception
     */
    protected function connect(): void
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('Redis extension is not loaded');
        }

        try {
            $this->redis = new \Redis();
            
            $connected = $this->redis->connect(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );

            if (!$connected) {
                throw new \Exception('Failed to connect to Redis server');
            }

            // 认证
            if (!empty($this->config['password'])) {
                if (!$this->redis->auth($this->config['password'])) {
                    throw new \Exception('Redis authentication failed');
                }
            }

            // 选择数据库
            if ($this->config['database'] > 0) {
                $this->redis->select($this->config['database']);
            }

            // 设置前缀
            $this->redis->setOption(\Redis::OPT_PREFIX, $this->config['prefix']);
            
        } catch (\RedisException $e) {
            Log::error('Redis connection error: ' . $e->getMessage());
            throw new \Exception('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        try {
            if (!$this->redis) {
                return $default;
            }

            $value = $this->redis->get($key);
            
            if ($value === false) {
                return $default;
            }

            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
            
        } catch (\RedisException $e) {
            Log::error('Redis get error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            if (!$this->redis) {
                return false;
            }

            $serialized = is_scalar($value) ? $value : json_encode($value);
            
            if ($ttl > 0) {
                return $this->redis->setex($key, $ttl, $serialized);
            } else {
                return $this->redis->set($key, $serialized);
            }
            
        } catch (\RedisException $e) {
            Log::error('Redis set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        try {
            if (!$this->redis) {
                return false;
            }

            return $this->redis->del($key) > 0;
            
        } catch (\RedisException $e) {
            Log::error('Redis delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        try {
            if (!$this->redis) {
                return false;
            }

            // 使用模式匹配删除所有配置相关的键
            $keys = $this->redis->keys('*');
            if (!empty($keys)) {
                return $this->redis->del($keys) > 0;
            }
            
            return true;
            
        } catch (\RedisException $e) {
            Log::error('Redis clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        try {
            if (!$this->redis) {
                return false;
            }

            return $this->redis->exists($key) > 0;
            
        } catch (\RedisException $e) {
            Log::error('Redis has error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverType(): string
    {
        return 'redis';
    }

    /**
     * 获取Redis连接实例
     *
     * @return \Redis|null
     */
    public function getRedis(): ?\Redis
    {
        return $this->redis;
    }

    /**
     * 关闭Redis连接
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->redis) {
            $this->redis->close();
            $this->redis = null;
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->close();
    }
}