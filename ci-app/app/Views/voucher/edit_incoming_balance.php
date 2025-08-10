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

echo('<datalist id="dl_accounts">');
/** @var array $accounts */
foreach ($accounts as $account) {
    echo('<option label="' . esc($account->account_id) . " " . esc($account->name) . '" value="' . esc($account->account_id) . '" ></option>');
}
echo('</datalist>');

echo('<datalist id="dl_cost_centers">');
/** @var array $cost_centers */
foreach ($cost_centers as $cost_center) {
    echo('<option label="' . esc($cost_center[0]) . " " . esc($cost_center[1]) . '" value="' . esc($cost_center[0]) . '" ></option>');
}
echo('</datalist>');

echo('<datalist id="dl_projects">');
/** @var array $projects */
foreach ($projects as $project) {
    echo('<option label="' . esc($project[0]) . " " . esc($project[1]) . '" value="' . esc($project[0]) . '" ></option>');
}
echo('</datalist>');

/** @var object $voucher */
/** @var array $values */

?>


<form class="container" action="/voucher/save-incoming-balance" method="post">
    <?= csrf_field() ?>
    <fieldset>
        <table>
            <thead>
            <tr>
                <th><b>Konto</b></th>
                <th><b>Kostnadsställe</b></th>
                <th><b>Projekt</b></th>
                <th><b>Debet</b></th>
                <th><b>Kredit</b></th>
            </tr>
            </thead>

            <!-- Iterate over voucher rows if they exist, otherwise use default rows -->
            <tbody>
            <?php if (isset($voucher->rows) && count($voucher->rows) > 0): ?>
                <?php foreach ($voucher->rows as $index => $row): ?>
                <tr class="voucher_row">
                    <th><input list="dl_accounts" id="vr_account-<?= $index ?>" name="vr_account-<?= $index ?>" class="voucher_row_field" size="10"
                               value="<?= esc($row->account_id) ?>" /></th>
                    <th><input type="text" id="vr_costcenter-<?= $index ?>" name="vr_costcenter-<?= $index ?>" class="voucher_row_field" size="10"
                               value="<?= ($row->cost_center_id == 0) ? '' : esc($row->cost_center_id) ?>" /></th>
                    <th><input type="text" id="vr_project-<?= $index ?>" name="vr_project-<?= $index ?>" class="voucher_row_field" size="10"
                               value="<?= ($row->project_id == 0) ? '' : esc($row->project_id) ?>" /></th>
                    <th><input type="text" id="vr_debet-<?= $index ?>" name="vr_debet-<?= $index ?>" class="voucher_row_field number" size="15"
                               value="<?= $row->amount > 0 ? format_bc($row->amount) : '' ?>" /></th>
                    <th><input type="text" id="vr_kredit-<?= $index ?>" name="vr_kredit-<?= $index ?>" class= "voucher_row_field number" size="15"
                               value="<?= $row->amount < 0 ? format_bc(abs($row->amount)) : '' ?>" /></th>
                    <th>
                        <?php if ($index === count($voucher->rows) - 1): ?>
                            <input id="incoming_balance_new_row_01" class="primary new_row_button" type="button" name="new_row" value="Ny rad" />
                        <?php endif; ?>
                    </th>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="voucher_row">
                    <th><input list="dl_accounts" id="vr_account-0" name="vr_account-0" class="voucher_row_field" size="10"/></th>
                    <th><input type="text" id="vr_costcenter-0" name="vr_costcenter-0" class="voucher_row_field" size="10"/></th>
                    <th><input type="text" id="vr_project-0" name="vr_project-0" class="voucher_row_field" size="10"/></th>
                    <th><input type="text" id="vr_debet-0" name="vr_debet-0" class="voucher_row_field number" size="15"/></th>
                    <th><input type="text" id="vr_kredit-0" name="vr_kredit-0" class="voucher_row_field number" size="15"/></th>
                    <th>
                    </th>
                </tr>
                <tr class="voucher_row">
                    <th><input list="dl_accounts" id="vr_account-1" name="vr_account-1" class="voucher_row_field" size="10"/></th>
                    <th><input type="text" id="vr_costcenter-1" name="vr_costcenter-1" class="voucher_row_field" size="10"/></th>
                    <th><input type="text" id="vr_project-1" name="vr_project-1" class="voucher_row_field" size="10"/></th>
                    <th><input type="text" id="vr_debet-1" name="vr_debet-1" class="voucher_row_field number" size="15"/></th>
                    <th><input type="text" id="vr_kredit-1" name="vr_kredit-1" class="voucher_row_field number" size="15"/></th>
                    <th><input id="incoming_balance_new_row_02" class="primary new_row_button" type="button" name="new_row" value="Ny rad" /></th>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="grid">
            <input class="tertiary" type="submit" id="submit" name="submit" value="Spara"/>

            <span>&nbsp;</span>
        </div>
    </fieldset>
</form>
