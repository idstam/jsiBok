<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyVoucherSeriesModel extends Model
{
    protected $table = 'company_voucher_series';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['company_id', 'name', 'title'];

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

    public function ensureVoucherSeries($companyID, $name, $title, $bookingYearID = -1)
    {
        $seriesID = 0;

        $voucherSerie = $this->where(
            [
                'company_id' => $companyID,
                'name' => $name
            ])->first();
        if (is_null($voucherSerie)) {
            $this->insert([
                'company_id' => $companyID,
                'name' => $name,
                'title' => $title,
            ]);
            $seriesID = $this->getInsertID();
        } else {
            $seriesID = $voucherSerie->id;
        }
        if($bookingYearID != -1) {
            $vsmv = model('App\Models\CompanyVoucherSeriesValuesModel');
            $vsmv->ensureVoucherSeriesValue($companyID, $seriesID, $bookingYearID);
        }
        return $seriesID;
    }

}
