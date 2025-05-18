<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVoucherTemplatesTable extends Migration
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
            'title'       => [
                'type'       => 'VARCHAR',
                'constraint' => '250',
            ],
            'serie'       => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'external_reference'       => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'        => true,
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
        $this->forge->addForeignKey(['company_id', 'serie'], 'company_voucher_series', ['company_id', 'name']);

        $this->forge->addUniqueKey(['company_id','title'], 'template_title_UDX');
        $this->forge->createTable('company_voucher_templates');

    }

    public function down()
    {
        $this->forge->dropTable('company_voucher_templates');

    }
}
