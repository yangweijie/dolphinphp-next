<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class NumberColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    public $decimals = 0;
    public $decPoint = '.';
    public $thousandsSep = ',';
    
    public function render()
    {
        return $this->view('number-column.html');
    }
}