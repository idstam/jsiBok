<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyVoucherSeriesTable extends Migration
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
            'name'       => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'title'       => [
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
        $this->forge->addForeignKey('company_id', 'companies', 'id');

        $this->forge->addUniqueKey(['company_id', 'name'], 'company_voucher_series_UDX');
        $this->forge->createTable('company_voucher_series');

    }

    public function down()
    {
        $this->forge->dropTable('company_voucher_series');

    }
}
