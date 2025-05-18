<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class CreateVoucherTemplateTest extends CIUnitTestCase
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
        foreach (['company_voucher_template_rows', 'company_voucher_templates', 'company_users',
                     'company_voucher_series', 'company_booking_years', 'company_values',
                     'company_account_vat_sru', 'company_booking_accounts', 'companies'] as $tableName) {
            $builder = $db->table($tableName);
            try {
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 0;');
                $builder->truncate();
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

    public function testCreateVoucherTemplateWithSpecificAccounts()
    {
        // Test creating a VoucherTemplate with:
        // - Debit account: 1930 (100%)
        // - Credit accounts: 2641 (20%) and 5460 (80%)
        // - Title: "kontorsmaterial"
        // - Serie: "V"

        $title = "kontorsmaterial";
        $data = [
            'vserie' => 'V',
            'vtitle' => $title,
        ];

        $rows = [
            ['account' => '1930', 'debet' => '%100', 'kredit' => 0],    // Debit account 1930 (100%)
            ['account' => '2641', 'debet' => 0, 'kredit' => '%20'],     // Credit account 2641 (20%)
            ['account' => '5460', 'debet' => 0, 'kredit' => '%80'],     // Credit account 5460 (80%)
        ];

        for ($i = 0; $i < count($rows); $i++) {
            $data['vr_account-' . $i] = $rows[$i]['account'];
            $data['vr_debet-' . $i] = $rows[$i]['debet'];
            $data['vr_kredit-' . $i] = $rows[$i]['kredit'];
        }

        $result = $this->withSession($this->session)->post('voucher-template/save', $data);

        // Verify the template was created
        $this->seeInDatabase('company_voucher_templates', ['title' => $title, 'serie' => 'V']);
        $templateID = $this->grabFromDatabase('company_voucher_templates', 'id', ['title' => $title]);

        // Verify the template rows were created with correct accounts and amounts
        foreach ($rows as $row) {
            if ($row['debet'] !== 0) {
                $this->seeInDatabase('company_voucher_template_rows', [
                    'template_id' => $templateID, 
                    'account_id' => $row['account'], 
                    'debet_amount' => $row['debet']
                ]);
            } else {
                $this->seeInDatabase('company_voucher_template_rows', [
                    'template_id' => $templateID, 
                    'account_id' => $row['account'], 
                    'kredit_amount' => $row['kredit']
                ]);
            }
        }
    }

}
