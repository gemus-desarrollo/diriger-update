<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../config.inc.php";
include_once "code.class.php";
include_once "unidad.class.php";

class Tindicador extends Tunidad {
    protected $calculo;
    protected $cumulative;
    protected $formulated;
    protected $chk_cumulative;

    protected $id_user_real,
            $id_user_plan;
    private $inicio_origen,
            $fin_origen;

    protected $id_proceso_ref,
            $id_proceso_ref_code;

    protected $array_formulated;
    protected $trend;
    protected $criterio;
    protected $_yellow_cot;
    protected $_orange_cot;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tunidad::__construct($this->clink);

        $this->trend= null;

        $this->_yellow= _YELLOW;
        $this->_orange= _ORANGE;
        $this->_green= _GREEN;
        $this->_aqua= _AQUA;
        $this->_blue= _BLUE;

        $this->_yellow_cot= _YELLOW;
        $this->_orange_cot= _ORANGE;

        $this->id_indicador= null;
        $this->init();

        $this->className= "Tindicador";
    }

    public function set_orange_cot($id) {
        $this->_orange_cot = $id;
    }
    public function set_yellow_cot($id) {
        $this->_yellow_cot = $id;
    }
    public function SetFormCalculo($id) {
        $this->calculo = $id;
    }
    public function GetFormCalculo() {
        return $this->calculo;
    }
    public function SetIdUsrReal($id) {
        $this->id_user_real = $id;
    }
    public function SetIdUsrPlan($id) {
        $this->id_user_plan = $id;
    }
    public function SetIfCumulative($id = true) {
        $this->cumulative = $id;
    }
    public function GetIfCumulative() {
        return $this->cumulative;
    }
    public function SetChkCumulative($id) {
        $this->chk_cumulative= $id;
    }
    public function GetChkCumulative() {
        return $this->chk_cumulative;
    } 
    public function GetIdUserReal() {
        return $this->id_user_real;
    }
    public function GetIdUserPlan() {
        return $this->id_user_plan;
    }
    public function GetIndicio_origen() {
        return $this->inicio_origen;
    }
    public function GetFin_origen() {
        return $this->fin_origen;
    }
    public function SetTrend($id) {
        $this->trend = $id;
    }
    public function GetTrend() {
        return $this->trend;
    }
    public function GetCriterio() {
        return $this->criterio;
    }
    public function get_orange_cot() {
        return $this->_orange_cot;
    }
    public function get_yellow_cot() {
        return $this->_yellow_cot;
    }
    public function GetIdProceso_ref() {
        return $this->id_proceso_ref;
    }
    public function get_id_proceso_ref_code() {
        return $this->id_proceso_ref_code;
    }
    public function SetIfFormulated($id = true) {
        $this->formulated = $id;
    }
    public function GetIfFormulated() {
        return $this->formulated;
    }

    private function init() {
        $this->id_code= null;
        $this->id_indicador_code= null;

        $this->id_proceso_ref= null;
        $this->id_proceso_ref_code= null;

        $this->nombre= null;
        $this->descripcion= null;
        $this->calculo= null;
        $this->cumulative= null;
        $this->chk_cumulative= null;
        $this->formulated= null;
    }

    public function GetNumero() {
        if (!empty($this->numero))
            return $this->numero;
        else
            return $this->find_numero('tindicadores');
    }

    public function Set($id= null, $id_code= null) {
       $this->init();

        if (!empty($id))
            $this->id_indicador= $id;
        if (!empty($id_code))
            $this->id_indicador_code= $id_code;

        $sql= "select * from tindicadores ";
        if (!empty($id))
            $sql.= "where tindicadores.id = $this->id_indicador ";
        elseif (!empty($id_code))
            $sql.= "where tindicadores.id_code = '$this->id_indicador_code' ";
        else
            $sql.= "where tindicadores.id = $this->id_indicador ";
        if (!empty($this->year))
            $sql.= "and (inicio <= $this->year and $this->year <= fin) ";
        if ((empty($id) && empty($id_code)) && !empty($this->id_proceso))
            $sql.= "and tindicadores.id_proceso = $this->id_proceso ";

        $result= $this->do_sql_show_error('Set', $sql);
        if (!is_null($this->error))
            return $this->error;

        $row= $this->clink->fetch_array($result);
        if ($this->cant == 0)
            return null;

        $this->id_code= $row['id_code'];
        $this->id_indicador_code= $this->id_code;
        $this->id_proceso_ref= $row['id_proceso'];
        $this->id_proceso_ref_code= $row['id_proceso_code'];
        $this->id_proceso= $this->id_proceso_ref;
        $this->id_proceso_code= $this->id_proceso_ref_code;

        $this->id_unidad= $row['id_unidad'];
        $this->id_unidad_code= $row['id_unidad_code'];

        $this->ind_definido= boolean($row['ind_definido']);

        $this->nombre= stripslashes($row['nombre']);
        $this->numero= $row['numero'];
        $this->descripcion= stripslashes($row['descripcion']);
        $this->calculo= stripslashes($row['calculo']);
        $this->cumulative= boolean($row['cumulative']);
        $this->formulated= boolean($row['formulated']);
        $this->chk_cumulative= boolean($row['chk_cumulative']);

        $this->array_indicadores= $this->formulated ? $this->get_array_indicadores_from_ref() : null;

        $this->id_proyecto= $row['id_proyecto'];
        $this->id_proyecto_code= $row['id_proyecto_code'];

        $this->periodicidad= $row['periodicidad'];
        $this->carga= $row['carga'];
        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];
        $this->inicio_origen= $row['inicio_origen'];
        $this->fin_origen= $row['fin_origen'];

        $this->id_user_plan= $row['id_usuario_plan'];
        $this->id_user_real= $row['id_usuario_real'];
        $this->origen_data= $row['origen_data'];

        $this->get_criterio();

        return $this->error;
    }

    public function get_array_indicadores_from_ref($id= null, $flag= true) {
        $id= !empty($id) ? $id : $this->id_indicador;
        $flag= !is_null($flag) ? $flag : true;

        if (isset($this->array_indicadores)) unset($this->array_indicadores);
        $this->array_indicadores= array();

        $sql= "select distinct tindicadores.* from tindicadores, tformulas where id_indicador_ref = $id ";
        $sql.= "and tindicadores.id = tformulas.id_indicador ";
        $result= $this->do_sql_show_error('get_array_indicadores_from_ref', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('nombre'=>$row['nombre'], 'id_code'=>$row['id_code'], 'id_proceso'=>$row['id_proceso']);
            $this->array_indicadores[$row['id']]= $flag ? $row['id_code'] : $array;
        }
        return $this->array_indicadores;
    }

    public function get_array_indicadores_ref($id= null) {
        $id= !empty($id) ? $id : $this->id_indicador;

        if (isset($this->array_indicadores)) unset($this->array_indicadores);
        $this->array_indicadores= array();

        $sql= "select id_indicador_ref, id_indicador_ref_code, cumulative, formulated from tformulas, tindicadores ";
        $sql.= "where tformulas.id_indicador_ref = tindicadores.id and tformulas.id_indicador = $id ";
        $result= $this->do_sql_show_error('get_array_indicadores_ref', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id_indicador_ref'], 'id_code'=>$row['id_indicador_ref_code'],
                    'cumulative'=>boolean($row['cumulative']), 'formulated'=>boolean($row['formulated']));
            $this->array_indicadores[$row['id_indicador_ref']]= $array;
        }
        return $this->array_indicadores;
    }

    public function add() {
        $descripcion= setNULL_str($this->descripcion);

        $calculo= setNULL_str($this->calculo);

        $nombre= setNULL_str($this->nombre);
        $cumulative= boolean2pg($this->cumulative);
        $chk_cumulative= boolean2pg($this->chk_cumulative);
        $formulated= boolean2pg($this->formulated);

        $id_proyecto= setNULL_empty($this->id_proyecto);
        $id_proyecto_code= setNULL_str($this->id_proyecto_code);

        $sql= "insert into tindicadores (nombre, numero, id_proceso, id_proceso_code, id_proyecto, id_proyecto_code, ";
        $sql.= "calculo, descripcion, periodicidad, carga, id_unidad, id_unidad_code, inicio, fin, cumulative, formulated, ";
        $sql.= "chk_cumulative, cronos, situs) values ($nombre, $this->numero, {$_SESSION['id_entity']}, ";
        $sql.= "'{$_SESSION['id_entity_code']}', $id_proyecto, $id_proyecto_code, $calculo, $descripcion, '$this->periodicidad', ";
        $sql.= "'$this->carga', $this->id_unidad, '$this->id_unidad_code', $this->inicio, $this->fin, ";
        $sql.= "$cumulative, $formulated, $chk_cumulative, '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id= $this->clink->inserted_id("tindicadores");
            $this->id_indicador= $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tindicadores', 'id', 'id_code');

            $this->id_code= $this->obj_code->get_id_code();
            $this->id_indicador_code= $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion= setNULL_str($this->descripcion);
        $calculo= setNULL_str($this->calculo);
        $nombre= setNULL_str($this->nombre);
        $cumulative= boolean2pg($this->cumulative);
        $formulated= boolean2pg($this->formulated);
        $chk_cumulative= boolean2pg($this->chk_cumulative);

        $id_proyecto= setNULL_empty($this->id_proyecto);
        $id_proyecto_code= setNULL_str($this->id_proyecto_code);

        $sql= "update tindicadores set nombre= $nombre, descripcion= $descripcion, cronos= '$this->cronos', ";
        $sql.= "periodicidad= '$this->periodicidad', carga= '$this->carga', id_unidad= $this->id_unidad, ";
        $sql.= "id_unidad_code= '$this->id_unidad_code', calculo= $calculo, situs= '$this->location', ";
        $sql.= "inicio= $this->inicio, fin= $this->fin, cumulative= $cumulative, formulated= $formulated, ";
        $sql.= "chk_cumulative= $chk_cumulative, id_proyecto= $id_proyecto, id_proyecto_code= $id_proyecto_code, ";
        $sql.= "numero= $this->numero where id = $this->id_indicador ";
        $result= $this->do_sql_show_error('update', $sql);

        return $this->error;
    }

    public function update_inicio_fin() {
        $sql= "update tindicadores set inicio= $this->inicio, fin= $this->fin, cronos= '$this->cronos', ";
        $sql.= "where id = $this->id_indicador ";
        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    /**
     * actualizar la tabla tindicador_criterio y tproceso_indicadores cuando se cambia de proceso
     * @param $id_proceso_old
     * @param $id_proceso_new
     * @param $year
     */
    public function update_proceso_ref($id_proceso_old, $id_proceso_new, $year) {
        $id_proceso_new_code= get_code_from_table('tprocesos', $id_proceso_new);

        $sql= "update tindicador_criterio set id_proceso= $id_proceso_new, id_proceso_code= '$id_proceso_new_code' ";
        $sql.= "where id_proceso = $id_proceso_old and id_indicador = $this->id_indicador and year > $year ";
        $result= $this->do_sql_show_error('update_proceso_ref', $sql);

        $sql= "update tproceso_indicadores set id_proceso= $id_proceso_new, id_proceso_code= '$id_proceso_new_code' ";
        $sql.= "where id_proceso = $id_proceso_old and id_indicador = $this->id_indicador and year > $year ";
        $result= $this->do_sql_show_error('update_proceso_ref', $sql);
    }

    public function get_criterio($year= null, $id_indicador= null) {
        $year= !empty($year) ? $year : $this->year;
        $id_indicador= !is_null($id_indicador) ? $id_indicador: $this->id_indicador;

        $sql= "select * from tindicador_criterio where id_indicador = $id_indicador and year = $year ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        $sql.= "order by cronos desc limit 1";
        $result= $this->do_sql_show_error('get_criterio', $sql);
        $row= null;

        if ($this->cant > 0) {
            $row= $this->clink->fetch_array($result);

            $this->trend= $row['trend'];
            $this->criterio= get_criterio($this->trend);
            $this->peso= $row['peso'];
            $this->id_perspectiva= $row['id_perspectiva'];
            $this->id_perspectiva_code= $row['id_perspectiva_code'];

            $this->_yellow= $row['_yellow'];
            $this->_orange= $row['_orange'];
            $this->_green= $row['_green'];
            $this->_aqua= $row['_aqua'];
            $this->_blue= $row['_blue'];

            $this->_yellow_cot= $row['_yellow_cot'];
            $this->_orange_cot= $row['_orange_cot'];

        } else {
            $this->_yellow= _YELLOW;
            $this->_orange= _ORANGE;
            $this->_green= _GREEN;
            $this->_aqua= _AQUA;
            $this->_blue= _BLUE;

            $this->_yellow_cot= _YELLOW;
            $this->_orange_cot= _ORANGE;
        }

        return $row;
    }

    public function add_criterio($year, $id_perspectiva= null, $id_perspectiva_code= null, $id_proceso= null, $id_proceso_code= null) {
        $year= !is_null($year) ? $year : $this->year;
        $id_perspectiva= !is_null($id_perspectiva) ? $id_perspectiva : $this->id_perspectiva;
        $id_proceso= !is_null($id_proceso) ? $id_proceso : $this->id_proceso;

        if (is_null($id_proceso_code))
            $id_proceso_code= ($id_proceso != $this->id_proceso) ? $this->obj_code->get_code_from_table('tprocesos', $id_proceso) : $this->id_proceso_code;
        if (is_null($id_perspectiva_code))
            $id_perspectiva_code= ($id_perspectiva != $this->id_perspectiva) ? $this->obj_code->get_code_from_table('tperspectivas', $id_perspectiva) : $this->id_perspectiva_code;

        if (empty($this->_yellow))
            $this->_yellow= _YELLOW;
        if (empty($this->_orange))
            $this->_orange= _ORANGE;
        if (empty($this->_green))
            $this->_green= _GREEN;
        if (empty($this->_aqua))
            $this->_aqua= _BLUE;
        if (empty($this->_blue))
            $this->_blue= _DARK;
        if (empty($this->_yellow_cot))
            $this->_yellow_cot= _YELLOW;
        if (empty($this->_orange_cot))
            $this->_orange_cot= _ORANGE;

        $id_perspectiva= setNULL($id_perspectiva);
        $id_perspectiva_code= setNULL_str($id_perspectiva_code);
        $peso= setZero($this->peso);

        $sql= "insert into tindicador_criterio (id_indicador, id_indicador_code, id_proceso, id_proceso_code, year, peso, ";
        if (!empty($id_perspectiva))
            $sql.= "id_perspectiva, id_perspectiva_code, ";
        $sql.= "_yellow, _orange, _green, _aqua, _blue, trend, _yellow_cot, _orange_cot, cronos, situs) values ($this->id_indicador, ";
        $sql.= "'$this->id_indicador_code', $id_proceso, '$id_proceso_code', $year, $peso, ";
        if (!empty($id_perspectiva))
            $sql.= "$id_perspectiva, $id_perspectiva_code, ";
        $sql.= "$this->_yellow, $this->_orange, $this->_green, $this->_aqua, $this->_blue, $this->trend, $this->_yellow_cot, ";
        $sql.= "$this->_orange_cot,  '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('add_criterio', $sql, false);
        if (!$result && (stripos($this->error, 'duplicate') || stripos($this->error, 'duplicada'))) {
            $this->error= null;
        }
        return $this->error;
    }

    public function delete_criterio($id_proceso) {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->Set($id_proceso);

        if ($obj_prs->GetConectado() != _NO_LOCAL && $id_proceso != $_SESSION['local_proceso_id'])
            return;

        $sql= "delete from tindicador_criterio where id_indicador = $this->id_indicador and year >= $this->year";
        $this->do_sql_show_error('delete_criterio', $sql);
    }

    private function if_exist_criterio($year, $id_perspectiva=null, $id_proceso= null) {
        $id_perspectiva= !is_null($id_perspectiva) ? $id_perspectiva : $this->id_perspectiva;
        $id_proceso= !is_null($id_proceso) ? $id_proceso : $this->id_proceso;

        $sql= "select * from tindicador_criterio where id_indicador = $this->id_indicador and year = $year ";
        if (!empty($id_proceso))
            $sql.= "and id_proceso = $id_proceso ";
        $result= $this->do_sql_show_error('if_exist_criterio', $sql);
        $row= $this->clink->fetch_array($result);

        if (empty($this->cant))
            return false;
        if (empty($row['trend']))
            return false;

        return true;
    }

    public function update_criterio($year, $id_perspectiva= null, $id_perspectiva_code= null, $id_proceso= null, $id_proceso_code= null) {
        $year= !is_null($year) ? $year : $this->year;
        $id_perspectiva= !is_null($id_perspectiva) ? $id_perspectiva : $this->id_perspectiva;
        $id_proceso= !is_null($id_proceso) ? $id_proceso : $this->id_proceso;

        if (empty($id_perspectiva_code))
            $id_perspectiva_code= ($id_perspectiva != $this->id_perspectiva) ? $this->obj_code->get_code_from_table('tperspectivas', $id_perspectiva) : $this->id_perspectiva_code;

        $id_perspectiva= setNULL_empty($id_perspectiva);
        $id_perspectiva_code= setNULL_str($id_perspectiva_code);
        $peso= setZero($this->peso);

        if (empty($this->_yellow))
            $this->_yellow= _YELLOW;
        if (empty($this->_orange))
            $this->_orange= _ORANGE;
        if (empty($this->_green))
            $this->_green= _GREEN;
        if (empty($this->_aqua))
            $this->_aqua= _BLUE;
        if (empty($this->_blue))
            $this->_blue= _DARK;
        if (empty($this->_yellow_cot))
            $this->_yellow_cot= _YELLOW;
        if (empty($this->_orange_cot))
            $this->_orange_cot= _ORANGE;

        $sql= "update tindicador_criterio set _orange= $this->_orange, _yellow= $this->_yellow, _green= $this->_green, _aqua= $this->_aqua, ";
        $sql.= "_blue= $this->_blue, trend= $this->trend, _orange_cot= $this->_orange_cot, _yellow_cot= $this->_yellow_cot, ";
        $sql.= "id_perspectiva= $id_perspectiva, id_perspectiva_code= $id_perspectiva_code, peso= $peso, cronos= '$this->cronos', ";
        $sql.= "situs= '$this->location' where id_indicador = $this->id_indicador and id_proceso= $id_proceso and year = $year ";
        $result= $this->do_sql_show_error('update_criterio', $sql);
        $this->cant= $this->clink->affected_rows($result);

        if (is_null($this->error) && $this->cant == -1)
            $this->cant= 0;

        return $this->error;
    }

    /**
     * Actualizar la escala de colores en todo el periodod del indicador
     */
    public function expand_criterio_in_period($id_perspectiva=null, $id_proceso= null) {
        $id_perspectiva= !empty($id_perspectiva) ? $id_perspectiva : $this->id_perspectiva;
        $id_proceso= !is_null($id_proceso) ? $id_proceso : $this->id_proceso;

        $id_proceso_code= ($id_proceso != $this->id_proceso) ? $this->obj_code->get_code_from_table('tprocesos', $id_proceso) : $this->id_proceso_code;
        $id_perspectiva_code= ($id_perspectiva != $this->id_perspectiva) ? $this->obj_code->get_code_from_table('tperspectivas', $id_perspectiva) : $this->id_perspectiva_code;

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            $exists= $this->if_exist_criterio($year, $id_perspectiva, $id_proceso);
            if ($exists && (int)$year < $this->year)
                continue;
            if ($exists)
                $this->update_criterio($year, $id_perspectiva, $id_perspectiva_code, $id_proceso);
            else
                $this->add_criterio($year, $id_perspectiva, $id_perspectiva_code, $id_proceso, $id_proceso_code);
        }
    }

    public function if_exist_ref($year, $id_inductor= null) {
        $id_inductor= !is_null($id_inductor) ? $id_inductor : $this->id_inductor;

        $sql= "select * from tref_indicadores where id_indicador = $this->id_indicador and year = $year ";
        if (!empty($id_inductor))
            $sql.= "and id_inductor = $id_inductor ";
        $result= $this->do_sql_show_error('if_exist_ref', $sql);
        return $this->cant;
    }

    public function update_ref($year= null, $id_inductor= null) {
        $year= !is_null($year) ? $year : $this->year;
        if (is_null($id_inductor))
            $id_inductor= $this->id_inductor;
        $peso= setNULL($this->peso);

        $sql= "update tref_indicadores set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_indicador = $this->id_indicador and id_inductor = $id_inductor  and year = $year";
        $result= $this->do_sql_show_error('update_ref', $sql);
        $this->cant= $this->clink->affected_rows($result);
        if ($result && $this->cant == -1)
            $this->cant= 0;

        return $this->error;
    }

    public function add_ref($year= null, $id_inductor= null, $id_inductor_code= null) {
        $year= !is_null($year) ? $year : $this->year;
        if (is_null($id_inductor)) {
            $id_inductor = $this->id_inductor;
            $id_inductor_code = $this->id_inductor_code;
        }
        $peso= setNULL($this->peso);

        $sql= "insert into tref_indicadores (id_indicador, id_indicador_code, id_inductor, id_inductor_code, peso, ";
        $sql.= "year, cronos, situs) values ($this->id_indicador, '$this->id_indicador_code', $id_inductor, ";
        $sql.= "'$id_inductor_code', $peso, $year, '$this->cronos', '$this->location')";
        $result= $this->do_sql_show_error('add_ref', $sql);
        return $this->error;
    }

    /**
     * Actualiza la refencia al indicador para todos los inductores
     */
    public function expand_period_ref($id_inductor= null) {
        $id_inductor= !is_null($id_inductor) ? $id_inductor : $this->id_inductor;

        $obj_ind= new Tinductor($this->clink);
        $obj_ind->Set($id_inductor);
        $id_inductor_code= $obj_ind->get_id_inductor_code();

        $inicio= max($this->inicio, $obj_ind->GetInicio());
        $fin= min($this->fin, $obj_ind->GetFin());

        for ($year= $inicio; $year <= $fin; $year++) {
            if ($this->if_exist_ref($year, $id_inductor) && $year < $this->year)
                continue;

            $this->update_ref($year, $id_inductor);

            if (!is_null($this->error) || $this->cant == 0)
                $this->add_ref($year, $id_inductor, $id_inductor_code);
        }

        // borra hacia el futuro
        if ($fin < $this->fin || $fin < $obj_ind->GetFin()) {
            $_year= ++$fin;
            $fin= max($this->fin, $obj_ind->GetFin());

            for ($year= $_year; $year <= $fin; $year++) {
                if ($year > $this->year)
                    $this->delete_ref($year, $id_inductor);
            }
        }
    }

    /**
     * Borra las referenvcias de un indicador en un escenario y perspectiva especifica, en un ano especifico.
     * Si el ano especificado es el actual, bora tambien las referencias futuras.
     */
    protected function delete_ref($year= null, $id_inductor= null) {
        $year= !empty($year) ? $year : $this->year;
        $id_inductor= !is_null($id_inductor) ? $id_inductor : $this->id_inductor;

        $sql= "delete from tref_indicadores where id_indicador = $this->id_indicador and (year = $year ";
        if ((int)$year == (int)date('Y', strtotime($this->cronos)))
            $sql.= "or year > $year";
        $sql.= ") ";
        if (!empty($id_inductor))
            $sql.= "and id_inductor = $id_inductor ";
        $result= $this->do_sql_show_error('delete_ref', $sql);
    }

    public function delete_period_ref($id_inductor= null) {
        $id_inductor= !is_null($id_inductor) ? $id_inductor : $this->id_inductor;

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            if ($this->if_exist_ref($year, $id_inductor) && $year < $this->year)
                continue;
            $this->delete_ref($year, $id_inductor);
        }
    }

    public function set_perspectiva($value=null, $year= null) {
        $id_perspectiva= !is_null($value) ? setNull($this->id_perspectiva) : setNULL(null);
        $id_perspectiva_code= !is_null($value) ? setNULL_str($this->id_perspectiva_code) : setNULL_str(null);
        $year= !empty($year) ? $year : $this->year;

        $sql= "update tindicador_criterio set id_perspectiva= $id_perspectiva, id_perspectiva_code= $id_perspectiva_code, ";
        if (!is_null($value))
            $sql.= "peso= $value, ";
        $sql.= "cronos= '$this->cronos', situs='$this->location' where id_indicador= $this->id_indicador ";
        $sql.= "and id_proceso = $this->id_proceso and (year >= $year and year <= $this->fin)";
        $result= $this->do_sql_show_error('set_perspectiva', $sql);

        return $this->add_criterio($year, !is_null($this->id_perspectiva) ? $this->id_perspectiva: null, !is_null($value) ? $this->id_perspectiva_code : null);
    }
    
    public function test_if_in_proceso($id_proceso= null, $id_indicador= null) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;
        
        $sql= "select * from tproceso_indicadores where id_indicador = $id_indicador and id_proceso = $id_proceso";
        $result= $this->do_sql_show_error('test_if_in_proceso', $sql);
        $row= $this->clink->fetch_array($result);
        return $this->cant > 0 ? $row : false;
    } 
    
    private function create_view() {
        $sql= "drop view if exists view_tindicadores;";
        $this->do_sql_show_error('create_view', $sql);

        $sql= "create view view_tindicadores as ";
        $sql.= "select tindicadores.*, tproceso_indicadores.id_proceso as _id_proceso, tproceso_indicadores.id_proceso_code as _id_proceso_code, ";
        $sql.= "year, critico, peso from tindicadores, tproceso_indicadores where tindicadores.id = tproceso_indicadores.id_indicador ";
        $this->do_sql_show_error('create_view', $sql);
    }

    public function listar($year= null, $with_null_perspectiva= _PERSPECTIVA_ALL, $flag= true) {
        $year= !empty($year) ? $year : $this->year;
        $flag= !is_null($flag) ? $flag : true;
        
        $this->create_view();

        $sql= "select distinct view_tindicadores.*, view_tindicadores.id as _id, id_code as _id_code, view_tindicadores.nombre as _nombre, ";
        $sql.= "view_tindicadores.descripcion as _descripcion, view_tindicadores.inicio as _inicio, view_tindicadores.fin as _fin, ";
        $sql.= "view_tindicadores.id_proceso as _id_proceso, view_tindicadores.numero as _numero, tindicador_criterio.cronos as _cronos, ";
        $sql.= "id_perspectiva from view_tindicadores, tindicador_criterio ";
        if (!empty($this->id_inductor))
            $sql.= ", tref_indicadores ";
        $sql.= "where view_tindicadores.id = tindicador_criterio.id_indicador ";
        if (!empty($this->id_inductor))
            $sql.= "and view_tindicadores.id = tref_indicadores.id_indicador ";

        if (!empty($this->id_proceso))
            $sql.= "and view_tindicadores._id_proceso = $this->id_proceso ";

        if ($with_null_perspectiva == _PERSPECTIVA_NOT_NULL)
            $sql.= "and id_perspectiva = $this->id_perspectiva ";
        if ($with_null_perspectiva == _PERSPECTIVA_NULL)
            $sql.= "and (id_perspectiva is null or id_perspectiva = 0) ";        
        if (!empty($this->id_inductor))
            $sql.= "and id_inductor = $this->id_inductor ";
        if (!empty($year))
            $sql.= "and ((fin >= $year and inicio <= $year) and tindicador_criterio.year = $this->year) ";
        if (!empty($this->inicio))
            $sql.= "and (fin >= $this->inicio and inicio <= $this->fin) ";
        if (!empty($this->id_user_real) && empty($this->id_user_plan))
            $sql.= "and id_usuario_real = $this->id_user_real ";
        if (empty($this->id_user_real) && !empty($this->id_user_plan))
            $sql.= "and id_usuario_plan = $this->id_user_plan ";
        if (!empty($this->id_user_real) && !empty($this->id_user_plan))
            $sql.= "and (id_usuario_real = $this->id_user_real or id_usuario_plan = $this->id_user_plan) ";
        $sql.= "order by view_tindicadores.numero asc, _nombre asc, _cronos desc";

        $result= $this->do_sql_show_error('listar', $sql);
        if (!$result)
            return null;
        if ($flag)
            return $result;
        
        return $this->_get_indicadores($result, $with_null_perspectiva);
    }
    
    private function _get_indicadores($result, $with_null_perspectiva= _PERSPECTIVA_ALL) {
        $this->array_indicadores= array();
        
        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids['_id'])
                continue;
            $array_ids[$row['_id']]= $array_ids[$row['_id']];
            
            if ($with_null_perspectiva == _PERSPECTIVA_NULL && !empty($row['id_perspectiva']))
                continue;
            if ($with_null_perspectiva == _PERSPECTIVA_NOT_NULL && empty($row['id_perspectiva']))
                continue;
            
            ++$i;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['_nombre'], 'id_perspectiva'=>$row['id_perspectiva'], 
                        'numero'=>$row['_numero'], 'id_proceso'=>$row['id_proceso'], 'id_usuario_real'=>$row['id_usuario_real'],
                        'id_usuario_plan'=>$row['id_usuario_plan'], 'periodicidad'=>$row['periodicidad']);
            $this->array_indicadores[$row['_id']]= $array;
        }
        $this->cant= $i;
        return $this->array_indicadores;
    }

    public function listar_perspectivas($year= null) {
        $year= !empty($year) ? $year : $this->year;

        $sql= "select distinct tperspectivas.id as _id, tperspectivas.nombre as _nombre, tperspectivas.id_proceso as _id_proceso, ";
        $sql.= "tperspectivas.inicio as _inicio, tperspectivas.fin as _fin, tperspectivas.numero as _numero, color ";
        $sql.= "from tindicador_criterio, tperspectivas where tperspectivas.id = tindicador_criterio.id_perspectiva ";
        $sql.= "and tindicador_criterio.id_proceso = $this->id_proceso  ";
        if (!empty($year))
            $sql.= "and (tperspectivas.inicio <= $year and tperspectivas.fin >= $year)  ";
        $sql.= "order by tperspectivas.numero asc ";

        $result= $this->do_sql_show_error('listar_perspectivas', $sql);

        $obj= new Tproceso($this->clink);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $obj->SetIdProceso($row['_id_proceso']);
            $obj->Set();

            ++$i;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['_nombre'], 'proceso'=>$obj->GetNombre(), 'tipo'=>$obj->GetTipo(),
                        'inicio'=>$row['_inicio'], 'fin'=>$row['_fin'], 'id_proceso'=>$row['_id_proceso'],
                        'color'=>$row['color'], 'numero'=>$row['_numero']);
            $this->array_perspectivas[]= $array;
        }

        usort($this->array_perspectivas, 'order_array_perspectivas');
        $this->cant= $i;
        return $this->array_perspectivas;
    }

    public function listar_by_nivel() {
        $sql= "select distinct tindicadores.*, tindicadores.id as _id, tindicadores.id_code as _id_code ";
        $sql.= "from tindicadores, tindicador_criterio ";
        if (!empty($this->id_inductor))
            $sql.= ", tref_indicadores ";
        $sql.= "where tindicadores.id = tindicador_criterio.id_indicador and tindicador_criterio.year = $this->year ";
        if (!empty($this->id_indicador))
            $sql.= "and tindicadores.id = $this->id_indicador ";
        if (!empty($this->id_perspectiva))
            $sql.= "and id_perspectiva = $this->id_perspectiva ";
        if (!empty($this->id_inductor)) {
            $sql.= "and tindicador_criterio.id_indicador = tref_indicadores.id_indicador and tref_indicadores.year = $this->year ";
            $sql.= "and id_inductor = $this->id_inductor ";
        }
        if ($this->role <= _MONITOREO)
            return false;
        if ($this->role == _REGISTRO)
            $sql.= "and id_usuario_real = $this->id_usuario ";
        if ($this->role >= _PLANIFICADOR && $this->role < _ADMINISTRADOR)
            $sql.= "and (id_usuario_real = $this->id_usuario or id_usuario_plan =  $this->id_usuario) ";
        $sql.= "order by nombre ";

        $result= $this->do_sql_show_error('listar_by_nivel', $sql);
        return $result;
    }

    public function eliminar($radio_date= null) {
        $error= null;
        $year= ($radio_date == 2) ? $this->inicio : $this->year;

        $obj= new Treference($this->clink);
        $obj->SetYear($year);
        $obj->SetIdProceso($this->id_proceso);
        $obj->empty_tref_indicadores(null, $this->id_indicador, $year);
        $obj->empty_tindicador_criterio();

        if ($radio_date == 2 || $year == $this->inicio) {
            $obj->empty_tref_indicadores(null, $this->id_indicador, $year, false);
            $obj->empty_tindicador_criterio();

            $sql= "delete from tindicadores where id = $this->id_indicador";
            $result = $this->do_sql_show_error('eliminar', $sql);

            if (!$result) {
                $error = "ERROR: Este indicador esta asociado a inductores. Para borrar el indicador, este no debe tener relacion ";
                $error .= "con ningun inductor. Elimine el indicador, desde la lista de inductores y despues intente borrarlo nuevamente.";
            }
        } else {
            $year= $this->year-1;
            $sql= "update tindicadores set fin= $year where id = $this->id_indicador ";
            $this->do_sql_show_error('eliminar', $sql);

        }

        return $error;
    }

    public function desactivar() {
        $sql= "delete from tref_indicadores where id_indicador = $this->id_indicador ";
        if (!empty($this->year))
            $sql.= "and year >= $this->year";
        $this->do_sql_show_error('desactivar', $sql);
        if (!is_null($this->error))
            return $this->error;

        $sql= "delete from tindicador_criterio where id_indicador = $this->id_indicador ";
        if (!empty($this->year))
            $sql.= "and year >= $this->year";
        $this->do_sql_show_error('desactivar', $sql);
        if (!is_null($this->error))
            return $this->error;

        $sql= "delete from tproceso_indicadores where id_indicador = $this->id_indicador ";
        if (!empty($this->year))
            $sql.= "and year >= $this->year";
        $this->do_sql_show_error('desactivar', $sql);
        if (!is_null($this->error))
            return $this->error;
    }

    public function update_access($multi_query= false) {
         $multi_query= !is_null($multi_query) ? $multi_query : false;
         $id_user_real= setNULL_empty($this->id_user_real);
         $id_user_plan= setNULL_empty($this->id_user_plan);

         $sql= "update tindicadores set id_usuario_real= $id_user_real, id_usuario_plan= $id_user_plan ";
         $sql.= "where id = $this->id_indicador; ";

         if ($multi_query)
             return $sql;
         $this->do_sql_show_error('update_access', $sql);
    }

    public function get_proceso($id_indicador) {
         $sql= "select tprocesos.nombre as proceso, tipo, tprocesos.id as _id_proceso from tindicadores, tprocesos ";
         $sql.= "where tindicadores.id = $id_indicador and tindicadores.id_proceso = tprocesos.id ";
         $result= $this->do_sql_show_error('get_nombre', $sql);

         $row= $this->clink->fetch_array($result);
         $array= array('id_proceso'=>$row['_id_proceso'], 'proceso'=>$row['proceso'], 'tipo'=>$row['tipo']);
         return $array;
    }

  /**
   * Para el calculo utilizando otro indicadores
   */
    public function get_array_formulated($id_indicador= null) {
        $id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;
        if (isset($this->array_formulated[$id_indicador])) {
            reset($this->array_formulated[$id_indicador]);
            return $this->array_formulated[$id_indicador];
        }
        $sql= "select id_indicador_ref from tformulas where id_indicador = $id_indicador";
        $result= $this->do_sql_show_error('get_array_formulated', $sql);

        $i= 0;
        $this->array_formulated[$id_indicador]= array();
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->array_formulated[$id_indicador][$row['id_indicador_ref']]= $row['id_indicador_ref'];
        }
        return $i;
    }

    public function clean_formulate() {
        $sql= "delete from tformulas where id_indicador_ref = $this->id_indicador ";
        $this->do_sql_show_error('clean_formulate', $sql);
    }

    public function addto_formulate($id_code) {
        $id= get_id_from_id_code($id_code, 'tindicadores', $this->clink);

        $sql= "insert into tformulas (id_indicador_ref, id_indicador_ref_code, id_indicador, id_indicador_code, cronos, situs) ";
        $sql.= "values ($this->id_indicador, '$this->id_indicador_code', $id, '$id_code', '$this->cronos', '$this->location') ";
        $result= $this->do_sql_show_error('addto_formulate', $sql);
    }

    function replace_formulate($string) {
        if (empty($string))
            return null;

        $obj= new Tindicador($this->clink);
        preg_match_all('/\_[A-Z]{2}[0-9]{10}/i', $string, $array_code);

        foreach ($array_code[0] as $code) {
            $obj->Set(null, substr($code,1));
            $name= $obj->GetNombre();
            $string= str_replace($code, "'$name'", $string);
        }
        return $string;
    }
} // class

function order_array_perspectivas($a, $b) {
    if ($a['tipo'] == $b['tipo'])
        return 0;
    return ($a['tipo'] < $b['tipo']) ? -1 : 1;
}

function get_criterio($trend) {
    $criterio= null;
    switch ($trend){
        case 1:
            $criterio = '>=';
            break;
        case 2:
            $criterio = '<=';
            break;
        case 3:
            $criterio = '[]';
            break;
        default: $criterio = '>=';
    }
    return $criterio;
}
?>