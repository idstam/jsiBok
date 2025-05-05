<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Entities\VoucherEntity;
use App\Entities\VoucherRowEntity;

class VoucherModel extends Model
{
    protected $table            = 'company_vouchers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['company_id', 'user_id', 'voucher_date','booking_year_id' , 'title', 'serie', 'voucher_number', 'external_reference', 'source'];

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

    public function Add($voucher)
    {
        $voucher->id = -1;

        $voucher->Validate();
        if(count($voucher->validationErrors) > 0){
            return $voucher;
        }


        $cam = model('App\Models\CompanyBookingAccountsModel');
        if(!$cam->ensureCompanyAccountsExists($voucher)){
            return $voucher;
        }
        $missingDim = [];

        foreach($voucher->rows as $row){
            $account = $cam->where(['account_id' =>$row->account_id])->first();
            if(is_null($account))
            {
                //TODO:Handle account both from base and company accounts
                array_push($missingDim,"Konto $row->account_id finns inte i kontoplanen.");
            }
            else{
                $row->account_id  = $account->account_id;
            }
        }

        if(count($missingDim) >0){
            $voucher->validationErrors = $missingDim;
            return $voucher;
        }

        try {
            $this->db->transException(true)->transStart();
            
            //Get and update next voucher number for the serie
            $vsm = model('App\Models\CompanyVoucherSeriesModel');
            $voucherSerie = $vsm->where(
                [
                'company_id' => $voucher->company_id,
                    'name' => $voucher->serie])->first();

            $vsm->update($voucherSerie->id,['next' => $voucherSerie->next +1]);

            //Set the voucher header            
            $voucher->voucher_number = $voucherSerie->next;
            
            //Save the voucher
            $voucher->voucher_date = ensure_date_string($voucher->voucher_date, 'Y-m-d');
            $this->insert($voucher);
            $voucherID = $this->getInsertID();
            $voucher->SetID($voucherID);

            //Save rows
            $vrm = model('App\Models\VoucherRowModel');
            $vrm->insertBatch($voucher->rows);

            // $cu->insert([
            //     'company_id' => $companyID,
            //     'user_id' => $sessionUserID,
            //     'role' => 'owner'
            // ]);
            $this->db->transComplete();
        } catch (DatabaseException $e) {
            $voucher->validationErrors = ['Ett oväntat fel uppstod när vi försökte spara verifikater till databasen. Loggen är skickad till supporten.'];
            return $voucher;
        }

        return $voucher;
    }
    private function splitNumber($number){
        $s = substr($number, 0, 1);
        $s = strtoupper($s);
        $n = substr($number, 1, strlen($number));
         return [$s, $n];
    }
    public function GetByNumber($company_id, $number){
        [$s, $n] = $this->splitNumber($number);

        $voucher_data = $this->where('company_id', $company_id)->where('voucher_number', $n)->where('serie', $s)->first();
        $vrm = model('App\Models\VoucherRowModel');
        $row_data = $vrm->where('company_id', $company_id)->where('voucher_id', $voucher_data->id)->findAll();

        $voucher = new VoucherEntity((array)$voucher_data);
        $rows = [];
        foreach ($row_data as $row) {
            array_push($rows, new VoucherRowEntity((array)$row));
        }
        $voucher->rows = $rows;

        return $voucher;
    }
    
}
