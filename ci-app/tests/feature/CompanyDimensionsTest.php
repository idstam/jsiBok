<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class CompanyDimensionsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $migrateOnce = true;
    protected $refresh = true;
    protected $namespace = null;

    protected $companyID;
    protected $userID;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupCompany();
    }

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        foreach ([
            'company_voucher_rows','company_vouchers','company_users','company_voucher_series_values','company_voucher_series',
            'company_booking_years','company_values',
            'company_dimensions',
            'company_account_vat_sru','company_booking_accounts','companies','users'] as $tableName) {
            $builder = $db->table($tableName);
            try {
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 0;');
                $builder->truncate();
                $db->simpleQuery('SET FOREIGN_KEY_CHECKS = 1;');
            } catch (\Exception $e) {
                // ignore
            }
        }
        parent::tearDown();
    }

    protected function setupCompany(): void
    {
        $this->hasInDatabase('users', [
            'email' => 'dimuser@example.com',
            'name' => 'Dim User',
            'salt' => 'salt',
            'password' => md5('salt|pwd'),
        ]);
        $this->userID = (int)$this->grabFromDatabase('users', 'id', ['email' => 'dimuser@example.com']);

        $session = [
            'email' => 'dimuser@example.com',
            'userID' => $this->userID,
        ];

        $companyName = uniqid('cmp_dim_');
        $data = [
            'name' => $companyName,
            'org_no' => '111111-1111',
            'booking_year_start' => '2024-01-01',
            'booking_year_end' => '2024-12-31',
        ];
        $this->withSession($session)->post('company/save', $data);

        $this->companyID = (int)$this->grabFromDatabase('companies', 'id', ['name' => $companyName]);
        $bookingYearID = (int)$this->grabFromDatabase('company_booking_years', 'id', ['company_id' => $this->companyID]);
        $this->session = [
            'email' => 'dimuser@example.com',
            'userID' => $this->userID,
            'companyID' => $this->companyID,
            'companyName' => $companyName,
            'yearStart' => '2024-01-01',
            'yearEnd' => '2024-12-31',
            'yearID' => $bookingYearID,
        ];
    }

    protected function seedDimensions(int $dimNumber, int $total = 230): void
    {
        // Seed many rows to test pagination (perPage=100)
        for ($i = 1; $i <= $total; $i++) {
            $this->hasInDatabase('company_dimensions', [
                'company_id' => $this->companyID,
                'dim_number' => $dimNumber,
                'dim_code' => $i,
                'title' => ($dimNumber === 1 ? 'KS ' : 'PR ') . $i,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function testPaginationBoundariesForKostnadsstalle(): void
    {
        $perPage = 25; // must match controller
        $total = 230;
        $this->seedDimensions(1, $total); // type=kostnadsstalle
        $lastPage = (int)ceil($total / $perPage);

        // page -1 clamps to 1
        $res = $this->withSession($this->session)->get('/company/dimensions?type=kostnadsstalle&page=-1');
        $res->assertOK();
        $res->assertSee('Sida 1 av ' . $lastPage);

        // page 0 clamps to 1
        $res = $this->withSession($this->session)->get('/company/dimensions?type=kostnadsstalle&page=0');
        $res->assertOK();
        $res->assertSee('Sida 1 av ' . $lastPage);

        // page 1 shows 100
        $res = $this->withSession($this->session)->get('/company/dimensions?type=kostnadsstalle&page=1');
        $res->assertOK();
        $res->assertSee('Visar ' . $perPage . ' av totalt ' . $total . ' rader.');

        // last page shows remaining
        $res = $this->withSession($this->session)->get('/company/dimensions?type=kostnadsstalle&page=' . $lastPage);
        $res->assertOK();
        $res->assertSee('Sida ' . $lastPage . ' av ' . $lastPage);
        $lastCount = $total - ($perPage * ($lastPage - 1));
        $res->assertSee('Visar ' . $lastCount . ' av totalt ' . $total . ' rader.');

        // beyond last clamps to last
        $res = $this->withSession($this->session)->get('/company/dimensions?type=kostnadsstalle&page=' . ($lastPage + 1));
        $res->assertOK();
        $res->assertSee('Sida ' . $lastPage . ' av ' . $lastPage);
    }

    public function testCrudOperationsForProject(): void
    {
        // Use type=project (dim_number=2) for CRUD
        // Start with a few rows
        for ($i = 1; $i <= 3; $i++) {
            $this->hasInDatabase('company_dimensions', [
                'company_id' => $this->companyID,
                'dim_number' => 2,
                'dim_code' => $i,
                'title' => 'PR ' . $i,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Create new row via POST
        $post = [
            'type' => 'project',
            'rows' => [
                'new0' => [
                    'id' => 0,
                    'dim_code' => 555,
                    'title' => 'New Project',
                ],
            ],
        ];
        $res = $this->withSession($this->session)->post('/company/dimensions?type=project', $post);
        $res->assertRedirectTo('/company/dimensions?type=project');
        $this->seeInDatabase('company_dimensions', [
            'company_id' => $this->companyID,
            'dim_number' => 2,
            'dim_code' => 555,
            'title' => 'New Project',
            'deleted_at' => null,
        ]);

        // Update an existing row: change code and title of the first seeded row
        $id = (int)$this->grabFromDatabase('company_dimensions', 'id', [
            'company_id' => $this->companyID,
            'dim_number' => 2,
            'dim_code' => 1,
        ]);
        $post = [
            'type' => 'project',
            'rows' => [
                (string)$id => [
                    'id' => $id,
                    'dim_code' => 777,
                    'title' => 'Updated Project 1',
                ],
            ],
        ];
        $res = $this->withSession($this->session)->post('/company/dimensions?type=project', $post);
        $res->assertRedirectTo('/company/dimensions?type=project');
        $this->seeInDatabase('company_dimensions', [
            'id' => $id,
            'company_id' => $this->companyID,
            'dim_number' => 2,
            'dim_code' => 777,
            'title' => 'Updated Project 1',
            'deleted_at' => null,
        ]);

        // Delete an existing row: mark id for deletion and verify soft delete
        $idToDelete = (int)$this->grabFromDatabase('company_dimensions', 'id', [
            'company_id' => $this->companyID,
            'dim_number' => 2,
            'dim_code' => 2,
        ]);
        $post = [
            'type' => 'project',
            'deletes' => [$idToDelete],
        ];
        $res = $this->withSession($this->session)->post('/company/dimensions?type=project', $post);
        $res->assertRedirectTo('/company/dimensions?type=project');

        // Soft delete expectations: default visible query should not find it; trashed should exist
        $db = \Config\Database::connect();
        $visibleCount = $db->table('company_dimensions')
            ->where(['id' => $idToDelete, 'company_id' => $this->companyID])
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();
        $this->assertSame(0, $visibleCount, 'Expected dimension row to be hidden (soft-deleted)');

        $trashed = $db->table('company_dimensions')
            ->where(['id' => $idToDelete, 'company_id' => $this->companyID])
            ->where('deleted_at IS NOT NULL', null, false)
            ->get()->getRow();
        $this->assertNotNull($trashed, 'Expected soft-deleted dimension row to exist with deleted_at');
    }
}
