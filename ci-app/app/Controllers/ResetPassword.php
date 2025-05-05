<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ResetPassword extends BaseController
{
    public function getIndex(){
		return $this->index();
	}

    public function postIndex(){
        return $this->index();
    }
    public function index()
    {
        //Begär återställning för en epostadress
        //Få en länk hit med en guid
        //Ange nytt lösenord

        helper('jsi_helper');
        $validation =  \Config\Services::validation();

        if (strtolower($this->request->getMethod()) == 'post') {
            $redirTarget = $this->setNewPassword();  
            return redirect()->to($redirTarget );
        } 
        $resetID = $this->request->getPostGet('resetid');
        $resetModel = model('App\Models\PasswordResetModel');
        $resetRequest = $resetModel->where('request_id', $resetID )->first();

		$data['title'] = 'Återställ lösenord';
		$data['description'] = 'Återställ lösenord';
        $data['reset_id'] = $resetID;

		echo view('common/header', $data);


        if($resetRequest ){
            echo view('password_reset_set_new', $data);
        } else{
            $validation->setError('old', 'Länken du följde hit är inte giltig längre. Försök återställa lösenordet igen.' . $resetID ) ;
            $this->session->setFlashdata('errors', $validation->getErrors());
            echo view('user/login', $data);
        } 

		
		echo view('common/empty_footer');
    }

    function setNewPassword(){
        $validation =  \Config\Services::validation();
        $resetModel = model('App\Models\PasswordResetModel');

        $resetID = $this->request->getPostGet('reset_id');
        $pwdA = $this->request->getPostGet('password');
        $pwdB = $this->request->getPostGet('password2');

        $resetRequest = $resetModel->where('request_id', $resetID )->first();

        if($pwdA !=$pwdB ){ 
            $validation->setError('passwords', 'De angivna lösenorden är olika.') ;
            $this->session->setFlashdata('errors', $validation->getErrors());
            return '/resetpassword?resetid=' .$resetID  ;
        } 

        $usersModel = model('App\Models\UsersModel');
		$user = $usersModel->where('email', $resetRequest['email'])->first();
		if (is_array($user) && count($user) > 0) {

			$user['salt'] = uniqid();
			$hash = md5($user['salt'] . "|" . $pwdA);

			$user['password'] = $hash;

			$usersModel->save($user);

			slackIt('info', ':shrug: Password reset: ' . $resetRequest['email'], '');
           $resetModel->delete($resetRequest['email']) ;
        }	

        $validation->setError('old', 'Nu är lösenordet utbytt. Logga in med ditt nya lösenord.') ;
        $this->session->setFlashdata('errors', $validation->getErrors());

        return '/login';
    } 
}
