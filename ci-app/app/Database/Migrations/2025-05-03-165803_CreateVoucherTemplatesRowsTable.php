<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVoucherTemplatesRowsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'template_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
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
        $this->forge->addForeignKey(['company_id'], 'companies', ['id']);
        $this->forge->addForeignKey(['template_id'], 'company_voucher_templates', ['id']);
        
        // $this->forge->addForeignKey(['account_id', 'company_id'], 'company_booking_accounts', ['id', 'company_id']);
        // $this->forge->addForeignKey(['cost_center_id', 'company_id'], 'company_dimensions', ['id', 'company_id']);
        // $this->forge->addForeignKey(['project_id', 'company_id'], 'company_dimensions', ['id', 'company_id']);

        $this->forge->createTable('company_voucher_template_rows');

    }

    public function down()
    {
        $this->forge->dropTable('company_voucher_template_rows');

    }
}
