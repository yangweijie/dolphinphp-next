<?php

namespace app\service;

use think\facade\Log;

/**
 * 性能监控服务类
 * Class PerformanceService
 * @package app\service
 */
class PerformanceService
{
    /**
     * 性能指标
     * @var array
     */
    protected $metrics = [];
    
    /**
     * 开始计时
     * @param string $name 计时器名称
     * @return void
     */
    public function startTimer($name)
    {
        $this->metrics[$name] = [
            'start' => microtime(true),
            'end' => null,
            'duration' => null
        ];
    }
    
    /**
     * 结束计时
     * @param string $name 计时器名称
     * @return float|null 持续时间（秒）
     */
    public function endTimer($name)
    {
        if (isset($this->metrics[$name])) {
            $this->metrics[$name]['end'] = microtime(true);
            $this->metrics[$name]['duration'] = $this->metrics[$name]['end'] - $this->metrics[$name]['start'];
            return $this->metrics[$name]['duration'];
        }
        return null;
    }
    
    /**
     * 获取计时器持续时间
     * @param string $name 计时器名称
     * @return float|null 持续时间（秒）
     */
    public function getTimerDuration($name)
    {
        return isset($this->metrics[$name]['duration']) ? $this->metrics[$name]['duration'] : null;
    }
    
    /**
     * 记录内存使用情况
     * @param string $name 记录名称
     * @return int 当前内存使用量（字节）
     */
    public function recordMemoryUsage($name)
    {
        $memory = memory_get_usage(true);
        $this->metrics[$name . '_memory'] = $memory;
        return $memory;
    }
    
    /**
     * 记录峰值内存使用情况
     * @param string $name 记录名称
     * @return int 峰值内存使用量（字节）
     */
    public function recordPeakMemoryUsage($name)
    {
        $memory = memory_get_peak_usage(true);
        $this->metrics[$name . '_peak_memory'] = $memory;
        return $memory;
    }
    
    /**
     * 记录数据库查询
     * @param string $sql SQL语句
     * @param float $duration 查询持续时间
     * @return void
     */
    public function recordDatabaseQuery($sql, $duration)
    {
        if (!isset($this->metrics['db_queries'])) {
            $this->metrics['db_queries'] = [];
        }
        
        $this->metrics['db_queries'][] = [
            'sql' => $sql,
            'duration' => $duration
        ];
    }
    
    /**
     * 获取慢查询
     * @param float $threshold 阈值（秒）
     * @return array 慢查询列表
     */
    public function getSlowQueries($threshold = 1.0)
    {
        $slowQueries = [];
        
        if (isset($this->metrics['db_queries'])) {
            foreach ($this->metrics['db_queries'] as $query) {
                if ($query['duration'] >= $threshold) {
                    $slowQueries[] = $query;
                }
            }
        }
        
        return $slowQueries;
    }
    
    /**
     * 记录API调用
     * @param string $endpoint API端点
     * @param float $duration 调用持续时间
     * @param int $responseCode 响应码
     * @return void
     */
    public function recordApiCall($endpoint, $duration, $responseCode)
    {
        if (!isset($this->metrics['api_calls'])) {
            $this->metrics['api_calls'] = [];
        }
        
        $this->metrics['api_calls'][] = [
            'endpoint' => $endpoint,
            'duration' => $duration,
            'response_code' => $responseCode
        ];
    }
    
    /**
     * 获取性能报告
     * @return array
     */
    public function getPerformanceReport()
    {
        $report = [
            'timers' => [],
            'memory' => [],
            'database' => [],
            'api' => []
        ];
        
        // 收集计时器信息
        foreach ($this->metrics as $name => $metric) {
            if (isset($metric['duration'])) {
                $report['timers'][$name] = [
                    'duration' => round($metric['duration'] * 1000, 2) . 'ms'
                ];
            } elseif (strpos($name, '_memory') !== false) {
                $report['memory'][$name] = $this->formatBytes($metric);
            }
        }
        
        // 收集数据库查询信息
        if (isset($this->metrics['db_queries'])) {
            $totalQueries = count($this->metrics['db_queries']);
            $totalDuration = array_sum(array_column($this->metrics['db_queries'], 'duration'));
            $slowQueries = $this->getSlowQueries(0.1); // 100ms以上的查询视为慢查询
            
            $report['database'] = [
                'total_queries' => $totalQueries,
                'total_duration' => round($totalDuration * 1000, 2) . 'ms',
                'average_duration' => $totalQueries > 0 ? round(($totalDuration / $totalQueries) * 1000, 2) . 'ms' : '0ms',
                'slow_queries' => count($slowQueries)
            ];
        }
        
        // 收集API调用信息
        if (isset($this->metrics['api_calls'])) {
            $totalCalls = count($this->metrics['api_calls']);
            $totalDuration = array_sum(array_column($this->metrics['api_calls'], 'duration'));
            $failedCalls = count(array_filter($this->metrics['api_calls'], function($call) {
                return $call['response_code'] >= 400;
            }));
            
            $report['api'] = [
                'total_calls' => $totalCalls,
                'total_duration' => round($totalDuration * 1000, 2) . 'ms',
                'average_duration' => $totalCalls > 0 ? round(($totalDuration / $totalCalls) * 1000, 2) . 'ms' : '0ms',
                'failed_calls' => $failedCalls
            ];
        }
        
        return $report;
    }
    
    /**
     * 记录性能日志
     * @param string $message 日志消息
     * @param string $level 日志级别
     * @return void
     */
    public function logPerformance($message, $level = 'info')
    {
        $logMessage = "[PERFORMANCE] {$message}";
        Log::write($logMessage, $level);
    }
    
    /**
     * 格式化字节数
     * @param int $bytes 字节数
     * @param int $precision 精度
     * @return string 格式化后的字符串
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * 获取所有指标
     * @return array
     */
    public function getMetrics()
    {
        return $this->metrics;
    }
    
    /**
     * 重置指标
     * @return void
     */
    public function resetMetrics()
    {
        $this->metrics = [];
    }
}