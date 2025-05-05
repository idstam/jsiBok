
<?php

function format_bc($number): string
{
    $number = bcmul($number, '1', 2);
    $tokens = str_split(strrev($number), 3);
    $ret = join(' ', $tokens);
    $ret = strrev($ret);
    $ret = str_replace(' .', ',', $ret);

    return $ret ;
}
?>
<table>
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Debet</th>
                <th>Kredit</th>
            </tr>
            </thead>

    <tbody>

<?php
/** @var array $reportQuery */
$lastVoucher = -1;
foreach ($reportQuery as $row){

?>

<?php if($lastVoucher !== $row->voucher_id) {
        $lastVoucher = $row->voucher_id;
?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        <?php
?>

    <tr>
        <td colspan="3">
            <strong><?= substr($row->voucher_date, 0, 10) ?>&nbsp;&nbsp;<a
                        href="/voucher/saved/<?= $row->ver_number ?>"><?= $row->ver_number ?></a>
                <a href="/voucher/saved/<?= $row->ver_number ?>"><?= esc($row->title) ?></a></strong>

        </td>

    </tr>
<?php }  ?>

    <tr>
        <td><?=  esc($row->account_id) . " " . esc($row->account_name) ?></td>
<!--        <div class="col-sm-1 col-md-1">-->
<!--            <label>--><?php //= esc($row->cost_center_id == 0 ? '' : $row->cost_center_id) ?><!--</label>-->
<!--        </div>-->
<!--        <div class="col-sm-1 col-md-1">-->
<!--            <label>--><?php //= esc($row->project_id == 0 ? '' : $row->project_id) ?><!--</label>-->
<!--        </div>-->
        <td class="number"><?= $row->amount > 0 ? format_bc(abs($row->amount)) : '' ?></td>
        <td class="number"><?= $row->amount < 0 ? format_bc(abs($row->amount)) : '' ?></td>
    </tr>


<?php

} ?>
    </tbody>
</table>
</div>