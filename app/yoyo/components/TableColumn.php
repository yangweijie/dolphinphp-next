<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class TableColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    
    public function render()
    {
        return $this->view('table-column.html');
    }
}