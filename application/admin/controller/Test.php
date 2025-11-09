<?php

namespace app\admin\controller;

class Test
{
    /**
     * 测试首页 - 综合展示所有组件
     */
    public function index()
    {
        // 模拟用户数据
        $users = [
            ['id' => 1, 'username' => 'admin', 'email' => 'admin@test.com', 'status' => 1, 'is_admin' => 1, 'avatar' => '/uploads/avatar1.jpg', 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'username' => 'user1', 'email' => 'user1@test.com', 'status' => 1, 'is_admin' => 0, 'avatar' => '/uploads/avatar2.jpg', 'created_at' => '2024-01-02 11:00:00'],
            ['id' => 3, 'username' => 'user2', 'email' => 'user2@test.com', 'status' => 0, 'is_admin' => 0, 'avatar' => '', 'created_at' => '2024-01-03 12:00:00'],
        ];

        // 分页配置
        $pagination_config = [
            'current_page' => input('page', 1),
            'per_page' => input('per_page', 15),
            'total' => 100,
            'url' => url('test/index'),
            'params' => input(),
            'target' => '#user-table-container',
            'show_info' => true,
            'show_jumper' => true,
            'show_per_page' => true,
            'per_page_options' => [10, 15, 20, 30, 50],
            'theme' => 'default'
        ];

        // 表格列配置
        $table_columns = [
            ['field' => 'id', 'title' => 'ID', 'type' => 'text'],
            ['field' => 'username', 'title' => '用户名', 'type' => 'text'],
            ['field' => 'avatar', 'title' => '头像', 'type' => 'image', 'config' => ['width' => 50, 'height' => 50]],
            ['field' => 'email', 'title' => '邮箱', 'type' => 'text'],
            ['field' => 'status', 'title' => '状态', 'type' => 'status', 'config' => [
                'status_map' => ['0' => '禁用', '1' => '启用'],
                'color_map' => ['0' => 'danger', '1' => 'success']
            ]],
            ['field' => 'is_admin', 'title' => '管理员', 'type' => 'switch'],
            ['field' => 'created_at', 'title' => '创建时间', 'type' => 'datetime'],
            ['field' => 'id', 'title' => '操作', 'type' => 'callback', 'config' => ['callback' => 'render_user_actions']]
        ];

        // 表单选项数据
        $roles = [
            '1' => '管理员',
            '2' => '编辑',
            '3' => '普通用户'
        ];

        $permissions = [
            'user.view' => '查看用户',
            'user.add' => '添加用户',
            'user.edit' => '编辑用户',
            'user.delete' => '删除用户',
            'article.view' => '查看文章',
            'article.add' => '添加文章'
        ];

        // 侧边栏配置
        $aside_config = [
            'title' => '操作面板',
            'position' => 'right',
            'width' => '300px',
            'theme' => 'light',
            'resizable' => true
        ];

        // 如果是HTMX请求，只返回表格部分
        if (request()->header('HX-Request')) {
            return view('test/table_partial', [
                'users' => $users,
                'table_columns' => $table_columns,
                'pagination_config' => $pagination_config
            ])->getContent();
        }

        return view('test/index', [
            'users' => $users,
            'table_columns' => $table_columns,
            'pagination_config' => $pagination_config,
            'roles' => $roles,
            'permissions' => $permissions,
            'aside_config' => $aside_config
        ]);
    }

    /**
     * 测试表单提交
     */
    public function save()
    {
        $data = input('post.');

        // 模拟保存逻辑
        if (empty($data['username'])) {
            return json(['code' => 0, 'msg' => '用户名不能为空']);
        }

        // 成功响应
        return json(['code' => 1, 'msg' => '保存成功', 'data' => $data]);
    }

    /**
     * 测试删除用户
     */
    public function delete()
    {
        $id = input('id');

        // 模拟删除逻辑
        sleep(1); // 模拟处理时间

        if ($id == 1) {
            return json(['code' => 0, 'msg' => '管理员账户不能删除']);
        }

        return json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * 测试YoYo组件渲染
     */
    public function yoyo_test()
    {
        return view('test/yoyo_components');
    }
}