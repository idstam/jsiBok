<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyBookingAccountsModel extends Model
{
    protected $table = 'company_booking_accounts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['id', 'company_id', 'account_id', 'name'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function accountNameMap($companyID): array
    {
        $companyAccounts = $this->where('company_id', $companyID)->orderBy('account_id') ->findAll();
        $ret = [];
        foreach ($companyAccounts as $account){
            $ret[$account->account_id] = $account->name;
        }
        return $ret;
    }
    public function ensureCompanyAccountsExists($voucher)
    {
        $voucherAccounts = [];
        foreach($voucher->rows as $row){
            $voucherAccounts[] = $row->account_id;
        }

        $builder = $this->db->table('company_booking_accounts');
//        $query = $builder->join('base_accounts', 'base_accounts.id = company_booking_accounts.id', 'right')->whereIn('base_accounts.id', $voucherAccounts)->getCompiledSelect();
//        d($query);
        $builder->select('base_accounts.*, company_booking_accounts.account_id');
        $query = $builder->join('base_accounts', 'base_accounts.id = company_booking_accounts.account_id', 'right')->whereIn('base_accounts.id', $voucherAccounts)->get();
        foreach($query->getResult() as $row){

            if($row->account_id === null) {
                $this->insert(['company_id' => $voucher->company_id, 'account_id' => $row->id, 'name' => $row->name]);
            }
        }

        return true;


    }
}
