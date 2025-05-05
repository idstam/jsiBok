<?php

namespace App\Controllers;

use App\Libraries\Sie\SieDocumentWriter;

//use Libraries\Sie\SieDocument as SieDocument;

class Home extends BaseController
{
    public function index()
    {
		if($this->session->has('userID')){
			return redirect()->to("/company");
		}
		$data['title'] = 'huvudboken.se';
		$data['description'] = 'Gratis, eller billig, bokföring online.';
		echo view('common/header', $data);
		echo view('welcome_message');
		echo view('common/footer');

    }
}
