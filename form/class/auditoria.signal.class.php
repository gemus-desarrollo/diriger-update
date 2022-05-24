<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../php/config.inc.php";
include_once "../php/class/base.class.php";
include_once "../php/class/time.class.php";
include_once "../php/class/proceso.class.php";

include_once "../php/class/document.class.php";

include_once "evento.signal.class.php";

class Taudit_signals extends Tevento_signals {
    protected $alarm;
    protected $type;
    public $to_do;
    public $obj_doc;
    public $print_reject;
    public $cant_print_reject;
    public $cant_show;

    public $html;
    public $to_write;
    public $thtd;

    public function Taudit_signals($clink) {
        $this->to_write= true;
        $this->thtd= 0;
        
        $this->cant_print_reject= 0;
        $this->cant_show= 0;  
        $this->clink= $clink;

        Tevento_signals::__construct($clink);
        
        $this->className= 'Taudit_signals';
    }

    public function write_img($array) {
        $this->get_class($array);
        return $this->get_alarm($array);
    }

    public function write_line($array) {
        $this->html= null;
        $interval= textparse($array['evento']);

        switch($this->print_reject) {
            case(_PRINT_REJECT_NO):
                if ($array['user_check']) {
                    ++$this->cant_print_reject;
                    return null;
                }

                if (!empty($array['rechazado']) && $array['cumplimiento'] == _SUSPENDIDO) {
                    ++$this->cant_print_reject;
                    return null;
                }
                break;

            case(_PRINT_REJECT_OUT):
                if (!empty($array['rechazado']) && $array['cumplimiento'] == _SUSPENDIDO) {
                    ++$this->cant_print_reject;
                    return null;
                }
                break;
        }

        ++$this->cant_show;

        $line= "$interval <br/>";

        if ($this->to_write) 
            echo $line;
        else 
            $this->html= $line;

        return $this->html;
    }

    public function write_html($array, $if_interval= true, $block_reg= false, $block_repro= false) {
        $if_interval= !is_null($if_interval) ? $if_interval : true;

        $block_reg= !is_null($block_reg) ? $block_reg : false;
        $block_reg= setZero($block_reg);

        $block_repro= !is_null($block_repro) ? $block_repro : false;
        $block_repro= setZero($block_repro);

        $this->id= $array['id'];
        $id_proceso= setZero($array['id_proceso']);
        $day= $if_interval ? 1 : 0;

        global $eventos_cump;
        $interval= textparse($array['evento']);
        $cumplimiento= $array['cumplimiento'];
        
        $this->html= null;

        switch($this->print_reject) {
            case(_PRINT_REJECT_NO):
                if ($array['user_check']) {
                    ++$this->cant_print_reject;
                    return null;
                }

                if (!empty($array['rechazado']) && $array['cumplimiento'] == _SUSPENDIDO) {
                    ++$this->cant_print_reject;
                    return null;
                }
                break;

            case(_PRINT_REJECT_OUT):
                if (!empty($array['rechazado']) && $array['cumplimiento'] == _SUSPENDIDO) {
                    ++$this->cant_print_reject;
                    return null;
                }
                break;
        }

        ++$this->cant_show;

        $this->cumplimiento= $cumplimiento;
        $this->fecha_fin_plan= $array['fecha_fin_plan'];

        switch($this->cumplimiento) {
            case _NO_INICIADO:
                $class= 'blank';
                break;
            case _EN_CURSO:
                $class= 'yellow';
                break;
            case _COMPLETADO:
                $class= 'green';
                break;
            case _ESPERANDO:
                $class= 'orange';
                break;
            case _POSPUESTO:
            case _DELEGADO:
            case _CANCELADO:
            case _SUSPENDIDO:
                $class= 'red';
                break;
            case _INCUMPLIDO:
                $class= 'dark';
                break;
            default:
                $class= 'blank';
                break;
        }

        $xmsg= null;

        if (!empty($array['rechazado']) && ($this->cumplimiento == _CANCELADO || $this->cumplimiento == _DELEGADO || $this->cumplimiento == _SUSPENDIDO)) {
            $class= 'gray';
            $xmsg= "(Actividad rechazada o delegada) ";
        }

        $msg= "<B>".$eventos_cump[$array['cumplimiento']]."</B> <br/>";
        if ($if_interval) $msg.= "<EM>".$this->gen_str_interval($interval)."</EM><br />";
        $msg.= textparse($array['memo'], true);
        $msg.= '<p>'.$xmsg.'</p>';

        $html= "<div class=\'alarm-box $class\' style=\'max-width:100%;\' ";
        $html.= "onclick=\"javascript:ShowContentEvent($this->id,\'win-board-signal\', $day, $id_proceso, $block_reg, $block_repro); return true;\" ";
        $html.= "onmouseover=\"Tip('".addslashes($msg)."')\" onmouseout=\"UnTip()\">";

        if (!is_null($array['aprobado'])) {
            $html.= "<img src=\'../img/accept.ico\' title=\'aprobada por el jefe\' /> ";
        }

        if ($array['toshow'] == 2) {
            $html.= "<img src=\'../img/process.ico\' title=\'incluida en uno de los Planes Mensual o Anual de la Organización\' /> ";
        }

        $this->obj_doc->SetIdAuditoria($array['id']);
        $this->obj_doc->get_documentos(false);
        $cant_docs= $this->obj_doc->GetCantidad();
        if ($cant_docs) 
            $html.= "<img src='../img/docx-mac.ico' title='tiene documentos o archivos adjuntos' /> ";     
        
        $this->if_synchronize= false;
        if ($this->array_procesos[$array['id_proeceso_asigna']]['conectado'] != _LAN && $array['id_proceso_asigna'] != $_SESSION['local_proceso_id']) {
            $html.= "<img src='../img/transmit.ico' title='recivido desde servidor remoto' /> ";
            $this->if_synchronize= true;
        }
        
        $html.= "$interval</div>";
        if (!is_null($interval)) 
            $html.= "<br/>";
        $html= stripslashes($html);

        if ($this->to_write) 
            echo $html;
        else 
            $this->html= $html;

        return $this->html;
    }
    
    public function do_list($top, $month= null, $to_do= null) {
        $show= null;
        $interval= null;
        $this->month= (int)$month;
        $this->to_do= !empty($to_do) ? $to_do : null;

        if (!$top['hit']) {
            $interval= $this->set_day_intervals($top);
            if (is_null($interval)) 
                return null;

            if ($this->to_do !=  _PRINT_IND) 
                $show= $this->write_html($top, null, false, true);
            else 
                $show= $this->write_line($top);
        }

        if ($top['hit']) {
            $id= $top['id'];
            reset($this->array_auditorias);

            foreach ($this->array_auditorias as $array) {
                if ($array['id_auditoria'] != $id) 
                    continue;

                $interval= $this->set_day_intervals($array);
                if (is_null($interval)) 
                    continue;

                if ($this->to_do !=  _PRINT_IND) 
                    $show= $this->write_html($array, null, false, true);
                else 
                    $show= $this->write_line($array);
            }
        }

        return $show;
    }

    private function set_day_intervals(&$array) {
        $fecha_inicio= $array['fecha_inicio_plan'];
        $fecha_fin= $array['fecha_fin_plan'];
        $interval= null;

        $year0= (int)date('Y', strtotime($fecha_inicio));
        $month0= (int)date('m', strtotime($fecha_inicio));
        $day0= (int)date('d', strtotime($fecha_inicio));
        $year1= (int)date('Y', strtotime($fecha_fin));
        $month1= (int)date('m', strtotime($fecha_fin));
        $day1= (int)date('d', strtotime($fecha_fin));

        if ($year0 != $this->year && $year1 != $this->year) 
            return null;
        if ($month0 != $this->month && $month1 != $this->month) 
            return null;

        if ($year0 == $this->year && $year1 == $this->year) {
            if ($month0 == $this->month && $month1 == $this->month) $interval= (int)$day0 != (int)$day1 ? "$day0-$day1" : $day0;
            if (($month0 < $this->month || ($month0 == 12 && $this->month == 1)) && $month1 == $this->month) $interval= "-$day1";
            if ($month0 == $this->month && ($month1 > $this->month || ($month1 == 1 && $this->month == 12))) $interval= "$day0-";
        }

        if ($year0 < $this->year && $year1 == $this->year) {
            if ($month1 == $this->month) $interval= "-$day1";
        }

        if ($year0 == $this->year && $year1 > $this->year) {
            if ($month0 == $this->month) $interval= "$day0-";
        }

        $array['evento']= $interval;

        return $interval;
    }

    private function gen_str_interval($interval) {
        global $meses_array;
        $str= null;
        $array= preg_split('/-/', $interval);

        if (!empty($array[0])) 
            $str= " desde el ".$array[0];
        if (!empty($array[1])) 
            $str= "hasta el ".$array[1];
        $str.= " ".$meses_array[$this->month];

        return $str;
    }
}
