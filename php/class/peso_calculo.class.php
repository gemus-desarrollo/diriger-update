<?php
/**
 * Description of peso_item
 *
 * @author mustelier
 */

if (!class_exists('Tpeso'))
    include_once "peso.class.php";

class Tpeso_calculo extends Tpeso {
    private $flag_id_proceso;

    private $_array_politicas;
    private $_array_objetivos;
    private $_array_programas;
    private $_array_inductores;
    private $_array_perspectivas;
    
    private $array_prs_cascade_down;
    private $chain_prs_cascade_down;

    private $current_date;
    
    public $obj_matrix;
    protected $array_year_month;

    protected $trend,
              $cumulative;
    public $if_real;

    private $array_type_indicadores;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tpeso::__construct($clink);
    }    

    public function SetYearMonth($year, $month) {
        $this->year= (int)$year;
        $this->month= (int)$month;
        $this->array_year_month["$year-$month"]= array($year, $month);
        
        if ($this->obj_matrix) 
            $this->obj_matrix->SetYearMonth($this->year, $this->month);
    }
    
    private function set_array_type_indicadores() {
        $sql= "select id, nombre, formulated, cumulative from tindicadores";
        $result= $this->do_sql_show_error('set_array_type_indicadores', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_type_indicadores[$row['id']]= array('nombre'=>$row['nombre'], 'formulated'=>$row['formulated'], 
                                                            'cumulative'=>$row['cumulative']);
        }
    }

    public function set_matrix() {
        $this->obj_matrix= new Tresume($this->clink);
        $this->obj_matrix->SetYear($this->year);
        $this->obj_matrix->SetMonth($this->month);
        $this->obj_matrix->SetIdProceso($this->id_proceso);
        
        $this->obj_matrix->Set(); 
    }

    public function close_matrix() {
        $this->obj_matrix->if_real= $this->if_real;
        
        if ($this->array_year_month) {
            foreach ($this->array_year_month as $key => $array)
                $this->obj_matrix->execute_sql_cells((int)$array[0], (int)$array[1]);
        } else {
            $this->obj_matrix->execute_sql_cells();
        }    
    }

    private function _set_calcular($table, $id, $calcular) {
        $array= $this->obj_matrix->get_field($table);
        $calcular= boolean2pg($calcular);
        $value= setNULL($this->array_register['valor']);

        $time= new TTime();
        $time->SetYear($this->year);
        $time->SetMonth($this->month);
        $this->day= (int) date('t', strtotime("$this->year-$this->month-01"));

        $reg_date= $this->year."-".str_pad($this->month,2,'0',STR_PAD_LEFT)."-".str_pad($this->day,2,'0',STR_PAD_LEFT); 

        $id_proceso= null;
        $id_proceso_code= null;
        if ($this->flag_field_prs) {
            if (!empty($this->id_proceso))
                $id_proceso= $this->id_proceso;
            else if ($this->flag_id_proceso)
                $id_proceso= $this->flag_field_prs;

            $id_proceso_code= get_code_from_table("tprocesos", $id_proceso);
        }    

        $id_code= get_code_from_table($array[2], $id);

        $sql= "insert into treg_{$table} ({$array[0]}, {$array[0]}_code, month, year, calcular, reg_fecha, ";
        if (!empty($this->array_register))
            $sql.= "valor, ";
        if ($this->flag_field_prs)
            $sql.= "id_proceso, id_proceso_code, ";
        $sql.= "cronos, situs) values ($id, '$id_code', $this->month, $this->year, $calcular, '$reg_date', ";
        if (!empty($this->array_register))
            $sql.= "$value, ";
        if ($this->flag_field_prs)
            $sql.= "$id_proceso, '$id_proceso_code' ";
        $sql.= "'$this->cronos', '$this->location') ";
        
        $result= $this->do_sql_show_error('_set_calcular', $sql);
    }

    private function _read_calcular($table, $id) {
        $calcular= null;
        $value= null;
        $register= null; 
        $item= $this->obj_matrix->get_index($table, $id);
        $array= $this->obj_matrix->get_field($table);

        $sql= "select * from treg_{$table} where month = $this->month and year = $this->year ";
        if ($this->flag_field_prs) {
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso = $this->id_proceso ";
            else if (!empty($this->flag_id_proceso))
                $sql.= "and id_proceso = $this->flag_id_proceso ";
        }
        if (!is_null($array[0]))
            $sql.= "and {$array[0]} = $id ";
        $sql.= "order by cronos desc LIMIT 1";

        $result= $this->do_sql_show_error('_read_calcular', $sql);
        $nums= $this->cant;
        $row= $this->clink->fetch_array($result);

        $origen= $this->get_reg_origen("treg_$table");
        
        if ($nums > 0) {
            $value= $row['valor'];
            $calcular= boolean($row['calcular']) ? 1 : 0;
            $observacion= $row['observacion'];

            $register= array('id_usuario'=>$row['id_usuario'], 'calcular'=>$calcular, 'observacion'=>$row['observacion'],
                'reg_fecha'=>$row['reg_fecha'], 'if_eficaz'=>boolean($row['eficaz']), 'cronos'=>$row['cronos'],
                'origen'=>$origen['text'], 'signal'=>$origen['signal']);

        } else {
            $register= array('id_usuario'=>null, 'calcular'=>null, 'observacion'=>null, 'reg_fecha'=>null, 'if_eficaz'=>null, 
                            'cronos'=>null, 'origen'=>null, 'signal'=>$origen['signal']);
            $value= null;
            $calcular= 1;
            $observacion= null;
        }

        $this->obj_matrix->set_cell($item, $register);

        return array($value, $calcular, $observacion);
    }

    private function read_calcular($table, $id, &$calcular, &$observacion) {
        $value= null;
        $observacion= null;

        $row= $this->obj_matrix->get_calcular($table, $id);

        if (is_null($row)) {
            $array = $this->_read_calcular($table, $id);
            $value= $array[0];
            $calcular= $array[1];
            $observacion= $array[2];

        } else {
            $value= $row['valor'];
            $calcular= $row['calcular'];
            $observacion= $row['observacion'];
        }
        return $value;
    }

    private function split_plus($plus) {
        $sql= array();

        $array= preg_split("[=]",$plus);

        $sql['field_id']= trim($array[0]);
        $sql['id_code']= trim($array[2]);

        $array= preg_split("[and]",$array[1]);
        $sql['id']= trim($array[0]);
        $sql['field_id_code']= trim($array[1]);

        return $sql;
    }

    public function init_calcular() {
        $this->id_politica= null;
        $this->id_objetivo= null;
        $this->id_objetivo_sup= null;
        $this->id_inductor= null;
        $this->id_perspectiva= null;
        $this->id_programa= null;
        
        $this->_green= null;
        $this->if_eficaz= null;

        if (isset($this->array_register)) 
            unset($this->array_register);
        $this->array_register= null;

        if (isset($this->array_indicadores)) 
            unset($this->array_indicadores);
        $this->array_indicadores= null;

        unset($this->_array_programas); $this->_array_programas= null;
        unset($this->_array_politicas); $this->_array_politicas= null;
        unset($this->_array_inductores); $this->_array_inductores= null;
        unset($this->_array_perspectivas); $this->_array_perspectivas= null;
        unset($this->_array_objetivos); $this->_array_objetivos= null;

        $xtime= new TTime();
        $xtime->SetYear($this->year);
        $xtime->SetMonth($this->month);
        $day= empty($this->day) ? $xtime->longmonth() : $this->day;

        $this->compute_executed= false;
        $this->compute_traze= false;
        $this->current_date= strtotime($this->year."-".str_pad($this->month,2,'0',STR_PAD_LEFT)."-".str_pad($day,2,'0',STR_PAD_LEFT));

        $this->obj_matrix->SetYear($this->year);
        $this->obj_matrix->SetMonth($this->month);
        $this->obj_matrix->SetDay($this->day);
        
        $this->obj_matrix->SetIdProceso($this->id_proceso);
        $this->obj_matrix->set_id_proceso_code($this->id_proceso_code);
    }

    private function calcular($array) {
        $value= null;
        $peso= 0;
        $i= 0;

        foreach ($array as $cell) {
            if (empty($cell['peso'])) 
                continue;
            if (is_null($cell['value'])) 
                continue;

            ++$i;
            if (is_null($value)) 
                $value= 0;
            $value+= $cell['value']*$cell['peso'];
            $peso+= $cell['peso'];
        }

        if ($i > 0) 
            $value= ((float)$value)/$peso;
        return $value;
    }
    
    /**
     * calculos
     */

    public function calcular_empresa() {
        $id= $_SESSION['id_entity'];
        $this->flag_field_prs= true;
        $this->id_proceso= $id;
        $calcular= null;
        $observacion= null;
        $key_r= $this->obj_matrix->get_index('empresa', $id);

        $value= $this->read_calcular("proceso", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            return $value;
        }
        
        $row_items= $this->obj_matrix->get_row('empresa', $id, 'PER', null);

        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_perspectiva= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cumulative= false;
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $ratio= $this->calcular_perspectiva($id_perspectiva);
                $array[]= array('item'=>'PER', 'id'=>$id_perspectiva, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }

            $value= $this->calcular($array);        
        }

        $row_items= $this->obj_matrix->get_row('empresa', $id, 'IND', null);

        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_inductor= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cumulative= false;
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $ratio= $this->calcular_inductor($id_inductor);
                $array[]= array('item'=>'IND', 'id'=>$id_inductor, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }

            $value= $this->calcular($array);        
        }

        
        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
        }
        return $value;
    }

    public function calcular_politica($id, $string_procesos_down_entity= null) {
        $this->flag_field_prs= true;
        $this->id_politica= $id;
        $array= null;
        $calcular= true;
        $observacion= null;
        $key_r= $this->obj_matrix->get_index('politica', $id);

        $value= $this->read_calcular("politica", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            return $value;
        }    

        $row_items= $this->obj_matrix->get_row('politica', $id, 'OBJ', null);
        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_objetivo= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $ratio= $this->calcular_objetivo($id_objetivo);
                $array[]= array('item'=>'OBJ', 'id'=>$id_objetivo, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }
            
            $value= $this->calcular($array);
        }          

        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->_set_calcular('politica', $id, false);
        }
        return $value;  
    }

    public function calcular_objetivo($id) {
        $this->flag_field_prs= true;
        $this->id_objetivo= $id;
        $array= null;
        $observacion= null;

        $key_r= $this->obj_matrix->get_index('objetivo', $id);

        $value= $this->read_calcular("objetivo", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            return $value;
        }        
        
        $array_prs_cascade_down= $this->get_procesos_down_cascade($this->id_proceso, false);
        
        $row_items= $this->obj_matrix->get_row('objetivo', $id, 'OBJ', $array_prs_cascade_down);

        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_objetivo= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $ratio= $this->calcular_objetivo($id_objetivo);
                $array[]= array('item'=>'OBJ', 'id'=>$id_objetivo, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }
        }    

        $row_items= $this->obj_matrix->get_row('objetivo', $id, 'IND', $array_prs_cascade_down);

        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_inductor= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $ratio= $this->calcular_inductor($id_inductor);
                $array[]= array('item'=>'IND', 'id'=>$id_inductor, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }
        }          

        $value= $this->calcular($array);

        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->_set_calcular('objetivo', $id, false);
        }
        return $value;
    }

    public function calcular_programa($id) {
        $this->flag_field_prs= true;
        $this->id_programa= $id;
        $calcular= null;
        $observacion= null;
        $array= null;
        $key_r= $this->obj_matrix->get_index('programa', $id);

        $value= $this->read_calcular("programa", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            return $value;
        }   

        $array_prs_cascade_down= $this->get_procesos_down_cascade($this->id_proceso, false);
        
        $row_items= $this->obj_matrix->get_row('programa', $id, 'INDI', $array_prs_cascade_down);

        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_indicador= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cumulative= false;
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $_array= $this->calcular_indicador($id_indicador, null, $cumulative);
                $ratio= $cumulative ? $_array['ratio_cumulative'] : $_array['ratio'];
                $array[]= array('item'=>'INDI', 'id'=>$id_indicador, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }
            
            $value= $this->calcular($array);
        }

        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->_set_calcular('programa', $id, false);
        }
        return $value;
    }

    public function calcular_inductor($id) {
        $this->flag_field_prs= false;
        $this->id_inductor= $id;
        $calcular= null;
        $observacion= null;
        $key_r= $this->obj_matrix->get_index('inductor', $id);

        $value= $this->read_calcular("inductor", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            return $value;
        }

        $row_items= $this->obj_matrix->get_row('inductor', $id, 'INDI', null);
        
        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_indicador= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cumulative= false;
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $_array= $this->calcular_indicador($id_indicador, null, $cumulative);
                $ratio= $cumulative ? $_array['ratio_cumulative'] : $_array['ratio'];
                $array[]= array('item'=>'INDI', 'id'=>$id_indicador, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }

            $value= $this->calcular($array);
        }

        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->_set_calcular('inductor', $id, false);
        }
        
        return $value;
    }

    public function get_procesos_down_cascade($id_proceso, $do_chain= true) {
        $array_prs_cascade_down= $this->array_prs_cascade_down[$id_proceso];
        if (is_array($array_prs_cascade_down)) 
            return $do_chain ? $this->chain_prs_cascade_down[$id_proceso] : $array_prs_cascade_down;

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso($id_proceso);

        $obj_prs->get_procesos_down($id_proceso, null, null, true);
        $this->array_prs_cascade_down[$id_proceso]= $obj_prs->array_cascade_down;

        $i= 0;
        foreach ($this->array_prs_cascade_down[$id_proceso] as $row) {
            ++$i;
            $str= ($i > 1) ? ',' : '';
            $this->chain_prs_cascade_down[$id_proceso].= $str.$row['id'];
        }

        return $do_chain ? $this->chain_prs_cascade_down[$id_proceso] : $this->array_prs_cascade_down[$id_proceso];
    }

    public function calcular_perspectiva($id) {
        global $array_procesos_down_entity;
        reset($array_procesos_down_entity);

        $observacion= null;
        $this->flag_field_prs= true;
        $array= null;
        $this->id_perspectiva= $id;
        $key_r= $this->obj_matrix->get_index('perspectiva', $id);

        $value= $this->read_calcular("perspectiva", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            return $value;
        }

        $row_items= $this->obj_matrix->get_row('perspectiva', $id, 'INDI', $array_procesos_down_entity);

        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_indicador= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cumulative= false;
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;
                ++$i;
                $_array= $this->calcular_indicador($id_indicador, null, $cumulative);
                $ratio= $cumulative ? $_array['ratio_cumulative'] : $_array['ratio'];
                $array[]= array('item'=>'INDI', 'id'=>$id_indicador, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }

            $value= $this->calcular($array);
        }

        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->_set_calcular('perspectiva', $id, false);
        }
        return $value;
    }

    public function set_calcular_indicador($id, $calcular= true, $value= null) {
        $calcular= !is_null($calcular) ? $calcular : true;
        $this->obj_matrix->if_real= $this->if_real;
        $this->obj_matrix->set_item_calcular("INDI-$id", $calcular, $value, $this->observacion);
    }

    // $date_below_cutoff => fecha a partir de la cual se inicia l busqueda del valor real
    /*
    Se utiliza a la hora de graficar mantener el valor del $date_below_cutoff cuando se trabaja 
    con otras magnitudes que no son indicadores. Ejemplo las perspectivas que utilizan varios indicadores    
    */
    public function calcular_indicador($id, $all_register= false, &$cumulative= null, $date_bellow_cutoff= null) {
        global $array_date_bellow_cutoff;

        $obj_cal= new Tcalculator($this->clink);
        
        $all_register= !is_null($all_register) ? $all_register : false;
        
        $cell= new Tcell($this->clink);
        $cell->fix_year= true;
        $cell->fix_interval= true;
        $cell->compute_traze= $this->compute_traze;

        if (is_null($this->array_type_indicadores)) 
            $this->set_array_type_indicadores();
        $cumulative= $this->array_type_indicadores[$id]['cumulative'];        
        
        if (!is_null($this->obj_matrix->array_items["INDI-$id"][1][$this->year][$this->month])) {
            return $this->obj_matrix->array_items["INDI-$id"][1][$this->year][$this->month];
        }

        $array= array('alarm'=>'blank', 'arrow'=>'blank', 'alarm_cumulative'=>'blank',
                        'arrow_cumulative'=>'blank', 'real'=>null, 'acumulado_real'=>null, 'plan'=>null,
                        'acumulado_plan'=>null, 'acumulado_plan_cot'=>null);        
        $this->obj_matrix->array_items["INDI-$id"][1][$this->year][$this->month]= $array;

        $cell->SetYear($this->year);
        $cell->SetMonth($this->month);
        $cell->SetDay($this->day);

        $cell->SetIndicador($id);
        
        if ($cell->GetIfFormulated()) {
            $error= $obj_cal->_validar_code($cell->get_id_indicador_code());
            if ($error) {
            ?>
            <div class="col-4">
                <div class="alert alert-danger">
                    Error en la formula del indicador <strong><?=$cell->GetNombre()?></strong>
                </div>
            </div>

            <?php
            $this->obj_matrix->array_items["INDI-$id"][1][$this->year][$this->month]= array();
            return null;
        }  }
        
        $period= $cell->GetPeriodicidad();
        if (empty($period)) 
            return $array;
        /*
        if (!$cell->top_month($this->month)) 
            return $array;
        */
        if (!empty($this->periodicidad)) 
            $cell->SetScale($this->periodicidad);

        $init= null;
        if (is_null($date_bellow_cutoff) && !is_null($array_date_bellow_cutoff)) {
            $end= strtotime($array_date_bellow_cutoff[$id]['end']);
            $init= strtotime($array_date_bellow_cutoff[$id]['init']);
            if ($this->current_date > $end) 
                $date_bellow_cutoff= $array_date_bellow_cutoff[$id]['end'];
            if ($this->current_date >= $init && $this->current_date <= $end) 
                $date_bellow_cutoff= $array_date_bellow_cutoff[$id]['init'];
        }

        if (!is_null($date_bellow_cutoff)) 
            $cell->SetDateBelowCutoff($date_bellow_cutoff);
        $cell->strict_low_cutoff= false;
        if (!$this->compute_executed && (!is_null($array_date_bellow_cutoff[$id]['init']) && $this->current_date > $init)) 
            $cell->strict_low_cutoff= true;

        return $this->_calcular_indicador($id, $cell, $cumulative, $all_register);
    }

    private function _calcular_indicador($id, $cell, &$cumulative, $all_register) {
        global $array_date_bellow_cutoff;

        $cell->Set();

        $dates= $cell->GetDateInterval();
        $trend= $cell->GetTrend();
        $this->trend= $trend;
        $ratio= $cell->GetRatio();
        $ratio_cumulative= $cell->GetRatio_cumulative();
        $cumulative= $cell->GetIfCumulative();
        $this->cumulative= $cumulative;
        $percent= $cell->GetPercent();
        $percent_cumulative= $cell->GetPercent_cumulative();

        $arrow= $cell->GetFlecha();
        $arrow_cumulative= $cell->GetFlecha_cumulative();
        $alarm= $cell->GetAlarm();
        $alarm_cumulative= $cell->GetAlarm_cumulative();
        $real= $cell->GetReal();
        $acumulado_real= $cell->GetAcumuladoReal();
        $plan= $cell->GetPlan();
        $acumulado_plan= $cell->GetAcumuladoPlan();
        $acumulado_plan_cot= $cell->GetAcumuladoPlan_cot();

        $reg_date= $cell->row_real[0]['reg_date'];
        $reg_date_real= $cell->row_real[0]['reg_date_real'];
        $reg_date_plan= $cell->row_plan[0]['reg_date'];

        $id_user_real= $cell->row_real[0]['id_usuario'];
        $origen_user_real= $cell->GetOrigenData('user_real');
        $id_user_plan= $cell->row_plan[0]['id_usuario'];
        $origen_user_plan= $cell->GetOrigenData('user_plan');

        $observacion_real= $cell->row_real[0]['observacion'];
        $observacion_plan= $cell->row_plan[0]['observacion'];

        $obj_user= new Tusuario($this->clink);

        $registro_plan= '&nbsp;';
        if (!empty($cell->row_plan[0]['id_usuario'])) {
            $user= $obj_user->GetEmail($cell->row_plan[0]['id_usuario']);
            if (is_array($user)) 
                $registro_plan= $user['nombre'].' ('.$user['cargo'].')'.'  '.odbc2time_ampm($cell->row_plan[0]['cronos']);

        } elseif (!empty($cell->row_plan[0]['origen_data'])) {
            $registro_plan= merge_origen_data_user($this->GetOrigenData('user_plan', $cell->row_plan[0]['origen_data']));
            $registro_plan.= "<br />Fecha y hora: ".odbc2time_ampm($cell->row_plan[0]['cronos']);
        }
        $registro_real= '&nbsp;';

        if (!empty($cell->row_real[0]['id_usuario'])) {
            $user= $obj_user->GetEmail($cell->row_real[0]['id_usuario']);
            if (is_array($user)) 
                $registro_real= $user['nombre'].' ('.$user['cargo'].')'.'  '.odbc2time_ampm($cell->row_real[0]['cronos']);

        } elseif (!empty($cell->row_real[0]['origen_data'])) {
            $registro_real= merge_origen_data_user($this->GetOrigenData('user_real', $cell->row_real[0]['origen_data']));
            $registro_real.= "<br />Fecha y hora: ".odbc2time_ampm($cell->row_real[0]['cronos']);
        }

        if (!is_null($array_date_bellow_cutoff)) {
            $array_date_bellow_cutoff[$id]= array('reg_date_real'=>$reg_date_real, 'reg_date'=>$reg_date, 'init'=>$dates['init'], 'end'=>$dates['end']);
        }

        if (($cell->updated && $cell->updated_plan) || $all_register) {
            $array= array('calcular'=>false, 'signal'=>'indi', 'cronos'=>$this->cronos, 
                'year'=>$this->year, 'month'=>$this->month, 'reg_date'=>$reg_date, 'reg_date_real'=>$reg_date_real,
                'reg_date_plan'=>$reg_date_plan, 'ratio'=>$ratio, 'ratio_cumulative'=>$ratio_cumulative, 'alarm'=>$alarm,
                'arrow'=>$arrow, 'alarm_cumulative'=>$alarm_cumulative,'arrow_cumulative'=>$arrow_cumulative, 'real'=>$real,
                'acumulado_real'=>$acumulado_real, 'plan'=>$plan, 'acumulado_plan'=>$acumulado_plan, 'acumulado_plan_cot'=>$acumulado_plan_cot,
                'percent'=>$percent, 'percent_cumulative'=>$percent_cumulative, 'trend'=>$trend,
                'id_user_real'=>$id_user_real, 'id_user_plan'=>$id_user_plan, 'observacion_real'=>$observacion_real,
                'observacion_plan'=>$observacion_plan, 'registro_real'=>$registro_real, 'registro_plan'=>$registro_plan,
                'origen_user_plan'=>$origen_user_plan, 'origen_user_real'=>$origen_user_real);
            
            if ($this->obj_matrix) {
                $this->obj_matrix->array_items["INDI-$id"][1][$this->year][$this->month]= $array;
            }
        }
        
        return $array;
    }

    private function _GetArrow($delta) {
        $criterio = get_criterio($this->trend);

        $flecha = 'blank';
        if ($criterio == '>=') {
            if ($delta > 0)
                $flecha = 'green';
            elseif ($delta == 0)
                $flecha = 'yellow';
            else
                $flecha = 'red';
        }
        if ($criterio == '<=') {
            if ($delta < 0)
                $flecha = 'green';
            elseif ($delta == 0)
                $flecha = 'yellow';
            else
                $flecha = 'red';
        }
        if ($criterio == '[]') {
            if ($delta == 0)
                $flecha = 'green';
            else
                $flecha = 'red';
        }

        return $flecha;        
    }

    public function GetArrow($id, $year0, $month0, $year1, $month1) {
        $row0= $this->obj_matrix->array_items["INDI-$id"][1][$year0][$month0];
        $row1= $this->obj_matrix->array_items["INDI-$id"][1][$year1][$month1];
        $array= array('arrow'=>null, 'arrow_cumulative'=>null);

        $delta = ($row0['percent'] - $row1['percent']);
        $array['arrow']= $this->_GetArrow($delta);
        if ($this->cumulative) {
            $delta = ($row0['percent_cumulative'] - $row1['percent_cumulative']);
            $array['arrow_cumulative']= $this->_GetArrow($delta);            
        }

        return $array;
    }

    /**
     * procesos
     */
    private function get_array_indicadores($id_proceso) {
        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdProceso($id_proceso);

        $result= $obj_prs->listar_indicadores(false);

        while ($row= $this->clink->fetch_array($result)) {
            if (!boolean($row['_critico'])) 
                continue;

            $array= array('id'=>$row['_id_indicador'], 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'],
                    'peso'=>$row['_peso'], 'inicio'=>$row['_inicio'], 'fin'=>$row['_fin']);
            $this->array_indicadores[$row['_id_indicador']]= $array;
        }
    }

    private function test_if_critico($id_proceso, $id_indicador) {
        if (is_null($this->array_indicadores)) 
            $this->get_array_indicadores($id_proceso);
        return array_key_exists($id_indicador, $this->array_indicadores);
    }

    public function calcular_proceso($id, $tipo_prs= _TIPO_PROCESO_INTERNO, &$observacion= null) {
        $this->if_eficaz= true;
        $calcular= null;
        $this->id_proceso= null;

        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso($id);
        $obj_prs->SetYear($this->year);
        $obj_prs->get_criterio_eval();
        $this->_green= $tipo_prs == _TIPO_PROCESO_INTERNO ? $obj_prs->get_green() : null;    
        
        $key_r= $this->obj_matrix->get_index('proceso', $id);    
        
        $value= $this->read_calcular("proceso", $id, $calcular, $observacion);
        if (!is_null($calcular) && !$calcular) {
            $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month]['valor']= $value;
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->if_eficaz= $this->array_register['if_eficaz'];
            return $value;
        } 

        $row_items= $this->obj_matrix->get_row('proceso', $id, 'INDI', null);

        $i= 0;
        if ($this->obj_matrix->cant > 0) {
            foreach ($row_items as $key_c => $row) {
                $id_indicador= $this->obj_matrix->array_items[$key_c][0]['id'];
                $cumulative= false;
                $cell= $this->obj_matrix->get_cell($key_r, null, $key_c, null)[2];
                if ($cell[$row[0]][0] != _DEPEND_UPON_DIRECT)
                    continue;

                ++$i;
                $_array= $this->calcular_indicador($id_indicador, null, $cumulative);
                $ratio= $cumulative ? $_array['ratio_cumulative'] : $_array['ratio'];

                if ($tipo_prs == _TIPO_PROCESO_INTERNO) {
                    $if_critico= $this->test_if_critico($id, $id_indicador);
                    $alarm= $cumulative ? $_array['alarm_cumulative'] : $_array['alarm'];
                    if ($if_critico && ($alarm != 'green' && $alarm != 'blue' && $alarm != 'aqua')) {
                        $this->if_eficaz= false;
                        $observacion.= "Indicador crítico <b>{$this->array_type_indicadores[$id_indicador]['nombre']}</b> incumplido.<br/>";
                    }        
                }
                
                $array[]= array('item'=>'INDI', 'id'=>$id_indicador, 'value'=>$ratio, 'peso'=>$cell[$row[0]][1]);
            }

            $value= $this->calcular($array);
        } 

        if ((!empty($value) && !empty($this->_green)) && $value < $this->_green) 
            $this->if_eficaz= false;
        if (is_null($value)) 
            $this->if_eficaz= false;   
        
        if ($i > 0) {
            $this->obj_matrix->set_item_calcular($key_r, false, $value, $observacion);
            $this->array_register= $this->obj_matrix->array_items[$key_r][1][$this->year][$this->month];
            $this->_set_calcular('proceso', $id, false);
        }
        return $value;
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Tmatrix'))
    include_once "matrix.class.php";
if (!class_exists('Tresume'))
    include_once "resume.class.php";