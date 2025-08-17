<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyVoucherSeriesValues extends Migration
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
            'booking_year_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'voucher_series_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'next'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'default'       => 1
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
        $this->forge->addForeignKey('booking_year_id', 'company_booking_years', 'id');
        $this->forge->addForeignKey('voucher_series_id', 'company_voucher_series', 'id');
        $this->forge->addUniqueKey(['company_id', 'booking_year_id', 'voucher_series_id'], 'company_voucher_series_values_UDX');
        $this->forge->addKey(['voucher_series_id', 'booking_year_id']);
        $this->forge->createTable('company_voucher_series_values');

    }

    public function down()
    {
        $this->forge->dropTable('company_voucher_series_values');

    }
}
