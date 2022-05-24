<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

include_once "../config.inc.php";

if (!class_exists('Tbase_planning'))
    include_once "base_planning.class.php";
/**
 * Class Tschedule
 * crea arreglo de fechas
 */
class Tschedule extends Tbase {

    private $time_inicio, $time_fin, $fecha_tmp;
    public $input_array_dates;
    public $array_dates;
    public $cant_days;
    public $periodic;
    private $hit;

    public function __construct() {
        Tbase::__construct();
    }

    public function set_from_url_post() {
        $k = 0;

        $this->periodicidad = $_POST['periodicidad'];

        if ($this->periodicidad == 1)
            $this->carga = $_POST['carga'];

        if ($this->periodicidad == 2) {
            for ($i = 1; $i < 8; ++$i) {
                if (!is_null($_POST['dayweek' . $i])) {
                    $this->dayweek[$i] = $_POST['dayweek' . $i];
                    ++$k;
            }   }
        }

        if ($this->periodicidad == 3) {
            $this->fixed_day = $_POST['fixed_day'];

            if ($this->fixed_day == 0) {
                $this->carga = $_POST['input_carga4'];
            }
            if ($this->fixed_day == 1) {
                $this->carga = $_POST['sel_carga'];
                $this->dayweek = $_POST['dayweek0'];
            }
        }

        if ($this->periodicidad == 4) {
            $this->input_array_dates = preg_split("[,]", $_POST['_chain'], null, PREG_SPLIT_NO_EMPTY);
        }
    }

    public function set_dates() {
        $this->time_inicio = date('H:i:s', strtotime($this->fecha_inicio_plan));
        $this->time_fin = date('H:i:s', strtotime($this->fecha_fin_plan));
        $fecha = date('Y-m-d', strtotime($this->fecha_inicio_plan));
        $this->fecha_tmp = $fecha . ' ' . $this->time_fin;

        if (isset($this->array_dates)) unset($this->array_dates);
        $this->array_dates = array();
    }

    public function create_array_dates() {
        $this->hit = ($this->periodic && (strtotime(date('Y-m-d', strtotime($this->fecha_inicio_plan))) != date('Y-m-d', strtotime($this->fecha_fin_plan)))) ? 1 : 0;

        if ($this->periodicidad < 3)
            $this->set_date_array_0_1_2();
        if ($this->periodicidad == 3)
            $this->set_date_array_3();
        if ($this->periodicidad == 4)
            $this->set_date_array_4();

        return $this->array_dates;
    }

    private function set_date_array_0_1_2() {
        $cant = 0;
        $fecha_inicio = $this->fecha_inicio_plan;

        if ($this->periodicidad == 0) {
            $fecha_inicio = $this->get_work_day($fecha_inicio);
            $fecha_fin = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_fin;

            $this->array_dates[$cant++] = array('inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'hit' => $this->hit);
        }

        if ($this->periodicidad == 1) {
            $i = 0;
            if (empty($this->carga))
                $this->carga= 1;

            do {
                $fecha_inicio = $this->get_work_day($fecha_inicio);
                $fecha_fin = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_fin;

                if (strtotime($fecha_fin) <= strtotime($this->fecha_fin_plan))
                    $this->array_dates[$cant++] = array('inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'hit' => $this->hit);
                // if ($i > 0)
                    $fecha_inicio = add_date($fecha_inicio, $this->carga);
                $fecha_inicio = $this->get_work_day($fecha_inicio);
                ++$i;
                if ($i >= 1830)
                    break; // limitando a 5 years
            } while (strtotime($fecha_inicio) <= strtotime($this->fecha_fin_plan));
        }

        if ($this->periodicidad == 2) {
            $this->carga = 7;
            $dayweek = explode('-', $this->dayweek);

            $month_inicio = date('m', strtotime($this->fecha_inicio_plan));
            $year_inicio = date('Y', strtotime($this->fecha_inicio_plan));
            $month_fin = date('m', strtotime($this->fecha_fin_plan));
            $year_fin = date('Y', strtotime($this->fecha_fin_plan));

            for ($year = $year_inicio; $year <= $year_fin; ++$year) {
                if ($year == $year_inicio)
                    $month0 = $month_inicio;
                if ($year == $year_fin)
                    $month1 = $month_fin;
                if ($year > $year_inicio)
                    $month0 = 1;
                if ($year < $year_fin)
                    $month1 = 12;

                for ($month = $month0; $month <= $month1; ++$month) {
                    if (strtotime($fecha_fin) > strtotime($this->fecha_fin_plan))
                        break;
                    // recorre las 4 semanas que puede tener un mes
                    for ($j = 1; $j < 6; $j++) {
                        if (strtotime($fecha_fin) > strtotime($this->fecha_fin_plan))
                            break;
                        // recorre las 7 dias de la semana
                        for ($i = 1; $i < 8; $i++) {
                            $wday = (int) $dayweek[$i];
                            if (!$wday)
                                continue;

                            $fecha_inicio = get_date_day($i, $j, $month, $year);
                            if (!$fecha_inicio)
                                break;
                            if (strtotime($fecha_fin) > strtotime($this->fecha_fin_plan))
                                break;

                            if (empty($fecha_inicio))
                                continue;

                            $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_inicio;
                            $fecha_inicio = $this->get_work_day($fecha_inicio);
                            if (strtotime($fecha_inicio) < strtotime($this->fecha_inicio_plan))
                                continue;

                            $fecha_fin = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_fin;

                            if (strtotime($fecha_fin) <= strtotime($this->fecha_fin_plan)) {
                                $this->array_dates[$cant++] = array('inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'hit' => $this->hit);
            }   }   }   }   }
        }
        $this->cant = $cant;
    }

    private function set_date_array_3() {
        $cant = 0;
        $fecha_inicio = $this->fecha_inicio_plan;

        $month_inicio = (int)date('m', strtotime($this->fecha_inicio_plan));
        $year_inicio = (int)date('Y', strtotime($this->fecha_inicio_plan));
        $month_fin = (int)date('m', strtotime($this->fecha_fin_plan));
        $year_fin = (int)date('Y', strtotime($this->fecha_fin_plan));

        for ($year = $year_inicio; $year <= $year_fin; ++$year) {
            if ($year == $year_inicio)
                $month0 = $month_inicio;
            if ($year == $year_fin)
                $month1 = $month_fin;
            if ($year > $year_inicio)
                $month0 = 1;
            if ($year < $year_fin)
                $month1 = 12;

            for ($month = $month0; $month <= $month1; ++$month) {
                if ($this->fixed_day == 0) {
                    $fecha_inicio = $year . '-' . str_pad($month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($this->carga, 2, "0", STR_PAD_LEFT);
                }
                if ($this->fixed_day == 1) {
                    $fecha_inicio = get_date_day((int)$this->dayweek, (int)$this->carga, $month, $year);
                    if (empty($fecha_inicio))
                        continue;
                }

                $fecha_inicio = $this->get_work_day($fecha_inicio);
                $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_inicio;
                $fecha_fin = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_fin;
                /*
                if (strtotime(date('Y-m-d', strtotime($fecha_inicio))) < strtotime(date('Y-m-d', strtotime($this->fecha_inicio_plan))))
                    $this->array_dates[$cant++] = array('inicio' => date('Y-m-d', strtotime($this->fecha_inicio_plan)).' '.$this->time_inicio,
                                                        'fin' =>date('Y-m-d', strtotime($this->fecha_inicio_plan)).' '.$this->time_fin, 'hit' => $this->hit);
                 */
                if (strtotime($fecha_fin) <= strtotime($this->fecha_fin_plan) && strtotime($fecha_inicio) >= strtotime($this->fecha_inicio_plan))
                    $this->array_dates[$cant++] = array('inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'hit' => $this->hit);
            }
        }
        $this->cant = $cant;
    }

    private function set_date_array_4() {
        $cant = 0;
        foreach ($this->input_array_dates as $fecha_inicio) {
            $fecha_inicio = $this->get_work_day($fecha_inicio);
            $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_inicio;
            $fecha_fin = date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_fin;

            if (strtotime(date('Y-m-d', strtotime($fecha_inicio))) < strtotime(date('Y-m-d', strtotime($this->fecha_inicio_plan))))
                $this->array_dates[$cant++] = array('inicio' => date('Y-m-d', strtotime($this->fecha_inicio_plan)).$this->time_inicio,
                                                    'fin' =>date('Y-m-d', strtotime($this->fecha_inicio_plan)).$this->time_fin, 'hit' => $this->hit);
            if ((strtotime($fecha_fin) <= strtotime($this->fecha_fin_plan)) && (strtotime($fecha_inicio) >= strtotime($this->fecha_inicio_plan)))
                $this->array_dates[$cant++] = array('inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'hit' => $this->hit);
        }
        $this->cant = $cant;
    }

    public function get_work_day($fecha) {
        global $day_feriados;

        if (is_null($fecha)) {
            echo "FUNCTION: get_work_day ERROR: La variable fecha no puede ser NULL ";
            exit;
        }

        $time = strtotime($fecha);
        if (date('N', $time) == 6 && empty($this->saturday)) {
            $fecha = add_date($fecha, 1);
            $time = strtotime($fecha);
        }
        if (date('N', $time) == 7 && empty($this->sunday)) {
            $fecha = add_date($fecha, 1);
            $time = strtotime($fecha);
        }
        $m_d = date('j/m', $time);
        if (array_search($m_d, $day_feriados) !== false && empty($this->freeday)) {
            $fecha = add_date($fecha, 1);
            $fecha = $this->get_work_day($fecha);
        }

        return $fecha;
    }

    public function add_days_to_schudele() {
        $cant_dates = $this->cant;
        $cant = 0;
        $j = 1;
        $index = 0;
        $array_dates = array();
        $fecha_next = null;
        $fecha = null;
        $fecha_fin = null;

        for ($i = 0; $i < $cant_dates; $i++) {
            $fecha = $this->array_dates[$i]['inicio'];
            $fecha_fin = $this->array_dates[$i]['fin'];
            $fecha_next = $this->array_dates[$i + 1]['inicio'];

            $index = ($cant == 0) ? 0 : ++$cant;
            $array_dates[$index] = array('inicio' => $fecha, 'fin' => $fecha_fin, 'hit' => 1);
            $array_dates[++$cant] = array('inicio' => $fecha, 'fin' => $fecha_fin, 'hit' => 0);
            $j = 1;
            $break = is_null($fecha_next) ? true : false;

            while (!$break && $j < (int) $this->cant_days) {
                $fecha = add_date($fecha, 1);
                $fecha = $this->get_work_day($fecha);
                $fecha_fin = date('Y-m-d', strtotime($fecha)) . ' ' . $this->time_fin;

                $break = true;
                if (!is_null($fecha_next) && strtotime($fecha) < strtotime($fecha_next))
                    $break = false;

                if (!$break) {
                    $array_dates[++$cant] = array('inicio' => $fecha, 'fin' => $fecha_fin, 'hit' => 0);
                    ++$j;
                }
            }
            $array_dates[$index]['fin'] = $fecha_fin;

            while (strtotime($fecha_fin) >= strtotime($fecha_next)) {
                ++$i;
                $fecha_next= $this->array_dates[$i + 1]['inicio'];
                if (is_null($fecha_next))
                    break;
            }

            if (is_null($fecha_next))
                break;
        }

        if (is_null($fecha_next)) {
            while ($j < $this->cant_days) {
                $fecha = add_date($fecha, 1);
                $fecha = $this->get_work_day($fecha);
                $fecha_fin = date('Y-m-d', strtotime($fecha)) . ' ' . $this->time_fin;

                //    if (strtotime($fecha) <= strtotime($this->fecha_fin_plan)) {
                $array_dates[++$cant] = array('inicio' => $fecha, 'fin' => $fecha_fin, 'hit' => 0);
                ++$j;
                //    }
            }

            $array_dates[$index]['fin'] = $fecha_fin;
        }

        $this->cant = ++$cant;

        unset($this->array_dates);
        $this->array_dates = array();

        for ($i = 0; $i < $cant; $i++)
            $this->array_dates[$i] = $array_dates[$i];

        return $this->array_dates;
    }

}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";
include_once "code.class.php";