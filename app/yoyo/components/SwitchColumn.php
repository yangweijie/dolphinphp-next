<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class SwitchColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    public $trueValue = 1;
    public $falseValue = 0;
    public $trueLabel = '是';
    public $falseLabel = '否';
    public $trueClass = 'text-success';
    public $falseClass = 'text-danger';
    
    public function render()
    {
        return $this->view('switch-column.html');
    }
}