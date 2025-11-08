<?php

namespace app\service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Config;

/**
 * JWT服务类
 * Class JwtService
 * @package app\service
 */
class JwtService
{
    /**
     * 生成JWT Token
     * @param array $data 用户数据
     * @param int $expire 过期时间（秒）
     * @return string
     */
    public function createToken($data, $expire = 3600)
    {
        $key = Config::get('jwt.key', 'default_key');
        $alg = Config::get('jwt.alg', 'HS256');
        
        $time = time();
        $payload = [
            'iat' => $time, // 签发时间
            'exp' => $time + $expire, // 过期时间
            'data' => $data // 用户数据
        ];
        
        return JWT::encode($payload, $key, $alg);
    }
    
    /**
     * 解析JWT Token
     * @param string $token JWT Token
     * @return object|null
     */
    public function parseToken($token)
    {
        try {
            $key = Config::get('jwt.key', 'default_key');
            $alg = Config::get('jwt.alg', 'HS256');
            
            return JWT::decode($token, new Key($key, $alg));
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 验证JWT Token
     * @param string $token JWT Token
     * @return bool
     */
    public function verifyToken($token)
    {
        $payload = $this->parseToken($token);
        return $payload !== null && isset($payload->exp) && $payload->exp > time();
    }
    
    /**
     * 从Token中获取用户数据
     * @param string $token JWT Token
     * @return array|null
     */
    public function getUserDataFromToken($token)
    {
        $payload = $this->parseToken($token);
        if ($payload && isset($payload->data)) {
            return (array) $payload->data;
        }
        return null;
    }
}