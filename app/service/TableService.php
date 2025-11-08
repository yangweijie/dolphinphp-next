<?php

namespace app\service;

use app\model\Table;
use think\facade\Db;

/**
 * 表格服务类
 * Class TableService
 * @package app\service
 */
class TableService extends BaseService
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Table();
    }

    /**
     * 获取表格列表（带搜索和分页）
     * @param array $params 查询参数
     * @return array
     */
    public function getTableList($params = [])
    {
        $where = $this->buildWhere($params);
        $options = [
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 15,
            'sort' => $this->buildSort($params),
            'with' => ['creator', 'updater']
        ];

        // 搜索条件
        if (!empty($params['search'])) {
            $options['search'] = [
                'title' => $params['search'],
                'name' => $params['search']
            ];
        }

        return $this->getList($where, $options);
    }

    /**
     * 获取表格信息
     * @param int $id 表格ID
     * @return array|null
     */
    public function getTableInfo($id)
    {
        return $this->getInfo($id, ['creator', 'updater']);
    }

    /**
     * 根据名称获取表格信息
     * @param string $name 表格名称
     * @return array|null
     */
    public function getTableByName($name)
    {
        try {
            $table = $this->model->where('name', $name)->where('status', Table::STATUS_ENABLE)->find();
            return $table ? $table->toArray() : null;
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return null;
        }
    }

    /**
     * 创建表格
     * @param array $data 表格数据
     * @return bool|int
     */
    public function createTable($data)
    {
        try {
            // 检查名称是否已存在
            if ($this->model->where('name', $data['name'])->find()) {
                $this->setError('表格名称已存在');
                return false;
            }

            // 验证列配置
            if (!empty($data['columns'])) {
                $validateResult = $this->validateColumns($data['columns']);
                if ($validateResult !== true) {
                    $this->setError($validateResult);
                    return false;
                }
            }

            // 设置默认状态
            $data['status'] = $data['status'] ?? Table::STATUS_ENABLE;

            return $this->add($data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 更新表格
     * @param int $id 表格ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateTable($id, $data)
    {
        try {
            $table = $this->model->find($id);
            if (!$table) {
                $this->setError('表格不存在');
                return false;
            }

            // 如果修改了名称，检查新名称是否已存在
            if (isset($data['name']) && $data['name'] != $table->name) {
                if ($this->model->where('name', $data['name'])->where('id', '<>', $id)->find()) {
                    $this->setError('表格名称已存在');
                    return false;
                }
            }

            // 验证列配置
            if (!empty($data['columns'])) {
                $validateResult = $this->validateColumns($data['columns']);
                if ($validateResult !== true) {
                    $this->setError($validateResult);
                    return false;
                }
            }

            return $this->edit($id, $data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 删除表格
     * @param int $id 表格ID
     * @return bool
     */
    public function deleteTable($id)
    {
        return $this->del($id);
    }

    /**
     * 批量删除表格
     * @param array $ids 表格ID数组
     * @return bool
     */
    public function batchDeleteTable($ids)
    {
        return $this->batchDel($ids);
    }

    /**
     * 修改表格状态
     * @param int $id 表格ID
     * @param int $status 状态值
     * @return bool
     */
    public function modifyTableStatus($id, $status)
    {
        return $this->modifyStatus($id, $status);
    }

    /**
     * 验证列配置
     * @param array $columns 列配置
     * @return true|string
     */
    protected function validateColumns($columns)
    {
        if (!is_array($columns)) {
            return '列配置必须是数组';
        }

        if (empty($columns)) {
            return '列配置不能为空';
        }

        $columnNames = [];
        foreach ($columns as $index => $column) {
            // 检查必填字段
            if (empty($column['name'])) {
                return '第' . ($index + 1) . '个列缺少name属性';
            }
            if (empty($column['title'])) {
                return '第' . ($index + 1) . '个列缺少title属性';
            }
            if (empty($column['type'])) {
                return '第' . ($index + 1) . '个列缺少type属性';
            }

            // 检查列名是否重复
            if (in_array($column['name'], $columnNames)) {
                return '列名 ' . $column['name'] . ' 重复';
            }
            $columnNames[] = $column['name'];

            // 验证列类型
            $validTypes = ['text', 'number', 'date', 'datetime', 'image', 'switch', 'tag', 'progress', 'badge', 'link', 'action'];
            if (!in_array($column['type'], $validTypes)) {
                return '第' . ($index + 1) . '个列的type属性不合法';
            }

            // 验证对齐方式
            if (isset($column['align']) && !in_array($column['align'], ['left', 'center', 'right'])) {
                return '第' . ($index + 1) . '个列的align属性不合法';
            }
        }

        return true;
    }

    /**
     * 获取表格列类型列表
     * @return array
     */
    public function getColumnTypes()
    {
        return [
            ['value' => 'text', 'label' => '文本'],
            ['value' => 'number', 'label' => '数字'],
            ['value' => 'date', 'label' => '日期'],
            ['value' => 'datetime', 'label' => '日期时间'],
            ['value' => 'image', 'label' => '图片'],
            ['value' => 'switch', 'label' => '开关'],
            ['value' => 'tag', 'label' => '标签'],
            ['value' => 'progress', 'label' => '进度条'],
            ['value' => 'badge', 'label' => '徽章'],
            ['value' => 'link', 'label' => '链接'],
            ['value' => 'action', 'label' => '操作'],
        ];
    }

    /**
     * 获取表格统计信息
     * @return array
     */
    public function getStatistics()
    {
        try {
            $total = $this->model->count();
            $enable = $this->model->where('status', Table::STATUS_ENABLE)->count();
            $disable = $this->model->where('status', Table::STATUS_DISABLE)->count();

            return [
                'total' => $total,
                'enable' => $enable,
                'disable' => $disable
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'enable' => 0,
                'disable' => 0
            ];
        }
    }

    /**
     * 复制表格
     * @param int $id 原表格ID
     * @return bool|int
     */
    public function copyTable($id)
    {
        try {
            $table = $this->model->find($id);
            if (!$table) {
                $this->setError('表格不存在');
                return false;
            }

            // 复制数据
            $data = $table->toArray();
            unset($data['id']);
            unset($data['created_at']);
            unset($data['updated_at']);
            
            // 生成新名称
            $data['title'] = $data['title'] . ' (副本)';
            $data['name'] = $this->generateUniqueName($data['name']);

            return $this->add($data);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 生成唯一的表格名称
     * @param string $originalName 原始名称
     * @return string
     */
    protected function generateUniqueName($originalName)
    {
        $baseName = $originalName;
        $counter = 1;
        
        while ($this->model->where('name', $originalName)->find()) {
            $originalName = $baseName . '_' . $counter;
            $counter++;
        }
        
        return $originalName;
    }

    /**
     * 获取数据源类型列表
     * @return array
     */
    public function getDataSourceTypes()
    {
        return [
            ['value' => 'static', 'label' => '静态数据'],
            ['value' => 'api', 'label' => 'API接口'],
            ['value' => 'database', 'label' => '数据库'],
            ['value' => 'service', 'label' => '服务方法'],
        ];
    }

    /**
     * 更新表格行数据
     * @param string $name 表格名称
     * @param int $id 行ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateTableRow($name, $id, $data)
    {
        try {
            $table = $this->model->where('name', $name)->where('status', Table::STATUS_ENABLE)->find();
            if (!$table) {
                $this->setError('表格不存在或已禁用');
                return false;
            }

            $dataSource = $table->data_source ?? 'static';
            $config = $table->config ?? [];

            switch ($dataSource) {
                case 'database':
                    return $this->updateDatabaseRow($config, $id, $data);
                case 'static':
                    return $this->updateStaticRow($config, $id, $data, $table);
                default:
                    $this->setError('该数据源类型不支持行内编辑');
                    return false;
            }
        } catch (\Exception $e) {
            $this->setError('更新行数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 批量更新表格行数据
     * @param string $name 表格名称
     * @param array $data 批量更新数据
     * @return bool
     */
    public function batchUpdateTableRows($name, $data)
    {
        try {
            $table = $this->model->where('name', $name)->where('status', Table::STATUS_ENABLE)->find();
            if (!$table) {
                $this->setError('表格不存在或已禁用');
                return false;
            }

            $dataSource = $table->data_source ?? 'static';
            $config = $table->config ?? [];

            switch ($dataSource) {
                case 'database':
                    return $this->batchUpdateDatabaseRows($config, $data);
                case 'static':
                    return $this->batchUpdateStaticRows($config, $data, $table);
                default:
                    $this->setError('该数据源类型不支持批量更新');
                    return false;
            }
        } catch (\Exception $e) {
            $this->setError('批量更新行数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取表格数据
     * @param string $name 表格名称
     * @param array $params 查询参数
     * @return array
     */
    public function getTableData($name, $params = [])
    {
        try {
            $table = $this->model->where('name', $name)->where('status', Table::STATUS_ENABLE)->find();
            if (!$table) {
                $this->setError('表格不存在或已禁用');
                return [];
            }

            $dataSource = $table->data_source ?? 'static';
            $config = $table->config ?? [];

            switch ($dataSource) {
                case 'static':
                    return $this->getStaticData($config, $params);
                case 'api':
                    return $this->getApiData($config, $params);
                case 'database':
                    return $this->getDatabaseData($config, $params);
                case 'service':
                    return $this->getServiceData($config, $params);
                default:
                    $this->setError('不支持的数据源类型: ' . $dataSource);
                    return [];
            }
        } catch (\Exception $e) {
            $this->setError('获取表格数据失败: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取静态数据
     * @param array $config 配置信息
     * @param array $params 查询参数
     * @return array
     */
    protected function getStaticData($config, $params)
    {
        // 获取静态数据
        $staticData = $config['staticData'] ?? [];
        
        // 处理分页
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 15);
        $offset = ($page - 1) * $limit;
        
        // 处理排序
        if (!empty($params['sort'])) {
            $sortField = $params['sort'];
            $sortOrder = strtolower($params['order'] ?? 'asc');
            usort($staticData, function($a, $b) use ($sortField, $sortOrder) {
                $result = $a[$sortField] <=> $b[$sortField];
                return $sortOrder === 'desc' ? -$result : $result;
            });
        }
        
        // 处理筛选
        if (!empty($params['filter'])) {
            $filter = $params['filter'];
            $staticData = array_filter($staticData, function($item) use ($filter) {
                foreach ($filter as $key => $value) {
                    if (isset($item[$key]) && strpos((string)$item[$key], (string)$value) === false) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        // 重新索引数组
        $staticData = array_values($staticData);
        
        // 获取总数
        $total = count($staticData);
        
        // 分页数据
        $data = array_slice($staticData, $offset, $limit);
        
        return [
            'total' => $total,
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'last_page' => ceil($total / $limit)
        ];
    }

    /**
     * 获取API数据
     * @param array $config 配置信息
     * @param array $params 查询参数
     * @return array
     */
    protected function getApiData($config, $params)
    {
        try {
            // 获取API配置
            $apiUrl = $config['apiUrl'] ?? '';
            $method = $config['method'] ?? 'GET';
            $headers = $config['headers'] ?? [];
            $paramsMap = $config['paramsMap'] ?? [];
            
            if (empty($apiUrl)) {
                $this->setError('API地址不能为空');
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 构建请求参数
            $requestParams = [];
            foreach ($paramsMap as $tableParam => $apiParam) {
                if (isset($params[$tableParam])) {
                    $requestParams[$apiParam] = $params[$tableParam];
                }
            }
            
            // 添加分页参数
            $requestParams['page'] = $params['page'] ?? 1;
            $requestParams['limit'] = $params['limit'] ?? 15;
            
            // 发起HTTP请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            // 设置请求方法
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestParams));
            } else {
                // GET请求
                if (!empty($requestParams)) {
                    $queryString = http_build_query($requestParams);
                    curl_setopt($ch, CURLOPT_URL, $apiUrl . '?' . $queryString);
                }
            }
            
            // 设置请求头
            if (!empty($headers)) {
                $headerArray = [];
                foreach ($headers as $key => $value) {
                    $headerArray[] = $key . ': ' . $value;
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
            }
            
            // 执行请求
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // 检查响应
            if ($httpCode !== 200) {
                $this->setError('API请求失败，HTTP状态码: ' . $httpCode);
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 解析响应
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->setError('API响应解析失败: ' . json_last_error_msg());
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 提取数据
            $data = $responseData['data'] ?? [];
            $total = $responseData['total'] ?? count($data);
            $page = $responseData['page'] ?? ($params['page'] ?? 1);
            $limit = $responseData['limit'] ?? ($params['limit'] ?? 15);
            $lastPage = $responseData['last_page'] ?? ceil($total / $limit);
            
            return [
                'total' => $total,
                'data' => $data,
                'page' => $page,
                'limit' => $limit,
                'last_page' => $lastPage
            ];
        } catch (\Exception $e) {
            $this->setError('API请求异常: ' . $e->getMessage());
            return [
                'total' => 0,
                'data' => [],
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 15
            ];
        }
    }

    /**
     * 获取数据库数据
     * @param array $config 配置信息
     * @param array $params 查询参数
     * @return array
     */
    protected function getDatabaseData($config, $params)
    {
        try {
            // 获取数据库配置
            $tableName = $config['tableName'] ?? '';
            $queryConfig = $config['query'] ?? [];
            
            if (empty($tableName)) {
                $this->setError('数据库表名不能为空');
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 构建查询
            $query = Db::table($tableName);
            
            // 处理筛选条件
            if (!empty($queryConfig['where'])) {
                foreach ($queryConfig['where'] as $where) {
                    if (isset($where['field']) && isset($where['operator']) && isset($where['value'])) {
                        $query->where($where['field'], $where['operator'], $where['value']);
                    }
                }
            }
            
            // 处理搜索条件
            if (!empty($params['search'])) {
                $searchFields = $queryConfig['searchFields'] ?? [];
                if (!empty($searchFields)) {
                    $query->where(function($q) use ($searchFields, $params) {
                        foreach ($searchFields as $field) {
                            $q->whereOr($field, 'like', '%' . $params['search'] . '%');
                        }
                    });
                }
            }
            
            // 获取总数
            $total = $query->count();
            
            // 处理排序
            if (!empty($params['sort'])) {
                $sortField = $params['sort'];
                $sortOrder = strtolower($params['order'] ?? 'asc');
                // 确保排序字段是允许的字段
                $allowedSortFields = $queryConfig['sortFields'] ?? [];
                if (empty($allowedSortFields) || in_array($sortField, $allowedSortFields)) {
                    $query->order($sortField, $sortOrder);
                }
            } else {
                // 默认排序
                $defaultSort = $queryConfig['defaultSort'] ?? [];
                if (!empty($defaultSort)) {
                    foreach ($defaultSort as $field => $order) {
                        $query->order($field, $order);
                    }
                } else {
                    $query->order('id', 'desc');
                }
            }
            
            // 处理分页
            $page = (int)($params['page'] ?? 1);
            $limit = (int)($params['limit'] ?? 15);
            $offset = ($page - 1) * $limit;
            
            // 获取数据
            $data = $query->limit($offset, $limit)->select()->toArray();
            
            return [
                'total' => $total,
                'data' => $data,
                'page' => $page,
                'limit' => $limit,
                'last_page' => ceil($total / $limit)
            ];
        } catch (\Exception $e) {
            $this->setError('查询数据库失败: ' . $e->getMessage());
            return [
                'total' => 0,
                'data' => [],
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 15
            ];
        }
    }

    /**
     * 获取服务数据
     * @param array $config 配置信息
     * @param array $params 查询参数
     * @return array
     */
    protected function getServiceData($config, $params)
    {
        try {
            // 获取服务配置
            $serviceClass = $config['serviceClass'] ?? '';
            $method = $config['method'] ?? 'getList';
            $paramsMap = $config['paramsMap'] ?? [];
            
            if (empty($serviceClass)) {
                $this->setError('服务类名不能为空');
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 检查服务类是否存在
            if (!class_exists($serviceClass)) {
                $this->setError('服务类不存在: ' . $serviceClass);
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 创建服务实例
            $service = new $serviceClass();
            
            // 检查方法是否存在
            if (!method_exists($service, $method)) {
                $this->setError('服务方法不存在: ' . $method);
                return [
                    'total' => 0,
                    'data' => [],
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15
                ];
            }
            
            // 构建方法参数
            $methodParams = [];
            foreach ($paramsMap as $tableParam => $serviceParam) {
                if (isset($params[$tableParam])) {
                    $methodParams[$serviceParam] = $params[$tableParam];
                }
            }
            
            // 添加分页参数
            $methodParams['page'] = $params['page'] ?? 1;
            $methodParams['limit'] = $params['limit'] ?? 15;
            
            // 调用服务方法
            $result = call_user_func([$service, $method], $methodParams);
            
            // 处理结果
            if (is_array($result)) {
                // 如果返回的是数组格式的数据
                if (isset($result['data']) && isset($result['total'])) {
                    return $result;
                }
                
                // 如果返回的是简单数组，转换为标准格式
                return [
                    'total' => count($result),
                    'data' => $result,
                    'page' => $params['page'] ?? 1,
                    'limit' => $params['limit'] ?? 15,
                    'last_page' => 1
                ];
            }
            
            // 如果返回的是其他类型，转换为空数组
            return [
                'total' => 0,
                'data' => [],
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 15,
                'last_page' => 1
            ];
        } catch (\Exception $e) {
            $this->setError('调用服务失败: ' . $e->getMessage());
            return [
                'total' => 0,
                'data' => [],
                'page' => $params['page'] ?? 1,
                'limit' => $params['limit'] ?? 15
            ];
        }
    }

    /**
     * 更新数据库行数据
     * @param array $config 配置信息
     * @param int $id 行ID
     * @param array $data 更新数据
     * @return bool
     */
    protected function updateDatabaseRow($config, $id, $data)
    {
        try {
            // 获取数据库配置
            $tableName = $config['tableName'] ?? '';
            $primaryKey = $config['primaryKey'] ?? 'id';
            
            if (empty($tableName)) {
                $this->setError('数据库表名不能为空');
                return false;
            }
            
            // 过滤不允许更新的字段
            $protectedFields = ['id', 'created_at', 'updated_at'];
            foreach ($protectedFields as $field) {
                unset($data[$field]);
            }
            
            // 更新数据
            $result = Db::table($tableName)->where($primaryKey, $id)->update($data);
            return $result !== false;
        } catch (\Exception $e) {
            $this->setError('更新数据库行失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 批量更新数据库行数据
     * @param array $config 配置信息
     * @param array $data 批量更新数据
     * @return bool
     */
    protected function batchUpdateDatabaseRows($config, $data)
    {
        try {
            // 获取数据库配置
            $tableName = $config['tableName'] ?? '';
            $primaryKey = $config['primaryKey'] ?? 'id';
            
            if (empty($tableName)) {
                $this->setError('数据库表名不能为空');
                return false;
            }
            
            // 批量更新数据
            foreach ($data as $row) {
                if (!isset($row[$primaryKey])) {
                    $this->setError('缺少主键字段: ' . $primaryKey);
                    return false;
                }
                
                $id = $row[$primaryKey];
                unset($row[$primaryKey]);
                
                // 过滤不允许更新的字段
                $protectedFields = ['id', 'created_at', 'updated_at'];
                foreach ($protectedFields as $field) {
                    unset($row[$field]);
                }
                
                // 更新数据
                Db::table($tableName)->where($primaryKey, $id)->update($row);
            }
            
            return true;
        } catch (\Exception $e) {
            $this->setError('批量更新数据库行失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 更新静态行数据
     * @param array $config 配置信息
     * @param int $id 行ID
     * @param array $data 更新数据
     * @param Table $table 表格模型
     * @return bool
     */
    protected function updateStaticRow($config, $id, $data, $table)
    {
        try {
            // 获取静态数据
            $staticData = $config['staticData'] ?? [];
            
            // 查找并更新指定行
            $found = false;
            foreach ($staticData as &$row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    // 合并更新数据
                    $row = array_merge($row, $data);
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $this->setError('未找到指定的行数据');
                return false;
            }
            
            // 更新配置中的静态数据
            $config['staticData'] = $staticData;
            
            // 保存到表格配置中
            return $table->save(['config' => $config]) !== false;
        } catch (\Exception $e) {
            $this->setError('更新静态行数据失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 批量更新静态行数据
     * @param array $config 配置信息
     * @param array $data 批量更新数据
     * @param Table $table 表格模型
     * @return bool
     */
    protected function batchUpdateStaticRows($config, $data, $table)
    {
        try {
            // 获取静态数据
            $staticData = $config['staticData'] ?? [];
            
            // 批量更新数据
            foreach ($data as $row) {
                if (!isset($row['id'])) {
                    $this->setError('缺少ID字段');
                    return false;
                }
                
                $id = $row['id'];
                
                // 查找并更新指定行
                $found = false;
                foreach ($staticData as &$staticRow) {
                    if (isset($staticRow['id']) && $staticRow['id'] == $id) {
                        // 合并更新数据
                        $staticRow = array_merge($staticRow, $row);
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $this->setError('未找到ID为 ' . $id . ' 的行数据');
                    return false;
                }
            }
            
            // 更新配置中的静态数据
            $config['staticData'] = $staticData;
            
            // 保存到表格配置中
            return $table->save(['config' => $config]) !== false;
        } catch (\Exception $e) {
            $this->setError('批量更新静态行数据失败: ' . $e->getMessage());
            return false;
        }
    }
}