<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;
use app\service\HtmxService;

class HtmxComponent extends Component
{
    protected $htmxService;
    
    public function __construct()
    {
        $this->htmxService = new HtmxService();
    }
    
    /**
     * 检查是否是HTMX请求
     * @return bool
     */
    public function isHtmxRequest()
    {
        return $this->htmxService->isHtmxRequest();
    }
    
    /**
     * 获取HTMX触发元素
     * @return string|null
     */
    public function getHtmxTrigger()
    {
        return $this->htmxService->getHtmxTrigger();
    }
    
    /**
     * 渲染表单字段联动
     * @param string $triggerField 触发字段
     * @param string $targetField 目标字段
     * @param callable $callback 回调函数
     * @return string
     */
    public function renderFieldCascade($triggerField, $targetField, $callback)
    {
        $result = $this->htmxService->handleFieldCascade($triggerField, $targetField, $callback);
        
        if ($result !== null) {
            // 如果是HTMX请求且触发了联动，返回目标字段的更新内容
            $this->setVars(['options' => $result, 'field' => $targetField]);
            return $this->view('htmx/field-cascade.html');
        }
        
        return '';
    }
    
    /**
     * 渲染实时表单验证
     * @param string $field 字段名
     * @param callable $validator 验证器函数
     * @return string
     */
    public function renderRealTimeValidation($field, $validator)
    {
        $result = $this->htmxService->handleRealTimeValidation($field, $validator);
        
        if ($result !== null) {
            // 如果是HTMX请求且触发了验证，返回验证结果
            $this->setVars(['validation' => $result, 'field' => $field]);
            return $this->view('htmx/real-time-validation.html');
        }
        
        return '';
    }
    
    /**
     * 渲染动态内容加载
     * @param string $url 加载URL
     * @param array $options 选项
     * @return string
     */
    public function renderDynamicContent($url, $options = [])
    {
        $this->setVars(['url' => $url, 'options' => $options]);
        return $this->view('htmx/dynamic-content.html');
    }
    
    /**
     * 渲染无刷新表单提交
     * @param string $url 提交URL
     * @param array $options 选项
     * @return string
     */
    public function renderFormSubmit($url, $options = [])
    {
        $this->setVars(['url' => $url, 'options' => $options]);
        return $this->view('htmx/form-submit.html');
    }
}