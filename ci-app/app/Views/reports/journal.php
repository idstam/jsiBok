<?php
/** @var string $reportName */
/** @var string $fromDate */
/** @var string $toDate */
/** @var array $reportQuery */

?>
<div class="container">
    <h1><?= $reportName ?>&nbsp;</h1>


<table>
    <thead>
    <tr>
        <th>Datum</th>
        <th>Titel</th>
        <th>Info</th>
        <th>Användare</th>
    </tr>
    </thead>
    <tbody>


    <?php

    foreach ($reportQuery as $row) {

    ?>
    <tr>
        <td>
            <?= $row->created_at ?>
        </td>
        <td >
            <?= $row->title ?>
        </td>
        <td >
            <?= $row->details ?>
        </td>
        <td >
            <?= $row->user ?>
        </td>
    </tr>
<?php } ?>

    </tbody>
</table>
</div>