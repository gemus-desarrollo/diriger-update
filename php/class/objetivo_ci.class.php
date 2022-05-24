<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tobjetivo'))
    include_once "objetivo.class.php";

class Tobjetivo_ci extends Tobjetivo {
    protected $array_register;
    public $total;
    public $no_iniciadas;
    public $cumplidas;
    public $incumplidas;
    public $canceladas;
    
    public $no_iniciadas_list;
    public $cumplidas_list;
    public $incumplidas_list;
    public $canceladas_list;
            
    public function __construct($clink= null) {
         $this->clink= $clink;
         Tobjetivo::__construct($clink);

         $this->if_ci= true;
    }

    public function add_tarea($id_tarea, $id_tarea_code, $peso= 0) {
        $sql= "insert into tobjetivo_tareas (id_objetivo, id_objetivo_code, id_tarea, id_tarea_code, peso, id_proceso, ";
        $sql.= "id_proceso_code, cronos, situs) values ($this->id_objetivo, '$this->id_objetivo_code', $id_tarea, ";
        $sql.= "'$id_tarea_code', $peso, $this->id_proceso, '$this->id_proceso_code', '$this->cronos', '$this->location') ";
        $result= $this->do_sql_show_error('add_tarea', $sql);
        $cant= $this->clink->affected_rows();

        if (!$result || empty($cant)) {
            $sql= "update tobjetivo_tareas set peso= $peso, cronos= '$this->cronos', situs= '$this->location' ";
            $sql.= "where id_tarea= $id_tarea and id_objetivo= $this->id_objetivo ";
            if (!empty($this->id_proceso)) $sql.= "and id_proceso = $this->id_proceso";
            $resulr= $this->do_sql_show_error('add_tarea', $sql);
        }
    }

    public function delete_tarea($id_tarea= null) {
        $id_tarea= !is_null($id_tarea) ? $id_tarea : $this->id_tarea;

        $sql= "delete from tobjetivo_tareas where id_tarea = $id_tarea ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        $this->do_sql_show_error('eliminar_tarea', $sql);
    }

    public function get_tareas($full_list= true, $init_array= true) {
        $full_list= !is_null($full_list) ? $full_list : true;
        $init_array= !is_null($init_array) ? $init_array : true;
        if ($init_array)
            if (isset($this->array_tareas)) unset($this->array_tareas);
        if (isset($this->array_pesos)) unset($this->array_pesos);

        $sql= "select ttareas.*, tobjetivo_tareas.peso as peso from tobjetivo_tareas, ttareas  ";
        $sql.= "where tobjetivo_tareas.id_tarea = ttareas.id and id_objetivo = $this->id_objetivo ";
        if (!empty($this->year)) {
            $sql.= "and ((".year2pg("fecha_inicio_plan")." <= $this->year or ".year2pg("fecha_fin_plan")." >= $this->year) ";
            $sql.= " or (".year2pg("fecha_inicio_real"). " = $this->year or ".year2pg("fecha_fin_real"). " = $this->year) ";
            $sql.= "or fecha_fin_real is null) ";
        }
        if (!empty($this->id_proceso))
            $sql.= "and tobjetivo_tareas.id_proceso = $this->id_proceso ";
        $result= $this->do_sql_show_error('get_tareas', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if ($full_list) {
                $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_responsable'=>$row['id_responsable'],
                'fecha_inicio'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'], 'peso'=>$row['peso'],
                'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'], 'cumplimiento'=>null, 'array'=>null);

                $this->array_tareas[$row['id']]= $array;
            } else {
                $this->array_pesos[$row['id']]= $row['peso'];
            }
        }
        return $full_list ? $this->array_tareas : $this->array_pesos;
    }

    private function read_calcular(&$calcular) {
        $value= null;
        $calcular= boolean2pg(1);

        $sql= "select * from treg_objetivo where id_proceso= $this->id_proceso and year = $this->year ";
        $sql.= "and month = $this->month and id_objetivo = $this->id_objetivo ";
        $result= $this->do_sql_show_error("get_value", $sql);

        if (!empty($this->cant)) {
            $row= $this->clink->fetch_array($result);
            $calcular= boolean($row['calcular']);
            $value= $row['valor'];
        }
        return $value;
    }

    private function update_calcular($value) {
        $calcular= is_null($value) ? 1 : 0;
        $calcular= boolean2pg($calcular);
        $value= setNULL($value);

        $sql= "update treg_objetivo set valor = $value, calcular= $calcular, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_objetivo = $this->id_objetivo and id_proceso = $this->id_proceso and year = $this->year and month= $this->month ";
        $result= $this->do_sql_show_error('update_calcular', $sql);

        $cant= $this->clink->affected_rows();
        if (empty($cant)) 
            $this->insert_calcular($value);
    }

    private function insert_calcular($value) {
        $calcular= is_null($value) ? 1 : 0;
        $calcular= boolean2pg($calcular);
        $value= setNULL($value);

        $sql= "insert into treg_objetivo (valor, calcular, id_objetivo, id_objetivo_code, id_proceso, id_proceso_code, year, ";
        $sql.= "month, cronos, situs) values ($value, $calcular, $this->id_objetivo, '$this->id_objetivo_code', $this->id_proceso, ";
        $sql.= "'$this->id_proceso_code', $this->year, $this->month, '$this->cronos', '$this->location') ";
        $result= $this->do_sql_show_error('update_calcular', $sql);
    }

    public function calcular_objetivo_ci() {
        $value= null;
        $i= 0;
        
        $calcular= false;
        $_value= $this->read_calcular($calcular);

        if (isset($this->array_eventos)) unset($this->array_eventos);
        $this->array_eventos= array();
        
        $obj= new Ttarea($this->clink);
        $obj->SetYear($this->year);
        $obj->SetMonth($this->month);
        $obj->SetIdProceso($this->id_proceso);
        
        $obj->init_list();
        $this->no_iniciadas_list= array();
        $this->cumplidas_list= array();
        $this->incumplidas_list= array();
        $this->canceladas_list= array();
        
        $this->array_eventos= array();
        
        if (is_null($this->array_tareas))
            $this->get_tareas(true);

        $real= null;
        $plan= 0;
        foreach ($this->array_tareas as $tarea) {
            $obj->init_list();
            $obj->SetIdTarea($tarea['id']);
            $obj->SetIdResponsable($tarea['id_responsable']);
            $value= $obj->compute_from_eventos($tarea['id'], $this->id_objetivo);
            if (is_null($value))
                continue;

            $this->total+= $obj->total;
            $this->array_eventos= array_merge_overwrite($this->array_eventos, $obj->array_eventos);
            $this->no_iniciadas+= $obj->no_iniciadas;
            $this->no_iniciadas_list= array_merge_overwrite($this->no_iniciadas_list, $obj->no_iniciadas_list);
            $this->cumplidas+= $obj->cumplidas;
            $this->cumplidas_list= array_merge_overwrite($this->cumplidas_list, $obj->cumplidas_list);
            $this->incumplidas+= $obj->incumplidas;
            $this->incumplidas_list= array_merge_overwrite($this->incumplidas_list, $obj->incumplidas_list);
            $this->canceladas+= $obj->canceladas;
            $this->canceladas_list= array_merge_overwrite($this->canceladas_list, $obj->canceladas_list);
            
            $this->array_tareas[$tarea['id']]['cumplimiento']= $value;
            $array= $obj->compute_tarea();
            $this->array_tareas[$tarea['id']]['array']= $array;
            
            if ($tarea['peso'] > 0 && !is_null($value)) {
                ++$i;
                $real+= (float)$tarea['peso']*$value;
                $plan+= (float)$tarea['peso']*100;
            }
        }

        $value= !is_null($real) ? ((float)$real/$plan)*100 : null;
        if ($_value != $value && !is_null($value))
            $this->update_calcular($value);

        reset($this->array_tareas);
        return $value;
    }

    public function set_proceso($action= 'add') {
        if ($action == 'add') {
            $sql= "insert into tproceso_objetivos (year, id_proceso, id_proceso_code, id_objetivo, id_objetivo_code, ";
            $sql.= "cronos, situs) values ($this->year, $this->id_proceso, '$this->id_proceso_code', $this->id_objetivo, ";
            $sql.= "'$this->id_objetivo_code', '$this->cronos', '$this->location') ";
        } else {
            $sql= "delete from tproceso_objetivos where id_proceso = $this->id_proceso and id_objetivo = $this->id_objetivo ";
            if (!empty($this->year))
                $sql.= "and year = $this->year ";
        }

        $this->_set_user($sql);
        return $this->error;
    }

    public function get_procesos() {
        if (isset($this->array_procesos)) unset($this->array_procesos);

        $sql= "select * from tproceso_objetivos where id_objetivo = $this->id_objetivo ";
        if (!empty($this->year)) 
            $sql.= "and year = $this->year ";
        $result= $this->do_sql_show_error('get_procesos', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id_proceso'], 'id_code'=>$row['id_proceso_code']);
            $this->array_procesos[$row['id_proceso']]= $array;
        }
        return $this->array_procesos;
    }

    public function get_array_register($id= null) {
        if (is_null($id)) $id= $this->id_objetivo;

        $sql= "select * from treg_objetivo where year = $this->year and id_proceso = $this->id_proceso ";
        $sql.= "and id_objetivo = $id";
        $result= $this->do_sql_show_error('get_array_register', $sql);
        $row= $this->clink->fetch_array($result);

       if (is_null($this->array_register))
           $this->array_register= array('id_usuario'=>$row['id_usuario'], 'observacion'=>$row['observacion'], 'reg_fecha'=>$row['reg_fecha']);
       return $this->array_register;
    }
}
