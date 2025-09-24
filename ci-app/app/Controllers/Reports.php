<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Reports extends BaseController
{
    public function getIndex()
    {
        if ($this->session->get('userID') == null || $this->session->get('companyName') == null) {
            return redirect()->to('/');
        }
        //helper('jsi_helper');
        $data = [];
        $data['title'] = 'Rapporter';
        $data['description'] = '';

        $cm = model('App\Models\CompanyBookingYearsModel');
        $years = $cm->where('company_id', $this->session->get('companyID'))->findAll();
        $data['startMonth'] = ensure_date_string($years[0]->year_start, 'm');
        $data['endMonth'] = ensure_date_string($years[0]->year_end, 'm');

        $data['years'] = $years;
        echo view('common/header', $data);
        echo view('reports/choose', $data);

        echo view('common/footer');

    }

    public function getChoose()
    {
        if ($this->session->get('userID') == null || $this->session->get('companyName') == null) {
            return redirect()->to('/');
        }
        //helper('jsi_helper');
        $ym = model('App\Models\CompanyBookingYearsModel');


        $year = $ym->where('company_id', $this->session->get('companyID'))->where('id', $this->request->getGet('booking_year'))->first();
        $y = ensure_date($year->year_start)->format('Y');
        list($fromDate, $toDate, $tomDate) = $this->getPeriodDates($year);

        if ($this->request->getGet('submit1')) {
            return $this->huvudbok($this->session->get('companyID'), $year->id, $fromDate, $toDate, $tomDate);
        }
        if ($this->request->getGet('submit2')) {
            return $this->resultat($this->session->get('companyID'), $year->id, $fromDate, $toDate, $tomDate);
        }
        if ($this->request->getGet('submit3')) {
            return $this->balans($this->session->get('companyID'), $year->id, $fromDate, $toDate, $tomDate);
        }
        if ($this->request->getGet('submitVerifikat')) {
            return $this->verifikat($this->session->get('companyID'), $year->id, $fromDate, $toDate, $tomDate);
        }
        if ($this->request->getGet('submitMoms')) {
            return $this->moms($this->session->get('companyID'), $year->id, $fromDate, $toDate, $tomDate);
        }
        if ($this->request->getGet('submitJournal')) {
            return $this->journal($this->session->get('companyID'), $year->id, $fromDate, $toDate, $tomDate);
        }

        return redirect()->to('/reports');
    }

    private function huvudbok($companyID, $bookingYear, $fromDate, $toDate, $tomDate): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $rm = model('App\Models\ReportsModel');
        $cba = model('App\Models\CompanyBookingAccountsModel');
        $accountNameMap = $cba->accountNameMap($companyID);

        //dd(ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($toDate, 'Y-m-d'));
        $reportQuery = $rm->huvudbok($companyID, $bookingYear, ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($tomDate, 'Y-m-d'));
        $data = [];
        $data['title'] = 'Huvudbok';
        $data['reportName'] = 'Huvudbok';
        $data['description'] = '';
        $data['reportQuery'] = $reportQuery->getResult('object');
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;
        $data['accountNames'] = $accountNameMap;

        echo view('common/header', $data);
        echo view('reports/header', $data);
        echo view('reports/huvudbok', $data);
        echo view('common/footer', $data);
        return '';
    }

    private function moms($companyID, $bookingYear, $fromDate, $toDate, $tomDate): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $rm = model('App\Models\ReportsModel');
        $cba = model('App\Models\CompanyBookingAccountsModel');
        $accountNameMap = $cba->accountNameMap($companyID);

        //dd(ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($toDate, 'Y-m-d'));
        $reportQuery = $rm->moms($companyID, $bookingYear, ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($tomDate, 'Y-m-d'));
        $data = [];

        $data['title'] = 'Momsredovisning';
        $data['reportName'] = 'Momsredovisning';
        $data['description'] = '';
        $data['reportQuery'] = $reportQuery->getResult('object');
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;
        $data['orgNr'] = $this->session->get('orgNo');
        $data['accountNames'] = $accountNameMap;

        echo view('common/header', $data);
        echo view('reports/header', $data);
        echo view('reports/moms', $data);
        echo view('reports/moms-xml', $data);
        echo "</div>";
        echo view('common/footer', $data);
        return '';
    }

    private function verifikat($companyID, $bookingYear, $fromDate, $toDate, $tomDate)
    {
        $rm = model('App\Models\ReportsModel');

        //dd(ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($toDate, 'Y-m-d'));
        $reportQuery = $rm->verifikat($companyID, $bookingYear, ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($tomDate, 'Y-m-d'));
        $data = [];
        $data['title'] = 'Verifikat';
        $data['reportName'] = 'Verifikat';
        $data['description'] = '';
        $data['reportQuery'] = $reportQuery->getResult('object');
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;

        echo view('common/header', $data);
        echo view('reports/header', $data);
        echo view('reports/verifikat', $data);
        echo view('common/footer', $data);
        return '';


    }

    private function balans($companyID, $bookingYear, $fromDate, $toDate, $tomDate): string|\CodeIgniter\HTTP\RedirectResponse
    {

        $rm = model('App\Models\ReportsModel');

        //dd(ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($toDate, 'Y-m-d'));
        $reportQuery = $rm->balansAndResultat($companyID, $bookingYear, ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($tomDate, 'Y-m-d'), 0, 2999);
        $data = [];
        $data['title'] = 'Balansräkning';
        $data['reportName'] = 'Balansräkning';
        $data['description'] = 'DESCRIPTION  -------------';
        $data['reportQuery'] = $reportQuery->getResult('object');
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;

        echo view('common/header', $data);
        echo view('reports/header', $data);
        echo view('reports/balans', $data);
        echo view('common/footer', $data);
        return '';
    }

    private function resultat($companyID, $bookingYear, $fromDate, $toDate, $tomDate): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $rm = model('App\Models\ReportsModel');
        helper('jsi_helper');
        //dd(ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($toDate, 'Y-m-d'));
        $reportQuery = $rm->balansAndResultat($companyID, $bookingYear, ensure_date_string($fromDate, 'Y-m-d'), ensure_date_string($tomDate, 'Y-m-d'), 3000, 9999);
        $data = [];
        $data['title'] = 'Resultaträkning';
        $data['reportName'] = 'Resultaträkning';
        $data['description'] = 'DESCRIPTION  -------------';
        $data['reportQuery'] = $reportQuery->getResult('object');
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;

        echo view('common/header', $data);
        echo view('reports/header', $data);
        echo view('reports/resultat', $data);
        echo view('common/footer', $data);
        return '';
    }

    private function journal($companyID, $bookingYear, $fromDate, $toDate, $tomDate): string|\CodeIgniter\HTTP\RedirectResponse
    {
        helper('jsi_helper');
        $jm = model('App\Models\JournalModel');
        $reportQuery = $jm->getJournalEntries($companyID, $bookingYear);

        $data = [];
        $data['title'] = 'Journal';
        $data['reportName'] = 'Journal';
        $data['description'] = 'DESCRIPTION  -------------';
        $data['reportQuery'] = $reportQuery->getResult('object');
        $data['fromDate'] = $fromDate;
        $data['toDate'] = $toDate;

        echo view('common/header', $data);
        //echo view('reports/header', $data);
        echo view('reports/journal', $data);
        echo view('common/footer', $data);
        return '';
    }


    /**
     * @param object|array|null $year
     * @return array
     * @throws \DateMalformedStringException
     */
    public function getPeriodDates(object|array|null $year): array
    {
        $fm = $this->request->getGet('from_period');
        if ($fm == '-1') {
            $fromDate = ensure_date($year->year_start);
            $toDate = ensure_date($year->year_end);
            $tomDate = ensure_date($year->year_end)->modify('+1 day');
        } elseif ($fm == '-2') {
            $fromDate = ensure_date($year->year_start);
            $toDate = ensure_date($year->year_start)->modify('+3 month')->modify('-1 day');
            $tomDate = ensure_date($year->year_start)->modify('+3 month');
        } elseif ($fm == '-3') {
            $fromDate = ensure_date($year->year_start)->modify('+3 month');
            $toDate = ensure_date($year->year_start)->modify('+6 month')->modify('-1 day');
            $tomDate = ensure_date($year->year_start)->modify('+6 month');
        } elseif ($fm == '-4') {
            $fromDate = ensure_date($year->year_start)->modify('+6 month');
            $toDate = ensure_date($year->year_start)->modify('+9 month')->modify('-1 day');
            $tomDate = ensure_date($year->year_start)->modify('+9 month');
        } elseif ($fm == '-5') {
            $fromDate = ensure_date($year->year_start)->modify('+9 month');
            $toDate = ensure_date($year->year_start)->modify('+1 year')->modify('-1 day');
            $tomDate = ensure_date($year->year_start)->modify('+1 year');
        } else {
            $fromDate = ensure_date($year->year_start)->modify('+' . $fm . ' month');
            $tm = intval($this->request->getGet('to_period'));
            $toDate = ensure_date($year->year_start)->modify('+' . $tm . ' month');
            $tomDate = ensure_date($year->year_start)->modify('+' . ($tm + 1) . ' month');
        }
        return array($fromDate, $toDate, $tomDate);
    }

}
