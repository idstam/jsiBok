<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use \App\Libraries\JsiReturns;

class Login extends BaseController
{
	public function getIndex()
	{
		return $this->index();
	}

	public function postIndex()
	{
		return $this->getIndex();
	}
	public function index()
	{
		helper('jsi_helper');

		if (strtolower($this->request->getMethod()) == 'post') {
			$this->session->remove('email');
			$this->session->remove('userID');
			$this->session->remove('companyID');
			$this->session->remove('companyName');			
			$this->session->remove('yearStart');
			$this->session->remove('yearEnd');
			$this->session->remove('yearID');
			$this->session->remove('role');
			


			$fieldRules = [
				'user'  => ['label' => 'Epost', 'rules' => 'required|valid_email']
			];
			if ($this->validate($fieldRules)) {

				$usersModel = model('App\Models\UsersModel');
				$user = $usersModel->where('email', $this->request->getPost('user'))->first();
				if ($user !== null) {

					if ($this->request->getPost('pwdreset') != null) {
						$this->pwd_reset();
						return;
					}

					$h = md5($user->salt . "|" . $this->request->getPost('password'));

					if ($user->password === $h) {
						$this->session->set('email', $this->request->getPost('user'));
						$this->session->set('userID', $user->id);
						
						//TODO:$session->set('role', $user['role']);

						$now = date('Y-m-d');

						$login_data = [
							'last_login'    => $now
						];
						$usersModel->update($this->request->getPost('user'), $login_data);


						return redirect()->to('/company');
					}else {
                        $this->session->setFlashdata('errors', array("FelNamnPwd" => "Fel användarnamn eller lösenord."));

                        return redirect()->to('/login');
                        return;
                    }
				} else {
					$this->session->setFlashdata('errors', array("FelNamnPwd" => "Fel användarnamn eller lösenord."));
					
					return redirect()->to('/login');
					return;
				}
			}
		}


		$data['title'] = 'Logga in';
		$data['description'] = 'Logga in';

		echo view('common/header', $data);
		echo view('user/login', $data);
		echo view('common/empty_footer');
	}

	private function pwd_reset()
	{
		$resetModel = model('App\Models\PasswordResetModel');
		$requestID =  uniqid();
		$now = date('Y-m-d H:i:s');
		$data = array(
			'email' => $this->request->getPost('user'),
			'requested'  => $now,
			'request_id'  => $requestID
		);

		$resetModel->replace($data);

		$data['title'] = 'Nytt lösenord';
		$data['description'] = 'Nytt lösenord';
		$data['email'] = $this->request->getPost('user');


		$home = isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == "huvudboken.se" ? "huvudboken.se" : "localhost:8080";
		$text = 'Hej' . PHP_EOL . PHP_EOL .
			'Här kommer en länk så att du kan byta lösenord på huvudboken.se.' . PHP_EOL . PHP_EOL .
			'https://' . $home . '/resetpassword?resetid=' . $requestID . PHP_EOL . PHP_EOL .
			'mvh' . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL .
			'//johan//';


		mailgun(
			$data['email'],
			'dig',
			'Huvudboken.se',
			'info@huvudboken.se',
			'Nytt lösenord till huvudboken.se',
			'',
			$text,
			'pwd_reset',
			'info@huvudboken.se'
		);

		echo view('common/header', $data);
		echo view('user/password_reset', $data);
		echo view('common/empty_footer');
	}

    public function getLogout(){
        $this->session->destroy();
        return redirect()->to("/");
    }
}
