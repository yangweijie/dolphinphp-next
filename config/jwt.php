<?php
// JWT配置
return [
    // 加密密钥
    'key' => env('JWT_KEY', 'default_jwt_key'),
    
    // 加密算法
    'alg' => env('JWT_ALG', 'HS256'),
    
    // Token过期时间（秒）
    'expire' => env('JWT_EXPIRE', 3600),
];