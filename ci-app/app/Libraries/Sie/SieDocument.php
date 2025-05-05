<?php

namespace App\Libraries\Sie;

include_once('SieClasses.php');
include_once('SieCallbacks.php');

class SieDocument
{
    /// <summary>
    /// This is where all the callbacks to client code happens.
    /// </summary>
    public SieCallbacks $Callbacks;

    public bool $IgnoreBTRANS = false;
    public bool $IgnoreMissingOMFATTNING = false;
    public bool $IgnoreRTRANS = false;
    public bool $AllowMissingDate  = true;
    public bool $AllowUnbalancedVoucher = false;
    

    public bool $AllowUnderDimensions = false;

    public string $DateFormat = "Ymd";
    public string $CodePage = "cp437";

    public \DateTime $MinDate;
    public \DateTime $MaxDate;
    public $Series = [];

    /// <summary>
    /// If this is set to true in ReadFile no period values, balances or transactions will be saved in memory.
    /// Use this in combination with callbacks to stream through a file.
    /// </summary>
    public bool $StreamValues = false;

    /// <summary>
    /// Calculates KSUMMA
    /// </summary>
    //public SieCRC32 $CRC;

    /// <summary>
    /// This is the file currently being read.
    /// </summary>
    private string $_fileName;
    function __construct()
    {
        //this.Encoding = EncodingHelper.GetDefault();
    }


    /// <summary>
    /// #DIM
    /// </summary>
    public array $DIM; //Dictionary<string, SieDimension>

    /// <summary>
    /// #UNDERDIM
    /// </summary>
    public array $UNDERDIM; //Dictionary<string, SieDimension>

    /// <summary>
    /// #DIM or #UNDERDIM, should be empty after reading file.
    /// </summary>
    public array $TEMPDIM; //Dictionary<string, SieDimension>

    /// <summary>
    /// #FLAGGA
    /// </summary>
    public int $FLAGGA;

    public SieCompany $FNAMN;

    /// <summary>
    /// #FORMAT
    /// </summary>
    public string $FORMAT;

    /// <summary>
    /// #GEN
    /// </summary>
    public \DateTime $GEN_DATE; //DateTime

    public string $GEN_NAMN;

    /// <summary>
    /// #IB
    /// </summary>
    public array $IB; //List<SiePeriodValue>

    /// <summary>
    /// #KONTO
    /// </summary>
    public array $KONTO; //Dictionary<string, SieAccount>

    /// <summary>
    /// #KPTYP
    /// </summary>
    public string $KPTYP;

    public int $KSUMMA;

    /// <summary>
    /// #OIB
    /// </summary>
    public array $OIB; //List<SiePeriodValue>

    /// <summary>
    /// #OMFATTN Obligatory when exporting period values
    /// </summary>
    public ?\DateTime $OMFATTN; //DateTime?

    /// <summary>
    /// #OUB
    /// </summary>
    public array $OUB; //List<SiePeriodValue>

    /// <summary>
    /// #PBUDGET
    /// </summary>
    public array $PBUDGET; //List<SiePeriodValue> 

    /// <summary>
    /// #PROGRAM
    /// </summary>
    public array $PROGRAM; // List<string>

    /// <summary>
    /// #PROSA
    /// </summary>
    public string $PROSA;

    /// <summary>
    /// #PSALDO
    /// </summary>
    public array $PSALDO; //List<SiePeriodValue>

    /// <summary>
    /// #RAR
    /// </summary>
    public array  $RAR; //Dictionary<int, SieBookingYear>

    /// <summary>
    /// #RES
    /// </summary>
    public array $RES; //List<SiePeriodValue>

    /// <summary>
    /// #SIETYP
    /// </summary>
    public int $SIETYP = 0;

    /// <summary>
    /// #TAXAR
    /// </summary>
    public int $TAXAR;

    /// <summary>
    /// If this is set to true in ReadFile each error will be thrown otherwise they will just be callbacked.
    /// </summary>
    public bool $ThrowErrors = true;
    /// <summary>
    /// #UB
    /// </summary>
    public array $UB; //List<SiePeriodValue>

    /// <summary>
    /// Will contain all validation errors after doing a ValidateDocument
    /// </summary>
    public $ValidationExceptions = []; // List<Exception>
    /// <summary>
    /// #VALUTA
    /// </summary>
    public string $VALUTA;

    /// <summary>
    /// #VER
    /// </summary>
    public array $VER; //List<SieVoucher>


    /// <summary>
    /// Does a fast scan of the file to get the Sie version it adheres to.
    /// </summary>
    /// <param name="fileName"></param>
    /// <returns>-1 if no SIE version was found in the file else SIETYPE is returned.</returns>
    public function GetSieVersion(string $fileName) : int
    {
        $ret = -1;
        $file = new \SplFileObject($fileName);
        foreach ($file as $line) {
            if (str_starts_with($line, "#SIETYP")) {       
                $di = new SieDataItem($line, $this);
                $ret = $di->GetInt(0);
                break;
            }
        }
        return $ret;
    }
    // public static int GetSieVersion(string fileName, Encoding encoding)
    // {
    //     int ret = -1;
    //     foreach (var line in File.ReadLines(fileName, encoding))
    //     {
    //         if (line.StartsWith("#SIETYP"))
    //         {
    //             var di = new SieDataItem(line, null);
    //             ret = di.GetInt(0);
    //             break;
    //         }
    //     }

    //     return ret;
    // }

    public function ReadDocument(string $fileName)
    {
        $this->initialize();
        $this->_fileName = $fileName;

        $firstLine = true;
        $curVoucher = null;
        foreach (new \SplFileObject($fileName) as $line) {
            $line = iconv($this->CodePage,'UTF-8', $line);
            if (!$this->parseLine($line, $curVoucher, $firstLine)){
                return false;
            }
            $firstLine = false;
        }
        
        return true;
    }


    function initialize() : void
    {
        $this->Callbacks = new SieCallbacks();
       // if ($this->ThrowErrors) $this->Callbacks->SieException = SieDocument::throwCallbackException;

        #region Initialize lists
        $this->Series = [];
        $this->MinDate = \DateTime::createFromFormat('Ymd', '29991231');
        $this->MaxDate = \DateTime::createFromFormat('Ymd', '19000101');

        $this->FNAMN = new SieCompany();

        $this->KONTO = []; //new Dictionary<string, SieAccount>();
        $this->DIM = []; //new Dictionary<string, SieDimension>();
        $this->UNDERDIM = []; //new Dictionary<string, SieDimension>();
        $this->TEMPDIM = []; //new Dictionary<string, SieDimension>();

        $this->OIB = []; //new List<SiePeriodValue>();
        $this->OUB = []; //new List<SiePeriodValue>();
        $this->PSALDO = []; //new List<SiePeriodValue>();
        $this->PBUDGET = []; //new List<SiePeriodValue>();
        $this->PROGRAM = []; //new List<string>();
        $this->RAR = []; //new Dictionary<int, SieBookingYear>();
        $this->IB = []; //new List<SiePeriodValue>();
        $this->UB = []; //new List<SiePeriodValue>();
        $this->RES = []; //new List<SiePeriodValue>();

        $this->VER = []; //new List<SieVoucher>();
        $this->ValidationExceptions = []; //new List<Exception>();

        $this->initializeDimensions();
        #endregion //Initialize listst

        //$this->CRC = new SieCRC32(this . Encoding);
    }

    function parseLine(string $line, ?SieVoucher &$curVoucher, bool $firstLine)
    {
        $this->Callbacks->CallbackLine($line);

        $di = new SieDataItem($line, $this);

        if ($firstLine) {
            if ($di->ItemType != "#FLAGGA") {
                $this->Callbacks->CallbackException(new SieInvalidFileException($this->_fileName));
                return false;
            }
        }

        //if (CRC.Started && $di->ItemType != "#KSUMMA") CRC.AddData(di);

        $pv = null; //SiePeriodValue 


        switch ($di->ItemType) {
            case "#ADRESS":
                $this->FNAMN->Contact = $di->GetString(0);
                $this->FNAMN->Street = $di->GetString(1);
                $this->FNAMN->ZipCity = $di->GetString(2);
                $this->FNAMN->Phone = $di->GetString(3);
                
                break;

            case "#BKOD":
                $this->FNAMN->SNI = $di->GetInt(0);
                break;

            case "#BTRANS":
                if (!$this->IgnoreBTRANS) $curVoucher = $this->parseTRANS($di, $curVoucher);
                break;

            case "#DIM":
                $this->parseDimension($di);
                break;

            case "#UNDERDIM":
                $this->parseUnderDimension($di);
                break;

            case "#ENHET":
                $this->parseENHET($di);
                break;

            case "#FLAGGA":
                $this->FLAGGA = $di->GetInt(0);
                break;

            case "#FNAMN":
                $this->FNAMN->Name = $di->GetString(0);
                break;

            case "#FNR":
                $this->FNAMN->Code = $di->GetString(0);
                break;

            case "#FORMAT":
                $this->FORMAT = $di->GetString(0);
                break;

            case "#FTYP":
                $this->FNAMN->OrgType = $di->GetString(0);
                break;

            case "#GEN":
                $this->GEN_DATE = $di->GetDate(0);
                $this->GEN_NAMN = $di->GetString(1);
                break;

            case "#IB":
                $this->parseIB($di);
                break;

            case "#KONTO":
                $this->parseKONTO($di);
                break;
            case "#KSUMMA":
                // if ($this->CRC->Started) {
                //     $this->parseKSUMMA($di);
                // } else {
                //     $this->CRC->Start();
                // }

                break;
            case "#KTYP":
                $this->parseKTYP($di);
                break;

            case "#KPTYP":
                $this->KPTYP = $di->GetString(0);
                break;

            case "#OBJEKT":
                $this->parseOBJEKT($di);
                break;

            case "#OIB":
                $pv = $this->parseOIB_OUB($di);
                $this->Callbacks->CallbackOIB($pv);
                if (!$this->StreamValues) array_push($this->OIB, $pv);
                break;

            case "#OUB":
                $pv = $this->parseOIB_OUB($di);
                $this->Callbacks->CallbackOUB($pv);
                if (!$this->StreamValues) array_push($this->OUB, $pv);
                break;

            case "#ORGNR":
                $this->FNAMN->OrgIdentifier = $di->GetString(0);
                break;

            case "#OMFATTN":
                $this->OMFATTN = $di->GetDate(0);
                break;

            case "#PBUDGET":
                $pv = $this->parsePBUDGET_PSALDO($di);
                if ($pv != null) {
                    $this->Callbacks->CallbackPBUDGET($pv);
                    if (!$this->StreamValues) array_push($this->PBUDGET, $pv);
                }

                break;

            case "#PROGRAM":
                $this->PROGRAM = $di->Data;
                break;

            case "#PROSA":
                $this->PROSA = $di->GetString(0);
                break;

            case "#PSALDO":
                $pv = $this->parsePBUDGET_PSALDO($di);
                if ($pv != null) {
                    $this->Callbacks->CallbackPSALDO($pv);
                    if (!$this->StreamValues) array_push($this->PSALDO, $pv);
                }

                break;

            case "#RAR":
                $this->parseRAR($di);
                break;

            case "#RTRANS":
                if (!$this->IgnoreBTRANS) $curVoucher = $this->parseTRANS($di, $curVoucher);
                break;

            case "#SIETYP":
                $this->SIETYP = $di->GetInt(0);
                break;

            case "#SRU":
                $this->parseSRU($di);
                break;

            case "#TAXAR":
                $this->TAXAR = $di->GetInt(0);
                break;

            case "#UB":
                $this->parseUB($di);
                break;

            case "#TRANS":
                $curVoucher = $this->parseTRANS($di, $curVoucher);
                break;
            case "#RES":
                $this->parseRES($di);
                break;

            case "#VALUTA":
                $this->VALUTA = $di->GetString(0);
                break;

            case "#VER":
                $curVoucher = $this->parseVER($di);
                break;

            case "":
                //Empty line
                break;
            case "{":
                break;
            case "}":
                if ($curVoucher != null) $this->closeVoucher($curVoucher);
                $curVoucher = null;
                
                break;
            default:
                $this->Callbacks->CallbackException(new NotImplementedException($di->ItemType));
                break;
        }

        return true;
    }


    private function parseRAR(SieDataItem $di)
    {

        $rar = new SieBookingYear();
        $rar->ID = $di->GetInt(0);
        $rar->Start = $di->GetDate(1);
        $rar->End = $di->GetDate(2);

        $this->RAR[$rar->ID] = $rar;
    }

    private function addValidationException(bool $isException, Exception $ex)
    {
        if ($isException) {
            array_push($this->ValidationExceptions, $ex);
            $this->Callbacks->CallbackException($ex);
        }
    }

    private function closeVoucher(SieVoucher $v)
    {
        //Check sum of rows
        $check = "0";
        foreach ($v->Rows as $r) {
            if ($r->Token == "#RTRANS" && $this->IgnoreRTRANS) continue;
            if ($r->Token == "#BTRANS" && $this->IgnoreBTRANS) continue;

            $check  = bcadd($check, $r->Amount, 2);
        }
        if (bccomp($check, "0") != 0 && !$this->AllowUnbalancedVoucher) {
            $ex = new SieVoucherMissmatchException($v->Series . "." . $v->Number . " Sum is not zero:" . $check);
            array_push($this->ValidationExceptions, $ex);
            $this->Callbacks->CallbackException($ex);
        }

        $this->Callbacks->CallbackVER($v);
        if (!$this->StreamValues) array_push($this->VER, $v);
        
    }

    private function initializeDimensions()
    {
        $nd = new SieDimension();
        $nd->Number = "1";
        $nd->Name = "Resultatenhet";
        $nd->IsDefault = true;
        $this->DIM["1"] = $nd;

        if ($this->AllowUnderDimensions) {
            $nd = new SieDimension();
            $nd->Number = "2";
            $nd->Name = "Kostnadsbärare";
            $nd->SetSuperDim($this->DIM["1"]);
            $nd->IsDefault = true;

            $this->UNDERDIM["2"] = $nd;
        } else {
            $this->DIM["2"] = new SieDimension();
            $this->DIM["2"]->docInit("2", "Kostnadsbärare", $this->DIM["1"], true);
            $this->DIM["3"] = new SieDimension();
            $this->DIM["3"]->docInit("3", "Reserverat", null, true);
            $this->DIM["4"] = new SieDimension();
            $this->DIM["4"]->docInit("4", "Reserverat", null, true);
            $this->DIM["5"] = new SieDimension();
            $this->DIM["5"]->docInit("5", "Reserverat", null, true);
            $this->DIM["6"] = new SieDimension();
            $this->DIM["6"]->docInit("6", "Projekt", null, true);
            $this->DIM["7"] = new SieDimension();
            $this->DIM["7"]->docInit("7", "Anställd", null, true);
            $this->DIM["8"] = new SieDimension();
            $this->DIM["8"]->docInit("8", "Kund", null, true);
            $this->DIM["9"] = new SieDimension();
            $this->DIM["9"]->docInit("9", "Leverantör", null, true);
            $this->DIM["10"] = new SieDimension();
            $this->DIM["10"]->docInit("10", "Faktura", null, true);
            $this->DIM["11"] = new SieDimension();
            $this->DIM["11"]->docInit("11", "Reserverat", null, true);
            $this->DIM["12"] = new SieDimension();
            $this->DIM["12"]->docInit("12", "Reserverat", null, true);
            $this->DIM["13"] = new SieDimension();
            $this->DIM["13"]->docInit("13", "Reserverat", null, true);
            $this->DIM["14"] = new SieDimension();
            $this->DIM["14"]->docInit("14", "Reserverat", null, true);
            $this->DIM["15"] = new SieDimension();
            $this->DIM["15"]->docInit("15", "Reserverat", null, true);
            $this->DIM["16"] = new SieDimension();
            $this->DIM["16"]->docInit("16", "Reserverat", null, true);
            $this->DIM["17"] = new SieDimension();
            $this->DIM["17"]->docInit("17", "Reserverat", null, true);
            $this->DIM["18"] = new SieDimension();
            $this->DIM["18"]->docInit("18", "Reserverat", null, true);
            $this->DIM["19"] = new SieDimension();
            $this->DIM["19"]->docInit("19", "Reserverat", null, true);

            
        }
    }

    private function parseDimension(SieDataItem $di)
    {
        $d = $di->GetString(0);
        $n = $di->GetString(1);

        if (array_key_exists($d, $this->TEMPDIM)) {
            $this->DIM[$d] = $this->TEMPDIM[$d];
            unset($this->TEMPDIM[$d]);
        }

        if (!array_key_exists($d, $this->DIM)) {
            $nd = new SieDimension();
            $nd->Number = $d;
            $this->DIM[$d] = $nd;
        }

        $this->DIM[$d]->Name = $n;
        $this->DIM[$d]->IsDefault = false;
    }

    private function parseUnderDimension(SieDataItem $di)
    {
        if (!$this->AllowUnderDimensions) return;

        $d = $di->GetString(0);
        $n = $di->GetString(1);
        $p = $di->GetString(2);

        if (array_key_exists($d, $this->UNDERDIM)) {
            $this->UNDERDIM[$d] =  $this->TEMPDIM[$d];
            unset($this->TEMPDIM[$d]);
        }

        if (!array_key_exists($d, $this->UNDERDIM)) {
            $nd = new SieDimension();
            $nd->Number = $d;
            $this->UNDERDIM[$d] = $nd;
        }

        $this->UNDERDIM[$d]->Name = $n;
        $this->UNDERDIM[$d]->IsDefault = false;
        $this->UNDERDIM[$d]->SetSuperDim($this->DIM[$p]);
    }

    private function parseENHET(SieDataItem $di)
    {
        if (!array_key_exists($di->GetString(0), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(0);
            $this->KONTO[$di->GetString(0)] = $n;
        }
        $this->KONTO[$di->GetString(0)]->Unit = $di->GetString(1);
    }

    private function parseIB(SieDataItem $di)
    {
        if (!array_key_exists($di->GetString(1), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(1);
            $this->KONTO[$di->GetString(1)] = $n;
        }

        $v = new SiePeriodValue();
        $v->YearNr = $di->GetInt(0);
        $v->Account = $this->KONTO[$di->GetString(1)];
        $v->Amount = $di->GetAmount(2);
        $v->Quantity = $di->GetAmount(3);
        $v->Token = $di->ItemType;
        $this->Callbacks->CallbackIB($v);
        if (!$this->StreamValues) array_push($this->IB, $v);
    }

    private function parseKONTO(SieDataItem $di)
    {
        if (array_key_exists($di->GetString(0),  $this->KONTO)) {
            $this->KONTO[$di->GetString(0)]->Name = $di->GetString(1);
        } else {
            $n = new SieAccount();
            $n->Number = $di->GetString(0);
            $n->Name = $di->GetString(1);
            $this->KONTO[$di->GetString(0)] = $n;
        }
    }

    private function parseKSUMMA(SieDataItem $di)
    {
        // $this->KSUMMA = $di->GetLong(0);
        // $checksum = $this->CRC . Checksum();
        // if ($this->KSUMMA != $checksum) {
        //     $this->Callbacks . CallbackException(new SieInvalidChecksumException($this->_fileName));
        // }
    }
    private function parseKTYP(SieDataItem $di)
    {
        //Create the account if it hasn't been loaded yet.
        if (!array_key_exists($di->GetString(0), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(0);
            $this->KONTO[$di->GetString(0)] = $n;
        }
        $this->KONTO[$di->GetString(0)]->Type = $di->GetString(1);
    }

    private function parseOBJEKT(SieDataItem $di)
    {
        $dimNumber = $di->GetString(0);
        $number = $di->GetString(1);
        $name = $di->GetString(2);

        $dim = $di->GetDimension($di, $dimNumber);

        $obj = new SieObject();
        $obj->Dimension = $dim;
        $obj->Number = $number;
        $obj->Name = $name;

        $dim->Objects[$number] = $obj;
    }

    private function parseOIB_OUB(SieDataItem $di)
    {
        //Create the account if it hasn't been loaded yet.
        if (!array_key_exists($di->GetString(1), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(1);
            $this->KONTO[$di->GetString(1)] = $n;
        }

        if ($this->SIETYP < 3) {
            $this->Callbacks->CallbackException(new SieInvalidFeatureException("Neither OIB or OUB is part of SIE < 3"));
        }

        $objOffset = 0;
        if (strpos($di->RawData, "{")) $objOffset = 1;

        $v = new SiePeriodValue();
        $v->YearNr = $di->GetInt(0);
        //Period = di.GetInt(1);
        $v->Account = $this->KONTO[$di->GetString(1)];
        $v->Amount = $di->GetAmount(2 + $objOffset);
        $v->Quantity = $di->GetAmount(3 + $objOffset);
        $v->Objects = $di->GetObjects();
        $v->Token = $di->ItemType;

        return $v;
    }

    private function parsePBUDGET_PSALDO(SieDataItem $di)
    {
        //Create the account if it hasn't been loaded yet.
        if (!array_key_exists($di->GetString(2), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(2);
            $this->KONTO[$di->GetString(2)] = $n;
        }

        if ($this->SIETYP == 1) {
            $this->Callbacks->CallbackException(new SieInvalidFeatureException("Neither PSALDO or PBUDGET is part of SIE 1"));
        }

        if ($this->SIETYP == 2 &&  strpos($di->RawData, "{") && !strpos($di->RawData, "{}")) {
            //Applications reading SIE type 2 should ignore PSALDO containing non empty dimension.
            return null;
        }

        $objOffset = 0;
        if (strpos($di->RawData, "{")) $objOffset = 1;

        $v = new SiePeriodValue();
        $v->YearNr = $di->GetInt(0);
        $v->Period = $di->GetInt(1);
        $v->Account = $this->KONTO[$di->GetString(2)];
        $v->Amount = $di->GetAmount(3 + $objOffset);
        $v->Quantity = $di->GetAmount(4 + $objOffset);
        $v->Token = $di->ItemType;

        if ($this->SIETYP != 2 && strpos($di->RawData, "{")) $v->Objects = $di->GetObjects();
        return $v;
    }

    private function parseRES(SieDataItem $di)
    {
        if (!array_key_exists($di->GetString(1), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(1);
            $this->KONTO[$di->GetString(1)] = $n;
        }
        $objOffset = 0;
        if (strpos($di->RawData, "{")) $objOffset = 1;
        $v = new SiePeriodValue();
        $v->YearNr = $di->GetInt(0);
        $v->Account = $this->KONTO[$di->GetString(1)];
        $v->Amount = $di->GetAmount(2 + $objOffset);
        $v->Quantity = $di->GetAmount(3 + $objOffset);
        $v->Token = $di->ItemType;

        $this->Callbacks->CallbackRES($v);
        if (!$this->StreamValues) array_push($this->RES, $v);
    }

    private function parseSRU(SieDataItem $di)
    {
        if (!array_key_exists($di->GetString(0), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(0);
            $this->KONTO[$di->GetString(0)] = $n;
        }
        array_push($this->KONTO[$di->GetString(0)]->SRU, $di->GetString(1));
    }

    private function parseTRANS(SieDataItem $di, SieVoucher $v)
    {
        if (!array_key_exists($di->GetString(0), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(0);
            $this->KONTO[$di->GetString(0)] = $n;
        }
        
        $objOffset = 0;
        if (strpos($di->RawData, "{")) $objOffset = 1;

        $vr = new SieVoucherRow();
        $vr->Account = $this->KONTO[$di->GetString(0)];
        $vr->Objects = $di->GetObjects();
        $vr->Amount = $di->GetAmount(1 + $objOffset);
        $n = $di->GetDate(2 + $objOffset);
        $vr->RowDate = $di->GetDate(2 + $objOffset) != "" ? $di->GetDate(2 + $objOffset) : $v->VoucherDate;
        $vr->Text = $di->GetString(3 + $objOffset);
        $vr->Quantity = $di->GetAmountNull(4 + $objOffset);
        $vr->CreatedBy = $di->GetString(5 + $objOffset);
        $vr->Token = $di->ItemType;


        array_push($v->Rows, $vr);
        return $v;
    }

    private function parseUB(SieDataItem $di)
    {
        if (!array_key_exists($di->GetString(1), $this->KONTO)) {
            $n = new SieAccount();
            $n->Number = $di->GetString(0);
            $this->KONTO[$di->GetString(0)] = $n;
        }
        $v = new SiePeriodValue();
        $v->YearNr = $di->GetInt(0);
        $v->Account = $this->KONTO[$di->GetString(1)];
        $v->Amount = $di->GetAmount(2);
        $v->Quantity = $di->GetAmount(3);
        $v->Token = $di->ItemType;

        $this->Callbacks->CallbackUB($v);
        if (!$this->StreamValues) array_push($this->UB, $v);
    }

    private function parseVER(SieDataItem $di)
    {
        if ($di->GetDate(2) == null) $this->Callbacks->CallbackException(new SieMissingFieldException("Voucher date"));
        

        $v = new SieVoucher();
        $v->Series = $di->GetString(0);
        $v->Number = $di->GetString(1);
        $v->VoucherDate = $di->GetDate(2);
        $v->Text = $di->GetString(3);
        $v->CreatedDate = $di->GetDate(4);
        $v->CreatedBy = $di->GetString(5);
        $v->Token = $di->ItemType;

        if($v->VoucherDate < $this->MinDate) $this->MinDate = $v->VoucherDate;
        if($v->VoucherDate > $this->MaxDate) $this->MaxDate = $v->VoucherDate;
        if(array_key_exists($v->Series, $this->Series)) {
            $this->Series[$v->Series] = $this->Series[$v->Series] +1 ;
        } else {
            $this->Series[$v->Series] = 0;
        }
        
        return $v;
    }

    /// <summary>
    /// This is used to throw errors when throwError == true
    /// </summary>
    /// <param name="ex"></param>
    public function throwCallbackException(Exception $ex)
    {
        if($this->ThrowErrors){
            throw  $ex;
        }
    }

    public function ValidateDocument()
    {
        $this->ValidationExceptions = [];

        $this->addValidationException((
                !$this->AllowMissingDate &&
                !$this->GEN_DATE != ""),
            new SieMissingMandatoryDateException("#GEN Date is missing in " . $this->_fileName)
        );



        //If there are period values #OMFATTN has to tell the value date.
        $this->addValidationException(
            (!$this->IgnoreMissingOMFATTNING) &&
                (($this->SIETYP == 2 || $this->SIETYP == 3) &&
                    isset($this->OMFATTN)  &&
                    (count($this->RES) > 0 || count($this->UB) > 0 || count($this->OUB) > 0)),
            new SieMissingMandatoryDateException("#OMFATTN is missing in " . $this->_fileName)
        );

        //Ignore KSUMMA for multi byte code pages.
        // if (this . Encoding . IsSingleByte) {
        //     addValidationException(
        //         (CRC . Started) &&
        //             (KSUMMA == 0),
        //         new SieInvalidChecksumException(_fileName)
        //     );
        // }

        // All TEMPDIMs should have been resolved when read is completed.
        $this->addValidationException(
            count($this->TEMPDIM) > 0,
            new SieMissingMandatoryDateException("#DIM or #UNDERDIM is missing for one or more objects in " . $this->_fileName)
        );

        
        return count($this->ValidationExceptions) == 0;
    }

    //public SieBookingYear $RAR;
}
