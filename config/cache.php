<?php
// Redis缓存配置
return [
    // 默认缓存驱动
    'default' => env('CACHE_DRIVER', 'file'),
    
    // 缓存前缀
    'prefix' => env('CACHE_PREFIX', 'zbuilder'),
    
    // 缓存有效期（秒）
    'expire' => env('CACHE_EXPIRE', 3600),
    
    // 缓存存储配置
    'stores' => [
        // 文件缓存
        'file' => [
            'type' => 'file',
            'path' => app()->getRuntimePath() . 'cache' . DIRECTORY_SEPARATOR,
            'prefix' => '',
            'expire' => 0,
        ],
        
        // Redis缓存
        'redis' => [
            'type' => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', ''),
            'select' => env('REDIS_SELECT', 0),
            'timeout' => env('REDIS_TIMEOUT', 0),
            'expire' => env('REDIS_EXPIRE', 3600),
            'persistent' => env('REDIS_PERSISTENT', false),
            'prefix' => env('REDIS_PREFIX', 'zbuilder:'),
        ],
        
        // 内存缓存
        'memcache' => [
            'type' => 'memcache',
            'host' => env('MEMCACHE_HOST', '127.0.0.1'),
            'port' => env('MEMCACHE_PORT', 11211),
            'expire' => env('MEMCACHE_EXPIRE', 3600),
            'prefix' => env('MEMCACHE_PREFIX', 'zbuilder:'),
        ],
    ],
    
    // 缓存标签
    'taggable' => [
        'redis',
        'memcache',
    ],
];