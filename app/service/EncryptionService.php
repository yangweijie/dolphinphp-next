<?php

namespace app\service;

use think\facade\Config;

/**
 * 数据加密和脱敏服务类
 * Class EncryptionService
 * @package app\service
 */
class EncryptionService
{
    /**
     * 加密密钥
     * @var string
     */
    protected $key;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->key = Config::get('app.app_key', 'default_encryption_key');
    }
    
    /**
     * 加密数据
     * @param string $data 待加密数据
     * @return string
     */
    public function encrypt($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * 解密数据
     * @param string $data 待解密数据
     * @return string
     */
    public function decrypt($data)
    {
        if (empty($data)) {
            return $data;
        }
        
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, 0, $iv);
    }
    
    /**
     * 数据脱敏
     * @param string $data 待脱敏数据
     * @param string $type 脱敏类型
     * @param array $options 脱敏选项
     * @return string
     */
    public function mask($data, $type = 'default', $options = [])
    {
        if (empty($data)) {
            return $data;
        }
        
        switch ($type) {
            case 'phone':
                return $this->maskPhone($data);
                
            case 'email':
                return $this->maskEmail($data);
                
            case 'id_card':
                return $this->maskIdCard($data);
                
            case 'name':
                return $this->maskName($data);
                
            case 'bank_card':
                return $this->maskBankCard($data);
                
            default:
                return $this->maskDefault($data, $options);
        }
    }
    
    /**
     * 手机号脱敏
     * @param string $phone 手机号
     * @return string
     */
    protected function maskPhone($phone)
    {
        return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $phone);
    }
    
    /**
     * 邮箱脱敏
     * @param string $email 邮箱
     * @return string
     */
    protected function maskEmail($email)
    {
        $emailParts = explode('@', $email);
        if (count($emailParts) !== 2) {
            return $email;
        }
        
        $username = $emailParts[0];
        $domain = $emailParts[1];
        
        if (strlen($username) <= 2) {
            return str_repeat('*', strlen($username)) . '@' . $domain;
        }
        
        return substr($username, 0, 2) . '****@' . $domain;
    }
    
    /**
     * 身份证脱敏
     * @param string $idCard 身份证号
     * @return string
     */
    protected function maskIdCard($idCard)
    {
        return preg_replace('/(\d{6})\d{8}(\w{4})/', '$1********$2', $idCard);
    }
    
    /**
     * 姓名脱敏
     * @param string $name 姓名
     * @return string
     */
    protected function maskName($name)
    {
        $length = mb_strlen($name, 'UTF-8');
        if ($length <= 1) {
            return $name;
        }
        
        if ($length == 2) {
            return mb_substr($name, 0, 1, 'UTF-8') . '*';
        }
        
        return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $length - 2) . mb_substr($name, -1, 1, 'UTF-8');
    }
    
    /**
     * 银行卡脱敏
     * @param string $bankCard 银行卡号
     * @return string
     */
    protected function maskBankCard($bankCard)
    {
        return preg_replace('/(\d{4})\d{8}(\d{4})/', '$1********$2', $bankCard);
    }
    
    /**
     * 默认脱敏
     * @param string $data 数据
     * @param array $options 选项
     * @return string
     */
    protected function maskDefault($data, $options = [])
    {
        $start = $options['start'] ?? 0;
        $length = $options['length'] ?? null;
        $maskChar = $options['mask_char'] ?? '*';
        
        if ($length === null) {
            $length = mb_strlen($data, 'UTF-8') - $start;
        }
        
        if ($start + $length > mb_strlen($data, 'UTF-8')) {
            $length = mb_strlen($data, 'UTF-8') - $start;
        }
        
        $prefix = mb_substr($data, 0, $start, 'UTF-8');
        $suffix = mb_substr($data, $start + $length, null, 'UTF-8');
        $mask = str_repeat($maskChar, $length);
        
        return $prefix . $mask . $suffix;
    }
    
    /**
     * 创建审计日志
     * @param string $action 操作
     * @param array $data 数据
     * @param int $userId 用户ID
     * @return bool
     */
    public function createAuditLog($action, $data, $userId = 0)
    {
        // 这里可以实现审计日志的存储逻辑
        // 例如存储到数据库或日志文件中
        $logData = [
            'action' => $action,
            'data' => $data,
            'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => request()->server('HTTP_USER_AGENT'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // 记录日志
        trace($logData, 'audit');
        
        return true;
    }
}