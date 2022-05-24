<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

set_time_limit(9999999999999999999999999999999);
session_cache_expire(999999999999999999999999);

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "register.interface.php";

require_once "class/connect.class.php";
require_once "class/proceso.class.php";
require_once "class/proceso_item.class.php";
require_once "class/document.class.php";

require_once "class/mail.class.php";
require_once "class/code.class.php";
require_once "class/badger.class.php";

?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
$ajax_win= true;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class TEventoRegisterInterface extends TRegister {

    public function __construct($clink= null) {
        $this->clink= $clink;
        TRegister::__construct($clink);

        $this->id_usuario= $this->id_calendar;
        $this->id_responsable= $_SESSION['id_usuario'];
        $this->id_proceso= !empty($_POST['id_proceso']) ? (int)$_POST['id_proceso'] : null;
        $this->id_proceso_code= !empty($_POST['id_proceso_code']) ? $_POST['id_proceso_code'] : !empty($this->id_proceso) ? get_code_from_table("tprocesos", $this->id_proceso) : null;
        $this->tipo= !empty($_POST['tipo']) ? $_POST['tipo'] : null;

        $this->sendmail= !is_null($_POST['sendmail']) ? $_POST['sendmail'] : false;

        $this->observacion= !empty($_POST['observacion']) ? trim($_POST['observacion']) : null;
        $this->descripcion= !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
        $this->_ifmeeting= !empty($_POST['ifmeeting']) ? $_POST['ifmeeting'] : null;

        $this->cumplimiento= !empty($_POST['cumplimiento']) ? $_POST['cumplimiento'] : $_GET['cumplimiento'];
        if(empty($this->cumplimiento))
            $this->cumplimiento= null;

        $this->className= "Tevento";
    }

    /* borrado del evento originar !! si procesde? */
    protected function delete_usuarios() {
        $sql= null;

        $this->obj_reg->SetIdEvento($this->id_evento);
        $this->obj_reg->set_id_evento_code($this->id_evento_code);
        $this->obj_reg->SetIdAuditoria($this->id_auditoria);
        $this->obj_reg->set_id_auditoria_code($this->id_auditoria_code);

        $this->obj_reg->set_go_delete(null);
        $this->obj_reg->SetIdResponsable($this->id_responsable);

        $this->obj_reg->SetObservacion($this->observacion);
        $this->obj_reg->_observacion= $this->observacion;

        $this->obj_reg->set_toshow_plan($this->toshow_plan);

        if (!empty($this->id_calendar) || $this->signal == 'calendar') {
            $sql.= $this->_delete_user($this->id_calendar);
        }

        if ($this->radio_user || $this->radio_prs) {
            reset($this->array_reg_usuarios);
            foreach ($this->array_reg_usuarios as $user) {
                // no borrar a los responsables de los procesos que no seran eliminados
                if ($this->radio_prs == 1) {
                    $id_proceso= null;
                    $if_responsable= $this->test_if_responsable_prs($user['id'], $id_proceso);

                    if ($if_responsable && $id_proceso != $this->id_proceso)
                        continue;
                }
                $sql.= $this->_delete_user($user['id']);
            }
        }

        if ($this->multi_query && $sql)
            $this->do_multi_sql_show_error('delete_usuarios', $sql);
    }

    protected function _delete_user($id_usuario) {
        $sql= null;

        /* marcar la actividad como reprograma */
        $this->obj_reg->SetIdEvento($this->id_evento);
        $this->obj_reg->set_id_evento_code($this->id_evento_code);
        $this->obj_reg->SetIdAuditoria($this->id_auditoria);
        $this->obj_reg->set_id_auditoria_code($this->id_auditoria_code);

        $this->obj_reg->SetIdResponsable($_SESSION['id_usuario']);
        $this->obj_reg->SetObservacion($this->observacion);
        $this->obj_reg->SetCumplimiento(_REPROGRAMADO);
        $this->obj_reg->SetAprobado($this->aprobado);
        $this->obj_reg->SetRechazado($this->cronos);

        $delete= false;
        if (!array_key_exists($id_usuario, $this->array_usuarios_down))
            return null;

        if (!$this->test_if_responsable_prs($id_usuario))
            $delete= true;
        else {
            $id_proceso= $this->array_usuarios_down[$id_usuario]['id_proceso'];
            if ($this->test_if_delete_proceso($id_proceso, $this->array_reg_procesos[$id_proceso]['toshow']))
                $delete= true;
        }
        if ($this->array_reg_usuarios[$id_usuario]['cumplimiento'] == _CUMPLIDA)
            $delete= false;

        if ($delete || $this->go_delete == _DELETE_PHY) {
            $go_delete= $delete ? _DELETE_PHY : $this->go_delete;
            $sql.= $this->obj_reg->_delete_reg($this->id_evento, $this->id_evento_code, $id_usuario, $go_delete, 
                                                                                null, $this->multi_query);
        } else {
            $this->obj_reg->SetIdUsuario($id_usuario);
            $sql.= $this->obj_reg->add_cump(null, $this->multi_query);
        }

        return $this->multi_query ? $sql : null;
    }

    protected function delete_procesos() {
        $sql= null;

        $this->obj_reg->SetIdEvento($this->id);
        $this->obj_reg->set_id_evento_code($this->id_code);

        $this->obj_reg->SetIdResponsable($_SESSION['id_usuario']);
        $this->obj_reg->SetObservacion($this->observacion);
        $this->obj_reg->SetCumplimiento(_REPROGRAMADO);
        $this->obj_reg->SetAprobado($this->aprobado);
        $this->obj_reg->SetRechazado($this->cronos);

        reset($this->array_reg_procesos);
        foreach ($this->array_reg_procesos as $prs) {
            if (!array_key_exists($prs['id'], $this->array_procesos_down))
                continue;
            if ($this->radio_prs == 1 && $prs['id'] != $this->id_proceso)
                continue;
            $sql.= $this->_delete_process($prs);
        }

        if ($this->multi_query && $sql)
            $this->do_multi_sql_show_error('delete_procesos', $sql);        
    }

    protected function _delete_process($prs) {
        $sql= null;

        $this->obj_reg->SetIdResponsable($prs['id_responsable']);
        $this->obj_reg->SetIdProceso($prs['id']);
        $this->obj_reg->set_id_proceso_code($prs['id_code']);

        $this->obj_reg->SetIfEmpresarial($prs['empresarial']);
        $this->obj_reg->SetIdTipo_evento($prs['id_tipo_evento']);
        $this->obj_reg->set_id_tipo_evento_code($prs['id_tipo_evento_code']);
        $this->obj_reg->indice= $prs['indice'];
        $this->obj_reg->indice_plus= $prs['indice_plus'];

        $toshow= $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL ? _EVENTO_MENSUAL : _EVENTO_INDIVIDUAL;
        $this->obj_reg->toshow= $this->radio_toshow ? $toshow : $prs['toshow'];

        $this->obj_reg->SetValue(_REPROGRAMADO);
        $this->obj_reg->SetRechazado($this->cronos);
        $this->obj_reg->SetObservacion($this->observacion);

        $rowcmp= array();
        $this->obj_reg->setEvento_reg_proceso($rowcmp);

        $deleted= $this->test_if_delete_proceso($prs['id'], $prs['toshow']) ? true : false;

        if ($this->go_delete == _DELETE_PHY && $deleted) {
            $obj_prs= new Tproceso_item($this->clink);
            $obj_prs->SetYear($this->year);
            $obj_prs->SetIdProceso($prs['id']);
            $obj_prs->set_id_proceso_code($prs['id_code']);

            $obj_prs->SetIdEvento($this->id_evento);
            $obj_prs->set_id_evento_code($this->id_evento_code);
            $obj_prs->SetIdAuditoria($this->id_auditoria);
            $obj_prs->set_id_auditoria_code($this->id_auditoria_code);

            $sql.= $obj_prs->setEvento('delete', null, null, null, $this->multi_query);

        } else {
            $sql.= $this->obj_reg->add_cump_proceso(null, null, $rowcmp, $this->multi_query);
        }

        return $this->multi_query ? $sql : null;
    }

    protected function set_go_delete() {
        $this->aprobado= null;
        $this->go_delete= _DELETE_YES;

        if ($this->ifmeeting) {
            $this->radio_user= 1;
            $this->radio_prs= 2;
        }
        if ($this->radio_user == 1) {
            $this->radio_prs= 2;
        }

        $this->obj_event->SetYear($this->year);
        $this->obj_event->Set();

        if ($this->signal == 'calendar' && ($this->toshow_plan == _EVENTO_INDIVIDUAL && (!$this->radio_prs && !$this->radio_user)))
            $this->obj_event->SetIdUsuario($this->id_calendar);

        $this->toshow_plan= $this->obj_event->get_toshow_plan();
        $this->toshow= $this->obj_event->toshow;
        $this->aprobado= $this->obj_event->GetAprobado();
        $cumplimiento= $this->obj_event->GetCumplimiento();

        if ($this->signal == 'calendar') {
            if ($this->toshow_plan > _EVENTO_INDIVIDUAL)
                $this->go_delete= _DELETE_NO;
            if ($cumplimiento == _CUMPLIDA)
                $this->go_delete= _DELETE_NO;
            if (!empty($this->aprobado))
                $this->go_delete= _DELETE_NO;
        }

        if ($this->signal == 'anual_plan_meeting' && !$this->radio_toshow) {
            if ($this->toshow_plan > _EVENTO_INDIVIDUAL)
                $this->go_delete= _DELETE_NO;
        }

        if ($this->signal == 'mensual_plan' && !$this->radio_toshow) {
            /*
            if ($this->toshow_plan == _EVENTO_ANUAL)
                $this->go_delete= _DELETE_NO;
            if ($cumplimiento == _CUMPLIDA)
                $this->go_delete= _DELETE_NO;
            if (!empty($this->aprobado))
                $this->go_delete= _DELETE_NO;
            */
            $this->go_delete= _DELETE_NO;
        }
        if ($this->signal == 'anual_plan' || ($this->signal == 'anual_plan_meeting' && !$this->radio_toshow)) {
            if (!$this->radio_prs || !$this->radio_user)
                $this->go_delete= _DELETE_NO;
        }
    }


    private function _reject() {
        $this->init_mail();
        
        if (empty($this->radio_user) && (!empty($this->id_calendar) && $this->id_calendar == $this->id_responsable_ref))
            return;

        foreach ($this->array_eventos as $array) {
            $this->obj_reg->SetIdEvento($array['id']);
            $this->obj_reg->SetIdTarea($array['id_tarea']);
            $this->obj_reg->SetIdArchivo($array['id_archivo']);

            $id_usuario= $this->signal == 'calendar' ? $array['id_usuario'] : $array['id_responsable'];

            $this->obj_reg->SetIdUsuario($id_usuario);
            $row= $this->obj_reg->getEvento_reg();
            $this->obj_reg->setEvento_reg($row);
            $this->obj_reg->set_cronos($this->cronos);

            $id_responsable= $row['id_responsable'];
            $empresarial= $row['empresarial'];
            //if (!empty($empresarial)) continue;

            $this->obj_reg->SetCumplimiento(_CANCELADO);
            $this->obj_reg->SetRechazado($this->cronos);

            if ($this->_test_usuario_entity($id_usuario)) {
                $this->error= $this->obj_reg->add_cump();

                if (is_null($this->error) && ($id_responsable != $this->id_calendar))
                    $this->to_mail($id_responsable);
            }

            $array_usuarios= null;
            if (!empty($this->radio_user)) {
                if ($this->signal != "calendar" && !empty($this->id_proceso)) {
                    $obj_tables= new Ttmp_tables_planning($this->clink);
                    $obj_tables->SetYear($this->year);
                    $obj_tables->set_use_copy_tusuarios(false);
                    $obj_tables->add_to_tmp_tusuarios($this->id_proceso);
                    $this->obj_reg->if_tusuarios= $obj_tables->if_tusuarios;
                }

                $this->obj_reg->SetIdResponsable_ref($this->id_responsable_ref);
                $this->obj_reg->add_cump_users(_CANCELADO, $array_usuarios, $this->array_usuarios_entity);
        }   }

        if (is_null($this->error) && $this->id_responsable != $this->id_calendar)
            $this->to_mail($this->id_calendar);

        if (is_null($this->error)) {
            $this->init_mail($_SESSION['id_usuario']);
            $this->send_mails_by_users("RECHAZADA O ELIMINADA", $array_usuarios, $array);
            $this->obj_mail->smtpClose();
        }
    }

    private function _delete() {
        $obj= new Tevento($this->clink);
        $obj->SetYear($this->year);
        $obj->SetIdTarea($this->id_tarea);
        $obj->set_id_tarea_code($this->id_tarea_code);

        $obj_matter= new Ttematica($this->clink);
        $obj_matter->SetYear($this->year);

        $obj_assist= new Tasistencia($this->clink);
        $obj_assist->SetYear($this->year);

        $array= $this->set_delete_date();
        $id_evento= !empty($this->id_tarea) ? null : $array['id'];

        $all_month= $this->radio_date == 1 ? false : true;
        $this->array_eventos= $this->obj->get_eventos_by_period($array['date'], $array['extend'], $id_evento, true, null, $all_month);

        if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL || count($this->array_eventos) > 90)
            $this->multi_query= true;

        foreach ($this->array_eventos as $event) {
            $this->id_evento= $event['id'];
            $this->id_evento_code= $event['id_code'];
            $this->id= $this->id_evento;
            $this->id_code= $this->id_evento_code;
            $this->id_auditoria= $event['id_auditoria'];
            $this->id_auditoria_code= $event['id_auditoria_code'];

            $this->init_register_usuarios();
            $this->init_register_procesos();

            $this->delete_usuarios();
            $this->delete_procesos();

            $if_attached= false;
            if ($event['ifmeeting'] && $this->go_delete == _DELETE_PHY) {
                $obj_matter->SetIdEvento($event['id']);
                $if_attached= $obj_matter->get_if_attached_evento();

                if (!$if_attached) {
                    $obj_assist->SetIdUsuario(null);
                    $obj_assist->SetIdTematica(null);
                    $obj_assist->SetIdEvento($event['id']);
                    $obj_assist->set_id_evento_code($event['id_code']);
                    $obj_assist->eliminar(null, false);

                    $obj_matter->SetIdTematica(null);
                    $obj_matter->SetIdEvento($event['id']);
                    $obj_matter->set_id_evento_code($event['id_code']);
                    $obj_matter->eliminar();

                } else {
                    $this->error= "Esta reunión tiene registrado debates y acuerdos. No pudo ser eliminada. ";
                    $this->error.= "Debe eliminarle los acuerdos y los debates.";
                }
            }

            if (!$if_attached && $this->go_delete == _DELETE_PHY) {
                $obj->SetIdEvento($event['id']);
                $obj->set_id_evento_code($event['id_code']);
                $obj->SetIdTarea($this->id_tarea);
                $obj->set_id_tarea_code($this->id_tarea_code);
                $obj->eliminar_if_empty();
            }
        }

        if (($this->radio_date && $this->radio_user && (!empty($this->id_evento_ref) || !empty($this->id_tarea)))
                && $this->go_delete == _DELETE_PHY) {
            $obj->SetIdTarea($this->id_tarea);
            $obj->SetIdEvento($this->id_evento_ref);
            $obj->set_id_evento_ref_code($this->id_evento_ref_code);
            $observacion= null;
            $obj->eliminar_if_empty(null, false, $this->id_evento_ref, $observacion);
        }

        if ($this->radio_date && $this->radio_user && !empty($this->id_tarea)) {
            $obj= new Ttarea($this->clink);
            $obj->SetYear($this->year);
            $obj->SetIdTarea($this->id_tarea);
            $obj->set_id_tarea_code($this->id_tarea_code);
            $obj->eliminar_if_empty();
        }
    }

    private function _init_reprograming() {
        global $day_feriados;

        $this->obj_event= new Tevento($this->clink);
        $this->obj_event->SetIdEvento($this->id_evento);
        $this->obj_event->SetIdAuditoria($this->id_auditoria);

        $this->set_go_delete();

        $lugar= !empty($_POST['lugar']) ? $_POST['lugar'] : null;
        $fecha= date2odbc($_POST['fecha']);
        $fecha_inicio= $fecha.' '.ampm2odbc($_POST['hora_inicio']);
        $fecha_fin= $fecha.' '.ampm2odbc($_POST['hora_fin']);

        $this->lugar= $this->obj_event->GetLugar();
        $this->fecha_inicio_plan= $this->obj_event->GetFechaInicioPlan();
        $this->fecha_fin_plan= $this->obj_event->GetFechaFinPlan();

        $this->observacion= "TAREA REPROGRAMADA (".odbc2time_ampm($this->cronos)."): ".$this->observacion;
        $this->obj_event->SetFechaInicioPlan($fecha_inicio);
        $this->obj_event->SetFechaFinPlan($fecha_fin);
        $this->obj_event->SetIdUsuario($_SESSION['id_usuario']);

        $time = strtotime($fecha_inicio);
        $m_d = date('j/m', $time);
        if (date('N', $time) == 6)
            $this->obj_event->saturday= true;
        if (date('N', $time) == 7)
            $this->obj_event->sunday= true;
        if (array_search($m_d, $day_feriados) !== false)
            $this->obj_event->freeday= true;

        if ($this->go_delete == _DELETE_NO) {
            if ($this->toshow_plan == _EVENTO_ANUAL)
                $this->obj_event->set_toshow_plan(_EVENTO_MENSUAL);
            else
                $this->obj_event->set_toshow_plan($this->toshow_plan);
        }

        if ($lugar != $this->lugar)
            $this->obj_event->SetLugar($lugar);

        $this->_id= $this->id;
        $this->_id_code= $this->id_code;
    }

    private function _reprograming() {
        $this->_init_reprograming();

        $id_evento= $this->obj_event->get_id_evento_ref();
        $id_evento_code= $this->obj_event->get_id_evento_ref_code();
        $_id_evento= $id_evento;
        $fecha_inicio= $this->obj_event->GetFechaInicioPlan();
        $fecha_fin= $this->obj_event->GetFechaFinPlan();
        $duplicate= false;

        if (strtotime($fecha_inicio) != strtotime($this->fecha_inicio_plan) || strtotime($fecha_fin) != strtotime($this->fecha_fin_plan)) {
            $this->obj_event->set_id_copyfrom($this->id_evento);
            $this->obj_event->set_id_copyfrom_code($this->id_evento_code);

            if (empty($id_evento) && $this->go_delete == _DELETE_NO) {
                $id_evento= $this->id_evento;
                $id_evento_code= $this->id_evento_code;
            }

            $this->obj_event->add($id_evento, $id_evento_code);
            $this->error= $this->obj_event->error;
            if (!empty($this->error) && (stripos($this->error, 'duplicate') !== false || stripos($this->error, 'duplicad') !== false))
                $duplicate= true;

            if (is_null($this->error)) {
                $this->_id= $this->obj_event->GetId();
                $this->_id_code= $this->obj_event->get_id_code();

                $this->_id_evento= $this->_id;
                $this->_id_evento_code= $this->_id_code;
                $this->_id_auditoria= $this->id_auditoria;
                $this->_id_auditoria_code= $this->id_auditoria_code;

                if (empty($_id_evento) && $this->go_delete == _DELETE_NO) {
                    $this->obj_event->update_tidx($this->_id, false, "teventos");

                    $obj_event= new Tevento($this->clink);
                    $obj_event->SetIdEvento($this->id_evento);
                    $obj_event->Set();

                    $obj_event->set_id_evento_ref($this->id_evento);
                    $obj_event->set_id_evento_ref_code($this->id_evento_code);

                    $obj_event->update_id_evento_ref($this->id_evento);
                }
            }
        } else {
            $this->obj_event->update();
            $this->error= $this->obj_event->error;
        }
        if (!is_null($this->error)) {
            $this->error= "No se puede reprogramar, en ese día ya esta la tarea. ".$this->error;
            return;
        }

        if ($this->id != $this->_id || $duplicate) {
            if ($duplicate)
                $this->_id= $this->id;
            $this->_fix_reprograming($fecha_inicio);

            if (is_null($this->error) && !$duplicate) {
                $this->_fix_delete();
            }
        }

        if (is_null($this->error) && $this->sendmail) {
            $this->init_mail($_SESSION['id_usuario']);
            $this->send_mails_by_users("reprogramada", $this->array_reg_usuarios);
            $this->obj_mail->smtpClose();
        }
    }

    private function _fix_reprograming($fecha_inicio) {
        $this->obj_event->set_copyto($this->id, date('Y', strtotime($fecha_inicio)), $this->obj_event->get_id_code());

        if (is_null($this->error)) {
            if ($this->radio_user || $this->radio_prs) {
                $this->init_register_usuarios();
            }
            $this->fix_register_usuarios();
        }
        if (is_null($this->error)) {
            $this->init_register_procesos();
            $this->fix_register_procesos();
        }

        if ($this->id == $this->_id)
            return;

        $this->get_participantes();

        if ($this->ifmeeting && is_null($this->error)) {
            $this->fix_tematicas();
        }
        if ($this->ifmeeting && is_null($this->error)) {
            $this->fix_asistencias();
        }
        reset($this->array_grupos);
        reset($this->array_usuarios);

        $this->fix_participantes();

        if (is_null($this->error)) {
            $this->get_documentos();
            $this->fix_documentos();
        }
    }

    private function _fix_delete() {
        $deleted= false;
        $if_attached= false;

        $this->delete_usuarios();
        $this->delete_procesos();

        $obj_matter= new Ttematica($this->clink);
        $obj_matter->SetYear($this->year);

        $obj_assist= new Tasistencia($this->clink);
        $obj_assist->SetYear($this->year);

        if ($this->ifmeeting) {
            $obj_matter->SetIdEvento($this->id);
            $if_attached= $obj_matter->get_if_attached_evento();

            if (!$if_attached) {
                $obj_assist->SetIdUsuario(null);
                $obj_assist->SetIdTematica(null);
                $obj_assist->SetIdEvento($this->id);
                $obj_assist->set_id_evento_code($this->id_code);
                $obj_assist->eliminar(null, false);

                $obj_matter->SetIdTematica(null);
                $obj_matter->SetIdEvento($this->id);
                $obj_matter->set_id_evento_code($this->id_code);
                $obj_matter->eliminar();
            } else {
                $this->error= "Esta reunión tiene registrado debates y acuerdos. No pudo ser eliminada. ";
                $this->error.= "Debe eliminarle los acuerdos y los debates.";
            }
        }

        if ($this->go_delete == _DELETE_YES || $this->go_delete == _DELETE_PHY) {
            $deleted= $this->obj_event->eliminar_if_empty();
        }

        return $deleted;
    }

    private function fix_tematicas() {
        $this->obj_event->copy_in_object($this->obj_matter);
        $this->obj_matter->SetYear($this->year);
        $this->obj_matter->SetIdEvento($this->_id_evento);
        $this->obj_matter->set_id_evento_code($this->_id_evento_code);

        $array_tematicas= $this->obj_matter->move_all_by_evento_to($this->id_evento);
        $this->error= $this->obj_matter->error;

        foreach ($array_tematicas as $row) {
            $this->fix_participantes($row['id'], $row['id_code']);
        }
    }

    private function fix_asistencias() {
        $this->obj_assist->SetIdEvento($this->_id_evento);
        $this->obj_assist->set_id_evento_code($this->_id_evento_code);

        $this->obj_assist->move_all_by_evento_to($this->id_evento);
        $this->error= $this->obj_assist->error;
    }

    private function _copy() {
        $array_id_show= $this->copy_all == 2 ? $this->obj->lista_parent_eventos() : $this->array_id_show;

        foreach ($array_id_show as $id) {
            if (isset($this->obj)) 
                unset($this->obj);
            $this->obj= new Tevento($this->clink);
            $this->obj->SetIdEvento($id);
            $this->obj->Set();

            $id_entity= $this->obj->GetIdProceso();
            if ($id_entity != $_SESSION['id_entity'])
                continue;

            $this->id_auditoria= $this->obj->GetIdAuditoria();
            if (!empty($this->id_auditoria))
                continue;
            $this->id_tarea= $this->obj->GetIdTarea();
            if (!empty($this->id_tarea))
                continue;

            $this->if_valid_tipo_evento();
            if ($this->if_synchronize($id_entity))
                continue;

            $id_evento_ref= $this->obj->get_id_evento_ref();
            if (!empty($id_evento_ref)) {
                unset($this->obj);
                $this->obj= new Tevento($this->clink);
                $this->obj->SetIdEvento($id_evento_ref);
                $this->obj->Set();
            }

            $array= $this->obj->if_exists_copyto($this->to_year);

            $this->obj->set_cronos($this->cronos);
            $this->obj->action= $this->action;
            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
            $this->obj->SetYear($this->year);
            $this->obj->SetMonth($this->month);

            $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs, $this->to_year, $array);
        }
    }

    private function _init_apply() {
        if ($this->extend != 'A') {
            if (!empty($_POST['fecha']) && !empty($_POST['hora']))
                $this->fecha= time2odbc($_POST['fecha'].' '.$_POST['hora']);
            else
                $this->fecha= $_POST['year'].'-'.str_pad($_POST['month'], 2, '0',STR_PAD_LEFT).'-'.str_pad($_POST['day'], 2, '0',STR_PAD_LEFT);

            $this->signal == 'calendar' ? $this->obj->SetIfEmpresarial(null) : $this->obj->SetIfEmpresarial(1);
            $id_evento_ref= $this->extend == 'U' || $this->extend == 'Y' || $this->extend == 'N' ? $this->id_evento_ref : null;

            $with_task_added= !empty($this->id_tarea) ? true : null;
            $id_evento_ref= $with_task_added ? null : $id_evento_ref;

            if ($this->signal == 'calendar') {
                $this->obj->SetIdUsuario($this->id_calendar);
                $this->obj->SetIdProceso(null);
            } else {
                $this->obj->SetIdUsuario(null);
                $this->obj->SetIdProceso($this->id_proceso);
            }

            $all_month= $this->radio_date == 1 ? false : true;
            $this->array_eventos= $this->obj->get_eventos_by_period($this->fecha, $this->extend, $id_evento_ref,
                                                                                true, $with_task_added, $all_month);
            if ($this->signal == 'anual_plan') {
                $this->array_eventos[]= array('id'=>$this->id, 'id_code'=>$this->id_code, 'id_usuario'=>$this->id_calendar,
                    'id_responsable'=>$this->id_responsable_ref, 'ifmeeting'=>$this->obj->GetIdTipo_reunion(), 
                    'id_tarea'=>$this->obj->GetIdTarea(), 'id_tarea_code'=>$this->obj->get_id_tarea_code(), 
                    'id_tematica'=>$this->obj->GetIdTematica(), 'id_tematica_code'=>$this->obj->get_id_tematica_code(), 
                    'rechazado'=>$this->obj->GetRechazado(), 'id_archivo'=>$this->obj->GetIdArchivo(),
                    'id_archivo_code'=> $this->obj->get_id_archivo_code(), 'lugar'=>$this->obj->GetLugar(),
                    'evento'=>$this->obj->GetNombre(), 'observacion'=>$this->obj->GetObservacion(), 
                    'toshow'=>$this->obj->get_toshow_plan(),
                    'descripcion'=>$this->obj->GetDescripcion(), 'fecha_inicio'=>$this->obj->GetFechaInicioPlan(),
                    'fecha_fin'=>$this->obj->GetFechaFinPlan(), 'id_proceso'=> $this->obj->GetIdProceso(),
                    'id_proceso_code'=> $this->obj->get_id_proceso_code(), 'id_user_asigna'=> $this->obj->get_id_user_asigna(),
                    'id_nota'=>null, 'id_nota_code'=>null, 'id_riesgo'=>null, 'id_riesgo_code'=>null,
                    'id_proyecto'=>$this->obj->GetIdProyecto(), 'id_proyecto_code'=>$this->obj->get_id_proyecto_code(), 
                    'cumplimiento'=>null, 'rechazado'=>null, 'aprobado'=>null);
            }

        } else {
            $this->if_fixed= $this->obj->get_if_fixed($this->id);
            $this->obj_reg->SetIdEvento($this->id);

            if ($this->signal == 'calendar') {
                $this->obj_reg->SetIdUsuario($this->id_calendar);
                $rowcmp= $this->obj_reg->getEvento_reg();
            } else {
                $this->obj->SetIdUsuario(null);
                $this->obj->SetIdProceso($this->id_proceso);
                $rowcmp= $this->obj_reg->get_reg_proceso();
            }

            $this->array_eventos[]= array('id'=>$this->id, 'id_code'=>$this->id_code, 'id_usuario'=>$this->id_calendar,
                'id_responsable'=>$this->id_responsable_ref, 'ifmeeting'=>$this->obj->GetIdTipo_reunion(), 
                'id_tarea'=>$this->obj->GetIdTarea(), 'id_tarea_code'=>$this->obj->get_id_tarea_code(), 
                'id_tematica'=>$this->obj->GetIdTematica(), 'id_tematica_code'=>$this->obj->get_id_tematica_code(), 
                'rechazado'=>$this->obj->GetRechazado(), 'id_archivo'=>$this->obj->GetIdArchivo(),
                'id_archivo_code'=> $this->obj->get_id_archivo_code(), 'lugar'=>$this->obj->GetLugar(),
                'evento'=>$this->obj->GetNombre(), 'observacion'=>$this->obj->GetObservacion(), 
                'toshow'=>$this->obj->get_toshow_plan(),
                'descripcion'=>$this->obj->GetDescripcion(), 'fecha_inicio'=>$this->obj->GetFechaInicioPlan(),
                'fecha_fin'=>$this->obj->GetFechaFinPlan(), 'id_proceso'=> $this->obj->GetIdProceso(),
                'id_proceso_code'=> $this->obj->get_id_proceso_code(), 'id_user_asigna'=> $this->obj->get_id_user_asigna(),
                'id_nota'=>null, 'id_nota_code'=>null, 'id_riesgo'=>null, 'id_riesgo_code'=>null,
                'id_proyecto'=>$this->obj->GetIdProyecto(), 'id_proyecto_code'=>$this->obj->get_id_proyecto_code(),
                'cumplimiento'=>$rowcmp['cumplimiento'], 'rechazado'=>$rowcmp['rechazado'], 'aprobado'=>$rowcmp['aprobado']);
        }
    }        

    public function apply() {
        $this->obj= new Tevento($this->clink);
        $this->obj_event= new Tevento($this->clink);
        $this->obj_reg= new Tplanning($this->clink);
        $this->obj_matter= new Ttematica($this->clink);
        $this->obj_assist= new Tasistencia($this->clink);

        if ($this->menu != 'fcopy' && $this->action != 'copy') {
            $this->obj->SetIdEvento($this->id);
            $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();

            $this->id_evento= $this->id;
            $this->id_evento_code= $this->id_code;
            $this->id_auditoria= $this->obj->GetIdAuditoria();
            $this->id_auditoria_code= $this->obj->get_id_auditoria_code();

            $this->id_evento_ref= $this->obj->get_id_evento_ref();
            $this->id_evento_ref_code= $this->obj->get_id_evento_ref_code();

            $this->toshow_plan= $this->obj->get_toshow_plan();
            $this->nombre= $this->obj->GetNombre();
            $this->lugar= $this->obj->GetLugar();
            $this->fecha_inicio_plan= $this->obj->GetFechaInicioPlan();
            $this->fecha_fin_plan= $this->obj->GetFechaFinPlan();

            $this->_id_proceso= $this->obj->GetIdProceso();
            $this->_id_proceso_code= $this->obj->get_id_proceso_code();

            if (empty($this->id_evento_ref)) {
                $this->id_evento_ref= $this->id;
                $this->id_evento_ref_code= $this->id_code;
            }

            $this->id_tarea= $this->obj->GetIdTarea();
            $this->id_tarea_code= $this->obj->get_id_tarea_code();
            $this->id_auditoria= $this->obj->GetIdAuditoria();
            $this->id_auditoria_code= $this->obj->get_id_auditoria_code();

            $this->ifmeeting= $this->obj->GetIdTipo_reunion();
            $this->id_tematica= $this->obj->GetIdTematica();
            $this->id_tematica_code= $this->obj->get_id_tematica_code();

            $this->id_archivo= $this->obj->GetIdArchivo();
            $this->id_archivo_code= $this->obj->get_id_archivo_code();

            $this->id_user_asigna= $this->obj->get_id_user_asigna();

            $this->obj->set_cronos($this->cronos);

            $this->id_responsable_ref= $this->obj->GetIdResponsable();
            $this->obj->SetIdUsuario($this->id_calendar);

            $this->obj->SetMonth($this->month);
            $this->year= !empty($this->year) ? $this->year : date('Y', strtotime($this->fecha_inicio_plan));
            $this->obj->action= $this->action;
        }

        $this->obj_event->SetYear($this->year);
        $this->obj->SetYear($this->year);
        $this->obj_reg->SetYear($this->year);
        $this->obj_matter->SetYear($this->year);
        $this->obj_assist->SetYear($this->year);

        $this->init_entity();
        if ($this->extend != 'A')
            $this->get_evento_origen();

        if (!empty($this->id_proceso))
            $this->init_cascade_down();
        $this->if_fixed= false;

        if ($this->menu != 'fcopy') {
            $this->_init_apply();

            $this->obj->SetIdResponsable($this->id_responsable);
            $this->obj->SetObservacion($this->observacion);
            $this->obj->SetDescripcion($this->descripcion);
            $this->obj->SetIdProceso($this->signal == 'calendar' ? null : $this->id_proceso);
            $this->obj->set_id_proceso_code($this->signal == 'calendar' ? null : $this->id_proceso_code);
        }

        if ($this->ifmeeting
            && ($this->if_fixed && ($this->menu != 'fregevento' || ($this->menu == 'fcopy' && $this->action == 'repro')))) {
            $this->error= "Esta reunión ya fue realizada, tiene asistentes reportados o acuerdos registrados. ";
            $this->error.= "No puede ser eliminada, reprogramada o rechazada.";
        }

        $this->obj->copy_in_object($this->obj_reg);
        $this->obj_reg->SetYear($this->year);

        if ($this->menu == 'fregevento')
            $this->_register();
        if ($this->menu == 'freject' && !$this->if_fixed)
            $this->_reject();
        if ($this->menu == 'fdelete' && !$this->if_fixed)
            $this->_delete();
        if ($this->menu == 'freproevento' && !$this->if_fixed)
            $this->_reprograming();
        if ($this->menu == 'fdelegate' && !$this->if_fixed)
            $this->_delegate();

        if ($this->menu == 'fcopy') {
            $this->obj->SetYear($this->year);

            if ($this->action == 'repro' && !$this->if_fixed) {
                $this->obj->SetIdEvento($this->id);
                $this->obj->Set();

                $this->if_valid_tipo_evento();
                $if_synchronized= $this->if_synchronize($this->obj->GetIdProceso()) ? true : false ;
                $array= $this->obj->if_exists_copyto($this->to_year);
                $if_copy_exists= !empty($array) ? true : false;
                $if_entity= array_key_exists($this->obj->GetIdProceso(), $this->array_procesos_entity) ? true : false;

                if (!$if_synchronized && $if_entity) {
                    $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs, $this->to_year, $array);

                } else {
                    if ($if_synchronized || $if_entity)
                        $this->obj->error= "No puede copiar una tarea generada desde otra Unidad Organizativa.";
                    if ($if_copy_exists)
                        $this->obj->error= "Esta actividad ya fue copiada al $this->to_year";
                }

                $this->error= $this->obj->error;
            }

            if ($this->action == 'copy') {
                $this->_copy();
            }
        }

        if (is_null($this->error)) {
      ?>
cerrar();

<?php  } else { ?>
cerrar("<?= $this->error?>");
<?php
        }
    }
}
?>

<?php if (!$ajax_win) { ?>
</body>

</html>
<?php } else { ?>
</div>
<?php } ?>

<?php if (!$block_execute_apply_function) { ?>
<script type="text/javascript">
<?php if (!$ajax_win) { ?>
$(document).ready(function() {
    setInterval('setChronometer()', 1);

    $('#body-log table').mouseover(function() {
        _moveScroll = false;
    });
    $('#body-log table').mouseout(function() {
        _moveScroll = true;
    });
    <?php } ?>

    <?php
        $interface= new TEventoRegisterInterface($clink);

        $badger= new Tbadger();
        $badger->SetLink($clink);
        $badger->SetYear($interface->GetYear());
        $badger->set_user_date_ref();
        $badger->set_tusuarios();

        $interface->apply();
        ?>

    <?php if (!$ajax_win) { ?>
});
<?php } ?>
</script>
<?php } ?>