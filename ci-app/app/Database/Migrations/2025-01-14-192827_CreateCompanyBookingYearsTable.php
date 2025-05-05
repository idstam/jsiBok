<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyBookingYearsTable extends Migration
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
            'company_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'year_start'       => [
                'type'       => 'DATETIME',
                'null'        => true,
            ],
            'year_end'       => [
                'type'       => 'DATETIME',
                'null'        => true,
            ],
            'active'       => [
                'type'       => 'int',
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
        $this->forge->addPrimaryKey('id');

        $this->forge->addUniqueKey(['company_id', 'year_start'], 'company_years_UDX');
        $this->forge->addForeignKey('company_id', 'companies', 'id');

        $this->forge->createTable('company_booking_years');
        

       

    }

    public function down()
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('company_booking_years');
    }
}
