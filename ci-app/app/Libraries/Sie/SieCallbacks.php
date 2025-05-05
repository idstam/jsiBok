<?php
namespace App\Libraries\Sie;

include_once('SieClasses.php');
include_once('SieDocument.php');

class SieCallbacks
    {
        //public Action<string> Line;
        public $Line = null;
        public function CallbackLine(string $message)
        {
            $cb = $this->Line;
            if($cb != null) $cb($message);
        }

        //public Action<Exception> SieException;
        public $SieException = null;
        public function CallbackException(Exception $ex)
        {
            if($this->SieException != null){
                $cb = $this->SieException;
                $cb($ex);
            } 
        }

        //public Action<SiePeriodValue> IB;
        public $IB = null;
        public function CallbackIB(SiePeriodValue $pv)
        {
            if ($this->IB != null){
                $cb = $this->IB;
                $cb($pv);
            }
        }

        //public Action<SiePeriodValue> UB;
        public $UB = null;
        public function CallbackUB(SiePeriodValue $pv)
        {
            if ($this->UB != null){
                $cb = $this->UB;
                 $cb($pv);
            }
        }

        //public Action<SiePeriodValue> OIB;
        public $OIB = null;
        public function CallbackOIB(SiePeriodValue $pv)
        {
            if ($this->OIB != null){
                $cb = $this->OIB;
                 $cb($pv);
            }
        }

        //public Action<SiePeriodValue> OUB;
        public $OUB = null;
        public function CallbackOUB(SiePeriodValue $pv)
        {
            if ($this->OUB != null){
                $cb = $this->OUB;
                $cb($pv);
            }
        }

        //public Action<SiePeriodValue> PSALDO;
        public $PSALDO = null;
        public function CallbackPSALDO(SiePeriodValue $pv)
        {
            if ($this->PSALDO != null){
                $cb = $this->PSALDO;
                $cb($pv);
            }
        }

        //public Action<SiePeriodValue> PBUDGET;
        public $PBUDGET = null;
        public function CallbackPBUDGET(SiePeriodValue $pv)
        {
            if ($this->PBUDGET != null){
                $cb = $this->PBUDGET;
                $cb($pv);
            }
        }

        //public Action<SiePeriodValue> RES;
        public $RES = null;
        public function CallbackRES(SiePeriodValue $pv)
        {
            if ($this->RES != null){
                $cb = $this->RES;
                 $cb($pv);
            }
        }

        //public Action<SieVoucher> VER;
        public $VER = null;
        public function CallbackVER(SieVoucher $v)
        {
            if ($this->VER != null){
                $cb = $this->VER;
                $cb($v);
            }
        }
    }
