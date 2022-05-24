<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */

if (!class_exists('Tmatrix'))
    include_once "matrix.class.php";


class Tresume extends Tmatrix {
    protected $array_year_month;

    public function SetYearMonth($year, $month) {
        $this->year= (int)$year;
        $this->month= (int)$month;
        $this->array_year_month["$year-$month"]= array($year, $month);
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tmatrix::__construct($clink);
    }

    /*
     * item => nombre de la tabla
     */
    public function set_item_calcular($key, $calcular= true, $value= null, $observacion= null) {
        $calcular= !is_null($calcular) ? $calcular : true;
        $reg_fecha= $this->year.'-'.str_pad($this->month,2, '0', STR_PAD_LEFT).'-'.str_pad($this->day, 2, '0', STR_PAD_LEFT);

        $update= false;
        if (is_null($this->array_items[$key][1][$this->year][$this->month])) {
            $this->set_cell($key, null);
            $update= true;
        }
        if (!is_null($value) && $value != $this->array_items[$key][1][$this->year][$this->month]['valor']) {
            $update= true;
        }
        if ((!is_null($this->array_items[$key][1][$this->year][$this->month]['cronos'])
                && $this->array_items[$key][1][$this->year][$this->month]['cronos'] != $this->cronos)
                || is_null($this->array_items[$key][1][$this->year][$this->month]['cronos'])) {
            $update= true;
        }
        if (!$update)
            return;

        $_value= $this->array_items[$key][1][$this->year][$this->month]['valor'];

        $this->array_items[$key][1][$this->year][$this->month]['calcular']= $calcular;
        $this->array_items[$key][1][$this->year][$this->month]['valor']= $value;
        $this->array_items[$key][1][$this->year][$this->month]['observacion']= $observacion;
        $this->array_items[$key][1][$this->year][$this->month]['reg_fecha']= $reg_fecha;
        $this->array_items[$key][1][$this->year][$this->month]['cronos']= $this->cronos;
        $this->array_items[$key][1][$this->year][$this->month]['id_usuario']= $_SESSION['id_usuario'];
        $this->array_items[$key][1][$this->year][$this->month]['id_proceso']= $this->id_proceso;

        if (((!is_null($_value) || !is_null($value)) && $_value != $value)
                || (stripos($key, "INDI") !== false && ($_value != $value || (is_null($_value) && is_null($value))))) {
            $this->_set_items_calcular($key, $calcular, $observacion);
        }
    }

    protected function _set_items_calcular($ref, $calcular, $observacion) {
        $reg_fecha= $this->year.'-'.str_pad($this->month,2, '0', STR_PAD_LEFT).'-'.str_pad($this->day, 2, '0', STR_PAD_LEFT);

        reset($this->array_items);
        foreach ($this->array_items as $key => $row) {
            $depend= $this->cell_depend($ref, $key);
            if ($depend == _DEPEND_DOWN || $depend == _DEPEND_DOWN_DIRECT) {
                if ($ref == $key)
                    continue;

                $update= false;
                if (is_null($this->array_items[$key][1][$this->year][$this->month])) {
                    $this->set_cell($key, null);
                    $update= true;
                }
                if (!is_null($this->array_items[$key][1][$this->year][$this->month]['cronos'])
                       && $this->array_items[$key][1][$this->year][$this->month]['cronos'] != $this->cronos) {
                   $update= true;
                }
                if (!$update)
                    continue;

                $this->array_items[$key][1][$this->year][$this->month]['observacion']= $observacion;
                $this->array_items[$key][1][$this->year][$this->month]['calcular']= $calcular;
                $this->array_items[$key][1][$this->year][$this->month]['reg_fecha']= $reg_fecha;
                $this->array_items[$key][1][$this->year][$this->month]['cronos']= $this->cronos;
                $this->array_items[$key][1][$this->year][$this->month]['id_proceso']= $this->id_proceso;
                $this->array_items[$key][1][$this->year][$this->month]['id_usuario']= null;
            }
        }
    }

    public function execute_sql_cells($year= null, $month= null) {
        $year= !empty($year) ? $year : $this->year;
        $month= !empty($month) ? $month : $this->month;

        $i= 0;
        $sql= null;
        reset($this->array_items);
        foreach ($this->array_items as $key => $row) {
            $cell= $row[1][$year][$month];
            if (is_null($cell) || strtotime($cell['cronos']) != strtotime($this->cronos))
                continue;

            $valor= $cell['valor'];
            $calcular= $cell['calcular'];
            $observacion= $cell['observacion'];
            $id_proceso= $cell['id_proceso'];
            $reg_fecha= $cell['reg_fecha'];
            $eficaz= $cell['eficaz'];

            $item= $this->get_item($key);
            $tabla= $item[3];
            $id= $item[2];
            $id_code= $this->array_items[$key][0]['id_code'];

            $this->year= $year;
            $this->month= $month;

            if ($tabla != "tregistro")
                $sql.= $this->treg_table($tabla, $id, $id_code, $valor, $calcular, $id_proceso, $reg_fecha, $observacion, $eficaz);
            else {
                if ($tabla == "tregistro")
                    $this->set_calcular_indicador_ref($id);
            }

            ++$i;
            if ($i > 100) {
                $this->do_multi_sql_show_error('sql_cells', $sql);
                $i= 0;
                $sql= null;
            }
        }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('sql_cells', $sql);
    }

    private function _set_calcular_indicador_ref($key_r, $if_real= null) {
        $if_real= !is_null($if_real) ? $if_real : $this->if_real;
        $item= $this->get_item($key_r);
        if (!$this->array_items[$key_r][0]['formulated'])
            return null;

        $obj= new Tregistro($this->clink);
        $obj->SetYear($this->year);
        $obj->Set($item[2]);

        $obj->SetYear($this->year);
        $obj->SetMonth($this->month);
        $obj->SetDay($this->day);
        $obj->SetIdUsuario($_SESSION['id_usuario'] ? $_SESSION['id_usuario'] : _USER_SYSTEM);
        $obj->SetObservacion(null);

        if (!is_null($if_real))
            $if_real ? $obj->SetReal(null) : $obj->SetPlan(null);
        else {
            $obj->SetPlan(null);
            $obj->SetReal(null);
        }

        if (is_null($if_real) || $if_real)
            $obj->update_real(null, null, null, true);
        if (is_null($if_real) || !$if_real)
            $obj->update_plan(null, null, null, true, null, true);
    }

    public function set_calcular_indicador_ref($id, $if_real= null) {
        $if_real= !is_null($if_real) ? $if_real : $this->if_real;
        $key_r= "INDI-$id";
        $this->_set_calcular_indicador_ref($key_r, $if_real);

        reset($this->matrix);
        foreach ($this->matrix[$this->year][$key_r] as $key_c => $row) {
            $item= $this->get_item($key_c);

            if ($item[0] != "INDI")
                continue;
            if ($key_r == $key_c)
                continue;
            if ($this->matrix[$this->year][$key_r][$key_c][$_SESSION['local_proceso_id']][0] != _DEPEND_DOWN
                && $this->matrix[$this->year][$key_r][$key_c][$_SESSION['local_proceso_id']][0] != _DEPEND_DOWN_DIRECT)
                continue;
            if (!$this->array_items[$key_c][0]['formulated'])
                continue;

            $this->_set_calcular_indicador_ref($key_c, $if_real);
        }
    }

    private function if_reg_exist($table, $id_field, $id_proceso= null, $use_proceso= true) {
        $use_proceso= !is_null($use_proceso) ? $use_proceso : true;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $field= null;

        switch ($table) {
            case ("treg_politica"):
                $field= "id_politica";
                break;
            case ("treg_objetivo"):
                $field= "id_objetivo";
                break;
            case ("treg_inductor"):
                $field= "id_inductor";
                break;
            case ("treg_perspectiva"):
                $field= "id_perspectiva";
                break;
            case ("treg_proceso"):
                $field= "id_proceso";
                break;
        }

        $sql= "select id from $table where $field = $id_field and month = $this->month and year = $this->year ";
        $sql.= $use_proceso ? "and id_proceso = $id_proceso " : null;
        $result= $this->do_sql_show_error("if_reg_exist", $sql);
        $id= $this->clink->fetch_result($result, 0, 0);

        return $this->cant > 0 && !empty($id) ? $id : false;
    }

    private function treg_table($table, $id, $id_code, $valor, $calcular, $id_proceso= null, $reg_fecha= null, $observacion=null, $eficaz= null) {
        $field= null;
        $use_proceso= true;

        switch ($table) {
            case ("treg_politica"):
                $field= "id_politica";
                $use_proceso= true;
                break;
            case ("treg_objetivo"):
                $field= "id_objetivo";
                $use_proceso= true;
                break;
            case ("treg_inductor"):
                $field= "id_inductor";
                $use_proceso= false;
                break;
            case ("treg_perspectiva"):
                $field= "id_perspectiva";
                $use_proceso= true;
                break;
            case ("treg_programa"):
                $field= "id_programa";
                $use_proceso= true;
                break;
            case ("treg_proceso"):
                $field= "id_proceso";
                $use_proceso= false;
                break;
        }

        $if_exist= $this->if_reg_exist($table, $id, $id_proceso, $use_proceso);

        $_valor= setNULL($valor);
        $calcular= boolean2pg($calcular);
        $observacion= setNULL_str($observacion);
        $reg_fecha= setNULL_str($reg_fecha);
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;

        if ($if_exist) {
            $month= !empty($this->month) ? $this->month : null;

            $sql= "update table $table set calcular= $calcular ";
            if (!is_null($valor))
                $sql.= ", valor= $_valor, observacion= $observacion, cronos= '$this->cronos ";
            if ($table == "treg_proceso")
                $sql.= ", eficaz = ". boolean($eficaz);
            $sql.= " where $field = $id and year = $this->year and month ". setNULL_equal_sql($this->month). " ";
            if ($use_proceso && !empty($id_proceso))
                $sql.= "and id_proceso = $id_proceso ";
            $sql.= "; ";

        } else {
            $month= setNULL_empty($this->month);
            if ($use_proceso) {
                $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];
                $id_proceso_code= get_code_from_table('tprocesos', $id_proceso, $this->clink);
                $id_proceso= setNULL($id_proceso);
                $id_proceso_code= setNULL_str($id_proceso_code);
            }

            $sql= "insert into $table ($field, {$field}_code, valor, calcular, observacion, month, year, ";
            $sql.= $use_proceso ? "id_proceso, id_proceso_code, " : null;
            $sql.= $table == "treg_proceso" ? ", eficaz" : null;
            $sql.= "id_usuario, reg_fecha, cronos, situs) values ($id, '$id_code', $_valor, $calcular, $observacion, $month, $this->year, ";
            $sql.= $use_proceso ? "$id_proceso, $id_proceso_code, " : null;
            if ($table == "treg_proceso")
                $sql.= boolean($eficaz).", ";
            $sql.= "{$_SESSION['id_usuario']}, $reg_fecha, '$this->cronos', '$this->location'); ";
        }

        return $sql;
    }

    public function get_calcular($item, $id) {
        $i_item= $this->get_index($item, $id);
        return $this->array_items[$i_item][1][$this->year][$this->month];
    }

    public function get_indicador_by_perspectiva($id) {
        $array= $this->get_row('perspectiva', $id, 'INDI');
        return $array;
    }

}



