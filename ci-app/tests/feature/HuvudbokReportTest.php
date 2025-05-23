<?php

namespace feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class HuvudbokReportTest extends CIUnitTestCase
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
                    'company_values', 'company_account_vat_sru', 'company_booking_accounts',
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



}
