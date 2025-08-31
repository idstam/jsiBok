<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyAccountsVatSruTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'account_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'vat' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'sru' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);
        $this->forge->addPrimaryKey(['id']);
        $this->forge->addUniqueKey(['company_id', 'account_id'], 'company_account_vat_sru_UDX');
        $this->forge->createTable('company_account_vat_sru');
    }

    public function down()
    {
        $this->forge->dropTable('company_account_vat_sru');
    }

    private function fillTable(){

    }

}
