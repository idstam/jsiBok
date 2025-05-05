<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJournalTable extends Migration
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
            'user_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'booking_year'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'title'       => [
                'type'       => 'VARCHAR',
                'constraint' => '250',
            ],
            'details'       => [
                'type'       => 'VARCHAR',
                'constraint' => '25000',
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
        $this->forge->createTable('journal');

    }

    public function down()
    {
        $this->forge->dropTable('journal');

    }
}
