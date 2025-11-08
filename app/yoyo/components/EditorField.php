<?php

namespace app\yoyo\components;

use Clickfwd\Yoyo\Component;

class EditorField extends Component
{
    public $name;
    public $label;
    public $value = '';
    public $required = false;
    public $helpText;
    public $placeholder = '请输入内容...';
    public $height = 300;
    
    public function render()
    {
        return $this->view('editor-field.html');
    }
}