
<?php

function format_bc($number): string
{
    $tokens = str_split(strrev($number), 3);
    $ret = join(' ', $tokens);
    $ret = strrev($ret);
    $ret = str_replace(' .', ',', $ret);

    return $ret;
}
/** @var array $reportQuery */
/** @var array $accountNames */


$ruta = array_fill(0, 100, '0');

foreach ($reportQuery as $row) {
    $num = (new BcMath\Number($row->amount))->round(0, RoundingMode::HalfEven);
    $ruta[$row->ruta] = $num;
    //$ruta[$row->ruta] = bcadd($row->amount, 0, 0);

}
//Det blir plus här eftersom det är kreditvärden från databasen.
$ruta[49] = $ruta[48] + $ruta[60] + $ruta[61] + $ruta[62] + $ruta[30] + $ruta[31] + $ruta[32];


?>

<table>
<!--    <thead>-->
<!--    <tr>-->
<!--        <th>Section</th>-->
<!--        <th>Code</th>-->
<!--        <th>Description</th>-->
<!--        <th>Value</th>-->
<!--    </tr>-->
<!--    </thead>-->
    <tbody>
    <tr>
        <td colspan="3"><strong>Momspliktig försäljning eller uttag exklusive moms</strong></td>
    </tr>
    <tr>
        <td>05</td>
        <td>Momspliktig försäljning som inte ingår i ruta 06, 07 eller 08</td>
        <td class="number"><?= format_bc($ruta[5]) ?></td>
    </tr>
    <tr>
        <td>06</td>
        <td>Momspliktiga uttag</td>
        <td class="number"><?= format_bc($ruta[6]) ?></td>
    </tr>
    <tr>
        <td>07</td>
            <td>Beskattningsunderlag vid vinstmarginalbeskattning</td>
        <td class="number"><?= format_bc($ruta[7]) ?></td>
    </tr>
    <tr>
        <td>08</td>
        <td>Hyresinkomster vid frivillig skattskyldighet</td>
        <td class="number"><?= format_bc($ruta[8]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Utgående moms på försäljning eller uttag i ruta 05 - 08</strong></td>
    </tr>
    <tr>
        <td>10</td>
        <td>Utgående moms 25%</td>
        <td class="number"><?= format_bc($ruta[10]) ?></td>
    </tr>
    <tr>
        <td>11</td>
        <td>Utgående moms 12%</td>
        <td class="number"><?= format_bc($ruta[11]) ?></td>
    </tr>
    <tr>
        <td>12</td>
        <td>Utgående moms 6%</td>
        <td class="number"><?= format_bc($ruta[12]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Momspliktiga inköp vid omvänd skattskyldighet</strong></td>
    </tr>
    <tr>
        <td>20</td>
        <td>Inköp av varor från ett annat EU-land</td>
        <td class="number"><?= format_bc($ruta[20]) ?></td>
    </tr>
    <tr>
        <td>21</td>
        <td>Inköp av tjänster från ett annat EU-land enligt huvudregeln</td>
        <td class="number"><?= format_bc($ruta[21]) ?></td>
    </tr>
    <tr>
        <td>22</td>
        <td>Inköp av tjänster från ett land utanför EU</td>
        <td class="number"><?= format_bc($ruta[22]) ?></td>
    </tr>
    <tr>
        <td>23</td>
        <td>Inköp av varor i Sverige</td>
        <td class="number"><?= format_bc($ruta[23]) ?></td>
    </tr>
    <tr>
        <td>24</td>
        <td>Övriga inköp av tjänster</td>
        <td class="number"><?= format_bc($ruta[24]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Utgående moms på inköp i ruta 20 - 24</strong></td>
    </tr>
    <tr>
        <td>30</td>
        <td>Utgående moms 25%</td>
        <td class="number"><?= format_bc(-$ruta[30]) ?></td>
    </tr>
    <tr>
        <td>31</td>
        <td>Utgående moms 12%</td>
        <td class="number"><?= format_bc(-$ruta[31]) ?></td>
    </tr>
    <tr>
        <td>32</td>
        <td>Utgående moms 6%</td>
        <td class="number"><?= format_bc(-$ruta[32]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Import</strong></td>
    </tr>
    <tr>
        <td>50</td>
        <td>Beskattningsunderlag vid import</td>
        <td class="number"><?= format_bc($ruta[50]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Utgående moms på import i ruta 50</strong></td>
    </tr>
    <tr>
        <td>60</td>
        <td>Utgående moms 25%</td>
        <td class="number"><?= format_bc(-$ruta[60]) ?></td>
    </tr>
    <tr>
        <td>61</td>
        <td>Utgående moms 12%</td>
        <td class="number"><?= format_bc(-$ruta[61]) ?></td>
    </tr>
    <tr>
        <td>62</td>
        <td>Utgående moms 6%</td>
        <td class="number"><?= format_bc(-$ruta[62]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Försäljning m.m. som är undantagen från moms</strong></td>
    </tr>
    <tr>
        <td>35</td>
        <td>Försäljning av varor till ett annat EU-land</td>
        <td class="number"><?= format_bc($ruta[35]) ?></td>
    </tr>
    <tr>
        <td>36</td>
        <td>Försäljning av varor utanför EU</td>
        <td class="number"><?= format_bc($ruta[36]) ?></td>
    </tr>
    <tr>
        <td>37</td>
        <td>Mellanmans inköp av varor vid trepartshandel</td>
        <td class="number"><?= format_bc($ruta[37]) ?></td>
    </tr>
    <tr>
        <td>38</td>
        <td>Mellanmans försäljning av varor vid trepartshandel</td>
        <td class="number"><?= format_bc($ruta[38]) ?></td>
    </tr>
    <tr>
        <td>39</td>
        <td>Försäljning av tjänster till näringsidkare i annat EU-land enligt huvudregeln</td>
        <td class="number"><?= format_bc($ruta[39]) ?></td>
    </tr>
    <tr>
        <td>40</td>
        <td>Övrig försäljning av tjänster omsatta utom landet</td>
        <td class="number"><?= format_bc($ruta[40]) ?></td>
    </tr>
    <tr>
        <td>41</td>
        <td>Försäljning när köparen är skattskyldig i Sverige</td>
        <td class="number"><?= format_bc($ruta[41]) ?></td>
    </tr>
    <tr>
        <td>42</td>
        <td>Övrig försäljning m.m.</td>
        <td class="number"><?= format_bc($ruta[42]) ?></td>
    </tr>
    <tr>
        <td colspan="3">Ingående moms</td>
    </tr>
    <tr>
        <td>48</td>
        <td>Ingående moms att dra av</td>
        <td class="number"><?= format_bc($ruta[48]) ?></td>
    </tr>
    <tr>
        <td colspan="3"><strong>Moms att betala eller få tillbaka</strong></td>
    </tr>
    <tr>
        <td>49</td>
        <td>Moms att betala (+) eller att få tillbaka (-)</td>
        <td class="number"><?= format_bc($ruta[49]) ?></td>
    </tr>
    </tbody>
</table>

</div>