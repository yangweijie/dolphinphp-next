#!/bin/bash

# ZBuilder API 部署脚本
# 使用方法: ./deploy.sh [environment]

set -e

# 配置变量
ENVIRONMENT=${1:-production}
PROJECT_DIR="/var/www/zbuilder-api"
BACKUP_DIR="/var/backups/zbuilder-api"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo "开始部署 ZBuilder API 到 $ENVIRONMENT 环境..."

# 创建备份
echo "创建备份..."
mkdir -p $BACKUP_DIR
tar -czf "$BACKUP_DIR/backup_$TIMESTAMP.tar.gz" -C $PROJECT_DIR .

# 拉取最新代码
echo "拉取最新代码..."
cd $PROJECT_DIR
git pull origin main

# 安装/更新依赖
echo "安装/更新依赖..."
composer install --no-dev --optimize-autoloader

# 更新环境配置
echo "更新环境配置..."
cp .env.$ENVIRONMENT .env

# 运行数据库迁移
echo "运行数据库迁移..."
php think migrate:run

# 清除缓存
echo "清除缓存..."
php think cache:clear

# 重启服务
echo "重启服务..."
sudo systemctl restart php8.1-fpm
sudo systemctl reload nginx

# 运行健康检查
echo "运行健康检查..."
curl -f http://localhost/health || {
    echo "健康检查失败，回滚部署..."
    # 这里可以添加回滚逻辑
    exit 1
}

echo "部署完成！"