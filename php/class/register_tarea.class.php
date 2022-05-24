<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */


if (!class_exists('Tregtarea'))
    include_once "regtarea.class.php";
if (!class_exists('Tkanban'))
    include_once "kanban.class.php";


class Tregister_tarea extends Tregtarea {
    public $array_dates;
    private $cant_dates;
    protected $estado;

    private $id_column_current,
            $id_column_todo,
            $id_column_inproccess,
            $id_column_ended;

    public function SetIiniciada() {
        $this->estado= 'F';
    }
    public function SetCompletada() {
        $this->estado= 'C';
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tregtarea::__construct($this->clink);

        $this->className = 'Ttarea';
    }

    private function get_array_dates() {
        $obj_event= new Tevento($this->clink);
        $obj_event->SetYear($this->year);

        $this->Set();
        $init_year= date('Y', strtotime($this->fecha_inicio_plan));
        $end_year= date('Y', strtotime($this->fecha_fin_plan));

        $obj_event->get_eventos_by_tarea($this->id_tarea, $init_year, $end_year);
        $i= 0;
        foreach ($obj_event->array_eventos as $event) {
            $date= date('Y-m-d', strtotime($event['fecha_inicio']));
            $this->array_dates[$this->id_tarea][$i++]= array('id'=>$event['id'], 'date'=>$date);
        }
        $this->cant_dates= $i;
        $this->cant= $i;
        return $this->array_dates;
    }

    private function get_kanban_column($id_tarea= null) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;

        $sql= "select tkanban_column_tareas.* from tkanban_column_tareas where id_tarea = $id_tarea ";
        $sql.= "and active != false ";
        if (!empty($this->id_proyecto))
            $sql.= "and id_proyecto = $this->id_proyecto ";
        if (!empty($this->id_responsable))
            $sql.= "and id_responsable = $this->id_responsable ";
        $sql.= "order by cronos desc";

        $result = $this->do_sql_show_error('get_kanban_column', $sql);
        $i= 0;
        while($row= $this->clink->fetch_array($result)) {
            if ($i == 0)
                $this->id_column_current= $row;
            if ($row['fixed'] == _TAREA_NO_INICIADA)
                $this->id_column_todo= $row;
            if ($row['fixed'] == _TAREA_EN_PROCESO)
                $this->id_column_inproccess= $row;
            if ($row['fixed'] == _TAREA_TERMINADA)
                $this->id_column_ended= $row;
        }
    }

    /** Para cuando desde los palnes se actualice el estado de cumplimiento de un dia especifico.
     * Entonces se producca la actualizacion del estado de la tarea en el tablero kanban
    */
    protected function set_kanban_column($date, $cumplimiento) {
        $obj_kan= new Tkanban($this->clink);
        $obj_kan->SetIdProyecto($this->id_proyecto);
        $obj_kan->SetIdResponsable($this->id_responsable);

        if (empty($this->array_dates[$this->id_tarea]))
            $this->get_array_dates();

        $column= $this->get_kanban_column($this->id_tarea);
        $id_column_new= null;

        if ($this->cant_dates == 1) {
            if ($cumplimiento == _CUMPLIDA)
                $id_column_new= $this->id_column_current != $this->id_column_ended ? $this->id_column_ended : null;
            elseif ($cumplimiento == _EN_CURSO)
                $id_column_new=  $this->id_column_current != $this->id_column_inproccess ? $this->id_column_inproccess : null;
            else
                $id_column_new=  $this->id_column_current != $this->id_column_todo ? $this->id_column_todo : null;
        }

        if ($this->cant_dates > 1) {
            if ($date <= $this->array_dates[$this->id_tarea][0]['date']) {
                if ($cumplimiento == _CUMPLIDA || $cumplimiento == _EN_CURSO) {
                    if ($this->id_column_current == $this->id_column_todo)
                        $id_column_new= $this->id_column_inproccess;
                } else {
                    $id_column_new= $this->id_column_todo;
                }
            }

            if ($date > $this->array_dates[$this->id_tarea][0]['date'] && $date < $this->array_dates[$this->id_tarea][$this->cant_dates-1]['date']) {
                if ($cumplimiento == _CUMPLIDA || $cumplimiento == _EN_CURSO) {
                    if ($this->id_column_current == $this->id_column_todo)
                        $id_column_new= $this->id_column_inproccess;
                    else {
                        if ($this->id_column_current == $this->id_column_ended)
                            $id_column_new= $this->id_column_inproccess;
                    }
                }
            }

            if ($date >= $this->array_dates[$this->id_tarea][$this->cant_dates-1]['date']) {
                if ($cumplimiento == _CUMPLIDA || $cumplimiento == _EN_CURSO) {
                    if ($this->id_column_current != $this->id_column_ended)
                        $id_column_new= $this->id_column_ended;
                }
            } else {
                if ($this->id_column_current == $this->id_column_ended)
                    $id_column_new= $this->id_column_inproccess;
            }
        }

        if (!empty($id_column_new))
            $obj_kan->update_tarea($this->id_column_current, $id_column_new);

        return;
    }

    /* para cuando se mueve en el kanban actualice el avance */
    public function fix_tarea_kanban_status($id_column_origen, $id_column_target) {
        if (empty($this->array_dates[$this->id_tarea]))
            $this->get_array_dates();

        if ($id_column_target == $this->id_column_ended)
            $this->fecha_fin_real= $this->cronos;

        if ($id_column_target == $this->id_column_todo && $id_column_target != $this->id_column_ended)
            $this->fecha_inicio_real= $this->cronos;

        if ($id_column_origen == $this->id_column_todo && $id_column_target != $this->id_column_ended) {
            $this->fecha_fin_real= null;
            $this->fecha_inicio_real= null;
        }

        $this->id_kanban_column= $id_column_target;
        $this->id_kanban_column_code= get_code_from_table($id_column_target, "tkanban_columns");
        $this->add_cump_to_task();
    }

    private function fix_target_kanban_status($id_target, $tipo) {
        if ($this->estado == 'C') {

        }
    }

    public function set_dependecies_status() {
        if (isset($this->array_targets))
            unset($this->array_targets);

        $this->array_targets= array();
        $this->array_targets= $this->GetDependencies(null, 'target');
        if (empty($this->cant))
            return;

        foreach ($this->array_targets as $row) {
            $this->fix_target_kanban_status($row['id'], $row['tipo']);
        }
    }
    /*
    * tipo => La dependencia que se quiere definir
    * tipo_prev => la dependencia que ya existe
    */
    private function if_restriction($tipo, $tipo_prev) {
        if ($tipo_prev == 'CC' || $tipo_prev == 'FF') {
            if ($tipo == 'FC' || $tipo == 'CF')
                return false;
        }
        if ($tipo_prev == 'FC') {
            if ($tipo != 'CC')
                return false;
        }
        if ($tipo_prev == 'CF') {
            if ($tipo != 'FF')
                return false;
        }
        return true;
    }

    private function _if_restrictions_depend($tipo) {
        $valid= null;

        switch ($tipo) {
            case 'CC':
                $valid= 'FF';
                break;
            case 'FF':
                $valid= 'CC';
                break;
            case 'CF':
                $valid= 'FF';
                break;
            case 'FC':
                $valid= 'CC';
                break;
        }
        return $valid;
    }

    public function get_restrictions_depend() {
        $array= null;
        $this->GetDependencies($this->id_tarea, 'source');
        $this->GetDependencies($this->id_tarea, 'target');

        if (array_key_exists($this->id_tarea, $this->array_source_tareas)) {
            $array= array();

            reset($this->array_source_tareas);
            foreach ($this->array_source_tareas as $tarea) {
                $array[$tarea['id']]= $this->_if_restrictions_depend($tarea['tipo']);
            }
        }
        if (array_key_exists($this->id_tarea, $this->array_target_tareas)) {
            if (is_null($array))
                $array= array();

            reset($this->array_target_tareas);
            foreach ($this->array_target_tareas as $tarea) {
                $array[$tarea['id']]= $this->_if_restrictions_depend($tarea['tipo']);
            }
        }

        return $array;
    }

    private function set_array_tareas_dependecies_status($result_task, $array_task_dependencies) {
        global $Ttarea_restrictions;

        $array_empty= array (
            'valid' => 4,
            'restrictions' => array('FS' => "Al Finalizar Comienza (FC)", 
                                    'SF' => "Al Comenzar Finaliza (CF)", 
                                    'SS' => "Al Comenzar Comienza (CC)", 
                                    'FF' => "Al Finalizar Finaliza(FF)"
                                )
        );

        $i= 0;
        $array_task_status= array();
        while ($row= $this->clink->fetch_array($result_task)) {
            ++$i;
            $id= $row['id'];
            if (array_key_exists($id, $array_task_dependencies)) {
                $array_task_status[$id]['status']= array('valid' => 1, 
                                                        'restrictions' => array($valid => $Ttarea_restrictions[$valid]));
            }
        }

        return $array_task_status;
    }
}
