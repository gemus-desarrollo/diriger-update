<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "base_planning.class.php";
include_once "asistencia.class.php";
include_once "register_planning.class.php";


class Tgrupo_extend extends Tbase_planning {

     public function __construct($clink= null) {
         Tbase_planning::__construct($clink);

         $this->clink= $clink;
         $this->className= "Tgrupo_extend";
         $this->array_eventos= null;
    }

    /**
     * actualizar la pertenecia a los eventos
     *
     */
    public function get_array_eventos() {
        $date= date('Y').'-'.date('m').'-01 00:00:00';
        $obj_reg= new Tregister_planning($this->clink);

        if (isset($this->array_eventos)) {
            unset($this->array_eventos);
            $this->array_eventos= null;
        }

        $sql= "select id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
        $sql.= "id_auditoria_code, id_tematica, id_tematica_code, $this->year as _year ";
        $sql.= "from tusuario_eventos_$this->year ";
        $sql.= "where id_usuario is null and id_evento is not null and id_grupo = $this->id_grupo ";

        $year= $this->year + 1;
        if ($this->clink->if_table_exist("tusuario_eventos_$year")) {
            $sql.= "union ";
            $sql.= "select id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
            $sql.= "id_auditoria_code, id_tematica, id_tematica_code, $year as _year ";
            $sql.= "from tusuario_eventos_$year ";
            $sql.= "where id_usuario is null and id_evento is not null and id_grupo = $this->id_grupo ";
        }
        $result= $this->do_sql_show_error('get_array_eventos', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $_row= $obj_reg->_set($row['id_evento']);
            if (strtotime($_row['fecha_inicio_plan']) < strtotime(date('Y').'-01-01 00:00:00'))
                continue;

            if (strtotime($_row['fecha_inicio_plan']) < strtotime($date)) {
                $row_cump= $obj_reg->get_last_reg($_row['_id'], $_row['_id_responsable']);
                if ($row_cump['cumplimiento'] == _COMPLETADO)
                    continue;
            }

            $array= array('year'=>$row['_year'], 'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'],
                    'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'], 'id_auditoria'=>$row['id_auditoria'],
                    'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tematica'=>$row['id_tematica'],
                    'id_tematica_code'=>$row['id_tematica_code'], 'id_tipo_reunion'=>$_row['id_tipo_reunion'],
                    'id_tipo_reunio_code'=>$_row['id_tipo_reunion_code']);
            $this->array_eventos[]= $array;
        }

        return $i;
    }

    public function update_usuario_eventos($action) {
        if (is_null($this->array_eventos))
            return;

        $obj_reg= new Tregister_planning($this->clink);
        $this->copy_in_object($obj_reg);

        reset($this->array_eventos);
        foreach ($this->array_eventos as $row) {
            $obj_reg->SetYear($row['year']);

            $obj_reg->SetIdEvento($row['id_evento']);
            $obj_reg->set_id_evento_code($row['id_evento_code']);
            $obj_reg->set_id_code($row['id_evento_code']);

            $obj_reg->SetIdTarea($row['id_tarea']);
            $obj_reg->set_id_tarea_code($row['id_tarea_code']);

            $obj_reg->SetIdAuditoria($row['id_auditoria']);
            $obj_reg->set_id_auditoria_code($row['id_auditoria_code']);

            $obj_reg->SetIdTematica($row['id_tematica']);
            $obj_reg->set_id_tematica_code($row['id_tematica_code']);

            $obj_reg->SetFechaInicioPlan(null);

            $obj_reg->update_reg($action, null, $this->id_usuario);
        }

        $obj_assist= new Tasistencia($this->clink);
        $this->copy_in_object($obj_assist);
        $obj_assist->SetIdProceso($_SESSION['id_entity']);
        $obj_assist->set_id_proceso_code($_SESSION['id_entity_code']);

        reset($this->array_eventos);
        foreach ($this->array_eventos as $row) {
            if (empty($row['id_tipo_reunion']))
                continue;

            $obj_assist->SetIdEvento($row['id_evento']);
            $obj_assist->set_id_evento_code($row['id_evento_code']);

            if ($action == 'add')
                $obj_assist->add();
            if ($action == 'delete')
                $obj_assist->eliminar();
        }
    }

    private function _update_usuario_add($if_jefe) {
        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdUsuario($this->id_usuario);

        $sql= "select distinct tprocesos.*, tprocesos.id as _id_proceso from tusuario_procesos, tprocesos ";
        $sql.= "where tusuario_procesos.id_grupo = $this->id_grupo and tprocesos.id = tusuario_procesos.id_proceso ";
        $sql.= "order by tprocesos.tipo desc";
        $result= $this->do_sql_show_error('_update_usuario_add', $sql);

        $array_procesos= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array_procesos[$row['_id_proceso']]= $row; 
        } 

        foreach ($array_procesos as $prs) {
            $obj_user->SetIdProceso($prs['id']);
            $obj_user->set_id_proceso_code($prs['id_code']);

            if ($prs['tipo'] <= _TIPO_DEPARTAMENTO && !$if_jefe) {
                $obj_user->update_proceso();
                break;
            }
        }
    }

    private function _update_usuario_delete($id_proceso) {
        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdUsuario($this->id_usuario);

        $sql= "select * from tusuario_procesos where id_proceso = $id_proceso ";
        $sql.= "and (id_grupo = $this->id_grupo or id_usuario = $this->id_usuario) ";
        $result= $this->do_sql_show_error('_update_usuario_delete', $sql);
        $cant= $this->clink->num_rows($result);
        if (empty($cant))
            return;

        $found= false;
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($row['id_usuario'])) {
                $found= true;
                break;
            }   
        }

        if (!$found) {
            $obj_user->update_proceso($_SESSION['id_entity'], $_SESSION['id_entity_code']);
        }
    }

    public function update_usuario_procesos($action = 'add') {
        global $array_procesos_entity;

        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdUsuario($this->id_usuario);
        $obj_user->Set();
        $id_proceso= $obj_user->GetIdProceso();
        if ($array_procesos_entity[$id_proceso]['id_entity'] != $_SESSION['id_entity'])
            return;

        $if_jefe= false;
        if (!empty($obj_user->GetIdProceso_jefe()) 
            && $array_procesos_entity[$obj_user->GetIdProceso_jefe()]['tipo'] <= _TIPO_DEPARTAMENTO)
            $if_jefe= true;

        if ($action == 'add')    
            $this->_update_usuario_add($if_jefe);
        if ($action == 'delete')
            $this->_update_usuario_delete($id_proceso);
    }

}

?>