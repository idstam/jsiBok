<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportsModel extends Model
{
    protected $table            = 'reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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

    public function balansAndResultat($companyId, $booking_year, $start, $end, $accStart, $accEnd){
        $sql = "

select
        level1,
        level2,
        account_id,
        max(account_name) as account_name,
        sum(ib_amount) as ib_amount,
        sum(is_amount) as is_amount,
        sum(p_amount) as p_amount

from (
    -- IB
    select level1.name             as level1,
             level2.name             as level2,
             cba.account_id          as account_id,
             cba.name            as account_name,
             coalesce(cab.amount, 0) as ib_amount,
             0                       as is_amount,
             0                       as p_amount
      from company_booking_accounts cba
               inner join base_account_headers level1 on (cba.account_id / 1000) = level1.number and level1.level = 1
               inner join base_account_headers level2 on (cba.account_id / 100) = level2.number and level2.level = 2
               left join company_account_balance cab
                         on cba.account_id = cab.account_id and cab.booking_year_id = @year and cab.type = 'IB'
      where cba.account_id between @accStart and @accEnd
        and cab.account_id between @accStart and @accEnd
        and cab.company_id = @company_id
      group by cba.account_id
union all
    -- IS
    select level1.name   as level1,
           level2.name   as level2,
           r.account_id  as account_id,
           cba.name            as account_name,
           0             as ib_amount,
           sum(r.amount)             as is_amount,
           0 as p_amount
    from company_voucher_rows r
             inner join base_account_headers level1 on (r.account_id / 1000) = level1.number and level1.level = 1
             inner join base_account_headers level2 on (r.account_id / 100) = level2.number and level2.level = 2
             inner join company_vouchers cv on r.voucher_id = cv.id
        inner join company_booking_accounts cba on r.account_id = cba.account_id
    where r.account_id between @accStart and @accEnd
      and cv.booking_year_id = @year
    and cv.voucher_date < @start
    and cv.company_id = @company_id
    group by r.account_id

    union all
    -- Period
    select level1.name   as level1,
           level2.name   as level2,
           r.account_id  as account_id,
           cba.name            as account_name,
           0             as ib_amount,
           0             as is_amount,
           sum(r.amount) as p_amount
    from company_voucher_rows r
             inner join base_account_headers level1 on (r.account_id / 1000) = level1.number and level1.level = 1
             inner join base_account_headers level2 on (r.account_id / 100) = level2.number and level2.level = 2
             inner join company_vouchers cv on r.voucher_id = cv.id
            inner join company_booking_accounts cba on r.account_id = cba.account_id
    where r.account_id between @accStart and @accEnd
      and cv.booking_year_id = @year
    and cv.voucher_date >= @start
    and cv.voucher_date < @end
    and cv.company_id = @company_id
    group by r.account_id

    ) as data
group by level1, level2, account_id
order by account_id

        ";

        $sql =  str_replace('@year', (strval(intval($booking_year))), $sql);
        $sql =  str_replace('@start', $this->db->escape($start), $sql);
        $sql =  str_replace('@end', $this->db->escape($end), $sql);
        $sql =  str_replace('@accStart', (strval(intval($accStart))), $sql);
        $sql =  str_replace('@accEnd', (strval(intval($accEnd))), $sql);
        $sql =  str_replace('@company_id', $this->db->escape($companyId), $sql);
        $query = $this->db->query($sql);

        //dd($booking_year, $start, $end, $booking_year, $start, $end, $booking_year, $start, $end);
        //dd($this->db->getLastQuery());

        return $query;

    }
    public function huvudbok($companyId, $booking_year, $start, $end): \CodeIgniter\Database\Query|bool|\CodeIgniter\Database\BaseResult
    {
        //<editor-fold desc="All the sql">
$sql = "

select *
from (
    select '0IB'          as row_type,
             0              as voucher_id,
             cba.account_id as account_id,
             coalesce(cab.amount, 0)     as amount,
             0              as debet,
             0              as kredit,
             ''             as ver_number,
             cba.name,
            null as voucher_date
        from company_booking_accounts cba
        left join company_account_balance cab on cba.account_id = cab.account_id and cab.booking_year_id = @year and cab.type = 'IB'
        where cab.company_id = @company_id
        and cab.amount != 0
      group by cba.account_id

      union all
      select '1IS'                                  as row_type,
             0                                      as voucher_id,
             cba.account_id as account_id,
             sum(coalesce(r.amount, 0))                        as amount,
             sum(case when r.amount >= 0 then r.amount else 0 end)       as debet,
             sum(case when r.amount < 0 then (r.amount * -1) else 0 end) as kredit,
             ''                                     as ver_number,
             cba.name,
             null as voucher_date
          from company_voucher_rows r
          inner join company_booking_accounts cba on cba.account_id = r.account_id
          inner join company_vouchers cv on r.voucher_id = cv.id and cv.booking_year_id = @year and cv.voucher_date < @start
          where cv.company_id = @company_id
      group by cba.account_id

      union all

      select '2TX'                                                  as row_type,
             cv.id                                                  as voucher_id,
             r.account_id,
             r.amount                                               as amount,
             case when r.amount >= 0 then r.amount else 0 end       as debet,
             case when r.amount < 0 then (r.amount * -1) else 0 end as kredit,
             CONCAT(cv.serie, cv.voucher_number)               as ver_number,
             cv.title                                               as name,
             cv.voucher_date
      from company_voucher_rows r
               inner join company_vouchers cv on r.voucher_id = cv.id
               inner join company_booking_years cby on cv.company_id = cby.company_id and cv.booking_year_id = cby.id
      where cby.id = @year
        and cv.voucher_date >= @start
        and cv.voucher_date < @end
        and cv.company_id = @company_id

union all
            select '9ED'                                                  as row_type,
            0,99999,null,null,null,null,null,null

) as data
order by account_id, row_type, voucher_date, ver_number;
";
//</editor-fold>

        //dd("'$booking_year'", "'$start'","'$end'");
        $sql =  str_replace('@year', (strval(intval($booking_year))), $sql);
        $sql =  str_replace('@start', $this->db->escape($start), $sql);
        $sql =  str_replace('@end', $this->db->escape($end), $sql);
        $sql =  str_replace('@company_id', $this->db->escape($companyId), $sql);

        $query = $this->db->    query($sql,);

        //dd($this->db->getLastQuery());

        return $query;

    }

    public function moms($companyId, $booking_year, $start, $end){
        //<editor-fold desc="All the sql">
        $sql = "
        select
             sum(r.amount)  as amount,
             cvs.vat as ruta
        from company_vouchers cv
        inner join company_voucher_rows r on cv.id = r.voucher_id
        inner join company_account_vat_sru cvs on cvs.account_id = r.account_id
        where cv.company_id = @company_id
        and cv.voucher_date >= @start
        and cv.voucher_date < @end

group by cvs.vat
";
//</editor-fold>

        //dd("'$booking_year'", "'$start'","'$end'");
        $sql =  str_replace('@year', (strval(intval($booking_year))), $sql);
        $sql =  str_replace('@start', $this->db->escape($start), $sql);
        $sql =  str_replace('@end', $this->db->escape($end), $sql);
        $sql =  str_replace('@company_id', $this->db->escape($companyId), $sql);

        $query = $this->db->query($sql,);

        //dd($this->db->getLastQuery());

        return $query;

    }

    public function verifikat($companyId, $booking_year, $start, $end)
    {
        //<editor-fold desc="All the sql">
        $sql = "
select
        v.id as voucher_id,
        v.company_id,
        v.voucher_date,
        v.booking_year_id,
        v.title,
        v.serie,
        v.voucher_number,
        vr.account_id,
        vr.cost_center_id,
        vr.project_id,
        vr.amount,
        CONCAT(v.serie, v.voucher_number) as ver_number,
        cba.name as account_name
from company_vouchers v
inner join company_voucher_rows vr on v.id = vr.voucher_id
inner join  company_booking_accounts cba on cba.account_id = vr.account_id and vr.account_id and vr.company_id = cba.company_id
where v.company_id = @company_id
and v.booking_year_id = @year
and v.voucher_date >= @start
and v.voucher_date < @end
order by v.id, vr.id
";
//</editor-fold>

        //dd("'$booking_year'", "'$start'","'$end'", "'$companyId'");
        $sql =  str_replace('@year', (strval(intval($booking_year))), $sql);
        $sql =  str_replace('@start', $this->db->escape($start), $sql);
        $sql =  str_replace('@end', $this->db->escape($end), $sql);
        $sql =  str_replace('@company_id', $this->db->escape($companyId), $sql);

        $query = $this->db->query($sql,);

        //dd($this->db->getLastQuery());


        return $query;

    }

}
