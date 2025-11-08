<?php

namespace app\controller;

use app\BaseController;
use app\service\DocumentationService;
use app\service\TestService;

/**
 * 示例控制器
 * Class Example
 * @package app\controller
 */
class Example extends BaseController
{
    /**
     * 首页
     * @return string
     */
    public function index()
    {
        return $this->view('example/index.html');
    }
    
    /**
     * 表单示例
     * @return string
     */
    public function form()
    {
        return $this->view('example/form.html');
    }
    
    /**
     * 表格示例
     * @return string
     */
    public function table()
    {
        return $this->view('example/table.html');
    }
    
    /**
     * API文档
     * @return string
     */
    public function apiDocs()
    {
        $docService = new DocumentationService();
        $documentation = $docService->getApiDocumentation();
        
        return $docService->generateDocumentationHtml($documentation);
    }
    
    /**
     * 组件文档
     * @return string
     */
    public function componentDocs()
    {
        $docService = new DocumentationService();
        $documentation = $docService->getComponentDocumentation();
        
        return $docService->generateDocumentationHtml($documentation);
    }
    
    /**
     * 运行测试
     * @return \think\response\Json
     */
    public function runTests()
    {
        $testService = new TestService();
        
        $testTypes = ['form', 'table', 'auth', 'validation'];
        $results = [];
        
        foreach ($testTypes as $testType) {
            $results[$testType] = $testService->runApiTest($testType);
        }
        
        $report = $testService->generateTestReport($results);
        
        return json([
            'code' => 200,
            'msg' => '测试完成',
            'data' => [
                'results' => $results,
                'report' => $report
            ]
        ]);
    }
    
    /**
     * 获取测试报告
     * @return string
     */
    public function testReport()
    {
        return $this->view('example/test-report.html');
    }
    
    /**
     * 部署指南
     * @return string
     */
    public function deployment()
    {
        return $this->view('example/deployment.html');
    }
}
