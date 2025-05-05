<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBaseDimensionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'dim_number'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'dim_code'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'title'       => [
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
        $this->forge->addUniqueKey(['dim_number', 'dim_code'], 'base_dim_numbers_UDX');
        $this->forge->createTable('base_dimensions');

    }

    public function down()
    {
        $this->forge->dropTable('base_dimensions');

    }
}
