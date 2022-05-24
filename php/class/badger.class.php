<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 12/01/15
 * Time: 13:25
 */
include_once "base.class.php";
include_once "proceso.class.php";
include_once "orgtarea.class.php";
include_once "usuario.class.php";

global $clink;
global $config;


class Tbadger extends Tbase{
    public $obj_sub;
    public $obj_prs;

    public $array_procesos_down;
    public $array_procesos;
    public $tr_display;
    protected $user_date_ref;
    public $if_usuario_jefe;
    public $freeassign;
    public $acc;

    public $array_usuarios_sub;

    /**
     * 0 ==> en ctprocesos solo existen procesos internos
     */
    public $other_type_prs;


    public function __construct() {
        global $config;
        global $clink;

        Tbase::__construct($clink);

        $this->tr_display= 'none';
        $this->if_usuario_jefe= false;
        $this->array_procesos= null;
        if ($_SESSION['nivel'] == _GLOBALUSUARIO || ($this->acc == 3 && $_SESSION['id_entity'] == $_SESSION['local_proceso_id']))
            $this->freeassign= _TO_ALL_ENTITIES;
        else    
            $this->freeassign= ($config->freeassign || $_SESSION['freeassign']) ? _TO_ENTITY : $config->freeassign;
        $this->freeassign= max($this->freeassign, $_SESSION['freeassign']);
        $config->freeassign= $this->freeassign;
        $this->acc= null;
    }

    public function set_planwork() {
        $this->acc = $_SESSION['acc_planwork'];
        $this->execute(_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL);
    }
    public function set_planrisk() {
        $this->acc = $_SESSION['acc_planrisk'];
        $this->execute(_PLAN_TIPO_PREVENCION);
    }
    public function set_planaudit() {
        $this->acc = $_SESSION['acc_planaudit'];
        $this->execute(_PLAN_TIPO_AUDITORIA);
    }
    public function set_planheal() {
        $this->acc = $_SESSION['acc_planheal'];
        $this->execute(_PLAN_TIPO_MEDIDAS);
    }
    public function set_planproject() {
        $this->acc = $_SESSION['acc_planproject'];
        $this->execute(_PLAN_TIPO_PROYECTO);
    }

    public function set_planarchive() {
        $this->acc = $_SESSION['acc_archive'];
        $this->execute(_PLAN_TIPO_INFORMATIVO);
    }

    private function Set() {
        if (!$this->obj_sub) {
            $this->obj_sub= new Torgtarea($this->clink);
            $this->obj_sub->SetYear($this->year);
            $this->obj_sub->set_user_date_ref($this->user_date_ref);
        }

        if (!$this->obj_prs) {
            $this->obj_prs= new Tproceso($this->clink);
            $this->obj_prs->SetYear($this->year);
            $this->obj_prs->set_user_date_ref($this->user_date_ref);

            $this->obj_prs->SetIdUsuario($_SESSION['id_usuario']);

            if ($this->freeassign < _TO_ENTITY) {
                $this->obj_prs->SetIdProceso($_SESSION['usuario_proceso_id']);
                $this->obj_prs->SetTipo(null);   
            }
            if ($this->freeassign == _TO_ENTITY) {
                $this->obj_prs->SetIdProceso($_SESSION['id_entity']);
                $this->obj_prs->SetTipo($_SESSION['entity_tipo']);                
            } 
            if ($this->freeassign == _TO_ALL_ENTITIES) {
                $this->obj_prs->SetIdProceso(null);
                $this->obj_prs->SetTipo(null);                   
            }
        }
    }

    private function execute($type= null) {
        global $config;
        
        if ($_SESSION['nivel'] == _GLOBALUSUARIO || ($this->acc == 3 && $_SESSION['id_entity'] == $_SESSION['local_proceso_id']))
            $this->freeassign= _TO_ALL_ENTITIES;
        else    
            $this->freeassign= ($this->freeassign || $this->acc > 1) ? _TO_ENTITY : $config->freeassign;
        $this->freeassign= max($this->freeassign, $_SESSION['freeassign']);
        $config->freeassign= $this->freeassign;    

        $this->Set();

        $this->set_tusuarios($type);
        if ($_SESSION['nivel'] >= _SUPERUSUARIO)
            return null;

        $this->set_tprocesos($type);
        reset($this->array_procesos);
        reset($this->array_procesos_down);
    }

    /**
     * crea la tabla temporal _ctusuarios con los usuarios pertenecientes a los procesos del cual es usuario de sesion es responsable
     * y con todos sus subordinados
     * @return null
     */
    public function set_tusuarios($type= null) {
        global $array_procesos_entity;

        $this->Set();

        if (is_null($type) || ($type != _PLAN_TIPO_AUDITORIA && $type != _PLAN_TIPO_PREVENCION && $type != _PLAN_TIPO_MEDIDAS)) {
            $exclude_prs_type= array();
            $exclude_prs_type[_TIPO_PROCESO_INTERNO]= 1;
            $exclude_prs_type[_TIPO_ARC]= 1;
        } else {
            $exclude_prs_type= null;
        }

        // construyendo los subordinados a partir de los procesos de los que es responsable y de los otros procesos ---------
        if ($_SESSION['nivel'] == _GLOBALUSUARIO) {
            $this->if_usuario_jefe= true;
            $this->tr_display= 'table-row';
            $this->other_type_prs= 1;
            return;
        }
        if ($_SESSION['nivel'] == _SUPERUSUARIO || $this->acc == 3) {
            $this->if_usuario_jefe= true;
            $this->array_procesos_down= $this->obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], null);
        }   
        if ($_SESSION['nivel'] < _SUPERUSUARIO && $this->acc != 3) {
            $array_procesos_down= $this->obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null, null, $exclude_prs_type);
            $this->if_usuario_jefe= count($this->array_procesos_down) > 0 ?  true : false;

            if (!empty($this->acc)) {
                $array= array('id'=>$_SESSION['usuario_proceso_id'], 'id_code'=>$_SESSION['usuario_proceso_id_code']);
                $array_procesos_down[$_SESSION['usuario_proceso_id']]= $array;
            }
            if (count($array_procesos_down))
                $this->array_procesos_down= $this->obj_prs->get_procesos_down_cascade(null, null, null, $array_procesos_down);
        }

        if ($this->if_usuario_jefe && $_SESSION['nivel'] >= _PLANIFICADOR)
            $this->tr_display= 'table-row';

        foreach ($this->array_procesos_down as $array) {
            $prs= $array_procesos_entity[$array['id']];
            if ($this->freeassign == _TO_ENTITY && $prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if ($this->freeassign == _TO_ALL_ENTITIES && ($prs['id_entity'] != $_SESSION['id_entity']) && !$prs['if_entity'])
                continue;
            $this->obj_sub->add_to_copy_tusuarios_from_proceso($array['id'], true, $this->freeassign);
        }

        $this->obj_sub->set_use_copy_tusuarios(false);
        $this->obj_sub->set_use_copy_tprocesos(false);
        $this->obj_sub->SetIdUsuario(null);
        $this->obj_sub->SetIdProceso(null);
        $this->obj_sub->SetIdResponsable($_SESSION['id_usuario']);
        $this->obj_sub->set_user_date_ref($this->user_date_ref);

        $this->obj_sub->get_subordinados_array(true);
        foreach ($this->obj_sub->array_usuarios as $user) 
            $this->array_usuarios_sub[$user['id']]= $user;

        reset($this->obj_sub->array_usuarios);
        $this->obj_sub->add_to_copy_tusuarios_from_tsubordinados();
        //----------------------------------------------------------------------------------------------------------------------------

        // Si no hay libre asignacion de tareas asignar a los mismos procesos en los que participa el usuario
        if (!$this->freeassign && (!empty($this->acc) && $this->acc != 3)) {
            if (!array_key_exists($_SESSION['usuario_proceso_id'], $this->array_procesos_down)) {
                    $this->obj_sub->add_to_copy_tusuarios_from_proceso($_SESSION['usuario_proceso_id']);
            }
        }

        if ($this->freeassign >= _TO_ENTITY && !array_key_exists($_SESSION['id_entity'], $this->array_procesos_down)) {
            $this->obj_sub->add_to_copy_tusuarios_from_proceso($_SESSION['id_entity']);
        }        

        //agregando al propio usuario a la lista. Se pueda poner tarea a si mismo ------------------------------------
        $this->if_copy_tusuarios= $this->obj_sub->if_copy_tusuarios;
        $this->obj_sub->add_to_copy_tusuario($_SESSION['id_usuario']);

        if (!empty($this->acc))
            $this->obj_sub->add_to_subordinados_array_from_copy(true);

        if ($_SESSION['nivel'] >= _SUPERUSUARIO || $this->acc == 3) {
            $this->tr_display= 'table-row';
            $this->other_type_prs= 1;
        }
    }

    /*
     * Agregar los procesos
     */
    public function set_tprocesos($type= null) {
        global $config;
        global $array_procesos_entity;

        $this->Set();

        if ($_SESSION['nivel'] == _GLOBALUSUARIO) {
            $this->tr_display= 'table-row';
            $this->other_type_prs= 1;
            return;
        }

        if ($_SESSION['nivel'] == _SUPERUSUARIO || $this->acc == 3) {
            $this->tr_display= 'table-row';
            $this->other_type_prs= 1;
        }

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
        $this->array_procesos= $obj_prs->get_procesos_by_user();

        $this->obj_sub->_create_copy_tprocesos();
        $this->if_copy_tprocesos= true;
        $this->other_type_prs= 0;

        if (is_null($type) || ($type != _PLAN_TIPO_AUDITORIA && $type != _PLAN_TIPO_PREVENCION 
                                && $type != _PLAN_TIPO_MEDIDAS && $type != _PLAN_TIPO_INFORMATIVO)) {
            $exclude_prs_type= array();
            if (!$config->show_prs_plan)
                $exclude_prs_type[_TIPO_PROCESO_INTERNO]= 1;
                $exclude_prs_type[_TIPO_ARC]= 1;
        } else {
            $exclude_prs_type= null;
        }

        if (!empty($this->acc)) {
            $this->obj_prs->SetIdUsuario(null);
            $this->obj_prs->set_acc($this->acc);

            if ($this->acc == 1 || $this->acc == 2)
              $this->array_procesos_down= $this->obj_prs->get_procesos_down_cascade(null, $_SESSION['usuario_proceso_id'], null, null);


            if ($this->acc == 3 || $this->acc == 2) {
                $id_entity= $this->freeassign == _TO_ALL_ENTITIES ? $_SESSION['local_proceso_id'] : $_SESSION['id_entity'];
                if ($this->acc == 3 || ($this->acc == 2 && $_SESSION['usuario_proceso_id'] == $id_entity))
                    $this->array_procesos_down= $this->obj_prs->get_procesos_down_cascade(null, $id_entity, null, null);
                $this->array_procesos_down[$_SESSION['id_entity']] = array('id'=>$_SESSION['id_entity'], 'tipo'=>$_SESSION['entity_tipo']);
            }   
        }

        if (empty($this->acc)) {
            if (is_null($type) || ($type != _PLAN_TIPO_AUDITORIA && $type != _PLAN_TIPO_PREVENCION 
                                    && $type != _PLAN_TIPO_MEDIDAS && $type != _PLAN_TIPO_INFORMATIVO)) {
                $exclude_prs_type= array();
                $exclude_prs_type[_TIPO_PROCESO_INTERNO]= 1;
                $exclude_prs_type[_TIPO_ARC]= 1;
            } else {
                $exclude_prs_type= null;
            }

            $this->array_procesos_down= $this->obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null, null, $exclude_prs_type);
        }

        // agregar todos los procesos de los que el usuario es responsable y los procesos subordinados a este
        $i= 0;
        if ($this->if_usuario_jefe || !empty($this->acc)) {
            reset($this->array_procesos_down);
            foreach ($this->array_procesos_down as $array)  {
                if ($array['id'] == $_SESSION['id_entity']
                        && (empty($this->acc) || ($this->acc == 1 && $_SESSION['usuario_proceso_id'] != $_SESSION['id_entity'])))
                    continue;

                if (!empty($this->acc)) {
                    if ($this->freeassign == _TO_ENTITY && $array_procesos_entity[$array['id']]['id_entity'] != $_SESSION['id_entity']) 
                        continue;
                    if ($this->freeassign == _TO_ALL_ENTITIES 
                        && ($array_procesos_entity[$array['id']]['id_entity'] != $_SESSION['id_entity'] 
                            && $array_procesos_entity[$array['id']]['id_proceso'] != $_SESSION['id_entity']))
                        continue;
                }

                if ($array['tipo'] < _TIPO_PROCESO_INTERNO)
                    ++$this->other_type_prs;

                ++$i;
                $this->obj_sub->add_to_copy_tprocesos_from_proceso($array['id']);
            }
        }

        //------------------------------------------------------------------------------------------------------------

        if (($this->if_usuario_jefe && $_SESSION['nivel'] >= _PLANIFICADOR) || !empty($this->acc))
            $this->tr_display= 'table-row';

        // agregar los procesos desde la lista de procesos de los que participa el usuario
        $i= 0;
        reset($this->array_procesos);
        foreach ($this->array_procesos as $array) {
            if ($array['id'] != $_SESSION['id_entity'] || ($array['id'] == $_SESSION['id_entity'] && $this->acc != 2)) {

                if ($array['id'] != $_SESSION['usuario_proceso_id']) {
                    if ($array['id'] == $_SESSION['local_proceso_id'] && (empty($this->acc) || $this->acc == 1))
                        continue;

                    if (!is_null($this->array_procesos_down))
                        if ($array['id'] != $_SESSION['local_proceso_id'] && (empty($this->acc) && !array_key_exists($array['id'], $this->array_procesos_down)))
                            continue;
                }

                if ($array['id'] == $_SESSION['usuario_proceso_id'] && empty($this->acc))
                    continue;
                if ($array['tipo'] < _TIPO_PROCESO_INTERNO)
                    ++$this->other_type_prs;

                ++$i;
                $this->obj_sub->add_to_copy_tprocesos_from_proceso($array['id']);
            }
        }

        if ($this->acc == 2 || ($this->acc == 1 && $_SESSION['usuario_proceso_id'] == $_SESSION['id_entity']))
            $this->obj_sub->add_to_copy_tprocesos_from_proceso($_SESSION['id_entity']);

        if (empty($this->other_type_prs) && $_SESSION['usuario_proceso_id'] != $_SESSION['id_entity'])
            $this->obj_sub->add_to_copy_tprocesos_from_proceso($_SESSION['usuario_proceso_id']);
    }
}

global $array_procesos_entity;
global $array_procesos_down_entity;
global $string_procesos_down_entity;
    
function set_procesos_array() {
    global $clink;
    global $year;
    global $array_procesos_entity;

    $obj_prs= new Tproceso($clink);
    $obj_prs->SetYear($year);
    $obj_prs->SetIdEntity(null);
    $array_procesos_entity= $obj_prs->listar(false);

    foreach ($array_procesos_entity as $row) {
        $id_prs= $row['id'];
        $id_entity= $array_procesos_entity[$id_prs]['id_entity'];
        $array_procesos_entity[$id_prs]['id_entity']= !empty($id_entity) ? $id_entity : $id_prs; 
        $array_procesos_entity[$id_prs]['id_entity_code']= !empty($id_entity) ? $array_procesos_entity[$id_prs]['id_entity_code'] : $row['id_code']; 
    }
    reset($array_procesos_entity);
}

function set_array_procesos_down() { 
    global $clink;
    global $year;
    global $array_procesos_down_entity;
    global $string_procesos_down_entity;
    
    $array_procesos_down_entity= array();
    $string_procesos_down_entity= null;

    $obj_prs= new Tproceso($clink);
    $obj_prs->SetYear($year);
    $obj_prs->SetIdProceso($_SESSION['id_entity']);
    $obj_prs->SetTipo($_SESSION['entity_tipo']);

    $obj_prs->listar_in_order('eq_desc', true);

    foreach ($obj_prs->array_procesos as $prs) {
        if ($_SESSION['entity_tipo'] <= _TIPO_GAE 
            && ((!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity']) 
                || (empty($prs['id_entity']) && $prs['id'] != $_SESSION['id_entity'])))
            continue;
        if ($_SESSION['entity_tipo'] >= _TIPO_EMPRESA 
            && (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity']))
            continue;
        $array_procesos_down_entity[$prs['id']]= $prs;
    }

    $i= 0;
    foreach ($array_procesos_down_entity as $prs) {
        ++$i;
        if ($i > 1)
            $string_procesos_down_entity.= ",";
        $string_procesos_down_entity.= $prs['id'];
    }

    reset($array_procesos_down_entity);
}

set_procesos_array();
set_array_procesos_down();

if (isset($clink))
    $clink->free_result();
?>