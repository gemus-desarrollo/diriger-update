<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

//include_once "../../php/class/proyecto.class.php";
//include_once "../../php/class/time.class.php";


class Ttarea_signals extends Tproyecto {
    private $array_status;
       
    
    private function eval_tarea() {
        $array= $this->array_status;
        $status= _EN_ESPERA;
        $now= date("Y-m-d");

        if (!empty($array[2]['fecha_fin_real']) && !empty($array[0])) {
            if ($array[0]['valor'] >= 100) 
                $status= _CUMPLIDA;
            if ($array[0]['valor'] < 100) 
                $status=_DETENIDA;
        }

        if (!empty($array[2]['fecha_inicio_real'])) {
            if (strtotime($array[2]['fecha_inicio_real']) > strtotime($array[2]['fecha_inicio_plan'])) 
                $status=_ATRAZADA;
            if (strtotime($array[2]['fecha_inicio_real']) < strtotime($array[2]['fecha_inicio_plan'])) 
                $status=_EN_TIEMPO;
        } else {
            if (strtotime($array[2]['fecha_fin_plan']) < strtotime($now)) 
                $status=_DETENIDA;
            if (strtotime($array[2]['fecha_inicio_plan']) >= strtotime($now)) 
                $status= _EN_ESPERA;
            if ((strtotime($array[2]['fecha_inicio_plan']) < strtotime($now)) && (strtotime($array[2]['fecha_fin_plan']) > strtotime($now))) 
                $status= _DESACTUALIZADA;
        }
        
        if (!empty($array[1]['reg_fecha'])) {
            if (!empty($array[0]['reg_fecha'])) {
                if ((strtotime($array[0]['reg_fecha']) > strtotime($array[2]['fecha_fin_plan'])) && $array[0]['valor'] < 100) 
                    $status= _ATRAZADA;
                
                if (strtotime($array[0]['reg_fecha']) <= strtotime($array[1]['reg_fecha'])) {
                    if ($array[0]['valor'] >= $array[1]['valor']) 
                        $status= _EN_TIEMPO;
                } else {
                    if ($array[0]['valor'] < $array[1]['valor']) 
                        $status= _ATRAZADA;
                }   
            }            
        }

        return $status;
    }
    

    private function set_class() {
        $status= $this->eval_tarea();
        
        switch($status) {
            case _DESACTUALIZADA:
                $class= 'red';
                break; 
            case _ATRAZADA:
                $class= 'orange';
                break;                
            case _EN_TIEMPO:
                $class= 'blank';
                break;             
            case _CUMPLIDA:
                $class= 'green';
                break;             
            case _EN_ESPERA:
                $class= 'gray';
                break;
            case _DETENIDA:
                $class= 'yellow';
                break;                                             
            default:
                $class= 'blank';
                break;             
        }
        
        return $class; 
    } 
    
    
    public function getClass($array) {
        $this->array_status= $array;
        return $this->set_class();
    }   


    public function get_alarm_gtask($array) {
        global $eventos_cump;
        $class = null;
        $msg = null;

        $this->cumplimiento= !empty($array['cumplimiento']) ? $array['cumplimiento'] : _NO_INICIADO;
        $this->id= $array['id'];    

        switch($this->cumplimiento) {
            case _NO_INICIADO:
                $class= 'gtaskblank';
                break;                                   
            case _EN_CURSO:
                $class= 'gtaskyellow';
                break;
            case _COMPLETADO:
                $class= 'gtaskgreen';
                break;                
            case _ESPERANDO:
                $class= 'gtaskorange';
                break;
            case _DELEGADO:  

            case _DELEGADO:
            case _REPROGRAMADO:
                $class= 'gtaskgray';
                break;
            case _POSPUESTO:              
            case _CANCELADO:
            case _SUSPENDIDO:
                $class= 'gtaskred';
                break;  
            case _INCUMPLIDO:
                $class= 'gtaskdark';
                break;
            default:
                $class= 'gtaskblank';
                break;             
        } 
     
        if (is_null($msg)) 
            $msg= $eventos_cump[$this->cumplimiento];
        
        $array= array('class'=>$class, 'msg'=>$msg); 
        return $array;
    }
    
    public function get_alarm($array) {
        global $eventos_cump;
        $class = null;
        $msg = null;

        $this->cumplimiento= $array['cumplimiento'];
        $this->id= $array['id'];    

        switch($this->cumplimiento) {
            case _NO_INICIADO:
                $class= 'blank';
                break;                                   
            case _EN_CURSO:
                $class= 'blue';
                break;
            case _COMPLETADO:
                $class= 'green';
                break;                
            case _ESPERANDO:
                $class= 'yellow';
                break;             
            case _POSPUESTO:
            case _DELEGADO:
            case _CANCELADO:
            case _SUSPENDIDO:
                $class= 'orange';
                break;  
            case _INCUMPLIDO:
                $class= 'red';
                break; 				
            default:
                $class= 'blank';
                break;             
        }        
        
        if (!empty($array['rechazado']) && ($this->cumplimiento == _CANCELADO || $this->cumplimiento == _SUSPENDIDO || $this->cumplimiento == _DELEGADO)) {
            $class= 'gray';
            $msg= "RECHAZADA O DELEGADA";
        }
     
        if (is_null($msg)) 
            $msg= $eventos_cump[$this->cumplimiento];
        
        $array= array('class'=>$class, 'msg'=>$msg);
        return $array;
    }    
}


?>