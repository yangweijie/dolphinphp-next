<?php

namespace app\model;

use think\Model;

class Table extends Model
{
    /**
     * 设置当前模型对应的完整数据表名称
     * @var string
     */
    protected $table = 'dp_tables';

    /**
     * 设置主键名
     * @var string
     */
    protected $pk = 'id';

    /**
     * 自动时间戳
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 时间字段取出后的默认时间格式
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 字段类型映射
     * @var array
     */
    protected $type = [
        'id' => 'integer',
        'columns' => 'json',
        'config' => 'json',
        'filters' => 'json',
        'actions' => 'json',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 可搜索字段
     * @var array
     */
    protected $searchField = ['title', 'name'];

    /**
     * 可排序字段
     * @var array
     */
    protected $sortField = ['id', 'title', 'name', 'created_at'];

    /**
     * 表格状态常量
     */
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

    /**
     * 数据源类型常量
     */
    const DATA_SOURCE_MANUAL = 'manual';
    const DATA_SOURCE_API = 'api';
    const DATA_SOURCE_MODEL = 'model';

    /**
     * 获取状态标签
     * @param int $status
     * @return string
     */
    public static function getStatusLabel($status = null)
    {
        $labels = [
            self::STATUS_DISABLE => '禁用',
            self::STATUS_ENABLE => '启用',
        ];
        return $status !== null ? ($labels[$status] ?? '未知') : $labels;
    }

    /**
     * 获取数据源标签
     * @param string $dataSource
     * @return string
     */
    public static function getDataSourceLabel($dataSource = null)
    {
        $labels = [
            self::DATA_SOURCE_MANUAL => '手动输入',
            self::DATA_SOURCE_API => 'API接口',
            self::DATA_SOURCE_MODEL => '数据模型',
        ];
        return $dataSource !== null ? ($labels[$dataSource] ?? '未知') : $labels;
    }

    /**
     * 获取列配置
     * @return array
     */
    public function getColumnsConfig()
    {
        return $this->columns ?: [];
    }

    /**
     * 获取表格配置
     * @return array
     */
    public function getTableConfig()
    {
        return $this->config ?: [];
    }

    /**
     * 获取筛选配置
     * @return array
     */
    public function getFiltersConfig()
    {
        return $this->filters ?: [];
    }

    /**
     * 获取操作配置
     * @return array
     */
    public function getActionsConfig()
    {
        return $this->actions ?: [];
    }

    /**
     * 设置列配置
     * @param array $columns
     * @return $this
     */
    public function setColumnsAttr($columns)
    {
        return is_array($columns) ? json_encode($columns, JSON_UNESCAPED_UNICODE) : $columns;
    }

    /**
     * 设置表格配置
     * @param array $config
     * @return $this
     */
    public function setConfigAttr($config)
    {
        return is_array($config) ? json_encode($config, JSON_UNESCAPED_UNICODE) : $config;
    }

    /**
     * 设置筛选配置
     * @param array $filters
     * @return $this
     */
    public function setFiltersAttr($filters)
    {
        return is_array($filters) ? json_encode($filters, JSON_UNESCAPED_UNICODE) : $filters;
    }

    /**
     * 设置操作配置
     * @param array $actions
     * @return $this
     */
    public function setActionsAttr($actions)
    {
        return is_array($actions) ? json_encode($actions, JSON_UNESCAPED_UNICODE) : $actions;
    }

    /**
     * 获取列配置
     * @param string $value
     * @return array
     */
    public function getColumnsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 获取表格配置
     * @param string $value
     * @return array
     */
    public function getConfigAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 获取筛选配置
     * @param string $value
     * @return array
     */
    public function getFiltersAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 获取操作配置
     * @param string $value
     * @return array
     */
    public function getActionsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 搜索器：标题
     * @param \think\db\Query $query
     * @param string $value
     * @return void
     */
    public function searchTitleAttr($query, $value)
    {
        $query->whereLike('title', '%' . $value . '%');
    }

    /**
     * 搜索器：名称
     * @param \think\db\Query $query
     * @param string $value
     * @return void
     */
    public function searchNameAttr($query, $value)
    {
        $query->whereLike('name', '%' . $value . '%');
    }

    /**
     * 搜索器：状态
     * @param \think\db\Query $query
     * @param int $value
     * @return void
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 搜索器：数据源
     * @param \think\db\Query $query
     * @param string $value
     * @return void
     */
    public function searchDataSourceAttr($query, $value)
    {
        $query->where('data_source', $value);
    }

    /**
     * 搜索器：创建者
     * @param \think\db\Query $query
     * @param int $value
     * @return void
     */
    public function searchCreatedByAttr($query, $value)
    {
        $query->where('created_by', $value);
    }

    /**
     * 关联：创建者
     * @return \think\model\relation\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联：更新者
     * @return \think\model\relation\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}