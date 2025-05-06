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
class VoucherEntity extends Entity
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

    public function Validate()
    {
        helper('jsi_helper');

        //TODO:Schedule Vouchers for the future.

        $this->validationErrors = [];
        $voucherDate = $this->attributes['voucher_date'];
        $bookingYearStart = $this->attributes['booking_year_start'];
        $bookingYearEnd = $this->attributes['booking_year_end'];

        //Voucher date should not be in the future
        $now = new \DateTime('now');
        if ($voucherDate > $now) {
            array_push($this->validationErrors, "Bokföringsdatum får inte vara i framtiden: " . date_format($voucherDate, 'Y-m-d)'));
        }

        //Voucher date should be in an active booking year
        $bym = model('App\Models\CompanyBookingYearsModel');
        $voucherBookingYear = $bym->
        where('year_start <=', ensure_date_string($voucherDate, 'Y-m-d'))->
        where('year_end >=', ensure_date_string($voucherDate, 'Y-m-d'))->first();

        if($voucherBookingYear){
            $this->attributes['booking_year_id'] = $voucherBookingYear->id;
            $this->attributes['booking_year_start'] = $voucherBookingYear->year_start;;
            $this->attributes['booking_year_end'] = $voucherBookingYear->year_end;
            $bookingYearStart = $this->attributes['booking_year_start'];
            $bookingYearEnd = $this->attributes['booking_year_end'];

            if($voucherBookingYear->active == 0){
                $a = ensure_date($voucherDate, 'Y-m-d');
                $b = ensure_date($bookingYearStart, 'Y-m-d');
                $c = ensure_date($bookingYearEnd, 'Y-m-d');
                //dd($a, $b, $c);
                array_push($this->validationErrors,"Bokföringsåret (" . $b . " - " .$c . ") är stängt.");
            }

        } else{
            array_push($this->validationErrors,"Det finns inget bokföringsår för " . ensure_date_string($voucherDate, 'Y-m-d'));
        }

//        //Voucher date should be within the current booking year
//        if ($voucherDate < $bookingYearStart || $voucherDate > $bookingYearEnd) {
//            $a = ensure_date($voucherDate, 'Y-m-d');
//            $b = ensure_date($bookingYearStart, 'Y-m-d');
//            $c = ensure_date($bookingYearEnd, 'Y-m-d');
//            //dd($a, $b, $c);
//            array_push($this->validationErrors, "Bokföringsdatum," .
//                $a .
//                " , måste vara inom aktuellt bokföringsår: " .
//                $b . " - " .
//                $c);
//        }

        //There should be at least two rows.
        if (count($this->attributes['rows']) < 2) {
            array_push($this->validationErrors, "Det behövs minst två rader får att få ihop ett verifikat.");
        }
        //The amounts of all rows should sum to zero
        $sum = 0;
        foreach ($this->attributes['rows'] as $row) {
            $sum = bcadd($sum, $row->amount, 2);
        }
        if (bccomp($sum, "0", 2) !== 0) {
            array_push($this->validationErrors, "Verifikatet är inte i balans. Diff: $sum");
        }

        //Serie should be set to a non empty string
        if ($this->attributes['serie'] == "") {
            array_push($this->validationErrors, "Serie saknas.");
        }
        //Source should be set to a non empty string
        if ($this->attributes['source'] == "") {
            array_push($this->validationErrors, "Källa saknas. (internt programfel)");
        }
        //Title should be set to a non empty string
        if ($this->attributes['title'] == "") {
            array_push($this->validationErrors, "Rubrik saknas.");
        }

        return $this->validationErrors;
    }

    public function SetID($id): void
    {
        $this->attributes['id'] = $id;

        foreach ($this->attributes['rows'] as $row) {
            $row->voucher_id = $id;
        }
    }

    public function Revert($userID): void
    {
        $this->user_id = $userID;
        $this->source = 'Ångrad';
        $this->title = '[Ångrad] ' . $this->title;
        $rows = [];
        foreach ($this->attributes['rows'] as $row) {
            $row->amount = -1 * $row->amount;
            array_push($rows, $row);
        }
        $this->attributes['rows'] = $rows;
    }
}
