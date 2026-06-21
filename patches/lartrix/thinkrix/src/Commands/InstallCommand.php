<?php

declare(strict_types=1);

namespace Thinkrix\Commands;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\Db;
use Thinkrix\Models\AdminUser;
use Thinkrix\Models\Menu;
use Thinkrix\Models\Permission;
use Thinkrix\Models\Role;
use Thinkrix\Models\Setting;
use Thinkrix\Models\NotificationCategory;
use think\migration\command\migrate\Run as MigrationRun;

class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('thinkrix:install')
            ->setDescription('安装 Thinkrix：发布资源、配置和数据库迁移')
            ->addOption('username', 'u', Option::VALUE_OPTIONAL, '超级管理员用户名', 'admin')
            ->addOption('password', 'p', Option::VALUE_OPTIONAL, '超级管理员密码', 'thinkrix2025')
            ->addOption('force', 'f', Option::VALUE_NONE, '强制覆盖已存在的文件');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->info('开始安装 Thinkrix...');
        $output->writeln('');

        // 1. 发布前端资源
        $output->info('1. 发布前端资源到 public/admin...');
        $this->publishAssets($output);
        $output->info('   前端资源发布完成。');

        // 2. 发布配置文件
        $output->info('2. 发布配置文件到 config...');
        $this->publishConfig($output);
        $output->info('   配置发布完成。');

        // 3. 执行数据库迁移
        $output->info('3. 执行数据库迁移...');
        if ($this->runMigrations($output)) {
            $output->info('   迁移完成。');
        } else {
            $output->writeln('<comment>   迁移未执行，将使用完整建表兜底。</comment>');
        }

        // 4. 创建基础表（如果不使用迁移）
        $output->info('4. 初始化基础数据表...');
        $this->initBaseTables($output);
        $output->info('   基础表初始化完成。');

        // 5. 创建超级管理员角色
        $output->info('5. 创建超级管理员角色...');
        $role = $this->createSuperAdminRole($output);
        $output->info('   角色创建完成。');

        // 6. 创建基础权限
        $output->info('6. 创建基础权限...');
        $this->createBasePermissions($output);
        $output->info('   权限创建完成。');

        // 7. 创建默认菜单
        $output->info('7. 创建默认菜单...');
        $this->createDefaultMenus($output);
        $output->info('   默认菜单创建完成。');

        // 8. 初始化通知分类
        $output->info('8. 初始化通知分类...');
        $this->initNotificationCategories($output);
        $output->info('   通知分类初始化完成。');

        // 9. 创建管理员账户
        $output->info('9. 创建超级管理员账户...');
        [$username, $password] = $this->resolveAdminCredentials($input, $output);
        $admin = $this->createSuperAdmin($role, $username, $password, $output);

        // 输出安装摘要
        $output->writeln('');
        $output->info('========================================');
        $output->info('       Thinkrix 安装完成！');
        $output->info('========================================');
        $output->writeln('');
        $output->writeln("管理员用户名: {$admin->username}");

        // 10. 配置 composer merge-plugin
        $output->info('10. 配置 Composer 模块依赖合并...');
        $this->setupComposerMerge($output);

        return 0;
    }

    protected function publishAssets(Output $output): void
    {
        $source = __DIR__ . '/../../../public/admin';
        $target = public_path() . 'admin';

        if (is_dir($target)) {
            $output->writeln('   前端资源目录已存在，跳过。如需强制覆盖，请手动删除目录后重试。');
            return;
        }

        $this->copyDir($source, $target);
    }

    protected function publishConfig(Output $output): void
    {
        $source = __DIR__ . '/../../../config/thinkrix.php';
        $target = config_path() . 'thinkrix.php';

        if (file_exists($target)) {
            $output->writeln('   配置文件已存在，跳过。');
            return;
        }

        copy($source, $target);
    }

    protected function setupComposerMerge(Output $output): void
    {
        $composerJsonPath = root_path() . 'composer.json';

        if (!file_exists($composerJsonPath)) {
            $output->writeln('<comment>   composer.json 不存在，跳过。</comment>');
            return;
        }

        $composer = json_decode(file_get_contents($composerJsonPath), true);

        if (isset($composer['extra']['merge-plugin'])) {
            $output->writeln('   composer merge-plugin 已配置，跳过。');
            return;
        }

        $composer['extra']['merge-plugin'] = [
            'include' => [
                'vendor/*/*/composer.json',
            ],
        ];

        file_put_contents($composerJsonPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $output->writeln('   composer merge-plugin 配置完成。');
    }

    protected function runMigrations(Output $output): bool
    {
        try {
            $migrationPath = realpath(__DIR__ . '/../../database/migrations');
            $command = new class($migrationPath) extends MigrationRun {
                public function __construct(protected string $migrationPath)
                {
                    parent::__construct();
                }
                protected function getPath(): string
                {
                    return $this->migrationPath;
                }
            };
            $command->setApp($this->app);
            $command->setConsole($this->getConsole());
            $command->run(new Input([$command->getName()]), $output);
            return true;
        } catch (\Throwable $e) {
            $output->writeln('<comment>   迁移执行失败: ' . $e->getMessage() . '</comment>');
            return false;
        }
    }

    /**
     * 判断当前数据库是否为 SQLite
     */
    protected function isSqlite(): bool
    {
        try {
            $config = $this->app->db->getConfig();
            $default = $config['default'] ?? 'mysql';
            $connection = $config['connections'][$default] ?? [];
            return ($connection['type'] ?? '') === 'sqlite';
        } catch (\Throwable $e) {
            return env('DB_DRIVER', 'mysql') === 'sqlite';
        }
    }

    protected function initBaseTables(Output $output): void
    {
        if ($this->isSqlite()) {
            $this->initBaseTablesSqlite($output);
        } else {
            $this->initBaseTablesMysql($output);
        }
    }

    protected function initBaseTablesMysql(Output $output): void
    {
        $sqls = [
            // 管理员用户表
            "CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `username` varchar(20) NOT NULL COMMENT '用户名',
                `password` varchar(255) NOT NULL COMMENT '密码',
                `nickname` varchar(20) DEFAULT NULL COMMENT '昵称',
                `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
                `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
                `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
                `status` varchar(10) DEFAULT '1' COMMENT '状态',
                `remark` varchar(255) DEFAULT NULL COMMENT '备注',
                `last_login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
                `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
                `deleted_at` datetime DEFAULT NULL COMMENT '软删除',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员用户表';",

            // 权限表
            "CREATE TABLE IF NOT EXISTS `permissions` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `parent_id` int UNSIGNED DEFAULT NULL COMMENT '父级权限ID',
                `name` varchar(255) NOT NULL COMMENT '权限标识',
                `title` varchar(255) DEFAULT NULL COMMENT '权限名称',
                `guard_name` varchar(50) NOT NULL DEFAULT 'admin' COMMENT 'guard名称',
                `module` varchar(255) DEFAULT NULL COMMENT '所属模块',
                `description` text COMMENT '描述',
                `sort` int DEFAULT '0' COMMENT '排序',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_name_guard` (`name`,`guard_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='权限表';",

            // 角色表
            "CREATE TABLE IF NOT EXISTS `roles` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL COMMENT '角色标识',
                `title` varchar(255) DEFAULT NULL COMMENT '角色名称',
                `guard_name` varchar(50) NOT NULL DEFAULT 'admin' COMMENT 'guard名称',
                `description` text COMMENT '描述',
                `status` tinyint(1) DEFAULT '1' COMMENT '状态',
                `is_system` tinyint(1) DEFAULT '0' COMMENT '是否系统内置',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_name_guard` (`name`,`guard_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';",

            // 角色-权限关联表
            "CREATE TABLE IF NOT EXISTS `role_has_permissions` (
                `role_id` int UNSIGNED NOT NULL,
                `permission_id` int UNSIGNED NOT NULL,
                PRIMARY KEY (`role_id`,`permission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色权限关联表';",

            // 模型-角色关联表
            "CREATE TABLE IF NOT EXISTS `model_has_roles` (
                `role_id` int UNSIGNED NOT NULL,
                `model_type` varchar(255) NOT NULL,
                `model_id` int UNSIGNED NOT NULL,
                PRIMARY KEY (`role_id`,`model_type`,`model_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='模型角色关联表';",

            // 菜单表
            "CREATE TABLE IF NOT EXISTS `admin_menus` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `guard_name` varchar(50) NOT NULL DEFAULT 'admin' COMMENT '所属guard',
                `parent_id` int UNSIGNED DEFAULT NULL COMMENT '父级菜单ID',
                `name` varchar(255) NOT NULL COMMENT '路由名称',
                `path` varchar(255) NOT NULL COMMENT '路由路径',
                `component` varchar(255) DEFAULT NULL COMMENT '组件路径',
                `redirect` varchar(255) DEFAULT NULL COMMENT '重定向',
                `title` varchar(255) DEFAULT NULL COMMENT '菜单标题',
                `icon` varchar(255) DEFAULT NULL COMMENT '菜单图标',
                `order` int DEFAULT '0' COMMENT '排序',
                `hide_in_menu` tinyint(1) DEFAULT '0' COMMENT '是否隐藏',
                `keep_alive` tinyint(1) DEFAULT '0' COMMENT '是否缓存',
                `permissions` text COMMENT '所需权限（JSON数组）',
                `use_json_renderer` tinyint(1) DEFAULT '0' COMMENT '使用JSON渲染',
                `schema_source` varchar(255) DEFAULT NULL COMMENT 'Schema来源',
                `layout_type` varchar(50) DEFAULT NULL COMMENT '布局类型',
                `open_type` varchar(50) DEFAULT NULL COMMENT '打开方式',
                `href` varchar(255) DEFAULT NULL COMMENT '外部链接',
                `is_default_after_login` tinyint(1) DEFAULT '0' COMMENT '登录后默认页',
                `fixed_index_in_tab` int DEFAULT NULL COMMENT '固定标签索引',
                `requires_auth` tinyint(1) DEFAULT '1' COMMENT '需要认证',
                `active_menu` varchar(255) DEFAULT NULL COMMENT '激活的菜单',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_name_guard` (`name`,`guard_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='菜单表';",

            // 设置表
            "CREATE TABLE IF NOT EXISTS `admin_settings` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `group` varchar(100) DEFAULT 'general' COMMENT '分组',
                `key` varchar(100) NOT NULL COMMENT '键名',
                `title` varchar(255) DEFAULT NULL COMMENT '标题',
                `type` varchar(50) DEFAULT 'string' COMMENT '类型',
                `value` text COMMENT '值',
                `default_value` text COMMENT '默认值',
                `description` text COMMENT '描述',
                `sort` int DEFAULT '0' COMMENT '排序',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_key` (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统设置表';",

            // 模块表
            "CREATE TABLE IF NOT EXISTS `modules` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL COMMENT '模块名称',
                `title` varchar(255) DEFAULT NULL COMMENT '模块标题',
                `description` text COMMENT '描述',
                `version` varchar(20) DEFAULT '1.0.0' COMMENT '版本',
                `author` varchar(100) DEFAULT NULL COMMENT '作者',
                `website` varchar(255) DEFAULT NULL COMMENT '网址',
                `logo` varchar(255) DEFAULT NULL COMMENT 'LOGO',
                `enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用',
                `config` text COMMENT '配置（JSON）',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='模块表';",

            // Token 表（替代 Laravel Sanctum）
            "CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `tokenable_type` varchar(255) NOT NULL,
                `tokenable_id` int UNSIGNED NOT NULL,
                `name` varchar(255) NOT NULL COMMENT 'Token名称',
                `token` varchar(64) NOT NULL COMMENT 'Token值（SHA256）',
                `abilities` text COMMENT '权限（JSON数组）',
                `last_used_at` datetime DEFAULT NULL,
                `expires_at` datetime DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_token` (`token`),
                KEY `idx_tokenable` (`tokenable_type`,`tokenable_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='访问令牌表';",

            "CREATE TABLE IF NOT EXISTS `dict_groups` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `code` varchar(50) NOT NULL COMMENT '分组编码',
                `name` varchar(100) NOT NULL COMMENT '分组名称',
                `description` varchar(255) DEFAULT NULL COMMENT '描述',
                `is_system` tinyint(1) DEFAULT '0' COMMENT '是否系统内置',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='字典分组表';",

            "CREATE TABLE IF NOT EXISTS `dict_items` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `group_id` int UNSIGNED NOT NULL COMMENT '分组ID',
                `code` varchar(50) NOT NULL COMMENT '项编码',
                `label` varchar(100) NOT NULL COMMENT '显示文本',
                `value` varchar(100) NOT NULL COMMENT '存储值',
                `sort` int DEFAULT '0' COMMENT '排序',
                `is_enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用',
                `extra` text COMMENT '额外数据（JSON）',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_group_code` (`group_id`,`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='字典项表';",

            "CREATE TABLE IF NOT EXISTS `notification_categories` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL COMMENT '分类名称',
                `key` varchar(50) NOT NULL COMMENT '分类标识',
                `icon` varchar(100) DEFAULT NULL COMMENT '图标',
                `color` varchar(50) DEFAULT NULL COMMENT '颜色',
                `sort` int DEFAULT '0' COMMENT '排序',
                `message_types` text COMMENT '消息类型（JSON数组）',
                `guard_name` varchar(50) NOT NULL DEFAULT 'admin' COMMENT '所属guard',
                `enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_key_guard` (`key`,`guard_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通知分类表';",

            "CREATE TABLE IF NOT EXISTS `notification_messages` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL COMMENT '消息标题',
                `content` text COMMENT '消息内容',
                `type` varchar(50) NOT NULL DEFAULT 'system' COMMENT '消息类型',
                `category_key` varchar(50) NOT NULL COMMENT '分类标识',
                `guard_name` varchar(50) NOT NULL DEFAULT 'admin' COMMENT '所属guard',
                `user_id` int UNSIGNED DEFAULT NULL COMMENT '接收用户ID',
                `from_user_id` int UNSIGNED DEFAULT NULL COMMENT '发送用户ID',
                `from_guard` varchar(50) DEFAULT NULL COMMENT '发送者guard',
                `target_guards` text COMMENT '目标guards（JSON数组）',
                `is_read` tinyint(1) DEFAULT '0' COMMENT '是否已读',
                `read_at` datetime DEFAULT NULL COMMENT '阅读时间',
                `extra` text COMMENT '额外数据（JSON）',
                `created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_is_read` (`is_read`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通知消息表';",
        ];

        foreach ($sqls as $sql) {
            try {
                $this->app->db->execute($sql);
            } catch (\Exception $e) {
                $output->writeln('<comment>   建表警告: ' . $e->getMessage() . '</comment>');
            }
        }
    }

    protected function initBaseTablesSqlite(Output $output): void
    {
        // 读取表前缀（ThinkORM 对 belongsToMany 的中间表会自动应用前缀）
        $prefix = '';
        try {
            $config = $this->app->db->getConfig();
            $default = $config['default'] ?? 'mysql';
            $prefix = $config['connections'][$default]['prefix'] ?? '';
        } catch (\Throwable $e) {
            // 忽略
        }
        $pivot = fn(string $name) => $prefix . $name;

        $sqls = [
            // 管理员用户表
            "CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `username` TEXT NOT NULL,
                `password` TEXT NOT NULL,
                `nickname` TEXT,
                `avatar` TEXT,
                `email` TEXT,
                `phone` TEXT,
                `status` TEXT DEFAULT '1',
                `remark` TEXT,
                `last_login_ip` TEXT,
                `last_login_time` TEXT,
                `deleted_at` TEXT,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`username`)
            );",

            // 权限表
            "CREATE TABLE IF NOT EXISTS `permissions` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `parent_id` INTEGER,
                `name` TEXT NOT NULL,
                `title` TEXT,
                `guard_name` TEXT NOT NULL DEFAULT 'admin',
                `module` TEXT,
                `description` TEXT,
                `sort` INTEGER DEFAULT 0,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`name`, `guard_name`)
            );",

            // 角色表
            "CREATE TABLE IF NOT EXISTS `roles` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `name` TEXT NOT NULL,
                `title` TEXT,
                `guard_name` TEXT NOT NULL DEFAULT 'admin',
                `description` TEXT,
                `status` INTEGER DEFAULT 1,
                `is_system` INTEGER DEFAULT 0,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`name`, `guard_name`)
            );",

            // 角色-权限关联表（ThinkORM 会给 belongsToMany 中间表加前缀）
            "CREATE TABLE IF NOT EXISTS `{$pivot('role_has_permissions')}` (
                `role_id` INTEGER NOT NULL,
                `permission_id` INTEGER NOT NULL,
                PRIMARY KEY (`role_id`, `permission_id`)
            );",

            // 模型-角色关联表（ThinkORM 会给 belongsToMany 中间表加前缀）
            // model_type 设为可空：attach 时不会自动填充 wherePivot 条件值
            "CREATE TABLE IF NOT EXISTS `{$pivot('model_has_roles')}` (
                `role_id` INTEGER NOT NULL,
                `model_type` TEXT,
                `model_id` INTEGER NOT NULL,
                PRIMARY KEY (`role_id`, `model_type`, `model_id`)
            );",

            // 菜单表
            "CREATE TABLE IF NOT EXISTS `admin_menus` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `guard_name` TEXT NOT NULL DEFAULT 'admin',
                `parent_id` INTEGER,
                `name` TEXT NOT NULL,
                `path` TEXT NOT NULL,
                `component` TEXT,
                `redirect` TEXT,
                `title` TEXT,
                `icon` TEXT,
                `order` INTEGER DEFAULT 0,
                `hide_in_menu` INTEGER DEFAULT 0,
                `keep_alive` INTEGER DEFAULT 0,
                `permissions` TEXT,
                `use_json_renderer` INTEGER DEFAULT 0,
                `schema_source` TEXT,
                `layout_type` TEXT,
                `open_type` TEXT,
                `href` TEXT,
                `is_default_after_login` INTEGER DEFAULT 0,
                `fixed_index_in_tab` INTEGER,
                `requires_auth` INTEGER DEFAULT 1,
                `active_menu` TEXT,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`name`, `guard_name`)
            );",

            // 设置表
            "CREATE TABLE IF NOT EXISTS `admin_settings` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `group` TEXT DEFAULT 'general',
                `key` TEXT NOT NULL,
                `title` TEXT,
                `type` TEXT DEFAULT 'string',
                `value` TEXT,
                `default_value` TEXT,
                `description` TEXT,
                `sort` INTEGER DEFAULT 0,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`key`)
            );",

            // 模块表
            "CREATE TABLE IF NOT EXISTS `modules` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `name` TEXT NOT NULL,
                `title` TEXT,
                `description` TEXT,
                `version` TEXT DEFAULT '1.0.0',
                `author` TEXT,
                `website` TEXT,
                `logo` TEXT,
                `enabled` INTEGER DEFAULT 1,
                `config` TEXT,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`name`)
            );",

            // Token 表（app('db')->name('personal_access_tokens') 会应用前缀）
            "CREATE TABLE IF NOT EXISTS `{$pivot('personal_access_tokens')}` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `tokenable_type` TEXT NOT NULL,
                `tokenable_id` INTEGER NOT NULL,
                `name` TEXT NOT NULL,
                `token` TEXT NOT NULL,
                `abilities` TEXT,
                `last_used_at` TEXT,
                `expires_at` TEXT,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`token`)
            );
            CREATE INDEX IF NOT EXISTS `{$pivot('idx_tokenable')}` ON `{$pivot('personal_access_tokens')}` (`tokenable_type`, `tokenable_id`);",

            // 字典分组表
            "CREATE TABLE IF NOT EXISTS `dict_groups` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `code` TEXT NOT NULL,
                `name` TEXT NOT NULL,
                `description` TEXT,
                `is_system` INTEGER DEFAULT 0,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`code`)
            );",

            // 字典项表
            "CREATE TABLE IF NOT EXISTS `dict_items` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `group_id` INTEGER NOT NULL,
                `code` TEXT NOT NULL,
                `label` TEXT NOT NULL,
                `value` TEXT NOT NULL,
                `sort` INTEGER DEFAULT 0,
                `is_enabled` INTEGER DEFAULT 1,
                `extra` TEXT,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`group_id`, `code`)
            );",

            // 通知分类表
            "CREATE TABLE IF NOT EXISTS `notification_categories` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `name` TEXT NOT NULL,
                `key` TEXT NOT NULL,
                `icon` TEXT,
                `color` TEXT,
                `sort` INTEGER DEFAULT 0,
                `message_types` TEXT,
                `guard_name` TEXT NOT NULL DEFAULT 'admin',
                `enabled` INTEGER DEFAULT 1,
                `created_at` TEXT,
                `updated_at` TEXT,
                UNIQUE (`key`, `guard_name`)
            );",

            // 通知消息表
            "CREATE TABLE IF NOT EXISTS `notification_messages` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `title` TEXT NOT NULL,
                `content` TEXT,
                `type` TEXT NOT NULL DEFAULT 'system',
                `category_key` TEXT NOT NULL,
                `guard_name` TEXT NOT NULL DEFAULT 'admin',
                `user_id` INTEGER,
                `from_user_id` INTEGER,
                `from_guard` TEXT,
                `target_guards` TEXT,
                `is_read` INTEGER DEFAULT 0,
                `read_at` TEXT,
                `extra` TEXT,
                `created_at` TEXT,
                `updated_at` TEXT
            );
            CREATE INDEX IF NOT EXISTS `idx_user_id` ON `notification_messages` (`user_id`);
            CREATE INDEX IF NOT EXISTS `idx_is_read` ON `notification_messages` (`is_read`);
            CREATE INDEX IF NOT EXISTS `idx_created_at` ON `notification_messages` (`created_at`);",
        ];

        foreach ($sqls as $sql) {
            try {
                // 按分号分割多条 SQL 语句执行（SQLite 不支持单次执行多条）
                $statements = explode(';', $sql);
                foreach ($statements as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt !== '') {
                        $this->app->db->execute($stmt . ';');
                    }
                }
            } catch (\Exception $e) {
                $output->writeln('<comment>   建表警告: ' . $e->getMessage() . '</comment>');
            }
        }
    }

    protected function createSuperAdminRole(Output $output): Role
    {
        $roleName = config('thinkrix.super_admin_role', 'super-admin');
        $existing = Role::where('name', $roleName)->where('guard_name', 'admin')->find();
        if ($existing) {
            $output->writeln('   超级管理员角色已存在，跳过。');
            return $existing;
        }
        return Role::create([
            'name' => $roleName, 'guard_name' => 'admin',
            'title' => '超级管理员', 'description' => '拥有所有权限的超级管理员',
            'status' => true, 'is_system' => true,
        ]);
    }

    protected function createBasePermissions(Output $output): void
    {
        $role = Role::where('name', 'super-admin')->where('guard_name', 'admin')->find();
        if (!$role) {
            return;
        }

        $permissionDefs = [
            // 仪表盘
            ['name' => 'dashboard', 'title' => '仪表盘', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'dashboard.view', 'title' => '查看仪表盘', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'dashboard'],

            // 系统管理
            ['name' => 'system', 'title' => '系统管理', 'guard_name' => 'admin', 'module' => 'system'],
            // 系统设置（路由权限标识：system.setting.*）
            ['name' => 'system.setting', 'title' => '系统设置', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system'],
            ['name' => 'system.setting.list', 'title' => '查看系统设置', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.setting'],
            ['name' => 'system.setting.update', 'title' => '编辑系统设置', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.setting'],

            // 管理员管理（路由权限标识：system.user.*）
            ['name' => 'system.user', 'title' => '管理员管理', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'system.user.list', 'title' => '管理员列表', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.user'],
            ['name' => 'system.user.create', 'title' => '创建管理员', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.user'],
            ['name' => 'system.user.update', 'title' => '编辑管理员', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.user'],
            ['name' => 'system.user.delete', 'title' => '删除管理员', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.user'],

            // 角色管理（路由权限标识：system.role.*）
            ['name' => 'system.role', 'title' => '角色管理', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'system.role.list', 'title' => '角色列表', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.role'],
            ['name' => 'system.role.create', 'title' => '创建角色', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.role'],
            ['name' => 'system.role.update', 'title' => '编辑角色', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.role'],
            ['name' => 'system.role.delete', 'title' => '删除角色', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.role'],

            // 权限管理（路由权限标识：system.permission.*）
            ['name' => 'system.permission', 'title' => '权限管理', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'system.permission.list', 'title' => '权限列表', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.permission'],
            ['name' => 'system.permission.create', 'title' => '创建权限', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.permission'],
            ['name' => 'system.permission.update', 'title' => '编辑权限', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.permission'],
            ['name' => 'system.permission.delete', 'title' => '删除权限', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.permission'],

            // 菜单管理（路由权限标识：system.menu.*）
            ['name' => 'system.menu', 'title' => '菜单管理', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'system.menu.list', 'title' => '菜单列表', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.menu'],
            ['name' => 'system.menu.create', 'title' => '创建菜单', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.menu'],
            ['name' => 'system.menu.update', 'title' => '编辑菜单', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.menu'],
            ['name' => 'system.menu.delete', 'title' => '删除菜单', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.menu'],
            ['name' => 'system.menu.sort', 'title' => '菜单排序', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.menu'],

            // 字典管理（路由权限标识：system.dict.*）
            ['name' => 'system.dict', 'title' => '字典管理', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'system.dict.list', 'title' => '字典列表', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.create', 'title' => '创建字典分组', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.update', 'title' => '编辑字典分组', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.delete', 'title' => '删除字典分组', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.item.list', 'title' => '字典项列表', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.item.create', 'title' => '创建字典项', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.item.update', 'title' => '编辑字典项', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],
            ['name' => 'system.dict.item.delete', 'title' => '删除字典项', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.dict'],

            // 通知管理（路由权限标识：system.setting.update）
            ['name' => 'system.notification', 'title' => '通知管理', 'guard_name' => 'admin', 'module' => 'system'],
            ['name' => 'system.setting.update', 'title' => '发送通知', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.notification'],
            ['name' => 'system.setting.update', 'title' => '删除通知', 'guard_name' => 'admin', 'module' => 'system', 'parent' => 'system.notification'],
        ];

        try {
            $this->createPermissionsRecursive($permissionDefs);
        } catch (\Exception $e) {
            $output->writeln('<comment>   权限创建警告: ' . $e->getMessage() . '</comment>');
        }

        if ($role) {
            $permissionIds = Permission::where('guard_name', 'admin')->column('id');
            $role->permissions()->sync($permissionIds);
        }
    }

    protected function createPermissionsRecursive(array $permissions, ?int $parentId = null, ?string $module = null): void
    {
        foreach ($permissions as $perm) {
            $parent = $perm['parent'] ?? null;

            $data = [
                'name' => $perm['name'],
                'title' => $perm['title'],
                'guard_name' => $perm['guard_name'] ?? 'admin',
                'module' => $perm['module'] ?? $module ?? 'system',
                'parent_id' => $parentId,
            ];

            if ($parent && !$parentId) {
                $parentPerm = Permission::where('name', $parent)->where('guard_name', $perm['guard_name'] ?? 'admin')->find();
                if ($parentPerm) {
                    $data['parent_id'] = $parentPerm->id;
                }
            }

            try {
                Permission::create($data);
            } catch (\Exception $e) {
                // 权限可能已存在，跳过
            }
        }
    }

    protected function createDefaultMenus(Output $output): void
    {
        // 注意：schema_source 不要包含 apiPrefix，前端会自动拼接
        // 例如 apiPrefix=/api/admin，schema_source=/roles → 请求 /api/admin/roles

        // 仪表盘
        $dashboard = $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'dashboard',
            'path' => '/dashboard',
            'component' => null,
            'title' => '仪表盘',
            'icon' => 'OxygenIcons:OxygenIconsDashboard',
            'order' => 0,
            'use_json_renderer' => true,
            'schema_source' => '/dashboard',
            'is_default_after_login' => true,
        ]);

        // 系统管理（父级分组菜单，path 留空避免前端路径合并）
        $system = $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system',
            'path' => '',
            'component' => null,
            'redirect' => '/system/admin-users',
            'title' => '系统管理',
            'icon' => 'OxygenIcons:AdminSettings',
            'order' => 999,
        ]);

        $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system.admin-users',
            'path' => '/system/admin-users',
            'component' => null,
            'title' => '管理员管理',
            'use_json_renderer' => true,
            'schema_source' => '/users?action_type=list_ui',
            'parent_id' => $system?->id,
        ], $system?->id);

        $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system.roles',
            'path' => '/system/roles',
            'component' => null,
            'title' => '角色管理',
            'use_json_renderer' => true,
            'schema_source' => '/roles?action_type=list_ui',
            'parent_id' => $system?->id,
        ], $system?->id);

        $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system.permissions',
            'path' => '/system/permissions',
            'component' => null,
            'title' => '权限管理',
            'use_json_renderer' => true,
            'schema_source' => '/permissions?action_type=list_ui',
            'parent_id' => $system?->id,
        ], $system?->id);

        $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system.menus',
            'path' => '/system/menus',
            'component' => null,
            'title' => '菜单管理',
            'use_json_renderer' => true,
            'schema_source' => '/menus?action_type=list_ui',
            'parent_id' => $system?->id,
        ], $system?->id);

        $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system.settings',
            'path' => '/system/settings',
            'component' => null,
            'title' => '系统设置',
            'use_json_renderer' => true,
            'schema_source' => '/settings?action_type=form_ui',
            'parent_id' => $system?->id,
        ], $system?->id);

        $this->createMenu([
            'guard_name' => 'admin',
            'name' => 'system.dict',
            'path' => '/system/dict',
            'component' => null,
            'title' => '字典管理',
            'use_json_renderer' => true,
            'schema_source' => '/dicts/groups?action_type=list_ui',
            'parent_id' => $system?->id,
        ], $system?->id);
    }

    protected function createMenu(array $data, ?int $parentId = null): ?Menu
    {
        if ($parentId !== null) {
            $data['parent_id'] = $parentId;
        }
        try {
            return Menu::create($data);
        } catch (\Exception $e) {
            $this->output?->writeln('<comment>   菜单创建警告: ' . $e->getMessage() . '</comment>');
            return null;
        }
    }

    protected function initNotificationCategories(Output $output): void
    {
        $categories = [
            ['name' => '系统通知', 'key' => 'system', 'icon' => 'clarity:notification-solid', 'color' => '#1890ff', 'guard_name' => 'admin'],
            ['name' => '待办事项', 'key' => 'todo', 'icon' => 'clarity:notification-solid', 'color' => '#52c41a', 'guard_name' => 'admin'],
            ['name' => '审批通知', 'key' => 'approval', 'icon' => 'clarity:notification-solid', 'color' => '#faad14', 'guard_name' => 'admin'],
            ['name' => '告警通知', 'key' => 'alert', 'icon' => 'clarity:notification-solid', 'color' => '#ff4d4f', 'guard_name' => 'admin'],
        ];

        foreach ($categories as $category) {
            try {
                NotificationCategory::create($category);
            } catch (\Exception $e) {
                // 分类可能已存在，跳过
            }
        }
    }

    protected function resolveAdminCredentials(Input $input, Output $output): array
    {
        $username = $input->getOption('username') ?: 'admin';
        $password = $input->getOption('password') ?: 'thinkrix2025';

        return [$username, $password];
    }

    protected function createSuperAdmin(Role $role, string $username, string $password, Output $output): AdminUser
    {
        $existing = AdminUser::where('username', $username)->find();
        if ($existing) {
            $output->writeln("   管理员账户 '{$username}' 已存在，跳过创建。");
            return $existing;
        }

        $admin = AdminUser::create([
            'username' => $username,
            // 密码不手动哈希——AdminUser 模型的 setPasswordAttr 修改器会自动处理
            'password' => $password,
            'nickname' => '超级管理员',
            'status' => '1',
        ]);

        // save() 的第二个参数是中间表额外数据
        // 必须传入 model_type，否则 wherePivot 查询匹配不上
        $admin->roles()->save($role, ['model_type' => \Thinkrix\Models\AdminUser::class]);

        echo "   管理员密码: {$password}\n";

        return $admin;
    }

    protected function copyDir(string $source, string $dest): void
    {
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $dest . '/' . $iterator->getSubPathname();

            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }
}
