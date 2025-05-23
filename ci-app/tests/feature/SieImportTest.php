<?php

namespace feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class SieImportTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    // For Migrations
    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupUser();
    }

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        $driver = $db->DBDriver;

        foreach(['company_account_balance', 'company_voucher_rows', 'company_vouchers',
                    'company_users', 'company_voucher_series', 'company_booking_years',
                    'company_values','company_account_vat_sru', 'company_booking_accounts',
                    'companies'] as $tableName){
            $builder = $db->table($tableName);
            try {
                // Disable foreign key checks based on database driver
                if ($driver == 'MySQLi') {
                    $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 0;');
                } elseif ($driver == 'SQLite3') {
                    $db->simpleQuery('PRAGMA foreign_keys = OFF;');
                }

                $builder->truncate();

                // Re-enable foreign key checks based on database driver
                if ($driver == 'MySQLi') {
                    $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 1;');
                } elseif ($driver == 'SQLite3') {
                    $db->simpleQuery('PRAGMA foreign_keys = ON;');
                }
            } catch (\Exception $e) {
                d($e);
                dd($tableName);
            }
        }
        parent::tearDown();

    }
    protected $companyName = '';
    protected $companyID;
    protected $userID;
    protected $session;
    protected $bookingYearID;

    protected function setupUser()
    {
        $email = uniqid() . 'joe@example.com';
        $data = [
            'email' => $email,
            'name' => 'Joe Cool',
            'salt' => 'salt',
            'password' => md5('salt|pwd'),
        ];

        $this->hasInDatabase('users', $data);
        $this->userID = $this->grabFromDatabase('users', 'id', ['email' => $email]);

        $this->session = [
            'email' => $email,
            'userID' => $this->userID,
        ];

    }


    public function testBrokenYear(): void
    {
        $this->setupUser();
        $this->session['sieTempFile'] = 'writable/uploads/TEST_BROKEN_YEAR.sie';

        $data = [
            'sie_rar-0' => '1',
            'sie_serie-#' => 'V',
            'sie_serie-A' => 'V',
            'sie_serie-F' => 'V',
            'sie_serie-I' => 'V',
            'sie_serie-L' => 'V',
            'sie_serie-U' => 'V',
            'to_new_company'=> '1',
        ];

        $result = $this->withSession($this->session)->post('sie/import', $data);



        $this->seeInDatabase('companies', ['org_no'=> '556265-1892']);
        $this->seeInDatabase('journal', ['user_id'=>$this->userID, 'title'=> 'Nytt företag från SIE-fil']);


        // Get every row created by ExampleSeeder

        // Make sure the count is as expected
        //$this->assertCount(3, $objects);
    }

}
