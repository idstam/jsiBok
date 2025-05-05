<!DOCTYPE html>
<html lang="sv-SE">
<head>
    <meta charset="UTF-8">
    <title><?= /** @var string $title */
        $title ?></title>
    <meta name="description" content="<?= /** @var string $description */
    $description ?>">
    <!--    <link rel="stylesheet" href="/mini.css">-->
    <meta name="color-scheme" content="light dark">
    <link rel="stylesheet" href="/huvudboken.css">
    <link rel="stylesheet" href="/pico/pico.min.css">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico"/>
    <script src="/huvudboken.js"></script>


</head>
<body>
<?php
helper('jsi_helper');

$session = \Config\Services::session();
$errors = $session->getFlashdata('errors');
if ($errors != NULL) {
    foreach ($errors as $field => $text) {
        \Config\Services::validation()->setError($field, $text);
    }
}
if ($errors != NULL) {
    echo('<article class="error">');
    echo('<ul>');
    foreach ($errors as $field => $text) {
        echo('<li>' . $text . '</li>');
    }
    echo('</ul>');
    echo('</article>');
}


$infos = $session->getFlashdata('info');
if ($infos != NULL) {
    echo('<article class="info">');
    echo('<ul>');
    foreach ($infos as $field => $text) {
        echo('<li>' . $text . '</li>');
    }
    echo('</ul>');
    echo('</article>');
}

$warnings = $session->getFlashdata('warning');
if ($warnings != NULL) {
    echo('<article class="warning">');
    echo('<ul>');
    foreach ($warnings as $field => $text) {
        echo('<li>' . $text . '</li>');
    }
    echo('</ul>');
    echo('</article>');
}

$home_caption = "huvudboken.se";

?>

<header>
    <nav>
        <ul>
            <?= !$session->has('userID') ? '<li><h1><a class="contrast" href="/">' . $home_caption . '</a></h1></li>' : "" ?>
        </ul>
        <ul>

            <?= !$session->has('userID') ? '<li><strong><a class="outline" href="/login">Logga in</a></strong></li>' : "" ?>


            <?= $session->has('companyName') ? '<li><strong><a class="outline" href="/company">Företag</a></strong></li>' : "" ?>

            <?= $session->has('companyName') ? '<li><strong><a class="outline" href="/voucher" >Bokföring</a></strong></li>' : "" ?>
            <?= $session->has('companyName') ? '<li><strong><a class="outline" href="/reports" >Rapporter</a></strong></li>' : "" ?>

            <?= $session->has('userID') ? '<li><strong><a class="outline" href="/login/logout">Logga ut</a></strong></li>' : "" ?>
            <li><strong><a class="outline" href="/about" >Om oss</a></strong></li>
        </ul>
            <div >
                <?= $session->has('companyName') ? '<b>' . $session->get('companyName') . "</b>" : "" ?>
                <?= $session->has('companyName') ? '&nbsp;<i>' . ensure_date_string($session->get('yearStart'), 'Y-m-d') . " - " . ensure_date_string($session->get('yearEnd'), 'Y-m-d') . "</i>" : "" ?>
                &nbsp;&nbsp;
            </div>

    </nav>
<hr>
    <?= isset($page_header) ? '<h1>&nbsp;' . $page_header . '</h1>' : ''; ?>
</header>
<?php //= \Config\Services::validation()->listErrors() ?>


