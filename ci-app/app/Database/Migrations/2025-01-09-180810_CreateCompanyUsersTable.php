<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyUsersTable extends Migration
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

            'company_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'user_id'          => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
            ],
            'role'       => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
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
        $this->forge->addUniqueKey(['company_id', 'user_id']);
        $this->forge->addForeignKey(['company_id'], 'companies', ['id']);
        $this->forge->addForeignKey(['user_id'], 'users', ['id']);
        $this->forge->createTable('company_users');

    }

    public function down()
    {
        $this->forge->dropTable('company_users');

    }
}
