<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateTablesTable extends Migrator
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
        $table = $this->table('tables', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('title', 'string', ['limit' => 100, 'null' => false])
              ->addColumn('name', 'string', ['limit' => 50, 'null' => false])
              ->addColumn('description', 'text', ['null' => true, 'default' => null])
              ->addColumn('columns', 'text', ['null' => false])
              ->addColumn('config', 'text', ['null' => true, 'default' => null])
              ->addColumn('data_source', 'string', ['limit' => 50, 'null' => true, 'default' => 'manual'])
              ->addColumn('filters', 'text', ['null' => true, 'default' => null])
              ->addColumn('actions', 'text', ['null' => true, 'default' => null])
              ->addColumn('status', 'integer', ['limit' => 1, 'null' => false, 'default' => 1])
              ->addColumn('created_by', 'integer', ['null' => false])
              ->addColumn('updated_by', 'integer', ['null' => true, 'default' => null])
              ->addColumn('created_at', 'datetime', ['null' => false])
              ->addColumn('updated_at', 'datetime', ['null' => false])
              ->addIndex(['name'], ['unique' => true])
              ->addIndex(['created_by'])
              ->create();
    }
}
