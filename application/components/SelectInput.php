<?php
namespace app\components;


use Clickfwd\Yoyo\Component;

class SelectInput extends Component
{
    public $name;
    public $value = '';
    public $title = '';
    public $options = [];
    public $placeholder = '';
    public $loadOptionsUrl = '';

    public function mount($name, $title, $options = [], $value = '')
    {
        $this->name = $name;
        $this->title = $title;
        $this->options = $options;
        $this->value = $value;
        $this->placeholder = "请选择{$title}";
    }

    public function loadOptions()
    {
        if ($this->loadOptionsUrl) {
            // 动态加载选项
            $response = file_get_contents($this->loadOptionsUrl);
            $this->options = json_decode($response, true);
        }
    }

    public function render()
    {
        return <<<HTML
<label class="col-xs-12" for="{$this->name}">{$this->title}</label>
<div class="col-xs-12">
    <select class="form-control" 
            id="{$this->name}" 
            name="{$this->name}" 
            wire:model="value">
        <option value="">{$this->placeholder}</option>
        {notempty name="this->validation_errors"}
            <div class="help-block text-danger">
            {foreach $this->options as $key=>$option}
                <option value="{$key}" @if($this->value == $key) selected @endif>
                {$option}
            </option>
            {/foreach}
            </div>
        {/notempty}
        
        @foreach($this->options as $key => $option)
            <option value="{$key}" @if($this->value == $key) selected @endif>
                {$option}
            </option>
        @endforeach
    </select>
</div>
HTML;
    }
}