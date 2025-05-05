<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyAccountVatSruModel extends Model
{
    protected $table            = 'company_account_vat_sru';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['company_id', 'calendar_year', 'account_id', 'vat', 'sru'];

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

    public function CopyVatSru($companyId, $calendar_year){
        $sql = "
insert into company_account_vat_sru(calendar_year, company_id, account_id, vat, sru, created_at, updated_at, deleted_at)
select ?, ?, b.account_id, b.vat, b.sru, ?, b.updated_at, b.deleted_at
from base_account_vat_sru b where b.calendar_year = ?        
";
        $now = date('Y-m-d H:i:s');
        $query = $this->db->query($sql, [$calendar_year, $companyId, $now , $calendar_year]);

            //dd($this->db->getLastQuery());

        return $query;

    }

}
