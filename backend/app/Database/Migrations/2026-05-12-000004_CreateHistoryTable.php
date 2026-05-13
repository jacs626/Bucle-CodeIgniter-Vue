<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoryTable extends Migration
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
            'block_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'date' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'note' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'block_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'block_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'entity_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'entity_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'executed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
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
        $this->forge->addForeignKey('block_id', 'blocks', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('history', true);
    }

    public function down()
    {
        $this->forge->dropTable('history', true);
    }
}