<?php

namespace app\service;

/**
 * 安全过滤服务类
 * Class SecurityService
 * @package app\service
 */
class SecurityService
{
    /**
     * XSS过滤
     * @param string $input 输入内容
     * @return string
     */
    public function xssFilter($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // 移除不可见字符
        $input = $this->removeInvisibleCharacters($input);
        
        // 移除HTML标签
        $input = strip_tags($input);
        
        // 转换特殊字符
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return $input;
    }
    
    /**
     * 批量XSS过滤
     * @param array $data 数据数组
     * @return array
     */
    public function xssFilterArray($data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->xssFilter($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->xssFilterArray($value);
            }
        }
        return $data;
    }
    
    /**
     * SQL注入过滤
     * @param string $input 输入内容
     * @return string
     */
    public function sqlFilter($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        
        // 移除SQL关键字
        $keywords = [
            'union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter', 'exec', 'execute',
            'script', 'javascript', 'vbscript', 'applet', 'alert', 'document.cookie', 'onload', 'onerror'
        ];
        
        $input = preg_replace('/\b(' . implode('|', $keywords) . ')\b/i', '', $input);
        
        // 移除特殊字符
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
        
        return $input;
    }
    
    /**
     * 文件上传安全检查
     * @param array $file 上传文件信息
     * @param array $allowedTypes 允许的文件类型
     * @param int $maxSize 最大文件大小（字节）
     * @return array ['valid' => bool, 'error' => string]
     */
    public function fileUploadCheck($file, $allowedTypes = [], $maxSize = 10485760)
    {
        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => '文件上传失败'];
        }
        
        // 检查文件大小
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => '文件大小超出限制'];
        }
        
        // 检查文件类型
        if (!empty($allowedTypes)) {
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedTypes)) {
                return ['valid' => false, 'error' => '不支持的文件类型'];
            }
        }
        
        // 检查MIME类型
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // 验证MIME类型
        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'text/plain', 'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedMimeTypes)) {
            return ['valid' => false, 'error' => '不支持的文件类型'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    /**
     * CSRF令牌生成
     * @return string
     */
    public function generateCsrfToken()
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * CSRF令牌验证
     * @param string $token 令牌
     * @param string $sessionToken 会话中的令牌
     * @return bool
     */
    public function verifyCsrfToken($token, $sessionToken)
    {
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * 移除不可见字符
     * @param string $input 输入内容
     * @return string
     */
    protected function removeInvisibleCharacters($input)
    {
        $nonDisplayables = [
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        ];
        
        do {
            $cleaned = $input;
            foreach ($nonDisplayables as $regex) {
                $input = preg_replace($regex, '', $input);
            }
        } while ($cleaned !== $input);
        
        return $input;
    }
}