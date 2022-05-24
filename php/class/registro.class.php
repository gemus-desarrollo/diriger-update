<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

//include("../config.inc.php";
include_once "time.class.php";

if (!class_exists('Tindicador'))
    include_once "indicador.class.php";

class Tregistro extends Tindicador {
    protected $plan,
            $plan_cot;
    protected $observacion_plan;
    protected $observacion_real;
    private $time;
    protected $acumulado_plan,
            $acumulado_plan_cot;
    protected $acumulado_real, 
            $acumulado_corte;

    private $array_values_interval;

    protected $origen_data_real,
            $origen_data_plan;

    private $obj_register;
    public $array_fixed_register;
    protected $array_fixed_indicadores;

    protected $intervals;
    protected $dates;
    protected $reg_date;
    protected $scale;   // escala utilizada en una grafica en el eje x-axis
    public $fix_year; //obligar a que los datos sean del ano especifico;
    public $compute_traze; // buscar la traza en las tablas treg_real y treg_plan

    public $null_real_found, // si existe indicadores con valores null dentro de una formula
        $null_plan_found;
    public $not_null_real_found, // si existe al menos un indicador con valor diferente de null dentro de una formula
        $not_null_plan_found;
    public $not_null_acumulado_real_found, // si existe al menos un indicador con valor diferente de null dentro de una formula
        $not_null_acumulado_plan_found;
    public $recompute;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tindicador::__construct($clink);
        $this->time= new TTime();
        $this->cronos= date('Y-m-d H:i:s');
    }

    public function __destruct() {
        foreach ($this->array_fixed_indicadores as $id) {
            $inicio= null;
            $fin= null;

            $result= $this->_get_tregistro_arg($this->year, $inicio, $fin);
            $sql= null;
            for ($yy= $inicio; $yy <= $fin; $yy++) {
                for ($mm= 1; $mm < 13; $mm++) {
                    for ($dd= 1; $dd < 32; $dd++) {
                        $date= $yy.'-'.str_pad($mm,2,'0',STR_PAD_LEFT).'-'.str_pad($dd,2,'0',STR_PAD_LEFT);

                    if ($this->array_fixed_register[$id][$yy][$mm][$dd]['plan']['flag']
                         && is_string($this->array_fixed_register[$id][$yy][$mm][$dd]['plan']['sql']))
                    $sql.= $this->array_fixed_register[$id][$yy][$mm][$dd]['plan']['sql'];

                    if ($this->array_fixed_register[$id][$yy][$mm][$dd]['real']['flag']
                         && is_string($this->array_fixed_register[$id][$yy][$mm][$dd]['real']['sql']))
                    $sql.= $this->array_fixed_register[$id][$yy][$mm][$dd]['real']['sql'];
            }   }   }

            if ($sql)
                $this->do_multi_sql_show_error('__destruct', $sql);

            if (!is_null($this->obj_register))
                unset ($this->obj_register);
        }
    }

    public function SetPlan($id) {
        $this->plan = $id;
    }
    public function SetPlan_cot($id) {
        $this->plan_cot = $id;
    }
    public function SetReal($id) {
        $this->value = $id;
    }
    public function SetObservacionPlan($id) {
        $this->observacion_plan = $id;
    }
    public function SetObservacionReal($id) {
        $this->observacion_real = $id;
    }
    public function GetPlan() {
        return $this->plan;
    }
    public function GetPlan_cot() {
        return $this->plan_cot;
    }
    public function GetAcumuladoPlan() {
        return $this->acumulado_plan;
    }
    public function GetAcumuladoPlan_cot() {
        return $this->acumulado_plan_cot;
    }
    public function GetReal() {
        return $this->value;
    }
    public function GetAcumuladoReal() {
        return $this->acumulado_real;
    }
    public function GetAcumuladoCorte() {
        return $this->acumulado_corte;
    }    
    public function SetAcumuladoPlan($id) {
        $this->acumulado_plan = $id;
    }
    public function SetAcumuladoPlan_cot($id) {
        $this->acumulado_plan_cot = $id;
    }
    public function SetAcumuladoReal($id) {
        $this->acumulado_real = $id;
    }
    public function SetAcumuladoCorte($id) {
        $this->acumulado_corte = $id;
    }
    public function GetObservacionPlan() {
        return $this->observacion_plan;
    }
    public function GetObservacionReal() {
        return $this->observacion_real;
    }   
    public function GetOrigenDataPlan() {
        return $this->origen_data_plan;
    }
    public function GetOrigenDataReal() {
        return $this->origen_data_real;
    }

    public function Set($id_indicador= null) {
        $id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;
        Tindicador::SetYear($this->year);
        Tindicador::__construct($this->clink);
        Tindicador::Set($id_indicador);
    }

    protected function create_intervals() {
        if (isset($this->intervals)) 
            unset($this->intervals);

        $sql_piece= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");
        $sql= "select corte, $sql_piece as _date from tregistro where id_indicador = $this->id_indicador ";
        if ($this->fix_year)
            $sql.= "and year = $this->year ";
        $sql.= "order by _date asc";
        $result= $this->do_sql_show_error('create_intervals', $sql);
        $i= 0;
        while ($row= $this->clink->fetch_array($result))
            $this->intervals[$i++]= array('corte'=>$row['corte'], 'date'=>$row['_date']);
    }

    public function insert_registro($action= 'add') {
        $error= null;
        $array_corte= array();
        $_year= $this->year;

        for ($j= $this->inicio; $j <= $this->fin and is_null($error); $j++) {
            for ($i=1; $i <= 12 and is_null($error); $i++) {
                $this->month= $i;
                $this->year= $j;

                $this->time->SetYear($this->year);
                $this->time->SetMonth($this->month);

                if ($action == 'add')
                    $fix= true;
                else
                    $fix= ($this->year >= $_year) ? true : false;

                $error= $this->insert_registro_month($array_corte, $fix);
                if (!is_null($error))
                    break;
            }
        }

        if (is_null($error))
            $error= $this->delete_registro_month($array_corte);

        return $error;
    }

    private function insert_registro_month(&$array_corte, $fix) {
        global $periodo_month;

        $lastday= $this->time->longmonth();
        $initday= $periodo_month[$this->periodicidad] < $periodo_month['M'] ? 1 : 27;
        $calcular= $this->formulated ? 1 : 0;

        for ($day= $initday; $day <= $lastday; $day++) {
            $this->time->SetDay($day);
            $corte= $this->time->ifDayPeriodo($this->periodicidad) ? 1 : 0;
            $carga= $periodo_month[$this->periodicidad] > $periodo_month['M'] && $this->time->ifDayPeriodo('M') ? 1 : 0;
            if (!$corte && !$carga)
                continue;

            $sql= "select id, corte from tregistro where id_indicador = $this->id_indicador and day = $day ";
            $sql.= "and month = $this->month and year = $this->year ";
            $result= $this->do_sql_show_error('insert_registro_month', $sql, false);
            $nums= $this->cant;

            if ($nums == 0) {
                $sql= "insert into tregistro (id_indicador, id_indicador_code, day, month, year, corte, calcular_real, calcular_plan, ";
                $sql.= "cronos, situs) values ($this->id_indicador, '$this->id_code',$day, $this->month, $this->year, ". boolean2pg($corte).", ";
                $sql.= boolean2pg($calcular).", ".boolean2pg($calcular).", '$this->cronos', '$this->location')";
                $result= $this->do_sql_show_error('insert_registro_month', $sql, false);

                if ($fix)
                    $array_corte[]= $this->clink->inserted_id("tregistro");

            } else {
                $row= $this->clink->fetch_array($result);
                $array_corte[]= $row['id'];

                if ($corte != boolean($row['corte']) || $calcular) {
                    $sql= "update tregistro set ";
                    if ($calcular)
                        $sql.= "calcular_real= ".boolean2pg($calcular).", calcular_plan= ".boolean2pg($calcular)." ";
                    if ($corte != boolean($row['corte']))
                        $sql.= "corte= ".boolean2pg($corte).", cronos= '$this->cronos', situs= '$this->location' ";
                    $sql.= "where id_indicador = $this->id_indicador and day = $day and month = $this->month and year = $this->year ";

                    $result= $this->do_sql_show_error('insert_registro_month', $sql);
            }   }
        }
        return $this->error;
    }

    private function delete_registro_month($array_corte) {
        $cant= count($array_corte);
        if ($cant == 0) 
            return null;

        $chain_corte= implode(',', $array_corte);
        $date_init= "{$this->inicio}-0-01";
        $date_end= "{$this->fin}-12-31";
        $sql_piece= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "delete from tregistro where id_indicador = $this->id_indicador ";
        $sql.= "and (($sql_piece < '$date_init' or $sql_piece > '$date_end') or (id not in ($chain_corte))) ";
        $sql.= "and (id_usuario_real is null and id_usuario_plan is null) ";
        $result= $this->do_sql_show_error('insert_registro', $sql);
        return $this->error;
    }

    private function _get_tregistro_arg($year= null, &$inicio= null, &$fin= null) {
        $year= !empty($year) ? (int)$year : (int)$this->year;

        $sql= "select min(year) as _inicio, max(year) as _fin from tregistro where id_indicador = $this->id_indicador ";
        $result= $this->do_sql_show_error('_get_tregistro_arg', $sql);
        $row= $this->clink->fetch_array($result);
        $inicio= !empty($row['_inicio']) ? (int)$row['_inicio'] : (int)$year;
        $fin= !empty($row['_fin']) ? (int)$row['_fin'] : (int)$year;

        $sql= "select * from tregistro where id_indicador = $this->id_indicador order by year asc, month asc, day asc ";
        $result= $this->do_sql_show_error('_get_tregistro_arg', $sql);
        return $result;
    }

    public function update_plan($day= null, $month= null, $year= null, $calcular= null, $reg_date= null, 
                                                                                    $fix_new_corte= true) {
        $this->array_fixed_indicadores[$this->id_indicador]= $this->id_indicador;
        $fix_new_corte= !is_null($fix_new_corte) ? $fix_new_corte : true;
        $calcular= !is_null($calcular) ? $calcular : $this->formulated ? true : false;

        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? $year : $this->year;

        if (!$calcular) {
            if (((is_null($this->plan) || !is_numeric($this->plan)) && (is_null($this->plan_cot) || !is_numeric($this->plan_cot)))
                    && (is_null($this->observacion_plan) || strlen($this->observacion_plan) <= 0)) {
                return null;
        }   }

        $plan= (is_null($this->plan) || !is_numeric($this->plan)) ? 'NULL' : $this->plan;
        $plan_cot= (is_null($this->plan_cot) || !is_numeric($this->plan_cot)) ? 'NULL' : $this->plan_cot ;
        $observacion_plan= setNULL_str($this->observacion_plan);
        $acumulado_plan= setNULL($this->acumulado_plan);
        $acumulado_plan_cot= setNULL($this->acumulado_plan_cot);

        $date= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
        $sql_piece= "concat_ws('-',".literal2pg("year").", lpad(".literal2pg("month").",2,'0'), lpad(".literal2pg("day").",2,'0'))";

        $sql= "select id, plan, plan_cot, month, day, year from tregistro where id_indicador = $this->id_indicador ";
        if ($this->periodicidad != 'A')
            $sql.= "and year = $year ";
        if (is_null($reg_date))
            $sql.= "and ".str_to_date2pg($sql_piece)." >= ".date2pg("'$date'")." ";
        else
            $sql.= "and ".str_to_date2pg($sql_piece)." >= ".date2pg("'$reg_date'")." ";
        $sql.= "order by year asc, month asc, day asc";
        $result= $this->do_sql_show_error('update_plan{1}', $sql);
        $cant= $this->cant;

        if (empty($cant) && $fix_new_corte) {
            $this->busca_corte(true, $day, $month, $year);
            return $this->update_plan($day, $month, $year, $calcular, $reg_date, false);
        }
        if (empty($cant) && $fix_new_corte)
            return -1;

        $row= $this->clink->fetch_array($result);
        $id_corte= $row['id'];

        $sql= "update tregistro set observacion_plan= $observacion_plan, cronos= '$this->cronos', situs= '$this->location' ";
        if ($this->formulated)
            $sql.= ", calcular_plan= ".boolean2pg($calcular)." ";
        if (!$this->formulated || (!$calcular && $this->formulated))
            $sql.= ", cronos_plan= '$this->cronos', id_usuario_plan= $this->id_usuario ";
        if (!$this->formulated || ((!$calcular && $this->formulated) && (!is_null($this->plan) || !is_null($this->plan_cot))))
            $sql.= ", plan= $plan, plan_cot= $plan_cot ";
        if (!$calcular && $this->formulated && (!is_null($this->acumulado_plan) || !is_null($this->acumulado_plan_cot)))
            $sql.= ", acumulado_plan= $acumulado_plan, acumulado_plan_cot= $acumulado_plan_cot ";
        $sql.= "where id = $id_corte";
        $result= $this->do_sql_show_error('update_plan{2}', $sql);
        $cant= $this->clink->affected_rows();

        if ($cant > 0) {
            if ($this->cumulative && !$this->formulated) {
                $this->id_usuario= _USER_SYSTEM;
                $this->set_cumulative_plan($row['day'], $row['month'], $row['year']);
            } 
            else
                if (is_null($this->array_fixed_register[$this->id_indicador][$row['year']][$row['month']][$row['day']]['plan'])) {
                    $this->array_fixed_register[$this->id_indicador][$row['year']][$row['month']][$row['day']]['plan']['flag']= true;
                    $this->insert_reg_plan($row['day'], $row['month'], $row['year'], $calcular);
                }
            $this->update_formulated($row['day'], $row['month'], $row['year'], false);
        }

        $this->id_usuario= $_SESSION['id_usuario'];

        if (($this->plan != $row['plan'] || $this->plan_cot != $row['plan_cot']) && (empty($cant) || !$result))
            return -1;
        if ($cant > 0)
            return 1;
    }

    private function insert_reg_plan($day= null, $month= null, $year= null, $calcular= null, 
                                                            $execute_sql= true, $array= null) {
        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? $year : $this->year;
        $calcular= !is_null($calcular) ? $calcular : $this->formulated ? true : false;
        $execute_sql= !is_null($execute_sql) ? $execute_sql : true;

        $plan= is_array($array) ? $array['plan'] : $this->plan;
        $plan_cot= is_array($array) ? $array['plan_cot'] : $this->plan_cot;
        $acumulado_plan= is_array($array) ? $array['acumulado_plan'] : $this->acumulado_plan;
        $acumulado_plan_cot= is_array($array) ? $array['acumulado_plan_cot'] : $this->acumulado_plan_cot;
        $observacion_plan= is_array($array) ? $array['observacion_plan'] : $this->observacion_plan;

        if (((is_null($plan) || !is_numeric($plan)) && (is_null($acumulado_plan) || !is_numeric($acumulado_plan))
                && (is_null($plan_cot) || !is_numeric($plan_cot)) && (is_null($acumulado_plan_cot) || !is_numeric($acumulado_plan_cot)))
            && (is_null($observacion_plan) || strlen($observacion_plan) <= 0)) {
            return;
        }

        $plan= (is_null($plan) || !is_numeric($plan)) ? 'NULL' : $plan;
        $plan_cot= (is_null($plan_cot) || !is_numeric($plan_cot)) ? 'NULL' : $plan_cot ;
        $acumulado_plan= setNULL($acumulado_plan);
        $acumulado_plan_cot= setNULL($acumulado_plan_cot);
        $observacion_plan= setNULL_str($observacion_plan);

        $sql= "insert into treg_plan (id_indicador, id_indicador_code, plan, plan_cot, day, month, year, id_usuario, observacion, ";
        $sql.= "acumulado_plan, acumulado_plan_cot, calcular, cronos, situs) values ($this->id_indicador, '$this->id_indicador_code', ";
        $sql.= "$plan, $plan_cot, $day, $month, $year, $this->id_usuario, $observacion_plan, $acumulado_plan, $acumulado_plan_cot, ";
        $sql.= boolean2pg($calcular). ", '$this->cronos', '$this->location'); ";
        if ($execute_sql)
            $result= $this->do_multi_sql_show_error('insert_reg_plan', $sql);
        return $sql;
    }

    protected function set_cumulative_plan($day= null, $month= null, $year= null) {
        if (!$this->cumulative) return null;

        $array= array();
        $this->acumulado_plan= null;
        $this->acumulado_plan_cot= null;

        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? (int)$year : (int)$this->year;
        $inicio= null;
        $fin= null;

        $result= $this->_get_tregistro_arg($year, $inicio, $fin);

        while ($row= $this->clink->fetch_array($result)) {
            if ($this->periodicidad != 'A' && $year != (int)$row['year'])
                continue;
            $array[(int)$row['year']][(int)$row['month']][(int)$row['day']]['plan']= $row['plan'];
            $array[(int)$row['year']][(int)$row['month']][(int)$row['day']]['plan_cot']= $this->trend == 3 ? $row['plan_cot'] : null;
        }

        $acumulado_plan= null;
        $acumulado_plan_cot= null;

        for ($yy= $inicio; $yy <= $fin; $yy++) {
            if ($this->periodicidad != 'A' && $yy != $year)
                continue;
            for ($mm= 1; $mm < 13; $mm++) {
                for ($dd= 1; $dd < 32; $dd++) {
                    if (empty($array[$yy][$mm][$dd]))
                        continue;

                    if (!is_null($array[$yy][$mm][$dd]['plan']))
                        $acumulado_plan+= $array[$yy][$mm][$dd]['plan'];
                    if ($this->trend != 3)
                        $array[$yy][$mm][$dd]['acumulado_plan']= $acumulado_plan;
                    if ($this->trend == 3) {
                        if (!is_null($array[$yy][$mm][$dd]['plan_cot']))
                            $acumulado_plan_cot+= $array[$yy][$mm][$dd]['plan_cot'];
                        $array[$yy][$mm][$dd]['acumulado_plan_cot']= $acumulado_plan_cot;
                    }
        }   }   }

        $date_cut= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
        $i= 0;
        $sql= null;
        $sql_reg= null;
        for ($yy= $inicio; $yy <= $fin; $yy++) {
            if ($this->periodicidad != 'A' && $yy != $year)
                continue;
            for ($mm= 1; $mm < 13; $mm++) {
                for ($dd= 1; $dd < 32; $dd++) {
                    if (empty($array[$yy][$mm][$dd]))
                        continue;
                    $date= $yy.'-'.str_pad($mm,2,'0',STR_PAD_LEFT).'-'.str_pad($dd,2,'0',STR_PAD_LEFT);
                    if (strtotime($date) < strtotime($date_cut))
                        continue;
                    ++$i;
                    $plan= setNULL($array[$yy][$mm][$dd]['plan']);
                    $plan_cot= setNULL($array[$yy][$mm][$dd]['plan_cot']);
                    $acumulado_plan= setNULL($array[$yy][$mm][$dd]['acumulado_plan']);
                    $acumulado_plan_cot= setNULL($array[$yy][$mm][$dd]['acumulado_plan_cot']);

                    $sql.= "update tregistro set acumulado_plan= $acumulado_plan, acumulado_plan_cot= $acumulado_plan_cot ";
                    $sql.= "where id_indicador = $this->id_indicador and day = $dd and month = $mm and year = $year; ";

                    $array_values= array(
                        'plan' => $plan,
                        'plan_cot' => $plan_cot,
                        'acumulado_plan' => $acumulado_plan,
                        'acumulado_plan_cot' => $acumulado_plan_cot,
                        'observacion_plan' => $this->observacion_plan
                    );
                    $sql_reg.= $this->insert_reg_plan($dd, $mm, $yy, false, false, $array_values);

                    if (!$this->array_fixed_register[$this->id_indicador][$yy][$mm][$dd]['plan'])
                        $this->update_formulated($dd, $mm, $yy, false);
                    $this->array_fixed_register[$this->id_indicador][$yy][$mm][$dd]['plan']['flag']= true;
                    $this->array_fixed_register[$this->id_indicador][$yy][$mm][$dd]['plan']['sql']= $sql_reg;
        }   }   }

        if ($sql && $i)
            $result= $this->do_multi_sql_show_error('set_cumulative_plan', $sql);

        if (!empty($array[$year][$month][$day])) {
            $this->acumulado_plan= $array[$year][$month][$day]['acumulado_plan'];
            $this->acumulado_plan_cot= $array[$year][$month][$day]['acumulado_plan_cot'];
        }
    }

    public function listar_reg_plan(&$date_corte) {
        $this->busca_corte(false,null,null,null,$date_corte);
        $date= $date_corte['year'].'-'.str_pad($date_corte['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($date_corte['day'],2,'0',STR_PAD_LEFT);

        $sql= "select distinct plan, acumulado_plan, acumulado_plan_cot, nombre, cargo, day, month, year, treg_plan.cronos as reg_date, ";
        $sql.=  "concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0')) as fecha ";
        $sql.= "from treg_plan, tusuarios where treg_plan.id_usuario = tusuarios.id and id_indicador = $this->id_indicador ";
        $sql.= "and (".str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))")." <= '$date' ";
        $sql.= "or ".date2pg("treg_plan.cronos")." <= date(now())) and (plan is not null or observacion is not null) ";
        $sql.= "and id_usuario != "._USER_SYSTEM;
        if (!empty($this->year))
            $sql.= " and year = $this->year ";
        $sql.= " order by treg_plan.cronos desc limit 93";

        $result= $this->do_sql_show_error('listar_reg_plan', $sql);
        return $result;
    }

    protected function busca_corte($fix_new_corte= false/*, $corte= 1*/, $day= null, $month= null, 
                                                                    $year= null, &$date_corte= null) {
        $fix_new_corte= !is_null($fix_new_corte) ? $fix_new_corte : false;
        /* $corte= !is_null($corte) ? $corte : 1;*/
        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? $year : $this->year;
        $date= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
        $array= null;

        $sql_piece= "concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))";
        $sql= "select *, id as _id, ".str_to_date2pg($sql_piece)." as _date ";
        $sql.= "from tregistro where id_indicador = $this->id_indicador ";
        $sql.= "and ".str_to_date2pg($sql_piece)." >= '$date' ";
        if ($this->periodicidad != 'A')
            $sql.= "and year = $year ";
        $sql.= "order by _date asc ";
        $result= $this->do_sql_show_error('busca_corte', $sql);
        $cant= $this->cant;

        $corte= false;
        $carga= false;

        if ($cant > 0) {
            while ($row= $this->clink->fetch_array($result)) {
                if ($corte)
                    break;
                if (boolean($row['corte']) && $corte)
                    continue;
                if (!boolean($row['corte']) && $carga)
                    continue;
                $array[$row['_id']]= array('id'=>$row['_id'], 'corte'=> boolean($row['corte']), 'year'=>$row['year'], 
                                            'month'=>$row['month'], 'day'=>$row['day'], 'valor'=>$row['valor'],
                                            'acumulado_real'=>$row['acumulado_real'], 'acumulado_plan'=>$row['acumulado_plan'], 
                                            'plan_cot'=>$row['plan_cot'], 'acumulado_plan_cot'=>$row['acumulado_plan_cot'], 
                                            'cronos_real'=> $row['cronos_real'], 'observacion_plan'=>$row['observacion_plan']);
                                            
                if (boolean($row['corte'])) {
                    $date_corte= array('year'=>$row['year'], 'month'=>$row['month'], 'day'=>$row['day'], 'plan'=>$row['plan'],
                                'acumulado_plan'=>$row['acumulado_plan'], 'plan_cot'=>$row['plan_cot'],
                                'acumulado_plan_cot'=>$row['acumulado_plan_cot'], 'observacion_plan'=>$row['observacion_plan']);
                    $corte= true;
                } else {
                    $carga= true;
        }   }   }

        // Para corregir posibles errores que hayan eliminado registros de la bd
        if ((empty($cant) || !$corte) && $fix_new_corte) {
            $obj= new Tregistro($this->clink);
            $obj->SetYear($year);
            $obj->Set($this->id_indicador);
            $obj->insert_registro();
            $array= $this->busca_corte(null,null,null,null,$date_corte);
        }

        return $array;
    }

    public function update_real($day= null, $month= null, $year= null, $calcular= null, $array_reg= null) {
        $this->array_fixed_indicadores[$this->id_indicador]= $this->id_indicador;
        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? $year : $this->year;
        $calcular= !is_null($calcular) ? $calcular : $this->formulated ? true : false;

        if (!$calcular) {
            if ((is_null($this->value) || !is_numeric($this->value))
                && (is_null($this->observacion_real) || strlen($this->observacion_real) <= 0))
                return 0;
        }

        $value= (is_null($this->value) || !is_numeric($this->value)) ? 'NULL' : $this->value;
        $acumulado_real= setNULL($this->acumulado_real);
        $observacion_real= (strlen($this->observacion_real) == 0) ? null : $this->observacion_real;
        $observacion_real= setNULL_str($observacion_real);

        if (is_null($array_reg))
            $array_reg= $this->busca_corte(true, $day, $month, $year);
        if (is_null($array_reg))
            return -1;

        $this->reg_date= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
        $j= 0;
        $k= 0;

        $i= 0;
        foreach ($array_reg as $array) {
            ++$i;
            if (!$this->cumulative && $i > 1)
                break;
            $id_corte= $array['id'];

            $sql= "update tregistro set cronos= '$this->cronos', situs= '$this->location', reg_date_real= '$this->reg_date', ";
            $sql.= "observacion_real= $observacion_real ";
            if ($this->formulated)
                $sql.= ", calcular_real= ".boolean2pg($calcular)." ";
            if (!$this->formulated || (!$calcular && $this->formulated))
                $sql.= ", cronos_real= '$this->cronos', id_usuario_real= $this->id_usuario ";
            if (!$this->formulated || ((!$calcular && $this->formulated) && !is_null($this->value)))
                $sql.= ", valor= $value ";
            if (!$calcular && $this->cumulative && !is_null($this->acumulado_real))
                $sql.= ", acumulado_real= $acumulado_real ";
            $sql.= "where id = $id_corte ";
            $result= $this->do_sql_show_error('update_real', $sql);
            $cant= $this->clink->affected_rows();

            if ($result) {
                $day= $array['day'] > $day ? $array['day'] : $day;
                $month= $array['month'] > $month ? $array['month'] : $month;
                ++$j;
            }
        }

        if ($j > 0 && (!empty($month) && !empty($day))) {
            if ($this->cumulative && !$this->formulated) {
                $this->id_usuario= _USER_SYSTEM;
                $this->set_cumulative_real($day, $month, $year, $calcular);
                
            } else {
                if (is_null($this->array_fixed_register[$this->id_indicador][$array['year']][$array['month']][$array['day']]['real'])) {
                    $this->array_fixed_register[$this->id_indicador][$array['year']][$array['month']][$array['day']]['real']['flag']= true;
                    $this->insert_reg_real($array['day'], $array['month'], $array['year'], $calcular, $this->reg_date);
                }
            }
            $this->update_formulated($array['day'], $array['month'], $array['year'], true);
        }

        $this->id_usuario= $_SESSION['id_usuario'];
        return $j == 0 ? -1 : 1;
    }

    private function insert_reg_real($day= null, $month= null, $year= null, $calcular= null, $reg_date= null, 
                                                                                $execute_sql= true, $array= null) {
        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? $year : $this->year;
        $calcular= !is_null($calcular) ? $calcular : $this->formulated ? true : false;
        $execute_sql= !is_null($execute_sql) ? $execute_sql : true;

        $value= is_array($array) ? $array['real'] : $this->value;
        $acumulado_real= is_array($array) ? $array['acumulado_real'] : $this->acumulado_real;
        $acumulado_corte= is_array($array) ? $array['acumulado_corte'] : $this->acumulado_corte;
        $observacion_real= is_array($array) ? $array['observacion_real'] : $this->observacion_real;
        $chk_cumulative= is_array($array) ? $array['chk_cumulative'] : $this->chk_cumulative;

        if (((is_null($value) || !is_numeric($value)) && (is_null($acumulado_real) || !is_numeric($acumulado_real)))
            && (is_null($observacion_real) || strlen($observacion_real) <= 0))
            return;

        $value= (is_null($value) || !is_numeric($value)) ? 'NULL' : $value;
        $acumulado_corte= setNULL($acumulado_corte);
        $observacion_real= setNULL_str($observacion_real);
        $acumulado_real= setNULL($acumulado_real);
        $chk_cumulative= boolean2pg($chk_cumulative);
        $calcular= boolean2pg($calcular);
        $reg_date= setNULL_str($reg_date);

        $sql= "insert into treg_real (id_indicador,id_indicador_code,valor, day, month, year, id_usuario, observacion,";
        $sql.= "acumulado_corte, acumulado_real, chk_cumulative, reg_date, calcular, cronos, situs) values ";
        $sql.= "($this->id_indicador, '$this->id_indicador_code', $value, $day, $month, $year, $this->id_usuario, ";
        $sql.= "$observacion_real, $acumulado_corte, $acumulado_real, $chk_cumulative, $reg_date, $calcular, ";
        $sql.= "'$this->cronos', '$this->location'); ";

        if ($execute_sql)
            $result= $this->do_sql_show_error('insert_reg_real', $sql);
        return $sql;
    }

    protected function set_cumulative_real($day= null, $month= null, $year= null) {
        if (!$this->cumulative)
            return null;
        $array= array();
        $this->acumulado_real= null;

        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? (int)$year : (int)$this->year;
        $inicio= null;
        $fin= null;

        $result= $this->_get_tregistro_arg($year, $inicio, $fin);

        while ($row= $this->clink->fetch_array($result)) {
            if ($this->periodicidad != 'A' && $year != (int)$row['year'])
                continue;
            $array[(int)$row['year']][(int)$row['month']][(int)$row['day']]['real']= $row['valor'];
            $array[(int)$row['year']][(int)$row['month']][(int)$row['day']]['reg_date_real']= $row['reg_date_real'];
        }

        $acumulado_real= null;
        for ($yy= $inicio; $yy <= $fin; $yy++) {
            if ($this->periodicidad != 'A' && $yy != $year)
                continue;
            for ($mm= 1; $mm < 13; $mm++) {
                for ($dd= 1; $dd < 32; $dd++) {
                    if (empty($array[$yy][$mm][$dd]))
                        continue;

                    if (!is_null($array[$yy][$mm][$dd]['real']))
                        $acumulado_real+= $array[$yy][$mm][$dd]['real'];
                    $array[$yy][$mm][$dd]['acumulado_real']= $acumulado_real;
        }   }   }

        $date_cut= $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
        $i= 0;
        $sql= null;

        for ($yy= $inicio; $yy <= $fin; $yy++) {
            if ($this->periodicidad != 'A' && $yy != $year)
                continue;
            for ($mm= 1; $mm < 13; $mm++) {
                for ($dd= 1; $dd < 32; $dd++) {
                    if (empty($array[$yy][$mm][$dd]))
                        continue;
                    $date= $yy.'-'.str_pad($mm,2,'0',STR_PAD_LEFT).'-'.str_pad($dd,2,'0',STR_PAD_LEFT);
                    if (strtotime($date) < strtotime($date_cut))
                        continue;
                    $real= $array[$yy][$mm][$dd]['real'];
                    $acumulado_real= $array[$yy][$mm][$dd]['acumulado_real'];
                    ++$i;
                    $_date= (int)$yy != (int)$year || (int)$mm != (int)$month || (int)$dd != (int)$day ? $date : $this->reg_date;
                    $sql.= "update tregistro set acumulado_real= $acumulado_real, reg_date_real= '$_date' ";
                    $sql.= "where id_indicador = $this->id_indicador and day = $dd and month = $mm and year = $year; ";

                    $array_values= array(
                        'real' => $real,
                        'acumulado_corte' => $real,
                        'acumulado_real' => $acumulado_real,
                        'observacion_real' => $this->observacion_real
                    );
                    $sql.= $this->insert_reg_real($dd, $mm, $yy, false, $this->reg_date, false, $array_values);

                    if (!$this->array_fixed_register[$this->id_indicador][$yy][$mm][$dd]['real']['flag'])
                        $this->update_formulated($dd, $mm, $yy, true);
                    $this->array_fixed_register[$this->id_indicador][$yy][$mm][$dd]['real']['flag']= true;
                    $this->array_fixed_register[$this->id_indicador][$yy][$mm][$dd]['real']['sql']= $sql_reg;
        }   }   }

        if ($sql && $i)
            $result= $this->do_multi_sql_show_error('set_cumulative_real', $sql);

        if (!empty($array[$year][$month][$day]))
            $this->acumulado_real= $array[$year][$month][$day]['acumulado_real'];
    }

    public function listar_reg_real(&$corte, $periodicidad= null, $one_day= false) {
        global $periodo_month;

        $one_day= !is_null($one_day) ? $one_day : false;
        $periodicidad= !is_null($periodicidad) ? $periodo_month[$periodicidad] : null;
        $limit= null;

        if (empty($corte['year']) && empty($corte['month']) && empty($corte['day'])) {
            $this->busca_corte(false, null, null, null, $corte);
            $limit= 90;
        } else {
            $limit= 1;
        }
        $date= $corte['year'].'-'.str_pad($corte['month'],2,'0',STR_PAD_LEFT).'-'.str_pad($corte['day'],2,'0',STR_PAD_LEFT);
        $sql_tmp= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "select treg_real.cronos as fecha, nombre, treg_real.* from treg_real, tusuarios ";
        $sql.= "where treg_real.id_usuario = tusuarios.id and id_indicador = $this->id_indicador ";
        if (!$one_day)
            $sql.= "and ($sql_tmp <= '$date' or date(treg_real.cronos) <= date(now())) ";
        else
            $sql.= "and reg_date = '$date' ";
        $sql.= "and (valor is not null or observacion is not null) ";
        if ($one_day)
            $sql.= "and id_usuario != "._USER_SYSTEM; 
        $sql.= " ";
        if (!empty($periodicidad) && $periodicidad <= 7) {
            $sql.= "and year = {$corte['year']} ";
            if ($periodicidad <= 4)
                $sql.= "and month = {$corte['month']} ";
            if ($periodicidad == 1)
                $sql.= "and day = {$corte['day']} "; 
        }
        $sql.= "order by reg_date desc, treg_real.cronos desc limit $limit";
        $result= $this->do_sql_show_error('listar_reg_real', $sql);

        return $limit == 90 ? $result : $this->clink->fetch_array($result);
    }

    public function GetPlanReal($id_indicador, $day, $month, $year) {
        $sql= "select * from tregistro where id_indicador = $id_indicador and day = $day and month = $month and year = $year ";
        $result= $this->do_sql_show_error('GetPlanReal', $sql);
        $row= $this->clink->fetch_array($result);
        return $row;
    }

    public function listar() {
        $sql= "select distinct * from tindicadores, tregistro where tindicadores.id = tregistro.id_indicador ";
        if (!empty($this->day))
            $sql.= "and day = $this->day";
        if (!empty($this->month))
            $sql.= "and month = $this->month";
        if (!empty($this->year))
            $sql.= "and year = $this->year ";
        if (!empty($this->id_perspectiva))
            $sql.= "and id_perspectiva = $this->id_perspectiva ";
        if (!empty($this->id_inductor))
            $sql.= "and id_inductor = $this->id_inductor ";
        if (!empty($this->id_indicador))
            $sql.= "and id_indicador = $this->id_indicador ";
        if (!empty($this->id_user_real))
            $sql.= "and tindicadores.id_usuario_real = $this->id_user_real ";
        if (!empty($this->id_user_plan))
            $sql.= "and tindicadores.id_usuario_plan = $this->id_user_plan ";
        $sql.= "order by nombre, year asc, month asc ";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function update_formulated($day= null, $month= null, $year= null, $if_real= null) {
        $day= !empty($day) ? $day : $this->day;
        $month= !empty($month) ? $month : $this->month;
        $year= !empty($year) ? $year : $this->year;

        $cant= $this->get_array_formulated();
        if (empty($cant))
            return;

        if (is_null($this->obj_register))
            $this->obj_register= new Tregistro($this->clink);
        $this->obj_register->SetYear($this->year);
        $this->obj_register->SetMonth($this->month);
        $this->obj_register->SetDay($this->day);
        $this->obj_register->SetIdUsuario($_SESSION['id_usuario']);

        if (!is_null($if_real) && $if_real)
            $text= " real";
        if (!is_null($if_real) && !$if_real)
            $text= " de plan";
        $observacion= "Modificado valor $text del indicador $this->nombre en fecha ". odbc2date($this->cronos);

        foreach ($this->array_formulated[$this->id_indicador] as $id) {
            $this->obj_register->SetIdIndicador($id);
            $this->obj_register->Set($id);

            if (is_null($if_real) || $if_real)
                $this->obj_register->SetObservacionReal($observacion);
            if (is_null($if_real) || !$if_real)
                $this->obj_register->SetObservacionPlan($observacion);

            if (is_null($if_real) || !$if_real)
                $this->obj_register->update_plan($day, $month, $year, true, null);
            if (is_null($if_real) || $if_real)
                $this->obj_register->update_real($day, $month, $year, true);
        }
    }

    /*
    * Calculo de los acumulados dentro de un periodo. Es decir entre dos cortes 
    */
    private function _listar_treg_real_interval($pcorte, $corte) {
        $index= null;
        $index_value= null;
        $index_acumulado= null;
        $index_acumulado_corte= null;
        $index_reg_date= null;

        $last_index= null;
        $last_value= null;
        $last_acumulado= null;
        $last_acumulado_corte= null;

        if (isset($this->array_values_interval))
            unset($this->array_values_interval);

        $this->array_values_interval= array();

        $sql= "select * from treg_real where id_indicador = $this->id_indicador and (reg_date <= '$corte' ";
        if (!empty($pcorte))
            $sql.= "and reg_date > '$pcorte' ";
        $sql.= ") order by reg_date asc, cronos desc ";
        $result= $this->do_sql_show_error('_listar_treg_real_interval', $sql);

        $i= 0;
        $array_results= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array_results[date('Y-m-d', strtotime($row['reg_date']))]= $row;
            ++$i;
        }

        $i= 0;
        $new_register= true;
        foreach ($array_results as $row) {
            if (strtotime($row['reg_date']) <= strtotime($this->reg_date)) {
                $index= $i;
                $index_value= $row['valor'];
                $index_acumulado= $row['acumulado_real'];
                $index_acumulado_corte= $row['acumulado_corte'];
                $index_reg_date= $row['reg_date'];
            }
            if (strtotime($this->reg_date) == strtotime($row['reg_date'])) {
                $new_register= false;
            }
            $last_index= $i;
            $last_value= $row['valor'];
            $last_acumulado= $row['acumulado_real'];
            $last_acumulado_corte= $row['acumulado_corte'];

            $this->array_values_interval[$i]= $row;
            ++$i;
        }
        
        $array= array('index'=>$index, 'index_value'=>$index_value, 'index_acumulado'=>$index_acumulado,
                    'index_acumulado_corte'=>$index_acumulado_corte, 'last_index'=>$last_index, 
                    'last_value'=>$last_value, 'last_acumulado'=>$last_acumulado, 
                    'last_acumulado_corte'=>$last_acumulado_corte, 'index_reg_date'=>$index_reg_date, 
                    'new_register'=>$new_register, 'cant'=>$i);
        
        return !empty($i) ? $array : null;
    }

    public function update_cumulative_in_period() {
        $corte= null;
        $pcorte= null;
        $value= $this->value;

        $array_reg= $this->busca_corte();
        $this->reg_date= $this->year.'-'.str_pad($this->month, 2, '0', STR_PAD_LEFT).'-'.str_pad($this->day, 2, '0', STR_PAD_LEFT);
        $_reg_date= strtotime($this->reg_date);

        $i= 0;
        foreach ($array_reg as $row) {
            $icorte= $row['year'].'-'.str_pad($row['month'], 2, '0', STR_PAD_LEFT).'-'.str_pad($row['day'], 2, '0', STR_PAD_LEFT);
            if ($_reg_date <=  strtotime($icorte)
                && (empty($pcorte) || (!empty($pcorte) && $_reg_date > strtotime($pcorte)))) {
                $corte= $icorte;
                if (!empty($pcorte) && $_reg_date > strtotime($pcorte)) {
                    $pcorte= $icorte;
                }
            }
            ++$i;
        }

        if (empty($corte)) 
            return null;

        $new_values= null;
        $update_tregistro= false;
        $array_results= $this->_listar_treg_real_interval($pcorte, $corte);

        if ($array_results) 
            $update_tregistro= $this->_update_cumulative_in_period($array_results, $new_values);
        else {
            $update_tregistro= true;
            $new_values= array('valor'=>$this->value, 'acumulado_corte'=>$this->value, 
                            'acumulado_real'=>$this->value);
        }
        if ($update_tregistro) 
            $this->_update_cumulative_in_period_treg_real($array_results);
            
        if (!is_null($new_values)) {
            $this->value= $new_values['acumulado_corte'];
            $this->acumulado_real= $new_values['acumulado_real'];
            $this->acumulado_corte= null;

            $this->update_real($this->day, $this->month, $this->year, null, $array_reg);
        } else {
            $this->acumulado_corte= $this->value;
            if ($this->cumulative)
                $this->acumulado_real= $this->value;
        }

        $this->value= $value;

        if (empty($this->error)) {
            $day= date('d', strtotime($corte));
            $month= date('m', strtotime($corte));
            $year= date('Y', strtotime($corte));
            
            $this->value= $new_values['valor'];
            $this->acumulado_real= $new_values['acumulado_real'];
            $this->acumulado_corte= $new_values['acumulado_corte'];

            $this->insert_reg_real($day, $month, $year, false, $this->reg_date); 
        }
    }

    private function _update_cumulative_in_period($array_results, &$new_values) {
        $new_values= null;
        $update_tregistro= false;
        $acumulado_corte= null;
        $acumulado_real= null;

        $index= $array_results['index'];
        $index_value= $array_results['index_value'];
        $last_index= $array_results['last_index'];
        $new_register= $array_results['new_register'];

        if (!is_null($index) && !$new_register) {
            $this->array_values_interval[$index]['observacion_real']= $this->observacion_real;
        }

        $i= 0;
        if (!$new_register && (!empty($index) && $index-1 >= 0)) {
            $this->array_values_interval[$index]['valor']= $this->value;
            $acumulado_corte= $this->array_values_interval[$index-1]['acumulado_corte'] + $this->value;
            $this->array_values_interval[$index]['acumulado_corte']= $acumulado_corte;

            if ($this->cumulative) {
                $acumulado_real= $this->array_values_interval[$index-1]['acumulado_real'] + $this->value;
                $this->array_values_interval[$index]['acumulado_real']= $acumulado_real;
            } 
        }

        if (!$new_register && $index == 0)  {
            $this->array_values_interval[$index]['valor']= $this->value;
            $acumulado_corte= $this->value;
            $this->array_values_interval[$index]['acumulado_corte']= $this->value;

            if ($this->cumulative) {
                $acumulado_real= $this->value;
                $this->array_values_interval[$index]['acumulado_real']= $this->value;
            }             
        }
        
        if ($new_register && !is_null($index)) {
            $acumulado_corte= $this->array_values_interval[$index]['acumulado_corte'] + $this->value;
            if ($this->cumulative)
                $acumulado_real= $this->array_values_interval[$index]['acumulado_real'] + $this->value;
        }

        $index_value= !$new_register ? $index_value : 0;

        if (!is_null($index) && ($index < $last_index || ($new_register && $index == $last_index))) {
            $j= $index + 1;
            for ($i= $j; $i <= $last_index; $i++) {
                $acumulado_corte= $this->array_values_interval[$i]['acumulado_corte'] - $index_value + $this->value;
                $this->array_values_interval[$i]['acumulado_corte']= $acumulado_corte;
        
                if ($this->cumulative) {
                    $acumulado_real= $this->array_values_interval[$i]['acumulado_real'] - $index_value + $this->value;
                    $this->array_values_interval[$i]['acumulado_real']= $acumulado_real;
                }
                if (!$this->array_values_interval[$i]['chk_cumulative']) 
                    break;
            }

            if ($i >= $last_index) 
                $update_tregistro= true;

        } else {
            $update_tregistro= true;
        }

        if ($update_tregistro)
            $new_values= array('valor'=>$this->value, 'acumulado_corte'=>$acumulado_corte, 'acumulado_real'=>$acumulado_real);

        return $update_tregistro;
    }

    private function _update_cumulative_in_period_treg_real($array_results) {
        $index= $array_results['index'];
        $new_register= $array_results['new_register'];

        $i= 0;
        $j= 0;
        $sql= null;
        foreach ($this->array_values_interval as $row) {
            if ($i < $index || ($i == $index && $new_register)) {
                ++$i;
                continue;
            }    
            $sql.= $this->_update_cumulative_in_period_insert_treg_real($row);
            ++$i;
            ++$j;
            if ($j >= 1000) {
                $this->do_multi_sql_show_error('_update_cumulative_in_period_treg_real', $sql);
                $j= 0;
                $sql= null;
                if (!empty($this->error))
                    return false;                
            }
        }
        if (!empty($sql)) {
            $this->do_multi_sql_show_error('_update_cumulative_in_period_treg_real', $sql);
            if (!empty($this->error))
                return false;                
        } 

        return true;
    }

    private function _update_cumulative_in_period_insert_treg_real($row) {
        $observacion_real= setNULL_str($row['observacion_real']);
        $reg_date= setNULL_str($row['reg_date']);
        $chk_cumulative= $row['chk_cumulative'];

        $value= (is_null($row['valor']) || !is_numeric($row['valor'])) ? 'NULL' : $row['valor'];
        $acumulado_real= setNULL($row['acumulado_real']);
        $acumulado_corte= setNULL($row['acumulado_corte']);
        $calcular= boolean2pg($row['calcular']);
        $chk_cumulative= boolean2pg($chk_cumulative);

        $sql= "insert into treg_real (id_indicador,id_indicador_code, valor, day, month, year, id_usuario, ";
        $sql.= "observacion, acumulado_corte, acumulado_real, chk_cumulative, reg_date, calcular, cronos, situs) ";
        $sql.= "values ($this->id_indicador, '$this->id_indicador_code', $value, {$row['day']}, {$row['month']}, ";
        $sql.= "{$row['year']}, $this->id_usuario, $observacion_real, $acumulado_corte, $acumulado_real, ";
        $sql.= "$chk_cumulative, $reg_date, $calcular, '$this->cronos', '$this->location'); ";
        
        return $sql;
    }
}
