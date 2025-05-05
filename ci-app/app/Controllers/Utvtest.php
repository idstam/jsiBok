<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Utvtest extends BaseController
{
    public function getIndex(){
        return $this->index();
    }

    public function index()
    {
        helper('jsi_helper');

        if ($this->session->get('email') != 'johan@jsi.se') {
            return redirect()->to('/');
        }

        $data['title'] = 'UTV-TEST';
        $data['description'] = 'Blandade testsaker';



        echo view('common/header', $data);

        echo "XX " . mailgun("johan@idstam.se", "Johan", "Huvudboken", "info@huvudboken.se", "Test rubrik", '', 'Lite text', '', 'info@huvudboken.se');
        echo view('common/footer');
    }
}
