<?php

namespace app\common\model\traits;

trait HtmxPaginationTrait
{
    /**
     * HTMX分页
     * @param int $per_page 每页条数
     * @param array $config 分页配置
     * @return array
     */
    public function htmxPaginate($per_page = 15, $config = []): array
    {
        $page = request()->param('page', 1);
        $per_page = request()->param('per_page', $per_page);

        // 获取总数（使用子查询避免GROUP BY问题）
        $total = $this->count();

        // 获取分页数据
        $data = $this->page($page, $per_page)->select();

        // 计算分页信息
        $total_pages = ceil($total / $per_page);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => intval($page),
                'per_page' => intval($per_page),
                'total' => intval($total),
                'total_pages' => intval($total_pages),
                'has_prev' => $page > 1,
                'has_next' => $page < $total_pages,
                'prev_page' => max(1, $page - 1),
                'next_page' => min($total_pages, $page + 1),
                'start_record' => ($page - 1) * $per_page + 1,
                'end_record' => min($page * $per_page, $total),
            ],
            'config' => array_merge([
                'url' => request()->url(),
                'params' => request()->except(['page', 'per_page']),
                'target' => '#content',
                'show_info' => true,
                'show_jumper' => true,
                'show_per_page' => true,
            ], $config)
        ];
    }
}