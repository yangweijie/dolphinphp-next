<?php

namespace app\model;

use think\Model;

class Form extends Model
{
    /**
     * 设置当前模型对应的完整数据表名称
     * @var string
     */
    protected $table = 'dp_forms';

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
        'fields' => 'json',
        'config' => 'json',
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
     * 表单状态常量
     */
    const STATUS_DISABLE = 0;
    const STATUS_ENABLE = 1;

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
     * 获取字段配置
     * @return array
     */
    public function getFieldsConfig()
    {
        return $this->fields ?: [];
    }

    /**
     * 获取表单配置
     * @return array
     */
    public function getFormConfig()
    {
        return $this->config ?: [];
    }

    /**
     * 设置字段配置
     * @param array $fields
     * @return $this
     */
    public function setFieldsAttr($fields)
    {
        return is_array($fields) ? json_encode($fields, JSON_UNESCAPED_UNICODE) : $fields;
    }

    /**
     * 设置表单配置
     * @param array $config
     * @return $this
     */
    public function setConfigAttr($config)
    {
        return is_array($config) ? json_encode($config, JSON_UNESCAPED_UNICODE) : $config;
    }

    /**
     * 获取字段配置
     * @param string $value
     * @return array
     */
    public function getFieldsAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 获取表单配置
     * @param string $value
     * @return array
     */
    public function getConfigAttr($value)
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