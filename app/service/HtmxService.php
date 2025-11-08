<?php

namespace app\service;

/**
 * HTMX服务类
 * Class HtmxService
 * @package app\service
 */
class HtmxService
{
    /**
     * 检查是否是HTMX请求
     * @return bool
     */
    public function isHtmxRequest()
    {
        return request()->header('HX-Request') === 'true';
    }
    
    /**
     * 获取HTMX触发元素
     * @return string|null
     */
    public function getHtmxTrigger()
    {
        return request()->header('HX-Trigger');
    }
    
    /**
     * 获取HTMX触发名称
     * @return string|null
     */
    public function getHtmxTriggerName()
    {
        return request()->header('HX-Trigger-Name');
    }
    
    /**
     * 获取HTMX目标元素
     * @return string|null
     */
    public function getHtmxTarget()
    {
        return request()->header('HX-Target');
    }
    
    /**
     * 获取HTMX当前URL
     * @return string|null
     */
    public function getHtmxCurrentUrl()
    {
        return request()->header('HX-Current-URL');
    }
    
    /**
     * 生成HTMX响应头
     * @param string $name 头名称
     * @param string $value 头值
     * @return array
     */
    public function generateHtmxHeader($name, $value)
    {
        return ['HX-' . $name => $value];
    }
    
    /**
     * 生成HTMX重定向响应头
     * @param string $url 重定向URL
     * @return array
     */
    public function redirect($url)
    {
        return $this->generateHtmxHeader('Redirect', $url);
    }
    
    /**
     * 生成HTMX刷新响应头
     * @return array
     */
    public function refresh()
    {
        return $this->generateHtmxHeader('Refresh', 'true');
    }
    
    /**
     * 生成HTMX重新加载响应头
     * @return array
     */
    public function reload()
    {
        return $this->generateHtmxHeader('Reload', 'true');
    }
    
    /**
     * 生成HTMX提示响应头
     * @param string $message 提示消息
     * @param string $type 提示类型 (success, error, warning, info)
     * @return array
     */
    public function pushToast($message, $type = 'info')
    {
        return $this->generateHtmxHeader('Push-Toast', json_encode(['message' => $message, 'type' => $type]));
    }
    
    /**
     * 生成HTMX提示响应头（成功）
     * @param string $message 提示消息
     * @return array
     */
    public function pushSuccessToast($message)
    {
        return $this->pushToast($message, 'success');
    }
    
    /**
     * 生成HTMX提示响应头（错误）
     * @param string $message 提示消息
     * @return array
     */
    public function pushErrorToast($message)
    {
        return $this->pushToast($message, 'error');
    }
    
    /**
     * 生成HTMX提示响应头（警告）
     * @param string $message 提示消息
     * @return array
     */
    public function pushWarningToast($message)
    {
        return $this->pushToast($message, 'warning');
    }
    
    /**
     * 生成HTMX提示响应头（信息）
     * @param string $message 提示消息
     * @return array
     */
    public function pushInfoToast($message)
    {
        return $this->pushToast($message, 'info');
    }
    
    /**
     * 生成HTMX触发事件响应头
     * @param string $event 事件名称
     * @param mixed $data 事件数据
     * @return array
     */
    public function triggerEvent($event, $data = null)
    {
        $value = $data ? json_encode(['event' => $event, 'data' => $data]) : $event;
        return $this->generateHtmxHeader('Trigger', $value);
    }
    
    /**
     * 生成HTMX替换URL响应头
     * @param string $url URL
     * @return array
     */
    public function replaceUrl($url)
    {
        return $this->generateHtmxHeader('Replace-Url', $url);
    }
    
    /**
     * 生成HTMX推送URL响应头
     * @param string $url URL
     * @return array
     */
    public function pushUrl($url)
    {
        return $this->generateHtmxHeader('Push-Url', $url);
    }
    
    /**
     * 处理表单字段联动
     * @param string $triggerField 触发字段
     * @param string $targetField 目标字段
     * @param callable $callback 回调函数
     * @return mixed
     */
    public function handleFieldCascade($triggerField, $targetField, $callback)
    {
        if ($this->isHtmxRequest() && $this->getHtmxTrigger() === $triggerField) {
            $triggerValue = request()->param($triggerField);
            return call_user_func($callback, $triggerValue);
        }
        return null;
    }
    
    /**
     * 处理实时表单验证
     * @param string $field 字段名
     * @param callable $validator 验证器函数
     * @return array|null
     */
    public function handleRealTimeValidation($field, $validator)
    {
        if ($this->isHtmxRequest() && $this->getHtmxTrigger() === $field) {
            $value = request()->param($field);
            $result = call_user_func($validator, $value);
            
            if ($result === true) {
                return [
                    'valid' => true,
                    'message' => ''
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => $result
                ];
            }
        }
        return null;
    }
    
    /**
     * 生成HTMX属性数组
     * @param array $attributes HTMX属性
     * @return array
     */
    public function generateHtmxAttributes($attributes)
    {
        $htmxAttributes = [];
        
        foreach ($attributes as $key => $value) {
            $htmxAttributes['hx-' . strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $key))] = $value;
        }
        
        return $htmxAttributes;
    }
}