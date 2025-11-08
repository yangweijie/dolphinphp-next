<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class TextColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    public $maxLength = 0;
    
    public function render()
    {
        return $this->view('text-column.html');
    }
}