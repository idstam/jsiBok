<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class User extends BaseController
{
    public function index()
    {
        $sessionEmail = $this->session->get('email');

		$data['title'] = 'huvudboken.se';
		$data['description'] = 'Gratis, eller billig, bokföring online.';
		echo view('common/header', $data);

        if($sessionEmail == null){
            echo view('user/create');
        }else{
            echo view('user/edit');
        }
		
		echo view('common/footer');
    }

    public function getCreate($email = "", $name = "")
    {
        $sessionEmail = $this->session->get('email');

		$data['title'] = 'huvudboken.se';
		$data['description'] = 'Gratis, eller billig, bokföring online.';
		$data["email"] = $email;
		$data["name"] = $name;

		echo view('common/header', $data);

        if($sessionEmail == null){
            echo view('user/create');
        }else{
            echo view('user/edit');
        }
		
		echo view('common/footer');

    }
	public function postSave(){
		helper('jsi_helper');
        $sessionEmail = $this->session->get('email');

		$validation =  \Config\Services::validation();

		if (strtolower($this->request->getMethod()) != 'post') {
            return redirect()->to("/");
        } 
		
		$email = $this->request->getPost('email');
		
		$name = $this->request->getPost('name');
		$password1 = $this->request->getPost('password1');
		$password2 = $this->request->getPost('password2');

		$usersModel = model('App\Models\UsersModel');
		$existing = $usersModel->where(['email'=> $email])->countAllResults();
		if($existing > 0 ){
			$this->session->setFlashdata('errors', array("Epostadressen upptagen." => esc($this->request->getPost('user')) . " används redan av en användare i systemet."));
			return redirect()->to('/user/create');
		} 

		if($password1 !== $password2){
			$this->session->setFlashdata('errors', array("Password missmatch." => "Lösenorden matchar inte."));
			return $this->getCreate($email, $name);
		}
		$fieldRules = [
			'email'  => ['label' => 'Epostadress', 'rules' => 'required|valid_emails'],
		];

		if (!$this->validate($fieldRules)) {
			return $this->getCreate($email, $name);
		}

		
		$usersModel = model('App\Models\UsersModel');
		$salt = uniqid();
		$hash = md5($salt . '|' . $password1);
		
		if($existing == 0 ){
			$savedata = ['email' => $email,'name' => $name, 'salt' => $salt, 'password' => $hash ];	

			$usersModel->insert($savedata);
			$userID = $usersModel->getInsertID();
			$this->session->set('email', $email);
			$this->session->set('userID', $userID);
			$this->journal->Write('Ny användare', "$name | $email");

			$text = 'Hej' . PHP_EOL . PHP_EOL .
			'Välkommen till huvudboken.se' . PHP_EOL . PHP_EOL .
			'Använd epostadress: ' . $email . ' och lösenord: [PWD] för att logga in.'. PHP_EOL . PHP_EOL .
			'mvh'. PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
			'//johan//';
	
			$text = str_replace("[PWD]", $password1, $text);

			slackIt('info', ':man_dancing: New user: '. esc($name) . ' ' . esc($email)  , '');
			mailgun(
			$email,
			$name,
			'huvudboken.se',
			'info@huvudboken.se',
			'Välkommen till huvudboken.se)',
			'',
			$text,
			'new_user',
			'info@huvudboken.se');
		}
		return redirect()->to("/company");
	}
	



}
