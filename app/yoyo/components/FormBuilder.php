<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;
use app\service\FormService;

class FormBuilder extends Component
{
    public $forms = [];
    public $statistics = [];
    
    protected $formService;
    
    public function __construct()
    {
        $this->formService = new FormService();
    }
    
    public function mount()
    {
        $this->loadForms();
    }
    
    public function loadForms()
    {
        // 获取表单列表
        $result = $this->formService->getFormList([
            'page' => 1,
            'limit' => 10,
            'status' => 1 // 只获取启用的表单
        ]);
        
        $this->forms = $result['data'] ?? [];
        $this->statistics = $this->formService->getStatistics();
    }
    
    public function render()
    {
        return $this->view('form.html');
    }
}