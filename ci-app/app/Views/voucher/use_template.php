<?php
/** @var array $voucher_templates */

echo('<datalist id="dl_templates">');

foreach ($voucher_templates as $template) {
    echo('<option label="' . esc($template[0]) . '" value="' . esc($template[1]) . '" ></option>');
}
echo('</datalist>');

?>

<form class="container" action="/voucher/use_template" method="post">
    <?= csrf_field() ?>
    <fieldset>
        <legend><strong>Använd mall</strong></legend>
        <div class="grid">
        <label for="vtitle">Namn
        <input list="dl_templates" id="tname" name="tname" size="15"
               style="padding: calc(var(--universal-padding) / 2);"/></label>
        <label for="vdate">Belopp
        <input type="text" id="tamount" name="tamount" size="15"
               style="padding: calc(var(--universal-padding) / 2);"/></label>
        </div>
        <div class="grid">
        <input class="tertiary" type="submit" id="use_template" name="use_template" value="Använd"/>

        <strong><a  href="/voucher-template/new">Ny&nbsp;mall</a></strong>
        </div>
    </fieldset>
</form>

<hr>