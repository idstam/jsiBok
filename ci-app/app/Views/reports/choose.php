<?php
$months = array(
    1 => 'January',
    2 => 'Februari',
    3 => 'Mars',
    4 => 'April',
    5 => 'Maj',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Augusti',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'December'
);
?>

<form class="container" action="/reports/choose" method="get">

    <fieldset>
        <legend><strong>Rapporter</strong></legend>
        <label for="booking_year">Bokföringsår</label>
        <select id="booking_year" name="booking_year">
            <?php
            /** @var array $years */
            foreach (array_reverse($years) as $year) {
                echo '<option value="' . $year->id . '">' . date_format(date_create(esc($year->year_start)), 'Y-m-d') . '</option>';
            }
            ?>
        </select>
        <label for="from_period">Period</label>
        <select id="from_period" name="from_period">
            <option value="-1" selected>Hela året</option>
            <option value="-2" >Kvartal 1</option>
            <option value="-3" >Kvartal 2</option>
            <option value="-4" >Kvartal 3</option>
            <option value="-5" >Kvartal 4</option>
            <?php
            /** @var int $startMonth */
            for ($i = 0; $i <= 11; $i++) {
                $m = $startMonth + $i;
                if ($m > 12) {
                    $m = $m - 12;
                }

                echo('<option value="' . $i . '" >' . $months[$m] . '</option>');

            } ?>

        </select>
        <div class="grid">
            <input class="primary" type="submit" id="submitMoms" name="submitMoms" value="Moms"/>
            <input class="primary" type="submit" id="submitVerifikat" name="submitVerifikat" value="Verifikat"/>

            <input class="primary" type="submit" id="submit1" name="submit1" value="Huvudbok"/>
            <input class="primary" type="submit" id="submit2" name="submit2" value="Resultaträkning"/>
            <input class="primary" type="submit" id="submit3" name="submit3" value="Balansräkning"/>
        </div>
    </fieldset>

</form>

