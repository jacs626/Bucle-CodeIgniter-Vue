<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlocksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'entity_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'data' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'schedule' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'parent_block_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'order_index' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'   => 0,
            ],
            'is_active' => [
                'type'       => 'BOOLEAN',
                'default'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('entity_id', 'entities', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('parent_block_id', 'blocks', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('blocks', true);
    }

    public function down()
    {
        $this->forge->dropTable('blocks', true);
    }
}