<?php
/** @var object $company */
/** @var array $booking_years */

/** @var string $new_year_start */
/** @var string $new_year_end */
/** @var array $voucher_series */
/** @var array $values */
/** @var string $active_plan */

$icons = new \Feather\IconManager();

?>


<form class="container" action="/company/edit" method="post">
    <?= csrf_field() ?>
    <fieldset >
    <legend><strong>Inställningar för <?=
            esc($company->name) ?></strong></legend>
            <label for="booking_year">Aktivt bokföringsår</label>
        <div class="grid">
            <select id="booking_year_001" name="booking_year">
                <?php
                foreach ($booking_years as $year) { ?>
                    <option value="<?=$year->id ?>"  <?= esc($year->active) == 1 ? 'selected' : '' ?>><?= date_format(date_create(esc($year->year_start)), 'Y-m-d') ?></option>
                <?php } ?>
            </select>
            <strong><a  href="/voucher/incoming-balance">Ingående balanser</a></strong>
        </div>
            <label for="new_booking_year_start">Nytt bokföringsår</label>
        <div class="grid">
            <label for="from">Från</label>
            <label for="to">Till</label>
        </div>

        <div class="grid">
            <input type="date" id="new_booking_year_start" name="new_booking_year_start" />
            <input type="date" id="new_booking_year_end" name="new_booking_year_end" />
        </div>

            <label for="default_series">Manuell verifikationsserie</label>
            <select id="default_series" name="default_series">
                <?php
                foreach ($voucher_series as $serie) { ?>
                    <option value="<?= $serie->name ?>" <?=
                    $serie->name == $values["default_series"]->string_value ? 'selected' : '' ?>><?= $serie->name . ' - ' . $serie->title ?></option>
                <?php } ?>
                <!-- <option><?= esc($values["default_series"]->string_value) ?></option> -->
            </select>
            <label for="moms_period">Momsperiod</label>
            <select id="moms_period" name="moms_period" >
                <?php
                $periods = ['År', 'Kvartal', 'Månad'];
                foreach ($periods as $period) { ?>
                    <option value="<?=$period?>"  <?= $period == $values['moms_period']->string_value ? 'selected' : '' ?>> <?=$period?> </option>
                <?php } ?>
            </select>
            <label for="price_plan">Prisplan</label>
            <select id="price_plan" name="price_plan" disabled>
                <?php
                $periods = ['Gratis', 'Plus', 'Premium'];

                foreach ($periods as $period) { ?>
                    <option value="<?=$period?>"  <?= $period == $values['price_plan']->string_value ? 'selected' : '' ?>> <?=$period?> </option>
                <?php } ?>
            </select>

                <input class="tertiary" type="submit" id="spara" name="spara" value="Spara" />


</fieldset>

</form>
<hr>
