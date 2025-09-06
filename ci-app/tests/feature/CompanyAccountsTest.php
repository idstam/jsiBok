<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class CompanyAccountsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected $companyName = '';
    protected $companyID;
    protected $userID;
    protected $session;
    protected $bookingYearID;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupCompany();
    }

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        foreach (['company_voucher_rows','company_vouchers','company_users','company_voucher_series_values','company_voucher_series','company_booking_years','company_values','company_account_vat_sru','company_booking_accounts','companies','users'] as $tableName) {
            $builder = $db->table($tableName);
            try {
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 0;');
                $builder->truncate();
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 1;');
            } catch (\Exception $e) {
                // swallow to keep teardown robust in CI environments
            }
        }
        parent::tearDown();
    }

    protected function setupCompany()
    {
        $this->hasInDatabase('users', [
            'email' => 'joe@example.com',
            'name' => 'Joe Cool',
            'salt' => 'salt',
            'password' => md5('salt|pwd'),
        ]);
        $this->userID = $this->grabFromDatabase('users', 'id', ['email' => 'joe@example.com']);

        $session = [
            'email' => 'joe@example.com',
            'userID' => $this->userID,
        ];

        $this->companyName = uniqid('cmp_');
        $data = [
            'name' => $this->companyName,
            'org_no' => '555555-5555',
            'booking_year_start' => '2024-01-01',
            'booking_year_end' => '2024-12-31',
        ];
        $this->withSession($session)->post('company/save', $data);

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

    protected function seedAccounts(int $total = 60): void
    {
        // Create company_booking_accounts in ascending account_id order
        for ($i = 0; $i < $total; $i++) {
            $aid = 1000 + $i; // 1000..(1000+total-1)
            $this->hasInDatabase('company_booking_accounts', [
                'company_id' => $this->companyID,
                'account_id' => $aid,
                'name' => 'Account ' . $aid,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if ($i % 2 === 0) { // some VAT/SRU mappings
                $this->hasInDatabase('company_account_vat_sru', [
                    'company_id' => $this->companyID,
                    'account_id' => $aid,
                    'vat' => 25,
                    'sru' => 1234,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function testPaginationBoundaries(): void
    {
        $perPage = 25; // must match controller
        $total = 60;
        $this->seedAccounts($total);
        $lastPage = (int)ceil($total / $perPage);

        // page -1 clamps to 1
        $res = $this->withSession($this->session)->get('/company/accounts?page=-1');
        $res->assertOK();
        $res->assertSee('Sida 1 av ' . $lastPage);

        // page 0 clamps to 1
        $res = $this->withSession($this->session)->get('/company/accounts?page=0');
        $res->assertOK();
        $res->assertSee('Sida 1 av ' . $lastPage);

        // page 1 normal
        $res = $this->withSession($this->session)->get('/company/accounts?page=1');
        $res->assertOK();
        // should show 25 rows (we check text that reports count)
        $res->assertSee('Visar ' . $perPage . ' av totalt ' . $total . ' konton.');

        // last page shows remaining rows
        $res = $this->withSession($this->session)->get('/company/accounts?page=' . $lastPage);
        $res->assertOK();
        $res->assertSee('Sida ' . $lastPage . ' av ' . $lastPage);
        $lastCount = $total - ($perPage * ($lastPage - 1));
        $res->assertSee('Visar ' . $lastCount . ' av totalt ' . $total . ' konton.');

        // page after last clamps to last
        $res = $this->withSession($this->session)->get('/company/accounts?page=' . ($lastPage + 1));
        $res->assertOK();
        $res->assertSee('Sida ' . $lastPage . ' av ' . $lastPage);
    }

    public function testCrudOperations(): void
    {
        $this->seedAccounts(3); // 1000,1001,1002

        // Create new row with VAT/SRU
        $post = [
            'rows' => [
                'new0' => [
                    'account_id' => 1999,
                    'name' => 'New Account',
                    'vat' => 12,
                    'sru' => 5678,
                ],
            ],
        ];
        $res = $this->withSession($this->session)->post('/company/accounts', $post);
        $res->assertRedirectTo('/company/accounts');
        $this->seeInDatabase('company_booking_accounts', ['company_id' => $this->companyID, 'account_id' => 1999, 'name' => 'New Account']);
        $this->seeInDatabase('company_account_vat_sru', ['company_id' => $this->companyID, 'account_id' => 1999, 'vat' => 12, 'sru' => 5678]);

        // Update existing: change name, vat, sru for account 1000
        $post = [
            'rows' => [
                '1000' => [
                    'orig_account_id' => 1000,
                    'account_id' => 1000,
                    'name' => 'Updated 1000',
                    'vat' => 6,
                    'sru' => 9999,
                ],
            ],
        ];
        $res = $this->withSession($this->session)->post('/company/accounts', $post);
        $res->assertRedirectTo('/company/accounts');
        $this->seeInDatabase('company_booking_accounts', ['company_id' => $this->companyID, 'account_id' => 1000, 'name' => 'Updated 1000']);
        $this->seeInDatabase('company_account_vat_sru', ['company_id' => $this->companyID, 'account_id' => 1000, 'vat' => 6, 'sru' => 9999]);

        // Delete existing: mark 1001 for deletion, ensure cavs soft-deleted (if existed) and cba soft-deleted
        $this->seeInDatabase('company_booking_accounts', ['company_id' => $this->companyID, 'account_id' => 1001]);
        $db = \Config\Database::connect();
        // Check if there was an existing VAT/SRU mapping for 1001 before deletion
        $hadCavs = $db->table('company_account_vat_sru')->where(['company_id' => $this->companyID, 'account_id' => 1001])->countAllResults() > 0;
        $post = [
            'deletes' => [1001],
        ];
        $res = $this->withSession($this->session)->post('/company/accounts', $post);
        $res->assertRedirectTo('/company/accounts');

        // CBA is soft-deleted: default (non-deleted) scope should not find it
        $db = \Config\Database::connect();
        $visibleCount = $db->table('company_booking_accounts')->where(['company_id' => $this->companyID, 'account_id' => 1001])->where('deleted_at IS NULL', null, false)->countAllResults();
        $this->assertSame(0, $visibleCount, 'Expected CBA row to be hidden by default (soft-deleted)');
        $trashedCba = $db->table('company_booking_accounts')->where(['company_id' => $this->companyID, 'account_id' => 1001])->where('deleted_at IS NOT NULL', null, false)->get()->getRow();
        $this->assertNotNull($trashedCba, 'Expected CBA soft-deleted row to exist with deleted_at set');

        // CAVS handling: if CAVS existed before, it should now be soft-deleted; otherwise, it should not exist
        if ($hadCavs) {
            $row = $db->table('company_account_vat_sru')->where(['company_id' => $this->companyID, 'account_id' => 1001])->get()->getRow();
            $this->assertNull($row, 'Expected soft-deleted cavs row to be hidden by default selects');
            $row = $db->table('company_account_vat_sru')->where(['company_id' => $this->companyID, 'account_id' => 1001])->where('deleted_at IS NOT NULL', null, false)->get()->getRow();
            $this->assertNotNull($row, 'Expected cavs soft-deleted row to exist with deleted_at set');
        } else {
            $visibleCountCavs = $db->table('company_account_vat_sru')->where(['company_id' => $this->companyID, 'account_id' => 1001])->where('deleted_at IS NULL', null, false)->countAllResults();
            $trashedCountCavs = $db->table('company_account_vat_sru')->where(['company_id' => $this->companyID, 'account_id' => 1001])->where('deleted_at IS NOT NULL', null, false)->countAllResults();
            $this->assertSame(0, $visibleCountCavs + $trashedCountCavs, 'Expected no cavs row to exist when none existed before');
        }
    }
}
