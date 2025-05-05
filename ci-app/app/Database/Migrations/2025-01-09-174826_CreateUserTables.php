<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTables extends Migration
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
            'name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '250',
            ],
            'email'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'salt'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'password'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'last_login'       => [
                'type'       => 'DATETIME',
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['email'], 'users_email_IDX');
        $this->forge->createTable('users');
        
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
