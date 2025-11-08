<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateUsersTable extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('users', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('username', 'string', ['limit' => 50, 'null' => false])
              ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
              ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true, 'default' => null])
              ->addColumn('role', 'string', ['limit' => 20, 'null' => false, 'default' => 'user'])
              ->addColumn('status', 'integer', ['limit' => 1, 'null' => false, 'default' => 1])
              ->addColumn('last_login_time', 'datetime', ['null' => true, 'default' => null])
              ->addColumn('last_login_ip', 'string', ['limit' => 45, 'null' => true, 'default' => null])
              ->addColumn('created_at', 'datetime', ['null' => false])
              ->addColumn('updated_at', 'datetime', ['null' => false])
              ->addIndex(['username'], ['unique' => true])
              ->addIndex(['email'], ['unique' => true])
              ->create();
    }
}
