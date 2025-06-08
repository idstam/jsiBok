<?php
/** @var string $company_name */
/** @var string $sie_company_name */
/** @var string $sie_org_no */
/** @var string $sie_gen_date */
/** @var string $sie_gen_program */
/** @var string $sie_min_date */
/** @var string $sie_max_date */
/** @var array $sie_rar */
/** @var array $sie_series */
/** @var array $voucher_series */
/** @var boolean $to_new_company */

?>
<form class="container" action='/sie/import' method='post'>
    <?= csrf_field() ?>
    <fieldset>
        <legend><strong>Importera SIE-fil till <?= $company_name == '' ? 'nytt företag' : $company_name ?></strong>
        </legend>
        <input type='hidden' name='to_new_company' value='<?= $to_new_company ?>'>
        <label for="sie_company_name">Företag i filen:&nbsp;<?= $sie_company_name ?> &nbsp;<?= $sie_org_no ?></label>
        <label for="sie_company_name">Skapad:&nbsp;<?= $sie_gen_date ?> av&nbsp;<?= $sie_gen_program ?></label>
        <label for="sie_company_name">Verifikationsdatum&nbsp;<?= $sie_min_date ?>&nbsp;till och med&nbsp;<?= $sie_max_date ?></label>

        <hr>
        <?php if ($company_name === "") { ?>
            <div class="grid">
                <label for="sie_ib">Läs in ingående balanser
                    <input type="checkbox" id="sie_ib" name="sie_ib" checked
                           style="padding: calc(var(--universal-padding) / 2);"/></label>
            </div>
        <?php } ?>
        <?php
        if (count($sie_rar) > 1) foreach ($sie_rar as $rar) { ?>
            <div class="grid">
                <label for="sie_rar-<?= $rar->ID ?>">Läs in bokföringsår <?= $rar->Start->format("Ymd") ?>
                    - <?= $rar->End->format("Ymd") ?>
                    <input type="checkbox" id="sie_rar-<?= $rar->ID ?>"
                           name="sie_rar-<?= $rar->ID ?>" <?= $rar->ID == '0' ? 'disabled' : '' ?> checked
                           style="padding: calc(var(--universal-padding) / 2);"/>
                </label>
            </div>
        <?php } ?>
        <!-- <div class="row">
            <div class="col-sm-12">
                <label for="sie_ib">Använd kontonamn från filen i stället för BAS-kontoplanen</label>
                <input type="checkbox" id="sie_account_names" name="sie_account_names" style="padding: calc(var(--universal-padding) / 2);" />
            </div>
        </div> -->
        <hr>
        <?php
        foreach ($sie_series as $serie => $count) { ?>
            <!-- För serie <label><?= $serie ?></label> i filen; använd: <input type="text" list="dl_series" id="sie_serie-<?= $serie ?>" name="sie_serie-<?= $serie ?>" value="<?= $serie ?>" style="padding: calc(var(--universal-padding) / 2);" /> i bokföringen. -->
        <div class="grid">
            <label> För serie <?= $serie ?> i filen; använd:</label> <select list="dl_series"
                                                                            id="sie_serie-<?= $serie ?>"
                                                                            name="sie_serie-<?= $serie ?>"
                                                                            value="<?= $serie ?>"
                                                                            style="padding: calc(var(--universal-padding) / 2);">
                <?php
                echo('<option label="' . esc($serie) . '" value="' . esc($serie) . '" ></option>');

                foreach ($voucher_series as $vserie) {
                    if ($vserie->name !== "IB") {
                        echo('<option label="' . esc($vserie->name) . ' ' . esc($vserie->title) . '" value="' . esc($vserie->name) . '" ></option>');
                    }
                }
                ?>
            </select> i bokföringen.
        <?php } ?>
        </div>
        <div class="grid">
        <input type="submit" id="submit" name="submit" value="Importera" aria-busy="true" onclick="loading('submit')"/>
            <span></span>
        </div>
    </fieldset>
</form>