<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tperspectiva'))
    include_once "perspectiva.class.php";

class Tinductor extends Tperspectiva {

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tperspectiva::__construct($clink);

        $this->className = "Tinductor";
    }

    public function GetNumero() {
        if (!empty($this->numero)) return $this->numero;
        else return $this->find_numero('tinductores');
    }

    public function Set($id = null) {
        if (!empty($id))
            $this->id_inductor = $id;

        $sql = "select * from tinductores where id = $this->id_inductor ";
        $result = $this->do_sql_show_error('Set', $sql);

        if (!$result)
            return $this->error;

        $row = $this->clink->fetch_array($result);

        $this->id_code = $row['id_code'];
        $this->id_inductor_code = $this->id_code;
        $this->numero = $row['numero'];

        $this->id_proceso = $row['id_proceso'];
        $this->id_proceso_code = $row['id_proceso_code'];
        $this->id_perspectiva = $row['id_perspectiva'];
        $this->id_perspectiva_code = $row['id_perspectiva_code'];

        $this->nombre = stripslashes($row['nombre']);
        $this->descripcion = stripslashes($row['descripcion']);
        $this->peso= $row['peso'];
        $this->inicio = $row['inicio'];
        $this->fin = $row['fin'];

        $this->if_send_up = $row['if_send_up'];
        $this->if_send_down = $row['if_send_down'];
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);
        $id_perspectiva = setNULL_empty($this->id_perspectiva);
        $id_perspectiva_code = setNULL_str($this->id_perspectiva_code);
        $peso= setNULL($this->peso);

        $if_send_down = boolean2pg($this->if_send_down);
        $if_send_up = boolean2pg($this->if_send_up);

        $sql = "insert into tinductores (numero, id_perspectiva,id_perspectiva_code, nombre, descripcion, ";
        $sql .= "id_proceso, id_proceso_code, peso, inicio, fin, if_send_up, if_send_down, cronos, situs) values (";
        $sql .= "$this->numero, $id_perspectiva, $id_perspectiva_code, $nombre, $descripcion, $this->id_proceso, ";
        $sql .= "'$this->id_proceso_code', $peso, $this->inicio, $this->fin, $if_send_up, $if_send_down, ";
        $sql.= "'$this->cronos', '$this->location' )";

        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tinductores");
            $this->id_inductor = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tinductores', 'id', 'id_code');

            $this->id_code = $this->obj_code->get_id_code();
            $this->id_inductor_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $id_perspectiva = setNULL_empty($this->id_perspectiva);
        $id_perspectiva_code = !empty($this->id_perspectiva) ? setNULL_str($this->id_perspectiva_code) : 'NULL';
        $peso= setNULL($this->peso);
        $if_send_down = boolean2pg($this->if_send_down);
        $if_send_up = boolean2pg($this->if_send_up);

        $sql = "update tinductores set numero= $this->numero, nombre= $nombre, descripcion= $descripcion, ";
        $sql .= "inicio= $this->inicio, fin= $this->fin, id_proceso= $this->id_proceso, ";
        $sql .= "id_proceso_code= '$this->id_proceso_code', if_send_up= $if_send_up, if_send_down= $if_send_down, ";
        $sql .= "cronos= '$this->cronos', situs= '$this->location', id_perspectiva= $id_perspectiva, ";
        $sql .= "id_perspectiva_code= $id_perspectiva_code, peso= $peso where id = $this->id_inductor ";

        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar($with_null_perspectiva = _PERSPECTIVA_ALL) {
        $this->cant = 0;
        $with_null_perspectiva = is_null($with_null_perspectiva) ? _PERSPECTIVA_ALL : $with_null_perspectiva;

        $sql = "select distinct tinductores.*, tinductores.id as _id, tinductores.id as id_inductor, tinductores.id_code as _id_code, ";
        $sql .= "tinductores.inicio as _inicio, tinductores.fin as _fin, tinductores.nombre as _nombre, tinductores.numero as _numero, ";
        $sql .= "tinductores.descripcion as _descripcion, tinductores.id_proceso as _id_proceso, ";
        $sql .= "tinductores.id_proceso_code as _id_proceso_code from tinductores ";
        if ($with_null_perspectiva == _PERSPECTIVA_NOT_NULL)
            $sql .= ", tperspectivas ";
        $sql .= "where 1 ";
        if (!empty($this->id_proceso))
            $sql .= "and tinductores.id_proceso = $this->id_proceso ";
        if (!empty($this->year))
            $sql .= "and (tinductores.inicio <= $this->year and tinductores.fin >= $this->year) ";
        if (!empty($this->inicio))
            $sql .= "and (tinductores.inicio <= $this->fin and tinductores.fin >= $this->inicio) ";
        if ($with_null_perspectiva == _PERSPECTIVA_NULL)
            $sql .= "and id_perspectiva is null ";
        if ($with_null_perspectiva == _PERSPECTIVA_NOT_NULL) {
            $sql .= "and (tinductores.id_perspectiva = tperspectivas.id ";
            if (!empty($this->id_perspectiva))
                $sql .= "and id_perspectiva = $this->id_perspectiva) ";
            else {
                $sql .= ") ";
                if (!empty($this->year))
                    $sql .= "and (tperspectivas.inicio >= $this->year or tperspectivas.fin <= $this->year)) ";
            }
        }
        $sql.= "order by tinductores.inicio asc, _numero asc ";

        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar($radio_date = null) {
        $error = null;
        $year = ($radio_date == 2) ? $this->inicio : $this->year;

        $obj_ref = new Treference($this->clink);
        $obj_ref->empty_tref_indicadores($this->id_inductor, null, $year);
        $obj_ref->empty_tobjetivo_inductores();
        $obj_ref->empty_tinductor_riesgos();
        $obj_ref->empty_tinductor_eventos();

        if ($radio_date == 2 || $year == $this->inicio) {
            $obj_ref->empty_tref_indicadores($this->id_inductor, null, $year, false);
            $obj_ref->empty_tobjetivo_inductores();
            $obj_ref->empty_tinductor_riesgos();
            $obj_ref->empty_tinductor_eventos();

            $sql = "delete from tinductores where id = $this->id_inductor ";
            $result = $this->do_sql_show_error('eliminar', $sql);

            if (!$result) {
                $error = "ERROR: Este inductor contiene indicadores. Para borrar el inductor, este no debe tener indicadores asignados. ";
                $error .= "Vacie el inductor, desde la lista de indicadores y despues intente borrarlo nuevamente.";
            }
        } else {
            $year = $this->year - 1;
            $sql = "update tinductores set fin= $year where id = $this->id_inductor ";
            $this->do_sql_show_error('eliminar', $sql);
        }

        return $error;
    }

    public function if_exist_ref($year, $id_objetivo) {
        $sql = "select * from tobjetivo_inductores where id_inductor = $this->id_inductor and year = $year ";
        if (!empty($id_objetivo))
            $sql .= "and id_objetivo = $id_objetivo ";
        $result= $this->do_sql_show_error('if_exist_ref', $sql);
        return $this->cant;
    }

    public function update_ref($year, $id_objetivo, $id_inductor = null) {
        $peso = setNULL($this->peso);
        if (is_null($id_inductor))
            $id_inductor = $this->id_inductor;

        $sql = "update tobjetivo_inductores set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
        $sql .= "where id_inductor = $id_inductor and id_objetivo = $id_objetivo ";
        $sql .= "and year = $year";
        $result= $this->do_sql_show_error('update_ref', $sql);
        $this->cant = $this->clink->affected_rows();

        if (is_null($this->error)) {
            $obj_ref = new Treference($this->clink);
            $obj_ref->set_calcular_year_objetivo($id_objetivo, $year);
        }

        return $this->error;
    }

    public function add_ref($year, $id_objetivo, $id_objetivo_code) {
        $peso = setNULL($this->peso);

        $sql = "insert into tobjetivo_inductores (id_inductor, id_inductor_code, peso, year, ";
        $sql .= " id_objetivo, id_objetivo_code, cronos, situs) values ($this->id_inductor, '$this->id_inductor_code', ";
        $sql .= "$peso, $year, $id_objetivo, '$id_objetivo_code', '$this->cronos', '$this->location') ";
        $result= $this->do_sql_show_error('add_ref', $sql);

        if (is_null($this->error)) {
            $obj_ref = new Treference($this->clink);
            $obj_ref->set_calcular_year_objetivo($id_objetivo, $year);
        }

        return $this->error;
    }

    public function expand_period_ref($id_objetivo) {
        $obj_obj = new Tobjetivo($this->clink);
        $obj_obj->Set($id_objetivo);
        $id_objetivo_code = $obj_obj->get_id_code();

        $inicio = max($this->inicio, $obj_obj->GetInicio());
        $fin = min($this->fin, $obj_obj->GetFin());

        for ($year = $inicio; $year <= $fin; $year++) {
            if ($this->if_exist_ref($year, $id_objetivo) && $year < $this->year)
                continue;

            $this->update_ref($year, $id_objetivo);

            if (!is_null($this->error) || $this->cant == 0)
                $this->add_ref($year, $id_objetivo, $id_objetivo_code);
        }

        // borra hacia el futuro
        if ($fin < $this->fin || $fin < $obj_obj->GetFin()) {
            $_year = ++$fin;
            $fin = max($this->fin, $obj_obj->GetFin());

            for ($year = $_year; $year <= $fin; $year++) {
                if ($year > $this->year)
                    $this->delete_ref($year, $id_objetivo);
            }
        }
    }

    protected function delete_ref($year, $id_objetivo = null) {
        $id_objetivo = !is_null($id_objetivo) ? $id_objetivo : $this->id_objetivo;

        $sql = "delete from tobjetivo_inductores where id_inductor = $this->id_inductor ";
        $sql .= "and (year = $year ";
        if ((int) $year == (int) date('Y', strtotime($this->cronos)))
            $sql .= "or year > $year ";
        $sql .= ") ";

        if (!empty($id_objetivo))
            $sql .= "and id_objetivo = $id_objetivo ";

        $this->do_sql_show_error('delete_ref', $sql);

        if (is_null($this->error)) {
            $obj_ref = new Treference($this->clink);
            $obj_ref->set_calcular_year_objetivo($id_objetivo, $year);
        }
    }

    public function delete_period_ref($id_objetivo) {
        $id_objetivo = !is_null($id_objetivo) ? $id_objetivo : $this->id_objetivo;

        for ($year = $this->inicio; $year <= $this->fin; $year++) {
            if ($this->if_exist_ref($year, $id_objetivo) && $year < $this->year)
                continue;
            $this->delete_ref($year, $id_objetivo);
        }
    }

    public function set_perspectiva($fix = null) {
        $id_perspectiva = $fix ? setNull($this->id_perspectiva) : setNULL(null);
        $id_perspectiva_code = $fix ? setNULL_str($this->id_perspectiva_code) : setNULL_str(null);

        $sql = "update tinductores set id_perspectiva= $id_perspectiva, id_perspectiva_code= $id_perspectiva_code, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' where id= $this->id_inductor ";
        $result= $this->do_sql_show_error('set_perspectiva', $sql);
        return !$result ? $this->error : null;
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Treference'))
    include_once "reference.class.php";