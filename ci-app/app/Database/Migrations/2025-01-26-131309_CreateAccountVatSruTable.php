<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Migration;

class CreateAccountVatSruTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'calendar_year'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'account_id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
            ],
            'vat'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'null'        => true,
            ],
            'sru'          => [
                'type'           => 'INT',
                'unsigned'       => true,
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
        $this->forge->addUniqueKey(['calendar_year', 'account_id'], 'account_vat_sru_UDX');
        $this->forge->createTable('base_account_vat_sru');
        $this->fillTable();
    }

    public function down()
    {
        $this->forge->dropTable('base_account_vat_sru');
    }

    private function fillTable()
    {

        $db      = \Config\Database::connect();
        try {
            $db->transException(true)->transStart();

            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2610,10,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2611,10,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2612,10,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2613,10,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2614,30,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2615,60,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2616,10,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2620,11,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2621,11,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2622,11,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2623,11,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2624,31,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2625,61,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2626,11,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2630,12,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2631,12,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2632,12,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2633,12,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2634,32,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2635,62,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2636,12,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2640,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2641,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2642,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2645,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2646,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2647,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2649,48,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(2650,49,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3001,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3002,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3003,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3004,42,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3101,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3105,36,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3106,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3108,35,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3211,7,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3212,7,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3231,41,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3301,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3305,40,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3308,39,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3401,6,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3402,6,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3403,6,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3404,42,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3500,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3510,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3511,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3518,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3520,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3530,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3550,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3560,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3561,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3562,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3570,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3590,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3600,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3610,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3611,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3612,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3613,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3619,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3640,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3620,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3630,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3680,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3690,5,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3470,1,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3910,8,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3911,8,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(3912,8,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4211,7,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4212,7,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4410,23,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4415,23,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4416,23,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4417,23,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4420,24,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4425,24,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4426,24,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4427,24,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4515,20,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4516,20,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4517,20,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4531,22,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4532,22,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4533,22,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4535,21,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4536,21,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4537,21,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4545,50,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4546,50,0 );');
            $db->query('INSERT INTO base_account_vat_sru (account_id, vat, calendar_year) VALUES(4547,50,0 );');

            $db->query('UPDATE base_account_vat_sru set created_at=\'' . date("Y-m-d H:i:s") . '\'');


            $db->transComplete();
        } catch (DatabaseException $e) {
            $this->down();
            throw $e;
        }
    }
}
