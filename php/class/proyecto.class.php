<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tbase_planning'))
    include_once "base_planning.class.php";

class Tproyecto extends Tbase_planning {

    protected $codigo;

    public function GetCodigo() {
        return $this->codigo;
    }

    public function SetCodigo($id) {
        $this->codigo = $id;
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase_planning::__construct($clink);
        $this->id_proyecto = 0;
        $this->nums_proyecto = 0;
    }

    public function Set($id = null) {
        if (!empty($id))
            $this->id_proyecto = $id;

        $sql = "select * from tproyectos where id = $this->id_proyecto ";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id_code = $row['id_code'];
            $this->id_proyecto_code = $this->id_code;

            $this->nombre = stripslashes($row['nombre']);
            $this->codigo = stripslashes($row['codigo']);

            $this->descripcion = stripslashes($row['descripcion']);
            $this->id_responsable = $row['id_responsable'];

            $this->id_proceso = $row['id_proceso'];
            $this->id_proceso_code = $row['id_proceso_code'];
            $this->origen_data = $row['origen_data'];

            $this->fecha_inicio_plan = $row['fecha_inicio_plan'];
            $this->fecha_fin_plan = $row['fecha_fin_plan'];
            $this->fecha_inicio_real = $row['fecha_inicio_real'];
            $this->fecha_fin_real = $row['fecha_fin_real'];

            $this->id_user_asigna = $row['id_usuario'];
        }

        if (!is_null($this->error))
            return $this->error;

        $sql = "select * from tproceso_proyectos where id_proyecto = $this->id_proyecto and id_proceso = $this->id_proceso ";
        if (!empty($this->year))
            $sql .= "and year = $this->year ";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id_programa = $row['id_programa'];
            $this->id_programa_code = $row['id_programa_code'];
        }

        return $this->error;
    }

    public function set_by_id_tarea($id_tarea) {
        $sql = "select distinct tproyectos.*, tusuarios.nombre as responsable from tproyectos, ttareas, tusuarios ";
        $sql .= "where ttareas.id_proyecto = tproyectos.id and tproyectos.id_responsable = tusuarios.id and ttareas.id = $id_tarea";
        $result = $this->do_sql_show_error('set_by_id_tarea', $sql);

        $num_rows = $this->clink->num_rows($result);
        if (empty($num_rows))
            return null;

        $row = $this->clink->fetch_array($result);
        $array = array('id' => $row['id'], 'id_code' => $row['id_code'], 'nombre' => $row['nombre'],
                    'responsable' => $row['responsable'], 'id_responsable' => $row['id_responsable']);

        return $array;
    }

    public function add() {
        $codigo = setNULL_str($this->codigo);
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "insert into tproyectos (codigo, nombre, id_responsable, id_proceso, id_proceso_code, fecha_inicio_plan, ";
        $sql .= "fecha_fin_plan, descripcion, id_usuario, cronos, situs) values ($codigo, $nombre, $this->id_responsable, ";
        $sql .= "{$_SESSION['id_entity']}, '{$_SESSION['id_entity_code']}', '$this->fecha_inicio_plan', '$this->fecha_fin_plan', ";
        $sql .= "$descripcion, $this->id_usuario, '$this->cronos', '$this->location') ";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_proyecto = $this->clink->inserted_id("tproyectos");
            $this->id = $this->id_proyecto;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tproyectos', 'id', 'id_code');
            $this->id_code = $this->obj_code->get_id_code();
            $this->id_proyecto_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "update tproyectos set nombre= $nombre, descripcion= $descripcion, id_responsable= $this->id_responsable, ";
        $sql .= "id_usuario= $this->id_usuario, codigo= '$this->codigo', fecha_inicio_plan= '$this->fecha_inicio_plan', ";
        $sql .= "fecha_fin_plan= '$this->fecha_fin_plan', cronos= '$this->cronos', situs= '$this->location' ";
        $sql .= "where id = $this->id_proyecto ";
        $result= $this->do_sql_show_error('update', $sql);

        return $this->error;
    }

    public function listar($filter = 1, $flag = true) {
        $filter = !is_null($filter) ? $filter : 1;

        if (!empty($this->year) && $filter) {
            $this->date_interval($fecha_inicio, $fecha_fin);
            $fecha_inicio= "{$this->year}-01-01";
        }

        $fecha_fin = !empty($this->fecha_fin_plan) ? $this->fecha_fin_plan : $fecha_fin;
        $fecha_inicio = !empty($this->fecha_inicio_plan) ? $this->fecha_inicio_plan : $fecha_inicio;

        $sql = "select DISTINCT tproyectos.*, tproyectos.id as _id, tproyectos.id_code as _id_code, tproyectos.nombre as _nombre, ";
        $sql.= "fecha_inicio_plan, fecha_fin_plan, fecha_inicio_real, fecha_fin_real, descripcion, id_programa, id_programa_code, ";
        $sql.= "tproyectos.id_responsable as _id_responsable, tproyectos.id_proceso as _id_proceso, ";
        $sql.= "tproyectos.id_proceso_code as _id_proceso_code ";
        $sql.= "from tproyectos, tproceso_proyectos where tproyectos.id = tproceso_proyectos.id_proyecto ";
        if (!empty($this->id_proceso))
            $sql .= "and tproceso_proyectos.id_proceso = $this->id_proceso ";
        if (!empty($this->id_programa) && $this->id_programa != 1)
            $sql .= "and tproceso_proyectos.id_programa = $this->id_programa ";
        if (!empty($this->id_responsable) && empty($this->id_usuario))
            $sql .= "and id_responsable = $this->id_responsable ";
        if (!empty($this->year) && $filter) {
            $sql .= "and (fecha_fin_real is null ";
            $sql .= "or (((date(fecha_inicio_plan) >= '$fecha_inicio' and  date(fecha_inicio_plan) <= '$fecha_fin') or ";
            $sql .= "(date(fecha_fin_plan) >= '$fecha_inicio' and date(fecha_fin_plan) <= '$fecha_fin'))) ";
            $sql .= "or ((date(fecha_inicio_real) >= '$fecha_inicio' and  date(fecha_inicio_real) <= '$fecha_fin') or ";
            $sql .= "(date(fecha_fin_real) >= '$fecha_inicio' and date(fecha_fin_real) <= '$fecha_fin'))) ";
        }
        $sql .= "order by ";
        if ($filter)
            $sql .= "fecha_inicio_plan desc, ";
        $sql .= "tproyectos.nombre asc  ";

        $result = $this->do_sql_show_error('listar', $sql);

        if ($flag)
            return $result;

        $i = 0;
        if (isset($this->array_proyectos))
            unset($this->array_proyectos);
        $this->array_proyectos = array();

        while ($row = $this->clink->fetch_array($result)) {
            if (!empty($row['_id'])) {
                $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'], 'id_responsable' => $row['id_responsable'], 'id_proceso' => $row['_id_proceso'],
                        'fecha_inicio_plan' => $row['fecha_inicio_plan'], 'fecha_fin_plan' => $row['fecha_fin_plan']);
                $this->array_proyectos[$row['_id']] = $array;
            }
            ++$i;
        }

        return $this->array_proyectos;
    }

    public function GetProyectosProceso($id_proceso = null) {
        $id_proceso = !empty($id_proceso) ? $id_proceso : $this->id_proceso;

        $sql = "select DISTINCT tproyectos.*, tproyectos.id as _id, tproyectos.id_code as _id_code, nombre, ";
        $sql.= "descripcion, tproyectos.id_responsable as _id_responsable, tproyectos.id_proceso as _id_proceso, ";
        $sql.= "fecha_inicio_plan, fecha_fin_plan from tproyectos, tproceso_proyectos ";
        $sql.= "where tproyectos.id = tproceso_proyectos.id_proyecto and tproceso_proyectos.id_proceso = $id_proceso ";
        if (!empty($this->id_programa))
            $sql .= "and tproceso_proyectos.id_programa = $this->id_programa ";

        $this->clink->free_result();
        $result = $this->do_sql_show_error('GetProyectoProceso', $sql);

        $i = 0;
        while ($row = $this->clink->fetch_array($result)) {
            ++$i;
            if (!empty($row['_id'])) {
                $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => $row['nombre'], 
                    'descripcion' => $row['descripcion'], 'id_programa'=>$row['id_programa'],
                    'id_responsable' => $row['_id_responsable'], 'id_proceso' => $row['_id_proceso'],
                    'fecha_inicio_plan' => $row['fecha_inicio_plan'], 'fecha_fin_plan' => $row['fecha_fin_plan']);
                $this->array_proyectos[$row['_id']] = $array;
            }
        }

        if ($i > 0)
            $this->clink->data_seek($result);
        return $result;
    }

    public function GetProyectosUser($filter = 1) {
        $this->_proyecto_user($filter);
        $this->_proyecto_group($filter);

        return $this->array_proyectos;
    }

    private function _proyecto_user($filter = 1) {
        if (!empty($this->year) && !empty($filter)) {
            $this->date_interval($fecha_inicio, $fecha_fin);
        }

        $sql = "select DISTINCT tproyectos.*, tproyectos.id as _id, nombre, descripcion ";
        $sql .= "from tproyectos, tusuario_proyectos where tproyectos.id = tusuario_proyectos.id_proyecto ";
        $sql .= "and (tusuario_proyectos.id_usuario = $this->id_usuario or tproyectos.id_responsable = $this->id_usuario) ";
        if (!empty($this->year))
            $sql.= "and (YEAR(fecha_inicio_plan) <= $this->year and YEAR(fecha_fin_plan) >= $this->year) ";
        if (!empty($this->year) && $filter == 1) {
            $sql.= "and (fecha_fin_real is null ";
            $sql.= "or ((" . date2pg("fecha_inicio_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_inicio_plan") . " <= '$fecha_fin') ";
            $sql.= "or (" . date2pg("fecha_fin_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_fin_plan") . " <= '$fecha_fin'))) ";
        }
        $sql .= "order by tproyectos.nombre asc  ";

        $result = $this->do_sql_show_error('_proyecto_user', $sql);

        $i = 0;
        while ($row = $this->clink->fetch_array($result)) {
            ++$i;
            if (!empty($row['_id'])) {
                $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'], 'id_responsable' => $row['id_responsable'], 
                        'id_proceso' => $row['id_proceso'], 'id_programa'=>$row['id_programa'],
                        'fecha_inicio_plan' => $row['fecha_inicio_plan'], 'fecha_fin_plan' => $row['fecha_fin_plan']);
                $this->array_proyectos[$row['_id']] = $array;
            }
        }

        if ($i > 0)
            $this->clink->data_seek($result);
        return $result;
    }

    private function _proyecto_group($filter = 1) {
        if (!empty($this->year) && !empty($filter))
            $this->date_interval($fecha_inicio, $fecha_fin);

        $this->cleanListaUser();

        $sql = "select DISTINCT tproyectos.*, tproyectos.id as _id, nombre, id_grupo, descripcion ";
        $sql .= "from tproyectos, tusuario_proyectos where tproyectos.id = tusuario_proyectos.id_proyecto ";
        $sql .= "and tusuario_proyectos.id_grupo is not null ";
        if (!empty($this->year))
            $sql.= "and (YEAR(fecha_inicio_plan) <= $this->year and YEAR(fecha_fin_plan) >= $this->year) ";
        if (!empty($this->year) && $filter == 1) {
            $sql .= "and (fecha_fin_real is null ";
            $sql .= "or ((" . date2pg("fecha_inicio_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_inicio_plan") . " <= '$fecha_fin') ";
            $sql .= "or (" . date2pg("fecha_fin_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_fin_plan") . " <= '$fecha_fin'))) ";
        }
        $sql .= "order by tproyectos.nombre asc  ";

        $result = $this->do_sql_show_error('_proyecto_group', $sql);

        $found = false;
        while ($row = $this->clink->fetch_array($result)) {
            $this->cleanListaUser();
            $this->push2ListaUserGroup($row['id_grupo']);
            $_array_user = $this->get_list_user(false);

            foreach ($_array_user as $array) {
                if (array_search($this->id_usuario, $_array_user)) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'], 'id_responsable' => $row['id_responsable'], 
                        'id_proceso' => $row['id_proceso'], 'id_programa'=>$row['id_programa'],
                        'fecha_inicio_plan' => $row['fecha_inicio_plan'], 'fecha_fin_plan' => $row['fecha_fin_plan']);
                $this->array_proyectos[$row['_id']] = $array;
                $found = false;
            }
        }
    }

    public function eliminar($ifDelete_task= false) {
        if ($ifDelete_task)
            $sql= "delete from ttareas where id_proyecto = $this->id_proyecto";
        else
            $sql= "update ttareas set id_proyecto= null, id_proyecto_code= null where id_proyecto= $this->id_proyecto";
        $this->do_sql_show_error('eliminar', $sql);

        $sql = "delete from tproyectos where id = $this->id_proyecto";
        $this->do_sql_show_error('eliminar', $sql);
    }

    public function cleanObjeto() {
        $sql = "delete from tusuario_proyectos where id_proyecto= $this->id_proyecto ";
        return $this->_clean_object($sql);
    }

    public function setUsuario($action = 'add') {
        if ($action == 'add') {
            $sql = "insert into tusuario_proyectos (id_usuario,id_proyecto, id_proyecto_code, cronos, situs) ";
            $sql .= "values ($this->id_usuario, $this->id_proyecto, '$this->id_code', '$this->cronos', '$this->location') ";
        }
        if ($action == 'delete')
            $sql = "delete from tusuario_proyectos where id_usuario = $this->id_usuario and id_proyecto = $this->id_proyecto ";

        return $this->_set_user($sql);
    }

    public function listar_usuarios($use_id_user = true, $flag= false) {
        $use_id_user= !is_null($use_id_user) ? $use_id_user : true;
        $flag= !is_null($flag) ? $flag : false;

        $sql= "select tusuarios.*, tusuarios.id as _id from tusuarios, tusuario_proyectos ";
        $sql.= "where tusuarios.id = tusuario_proyectos.id_usuario ";
        $sql.= "and tusuario_proyectos.id_proyecto = $this->id_proyecto order by nombre asc ";

        return $this->_list_user($sql, $use_id_user, $flag);
    }

    public function listar_grupos($year= null, $flag= false) {
        $sql= "select tgrupos.*, tgrupos.id as _id, tusuario_proyectos.cronos as _cronos ";
        $sql.= "from tgrupos, tusuario_proyectos where tgrupos.id = tusuario_proyectos.id_grupo ";
        $sql.= "and tusuario_proyectos.id_proyecto = $this->id_proyecto order by nombre asc ";

        return $this->_list_group($sql, $flag);
    }

    public function setGrupo($action = 'add') {
        if ($action == 'add') {
            $sql = "insert into tusuario_proyectos (id_grupo,id_proyecto,id_proyecto_code, cronos, situs) ";
            $sql .= "values ($this->id_grupo, $this->id_proyecto, '$this->id_code', '$this->cronos', '$this->location')";
        }
        if ($action == 'delete')
            $sql = "delete from tusuario_proyectos where id_grupo = $this->id_grupo and id_proyecto = $this->id_proyecto ";

        return $this->_set_group($sql);
    }

    public function cleanUsuario() {
        $sql = "delete from tusuario_proyectos where id_usuario = $this->id_usuario ";
        $result= $this->do_sql_show_error('cleanUsuario', $sql);
        return $this->error;
    }

    public function set_fin_by_tarea($fecha_fin = null) {
        $fecha_fin = !empty($fecha_fin) ? $fecha_fin : $this->fecha_fin_real;

        $sql = "select * from tproyectos where id = $this->id_proyecto and (fecha_fin_real is  not null ";
        $sql .= "or " . str_to_date2("fecha_fin_real") . " > " . str_to_date2pg("'$fecha_fin'") . ") ";
        $this->do_sql_show_error('set_fin_by_tarea', $sql);

        if ($this->cant > 0)
            return false;

        $sql = "select * from ttareas where id_proyecto = $this->id_proyecto and (fecha_fin_real is null ";
        $sql .= "or " . str_to_date2("fecha_fin_real") . " > " . str_to_date2pg("'$fecha_fin'") . ") ";
        $this->do_sql_show_error('set_fin_by_tarea', $sql);

        if ($this->cant > 0)
            return false;

        $sql = "update tproyectos set fecha_fin_real= '$fecha_fin', cronos= '$this->cronos', situs= '$this->location' ";
        $sql .= "where id = $this->id_proyecto ";
        $this->do_sql_show_error('set_fin_by_tarea', $sql);

        return true;
    }

    public function set_inicio_by_tarea($fecha_inicio = null) {
        $fecha_inicio = !empty($fecha_inicio) ? $fecha_inicio : $this->fecha_inicio_real;

        $sql = "update tproyectos set fecha_inicio_real= '$fecha_inicio', cronos= '$this->cronos', situs=  ";
        $sql .= " '$this->location' where id = $this->id_proyecto and (fecha_inicio_real is not null ";
        $sql .= "or " . str_to_date2pg("fecha_inicio_real") . " > " . str_to_date2pg("'$fecha_inicio'") . ") ";
        $result= $this->do_sql_show_error('set_inicio_by_tarea', $sql);

        if ($this->cant > 0)
            return true;
    }

    public function listar_tareas($year = null, $id_proceso = null, $flag = false) {
        $year= !empty($year) ? $year : $this->year;

        if (!empty($year))
            $sql_init = "and (" . year2pg("fecha_inicio_plan") . " = $year or " . year2pg("fecha_fin_plan") . " = $year) ";
        if (!empty($this->inicio) && !empty($this->fin)) {
            $sql_init .= "and ((" . year2pg("fecha_inicio_plan") . " >= $this->inicio and " . year2pg("fecha_inicio_plan") . " <= $this->fin) ";
            $sql_init .= "or (" . year2pg("fecha_fin_plan") . " >= $this->inicio and " . year2pg("fecha_fin_plan") . " <= $this->fin)) ";
        }

        $sql= "select distinct ttareas.*, ttareas.nombre as _nombre, ttareas.id_responsable as _id_responsable, ";
        $sql.= "ttareas.id as _id, ttareas.id_code as _id_code, tproceso_eventos_$year.id_proceso as id_proceso_ref, ";
        $sql.= "tproceso_eventos_$year.id_responsable as id_responsable_ref from ttareas, tproceso_eventos_$year ";
        $sql.= "where ttareas.id_proyecto = $this->id_proyecto ";
        $sql.= "and ((ifgrupo = false or ifgrupo is null) and ttareas.id = tproceso_eventos_$year.id_tarea) ";
        if (!empty($id_proceso)) 
            $sql .= "and ((ifgrupo = false or ifgrupo is null) and tproceso_eventos_$year.id_proceso = $id_proceso) ";   
        $sql.= $sql_init;
        $sql .= " union ";
        $sql.= "select distinct ttareas.*, ttareas.nombre as _nombre, ttareas.id_responsable as _id_responsable, ";
        $sql.= "ttareas.id as _id, ttareas.id_code as _id_code, null as id_proceso_ref, ";
        $sql.= "null as id_responsable_ref from ttareas where ttareas.id_proyecto = $this->id_proyecto ";
        $sql.= "and ifgrupo = true ";   
        $sql.= $sql_init;
        $sql.= "order by fecha_inicio_plan asc";

        $result = $this->do_sql_show_error('listar_tareas', $sql);

        if (!$flag)
            return $result;

        while ($row = $this->clink->fetch_array($result)) {
            if (array_key_exists($row['_id'], $this->array_tareas) == false) {
                $array_procesos[$row['_id']] = array($row['id_proceso_ref']);
            } else {
                $array_procesos[$row['_id']][] = $row['id_proceso_ref'];
                $this->array_tareas[$row['_id']]['procesos'] = $array_procesos[$row['_id']];
                --$this->cant;
                continue;
            }

            $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => $row['_nombre'], 'descripcion' => $row['descripcion'],
                'fecha_inicio' => $row['fecha_inicio_plan'], 'fecha_fin' => $row['fecha_fin_plan'], 'id_responsable' => $row['_id_responsable'],
                'id_proceso' => $row['id_proceso'], 'id_proceso_code' => $row['id_proceso_code'], 'ifgrupo' => $row['ifgrupo'], 'chk_date' => $row['chk_date'],
                'id_proceso_ref' => $row['id_proceso_ref'], 'id_responsable_ref' => $row['id_responsable_ref'], 'id_usuario' => $row['id_usuario'],
                'id_proyecto' => $row['id_proyecto'], 'id_proyecto_code' => $row['id_proyecto_code'],
                'origen_data' => $row['origen_data'], 'procesos' => $array_procesos[$row['_id']], 'copyto' => $row['copyto']);

            $this->array_tareas[$row['_id']] = $array;
        }

        return $this->array_tareas;
    }

    public function cleanTareas() {
        $sql= "update ttareas set id_proyecto= NULL, id_proyecto_code= NULL, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_proyecto= $this->id_proyecto ";
        $this->do_sql_show_error('cleanTareas', $sql);
    }

    public function delete_tarea($id_tarea) {
        if (empty($id_tarea))
            $id_tarea = $this->id_tarea;

        $sql= "update ttareas set id_proyecto= NULL, id_proyecto_code= NULL, cronos= '$this->cronos', ";
        $sql.= "situs= '$this->location' where id_tarea = $id_tarea and id_proyecto = $this->id_proyecto ";
        $result= $this->do_sql_show_error('delete_tareas', $sql);

        return $this->error;
    }

    public function add_tarea() {
        $sql= "update ttareas set id_proyecto= $this->id_proyecto, id_proyecto_code= '$this->id_proyecto_code', ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' where id = $this->id_tarea ";
        $result= $this->do_sql_show_error('add_tareas', $sql);

        return $this->error;
    }

    public function get_array_usuarios() {
        $sql= "select tusuarios.*, tusuarios.id as _id from tusuarios, tusuario_proyectos ";
        $sql.= "where tusuario_proyectos.id_usuario is not null and tusuario_proyectos.id_proyecto = $this->id_proyecto ";
        $sql.= "and tusuarios.id = tusuario_proyectos.id_usuario ";
        $sql.= "union ";
        $sql.= "select tusuarios.*, tusuarios.id as _id from tusuarios, tusuario_proyectos, tusuario_grupos ";
        $sql.= "where tusuario_proyectos.id_grupo is not null and tusuario_proyectos.id_proyecto = $this->id_proyecto ";
        $sql.= "and tusuario_grupos.id_grupo = tusuario_proyectos.id_grupo and  tusuarios.id = tusuario_grupos.id_usuario ";

        return $this->_list_user($sql);
    }

}


/*
 * Clases adjuntas o necesarias
 */

include_once "time.class.php";