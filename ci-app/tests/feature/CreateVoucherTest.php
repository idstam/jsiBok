<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class CreateVoucherTest extends CIUnitTestCase
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
                     'company_voucher_series_values','company_voucher_series', 'company_booking_years',
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

    public function testSavePageNotLoggedIn()
    {
        //Should redirect to /        
        $result = $this->withSession([])->get('/voucher');

        $response = $result->response();
        $result->assertOK();
        $result->assertRedirectTo('/');

    }

    public function testIndexPageLoggedIn()
    {

        $result = $this->withSession($this->session)->get('voucher');
        $result->assertOK();
        $result->assertSee("Nytt verifikat för $this->companyName");

    }

    public function testCreateSimpleVoucher()
    {
        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name' => 'V']);
        $nextVoucherNumberPre = $this->grabFromDatabase('company_voucher_series_values', 'next', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID]);

        $title = uniqid();
        $data = [
            'vserie' => 'V',
            'vdate' => '2024-01-10',
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
        $result = $this->withSession($this->session)->post('voucher/save', $data);

        $this->seeInDatabase('company_vouchers', ['title' => $title]);
        $voucherID = $this->grabFromDatabase('company_vouchers', 'id', ['title' => $title]);

        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name'=>'V']);
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'id' => $seriesID, 'next' => $nextVoucherNumberPre + 1]);

        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]['debet'] > 0) {
                $this->seeInDatabase('company_voucher_rows', ['voucher_id' => $voucherID, 'account_id' => $rows[$i]['account'], 'amount' => $rows[$i]['debet']]);
            } else {
                $this->seeInDatabase('company_voucher_rows', ['voucher_id' => $voucherID, 'account_id' => $rows[$i]['account'], 'amount' => -$rows[$i]['kredit']]);
            }
        }
    }

    public function testCreateVoucherUnbalanced()
    {
        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name' => 'V']);
        $nextVoucherNumberPre = $this->grabFromDatabase('company_voucher_series_values', 'next', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID]);

        $title = uniqid();
        $data = [
            'vserie' => 'V',
            'vdate' => '2024-01-10',
            'vtitle' => $title,
        ];

        $rows = [
            ['account' => '1010', 'debet' => 1000, 'kredit' => 0],
            ['account' => '2020', 'debet' => 0, 'kredit' => 500],
        ];
        for ($i = 0; $i < count($rows); $i++) {
            $data['vr_account-' . $i] = $rows[$i]['account'];
            $data['vr_debet-' . $i] = $rows[$i]['debet'];
            $data['vr_kredit-' . $i] = $rows[$i]['kredit'];
        }
        $result = $this->withSession($this->session)->post('voucher/save', $data);

        $this->dontSeeInDatabase('company_vouchers', ['title' => $title]);

        //Since this should fail the voucher number should not be incremented
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID, 'next' => $nextVoucherNumberPre]);

        $result->assertSee('Verifikatet är inte i balans. Diff: 500.00');
    }

    public function testCreateVoucherOutsideOfYear()
    {
        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name' => 'V']);
        $nextVoucherNumberPre = $this->grabFromDatabase('company_voucher_series_values', 'next', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID]);

        $title = uniqid();
        $data = [
            'vserie' => 'V',
            'vdate' => '2025-01-10',
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
        $result = $this->withSession($this->session)->post('voucher/save', $data);

        $this->dontSeeInDatabase('company_vouchers', ['title' => $title]);

        //Since this should fail the voucher number should not be incremented
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID, 'next' => $nextVoucherNumberPre]);

        $result->assertSee('Det finns inget bokföringsår för');
    }

    public function testCreateVoucherNegativeDebetAmount()
    {
        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name' => 'V']);
        $nextVoucherNumberPre = $this->grabFromDatabase('company_voucher_series_values', 'next', ['company_id' => $this->companyID, 'id' => $seriesID, 'booking_year_id' => $this->bookingYearID]);

        $title = uniqid();
        $data = [
            'vserie' => 'V',
            'vdate' => '2024-01-10',
            'vtitle' => $title,
        ];

        $rows = [
            ['account' => '1010', 'debet' => -1000, 'kredit' => 0],
            ['account' => '2020', 'debet' => 0, 'kredit' => 1000],
        ];
        for ($i = 0; $i < count($rows); $i++) {
            $data['vr_account-' . $i] = $rows[$i]['account'];
            $data['vr_debet-' . $i] = $rows[$i]['debet'];
            $data['vr_kredit-' . $i] = $rows[$i]['kredit'];
        }
        $result = $this->withSession($this->session)->post('voucher/save', $data);
        //dd($result);

        //The negative sign should just be stripped from the amount and the voucher should pass

        $this->seeInDatabase('company_vouchers', ['title' => $title]);
        $voucherID = $this->grabFromDatabase('company_vouchers', 'id', ['title' => $title]);

        $seriesID = $this->grabFromDatabase('company_voucher_series', 'id', ['company_id' => $this->companyID, 'name'=>'V']);
        $this->seeInDatabase('company_voucher_series_values', ['company_id' => $this->companyID, 'id' => $seriesID, 'next' => $nextVoucherNumberPre + 1]);

        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]['debet'] != 0) {
                $this->seeInDatabase('company_voucher_rows', ['voucher_id' => $voucherID, 'account_id' => $rows[$i]['account'], 'amount' => abs($rows[$i]['debet'])]); //Note the abs()
            } else {
                $this->seeInDatabase('company_voucher_rows', ['voucher_id' => $voucherID, 'account_id' => $rows[$i]['account'], 'amount' => -abs($rows[$i]['kredit'])]);
            }
        }
    }

}
