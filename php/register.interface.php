<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

set_time_limit(99999999999999999999999999999999);
session_cache_expire(99999999999999999999999999);

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "register_base.interface.class.php";

require_once "class/mail.class.php";
require_once "class/code.class.php";
require_once "class/badger.class.php";


class TRegister extends TRegisterBase {

    public function __construct($clink= null) {
        $this->clink = $clink;
        TRegisterBase::__construct($clink);
    }

    protected function _test_usuario_entity($id_usuario) {
        return array_key_exists($id_usuario, $this->array_usuarios_entity);
    }

    protected function body_mail($array, $id_usuario, $action, $extend) {
        $this->obj_mail->lugar = utf8_decode($array['lugar']);
        $this->obj_mail->evento = utf8_decode($array['evento']);
        $this->obj_mail->observacion = utf8_decode($array['observacion']);
        $this->obj_mail->descripcion = utf8_decode($array['descripcion']);
        $this->obj_mail->fecha_inicio = $array['fecha_inicio'];
        $this->obj_mail->fecha_fin = $array['fecha_fin'];

        $obj_user = new Tusuario($this->clink);
        $email = $obj_user->GetEmail($id_usuario);
        $this->obj_mail->usr_target = utf8_decode($email['nombre']);
        if (!empty($email['cargo']))
            $this->obj_mail->usr_tarjet.= ", ". utf8_decode($email['cargo']);

        $this->obj_mail->Subject= "ACTIVIDAD ". strtoupper($action);
        if ($extend == 'A')
            $this->obj_mail->body_event_mail($action);
        else
            $this->obj_mail->body_event_mail_block($action);

        unset($obj_user);
        return $email;
    }

    protected function to_mail($id_to, $email= null) {
        if (is_null($email)) {
            $obj_user = new Tusuario($this->clink);
            $email = $obj_user->GetEmail($id_to);
        }
        if (empty($email['email']))
            return;

        if (!empty($email['email'])) {
            $this->obj_mail->clearAddresses();

            $nombre= $email['nombre'];
            $nombre.= !empty($email['cargo']) ? ", {$email['cargo']}" : null;
            $this->obj_mail->addAddress($email['email'], utf8_decode($nombre));
            $this->send_mail();
        }
    }

    protected function send_mail() {
        try {
            $this->obj_mail->send();
        } catch (Exception $e) {
            $this->error = "No se ha podido contactar al servidor de correos. ";
            echo $this->error;
        }
    }

    protected function send_mails_by_users($action, $array_usuarios= null, $evento= null) {
        if (is_null($evento)) {
            $obj= !empty($this->obj_event) ? $this->obj_event : $this->obj;
            $evento= array('lugar'=>$obj->GetLugar(), 'evento'=>$obj->GetNombre(), 'observacion'=>$obj->GetObservacion(),
                'descripcion'=>$obj->GetDescripcion(), 'fecha_inicio'=>$obj->GetFechaInicioPlan(),
                'fecha_fin'=>$obj->GetFechaFinPlan());
        }

        reset($array_usuarios);
        foreach ($array_usuarios as $user) {
            $email= $this->body_mail($evento, $user['id'], "reprogramada", null);
            $this->to_mail(null, $email);
        }
    }

    protected function if_valid_tipo_evento() {
        $id_tipo_evento= $this->obj->GetIdTipo_evento();
        if (empty($id_tipo_evento))
            return true;

        $obj_tipo= new Ttipo_evento($this->clink);
        $obj_tipo->SetIdTipo_evento($id_tipo_evento);
        $obj_tipo->Set();

        $inicio= $obj_tipo->GetInicio();
        $fin= $obj_tipo->GetFin();
        $valid= $this->to_year < $inicio || $this->to_year > $fin ? false : true;

        if (!$valid) {
            $this->obj->SetIdTipo_evento(null);
            $this->obj->set_id_tipo_evento_code(null);
        }
        return $valid;
    }

    protected function get_documentos() {
        $this->obj_doc= new Tdocumento($this->clink);
        $this->obj_doc->SetIdEvento($this->id_evento);
        $this->obj_doc->SetIdAuditoria($this->id_auditoria);

        $this->array_documentos= $this->obj_doc->get_documentos(false);
        unset($this->obj_doc);
        $this->obj_doc= null;
    }

    protected function fix_documentos() {
        $obj_doc= new Tdocumento($this->clink);
        $obj_doc->SetYear($this->year);

        if ($this->ifmeeting) {
            $obj_doc->SetIdEvento($this->_id_evento);
            $obj_doc->set_id_evento_code($this->_id_evento_code);

            $obj_doc->move_all_by_evento_to($this->id_evento);
            $this->error= $obj_doc->error;

        } else {
            $obj_doc->SetIdEvento($this->_id_evento);
            $obj_doc->set_id_evento_code($this->_id_evento_code);
            $obj_doc->SetIdAuditoria($this->_id_auditoria);
            $obj_doc->set_id_auditoria_code($this->_id_auditoria_code);

            $obj_doc->copy_documentos($this->array_documentos);
            /*
            foreach ($array_documentos as $array) {
                $obj_doc->SetIdDocumento($array['id']);
                $obj_doc->SetIdEvento($this->id);
                $obj_doc->eliminar();
            }
            */
        }
    }

    protected function get_participantes() {
        $obj= new Tevento($this->clink);
        $obj->SetIdEvento($this->id_evento);
        $obj->SetIdAuditoria($this->id_auditoria);

        $obj->SetYear($this->year);
        $obj->SetIdEntity($_SESSION['id_entity']);
        $obj->set_id_entity_code($_SESSION['id_entity_code']);

        $this->array_grupos= $obj->listar_grupos();
        $this->array_usuarios= $obj->listar_usuarios();
    }

    protected function fix_participantes($id_tematica= null, $id_tematica_code= null) {
        $obj= new Tevento($this->clink);
        $obj->SetYear($this->year);

        $obj->SetIdEvento($this->_id_evento);
        $obj->set_id_evento_code($this->_id_evento_code);
        $obj->SetIdAuditoria($this->_id_auditoria);
        $obj->set_id_auditoria_code($this->_id_auditoria_code);
        $obj->SetIdTarea($this->id_tarea);
        $obj->set_id_tarea_code($this->id_tarea_code);
        $obj->SetIdTematica($id_tematica);
        $obj->set_id_tematica_code($id_tematica_code);

        foreach ($this->array_grupos as $row) {
            $obj->SetIdGrupo($row['id']);
            $obj->setGrupo('add');
        }
        foreach ($this->array_usuarios as $row) {
            if (is_array($this->array_usuarios_down) && !array_key_exists($row['id'], $this->array_usuarios_down))
                continue;
            $obj->SetIdUsuario($row['id']);
            $obj->setUsuario('add');
        }
    }

    protected function test_if_delete_proceso($id_proceso, $toshow_prs) {
        if (is_null($toshow_prs))
            return true;
        if ($toshow_prs == _EVENTO_ANUAL && !$this->radio_toshow)
            return false;
        if ($this->radio_prs && $id_proceso = $this->id_proceso)
            return true;
        if ($this->radio_prs == 2 && array_key_exists($id_proceso, $this->array_procesos_down))
            return true;

        return false;
    }

    protected function test_if_responsable_prs($id_usuario, &$id_proceso= null) {
        foreach ($this->array_procesos_down as $prs) {
            if ($id_usuario == $prs['id_responsable']) {
                $id_proceso= $prs['id'];
                return true;
        }   }
        
        reset($this->array_procesos_down);
        return false;
    }

    protected function set_delete_date() {
        $id= null;
        $id_code= null;
        $date= null;
        $this->go_delete= $_POST['go_delete'];
        $this->obj->set_go_delete($this->go_delete);

        $this->obj->array_usuarios_entity= $this->array_usuarios_entity;

        $signal= $this->signal;
        if ($this->signal == 'anual_plan' || ($this->signal == 'anual_plan_meeting' && $this->_ifmeeting == 3)) {
            $signal= 'anual_plan';
            $extend= 'Y';
            $date= $this->year.'-01-01';
        }
        if ($this->radio_date == 0 || ($this->signal == 'anual_plan_meeting' && $this->_ifmeeting == 1)) {
            $signal= 'mensual_plan';
            $extend= 'D';
            $date= date('Y-m-d', strtotime($this->fecha_inicio_plan));
        }
        if (($signal != 'anual_plan') && ($this->radio_date == 1 || $this->radio_date == 2)) {
            $extend= 'M';
            $date= date('Y-m-d', strtotime($this->fecha_inicio_plan));
        }
        if (($signal == 'anual_plan') || $this->radio_date == 2) {
            $extend= 'Y';
            $date= $this->year.'-01-01';
        }

        if ($extend != "D") {
            if ($this->className == "Tevento") {
                $id= !empty($this->id_evento_ref) ? $this->id_evento_ref : $this->id_evento;
                $id_code= !empty($this->id_evento_ref_code) ? $this->id_evento_ref_code : $this->id_evento_code;
            }
            if ($this->className == "Tauditoria") {
                $id= !empty($this->id_auditoria_ref) ? $this->id_auditoria_ref : $this->id_auditoria;
                $id_code= !empty($this->id_auditoria_ref_code) ? $this->id_auditoria_ref_code : $this->id_auditoria_code;
            }
        } else {
            if ($this->className == "Tevento") {
                $id= $this->id_evento;
                $id_code= $this->id_evento_code;
            }
            if ($this->className == "Tauditoria") {
                $id= $this->id_auditoria;
                $id_code= $this->id_auditoria_code;
            }
        }

        return array('id'=>$id, 'id_code'=>$id_code, 'date'=>$date, 'extend'=>$extend);
    }

    /*
     * Estado de los participantes en el nuevo ebento
     */
    protected function fix_register_usuarios() {
        global $badger;

        $id_responsable= ($this->signal != 'calendar' || $this->radio_user) ? $this->id_responsable : $this->id_calendar;
        $this->obj_event->SetIdUsuario($id_responsable);

        $this->obj_event->SetIdEvento($this->_id_evento);
        $this->obj_event->set_id_evento_code($this->_id_evento_code);
        $this->obj_event->SetIdAuditoria($this->_id_auditoria);
        $this->obj_event->set_id_auditoria_code($this->_id_auditoria_code);
        $this->obj_event->SetIdTarea($this->id_tarea);
        $this->obj_event->set_id_tarea_code($this->id_tarea_code);

        $this->obj_event->SetObservacion($this->observacion);
        $this->obj_event->_observacion= $this->observacion;
        $this->obj_event->SetCumplimiento(_NO_INICIADO);
        $this->obj_event->SetAprobado($this->aprobado);
        $this->obj_event->SetRechazado(null);

        if (array_key_exists($id_responsable, $this->array_usuarios_down))
            $this->obj_event->update_reg('add', $this->go_delete, $_SESSION['id_usuario']);

        if (($this->signal == 'calendar' && $this->radio_user) && $this->id_responsable != $this->id_calendar)  {
            $this->obj_event->SetObservacion($this->observacion);
            $this->obj_event->SetCumplimiento(_NO_INICIADO);
            $this->obj_event->SetAprobado($this->aprobado);
            $this->obj_event->SetRechazado(null);

            $this->obj_event->SetIdUsuario($this->id_calendar);
            $this->obj_event->update_reg('add', $this->go_delete, $_SESSION['id_usuario']);
        }

        if ($this->radio_user || $this->radio_prs) {
            foreach ($this->array_reg_usuarios as $user) {
                /*
                if (is_array($this->array_usuarios_entity) && !array_key_exists($user['id'], $this->array_usuarios_entity))
                    continue;
                */
                if (!empty($this->id_calendar) && $user['id'] == $this->id_calendar) {
                    continue;
                }
                if ($this->signal != 'calendar' && $user['id'] == $this->id_responsable) {
                    continue;
                }

                $continue= false;

                if ($this->radio_user && array_key_exists($user['id'], $this->array_usuarios_down)) {
                    $continue= true;
                }
                if ($this->radio_prs >= 1 && $user['id_proceso'] == $this->id_proceso) {
                    $continue= true;
                }
                if ($this->radio_prs == 2 && array_key_exists($user['id_proceso'], $this->array_procesos_down)) {
                    $continue= true;
                }
                if (!$continue)
                    continue;

                $this->obj_event->SetIdUsuario($user['id']);

                $this->obj_event->SetObservacion($this->observacion);
                $this->obj_event->SetCumplimiento(_NO_INICIADO);
                $this->obj_event->SetAprobado($this->aprobado);
                $this->obj_event->SetRechazado(null);

                $this->obj_event->update_reg('add', $this->go_delete, $_SESSION['id_usuario']);
            }
        } else {
            $this->array_reg_usuarios= $this->_fix_register_usuario();
        }

        reset($this->array_reg_usuarios);
        return $this->array_reg_usuarios;
    }

    private function _fix_register_usuario() {
        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdUsuario($this->id_calendar);
        $obj_user->Set();

        $array= array('id'=>$this->id_calendar, 'nombre'=>$obj_user->GetNombre(), 'email'=>$obj_user->GetMail_address(),
            'cargo'=>$obj_user->GetCargo(), 'eliminado'=>$obj_user->GetEliminado(), 'usuario'=>$obj_user->GetUsuario(),
            'nivel'=>$obj_user->GetRole(), 'id_proceso'=>$obj_user->GetIdProceso(), 'id_proceso_code'=>$obj_user->get_id_proceso_code(),
            '_id'=>$this->id_calendar, 'prs'=>null, 'marked'=>null
        );

        $this->array_reg_usuarios[$this->id_calendar]= $array;
        return $this->array_reg_usuarios;
    }

    /*  asignacion a los procesos */
    protected function fix_register_procesos() {
        $this->obj_reg->SetIdEvento($this->_id_evento);
        $this->obj_reg->set_id_evento_code($this->_id_evento_code);
        $this->obj_reg->SetIdAuditoria($this->_id_auditoria);
        $this->obj_reg->set_id_auditoria_code($this->_id_auditoria_code);
        $this->obj_reg->SetIdTarea($this->id_tarea);
        $this->obj_reg->set_id_tarea_code($this->id_tarea_code);

        foreach ($this->array_reg_procesos as $prs) {
            $continue= false;
            if ($this->radio_user && array_key_exists($prs['id_responsable'], $this->array_usuarios_down))
                $continue= true;
            if ($this->radio_prs == 1 && $prs['id'] == $this->id_proceso)
                $continue= true;
            if ($this->radio_prs == 2 && array_key_exists($prs['id'], $this->array_procesos_down))
                $continue= true;

            if (!$continue)
                continue;

            $this->obj_reg->SetIdResponsable($prs['id_responsable']);
            $this->obj_reg->SetIdProceso($prs['id']);
            $this->obj_reg->set_id_proceso_code($prs['id_code']);

            $this->obj_reg->SetIfEmpresarial($prs['empresarial']);
            $this->obj_reg->SetIdTipo_evento($prs['id_tipo_evento']);
            $this->obj_reg->set_id_tipo_evento_code($prs['id_tipo_evento_code']);
            $this->obj_reg->indice= $prs['indice'];
            $this->obj_reg->indice_plus= $prs['indice_plus'];

            $toshow= $prs['toshow'] >=  _EVENTO_INDIVIDUAL ? _EVENTO_MENSUAL : _EVENTO_INDIVIDUAL;
            $this->obj_reg->toshow= $this->radio_toshow ? $prs['toshow'] : $toshow;

            $this->obj_reg->SetValue(_NO_INICIADO);
            $this->obj_reg->SetObservacion($this->observacion);

            $this->obj_reg->update_reg_proceso('add');
        }
    }

    private function _register_prs() {
        $this->init_register_procesos();

        foreach ($this->array_reg_procesos as $prs) {
            if (!array_key_exists($prs['id'], $this->array_procesos_down))
                continue;

            if ($this->extend != 'P') { // 'P' => significa que viene del tablero de gantt o del kanban
                if (($this->extend != 'A' && $this->extend != 'U') && (!is_null($prs['rechazado'])
                        || ($prs['cumplimiento'] == _SUSPENDIDO || $prs['cumplimiento'] == _CANCELADO || $prs['cumplimiento'] == _DELEGADO || $prs['cumplimiento'] == _POSPUESTO)))
                    continue;
            }  

            $prs['id_evento']= !empty($prs['id_evento']) ? $prs['id_evento'] : $this->id_evento;
            $prs['id_evento_code']= !empty($prs['id_evento_code']) ? $prs['id_evento_code'] : $this->id_evento_code;

            $prs['rechazado']= $this->rechazado;
            $prs['cumplimiento']= $this->cumplimiento;
            if (($this->radio_user)
                    || ($this->radio_prs && ($this->signal == 'calendar'
                        && ($prs['id_responsable'] == $this->id_calendar || $this->id_responsable == $this->id_calendar 
                            || $prs['id_responsable_prs'] == $this->id_calendar)))
                    || ($this->signal != 'calendar' && ($this->radio_prs == 2 || ($this->radio_prs == 1 && $prs['id'] == $this->id_proceso)))) {

                $this->obj_reg->add_cump_proceso($prs['id'], $prs['id_code'], $prs);
        }   } 
    }

    private function _register_cascade() {
        $this->init_register_usuarios();

        if ($this->radio_user) {
            foreach ($this->array_reg_usuarios as $id_usuario => $user) {
                if ($id_usuario == $this->id_usuario)
                    continue;
                if (!array_key_exists($id_usuario, $this->array_usuarios_down))
                    continue;

                if ($this->extend != 'P') {
                    if (($this->extend != 'A' && $this->extend != 'U') && (!is_null($user['rechazado'])
                            || ($user['cumplimiento'] == _SUSPENDIDO || $user['cumplimiento'] == _CANCELADO || $user['cumplimiento'] == _DELEGADO || $user['cumplimiento'] == _POSPUESTO)))
                        continue;
                }

                $this->obj_reg->set_user_check($user['user_check']);
                $this->obj_reg->SetIdUsuario($id_usuario);
                $this->obj_reg->add_cump();
        }   }

        $this->_register_prs();  
    }

    protected function _register() {
        global $config;

        $this->obj_reg->SetYear($this->year);
        $this->_init_prs();
        $cant_events= count($this->array_eventos);

        foreach ($this->array_eventos as $array) {
            if ($this->extend == 'P') {
                $year= date('Y', strtotime($array['fecha_inicio_plan']));
                if ($year < $this->year)
                    continue;
                $this->obj_reg->SetYear($year);
            }

            if ($this->extend != 'A' && $cant_events > 1) {
                if (!$config->risk_note_automatic && !empty($array['id_tarea'])) {
                    if ($this->if_riesgo_origin($array['id_tarea']))
                        continue;
                    if ($this->if_nota_origin($array['id_tarea']))
                        continue;
                }
                if (!$config->accords_automatic && $this->if_accords_origin($array['id'])) {
                    continue;
                }                
            }

            $this->id_evento= $array['id'];
            $this->id_auditoria= $array['id_auditoria'];
            $this->id_tarea= $array['id_tarea'];

            $this->id_evento_code= $array['id_code'];
            $this->id_auditoria_code= $array['id_auditoria_code'];
            $this->id_tarea_code= $array['id_tarea_code'];

            $this->rechazado= $array['rechazado'];
            $this->obj_reg->SetIdEvento($array['id']);
            $this->obj_reg->set_id_evento_code($array['id_code']);

            $this->obj_reg->SetIdTarea($array['id_tarea']);
            $this->obj_reg->set_id_tarea_code($array['id_tarea_code']);

            $this->obj_reg->SetIdAuditoria($array['id_auditoria']);
            $this->obj_reg->set_id_auditoria_code($array['id_auditoria_code']);

            $this->obj_reg->SetIdArchivo($array['id_archivo']);
            $this->obj_reg->set_id_archivo_code($array['id_archivo_code']);

            $this->id_usuario= $this->signal == 'calendar' ? $array['id_usuario'] : $array['id_responsable'];
            $this->id_responsable= $array['id_responsable'];
            $this->id_user_asigna= $array['id_user_asigna'];

            $this->obj_reg->SetIdUsuario($this->id_usuario);
            $this->obj_reg->SetCumplimiento(null);
            $row= $this->obj_reg->getEvento_reg();

            if (is_null($row))
                continue;
            if ($this->extend != 'P') {
                if (($this->extend != 'A' && $this->extend != 'U') && (!is_null($row['rechazado'])
                        || ($row['cumplimiento'] == _SUSPENDIDO || $row['cumplimiento'] == _CANCELADO || $row['cumplimiento'] == _DELEGADO || $row['cumplimiento'] == _POSPUESTO)))
                    continue;
            }
            if ($this->extend == 'P') {
                if ($row['cumplimiento'] == _COMPLETADO)
                    continue;
            }

            $this->obj_reg->setEvento_reg($row);
            $this->obj_reg->set_cronos($this->cronos);
            $this->obj_reg->SetCumplimiento($this->cumplimiento);
            if ($this->cumplimiento != _SUSPENDIDO && !is_null($array['rechazado']))
                $this->obj_reg->compute= 1;

            if ($this->cumplimiento == _COMPLETADO || $this->cumplimiento == _INCUMPLIDO || $this->cumplimiento == _EN_CURSO) {
                $this->rechazado= null;
                $this->obj_reg->SetRechazado(null);
                $this->obj_reg->toshow= 1;
                $this->obj_reg->compute= 1;
            }

            if ($this->_test_usuario_entity($this->id_usuario)) {
                $this->error= $this->obj_reg->add_cump();

                if (!empty($array['id_tematica'])) {
                    $this->obj_reg->copy_in_object($this->obj_matter);

                    $this->obj_matter->SetEvaluado($this->cronos);
                    $this->obj_matter->SetIdResponsable_eval($_SESSION['id_usuario']);
                    $this->obj_matter->SetEvaluacion($this->observacion);
                    $this->obj_matter->SetIdTematica($array['id_tematica']);
                    $this->obj_matter->SetIdProceso(null);
                    $this->obj_matter->SetIdEvento(null);

                    $this->obj_matter->update_cump_matter();
            }   }

            $this->_register_cascade();
        }
    }

    private function _delegate_prs($array) {
        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);

        $obj_prs->GetProcesoEvento($array['id']);
        $obj_prs->SetIdResponsable($this->id_responsable);

        $this->empresarial = !empty($_POST['tipo_actividad1']) ? $_POST['tipo_actividad1'] : $this->toshow;
        $this->id_tipo_evento = !empty($_POST['tipo_actividad3']) ? $_POST['tipo_actividad3'] : $_POST['tipo_actividad2'];
        $this->id_tipo_evento= !empty($this->id_tipo_evento) ? $this->id_tipo_evento : null;
        $this->id_tipo_evento_code= !empty($this->id_tipo_evento) ? get_code_from_table('ttipo_eventos', $this->id_tipo_evento) : null;

        foreach ($obj_prs->array_procesos as $prs) {
            if (!$this->radio_prs && $prs['id'] != $this->id_proceso)
                continue;
            /*
            if ($prs['id_responsable'] == $this->id_usuario)
                continue;
            */
            if (!array_key_exists($prs['id'], $this->array_procesos_entity))
                continue;
            if (!array_key_exists($prs['id'], $this->array_procesos_down))
                continue;

            $obj_prs->SetIdEvento($array['id']);
            $obj_prs->set_id_evento_code($array['id_code']);
            $obj_prs->SetIdAuditoria($array['id_auditoria']);
            $obj_prs->set_id_auditoria_code($array['id_auditoria_code']);
            $obj_prs->SetIdTarea($array['id_tarea']);
            $obj_prs->set_id_tarea_code($array['id_tarea_code']);

            $obj_prs->SetIdProceso($prs['id']);
            $obj_prs->set_id_proceso_code($prs['id_code']);
            $obj_prs->toshow= $prs['toshow'];

            $cumplimiento= $this->id_responsable != $prs['id_responsable'] ? _DELEGADO : $prs['cumplimiento'];
            $obj_prs->SetCumplimiento($cumplimiento);

            $obj_prs->SetAprobado($prs['aprobado']);
            $obj_prs->SetIdResponsable_aprb($prs['id_responsable_aprb']);

            $obj_prs->SetIfEmpresarial($this->empresarial);
            $obj_prs->SetIdTipo_evento($this->id_tipo_evento);
            $obj_prs->set_id_tipo_evento_code($this->id_tipo_evento_code);

            $obj_prs->setEvento('add');

            $this->_delegate_user($array, $prs['id_responsable']);
        }
    }

    private function _fix_new_responsable_evento($id) {
        $obj= new Tevento($this->clink);
        $obj->SetYear($this->year);
        $obj->SetIdEvento($id);
        $obj->Set();

        $obj->update_responsable("teventos", $this->id_usuario, $id);
    }

    private function _fix_new_responsable_auditoria($id) {
        $obj= new Tauditoria($this->clink);
        $obj->SetYear($this->year);
        $obj->SetIdAuditoria($id);
        $obj->Set();

        $obj->update_responsable("tauditorias", $this->id_usuario, $id);
    }

    private function _delegate_user_init($array) {
        $this->obj_reg->SetIdEvento($array['id']);
        $this->obj_reg->set_id_evento_code($array['id_code']);

        $this->obj_reg->SetIdAuditoria($array['id_auditoria']);
        $this->obj_reg->set_id_auditoria_code($array['id_auditoria_code']);

        $this->obj_reg->SetIdTarea($array['id_tarea']);
        $this->obj_reg->set_id_tarea_code($array['id_tarea_code']);

        $this->obj_reg->SetIdArchivo($array['id_archivo']);
        $this->obj_reg->set_id_archivo_code($array['id_archivo_code']);

        $this->obj_reg->SetIdProceso($this->id_proceso);

        $rowcmp= $this->obj_reg->get_reg_proceso();

        if ($this->radio_user && $array['id_proceso'] == $_SESSION['id_entity']
                    && (empty($rowcmp) || ($rowcmp['id_proceso'] == $array['id_proceso']))) {
            $this->obj->update_responsable("teventos", $this->id_usuario, $array['id']);
        }
        if ($this->radio_user && !empty($rowcmp)) {
            $rowcmp['id_responsable']= $this->id_usuario;
            $this->obj_reg->add_cump_proceso($this->id_proceso, $this->id_proceso_code, $rowcmp);
        }
        $this->obj_reg->set_cronos($this->cronos);
    }

    private function _delegate_user_end($id_responsable_ref) {
        /*
         * Eliminar el registro del usuario anterior
         */
        $id_responsable= !empty($id_responsable_ref) ? $id_responsable_ref : $this->id_responsable_ref;

        if (is_null($this->error)
            && ($this->id_usuario != $id_responsable && array_key_exists($id_responsable, $this->array_usuarios_entity))) {

            $this->obj->SetIdUsuario($id_responsable);
            $this->obj_reg->SetIdUsuario($id_responsable);
            $this->obj_reg->set_cronos($this->cronos);
            $this->obj_reg->SetCumplimiento(_DELEGADO);

            if ($this->radio_user) {
                $this->obj->setUsuario('delete');
                $this->obj_reg->update_reg('delete', _DELETE_PHY);
            } else {
                if (empty($this->accept_mail_user_list)
                    || (is_array($this->accept_mail_user_list) && array_key_exists($this->id_usuario, $this->accept_mail_user_list))) {
                    $this->obj_reg->set_user_check(true);
                    $this->obj_reg->toshow= 1;
                    $this->obj_reg->compute= 1;
                    $this->error= $this->obj_reg->add_cump();
        }   }   }
    }

    private function _delegate_user($array, $id_responsable_ref= null) {
        $this->_delegate_user_init($array);

        /*
         * Adicionar al nuevo usuario
         */
        $this->obj->SetIdEvento($array['id']);
        $this->obj->SetIdTarea($array['id_tarea']);
        $this->obj->SetIdAuditoria($array['id_auditoria']);
        $this->obj->SetIdArchivo($array['id_archivo']);
        $this->obj->SetIdUsuario($this->id_usuario);

        $this->array_reg_usuarios= $this->obj->get_array_reg_usuarios();
        $found= array_key_exists($this->id_usuario, $this->array_reg_usuarios);

        $this->obj_reg->SetIdResponsable($_SESSION['id_usuario']);

        if (!$found) {
            $this->obj->SetIdUsuario($this->id_usuario);
            $this->obj->setUsuario('add');

            $this->obj_reg->SetIdUsuario($this->id_usuario);
            $this->obj_reg->SetCumplimiento(_NO_INICIADO);
            $this->obj_reg->SetRechazado(null);
            $this->obj_reg->toshow= 1;
            $this->obj_reg->compute= 1;

            $this->error= $this->obj_reg->add_cump();
        }

        $this->_delegate_user_end($id_responsable_ref);
    }

    protected function _setting() {
        $action= 'MODIFICADO';

        if ($this->control_list == 0) {
            $this->set_usuarios_array($action);
            $this->set_grupos_array($action);

            if (!empty($this->sendmail))
                $this->toMail();
        } else {
            $this->obj->SetIdAuditoria($this->_id_auditoria);
            $this->obj->set_id_auditoria_code($this->_id_auditoria_code);
            $this->obj->SetIdEvento($this->_id_evento);
            $this->obj->set_id_evento_code($this->_id_evento_code);
        }

        $this->set_usuarios_from_array($action);
        $this->set_grupos_from_array($action);

        $this->setReg(null, false);
    }

    protected function _delegate() {
        $this->control_list= 0;
        $this->obj_reg->SetYear($this->year);

        $this->id_usuario= !empty($_POST['usuario']) ? $_POST['usuario'] : $_GET['usuario'];
        $this->id_responsable= $this->id_usuario;
        $this->id_responsable_ref= !empty($_POST['_id_responsable']) ? $_POST['_id_responsable'] : null;

        if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
            if (empty($this->id_usuario))
                return;
            $this->obj_reg->SetIdUsuario($this->id_usuario);
        }

        $array_ids= array();
        foreach ($this->array_eventos as $array) {
            if ($array_ids[$array['id']])
                continue;
            $array_ids[$array['id']]= $array['id'];

            if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL)
                $this->_delegate_user($array, null);
            else {
                $this->_delegate_prs($array);
                $this->_fecha_inicio= $array['fecha_inicio'];
                $this->_setting();
            }

            if ($this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL
                || ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $array['toshow'] == _EVENTO_INDIVIDUAL)) {
                if (!empty($this->id_usuario) && $this->id_usuario != $array['id_responsable']
                    && ($array['id_responsable'] == $_SESSION['id_usuario']) || $array['id_user_asigna'] == $_SESSION['id_usuario'])
                    $this->_fix_new_responsable_evento($array['id']);
            }
        }

        foreach ($this->array_auditorias as $array) {
            if ($this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL
                    || ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $array['toshow'] == _EVENTO_INDIVIDUAL)) {
                if (!empty($this->id_usuario) && $this->id_usuario != $array['id_responsable']
                    && ($array['id_responsable'] == $_SESSION['id_usuario']) || $array['id_user_asigna'] == $_SESSION['id_usuario']) {
                    $this->_fix_new_responsable_auditoria($array['id']);
        }   }  }
    }
}