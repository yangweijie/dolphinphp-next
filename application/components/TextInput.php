<?php
namespace app\components;

use Clickfwd\Yoyo\Component;

class TextInput extends Component
{
    public $name;
    public $value = '';
    public $placeholder = '';
    public $title = '';
    public $validation_errors = [];

    public function mount($name, $title = '', $value = '', $placeholder = '')
    {
        $this->name = $name;
        $this->title = $title;
        $this->value = $value;
        $this->placeholder = $placeholder ?: "请输入{$title}";
    }

    public function updated($property, $value)
    {
        if ($property === 'value') {
            $this->validateField();
        }
    }

    public function validateField()
    {
        $this->validation_errors = [];

        // 示例验证逻辑
        if (empty($this->value)) {
            $this->validation_errors[] = "{$this->title}不能为空";
        }

        // 触发验证事件
        $this->emit('field-validated', [
            'field' => $this->name,
            'valid' => empty($this->validation_errors)
        ]);
    }

    public function render()
    {
        return <<<HTML
<label class="col-xs-12" for="{$this->name}">{$this->title}</label>
<div class="col-xs-12">
    <input class="form-control" 
           type="text" 
           id="{$this->name}" 
           name="{$this->name}" 
           wire:model.lazy="value"
           placeholder="{$this->placeholder}" />
    {notempty name="this->validation_errors"}
        <div class="help-block text-danger">
        {foreach $this->validation_errors as $error}
            <div>{$error}</div>
        {/foreach}
        </div>
    {/notempty}
</div>
HTML;
    }
}