<?php
namespace app\common\taglib;

use think\template\TagLib;

class Yoyo extends TagLib
{
    protected $tags = [
        // 表单组件标签
        'form'          => ['attr' => 'action,method,config', 'close' => 1],
        'text'          => ['attr' => 'name,title,value,config', 'close' => 0],
        'textarea'      => ['attr' => 'name,title,value,config', 'close' => 0],
        'password'      => ['attr' => 'name,title,value,config', 'close' => 0],
        'number'        => ['attr' => 'name,title,value,config', 'close' => 0],
        'select'        => ['attr' => 'name,title,options,value,config', 'close' => 0],
        'checkbox'      => ['attr' => 'name,title,options,value,config', 'close' => 0],
        'radio'         => ['attr' => 'name,title,options,value,config', 'close' => 0],
        'switch'        => ['attr' => 'name,title,value,config', 'close' => 0],
        'date'          => ['attr' => 'name,title,value,config', 'close' => 0],
        'hidden'        => ['attr' => 'name,value', 'close' => 0],

        // 表格组件标签 - 修正这里
        'table'         => ['attr' => 'data,config', 'close' => 1],
        'column'        => ['attr' => 'field,title,type,config', 'close' => 0],

        // 布局组件标签
        'card'          => ['attr' => 'title,config', 'close' => 1],

        // 其他组件标签
        'paginate'      => ['attr' => 'config', 'close' => 0],
    ];

    /**
     * 表单标签
     */
    public function tagForm($tag, $content)
    {
        $action = $this->autoBuildVar($tag['action'] ?? "''");
        $method = $this->autoBuildVar($tag['method'] ?? "'POST'");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        $parse = '<?php ';
        $parse .= '$__yoyo_form_config = array_merge([';
        $parse .= "'action' => {$action},";
        $parse .= "'method' => {$method}";
        $parse .= '], ' . $config . '); ?>';
        $parse .= '<form action="<?php echo $__yoyo_form_config[\'action\']; ?>" ';
        $parse .= 'method="<?php echo $__yoyo_form_config[\'method\']; ?>" ';
        $parse .= 'class="yoyo-form">';
        $parse .= $content;
        $parse .= '</form>';

        return $parse;
    }

    /**
     * 文本输入框标签
     */
    public function tagText($tag, $content)
    {
        $config = $tag['config'] ??[];
        $name = $this->autoBuildVar($name);
        $title = $this->autoBuildVar($title);
        $value = $this->autoBuildVar($value)??'';
        $config = $this->autoBuildVar($config)??[];
//        var_dump($config);
//        die;
        return <<<TPL
{assign name="config" value="$config" /}
{:yoyo_text('$name' , '$title' , '$value', \$config)}
TPL;
    }

    /**
     * 多行文本框标签
     */
    public function tagTextarea($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $value = $this->autoBuildVar($tag['value'] ?? "''");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_textarea(' . $name . ', ' . $title . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 密码框标签
     */
    public function tagPassword($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $value = $this->autoBuildVar($tag['value'] ?? "''");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_password(' . $name . ', ' . $title . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 数字输入框标签
     */
    public function tagNumber($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $value = $this->autoBuildVar($tag['value'] ?? "0");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_number(' . $name . ', ' . $title . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 下拉选择框标签
     */
    public function tagSelect($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $options = $this->autoBuildVar($tag['options'] ?? '[]');
        $value = $this->autoBuildVar($tag['value'] ?? "''");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_select(' . $name . ', ' . $title . ', ' . $options . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 复选框标签
     */
    public function tagCheckbox($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $options = $this->autoBuildVar($tag['options'] ?? '[]');
        $value = $this->autoBuildVar($tag['value'] ?? '[]');
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_checkbox(' . $name . ', ' . $title . ', ' . $options . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 单选框标签
     */
    public function tagRadio($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $options = $this->autoBuildVar($tag['options'] ?? '[]');
        $value = $this->autoBuildVar($tag['value'] ?? "''");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_radio(' . $name . ', ' . $title . ', ' . $options . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 开关标签
     */
    public function tagSwitch($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $value = $this->autoBuildVar($tag['value'] ?? 'false');
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_switch(' . $name . ', ' . $title . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 日期选择器标签
     */
    public function tagDate($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $value = $this->autoBuildVar($tag['value'] ?? "''");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo yoyo_date(' . $name . ', ' . $title . ', ' . $value . ', ' . $config . '); ?>';
    }

    /**
     * 隐藏字段标签
     */
    public function tagHidden($tag, $content)
    {
        $name = $this->autoBuildVar($tag['name'] ?? "''");
        $value = $this->autoBuildVar($tag['value'] ?? "''");

        return '<input type="hidden" name="<?php echo ' . $name . '; ?>" value="<?php echo ' . $value . '; ?>">';
    }

    /**
     * 表格标签 - 关键修正
     */
    public function tagTable($tag, $content)
    {
        $data = $this->autoBuildVar($tag['data'] ?? '[]');
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        $parse = '<?php ';
        $parse .= '$__yoyo_table_data = ' . $data . '; ';
        $parse .= '$__yoyo_table_config = ' . $config . '; ';
        $parse .= '$__yoyo_table_columns = []; ';
        $parse .= '?>';
        $parse .= $content;
        $parse .= '<?php echo yoyo_table($__yoyo_table_data, $__yoyo_table_columns, $__yoyo_table_config); ?>';

        return $parse;
    }

    /**
     * 表格列标签 - 关键修正
     */
    public function tagColumn($tag, $content)
    {
        $field = $this->autoBuildVar($tag['field'] ?? "''");
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $type = $this->autoBuildVar($tag['type'] ?? "'text'");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        $parse = '<?php $__yoyo_table_columns[] = [';
        $parse .= '"field" => ' . $field . ', ';
        $parse .= '"title" => ' . $title . ', ';
        $parse .= '"type" => ' . $type . ', ';
        $parse .= '"config" => ' . $config;
        $parse .= ']; ?>';

        return $parse;
    }

    /**
     * 卡片标签
     */
    public function tagCard($tag, $content)
    {
        $title = $this->autoBuildVar($tag['title'] ?? "''");
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        $parse = '<?php $__card_config = ' . $config . '; ?>';
        $parse .= '<div class="card">';
        $parse .= '<?php if(' . $title . '): ?>';
        $parse .= '<div class="card-header">';
        $parse .= '<h4 class="card-title"><?php echo ' . $title . '; ?></h4>';
        $parse .= '</div>';
        $parse .= '<?php endif; ?>';
        $parse .= '<div class="card-body">';
        $parse .= $content;
        $parse .= '</div>';
        $parse .= '</div>';

        return $parse;
    }

    /**
     * 分页标签
     */
    public function tagPaginate($tag, $content)
    {
        $config = $this->autoBuildVar($tag['config'] ?? '[]');

        return '<?php echo htmx_paginate(' . $config . '); ?>';
    }
}