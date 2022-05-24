<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "evento.signal.class.php";

class Triesgo_signals extends Tevento_signals {
    public $if_synchronize;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tevento_signals::__construct($clink);

        if (!empty($this->clink)) {
            $obj_prs= new Tproceso($this->clink);
            $obj_prs->SetConectado(_NO_LOCAL);
        }
    }

    public function get_alarm($class) {
        /*
          $img= "<img src='../img/shield-".$class.".png' width='50' height='62' />";
          return $img;
         */

        $body = null;

        switch ($class) {
            case 'gray': 
                $body = 'default'; 
                break;
            case 'blue': 
                $body = 'primary'; 
                break;
            case 'yellow': 
                $body = 'info'; 
                break;
            case 'orange': 
                $body = 'warning'; 
                break;
            case 'red': 
                $body = 'danger'; 
                break;
            default: 
                $body = 'danger';
        }

        return $body;
    }

    private function get_class($array) {
        $value= $array['nivel'];
        if (empty($value))
            $xvalue= 0;

        global $nivel_array;
        $value= $nivel_array[$value];

        switch($value) {
            case 'TRIVIAL':
                $class= 'gray';
                break;
            case 'BAJO':
                $class= 'aqua';
                break;
            case 'MODERADO':
                $class= 'yellow';
                break;
            case 'SIGNIFICATIVO':
                $class= 'orange';
                break;
            case 'ALTO':
                $class= 'orange';
                break;
            case 'MUY ALTO':
                $class= 'red';
                break;
            case 'SEVERO':
                $class= 'red';
                break;
            default:
                $class= 'red';
                break;
        }

        return $class;
    }


    public function write_img($array) {
        $class= $this->get_class($array);
    //    $img= $this->get_alarm($class);
        echo $class;
    }

    public function write_html($array) {
        $class= $this->get_class($array);

        $html= "<div class='alarm-box $class' onclick='javascript:mostrar(".$array['id'].")'>".$nivel_array[$value]."</div>";
        echo $html;
    }

    public function test_if_synchronize($array) {
        $this->if_synchronize= false;

        $conectado= $this->array_procesos[$array['id_proceso']]['conectado'];

        if (!empty($array['id_proceso']) && ($conectado != _LAN && $array['id_proceso'] != $_SESSION['local_proceso_id'])) {
            $html.= "<img src='../img/transmit.ico' title='recivido desde servidor remoto' /> ";
            $this->if_synchronize= true;
        }
        return $this->if_synchronize;
    }
}


?>