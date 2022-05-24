<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../config.inc.php";

if (!class_exists('Tbase_cell'))
    include_once "base_cell.class.php";

class Tcell extends Tbase_cell {

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase_cell::__construct($clink);
    }

    public function SetIndicador($id= null) {
         Tindicador::SetYear($this->year);
         Tindicador::__construct($this->clink);
         $this->id_indicador= !empty($id) ? $id : $this->id_indicador;
         Tindicador::Set($this->id_indicador);

         $this->used_compute_function= false;

         if ($this->formulated) {
             if (isset($this->obj_calc))
                 unset($this->obj_calc);
             $this->obj_calc= new Tcalculator($this->clink);

             $this->obj_calc->recompute= (int)$this->recompute;
             $this->obj_calc->SetNombre($this->nombre);
             $this->obj_calc->SetIdIndicador($this->id_indicador);
             $this->obj_calc->set_id_indicador_code($this->id_indicador_code);
         }
     }

     public function _init() {
         if (!$this->formulated)
             return null;
         if ( $this->obj_calc)
            $this->obj_calc->_init();
     }

    public function _set() {
        $this->blank_value();
        $this->blank_plan();

        if (!empty($this->scale) && $this->scale > $this->periodicidad)
            $this->periodicidad= $this->scale;

        $in_interval= $this->select_interval($date, $init_date, $end_date, $reg_date_plan);
        $this->dates= array('date'=>$date, 'init'=>$init_date, 'end'=>$end_date, 'corte'=>$reg_date_plan);
        $this->buscar_valor();

        if (!empty($this->nrow))
            $this->value= $this->row_real[0]['valor'];

        $result= $this->buscar_plan($this->reg_date);
        $this->valid_period= $this->ifValid_period();

        $ratio= null;

        if ($result && $this->valid_period) {
            if ($this->not_null_real_found && $this->not_null_plan_found) {
                $array= $this->_set_alarm($this->plan, $this->value, $this->plan_cot);
                $this->alarm= $array['alarm'];
                $this->ratio= $array['ratio'];
                $this->percent= $array['percent'];
            }
            if ($this->cumulative && ($this->not_null_acumulado_real_found && $this->not_null_acumulado_plan_found)) {
                $array= $this->_set_alarm($this->acumulado_plan, $this->acumulado_real, $this->acumulado_plan_cot);
                $this->alarm_cumulative= $array['alarm'];
                $this->ratio_cumulative= $array['ratio'];
                $this->percent_cumulative= $array['percent'];
            }
        }

       return $this->ratio;
    }

    public function get_register() {
        global $periodo_month;

        $this->intervals= array();
        $this->blank_value();
        $this->blank_plan();
        $ratio= null;

        if (!empty($this->scale)) {
            if ($this->scale > $this->periodicidad && $periodo_month[$this->scale] >= $periodo_month['M'])
                $this->periodicidad = $this->scale;
            if ($periodo_month[$this->scale] < $periodo_month['M'])
                $this->scale= null;
        }

        $in_interval= $this->select_interval($date, $init_date, $end_date, $reg_date_plan);
        $this->dates= array('date'=>$date, 'init'=>$init_date, 'end'=>$end_date, 'corte'=>$reg_date_plan);

        if (!$this->fix_interval || ($this->fix_interval && $in_interval)) {
            $this->buscar_valor();

            if ($this->nrow > 0) {
                $this->value= $this->row_real[0]['valor'];
                $this->acumulado_real= $this->cumulative ? $this->row_real[0]['acumulado_real'] : null;
                $this->observacion_real= !empty($this->row_real[0]['observacion']) ? stripslashes($this->row_real[0]['observacion']) : null;
                $this->origen_data_real= !empty($this->row_real[0]['origen_data']) ? stripslashes($this->row_real[0]['origen_data']) : null;

                $this->updated= ($this->formulated && $this->used_compute_function) ? $this->obj_calc->updated : $this->ifUpdated();
                if (!$this->formulated && ($this->cumulative && is_null($this->acumulado_real)))
                    $this->set_cumulative_real();
            }
        }

        $in_interval= !is_null($this->reg_date) ? $this->test_if_in_interval($this->reg_date) : false;

        if (($this->fix_interval && !$in_interval) || $this->nrow == 0) {
            $this->nrow= 0;
            $this->blank_value();
        }

        $result= $this->buscar_plan();
        $in_interval= !is_null($this->reg_date_plan) ? $this->test_if_in_interval($this->reg_date_plan, true) : false;

        if (!$result || ($this->fix_interval && !$in_interval))
            $this->blank_plan();
        else {
            $this->updated_plan= ($this->formulated && $this->used_compute_function) ? $this->row_plan[0]['updated_plan'] : $this->ifUpdated_plan();
            $this->valid_period= ($this->formulated && $this->used_compute_function) ? $this->row_real[0]['valid_period'] : $this->ifValid_period();

            if (!$this->formulated && ($this->cumulative && (is_null($this->acumulado_plan) && is_null($this->acumulado_plan_cot))))
                $this->set_cumulative_plan();
        }

        return $result;
    }

    public function Set() {
        $result= $this->get_register();

    //    if ($this->nrow > 0 && $result && $this->valid_period) {
        if ($this->nrow > 0 && $result) {
            if ($this->not_null_real_found && $this->not_null_plan_found) {
                $array= $this->_set_alarm($this->plan, $this->value, $this->plan_cot);
                $this->alarm= $array['alarm'];
                $this->ratio= $array['ratio'];
                $this->percent= $array['percent'];

                $this->flecha= $this->SetFlecha(false);
            }
            if ($this->cumulative && ($this->not_null_acumulado_real_found && $this->not_null_acumulado_plan_found)) {
                $array= $this->_set_alarm($this->acumulado_plan, $this->acumulado_real, $this->acumulado_plan_cot);
                $this->alarm_cumulative= $array['alarm'];
                $this->ratio_cumulative= $array['ratio'];
                $this->percent_cumulative= $array['percent'];

                $this->flecha_cumulative= $this->SetFlecha(true);
            }
        }
        return $this->ratio;
    }

    private function _interval_no_scale(&$reg_date, &$init_date, &$end_date, &$reg_date_plan= null) {
        $found= false;
        $_reg_date= (int)strtotime($reg_date);
        $date= $_reg_date;

        reset($this->intervals);
        $size= count($this->intervals);

        $index= null;
        foreach ($this->intervals as $i => $segm) {
            $j= $this->periodicidad != 'D' ? $i+1 : $i;
            if (((($this->periodicidad != 'D' && strtotime($segm['date']) < $date) || ($this->periodicidad == 'D' && strtotime($segm['date']) <= $date)))
                && ($i < ($size-1) && $date <= (int)strtotime($this->intervals[$j]['date']))) {
                $init_date= $this->periodicidad != 'D' ? add_date($segm['date'],1) : $segm['date'];
                $end_date= $this->intervals[$j]['date'];
                $found= true;
                $index= $i;
            }
        }

        if (!$found) {
            $init_date= "{$this->year}-01-01";
            if (strtotime($init_date) <= $date && $date <= (int)strtotime($this->intervals[0]['date'])) {
                $end_date = $this->intervals[0]['date'];
                $found = true;
                $index= 0;
        }   }
        if ($found) {
            reset($this->intervals);
            for ($i= $index; $i < $size; $i++) {
                $_corte= $this->intervals[$i]['date'] ? strtotime($this->intervals[$i]['date']) : null;
                if ($this->intervals[$i]['corte'] && $_reg_date <= $_corte) {
                    $reg_date_plan= $this->intervals[$i]['date'];
                    break;
        }   }   }
        if (!$found) {
            if ((int)strtotime($this->intervals[$size-1]['date']) <= $date && $date <= strtotime($end_date)) {
                $init_date = $this->intervals[$size-1]['date'];
                $found = true;
                $index= $size-1;
        }   }
        if ($found && is_null($reg_date_plan)) {
            reset($this->intervals);
            for ($i= $index; $i >= 0; $i--) {
                if ($this->intervals[$i]['corte']) {
                    $reg_date_plan= $this->intervals[$i]['date'];
                    break;
        }   }   }

        if (!empty($init_date) && !empty($end_date))
            $found= true;

        return $found;
    }

    private function _interval_by_scale(&$reg_date, &$init_date, &$end_date) {
        global $periodo_month;

        $_reg_date= (int)strtotime($reg_date);
        $date= $_reg_date;

        $found= false;
        $init_date= "{$this->year}-01-01";
        $end_date= null;
        $this->xtime->SetYear($this->year);

        for ($i=1; $i <= 12 && is_null($end_date); ++$i) {
            $this->xtime->SetMonth($i);
            $lastday = $this->xtime->longmonth();
            $initday = $periodo_month[$this->scale] < $periodo_month['M'] ? 1 : 27;

            for ($day = $initday; $day <= $lastday; ++$day) {
                $this->xtime->SetDay($day);
                $corte = $this->xtime->ifDayPeriodo($this->scale) ? 1 : 0;
                $current= $this->year . "-" . str_pad($i, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);

                if ($corte) {
                    if ($date <= strtotime($current)) {
                        if (!empty($end_date))
                            $init_date= $end_date;
                        $end_date= $current;
                    } else {
                        $init_date= $this->periodicidad != 'D' ? add_date($current,1) : $current;
                    }
        }   }   }

        if (!empty($init_date) && !empty($end_date))
            $found= true;
        return $found;
    }

    private function select_interval(&$reg_date, &$init_date, &$end_date, &$reg_date_plan= null) {
        $found= false;

        if (empty($this->day)) {
            if (($this->year < $this->xtime->GetYear())
                    || ($this->year == (int)$this->xtime->GetYear() && $this->month < (int)$this->xtime->GetMonth())) {
                $this->xtime->SetYear($this->year);
                $this->xtime->SetMonth($this->month);
                $this->day= $this->xtime->longmonth();
            } else
                $this->day= $this->xtime->GetDay();
        }

        $reg_date= $this->year."-".str_pad($this->month,2,'0',STR_PAD_LEFT)."-".str_pad($this->day,2,'0',STR_PAD_LEFT);
        $end_date= $this->fix_year ? "{$this->year}-31-12" : "{$this->fin}-31-12";

        if ($this->scale == 'D') {
            $init_date= $reg_date;
            $end_date= $init_date;
        }

        $this->create_intervals();

        if (empty($this->scale))
            $found= $this->_interval_no_scale ($reg_date, $init_date, $end_date, $reg_date_plan);
        else {
            $found= $this->_interval_by_scale ($reg_date, $init_date, $end_date);
            $reg_date_plan= $end_date;
        }
        return $found;
    }

    private function test_if_in_interval($date, $is_plan= false) {
        $found= false;
        $date= strtotime($date);
        $corte= empty($this->scale) ? (!empty($this->dates['corte']) ? strtotime($this->dates['corte']) : null) : strtotime($this->dates['end']);
        $end= !$is_plan ? strtotime($this->dates['end']) : $corte;

        if (strtotime($this->dates['init']) <= $date && $date <= $end)
            $found= true;
        return $found;
    }

    private function ifUpdated() {
        $this->current_date = $this->year . '-' . $this->month . '-' . $this->day;
        return $this->_if_updated($this->carga, $this->reg_date, $this->current_date);
    }

    private function ifUpdated_plan() {
        if (is_null($this->reg_date_plan))
            return false;
        $this->current_date = $this->year . '-' . $this->month . '-' . $this->day;
        return $this->_if_updated($this->periodicidad, $this->current_date, $this->reg_date_plan);
    }

    private function ifValid_period() {
        if (is_null($this->reg_date_plan))
            return false;
        return $this->_if_updated($this->periodicidad, $this->reg_date, $this->reg_date_plan);
    }

    public function get_last_update($id_indicador= NULL) {
        if (!empty($id_indicador)) $this->id_indicador= $id_indicador;
        $this->reg_date= null;
        $this->fecha= null;

        $this->select_interval($date, $init_date, $end_date, $reg_date_plan);
        $this->dates= array('date'=>$date, 'init'=>$init_date, 'end'=>$end_date, 'corte'=>$reg_date_plan);

        $noempty= $this->buscar_valor();
        if (empty($noempty))
            return -1;

        $this->fecha= $this->reg_date;
        $this->updated= $this->ifUpdated();

        return $this->updated;
    }

    /**
     * Lee el ultimo valor reportado en treg_real segun fecha selecionado en el tablero
     * lee los ultimos valores registrados en los cortes anteriores a la fecha del ultimo valor tomado de treg_real
     * @return int
     */

    private function _current_valor(&$row= null) {
        $date = $this->dates['date'];
        $init_date = $this->dates['init'];
        $end_date = $this->dates['end'];
        $sql_piece= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "select *, $sql_piece as reg_date, ";
        if ($_SESSION['_DB_SYSTEM'] == "mysql")
            $sql.= "datediff(date('$date'), reg_date_real) as _diff ";
        else
            $sql.= "date_part('day', (date '$date' - reg_date_real)) as _diff ";
        $sql.= "from tregistro where id_indicador = $this->id_indicador ";
        if ($this->cumulative)
            $sql.= "and (acumulado_real is not null or length(observacion_real) > 0) ";
        else
            $sql.= "and (valor is not null or length(observacion_real) > 0) ";
        if ($_SESSION['_DB_SYSTEM'] == "mysql")
            $sql.= "and datediff(date('$date'), reg_date_real) >= 0 ";
        else
            $sql.= "and date_part('day', (date '$date' - reg_date_real)) > 0 ";
        $sql.= "and (reg_date_real <= " . str_to_date2pg("'$end_date'") . " ";
        if ($this->date_below_cutoff)
            $sql .= "and reg_date_real > " . str_to_date2pg("'$this->date_below_cutoff'") . " ";
        if ($this->fix_interval) {
            $sign = $this->strict_low_cutoff ? ">" : ">=";
            $sql.= "and ($sql_piece $sign ".str_to_date2pg("'$init_date'")." and $sql_piece <= ".str_to_date2pg("'$end_date'") . ") ";
        }
        $sql.= ") ";
        if ($this->fix_year)
            $sql .= "and year = $this->year ";
        $sql.= "order by _diff asc, reg_date desc LIMIT 1";

        $result = $this->do_sql_show_error('_current_valor{1}', $sql);
        $row = $this->clink->fetch_array($result);
        $this->reg_date = $row['reg_date'];
        $this->reg_date_real = $row['reg_date_real'];

        if (!empty($this->scale) && empty($this->reg_date))
            return null;

        if (empty($this->reg_date)) {
            $sql= "select *, $sql_piece as _reg_date, calcular as _caular_real ";
            $sql.= "from treg_real where id_indicador = $this->id_indicador ";
            if (!$this->cumulative)
                $sql.= "and (acumulado_real is not null or length(observacion) > 0) ";
            else
                $sql.= "and (valor is not null or length(observacion) > 0) ";
            if ($this->fix_interval) {
                $sign = $this->strict_low_cutoff ? ">" : ">=";
                $sql.= "and $sql_piece $sign ".str_to_date2pg("'$init_date'")." ";
                $sql.= "and $sql_piece <= ".str_to_date2pg("'$end_date'")." ";
            }
            $sql.= "and reg_date <= ".str_to_date2pg("'$end_date'")." and reg_date <= ".str_to_date2pg("'$date'")." order by cronos desc";

            $result = $this->do_sql_show_error('_current_valor{2}', $sql);
            $row = $this->clink->fetch_array($result);

            $this->reg_date = $row['_reg_date'];
            $this->reg_date_real = $row['reg_date'];
        }

        if (!empty($this->reg_date) && (!$this->formulated || ($this->formulated && !boolean($row['calcular_real'])))) {
            $this->row_real[$this->nrow]['id']= $this->id_indicador;
            $this->row_real[$this->nrow]['reg_date'] = $row['_reg_date'];
            $this->row_real[$this->nrow]['reg_date_real'] = $row['reg_date'];

            $this->row_real[$this->nrow]['origen_data']= $this->GetOrigenData('user_real', $row['origen_data']);
            $this->origen_data_real= stripslashes($this->row_real['origen_data']);

            $this->row_real[$this->nrow]['valor'] = $row['valor'];
            $this->row_real[$this->nrow]['acumulado_real'] = $this->cumulative ? $row['acumulado_real'] : null;
            $this->row_real[$this->nrow]['id_usuario'] = !empty($this->reg_date) ? $row['id_usuario_real'] : $row['id_usuario'];
            $this->row_real[$this->nrow]['fecha'] = $row['reg_date'];
            $this->row_real[$this->nrow]['observacion'] = $row['observacion_real'];
            $this->row_real[$this->nrow]['cronos'] = $row['cronos'];

            $this->not_null_real_found= !is_null($row['valor']) ? true : false;
            $this->not_null_acumulado_real_found= !is_null($row['acumulado_real']) && $this->cumulative ? true : false;
            return 1;
        }

        return null;
    }

    private function _traze_valor($noempty) {
        $sql_piece= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "select *, $sql_piece as reg_date ";
        $sql.= "from tregistro where id_indicador = $this->id_indicador and corte = " . boolean2pg(1) . " ";
        $sql.= "and $sql_piece < " . str_to_date2pg("'$this->reg_date'") . " ";
        $sql.= "and (valor is not null or length(observacion_real) > 0) order by reg_date desc ";
        $result = !is_null($this->reg_date) ? $this->do_sql_show_error('_traze_valor', $sql) : null;
        $num_rows= $result ? $this->clink->num_rows($result) : 0;

        while ($row = $this->clink->fetch_array($result)) {
            $this->row_real[$this->nrow]['updated'] = null;
            $this->row_real[$this->nrow]['valid_period'] = null;

            $value = $row['valor'];

            if ($this->formulated && boolean($row['calcular_real'])) {
                $this->obj_calc->contents = $this->calculo;
                $value = $this->calcular_traze($this->nrow, true, $row['year'], $row['month'], $row['day']);

                if (is_null($value))
                    continue;
            }

            if (!$this->formulated || ($this->formulated && (is_null($value) || !boolean($row['calcular_real'])))) {
                if (!$this->formulated || ($this->formulated && !boolean($row['calcular_real'])))
                    $this->row_real[$this->nrow]['valor'] = $row['valor'];
                else
                    $this->row_real[$this->nrow]['valor'] = $this->row_real[$this->nrow]['valor'];

                if (!$this->formulated || ($this->formulated && !boolean($row['calcular_real'])))
                    $this->row_real[$this->nrow]['acumulado_real'] = $this->cumulative ? $row['acumulado_real'] : null;

                $this->row_real[$this->nrow]['id_usuario'] = $row['id_usuario_real'];
                $this->row_real[$this->nrow]['fecha'] = $row['reg_date_real'];
                $this->row_real[$this->nrow]['reg_date'] = $row['reg_date'];
                $this->row_real[$this->nrow]['observacion'] = $row['observacion_real'];
                $this->row_real[$this->nrow]['cronos'] = $row['cronos_real'];
            }

            if (!$this->formulated)
                $this->row_real[$this->nrow]['origen_data'] = $this->GetOrigenData('user', $row['origen_data']);
            else
               $this->row_real[$this->nrow]['origen_data'] = $this->GetOrigenData('user_real', $row['origen_data']);

            $this->row_real[$this->nrow]['reg_date'] = $row['reg_date'];

            ++$this->nrow;
            ++$noempty;

            if ($this->nrow > 3)
                break;
        }

        return $noempty;
    }

    protected function buscar_valor() {
        $noempty = 0;
        $this->nrow = 0;
        $value = null;
        if (isset($this->row))
            unset($this->row);

        $row= null;
        $result= !$this->formulated || ($this->formulated && !$this->recompute) ? $this->_current_valor($row) : null;
        if ($result && !$this->compute_traze) {
            ++$this->nrow;
            return 1;
        }

        $this->row_real[$this->nrow]['updated'] = null;
        $this->row_real[$this->nrow]['valid_period'] = null;

        if ($this->formulated && (is_null($row['calcular_real']) || $this->recompute || (!is_null($row['calcular_real']) && boolean($row['calcular_real'])))) {
            $this->obj_calc->contents = $this->calculo;
            $value = $this->calcular($this->nrow, true, $this->year, $this->month, $this->day);

            $this->used_compute_function = true;
            $this->null_real_found= array_merge ((array)$this->null_real_found, (array)$this->obj_calc->null_real_found);
            $this->not_null_real_found= $this->obj_calc->not_null_real_found;
            $this->not_null_acumulado_real_found= $this->obj_calc->not_null_acumulado_real_found;
            if (is_null($value))
                return null;

        } else {
            if ($row && !is_null($row['valor']))
                $this->not_null_real_found= true;
            if ($row && !is_null($row['acumulado_real']))
                $this->not_null_acumulado_real_found= true;
        }

        if ((!$this->formulated && (!is_null($row['value']) || !is_null($row['acumulado_real'])))
                || ($this->formulated && (is_null($value) || !boolean($row['calcular_real'])))) {

            $this->reg_date_real = $row['reg_date_real'];
            $this->origen_data_real= stripslashes($this->row_real['origen_data']);

            /*
            $this->row_real[$this->nrow]['valor'] = $row['valor'];
            $this->row_real[$this->nrow]['acumulado_real'] = $row['acumulado_real'];
            $this->row_real[$this->nrow]['origen_data']= $this->GetOrigenData('user_real', $row['origen_data']);

            $this->row_real[$this->nrow]['id']= $this->id_indicador;
            $this->row_real[$this->nrow]['id_usuario'] = $row['id_usuario_real'];
            $this->row_real[$this->nrow]['fecha'] = $row['reg_date_real'];
            $this->row_real[$this->nrow]['reg_date'] = $row['reg_date'];
            $this->row_real[$this->nrow]['reg_date_real'] = $row['reg_date_real'];
            $this->row_real[$this->nrow]['observacion'] = $row['observacion_real'];
            $this->row_real[$this->nrow]['cronos'] = $row['cronos_real'];
            */

            if (!$this->formulated)
                $this->row_real[$this->nrow]['origen_data'] = $this->GetOrigenData('user', $row['origen_data']);
            else
                $this->row_real[$this->nrow]['origen_data'] = $this->GetOrigenData('user_real', $row['origen_data']);

            $this->row_real[$this->nrow]['reg_date'] = $row['reg_date'];
            ++$this->nrow;
            ++$noempty;
        } else {
            if (!is_null($row)) {
                ++$this->nrow;
                ++$noempty;
            }
        }

        $reg_date = null;
        if (!is_null($this->reg_date))
            $reg_date = $this->reg_date;
        if (is_null($this->reg_date) && $this->formulated)
            $reg_date = $row['year'] . "-" . str_pad((int)$row['month'], 2, '0', STR_PAD_LEFT) . "-" . str_pad((int)$row['day'], 2, '0', STR_PAD_LEFT);

        if (!$this->compute_traze)
            return $noempty;
        $noempty= $this->_traze_valor($noempty);
        return $noempty;
    }

    /**
     * Buscar los tres ultimos palnes reportados en la tabla tregistro
     *
     */
    private function _current_plan(&$row) {
        $date= $this->dates['date'];
        $this->nrow_plan= 0;
        $this->reg_date_plan= null;

        $corte= $this->dates['corte'];
        $reg_date= !is_null($this->reg_date) ? $this->reg_date : $date;
        $true= $_SESSION['_DB_SYSTEM'] == "mysql" ? "1" : "'1'";

        $sql_piece= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "select *, $sql_piece as reg_date ";
        $sql.= "from tregistro where id_indicador = $this->id_indicador and corte = $true ";
        if ($this->fix_year)
            $sql.= "and year = $this->year ";
        if ($this->fix_interval) {
            $sign= empty($this->scale) ? "=" : "<=";
            $sql.= "and $sql_piece $sign ".str_to_date2pg("'$corte'")." ";
        } else
            $sql.= "$sql_piece >= ".str_to_date2pg("'$reg_date'")." ";
        $sql.= "order by reg_date desc LIMIT 1";

	    $result= $this->do_sql_show_error('_current_plan', $sql);
        $row= $this->clink->fetch_array($result);

        if (empty($row['cronos_plan']) && (!$this->formulated && !$this->cumulative))
            return null;

        return 1;
    }

    private function _traze_plan() {
        $sql_piece= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "select *, $sql_piece as reg_date from tregistro where id_indicador = $this->id_indicador ";
        $sql.= "and $sql_piece < ".str_to_date2pg("'$this->reg_date_plan'")." ";
        $sql.= "and corte = true and (valor is not null or length(observacion_real) > 0) order by reg_date desc ";
        $result= $this->do_sql_show_error('_traze_plan', $sql);
        $num_rows= $result ? $this->clink->num_rows($result) : 0;
        if ($num_rows > 0)
            ++$this->nrow_plan;
        else
            return null;

        while ($row= $this->clink->fetch_array($result)) {
            $value= null;
            $this->row_plan[$this->nrow_plan]['updated_plan']= null;

            if ($this->formulated && boolean($row['calcular_plan'])) {
                $this->obj_calc->contents= $this->calculo;
                $value= $this->calcular_traze($this->nrow_plan, false, $row['year'], $row['month'], $row['day']);

                if (is_null($value))
                    continue;
            }

            if (!$this->formulated || ($this->formulated && (is_null($value) || !boolean($row['calcular_plan'])))) {
                if (!$this->formulated || ($this->formulated && !boolean($row['calcular_plan']))) {
                    $this->row_plan[$this->nrow_plan]['plan']= $row['plan'];
                    $this->row_plan[$this->nrow_plan]['acumulado_plan']= $this->cumulative ? $row['acumulado_plan'] : null;
                } else {
                    $this->row_plan[$this->nrow_plan]['plan']= $this->row_plan[$this->nrow_plan]['plan'];
                    $this->row_plan[$this->nrow_plan]['acumulado_plan']= $this->cumulative ? $this->row_plan[$this->nrow_plan]['acumulado_plan'] : null;
                }

                $this->row_plan[$this->nrow_plan]['id_usuario']= $row['id_usuario_plan'];
                $this->row_plan[$this->nrow_plan]['cronos']= $row['cronos_plan'];
                $this->row_plan[$this->nrow_plan]['observacion']= $row['observacion_plan'];
                $this->row_plan[$this->nrow_plan]['reg_date']= $row['reg_date'];
            }
            $this->row_plan[$this->nrow_plan]['plan_cot']= $row['plan_cot'];
            $this->row_plan[$this->nrow_plan]['acumulado_plan_cot']= $this->cumulative ? $row['acumulado_plan_cot'] : null;
            $this->row_plan[$this->nrow_plan]['origen_data']= $this->GetOrigenData('user_plan', $row['origen_data']);

            ++$this->nrow_plan;
            if ($this->nrow_plan > 3)
                break;
        }
    }

    protected function buscar_plan() {
        $row= null;
        if (!$this->formulated || ($this->formulated && !$this->recompute)) {
            $result= $this->_current_plan($row);
            if (is_null($result))
                return null;
        }

        $this->row_plan[$this->nrow_plan]['updated_plan']= null;
        $this->nrow_plan= 0;
        $value= null;

        if ($this->formulated && ((is_null($row['calcular_plan']) || $this->recompute || (!is_null($row['calcular_plan']) && boolean($row['calcular_plan'])))
            || ($this->cumulative && (is_null($row['acumulado_plan']) && is_null($row['acumulado_plan_cot']))))) {
            $this->obj_calc->contents= $this->calculo;
            $value= $this->calcular($this->nrow_plan, false, $this->year, $this->month, $this->day);
            $this->used_compute_function= true;
            $this->null_plan_found= array_merge ((array)$this->null_plan_found, (array)$this->obj_calc->null_plan_found);
            $this->not_null_plan_found= $this->obj_calc->not_null_plan_found;
            $this->not_null_acumulado_plan_found= $this->obj_calc->not_null_acumulado_plan_found;

            if (is_null($value))
                return 1;

        } else {
            if ($row && (!is_null($row['plan']) || ($this->trend == 3 && !is_null($row['plan_cot']))))
                $this->not_null_plan_found= true;
            if ($row && (!is_null($row['acumulado_plan']) || ($this->trend == 3 && !is_null($row['acumulado_plan_cot']))))
                $this->not_null_acumulado_plan_found= true;
        }

        if ((!$this->formulated && (!is_null($row['plan']) || !is_null($row['acumulado_plan']) || !is_null($row['plan_cot']) || !is_null($row['acumulado_plan_cot'])))
                || ($this->formulated && is_null($value))) {
            $this->row_plan[$this->nrow_plan]['plan']= $row['plan'];
            $this->row_plan[$this->nrow_plan]['plan_cot']= $row['plan_cot'];
            $this->row_plan[$this->nrow_plan]['acumulado_plan']= $this->cumulative ? $row['acumulado_plan'] : null;
            $this->row_plan[$this->nrow_plan]['acumulado_plan_cot']= $this->cumulative ? $row['acumulado_plan_cot'] : null;
            $this->row_plan[$this->nrow_plan]['id_usuario']= $row['id_usuario_plan'];
            $this->row_plan[$this->nrow_plan]['cronos']= $row['cronos_plan'];
            $this->row_plan[$this->nrow_plan]['observacion']= stripslashes($row['observacion_plan']);
            $this->row_plan[$this->nrow_plan]['reg_date']= $row['reg_date'];

            $this->row_plan[$this->nrow_plan]['origen_data']= $this->GetOrigenData('user_plan', $row['origen_data']);
        }

        $this->origen_data_plan= stripslashes($this->row_plan['origen_data']);

        $this->reg_date_plan= $this->row_plan[$this->nrow_plan]['reg_date'];

        $plan= $this->row_plan[$this->nrow_plan]['plan'];
        $acumulado= $this->cumulative ? $this->row_plan[$this->nrow_plan]['acumulado_plan'] : null;
        $this->plan= $plan;
        $this->acumulado_plan= $acumulado;

        $this->plan_cot= $this->row_plan[$this->nrow_plan]['plan_cot'];
        $this->acumulado_plan_cot= $this->cumulative ? $this->row_plan[$this->nrow_plan]['acumulado_plan_cot'] : null;
        $this->observacion_plan= $this->row_plan[$this->nrow_plan]['observacion'];

        if (!$this->compute_traze)
            return 1;

        $this->_traze_plan();

        $this->plan= $plan;
        $this->acumulado_plan= $acumulado;

        return 1;
    }

    private function calcular($nrow, $use_real) {
        $value= null;
        $accumulated= null;

        $this->obj_calc->SetReal(null);
        $this->obj_calc->SetPlan(null);
        $this->obj_calc->SetAcumuladoPlan(null);
        $this->obj_calc->SetAcumuladoPlan_cot(null);

        $this->obj_calc->SetYear($this->year);
        $this->obj_calc->SetMonth($this->month);
        $this->obj_calc->SetDay($this->day);
        $this->obj_calc->SetIdUsuario(_USER_SYSTEM);
        $this->obj_calc->SetIfCumulative($this->cumulative);

        $value= $this->obj_calc->compute($use_real);

        if (is_null($value))
            return null;

        $date= $this->year."-".str_pad($this->month,2,'0',STR_PAD_LEFT)."-".str_pad($this->day,2,'0',STR_PAD_LEFT);

        if ($use_real) {
            $this->not_null_real_found= $this->obj_calc->not_null_real_found;
            $this->not_null_acumulado_real_found= $this->obj_calc->not_null_acumulado_real_found;

            $this->row_real[$nrow]['valor']= $value[0];
            $this->row_real[$nrow]['acumulado_real']= $value[1];
            $this->row_real[$nrow]['id_usuario']= _USER_SYSTEM;

            $this->row_real[$nrow]['fecha']= $date;
            $this->row_real[$nrow]['observacion']= null;
            $this->row_real[$nrow]['cronos']= $this->cronos;

            $this->row_real[$nrow]['updated']= $this->obj_calc->updated;
            $this->row_real[$nrow]['valid_period']= $this->obj_calc->valid_period;

        } else {
            $this->not_null_plan_found= $this->obj_calc->not_null_plan_found;
            $this->not_null_acumulado_plan_found= $this->obj_calc->not_null_acumulado_plan_found;

            $this->row_plan[$nrow]['plan']= $this->trend != 3 ? $value[0] : null;
            $this->row_plan[$nrow]['plan_cot']= $this->trend == 3 ? $value[0] : null;
            $this->row_plan[$nrow]['acumulado_plan']= $this->trend != 3 ? $value[1] : null;
            $this->row_plan[$nrow]['acumulado_plan_cot']= $this->trend == 3 ? $value[1] : null;

            $this->row_plan[$nrow]['id_usuario']= _USER_SYSTEM;
            $this->row_plan[$nrow]['cronos']= $this->cronos;

            $this->row_plan[$nrow]['observacion']= null;
            $this->row_plan[$nrow]['reg_date']= $date;

            $this->row_plan[$nrow]['updated_plan']= $this->obj_calc->updated_plan;
        }

        $this->SetIdUsuario(_USER_SYSTEM);

        if (!is_null($value) && $use_real) {
            $this->SetReal($value[0]);
            $this->SetAcumuladoReal($value[1]);
            if ($this->not_null_real_found || $this->not_null_acumulado_real_found) {
                $calcular= $this->obj_calc->null_real_found ? true : false;
                $this->update_real($this->day, $this->month, $this->year, $calcular);
            }
        }
        if (!is_null($value) && !$use_real) {
            $this->SetPlan($value[0]);
            $this->SetAcumuladoPlan($value[1]);
            if ($this->trend == 3)
                $this->SetAcumuladoPlan_cot($value[1]);
            if ($this->not_null_plan_found || $this->not_null_acumulado_plan_found) {
                $calcular= $this->obj_calc->null_plan_found ? true : false;
                $this->update_plan($this->day, $this->month, $this->year, $calcular, $date, false);
            }
        }

        return $value;
    }

    private function calcular_traze($nrow, $use_real, $year, $month, $day) {
        $obj_cal= new Tcalculator($this->clink);
        $obj_cal->recompute= $this->recompute;

        $obj_cal->SetNombre($this->nombre);
        $obj_cal->SetIdIndicador($this->id_indicador);
        $obj_cal->set_id_indicador_code($this->id_indicador_code);

        $obj_cal->SetYear($year);
        $obj_cal->SetMonth($month);
        $obj_cal->SetDay($day);

        $obj_cal->SetIfCumulative($this->cumulative);
        $obj_cal->use_real= $use_real;

        $value= $obj_cal->Set();

        if (is_null($value))
            return null;

        if ($use_real) {
            $this->row_real[$nrow]['valor']= $obj_cal->row_real['valor'];
            $this->row_real[$nrow]['acumulado_real']= $obj_cal->row_real['acumulado_real'];
            $this->row_real[$nrow]['id_usuario']= _USER_SYSTEM;

            $this->row_real[$nrow]['fecha']= $obj_cal->row_real['fecha'];
            $this->row_real[$nrow]['observacion']= null;
            $this->row_real[$nrow]['cronos']= $this->cronos;

            $this->row_real[$nrow]['updated']= $obj_cal->row_real['updated'];
            $this->row_real[$nrow]['valid_period']= $obj_cal->row_real['valid_period'];

        } else {
            $this->row_plan[$nrow]['plan']= $obj_cal->row_plan['plan'];
            $this->row_plan[$nrow]['plan_cot']= $obj_cal->row_plan['plan_cot'];
            $this->row_plan[$nrow]['acumulado_plan']= $obj_cal->row_plan['acumulado_plan'];
            $this->row_plan[$nrow]['acumulado_plan_cot']= $obj_cal->row_plan['acumulado_plan_cot'];

            $this->row_plan[$nrow]['id_usuario']= _USER_SYSTEM;
            $this->row_plan[$nrow]['cronos']= $this->cronos;

            $this->row_plan[$nrow]['observacion']= null;
            $this->row_plan[$nrow]['reg_date']= $obj_cal->row_plan['reg_date'];

            $this->row_plan[$nrow]['updated_plan']= $obj_cal->row_plan['updated_plan'];
        }

        $this->SetIdUsuario(_USER_SYSTEM);

        if (!is_null($value)&& $use_real) {
            $this->SetReal($value[0]);
            $this->SetAcumuladoReal($value[1]);
            $this->update_real($day, $month, $year, false);
        }
        if (!is_null($value) && !$use_real) {
            $this->SetPlan($value[0]);
            $this->SetAcumuladoPlan($value[1]);
            if ($this->trend == 3)
                $this->SetAcumuladoPlan_cot($value[1]);
            $this->update_plan($day, $month, $year, false, $date, false);
        }

        return $value;
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