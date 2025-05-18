<?php
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

echo('<datalist id="dl_templates">');
/** @var array $voucher_templates */
foreach ($voucher_templates as $template) {
    echo('<option label="' . esc($template[0]) . '" value="' . esc($template[1]) . '" ></option>');
}
echo('</datalist>');

echo('<datalist id="dl_series">');
/** @var array $voucher_series */
foreach ($voucher_series as $serie) {
    echo('<option label="' . esc($serie->name) . ' ' . esc($serie->title) . '" value="' . esc($serie->name) . '" ></option>');
}
echo('</datalist>');

/** @var object $voucher */
/** @var array $values */

?>


<form class="container" action="/voucher/save" method="post">
    <?= csrf_field() ?>
    <fieldset>
        <legend><strong>Verifikat</strong></legend>
        <div class="grid">
            <label for="vserie">Serie
        <input type="text" list="dl_series" id="vserie" name="vserie"
               value="<?= esc($values['default_series']->string_value) ?>"
               style="padding: calc(var(--universal-padding) / 2);"/></label>
            <label for="vdate">Datum
        &nbsp;<input type="text" id="vdate" name="vdate" autofocus required
                     value="<?= date('Y-m-d') ?>" placeholder="yyyy-mm-dd"
                     style="padding: calc(var(--universal-padding) / 2);"/></label>
        </div>
        <label for="vtitle">Rubrik</label>
        <input type="text" id="vtitle" name="vtitle" required value="<?= esc($voucher->title) ?>"
               style="padding: calc(var(--universal-padding) / 2);"/>

        <hr/>
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
                               value="<?= esc($row->account_id) ?>" style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_costcenter-<?= $index ?>" name="vr_costcenter-<?= $index ?>" class="voucher_row_field" size="10"
                               value="<?= ($row->cost_center_id == 0) ? '' : esc($row->cost_center_id) ?>" style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_project-<?= $index ?>" name="vr_project-<?= $index ?>" class="voucher_row_field" size="10"
                               value="<?= ($row->project_id == 0) ? '' : esc($row->project_id) ?>" style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_debet-<?= $index ?>" name="vr_debet-<?= $index ?>" class="voucher_row_field" size="15"
                               value="<?= $row->amount > 0 ? esc($row->amount) : '' ?>" style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_kredit-<?= $index ?>" name="vr_kredit-<?= $index ?>" class="voucher_row_field" size="15"
                               value="<?= $row->amount < 0 ? esc(abs($row->amount)) : '' ?>" style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th>
                        <?php if ($index === count($voucher->rows) - 1): ?>
                            <input class="primary new_row_button" type="button" name="new_row" value="Ny rad" onclick="new_voucher_row();"/>
                        <?php endif; ?>
                    </th>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr class="voucher_row">
                    <th><input list="dl_accounts" id="vr_account-0" name="vr_account-0" class="voucher_row_field" size="10"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_costcenter-0" name="vr_costcenter-0" class="voucher_row_field" size="10"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_project-0" name="vr_project-0" class="voucher_row_field" size="10"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_debet-0" name="vr_debet-0" class="voucher_row_field" size="15"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_kredit-0" name="vr_kredit-0" class="voucher_row_field" size="15"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th>
                    </th>
                </tr>
                <tr class="voucher_row">
                    <th><input list="dl_accounts" id="vr_account-1" name="vr_account-1" class="voucher_row_field" size="10"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_costcenter-1" name="vr_costcenter-1" class="voucher_row_field" size="10"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_project-1" name="vr_project-1" class="voucher_row_field" size="10"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_debet-1" name="vr_debet-1" class="voucher_row_field" size="15"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input type="text" id="vr_kredit-1" name="vr_kredit-1" class="voucher_row_field" size="15"
                               style="padding: calc(var(--universal-padding) / 2);"/></th>
                    <th><input class="primary new_row_button" type="button" name="new_row" value="Ny rad" onclick="new_voucher_row();"/>
                    </th>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="grid">
            <input class="tertiary" type="submit" id="submit" name="submit" value="Bokför"/>

            <span>&nbsp;</span>
        </div>



    </fieldset>

</form>
