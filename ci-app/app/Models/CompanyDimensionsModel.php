<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyDimensionsModel extends Model
{
    protected $table = 'company_dimensions';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $allowedFields = [
        'company_id', 'dim_number', 'dim_code', 'title',
        'created_at','updated_at','deleted_at'
    ];

    public function getByCompanyAndType(int $companyId, int $dimNumber, ?int $limit = null, ?int $offset = null): array
    {
        $builder = $this->where('company_id', $companyId)
            ->where('dim_number', $dimNumber)
            ->orderBy('dim_code', 'ASC');
        if ($limit !== null) {
            $builder = $builder->limit($limit, $offset ?? 0);
        }
        return $builder->find();
    }

    public function countByCompanyAndType(int $companyId, int $dimNumber): int
    {
        return $this->where('company_id', $companyId)->where('dim_number', $dimNumber)->countAllResults();
    }
}
