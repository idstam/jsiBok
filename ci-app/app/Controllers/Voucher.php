<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities;
use App\Entities\VoucherEntity;

use CodeIgniter\HTTP\ResponseInterface;

class Voucher extends BaseController
{
    public function getIndex($voucher = null)
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyName') == null) {
            return redirect()->to('/company');
        }

        if ($this->request->getMethod() == "POST" && isset($_POST["use_template"])) {
            $voucher = $this->use_template();
        } else {
            if ($voucher == null) {
                $voucher = new VoucherEntity();
                $voucher->rows = [];
            }
        }

        $data = [];
        $data['title'] = 'Bokföring';
        $data['description'] = '';
        $data['page_header'] = 'Nytt verifikat för ' . $this->session->get('companyName');


        $voucher_templates = [];
        $data["voucher"] = $voucher;
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
        


        echo view('common/header', $data);
        echo view("voucher/use_template", $data);
        echo view("voucher/edit_voucher", $data);
        echo view('common/footer', $data);
    }

    public function postUse_template()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }

        $v = new VoucherEntity();
        $v->rows = [];
        $v->title = "Titel från mall";
        return $v;
    }
    public function postSave()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }

        $v = new \App\Entities\VoucherEntity();
        $v->voucher_date = ensure_date($this->request->getPost("vdate"));
        $v->title = $this->request->getPost("vtitle");
        $v->serie = $this->request->getPost("vserie");
        $v->company_id  = $this->session->get('companyID');
        $v->user_id  = $this->session->get('userID');
        $v->external_reference  = '';
        $rows = [];
        $checkTotal = '0';
        $rowNo = 0;
        
        while (true) {

            if ($this->request->getPost("vr_account-" . $rowNo) !== null) {
                $vr = new \App\Entities\VoucherRowEntity();
                $vr->company_id  = $this->session->get('companyID');
                $vr->account_id = $this->request->getPost("vr_account-" . $rowNo);
                $vr->cost_center_id = $this->request->getPost("vr_costcenter-" . $rowNo);
                $vr->project_id = $this->request->getPost("vr_project-" . $rowNo);
                $vr->setAmountFromPost($this->request->getPost("vr_debet-" . $rowNo), $this->request->getPost("vr_kredit-" . $rowNo));
                $rows[$rowNo] = $vr;
                $rowNo += 1;

            } else {
                break;
            }
        }

        $v->rows = $rows;
        $v->booking_year_start = ensure_date($this->session->get('yearStart'));
        $v->booking_year_end = ensure_date($this->session->get('yearEnd'));
        $v->booking_year_id = $this->session->get('yearID');

        $vm = model('App\Models\VoucherModel');
        $v->source ='Manuell bokning';
        $v = $vm->Add($v);

        if ($v->id !== -1) {
            $this->journal->Write('Nytt verifikat', "$v->serie $v->voucher_number | $v->title");
            return $this->getSaved($v);
        } else {
            $errCount = 0;
            foreach ($v->validationErrors as $e) {
                $errCount++;
                $this->session->setFlashdata('errors', array("VoucherValidationError$errCount" => $e));
            }
            return $this->getIndex($v);
        }

        
        
    }

    public function getSaved($voucher = null) {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        $companyID = $this->session->get('companyID');
        
        if (! $voucher instanceof \App\Entities\VoucherEntity){
            if(is_string($voucher)) {
                $tmp = $voucher;
                $vm = model('App\Models\VoucherModel');
                $voucher = $vm->GetByNumber($companyID, $tmp);
                if ($voucher === null) {
                    $this->session->setFlashdata('errors', array("Okänt verifikationsnummer" => 'Okänt verifikationsnummer: ' . esc($tmp)));
                    return redirect()->to('/voucher');
                }
            }
        }


        $data = [];
        $data['title'] = 'Bokföring';
        $data['description'] = '';
        //$data['page_header'] = 'Senaste verifikatet för ' . $this->session->get('companyName');

        
        $data["voucher"] = $voucher;



        echo view('common/header', $data);
        echo view("voucher/created_voucher", $data);
        echo view('common/footer', $data);

    }
    public function getRevert($voucher = null){
        
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        $companyID = $this->session->get('companyID');
        
        if (! $voucher instanceof \App\Entities\VoucherEntity){
            if(is_string($voucher)) {
                $tmp = $voucher;
                $vm = model('App\Models\VoucherModel');
                $voucher = $vm->GetByNumber($companyID, $tmp);
                if ($voucher === null) {
                    $this->session->setFlashdata('errors', array("Okänt verifikationsnummer" => 'Okänt verifikationsnummer: ' . esc($tmp)));
                    return redirect()->to('/voucher');
                }
            }
        }

        //TODO: DO not show the regret button for a closed year.

        $voucher->Revert($this->session->get('userID'));
        $vm = model('App\Models\VoucherModel');
        $yearStart = $this->session->get('yearStart');
        $yearEnd = $this->session->get('yearEnd');

        $v = $vm->Add($voucher, $yearStart, $yearEnd);
        if ($v->id !== -1) {
            $this->journal->Write('Nytt verifikat', "$v->serie $v->voucher_number | $v->title");
            return $this->getSaved($v);
        } else {
            $errCount = 0;
            
            foreach ($v->validationErrors as $e) {
                $errCount++;
                $this->session->setFlashdata('errors', array("VoucherValidationError$errCount" => $e));
            }
            return $this->getIndex($v);
        }


    }
}
