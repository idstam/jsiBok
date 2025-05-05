<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();

        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'number'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'org_no'       => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'owner_id'       => [
                'type'       => 'INTEGER',
                
            ],
            'owner_email'       => [
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
        
        $this->forge->addUniqueKey(['number'], 'companies_number_IDX');
        $this->forge->addUniqueKey(['id', 'org_no', 'name'], 'companies_orgno_name_IDX');
        $this->forge->createTable('companies');
    }

    public function down()
    {
        $this->forge->dropTable('companies');
    }
}
