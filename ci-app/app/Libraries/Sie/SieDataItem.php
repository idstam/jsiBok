<?php
namespace App\Libraries\Sie;

include_once('SieClasses.php');
include_once('SieDocument.php');

class SieDataItem
{
    public SieDocument $Document;
    public string $ItemType;
    public array $Data; //List<string>
    public string $RawData;

    function __construct (string $line, SieDocument $document)
    {
        $this->RawData = $line;
        $this->Document = $document;
        $l = trim($line);
        $p = $this->FirstWhiteSpace($l);


        if ($p == false)
        {
            $this->ItemType = $l;
            $this->Data = [];
        }
        else
        {
            $this->ItemType = substr($l, 0, $p);
            $this->Data = $this->splitLine(substr($l, $p + 1, strlen($l) - ($p + 1)));    
            
        }

    }

    private function FirstWhiteSpace(string $str)
    {
        $a = strpos($str, " ");
        $b = strpos($str, "\t");

        if ($a == false && $b == false) return false;
        if ($a == false && $b != false) return $b;
        if ($b == false) return $a;

        if ($a <= $b)
        {
            return $a;
        }
        else
        {
            return $b;
        }
    }

    private function splitLine(string $untrimmedData)
    {
        $data = trim($untrimmedData);

        $ret = [];

        $isInField = 0;
        $isInObject = false;
        $buffer = "";

        $skipNext = false;
        foreach (str_split($data) as $c)
        {
            if ($skipNext && ($c == '"'))
            {
                $skipNext = false;
                continue;
            }

            if ($c == "\\")
            {
                $skipNext = true;
                continue;
            }

            if ($c == '"' && !$isInObject)
            {
                $isInField += 1;
                continue;
            }

            if ($c == '{') $isInObject = true;
            if ($c == '}') $isInObject = false;

            if (($c == ' ' || $c == "\t") && ($isInField != 1) && !$isInObject)
            {

                $trimBuf = trim($buffer);
                if (strlen($trimBuf) > 0 || $isInField == 2)
                {
                    array_push($ret, $trimBuf);
                    $buffer = "";
                }
                $isInField = 0;
            }

            $buffer .= $c;
        }
        if (strlen($buffer) > 0)
        {
            array_push($ret, trim($buffer));
        }

        return $ret;
    }

    public function GetLong(int $field)
    {
        if (count($this->Data) <= $field) return 0;
        return intval($this->Data[$field]);
    }

    public function GetIntNull(int $field)
    {
        if (count($this->Data) <= $field) return null;
        return intval($this->Data[$field]);
    }

    public function GetInt(int $field)
    {
        if (count($this->Data) <= $field) return 0;
        return intval($this->Data[$field]);
    }

    public function GetFloatNull(int $field)
    {
        if (count($this->Data) <= $field) return null;

        $locale_info = localeconv();
        $d = .0;
        $sep = $locale_info['decimal_point'];
        $foo = str_replace(".", $sep, $this->Data[$field]);
        return floatval($foo);
    }

    public function GetFloat(int $field)
    {
        $foo = $this->GetFloatNull($field);
        return floatval($foo);
    }

    public function GetAmount(int $field){
        return $this->GetString($field);
    }
    public function GetAmountNull(int $field){
        if (count($this->Data) <= $field) return null;
        
        return $this->GetString($field);
    }
    


    public function GetString(int $field)
    {
        if (count($this->Data) <= $field) return "";

        $s = trim($this->Data[$field]);
        $s = trim($s, '"');

        return $s;
    }

    public function GetDate(int $field) : ?\DateTime
    {
        if (count($this->Data) <= $field) return null;

        $fieldDate = trim($this->Data[$field]);
        if ($fieldDate == "") return null;

        if($fieldDate == "00000000") return null;

        $dateFormat = $this->Document->DateFormat;

        $ret =  \DateTime::createFromFormat($dateFormat, $fieldDate);
        $ret->settime(0,0);

        return !$ret ? null:  $ret;
        
    }

    public function GetObjects()
    {
        $item = $this;
        $dimNumber = null;
        $objectNumber = null;
        $ret = []; //new List<SieObject>();


        if (strpos($item->RawData,"{}")) return [];

        $data = null;
        foreach ($item->Data as $i)
        {
            $i = trim($i);
            if (str_starts_with($i, "{"))
            {
                $data = str_replace("{", "", $i);
                $data = str_replace("}", "", $data);
                break;
            }
        }

        if($data == null) 
        {
            $item->Document->Callbacks->CallbackException(new SieMissingObjectException($item->RawData));
            return [];
        }

        $dimData = $this->splitLine($data);

        for ($i = 0; $i < count($dimData); $i += 2)
        {
            $dimNumber = $dimData[$i];

            $d = $this->GetDimension($item, $dimNumber);          
            
            $objectNumber = $dimData[$i + 1];

            //Add temporary object if the objects hasn't been loaded yet.
            if (!array_key_exists($objectNumber, $d->Objects))
            {
                $nso = new SieObject();
                $nso->Dimension = $d;
                $nso->Number = $objectNumber;
                $nso->Name = "[TEMP]";
                $d->Objects[$objectNumber] = $nso;
            }
            array_push($ret, $d->Objects[$objectNumber]);
        }
        

        return $ret;
    }

    public function GetDimension(SieDataItem $item, string $dimNumber)
    {

        if (array_key_exists($dimNumber, $item->Document->UNDERDIM))
        {
            return $item->Document->UNDERDIM[$dimNumber];
        }

        if (array_key_exists($dimNumber, $item->Document->DIM))
        {
            return $item->Document->DIM[$dimNumber];
        }

        if (array_key_exists($dimNumber, $item->Document->TEMPDIM))
        {
            return $item->Document->TEMPDIM[$dimNumber];
        }

        //Add temporary Dimension if the dimensions hasn't been loaded yet.
        $nsd = new SieDimension();
        $nsd->Number = $dimNumber;
        $nsd->Name = "[TEMP]";

        $item->Document->TEMPDIM[$dimNumber] = $nsd;
        return $item->Document->TEMPDIM[$dimNumber];         
    }
}