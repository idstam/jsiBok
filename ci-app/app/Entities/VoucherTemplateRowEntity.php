<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property float|int|object|string|null $amount
 * @property int|null $account_id
 * @property int|null $company_id
 * @property int|null $cost_center_id
 * @property int|null $project_id
 */
class VoucherTemplateRowEntity extends Entity
{
    protected $attributes = [
        'id'  => null,
        'template_id'  => null,
        'company_id'  => null,
        'account_id'  => null,
        'cost_center_id'  => null,
        'project_id'  => null,
        'debet_amount'  => null,
        'kredit_amount'  => null,
    ];
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];

    public function setAmountFromPost($debet, $kredit){
        if($debet != '' && $debet != '0' && $debet !== null){
            if(bccomp($debet, 0, 2) == -1){
                $debet = str_replace('-', '', $debet);
            }
            $this->attributes['amount'] = $debet;
            return;
        }
        if($kredit != '' && $kredit != '0' && $kredit !== null){
            if(bccomp($kredit, 0, 2) == 1){
                $kredit = '-' . $kredit;
            }
            $this->attributes['amount'] = $kredit;
            return;
        }
    }

    public function setTemplateAmountFromPost(string $debet,string $kredit){
        if($debet != '' && $debet != '0' && $debet !== null){
            $this->attributes['debet_amount'] = $debet;
            return;
        }
        if($kredit != '' && $kredit != '0' && $kredit !== null){
            $this->attributes['kredit_amount'] = $kredit;
            return;
        }
    }

}
