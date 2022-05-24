<?php

/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 11/7/2019
 * Time: 5:25 p.m.
 */

include_once "perspectiva.class.php";
include_once "inductor.class.php";
include_once "politica.class.php";
include_once "objetivo.class.php";

include_once "proceso_item.class.php";

include_once "indicador.class.php";
include_once "programa.class.php";
include_once "registro.class.php";
include_once "cell.class.php";


class Tdata extends Tbase {
    private $obj,
            $obj_peso;
    private $item;
    public $title;

    public $array_alarm;

    protected $scale;
    protected $periodicidad;
    protected $cumulative;
    protected $trend;
    protected $carga;
    protected $year;
    protected $month;
    protected $inicio;
    protected $fin;

    public $dates;
    public $array_data;
    public $ydata_real;
    public $ydata_plan;
    public $xlabels;


    public $radio_cumulative;
    public $radio_formulated;

    // para los indicadores
    private $date_below_cutoff;  // Si se considera la fecha inicial del intervalo en la busqueda del valor

    public function SetScale($id= null) {
        $this->scale= !is_null($id) ? $id : 'M';
    }

    public function __construct($clink, $item= 'indicador') {
        $this->clink= $clink;
        $this->item= !empty($item) ? $item : 'indicador';
        $this->date_below_cutoff= null;
    }

    public function create_intervals() {
        global $periodo_month;
        global $meses_short_array;
        global $periodo_month_inv;

        $time= new TTime();
        $this->inicio= $this->year > $this->inicio ? $this->year : $this->inicio;
        $initdate= $this->inicio.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-01';
        $period= $periodo_month[$this->scale];

        if ($period == $periodo_month['D'])
            $lastdate= add_date($initdate, 0, 1);
        if ($period == $periodo_month['S'])
            $lastdate= add_date($initdate, 0, 3);
        if ($period == $periodo_month['Q'])
            $lastdate= add_date($initdate, 0, 6);
        if ($period == $periodo_month['M'])
            $lastdate= add_date($initdate, 0, 0, 1);
        if ($period > $periodo_month['M'])
            $lastdate= $this->fin.'-12-31';

        $fix_work_day= $this->scale == 'S' || $this->scale == 'Q' ? true : false;
        $show_year= $this->inicio != $this->fin ? true : false;

        $year= date('Y', strtotime($initdate));
        $month= date('m', strtotime($initdate));
        $day= date('m', strtotime($initdate));

        $i= 0;
        for ($date=$initdate; strtotime($date) <= strtotime($lastdate); $date= date('Y-m-d', strtotime(add_date($date,1)))) {
            $totime= strtotime($date);
            $y= date('Y', $totime);
            $m= date('m', $totime);
            $d= date('d', $totime);

            $time->SetYear($y);
            $time->SetMonth($m);
            $time->SetDay($d);

            $carga= $time->ifDayPeriodo($this->scale, $fix_work_day);
            $_date= date('Y-m-d', strtotime(add_date($date,1)));

            if ($carga) {
                $label= null;
                $d= (int)$d;

                if ($i == 0) {
                    $label= $show_year ? "{$year}," : "";
                    $label.= "{$meses_short_array[(int)$m]}";
                    $label.= $fix_work_day  || $this->scale == 'D' ? ",{$d}" : "";

                } else {
                    if ($year != $y) {
                        $label= $y;
                        $year= $y;
                    }
                    if ((int)$month != (int)$m) {
                        $label.= (!is_null($label) ? "{$label}," : "").$meses_short_array[(int)$m];
                        $month= $m;
                    }
                    if ($fix_work_day || $this->scale == 'D')
                        $label.= !is_null($label) ? ",{$d}" : $d;
                }

                $this->dates[date('Y-m-d', strtotime($date))]= array('label'=>$label, 'date'=>$date, 'valor'=>null, 'plan'=>null, 'alarm'=>null);
                ++$i;
            }
        }

        foreach ($this->dates as $date)
            $this->xlabels[]= $date['label'];
    }

    public function set() {
        global $periodo_month;
        global $sign;
        global $decimal;

        switch ($this->item) {
            case 'politica':
                $this->obj= new Tpolitica($this->clink);
                break;
            case 'objetivo':
                $this->obj= new Tobjetivo($this->clink);
                break;
            case 'objetivo_ci':
                $this->obj= new Tobjetivo($this->clink);
                break;
            case 'perspectiva':
                $this->obj= new Tperspectiva($this->clink);
                break;
            case 'programa':
                $this->obj= new Tprograma($this->clink);
                break;
            case 'inductor':
                $this->obj= new Tinductor($this->clink);
                break;
            case 'indicador':
                $this->obj= new Tindicador($this->clink);
                break;
            case 'proceso':
                $this->obj= new Tproceso($this->clink);
                break;
            case 'empresa':
                $this->obj= new Tproceso($this->clink);
                break;
            default:
                return;
                break;
        }

        $this->obj->SetYear($this->year);
        $this->obj->SetMonth($this->month);
        $this->obj->SetDay($this->day);

        $this->obj->Set($this->id);
        $this->periodicidad= 'M';
        $this->cumulative= false;

        if ($this->item == 'indicador') {
            $this->periodicidad= $this->obj->GetPeriodicidad();
            $this->carga= $this->obj->GetCarga();
            $this->cumulative= $this->obj->GetIfCumulative();
            $this->trend= $this->obj->GetTrend();
            $sign= $this->obj->GetUnidad();
            $decimal= $this->obj->GetDecimal();
            if ($periodo_month[$this->scale] < $periodo_month[$this->carga])
                $this->scale= $this->carga;
        }
    }

    private function _calcular(&$array) {
        global $array_date_bellow_cutoff;
        global $array_procesos_down_entity;
        global $string_procesos_down_entity;
    
        $array= null;
        $value= null;

        switch($this->item) {
            case 'politica':
                $value= $this->obj_peso->calcular_politica($this->id, $string_procesos_down_entity);
                break;
            case 'objetivo':
                $value= $this->obj_peso->calcular_objetivo($this->id);
                break;
            case 'perspectiva':
                $value= $this->obj_peso->calcular_perspectiva($this->id, $array_procesos_down_entity);
                break;
            case 'programa':
                $value= $this->obj_peso->calcular_programa($this->id);
                break;
            case 'inductor':
                $value= $this->obj_peso->calcular_inductor($this->id);
                break;
            case 'indicador':
                break;
            case 'proceso':
                $value= $this->obj_peso->calcular_proceso($this->id);
                break;
            case 'empresa':
                $value= $this->obj_peso->calcular_empresa($this->id);
                break;
            default:
                null;
                break;
        }

        if ($this->item == 'indicador') {
            $cumulative= null;
            $this->obj_peso->SetPeriodicidad($this->scale);
            $array= $this->obj_peso->calcular_indicador($this->id, true, $cumulative, $this->date_below_cutoff);
            $value= !$this->cumulative ? $array['ratio'] : $array['ratio_cumulative'];
        }

        return $value;
    }

    private function _title() {
        switch ($this->item) {
            case 'politica':
                $this->title = "LINEAMIENTO:".$this->obj->GetNumero()." AÑO:$this->year";
                break;
            case 'objetivo':
                $this->title = "OBJETIVO ESTRATÉGICO:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            case 'perspectiva':
                $this->title = "PERSPECTIVA:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            case 'programa':
                $this->title = "PROGRAMA:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            case 'inductor':
                $this->title = "OBJETIVO DE TRABAJO:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            case 'indicador':
                $this->title = "INDICADOR:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            case 'proceso':
                $this->title = "UNIDAD ORGANIZATIVA:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            case 'empresa':
                $this->title = "UNIDAD ORGANIZATIVA:".$this->obj->GetNombre()." AÑO:$this->year";
                break;
            default:
                return;
                break;
        }
    }

    private function set_matrix() {
        $this->_title();
        $this->array_alarm= null;
        $this->obj_peso= new Tpeso_calculo($this->clink);

        if ($this->item != 'indicador') {
            $this->obj_peso->SetIdProceso($this->id_proceso);
            $this->obj_peso->set_id_proceso_code($this->id_proceso_code);
        }

        reset($this->dates);
        $array_years= array();
        foreach ($this->dates as $date) {
            $year= (int)date('Y', strtotime($date['date']));
            if ($array_years[$year])
                continue;
            $array_years[$year]= 1;
            $this->obj_peso->SetYear($year);
            $this->obj_peso->set_matrix();
        }
    }

    public function get() {
        // Se utiliza a la hora de graficar mantener el valor del $date_below_cutoff cuando se trabaja con otras magnitudes que no son indicadores
        // Ejemplo las perspectivas que utilizan varios indicadores
        global $array_date_bellow_cutoff;

        $array= null;
        $obj_signal= new Tlist_signals($this->clink);
        $this->set_matrix();

        $i= 0;
        $imax= count($this->dates);
        $this->date_below_cutoff= null;
        if ($this->item != 'indicador')
            $array_date_bellow_cutoff= array();

        reset($this->dates);
        foreach ($this->dates as $date) {
            ++$i;
            $year= (int)date('Y', strtotime($date['date']));
            $month= (int)date('m', strtotime($date['date']));
            $day= date('d', strtotime($date['date']));
            $_date= date('Y-m-d', strtotime($date['date']));

            $this->obj_peso->SetYear($year);
            $this->obj_peso->SetMonth($month);
            $this->obj_peso->SetDay($day);
            $value= null;
            $alarm= null;

            if (isset($array)) {
                unset($array);
                $array = null;
            }

            $this->obj_peso->init_calcular();
            $this->obj_peso->SetYearMonth($year, $month);
            $array= null;
            $value= $this->_calcular($array);

            if ($this->item != 'indicador') {
                $this->dates[$_date]['valor']= !is_null($value) ? $value : null;
                if (is_array($array))
                    $this->dates[$_date]['plan']= $array['plan'];

            } else {
                $this->date_below_cutoff= !is_null($array['reg_date_real']) ? $array['reg_date_real'] : null;

                if ($this->trend != 3) {
                    $this->dates[$_date]['valor']= $this->cumulative && $this->radio_cumulative ? $array['acumulado_real'] : $array['real'];
                    $this->dates[$_date]['plan']= $this->cumulative && $this->radio_cumulative ? $array['acumulado_plan'] : $array['plan'];
                } else {
                    $this->dates[$_date]['valor']= $this->cumulative && $this->radio_cumulative ? $array['acumulado_real_cot'] : $array['real_cot'];
                    $this->dates[$_date]['plan']= $this->cumulative && $this->radio_cumulative ? $array['acumulado_plan_cot'] : $array['plan_cot'];
                }
            }

            $this->dates[$_date]['alarm']= $obj_signal->get_alarm($value, null, false);
        }

        $this->obj_peso->close_matrix();
    }

    public function create_data() {
        reset($this->dates);
        foreach ($this->dates as $date) {
            $this->ydata_real[]= $date['valor'];
            $this->ydata_plan[]= $date['plan'];
        }
    }

    public function normalize() {
        $abs= 0;
        reset($this->array_data);

        for ($month= 1; $month <= $this->month; ++$month) {
            if (!is_null($this->array_data[$month-1]))
                $abs+= abs($this->array_data[$month-1]);
        }
        for ($month= 1; $month <= $this->month; ++$month) {
            if (!is_null($this->array_data[$month-1]))
                $this->array_data[$month-1]= ((float)$this->array_data[$month-1])/(float)$abs;
        }

        reset($this->array_data);
    }
}