<?php

use think\migration\Seeder;
use app\model\Form;

class FormSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'title' => '用户注册表单',
                'name' => 'user_registration',
                'fields' => json_encode([
                    ['name' => 'username', 'label' => '用户名', 'type' => 'text', 'required' => true],
                    ['name' => 'password', 'label' => '密码', 'type' => 'password', 'required' => true],
                    ['name' => 'email', 'label' => '电子邮箱', 'type' => 'email', 'required' => true],
                ]),
                'config' => json_encode(['submit_text' => '立即注册']),
                'status' => 1,
                'created_by' => 1,
            ],
            [
                'title' => '联系我们',
                'name' => 'contact_us',
                'fields' => json_encode([
                    ['name' => 'name', 'label' => '您的姓名', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => '电子邮箱', 'type' => 'email', 'required' => true],
                    ['name' => 'message', 'label' => '留言内容', 'type' => 'textarea', 'required' => true],
                ]),
                'config' => json_encode(['submit_text' => '发送留言']),
                'status' => 1,
                'created_by' => 1,
            ],
            [
                'title' => '产品反馈',
                'name' => 'product_feedback',
                'fields' => json_encode([
                    ['name' => 'product_name', 'label' => '产品名称', 'type' => 'text', 'required' => true],
                    ['name' => 'rating', 'label' => '评分', 'type' => 'select', 'options' => ['1' => '1星', '2' => '2星', '3' => '3星', '4' => '4星', '5' => '5星'], 'required' => true],
                    ['name' => 'comment', 'label' => '评论', 'type' => 'textarea'],
                ]),
                'config' => json_encode(['submit_text' => '提交反馈']),
                'status' => 0, // 禁用
                'created_by' => 1,
            ],
        ];

        $this->table('forms')->insert($data)->save();
    }
}