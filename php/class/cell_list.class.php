<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../config.inc.php";

if (!class_exists('Tcell'))
    include_once "cell.class.php";

class TCell_list extends  Tcell {
    private $array_month;
    private $array_real;
    private $array_plan;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TCell::__construct($clink);

        $this->array_month= null;
        $this->array_real= null;
        $this->array_plan= null;
    }

    private function _to_array_data_month() {
        if (isset($this->array_real)) unset($this->array_real);
        if (isset($this->array_plan)) unset($this->array_plan);
        $array= null;

        if (is_null($this->trend) || is_null($this->cumulative))
            Tindicador::Set($this->id_indicador);

        $sql= "select * from tregistro where year = $this->year and id_indicador = $this->id_indicador ";
        $result= $this->do_sql_show_error('_to_array_data_month', $sql);
        $row= $this->clink->fetch_array($result);

        if ($this->cumulative) {
            if (is_null($row['acumulado_plan']))
                $this->set_cumulative_plan();
            if (is_null($row['acumulado_real']))
                $this->set_cumulative_real();
        }

        if ($this->cumulative && (is_null($row['acumulado_plan']) || is_null($row['acumulado_real']))) {
            $sql= "select * from tregistro where year = $this->year and id_indicador = $this->id_indicador ";
            $result= $this->do_sql_show_error('_to_array_data_month', $sql);
        }

        $this->clink->data_seek($result);

        // para la operacion de la funcion private function get_month_corte($month)
        while ($row= $this->clink->fetch_array($result)) {
            if (is_null($this->array_month[(int)$row['month']]) || $row['corte'])
                $this->array_month[(int)$row['month']]= $row['corte'];
        }

        $this->clink->data_seek($result);
        $date_corte= null;
        while ($row= $this->clink->fetch_array($result)) {
            $day= $row['day'];
            $month= $row['month'];

            $value= $row['valor'];
            $str_real= $row['observacion_real'];

            if (boolean($row['corte'])) {
                $corte= "{$row['day']}/{$row['month']}/{$row['year']}";
                $plan= $row['plan'];
                $plan_cot= $row['plan_cot'];
                $str_plan= $row['observacion_plan'];

                $acumulado_plan= $this->cumulative ? $row['acumulado_plan'] : null;
                $acumulado_plan_cot= $this->cumulative ? $row['acumulado_plan_cot'] : null;

            } else {
                if (is_null($date_corte)
                    || (!is_null($date_corte) && ($month > $date_corte['month'] || ($month == $date_corte['month'] && $day > $date_corte['day'])))) {
                    $date_corte= array();
                    $this->busca_corte(false, $month, $day, $this->year, $date_corte);
                }

                $corte= "{$date_corte['day']}/{$date_corte['month']}/{$date_corte['year']}";
                $plan= $date_corte['plan'];
                $plan_cot= $date_corte['plan_cot'];
                $str_plan= $date_corte['observacion_plan'];

                $acumulado_plan= $this->cumulative ? $date_corte['acumulado_plan'] : null;
                $acumulado_plan_cot= $this->cumulative ? $date_corte['acumulado_plan_cot'] : null;
            }

            if (is_null($this->array_real[(int)$month]) || ($this->array_real[(int)$month]['day'] <= $day && (!is_null($value) || !is_null($str_real)))) {
                $this->array_real[(int)$month]= array('day'=> $day, 'real'=>$value, 'observacion_real'=>$str_real,
                    'id_usuario_real'=>$row['id_usuario_real'], 'cronos_real'=>$row['cronos_real'], 'reg_date_real'=>$row['reg_date_real'],
                    'acumulado_real'=>$this->cumulative ? $row['acumulado_real'] : null, 'corte'=>$corte);
            }
            if (is_null($this->array_plan[(int)$month]) || ($this->array_plan[(int)$month]['day'] <= $day && (!is_null($plan) || !is_null($str_plan)))) {
                $this->array_plan[(int)$month]= array('day'=> $day, 'plan'=>$plan, 'plan_cot'=>$plan_cot, 'observacion_plan'=>$str_plan,
                    'id_usuario_plan'=>$row['id_usuario_plan'], 'cronos_plan'=>$row['cronos_plan'],
                    'acumulado_plan'=>$acumulado_plan, 'acumulado_plan_cot'=>$acumulado_plan_cot, 'corte'=>$corte);
            }
        }
    }

    public function GetCells_monthly() {
        $this->_to_array_data_month();

        $array= null;

        for ($month= 1; $month < 13; $month++) {
            $day= $this->array_real[(int)$month]['day'];

            $value= $this->array_real[(int)$month]['real'];
            $acumulado_real= $this->cumulative ? $this->array_real[(int)$month]['acumulado_real'] : null;

            $str_real= $this->array_real[(int)$month]['observacion_real'];
            $id_usuario_real= $this->array_real[(int)$month]['id_usuario_real'];
            $cronos_real= $this->array_real[(int)$month]['cronos_real'];
            $reg_date_real= $this->array_real[(int)$month]['reg_date_real'];

            $corte= $this->array_plan[(int)$month]['corte'];
            $plan= $this->array_plan[(int)$month]['plan'];
            $acumulado_plan= $this->cumulative ? $this->array_plan[(int)$month]['acumulado_plan'] : null;
            $plan_cot= $this->array_plan[(int)$month]['plan_cot'];
            $acumulado_plan_cot= $this->cumulative ? $this->array_plan[(int)$month]['acumulado_plan_cot'] : null;

            $str_plan= $this->array_plan[(int)$month]['observacion_plan'];
            $id_usuario_plan= $this->array_plan[(int)$month]['id_usuario_plan'];
            $cronos_plan= $this->array_plan[(int)$month]['cronos_plan'];

            $_month= $this->get_month_corte($month);
            $_month= !empty($_month) ? $_month : $month;

            $_array= get_ratio($value, $plan, $this->trend, $plan_cot);
            $ratio= $_array['ratio'];
            $percent= $_array['percent'];

            $percent_cumulative= null;
            $ratio_cumulative= null;

            if ($this->cumulative) {
                $_array= get_ratio($acumulado_real, $acumulado_plan, $this->trend, $acumulado_plan_cot);
                $ratio_cumulative= $_array['ratio'];
                $percent_cumulative= $this->cumulative ? $_array['percent'] : null;
            }

            $array[(int)$month]= array('day'=>$day, 'month'=>$month, 'year'=>$this->year, 'real'=>$value,'plan'=>$plan, 'plan_cot'=>$plan_cot,
                                'ratio'=>$ratio, 'percent'=>$percent, 'observacion_plan'=>$str_plan, 'observacion_real'=>$str_real,
                                'id_usuario_real'=>$id_usuario_real, 'cronos_real'=>$cronos_real, 'reg_date_real'=>$reg_date_real,
                                'id_usuario_plan'=>$id_usuario_plan, 'cronos_plan'=>$cronos_plan,
                                'acumulado_plan'=>$acumulado_plan, 'acumulado_plan_cot'=>$acumulado_plan_cot, 'acumulado_real'=>$acumulado_real,
                                'ratio_cumulative'=>$ratio_cumulative, 'percent_cumulative'=>$percent_cumulative, 'corte'=>$corte);
        }
        return $array;
    }

    private function get_month_corte($month) {
        for ($i= $month; $i < 13; $i++) {
            if ($this->array_month[$i] == 1)
                return $i;
        }
        return null;
    }

   /// para la representacion del grafico, hay que revizarlo
    public function getdata_monthly() {
        $sql= "select * from tregistro where id_indicador = $this->id_indicador and year <= $this->year ";
        $sql.= "order by year asc, month asc, cronos desc ";
        $result = $this->do_sql_show_error('getdata_monthly', $sql);

        $i = 0;
        $this->nrow = 0;

        while ($row = $this->clink->fetch_array($result)) {
            $date = $this->meses[(int) $row['month']] . " " . $row['year'];
            $this->data_real[$date] = $row['valor'];

            if (!is_null($row['plan']))
                $this->data_diff[$date] = $row['valor'] - $row['plan'];
            else
                $this->data_diff[$date] = null;

            ++$i;
        }

        $this->nrow = $i;
        return $i;
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";

if (!class_exists('Tcalculator'))
    include_once "calculator.class.php";
if (!class_exists('Tregistro'))
    include_once "registro.class.php";