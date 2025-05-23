<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class CreateCompanyTest extends CIUnitTestCase
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

    }

    protected function tearDown(): void
    {
        $db = \Config\Database::connect();
        $driver = $db->DBDriver;

        foreach(['company_vouchers', 'company_users', 'company_voucher_series',
                    'company_booking_years','company_values','company_account_vat_sru',
                    'companies'] as $tableName){
            $builder = $db->table($tableName);

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
        }
        parent::tearDown();
    }

    public function testIndexPageNotLoggedIn(){
        //Should redirect to /        
        $result = $this->get('/company');

        $response = $result->response();
        $result->assertOK();
        $result->assertRedirectTo('/');

    }
    public function testSavePageNotLoggedIn(){
        //Should redirect to /        
        $result = $this->get('/company');

        $response = $result->response();
        $result->assertOK();
        $result->assertRedirectTo('/');

        //Should redirect to /        
        $result = $this->get('/company');

        $response = $result->response();
        $result->assertOK();
        $result->assertRedirectTo('/');

    }
    public function testIndexPageLoggedIn(){
        $data = [
            'email' => 'joe@example.com',
            'name'  => 'Joe Cool',
            'salt' => 'salt',
            'password' => md5('salt|pwd'),
        ];

        $this->hasInDatabase('users', $data);
        $id = $this->grabFromDatabase('users', 'id', ['email' => 'joe@example.com']);

        $session = [
            'email' => 'joe@example.com',
            'userID' => $id,
        ];

        $result = $this->withSession($session)->get('company');

        $response = $result->response();
        $result->assertOK();
        $result->assertSee('Nytt företag');


    }
    public function testCreateCompany(){
        $data = [
            'email' => 'joe@example.com',
            'name'  => 'Joe Cool',
            'salt' => 'salt',
            'password' => md5('salt|pwd'),
        ];
        $this->hasInDatabase('users', $data);
        $id = $this->grabFromDatabase('users', 'id', ['email' => 'joe@example.com']);


        $session = [
            'email' => 'joe@example.com',
            'userID' => $id,
        ];

        $name = uniqid();
        $data = [
            'name' => $name,
            'org_no' => '555555-5555',
            'booking_year_start' => '2024-01-01',
            'booking_year_end' => '2024-12-31'
        ];
        $result = $this->withSession($session)->post('company/save', $data);

        $response = $result->response();
        $result->assertOK();

        $this->seeInDatabase('companies', ['name'=> $name]);
        $number = $this->grabFromDatabase('companies', 'number', ['name' => $name]);
        $id = $this->grabFromDatabase('companies', 'id', ['name' => $name]);

        $this->seeInDatabase('company_values', ['company_id' => $id, 'name' => 'default_series']);
        $defaultSeries = $this->grabFromDatabase('company_values', 'string_value', ['company_id' => $id]);

        $result->assertRedirectTo('company/select/' . $number);

    }

    // public function testIndexPage(){
    //     $data = [
    //         'email' => 'joe@example.com',
    //         'name'  => 'Joe Cool',
    //     ];
    //     $this->hasInDatabase('users', $data);
    //     $id = $this->grabFromDatabase('users', 'id', ['email' => 'joe@example.com']);


    //     $result = $this->post('/user/save',  [
    //         'name'  => 'Fred Flintstone3',
    //         'email' => 'flintyfred3example.com',
    //         'password1' => 'pwd',
    //         'password2' => 'pwd',
    //     ]);

    //     $response = $result->response();
    //     $result->assertOK();
    //     $result->assertSee('The Epostadress field must contain all valid email addresses.');

    // }


}
