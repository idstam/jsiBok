
<?php
/** @var array $reportQuery */
/** @var string $fromDate */
/** @var string $toDate */
/** @var string $orgNr */

//https://www.skatteverket.se/foretag/moms/deklareramoms/skapaochskickainmomsdeklarationviafil.4.2fb39afe18dabf1e4d223cc.html
$radRubriker = [
    5 => "ForsMomsEjAnnan",
    6 => "UttagMoms",
    7 => "UlagMargbesk",
    8 => "HyrinkomstFriv",
    10 => "MomsUtgHog",
    11 => "MomsUtgMedel",
    12 => "MomsUtgLag",
    20 => "InkopVaruAnnatEg",
    21 => "InkopTjanstAnnatEg",
    22 => "InkopTjanstUtomEg",
    23 => "InkopVaruSverige",
    24 => "InkopTjanstSverige",
    30 => "MomsInkopUtgHog",
    31 => "MomsInkopUtgMedel",
    32 => "MomsInkopUtgLag",
    35 => "ForsVaruAnnatEg",
    36 => "ForsVaruUtomEg",
    37 => "InkopVaruMellan3p",
    38 => "ForsVaruMellan3p",
    39 => "ForsTjSkskAnnatEg",
    40 => "ForsTjOvrUtomEg",
    41 => "ForsKopareSkskSverige",
    42 => "ForsOvrigt",
    50 => "MomsUlagImport",
    60 => "MomsImportUtgHog",
    61 => "MomsImportUtgMedel",
    62 => "MomsImportUtgLag",
    48 => "MomsIngAvdr",
    49 => "MomsBetala"
];
?>


    <?php
    if($orgNr =="") $orgNr = "MISSING_ORGNO";

    $period = ensure_date_string($toDate, 'Ym');

    $data ='<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
    $data .= '<!DOCTYPE eSKDUpload PUBLIC "-//Skatteverket, Sweden//DTD Skatteverket eSKDUpload-DTD Version 6.0//SV" "https://www.skatteverket.se/download/18.3f4496fd14864cc5ac99cb1/1415022101213/eSKDUpload_6p0.dtd">' . "\n";
    $data .= '<eSKDUpload Version="6.0">' . "\n";
    $data .= '<OrgNr>' . $orgNr . '</OrgNr>' . "\n";
    $data .= "<Moms>" . "\n";
    $data .= '<Period>' . $period . '</Period>' . "\n";

    $rutor = array_fill(0, 100, new BcMath\Number(0));

    foreach ($reportQuery as $row) {
        $num = (new BcMath\Number($row->amount))->round(0, RoundingMode::HalfEven);
        $rutor[$row->ruta] = $num;
        //$ruta[$row->ruta] = bcadd($row->amount, 0, 0);

    }
    $rutor[49] = $rutor[48] + $rutor[60] + $rutor[61] + $rutor[62] + $rutor[30] + $rutor[31] + $rutor[32];

    //Det blir plus här eftersom det är kreditvärden från databasen.
    foreach([49, 30, 31, 32, 60, 61, 62 ] as $toNegate){
        $rutor[$toNegate] = -$rutor[$toNegate];
    }



    foreach($rutor as $ruta => $amount ) {
        if(array_key_exists(intval($ruta), $radRubriker) && $amount != "0") {
            $no = str_pad($ruta, 2, "0", STR_PAD_LEFT) . ".";
            $rutaValue = '<' . $radRubriker[intval($ruta)] . '>' . $amount . '</' . $radRubriker[intval($ruta)] . '>';

            $data .= $rutaValue . "\n";
        }
    }
    $data .= '</Moms>' . "\n";
    $data .= '</eSKDUpload>' . "\n";

//    echo( htmlentities($data));
//    file_put_contents($orgNr . "_" . $period .  ".xml", $data);
    ?>
<textarea id="textData" rows="6" cols="50" hidden ><?= $data ?></textarea>
<input type="hidden" id="textDataFileName" value="<?= 'moms_' . $orgNr . '_' . $period . '.xml' ?>">
<button id="btnDownloadTextData" >Spara fil åt Skatteverket</button>
<button id="btnBokaMomsen" >Bokför momsdeklaration</button>
<br>
<br>