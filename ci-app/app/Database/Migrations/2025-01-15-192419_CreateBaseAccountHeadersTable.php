<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBaseAccountHeadersTable extends Migration
{
    public function up()
    {

        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'number'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'level'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'description'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'created_at'       => [
                'type'       => 'DATETIME',
                'null'        => true,
            ],
            'updated_at'       => [
                'type'       => 'DATETIME',
                'null'        => true,
            ],
            'deleted_at'       => [
                'type'       => 'DATETIME',
                'null'        => true,
            ],

        ]);
        $this->forge->addPrimaryKey(['id']);
        $this->forge->addUniqueKey(['number'], 'base_account?header_numbers_UDX');
        $this->forge->createTable('base_account_headers');
    }

    public function down()
    {
        $this->forge->dropTable('base_account_headers');
    }
}
