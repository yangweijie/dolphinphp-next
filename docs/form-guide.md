# 表单构建器开发指南

## 概述

海豚PHP的表单构建器(FormBuilder)提供了强大的动态表单生成能力，支持多种字段类型、验证规则和交互功能。

## 基本用法

### 创建表单服务

```php
<?php
namespace app\service;

use app\BaseService;
use think\facade\Db;

class FormService extends BaseService
{
    // 获取表单配置
    public function getFormConfig($formId)
    {
        $form = Db::name('forms')->where('id', $formId)->find();
        
        return [
            'title' => $form['title'],
            'fields' => json_decode($form['fields'], true),
            'config' => json_decode($form['config'], true)
        ];
    }
    
    // 处理表单提交
    public function submitForm($formId, $data)
    {
        // 数据验证
        $this->validateFormData($formId, $data);
        
        // 保存数据
        return Db::name('form_data')->insert([
            'form_id' => $formId,
            'data' => json_encode($data),
            'created_at' => time()
        ]);
    }
}
```

## 字段类型

### 文本字段 (TextField)

```php
// 在FormBuilder中定义
'fields' => [
    [
        'name' => 'username',
        'type' => 'text',
        'label' => '用户名',
        'required' => true,
        'placeholder' => '请输入用户名',
        'rules' => ['min:3', 'max:20']
    ]
]
```

### 下拉选择 (SelectField)

```php
'fields' => [
    [
        'name' => 'gender',
        'type' => 'select',
        'label' => '性别',
        'options' => [
            ['value' => 'male', 'label' => '男'],
            ['value' => 'female', 'label' => '女']
        ],
        'default' => 'male'
    ]
]
```

### 复选框 (CheckboxField)

```php
'fields' => [
    [
        'name' => 'hobbies',
        'type' => 'checkbox',
        'label' => '兴趣爱好',
        'options' => [
            ['value' => 'reading', 'label' => '阅读'],
            ['value' => 'sports', 'label' => '运动'],
            ['value' => 'music', 'label' => '音乐']
        ]
    ]
]
```

### 单选框 (RadioField)

```php
'fields' => [
    [
        'name' => 'notification',
        'type' => 'radio',
        'label' => '通知方式',
        'options' => [
            ['value' => 'email', 'label' => '邮件'],
            ['value' => 'sms', 'label' => '短信'],
            ['value' => 'push', 'label' => '推送']
        ],
        'default' => 'email'
    ]
]
```

## 验证规则

### 内置验证规则

```php
'rules' => [
    'required',          // 必填
    'email',             // 邮箱格式
    'url',               // URL格式
    'numeric',           // 数字
    'integer',           // 整数
    'min:6',             // 最小长度
    'max:100',           // 最大长度
    'regex:/^[a-z]+$/'  // 正则表达式
]
```

### 自定义验证规则

```php
// 在FormService中添加
protected function validateFormData($formId, $data)
{
    $config = $this->getFormConfig($formId);
    
    foreach ($config['fields'] as $field) {
        if ($field['required'] && empty($data[$field['name']])) {
            throw new \Exception("字段 {$field['label']} 不能为空");
        }
        
        // 应用其他验证规则
        $this->applyFieldRules($field, $data[$field['name']] ?? null);
    }
}
```

## API接口

### 获取表单配置

```http
GET /api/forms/{formId}/config
```

响应示例：
```json
{
    "success": true,
    "data": {
        "title": "用户注册表单",
        "fields": [
            {
                "name": "username",
                "type": "text",
                "label": "用户名",
                "required": true,
                "placeholder": "请输入用户名"
            }
        ]
    }
}
```

### 提交表单数据

```http
POST /api/forms/{formId}/submit
Content-Type: application/json

{
    "username": "testuser",
    "email": "test@example.com"
}
```

响应示例：
```json
{
    "success": true,
    "message": "表单提交成功"
}
```

## 前端集成

### HTML模板示例

```html
<!-- public/static/pages/form.html -->
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">{{ title }}</h1>
    
    <form hx-post="/api/forms/{{ formId }}/submit" 
          hx-target="#form-result"
          class="space-y-4">
        
        {% for field in fields %}
        <div class="form-field">
            <label class="block text-sm font-medium mb-1">
                {{ field.label }}
                {% if field.required %}<span class="text-red-500">*</span>{% endif %}
            </label>
            
            {% if field.type == 'text' %}
            <input type="text" 
                   name="{{ field.name }}" 
                   placeholder="{{ field.placeholder }}"
                   class="w-full px-3 py-2 border rounded-md"
                   {% if field.required %}required{% endif %}>
            
            {% elseif field.type == 'select' %}
            <select name="{{ field.name }}" class="w-full px-3 py-2 border rounded-md">
                {% for option in field.options %}
                <option value="{{ option.value }}" 
                        {% if option.value == field.default %}selected{% endif %}>
                    {{ option.label }}
                </option>
                {% endfor %}
            </select>
            
            {% elseif field.type == 'checkbox' %}
            <div class="space-y-2">
                {% for option in field.options %}
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="{{ field.name }}[]" 
                           value="{{ option.value }}"
                           class="mr-2">
                    {{ option.label }}
                </label>
                {% endfor %}
            </div>
            
            {% elseif field.type == 'radio' %}
            <div class="space-y-2">
                {% for option in field.options %}
                <label class="flex items-center">
                    <input type="radio" 
                           name="{{ field.name }}" 
                           value="{{ option.value }}"
                           {% if option.value == field.default %}checked{% endif %}
                           class="mr-2">
                    {{ option.label }}
                </label>
                {% endfor %}
            </div>
            {% endif %}
        </div>
        {% endfor %}
        
        <button type="submit" 
                class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
            提交
        </button>
    </form>
    
    <div id="form-result" class="mt-4"></div>
</div>
```

## 高级功能

### 字段联动

支持字段之间的动态联动，例如选择省份后动态加载城市列表。

### 文件上传

集成文件上传功能，支持图片、文档等多种文件类型。

### 富文本编辑器

集成Markdown和富文本编辑器，支持复杂的内容编辑需求。

## 最佳实践

1. **字段命名**: 使用有意义的字段名，避免使用保留关键字
2. **验证优先**: 在服务端和客户端都进行数据验证
3. **错误处理**: 提供清晰的错误提示信息
4. **用户体验**: 考虑表单的响应式和无障碍访问
5. **安全性**: 防止XSS、CSRF等安全攻击

## 常见问题

### Q: 如何添加新的字段类型？
A: 在FormService中添加字段类型处理逻辑，并在前端模板中增加对应的渲染代码。

### Q: 如何实现表单数据的导出？
A: 可以使用ThinkPHP的导出功能或者集成第三方导出库。

### Q: 如何实现表单版本控制？
A: 在数据库中添加版本字段，保存表单的历史版本信息。