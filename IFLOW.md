# IFLOW.md - 项目上下文说明

## 1. 项目概述

这是一个基于 ThinkPHP 8.0+ 构建的 PHP 项目，旨在提供一个现代化的 Web 应用框架。项目遵循 PSR 规范，并集成了 ORM、文件系统、缓存等常用组件。

项目结构清晰，采用 MVC 模式，包含控制器（Controller）、模型（Model）、服务（Service）等核心组件。此外，项目还集成了 Yoyo 前端组件库，用于构建动态 UI。

## 2. 核心技术栈

- **后端框架**: ThinkPHP 8.0+
- **PHP 版本**: >= 8.0.0
- **ORM**: think-orm 3.0+
- **前端组件**: Yoyo (clickfwd/yoyo)
- **缓存**: Redis (predis/predis)
- **JWT**: firebase/php-jwt
- **UUID**: ramsey/uuid
- **数据库迁移**: think-migration

## 3. 项目结构

```
dolphinphp-next/
├── app/                 # 应用目录
│   ├── controller/      # 控制器
│   ├── model/           # 模型
│   ├── service/         # 服务层
│   └── yoyo/            # Yoyo 组件
├── config/              # 配置文件
├── database/            # 数据库文件
│   ├── migrations/      # 迁移文件
│   └── seeds/           # 种子文件
├── public/              # 公共资源目录
├── route/               # 路由定义
├── runtime/             # 运行时目录
├── tests/               # 测试文件
├── vendor/              # Composer 依赖
└── view/                # 视图文件
```

## 4. 核心功能模块

- **用户管理**: 提供用户注册、登录、权限控制、状态管理等功能
- **表单构建器**: 动态创建和管理表单
- **表格构建器**: 动态生成和展示数据表格
- **Yoyo UI**: 集成 Yoyo 前端组件，实现动态交互

## 5. 开发与部署

### 5.1 环境要求

- PHP >= 8.0.0
- Composer
- Redis (可选，用于缓存)

### 5.2 安装依赖

```bash
composer install
```

### 5.3 运行项目

```bash
php think run
```

项目默认运行在 `http://localhost:8000`。

### 5.4 数据库迁移

```bash
php think migrate:run
```

### 5.5 代码规范

- 遵循 PSR-2 命名规范和 PSR-4 自动加载规范
- 使用严格类型声明 (`declare(strict_types=1)`)
- 控制器继承 `BaseController`
- 服务层封装业务逻辑
- 模型层处理数据交互

## 6. 路由说明

项目路由定义在 `route/app.php` 文件中，主要包括：

- `/` - 首页
- `/user/*` - 用户相关接口
- `/form/*` - 表单构建器接口
- `/table/*` - 表格构建器接口
- `/ui/yoyo` - Yoyo 组件接口

## 7. 配置文件

主要配置文件位于 `config/` 目录下，包括：

- `app.php` - 应用配置
- `database.php` - 数据库配置
- `cache.php` - 缓存配置
- `route.php` - 路由配置

## 8. 测试

项目使用 PHPUnit 进行单元测试，测试文件位于 `tests/` 目录下。

```bash
php vendor/bin/phpunit
```