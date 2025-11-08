# 表格构建器开发指南

## 概述

海豚PHP的表格构建器(TableBuilder)提供了强大的数据表格展示和管理功能，支持分页、排序、筛选、行内编辑等高级特性。

## 基本用法

### 创建表格服务

```php
<?php
namespace app\service;

use app\BaseService;
use think\facade\Db;

class TableService extends BaseService
{
    // 获取表格数据
    public function getTableData($tableId, $params = [])
    {
        $tableConfig = $this->getTableConfig($tableId);
        
        // 构建查询
        $query = Db::name($tableConfig['source']);
        
        // 应用筛选条件
        $this->applyFilters($query, $params);
        
        // 应用排序
        $this->applySorting($query, $params);
        
        // 分页处理
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 15;
        
        $total = $query->count();
        $data = $query->page($page, $pageSize)->select();
        
        return [
            'data' => $data,
            'total' => $total,
            'currentPage' => $page,
            'pageSize' => $pageSize,
            'totalPages' => ceil($total / $pageSize)
        ];
    }
    
    // 获取表格配置
    public function getTableConfig($tableId)
    {
        return Db::name('tables')->where('id', $tableId)->find();
    }
}
```

## 表格配置

### 基本配置

```php
// 表格配置示例
$config = [
    'title' => '用户列表',
    'source' => 'users',           // 数据源表名
    'primaryKey' => 'id',          // 主键字段
    'columns' => [                 // 列配置
        [
            'field' => 'username',
            'title' => '用户名',
            'sortable' => true,
            'filterable' => true
        ],
        [
            'field' => 'email',
            'title' => '邮箱',
            'sortable' => true
        ],
        [
            'field' => 'created_at',
            'title' => '创建时间',
            'type' => 'date',
            'format' => 'Y-m-d H:i:s'
        ]
    ],
    'actions' => [                 // 操作按钮
        [
            'name' => 'edit',
            'title' => '编辑',
            'type' => 'primary',
            'action' => 'editRow'
        ],
        [
            'name' => 'delete',
            'title' => '删除',
            'type' => 'danger',
            'action' => 'deleteRow',
            'confirm' => true
        ]
    ]
];
```

## 列类型

### 文本列 (TextColumn)

```php
'columns' => [
    [
        'field' => 'name',
        'title' => '姓名',
        'type' => 'text',
        'sortable' => true,
        'filterable' => true
    ]
]
```

### 数字列 (NumberColumn)

```php
'columns' => [
    [
        'field' => 'age',
        'title' => '年龄',
        'type' => 'number',
        'format' => '{value} 岁',
        'sortable' => true
    ]
]
```

### 日期列 (DateColumn)

```php
'columns' => [
    [
        'field' => 'created_at',
        'title' => '创建时间',
        'type' => 'date',
        'format' => 'Y-m-d H:i:s',
        'sortable' => true
    ]
]
```

### 操作列 (ActionColumn)

```php
'columns' => [
    [
        'field' => 'actions',
        'title' => '操作',
        'type' => 'actions',
        'actions' => [
            [
                'name' => 'view',
                'title' => '查看',
                'icon' => 'eye',
                'action' => 'viewItem'
            ],
            [
                'name' => 'edit',
                'title' => '编辑',
                'icon' => 'edit',
                'action' => 'editItem'
            ]
        ]
    ]
]
```

## API接口

### 获取表格数据

```http
GET /api/tables/{tableId}/data?page=1&pageSize=15&sort=username&order=asc
```

查询参数：
- `page`: 当前页码
- `pageSize`: 每页数量
- `sort`: 排序字段
- `order`: 排序方向 (asc/desc)
- `filters`: 筛选条件 (JSON格式)

响应示例：
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "username": "admin",
                "email": "admin@example.com",
                "created_at": "2023-01-01 10:00:00"
            }
        ],
        "total": 100,
        "currentPage": 1,
        "pageSize": 15,
        "totalPages": 7
    }
}
```

### 行内编辑

```http
PUT /api/tables/{tableId}/rows/{rowId}
Content-Type: application/json

{
    "username": "newadmin",
    "email": "newadmin@example.com"
}
```

响应示例：
```json
{
    "success": true,
    "message": "更新成功",
    "data": {
        "id": 1,
        "username": "newadmin",
        "email": "newadmin@example.com"
    }
}
```

### 删除行数据

```http
DELETE /api/tables/{tableId}/rows/{rowId}
```

响应示例：
```json
{
    "success": true,
    "message": "删除成功"
}
```

## 前端集成

### HTML模板示例

```html
<!-- public/static/pages/table.html -->
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">{{ title }}</h1>
    
    <!-- 筛选工具栏 -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-wrap gap-4">
            <!-- 搜索框 -->
            <div class="flex-1">
                <input type="text" 
                       placeholder="搜索..." 
                       class="w-full px-3 py-2 border rounded-md"
                       hx-get="/api/tables/{{ tableId }}/data"
                       hx-target="#table-body"
                       hx-trigger="keyup changed delay:500ms"
                       name="search">
            </div>
            
            <!-- 筛选按钮 -->
            <button class="bg-gray-200 px-4 py-2 rounded-md">
                筛选
            </button>
        </div>
    </div>
    
    <!-- 表格 -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    {% for column in columns %}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ column.title }}
                        {% if column.sortable %}
                        <button class="ml-1" 
                                hx-get="/api/tables/{{ tableId }}/data?sort={{ column.field }}&order=asc"
                                hx-target="#table-body">
                            ↕️
                        </button>
                        {% endif %}
                    </th>
                    {% endfor %}
                </tr>
            </thead>
            
            <tbody id="table-body" class="divide-y divide-gray-200">
                {% include 'table_rows.html' %}
            </tbody>
        </table>
        
        <!-- 分页 -->
        <div class="px-6 py-4 bg-gray-50">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                    显示 {{ (currentPage - 1) * pageSize + 1 }} 到 
                    {{ min(currentPage * pageSize, total) }} 条，共 {{ total }} 条
                </div>
                
                <div class="flex space-x-2">
                    {% if currentPage > 1 %}
                    <button class="px-3 py-1 border rounded"
                            hx-get="/api/tables/{{ tableId }}/data?page={{ currentPage - 1 }}"
                            hx-target="#table-body">
                        上一页
                    </button>
                    {% endif %}
                    
                    {% if currentPage < totalPages %}
                    <button class="px-3 py-1 border rounded"
                            hx-get="/api/tables/{{ tableId }}/data?page={{ currentPage + 1 }}"
                            hx-target="#table-body">
                        下一页
                    </button>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
</div>
```

### 表格行模板

```html
<!-- public/static/pages/table_rows.html -->
{% for item in items %}
<tr class="hover:bg-gray-50">
    {% for column in columns %}
    <td class="px-6 py-4 whitespace-nowrap">
        {% if column.type == 'text' %}
        {{ item[column.field] }}
        
        {% elseif column.type == 'number' %}
        {{ column.format|replace('{value}', item[column.field]) }}
        
        {% elseif column.type == 'date' %}
        {{ item[column.field]|date(column.format) }}
        
        {% elseif column.type == 'actions' %}
        <div class="flex space-x-2">
            {% for action in column.actions %}
            <button class="px-3 py-1 bg-{{ action.type }}-500 text-white rounded"
                    hx-{{ action.method|default('get') }}="/api/{{ action.url }}"
                    hx-target="#modal">
                {{ action.title }}
            </button>
            {% endfor %}
        </div>
        {% endif %}
    </td>
    {% endfor %}
</tr>
{% endfor %}
```

## 高级功能

### 批量操作

支持批量选择、批量编辑、批量删除等操作。

### 数据导出

支持将表格数据导出为Excel、CSV等格式。

### 自定义渲染

支持自定义单元格渲染函数，实现复杂的显示逻辑。

### 实时数据

支持WebSocket实时数据更新，适用于监控仪表盘等场景。

## 最佳实践

1. **分页优化**: 对于大数据量的表格，使用高效的分页查询
2. **索引优化**: 为常用的排序和筛选字段添加数据库索引
3. **缓存策略**: 对静态数据使用缓存，提高性能
4. **权限控制**: 实现行级别和列级别的数据权限控制
5. **响应式设计**: 确保表格在不同设备上都有良好的显示效果

## 性能优化

### 数据库优化

```php
// 使用索引优化查询
$query->where('status', 1)
      ->order('created_at DESC')
      ->field('id,username,email,created_at') // 只选择需要的字段
      ->page($page, $pageSize);
```

### 前端优化

- 使用虚拟滚动处理大量数据
- 实现懒加载和无限滚动
- 减少DOM操作，使用高效的模板渲染

## 常见问题

### Q: 如何处理大数据量的表格？
A: 使用分页查询，只加载当前页的数据，避免一次性加载所有数据。

### Q: 如何实现复杂的筛选条件？
A: 使用动态查询构建器，支持多条件组合筛选。

### Q: 如何保证数据的安全性？
A: 实现权限验证，防止越权访问和数据泄露。