<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Entities\CompanyEntity;
use Config\Database;

class CompaniesModel extends Model
{
    protected $table            = 'companies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = CompanyEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['number', 'org_no', 'name', 'owner_id', 'owner_email'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getCompaniesByUserID($userID)
    {
        $db      = Database::connect();
        $builder = $db->table('companies');
        $builder->select('companies.*, users.name as user_name, company_users.role as user_role');
        $builder->join('company_users', 'company_users.company_id = companies.id');
        $builder->join('users', 'users.id = company_users.user_id');
        $builder->where('company_users.user_id', $userID);


        //dd($builder->getCompiledSelect());

        $query = $builder->get();
        return $query->getResult();
    }

    public function createCompany($name, $orgNo, $yearStart, $yearEnd, $sessionUserID, $sessionEmail): bool|string
    {
        $db      = Database::connect();

        try {
            $this->db->transException(true)->transStart();
            $number = uniqid();

            $company = new CompanyEntity();
            $company->name = $name;
            $company->org_no = $orgNo;
            $company->number = $number;
            $company->owner_id = $sessionUserID;
            $company->owner_email = $sessionEmail;

            $this->save($company);
            $companyID = $this->getInsertID();


            $cu = model('App\Models\CompanyUsersModel');
            $cu->insert([
                'company_id' => $companyID,
                'user_id' => $sessionUserID,
                'role' => 'owner'
            ]);
            
            
            $by = model('App\Models\CompanyBookingYearsModel');
            $by->insert([
                'company_id' => $companyID,
                'year_start' => is_string($yearStart) ? $yearStart : $yearStart->format('Y-m-d'),
                'year_end' => is_string($yearEnd) ? $yearEnd : $yearEnd->format('Y-m-d'),
                'active' => 1
            ]);
            $yearID = $by->getInsertID();


            $cavs = model('App\Models\CompanyAccountVatSruModel');
            $cavs->CopyVatSru($companyID, 0);

            $vs = model('App\Models\CompanyVoucherSeriesModel');
            $serieID = $vs->ensureVoucherSeries($companyID, 'V', 'Verifikationer', $yearID);

            $cv = model('App\Models\CompanyValuesModel');
            $cv->insert([
                'company_id' => $companyID,
                'name' => 'default_series',
                'title' => 'Standardserie',
                'string_value' => 'V'
            ]);

            $cv->insert([
                'company_id' => $companyID,
                'name' => 'price_plan',
                'title' => 'Prisplan',
                'string_value' => 'Gratis'
            ]);

            $cv->insert([
                'company_id' => $companyID,
                'name' => 'moms_period',
                'title' => 'Momsperiod',
                'string_value' => 'Kvartal'
            ]);

            $this->db->transComplete();
        } catch (DatabaseException $e) {
            log_message("warning", var_export($e, true ));
            $this->db->transRollback();
            return false;
        }
        return $number;
    }
}
