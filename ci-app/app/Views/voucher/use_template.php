<?php
/** @var array $voucher_templates */
?>

<form class="container" action="/voucher/use_template" method="post">
    <?= csrf_field() ?>
    <fieldset>
        <legend><strong>Använd mall</strong></legend>
        <div class="grid">
        <label for="tname">Namn
        <select id="tname" name="tname" >
            <?php foreach ($voucher_templates as $template): ?>
                <option value="<?= esc($template[1]) ?>"><?= esc($template[0]) ?></option>
            <?php endforeach; ?>
        </select></label>
        <label for="vdate">Belopp
        <input type="text" id="tamount" name="tamount" size="15"
               /></label>
        </div>
        <div class="grid">
        <input class="tertiary" type="submit" id="use_template" name="use_template" value="Använd"/>

        <strong><a  href="/voucher-template/new">Ny&nbsp;mall</a></strong>
        </div>
    </fieldset>
</form>

<hr>
