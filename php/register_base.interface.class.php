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

require_once "interface.class.php";

require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/peso.class.php";

require_once "class/tmp_tables_planning.class.php";
require_once "class/planning.class.php";
require_once "class/tipo_evento.class.php";
require_once "class/evento.class.php";
require_once "class/auditoria.class.php";
require_once "class/tarea.class.php";

require_once "class/mail.class.php";
require_once "class/code.class.php";
require_once "class/badger.class.php";

class TRegisterBase extends TplanningInterface {
  
    protected $chronos_origen; // para el uso con kanban
    
    protected $month,
              $year;

    protected $extend;
    protected $print_reject;
    protected $rechazado;
    protected $aprobado;

    protected $if_fixed;

    protected $to_year;
    protected $copy_all;
    protected $nums_id_show;
    protected $array_id_show;
  
    protected $obj_event;
    protected $obj_doc;
    protected $obj_mail;
    protected $obj_reg;
    protected $obj_matter;

    protected $id_responsable_ref;
    protected $radio_toshow,
              $go_delete;

    protected $toshow_plan;
    
    protected $array_procesos_entity,
              $array_procesos_down;

    protected $array_usuarios_entity,
              $array_usuarios_down;

    protected $ifmeeting, 
            $_ifmeeting;

    public $array_usuarios,
           $array_grupos;
    public $array_reg_usuarios,
           $array_reg_procesos;

    protected $array_responsable_prs;

    protected $id_user_asigna;

    public $array_proyectos;
    public $array_notas;
    public $array_riesgos;
    protected $array_accords;

    public function __construct($clink= null) {
        $this->clink = $clink;
        TplanningInterface::__construct($clink);

        $this->radio_prs= !empty($_POST['_radio_prs']) ? $_POST['_radio_prs'] : 0;
        $this->radio_date= !empty($_POST['_radio_date']) ? $_POST['_radio_date'] : 0;
        $this->radio_user= !empty($_POST['_radio_user']) ? $_POST['_radio_user'] : 0;

        $this->radio_toshow= !empty($_POST['_radio_toshow']) ? $_POST['_radio_toshow'] : 0;

        $this->to_year= !empty($_POST['to_year']) ? (int)$_POST['to_year'] : 0;
        $this->copy_all= !is_null($_POST['copy_all']) ? $_POST['copy_all'] : $_GET['copy_all'];
        $this->nums_id_show= $_POST['nums_id_show'];

        $this->array_id_show= $_POST['array_id_show'];
        $this->array_id_show= !empty($this->nums_id_show) ? explode(',', $this->array_id_show) : null;

        $this->extend= $_POST['extend'];
        $this->print_reject= !empty($_POST['print_reject']) ? $_POST['print_reject'] : $_GET['print_reject'];
        $this->array_eventos= array();

        $this->tipo_plan= !empty($_POST['tipo_plan']) ? $_POST['tipo_plan'] : $_GET['tipo_plan'];
        $this->signal= !empty($_POST['signal']) ? $_POST['signal'] : $_GET['signal'];

        $this->radio_toshow= $this->signal == 'anual_plan' ? 1 : $this->radio_toshow;
    }

    protected function init_entity() {
        $this->array_usuarios_entity= array();
        $this->array_procesos_entity= array();

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->listar_procesos_entity();
        $this->array_procesos_entity= $obj_prs->array_procesos_entity;

        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdEntity($_SESSION['id_entity']);
        $obj_user->set_id_entity_code($_SESSION['id_entity_code']);
        $obj_user->SetYear($this->year);
        $obj_user->set_use_copy_tusuarios(false);

        $obj_user->listar(false, null, null, null, null, null, true);

        foreach ($obj_user->array_usuarios as $id => $array) {
            if (!$obj_prs->test_if_proceso_in_entity($array['id_proceso']))
                continue;
            $this->array_usuarios_entity[$id]= $array;
        }
    }

    public function init_cascade_down() {
        $id_proceso= !empty($this->id_proceso) ? $this->id_proceso : $_SESSION['id_entity'];
        if ($this->signal == 'calendar')
            $id_proceso= $_SESSION['id_entity'];

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $obj_prs->SetIdEntity(null);
        $obj_prs->SetIdResponsable(null);

        $this->array_procesos_down= $obj_prs->get_procesos_down(null, null, null, true);

        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdEntity(null);
        $obj_user->set_id_entity_code(null);
        $obj_user->SetYear($this->year);
        $obj_user->set_use_copy_tusuarios(false);

        $obj_user->listar(false, null, null, null, null, null, true);

        foreach ($obj_user->array_usuarios as $id => $user) {
            if (!array_key_exists($user['id_proceso'], $this->array_procesos_down))
                continue;
            $this->array_usuarios_down[$id]= $user;
        }
    }

    protected function init_mail($id_usuario= null) {
        $obj_user= new Tusuario($this->clink);
        $this->obj_mail= new Tmail;

        $id_usuario= !empty($id_usuario) ? $id_usuario : $this->id_responsable;
        $email= $obj_user->GetEmail($id_usuario);
        $this->obj_mail->_AddReplayTo($email['email'], $email['nombre']);
        $this->obj_mail->responsable = $email['nombre'];
        $this->obj_mail->cargo = $email['cargo'];

        unset($obj_user);
    }

    protected function _init_prs() {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso($this->id_proceso);
        $obj_prs->Set();

        $this->array_proceso= array();
        $array= array('id'=>$this->id_proceso, 'id_code'=>$obj_prs->get_id_code(), 'id_responsable'=>$obj_prs->GetIdResponsable());
        $this->array_procesos[$this->id_proceso]= $array;

        if ($this->radio_prs)
            $this->array_procesos= $obj_prs->listar_in_order('eq_desc', true, _TIPO_ARC, true, 'desc');
    }

    protected function init_register_usuarios() {
        global $badger;

        $obj_tables= new Ttmp_tables_planning($this->clink);
        $obj_tables->if_copy_tusuarios= $badger->if_copy_tusuarios;
        $obj_tables->set_use_copy_tusuarios(false);
        $obj_tables->SetYear($this->year);
        $obj_tables->SetIdUsuario(null);

        $obj_tables->SetIdTarea($this->id_tarea);
        $obj_tables->SetIdEvento($this->id_evento);
        $obj_tables->SetIdAuditoria($this->id_auditoria);

        $this->array_reg_usuarios= $obj_tables->get_array_reg_usuarios();
    }

    protected function init_register_procesos() {
        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);

        if($this->className == "Ttarea") {
            $obj_prs->GetProcesoTarea($this->id_tarea);
        }
        if ($this->className == "Tevento") {
            $obj_prs->GetProcesoEvento($this->id_evento);
        }
        if ($this->className == "Tauditoria") {
            $obj_prs->SetIdEvento($this->id_evento);
            $obj_prs->GetProcesoAuditoria($this->id_auditoria);
        }
        $this->array_reg_procesos= $obj_prs->array_procesos;

        foreach ($this->array_reg_procesos as $prs) {
            $this->array_responsable_prs[$prs['id']]= $prs['id_responsable'];
        }

        reset($this->array_reg_procesos);
        unset($obj_prs);
    }
    
    protected function get_evento_origen() {
        $obj_task= new Ttarea($this->clink);
        $obj_task->SetYear($this->year);

        $obj_task->get_riesgos(null, null, true);
        $obj_task->get_notas(null, null, true);
        $obj_task->get_proyectos(null, true);

        $this->array_riesgos= array_merge_overwrite($obj_task->array_riesgos, $this->array_riesgos);
        $this->array_notas= array_merge_overwrite($obj_task->array_notas, $this->array_notas);
        $this->array_proyectos= array_merge_overwrite($obj_task->array_proyectos, $this->array_proyectos);

        $obj_matter= new Ttematica($this->clink);
        $obj_matter->SetYear($this->year);

        $obj_matter->get_array_eventos_accords();
        $this->array_accords= array_merge_overwrite($obj_matter->array_accords, $this->array_accords);
    }

    protected function if_project_origin($id_tarea) {
        if (empty($id_tarea))
            return false;

        reset($this->array_proyectos);
        foreach ($this->array_proyectos as $row) {
            if ($row['id_tarea'] == $id_tarea)
                return true;
        }
        return false;
    }

    protected function if_nota_origin($id_tarea) {
        if (empty($id_tarea))
            return false;

        reset($this->array_notas);
        foreach ($this->array_notas as $row) {
            if ($row['id_tarea'] == $id_tarea)
                return true;
        }
        return false;
    }
     
    protected function if_riesgo_origin($id_tarea) {
        if (empty($id_tarea))
            return false;

        reset($this->array_riesgos);
        foreach ($this->array_riesgos as $row) {
            if ($row['id_tarea'] == $id_tarea)
                return true;
        }
        return false;
    } 
    
    protected function if_accords_origin($id) {
        if (empty($id))
            return false;

        reset($this->array_accords);
        foreach ($this->array_accords as $row) {
            if ($row['id_evento'] == $id)
                return true;
        }        
        return false;
    }
}