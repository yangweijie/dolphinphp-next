<?php

namespace app\common\model;

use app\common\model\traits\HtmxPaginationTrait;
use think\Model;

class BaseModel extends Model
{
    use HtmxPaginationTrait;

    /**
     * 快速创建HTMX分页查询
     * @param array $where 查询条件
     * @param string $order 排序
     * @param int $per_page 每页条数
     * @param array $config 分页配置
     * @return array
     */
    public static function htmxList($where = [], $order = '', $per_page = 15, $config = [])
    {
        $query = static::where($where);

        if ($order) {
            $query->order($order);
        }

        return $query->htmxPaginate($per_page, $config);
    }
}