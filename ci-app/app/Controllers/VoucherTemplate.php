<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class VoucherTemplate extends BaseController
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

    public function postSave()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyName') == null) {
            return redirect()->to('/company');
        }

        $t = new \App\Entities\VoucherEntity();
        $t->title = $this->request->getPost("vtitle");
        $t->serie = $this->request->getPost("vserie");
        $t->company_id  = $this->session->get('companyID');
        $rows = [];
        $rowNo = 0;

        while (true) {

            if ($this->request->getPost("vr_account-" . $rowNo) !== null) {
                $tr = new \App\Entities\VoucherRowEntity();
                $tr->company_id  = $this->session->get('companyID');
                $tr->account_id = $this->request->getPost("vr_account-" . $rowNo);
                $tr->cost_center_id = $this->request->getPost("vr_costcenter-" . $rowNo);
                $tr->project_id = $this->request->getPost("vr_project-" . $rowNo);
                $tr->setTemplateAmountFromPost($this->request->getPost("vr_debet-" . $rowNo), $this->request->getPost("vr_kredit-" . $rowNo));
                $rows[$rowNo] = $tr;
                $rowNo += 1;

            } else {
                break;
            }
        }

        $t->rows = $rows;

        $tm = model('App\Models\VoucherTemplateModel');
        $t = $tm->Add($t);

        if ($t->id !== -1) {
            $this->journal->Write('Ny bokföringsmall', "$t->title");
            return $this->getSaved($t);
        } else {
            $errCount = 0;
            foreach ($t->validationErrors as $e) {
                $errCount++;
                $this->session->setFlashdata('errors', array("VoucherValidationError$errCount" => $e));
            }
            return $this->getIndex($t);
        }



    }
}
