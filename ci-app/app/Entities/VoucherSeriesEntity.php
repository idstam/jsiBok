<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property string|null $title
 * @property string|null $name
 */
class VoucherSeriesEntity extends Entity
{
    protected $attributes = [
        'id'  => null,
        'company_id'  => null,
        'name'  => null,
        'title'  => null,
    ];
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}
