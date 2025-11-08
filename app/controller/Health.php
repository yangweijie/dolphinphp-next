<?php

namespace app\controller;

use app\BaseController;
use app\service\MonitoringService;
use app\service\PerformanceService;

/**
 * 健康检查控制器
 * Class Health
 * @package app\controller
 */
class Health extends BaseController
{
    /**
     * 健康检查端点
     * @return \think\response\Json
     */
    public function index()
    {
        $monitoringService = new MonitoringService();
        $health = $monitoringService->getSystemHealth();
        
        $status = $health['status'] === 'healthy' ? 200 : 503;
        
        return json($health, $status);
    }
    
    /**
     * 详细监控信息
     * @return \think\response\Json
     */
    public function metrics()
    {
        $monitoringService = new MonitoringService();
        $performanceService = new PerformanceService();
        
        $report = [
            'system_health' => $monitoringService->getSystemHealth(),
            'monitoring' => $monitoringService->getMonitoringReport(),
            'performance' => $performanceService->getPerformanceReport()
        ];
        
        return json($report);
    }
    
    /**
     * 触发的告警
     * @return \think\response\Json
     */
    public function alerts()
    {
        $monitoringService = new MonitoringService();
        $alerts = $monitoringService->getTriggeredAlerts();
        
        return json([
            'alerts' => $alerts
        ]);
    }
    
    /**
     * 重置告警
     * @param string $name 告警名称
     * @return \think\response\Json
     */
    public function resetAlert($name)
    {
        $monitoringService = new MonitoringService();
        $monitoringService->resetAlert($name);
        
        return json([
            'code' => 200,
            'msg' => '告警已重置'
        ]);
    }
    
    /**
     * 重置所有告警
     * @return \think\response\Json
     */
    public function resetAllAlerts()
    {
        $monitoringService = new MonitoringService();
        $monitoringService->resetAllAlerts();
        
        return json([
            'code' => 200,
            'msg' => '所有告警已重置'
        ]);
    }
}