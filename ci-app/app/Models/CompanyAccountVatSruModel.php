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
insert into company_account_vat_sru(company_id, account_id, vat, sru, created_at, updated_at, deleted_at)
select ?, b.account_id, b.vat, b.sru, ?, b.updated_at, b.deleted_at
from base_account_vat_sru b where b.calendar_year = ?        
";
        $now = date('Y-m-d H:i:s');
        $query = $this->db->query($sql, [$companyId, $now , $calendar_year]);
            //dd($this->db->getLastQuery());
        return $query;
    }

    /**
     * Returns rows joining company_account_vat_sru with company_booking_accounts for a company.
     * Uses table aliases and returns all rows for the company (no account filter).
     *
     * @param int $companyId
     * @return array<object>
     */
    public function getWithBookingAccounts(int $companyId, ?int $limit = null, ?int $offset = null): array
    {
        $sql = "
            SELECT cba.*, cavs.vat, cavs.sru
            FROM company_booking_accounts AS cba
            LEFT JOIN company_account_vat_sru AS cavs
                ON cavs.account_id = cba.account_id
               AND cavs.company_id = cba.company_id
            WHERE cba.company_id = ?
            ORDER BY cba.account_id ASC
        ";
        // Keep the base query intact; only append LIMIT/OFFSET if provided
        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset !== null) {
                $sql .= " OFFSET " . intval($offset);
            }
        }
        $query = $this->db->query($sql, [$companyId]);
        return $query->getResult();
    }

    /**
     * Count total rows for pagination for a company.
     */
    public function countWithBookingAccounts(int $companyId): int
    {
        $sql = "
            SELECT COUNT(*) AS cnt
            FROM company_booking_accounts AS cba
            WHERE cba.company_id = ?
        ";
        $query = $this->db->query($sql, [$companyId]);
        $row = $query->getRow();
        return $row ? (int)$row->cnt : 0;
    }
}
