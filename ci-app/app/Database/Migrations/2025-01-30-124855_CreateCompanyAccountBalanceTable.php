<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyAccountBalanceTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'booking_year_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'type'       => [
                'type'       => 'VARCHAR',
                'constraint' => '25',
            ],
            'company_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'account_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'cost_center_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'null'        => true,
            ],
            'project_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'null'        => true,
            ],
            'amount'       => [
                'type'       => 'numeric(19,4)',

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
        $this->forge->addUniqueKey(['company_id', 'booking_year_id', 'account_id', 'cost_center_id', 'project_id']);
        $this->forge->addForeignKey(['company_id'], 'companies', ['id']);
        
        // $this->forge->addForeignKey(['account_id', 'company_id'], 'company_booking_accounts', ['id', 'company_id']);
        // $this->forge->addForeignKey(['cost_center_id', 'company_id'], 'company_dimensions', ['id', 'company_id']);
        // $this->forge->addForeignKey(['project_id', 'company_id'], 'company_dimensions', ['id', 'company_id']);

        $this->forge->createTable('company_account_balance');

    }

    public function down()
    {
        $this->forge->dropTable('company_account_balance');

    }

}
