<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "riesgo.signal.class.php";

class Tnota_signals extends Triesgo_signals {

    public function __construct($clink= null) {
        $this->clink= $clink;        
        Triesgo_signals::__construct($clink);
    }    

    public function get_type($array) {
        $value= $array['tipo'];

        switch($value) {
            case _NO_CONFORMIDAD:
                $this->type= 'red';
                break;
            case _OBSERVACION:
                $this->type= 'orange';
                break;
            case _OPORTUNIDAD:
                $this->type= 'aqua';
                break;
            default:
                $this->type= 'gray';
                break;
        }

        return $this->type;
    }

    public function get_alarm($array) {
        $value= $array['estado'];

        switch($value) {
            case _IDENTIFICADO:
                $this->alarm= 'red';
                break;
            case _GESTIONANDOSE:
                $this->alarm= 'yellow';
                break;
            case _MITIGADO:
                $this->alarm= 'green';
                break;
            case _CERRADA:
                $this->alarm= 'grey';
                break;
            default:
                $this->alarm= 'red';
                break;
        }

        return $this->alarm;
    }
}
?>