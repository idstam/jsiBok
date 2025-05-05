<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Vouchtemplate extends BaseController
{
    public function getIndex()
    {
        if($this->session->get('userID') == null){
            return redirect()->to('/');
        }
        if ($this->session->get('companyName') == null) {
            return redirect()->to('/company');
        }

        $data = [];
        $data['title'] = 'Mallar';
        $data['description'] = '';
        $data['page_header'] = 'Hantera bokföringsmallar för ' . $this->session->get('companyName');


        $voucher_templates = [];
        $data["voucher_templates"] = $voucher_templates;

        $ca = model('App\Models\CompanyBookingAccountsModel');
        $data["accounts"] = $ca->where('company_id', $this->session->get('companyID'))->findAll();
        $data["cost_centers"]  = [];
        $data["projects"] = [];

        $vs = model('App\Models\CompanyVoucherSeriesModel');
        $data["voucher_series"] = $vs->where('company_id', $this->session->get('companyID'))->findAll();

        $cv = model('App\Models\CompanyValuesModel');
        $companyValues = $cv->where('company_id', $this->session->get('companyID'))->findAll();

        $values = [];
        foreach ($companyValues as $value) {
            $values[$value->name] = $value;
        }
        $data['values'] = $values;
        $data["voucher"] = new \App\Entities\VoucherEntity();
        echo view('common/header', $data);
        echo view("voucher/edit_voucher", $data);
        echo view('common/footer', $data);

    }
    public function getNew()
    {
        if($this->session->get('userID') == null){
            return redirect()->to('/');
        }
        if ($this->session->get('companyName') == null) {
            return redirect()->to('/company');
        }

        $data = [];
        $data['title'] = 'Mallar';
        $data['description'] = '';
        $data['page_header'] = 'Ny bokföringsmall';


        $voucher_templates = [];
        $data["voucher_templates"] = $voucher_templates;

        $ca = model('App\Models\CompanyBookingAccountsModel');
        $data["accounts"] = $ca->where('company_id', $this->session->get('companyID'))->findAll();
        $data["cost_centers"]  = [];
        $data["projects"] = [];

        $vs = model('App\Models\CompanyVoucherSeriesModel');
        $data["voucher_series"] = $vs->where('company_id', $this->session->get('companyID'))->findAll();

        $cv = model('App\Models\CompanyValuesModel');
        $companyValues = $cv->where('company_id', $this->session->get('companyID'))->findAll();

        $values = [];
        foreach ($companyValues as $value) {
            $values[$value->name] = $value;
        }
        $data['values'] = $values;
        $data["voucher"] = new \App\Entities\VoucherEntity();
        echo view('common/header', $data);
        echo view("voucher/edit_template", $data);
        echo view('common/footer', $data);

    }

    public function postNew()
    {

    }
}
