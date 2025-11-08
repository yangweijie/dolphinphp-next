<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class ImageColumn extends Component
{
    public $name;
    public $value;
    public $row;
    public $column;
    public $table;
    public $width = 50;
    public $height = 50;
    public $placeholder = '/static/images/placeholder.png';
    
    public function render()
    {
        return $this->view('image-column.html');
    }
}