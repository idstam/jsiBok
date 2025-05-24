<?php

namespace App\Controllers;

use App\Entities\CompanyEntity;
use App\Entities\VoucherEntity;
use App\Entities\VoucherRowEntity;
use App\Entities\VoucherSeriesEntity;
use App\Libraries\Sie\SieVoucher;
use App\Libraries\Sie\SieVoucherRow;
use CodeIgniter\Database\Exceptions\DatabaseException;

use App\Libraries\Sie\SieDocument;
use Config\Database;

class Sie extends BaseController
{
    private array $bookingYears = [];

    public function postImport()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        $sessionCompanyID = $this->session->get("companyID");
        $fileName = $this->session->get("sieTempFile");

        $toNewCompany = (bool)$this->request->getPost("to_new_company");

        $doc = new SieDocument();

        $doc->ReadDocument($fileName);


        //If companyID == null, then create a new company
        $sessionEmail = $this->session->get('email');
        $sessionUserID = $this->session->get('userID');

        $db = Database::connect();

        try {
            $db->transException(true);
            $db->transException(true)->transStart();
            if ($toNewCompany) {
                $cm = model('App\Models\CompaniesModel');

                $prevCompanies = $cm->like('name', $doc->FNAMN->Name, 'both', null, true)->countAllResults();


                if($prevCompanies > 0){
                    $doc->FNAMN->Name = $doc->FNAMN->Name . ' (' . $prevCompanies . ')';
                }
                $number = $this->createCompany($doc, $sessionUserID, $sessionEmail);
                if ($number) {

                    $sessionCompanyID = $cm->where('number', $number)->first()->id;
                } else {
                    $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara företaget till databasen. Loggen är skickad till supporten."));
                    $db->transRollback();
                    return redirect()->to('/company');
                }
            } else {
                $number = $this->session->get('companyNumber');
            }

            //Create all RAR
            if (!$this->createBookingYears($doc, $sessionCompanyID)) {
                $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara bokföringsår till databasen. Loggen är skickad till supporten."));
                $db->transRollback();
                return redirect()->to('/company');
            }

            //Create all VoucherSeries + IB
            $seriesMappings = $this->createVoucherSeries($doc, $sessionCompanyID);
            if (!$seriesMappings) {
                $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara verifikationsserier till databasen. Loggen är skickad till supporten."));
                $db->transRollback();
                return redirect()->to('/company');
            }

            //Activate and rename used accounts
            if (!$this->activateAndRenameAccounts($doc, $sessionCompanyID)) {
                $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara konton till databasen. Loggen är skickad till supporten."));
                $db->transRollback();
                return redirect()->to('/company');
            }

            //Import IB for selected years
            if (!$this->account_balances($doc, $sessionCompanyID)) {
                $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara kontobalanser till databasen. Loggen är skickad till supporten."));
                $db->transRollback();
                return redirect()->to('/company');
            }

            //Import Vouchers for selected years
            if (!$this->vouchers($doc, $sessionCompanyID, $seriesMappings)) {
                $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte spara verifikaten till databasen. Loggen är skickad till supporten."));
                $db->transRollback();
                return redirect()->to('/company');
            }
//            d($doc->GEN_DATE);
//            d($doc->MinDate);
//            dd($doc->MaxDate);
            $this->journal->Write('SIE-Fil inläst', "Skapad " . $doc->GEN_DATE->format('Y-m-d') . " | Period " . $doc->MinDate->format('Y-m-d') . " till " .$doc->MaxDate->format('Y-m-d'));

            $db->transComplete();

        } catch (DatabaseException $e) {
            log_message("warning", var_export($e, true));
            $this->session->setFlashdata('errors', array("UnexpectedDbError." => "Ett oväntat fel uppstod när vi försökte importera filen. Loggen är skickad till supporten."));
            $db->transRollback();
            return redirect()->to('/company');
        }

        return redirect()->to('/company/select/' . $number);
    }

    public function postValidate()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }

        if ($_FILES["sie_file"]['name'] == '') {
            $this->session->setFlashdata('errors', array("NoFile." => "Ingen fil vald."));
            return redirect()->to('/company');
        }

        $fileName = $_FILES["sie_file"]["tmp_name"];
        $tmpFilepath = WRITEPATH . 'uploads/' . uniqid() . '.sie';
        copy($fileName, $tmpFilepath);
        $this->session->set("sieTempFile", $tmpFilepath);

        $toNewCompany = intval((bool)$this->request->getPost("chk_to_new_company"));

        $doc = new SieDocument();

        $doc->ReadDocument($tmpFilepath);

        //log_message("notice", $this->session->get('companyName') .  " SIE import typ " . $doc->SIETYP . " " . $tmpFilepath . " " . $postedName);

        if (!$doc->ValidateDocument()) {
            log_message("notice", var_export($doc->ValidationExceptions, true));
            //$this->session->setFlashdata('errors', array("UnexpectedDbError." =>"Ett oväntat fel uppstod när vi försökte spara informationen till databasen. Loggen är skickad till supporten."));
        }

        $cm = model('App\Models\CompaniesModel');
        $company = $cm->where('id', $this->session->get('companyID'))->first();
        if ($company === null || $toNewCompany) {
            $toNewCompany = 1;
            $company = new CompanyEntity();
            $company->name = '';
            $company->org_no = '';
        }

        $data = [
            'sie_company_name' => $doc->FNAMN->Name,
            'sie_org_no' => $doc->FNAMN->OrgIdentifier ?? '',
            'sie_gen_date' => $doc->GEN_DATE->format('Ymd'),
            'sie_gen_namn' => $doc->GEN_NAMN,
            'sie_gen_program' => join(" ", $doc->PROGRAM),
            'sie_series' => $doc->Series,
            'sie_rar' => $doc->RAR,
            'sie_max_date' => $doc->MaxDate->format('Ymd'),
            'sie_min_date' => $doc->MinDate->format('Ymd'),
            'company_name' => $company->name,
            'org_no' => $company->org_no,
            'title' => 'Validera SIE-fil',
            'description' => '',
            'to_new_company'=> $toNewCompany,
        ];

        $vs = model('App\Models\CompanyVoucherSeriesModel');
        $series = $vs->where('company_id', $this->session->get('companyID'))->findAll();
        if (count($series) == 0) {
            $s = new VoucherSeriesEntity();
            $s->name = 'V';
            $s->title = 'Verifikationer';
            array_push($series, $s);
        }

        $data['voucher_series'] = $series;

        //$this->journal->Write('SIE-fil inläst', esc($this->session->get('companyName') .  " " . $doc->SIETYP . " " . $postedName));
        return view('common/header', $data) .
            view('sie/validate', $data) .
            view('common/footer', $data);

    }


    private function findBookingYear(\DateTime $date)
    {
        helper('jsi_helper');
        $d = $date;
        foreach ($this->bookingYears as $year) {
            if (ensure_date($year['year_start'], 'Y-m-d') <= $d && ensure_date($year['year_end'], 'Y-m-d') >= $d) {
                return $year;
            }
        }
        return false;

    }

    private function vouchers(SieDocument $doc, $companyID, $seriesMappings): bool
    {

        //TODO: Look for SIE-file with #OBJEKT to test cost center and project

        foreach ($doc->VER as $sv) {

            /** @var $sv SieVoucher */
            //If the year wasn't selected in the UI we should not import its vouchers.
            $bookingYear = $this->findBookingYear($sv->VoucherDate);
            if ($bookingYear === false) {
                continue;
            }
            $v = new VoucherEntity();
            $v->company_id = $companyID;
            $v->user_id = $this->session->get('userID');
            $v->booking_year_id = intval($bookingYear['dbID']);
            $v->booking_year_start = date_create_from_format('Y-m-d', $bookingYear['year_start']);
            $v->booking_year_end = date_create_from_format('Y-m-d+', $bookingYear['year_end']);

            $v->voucher_date = $sv->VoucherDate;
            $v->title = $sv->Text == "" ? 'Tom från SIE-fil' : $sv->Text;
            $v->serie = $seriesMappings[$sv->Series];
            //        'voucher_number'  => null,
            //$v->external_reference  => null,
            $v->source = 'SIE';
            $rows = [];

            foreach ($sv->Rows as $svr) {
                /** @var $sv SieVoucherRow */
                $vr = new VoucherRowEntity();
                //$vr->voucher_id  => null,
                $vr->company_id = $companyID;
                $vr->account_id = intval($svr->Account->Number);
//                $vr->cost_center_id  => null,
//                $vr->project_id  => null,
                $vr->amount = $svr->Amount;
                array_push($rows, $vr);
            }
            $v->rows = $rows;

            $vm = model('App\Models\VoucherModel');

            $v = $vm->Add($v);

            if ($v->id === -1) {
                foreach ($v->validationErrors as $e) {
                    log_message("notice", var_export($e, true));
                }
                return false;
            }
        }

        return true;
    }

    private function account_balances(SieDocument $doc, $companyID): bool
    {

        //TODO: Look for SIE-file with #OBJEKT to test cost center and project
        foreach (['IB' => $doc->IB, 'UB' => $doc->UB, 'RES' => $doc->RES] as $type => $periodValues) {
            foreach ($periodValues as $periodValue) {
                //If the year wasn't selected in the UI we should not import its balances.
                if (!key_exists($periodValue->YearNr, $this->bookingYears)) {
                    continue;
                }

                $am = model('App\Models\CompanyAccountBalanceModel');
                $data = [
                    'booking_year_id' => $this->bookingYears[$periodValue->YearNr]['dbID'],
                    'type' => $type,
                    'company_id' => $companyID,
                    'account_id' => intval($periodValue->Account->Number),
                    'cost_center_id' => null,
                    'project_id' => null,
                    'amount' => $periodValue->Amount
                ];

                $result = $am->insert($data, false);
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    private function activateAndRenameAccounts(SieDocument $doc, $companyID): bool
    {
        //dd($doc->KONTO);
        //TODO: Handle renamed accounts
        //Just copy them from the sie file for now
        $am = model('App\Models\CompanyBookingAccountsModel');
        foreach ($doc->KONTO as $konto) {
            $result = $am->ignore(true)->insert([
                'company_id' => $companyID,
                'account_id' => intval($konto->Number),
                'name' => $konto->Name
            ]);
//            if (!$result) {
//                return false;
//            }
        }
        return true;
    }

    private function createVoucherSeries(SieDocument $doc, $companyID): bool|array
    {
        $mappings = [];

        foreach ($this->request->getPost() as $name => $value) {
            if (str_starts_with($name, 'sie_serie-')) {
                $sieSerie = str_replace('sie_serie-', '', $name);
                $mappings[$sieSerie] = $value;

            }
        }

        $vs = model('App\Models\CompanyVoucherSeriesModel');

        foreach ($doc->Series as $name => $count) {
            if ($mappings[$name] == $name) {
                $data = [
                    'company_id' => $companyID,
                    'name' => $name,
                    'title' => $name . ' importerad',
                ];

                $result = $vs->ignore(true)->insert($data);
//                if (!$result) {
//                    return false;
//                }
            }
        }

        return $mappings;
    }

    private function createBookingYears(SieDocument $doc, $companyID): bool
    {
        $this->bookingYears = [];
        $postData = $this->request->getPost();
        $bym = model('App\Models\CompanyBookingYearsModel');
        foreach ($doc->RAR as $year) {
            if (($year->ID == 0) || isset($postData['sie_rar-' . $year->ID])) {
                $data = [
                    'company_id' => $companyID,
                    'year_start' => $year->Start->format('Y-m-d'),
                    'year_end' => $year->End->format('Y-m-d'),
//                    'year_start' => $year->Start,
//                    'year_end' => $year->End,
                    'active' => 0,
                ];
                $result = $bym->ignore(true)->insert($data);

                $yearID = $bym->where('company_id', $companyID)->where('year_start', $year->Start->format('Y-m-d'))->first()?->id;

                if (!$yearID) {
                    log_message('error', 'Failed to create booking year. Company ID: ' . $companyID .
                        ' Start: ' . $year->Start->format('Y-m-d') .
                        ' End: ' . $year->End->format('Y-m-d'));
                    return false;
                }

                $data['dbID'] = $yearID;
                $this->bookingYears[$year->ID] = $data;
            }
        }

        return true;
    }

    private function createCompany(SieDocument $doc, $sessionUserID, $sessionEmail)
    {
        $companiesModel = model('App\Models\CompaniesModel');
        $number = $companiesModel->createCompany(
            $doc->FNAMN->Name,
            $doc->FNAMN->OrgIdentifier ?? '',
            $doc->RAR[0]->Start,
            $doc->RAR[0]->End,
            $sessionUserID,
            $sessionEmail
        );
        if ($number) {
            $this->journal->Write(
                'Nytt företag från SIE-fil',
                $doc->FNAMN->Name . '|' .
                ($doc->FNAMN->OrgIdentifier ?? '') . '|' .
                $doc->RAR[0]->Start->format('Ymd') . '|' .
                $doc->RAR[0]->End->format('Ymd')
            );
            return $number;
        } else {
            return false;
        }
    }
}
