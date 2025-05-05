<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyVouchersTable extends Migration
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
            'voucher_date'       => [
                'type'       => 'DATETIME',
                'null'        => false,
            ],  
            'booking_year_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'title'       => [
                'type'       => 'VARCHAR',
                'constraint' => '250',
            ],
            'serie'       => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'voucher_number'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],            
            'external_reference'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'        => true,
            ],
            'source'       => [
                'type'       => 'VARCHAR',
                'constraint' => '250',
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
        $this->forge->addForeignKey(['user_id'], 'users', ['id']);
        $this->forge->addForeignKey(['company_id', 'serie'], 'company_voucher_series', ['company_id', 'name']);

        $this->forge->addUniqueKey(['company_id','serie', 'voucher_number'], 'voucher_number_UDX');
        $this->forge->createTable('company_vouchers');

    }

    public function down()
    {
        $this->forge->dropTable('company_vouchers');

    }
}
