<?php
/** @var array $reportQuery */
$lastLevel1 = '';
$lastLevel2 = '';

$in_amount_sum0 = '0';
$p_amount_sum0 = '0';
$ut_amount_sum0 = '0';

$in_amount_sum1 = '0';
$p_amount_sum1 = '0';
$ut_amount_sum1 = '0';

$in_amount_sum2 = '0';
$p_amount_sum2 = '0';
$ut_amount_sum2 = '0';

function format_bc($number): string
{
    $number = (new BcMath\Number($number))->round(0, RoundingMode::HalfEven);
    $tokens = str_split(strrev($number), 3);
    $ret = join(' ', $tokens);
    $ret = strrev($ret);
    $ret = str_replace(' .', ',', $ret);

    return $ret ;
}

function level1sum($title, $in_amount_sum1, $p_amount_sum1, $ut_amount_sum1): void
{ ?>
    <tr>
        <td>
            <h4 style="font-weight: bold;">Summa <?= $title ?></h4>
        </td>
        <td class="number">
            <h4 style="font-weight: bold;"><?= format_bc($in_amount_sum1) ?></h4>
        </td>
        <td class="number">
            <h4 style="font-weight: bold;"><?= format_bc($p_amount_sum1) ?></h4>
        </td>
        <td class="number">
            <h4 style="font-weight: bold;"><?= format_bc($ut_amount_sum1) ?></h4>
        </td>
    </tr>

<?php    }
function level2sum($title, $in_amount_sum2, $p_amount_sum2, $ut_amount_sum2): void
{ ?>
    <tr>
        <td style="font-weight: bold; padding-left: 2em;">Summa <?= $title ?></td>
        <td class="number">
            <label style="font-weight: bold;"><?= format_bc($in_amount_sum2) ?></label>
        </td>
        <td class="number">
            <label style="font-weight: bold;"><?= format_bc($p_amount_sum2) ?></label>
        </td>
        <td class="number">
            <label style="font-weight: bold;"><?= format_bc($ut_amount_sum2) ?></label>
        </td>
    </tr>

<?php    } ?>



    <table>
<!--        <thead>-->
<!--        <tr>-->
<!--            <th>&nbsp;</th>-->
<!--            <th>&nbsp;</th>-->
<!--            <th>&nbsp;</th>-->
<!--            <th>&nbsp;</th>-->
<!--        </tr>-->
<!--        </thead>-->
        <tbody>


    <?php


    foreach ($reportQuery    as $row) {
        $in_amount = bcadd($row->ib_amount, $row->is_amount, 2);
        $p_amount = bcadd($row->p_amount, '0', 2);
        $ut_amount = bcadd($in_amount, $p_amount, 2);


        //        $amount = bcmul($row->amount, '1', 2);
//        $debet = bcmul($row->debet, '1', 2);
//        $kredit = bcmul($row->kredit, '1', 2);

        if($lastLevel1 != $row->level1) {
            if($lastLevel2 != ''){
                level2sum($lastLevel2, $in_amount_sum2, $p_amount_sum2, $ut_amount_sum2);
                $lastLevel2 = '';
                $in_amount_sum2 = '0';
                $p_amount_sum2 = '0';
                $ut_amount_sum2 = '0';

            }
            if($lastLevel1 != ''){
                level1sum($lastLevel1, $in_amount_sum1, $p_amount_sum1, $ut_amount_sum1);
                $in_amount_sum1 = '0';
                $p_amount_sum1 = '0';
                $ut_amount_sum1 = '0';
            }

            ?>
            <tr>
                <td>
                    <h3><?= $row->level1 ?></h3>
                </td>
                <?php if($lastLevel1 == '') { ?>
                    <td>Ingående</td>
                    <td>Perioden</td>
                    <td>Utgående</td>
                <?php } ?>
            </tr>
            <?php
            $lastLevel1 = $row->level1;

        }
        if($lastLevel2 != $row->level2) {
            if($lastLevel2 != ''){
                level2sum($lastLevel2, $in_amount_sum2, $p_amount_sum2, $ut_amount_sum2);
                $in_amount_sum2 = '0';
                $p_amount_sum2 = '0';
                $ut_amount_sum2 = '0';
            }
            ?>

            <tr>
                <td>
                    <h4><?= $row->level2 ?></h4>
                </td>

            </tr>

            <?php
            $lastLevel2 = $row->level2;

        } ?>

        <tr>
                <td style="padding-left: 2em;"><?= $row->account_id ?>&nbsp;<?= $row->account_name ?></td>
                <td class="number"><?= format_bc($in_amount) ?></td>
                <td class="number"><?= format_bc($p_amount) ?></td>
                <td class="number"><?= format_bc($ut_amount) ?></td>
        </tr>


        <?php
        $in_amount_sum1 = bcadd($in_amount_sum1, $in_amount, 2);
        $p_amount_sum1 = bcadd($p_amount_sum1, $p_amount, 2);
        $ut_amount_sum1 = bcadd($ut_amount_sum1, $ut_amount, 2);

        $in_amount_sum2 = bcadd($in_amount_sum2, $in_amount, 2);
        $p_amount_sum2 = bcadd($p_amount_sum2, $p_amount, 2);
        $ut_amount_sum2 = bcadd($ut_amount_sum2, $ut_amount, 2);

        $in_amount_sum0 = bcadd($in_amount_sum0, $in_amount, 2);
        $p_amount_sum0 = bcadd($p_amount_sum0, $p_amount, 2);
        $ut_amount_sum0 = bcadd($ut_amount_sum0, $ut_amount, 2);

    }

    if ($lastLevel2 != '') {
        level2sum($lastLevel2, $in_amount_sum2, $p_amount_sum2, $ut_amount_sum2);
        $lastLevel2 = '';
        $in_amount_sum2 = '0';
        $p_amount_sum2 = '0';
        $ut_amount_sum2 = '0';

    }
    if ($lastLevel1 != '') {
        level1sum($lastLevel1, $in_amount_sum1, $p_amount_sum1, $ut_amount_sum1);
        $in_amount_sum1 = '0';
        $p_amount_sum1 = '0';
        $ut_amount_sum1 = '0';
    }
    ?>

</tbody>
</table>
</div>