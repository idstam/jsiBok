<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Company extends BaseController
{
    public function getIndex($cno = "")
    {
        helper('jsi_helper');

        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }

        $data['title'] = 'Välj, eller skapa, företag';
        $data['description'] = '';

        $cm = model('App\Models\CompaniesModel');
        $companies = $cm->getCompaniesByUserID($this->session->get('userID'));
        
        foreach ($companies as $c) {
            if ($c->number === $cno || $c->id == $this->session->get("companyID") || count($companies)== 1) {

                $by = model('App\Models\CompanyBookingYearsModel');
                $y = $by->where('active', 1)->where('company_id', $c->id) ->first();

                $start = ensure_date($y->year_start, 'Y-m-d');
                $end = ensure_date($y->year_end, 'Y-m-d');
                $this->session->set('yearStart', $start);
                $this->session->set('yearEnd', $end);
                $this->session->set('yearID', $y->id);

                if ($c->number === $cno || count($companies)== 1) {
                    //Write to the session only if we are changing selected company

                    //TODO:Use enforce date for booking year

                    $this->session->set('companyID', $c->id);
                    $this->session->set('companyNumber', $cno);
                    $this->session->set('companyName', $c->name);
                    $this->session->set('role', $c->user_role);
                    //return redirect()->to("/voucher");
                }

                $data["company"] = $c;
            }
        }
        
        $data['companies'] = $companies;
        $cv = model('App\Models\CompanyValuesModel');
        $cu = model('App\Models\CompanyUsersModel');

        $cv = model('App\Models\CompanyValuesModel');
        $companyValues = $cv->where('company_id', $this->session->get('companyID'))->findAll();
        $values = [];
        foreach ($companyValues as $value) {
            $values[$value->name] = $value;
        }
        $data['values'] = $values;

        $by = model('App\Models\CompanyBookingYearsModel');
        $years = $by->where('company_id', $this->session->get('companyID'))->findAll();
        $data['booking_years'] = $years;

        $vs = model('App\Models\CompanyVoucherSeriesModel');
        $series = $vs->where('company_id', $this->session->get('companyID'))->findAll();
        $data['voucher_series'] = $series;

        echo view('common/header', $data);

        if ($this->session->has('companyName')) {
            echo view('company/view');
        }

        if (count($companies) > 1) {
            echo view('company/select', $data);
        }

        echo view('company/import_sie', $data);
        echo view('company/create', $data);
        echo view('common/footer');
        return '';

    }
    public function getSelect($cno = "")
    {
        return $this->getIndex($cno);
    }
    public function postSave()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }

        $sessionEmail = $this->session->get('email');
        $sessionUserID = $this->session->get('userID');

        $companiesModel = model('App\Models\CompaniesModel');
        $postData = $this->request->getPost();
        //TODO:Validate this data before saving

        $number = $companiesModel->createCompany(
            $postData['name'],
            $postData['org_no'],
            $postData['booking_year_start'],
            $postData['booking_year_end'],
            $sessionUserID,
            $sessionEmail
        );
        if ($number) {
            $this->journal->Write(
                'Nytt företag',
                $postData['name'] . '|' .
                    $postData['org_no'] . '|' .
                    $postData['booking_year_start'] . ' till ' .
                    $postData['booking_year_end']
            );
            return redirect()->to('/company/select/' . $number);
        } else {
            $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara företaget till databasen. Loggen är skickad till supporten."));
            return redirect()->to('/company');
        }
    }
    public function postEdit()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyName') == null) {
            return redirect()->to('/company');
        }

        $postData = $this->request->getPost();
        //TODO:Validate this data before saving

        $by = model('App\Models\CompanyBookingYearsModel');
        $by->db->transStart();
        $by->where('company_id', $this->session->get('companyID'))->set(['active' => 0])->update()  ;
        //dd($postData);

        $newYearActive = array_key_exists('new_year_active', $postData) ? 1 : 0;
        if ($newYearActive == 0) {
            $by->where('company_id', $this->session->get('companyID'))->where('id', $postData['booking_year'])->set(['active' => 1])->update();
        }
        $by->db->transComplete();

        if($postData['new_booking_year_start'] !== "" && $postData['new_booking_year_end'] !== "") {
            $by->insert([
                'company_id' => $this->session->get('companyID'),
                'year_start' => $postData['new_booking_year_start'],
                'year_end' => $postData['new_booking_year_end'],
                'active' => $newYearActive
            ]);
            $newYearID = $by->getInsertID();
            $vsv = model('App\Models\CompanyVoucherSeriesValuesModel');
            $vsv->ensureVoucherSeriesValues($this->session->get('companyID'), $newYearID);

            $this->journal->Write(
                'Nytt bokföringsår',
                $postData['new_booking_year_start'] . ' till ' .
                $postData['new_booking_year_end']);
        }

        $cv = model('App\Models\CompanyValuesModel');
        $cv->where('name', 'default_series')->where('company_id', $this->session->get('companyID'))->set('string_value', $postData['default_series'])->update();

        if(key_exists('price_plan', $postData)) {
            $cv->where('name', 'price_plan')->where('company_id', $this->session->get('companyID'))->set('string_value', $postData['price_plan'])->update();
        }
        return redirect()->to('/company');
    }

}
