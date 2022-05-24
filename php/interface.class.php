<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

require_once _ROOT_DIRIGER_DIR."inc.php";

require_once "config.inc.php";

require_once "class/base.class.php";

require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/time.class.php";
require_once "class/proceso_item.class.php";

require_once "class/document.class.php";
require_once "class/inductor.class.php";
require_once "class/peso.class.php";

require_once "class/asistencia.class.php";
require_once "class/tematica.class.php";
require_once "class/tmp_tables_planning.class.php";
require_once "class/register_planning.class.php";

require_once "class/tipo_evento.class.php";
require_once "class/evento.class.php";
require_once "class/tipo_reunion.class.php";
require_once "class/mirror_evento.class.php";

require_once "class/auditoria.class.php";
require_once "class/tarea.class.php";
require_once "class/lista_requisito.class.php";

require_once "class/lista.class.php";

if (!class_exists('Tbase_alert'))
    require_once "../tools/alert/php/base_alert.class.php";
if (!class_exists('Talert'))
    require_once "../tools/alert/php/alert.class.php";

require_once "class/schedule.class.php";

require_once "class/mail.class.php";
require_once "base.interface.php";


class TplanningInterface extends TbaseInterface {

    public $menu;

    protected $obj;
    protected $obj_doc;
    protected $obj_reg;
    protected $obj_assist;
    protected $obj_meeting;

    protected $radio_prs;
    protected $radio_date;
    protected $radio_user;

    public $className;

    protected $table;
    protected $field;
    public $error;

    protected $_id_responsable;
    protected $responsable;
    protected $id_calendar;

    protected $sendmail,
            $from,
            $cargo,
            $to;
    protected $date_list,
            $date_list_hit;

    protected $obj_matter;
    protected $obj_mail;
    protected $obj_sch;

    public $_id,
            $_id_code;
    protected $input_array_dates;
    public $changed,
            $updated;
    public $periodic;
    protected $user_check;
    
    protected $id_documento;
    protected $id_documento_code;
    protected $_periodic,
            $_periodicidad;

    protected $_id_evento,
            $_id_evento_code;
    protected $_id_auditoria,
            $_id_auditoria_code;
    protected $_id_tematica,
            $_id_tematica_code;

    protected $freeassign;
    protected $id_tipo_reunion;
    protected $id_tipo_reunion_code;
    protected $ifaccords;
    protected $id_secretary;

    protected $id_proceso_user_responsable;
    protected $id_proceso_code_user_responsable;
    protected $tipo_proceso_user_responsable;

    protected $init_row_temporary;
    protected $if_jefe;

    protected $id_lista,
            $id_lista_code;
    protected $id_requisito,
            $id_requisito_code;

    protected $array_proceso_no_local;

    protected $id_evento_ref,
            $id_evento_ref_code;
    protected $id_auditoria_ref,
            $id_auditoria_ref_code;

    protected $acc;

    protected $ifGrupo;

    protected $multi_query;

    public function __construct($clink= null) {
        TbaseInterface::__construct($clink);
        
        $this->multi_query= false;

        $this->id_calendar= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : $_POST['id_calendar'];
        $this->_id_responsable= null;

        $this->clink= $clink;
        $this->obj_code= new Tcode($this->clink);

        $this->control_list= 0;
        $this->accept_user_list= array();
        $this->denied_user_list= array();
        $this->accept_group_list= array();
        $this->denied_group_list= array();

        $this->id_evento_ref= null;
        $this->id_evento_ref_code= null;
        $this->id_auditoria_ref= null;
        $this->id_auditoria_ref_code= null;

        $this->changed= false;
        $this->updated= false;
        $this->_periodicidad= null;

        $this->cronos= date('Y-m-d H:i:s');

        $this->id_proceso= !empty($_POST['proceso']) ? $_POST['proceso'] : $_POST['id_proceso'];
        if (empty($this->id_proceso))
            $this->id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
        if (empty($this->id_proceso))
            $this->id_proceso= $_SESSION['id_entity'];

        $this->id_proceso_code= !empty($_POST['proceso_code_'.$this->id_proceso]) ? $_POST['proceso_code_'.$this->id_proceso] : $_POST['id_proceso_code'];
        if (empty($this->id_proceso_code))
            $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);

        $this->init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;
        $this->acc= !is_null($_POST['acc']) ? $_POST['acc'] : $_GET['acc'];
        if (empty($this->acc))
            $this->acc= 0;
        $this->if_jefe= !is_null($_POST['if_jefe']) ? $_POST['if_jefe'] : null;

        $this->sendmail= !is_null($_POST['sendmail']) ? $_POST['sendmail'] : false;

        $this->ifGrupo= !empty($_POST['ifgrupo']) ? true : null;

        $this->obj_tables= new Ttmp_tables_planning($this->clink);
        $this->obj_tables->set_cronos($this->cronos);
        $this->obj_reg= new Tregister_planning($this->clink);
        $this->obj_reg->set_cronos($this->cronos);

        $this->obj_meeting= new Ttipo_reunion($this->clink);
    }

    protected function set_date_time_scheduler($test_changed= true) {
        $this->obj_sch= new Tschedule();

        if (!$test_changed)
            $this->changed= true;

        if (!empty($_POST['fecha_inicio'])) {
            $this->fecha_inicio= $_POST['fecha_inicio'].' '.$_POST['hora_inicio'];
            $this->fecha_inicio= time2odbc($this->fecha_inicio);
            $this->time_inicio= date('H:i:s', strtotime($this->fecha_inicio));

            if ($this->action == 'update')
                $this->changed= $test_changed ? !if_same_date($this->fecha_inicio, $this->obj->GetFechaInicioPlan()) : true;

            $this->obj->SetFechaInicioPlan($this->fecha_inicio);
        }

        if (!empty($_POST['fecha_fin'])) {
            $this->fecha_fin= $_POST['fecha_fin'].' '.$_POST['hora_fin'];
            $this->fecha_fin= time2odbc($this->fecha_fin);
            $this->time_fin= date('H:i:s', strtotime($this->fecha_fin));

            if ($this->action == 'update' && (!$this->changed && $test_changed))
                $this->changed= $test_changed ? !if_same_date($this->fecha_fin, $this->obj->GetFechaFinPlan()) : true;

            $this->obj->SetFechaFinPlan($this->fecha_fin);
        }

        $this->periodicidad=  $_POST['periodicidad'];

        if ($this->action == 'update' && (!$this->changed && $test_changed)) {
            if ($this->periodicidad != $this->obj->GetPeriodicidad())
                $this->changed= true;
        }

        $this->saturday= empty($_POST['saturday']) ? 0 : 1;
        $this->sunday= empty($_POST['sunday']) ? 0 : 1;
        $this->freeday= empty($_POST['freeday']) ? 0 : 1;

        if ($this->periodicidad == 1)
            $this->carga= $_POST['input_carga1'];
        $this->fixed_day= $_POST['fixed_day'];

        if ($this->periodicidad == 3)
            $this->carga= $this->fixed_day ? $_POST['sel_carga'] : $_POST['input_carga4'];

        if ($this->action == 'update' && (!$this->changed && $test_changed)) {
            if ($this->carga != $this->obj->GetCarga())
                $this->changed= true;
        }

        $this->dayweek= "";
        if ($this->periodicidad == 3 && $this->fixed_day == 1)
            $this->dayweek= $_POST['dayweek0'];

        if ($this->periodicidad == 2) {
            for ($i= 1; $i < 8; ++$i) {
                $j= !empty($_POST['dayweek'.$i]) ? $i : 0;
                $this->dayweek.= "-".$j;
            }
        }

        if ($this->action == 'update' && (!$this->changed && $test_changed)) {
            if ($this->dayweek != $this->obj->GetDayWeek())
                $this->changed= true;
        }

        if ($this->periodicidad == 4) {
            $input_array_dates= preg_split("[,]", $_POST['_chain'], -1, PREG_SPLIT_NO_EMPTY);
            $fecha_inicio= strtotime(date('y-m-d', strtotime($this->fecha_inicio)));
            $fecha_fin= strtotime(date('y-m-d', strtotime($this->fecha_fin)));

            foreach ($input_array_dates as $date) {
                list($y, $m, $d)= preg_split("[-]",$date);
                $m= str_pad($m,2,'0',STR_PAD_LEFT);
                $d= str_pad($d,2,'0',STR_PAD_LEFT);
                if (strtotime("{$y}-{$m}-{$d}") < $fecha_inicio || strtotime("{$y}-{$m}-{$d}") > $fecha_fin)
                    continue;

                $this->input_array_dates[]= "{$y}-{$m}-{$d}";
            }
            $this->obj_sch->input_array_dates= $this->input_array_dates;

            if (!$this->changed && $test_changed)
                $this->changed= !empty($_POST['changed_chain']) ? true : false;
        }

        $this->obj->SetPeriodicidad($this->periodicidad);
        $this->obj->SetCarga($this->carga);
        $this->obj->SetDayWeek($this->dayweek);

        $this->sendmail= $_POST['sendmail'];
        $this->obj->SetSendMail($this->sendmail);

        $this->toworkplan= $_POST['toworkplan'];
        $this->obj->SetToworkplan($this->toworkplan);

        $this->obj->saturday= $this->saturday;
        $this->obj->sunday= $this->sunday;
        $this->obj->freeday= $this->freeday;

        $this->set_scheduler();

        if ($this->periodicidad == 0) {
            $this->fecha_inicio= $this->obj_sch->get_work_day($this->fecha_inicio);
            $this->obj->SetFechaInicioPlan($this->fecha_inicio);
            $this->obj_sch->SetFechaInicioPlan($this->fecha_inicio);

            $this->fecha_fin= $this->obj_sch->get_work_day($this->fecha_fin);
            $this->obj->SetFechaFinPlan($this->fecha_fin);
            $this->obj_sch->SetFechaFinPlan($this->fecha_fin);
        }

        $this->periodic= !empty($_POST['_periodic']) ? 1 : 0;
        $this->cant_days= !empty($_POST['cant_days']) ? (int)$_POST['cant_days'] : null;

        if ($test_changed) {
            if ($this->periodic != $this->obj->GetIfPeriodic())
                $this->changed= true;
            if ($this->cant_days != $this->obj->GetCantidad_days())
                $this->changed= true;
        }

        $this->obj_sch->periodic= $this->periodic;
        $this->obj->SetIfPeriodic($this->periodic);

        $this->obj->SetCantidad_days($this->cant_days);
        $this->obj_sch->cant_days= $this->cant_days;
    }

    private function set_scheduler() {
        $this->obj_sch->SetFechaInicioPlan($this->fecha_inicio);
        $this->obj_sch->SetFechaFinPlan($this->fecha_fin);

        $this->periodicidad = !is_null($this->periodicidad) ? (int)$this->periodicidad : null;
        $this->obj_sch->periodicidad = $this->periodicidad;
        $this->obj_sch->sunday = $this->sunday;
        $this->obj_sch->saturday = $this->saturday;
        $this->obj_sch->freeday = $this->freeday;

        $this->obj_sch->SetPeriodicidad($this->periodicidad);
        $this->obj_sch->SetCarga(!is_null($this->carga) ? (int)$this->carga : null);
        $this->obj_sch->SetDayWeek($this->dayweek);
        $this->obj_sch->fixed_day = $this->fixed_day;

        $i = 0;
        foreach ($this->input_array_dates as $date)
            $this->obj_sch->input_array_date[$i++] = $date;
    }

    protected function set_reg_table($table) {
        $this->table = $table;

        switch ($table) {
            case('tusuario_proyectos'):
                $this->field = 'id_proyecto_code';
                break;
            case('tusuario_procesos'):
                $this->field = 'id_proceso_code';
                break;
            case('tusuario_tareas'):
                $this->field = 'id_tarea_code';
                break;
            case('tusuario_tableros'):
                $this->field = 'id_tablero_code';
                break;
            case('tusuario_grupos'):
                $this->field = 'id_grupo';
                break;
        }
    }

    protected function set_peso($id= null, $id_code= null, $found= false) {
        $tobj= new Tinductor($this->clink);
        $result= $tobj->listar();

        $obj_peso= new Tpeso($this->clink);
        $obj_peso->SetIdEvento($id);
        $obj_peso->set_id_evento_code($id_code);
        $obj_peso->SetIdTarea($this->id_tarea);
        $obj_peso->set_id_tarea_code($this->id_tarea_code);
        $obj_peso->set_cronos($this->cronos);

        while ($row= $this->clink->fetch_array($result)) {
            $value= $_POST['select_objt'.$row['_id']];
            $_value= $_POST['init_objt'.$row['_id']];

            if ($value > 0) {
                if (!empty($_value) && $found) {
                    if ($_value != $value)
                        $obj_peso->update_evento_ref_inductor($row['_id'], $row['_id_code'], $value, 'update');
                }
                else
                    $obj_peso->update_evento_ref_inductor($row['_id'], $row['_id_code'], $value, 'insert');
            }
            elseif (!empty($_value))
                $obj_peso->delete_evento_ref_inductor($row['_id']);
        }

        unset($obj_peso);
        unset($tobj);
    }

  // PARA EVENTOS Y TAREA /////////////////////
    private function setMail() {
        $obj_user = new Tusuario($this->clink);
        $this->obj_mail = new Tmail;

        $email= $obj_user->GetEmail($this->id_responsable);
        $nombre= $email['nombre'];
        if (!empty($email['cargo']))
            $nombre.= ", {$email['cargo']}";

        $this->obj_mail->_AddReplayTo($email['email'], utf8_decode($nombre));
        $this->obj_mail->responsable = utf8_decode($email['nombre']);
        $this->obj_mail->cargo = !empty($email['cargo']) ? utf8_decode($email['cargo']) : null;

        $this->obj_mail->fecha_inicio = $this->fecha_inicio;
        $this->obj_mail->fecha_fin = $this->fecha_fin;

        $this->obj_mail->descripcion= utf8_decode($this->obj->GetDescripcion());
        $this->obj_mail->periodicidad = $this->obj->GetPeriodicidad();
        $this->obj_mail->lugar = utf8_decode($this->obj->GetLugar());
        $this->obj_mail->evento = utf8_decode($this->obj->GetNombre());

        unset($obj_user);
    }

    private function toMail($action) {
    	if (is_null($this->obj_mail))
            $this->setMail();

        reset($this->accept_mail_user_list);
        array_multisort($this->accept_mail_user_list, SORT_DESC);

        $prev= null;
        $i= 0;
        $this->obj_mail->className= $this->className;
        $this->obj_mail->Subject= "Actividad o tarea $action";
        $this->obj_mail->body_event($action);

        foreach ($this->accept_mail_user_list as $array) {
            $email = $array['email'];
            if (empty($email))
                continue;
            if ($prev == $email)
                continue;
            else
                $prev= $email;

            ++$i;
            $this->obj_mail->AddAddress($email);
            $this->obj_mail->send();
            $this->obj_mail->clearAddresses();
    	}

        if (is_array($this->denied_mail_user_list)) {
            reset($this->denied_mail_user_list);
            array_multisort($this->denied_mail_user_list, SORT_DESC);
        } else {
            $this->obj_mail->smtpClose();
            return;
        }

        $prev= null;
        $i= 0;
        $this->obj_mail->body_event("CANCELADO");

    	foreach ($this->denied_mail_user_list as $array) {
            $email = $array['email'];
            if (empty($email))
                continue;
            if ($prev == $email)
                continue;
            else
                $prev= $email;

            ++$i;
            $this->obj_mail->AddAddress($email);
            $this->obj_mail->send();
            $this->obj_mail->clearAddresses();
    	}

        $this->obj_mail->smtpClose();
    }

    private function is_accept_mail_user($id_usuario) {
        reset($this->accept_mail_user_list);
        foreach ($this->accept_mail_user_list as $array) {
            if ($array['id'] == $id_usuario)
                return true;
        }
        return false;
    }

    protected function setReg($found= false, $fix_responsable= true, $multi_query= false) {
        $found= !is_null($found) ? $found : false;
        $fix_responsable= !is_null($fix_responsable) ? $fix_responsable : true;
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $user_check= true;
        $sql= null;

        reset($this->accept_mail_user_list);
        reset($this->denied_mail_user_list);
        
        foreach ($this->accept_mail_user_list as $user) {
            if (!is_null($user['eliminado']) && (strtotime($this->_fecha_inicio) > $user['eliminado']))
                continue;
            if ($found && !empty($user['flag']))
                continue;
            
            if (is_array($this->array_reg_usuarios)) 
                $found_user= $found ? array_key_exists($user['id'], $this->array_reg_usuarios) : false;
            else
                $found_user= false;
            
            $this->obj_reg->SetIdUsuario($user['id']);
            if (!$found_user || (!empty($this->_id_responsable)
                                && ($this->_id_responsable != $this->id_responsable && $this->_id_responsable == $user['id']))) {
                $this->obj_reg->SetCumplimiento(_NO_INICIADO);
                $this->obj_reg->set_user_check(false);
                $sql.= $this->obj_reg->update_reg('add', null, _USER_SYSTEM, $multi_query);
            }
    	}

        if ($fix_responsable) {
            $_user_check= null;
            if (is_array($this->array_reg_usuarios)) {
                $found_user= $found && !empty($found[1]) ? array_key_exists($this->id_responsable, $this->array_reg_usuarios) : false;
                $_user_check= $found_user ? boolean($this->array_reg_usuarios[$this->id_responsable]['user_check']) : false;
            } else
                $found_user= false;
            
            $user_check= array_key_exists($this->id_responsable, $this->accept_mail_user_list) ? false : true;
            
            if (($found_user && (bool)$user_check != (bool)$_user_check) || !$found_user) {
                $this->obj_reg->set_user_check($user_check);
                $this->obj_reg->SetCumplimiento(_NO_INICIADO);
                $this->obj_reg->SetIdUsuario($this->id_responsable);

                $sql.= $this->obj_reg->update_reg('add', null, _USER_SYSTEM, $multi_query);
        }   }

        $prev= null;
    	foreach ($this->denied_mail_user_list as $key => $user) {
            if ($this->id_responsable == $user['id'])
                continue;
            if ($this->is_accept_mail_user($user['id']))
                continue;

            $user_delete= true;
            if ($this->id_tipo_reunion) {
                $this->obj_matter->SetIdEvento($this->_id_evento);
                $attached= $this->obj_matter->get_if_attached_usuario($user['id']);
                $user_delete= $user['flag'] ? ($attached ? 0 : 1) : 0;
                $this->denied_mail_user_list[$key]['flag']= $user_delete;
            }

            if ($user_delete) {
                $this->obj_reg->SetIdUsuario($user['id']);
                $sql.= $this->obj_reg->update_reg('delete', null, null, $multi_query);
            }
        }
        
        if ($multi_query && $sql)
            $this->do_multi_sql_show_error('setReg', $sql);
    }

    protected function set_listas($id, $id_code) {
        $obj_lista= new Tlista($this->clink);
        $obj_lista->SetYear($this->year);
        $result= $obj_lista->listar();

        $obj_lista->SetIdAuditoria($id);
        $obj_lista->set_id_auditoria_code($id_code);

        while ($row= $this->clink->fetch_array($result)) {
            $obj_lista->SetIdLista($row['_id']);
            $obj_lista->set_id_lista_code($row['_id_code']);

            $_id= $_POST['chk_list_init_'.$row['_id']];
            $id= $_POST['chk_list_'.$row['_id']];

            if (empty($_id) && !empty($id))
                $obj_lista->setAuditoria('add');

            if (!empty($_id) && empty($id))
                $obj_lista->setAuditoria('delete');
        }
    }

    protected function set_asistencia($id, $id_code, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        $this->obj_assist->SetIdEvento($id);
        $this->obj_assist->set_id_evento_code($id_code);

        reset($this->accept_mail_user_list);
        foreach ($this->accept_mail_user_list as $user) {
            $this->obj_assist->SetIdUsuario($user['id']);
            $sql.= $this->obj_assist->add($multi_query);
        }

        if (!array_key_exists($this->id_responsable, $this->accept_mail_user_list)) {
            $this->obj_assist->SetIdUsuario($this->id_responsable);
            $sql.= $this->obj_assist->add($multi_query);
        }
        if ($this->id_secretary != $this->id_responsable && !array_key_exists($this->id_secretary, $this->accept_mail_user_list)) {
            $this->obj_assist->SetIdUsuario($this->id_secretary);
            $sql.= $this->obj_assist->add($multi_query);
        }

        reset($this->denied_mail_user_list);
        foreach ($this->denied_mail_user_list as $user) {
            $this->obj_assist->SetIdUsuario($user['id']);
            $sql.= $this->obj_assist->eliminar($multi_query);
        }

        $this->obj_assist->SetIdUsuario(null);

        reset($this->array_asistencias);
        foreach ($this->array_asistencias as $user) {
            if (!empty($user['id_usuario']))
                continue;
            $this->obj_assist->SetNombre($user['nombre']);
            $this->obj_assist->SetCargo($user['cargo']);
            $this->obj_assist->SetEntidad($user['entidad']);
            $this->obj_assist->SetIfInvitado($user['invitado']);
            $this->obj_assist->SetIfAusente($user['ausente']);

            $sql.= $this->obj_assist->add($multi_query);
        }

        if ($multi_query && $sql)
            $this->do_multi_sql_show_error('set_asistencia', $sql);
    }

    protected function setting($action, $found= false, $hit= false) {
        global $config;

        $hit= !is_null($hit) ? $hit : false;
        $this->year= date('Y', strtotime($this->obj->GetFechaInicioPlan()));
        $this->obj->SetYear($this->year);

        if ($this->control_list == 0) {
            if ($this->id_tipo_reunion)
                $this->obj_matter= new Ttematica($this->clink);

            $this->set_usuarios_array($action);
            if (is_null($this->if_jefe) || (!is_null($this->if_jefe) && $this->if_jefe))
                $this->set_grupos_array($action);

            $this->setProcesos();
            if ($this->className != 'Ttarea' && $config->use_anual_plan_organismo /*&& $this->toshow == _EVENTO_ANUAL*/)
                $this->setOrganismos();
            if ($this->className == 'Ttarea' && (!empty($this->id_riesgo) || !empty($this->id_nota)))
                $this->set_proceso_from_usuario_array();
            if ($this->className == 'Tauditoria' && $this->toworkplan)
                $this->set_array_usuarios_from_procesos();

            if (!empty($this->sendmail))
                $this->toMail($action);

        } else {
            $this->obj->SetIdAuditoria($this->_id_auditoria);
            $this->obj->set_id_auditoria_code($this->_id_auditoria_code);
            $this->obj->SetIdEvento($this->_id_evento);
            $this->obj->set_id_evento_code($this->_id_evento_code);
        }

        $this->set_usuarios_from_array($action, $found, $this->multi_query);
        if (is_null($this->if_jefe) || (!is_null($this->if_jefe) && $this->if_jefe)) {
            $this->set_grupos_from_array($action, $found, $this->multi_query);
        }
        $this->_setReg($found);
        $this->set_proceso_from_array($found, $this->multi_query);

        if ($this->className != 'Ttarea' && $config->use_anual_plan_organismo /*&& $this->toshow == _EVENTO_ANUAL*/)
            $this->set_organizations_from_array($found, $this->multi_query);

        if (!$hit && (empty($this->id_tematica) || (!empty($this->id_tematica) && $this->ifaccords))) {
            $this->setReg($found, null, $this->multi_query);
        }
        if ($this->className != 'Tauditoria' && $this->toshow) {
            if ($this->control_list == 0 && $this->className == 'Tevento') {
                $this->set_peso($this->id_evento, $this->id_evento_code, $found);
            } else {
                $this->set_peso($this->_id_evento, $this->_id_evento_code, $found);
            }
        }

        if ($this->className == 'Tauditoria' && $hit) {
            $id= $this->control_list ? $this->_id_auditoria : $this->id_auditoria;
            $id_code= $this->control_list ? $this->_id_auditoria_code : $this->id_auditoria_code;
            $this->set_listas($id, $id_code);
        }

        if ($this->id_tipo_reunion && $this->className == 'Tevento') {
            $id= $this->control_list ? $this->_id_evento : $this->id_evento;
            $id_code= $this->control_list ? $this->_id_evento_code : $this->id_evento_code;
            $this->set_asistencia($id, $id_code);
        }

        $this->control_list= 1;
    }

    protected function if_child_event($fecha= null, $id= null) {
        $i= 0;
        $this->_id= null;
        $this->_id_code= null;

        reset($this->obj->array_eventos);
        foreach ($this->obj->array_eventos as $key => $evento) {
            if (!is_null($fecha) && is_null($id)) {
                if (strtotime(date('Y-m-d', strtotime($evento['fecha_inicio_plan']))) == strtotime(date('Y-m-d', strtotime($fecha)))) {
                    $this->obj->array_eventos[$key]['flag']= 1;
                    $this->_id= $this->obj->array_eventos[$key]['id'];
                    $this->_id_code= $this->obj->array_eventos[$key]['id_code'];

                    return array($this->_id, $this->obj->array_eventos[$key]['toshow']);
            }   }

            if (is_null($fecha) && !is_null($id)) {
                if ($evento['id'] == $id) {
                    $this->obj->array_eventos[$key]['flag']= 1;
                }
                $this->_id= $this->obj->array_eventos[$key]['id'];
                $this->_id_code= $this->obj->array_eventos[$key]['id_code'];

                return array($this->_id, $this->obj->array_eventos[$key]['toshow']);
            }

            ++$i;
        }

       return false;
    }

    protected function if_in_date_intervals($fecha) {
        $fecha= strtotime(date('Y-m-d', strtotime($fecha)));
        if ($fecha < strtotime(date('Y-m-d', strtotime($this->fecha_inicio))) || $fecha > strtotime(date('Y-m-d',strtotime($this->fecha_fin))))
            return false;
        return true;
    }

    // marca los eventos que NO estan en el intervalo
    protected function if_in_date_intervals_all() {
        $i= 0;
        reset($this->obj->array_eventos);
        foreach ($this->obj->array_eventos as $evento) {
            $fecha= !empty($evento['fecha_inicio_plan']) ? $evento['fecha_inicio_plan'] : $evento['fecha_plan'];
            $fecha= date('Y-m-d', strtotime($fecha));

            if (strtotime($fecha) < strtotime(date('Y-m-d', strtotime($this->fecha_inicio))) || strtotime($fecha) > strtotime(date('Y-m-d',strtotime($this->fecha_fin))))
                $this->obj->array_eventos[$i]['flag']= 0;
            ++$i;
        }
    }

    protected function if_child_auditoria($fecha= null, $id= null) {
        $i= 0;
        $this->_id= null;
        $this->_id_code= null;

        reset($this->obj->array_auditorias);
        foreach ($this->obj->array_auditorias as $evento) {
            if (!is_null($fecha) && is_null($id)) {
                $fecha_inicio= !empty($evento['fecha_inicio_plan']) ? $evento['fecha_inicio_plan'] : $evento['fecha_inicio'];
                $fecha_inicio= date('Y-m-d', strtotime($fecha_inicio));
                
                if (strtotime($fecha_inicio) == strtotime(date('Y-m-d', strtotime($fecha)))) {
                    $this->obj->array_auditorias[$i]['flag']= 1;
                    $this->_id= $this->obj->array_auditorias[$i]['id'];
                    $this->_id_code= $this->obj->array_auditorias[$i]['id_code'];

                    return array($this->_id, $this->obj->array_auditorias[$i]['toshow']);
            }   }

            if (is_null($fecha) && !is_null($id)) {
                if ($evento['id'] == $id)
                    $this->obj->array_auditorias[$i]['flag']= 1;
            }

            ++$i;
        }

        return false;
    }

    protected function move_tematicas($id, $id_code, $fecha) {
        $i= 0;
        $cant= count($this->array_tematicas);
        if (!empty($cant))
            return true;

        $date= date("Y-m-d", strtotime($fecha));
        $time= null;

        reset($this->array_tematicas);
        foreach ($this->array_tematicas as $key => $array) {
            if (!empty($array['cant_debates']) || !empty($array['id_accords']))
                continue;

            $time= date('H:i:s', strtotime($array['fecha_inicio_plan']));
            $fecha= $date.' '.$time;

            $this->obj->SetIdTematica($array['id']);
            $this->obj->move_tematica($id, $fecha, $id_code);
            ++$i;
        }

        if ($i < $cant) 
            return false;
    }

    protected function copy_documentos() {
        if (!empty($this->_id_evento)) {
            $this->obj_doc->SetIdEvento($this->_id_evento);
            $this->obj_doc->set_id_evento_code($this->_id_evento_code);
        }
        if (!empty($this->_id_auditoria)) {
            $this->obj_doc->SetIdAuditoria($this->_id_auditoria);
            $this->obj_doc->set_id_auditoria_code($this->_id_auditoria_code);
        }

        $this->obj_doc->copy_documentos($this->array_documentos);
        reset($this->array_documentos);
    }

    private function _setting_freq_update($hit) {
        $found= null;
        
        if ($this->className == 'Tauditoria' && $hit) {
            $found= $this->if_child_auditoria($this->_fecha_inicio);

            $this->_id_auditoria= $found ? $this->_id : null;
            $this->_id_auditoria_code= $found ? $this->_id_code : null;
            $this->_id= $this->_id_auditoria;
            $this->_id_code= $this->_id_auditoria_code;

            $this->_id_evento= null;
            $this->_id_evento_code= null;
        }

        if ($this->className != 'Tauditoria' || ($this->className == 'Tauditoria' && !$hit)) {
            $found= $this->if_child_event($this->_fecha_inicio);

            $this->_id_evento= $found ? $this->_id : null;
            $this->_id_evento_code= $found ? $this->_id_code : null;
        }

        if ($found) {
            if ($this->className == 'Tauditoria' && !$hit) {
                $this->obj->SetIdAuditoria($this->_id_auditoria);
                $this->obj->set_id_auditoria_code($this->_id_auditoria_code);
                $this->obj->update_event($this->_id_evento);
            } else
                $this->obj->update($this->_id);

            if (!is_null($this->error))
                return $this->error;

            if (!$hit)
                $this->date_list[]= $this->_fecha_inicio;
            else
                $this->date_list_hit[]= $this->_fecha_inicio;
        }
        
        return $found;
    }

    private function _setting_freq_add($hit) {
        if ($this->action == 'update' && !is_null($this->obj_doc)) {
            $this->copy_documentos();
        }

        if ($this->className == 'Tevento') {
            $this->obj->SetIdTematica(null);
            $this->obj->set_id_tematica_code(null);

            $this->obj->add($this->id_evento, $this->id_evento_code);

            $this->_id_evento= $this->obj->GetIdEvento();
            $this->_id_evento_code= $this->obj->get_id_evento_code();
            $this->_id= $this->_id_evento;
            $this->_id_code= $this->_id_code;

            if ($this->action == 'update' && $this->id_tipo_reunion) {
                $this->move_tematicas($this->_id_evento, $this->_id_evento_code, $this->_fecha_inicio);
            }
        }

        if ($this->className == 'Ttarea') {
            $this->obj->add();

            $this->_id_evento= $this->obj->GetIdEvento();
            $this->_id_evento_code= $this->obj->get_id_evento_code();
            $this->_id= $this->_id_evento;
            $this->_id_code= $this->_id_code;
        }

        if ($this->className == 'Tauditoria') {
            if ($hit) {
                $this->obj->add($this->id_auditoria, $this->id_auditoria_code);

                $this->_id_auditoria= $this->obj->GetIdAuditoria();
                $this->_id_auditoria_code= $this->obj->get_id_auditoria_code();
                $this->_id= $this->_id_auditoria;
                $this->_id_code= $this->_id_auditoria_code;

                $this->_id_evento= null;
                $this->_id_evento_code= null;

            } else {
                $this->obj->SetIdEvento(null);
                $this->obj->set_id_evento_code(null);
                $this->obj->SetIdAuditoria($this->_id_auditoria);
                $this->obj->set_id_auditoria_code($this->_id_auditoria_code);

                $this->obj->add_evento();
                $this->error= $this->obj->error;
                $this->_id_evento= $this->obj->GetIdEvento();
                $this->_id_evento_code= $this->obj->get_id_evento_code();
                $this->_id= $this->_id_evento;
                $this->_id_code= $this->_id_code;
            }
        }

        if (!is_null($this->error))
            return false;

        if (!$hit)
            $this->date_list[]= $this->_fecha_inicio;
        else
            $this->date_list_hit[]= $this->_fecha_inicio;
        
        return null;
    }

    protected function setting_freq($fecha, $fecha_tmp, $hit= false, $exec_setting= true) {
        $hit= !is_null($hit) ? $hit : false;
        $exec_setting= !is_null($exec_setting) ? $exec_setting : true;
        
        $found= false;
        $className= $this->obj->className;
        $this->_fecha_inicio= null;
        $this->_fecha_fin= null;
        $this->_id= $this->obj->GetId();
        $this->_id_code= $this->obj->get_id_code();

        $this->obj->periodicidad= null;
        $this->obj->carga= null;
        $this->obj->periodic= null;
        $this->obj->dayweek= null;
        $this->obj->SetFechaInicioPlan($fecha);
        $this->obj->SetFechaFinPlan($fecha_tmp);
        $this->obj->SetYear(date('Y', strtotime($fecha)));

        if (!$hit && array_search($fecha, (array)$this->date_list) !== false)
            return null;
        if ($hit && array_search($fecha, (array)$this->date_list_hit) !== false)
            return null;
        $this->_fecha_inicio= $fecha;
        $this->_fecha_fin= $fecha_tmp;

        if ($this->action == 'update') {
            $found= $this->_setting_freq_update($hit);
        }
        if ($this->action == 'add' || ($this->action == 'update' && (!$found && $this->if_in_date_intervals($fecha)))) {
            $found= $this->_setting_freq_add($hit);
            if (!is_null($found) && !$found)
                return;
        }

        $this->obj->className= $className;
        $action= ($this->action == 'add' /*|| ($this->action == 'update' && !$found)*/) ? 'NUEVO' : 'MODIFICADO';

        if ($exec_setting)
            $this->setting($action, $found, $hit);
        return null;
    }

    protected function fix_periodic_events() {
        $this->obj_sch->set_dates();
        $this->obj_sch->create_array_dates();

        if (($this->cant_days && $this->periodic) && $this->className == 'Tauditoria')
            $this->obj_sch->add_days_to_schudele();
        $cant= $this->obj_sch->GetCantidad();

        $this->obj->set_null_periodicity();
        $this->obj->toshow= $this->toshow;

        if ($this->periodicidad == 0 && $this->className == 'Ttarea')
            $this->setting_freq($this->obj_sch->array_dates[0]['inicio'], $this->obj_sch->array_dates[0]['fin']);

        if ($this->periodicidad > 0 || ($this->periodicidad == 0 && $this->className == 'Tauditoria')) {
            $this->obj->set_id_evento_ref($this->id_evento_ref);
            $this->obj->set_id_auditoria_ref($this->id_auditoria_ref);

            reset($this->obj_sch->array_dates);
            for ($i= 0; $i < $cant; $i++) {
                if ($this->obj_sch->array_dates[$i]['hit'])
                    $this->obj->SetCantidad_days($this->cant_days);
                else
                    $this->obj->SetCantidad_days(null);

                $this->setting_freq($this->obj_sch->array_dates[$i]['inicio'], $this->obj_sch->array_dates[$i]['fin'], $this->obj_sch->array_dates[$i]['hit']);
            }
        }

        if (is_null($this->error) && ($this->action == 'update' && ($this->changed || $this->periodicidad == 4))) {
            $this->if_in_date_intervals_all();
            $this->delete_periodic();
        }
    }

    // borra los que estan marcados para borrarce
    protected function delete_periodic() {
        global $meeting_array;

        $i= 0;
        reset($this->obj->array_eventos);
        foreach ($this->obj->array_eventos as $key => $array)  {
            $fecha= date('Y-m-d', strtotime($array['fecha_inicio_plan']));
            if ($this->periodicidad == 4) {
                $this->obj->array_eventos[$key]['go_delete']= !empty($_POST["{$fecha}-go_delete"]) ? $_POST["{$fecha}-go_delete"] : null;
            }    
            $this->obj->array_eventos[$key]['go_delete']= !$this->obj->array_eventos[$key]['flag'] ? _DELETE_PHY : _DELETE_NO;
        }
        
        $this->obj->SetIdAuditoria(null);
        $this->obj->delete_periodic();

        if ($this->className == 'Tauditoria')
            $this->obj->delete_periodic_audit();

        if ($this->updated) {
            $this->obj->SetIdEvento($this->id);

            $this->obj_meeting->SetIdTipo_reunion($this->id_tipo_reunion);
            $this->obj_meeting->Set();
            $meeting= $this->obj_meeting->GetNombre();

            $observacion= !empty($this->id_tipo_reunion) ? $meeting : " ";
            $observacion.= $this->obj->GetNombre();
            $observacion.= "<br />Inicio: ". $this->obj->GetFechaInicioPlan(). " Fin:". $this->obj->GetFechaFinPlan();

            $result= $this->obj->eliminar();
            if ($result) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('teventos', 'id_code', $this->id_code);
            }
        }
    }

    protected function if_synchronize($id_proceso) {
        if (!is_array($this->array_proceso_no_local)) {
            $obj_prs= new Tproceso($this->clink);
            $obj_prs->Set($this->id_proceso);

            $obj_prs->SetIdUsuario(null);
            $obj_prs->SetIdResponsable(null);
            $obj_prs->SetConectado(_NO_LOCAL);
            $obj_prs->SetYear($this->year);

            $obj_prs->listar_in_order('eq_asc', false);
            $this->array_proceso_no_local= $obj_prs->array_procesos;
        }

        $if_synchronize= false;

        if (!empty($id_proceso)
                && (array_key_exists($id_proceso, $this->array_proceso_no_local) 
                    && ($id_proceso != $_SESSION['local_proceso_id'] && $this->array_proceso_no_local[$id_proceso]['conectado'] != _LAN))) {
            $if_synchronize= true;
        }

        return $if_synchronize;
    }
}

function if_same_date($fecha, $fecha_odbc) {
    return (strtotime($fecha) != strtotime($fecha_odbc)) ? false : true;
}

?>