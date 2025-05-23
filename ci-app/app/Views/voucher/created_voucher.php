<?php
/** @var object $voucher */
if(!function_exists("format_bc")) {
    function format_bc($number): string
    {
        $tokens = str_split(strrev($number), 3);
        $ret = join(' ', $tokens);
        $ret = strrev($ret);
        $ret = str_replace(' .', ',', $ret);
        $ret = str_replace(' ', '&nbsp;', $ret);
        return $ret;
    }
}
?>

<div class="container">
    <fieldset>
        <legend><strong>Verifikat</strong></legend>
        <label>Nummer:&nbsp;<?= esc($voucher->serie) . ' ' . esc($voucher->voucher_number) ?></label>

        <label>Datum:&nbsp;<?= esc($voucher->voucher_date) ?></label>
        <label>Rubrik:&nbsp;<?= esc($voucher->title) ?></label>
        <hr/>
        <table>
            <thead>
            <tr>
                <th>Konto</th>
                <th>Kostnadsställe</th>
                <th>Projekt</th>
                <th>Debet</th>
                <th>Kredit</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($voucher->rows as $row) { ?>
                    <tr>
                <td><?= esc($row->account_id)?></td>
                <td><?= esc($row->cost_center_id == 0 ? '' : $row->cost_center_id) ?></td>
                <td><?= esc($row->project_id == 0 ? '' : $row->project_id) ?></td>
                <td class="number"><?= $row->amount > 0 ? format_bc(abs(esc($row->amount))) : '' ?></td>
                <td class="number"><?= $row->amount < 0 ? format_bc(abs(esc($row->amount))) : '' ?></td>
                    </tr>
            <?php } ?>
            </tbody></table>

        <div class="row">
            <div class="col-sm-1 col-md-1">
                <a href="/voucher/revert/<?= esc($voucher->serie) . esc($voucher->voucher_number) ?>">
                    <button>Ångra</button>
                </a>

            </div>
        </div>
    </fieldset>

</div>