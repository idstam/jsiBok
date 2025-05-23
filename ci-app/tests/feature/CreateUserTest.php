<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class CreateUserTest extends CIUnitTestCase
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
        parent::tearDown();

    }

    public function testIndexPage(){
        // Get a simple page
        $result = $this->call('GET', '/');        
        $response = $result->response();
        $result->assertOK();
        $result->assertSeeLink('Skapa konto');
    }
    public function testCreateAccountLinkFromIndex(){
        // Get a simple page
        $result = $this->call('GET', '/user/create');        
        $response = $result->response();
        $result->assertOK();
        $result->assertSee('Skapa nytt konto.');
        $result->assertSee('Repetera lösenord');

    }


    public function testCreateAccountOK(){
        $email = uniqid() . '@example.com';
        $result = $this->call('POST','/user/save', 
         [
            'name'  => 'Fred Flintstone',
            'email' => $email,
            'password1' => 'pwd',
            'password2' => 'pwd',
        ]);

        $response = $result->response();
        $result->assertOK();
        $result->assertSessionHas('email', $email);
        $result->assertSessionHas('userID');
        $result->assertRedirectTo('/company');

        // $db      = \Config\Database::connect();
        // $builder = $db->table('users');

        // dd($builder->get()->getResult());
        $this->seeInDatabase('journal', ['details'=> 'Fred Flintstone | ' . $email]);
        //TODO: $this->seeInDatabase('users', ['email'=> $email]);

    }
    public function testCreateAccountDifferentPasswords(){
        $result = $this->post('/user/save',  [
            'name'  => 'Fred Flintstone2',
            'email' => 'flintyfred2@example.com',
            'password1' => 'pwd',
            'password2' => 'anotherpwd',
        ]);

        $response = $result->response();
        $result->assertOK();
        $result->assertSee('Lösenorden matchar inte');

    }
    public function testCreateAccountInvalidEmail(){
        $result = $this->post('/user/save',  [
            'name'  => 'Fred Flintstone3',
            'email' => 'flintyfred3example.com',
            'password1' => 'pwd',
            'password2' => 'pwd',
        ]);

        $response = $result->response();
        $result->assertOK();
        $result->assertSee('Epostadressen måste vara ifylld.');

    }

}
