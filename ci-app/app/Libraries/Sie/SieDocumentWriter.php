<?php

namespace App\Libraries\Sie;

include_once('SieDocument.php');

class WriteOptions
{
    // public WriteOptions(){
    //     this.Encoding = EncodingHelper.GetDefault();;
    // }

    public bool $WriteKSUMMA  = false;
    public $CodePage = "cp437";
    public string $DateFormat = "Ymd";
}


class SieDocumentWriter
{
    private SieDocument $doc;
    private $fileHandle;
    private WriteOptions $options;

    public function __construct(SieDocument $sie,string $user,  WriteOptions $options = null)
    {
        $this->doc = $sie;
        $this->doc->SIETYP = 4;
        $this->doc->GEN_NAMN = $user;
        $this->doc->GEN_DATE = new \DateTime();
        
        $this->options = $options ?? new WriteOptions();
    }





    private function SetDocumentKSUMMA()
    {
        // using (_stream = new MemoryStream())
        // {
        //     WriteCore();
        //     _stream.Position = 0;
        //     var tempDoc = new SieDocument();
        //     tempDoc.ThrowErrors = false;
        //     tempDoc.Encoding = _options.Encoding;
        //     tempDoc.ReadDocument(_stream);
        //     doc.KSUMMA = tempDoc.CRC.Checksum();
        // }

        // _stream = null;
    }

    private function getObjeklista(?array $objects): string
    {
        if($objects == null) return "";
        
        if ($this->doc->SIETYP < 3) return "";

        $ret = "{";
        if (!empty($objects)) {
            foreach ($objects as $o) {
                $ret .= $o->Dimension->Number;
                $ret .= " \"" . $o->Number . "\" ";
            }
        }
        $ret .= "}";


        return $ret;
    }

    private function makeSieDate(?\DateTime $date): string
    {
        if ($date != null) {
            return $date->format($this->options->DateFormat);
        } else {
            if ($this->options->DateFormat == "Ymd") {
                return "00000000";
            } else {
                $d = date_create_from_format("Ymd", "00000101");
                return date_create_from_format($this->options->DateFormat, $d);
            }
        }
    }

    private function WriteLine(string $line): void
    {
        if($line == "") return; 

        //var bytes = _options.Encoding.GetBytes(line.Trim() + Environment.NewLine);
        $bytes = trim($line) . "\n";
        $bytes = iconv("UTF-8", $this->options->CodePage, $bytes);
        fwrite($this->fileHandle, $bytes);
    }

    private function makeField(string $data): string
    {
        if (filter_var($data, FILTER_VALIDATE_INT)) {
            return $data;
        } else {
            return '"' . $data . '"';
        }
    }

    private function SieAmount($amount): string
    {
        return str_replace(',', '.', (string)$amount);
    }

    private function WriteVALUTA(): void
    {
        if (isset($this->doc->VALUTA) &&!empty(trim($this->doc->VALUTA))) {

            $this->WriteLine("#VALUTA " . $this->doc->VALUTA);
        }
    }

    private function WriteVER(): void
    {
        
        if ($this->doc->VER == "") return;

        foreach ($this->doc->VER as $v) {
            $createdBy = trim($v->CreatedBy) ? "" : '"' . $v->CreatedBy . '"';
            // Use an empty string rather than the default date of 000010101, when this optional field is not set
            $createdDate = $v->CreatedDate == "" ? "" : $this->makeSieDate($v->CreatedDate);
            $this->WriteLine('#VER "' . $v->Series . '" "' . $v->Number . '" ' . $this->makeSieDate($v->VoucherDate) . ' "' . $v->Text . '" ' . $createdDate . ' ' . $createdBy);

            $this->WriteLine("{");

            foreach ($v->Rows as $r) {
                $obj = $this->getObjeklista($r->Objects);
                $quantity = isset($r->Quantity) ? $this->SieAmount($r->Quantity) : "";
                $createdBy = !empty(trim($r->CreatedBy)) ? "\"" . $r->CreatedBy . "\"" : "";
                $this->WriteLine($r->Token . " " . $r->Account->Number . " " . $obj . " " . $this->SieAmount($r->Amount) . " " . $this->makeSieDate($r->RowDate) . " \"" . $r->Text . "\" " . $quantity . " " . $createdBy);
            }


            $this->WriteLine("}");
        }
    }

    private function WriteDIM()
    {
        if ($this->doc->DIM == null) return;
        foreach ($this->doc->DIM as $d) {
            $this->WriteLine("#DIM " . $d->Number . ' "' . $d->Name . '"');

            foreach ($d->Objects as $o) {
                $this->WriteLine("#OBJEKT " . $d->Number . ' ' . $o->Number . ' "' . $o->Name . '"');
            }
        }
    }



    private function WriteUNDERDIM()
    {
        if (empty($this->doc->UNDERDIM)) return;
        foreach ($this->doc->UNDERDIM as $d) {
            $this->WriteLine("#UNDERDIM " . $d->Number . " \"" . $d->Name . "\" " . $d->SuperDim->Number);

            foreach ($d->Objects as $o) {
                $this->WriteLine("#OBJEKT " . $d->Number . " " . $o->Number . " \"" . $o->Name . "\"");
            }
        }
    }

    private function WritePeriodValue($name, $list)
    {
        if ($list == null) return;
        foreach ($list as $v) {
            $objekt = isset($v->Objects) ? $this->getObjeklista($v->Objects): "";
            if (strpos("#IB#UB#RES", $name) !== false) $objekt = "";
            $quantity = "";
            if (!is_null($v->Quantity)) {
                $quantity = $this->SieAmount($v->Quantity);
            }
            $this->WriteLine($name . " " . $v->YearNr . " " . $v->Account->Number . " " . $objekt . " " . $this->SieAmount($v->Amount) . " " . $quantity);
        }
    }

    private function WritePeriodSaldo($name, $list)
    {
        foreach ($list as $v) {
            $objekt = isset($v->Objects) ? $this->getObjeklista($v->Objects): "";
            $this->WriteLine("$name {$v->YearNr} {$v->Period} {$v->Account->Number} $objekt " . $this->SieAmount($v->Amount));
        }
    }

    private function WriteRAR()
    {
        if ($this->doc->RAR === null) return;
        foreach ($this->doc->RAR as $r) {
            $this->WriteLine("#RAR " . $r->ID . " " . $this->makeSieDate($r->Start) . " " . $this->makeSieDate($r->End));
        }
    }


    private function WriteKONTO()
    {
        if ($this->doc->KONTO == null) return;
        foreach ($this->doc->KONTO as $k) {
            $this->WriteLine("#KONTO " . $k->Number . " \"" . $k->Name . "\"");
            if (isset($k->Unit)) {
                $this->WriteLine("#ENHET " . $k->Number . " \"" . trim($k->Unit) . "\"");
            }
            if (isset($k->Type)) {
                $this->WriteLine("#KTYP " . $k->Number . " " . trim($k->Type));
            }
        }
        foreach ($this->doc->KONTO as $k) {
            foreach ($k->SRU as $s) {
                $this->WriteLine("#SRU " . $k->Number . " " . $s);
            }
        }
    }
    private function WriteOMFATTN()
    {
        if (isset($this->doc->OMFATTN)) {
            $this->WriteLine("#OMFATTN " . $this->makeSieDate($this->doc->OMFATTN));
        }
    }

    private function WriteTAXAR()
    {
        if (isset($this->doc->TAXAR) && $this->doc->TAXAR > 0) {
            $this->WriteLine("#TAXAR " . $this->doc->TAXAR);
        }
    }

    private function WriteFTYP()
    {
        if (isset($this->doc->FNAMN->OrgType) &&!empty(trim($this->doc->FNAMN->OrgType))) {
            $this->WriteLine("#FTYP " . $this->doc->FNAMN->OrgType);
        }
    }
    private function WriteKPTYP()
    {
        if (isset($this->doc->KPTYP) && !empty(trim($this->doc->KPTYP))) {
            $this->WriteLine("#KPTYP " . $this->doc->KPTYP);
        }
    }

    private function WriteADRESS()
    {
        if ((isset($this->doc->FNAMN->Contact) && isset($this->doc->FNAMN->Street) && isset($this->doc->FNAMN->ZipCity) && isset($this->doc->FNAMN->Phone))) {
            $this->WriteLine("#ADRESS \"" . $this->doc->FNAMN->Contact . "\" \"" . $this->doc->FNAMN->Street . "\" \"" . $this->doc->FNAMN->ZipCity . "\" \"" . $this->doc->FNAMN->Phone . "\"");
        }
    }
    private function FNAMN()
    {
        return "#FNAMN \"" . $this->doc->FNAMN->Name . "\"";
    }

    private function ORGNR()
    {
        if(isset($this->doc->FNAMN->OrgIdentifier)){
            return "#ORGNR " . $this->doc->FNAMN->OrgIdentifier;
        } else{
            return "";
        }
    }

    private function WriteFNR()
    {
        if (isset($this->doc->FNAMN->Code) &&!empty(trim($this->doc->FNAMN->Code))) {
            $this->WriteLine("#FNR \"" . $this->doc->FNAMN->Code . "\"");
        }
    }

    private function SIETYP()
    {
        return "#SIETYP " . $this->doc->SIETYP;
    }

    private function GEN()
    {
        $ret = "#GEN ";
        $ret .= $this->makeSieDate($this->doc->GEN_DATE) . " ";
        $ret .= $this->makeField($this->doc->GEN_NAMN);
        return $ret;
    }
    private function FORMAT(): string
    {
        if ($this->options->CodePage == "cp437") {
            return "#FORMAT PC8";
        } else {
            return "#FORMAT {$this->options->CodePage}";
        }
    }

    private function PROGRAM(): string
    {
        $program = "#PROGRAM \"jsiSIE\" ";
        foreach ($this->doc->PROGRAM as $s) {
            $program .= $this->makeField($s) . " ";
        }
        return $program;
    }

    private function FLAGGA(): string
    {
        return "#FLAGGA {$this->doc->FLAGGA}";
    }


    private function WriteCore()
    {
        $this->WriteLine($this->FLAGGA());

        if ($this->options->WriteKSUMMA) $this->WriteLine("#KSUMMA");

        $this->WriteLine($this->PROGRAM());
        $this->WriteLine($this->FORMAT());
        $this->WriteLine($this->SIETYP());
        $this->WriteLine($this->GEN());
        if (!empty($this->doc->PROSA)) $this->WriteLine("#PROSA " . '"' . $this->doc->PROSA . '"');
        $this->WriteFNR();
        $this->WriteLine($this->ORGNR());
        $this->WriteLine($this->FNAMN());
        $this->WriteADRESS();
        $this->WriteFTYP();
        $this->WriteKPTYP();
        $this->WriteVALUTA();
        $this->WriteTAXAR();
        $this->WriteOMFATTN();
        $this->WriteRAR();

        $this->WriteDIM();
        $this->WriteUNDERDIM();
        $this->WriteKONTO();

        $this->WritePeriodValue("#IB", $this->doc->IB);
        $this->WritePeriodValue("#UB", $this->doc->UB);
        if ($this->doc->SIETYP >= 3) {
            $this->WritePeriodValue("#OIB", $this->doc->OIB);
            $this->WritePeriodValue("#OUB", $this->doc->OUB);
        }
        if ($this->doc->SIETYP > 1) {
            $this->WritePeriodSaldo("#PBUDGET", $this->doc->PBUDGET);
            $this->WritePeriodSaldo("#PSALDO", $this->doc->PSALDO);
        }
        $this->WritePeriodValue("#RES", $this->doc->RES);

        $this->WriteVER();
        //if ($this->options->WriteKSUMMA) $this->WriteLine("#KSUMMA " . $this->doc->KSUMMA);
    }

    public function Write(string $fileName)
    {
        if ($this->options->WriteKSUMMA) {
            $this->SetDocumentKSUMMA();
        }

        if (file_exists($fileName)) unlink($fileName); //'w' on fopen should do this but then the mod date is not updated
        $this->fileHandle = fopen($fileName, 'w');
        $this->WriteCore();
        fclose($this->fileHandle);
    }
}
