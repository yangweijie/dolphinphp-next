<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;
use app\service\UiComponentService;

class UiComponent extends Component
{
    protected $uiService;
    
    public function __construct()
    {
        $this->uiService = new UiComponentService();
    }
    
    /**
     * 渲染按钮组件
     * @param array $options 按钮选项
     * @return string
     */
    public function renderButton($options = [])
    {
        $config = $this->uiService->getButtonConfig($options);
        $this->setVars($config);
        return $this->view('ui/button.html');
    }
    
    /**
     * 渲染模态框组件
     * @param array $options 模态框选项
     * @return string
     */
    public function renderModal($options = [])
    {
        $config = $this->uiService->getModalConfig($options);
        $this->setVars($config);
        return $this->view('ui/modal.html');
    }
    
    /**
     * 渲染警告组件
     * @param array $options 警告选项
     * @return string
     */
    public function renderAlert($options = [])
    {
        $config = $this->uiService->getAlertConfig($options);
        $this->setVars($config);
        return $this->view('ui/alert.html');
    }
    
    /**
     * 渲染加载组件
     * @param array $options 加载选项
     * @return string
     */
    public function renderLoading($options = [])
    {
        $config = $this->uiService->getLoadingConfig($options);
        $this->setVars($config);
        return $this->view('ui/loading.html');
    }
}