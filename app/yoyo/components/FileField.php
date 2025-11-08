<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class FileField extends Component
{
    public $name;
    public $label;
    public $value;
    public $required = false;
    public $helpText;
    public $accept;
    public $multiple = false;
    
    public function render()
    {
        return $this->view('file-field.html');
    }
}