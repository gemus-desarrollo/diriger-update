<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */


if (!class_exists('Tpeso'))
    include_once "peso.class.php";

define("_DEPEND_NO", 0);            // no hay dependencia,
define("_DEPEND_UPON", 1);          // la referencia de la fila esta por encima del i-esimo de la columna
define("_DEPEND_UPON_DIRECT", 2);   // la referencia de la fila es el superior directo del i-esimo de la columna
define("_DEPEND_DOWN_DIRECT", -1);  // la referencia de la fila es un inferior directo del i-esimo de la columna
define("_DEPEND_DOWN", -2);         // la referencia de la fila esta por debajo del i-esimo de la columna


class Tmatrix extends Tpeso {
    public $array_items;
    public $matrix;
    private $index;

    public $if_real;
    public $if_get_items;

    public $empresa;

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tpeso::__construct($clink);
        $this->if_get_items= null;
        $this->index= 0;
    }

    public function Set() {        
        if (!$this->if_get_items) {
            $this->get_empresa();
            $this->get_politicas();
            $this->get_objetivos();
            $this->get_inductores();
            $this->get_programas();
            $this->get_perspectivas();
            $this->get_procesos();
            $this->get_indicadores();

            $this->if_get_items= array();
            $this->do_matrix();
        }

        if ($this->if_get_items[$this->year])
            return null;

        $this->set_cell_direct();
        $this->fill_matrix();
        $this->if_get_items[$this->year]= true;
    }

    public function get_cell($row_item, $row_id, $col_item, $col_id) {
        if (!empty($row_id) && !empty($col_id)) {
            $row= $this->get_index($row_item, $row_id);
            $col= $this->get_index($col_item, $col_id);
        } else {
            $row= $row_item;
            $col= $col_item;
        }

        return array($row, $col, $this->matrix[$this->year][$row][$col]);
    }

    protected function get_i_esimo($id, $year= null) {
        $year= !empty($year) ? $year : $this->year;
        return $this->array_items[$id][0];
    }

    public function set_cell($key, $row, $id_proceso= null, $year= null, $month= null) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $year= !empty($year) ? $year : $this->year;
        $month= !empty($month) ? $month : $this->month;
        $signal= $this->get_item($key);
        $signal= is_array($signal) ? $signal[0] : null;
        
        $this->array_items[$key][1][$year][$month]= array('valor'=>$row['valor'], 'calcular'=>$row['calcular'], 
            'id_proceso'=>$row['id_proceso'],'id_usuario'=>$row['id_usuario'],'observacion'=>$row['observacion'], 
            'reg_fecha'=>$row['reg_fecha'], 'if_eficaz'=>$row['if_eficaz'], 'cronos'=>$row['cronos'], 
            'origen'=>$row['origen'], 'signal'=>$signal);
    }

    private function do_matrix() {
        $array_items= array();
        foreach ($this->array_items as $key => $array)
            $array_items[$key]= null;

        reset($this->array_items);
        foreach ($this->array_items as $key_r => $row_r) {
            reset($array_items);
            foreach ($array_items as $key_c => $row_c)
                $this->matrix[$this->year][$key_r][$key_c]= null;
        }
    }

    /*
     * $item_r => articulo desde el cual se va buscar
     * el id del articulo desde el que sea buscar
     * $item_c => articulo a devolver
     */
    public function get_row($item_r, $id, $code_col= null, $array_cascade_prs= null) {
        $this->cant= 0;
        $array= array();
        $i_r= $this->get_index($item_r, $id);

        if (!is_null($array_cascade_prs))
            reset($array_cascade_prs);

        $i= 0;
        reset($this->matrix);
        foreach ($this->matrix[$this->year][$i_r] as $key => $row) {
            $code_item_c= $this->get_item($key)[0];
            if (!is_null($code_col) && ($code_col != $code_item_c))
                continue;

            $array_prs= array_keys($row);

            $found_prs= false;
            foreach ($array_prs as $index => $id_proceso) {
                if ($id_proceso == 0
                    || (!is_null($array_cascade_prs) && array_key_exists($id_proceso, $array_cascade_prs))
                    || (is_null($array_cascade_prs) && $id_proceso == $this->id_proceso)) {
                    $found_prs= true;
                    break;
                }
            }

            if ($found_prs) {
                ++$i;
                $array[$key][0]= $id_proceso;
                $array[$key][1]= $row[$id_proceso];
            }
        }
        $this->cant= $i;
        return $array;
    }

    protected function get_item($key) {
        $_key= preg_split('/-/i', $key, -1, PREG_SPLIT_NO_EMPTY);
        $code= $_key[0];
        $id= $_key[1];
        $field= null;
        $table= null;

        switch ($code) {
            case "EMP":
                $item= "proceso";
                $table= "treg_proceso";
                $field= "id_proceso";
                break;
            case "POL":
                $item= "politica";
                $table= "treg_politica";
                $field= "id_politica";
                break;
            case "OS":
                $item= "objetivo_sup";
                $table= "treg_objetivo";
                $field= "id_objetivo";
                break;
            case "OBJ":
                $item= "objetivo";
                $table= "treg_objetivo";
                $field= "id_objetivo";
                break;
            case "IND":
                $item= "inductor";
                $table= "treg_inductor";
                $field= "id_inductor";
                break;
            case "PER":
                $item= "perspectiva";
                $table= "treg_perspectiva";
                $field= "id_perspectiva";
                break;
            case "PROG":
                $item= "programa";
                $table= "treg_programa";
                $field= "id_programa";
                break;
            case "PROC":
                $item= "proceso";
                $table= "treg_proceso";
                $field= "id_proceso";
                break;
            case "INDI":
                $item= "indicador";
                $table= "tregistro";
                $field= "id_indicador";
                break;
            default :
                $item= null;
        }

        return !is_null($item) ? array($code, $item, $id, $table, $field) : null;
    }

    public function get_index($table, $id) {
        $item= null;

        switch ($table) {
            case "empresa":
                $item= "EMP";
                break;
            case "politica":
                $item= "POL";
                break;
            case "objetivo_sup":
                $item= "OS";
                break;
            case "objetivo":
                $item= "OBJ";
                break;
            case "inductor":
                $item= "IND";
                break;
            case "perspectiva":
                $item= "PER";
                break;
            case "programa":
                $item= "PROG";
                break;
            case "proceso":
                $item= "PROC";
                break;
            case "indicador":
                $item= "INDI";
                break;
            default :
                $item= null;
        }

        return "{$item}-{$id}";
    }

    public function get_field($table) {
        $field= null;
        $item= null;

        switch ($table) {
            case "empresa":
                $item= "EMP";
                $table= "tprocesos";
                $field= "id_proceso";
                break;            
            case "politica":
                $item= "POL";
                $table= "tpoliticas";
                $field= "id_politica";
                break;
            case "objetivo_sup":
                $item= "OS";
                $table= "tobjetivos";
                $field= "id_objetivo";
                break;
            case "objetivo":
                $item= "OBJ";
                $table= "tobjetivos";
                $field= "id_objetivo";
                break;
            case "inductor":
                $item= "IND";
                $table= "tinductores";
                $field= "id_inductor";
                break;
            case "perspectiva":
                $item= "PER";
                $table= "tperspectivas";
                $field= "id_perspectiva";
                break;
            case "programa":
                $item= "PROG";
                $table= "tprogramas";
                $field= "id_programa";
                break;
            case "indicador":
                $item= "INDI";
                $table= "tindicadores";
                $field= "id_indicador";
                break;
            default :
                $item= null;
                $field= null;
        }

        return !is_null($field) ? array($field, $item, $table) : null;
    }

    private function get_politicas() {
        $sql= "select distinct tpoliticas.* from tpoliticas, tpolitica_objetivos where (titulo = false or titulo = 0) ";
        $sql.= "and ((tpoliticas.id = tpolitica_objetivos.id_politica and tpolitica_objetivos.year = $this->year) ";
        $sql.= "and (tpolitica_objetivos.id_objetivo is not null or tpolitica_objetivos.id_objetivo_sup is not null)) ";
        $sql.= "and (inicio <= $this->year and fin >= $this->year)";
        $result= $this->do_sql_show_error("get_politicas", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            if (boolean($row['titulo'])) 
                continue;
            $this->array_items["POL-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_items["POL-{$row['id']}"][1]= null;
        }
    }
    private function get_empresa() {
        $this->array_items["EMP-{$_SESSION['id_entity']}"][0]= array('indice'=>$this->index++, 'id'=>$_SESSION['id_entity'], 'id_code'=>$_SESSION['id_entity_code']);
        $this->array_items["EMP-{$_SESSION['id_entity']}"][1]= null;
    } 
    private function get_objetivos() {
        $sql= "select distinct * from tobjetivos where (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error("get_objetivos", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_items["OBJ-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_items["OBJ-{$row['id']}"][1]= null;
        }
    }
    private function get_inductores() {
        $sql= "select distinct * from tinductores where (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error("get_inductores", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_items["IND-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_items["IND-{$row['id']}"][1]= null;
        }
    }
    private function get_perspectivas() {
        $sql= "select distinct * from tperspectivas where (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error("get_perspectivas", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_items["PER-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_items["PER-{$row['id']}"][1]= null;
        }
    }
    private function get_programas() {
        $sql= "select distinct * from tprogramas where (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error("get_programas", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_items["PROG-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_items["PROG-{$row['id']}"][1]= null;
        }
    }
    private function get_procesos() {
        $sql= "select distinct * from tprocesos where (inicio <= $this->year and fin >= $this->year) ";
        if (!empty($this->id_proceso))
            $sql.= "and (tipo = "._TIPO_PROCESO_INTERNO." or id = $this->id_proceso) ";
        else
            $sql.= "and tipo = "._TIPO_PROCESO_INTERNO." ";
        $result= $this->do_sql_show_error("get_procesos", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_items["PROC-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_items["PROC-{$row['id']}"][1]= null;
        }
    }
    private function get_indicadores() {
        $sql= "select distinct * from tindicadores where (inicio <= $this->year and fin >= $this->year) ";
        $result= $this->do_sql_show_error("get_indicadores", $sql);
        while ($row= $this->clink->fetch_array($result)) {
            $this->array_items["INDI-{$row['id']}"][0]= array('indice'=>$this->index++, 'id'=>$row['id'], 'id_code'=>$row['id_code'],
                                                              'formulated'=>$row['formulated'] ? 1 : 0, 'cumulative'=>$row['cumulative'] ? 1 : 0);
            $this->array_items["INDI-{$row['id']}"][1]= null;
        }
    }

    private function set_cell_direct() {
        reset($this->matrix);
        foreach ($this->matrix[$this->year] as $key_r => $array) {
            $array= $this->get_item($key_r);
            if (is_null($array)) 
                continue;
            $code= $array[0];
            $item= $array[1];
            $id= $array[2];

            switch ($code) {
                case "EMP":
                    $this->set_empresa();
                    $this->set_perspectivas($id);
                    $this->set_inductores(null, null);
                    break;
                case "OBJ":
                    $this->set_politicas($id);
                    $this->set_inductores($id, null);
                    $this->set_objetivos(null, $id);
                    break;
                case "IND":
                    $this->set_objetivos($id, null);
                    $this->set_indicadores(null, $id);
                    break;
                case "PROG":
                    $this->set_programas($id);
                    break;
                case "PROC":
                    $this->set_procesos($id);
                    $this->set_perspectivas($id);
                    break;
                case "PER":
                    $this->set_empresa();
                    $this->set_indicadores($id, null);
                    $this->set_inductores(null, $id);
                    break;
                case "INDI":
                    $this->set_indicadores_indi($id);
                    break;
                default :
                    continue;
            }
        }
    }

    /*
    * empresa(id_entity)
    */
    private function set_empresa() {
        $this->listar_perspectivas_ref_proceso($_SESSION['id_entity']);
        foreach ($this->array_perspectivas as $row) {
            $this->matrix[$this->year]["EMP-{$_SESSION['id_entity']}"]["PER-{$row['id']}"][$_SESSION['id_entity']]= array(_DEPEND_DOWN_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
        }

        $this->listar_inductores_ref_proceso($_SESSION['id_entity']);
        foreach ($this->array_inductores as $row) {
            $this->matrix[$this->year]["EMP-{$_SESSION['id_entity']}"]["IND-{$row['id']}"][$_SESSION['id_entity']]= array(_DEPEND_DOWN_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
        }        
    } 
    /*
     * politicas (objetivo)
     */
    private function set_politicas($id) {
        if (isset($this->array_politicas)) 
            unset($this->array_politicas);

        $this->listar_politicas_ref_objetivo($id);
        foreach ($this->array_politicas as $row) {
            $this->matrix[$this->year]["OBJ-$id"]["POL-{$row['id']}"][$row['id_proceso']]= array(_DEPEND_DOWN_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
            $this->matrix[$this->year]["POL-{$row['id']}"]["OBJ-$id"][$row['id_proceso']]= array(_DEPEND_UPON_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
        }
    }
    /*
     * objetivos (inductor)
     */
    private function set_objetivos($id_inductor= null, $id_objetivo= null) {
        if (!empty($id_inductor)) {
            $this->listar_objetivos_ref_inductor($id_inductor, false);
            foreach ($this->array_pesos as $_id => $value) {
                $this->matrix[$this->year]["OBJ-{$_id}"]["IND-{$id_inductor}"][0] = array(_DEPEND_UPON_DIRECT, !is_null($value) ? (int)$value : null);
                $this->matrix[$this->year]["IND-{$id_inductor}"]["OBJ-$_id"][0] = array(_DEPEND_DOWN_DIRECT, !is_null($value) ? (int)$value : null);
            }
        }

        if (!empty($id_objetivo)) {
            $this->listar_objetivos_sup_ref_objetivo($id_objetivo, false);
            foreach ($this->array_pesos as $_id => $array) {
                $this->matrix[$this->year]["OBJ-{$_id}"]["OBJ-{$id_objetivo}"][$array['id_proceso']] = array(_DEPEND_UPON_DIRECT, !is_null($array['peso']) ? (int)$array['peso'] : null);
                $this->matrix[$this->year]["OBJ-{$id_objetivo}"]["OBJ-{$_id}"][$array['id_proceso']] = array(_DEPEND_DOWN_DIRECT, !is_null($array['peso']) ? (int)$array['peso'] : null);
            }
        }
    }
    /*
     * inductores (objetivo)
     * inductores (perspectiva)
     */
    private function set_inductores($id_objetivo= null, $id_perspectiva= null) {
        if (!empty($id_objetivo)) {
            $this->listar_inductores_ref_objetivo($id_objetivo, false);
            foreach ($this->array_pesos as $_id => $value) {
                $this->matrix[$this->year]["OBJ-$id_objetivo"]["IND-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, !is_null($value) ? (int)$value : null);
                $this->matrix[$this->year]["IND-{$_id}"]["OBJ-$id_objetivo"][0]= array(_DEPEND_DOWN_DIRECT, !is_null($value) ? (int)$value : null);
        }   }

        if (!empty($id_perspectiva)) {
            $this->listar_inductores_ref_perspectiva($id_perspectiva, false);
            foreach ($this->array_pesos as $_id => $row) {
                $this->matrix[$this->year]["PER-$id_perspectiva"]["IND-{$_id}"][$row['id_proceso']]= array(_DEPEND_UPON_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
                $this->matrix[$this->year]["IND-{$_id}"]["PER-$id_perspectiva"][$row['id_proceso']]= array(_DEPEND_DOWN_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
        }   }

        $this->listar_inductores_ref_proceso($_SESSION['id_entity']);
        foreach ($this->array_pesos as $_id => $row) {
            $this->matrix[$this->year]["EMP-{$_SESSION['id_entity']}"]["IND-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
            $this->matrix[$this->year]["IND-{$_id}"]["EMP-{$_SESSION['id_entity']}"][0]= array(_DEPEND_DOWN_DIRECT, !is_null($row['peso']) ? (int)$row['peso'] : null);
        }    
    }
    /*
     * programa(indicador)
     */
    private function set_programas($id_programa) {
        $this->listar_indicadores_ref_programa($id_programa, false);
        foreach ($this->array_pesos as $_id => $value) {
            $this->matrix[$this->year]["INDI-$_id"]["PROG-{$id_programa}"][0]= array(_DEPEND_DOWN_DIRECT, (int)$value);
            $this->matrix[$this->year]["PROG-{$id_programa}"]["INDI-$_id"][0]= array(_DEPEND_UPON_DIRECT, (int)$value);
        }
    }
    /*
     * proceso(indicador)
     */
    private function set_procesos($id_proceso) {
        $this->listar_indicadores_ref_proceso($id_proceso, false);
        foreach ($this->array_pesos as $_id => $array) {
            $this->matrix[$this->year]["PROC-$id_proceso"]["INDI-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, (int)$array['peso']);
            $this->matrix[$this->year]["INDI-{$_id}"]["PROC-$id_proceso"][0]= array(_DEPEND_DOWN_DIRECT, (int)$array['peso']);
        }
        
        $this->listar_objetivos_ref_proceso($id_proceso, false);
        foreach ($this->array_pesos as $_id => $array) {
            $this->matrix[$this->year]["PROC-$id_proceso"]["OBJ-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, 1);
            $this->matrix[$this->year]["OBJ-{$_id}"]["PROC-$id_proceso"][0]= array(_DEPEND_DOWN_DIRECT, 1);
        }        
        
        $this->listar_inductores_ref_proceso($id_proceso, false);
        foreach ($this->array_pesos as $_id => $array) {
            $this->matrix[$this->year]["PROC-$id_proceso"]["IND-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, 1);
            $this->matrix[$this->year]["IND-{$_id}"]["PROC-$id_proceso"][0]= array(_DEPEND_DOWN_DIRECT, 1);
        }        
    }
    /*
     * perspectiva(proceso)
     */
    private function set_perspectivas($id_proceso) {
        $this->listar_perspectivas_ref_proceso($id_proceso, false);
        foreach ($this->array_pesos as $_id => $array) {
            $this->matrix[$this->year]["PROC-$id_proceso"]["PER-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, (int)$array['peso']);
            $this->matrix[$this->year]["PER-{$_id}"]["PROC-$id_proceso"][0]= array(_DEPEND_DOWN_DIRECT, (int)$array['peso']);
        }

        $this->listar_perspectivas_ref_proceso($_SESSION['id_entity']);
        foreach ($this->array_pesos as $_id => $array) {
            $this->matrix[$this->year]["EMP-{$_SESSION['id_entity']}"]["PER-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, !is_null($array['peso']) ? (int)$array['peso'] : null);
            $this->matrix[$this->year]["PER-{$_id}"]["EMP-{$_SESSION['id_entity']}"][0]= array(_DEPEND_DOWN_DIRECT, !is_null($array['peso']) ? (int)$array['peso'] : null);
        }        
    }
    /*
     * indicadores (perspectiva)
     * indicadores (inductor)
     */
    private function set_indicadores($id_perspectiva= null, $id_inductor= null) {
        if (!empty($id_perspectiva)) {
            $this->listar_indicadores_ref_perspectiva ($id_perspectiva, false);
            foreach ($this->array_pesos as $_id => $array) {
                $this->matrix[$this->year]["PER-$id_perspectiva"]["INDI-{$_id}"][$array['id_proceso']]= array(_DEPEND_UPON_DIRECT, (int)$array['peso']);
                $this->matrix[$this->year]["INDI-{$_id}"]["PER-$id_perspectiva"][$array['id_proceso']]= array(_DEPEND_DOWN_DIRECT, (int)$array['peso']);
            }
        }

        if (!empty($id_inductor)) {
            $this->listar_indicadores_ref_inductor($id_inductor, false);
            foreach ($this->array_pesos as $_id => $value) {
                $this->matrix[$this->year]["IND-$id_inductor"]["INDI-{$_id}"][0]= array(_DEPEND_UPON_DIRECT, (int)$value);
                $this->matrix[$this->year]["INDI-{$_id}"]["IND-$id_inductor"][0]= array(_DEPEND_DOWN_DIRECT, (int)$value);
            }
        }
    }

    private function set_indicadores_indi($id) {
        $obj_indi= new Tindicador($this->clink);
        $obj_indi->SetYear($this->year);
        $obj_indi->SetIdIndicador($id);

        $array_indicadores= $obj_indi->get_array_indicadores_ref();
        foreach ($array_indicadores as $_id => $row) {
            $this->matrix[$this->year]["INDI-{$id}"]["INDI-$_id"][0]= array(_DEPEND_DOWN_DIRECT, null);
            $this->matrix[$this->year]["INDI-{$_id}"]["INDI-$id"][0]= array(_DEPEND_UPON_DIRECT, null);
        }
    }

    protected function cell_depend($row, $col) {
        if (is_null($this->matrix[$this->year][$row][$col]) || $row == $col)
            return _DEPEND_NO;

        reset($this->matrix);
        foreach ($this->matrix[$this->year][$row][$col] as $id_proceso => $row) {
            if (!is_null($row[0]))
                return $row[0];
        }
        return _DEPEND_NO;
    }

    private function fill_matrix() {
        $array_col_up= array();
        $array_col_down= array();

        $array_items_1= array();
        $array_items_2= array();
        foreach ($this->array_items as $key => $row) {
            $array_items_1[$key]= null;
            $array_items_2[$key]= null;
        }

        $i= 0;
        reset($array_items_1);
        foreach ($array_items_1 as $key_r => $row) {
            $array_col_up= array();
            $array_col_down= array();

            $depend= _DEPEND_NO;
            reset($array_items_2);
            foreach ($array_items_2 as $key_c => $col) {
                if ($key_r == $key_c)
                    continue;
                $depend= $this->cell_depend($key_r, $key_c);

                if ($depend == _DEPEND_NO)
                    continue;
                if ($depend == _DEPEND_UPON || $depend == _DEPEND_UPON_DIRECT)
                    $array_col_up[$key_c]= $key_c;
                if ($depend == _DEPEND_DOWN || $depend == _DEPEND_DOWN_DIRECT)
                    $array_col_down[$key_c]= $key_c;
            }

            $this->fill_row($key_r, $array_col_up, _DEPEND_UPON);
            $this->fill_row($key_r, $array_col_down, _DEPEND_DOWN);
            ++$i;
        }
    }

    private function fill_row($ref, $row_r, $depend) {
        $i= 0;
        reset($row_r);
        foreach ($row_r as $key_r => $cell) {
            $array_col= array();

            reset($this->array_items);
            foreach ($this->array_items as $key_c => $row_c) {
                if ($ref == $key_c)
                    continue;
                if (!is_null($this->matrix[$this->year][$ref][$key_c][0]))
                    continue;
                $_depend= $this->cell_depend($key_r, $key_c);

                if (is_null($this->matrix[$this->year][$key_r][$key_c])) {
                    $this->matrix[$this->year][$ref][$key_c][0]= array($depend, null);
                    continue;
                }
                if (is_null($_depend) || $_depend == _DEPEND_NO) {
                    $this->matrix[$this->year][$ref][$key_c][0] = array($depend, null);
                    continue;
                }
                if ($depend == _DEPEND_DOWN && ($_depend == _DEPEND_DOWN || $_depend == _DEPEND_DOWN_DIRECT)) {
                    $this->matrix[$this->year][$ref][$key_c][0] = array($depend, null);
                    $array_col[$key_c]= $key_c;
                    continue;
                }
                if ($depend == _DEPEND_UPON && ($_depend == _DEPEND_UPON || $_depend == _DEPEND_UPON_DIRECT)) {
                    $this->matrix[$this->year][$ref][$key_c][0] = array($depend, null);
                    $array_col[$key_c]= $key_c;
                    continue;
                }
            }

            $this->fill_row_sub($ref, $key_r, $array_col, $depend);
            ++$i;
        }
    }

    private function fill_row_sub($ref, $key_r, $row_r, $depend) {
        $i= 0;
        reset($row_r);
        foreach ($row_r as $key_c => $cell) {
            if ($ref == $key_c || $key_r == $key_c)
                continue;
            if (!is_null($this->matrix[$this->year][$ref][$key_c][0]))
                continue;

            $_depend= $this->cell_depend($ref, $key_c);

            if (!is_null($_depend) && $_depend != _DEPEND_NO)
                continue;

            if ($depend == _DEPEND_DOWN) {
                $this->matrix[$this->year][$ref][$key_c][0] = array(_DEPEND_DOWN, null);
                $this->matrix[$this->year][$key_c][$ref][0] = array(_DEPEND_UPON, null);
            }
            if ($depend == _DEPEND_UPON) {
                $this->matrix[$this->year][$ref][$key_c][0] = array(_DEPEND_UPON, null);
                $this->matrix[$this->year][$key_c][$ref][0] = array(_DEPEND_DOWN, null);
            }
        }
    }
}
