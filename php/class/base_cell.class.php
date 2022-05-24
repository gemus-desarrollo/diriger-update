<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../config.inc.php";

if (!class_exists('Tregistro'))
    include_once "registro.class.php";

class Tbase_cell extends Tregistro {
    protected $alarm,
            $alarm_cumulative;
    protected $flecha,
            $flecha_cumulative;
    protected $ratio,
            $ratio_cumulative;
    protected $percent,
            $percent_cumulative;

    protected $meses;

    public $row_real;
    protected $nrow;

    public $row_plan;
    protected $nrow_plan;

    protected $reg_date;   // fecha corte a la que corresponde ultima actualziacion del indicador en el mes
    protected $reg_date_real;   // fecha de ultima actualziacion del indicador en el mes

    public $updated; // si esta actualizado el dato real
    public $updated_plan; // si esta actualizado el dato plan
    public $valid_period; // si el real y el plan estan en el mismo periodo
    public $data_real,
            $data_diff;

    public $fix_interval;  // obligar a que los datos sean de un periodo especifico;
    protected $date_below_cutoff;  // Si se considera la fecha a partir de la cual se inicia la busqueda del valor
    public $strict_low_cutoff;  // el intervalo superior es extricto

    protected $used_compute_function;

    protected $xtime;

    protected $current_date;
    protected $datetime;

    protected $obj_calc;


    public function GetDateTime() {
        return $this->datetime;
    }
    public function GetFecha_real() {
        return $this->reg_date_real;
    }
    public function GetFecha_plan() {
        return $this->reg_date_plan;
    }
    public function GetAlarm() {
        return $this->alarm;
    }
    public function GetFlecha() {
        return $this->flecha;
    }
    public function GetRatio() {
        return $this->ratio;
    }
    public function GetPercent() {
        return $this->percent;
    }
    public function GetAlarm_cumulative() {
        return $this->alarm_cumulative;
    }
    public function GetFlecha_cumulative() {
        return $this->flecha_cumulative;
    }
    public function GetRatio_cumulative() {
        return $this->ratio_cumulative;
    }
    public function GetPercent_cumulative() {
        return $this->percent_cumulative;
    }
    public function SetScale($id) {
        $this->scale = $id;
    }
    public function GetScale() {
        return $this->scale;
    }
    public function SetDateBelowCutoff($id) {
        $this->date_below_cutoff = $id;
    }
    public function GetDateInterval() {
        return $this->dates;
    }
// fecha en la que se actualizo el indicador
    public function GetStrFecha_real() {
        return $this->GetStrFecha($this->reg_date_real);
    }
    public function GetStrFecha_plan() {
        return $this->GetStrFecha($this->reg_date_plan);
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tregistro::__construct($clink);

        $this->meses = array("und", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sept", "Oct", "Nov", "Dic");
        $this->xtime = new TTime();
        $this->xtime->SetFormat('Y-m-d');

        $this->plan = null;
        $this->plan_cot = NULL;
        $this->reg_date_plan = null;

        $this->updated = false;
        $this->updated_plan = false;
        $this->valid_period = false;
        $this->fix_year = false;
        $this->fix_interval = false;
        $this->compute_traze = true;

        $this->used_compute_function = false;

        $this->scale = null;
        $this->date_below_cutoff = null;
        $this->strict_low_cutoff= false;
    }

    protected function GetStrFecha($fecha) {
        if (empty($fecha))
            $str = "actualizar";
        else {
            $this->xtime->splitTime($fecha);
            $str = $this->meses[(int) $this->xtime->GetMonth()] . ', ' . $this->xtime->GetDay() . ', ' . $this->xtime->GetYear();
        }
        return $str;
    }

    protected function blank_value() {
         $this->row_real= null;
         $this->value= NULL;

         $this->ratio= NULL;
         $this->percent= NULL;
         $this->alarm= 'blank';
         $this->flecha= 'blank';

         $this->ratio_cumulative= NULL;
         $this->percent_cumulative= NULL;
         $this->alarm_cumulative= 'blank';
         $this->flecha_cumulative= 'blank';
         $this->acumulado_real= null;

         $this->fecha= NULL;
         $this->reg_date= null;
         $this->reg_date_real= null;
         $this->observacion_real= NULL;

         $this->updated= false;
         $this->updated_plan= false;
         $this->valid_period= false;
     }

    protected function blank_plan() {
         $this->criterio= NULL;
         $this->observacion_plan= NULL;

         $this->reg_date_plan= null;
         $this->alarm= 'blank';
         $this->row_plan= null;
         $this->plan= NULL;
         $this->plan_cot= null;

         $this->alarm_cumulative= 'blank';
         $this->acumulado_plan= NULL;
         $this->acumulado_plan_cot= null;
     }

    public function top_month($month= null) {
        $month= !empty($month) ? $month : $this->month;
        $this->create_intervals();
        $month= (int)$month;

        foreach ($this->intervals as $segm) {
            if ($segm[1] == $month)
                return true;
        }
        return false;
    }

    protected function _set_alarm($plan = null, $real = null, $plan_cot = null) {
        $array = array('alarm' => null, 'ratio' => null, 'percent' => null);

        if ((is_null($plan) || is_null($real)) || empty($this->trend)) {
            $array['alarm'] = 'blank';
            return $array;
        }

        if (empty($this->trend))
            $this->searchCriterio();
        $this->criterio = get_criterio($this->trend);

        $alarm = 'red';

        if ($this->criterio == ">=" && $real >= $this->plan)
            $alarm = 'green';
        if ($this->criterio == "<=" && $real <= $plan)
            $alarm = 'green';
        if ($this->criterio == '[]' && ($real >= $plan_cot && $real <= $plan))
            $alarm = 'green';

        $array = get_ratio($real, $plan, $this->trend, $plan_cot);
        $ratio = $array['ratio'];
        $percent = $array['percent'];

        if (!is_null($ratio) && $this->trend != 3) {
            if ($ratio < $this->_orange)
                $alarm = 'red';
            if ($ratio >= $this->_orange && $ratio < $this->_yellow)
                $alarm = 'orange';
            if ($ratio >= $this->_yellow && $ratio < $this->_green)
                $alarm = 'yellow';
            if ($ratio >= $this->_green && $ratio < $this->_aqua)
                $alarm = 'green';
            if ($ratio >= $this->_aqua && $ratio < $this->_blue)
                $alarm = 'aqua';
            if ($ratio >= $this->_blue)
                $alarm = 'blue';
        }

        if (!is_null($ratio) && $this->trend == 3) {
            if ($real > $plan) {
                if ($ratio > $this->_orange_cot)
                    $alarm = 'red';
                if ($ratio > $this->_yellow_cot && $ratio <= $this->_orange_cot)
                    $alarm = 'orange';
                if ($ratio <= $this->_yellow_cot)
                    $this->alarm = 'yellow';
            }
            if ($real < $plan_cot) {
                if ($ratio >= $this->_yellow)
                    $alarm = 'yellow';
                if ($ratio < $this->_yellow && $ratio >= $this->_orange)
                    $alarm = 'orange';
                if ($ratio < $this->_orange)
                    $alarm = 'red';
            }
        }

        $array = array('alarm' => $alarm, 'ratio' => $ratio, 'percent' => $percent);
        return $array;
    }

    protected function SetFlecha($cumulative) {
        $flecha = 'blank';
        $valor1 = null;

        if ($cumulative) {
            $real0 = $this->row_real[0]['acumulado_real'];
            $real1 = $this->row_real[1]['acumulado_real'];
            $plan0 = $this->row_plan[0]['acumulado_plan'];
            $plan_cot0 = $this->row_plan[0]['acumulado_plan_cot'];
            $plan1 = $this->row_plan[1]['acumulado_plan'];
            $plan_cot1 = $this->row_plan[1]['acumulado_plan_cot'];

        } else {
            $real0 = $this->row_real[0]['valor'];
            $real1 = $this->row_real[1]['valor'];
            $plan0 = $this->row_plan[0]['plan'];
            $plan_cot0 = $this->row_plan[0]['plan_cot'];
            $plan1 = $this->row_plan[1]['plan'];
            $plan_cot1 = $this->row_plan[1]['plan_cot'];
        }

        $array0 = get_ratio($real0, $plan0, $this->trend, $plan_cot0);
        $array1 = null;

        if ($this->nrow > 1 && $this->nrow_plan > 1)
            $array1 = get_ratio($real1, $plan1, $this->trend, $plan_cot1);

        if (is_null($array0) || is_null($array1)) {
            $flecha = 'blank';
            return $flecha;
        }

        $delta = ($array0['percent'] - $array1['percent']);

        if ($this->criterio == '>=') {
            if ($delta > 0)
                $flecha = 'green';
            elseif ($delta == 0)
                $flecha = 'yellow';
            else
                $flecha = 'red';
        }

        if ($this->criterio == '<=') {
            if ($delta < 0)
                $flecha = 'green';
            elseif ($delta == 0)
                $flecha = 'yellow';
            else
                $flecha = 'red';
        }

        if ($this->criterio == '[]') {
            if ($delta == 0)
                $flecha = 'green';
            else
                $flecha = 'red';
        }

        return $flecha;
    }

    protected function searchCriterio() {
        $sql = "select trend from tindicador_criterio where id_indicador = $this->id_indicador and year = $this->year ";
        $result = $this->do_sql_show_error('searchCriterio', $sql);
        $row = $this->clink->fetch_array($result);

        $this->trend = $row['trend'];
        $this->criterio = get_criterio($this->trend);
    }

    protected function _if_updated($carga, $date1, $date2) {
        $diff = diffDate($date1, $date2);
        $year = $diff['y'];
        $month = $diff['m'];
        $day = $diff['d'];

        switch ($carga) {
            case ('D'):
                if ($year > 0 || $month > 0 || $day > 0)
                    return false;
            case ('S'):
                if ($year > 0 || $month > 0 || $day > 7)
                    return false;
            case ('Q'):
                if ($year > 0 || $month > 0 || $day > 15)
                    return false;
            case ('M'):
                if ($year > 0 || $month > 1 || ($month == 1 && $day > 0))
                    return false;
            case ('T'):
                if ($year > 0 || $month > 3 || ($month == 3 && $day > 0))
                    return false;
            case ('E'):
                if ($year > 0 || $month > 6 || ($month == 6 && $day > 0))
                    return false;
            case ('A'):
                if ($year > 1 || ($year == 1 && ($month > 0 || $day > 0)))
                    return false;
            default:
                return true;
        }
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";

if (!class_exists('Tcalculator'))
    include_once "calculator.class.php";