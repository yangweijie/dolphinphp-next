# 数据库开发指南

## 概述

海豚PHP使用ThinkPHP的数据库迁移和种子数据功能，提供数据库版本控制和数据初始化能力。

## 迁移文件结构

```
database/
├── migrations/           # 迁移文件目录
│   ├── 20250101000000_create_users_table.php
│   ├── 20250102000000_create_posts_table.php
│   └── 20250103000000_add_index_to_posts.php
├── seeds/               # 种子数据目录
│   ├── UserSeeder.php
│   ├── PostSeeder.php
│   └── FormSeeder.php
└── schema/              # 数据库结构文件（可选）
```

## 迁移文件示例

### 创建用户表

```php
<?php
// database/migrations/20250101000000_create_users_table.php

use think\migration\Migrations;
use think\migration\db\Column;

class CreateUsersTable extends Migrations
{
    public function up()
    {
        $table = $this->table('users', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '用户表'
        ]);
        
        $table->addColumn(Column::integer('id')->setIdentity(true))
              ->addColumn(Column::string('username', 50)->setComment('用户名'))
              ->addColumn(Column::string('email', 100)->setComment('邮箱'))
              ->addColumn(Column::string('password', 255)->setComment('密码'))
              ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0-禁用，1-启用'))
              ->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'))
              ->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'))
              ->addIndex(['username'], ['unique' => true])
              ->addIndex(['email'], ['unique' => true])
              ->create();
    }
    
    public function down()
    {
        $this->table('users')->drop();
    }
}
```

### 创建文章表

```php
<?php
// database/migrations/20250102000000_create_posts_table.php

use think\migration\Migrations;
use think\migration\db\Column;

class CreatePostsTable extends Migrations
{
    public function up()
    {
        $table = $this->table('posts', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '文章表'
        ]);
        
        $table->addColumn(Column::integer('id')->setIdentity(true))
              ->addColumn(Column::string('title', 200)->setComment('标题'))
              ->addColumn(Column::text('content')->setComment('内容'))
              ->addColumn(Column::integer('user_id')->setComment('用户ID'))
              ->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0-草稿，1-发布'))
              ->addColumn(Column::integer('view_count')->setDefault(0)->setComment('浏览数'))
              ->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'))
              ->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'))
              ->addIndex(['user_id'])
              ->addIndex(['status'])
              ->addIndex(['created_at'])
              ->addForeignKey('user_id', 'users', 'id', [
                  'delete' => 'CASCADE',
                  'update' => 'CASCADE'
              ])
              ->create();
    }
    
    public function down()
    {
        $this->table('posts')->drop();
    }
}
```

### 添加索引

```php
<?php
// database/migrations/20250103000000_add_index_to_posts.php

use think\migration\Migrations;

class AddIndexToPosts extends Migrations
{
    public function up()
    {
        $table = $this->table('posts');
        
        // 添加复合索引
        $table->addIndex(['status', 'created_at'], [
            'name' => 'idx_status_created_at'
        ])->update();
        
        // 添加全文索引（MySQL 5.6+）
        $table->addIndex(['title', 'content'], [
            'type' => 'fulltext',
            'name' => 'ft_title_content'
        ])->update();
    }
    
    public function down()
    {
        $table = $this->table('posts');
        $table->removeIndex(['status', 'created_at']);
        $table->removeIndex(['title', 'content']);
    }
}
```

## 种子数据示例

### 用户种子数据

```php
<?php
// database/seeds/UserSeeder.php

use think\migration\Seed;
use think\facade\Db;

class UserSeeder extends Seed
{
    public function run(): void
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'user1',
                'email' => 'user1@example.com',
                'password' => password_hash('user1123', PASSWORD_DEFAULT),
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'user2',
                'email' => 'user2@example.com',
                'password' => password_hash('user2123', PASSWORD_DEFAULT),
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        Db::name('users')->insertAll($data);
    }
}
```

### 文章种子数据

```php
<?php
// database/seeds/PostSeeder.php

use think\migration\Seed;
use think\facade\Db;

class PostSeeder extends Seed
{
    public function run(): void
    {
        $data = [
            [
                'title' => '欢迎使用海豚PHP',
                'content' => '这是第一篇介绍海豚PHP框架的文章...',
                'user_id' => 1,
                'status' => 1,
                'view_count' => 100,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => 'ThinkPHP 6.0 新特性',
                'content' => 'ThinkPHP 6.0 带来了许多令人兴奋的新特性...',
                'user_id' => 1,
                'status' => 1,
                'view_count' => 50,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => '数据库迁移指南',
                'content' => '本文将详细介绍如何使用数据库迁移功能...',
                'user_id' => 2,
                'status' => 0, // 草稿
                'view_count' => 10,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        Db::name('posts')->insertAll($data);
    }
}
```

### 表单构建器种子数据

```php
<?php
// database/seeds/FormSeeder.php

use think\migration\Seed;
use think\facade\Db;

class FormSeeder extends Seed
{
    public function run(): void
    {
        $forms = [
            [
                'title' => '用户注册表单',
                'name' => 'user_registration',
                'description' => '用户注册信息收集表单',
                'fields' => json_encode([
                    [
                        'name' => 'username',
                        'label' => '用户名',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => '请输入用户名',
                        'rules' => ['min:3', 'max:20']
                    ],
                    [
                        'name' => 'email',
                        'label' => '邮箱',
                        'type' => 'email',
                        'required' => true,
                        'placeholder' => '请输入邮箱',
                        'rules' => ['email']
                    ],
                    [
                        'name' => 'password',
                        'label' => '密码',
                        'type' => 'password',
                        'required' => true,
                        'placeholder' => '请输入密码',
                        'rules' => ['min:6']
                    ]
                ]),
                'config' => json_encode([
                    'submitText' => '立即注册',
                    'successMessage' => '注册成功',
                    'redirectUrl' => '/login'
                ]),
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => '联系我们',
                'name' => 'contact_us',
                'description' => '网站联系我们表单',
                'fields' => json_encode([
                    [
                        'name' => 'name',
                        'label' => '姓名',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => '请输入您的姓名'
                    ],
                    [
                        'name' => 'email',
                        'label' => '邮箱',
                        'type' => 'email',
                        'required' => true,
                        'placeholder' => '请输入您的邮箱'
                    ],
                    [
                        'name' => 'subject',
                        'label' => '主题',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => '请输入主题'
                    ],
                    [
                        'name' => 'message',
                        'label' => '留言内容',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => '请输入您的留言内容',
                        'rows' => 5
                    ]
                ]),
                'config' => json_encode([
                    'submitText' => '发送消息',
                    'successMessage' => '消息发送成功，我们会尽快回复您！',
                    'emailNotification' => true
                ]),
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'title' => '产品反馈',
                'name' => 'product_feedback',
                'description' => '收集用户对产品的反馈意见',
                'fields' => json_encode([
                    [
                        'name' => 'product_name',
                        'label' => '产品名称',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => '请输入产品名称'
                    ],
                    [
                        'name' => 'rating',
                        'label' => '评分',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            ['value' => '5', 'label' => '⭐⭐⭐⭐⭐ 非常好'],
                            ['value' => '4', 'label' => '⭐⭐⭐⭐ 很好'],
                            ['value' => '3', 'label' => '⭐⭐⭐ 一般'],
                            ['value' => '2', 'label' => '⭐⭐ 较差'],
                            ['value' => '1', 'label' => '⭐ 非常差']
                        ]
                    ],
                    [
                        'name' => 'feedback_type',
                        'label' => '反馈类型',
                        'type' => 'radio',
                        'required' => true,
                        'options' => [
                            ['value' => 'bug', 'label' => 'Bug报告'],
                            ['value' => 'suggestion', 'label' => '功能建议'],
                            ['value' => 'question', 'label' => '使用问题']
                        ]
                    ],
                    [
                        'name' => 'description',
                        'label' => '详细描述',
                        'type' => 'textarea',
                        'required' => true,
                        'placeholder' => '请详细描述您的问题或建议',
                        'rows' => 6
                    ],
                    [
                        'name' => 'contact_me',
                        'label' => '希望我们联系您',
                        'type' => 'checkbox',
                        'options' => [
                            ['value' => 'yes', 'label' => '是的，请联系我']
                        ]
                    ]
                ]),
                'config' => json_encode([
                    'submitText' => '提交反馈',
                    'successMessage' => '感谢您的反馈！',
                    'autoResponder' => true
                ]),
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        Db::name('forms')->insertAll($forms);
    }
}
```

## 迁移命令

### 运行所有迁移
```bash
php think migrate:run
```

### 回滚上一次迁移
```bash
php think migrate:rollback
```

### 回滚所有迁移
```bash
php think migrate:reset
```

### 查看迁移状态
```bash
php think migrate:status
```

### 创建新的迁移文件
```bash
php think migrate:create CreateProductsTable
```

## 种子数据命令

### 运行所有种子数据
```bash
php think seed:run
```

### 运行特定种子数据
```bash
php think seed:run -s UserSeeder
php think seed:run -s PostSeeder
php think seed:run -s FormSeeder
```

### 运行多个种子数据
```bash
php think seed:run -s UserSeeder -s PostSeeder
```

### 创建新的种子文件
```bash
php think seed:create ProductSeeder
```

## 数据库配置

### 配置文件
```php
// config/database.php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'dolphinphp',
            'username' => 'root',
            'password' => '',
            'hostport' => '3306',
            'charset' => 'utf8mb4',
            'prefix' => 'dp_',
            'debug' => true,
        ],
        'test' => [
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'dolphinphp_test',
            'username' => 'root',
            'password' => '',
            'hostport' => '3306',
            'charset' => 'utf8mb4',
            'prefix' => 'dp_',
            'debug' => true,
        ],
    ],
    'migration' => [
        'table' => 'migrations',    // 迁移记录表
        'version' => 20250101000000, // 当前版本
    ],
];
```

### 多环境配置
```php
// 根据环境加载不同配置
$env = app()->env->get();

if ($env === 'production') {
    // 生产环境配置
    $config = [
        'hostname' => 'production.db.example.com',
        'database' => 'dolphinphp_prod',
        'username' => 'prod_user',
        'password' => 'prod_password',
    ];
} elseif ($env === 'testing') {
    // 测试环境配置
    $config = [
        'hostname' => '127.0.0.1',
        'database' => 'dolphinphp_test',
        'username' => 'test_user',
        'password' => 'test_password',
    ];
} else {
    // 开发环境配置
    $config = [
        'hostname' => '127.0.0.1',
        'database' => 'dolphinphp_dev',
        'username' => 'dev_user',
        'password' => 'dev_password',
    ];
}
```

## 最佳实践

### 1. 迁移文件命名
使用时间戳前缀确保执行顺序：
```
20250101000000_create_table.php
20250102000000_alter_table.php
20250103000000_add_index.php
```

### 2. 种子数据管理
- 为每个主要数据表创建独立的种子文件
- 种子数据应该包含必要的测试数据
- 避免在生产环境运行种子数据

### 3. 数据库优化
```php
// 添加合适的索引
$table->addIndex(['status', 'created_at']);

// 使用合适的数据类型
$table->addColumn(Column::string('email', 100)); // 而不是 text

// 设置默认值和注释
$table->addColumn(Column::tinyInteger('status')->setDefault(1)->setComment('状态：0-禁用，1-启用'));
```

### 4. 外键约束
```php
// 添加外键约束
$table->addForeignKey('user_id', 'users', 'id', [
    'delete' => 'CASCADE',
    'update' => 'CASCADE'
]);
```

## 常见问题

### Q: 迁移执行失败怎么办？
A: 检查错误信息，修复问题后使用 `migrate:rollback` 回滚，然后重新运行。

### Q: 如何修改已执行的迁移？
A: 创建新的迁移文件来修改表结构，不要直接修改已执行的迁移文件。

### Q: 种子数据冲突怎么办？
A: 在种子数据中使用 `insertOrUpdate` 或先检查数据是否存在。

### Q: 如何在不同环境使用不同数据？
A: 根据环境变量加载不同的数据库配置和种子数据。

### Q: 迁移文件太多如何管理？
A: 按功能模块组织迁移文件，使用有意义的文件名。

## 故障排除

### 1. 表已存在错误
```bash
# 先回滚所有迁移
php think migrate:reset

# 然后重新运行
php think migrate:run
```

### 2. 外键约束错误
确保被引用的表先创建，或者暂时禁用外键检查：
```php
// 在迁移开始前
Db::execute('SET FOREIGN_KEY_CHECKS = 0');

// 在迁移结束后
Db::execute('SET FOREIGN_KEY_CHECKS = 1');
```

### 3. 数据插入错误
检查字段类型、长度和约束是否匹配。

### 4. 性能问题
对于大量数据，使用批量插入：
```php
Db::name('table')->insertAll($data);
```

## 版本控制

### 迁移记录表
系统会自动创建 `migrations` 表来跟踪已执行的迁移：
```sql
CREATE TABLE `migrations` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### 版本回滚
```bash
# 回滚到特定版本
php think migrate:rollback -t 20250101000000

# 回滚到指定日期
php think migrate:rollback -d 20250101
```