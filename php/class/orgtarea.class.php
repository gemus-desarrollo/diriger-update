<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

if (!class_exists('Tproceso'))
    include_once "proceso.class.php";

class Torgtarea extends Tproceso {

    public $array_jefes;
    public $array_subordinados;

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tproceso::__construct($clink);
    }

    public function get_subordinados_array($use_id_user= false) {
        $this->cleanListaUser();
        $this->listar_usuarios($use_id_user);

        $result = $this->listar_grupos(true);
        while ($row = $this->clink->fetch_array($result))
            $this->push2ListaUserGroup($row['_id'], $use_id_user);

        $_user_array= $this->get_list_user();
        if (!is_null($this->array_usuarios) && !is_null($_user_array))
            $this->array_usuarios = array_merge_overwrite((array)$this->array_usuarios, $_user_array);

        if (is_null($this->array_usuarios) && !is_null($_user_array))
            $this->array_usuarios = $_user_array;

        $this->order_array($use_id_user);

        reset($this->array_usuarios);
        return $this->array_usuarios;
    }

    public function add_to_subordinados_array_from_copy($use_id_user= true) {
        $use_id_user= is_null($use_id_user) ? true : $use_id_user;
        $sql= "select distinct *, id as _id, cronos as _cronos from _ctusuarios";
        $result= $this->do_sql_show_error('add_to_subordinados_array_from_copy', $sql, false);

        while ($row = $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                    'eliminado'=>$row['eliminado'], 'usuario'=>$row['usuario'], 'cronos'=>$row['_cronos'], '_id'=>$row['_id'],
                    'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code'], 'nivel'=>$row['nivel'],
                    'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 'id_auditoria'=>$row['id_auditoria'],
                    'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'],
                    'id_tematica'=>$row['tematica'], 'id_tematica_code'=>$row['id_tematica_code']);
            $this->array_usuarios[$row['_id']]= $array;
        }

        $this->order_array($use_id_user);
    }

    private function _create_copy_tusuarios() {
        $result = $this->_create_copy_table('tusuarios');
        if ($result)
            $this->if_copy_tusuarios = true;
    }

    public function add_to_copy_tusuario($id) {
        $sql = "insert into _ctusuarios ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
        $sql.= " select * from tusuarios where id = $id";
        $this->do_sql_show_error('add_to_copy_tusuario', $sql, false);
    }

    public function add_to_copy_tusuarios_from_tsubordinados() {
        if (!$this->if_copy_tusuarios)
            $this->_create_copy_tusuarios();

        $i = 0;
        $sql = null;
        foreach ($this->array_usuarios as $array) {
            $sql.= "insert into _ctusuarios ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
            $sql.= "select * from tusuarios where id = {$array['id']}; ";

            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('add_to_copy_tusuarios_from_tsubordinados', $sql, false);
                $sql = null;
                $i = 0;
        }   }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('add_to_copy_tusuarios_from_tsubordinados', $sql, false);

        $this->error = null;
        reset($this->array_usuarios);
    }

    public function add_to_copy_tusuarios_from_proceso($id_proceso, $fix_user_process = false, $freeassign= _TO_ENTITY) {
        global $array_procesos_entity;
      
        if (!$this->if_copy_tusuarios)
            $this->_create_copy_tusuarios();

        $obj = new Tproceso($this->clink);
        if (!is_null($this->user_date_ref))
            $obj->set_user_date_ref($this->user_date_ref);

        $result = $obj->listar_usuarios_proceso($id_proceso, true, false, null, $fix_user_process);

        $i = 0;
        $sql = null;
        while ($row = $this->clink->fetch_array($result)) {
            $prs= $array_procesos_entity[$row['id_proceso']];
            if ($freeassign == _TO_ENTITY && $prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if ($freeassign == _TO_ALL_ENTITIES && ($prs['id_entity'] != $_SESSION['id_entity'] 
                && (!$prs['if_entity'] || ($prs['if_entity'] && $row['id'] != $prs['id_responsable']))))
                continue;

            $sql.= "insert into _ctusuarios ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
            $sql.= " select * from tusuarios where id = {$row['_id']}; ";

            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('add_to_copy_tusuarios_from_proceso', $sql, false);
                $sql = null;
                $i = 0;
        }   }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('add_to_copy_tusuarios_from_proceso', $sql, false);

        $this->error = null;
    }

    public function _create_copy_tprocesos() {
        if ($this->if_copy_tprocesos)
            return;
        $result = $this->_create_copy_table('tprocesos');
        if ($result)
            $this->if_copy_tprocesos = true;
    }

    public function add_to_copy_tprocesos_from_proceso($id_proceso) {
        $this->_create_copy_tprocesos();

        $sql = "insert into _ctprocesos ";
        $sql .= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
        $sql .= " select * from tprocesos where id = $id_proceso";
        $this->do_sql_show_error('add_to_copy_tusuarios_from_proceso', $sql, false);
        return $this->error;
    }

    public function listar_all_responsables_task() {
        $sql = "select distinct(id_responsable), cargo, tusuarios.nombre as _nombre from ttareas, tusuarios ";
        $sql .= "where id_responsable = tusuarios.id and (eliminado is null or eliminado >= '$this->user_date_ref') ";
        $sql .= "order by tusuarios.nombre asc";
        $result = $this->do_sql_show_error('listar_all_responsables_task', $sql);
        return $result;
    }

    public function verifyArray($id_usuario) {
        $found = false;
        $cant = count($this->array_subordinados);

        if (empty($cant))
            return false;

        for ($i = 0; $i < $cant; ++$i)
            if ($this->array_subordinados[$i][0] == $id_usuario)
                $found = true;

        return $found;
    }

    public function listar_usuarios($use_id_user = true) {
        $sql = "select distinct tusuarios.*, id_usuario as _id, nombre, tsubordinados.cronos as _cronos ";
        $sql.= "from tsubordinados, tusuarios where tusuarios.id = id_usuario ";
        $sql.= "and id_responsable = $this->id_responsable and (eliminado is null or eliminado >= '$this->user_date_ref') ";
        $sql.= " order by nombre asc";
        return $this->_list_user($sql, $use_id_user);
    }

    public function setUsuario($value = 'add') {
        if ($value == 'add') {
            $sql = "insert into tsubordinados (id_responsable, id_usuario, cronos) ";
            $sql.= "values ($this->id_responsable, $this->id_usuario, '$this->cronos')";
        } else
            $sql = "delete from tsubordinados where id_responsable = $this->id_responsable and id_usuario = $this->id_usuario ";
        return $this->_set_user($sql);
    }

    public function listar_grupos($flag= false) {
        $sql = "select *, id_grupo as _id, nombre, tsubordinados.cronos as _cronos from tsubordinados, tgrupos ";
        $sql.= "where id_responsable = $this->id_responsable and tsubordinados.id_grupo = tgrupos.id order by nombre asc ";
        return $this->_list_group($sql, $flag);
    }

    public function setGrupo($value = 'add') {
        if ($value == 'add') {
            $sql = "insert into tsubordinados (id_responsable, id_grupo, cronos) ";
            $sql.= "values ($this->id_responsable, $this->id_grupo, '$this->cronos')";
        } else
            $sql = "delete from tsubordinados where id_responsable = $this->id_responsable and id_grupo = $this->id_grupo ";
        return $this->_set_group($sql);
    }

    public function cleanObjeto() {
        $sql = "delete from tsubordinados where id_responsable = $this->id_responsable ";
        return $this->_clean_object($sql);
    }

    //buscar si id_responsable es jefe del id_usuario
    public function if_chief($id_responsable = NULL, $id_usuario = NULL) {
        if (empty($id_responsable))
            $id_responsable = $this->id_responsable;
        if (empty($id_usuario))
            $id_usuario = $this->id_usuario;

        $sql = "select tsubordinados.id_responsable as _id from tsubordinados where id_responsable = $id_responsable ";
        $sql .= "and tsubordinados.id_usuario = $id_usuario ";
        $sql .= "UNION ";
        $sql .= "select tsubordinados.id_responsable as _id from tsubordinados, tusuario_grupos ";
        $sql .= "where tsubordinados.id_grupo = tusuario_grupos.id_grupo and tusuario_grupos.id_usuario = ";
        $sql .= "$id_usuario and id_responsable = $id_responsable";
        $this->do_sql_show_error('if_chief', $sql);
        return $this->cant;
    }

// lista todos los jefes que tiene el usuario en el sistema
    public function listar_chief($id_usuario = null) {
        $id_usuario = !empty($id_usuario) ? $id_usuario : $this->id_usuario;

        if (isset($this->array_jefes))
            unset($this->array_jefes);
        $this->array_jefes = null;

        $sql = "select id_responsable, nombre, cargo, nivel, email, tusuarios.eliminado from tsubordinados, tusuarios ";
        $sql .= "where tsubordinados.id_responsable = tusuarios.id and tsubordinados.id_usuario = $id_usuario ";
        $sql .= "union ";
        $sql .= "select id_responsable, nombre, cargo, nivel, email, tusuarios.eliminado from tsubordinados, tusuario_grupos, ";
        $sql .= "tusuarios where tsubordinados.id_responsable = tusuarios.id and tsubordinados.id_grupo = tusuario_grupos.id_grupo ";
        $sql .= "and tusuario_grupos.id_usuario = $id_usuario ";
        $result = $this->do_sql_show_error('listar_chief', $sql);

        while ($row = $this->clink->fetch_array($result)) {
            if (!is_null($row['eliminado']) && ((int) strtotime($row['eliminado']) < (int) strtotime($this->user_date_ref)))
                continue;
            $array = array('id' => $row['id_responsable'], 'nombre' => $row['nombre'], 'cargo' => $row['cargo'], 
                            'nivel'=>$row['nivel'], 'email' => $row['email']);
            $this->array_jefes[$row['id_responsable']] = $array;
        }
        return $this->array_jefes;
    }

    private function find_user_ingroup($id_grupo) {
        $this->cleanListaUser();
        $this->push2ListaUserGroup($id_grupo);

        $user_array = $this->get_list_user(false);

        foreach ($user_array as $array) {
            if ($this->id_usuario == $array['id'])
                return true;
        }
        return false;
    }

    public function get_planner_array() {
        $sql = "select distinct *, id as _id, tusuarios.cronos as _cronos from tusuarios where id != $this->id_responsable ";
        $sql .= "and (tusuarios.eliminado is null or tusuarios.eliminado > '$this->user_date_ref') ";
        $sql .= "and nivel >= " . _PLANIFICADOR . " order by nombre asc ";
        $this->_list_user($sql);
    }
}
