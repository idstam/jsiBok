<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Company extends BaseController
{
    public function getAccounts()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyID') == null) {
            return redirect()->to('/company');
        }
        $model = model('App\Models\CompanyAccountVatSruModel');
        $companyId = intval($this->session->get('companyID'));

        $perPage = 25;
        // Accept both query (?page=2) and segment if needed later; for now use query var
        $page = max(1, intval($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $perPage;

        // Fetch total first to clamp page within range
        $total = $model->countWithBookingAccounts($companyId);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        // Keep the current DB query; just limit/offset it
        $accounts = $model->getWithBookingAccounts($companyId, $perPage, $offset);

        // Build simple pagination data (not using CI4 model->paginate to keep custom SQL)
        $data = [
            'title' => 'Företagskonton (VAT/SRU)',
            'description' => '',
            'accounts' => $accounts,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
        ];

        echo view('common/header', $data);
        echo view('company/accounts', $data);
        echo view('common/footer');
        return '';
    }

    public function postAccounts()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyID') == null) {
            return redirect()->to('/company');
        }

        $companyId = intval($this->session->get('companyID'));
        $post = $this->request->getPost();
        $rows = $post['rows'] ?? [];
        $deletes = $post['deletes'] ?? [];

        $cba = model('App\Models\CompanyBookingAccountsModel');
        $cavs = model('App\Models\CompanyAccountVatSruModel');

        $db = \Config\Database::connect();
        $db->transStart();

        // Handle deletions
        foreach ($deletes as $delAccountId) {
            $aid = intval($delAccountId);
            // Soft-delete VAT/SRU mapping if exists
            $cavs->where('company_id', $companyId)->where('account_id', $aid)->delete();
            // Delete account row for this company
            $cba->where('company_id', $companyId)->where('account_id', $aid)->delete();
        }

        // Handle upserts/updates
        foreach ($rows as $key => $row) {
            $origAccountId = isset($row['orig_account_id']) ? intval($row['orig_account_id']) : null;
            $accountId = isset($row['account_id']) ? intval($row['account_id']) : null;
            $name = trim($row['name'] ?? '');
            $vat = ($row['vat'] === '' || $row['vat'] === null) ? null : intval($row['vat']);
            $sru = ($row['sru'] === '' || $row['sru'] === null) ? null : intval($row['sru']);

            // Skip rows that were marked for deletion
            if (in_array((string)$origAccountId, $deletes, true) || in_array($origAccountId, $deletes, true)) {
                continue;
            }

            if (!$accountId) {
                continue; // invalid row
            }

            // Upsert into company_booking_accounts
            $existing = $cba->where('company_id', $companyId)->where('account_id', $origAccountId ?? $accountId)->first();
            if ($existing) {
                // If account id changed, update account_id and name
                $data = [
                    'name' => $name,
                ];
                if ($origAccountId !== null && $origAccountId !== $accountId) {
                    $data['account_id'] = $accountId;
                }
                $cba->where('id', $existing->id)->set($data)->update();
            } else {
                // Insert new
                $cba->insert([
                    'company_id' => $companyId,
                    'account_id' => $accountId,
                    'name' => $name,
                ]);
            }

            // Upsert into company_account_vat_sru
            $existingVS = $cavs->where('company_id', $companyId)->where('account_id', $origAccountId ?? $accountId)->withDeleted()->first();
            if ($existingVS) {
                // If account id changed, ensure the mapping is moved
                $vsData = [
                    'vat' => $vat,
                    'sru' => $sru,
                    'deleted_at' => null,
                ];
                if ($origAccountId !== null && $origAccountId !== $accountId) {
                    $vsData['account_id'] = $accountId;
                }
                $cavs->where('id', $existingVS->id)->set($vsData)->update();
            } else {
                if ($vat !== null || $sru !== null) {
                    $cavs->insert([
                        'company_id' => $companyId,
                        'account_id' => $accountId,
                        'vat' => $vat,
                        'sru' => $sru,
                    ]);
                }
            }
        }

        $db->transComplete();

        return redirect()->to('/company/accounts');
    }

    public function getDimensions()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyID') == null) {
            return redirect()->to('/company');
        }
        $companyId = (int)$this->session->get('companyID');
        $type = $this->request->getGet('type') ?? 'kostnadsstalle';
        $dimNumber = ($type === 'project') ? 2 : 1; // 1=Kostnadsställe, 2=Project

        $model = model('App\\Models\\CompanyDimensionsModel');
        $perPage = 25;
        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $perPage;
        $total = $model->countByCompanyAndType($companyId, $dimNumber);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }
        $rows = $model->getByCompanyAndType($companyId, $dimNumber, $perPage, $offset);

        $data = [
            'title' => 'Dimensioner',
            'description' => '',
            'rows' => $rows,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'type' => $type,
            'dimNumber' => $dimNumber,
        ];

        echo view('common/header', $data);
        echo view('company/dimensions', $data);
        echo view('common/footer');
        return '';
    }

    public function postDimensions()
    {
        if ($this->session->get('userID') == null) {
            return redirect()->to('/');
        }
        if ($this->session->get('companyID') == null) {
            return redirect()->to('/company');
        }
        $companyId = (int)$this->session->get('companyID');
        $type = $this->request->getGet('type') ?? ($this->request->getPost('type') ?? 'kostnadsstalle');
        $dimNumber = ($type === 'project') ? 2 : 1;

        $post = $this->request->getPost();
        $rows = $post['rows'] ?? [];
        $deletes = $post['deletes'] ?? [];

        $model = model('App\\Models\\CompanyDimensionsModel');
        $db = \Config\Database::connect();
        $db->transStart();

        // Deletions (soft)
        foreach ($deletes as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $model->where('company_id', $companyId)->where('dim_number', $dimNumber)->where('id', $id)->delete();
            }
        }

        // Upserts
        foreach ($rows as $key => $row) {
            $id = isset($row['id']) ? (int)$row['id'] : 0;
            $code = isset($row['dim_code']) && $row['dim_code'] !== '' ? (int)$row['dim_code'] : null;
            $title = trim($row['title'] ?? '');

            // Skip invalid
            if ($code === null && $id === 0) {
                continue;
            }

            if ($id > 0) {
                // Update
                $model->update($id, [
                    'dim_code' => $code,
                    'title' => $title,
                ]);
            } else {
                // Insert
                $model->insert([
                    'company_id' => $companyId,
                    'dim_number' => $dimNumber,
                    'dim_code' => $code ?? 0,
                    'title' => $title,
                ]);
            }
        }

        $db->transComplete();

        return redirect()->to('/company/dimensions?type=' . $type);
    }
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
                    $this->session->set('companyNumber', $c->number);
                    $this->session->set('orgNo', $c->org_no);
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
