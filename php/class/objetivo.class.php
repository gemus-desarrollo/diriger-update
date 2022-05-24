<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tescenario'))
    include_once "escenario.class.php";

class Tobjetivo extends Tescenario {
    protected $if_ci; // si se trata de objetivos de control interno

    protected $if_send_up,
            $if_send_down;

    public function __construct($clink= null) {
         $this->clink= $clink;
         Tescenario::__construct($clink);

         $this->if_ci= false;
         $this->if_objsup= false;

         $this->className= "Tobjetivo";
     }

    public function SetIfControlInterno($id = 1) {
        $this->if_ci = empty($id) ? 0 : 1;
    }
    public function GetIfControlInterno() {
        return $this->if_ci;
    }
    public function SetIfObjetivoSup($id = 1) {
        $this->if_objsup = empty($id) ? 0 : 1;
    }
    public function GetIfObjetivoSup() {
        return $this->if_objsup;
    }
    public function SetIfSend_up($id = 1) {
        $this->if_send_up = !empty($id) ? 1 : 0;
    }
    public function GetIfSend_up() {
        return $this->if_send_up;
    }
    public function SetIfSend_down($id = 1) {
        $this->if_send_down = !empty($id) ? 1 : 0;
    }
    public function GetIfSend_down() {
        return $this->if_send_down;
    }

    public function GetNumero() {
        if (!empty($this->numero))
            return $this->numero;
        else
            return $this->find_numero('tobjetivos');
    }
    public function SetNumero($id) {
        $this->numero = $id;
    }

    protected function find_numero($table= 'tobjetivos') {
        return Tunidad::find_numero($table);
    }

    public function Set($id= null) {
        if (!empty($id)) $this->id_objetivo= $id;

        $sql= "select * from tobjetivos where id = $this->id_objetivo ";
        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->if_ci= boolean($row['if_control_interno']);

            $this->id_code= $row['id_code'];
            $this->id_objetivo_code= $this->id_code;

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

            $this->nombre= stripslashes($row['objetivo']);
            $this->descripcion= stripslashes($row['estrategia']);
            $this->inicio= $row['inicio'];
            $this->fin= $row['fin'];

            $this->numero= $row['numero'];
            $this->if_send_up= boolean($row['if_send_up']);
            $this->if_send_down= boolean($row['if_send_down']);
        }

        return $this->error;
    }

    public function add() {
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $if_ci= boolean2pg($this->if_ci);

        $if_send_up= boolean2pg($this->if_send_up);
        $if_send_down= boolean2pg($this->if_send_down);

        $sql= "insert into tobjetivos (numero, id_proceso, id_proceso_code, objetivo, estrategia, ";
        $sql.= "inicio, fin, if_send_up, if_send_down, cronos, situs, if_control_interno) values ($this->numero, ";
        $sql.= "$this->id_proceso, '$this->id_proceso_code',$nombre, $descripcion, $this->inicio, ";
        $sql.= "$this->fin, $if_send_up, $if_send_down, '$this->cronos', '$this->location', $if_ci) ";
        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_objetivo= $this->clink->inserted_id("tobjetivos");
            $this->id= $this->id_objetivo;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tobjetivos','id','id_code');
            $this->id_code= $this->obj_code->get_id_code();
            $this->id_objetivo_code= $this->id_code;
       }

        return $this->error;
     }

    public function update() {
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);

        $if_send_down= boolean2pg($this->if_send_down);
        $if_send_up= boolean2pg($this->if_send_up);

        $sql= "update tobjetivos set numero= $this->numero, objetivo= $nombre, estrategia= $descripcion, inicio= $this->inicio, ";
        $sql.= "fin= $this->fin, if_send_up= $if_send_up, if_send_down= $if_send_down, cronos= '$this->cronos', situs= '$this->location' ";
        if (!$this->if_ci)
            $sql.= ", id_proceso= $this->id_proceso, id_proceso_code= '$this->id_proceso_code' ";
        $sql.= "where id = $this->id_objetivo ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar() {
        $if_ci= boolean2pg($this->if_ci);

        $sql= "select distinct tobjetivos.*, tobjetivos.id as _id, tobjetivos.id_code as _id_code, tobjetivos.objetivo as _nombre, ";
        $sql.= "tobjetivos.numero as _numero, tobjetivos.inicio as _inicio, tobjetivos.fin as _fin, tprocesos.tipo as _tipo ";
        $sql.= "from tobjetivos, tprocesos ";
        if (!empty($this->id_proceso) && $this->if_ci)
            $sql.= ", tproceso_objetivos ";
        $sql.= "where if_control_interno = $if_ci ";
        if (!empty($this->id_proceso)) {
            if ($this->if_ci) {
                $sql.= "and tobjetivos.id = tproceso_objetivos.id_objetivo and tprocesos.id = tproceso_objetivos.id_proceso ";
                $sql.= "and tproceso_objetivos.id_proceso = $this->id_proceso ";
            } else
                $sql .= "and tobjetivos.id_proceso = tprocesos.id and tobjetivos.id_proceso = $this->id_proceso  ";
        }
        if (!empty($this->year))
            $sql.= "and ((tobjetivos.inicio <= $this->year and tobjetivos.fin >= $this->year) or (tobjetivos.inicio is null and tobjetivos.fin is null)) ";
        if (!empty($this->inicio) && !empty($this->fin))
            $sql.= "and (tobjetivos.fin >= $this->inicio and tobjetivos.inicio <= $this->fin) ";
        $sql.= "order by tipo asc, _numero asc, tobjetivos.inicio asc ";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function listar_restrict_id($id_objetivo= null) {
        $if_ci= boolean2pg($this->if_ci);

        $sql= "select distinct tobjetivos.*, tobjetivos.id as _id, tobjetivos.id_code as _id_code, tobjetivos.objetivo as _nombre, ";
        $sql.= "tobjetivos.numero as _numero, tobjetivos.inicio as _inicio, tobjetivos.fin as _fin ";
        $sql.= "from tobjetivos where if_control_interno = $if_ci ";
        if (!empty($id_objetivo))
            $sql.= "and id = $id_objetivo";
        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar($radio_date= null) {
        $error= null;
        $year= ($radio_date == 2) ? $this->inicio : $this->year;

        $obj_ref= new Treference($this->clink);
        $obj_ref->empty_tobjetivo_inductores($this->id_objetivo, null, $year);
        $obj_ref->empty_tpolitica_objetivos();

        if ($radio_date == 2 || $year == $this->inicio) {
            $obj_ref->empty_tobjetivo_inductores($this->id_objetivo, null, $year, false);
            $obj_ref->empty_tpolitica_objetivos();

            $sql= "delete from tobjetivos where id = $this->id_objetivo";
            $result = $this->do_sql_show_error('eliminar', $sql);

            if (!$result) {
                $error = "ERROR: Este objetivo contiene inductores. Para borrar el objetivo, este no debe tener inductores asignados. ";
                $error .= "Vacie el objetivo, desde la lista de objetivos y despues intente borrarlo nuevamente.";
            }
        } else {
            $year= $this->year-1;
            $sql= "update tobjetivos set fin= $year where id = $this->id_objetivo ";
            $this->do_sql_show_error('eliminar', $sql);
        }

        return $error;
    }

    public function get_politicas($id= null) {
        if (is_null($id)) $id= $this->id_objetivo;

        $array_politicas= null;

        $sql= "select distinct tpoliticas.*, tpoliticas.id as _id, peso from tpoliticas, tpolitica_objetivos ";
        $sql.= "where tpoliticas.id = id_politica and tpolitica_objetivos.id_objetivo = $id ";
        if (!empty($this->id_proceso))
            $sql.= "and tpolitica_objetivos.id_proceso = $this->id_proceso ";
        $result= $this->do_sql_show_error('get_politicas', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('numero'=>$row['numero'], 'peso'=>$row['peso']);
            $array_politicas[$row['_id']]= $array;
        }

        return $array_politicas;
    }

    /**
     * actualizar la relacion de los objetivos con las politicas y los objetivos superiores cuando el objetivo se cambia de proceso
     * @param $id_proceso_old
     * @param $id_proceso_new
     * @param $year
     */
    public function update_proceso_ref($id_proceso_old, $id_proceso_new, $year) {
        $id_proceso_new_code= get_code_from_table('tprocesos', $id_proceso_new);

        $sql= "update tpolitica_objetivos set id_proceso= $id_proceso_new, id_proceso_code= '$id_proceso_new_code' ";
        $sql.= "where id_proceso = $id_proceso_old and id_objetivo = $this->id_objetivo and year > $year ";
        if (!empty($this->id_objetivo_sup))
            $sql.= "and id_objetivo_sup = $this->id_objetivo_sup";
        if (!empty($this->id_politica))
            $sql.= "and id_politica = $this->id_politica ";
        $result= $this->do_sql_show_error('update_proceso_ref', $sql);
    }

    public function if_exist_ref($year, $id) {
        $sql= "select * from tpolitica_objetivos where id_objetivo = $this->id_objetivo and id_proceso = $this->id_proceso ";
        if (!empty($id) && !$this->if_objsup)
            $sql.= "and id_politica = $id ";
        if (!empty($id) && $this->if_objsup)
            $sql.= "and id_objetivo_sup = $id ";
        $sql.= "and year = $year ";

        $result= $this->do_sql_show_error('if_exist_ref', $sql);
        return $this->cant;
    }

    public function update_ref($year, $id) {
        $peso= setNULL($this->peso);

        $sql= "update tpolitica_objetivos set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_objetivo = $this->id_objetivo and year = $year and id_proceso = $this->id_proceso ";
        $sql.= !$this->if_objsup ? "and id_politica = $id " : "and id_objetivo_sup = $id ";

        $result= $this->do_sql_show_error('update_ref', $sql);
        $this->cant= $this->clink->affected_rows();

        if (is_null($this->error) && $this->cant > 0) {
            $obj_ref= new Treference($this->clink);
            $obj_ref->SetIdProceso($this->id_proceso);

            if ($this->if_objsup)
                $obj_ref->set_calcular_year_objetivo($id, $year);
            else
                $obj_ref->set_calcular_year_politica($id, $year);
        }
        return $this->error;
    }

    public function add_ref($year, $id, $id_code) {
        $peso= setNULL($this->peso);

        $sql= "insert into tpolitica_objetivos (id_objetivo, id_objetivo_code, id_proceso, id_proceso_code, peso, year, ";
        if ($this->if_objsup)
            $sql.= "id_objetivo_sup, id_objetivo_sup_code, ";
        else
            $sql.= "id_politica, id_politica_code, ";
        $sql.= "cronos, situs) values ($this->id_objetivo, '$this->id_objetivo_code', $this->id_proceso, '$this->id_proceso_code', ";
        $sql.= "$peso, $year, $id, '$id_code', '$this->cronos', '$this->location') ";

        $result= $this->do_sql_show_error('add_ref', $sql);

        if (is_null($this->error)) {
            $obj_ref= new Treference($this->clink);
            if ($this->if_objsup) 
                $obj_ref->set_calcular_year_objetivo($id, $year);
            else 
                $obj_ref->set_calcular_year_politica($id, $year);
        }

        return $this->error;
    }

    public function expand_period_ref($id_politica= null, $id_objetivo_sup= null) {
        if (empty($id_objetivo_sup) && empty($id_politica))
            die("El campo 'id_politica' e 'id_objetivo_sup' estan vacio funcion:expand_period_ref");

        $this->if_objsup= (empty($id_objetivo_sup) && !empty($id_politica)) ? false : true;

        $id_politica= $this->if_objsup ? $id_objetivo_sup : $id_politica;

        $obj_obj= $this->if_objsup ? new Tobjetivo($this->clink) : new Tpolitica($this->clink);
        $obj_obj->Set($id_politica);
        $id_politica_code= $obj_obj->get_id_code();

        $inicio= max($this->inicio, $obj_obj->GetInicio(), $_SESSION['inicio']);
        $fin= min($this->fin, $obj_obj->GetFin(), $_SESSION['fin']);

        for ($year= $inicio; $year <= $fin; $year++) {
            if ($this->if_exist_ref($year, $id_politica) && $year < $this->year)
                continue;
            $this->update_ref($year, $id_politica, $id_politica_code);

            if (!is_null($this->error) || ($this->cant == 0 || $this->cant == -1))
                $this->add_ref($year, $id_politica, $id_politica_code);
        }

        // borra hacia el futuro
        if ($fin < $this->fin || $fin < $obj_obj->GetFin()) {
            $_year= ++$fin;
            $fin= max($this->fin, $obj_obj->GetFin());

            for ($year= $_year; $year <= $fin; $year++) {
                if ($year > $this->year)
                    $this->delete_ref($year, $id_politica);
            }
        }
    }

    protected function delete_ref($year, $id= null) {
        if ($this->if_objsup) $id= empty($id) ? $this->id_objetivo_sup : $id;
        if (!$this->if_objsup) $id= empty($id) ? $this->id_politica : $id;

        $sql= "delete from tpolitica_objetivos where id_objetivo = $this->id_objetivo and id_proceso = $this->id_proceso and (year = $year ";
        if ((int)$year == (int)date('Y', strtotime($this->cronos)))
                $sql.= "or year > $year ";
        $sql.= ") ";
        if (!empty($id) && !$this->if_objsup)
            $sql.= "and id_politica = $id ";
        if (!empty($id) && $this->if_objsup)
            $sql.= "and id_objetivo_sup = $id ";

        $result= $this->do_sql_show_error('delete_ref', $sql);

        if (is_null($this->error)) {
            $obj_ref= new Treference($this->clink);
            if ($this->if_objsup)
                $obj_ref->set_calcular_year_objetivo($id, $year);
            else
                $obj_ref->set_calcular_year_politica($id, $year);
        }
    }

    public function delete_period_ref($id) {
        if ($this->if_objsup)
            $id= empty($id) ? $this->id_objetivo_sup : $id;
        if (!$this->if_objsup)
            $id= empty($id) ? $this->id_politica : $id;

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            if ($this->if_exist_ref($year, $id) && $year < $this->year)
                continue;
            $this->delete_ref($year, $id);
        }
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Treference'))
    include_once "reference.class.php";
if (!class_exists('Tpolitica'))
    include_once "politica.class.php";