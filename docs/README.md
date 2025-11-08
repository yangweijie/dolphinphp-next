# 海豚PHP (DolphinPHP) 开发文档

## 概述

海豚PHP是一个基于ThinkPHP 8.x开发的现代化PHP快速开发框架，秉承极简、极速、极致的开发理念，为开发集成了基于数据-角色的权限管理机制，集成多种灵活快速构建工具。

## 特性

- 🚀 **极速开发**: 基于ThinkPHP 8.x，性能卓越
- 🎨 **现代化UI**: 集成TailwindCSS、HTMX、Alpine.js
- 📋 **快速构建器**: 强大的ZBuilder表单和表格构建工具
- 🔐 **权限控制**: 基于RBAC的细粒度权限管理系统
- 📱 **响应式设计**: 支持PC、移动设备和微信界面
- 🛠️ **模块化**: 可方便快速扩展的模块、插件、钩子系统

## 目录结构

```
dolphinphp-next/
├── app/                 # 应用核心目录
│   ├── controller/     # 控制器
│   ├── model/          # 数据模型
│   ├── service/        # 服务层
│   └── yoyo/           # Yoyo组件系统
├── config/              # 配置文件
├── database/           # 数据库相关
├── public/             # 公共资源
│   └── static/pages/   # 静态页面模板
├── view/               # 前端资源
└── docs/               # 文档目录
```

## 快速开始

### 环境要求

- PHP 8.1+
- MySQL 8.0+ 或 SQLite
- Composer
- Node.js 16+

### 安装步骤

1. 克隆项目
```bash
git clone <your-repo-url>
cd dolphinphp-next
```

2. 安装依赖
```bash
composer install
npm install
```

3. 配置环境
```bash
cp .example.env .env
# 编辑.env文件配置数据库连接
```

4. 运行应用
```bash
php think run
# 访问 http://localhost:8000
```

## 核心功能

### 1. 表单构建器 (FormBuilder)

提供强大的动态表单生成能力，支持多种字段类型和验证规则。

### 2. 表格构建器 (TableBuilder)

支持数据表格的快速构建，包含分页、排序、筛选等功能。

### 3. 权限控制系统

基于角色的访问控制(RBAC)，支持细粒度的权限管理。

### 4. Yoyo组件系统

现代化的组件化开发模式，支持实时交互。

## 开发指南

详细开发指南请查看各子文档：

- [表单开发指南](./form-guide.md)
- [表格开发指南](./table-guide.md) 
- [权限系统指南](./permission-guide.md)
- [API开发指南](./api-guide.md)
- [前端开发指南](./frontend-guide.md)

## 贡献指南

欢迎提交Issue和Pull Request来帮助改进海豚PHP。

## 许可证

MIT License