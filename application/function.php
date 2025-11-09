<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2019 广东卓锐软件有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------

// 为方便系统核心升级，二次开发中需要用到的公共函数请写在这个文件，不要去修改common.php文件

// 全局函数：渲染用户操作按钮
use app\components\pagination\HtmxPaginator;

if (!function_exists('render_user_actions')) {
    function render_user_actions($value, $row)
    {
        $edit_url = url('test/edit', ['id' => $row['id']]);
        $delete_url = url('test/delete', ['id' => $row['id']]);

        $buttons = [];
        $buttons[] = "<a href=\"{$edit_url}\" class=\"btn btn-sm btn-primary\">编辑</a>";

        if ($row['id'] != 1) { // 管理员不能删除
            $buttons[] = "<button class=\"btn btn-sm btn-danger\" 
                                  hx-delete=\"{$delete_url}\"
                                  hx-target=\"#user-table-container\"
                                  hx-confirm=\"确定要删除用户 {$row['username']} 吗？\">删除</button>";
        }

        return implode(' ', $buttons);
    }
}

if (!function_exists('htmx_paginate')) {
    /**
     * 创建HTMX分页器
     * @param array $config 分页配置
     * @return string
     */
    function htmx_paginate($config = [])
    {
        $paginator = new HtmxPaginator($config);
        return $paginator->render();
    }
}