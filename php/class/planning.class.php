<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tregister_planning'))
    include_once "register_planning.class.php";

class Tplanning extends Tregister_planning {

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tregister_planning::__construct($clink);
    }

    public function set_null_periodicity() {
        $this->freeday= null;
        $this->saturday= null;
        $this->sunday= null;
        $this->periodicidad= null;
        $this->carga= null;
        $this->cant_days= null;
        $this->dayweek= null;
        $this->periodic= null;
    }

    /**
     * @param string $action
     * @param bool $indirect : El usuario es asignado por que participa de uno de los porcesos involucrado
     * no por que pertenezca al los usuaios o los grupos seleccionados
     * @return null
     */
    public function setUsuario($action= 'add', $indirect= false, $year= null, $multi_query= false) {
        $indirect= !is_null($indirect) ? $indirect : false;
        $year= !empty($year) ? $year : $this->year;
        $multi_query= !is_null($multi_query) ? $multi_query : false;

        if (empty($this->id_evento) && empty($this->id_auditoria) && empty($this->id_tarea) && empty($this->id_tematica))
            return null;

        $indirect= setNULL_empty($indirect);
        $id_tarea= setNULL($this->id_tarea);
        $id_tarea_code= setNULL_str($this->id_tarea_code);
        $id_auditoria= setNULL($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);
        $id_evento= setNULL($this->id_evento);
        $id_evento_code= setNULL_str($this->id_evento_code);
        $id_tematica= setNULL($this->id_tematica);
        $id_tematica_code= setNULL_str($this->id_tematica_code);

        $sql= null;
        if ($action == 'add') {
            $sql= "insert into tusuario_eventos_$year (id_evento, id_evento_code, id_tarea, id_tarea_code, ";
            $sql.= "id_auditoria, id_auditoria_code, id_tematica, id_tematica_code, id_usuario, indirect, cronos, situs) ";
            $sql.= "values ($id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, ";
            $sql.= "$id_tematica, $id_tematica_code, $this->id_usuario, $indirect, '$this->cronos', '$this->location') ";
        }
        if ($action == 'delete') {
           $sql= "delete from tusuario_eventos_$year where id_usuario = $this->id_usuario and id_grupo is null ";
           if (!empty($this->id_evento))
               $sql.= "and id_evento = $this->id_evento ";
           if (!empty($this->id_tarea))
               $sql.= "and id_tarea = $this->id_tarea ";
           if (!empty($this->id_auditoria))
               $sql.= "and id_auditoria = $this->id_auditoria ";
           if (!empty($this->id_tematica))
               $sql.= "and id_tematica = $this->id_tematica ";
        }
        $sql.= "; ";

        if (!$multi_query)
            $this->_set_user($sql);

        if (is_null($this->error) && $action == 'delete')
            $sql.= $this->add_to_tdelete("tusuario_eventos", $multi_query);

        return $multi_query ? $sql : $this->error;
    }

    public function listar_usuarios($use_id_user= true, $show_indirect= false, $year= null, $flag= false) {
        $use_id_user= !is_null($use_id_user) ? $use_id_user : true;
        $show_indirect= !is_null($show_indirect) ? $show_indirect : FALSE;
        $year= !empty($year) ? $year : $this->year;
        $flag= !is_null($flag) ? $flag : false;

        $sql= "select distinct tusuarios.*, tusuarios.id as _id, nombre, email, cargo, tusuario_eventos_{$year}.cronos as _cronos, ";
        $sql.= "indirect from tusuarios, tusuario_eventos_{$year} where tusuarios.id = tusuario_eventos_{$year}.id_usuario ";
        if (!empty($this->user_date_ref))
            $sql.= "and (eliminado is null or eliminado > '$this->user_date_ref') ";
        if (!$show_indirect)
            $sql.= "and (indirect is null or indirect = 0) ";
        if (!empty($this->id_tarea))
            $sql.= "and tusuario_eventos_{$year}.id_tarea = $this->id_tarea ";
        if (!empty($this->id_evento))
            $sql.= "and tusuario_eventos_{$year}.id_evento = $this->id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and tusuario_eventos_{$year}.id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_tematica))
            $sql.= "and tusuario_eventos_{$year}.id_tematica = $this->id_tematica ";
        if (!empty($this->id_proceso))
            $sql.= "and tusuarios.id_proceso = $this->id_proceso ";
        $sql.= "order by nombre asc ";

        return $this->_list_user($sql, $use_id_user, $flag);
    }

    public function setGrupo($action= 'add', $year= null, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;

        $action= !is_null($action) ? $action : 'add';
        $year= !empty($year) ? $year : $this->year;
        if (empty($this->id_evento) && empty($this->id_auditoria) && empty($this->id_tarea) && empty($this->id_tematica))
            return null;

        $id_tarea= setNULL($this->id_tarea);
        $id_tarea_code= setNULL_str($this->id_tarea_code);
        $id_auditoria= setNULL($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);
        $id_evento= setNULL($this->id_evento);
        $id_evento_code= setNULL_str($this->id_evento_code);
        $id_tematica= setNULL($this->id_tematica);
        $id_tematica_code= setNULL_str($this->id_tematica_code);

        $sql= null;

        if ($action == 'add') {
            $sql= "insert into tusuario_eventos_$year (id_evento, id_evento_code, id_tarea, id_tarea_code, ";
            $sql.= "id_auditoria, id_auditoria_code, id_tematica, id_tematica_code, id_grupo, cronos, situs) ";
            $sql.= "values ($id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, ";
            $sql.= "$id_tematica, $id_tematica_code, $this->id_grupo, '$this->cronos', '$this->location'); ";
            
            if (!$multi_query)
                $this->_set_group($sql);
        }
        if ($action == 'delete') {
            $sql= "delete from tusuario_eventos_$year where id_grupo = $this->id_grupo and id_usuario is null ";
            if (!empty($this->id_tarea))
                $sql.= "and id_tarea = $this->id_tarea ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento = $this->id_evento ";
            if (!empty($this->id_auditoria))
                $sql.= "and id_auditoria = $this->id_auditoria ";
            if (!empty($this->id_tematica))
                $sql.= "and id_tematica = $this->id_tematica ";
            $sql.= "; ";

            if (!$multi_query)
                $this->_set_group($sql);
        }

        return !$multi_query ? $this->error : $sql;
    }

    public function listar_grupos($year= null, $flag= false) {
        $year= !empty($year) ? $year : $this->year;
        $flag= !is_null($flag) ? $flag : false;

        $sql= "select distinct id_grupo as _id, nombre, id_entity, tusuario_eventos_{$year}.* ";
        $sql.= "from tgrupos, tusuario_eventos_{$year} where tgrupos.id = tusuario_eventos_{$year}.id_grupo ";
        if (!empty($this->id_tarea))
            $sql.= "and tusuario_eventos_{$year}.id_tarea = $this->id_tarea ";
        if (!empty($this->id_evento))
            $sql.= "and tusuario_eventos_{$year}.id_evento = $this->id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and tusuario_eventos_{$year}.id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_tematica))
            $sql.= "and tusuario_eventos_{$year}.id_tematica = $this->id_tematica ";
        if (!empty($this->id_entity))
            $sql.= "and tgrupos.id_entity = $this->id_entity ";

        return $this->_list_group($sql, $flag);
    }

    public function get_child_events_by_table($table, $id, $id_proceso= null, $fecha_origen= null, $fecha_fin= null) {
        if ($table == 'teventos')
            $this->get_child_events_by_evento($id, $id_proceso, $fecha_origen, $fecha_fin);
        if ($table == 'ttareas')
            $this->get_child_events_by_tarea($id, $id_proceso, $fecha_origen, $fecha_fin);
        if ($table == 'tauditorias')
            $this->get_child_events_by_auditoria($id, $id_proceso, $fecha_origen);

        return $this->array_eventos;
    }

    private function get_child_events_by_evento($id, $id_proceso= null, $fecha_origen= null, $fecha_fin= null) {
        $year= !empty($fecha_fin) ? date('Y', strtotime($fecha_origen)) : $this->year;

        $sql= "select distinct teventos.* from teventos ";
        if (!empty($id_proceso))
            $sql.= ", tproceso_eventos_{$year} ";
        $sql.= "where id_evento = $id ";
        if (!is_null($fecha_origen))
            $sql.= "and date(fecha_inicio_plan) >= date('$fecha_origen') ";
        if (!is_null($fecha_fin))
            $sql.= "and date(fecha_fin_plan) <= date('$fecha_fin') ";
        if (!empty($id_proceso))
            $sql.= "and tproceso_eventos_{$year}.id_proceso = $id_proceso ";
        $sql.= "order by fecha_inicio_plan asc";

        $this->get_child_events($sql);
        return $this->array_eventos;
    }

    private function get_child_events_by_tarea($id, $id_proceso= null, $fecha_inicio= null, $fecha_fin= null) {
        $year_init= !empty($fecha_inicio) ? date('Y', strtotime($fecha_inicio)) : $this->inicio;
        $year_init= !empty($this->inicio) ? $this->inicio : $this->year;
        $year_end= !empty($fecha_fin) ? date('Y', strtotime($fecha_fin)) : $this->fin;
        $year_end= !empty($this->fin) ? $this->fin : $this->year;

        if (empty($year_init) && !empty($this->fecha_inicio_plan))
            $year_init= date('Y', strtotime ($this->fecha_inicio_plan));
        if (empty($year_end) && !empty($this->fecha_fin_plan))
            $year_end= date('Y', strtotime ($this->fecha_fin_plan));

        $sql= null;
        for ($year= (int)$year_init; $year <= (int)$year_end; $year++) {
            $sql.= $year > $year_init ? "union " : "";
            $sql.= "select distinct teventos.*, $year as _year from teventos ";
            if (!empty($id_proceso))
                $sql.= ", tproceso_eventos_$year ";
            $sql.= "where teventos.id_tarea = $id ";
            if (!is_null($fecha_inicio))
                $sql.= "and (date(fecha_inicio_plan) <= date('$fecha_inicio') or date(fecha_inicio_real) <= date('$fecha_inicio')) ";
            if (!is_null($fecha_fin))
                $sql.= "and (date(fecha_fin_plan) <= date('$fecha_fin') or date(fecha_fin_real) <= date('$fecha_fin')) ";
            if (!empty($id_proceso))
                $sql.= "and (teventos.id = tproceso_eventos_$year.id_evento and tproceso_eventos_$year.id_proceso = $id_proceso) ";
        }
        $sql.= "order by fecha_inicio_plan asc";

        $this->get_child_events($sql);

        foreach ($this->array_eventos as $index => $evento) {
            $sql= "select * from treg_evento_{$evento['year']} where id_usuario = {$evento['id_responsable']} ";
            $sql.= "and id_evento = {$evento['id']} order by cronos desc limit 1";
            $result= $this->do_sql_show_error('get_child_events_by_tarea', $sql);

            if ((int)$this->cant == 0) {
                $this->array_eventos[$index]= null;
                unset($this->array_eventos[$index]);
            } else {
                $row= $this->clink->fetch_array($result);
                $this->array_eventos[$index]['cumplimiento']= $row['cumplimiento'];
                $this->array_eventos[$index]['rechazado']= $row['rechazado'];
            }
        }
        reset($this->array_eventos);
    }

    private function _get_child_events_by_auditoria($id, $id_proceso= null, $init_array= false) {
        $year_init= !empty($fecha_inicio) ? date('Y', strtotime($fecha_inicio)) : $this->inicio;
        $year_init= !empty($this->inicio) ? $this->inicio : $this->year;
        $year_end= !empty($fecha_fin) ? date('Y', strtotime($fecha_fin)) : $this->fin;
        $year_end= !empty($this->fin) ? $this->fin : $this->year;

        $sql= null;
        for ($year= (int)$year_init; $year <= (int)$year_end; $year++) {
            $sql.= $year > $year_init ? "union " : "";
            $sql.= "select distinct teventos.* from teventos, tproceso_eventos_{$year} where teventos.id_auditoria = $id ";
            $sql.= "and teventos.id_auditoria = tproceso_eventos_{$year}.id_auditoria ";
            if (!empty($id_proceso))
                $sql.= "and tproceso_eventos_{$year}.id_proceso = $id_proceso ";
        }
        $sql.= "order by fecha_inicio_plan asc";

        $this->get_child_events($sql, $init_array);
    }

    public function get_child_events_by_auditoria($id, $id_proceso= null, $fecha= null) {
        if (empty($id))
            return null;
        if (isset($this->array_eventos)) 
            unset($this->array_eventos);
        $this->array_eventos= array();

        $year_init= !empty($fecha_inicio) ? date('Y', strtotime($fecha_inicio)) : $this->inicio;
        $year_init= !empty($this->inicio) ? $this->inicio : $this->year;
        $year_end= !empty($fecha_fin) ? date('Y', strtotime($fecha_fin)) : $this->fin;
        $year_end= !empty($this->fin) ? $this->fin : $this->year;

        $sql= null;
        for ($year= (int)$year_init; $year <= (int)$year_end; $year++) {
            $sql.= $year > $year_init ? "union " : "";
            $sql.= "select distinct tauditorias.* from tauditorias, tproceso_eventos_{$year} where (tauditorias.id = $id ";
            $sql.= "or tauditorias.id_auditoria = $id) and tauditorias.id = tproceso_eventos_{$year}.id_auditoria ";
            if (!is_null($fecha))
                $sql.= "and ".date2pg("fecha_inicio_plan")." >= ".str_to_date2pg("'$fecha'")." ";
            if (!empty($id_proceso))
                $sql.= "and tproceso_eventos_{$year}.id_proceso = $id_proceso ";
        }
        $sql.= "order by fecha_inicio_plan asc";
        $result= $this->do_sql_show_error('get_child_events', $sql);

        while ($row= $this->clink->fetch_array($result))
            $this->_get_child_events_by_auditoria($row['id'], $id_proceso, false);

        return $this->array_eventos;
    }

    public function get_child_auditoria($id, $id_proceso= null, $fecha= null) {
        if (empty($id))
            return null;
        if (isset($this->array_auditorias)) 
            unset($this->array_auditorias);
        $this->array_auditorias= null;

        $sql= "select distinct tauditorias.* from tauditorias, tproceso_eventos_{$this->year} where tauditorias.id_auditoria = $id ";
        $sql.= "and tauditorias.id = tproceso_eventos_{$this->year}.id_auditoria ";
        if (!is_null($fecha))
            $sql.= "and ".date2pg("fecha_inicio_plan")." >= ".str_to_date2pg("'$fecha'")." ";
        if (!empty($id_proceso))
            $sql.= "and tproceso_eventos_{$this->year}.id_proceso = $this->id_proceso ";
        $sql.= "order by fecha_inicio_plan asc";
        $result= $this->do_sql_show_error('get_child_auditoria', $sql);
        if (empty($this->cant))
            return null;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'fecha_inicio_plan'=>$row['fecha_inicio_plan'],
                         'id_auditoria'=>$row['id_auditoria'], 'id_auditoria_code'=>$row['id_auditoria_code'],
                         'nombre'=>$row['nombre'], 'fecha_fin_plan'=>$row['fecha_fin_plan'], 'tipo'=>$row['tipo'], 'origen'=>$row['origen'],
                         'toshow'=>$row['toshow'], 'cant_days'=>$row['cant_days'], 'flag'=>0, 'cumplimiento'=>null);
            $this->array_auditorias[$i++]= $array;
        }
        $this->cant= $i;
        return $this->array_auditorias;
    }

    private function get_child_events($sql, $init_array= true) {
        if (isset($this->array_eventos) && $init_array)
            unset($this->array_eventos);

        $result= $this->do_sql_show_error('get_child_events', $sql);
        if (empty($this->cant))
            return null;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'fecha_inicio_plan'=>$row['fecha_inicio_plan'],
                        'id_responsable'=>$row['id_responsable'], 'id_auditoria'=>$row['id_auditoria'], 'id_auditoria_code'=>$row['id_auditoria_code'],
                        'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'], 'id_tipo_reunion'=>$row['id_tipo_reunion'],
                        'id_tipo_reunion_code'=>$row['id_tipo_reunion_code'], 'nombre'=>$row['nombre'], 'fecha_fin_plan'=>$row['fecha_fin_plan'],
                        'tipo'=>$row['tipo'], 'origen'=>$row['origen'], 'periodicidad'=>$row['periodicidad'], 'toshow'=>$row['toshow'],
                        'cant_days'=>$row['cant_days'], 'flag'=>0, 'go_delete'=>0, 'year'=>$row['_year'], 'cumplimiento'=>null, 'rechazado'=>null);
            $this->array_eventos[$i++]= $array;
        }

        $this->cant= $i;
        return $this->array_eventos;
    }

    public function search_date_out_array($inicio, $fin) {
        reset($this->array_eventos);
        $i= 0;
        $inicio= strtotime($inicio);
        $fin= strtotime($fin);

        foreach ($this->array_eventos as $array) {
            if ( strtotime($array['fecha_inicio_plan']) < $inicio || strtotime($array['fecha_inicio_plan']) > $fin)
                return $i;
            ++$i;
        }
        return null;
    }

    public function ifDayEvent($day, $month, $year, $tipo_plan= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
        $found= false;
        $fecha= strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT));

        reset($this->array_eventos);
        foreach ($this->array_eventos as $array) {
            if ($fecha == strtotime(substr($array['fecha_inicio_plan'],0,10))) {
                if (is_null($array['toshow']))
                    continue;
                else {
                    if ($array['toshow'] == _EVENTO_INDIVIDUAL && ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL))
                        continue;
                    if ($array['toshow'] == _EVENTO_MENSUAL && $tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL)
                        continue;
                }

                $row= $this->get_last_reg($array['id'], $array['id_responsable']);

                $found= array();
                $found['cumplimiento']= !empty($row['cumplimiento']) ? $row['cumplimiento'] : _NO_INICIADO;
                $found['aprobado']= !empty($row['aprobado']) ? $row['aprobado'] : null;
                break;
            }
        }
        return $found;
    }

    /**
     * Configura el evento para definor si sera msotrado o no
     */
    private function _update_toshow_procesos($toshow) {
        if (!is_null($toshow)) {
            $sql= "update tproceso_eventos_{$this->year} set toshow= $toshow where 1 ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento = $this->id_evento ";
            if (!empty($this->id_auditoria))
                $sql.= "and id_auditoria = $this->id_auditoria ";
            $result= $this->do_sql_show_error('_update_toshow_procesos', $sql);

            return;
        }

        $sql= "delete from tproceso_eventos_{$this->year} where 1 ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_tarea))
            $sql.= "and id_tarea = $this->id_tarea ";

        $result= $this->do_sql_show_error('_update_toshow_procesos', $sql);
        $cant= $this->cant;

        $field_evento= !empty($this->id_evento) ? "id_evento_code" : null;
        $value_evento= !empty($this->id_evento) ? $this->id_evento_code : null;

        $field_auditoria= !empty($this->id_auditoria) ? "id_auditoria_code" : null;
        $value_auditoria= !empty($this->id_auditoria) ? $this->id_auditoria_code : null;

        $field_tarea= !empty($this->id_tarea) ? "id_tarea_code" : null;
        $value_tarea= !empty($this->id_tarea) ? $this->id_tarea_code : null;

        $this->obj_code->reg_delete('tproceso_eventos', $field_evento, $value_evento, $field_auditoria, $value_auditoria,
                                    $field_tarea, $value_tarea, false);
    }

    private function _update_toshow_usuarios($toshow) {
        if (!is_null($toshow))
            return null;

        $sql= "delete from tusuario_eventos_{$this->year} where 1 ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        $result= $this->do_sql_show_error('_update_toshow', $sql);
        $cant= $this->cant;

        $field_evento= !empty($this->id_evento) ? "id_evento_code" : null;
        $value_evento= !empty($this->id_evento) ? $this->id_evento_code : null;

        $field_auditoria= !empty($this->id_auditoria) ? "id_auditoria_code" : null;
        $value_auditoria= !empty($this->id_auditoria) ? $this->id_auditoria_code : null;

        $field_tarea= !empty($this->id_tarea) ? "id_tarea_code" : null;
        $value_tarea= !empty($this->id_tarea) ? $this->id_tarea_code : null;

        $this->obj_code->reg_delete('tusuario_eventos', $field_evento, $value_evento, $field_auditoria, $value_auditoria,
                                    $field_tarea, $value_tarea, false);
    }

    private function _update_toshow($toshow= 0) {
        $_toshow= setNULL($toshow);

        $sql= null;
        if (!empty($this->id_evento))
            $sql.= "update teventos set toshow= $_toshow where id = $this->id_evento; ";
        if (!empty($this->id_auditoria))
            $sql.= "update tauditorias set toshow= $_toshow where id = $this->id_auditoria; ";
        $result= $this->do_multi_sql_show_error('_update_toshow', $sql);

        if (!empty($this->id_evento))
            $this->id_evento_code= !empty($this->id_evento_code) ?$this->id_evento_code : get_code_from_table("teventos", $this->id_evento);
        if (!empty($this->id_auditoria))
            $this->id_auditoria_code= !empty($this->id_auditoria_code) ?$this->id_auditoria_code : get_code_from_table("tauditorias", $this->id_auditoria);
        if (!empty($this->id_tarea))
            $this->id_tarea_code= !empty($this->id_tarea_code) ?$this->id_tarea_code : get_code_from_table("ttareas", $this->id_tarea);

        $this->_update_toshow_procesos($toshow);
        $this->_update_toshow_usuarios($toshow);
    }

    public function update_tidx($id= null, $tidx= null, $table= "teventos") {
        $table= !empty($table) ? $table : "teventos";
        if ($table == "teventos")
            $id= !empty($id) ? $id : $this->id_evento;
        if ($table == "tauditorias")
            $id= !empty($id) ? $id : $this->id_auditoria;

        $tidx= !is_null($tidx) ? $tidx : $this->tidx;
        $tidx= boolean2pg($tidx);

        $sql= "update $table set tidx = $tidx where id = $id";
        $result= $this->do_sql_show_error('update_tidx', $sql);
    }

    // limpia la tabla treg_evento de un evento en particular
    public function delete_periodic() {
        reset($this->array_eventos);
        $i= 0;
        foreach ($this->array_eventos as $array)  {
            if (is_null($array))
                continue;
            ++$i;
            if ($array['flag'] == 1)
                continue;

            $this->id_tarea= $array['id_tarea'];
            $this->id_tarea_code= $array['id_tarea_code'];
            $this->id_evento= $array['id'];
            $this->id_evento_code= $array['id_code'];
            $this->fecha_inicio_plan= $array['fecha_inicio_plan'];

            if (!is_null($array['toshow']))
                $this->_update_toshow(null);

            $this->go_delete_fix= $array['go_delete'];
            $this->_clean_user_reg($array, null);

            $deleted= false;
            if ($array['go_delete']) {
                // borrar de teventos
                if (!empty($this->id_evento))
                    $deleted= $this->_delete_if_empty($array);
                if (!empty($this->id_auditoria))
                    $deleted= $this->_delete_if_empty_audit($array);
            }

            $this->update_tidx($array['id'], false, "teventos");
        }

        if (!empty($this->id_tarea)) {
            $array= array('id'=>$this->id_tarea, 'id_code'=> $this->id_tarea_code, 'nombre'=> $this->nombre,
                    'fecha_inicio_plan'=>$this->fecha_inicio_plan, 'fecha_fin_plan'=> $this->fecha_fin_plan);
            $this->_delete_if_empty_task($array);
        }

        return $i;
    }

    public function delete_periodic_audit() {
        reset($this->array_auditorias);
        $i= 0;
        foreach ($this->array_auditorias as $array)  {
            if (is_null($array))
                continue;

            ++$i;
            if ($array['flag'] == 1)
                continue;

            $this->id_auditoria= $array['id'];
            $this->fecha_inicio_plan= $array['fecha_inicio_plan'];

            if (!is_null($array['toshow']))
                $this->_update_toshow(null);
            $this->_clean_user_reg($array);

            $deleted= false;
            if ($array['go_delete']) {
                // borrar de teventos
                if (!empty($this->id_evento))
                    $deleted= $this->_delete_if_empty($array);
                if (!empty($this->id_auditoria))
                    $deleted= $this->_delete_if_empty_audit($array);
            }

            $this->update_tidx($array['id'], false, "tauditorias");
        }
        return $i;
    }

    public function if_exists_copyto($year, $copyto= null) {
        $copyto= !empty($copyto) ? $copyto : $this->copyto;
        $id_code= $this->get_ifcopyto($year, $copyto);
        if (empty($id_code))
            return null;

        switch ($this->className) {
            case 'Tevento':
                $table= "teventos";
                break;
            case 'Tauditoria':
                $table= "tauditorias";
                break;
            case 'Ttarea':
                $table= 'ttareas';
                break;
            default :
                $table= "teventos";
        }

        $sql= "select * from $table where id_code = '$id_code'";
        $result= $this->do_sql_show_error('if_exists_copyto', $sql);
        if (empty($this->cant) || $this->cant == -1)
            return null;

        $row= $this->clink->fetch_array($result);
        return array('id'=>$row['id'], 'id_code'=>$row['id_code']);
    }

    public function _delete_if_empty($array= null, $multi_query= false, $if_parent= false, &$observacion= null) {
        global $meeting_array;
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $if_parent= !is_null($if_parent) ? $if_parent : false;
        
        $if_parent= !is_null($if_parent) ? $if_parent : false;
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $id= !empty($array['id']) ? $array['id'] : $this->id_evento;
        $id_code= !empty($array['id_code']) ? $array['id_code'] : $this->id_evento_code;
        $deleted= false;

        $sql= "select * from treg_evento_{$this->year} where id_evento = $id ";
        $this->do_sql_show_error('_delete_if_empty', $sql);
        $cant1= $this->cant;

        $cant2= -1;
        if ($if_parent) {
            $sql= "select * from teventos where id_evento = $id";
            $this->do_sql_show_error('_delete_if_empty', $sql);
            $cant2= $this->cant;
        }

        if (empty($cant1) || empty($cant2)) {
            $sql= "delete from teventos where id = $id; ";
            $result= false;
            if (!$multi_query)
                $result= $this->do_sql_show_error('_delete_if_empty', $sql);

            $observacion= !empty($array['id_tipo_reunion']) ? "{$meeting_array[(int)$array['id_tipo_reunion']]}" : "";
            $observacion.= $array['nombre'];
            $observacion.= "<br />Inicio: {$array['fecha_inicio_plan']} Fin: {$array['fecha_fin_plan']}";
            $this->obj_code->SetObservacion($observacion);

            if (!$multi_query && $result) {
                $this->obj_code->reg_delete('teventos', 'id_code', $id_code, null, null, null, null, false);
                $deleted= true;
            } else {
                $sql.= $this->obj_code->reg_delete('teventos', 'id_code', $id_code, null, null, null, null, true);
            }

            return $multi_query ? $sql : $deleted;
        }

        return null;
    }

    public function _delete_if_empty_task($array= null, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $id= !empty($array['id']) ? $array['id'] : $this->id_tarea;
        $id_code= !empty($array['id_code']) ? $array['id_code'] : $this->id_tarea_code;
        $deleted= false;

        if (empty($id))
            return true;

        $sql= "select * from teventos where id_tarea = $id ";
        $this->do_sql_show_error('_delete_if_empty', $sql);

        if (empty($this->cant)) {
            if (!$multi_query) 
                $sql= null;
            $sql.= "delete from ttareas where id = $id; ";
            if (!$multi_query)
                $result= $this->do_sql_show_error('_delete_if_empty_task', $sql);

            $observacion= $array['nombre'];
            $observacion= "<br />Inicio: {$array['fecha_inicio_plan']} Fin: {$array['fecha_fin_plan']}";
            $this->obj_code->SetObservacion($observacion);

            if (!$multi_query && $result) {
                $this->obj_code->reg_delete('ttareas', 'id_code', $id_code, null, null, null, null, false);
                $deleted= true;
            } else {
                $sql.= $this->obj_code->reg_delete('ttareas', 'id_code', $id_code, null, null, null, null, true);
            }

            return $multi_query ? $sql : $deleted;
        }

        return null;
    }

    /**
     * $parent= false => es una auditoria que deriva de otra. $parent= true => es una auditoria padre
     *
     */
    public function _delete_if_empty_audit($array= null, $parent= false) {
        $id= !empty($array['id']) ? $array['id'] : $this->id_auditoria;
        $id_code= !empty($array['id_code']) ? $array['id_code'] : $this->id_auditoria_code;

        $sql= "select * from tproceso_eventos_$this->year where id_auditoria = $id ";
        $this->do_sql_show_error('_delete_if_empty', $sql);
        $cant_1= $this->cant;

        $cant_2= 0;
        $deleted= false;

        if (!$parent) {
            $sql= "select * from treg_evento_$this->year where id_auditoria = $id ";
            $this->do_sql_show_error('_delete_if_empty_audit', $sql);
            $cant_2= $this->cant;
        }
        if (empty($cant_1) && (!empty($cant_2)) && !$parent) {
            $date= "$this->year-01-01";
            $this->if_tusuarios= false;
            $this->delete_reg($date, $id);

            $sql= "select * from treg_evento_$this->year where id_auditoria = $id ";
            $this->do_sql_show_error('_delete_if_empty_audit', $sql);
            $cant_2= $this->cant;
        }
        if (empty($cant_1) || (empty($cant_2) && !$parent)) {
            $sql= "delete from tauditorias where id = $id ";
            $result= $this->do_sql_show_error('_delete_if_empty_audit', $sql);
            
            if ($result) {
                $deleted= true;;
                $observacion= $array['nombre'];
                $observacion.= "<br />Inicio: {$array['fecha_inicio_plan']} Fin: {$array['fecha_fin_plan']}";
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tauditorias', 'id_code', $id_code, null, null, null, null, false);
            }
        }

        return $deleted;
    }

    public function _delete_reg($id_evento, $id_evento_code, $id_usuario= null, $go_delete= null,
                                                        $id_responsable= null, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        $this->SetIdEvento($id_evento);
        $this->set_id_evento_code($id_evento_code);
        $this->set_id_code($id_evento_code);

        $go_delete= !is_null($go_delete) ? $go_delete : $this->go_delete;
        if (is_null($go_delete))
            $go_delete= _DELETE_YES;

        $this->set_go_delete($go_delete);

        if (!is_null($id_usuario)) {
            $this->SetIdUsuario($id_usuario);
            $_row= $this->getEvento_reg();
            $this->setEvento_reg($_row);

            $sql.= $this->update_reg('delete', $go_delete, $id_responsable, $multi_query);
        }

        if (is_null($id_usuario) && $this->if_tusuarios) {
            $sql_temp= "select * from _tusuarios where marked is NULL ";
            $result= $this->do_sql_show_error('_delete_reg', $sql_temp);

            while ($row= $this->clink->fetch_array($result)) {
                $this->SetIdUsuario($row['id']);
                $_row= $this->getEvento_reg();
                $this->setEvento_reg($_row);

                $sql.= $this->update_reg('delete', $go_delete, $id_responsable, true);
            }
            if (!$multi_query && !empty($sql))
                $result= $this->do_multi_sql_show_error('_delete_reg', $sql);
        }

        return $multi_query ? $sql : null;
    }

    public function delete_reg($date, $id, $id_usuario= null, $flag_date= 0, $ref_code= false,
                                                            $iftarea= false, $fix_user= false) {
        $fix_user= !is_null($fix_user) ? $fix_user : false;
        $sign= empty($flag_date) ? " = " : " >= ";
        $sql= "select id, id_code, id_responsable, nombre, fecha_inicio_plan, fecha_fin_plan, ";
        $sql.= "id_tipo_reunion from teventos where date(fecha_inicio_plan) $sign '$date' ";
        if (!$this->if_auditoria) {
            if (!$iftarea) {
                if ($ref_code)
                    $sql.= "and (id_evento = $id or id = $id) ";
                else
                    $sql.= "and id = $id ";
            } else {
                $sql.= "and id_tarea = $id ";
                $this->id_tarea= $id;
            }
        } else {
            $sql.= "and id_auditoria = $id ";
            $this->id_auditoria= $id;
        }
        $result= $this->do_sql_show_error('delete_reg', $sql);

        $sql_empty= null;
        $obj_tmp= new Ttmp_tables_planning($this->clink);
        $obj_tmp->if_tusuarios= $this->if_tusuarios;
        $obj_tmp->SetIdAuditoria($this->id_auditoria);
        $obj_tmp->array_usuarios_entity= $this->array_usuarios_entity;
        $obj_tmp->SetYear($this->year);

        while ($row= $this->clink->fetch_array($result)) {
            if (!$this->if_tusuarios) {
                $obj_tmp->create_tmp_tusuarios_from_treg_evento($row['id'], $id_usuario);
                $this->if_tusuarios= $obj_tmp->if_tusuarios;
            }

            $id_responsable= $fix_user ? $row['id_responsable'] : null;
            $this->_delete_reg($row['id'], $row['id_code'], $id_usuario, null, $id_responsable); // borrar de treg_evento

            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'],
                'fecha_inicio_plan'=>$row['fecha_inicio_plan'], 'fecha_fin_plan'=>$row['fecha_fin_plan'], 'id_tipo_reunion'=>$row['id_tipo_reunion']);
            $sql= $this->_delete_if_empty($array, true); // borrar si no queda en treg_evento
            if (!is_null($sql))
                $sql_empty.= $sql;
        }
        if (!is_null($sql_empty))
            $this->do_multi_sql_show_error('delete_reg', $sql_empty);
    }

    public function purge_treg_evento() {
        $array= array();

        $sql= "select distinct id_evento, id_usuario, max(cronos) as _cronos from _treg_evento ";
        if (!empty($this->id_usuario))
            $sql.= "where id_usuario = $this->id_usuario ";
        $sql.= "group by id_evento, id_usuario ";
        $result= $this->do_sql_show_error('purge_treg_evento', $sql);

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($array[$row['id_evento']][$row['id_usuario']]))
                continue;
            $array[$row['id_evento']][$row['id_usuario']]= $row['_cronos'];

            ++$i;
            ++$j;
            $sql.= "delete from _treg_evento where id_usuario = ".$row['id_usuario']." and id_evento = ".$row['id_evento'] ;
            $sql.= " and cronos < '{$row['_cronos']}'; ";

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('purge_by_print_reject', $sql);
                $i= 0;
                $sql= null;
            }
        }
        if (!empty($sql))
            $this->do_multi_sql_show_error('purge_by_print_reject', $sql);

        return $j;
    }

    public function getResponsable($id) {
        $teventos= null;
        $plus= null;

        if (!$this->if_auditoria) {
            $teventos= $this->if_teventos ? "_teventos" : "teventos";
            $plus= "funcionario";
        } else {
            $teventos= $this->if_tauditorias ? "_tauditorias" : "tauditorias";
            $plus= "jefe_auditor";
        }

        $sql= "select tusuarios.id as _id, tusuarios.nombre as responsable, email, cargo, $plus ";
        $sql.= "as _funcionario from tusuarios, $teventos where tusuarios.id = $teventos.id_responsable ";
        $sql.= "and $teventos.id = $id";
        $result= $this->do_sql_show_error('Tplanning::getResponsable', $sql);
        $row= $this->clink->fetch_array($result);

        $array= array('id'=>$row['_id'],'nombre'=>stripslashes($row['responsable']), 'email'=>$row['email'],
            'cargo'=>stripslashes($row['cargo']), 'funcionario'=>stripslashes($row['_funcionario']));

        return $array;
    }
}

/*
 * Clases adjuntas o necesarias
 */

include_once "../config.inc.php";
include_once "time.class.php";
include_once "code.class.php";

if (!class_exists('Tproyecto'))
    include_once "proyecto.class.php";
