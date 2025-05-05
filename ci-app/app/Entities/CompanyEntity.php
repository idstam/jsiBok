<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property string|null $org_no
 * @property string|null $name
 * @property string|null $number
 * @property string|null $owner_id
 * @property string|null $owner_email
 */
class CompanyEntity extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'org_no' => null,
        'number' => null,
        'owner_id' => null,
        'owner_email' => null,
        'user_role' => null
    ];
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
}
