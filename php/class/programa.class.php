<?php

include_once "base.class.php";

class Tprograma extends Tbase {

    public function __construct($clink= null) {
         Tbase::__construct($clink);
         $this->clink= $clink;

         $this->className= "Tprograma";
         $this->obj_code= new Tcode($clink);
     }

    public function Set($id= null) {
        if (!empty($id))
            $this->id_programa= $id;

        $sql= "select * from tprogramas where id = $this->id_programa ";
        $result= $this->do_sql_show_error('Set', $sql);

        if (!$result)
            return $this->error;

        $row= $this->clink->fetch_array($result);
        $this->id_code= $row['id_code'];
        $this->id_programa_code= $this->id_code;

        $this->id_proceso= $row['id_proceso'];
        $this->id_proceso_code= $row['id_proceso_code'];

        $this->nombre= stripslashes($row['nombre']);
        $this->descripcion= stripslashes($row['descripcion']);

        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "insert into tprogramas (nombre, descripcion, ";
        $sql .= "id_proceso, id_proceso_code, inicio, fin, cronos, situs) values ( ";
        $sql .= "$nombre, $descripcion, $this->id_proceso, ";
        $sql .= "'$this->id_proceso_code', $this->inicio, $this->fin, '$this->cronos', '$this->location' )";

        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tprogramas");
            $this->id_programa = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tprogramas', 'id', 'id_code');

            $this->id_code = $this->obj_code->get_id_code();
            $this->id_programa_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "update tprogramas set nombre= $nombre, descripcion= $descripcion, inicio= $this->inicio, fin= $this->fin, ";
        $sql .= "id_proceso= $this->id_proceso, id_proceso_code= '$this->id_proceso_code', ";
        $sql .= "situs= '$this->location', cronos= '$this->cronos' where id = $this->id_programa ";

        $this->do_sql_show_error('add', $sql);

        return $this->error;
    }

    public function listar($local = _NO_LOCAL) {
        $sql = "select distinct tprogramas.*, tprogramas.id as _id, tprogramas.id as id_programa, id_code as _id_code, inicio as _inicio, ";
        $sql .= "fin as _fin, nombre as _nombre, descripcion as _descripcion, tprogramas.id_proceso as _id_proceso, ";
        $sql .= "tprogramas.id_proceso_code as _id_proceso_code from tprogramas ";
        $sql .= ($local == _NO_LOCAL) ? "where 1 " : ", tproceso_proyectos where tprogramas.id = tproceso_proyectos.id_programa ";
        if (!empty($this->id_proceso))
            $sql .= ($local == _NO_LOCAL) ? "and id_proceso = $this->id_proceso " : "and tproceso_proyectos.id_proceso = $this->id_proceso ";
        if (!empty($this->year))
            $sql .= "and (inicio <= $this->year and fin >= $this->year) ";
        if (!empty($this->inicio))
            $sql .= "and (inicio <= $this->fin and fin >= $this->inicio) ";
        $sql .= "order by inicio asc";

        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar($radio_date = null) {
        $error = null;
        $year = ($radio_date == 2) ? $this->inicio : $this->year;

        $obj = new Treference($this->clink);
        $obj->empty_tref_programas($this->id_programa, null, $year);

        if ($radio_date == 2 || $year == $this->inicio) {
            $obj->empty_tref_programas($this->id_programa, null, $year, false);

            $sql = "delete from tprogramas where id = $this->id_programa ";
            $result = $this->do_sql_show_error('eliminar', $sql);

            if (!$result) {
                $error = "ERROR: Este programa contiene indicadores. Para borrar el programa, este no debe tener indicadores asignados. ";
                $error .= "Vacie el programa, desde la lista de programas y despues intente borrarlo nuevamente.";
            }
        } else {
            $year = $this->year - 1;
            $sql = "update tprogramas set fin= $year where id = $this->id_programa ";
            $this->do_sql_show_error('eliminar', $sql);
        }

        return $error;
    }

    public function listar_indicadores($id_programa= null, $id_inductor = null) {
        $id_programa= !empty($id_programa) ? $id_programa : $this->id_programa;

        $sql = "select distinct tindicadores.*, tindicadores.id as _id, tindicadores.id_code as _id_code, tref_programas.peso as _peso ";
        $sql .= "from tindicadores, tref_programas ";
        if (!empty($id_inductor))
            $sql .= ", tref_indicadores ";
        $sql .= "where tref_programas.year = $this->year and tindicadores.id = tref_programas.id_indicador ";
        if (!empty($id_inductor)) {
            $sql .= "and (tindicadores.id = tref_indicadores.id_indicador and tref_indicadores.id_inductor = $id_inductor ";
            $sql .= "and tref_indicadores.year = $this->year) ";
        }
        if (!empty($id_programa))
            $sql .= "and id_programa = $id_programa ";

        $result = $this->do_sql_show_error('listar_indicadores', $sql);
        return $result;
    }

    public function if_exist_ref($year, $id_indicador) {
        $sql= "select * from tref_programas where id_programa = $this->id_programa and year = $year ";
        if (!empty($id_indicador)) $sql.= "and id_indicador = $id_indicador ";

        $result= $this->do_sql_show_error('if_exist_ref', $sql);
        return $this->cant;
    }

    public function update_ref($year, $id_indicador, $id_programa= null) {
        $peso= setNULL($this->peso);
        if (is_null($id_programa)) $id_programa= $this->id_programa;

        $sql= "update tref_programas set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_programa = $id_programa and id_indicador = $id_indicador and year = $year ";

        $this->do_sql_show_error('update_ref', $sql);
        $this->cant= $this->clink->affected_rows();

        if (is_null($this->error)) {
            $obj_peso= new Tpeso($this->clink);
            $obj_peso->set_calcular_year_programa($this->id_programa, $year);
        }

        return $this->error;
    }

    public function add_ref($year, $id_indicador, $id_indicador_code) {
        $peso= setNULL($this->peso);

        $sql= "insert into tref_programas (id_programa, id_programa_code, peso, year, id_indicador, id_indicador_code, ";
        $sql.= "cronos, situs) values ($this->id_programa, '$this->id_programa_code', $peso, $year, $id_indicador, ";
        $sql.= "'$id_indicador_code', '$this->cronos', '$this->location') ";

        $this->do_sql_show_error('add_ref', $sql);

        if (is_null($this->error)) {
            $obj_peso= new Tpeso($this->clink);
            $obj_peso->set_calcular_year_programa($this->id_programa, $year);
        }

        return $this->error;
    }

    public function expand_period_ref($id_indicador) {
        $obj_obj= new Tindicador($this->clink);
        $obj_obj->SetYear($this->year);
        $obj_obj->Set($id_indicador);
        $id_indicador_code= $obj_obj->get_id_code();

        $inicio= max($this->inicio, $obj_obj->GetInicio());
        $fin= min($this->fin, $obj_obj->GetFin());

        for ($year= $inicio; $year <= $fin; $year++) {
            if ($this->if_exist_ref($year, $id_indicador) && $year < $this->year)
                continue;

            $this->update_ref($year, $id_indicador);

            if (!is_null($this->error) || $this->cant == 0)
                $this->add_ref($year, $id_indicador, $id_indicador_code);
        }

        // borra hacia el futuro
        if ($fin < $this->fin || $fin < $obj_obj->GetFin()) {
            $_year= ++$fin;
            $fin= max($this->fin, $obj_obj->GetFin());

            for ($year= $_year; $year <= $fin; $year++) {
                if ($year > $this->year) $this->delete_ref($year, $id_indicador);
            }
        }
    }

    protected function delete_ref($year, $id_indicador= null) {
        $id_indicador= !is_null($id_indicador) ? $id_indicador : $this->id_indicador;

        $sql= "delete from tref_programas where id_programa = $this->id_programa and (year = $year ";
        if ((int)$year == (int)date('Y', strtotime($this->cronos)))
                $sql.= "or year > $year ";
        $sql.= ") ";
        if (!empty($id_indicador))
            $sql.= "and id_indicador = $id_indicador ";

        if (is_null($this->error)) {
            $obj_peso= new Tpeso($this->clink);
            $obj_peso->set_calcular_year_programa($this->id_programa, $year);
        }

        $this->do_sql_show_error('delete_ref', $sql);
    }

    public function delete_period_ref($id_indicador) {
        $id_indicador= !is_null($id_indicador) ? $id_indicador : $this->id_indicador;

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            if ($this->if_exist_ref($year, $id_indicador) && $year < $this->year)
                continue;
            $this->delete_ref($year, $id_indicador);
        }
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";
include_once "escenario.class.php";