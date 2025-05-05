<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyBookingAccountsTable extends Migration
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
            'account_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'name'       => [
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
        $this->forge->addForeignKey('company_id', 'companies', 'id');

        $this->forge->addUniqueKey(['company_id', 'account_id'], 'company_account_UDX');
        $this->forge->createTable('company_booking_accounts');

    }

    public function down()
    {
        $this->forge->dropTable('company_booking_accounts');

    }
}
