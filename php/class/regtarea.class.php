<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */


if (!class_exists('Ttarea'))
    include_once "tarea.class.php";

class Tregtarea extends Ttarea {

    private $last_reg;
    private $planning;
    public $array_tareas;

    public function GetPlanning() {
        return $this->planning;
    }
    public function SetPlanning($id = 1) {
        $this->planning = $id;
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Ttarea::__construct($this->clink);

        $this->className = 'Ttarea';
    }

    public function Set($id= null) {
        $this->id_tarea= !empty($id) ? $id : $this->id_tarea;
        if (empty($this->id_tarea))
            return null;
        Ttarea::Set();
    }

    public function getTarea_reg($id_tarea= null, $planning= false) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $planning= !empty($planning) ? "true" : "false";

        $sql = "select * from treg_tarea where id_tarea = $id_tarea and planning= $planning order by cronos desc";
        $result = $this->do_sql_show_error('Set', $sql);
        $row = $this->clink->fetch_array($result);
        return $row;
    }

    private function set_inicio_fin_tarea() {
        $fecha_inicio= setNULL_str($this->fecha_inicio_real);
        $fecha_fin= setNULL_str($this->fecha_fin_real);

        $sql= "update ttareas set fecha_inicio_real= $fecha_inicio, fecha_fin_real= $fecha_fin ";
        $sql.= "id_usuario= {$_SESSION['id_usuario']}, cronos= '$this->cronos' where id = $this->id_tarea ";
        $result = $this->do_sql_show_error('set_inicio_fin_tarea', $sql);
    }

    private function set_inicio_by_tarea() {
        $obj_proy = new Tproyecto($this->clink);
        $obj_proy->Set($this->id_proyecto);

        $obj_proy->set_inicio_by_tarea($this->fecha_inicio_real);
    }

    private function set_fin_by_tarea() {
        $obj_proy = new Tproyecto($this->clink);
        $obj_proy->Set($this->id_proyecto);

        $obj_proy->set_fin_by_tarea($this->fecha_fin_real);
    }

    public function add_cump_to_task($id_code = null) {
        if (!empty($this->id_proyecto)) {
            if (!empty($this->fecha_inicio_real))
                $this->set_inicio_by_tarea();
            if (!empty($this->fecha_fin_real))
                $this->set_fin_by_tarea();    
        }

        if (!empty($this->fecha_inicio_real) || !empty($this->fecha_fin_real))
            $this->linefeed();

        $planning = boolean2pg($this->planning);
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $reg_fecha = setNULL_str($this->reg_fecha);
        $observacion = setNULL_str($this->observacion);

        $value = setNULL_empty($this->value);

        $id_kanban_column= setNULL($this->id_kanban_column);
        $id_kanban_column_code= setNULL_str($this->id_kanban_column_code);

        if (!empty($id_code) && $this->planning) {
            $sql = "update treg_tarea set valor= $value, reg_fecha=$reg_fecha, observacion=$observacion, ";
            $sql .= "id_usuario={$_SESSION['id_usuario']}, id_kanban_column= $id_kanban_column, ";
            $sql.= "id_kanban_column_code= $id_kanban_column_code, cronos='$this->cronos', situs='$this->location' ";
            $sql.= "where id_code = '$id_code'";
        } else {
            $sql = "insert into treg_tarea (id_tarea, id_tarea_code, valor, reg_fecha, observacion, id_usuario, ";
            $sql .= "planning, id_kanban_column, id_kanban_column_code, cronos, situs) values ($this->id, ";
            $sql .= "'$this->id_code', $value, $reg_fecha, $observacion, {$_SESSION['id_usuario']}, $planning, ";
            $sql .= "$id_kanban_column, $id_kanban_column_code, '$this->cronos', '$this->location')";
        }
       
        $result = $this->do_sql_show_error('add_cump_to_task', $sql);

        if ($result && empty($id_code)) {
            $id = $this->clink->inserted_id("treg_tarea");

            $this->obj_code->SetId($id);
            $this->obj_code->set_code('treg_tarea', 'id', 'id_code');
        }
        return $this->error;
    }

    public function del_reg($id_code) {
        $sql = "delete from treg_tarea where id_code = '$id_code' ";
        $result= $this->do_sql_show_error('del_reg', $sql);
    }

    public function listTarea_reg($id_tarea) {
        $sql= "select treg_tarea.*, tkanban_columns.nombre as kanban_column from treg_tarea, tkanban_columns "; 
        $sql.= "where (treg_tarea.id_kanban_column = tkanban_columns.id and treg_tarea.id_tarea = $id_tarea) ";
        $sql.= "and planning = false ";
        if (!empty($this->id_proyecto))
            $sql.= "and tkanban_columns.id_proyecto = $this->id_proyecto ";
        if (!empty($this->id_responsable) && empty($this->id_proyecto))
            $sql.= "and tkanban_columns.id_responsable = $this->id_responsable "; 
        if (!empty($this->id_responsable))
            $sql.= "and treg_tarea.id_usuario = $this->id_respopnsable ";
        if (!empty($this->year))
            $sql.= "and YEAR(reg_fecha) = $this->year ";
        $sql.= "order by treg_tarea.cronos desc";
        $result = $this->do_sql_show_error('listTarea_reg', $sql);
        return $result;
    }

    public function listar_reg($id_tarea = null, $flag = false, $order= 'desc') {
        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $flag= !is_null($flag) ? $flag : false;
        $order= !empty($order) ? $order : 'desc';
        $this->cant = 0;
        $this->last_reg = null;

        if (empty($this->planning))
            $this->planning = 0;
        $this->date_interval($fecha_inicio, $fecha_fin);

        $sql = "select distinct treg_tarea.*, treg_tarea.id as _id, treg_tarea.id_code as _id_code, ";
        $sql .= "treg_tarea.cronos as _cronos, ttareas.id as _id_tarea, ttareas.id_code as _id_tarea_code, ";
        $sql.= "ttareas.nombre as _nombre, ttareas.id_tarea as id_tarea_grupo, ";
        $sql .= "ttareas.id_tarea_code as id_tarea_grupo_code, fecha_inicio_plan, fecha_fin_plan, fecha_inicio_real, ";
        $sql .= "fecha_fin_real, ttareas.id_usuario as id_usuario_asigna, carga from treg_tarea, ttareas ";
        $sql .= "where treg_tarea.id_tarea = ttareas.id and treg_tarea.id_tarea = $id_tarea ";
        if (!empty($this->year))
            $sql .= "and " . date2pg("reg_fecha") . " <= '$fecha_fin' ";
        if (!$flag)
            $sql .= "and planning = " . boolean2pg($this->planning) . " ";
        if (!empty($this->id_responsable))
            $sql .= "and (id_responsable = $this->id_responsable or ttareas.id_usuario = $this->id_responsable) ";
        $sql .= "order by reg_fecha $order, treg_tarea.cronos desc, nombre asc ";

        $result = $this->do_sql_show_error('listar_reg', $sql);
        if (empty($this->cant))
            return null;

        $row = $this->clink->fetch_array($result);
        $this->last_reg = $row['treg_tarea.id'];
        $this->clink->data_seek($result);

        if (!$flag)
            return $result;

        $array_tareas = array(null, null, null);

        while ($row = $this->clink->fetch_array($result)) {
            if (empty($array_tareas[2])) {
                $array = array('fecha_inicio_plan' => $row['fecha_inicio_plan'], 'fecha_fin_plan' => $row['fecha_fin_plan'],
                    'fecha_inicio_real' => $row['fecha_inicio_real'], 'fecha_fin_real' => $row['fecha_fin_real'], 
                    'carga' => $row['carga']);
                $array_tareas[2] = $array;
            }
            if (empty($array_tareas[0]) && !boolean($row['planning'])) {
                $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'id_usuario' => $row['id_usuario'], 
                    'valor' => $row['valor'], 'fecha' => $row['reg_fecha'], 'cronos' => $row['_cronos']);
                $array_tareas[0] = $array;
            }
            if (empty($array_tareas[1]) && boolean($row['planning'])) {
                $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'id_usuario' => $row['id_usuario'], 
                    'valor' => $row['valor'], 'fecha' => $row['reg_fecha'], 'cronos' => $row['_cronos']);
                $array_tareas[1] = $array;
            }
            if (!empty($array_tareas[0]) && !empty($array_tareas[1]))
                break;
        }

        return $array_tareas;
    }

    public function getAvance($id_tarea, $reg_fecha = null) {
        $sql = "select * from treg_tarea where id_tarea = $id_tarea and valor is not null and planning = false ";
        if (!is_null($reg_fecha))
            $sql .= "and " . date2pg("reg_fecha") . " <= '$reg_fecha' ";
        $sql .= "order by reg_fecha desc, cronos desc ";

        $result = $this->do_sql_show_error('getAvance', $sql);
        $row = $this->clink->fetch_array($result);
        $valor = !empty($row['valor']) ? $row['valor'] : 0;
        return $valor;
    }

    protected function linefeed() {
        $fecha_inicio = setNULL_str($this->fecha_inicio_real);
        $fecha_fin = setNULL_str($this->fecha_fin_real);

        if (!empty($this->fecha_inicio_real) && !empty($this->fecha_fin_real)) {
            $diff = diffDate($this->fecha_inicio_real, $this->fecha_fin_real);
            $diff = "{$diff['y']}-{$diff['m']}-{$diff['d']}";
        } else
            $diff = "NULL";

        $sql = "update ttareas set fecha_inicio_real= $fecha_inicio, fecha_fin_real= $fecha_fin, duracion_real= '$diff', ";
        $sql .= "cronos= '$this->cronos', situs= '$this->location' where id = $this->id_tarea ";
        $result= $this->do_sql_show_error('linefeed', $sql);
        return $this->error;
    }

    public function cleanReg() {
        $sql = "delete from treg_tarea where planning = true and id_tarea = $this->id_tarea ";
        $result= $this->do_sql_show_error('cleanReg', $sql);
    }

    public function getEventoTarea_reg($id_tarea= null, $reg_date= null) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $reg_date= !empty($reg_date) ? $reg_date : "$this->year-12-31";

        $sql= "select _treg_evento.*, fecha_inicio_plan from _teventos, _treg_evento ";
        $sql.= "where _teventos.id = _treg_evento.id_evento and _teventos.id_responsable = _treg_evento.id_usuario ";
        $sql.= "and _teventos.id_tarea = $id_tarea and fecha_inicio_plan <= '$reg_date' order by cronos desc limit 1";
        
        $result= $this->do_sql_show_error('getEvento_reg', $sql);
        $row= $this->clink->fetch_array($result);
        return $row;
    }

    private function create_tmp_eventos() {
        $this->obj_tables= new Tbase_tables_planning($this->clink);
        $this->obj_tables->_create_tmp_teventos();

        $sql= "insert into _teventos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= "select distinct id, null, id_code, numero, id_responsable, id_responsable_2, responsable_2_reg_date, ";
        $sql.= "id_responsable, id_proceso, id_usuario, origen_data, cronos, funcionario, nombre, fecha_inicio_plan, fecha_fin_plan, ";
        $sql.= "periodicidad, empresarial, id_tipo_evento, toshow, user_check, descripcion, lugar, id_evento, id_evento_code, id_tarea, ";
        $sql.= "id_tarea_code, id_auditoria, id_auditoria_code, id_tipo_reunion, id_tipo_reunion_code, id_tematica, id_tematica_code, ";
        $sql.= "id_copyfrom, id_copyfrom_code, ifassure, id_secretary, id_archivo, id_archivo_code, numero_plus, day(fecha_fin_plan), ";
        $sql.= "month(fecha_fin_plan), year(fecha_inicio_plan), 0, '0', indice, indice_plus, tidx, NULL, NULL, NULL, NULL, NULL, NULL ";
        $sql.= "from teventos where id_tarea is not null and year(fecha_inicio_plan) = $this->year";

        $result= $this->do_sql_show_error('create_tmp_eventos', $sql);
        $this->if_teventos= is_null($this->error) ? true : false;
    }

    public function create_tmp_treg_evento() {
        $this->obj_tables->_create_tmp_treg_evento();

        $sql= "select id, id_responsable from _teventos"; 
        $result= $this->do_sql_show_error('create_tmp_treg_evento', $sql);

        $sql= null;
        $i= 0;
        $j= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $sql.= "insert into _treg_evento ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "select treg_evento_{$this->year}.* from treg_evento_{$this->year} ";
            $sql.= "where treg_evento_{$this->year}.id_evento = {$row['id']} ";
            $sql.= "and treg_evento_{$this->year}.id_usuario = {$row['id_responsable']} order by cronos desc limit 1; ";

            ++$j;
            ++$i;
            if ($i >= 500) {
                $this->do_multi_sql_show_error('create_tmp_treg_evento', $sql);
                $sql= null;
                $i= 0;

                if (!is_null($this->error))
                    break;
            }
        }
        if (!empty($sql) && is_null($this->error))
            $this->do_multi_sql_show_error('create_tmp_treg_evento', $sql);

        $this->if_treg_evento= is_null($this->error) ? true : false;
    }


    public function automatic_tarea_status($result) {
        $obj_reg= new Ttmp_tables_planning($this->clink);
        $obj_reg->SetYear($this->year);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_responsable'=>$row['id_responsable']);
            $this->array_tareas[$row['id']]= $array;
        }

        if($i == 0)
            return null;

        $this->create_tmp_eventos();
        $this->create_tmp_treg_evento();

        $sql= "select _teventos.*, _teventos.id_responsable as _id_responsable, _treg_evento.cumplimiento as _cumplimiento ";
        $sql.= "from _teventos, _treg_evento where _teventos.id = _treg_evento.id_evento ";
        $sql.= "and _teventos.id_responsable = _treg_evento.id_usuario";
        $result= $this->do_sql_show_error('automatic_tarea_status', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->test_if_incumplida($row['id'], $row['_id_responsable'], $row['_cumplimiento'], $row['fecha_inicio_plan']);
        } 
    }

    public function update_tarea_status() {
        $reg_date= $this->year.'-'.str_pad($this->month, 2, "0", STR_PAD_LEFT).'-'.str_pad($this->day, 2, "0", STR_PAD_LEFT);

        $sql= null;
        $i= 0;
        $j= 0;
        foreach ($this->array_tareas as $id => $row) {
            $rowcmp= $this->getTarea_reg($id);
            $cronos_task= !empty($rowcmp) ? $rowcmp['cronos'] : null;
            $valor= setNULL($rowcmp['valor']);

            $this->id_usuario= $row['id_responsable'];
            $rowcmp= $this->getEventoTarea_reg($id, $reg_date);
            $cronos_event= !empty($rowcmp) ? $rowcmp['cronos'] : null;
            $cumplimiento= setNULL($rowcmp['cumplimiento']);
            $observacion= setNULL_str($row['observacion']);

            if((!empty($cronos_event) && (strtotime($cronos_task) < strtotime($cronos_event)))) {
                ++$j;
                ++$i;
                $sql.= "insert into treg_tarea (id_tarea, id_tarea_code, valor, cumplimiento, reg_fecha, observacion, ";
                $sql.= "id_usuario, planning, cronos, situs) values ($id, '{$row['id_code']}', $valor, $cumplimiento, ";
                $sql.= "'$reg_date', $observacion, {$row['id_responsable']}, false, '$this->cronos', '$this->location'); ";
            }

            if ($i >= 500) {
                $this->do_multi_sql_show_error('update_tarea_status', $sql);
                $sql= null;
                $i= 0;

                if (!is_null($this->error))
                    break;
            }
        }
        if (!empty($sql) && is_null($this->error))
            $this->do_multi_sql_show_error('update_tarea_status', $sql);

        $this->_set_id_code("treg_tarea", $_SESSION["location"]);
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";

if (!class_exists('Tproyecto'))
    include_once "proyecto.class.php";