#!/bin/bash

# ZBuilder API 回滚脚本
# 使用方法: ./rollback.sh [timestamp]

set -e

# 配置变量
PROJECT_DIR="/var/www/zbuilder-api"
BACKUP_DIR="/var/backups/zbuilder-api"
TIMESTAMP=${1}

if [ -z "$TIMESTAMP" ]; then
    echo "请提供要回滚到的备份时间戳"
    echo "可用的备份:"
    ls -1 $BACKUP_DIR/ | grep backup_ | sed 's/backup_//' | sed 's/.tar.gz//'
    exit 1
fi

BACKUP_FILE="$BACKUP_DIR/backup_$TIMESTAMP.tar.gz"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "备份文件不存在: $BACKUP_FILE"
    exit 1
fi

echo "开始回滚到 $TIMESTAMP..."

# 停止服务
echo "停止服务..."
sudo systemctl stop php8.1-fpm
sudo systemctl stop nginx

# 创建当前状态的备份
echo "创建当前状态的备份..."
CURRENT_BACKUP="$BACKUP_DIR/rollback_backup_$(date +"%Y%m%d_%H%M%S").tar.gz"
tar -czf $CURRENT_BACKUP -C $PROJECT_DIR .

# 恢复备份
echo "恢复备份..."
cd /
tar -xzf $BACKUP_FILE

# 重启服务
echo "重启服务..."
sudo systemctl start php8.1-fpm
sudo systemctl start nginx

# 运行健康检查
echo "运行健康检查..."
curl -f http://localhost/health || {
    echo "健康检查失败，可能需要手动修复"
    exit 1
}

echo "回滚完成！"