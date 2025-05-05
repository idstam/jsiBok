<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyDimensionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'company_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('company_id', 'companies', 'id');

        $this->forge->addUniqueKey(['company_id', 'dim_number', 'dim_code'], 'company_dimensions_UDX');
        $this->forge->createTable('company_dimensions');

    }

    public function down()
    {
        $this->forge->dropTable('company_dimensions');

    }
}
