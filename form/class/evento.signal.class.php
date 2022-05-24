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
include_once "../php/class/orgtarea.class.php";
include_once "../php/class/evento.class.php";

class Tevento_signals extends Torgtarea {
    public $to_do;
    public $obj_plan;
    public $obj_doc;
    public $print_reject;
    public $total;
    public $cant_print_reject;
    public $cant_show;

    public $html;
    public $to_write;
    public $tipo_plan;

    public $if_synchronize;
    public $user_check;
    public $toshow;

    public $calendar_type;

    public $array_status_eventos_ids;
    

    public function __construct($clink) {
        $this->clink= $clink;
        Torgtarea::__construct($clink);

        $this->array_jefes= null;
        $this->to_write= true;
        $this->cant_print_reject= 0;
        $this->total= 0;
        $this->cant_show= 0;

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->listar_all(null, false, null, null, false);
        $this->array_procesos= $obj_prs->array_procesos;

        $this->className= 'Tevento_signals';
    }

    public function SetObj(&$obj) {
        $this->obj_plan = &$obj;
    }

    private function test_chief($id_responsable) {
        if (is_null($this->array_jefes))
            $this->listar_chief($this->id_usuario);

        if (array_key_exists($id_responsable, $this->array_jefes))
            return true;
        else
            return false;
    }

    private function get_alarm() {
        if ($this->cumplimiento == _COMPLETADO) {
            $class = "green";
            return $class;
        }
        if ($this->cumplimiento == _INCUMPLIDO) {
            $class = "dark";
            return $class;
        }
        if ($this->cumplimiento > _COMPLETADO) {
            $class = "red";
            return $class;
        }

        $time= new TTime();
        $class= 'blank';

        if (empty($this->month))
            $this->month= $time->GetMonth();
        if (empty($this->year))
            $this->year= $time->GetYear();
        if (empty($this->hh))
            $this->hh= $time->GetHour();
        if (empty($this->mi))
            $this->mi= $time->GetMinute();

        if (empty($this->day) && ($this->month == $time->GetMonth()) && ($this->year == $time->GetYear())) {
            $this->day= $time->GetDay();
            $this->hh= $time->GetHour();
            $this->mi= $time->GetMinute();
        }
        if (empty($this->day) 
            && (($this->year == $time->GetYear() &&  $this->month != $time->GetMonth()) || $this->year != $time->GetYear())) {
            $time->longmonth($this->month, $this->year);
            $this->hh= 23;
            $this->mi= 59;
        }

        $strtime= $this->year.'/'.$this->month.'-'.$this->day.' '.$this->hh.':'.$this->mi.':00';

        $diff= diffDate($this->fecha_fin_plan, $strtime);

        if ($diff['y'] > 0 || ($diff['y'] == 0 && $diff['m'] > 0) || ($diff['y'] == 0 && $diff['m'] == 0 && $diff['d'] > 0)) {
            if ($this->cumplimiento < _COMPLETADO)
                $class = "red";
            if ($this->cumplimiento == _COMPLETADO)
                $class = "green";
            if ($this->cumplimiento > _COMPLETADO && $this->cumplimiento < _INCUMPLIDA)
                $class = 'orange';
            if ($this->cumplimiento == _INCUMPLIDO)
                $class = 'dark';

            return $class;
        }

        $diff = diffDate($this->fecha_inicio_plan, $strtime);

        if ($diff['y'] < 0 || ($diff['y'] == 0 && $diff['m'] < 0) || ($diff['y'] == 0 && $diff['m'] == 0 && $diff['d'])) {
            if ($this->cumplimiento >= _POSPUESTO && $this->cumplimiento < _INCUMPLIDA)
                $class = "blank";
            if ($this->cumplimiento == _INCUMPLIDO)
                $class = 'dark';
            return $class;
        }

        return null;
    }

    private function get_str($array) {
        $time= substr($array['time'], 0, strpos($array['time'],'-'));
        if ($this->to_do == _MES || $this->to_do == _PRINT_IND)
            $str= "<strong>".$time."</strong>";
        else
            $str= '';

        $len= strlen($array['evento']);
        $evento= textparse($array['evento'], true);
        $evento= stripslashes($evento);

        switch($this->to_do) {
            case _YEAR:
                $evento= utf8_encode(trim(substr(utf8_decode($evento), 0, 255)));
                if ($len > 255)
                    $evento.= '...';
                break;
            case _MES:
                if ($this->calendar_type == 0) {
                    $evento= utf8_encode(trim(substr(utf8_decode($evento), 0, 20)));
                    if ($len > 20)
                        $evento.= '...';
                } else
                    $evento= utf8_encode(trim(utf8_decode($evento)));
                break;
            case _SEMANA:
                $evento= utf8_encode(trim(substr(utf8_decode($evento), 0, 35)));
                if ($len > 35)
                    $evento.= '...';
                break;
        }
        $str.= '&nbsp;'.$evento;

        if ($this->to_do == _MES || $this->to_do == _MES_GENERAL)
            return $str;

        $len= strlen($array['lugar']);
        $lugar= textparse($array['lugar'], true);

        if ($this->to_do == _SEMANA) {
            $lugar= trim(substr($lugar, 0, 35));
            if ($len > 35)
                $lugar.= '...';
        }

        return $str;
    }

    private function _write_html_div($array, $id_proceso, $rightside, $class, $width) {
        $html= "<div class='alarm-box $class' style='max-width:{$width};' ";
        $block_repro= !empty($array['id_tarea']) || !empty($array['id_auditoria']) || !empty($array['id_tematica']) ? 'true' : 'false';
        $rightside= $rightside ? "rightsideClicked();" : "leftsideClicked();";

        if (!empty($array['id_proyecto'])) {
            $html.= "onclick=\"$rightside ShowContentEvent($this->id, 'win-board-signal-project', $this->day, $id_proceso, false, $block_repro); return true;\" ";
        } else {
            $html.= "onclick=\"$rightside ShowContentEvent($this->id, 'win-board-signal', $this->day, $id_proceso, false, $block_repro); return true;\" ";
        }

        $html.= "title='".textparse($array['evento'])." <p>".textparse($array['memo'])."</p>' ";
        $html.= ">";

        if (!empty($array['id_copyfrom'])) {
            $html.= "<img src='../img/hour-add.ico' title='es el resultado de una reprogramación (puntualización)' /> ";
        }
        if (!empty($array['toshow']) && $this->to_do != _PRINT_IND) {
            if ($this->to_do == _MES) {
                $img= "<img src='../img/process.ico' title='incluida en uno de los Planes Mensuales o Anual actividades de la Organización' /> ";
            }
            if ($array['toshow'] == 2 && $this->to_do == _MES_GENERAL) {
                $img= "<img src='../img/process.ico' title='incluida en el Plan Anual Actividades de la Organización' /> ";
            }
            if ($this->array_procesos[$array['id_proceso_asigna']]['tipo'] < $_SESSION['entity_tipo']) {
                $img= "<img src='../img/process.ico' title='asignada por una Organización Superior' /> ";
            }
            $html.= $img;
        }

        if (!empty($array['toshow']) && $this->to_do == _YEAR_PLANNING) {
            if ($array['toshow'] == 1) {
                $html.= "<img src='../img/process.ico' title='incluida en uno de los Planes Mensuales o Anual de actividades de la Organización' /> ";
            }
            if ($array['toshow'] == 2) {
                $html.= "<img src='../img/process.ico' title='incluida en el Plan Anual de Activiades la Organización' /> ";
            }
        }

        if (!is_null($array['id_tipo_reunion'])) {
            $html.= "<img src='../img/meeting.ico' title='reunión o asamblea' /> ";
        }
        if (!empty($array['id_tarea']) && empty($array['id_vento'])) {
            $html.= "<img src='../img/monitor-edit.ico' title='generado por Diriger a partir de una tarea' /> ";
        }
        if (!empty($array['id_auditoria'])) {
            $html.= "<img src='../img/icon-audit.ico' title='generado por Diriger a partir de una auditoria o accion de control' /> ";
        }
        if (!empty($array['id_tematica'])) {
            $html.= "<img src='../img/tematica.ico' title='Es un acuerdo de una reunion o consejo' /> ";
        }
        if (!empty($array['id_archivo'])) {
            $html .= "<img src='../img/archive.ico' title='Indicación recivida a través de la oficina de Archivo o del Despacho' /> ";
        }
        if (!is_null($array['aprobado'])) {
            $html.= "<img src='../img/accept.ico' title='aprobada por el jefe' /> ";
        }
        if ($array['user_check'] || ($this->to_do == _MES && $array['user_check_plan'])) {
            $html.= "<img src='../img/icon-eye.ico' title='el usuario es responsable pero no participa' /> ";
        }
        if ($this->if_synchronize) {
            $html.= "<img src='../img/transmit.ico' title='recivido desde servidor remoto' /> ";
        }
        if ($this->to_do == _MES) {
            if ($array['id_usuario'] != $this->id_usuario && $array['id_responsable'] != $this->id_usuario) {
                if ($this->test_chief($array['id_responsable']) || $this->test_chief($array['id_usuario'])) {
                   $html.= "<img src='../img/user-go.ico' title='asignada por el jefe inmediato' /> ";
                } else {
                    $html.= "<img src='../img/user-comment.ico' title='asignado por otro usuario' /> ";
                }
            }
        }

        return $html;
    }

    public function write_html($array, $rightside= false) {
        $id_proceso= setZero($array['id_proceso']);
        $cumplimiento= $array['cumplimiento'];

        $this->html= null;

        $this->if_synchronize= $this->test_if_synchronize($array);
        $this->toshow= $array['toshow'];
        $this->user_check= $array['user_check'];
        ++$this->total;

        if ($this->to_do != _YEAR && $this->to_do != _MES_GENERAL_STACK) {
            switch ($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && ($array['user_check_plan'] || $array['user_check'])) {
                        ++$this->cant_print_reject;
                        return null;
                    }

                    if ((!empty($array['rechazado'])
                            && ($array['cumplimiento'] == _SUSPENDIDO || $array['cumplimiento'] == _CANCELADO
                                    || $array['cumplimiento'] == _DELEGADO || $array['cumplimiento'] == _REPROGRAMADO))
                            || ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && ($array['user_check_plan'] || $array['user_check']))) {
                        ++$this->cant_print_reject;
                        return null;
                    }
                    break;

                case(_PRINT_REJECT_OUT):
                    if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
                        if (!empty($array['rechazado'])
                                && ($array['cumplimiento'] == _SUSPENDIDO || $array['cumplimiento'] == _CANCELADO
                                        || $array['cumplimiento'] == _DELEGADO)) {
                            ++$this->cant_print_reject;
                            return null;
                        }
                    }
                    break;
                    
                case(_PRINT_REJECT_OUT):
                    break;
            }
        }

        if ($this->to_do == _MES_GENERAL_STACK) {
            $result= $this->_get_status_intervals($array);
            if ($result[1] == $result[0]) {
                $this->cant_print_reject+= $result[1];
                return null;
            }
            $this->cant_print_reject+= $result[1];    
        }

        return $this->_write_html($array, $cumplimiento, $id_proceso, $rightside);
    }

    private function _write_html($array, $cumplimiento, $id_proceso, $rightside) {   
        global $eventos_cump;
 
        ++$this->cant_show;
        $this->id= $array['id'];
        $this->cumplimiento= $cumplimiento;
        $this->fecha_fin_plan= $array['fecha_fin'];

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
            case _DELEGADO:
            case _REPROGRAMADO:
                $class= 'gray';
                break;
            case _POSPUESTO:
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

        if (!empty($array['rechazado']) && ($this->cumplimiento == _CANCELADO || $this->cumplimiento == _SUSPENDIDO || $this->cumplimiento == _REPROGRAMADO)) {
            $class= 'gray';
            $xmsg= "(Actividad o tarea rechazada) ";
        }

        if (!is_null($array['id_tarea'])) {
            $class.= " task";
            $xmsg.= "(Actividad generada por el Diriger a partir de una tarea asignada) ";
        }

        $width= ($this->to_do != _MES_GENERAL && $this->to_do != _MES) || ($this->to_do == _MES && (int)$this->calendar_type != 2) ? '200px' : '100%';

        $msg= "<b>".$eventos_cump[$array['cumplimiento']]."</b>";
        $text= textparse($array['evento'], true);
        $text= htmlspecialchars($text, ENT_QUOTES);
        $msg.= "<p style=\'font-weight:bold\'>$text</p>";

        $memo= !empty($array['memo']) ? $array['memo'] : $array['descripcion'];
        if (!empty($memo)) {
            $memo= purge_html(trim($memo));
            $memo= textparse($memo);
            $memo= htmlspecialchars($memo, ENT_QUOTES);
            $msg.= $memo;
        }

        if (!empty($xmsg)) {
            $msg.= '<p>'.$xmsg.'</p>';
        }

        $html= $this->_write_html_div($array, $id_proceso, $rightside, $class, $width);

        $this->obj_doc->SetIdEvento($array['id']);
        $this->obj_doc->get_documentos(false);
        $cant_docs= $this->obj_doc->GetCantidad();
        if ($cant_docs) {
            $html.= "<img src='../img/docx-mac.ico' title='tiene documentos o archivos adjuntos' /> ";
        }
        $html.= $this->get_str($array);
        $html.= "</div>";

        if ($this->to_write) {
            echo $html;
        } else {
            $this->html= $html;
        }
        return $this->html;
    }

    private function _get_status_intervals($event) {
        $k= 0;
        $cant_print_reject= 0;

        foreach ($event['month'] as $day => $id) {
            if (empty($id))
                continue;
            ++$k;
            $cumplimiento= $this->array_status_eventos_ids[$id][0];
            $rechazado= $this->array_status_eventos_ids[$id][1];

            $continue= true;
            switch ($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if ((!empty($rechazado)
                            && ($cumplimiento == _SUSPENDIDO || $cumplimiento == _CANCELADO || $cumplimiento == _DELEGADO || $cumplimiento == _REPROGRAMADO))) {
                        $continue= false;
                    }
                    break;

                case(_PRINT_REJECT_OUT):
                    $continue= true;
                    break;
            }
            if (!$continue) {
                ++$cant_print_reject;
                continue;
            }                
        }

        return array($k, $cant_print_reject);
    }

    public function get_status_intervals($array_eventos) {
        $cant_print_reject= 0;
        $array_eventos= _ksort($array_eventos);

        $i= 0;
        $k= 0;
        reset($array_eventos);
        foreach ($array_eventos as $event) {
            ++$i;
            $array= $this->_get_status_intervals($event);
            $k+= $array[0];
            $cant_print_reject+= $array[1];
        }
        
        reset($array_eventos);
        $this->cant_print_reject+= $cant_print_reject;
        return $k > $cant_print_reject ? true : false;
    }

    public function write_line($array) {
        $this->html= null;

        $this->if_synchronize= $this->test_if_synchronize($array);
        $this->toshow= $array['toshow'];
        $this->user_check= $array['user_check'];

        if ($this->to_do != _YEAR && $this->to_do != _MES_GENERAL) {
            if ($this->if_synchronize && $this->user_check && $array['id_proceso_asigna'] != $_SESSION['superior_proceso_id'])
                return null;
        }

        if ($this->to_do != _YEAR && $this->to_do != _MES_GENERAL_STACK) {
            switch($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if ($array['user_check']) {
                        ++$this->cant_print_reject;
                        return null;
                    }

                    if ((!empty($array['rechazado'])
                            && ($array['cumplimiento'] == _SUSPENDIDO || $array['cumplimiento'] == _CANCELADO || $array['cumplimiento'] == _DELEGADO  
                                || $array['cumplimiento'] == _REPROGRAMADO))
                            || ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $array['user_check_plan'])) {
                        ++$this->cant_print_reject;
                        return null;
                    }
                    break;

                case(_PRINT_REJECT_OUT):
                    if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
                        if (!empty($array['rechazado']) 
                                && ($array['cumplimiento'] == _SUSPENDIDO || $array['cumplimiento'] == _CANCELADO
                                        || $array['cumplimiento'] == _DELEGADO)) {
                            ++$this->cant_print_reject;
                            return null;
                    }   }
                    break;
                    
                case(_PRINT_REJECT_OUT):
                    break;    
            }

            ++$this->cant_show;
        }

        $line= textparse($array['evento'], true);
        $line= stripslashes($line);
        $line= htmlspecialchars($line, ENT_QUOTES);

        $line= "<strong>".substr($array['time'],0,8).'</strong> '.$line;
        // $line.= ", <em>".$array['lugar'].'</em>';
        $line.= "<br />";

        if (is_null($array['time'])|| is_null($array['evento']))
            $line= "<br /><br /><br />";

        if ($this->to_write)
            echo $line;
        else
            $this->html= $line;

        return $this->html;
    }

    public function list_day($array_status, $to_do, $day= null) {
        $this->to_do= $to_do;
        if (!is_array($array_status))
            return null;

        reset($array_status);
        foreach ($array_status as $array) {
            $this->do_list($array, null, $day);
        } 
    }

    public function do_list($array, $to_do= NULL, $day= null) {
        $this->day= $day;
        $show= false;

        if (is_array($array))
            reset($array);
        if (!empty($to_do))
            $this->to_do= $to_do;

        if ($this->to_do !=  _PRINT_IND)
            $show= $this->write_html($array);
        else
            $show= $this->write_line($array);

        return $show;
    }

    public function write_msg_process($array) {
        global $Ttipo_proceso_array;

        $plan= null;
        switch($array['toshow']) {
            case 1:
                $plan= "Plan General Mensual";
                break;
            case 2:
                $plan= "Plan General Anuales";
                break;
        }

        $proceso= null;
        if (empty($array['id_proceso'])) {
            $obj= new Tproceso($this->clink);
            $obj->SetIdProceso($array['id_proceso']);
            $obj->Set();

            $proceso= $obj->GetNombre().', '.$Ttipo_proceso_array[$obj->GetTipo()];            
        }

        return !empty($array['id_proceso']) ? "$plan  $proceso" : $plan;
    }

    public function write_html_day($array, $rightside= true) {
        global $eventos_cump;
        $id_proceso= setZero($array['id_proceso']);

        $cumplimiento= $array['cumplimiento'];
        if (empty($cumplimiento)) 
            $xvalue= 0;
        $this->html= null;

        if ($this->to_do != _YEAR && $this->to_do != _MES_GENERAL_STACK) {
            switch($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if ($array['user_check'] && $this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL ) {
                        ++$this->cant_print_reject;
                        return null;
                    }

                    if ((!empty($array['rechazado'])
                            && ($array['cumplimiento'] == _SUSPENDIDO || $array['cumplimiento'] == _CANCELADO || $array['cumplimiento'] == _DELEGADO
                                || $array['cumplimiento'] == _REPROGRAMADO))
                            || $array['user_check_plan']) {
                        ++$this->cant_print_reject;
                        return null;
                    }
                    break;

                case(_PRINT_REJECT_OUT):
                    if ((!empty($array['rechazado'])
                            && ($array['cumplimiento'] == _SUSPENDIDO || $array['cumplimiento'] == _CANCELADO || $array['cumplimiento'] == _DELEGADO
                                || $array['cumplimiento'] == _REPROGRAMADO))
                            || $array['user_check_plan']) {
                        ++$this->cant_print_reject;
                        return null;
                    }
                    break;
            }
        }

        ++$this->cant_show;
        $this->id= $array['id'];
        $this->cumplimiento= $cumplimiento;
        $this->fecha_fin_plan= $array['fecha_fin'];

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
            case _DELEGADO:
                $class= 'gray';
                break;
            case _ESPERANDO:
                $class= 'orange';
                break;
            case _POSPUESTO:
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

        $inicio= DateTime_object($array['fecha_inicio']);
        $this->day= $inicio['d'];
        $fin= DateTime_object($array['fecha_fin']);

        $xmsg= null;

        if (!empty($array['rechazado']) && ($this->cumplimiento == _CANCELADO || $this->cumplimiento == _SUSPENDIDO || $this->cumplimiento == _DELEGADO)) {
            $class = 'gray';
            $xmsg = "(Actividad o tarea rechazada) ";
        }

        $msg= "<B>".$eventos_cump[$array['cumplimiento']]."</B>";
        $msg.= "<p style='font-weight:bold'>".textparse($array['evento'], true)."</p>";
        $msg.= $array['memo'];

        if (!empty($memo)) {
            $memo= purge_html(trim($memo), false);
            $msg.= textparse($memo);
        }
        if (!empty($xmsg))
            $msg.= '<p>'.$xmsg.'</p>';

        $rightside= $rightside ? "rightsideClicked();" : "leftsideClicked();";

        $html= "<div class='alarm-box $class' style='max-width:90px;' ";
        $html.= "onclick=\"$rightside ShowContentEvent($this->id,'win-board-signal', $this->day, $id_proceso, false, false); return true;\" ";
        $html.= "onmouseover=\"Tip('".addslashes($msg)."')\" onmouseout=\"UnTip()\">";

        if (!empty($array['id_copyfrom']))
            $html.= "<img src='../img/hour-add.ico' title='es el resultado de una reprogramación (puntualización)' /> ";

        if (!empty($array['toshow']) && $this->to_do != _PRINT_IND) {
            if ($this->to_do == _MES)
                $html.= "<img src='../img/process.ico' title='incluida en uno de los Planes Mensual o Anual de la Empresa' /> ";
            if ($array['toshow'] == 2 && $this->to_do == _MES_GENERAL)
                $html.= "<img src='../img/process.ico' title='incluida en el Plan Anual de la Empresa' /> ";
        }
        /*
        if (!is_null($array['id_tipo_reunion']))
            $html.= "<img src='../img/meeting.ico' title='reunion o asamblea' /> ";
        */
        if (!empty($array['id_auditoria']))
            $html.= "<img src='../img/icon-audit.ico' title='generado por Diriger a partir de una auditoria o accion de control' /> ";

        if (!empty($array['id_tematica']))
            $html.= "<img src='../img/tematica.ico' title='Es un acuerdo de una reunion o consejo' /> ";

        $this->obj_doc->SetIdEvento($array['id']);
        $this->obj_doc->get_documentos(false);
        $cant_docs= $this->obj_doc->GetCantidad();
        if ($cant_docs) 
            $html.= "<img src='../img/docx-mac.ico' title='tiene documentos o archivos adjuntos' /> ";

        if (!is_null($array['aprobado']))
            $html.= "<img src='../img/accept.ico' title='aprobada por el jefe' /> ";

        $html.= "Día:".(int)$inicio['d'].' a las:'.odbc2ampm($inicio['h'].':'.$inicio['i'].':00');
        $html.= "</div>";

        if ($this->to_write)
            echo $html;
        else
            $this->html= $html;

        return $this->html;
    }

    public function test_if_synchronize($array) {
        $this->if_synchronize= false;
        $conectado= $this->array_procesos[$array['id_proceso_asigna']]['conectado'];

        if (!empty($array['id_proceso_asigna']) && $conectado != _LAN && $array['id_proceso_asigna'] != $_SESSION['local_proceso_id']) {
            $html.= "<img src='../img/transmit.ico' title='recivido desde servidor remoto' /> ";
            $this->if_synchronize= true;
        }
        return $this->if_synchronize;
    }

    public function test_if_entity($array) {
        $array_prs= $this->array_procesos[$array['id_proceso_asigna']];
        $id_entity_prs= !empty($array_prs['id_entity']) ? $array_prs['id_entity'] : $array_prs['id'];
        $tipo= $this->array_procesos[$id_entity_prs]['tipo'];

        $if_entity= false;
        if ($id_entity_prs == $_SESSION['id_entity'] || $array_prs['id_proceso'] == $_SESSION['id_entity'])
            $if_entity= true;

        return array($if_entity, $tipo);
    }
}

function _ksort($array) {
    if (is_null($array) || !is_array($array))
        return null;
    $obj= new ArrayObject($array);
    $obj->ksort();

    return $obj->getArrayCopy();
}

function build_intervals($array, $array_status_eventos_ids= null, $print_reject= _PRINT_REJECT_NO) {
    $cant_print_reject= 0;
    $cant= count($array);
    if (empty($cant))
        return;
    if (array_sum($array) == 0)
        return;

    if ($cant == 1) {
        $continue= true;
        list($day, $id)= each($array);
        $cumplimiento= $array_status_eventos_ids[$id][0];
        $rechazado= $array_status_eventos_ids[$id][1];

        switch($print_reject) {
            case(_PRINT_REJECT_NO):
                if ((!empty($rechazado)
                        && ($cumplimiento == _SUSPENDIDO || $cumplimiento == _CANCELADO || $cumplimiento == _DELEGADO || $cumplimiento == _REPROGRAMADO))) {
                    $continue= false;
                }
                break;

            case(_PRINT_REJECT_OUT):
                $continue= true;
                break;
        }

        if ($continue)
            echo $day;

        return ++$cant_print_reject;
    }

    return _print_intervals($array, $array_status_eventos_ids, $print_reject);
}

function _print_intervals($array, $array_status_eventos_ids= null, $print_reject= _PRINT_REJECT_NO) {
    $cant= count($array);
    $cant_print_reject= 0;
    $array= _ksort($array);
    reset($array);
    $i= 0;
    $p= 0;
    $t= 0;
    $f= 0;
    $d= 0;

    foreach ($array as $day => $id) {
        ++$i;
        $cumplimiento= $array_status_eventos_ids[$id][0];
        $rechazado= $array_status_eventos_ids[$id][1];
        $continue= true;

        if (empty($id)) {
            if ($i < $cant)
                $continue= false;
        } else {
            switch($print_reject) {
                case(_PRINT_REJECT_NO):
                    if ((!empty($rechazado)
                            && ($cumplimiento == _SUSPENDIDO || $cumplimiento == _CANCELADO || $cumplimiento == _DELEGADO || $cumplimiento == _REPROGRAMADO))) {
                        $continue= false;
                    }
                    break;

                case(_PRINT_REJECT_OUT):
                    $continue= true;
                    break;
            }
            if (!$continue) {
                ++$cant_print_reject;
                continue;
            }
            $d= (int)$day;
        }

        if ($i == $cant) {
            echo $f ? ", " : "";
            if ($d > 0 && $t > 0)
                echo $d > $t ? "$t-$d" : "$d";
            elseif ($d > 0)
                echo $d;

            continue;
        }

        if ($p == 0) {
            $t= $d;
            $p= $d;
        }

        if ($d-$p == 1)
            $p= $d;

        if ($d-$p > 1) {
            echo $f ? ", " : "";
            echo $p > $t ? "$t-$p" : "$t";

            $p= $d;
            $t= $d;
            ++$f;
        }
    }

    return $cant_print_reject;
}


function test_if_eventos_in_hour($array_eventos, $hh) {
    reset($array_eventos);
    foreach ($array_eventos as $evento) {
        if ((int)date('H', strtotime($evento['fecha'])) == (int)$hh)
            return true;
    }
    return false;
}

function get_array_eventos_day_hour($array_eventos, $dd, $hh) {
    $array= array();

    reset($array_eventos);
    foreach ($array_eventos as $id => $evento) {
        if (((int)date('H', strtotime($evento['fecha'])) == (int)$hh)
            && ((int)date('d', strtotime($evento['fecha'])) == (int)$dd))
                $array[$id]= $evento;
    }
    return $array;
}

?>