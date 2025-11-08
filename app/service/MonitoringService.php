<?php

namespace app\service;

use think\facade\Log;

/**
 * 监控服务类
 * Class MonitoringService
 * @package app\service
 */
class MonitoringService
{
    /**
     * 告警配置
     * @var array
     */
    protected $alerts = [];
    
    /**
     * 监控指标
     * @var array
     */
    protected $metrics = [];
    
    /**
     * 记录系统指标
     * @param string $name 指标名称
     * @param mixed $value 指标值
     * @param array $tags 标签
     * @return void
     */
    public function recordMetric($name, $value, $tags = [])
    {
        $this->metrics[$name] = [
            'value' => $value,
            'tags' => $tags,
            'timestamp' => time()
        ];
        
        // 检查是否触发告警
        $this->checkAlerts($name, $value);
    }
    
    /**
     * 获取系统指标
     * @param string $name 指标名称
     * @return mixed|null
     */
    public function getMetric($name)
    {
        return isset($this->metrics[$name]) ? $this->metrics[$name] : null;
    }
    
    /**
     * 获取所有指标
     * @return array
     */
    public function getAllMetrics()
    {
        return $this->metrics;
    }
    
    /**
     * 添加告警规则
     * @param string $name 告警名称
     * @param string $metric 指标名称
     * @param string $operator 操作符
     * @param mixed $threshold 阈值
     * @param string $message 告警消息
     * @return void
     */
    public function addAlert($name, $metric, $operator, $threshold, $message)
    {
        $this->alerts[$name] = [
            'metric' => $metric,
            'operator' => $operator,
            'threshold' => $threshold,
            'message' => $message,
            'triggered' => false,
            'last_triggered' => null
        ];
    }
    
    /**
     * 检查告警规则
     * @param string $metric 指标名称
     * @param mixed $value 指标值
     * @return void
     */
    protected function checkAlerts($metric, $value)
    {
        foreach ($this->alerts as $name => &$alert) {
            if ($alert['metric'] === $metric && !$alert['triggered']) {
                $triggered = false;
                
                switch ($alert['operator']) {
                    case '>':
                        $triggered = $value > $alert['threshold'];
                        break;
                    case '>=':
                        $triggered = $value >= $alert['threshold'];
                        break;
                    case '<':
                        $triggered = $value < $alert['threshold'];
                        break;
                    case '<=':
                        $triggered = $value <= $alert['threshold'];
                        break;
                    case '=':
                    case '==':
                        $triggered = $value == $alert['threshold'];
                        break;
                    case '!=':
                        $triggered = $value != $alert['threshold'];
                        break;
                }
                
                if ($triggered) {
                    $alert['triggered'] = true;
                    $alert['last_triggered'] = time();
                    $this->triggerAlert($name, $alert['message']);
                }
            }
        }
    }
    
    /**
     * 触发告警
     * @param string $name 告警名称
     * @param string $message 告警消息
     * @return void
     */
    protected function triggerAlert($name, $message)
    {
        // 记录告警日志
        Log::alert("[ALERT] {$name}: {$message}");
        
        // 这里可以集成其他告警方式，如邮件、短信、Slack等
        // 示例：发送邮件告警
        // $this->sendEmailAlert($name, $message);
    }
    
    /**
     * 获取触发的告警
     * @return array
     */
    public function getTriggeredAlerts()
    {
        $triggered = [];
        
        foreach ($this->alerts as $name => $alert) {
            if ($alert['triggered']) {
                $triggered[$name] = $alert;
            }
        }
        
        return $triggered;
    }
    
    /**
     * 重置告警状态
     * @param string $name 告警名称
     * @return void
     */
    public function resetAlert($name)
    {
        if (isset($this->alerts[$name])) {
            $this->alerts[$name]['triggered'] = false;
        }
    }
    
    /**
     * 重置所有告警状态
     * @return void
     */
    public function resetAllAlerts()
    {
        foreach ($this->alerts as &$alert) {
            $alert['triggered'] = false;
        }
    }
    
    /**
     * 记录错误日志
     * @param string $message 错误消息
     * @param array $context 上下文信息
     * @return void
     */
    public function logError($message, $context = [])
    {
        Log::error($message, $context);
        
        // 记录错误计数指标
        $errorCount = isset($this->metrics['error_count']) ? $this->metrics['error_count']['value'] + 1 : 1;
        $this->recordMetric('error_count', $errorCount);
    }
    
    /**
     * 记录访问日志
     * @param string $endpoint 访问端点
     * @param int $responseCode 响应码
     * @param float $duration 持续时间
     * @return void
     */
    public function logAccess($endpoint, $responseCode, $duration)
    {
        // 记录访问计数指标
        $accessCount = isset($this->metrics['access_count']) ? $this->metrics['access_count']['value'] + 1 : 1;
        $this->recordMetric('access_count', $accessCount);
        
        // 记录响应码计数
        $responseCodeKey = 'response_code_' . $responseCode;
        $responseCodeCount = isset($this->metrics[$responseCodeKey]) ? $this->metrics[$responseCodeKey]['value'] + 1 : 1;
        $this->recordMetric($responseCodeKey, $responseCodeCount);
        
        // 记录慢请求
        if ($duration > 1.0) { // 超过1秒的请求视为慢请求
            $slowRequestCount = isset($this->metrics['slow_request_count']) ? $this->metrics['slow_request_count']['value'] + 1 : 1;
            $this->recordMetric('slow_request_count', $slowRequestCount);
        }
    }
    
    /**
     * 获取系统健康状态
     * @return array
     */
    public function getSystemHealth()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'metrics' => []
        ];
        
        // 检查错误率
        $errorCount = isset($this->metrics['error_count']) ? $this->metrics['error_count']['value'] : 0;
        $accessCount = isset($this->metrics['access_count']) ? $this->metrics['access_count']['value'] : 1;
        $errorRate = ($errorCount / $accessCount) * 100;
        
        if ($errorRate > 5) { // 错误率超过5%视为不健康
            $health['status'] = 'unhealthy';
        }
        
        // 检查触发的告警
        $triggeredAlerts = $this->getTriggeredAlerts();
        if (!empty($triggeredAlerts)) {
            $health['status'] = 'unhealthy';
        }
        
        // 收集关键指标
        $keyMetrics = [
            'error_count', 'access_count', 'slow_request_count',
            'response_code_200', 'response_code_404', 'response_code_500'
        ];
        
        foreach ($keyMetrics as $metric) {
            if (isset($this->metrics[$metric])) {
                $health['metrics'][$metric] = $this->metrics[$metric]['value'];
            }
        }
        
        return $health;
    }
    
    /**
     * 生成监控报告
     * @return array
     */
    public function getMonitoringReport()
    {
        return [
            'system_health' => $this->getSystemHealth(),
            'triggered_alerts' => $this->getTriggeredAlerts(),
            'metrics' => $this->getAllMetrics()
        ];
    }
}