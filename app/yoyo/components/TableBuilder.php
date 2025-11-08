<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;
use app\service\TableService;

class TableBuilder extends Component
{
    public $tables = [];
    public $statistics = [];
    
    protected $tableService;
    
    public function __construct()
    {
        $this->tableService = new TableService();
    }
    
    public function mount()
    {
        $this->loadTables();
    }
    
    public function loadTables()
    {
        // 获取表格列表
        $result = $this->tableService->getTableList([
            'page' => 1,
            'limit' => 10,
            'status' => 1 // 只获取启用的表格
        ]);
        
        $this->tables = $result['data'] ?? [];
        $this->statistics = $this->tableService->getStatistics();
    }
    
    public function render()
    {
        return $this->view('table.html');
    }
}