<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyValuesTable extends Migration
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
            'name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'int_value'       => [
                'type'       => 'int',
                'null'        => true,
            ],
            'amount_value'       => [
                'type'       => 'numeric(19,4)',
                'null'        => true,
            ],
            'string_value'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'        => true,
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
        $this->forge->addForeignKey('company_id', 'companies', 'id');

        $this->forge->addUniqueKey(['company_id', 'name'], 'company_values_UDX');
        $this->forge->createTable('company_values');

    }

    public function down()
    {
        $this->forge->dropTable('company_values');

    }
}
