<?php

namespace app\service;

/**
 * 文档服务类
 * Class DocumentationService
 * @package app\service
 */
class DocumentationService
{
    /**
     * 获取API文档
     * @param string $apiType API类型
     * @return array
     */
    public function getApiDocumentation($apiType = 'all')
    {
        $docs = [
            'form' => $this->getFormApiDocumentation(),
            'table' => $this->getTableApiDocumentation(),
            'auth' => $this->getAuthDocumentation(),
            'error' => $this->getErrorDocumentation()
        ];
        
        if ($apiType === 'all') {
            return $docs;
        }
        
        return $docs[$apiType] ?? [];
    }
    
    /**
     * 获取表单API文档
     * @return array
     */
    protected function getFormApiDocumentation()
    {
        return [
            'title' => '表单构建器API文档',
            'description' => '表单构建器API提供创建、管理、提交表单的功能',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/api/forms',
                    'description' => '获取表单列表',
                    'parameters' => [
                        'page' => ['type' => 'integer', 'required' => false, 'description' => '页码'],
                        'limit' => ['type' => 'integer', 'required' => false, 'description' => '每页数量'],
                        'search' => ['type' => 'string', 'required' => false, 'description' => '搜索关键词']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '数据']
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/forms/{id}',
                    'description' => '获取表单详情',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表单ID']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '表单数据']
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/forms',
                    'description' => '创建表单',
                    'parameters' => [
                        'title' => ['type' => 'string', 'required' => true, 'description' => '表单标题'],
                        'name' => ['type' => 'string', 'required' => true, 'description' => '表单名称'],
                        'fields' => ['type' => 'array', 'required' => true, 'description' => '字段配置']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '创建结果']
                    ]
                ],
                [
                    'method' => 'PUT',
                    'path' => '/api/forms/{id}',
                    'description' => '更新表单',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表单ID'],
                        'title' => ['type' => 'string', 'required' => false, 'description' => '表单标题'],
                        'fields' => ['type' => 'array', 'required' => false, 'description' => '字段配置']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息']
                    ]
                ],
                [
                    'method' => 'DELETE',
                    'path' => '/api/forms/{id}',
                    'description' => '删除表单',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表单ID']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息']
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/forms/{id}/submit',
                    'description' => '提交表单',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表单ID']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '提交结果']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 获取表格API文档
     * @return array
     */
    protected function getTableApiDocumentation()
    {
        return [
            'title' => '数据表格API文档',
            'description' => '数据表格API提供创建、管理、展示数据表格的功能',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/api/tables',
                    'description' => '获取表格列表',
                    'parameters' => [
                        'page' => ['type' => 'integer', 'required' => false, 'description' => '页码'],
                        'limit' => ['type' => 'integer', 'required' => false, 'description' => '每页数量'],
                        'search' => ['type' => 'string', 'required' => false, 'description' => '搜索关键词']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '数据']
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/tables/{id}',
                    'description' => '获取表格详情',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表格ID']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '表格数据']
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/tables/{name}/data',
                    'description' => '获取表格数据',
                    'parameters' => [
                        'name' => ['type' => 'string', 'required' => true, 'description' => '表格名称'],
                        'page' => ['type' => 'integer', 'required' => false, 'description' => '页码'],
                        'limit' => ['type' => 'integer', 'required' => false, 'description' => '每页数量'],
                        'sort' => ['type' => 'string', 'required' => false, 'description' => '排序字段'],
                        'order' => ['type' => 'string', 'required' => false, 'description' => '排序方向']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '表格数据']
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/tables',
                    'description' => '创建表格',
                    'parameters' => [
                        'title' => ['type' => 'string', 'required' => true, 'description' => '表格标题'],
                        'name' => ['type' => 'string', 'required' => true, 'description' => '表格名称'],
                        'columns' => ['type' => 'array', 'required' => true, 'description' => '列配置']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '创建结果']
                    ]
                ],
                [
                    'method' => 'PUT',
                    'path' => '/api/tables/{id}',
                    'description' => '更新表格',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表格ID'],
                        'title' => ['type' => 'string', 'required' => false, 'description' => '表格标题'],
                        'columns' => ['type' => 'array', 'required' => false, 'description' => '列配置']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息']
                    ]
                ],
                [
                    'method' => 'PUT',
                    'path' => '/api/tables/{name}/rows/{id}',
                    'description' => '更新表格行数据',
                    'parameters' => [
                        'name' => ['type' => 'string', 'required' => true, 'description' => '表格名称'],
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '行ID']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息']
                    ]
                ],
                [
                    'method' => 'DELETE',
                    'path' => '/api/tables/{id}',
                    'description' => '删除表格',
                    'parameters' => [
                        'id' => ['type' => 'integer', 'required' => true, 'description' => '表格ID']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 获取权限系统文档
     * @return array
     */
    protected function getAuthDocumentation()
    {
        return [
            'title' => '权限系统文档',
            'description' => '权限系统提供用户认证、授权和访问控制功能',
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/api/user/login',
                    'description' => '用户登录',
                    'parameters' => [
                        'username' => ['type' => 'string', 'required' => true, 'description' => '用户名或邮箱'],
                        'password' => ['type' => 'string', 'required' => true, 'description' => '密码']
                    ],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '用户信息和令牌']
                    ]
                ],
                [
                    'method' => 'POST',
                    'path' => '/api/user/logout',
                    'description' => '用户登出',
                    'parameters' => [],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息']
                    ]
                ],
                [
                    'method' => 'GET',
                    'path' => '/api/user/info',
                    'description' => '获取当前用户信息',
                    'parameters' => [],
                    'response' => [
                        'code' => ['type' => 'integer', 'description' => '状态码'],
                        'msg' => ['type' => 'string', 'description' => '消息'],
                        'data' => ['type' => 'object', 'description' => '用户信息']
                    ]
                ]
            ],
            'permissions' => [
                'form.create' => '创建表单',
                'form.view' => '查看表单',
                'form.edit' => '编辑表单',
                'form.delete' => '删除表单',
                'form.submit' => '提交表单',
                'table.create' => '创建表格',
                'table.view' => '查看表格',
                'table.edit' => '编辑表格',
                'table.delete' => '删除表格',
                'table.edit.data' => '编辑表格数据',
                'user.create' => '创建用户',
                'user.view' => '查看用户',
                'user.edit' => '编辑用户',
                'user.delete' => '删除用户'
            ]
        ];
    }
    
    /**
     * 获取错误码文档
     * @return array
     */
    protected function getErrorDocumentation()
    {
        return [
            'title' => '错误码说明文档',
            'description' => 'API错误码说明',
            'errors' => [
                200 => '请求成功',
                400 => '请求参数错误',
                401 => '未授权访问',
                403 => '权限不足',
                404 => '资源不存在',
                500 => '服务器内部错误'
            ]
        ];
    }
    
    /**
     * 获取前端组件文档
     * @param string $componentType 组件类型
     * @return array
     */
    public function getComponentDocumentation($componentType = 'all')
    {
        $docs = [
            'ui' => $this->getUiComponentDocumentation(),
            'form' => $this->getFormComponentDocumentation(),
            'table' => $this->getTableComponentDocumentation(),
            'htmx' => $this->getHtmxDocumentation()
        ];
        
        if ($componentType === 'all') {
            return $docs;
        }
        
        return $docs[$componentType] ?? [];
    }
    
    /**
     * 获取UI组件文档
     * @return array
     */
    protected function getUiComponentDocumentation()
    {
        return [
            'title' => 'UI组件文档',
            'description' => '基于TailwindCSS的UI组件',
            'components' => [
                'button' => [
                    'name' => '按钮(Button)',
                    'description' => '支持多种样式和尺寸的按钮组件',
                    'props' => [
                        'type' => ['type' => 'string', 'default' => 'button', 'description' => '按钮类型'],
                        'style' => ['type' => 'string', 'default' => 'primary', 'description' => '按钮样式'],
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '按钮尺寸'],
                        'disabled' => ['type' => 'boolean', 'default' => false, 'description' => '是否禁用'],
                        'block' => ['type' => 'boolean', 'default' => false, 'description' => '是否块级元素']
                    ]
                ],
                'modal' => [
                    'name' => '模态框(Modal)',
                    'description' => '支持动态内容加载的模态框组件',
                    'props' => [
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '模态框尺寸'],
                        'title' => ['type' => 'string', 'default' => '', 'description' => '模态框标题'],
                        'backdrop' => ['type' => 'boolean', 'default' => true, 'description' => '是否显示背景遮罩']
                    ]
                ],
                'alert' => [
                    'name' => '警告(Alert)',
                    'description' => '支持不同类型的消息提示组件',
                    'props' => [
                        'type' => ['type' => 'string', 'default' => 'info', 'description' => '警告类型'],
                        'title' => ['type' => 'string', 'default' => '', 'description' => '警告标题'],
                        'message' => ['type' => 'string', 'default' => '', 'description' => '警告消息'],
                        'dismissible' => ['type' => 'boolean', 'default' => false, 'description' => '是否可关闭']
                    ]
                ],
                'loading' => [
                    'name' => '加载(Loading)',
                    'description' => '支持加载状态显示的组件',
                    'props' => [
                        'type' => ['type' => 'string', 'default' => 'spinner', 'description' => '加载类型'],
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '加载尺寸'],
                        'fullScreen' => ['type' => 'boolean', 'default' => false, 'description' => '是否全屏显示']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 获取表单组件文档
     * @return array
     */
    protected function getFormComponentDocumentation()
    {
        return [
            'title' => '表单组件文档',
            'description' => '表单相关组件和样式',
            'components' => [
                'input' => [
                    'name' => '输入框(Input)',
                    'description' => '支持多种类型的输入框组件',
                    'props' => [
                        'type' => ['type' => 'string', 'default' => 'text', 'description' => '输入框类型'],
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '输入框尺寸'],
                        'disabled' => ['type' => 'boolean', 'default' => false, 'description' => '是否禁用'],
                        'readonly' => ['type' => 'boolean', 'default' => false, 'description' => '是否只读'],
                        'required' => ['type' => 'boolean', 'default' => false, 'description' => '是否必填']
                    ]
                ],
                'formGroup' => [
                    'name' => '表单组(FormGroup)',
                    'description' => '表单组容器组件',
                    'props' => [
                        'hasError' => ['type' => 'boolean', 'default' => false, 'description' => '是否有错误'],
                        'hasSuccess' => ['type' => 'boolean', 'default' => false, 'description' => '是否有成功状态'],
                        'inline' => ['type' => 'boolean', 'default' => false, 'description' => '是否内联布局']
                    ]
                ],
                'error' => [
                    'name' => '错误提示(Error)',
                    'description' => '表单验证错误提示组件',
                    'props' => [
                        'size' => ['type' => 'string', 'default' => 'sm', 'description' => '提示尺寸']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 获取表格组件文档
     * @return array
     */
    protected function getTableComponentDocumentation()
    {
        return [
            'title' => '表格组件文档',
            'description' => '表格相关组件和样式',
            'components' => [
                'table' => [
                    'name' => '表格(Table)',
                    'description' => '数据表格组件',
                    'props' => [
                        'striped' => ['type' => 'boolean', 'default' => false, 'description' => '是否斑马纹'],
                        'hover' => ['type' => 'boolean', 'default' => false, 'description' => '是否悬停效果'],
                        'bordered' => ['type' => 'boolean', 'default' => true, 'description' => '是否显示边框'],
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '表格尺寸']
                    ]
                ],
                'thead' => [
                    'name' => '表头(Thead)',
                    'description' => '表格头部组件',
                    'props' => [
                        'sticky' => ['type' => 'boolean', 'default' => false, 'description' => '是否粘性定位']
                    ]
                ],
                'th' => [
                    'name' => '表头单元格(Th)',
                    'description' => '表头单元格组件',
                    'props' => [
                        'align' => ['type' => 'string', 'default' => 'left', 'description' => '对齐方式'],
                        'sortable' => ['type' => 'boolean', 'default' => false, 'description' => '是否可排序'],
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '单元格尺寸']
                    ]
                ],
                'td' => [
                    'name' => '表格单元格(Td)',
                    'description' => '表格单元格组件',
                    'props' => [
                        'align' => ['type' => 'string', 'default' => 'left', 'description' => '对齐方式'],
                        'size' => ['type' => 'string', 'default' => 'md', 'description' => '单元格尺寸']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 获取HTMX文档
     * @return array
     */
    protected function getHtmxDocumentation()
    {
        return [
            'title' => 'HTMX使用文档',
            'description' => 'HTMX交互功能使用指南',
            'features' => [
                'fieldCascade' => [
                    'name' => '字段联动',
                    'description' => '表单字段之间的联动功能',
                    'usage' => '通过hx-trigger和hx-target实现字段联动'
                ],
                'realTimeValidation' => [
                    'name' => '实时验证',
                    'description' => '表单字段的实时验证功能',
                    'usage' => '通过hx-trigger实现输入时实时验证'
                ],
                'dynamicContent' => [
                    'name' => '动态内容加载',
                    'description' => '页面内容的动态加载功能',
                    'usage' => '通过hx-get和hx-trigger实现内容动态加载'
                ],
                'formSubmit' => [
                    'name' => '无刷新表单提交',
                    'description' => '表单的无刷新提交功能',
                    'usage' => '通过hx-post实现表单无刷新提交'
                ]
            ]
        ];
    }
    
    /**
     * 生成文档HTML
     * @param array $documentation 文档数据
     * @return string
     */
    public function generateDocumentationHtml($documentation)
    {
        $html = '<!DOCTYPE html>';
        $html .= '<html lang="zh-CN">';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>API文档</title>';
        $html .= '<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">';
        $html .= '</head>';
        $html .= '<body class="bg-gray-100">';
        $html .= '<div class="container mx-auto px-4 py-8">';
        
        foreach ($documentation as $sectionKey => $section) {
            $html .= '<div class="bg-white rounded-lg shadow-md p-6 mb-8">';
            $html .= '<h2 class="text-2xl font-bold mb-4">' . htmlspecialchars($section['title']) . '</h2>';
            $html .= '<p class="text-gray-600 mb-6">' . htmlspecialchars($section['description']) . '</p>';
            
            if (isset($section['endpoints'])) {
                $html .= '<div class="space-y-6">';
                foreach ($section['endpoints'] as $endpoint) {
                    $html .= '<div class="border border-gray-200 rounded-lg p-4">';
                    $html .= '<div class="flex items-center mb-2">';
                    $html .= '<span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800 mr-2">' . htmlspecialchars($endpoint['method']) . '</span>';
                    $html .= '<code class="text-sm bg-gray-100 px-2 py-1 rounded">' . htmlspecialchars($endpoint['path']) . '</code>';
                    $html .= '</div>';
                    $html .= '<p class="text-gray-700 mb-3">' . htmlspecialchars($endpoint['description']) . '</p>';
                    
                    if (!empty($endpoint['parameters'])) {
                        $html .= '<div class="mb-3">';
                        $html .= '<h4 class="font-semibold text-gray-700 mb-2">参数:</h4>';
                        $html .= '<table class="min-w-full divide-y divide-gray-200">';
                        $html .= '<thead class="bg-gray-50">';
                        $html .= '<tr>';
                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">参数</th>';
                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">类型</th>';
                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">必填</th>';
                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">说明</th>';
                        $html .= '</tr>';
                        $html .= '</thead>';
                        $html .= '<tbody class="bg-white divide-y divide-gray-200">';
                        
                        foreach ($endpoint['parameters'] as $paramName => $param) {
                            $html .= '<tr>';
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($paramName) . '</td>';
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . htmlspecialchars($param['type']) . '</td>';
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . ($param['required'] ? '是' : '否') . '</td>';
                            $html .= '<td class="px-6 py-4 text-sm text-gray-500">' . htmlspecialchars($param['description']) . '</td>';
                            $html .= '</tr>';
                        }
                        
                        $html .= '</tbody>';
                        $html .= '</table>';
                        $html .= '</div>';
                    }
                    
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</body>';
        $html .= '</html>';
        
        return $html;
    }
}