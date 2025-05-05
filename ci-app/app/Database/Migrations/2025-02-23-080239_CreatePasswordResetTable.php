<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePasswordResetTable extends Migration
{
    public function up()
    {

        $this->forge->addField([
            'email'       => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
                'null'        => false,
            ],
            'request_id'       => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
                'null'        => false,
            ],
            'requested'       => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
                'null'        => false,
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
        $this->forge->addPrimaryKey(['email']);
        $this->forge->addUniqueKey(['request_id']);


        $this->forge->createTable('password_reset');

    }

    public function down()
    {
        $this->forge->dropTable('password_reset');
    }
}
