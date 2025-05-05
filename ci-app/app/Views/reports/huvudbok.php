
    <table>
        <thead>
        <tr>
            <th>Konto</th>
            <th>&nbsp;</th>
            <th>Debet</th>
            <th>Kredit</th>
            <th>Saldo</th>
        </tr>
        </thead>
        <tbody>
        <?php

        function format_bc($number): string
        {
            $tokens = str_split(strrev($number), 3);
            $ret = join(' ', $tokens);
            $ret = strrev($ret);
            $ret = str_replace(' .', ',', $ret);
            $ret = str_replace(' ', '&nbsp;', $ret);
            return $ret;
        }

        /** @var array $reportQuery */
        /** @var array $accountNames */

        $seenIS = false;
        $seenIB = false;
        $seenTX = false;
        $lastAccount = -10;
        $amount = '0';
        $periodAmount = '0';
        $periodDebet = '0';
        $periodKredit = '0';

        $periodInAmount = '0';
        $periodInDebet = '0';
        $periodInKredit = '0';

        $periodIbAmount = '0';
        $periodIbDebet = '0';
        $periodIbKredit = '0';

        $transSaldo = '0';
        foreach ($reportQuery as $row) {
            if ($row->row_type == '4UB') {
                continue;
            }

            if ($row->account_id !== $lastAccount) {
                if ($seenTX) { ?>
                    <tr>
                        <td><i>Omslutning</i></td>
                        <td>&nbsp;</td>
                        <td class="number"><i><?= format_bc($periodDebet) ?></i></td>
                        <td class="number"><i><?= format_bc($periodKredit) ?></i></td>
                        <td class="number"><i><?= format_bc($periodAmount) ?></i></td>
                    </tr>

                <?php }

                if ($seenTX || $seenIS || $seenIB) {
//                $ubAmount = bcadd($periodAmount, $amount, 2);
//                $ubDebet = bcadd($periodDebet, $debet, 2);
//                $ubKredit = bcadd($periodKredit, $kredit, 2);
                    $ubAmount = bcadd($periodInAmount, $periodAmount, 2);
                    $ubAmount = bcadd($periodIbAmount, $ubAmount, 2);
                    ?>
                    <tr>
                        <td>Utgående saldo</td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td class="number"><?= format_bc($ubAmount) ?></td>
                    </tr>
                <?php }

                if ($row->row_type == '9ED') {
                    break;
                }
                $seenIS = false;
                $seenTX = false;
                $seenIB = false;
                $lastAccount = $row->account_id;

                $periodAmount = bcadd('0', '0', 2);
                $periodDebet = bcadd('0', '0', 2);
                $periodKredit = bcadd('0', '0', 2);

                $periodInAmount = bcadd('0', '0', 2);
                $periodInDebet = bcadd('0', '0', 2);
                $periodInKredit = bcadd('0', '0', 2);

                $periodIbAmount = bcadd('0', '0', 2);
                $periodIbDebet = bcadd('0', '0', 2);
                $periodIbKredit = bcadd('0', '0', 2);

                $transSaldo = bcadd('0', '0', 2);

                $ubAmount = bcadd('0', '0', 2);

                ?>
                <tr>
                    <td><strong><?= $row->account_id ?></strong></td>
                    <td colspan="4"><strong><?= $accountNames[$row->account_id] ?></strong></td>
                </tr>

                <?php
            }

            $amount = bcmul($row->amount, '1', 2);
            $debet = bcmul($row->debet, '1', 2);
            $kredit = bcmul($row->kredit, '1', 2);

            ?>


            <?php if ($row->row_type == '0IB') {
                $seenIB = true;

                $periodIbAmount = $amount;
                $periodIbDebet = $debet;
                $periodIbKredit = $kredit;
                $transSaldo = bcadd($transSaldo, $amount, 2);

                ?>

                <tr>
                    <td>Ingående balans</td>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td class="number"><?= format_bc($amount) ?></td>
                </tr>
            <?php }

            if ($row->row_type == '1IS') {
                $seenIS = true;
                $seenIB = true;
                $periodInAmount = bcadd($periodInAmount, $amount, 2);
                $periodInDebet = bcadd($periodInDebet, $debet, 2);
                $periodInKredit = bcadd($periodInKredit, $kredit, 2);
                $transSaldo = bcadd($transSaldo, $amount, 2);
                ?>

                <tr>
                    <td>Ingående saldo</td>
                    <td>&nbsp;</td>
                    <td class="number"><?= format_bc($debet) ?></td>
                    <td class="number"><?= format_bc($kredit) ?></td>
                    <td class="number"><?= format_bc($amount) ?></td>
                </tr>
            <?php }

            if ($row->row_type == '2TX') {
                $seenTX = true;
                $transSaldo = bcadd($transSaldo, $amount, 2);

                $periodAmount = bcadd($periodAmount, $amount, 2);
                $periodDebet = bcadd($periodDebet, $debet, 2);
                $periodKredit = bcadd($periodKredit, $kredit, 2);

                if (!$seenIS) {
                    $seenIS = true;
                    //$transSaldo = bcadd($periodInAmount, $transSaldo, 2);
                    ?>
                    <tr>
                        <td>Ingående saldo</td>
                        <td>&nbsp;</td>
                        <td class="number">0.00</td>
                        <td class="number">0.00</td>
                        <td class="number">0.00</td>
                    </tr>
                <?php }
                ?>

                <tr>
                    <td><?= substr($row->voucher_date, 0, 10) ?>&nbsp;&nbsp;<a
                                href="/voucher/saved/<?= $row->ver_number ?>"><?= $row->ver_number ?></a></td>
                    <td><a href="/voucher/saved/<?= $row->ver_number ?>"><?= $row->name ?></a></td>

                    <td class="number"><?= format_bc($debet) ?></td>
                    <td class="number"><?= format_bc($kredit) ?></td>
                    <td class="number"><?= format_bc($transSaldo) ?></td>
                </tr>
            <?php }
        } ?>
        </tbody>
    </table>
    </div>
