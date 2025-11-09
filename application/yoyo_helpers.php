<?php
// 在common.php中添加或单独创建助手函数文件

if (!function_exists('yoyo_text')) {
    /**
     * 文本输入框组件
     */
    function yoyo_text($name, $title, $value = '', $config = [])
    {
        $required = $config['required'] ?? false;
        $placeholder = $config['placeholder'] ?? "请输入{$title}";
        $maxlength = $config['maxlength'] ?? '';
        $readonly = $config['readonly'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';

        $attrs = [];
        if ($required) $attrs[] = 'required';
        if ($readonly) $attrs[] = 'readonly';
        if ($disabled) $attrs[] = 'disabled';
        if ($maxlength) $attrs[] = "maxlength=\"{$maxlength}\"";

        $attr_str = implode(' ', $attrs);

        return <<<HTML
<div class="form-group">
    <label for="{$name}">{$title}</label>
    <input type="text" 
           class="form-control {$class}" 
           id="{$name}" 
           name="{$name}" 
           value="{$value}"
           placeholder="{$placeholder}"
           {$attr_str}>
</div>
HTML;
    }
}

if (!function_exists('yoyo_textarea')) {
    /**
     * 多行文本框组件
     */
    function yoyo_textarea($name, $title, $value = '', $config = [])
    {
        $rows = $config['rows'] ?? 3;
        $placeholder = $config['placeholder'] ?? "请输入{$title}";
        $maxlength = $config['maxlength'] ?? '';
        $readonly = $config['readonly'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';

        $attrs = [];
        if ($readonly) $attrs[] = 'readonly';
        if ($disabled) $attrs[] = 'disabled';
        if ($maxlength) $attrs[] = "maxlength=\"{$maxlength}\"";

        $attr_str = implode(' ', $attrs);

        return <<<HTML
<div class="form-group">
    <label for="{$name}">{$title}</label>
    <textarea class="form-control {$class}" 
              id="{$name}" 
              name="{$name}" 
              rows="{$rows}"
              placeholder="{$placeholder}"
              {$attr_str}>{$value}</textarea>
</div>
HTML;
    }
}

if (!function_exists('yoyo_select')) {
    /**
     * 下拉选择框组件
     */
    function yoyo_select($name, $title, $options = [], $value = '', $config = [])
    {
        $placeholder = $config['placeholder'] ?? "请选择{$title}";
        $multiple = $config['multiple'] ?? false;
        $searchable = $config['searchable'] ?? false;
        $class = $config['class'] ?? '';

        $select_class = $searchable ? 'form-control select2' : 'form-control';
        $multiple_attr = $multiple ? 'multiple' : '';

        $options_html = '';
        if (!$multiple) {
            $options_html .= "<option value=\"\">{$placeholder}</option>";
        }

        foreach ($options as $key => $option) {
            if (is_array($value)) {
                $selected = in_array($key, $value) ? 'selected' : '';
            } else {
                $selected = $value == $key ? 'selected' : '';
            }
            $options_html .= "<option value=\"{$key}\" {$selected}>{$option}</option>";
        }

        return <<<HTML
<div class="form-group">
    <label for="{$name}">{$title}</label>
    <select class="{$select_class} {$class}" 
            id="{$name}" 
            name="{$name}" 
            {$multiple_attr}>
        {$options_html}
    </select>
</div>
HTML;
    }
}

if (!function_exists('yoyo_checkbox')) {
    /**
     * 复选框组件
     */
    function yoyo_checkbox($name, $title, $options = [], $value = [], $config = [])
    {
        $inline = $config['inline'] ?? false;
        $columns = $config['columns'] ?? 1;

        $checkbox_class = $inline ? 'checkbox-inline' : 'checkbox';
        $column_class = $columns > 1 ? "col-md-" . (12 / $columns) : '';

        $checkboxes = '';
        $options_array = array_chunk($options, ceil(count($options) / $columns), true);

        foreach ($options_array as $chunk) {
            if ($columns > 1) {
                $checkboxes .= '<div class="' . $column_class . '">';
            }

            foreach ($chunk as $key => $option) {
                $checked = in_array($key, (array)$value) ? 'checked' : '';
                $checkboxes .= <<<HTML
<div class="{$checkbox_class}">
    <label>
        <input type="checkbox" 
               name="{$name}[]" 
               value="{$key}" 
               {$checked}>
        {$option}
    </label>
</div>
HTML;
            }

            if ($columns > 1) {
                $checkboxes .= '</div>';
            }
        }

        return <<<HTML
<div class="form-group">
    <label>{$title}</label>
    <div class="checkbox-group">
        {$checkboxes}
    </div>
</div>
HTML;
    }
}

if (!function_exists('yoyo_switch')) {
    /**
     * 开关组件
     */
    function yoyo_switch($name, $title, $value = false, $config = [])
    {
        $on_text = $config['on_text'] ?? '开启';
        $off_text = $config['off_text'] ?? '关闭';
        $on_value = $config['on_value'] ?? 1;
        $off_value = $config['off_value'] ?? 0;
        $color = $config['color'] ?? 'success';

        $checked = $value ? 'checked' : '';
        $current_text = $value ? $on_text : $off_text;
        $switch_class = $value ? "switch-{$color}" : 'switch-default';

        return <<<HTML
<div class="form-group">
    <label>{$title}</label>
    <div class="switch-container">
        <label class="switch {$switch_class}">
            <input type="checkbox" 
                   name="{$name}" 
                   value="{$on_value}"
                   {$checked}>
            <span class="slider round">
                <span class="switch-text">{$current_text}</span>
            </span>
        </label>
        <input type="hidden" name="{$name}_hidden" value="{$off_value}">
    </div>
</div>
 
<style>
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 28px;
}
 
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
 
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}
 
.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
 
input:checked + .slider {
    background-color: #5cb85c;
}
 
.switch-success input:checked + .slider {
    background-color: #5cb85c;
}
 
input:checked + .slider:before {
    transform: translateX(32px);
}
 
.switch-text {
    font-size: 10px;
    color: white;
    font-weight: bold;
}
</style>
HTML;
    }
}

if (!function_exists('yoyo_date')) {
    /**
     * 日期选择器组件
     */
    function yoyo_date($name, $title, $value = '', $config = [])
    {
        $format = $config['format'] ?? 'Y-m-d';
        $min_date = $config['min_date'] ?? '';
        $max_date = $config['max_date'] ?? '';
        $show_clear = $config['show_clear'] ?? true;

        $min_attr = $min_date ? "min=\"{$min_date}\"" : '';
        $max_attr = $max_date ? "max=\"{$max_date}\"" : '';

        $clear_btn = '';
        if ($show_clear && $value) {
            $clear_btn = <<<HTML
<span class="input-group-btn">
    <button class="btn btn-default" type="button" onclick="document.getElementById('{$name}').value='';")>
        <i class="fa fa-times"></i>
    </button>
</span>
HTML;
        }

        return <<<HTML
<div class="form-group">
    <label for="{$name}">{$title}</label>
    <div class="input-group">
        <input class="form-control datepicker" 
               type="date" 
               id="{$name}" 
               name="{$name}" 
               value="{$value}"
               {$min_attr}
               {$max_attr}>
        <span class="input-group-addon">
            <i class="fa fa-calendar"></i>
        </span>
        {$clear_btn}
    </div>
</div>
HTML;
    }
}

if (!function_exists('yoyo_file')) {
    /**
     * 文件上传组件
     */
    function yoyo_file($name, $title, $value = '', $config = [])
    {
        $accept = $config['accept'] ?? '';
        $max_size = $config['max_size'] ?? 2048;
        $upload_url = $config['upload_url'] ?? '/upload';

        $accept_attr = $accept ? "accept=\"{$accept}\"" : '';

        return <<<HTML
<div class="form-group">
    <label for="{$name}">{$title}</label>
    <div class="file-upload-container">
        <input type="file" 
               class="form-control" 
               id="{$name}" 
               name="{$name}" 
               {$accept_attr}
               data-upload-url="{$upload_url}"
               data-max-size="{$max_size}">
        <input type="hidden" name="{$name}_path" value="{$value}">
        <div class="file-preview" style="margin-top: 10px;">
            <div class="file-info"></div>
        </div>
    </div>
</div>
HTML;
    }
}

if (!function_exists('yoyo_table')) {
    /**
     * 表格组件
     */
    function yoyo_table($data = [], $columns = [], $config = [])
    {
        $striped = $config['striped'] ?? true;
        $bordered = $config['bordered'] ?? true;
        $hover = $config['hover'] ?? true;
        $responsive = $config['responsive'] ?? true;

        $table_class = 'table';
        if ($striped) $table_class .= ' table-striped';
        if ($bordered) $table_class .= ' table-bordered';
        if ($hover) $table_class .= ' table-hover';

        // 构建表头
        $thead = '<thead><tr>';
        foreach ($columns as $column) {
            $thead .= "<th>{$column['title']}</th>";
        }
        $thead .= '</tr></thead>';

        // 构建表体
        $tbody = '<tbody>';
        foreach ($data as $row) {
            $tbody .= '<tr>';
            foreach ($columns as $column) {
                $field = $column['field'];
                $type = $column['type'] ?? 'text';
                $cell_value = $row[$field] ?? '';

                // 根据类型渲染单元格
                $cell_html = render_table_cell($cell_value, $type, $column['config'] ?? [], $row);
                $tbody .= "<td>{$cell_html}</td>";
            }
            $tbody .= '</tr>';
        }
        $tbody .= '</tbody>';

        $table_html = "<table class=\"{$table_class}\">{$thead}{$tbody}</table>";

        if ($responsive) {
            $table_html = "<div class=\"table-responsive\">{$table_html}</div>";
        }

        return $table_html;
    }
}

if (!function_exists('render_table_cell')) {
    /**
     * 渲染表格单元格
     */
    function render_table_cell($value, $type, $config = [], $row = [])
    {
        switch ($type) {
            case 'switch':
                $checked = $value ? 'checked' : '';
                return "<label class=\"switch\"><input type=\"checkbox\" {$checked} disabled><span class=\"slider\"></span></label>";

            case 'status':
                $status_map = $config['status_map'] ?? [];
                $color_map = $config['color_map'] ?? [];
                $text = $status_map[$value] ?? $value;
                $color = $color_map[$value] ?? 'default';
                return "<span class=\"badge badge-{$color}\">{$text}</span>";

            case 'date':
                return $value ? date('Y-m-d', strtotime($value)) : '-';

            case 'datetime':
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : '-';

            case 'image':
                $width = $config['width'] ?? 50;
                $height = $config['height'] ?? 50;
                return $value ? "<img src=\"{$value}\" style=\"width:{$width}px;height:{$height}px;object-fit:cover;\" alt=''>" : '-';

            case 'callback':
                $callback = $config['callback'] ?? '';
                if ($callback && function_exists($callback)) {
                    return call_user_func($callback, $value, $row);
                }
                return $value;

            default:
                return htmlspecialchars($value);
        }
    }
}