<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class DateColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    public $format = 'Y-m-d';
    
    public function render()
    {
        return $this->view('date-column.html');
    }
}