<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
/**
 * @property int|null $company_id
 * @property $user_id
 * @property int|null $booking_year_id
 * @property \DateTime|null $booking_year_start
 * @property \DateTime|null $booking_year_end
 * @property \DateTime|null $voucher_date
 * @property string $title
 * @property string|null $serie
 * @property string $source
 * @property array|null $rows
 * @property string|null $external_reference
 */
class VoucherTemplateEntity extends Entity
{
    protected $attributes = [

        'id' => null,
        'company_id' => null,
        'user_id' => null,
        'booking_year_id' => null,
        'booking_year_start' => null,
        'booking_year_end' => null,
        'voucher_date' => null,
        'title' => null,
        'serie' => null,
        'voucher_number' => null,
        'external_reference' => null,
        'source' => null,
        'rows' => []
    ];

    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [];

    public array $validationErrors = [];

    public function SetID(int $id): void
    {
        $this->attributes['id'] = $id;

        foreach ($this->attributes['rows'] as $row) {
            $row->template_id = $id;
        }
    }
    public function ValidateTemplate(): array
    {
        helper('jsi_helper');

        $this->validationErrors = [];

        //There should be at least two rows.
//        if (count($this->attributes['rows']) < 2) {
//            array_push($this->validationErrors, "Det behövs minst två rader får att få ihop ett verifikat.");
//        }
        //The amounts of all rows should sum to zero
//        $sum = 0;
//        foreach ($this->attributes['rows'] as $row) {
//            $sum = bcadd($sum, $row->amount, 2);
//        }
//        if (bccomp($sum, "0", 2) !== 0) {
//            array_push($this->validationErrors, "Verifikatet är inte i balans. Diff: $sum");
//        }

        //Serie should be set to a non empty string
        if ($this->attributes['serie'] == "") {
            array_push($this->validationErrors, "Serie saknas.");
        }
//        //Source should be set to a non empty string
//        if ($this->attributes['source'] == "") {
//            array_push($this->validationErrors, "Källa saknas. (internt programfel)");
//        }
        //Title should be set to a non empty string
        if ($this->attributes['title'] == "") {
            array_push($this->validationErrors, "Rubrik saknas.");
        }

        return $this->validationErrors;
    }

}
