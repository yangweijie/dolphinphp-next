<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class ActionColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    public $actions = [];
    
    public function render()
    {
        return $this->view('action-column.html');
    }
}