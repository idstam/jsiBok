<?php

namespace App\Models;

use App\Entities\VoucherTemplateEntity;
use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Entities\VoucherEntity;
use App\Entities\VoucherRowEntity;

class VoucherTemplateModel extends Model
{
    protected $table            = 'company_voucher_templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['company_id', 'title', 'serie', 'external_reference'];

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

    public function Add(VoucherTemplateEntity $template)
    {
        $template->id = -1;

        $template->ValidateTemplate();
        if(count($template->validationErrors) > 0){
            return $template;
        }

        $cam = model('App\Models\CompanyBookingAccountsModel');
        if(!$cam->ensureCompanyAccountsExists($template)){
            return $template;
        }
        $missingDim = [];

        foreach($template->rows as $row){
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
            $template->validationErrors = $missingDim;
            return $template;
        }

        try {
            $this->db->transException(true)->transStart();

            //Save the template
            $this->insert($template);
            $templateID = $this->getInsertID();
            $template->SetID($templateID);

            //Save rows
            $trm = model('App\Models\VoucherTemplateRowModel');


            $trm->insertBatch($template->rows);

            $this->db->transComplete();
        } catch (DatabaseException $e) {
            $this->db->transRollback();
            $template->validationErrors = ['Ett oväntat fel uppstod när vi försökte spara mallen till databasen. Loggen är skickad till supporten.'];
            //dd($template);
            return $template;
        }

        return $template;
    }

}
