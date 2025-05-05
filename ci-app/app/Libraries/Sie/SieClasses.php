<?php

namespace App\Libraries\Sie;

class SieDimension
{
    public string $Number;
    public string $Name;
    public bool $IsDefault = false;

    private SieDimension $parent;
    public SieDimension $SuperDim;



    public function SetSuperDim(SieDimension $value): void
    {
        $this->parent = $value;
        array_push($this->parent->SubDim, $this);
    }
    public function docInit($Number, $Name, ?SieDimension $SuperDim, $IsDefault): void
    {
        //{ Number = "2", Name = "Kostnadsbärare", SuperDim = DIM["1"], IsDefault = true }
        $this->Number = $Number;
        $this->Name = $Name;
        $this->IsDefault = $IsDefault;
        if($SuperDim != null)   $this->SetSuperDim($SuperDim);
    }
    public array $SubDim = []; //HashSet<SieDimension>
    public array $Objects = []; //Dictionary<string, SieObject>

}

class SieObject
{
    public SieDimension $Dimension;
    public string $Number;
    public string $Name;
}

class SieAccount
{
    public string $Number;
    public string $Name;
    public string $Unit;
    public string $Type;
    public array $SRU; //Set of strings

    function __construct()
    {
        $this->SRU = [];
        $this->Name = "";
    }
}

class SieVoucherRow
{
    public SieAccount $Account;
    public ?array $Objects;  //SieObject
    public string $Amount; //https://www.php.net/manual/en/ref.bc.php
    public \DateTime $RowDate; //DateTime
    public string $Text;
    public ?string $Quantity; //https://www.php.net/manual/en/ref.bc.php
    public string $CreatedBy;
    public string $Token;
}

class SieVoucher
{
    public string $Series;
    public string $Number;
    public \DateTime $VoucherDate;
    public string $Text;
    public ?\DateTime $CreatedDate;
    public ?string $CreatedBy;
    public string $Token;

    public array $Rows; //array of VoucherRow

    function __construct()
    {
        $this->Rows = [];
    }
}

class SiePeriodValue
{
    public SieAccount $Account;
    public int $YearNr;
    public int $Period;
    public string $Amount; //https://www.php.net/manual/en/ref.bc.php
    public ?string $Quantity; //https://www.php.net/manual/en/ref.bc.php
    public array $Objects; //SieObject
    public string $Token;

    public function ToVoucherRow() //SieVoucherRow
    {
        $vr = new SieVoucherRow();
        $vr->Account = $this->Account;
        $vr->Amount = $this->Amount;
        $vr->Objects = $this->Objects;
        $vr->Quantity = $this->Quantity;
        return $vr;
    }


    public function ToInvertedVoucherRow()
    {
        $vr = $this->ToVoucherRow();
        $vr->Amount  = bcmul($vr->Amount, "-1", 2);

        return $vr;
    }
}

class SieBookingYear
{
    public int $ID;
    public ?\DateTime $Start;
    public ?\DateTime $End;
}

class SieCompany
{
    /// <summary>
    /// The organisation type names as set by Bolagsverket
    /// </summary>
    private $organisationTypeNames; // = new Dictionary<string, string>();
    function __construct()
    {
        $this->loadOrgTypeNames();
    }

    /// <summary>
    /// #BKOD
    /// </summary>
    public int $SNI;

    /// <summary>
    /// #FNAMN
    /// </summary>
    public string $Name;
    /// <summary>
    /// #FNR
    /// </summary>
    public string $Code;

    /// <summary>
    /// #FTYP
    /// </summary>
    public string $OrgType;


    /// <summary>
    /// #ORGNR
    /// </summary>
    public string $OrgIdentifier;
    /// <summary>
    /// #ADRESS
    /// </summary>
    public string $Contact;
    public string $Street;
    public string $ZipCity;
    public string $Phone;


    private function loadOrgTypeNames()
    {
        $this->organisationTypeNames = array(
            ["AB" => "Aktiebolag."],
            ["E" => "Enskild näringsidkare."],
            ["HB" => "Handelsbolag."],
            ["KB" => "Kommanditbolag."],
            ["EK" => "Ekonomisk förening."],
            ["KHF" => "Kooperativ hyresrättsförening."],
            ["BRF" => "Bostadsrättsförening."],
            ["BF" => "Bostadsförening."],
            ["SF" => "Sambruksförening."],
            ["I" => "Ideell förening som bedriver näring."],
            ["S" => "Stiftelse som bedriver näring."],
            ["FL" => "Filial till utländskt bolag."],
            ["BAB" => "Bankaktiebolag."],
            ["MB" => "Medlemsbank."],
            ["SB" => "Sparbank."],
            ["BFL" => "Utländsk banks filial."],
            ["FAB" => "Försäkringsaktiebolag."],
            ["OFB" => "Ömsesidigt försäkringsbolag."],
            ["SE" => "Europabolag."],
            ["SCE" => "Europakooperativ."],
            ["TSF" => "Trossamfund."],
            ["X" => "Annan företagsform."]
        );
    }
}
class Exception extends \Exception
{
    public $description;
    function __construct($description)
    {
        parent::__construct($description);
        $this->description = $description;
    }
}
class SieInvalidChecksumException extends Exception
{
    //public SieInvalidChecksumException(string description) : base(description) { }
}

class SieInvalidFileException extends Exception
{
    //public SieInvalidFileException(string description) : base(description) { }
}

class SieDateException extends Exception
{
    //public SieDateException(string description) : base(description) { }
}

class SieInvalidFeatureException extends Exception
{
    //public SieInvalidFeatureException(string description) : base(description) { }
}

class SieMissingFieldException extends Exception
{
    //public SieMissingMandatoryDateException(string description) : base(description) { }
}

class SieMissingMandatoryDateException extends Exception
{
    //public SieMissingMandatoryDateException(string description) : base(description) { }
}

class SieMissingObjectException extends Exception
{
    //public SieMissingObjectException(string description) : base(description) { }
}

class SieVoucherMissmatchException extends Exception
{
    //public SieVoucherMissmatchException(string description) : base(description) { }
}

class NotImplementedException extends Exception
{
    //public NotImplementedException(string description) : base(description) { }
}
