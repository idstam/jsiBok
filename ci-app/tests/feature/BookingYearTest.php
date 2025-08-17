<?php

namespace feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class BookingYearTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    // For Migrations
    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupCompany();
    }

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        $driver = $db->DBDriver;

        foreach (['company_voucher_rows', 'company_vouchers', 'company_users',
                     'company_voucher_series_values', 'company_voucher_series', 'company_booking_years',
                     'company_values',
                     'company_account_vat_sru', 'company_booking_accounts', 'companies'] as $tableName) {
            $builder = $db->table($tableName);
            try {
                // Disable foreign key checks based on database driver
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 0;');
                $builder->truncate();

                // Re-enable foreign key checks based on database driver
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 1;');
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

    protected function setupCompany()
    {
        $data = [
            'email' => 'joe@example.com',
            'name' => 'Joe Cool',
            'salt' => 'salt',
            'password' => md5('salt|pwd'),
        ];

        $this->hasInDatabase('users', $data);
        $this->userID = $this->grabFromDatabase('users', 'id', ['email' => 'joe@example.com']);

        $session = [
            'email' => 'joe@example.com',
            'userID' => $this->userID,
        ];

        $this->companyName = uniqid();
        $data = [
            'name' => $this->companyName,
            'org_no' => '555555-5555',
            'booking_year_start' => '2024-01-01',
            'booking_year_end' => '2024-12-31'
        ];
        $result = $this->withSession($session)->post('company/save', $data);

        $this->companyID = $this->grabFromDatabase('companies', 'id', ['name' => $this->companyName]);
        $this->bookingYearID = $this->grabFromDatabase('company_booking_years', 'id', ['company_id' => $this->companyID]);
        $this->session = [
            'email' => 'joe@example.com',
            'userID' => $this->userID,
            'companyID' => $this->companyID,
            'companyName' => $this->companyName,
            'yearStart' => '2024-01-01',
            'yearEnd' => '2024-12-31',
            'yearID' => $this->bookingYearID,

        ];

    }
    //###########################################################
    // TESTS START HERE


    private function createVoucher($vdate)
    {
        $title = uniqid();
        $data = [
            'vserie' => 'V',
            'vdate' => $vdate,
            'vtitle' => $title,
        ];
        $rows = [
            ['account' => '1010', 'debet' => 1000, 'kredit' => 0],
            ['account' => '2020', 'debet' => 0, 'kredit' => 1000],
        ];
        for ($i = 0; $i < count($rows); $i++) {
            $data['vr_account-' . $i] = $rows[$i]['account'];
            $data['vr_debet-' . $i] = $rows[$i]['debet'];
            $data['vr_kredit-' . $i] = $rows[$i]['kredit'];
        }
        return $data;
    }

    public function testVoucherNumberOnNewYear()
    {
        $voucherData = $this->createVoucher('2024-01-10');
        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name' => 'V']);
        $nextVoucherNumberPre = $this->grabFromDatabase('company_voucher_series_values', 'next', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID]);

        $result = $this->withSession($this->session)->post('voucher/save', $voucherData);

        //Kolla att vouchern sparats
        $this->seeInDatabase('company_vouchers', ['title' => $voucherData['vtitle']]);
        $firstYearID = $this->grabFromDatabase('company_vouchers', 'booking_year_id', ['title' => $voucherData['vtitle']]);
        //Har den rätt nummer?
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'voucher_series_id' => $seriesID, 'booking_year_id' => $firstYearID, 'next' => $nextVoucherNumberPre + 1]);

        //Skapa ett nytt bokföringsår
        $data = [
            'booking_year' => $this->bookingYearID,
            'new_booking_year_start' => '2025-01-01',
            'new_booking_year_end' => '2025-12-31',
            'new_year_active' => 1,
            'default_series' => 'V'
        ];

        $result = $this->withSession($this->session)->post('company/edit', $data);
        $newYearID = $this->grabFromDatabase('company_booking_years', 'id', ['company_id' => $this->companyID, 'year_start' => $data['new_booking_year_start'], 'year_start' => $data['new_booking_year_start']]);
        $this->assertNotEquals($newYearID, $firstYearID);
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'voucher_series_id' => $seriesID, 'booking_year_id' => $newYearID]);

        $activeYear = $this->grabFromDatabase('company_booking_years', 'year_start', ['company_id' => $this->companyID, 'active' => 1]);
        $this->assertStringStartsWith('2025-01-01', $activeYear);

        //Nästa voucher
        $voucherData = $this->createVoucher('2025-01-10');
        $result = $this->withSession($this->session)->post('voucher/save', $voucherData);

        //Har den sparats?
        $this->seeInDatabase('company_vouchers', ['title' => $voucherData['vtitle']]);
        $secondYearID = $this->grabFromDatabase('company_vouchers', 'booking_year_id', ['title' => $voucherData['vtitle']]);

        //Fick den också rätt nummer?
        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name' => 'V']);
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'voucher_series_id' => $seriesID, 'booking_year_id' => $secondYearID, 'next' => $nextVoucherNumberPre + 1]);

    }


}
