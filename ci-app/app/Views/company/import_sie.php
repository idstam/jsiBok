<?php
/** @var ARRAY $companies */
$toNewCompanyChecked = count($companies) == 0 ? " checked disabled " : '';
?>

<form class="container" action="/sie/validate" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <fieldset >
        <legend><strong>Importera SIE-fil <?=
                count($companies) == 0 ? 'till nytt företag' : '' ?></strong></legend>
    <div class="grid">
        <input type="file" id="sie_file" name="sie_file" size="15"
               />

            <label for="chk_to_new_company">Till&nbsp;nytt&nbsp;företag
                <input id="chk_to_new_company"
                       name="chk_to_new_company"
                       type="checkbox" <?= $toNewCompanyChecked ?> />
            </label>
        <input class="primary" type="submit" id="import_sie" name="import_sie" value="Importera"/>
    </div>
    </fieldset>
</form>
<hr>