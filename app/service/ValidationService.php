<?php

namespace app\service;

/**
 * 数据验证服务类
 * Class ValidationService
 * @package app\service
 */
class ValidationService
{
    /**
     * 验证规则
     * @var array
     */
    protected $rules = [
        'required' => '必须填写',
        'email' => '邮箱格式不正确',
        'number' => '必须是数字',
        'integer' => '必须是整数',
        'string' => '必须是字符串',
        'array' => '必须是数组',
        'date' => '日期格式不正确',
        'url' => '网址格式不正确',
        'min' => '长度不能少于:min位',
        'max' => '长度不能超过:max位',
        'between' => '长度必须在:min到:max位之间',
        'min_value' => '数值不能小于:min',
        'max_value' => '数值不能大于:max',
        'regex' => '格式不正确',
    ];
    
    /**
     * 验证数据
     * @param array $data 待验证数据
     * @param array $rules 验证规则
     * @return array 验证结果 ['valid' => bool, 'errors' => array]
     */
    public function validate($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;
                
                if (!$this->validateRule($value, $ruleName, $ruleParam)) {
                    $errorMessage = $this->rules[$ruleName] ?? '验证失败';
                    $errorMessage = str_replace(':min', $ruleParam, $errorMessage);
                    $errorMessage = str_replace(':max', $ruleParam, $errorMessage);
                    $errors[$field] = $errorMessage;
                    break; // 一个字段只需要报告一个错误
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * 验证单个规则
     * @param mixed $value 值
     * @param string $rule 规则
     * @param mixed $param 参数
     * @return bool
     */
    protected function validateRule($value, $rule, $param = null)
    {
        switch ($rule) {
            case 'required':
                return $value !== null && $value !== '';
                
            case 'email':
                return $value === '' || filter_var($value, FILTER_VALIDATE_EMAIL);
                
            case 'number':
                return $value === '' || is_numeric($value);
                
            case 'integer':
                return $value === '' || filter_var($value, FILTER_VALIDATE_INT) !== false;
                
            case 'string':
                return $value === '' || is_string($value);
                
            case 'array':
                return $value === '' || is_array($value);
                
            case 'date':
                return $value === '' || strtotime($value) !== false;
                
            case 'url':
                return $value === '' || filter_var($value, FILTER_VALIDATE_URL);
                
            case 'min':
                if (is_string($value)) {
                    return strlen($value) >= $param;
                } elseif (is_numeric($value)) {
                    return $value >= $param;
                }
                return true;
                
            case 'max':
                if (is_string($value)) {
                    return strlen($value) <= $param;
                } elseif (is_numeric($value)) {
                    return $value <= $param;
                }
                return true;
                
            case 'between':
                $range = explode(',', $param);
                if (count($range) !== 2) {
                    return true;
                }
                $min = (int)$range[0];
                $max = (int)$range[1];
                
                if (is_string($value)) {
                    $length = strlen($value);
                    return $length >= $min && $length <= $max;
                } elseif (is_numeric($value)) {
                    return $value >= $min && $value <= $max;
                }
                return true;
                
            case 'min_value':
                return $value === '' || $value >= $param;
                
            case 'max_value':
                return $value === '' || $value <= $param;
                
            case 'regex':
                return $value === '' || preg_match($param, $value);
                
            default:
                return true;
        }
    }
    
    /**
     * 添加自定义验证规则
     * @param string $name 规则名称
     * @param string $message 错误消息
     * @param callable $callback 验证回调函数
     * @return void
     */
    public function addRule($name, $message, $callback)
    {
        $this->rules[$name] = $message;
        $this->customRules[$name] = $callback;
    }
    
    /**
     * 获取验证规则列表
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }
}