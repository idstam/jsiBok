<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class About extends BaseController
{
	public function getIndex(){
		return $this->index();
	}

	public function index()
	{
		$data['title'] = 'Om tjänsten';
		$data['description'] = 'Om tjänsten: Koppling mellan Skatteverket och Fortnox.';
		
		
		echo view('common/header', $data);
		echo view('about', $data);
		echo view('common/footer');
	}
}
