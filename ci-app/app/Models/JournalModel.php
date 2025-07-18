<?php

namespace App\Models;

use CodeIgniter\Model;

class JournalModel extends Model
{
    protected $table            = 'journal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id', 'company_id', 'user_id', 'booking_year', 'title', 'details'];

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

    public function Write($title, $details){
        //$db      = \Config\Database::connect();
        $session = service('session');
        $userID = $session->get('userID');
        $companyID = $session->has('companyID') ? $session->get('companyID') : -999;
        $bookingYear = $session->has('yearID') ? $session->get('yearID') : -999;

        $data =[
            'user_id' => $userID,
            'company_id' => $companyID,
            'booking_year' => $bookingYear,
            'title' => $title,
            'details' => $details,
        ];

        $this->insert($data);


    }
}
