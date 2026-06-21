---
name: create-migration
description: 创建数据库迁移文件（ThinkPHP / ThinkORM）
user_invocable: true
disable_model_invocation: true
---

# create-migration

创建新的 ThinkORM 数据库迁移文件。

## Usage

`/create-migration <migration_name>`

## Template

```php
<?php

use think\migration\Migrator;
use think\migration\db\Column;

class {{ClassName}} extends Migrator
{
    public function change()
    {
        $table = $this->table('{{table_name}}');
        // TODO: 在此添加字段定义
        // $table->addColumn('name', 'string', ['limit' => 100])
        //       ->addTimestamps()
        //       ->create();
    }
}
```
