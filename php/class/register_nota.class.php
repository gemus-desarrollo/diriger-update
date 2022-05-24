<?php
/**
 * Description of register_nota
 *
 * @author mustelier
 */

if (!class_exists('Tbase_lista'))
    include_once "base_lista.class.php";

class Tregister_nota extends Tbase_lista {
    public $array_notas;
    public $array_causas;
    protected $id_causa;
    protected $id_causa_code;
    protected $chk_apply;

    public function SetIdCausa($id) {
        $this->id_causa = $id;
    }
    public function GetIdCausa() {
        return $this->id_causa;
    }
    public function set_id_causa_code($id) {
        $this->id_causa_code = $id;
    }
    public function get_id_causa_code() {
        return $this->id_causa_code;
    }
    public function SetChkApply($id) {
        $this->chk_apply= $id;
    }
    public function GetChkApply() {
        return $this->chk_apply;
    }

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_lista::__construct($clink);
    }

    public function set_estado($id_proceso= null, $id_proceso_code= null) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        if ($id_proceso == $this->id_proceso && !empty($this->id_proceso_code))
            $id_proceso_code= $this->id_proceso_code;
        else    
            $id_proceso_code= get_code_from_table("tprocesos", $id_proceso);

        $calcular= setNULL_empty($this->calcular);
        $estado= setNULL($this->estado);
        $cumplimiento= setNULL($this->cumplimiento);
        $observacion= setNULL_str($this->observacion);

        $id_nota= setNULL_empty($this->id_nota);
        $id_nota_code= setNULL_str($this->id_nota_code);
        $id_auditoria= setNULL_empty($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);
        $id_lista= setNULL_empty($this->id_lista);
        $id_lista_code= setNULL_str($this->id_lista_code);
        $id_requisito= setNULL_empty($this->id_requisito);
        $id_requisito_code= setNULL_str($this->id_requisito_code);

        $chk_apply= boolean2pg($this->chk_apply);

        $sql= "insert into treg_nota (id_nota, id_nota_code, id_auditoria, id_auditoria_code, id_lista, id_lista_code, ";
        $sql.= "id_requisito, id_requisito_code, cumplimiento, chk_apply, calcular, id_proceso, id_proceso_code, ";
        $sql.= "id_usuario, estado, reg_fecha, observacion, cronos, situs) values ($id_nota, $id_nota_code, $id_auditoria, ";
        $sql.= "$id_auditoria_code, $id_lista, $id_lista_code, $id_requisito, $id_requisito_code, $cumplimiento, $chk_apply, ";
        $sql.= "$calcular, $id_proceso, '$id_proceso_code', $this->id_usuario, $estado, '$this->fecha', $observacion, ";
        $sql.= "'$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('set_estado', $sql);
        return $this->error;
    }

    public function getNota_reg($id_nota= null, $flag= false, $order= 'desc', $id_proceso= null, $chk_apply= null) {
        $id_nota= !empty($id_nota) ? $id_nota : $this->id_nota;

        $flag= !is_null($flag) ? $flag : false;
        $order= !is_null($order) ? $order : 'desc';
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $chk_apply= !is_null($chk_apply) ? $chk_apply : $this->chk_apply;
        $chk_apply= !is_null($chk_apply) ? $chk_apply : true;

        $sql= "select treg_nota.*, tusuarios.nombre as responsable from treg_nota, tusuarios ";
        $sql.= "where treg_nota.id_usuario = tusuarios.id ";
        if ($chk_apply)
            $sql.= "and treg_nota.chk_apply = true ";
        if (!empty($id_nota))
            $sql.= "and id_nota = $id_nota ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_lista))
            $sql.= "and id_lista = $this->id_lista ";
        if (!empty($this->id_requisito))
            $sql.= "and id_requisito = $this->id_requisito ";
        if (!empty($id_proceso))
            $sql.= "and treg_nota.id_proceso = $id_proceso ";
        $sql.= "order by ".date2pg("reg_fecha")." $order, treg_nota.cronos $order limit 1";

        $result= $this->do_sql_show_error('getNota_reg', $sql);
/*
        if ($this->cant == 0) {
            $sql= "select treg_nota.*, tusuarios.nombre as responsable from treg_nota, tusuarios ";
            $sql.= "where treg_nota.id_usuario = tusuarios.id ";
            if (!empty($id_nota))
                $sql.= "and id_nota = $id_nota ";
            if (!empty($this->id_requisito))
                $sql.= "and id_requisito = $this->id_requisito ";
            if (!empty($this->id_usuario))
                $sql.= "and id_usuario = $this->id_usuario ";
            if (!empty($id_proceso))
                $sql.= "and treg_nota.id_proceso = $id_proceso ";
            if (!empty($this->estado))
                $sql.= "and estado = $this->estado ";
            $sql.= "order by treg_nota.cronos $order limit 1";

            $result= $this->do_sql_show_error('getNota_reg', $sql);
        }
*/
        if ($flag)
            return $result;
        $array= null;

       if (!empty($this->cant))  {
           $row= $this->clink->fetch_array($result);

           $array= array('id'=>$row['id_nota'], 'id_code'=>$row['id_nota_code'], 'id_auditoria'=>$row['id_auditoria'],
               'id_auditoria_code'=>$row['id_auditoria_code'], 'id_lista'=>$row['id_lista'], 'id_lista_code'=>$row['id_lista_code'],  
               'id_requisito'=>$row['id_requisito'], 'id_requisito_code'=>$row['id_requisito_code'], 'estado'=>$row['estado'], 
               'cumplimiento'=>$row['cumplimiento'], 'responsable'=>$row['responsable'], 'id_proceso'=>$row['id_proceso'], 
               'observacion'=>$row['observacion'], 'id_usuario'=>$row['id_usuario'], 'cronos'=>$row['cronos'],
               'reg_fecha'=>$row['reg_fecha'], 'tipo'=>null, 'chk_apply'=>boolean($row['chk_apply']));
       }

        return $array;
    }

    public function getAvance($id_nota= null, $flag= false) {
        $id_nota= !empty($id_nota) ? $id_nota : $this->id_nota;

        $time= new TTime();
        $lastday= $time->longmonth($this->month, $this->year);
        $date= $this->year.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-'.$lastday;
        unset($time);

        $sql= "select treg_nota.*, tusuarios.nombre as responsable from treg_nota, tusuarios where ";
        $sql.= "(date(reg_fecha) <= '$date' and estado is not null) and treg_nota.id_usuario = tusuarios.id ";
        $sql.= "and chk_apply = true ";
        if (!empty($id_nota))
            $sql.= "and id_nota = $id_nota ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_lista))
            $sql.= "and id_lista = $this->id_lista ";
        if (!empty($this->id_requisito))
            $sql.= "and id_requisito = $this->id_requisito ";
        $sql.= "order by cronos desc ";

        $result= $this->do_sql_show_error('getAvance', $sql);
        if ($flag)
            return $result;

        $row= $this->clink->fetch_array($result);
        $valor= $row['valor'];

        $this->observacion= $row['observacion'];
        $this->cumplimiento= $row['cumplimiento'];
        $this->estado= $row['estado'];

        if (empty($valor))
            $valor= 0;
        return $valor;
    }

// tnota_causas  ///////////////////////////////////////////////////////////////////////////////////////
    public function set_causa($id= null) {
        if (!empty($id))
            $this->id_causa= $id;

        $sql= "select * from tnota_causas where id = $this->id_causa";
        $result= $this->do_sql_show_error('set_causa', $sql);
        if (!$result)
            return $this->error;
        $row= $this->clink->fetch_array($result);

        $this->id_causa_code= $row['id_code'];

        $this->id_nota= $row['id_nota'];
        $this->id_nota_code= $row['id_nota_code'];

        $this->id_riesgo= $row['id_riesgo'];
        $this->id_riesgo_code= $row['id_riesgo_code'];

        $this->id_usuario= $row['id_usuario'];
        $this->fecha= $row['fecha'];
        $this->desc= stripslashes($row['descripcion']);

        $this->kronos= $row['cronos'];
    }

    public function add_causa() {
        $fecha= setNULL_str($this->fecha);
        $descripcion= setNULL_str($this->descripcion);

        $id_nota= setNULL($this->id_nota);
        $id_nota_code= setNULL_str($this->id_nota_code);

        $id_riesgo= setNULL($this->id_riesgo);
        $id_riesgo_code= setNULL_str($this->id_riesgo_code);

        $sql= "insert into tnota_causas (id_nota, id_nota_code, id_riesgo, id_riesgo_code, fecha, descripcion, ";
        $sql.= "id_usuario, cronos, situs) values ($id_nota, $id_nota_code, $id_riesgo, $id_riesgo_code, ";
        $sql.= "$fecha, $descripcion, $this->id_usuario, '$this->cronos', '$this->location')";
        $result= $this->do_sql_show_error('add_causa', $sql);

        if ($result) {
            $this->id_causa= $this->clink->inserted_id("tnota_causas");
            $this->obj_code->SetId($this->id_causa);
            $this->obj_code->set_code('tnota_causas','id','id_code');
            $this->id_causa_code= $this->obj_code->get_id_code();
        }

        return $this->error;
    }

    public function update_causa() {
        $fecha= setNULL_str($this->fecha);
        $descripcion= setNULL_str($this->descripcion);

        $sql= "update tnota_causas set descripcion= $descripcion, fecha= $fecha, id_usuario= $this->id_usuario, ";
        $sql.= "situs= '$this->location', cronos= '$this->cronos' where 1 ";
        if (!empty($this->id_nota))
            $sql.= "and id_nota = $this->id_nota ";
        if (!empty($this->id_causa))
            $sql.= "and id = $this->id_causa ";
        if (!empty($this->id_riesgo))
            $sql.= "and id_riesgo = $this->id_riesgo ";
        $result= $this->do_sql_show_error('update_causa', $sql);
        return $this->error;
    }

    public function eliminar_causa($id_causa= null) {
        $id_causa= !empty($id_causa) ? $id_causa : $this->id_causa;
        if (empty($this->id_nota) && empty($id_causa) && empty($this->id_riesgo))
            return null;

        $sql= "delete from tnota_causas where 1 ";
        if (!empty($id_causa))
            $sql.= "and id = $id_causa ";
        if (!empty($this->id_nota))
            $sql.= "and id_nota = $this->id_nota ";
        if (!empty($this->id_riesgo))
            $sql.= "and id_riesgo = $this->id_riesgo ";
        $result= $this->do_sql_show_error('eliminar_causa', $sql);

        if (is_null($this->error)) {
            if (!empty($this->id_nota))
                $this->obj_code->reg_delete('tnota_causas', 'id_nota_code', $this->id_nota_code, 'id_code', $this->id_causa_code);
            if (!empty($this->id_riesgo))
                $this->obj_code->reg_delete('tnota_causas', 'id_riesgo_code', $this->id_riesgo_code, 'id_code', $this->id_causa_code);
        }

        return $this->error;
    }

    public function listar_causas($flag= false) {
        if (isset($this->array_causas)) unset ($this->array_causas);
        $this->array_causas= array();

        $sql= "select tnota_causas.*, id as id_causa, id_code as id_causa_code from tnota_causas ";
        if (!empty($this->id_nota))
            $sql.= "where id_nota = $this->id_nota ";
        if (!empty($this->id_riesgo))
            $sql.= "where id_riesgo = $this->id_riesgo ";
        $sql.= "order by fecha asc";
        $result= $this->do_sql_show_error('listar_causa', $sql);
        if (!$flag)
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'descripcion'=>$row['descripcion'],
                        'fecha'=>$row['fecha'], 'id_usuario'=>$row['id_usuario'], 'origen_data'=>$row['origen_data']);
            $this->array_causas[$row['id']]= $array;
        }

        $this->cant= $i;
        return $this->array_causas;
    }

    public function cleanCausas() {
        $sql= "delete from tnota_causas ";
        if (!empty($this->id_nota))
            $sql.= "where id_nota = $this->id_nota ";
        if (!empty($this->id_riesgo))
            $sql.= "where id_riesgo = $this->id_riesgo ";

        $result= $this->do_sql_show_error('cleanCausas', $sql);
        return $this->error;
    }

    public function get_array_usuarios() {
        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->GetIdRiesgo($this->id_riesgo);
        $obj_prs->SetIdNota($this->id_nota);

        $array_procesos= $obj_prs->GetProcesosRiesgo();
        $obj_prs->array_usuarios= null;

        foreach ($array_procesos as $id => $prs) {
            $obj_prs->SetIdProceso($id);
            $obj_prs->listar_usuarios_proceso($id, false, null, null, true);
        }

        foreach ($obj_prs->array_usuarios as $id => $array)
            $this->array_usuarios[$id]= $array;

        return $this->array_usuarios;
    }

    /*
     * Gestion de las tareas asociadas a riesgo y notas
     */
    protected $_id, $_table;

    protected function setClass($id= null) {
        $table= null;
        if ($this->className == 'Triesgo') {
            $this->_id= !empty($id) ? $id : $this->id_riesgo;
            $this->_table= 'riesgo';
        }
        if ($this->className == 'Tnota') {
            $this->_id= !empty($id) ? $id : $this->id_nota;
            $this->_table= 'nota';
        }
    }

    private function sql_tarea_proceso($id_proceso, $inicio= null, $fin= null) {
        $sql= null;
        $inicio= !empty($inicio) ? $inicio : $this->inicio ? $this->inicio : $this->year;
        $fin= !empty($fin) ? $fin : $this->fin ? $this->fin : $this->year;

        for ($year= $inicio; $year <= $fin; $year++) {
            $sql.= $year > $inicio ? " union " : "";
            $sql.= "select distinct ttareas.*, ttareas.nombre as _nombre, ttareas.id_responsable as _id_responsable, ";
            $sql.= "ttareas.id as _id, ttareas.id_code as _id_code, ttareas.id_proceso as id_proceso_ref, ";
            $sql.= "tproceso_eventos_$year.id_responsable as id_responsable_ref from ttareas, triesgo_tareas, ";
            $sql.= "tproceso_eventos_$year where ttareas.id = triesgo_tareas.id_tarea ";
            $sql.= "and triesgo_tareas.id_tarea = tproceso_eventos_$year.id_tarea ";
            $sql.= !empty($this->_id) ? "and id_$this->_table = $this->_id " : "and id_$this->_table is not null ";
            if (!empty($id_proceso))
                $sql.= "and tproceso_eventos_$year.id_proceso = $id_proceso ";
            if ($this->className == "Triesgos")
                $sql.= "and (".year2pg("fecha_inicio_plan")." = $year or ".year2pg("fecha_fin_plan")." = $year) ";
        }
        return $sql;
    }

    private function sql_tarea_riesgo($inicio= null, $fin= null) {
        $inicio= !empty($inicio) ? $inicio : $this->inicio;
        $fin= !empty($fin) ? $fin : $this->fin;

        $sql= "select distinct ttareas.*, ttareas.nombre as _nombre, ttareas.id_responsable as _id_responsable, ";
        $sql.= "ttareas.id as _id, ttareas.id_code as _id_code, ttareas.id_proceso as id_proceso_ref, ";
        $sql.= "ttareas.id_responsable as id_responsable_ref from ttareas, triesgo_tareas ";
        $sql.= "where ttareas.id = triesgo_tareas.id_tarea ";
        $sql.= !empty($this->_id) ? "and id_$this->_table = $this->_id " : "and id_$this->_table is not null ";

        if ($this->className == "Triesgo") {
            if (!empty($this->year) && (empty($inicio) && empty($fin))) {
                $sql.= "and (".year2pg("fecha_inicio_plan")." = $this->year or ".year2pg("fecha_fin_plan")." = $this->year) ";
            }
            if (!empty($inicio) && !empty($fin)) {
                $sql.= "and ((".year2pg("fecha_inicio_plan")." >= $inicio and ".year2pg("fecha_inicio_plan")." <= $fin) ";
                $sql.= "or (".year2pg("fecha_fin_plan")." >= $inicio and ".year2pg("fecha_fin_plan")." <= $fin)) ";
            }
        }
        return $sql;
    }

    protected function get_inicio_fin() {
        $array= null;

        $sql= "select min(fecha_inicio_plan) as min_date, max(ttareas.fecha_fin_plan) as max_date  ";
        $sql.= "from ttareas, triesgo_tareas where ttareas.id = triesgo_tareas.id_tarea ";
        $sql.= !empty($this->_id) ? "and id_$this->_table = $this->_id " : "and id_$this->_table is not null ";
        $result= $this->do_sql_show_error('get_inicio_fin', $sql);
        $row= $this->clink->fetch_array($result);
        if ($this->cant > 0) {
            $array= array('inicio'=>$row[0], 'fin'=>$row[1]);
        }
        return $array;
    }

    public function get_array_procesos($id_tarea= null) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;

        $sql= "select distinct tprocesos.* from tprocesos, tproceso_eventos_{$this->year} ";
        $sql.= "where tprocesos.id = tproceso_eventos_{$this->year}.id_proceso and id_tarea = $id_tarea";
        $result= $this->do_sql_show_error('get_inicio_fin', $sql);

        $array_procesos= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array_procesos[$row['id']]= array('id'=>$row['id'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                                        'id_entity'=>$row['id_entity'], 'id_responsable'=>$row['id_responsable'],
                                        'conectado'=>$row['conectado']);
        }
        return $array_procesos;
    }

    public function listar_tareas($id= null, $id_proceso= null, $flag= false, $inicio= null, $fin= null) {
        $this->setClass($id);

        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        if (isset($this->array_tareas)) 
            unset($this->array_tareas);
        $this->array_tareas= array();
        $array_procesos= array();

        $inicio= !empty($inicio) ? $inicio : $this->inicio;
        $fin= !empty($fin) ? $fin : $this->fin;

        if (!empty($id_proceso))
            $sql= $this->sql_tarea_proceso($id_proceso, $inicio, $fin);
        else
            $sql= $this->sql_tarea_riesgo($inicio, $fin);

        $result= $this->do_sql_show_error('listar_tareas', $sql);
        if (!$flag)
            return $result;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row['_id'], $this->array_tareas) == false)
                $array_procesos[$row['_id']]= $this->get_array_procesos($row['_id']);
            else {
                --$this->cant;
                continue;
            }
            ++$i;
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'nombre'=>$row['_nombre'], 
                'descripcion'=>$row['descripcion'], 'fecha_inicio'=>$row['fecha_inicio_plan'], 
                'fecha_fin'=>$row['fecha_fin_plan'], 'id_responsable'=>$row['_id_responsable'],
                'id_responsable_2'=>$row['id_responsable_2'], 'responsable_2_reg_date'=>$row['responsable_2_reg_date'],
                'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code'], 'ifgrupo'=>$row['ifgrupo'],
                'chk_date'=>boolean($row['chk_date']), 'id_proceso_ref'=>$row['id_proceso_ref'],
                'id_responsable_ref'=>$row['id_responsable_ref'], 'id_usuario'=>$row['id_usuario'], 
                'origen_data'=>$row['origen_data'], 'procesos'=>$array_procesos[$row['_id']],
                'copyto'=>$row['copyto']);

            $this->array_tareas[$row['_id']]= $array;
        }
        $this->cant= $i;
        return $this->array_tareas;
    }

    public function cleanTareas() {
        $this->setClass();

        $sql= "delete from t$this->_table"."_tareas where id_tareas is nor null and id_$this->_table= $this->_id ";
        $this->do_sql_show_error('clean_tarea', $sql);
        return $this->error;
    }

    public function delete_tarea($id_tarea) {
        if (empty($id_tarea)) 
            $id_tarea= $this->id_tarea;
        $this->setClass();

        $sql= "delete from triesgo_tareas where id_tarea = $id_tarea and id_$this->_table= $this->_id ";
        $result= $this->do_sql_show_error('delete_tarea', $sql);
        return $this->error;
    }

    public function add_tarea() {
        $this->setClass();
        $id_usuario= $_SESSION['id_usuario'];

        $sql= "insert into triesgo_tareas (id_$this->_table, id_$this->_table"."_code, id_tarea, id_tarea_code, ";
        $sql.= "id_usuario, cronos, situs) values ($this->_id, '$this->id_code', $this->id_tarea, ";
        $sql.= "'$this->id_tarea_code', $id_usuario, '$this->cronos', '$this->location')";
        $result= $this->do_sql_show_error('add_tarea', $sql);
        return $this->error;
    } 
    
    protected function get_tarea_reg($id_tarea= null, $year_init= null, $year_end= null) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $year_init= !empty($year_init) ? $year_init : $this->year;
        $year_end= !empty($year_end) && $year_end >= $year_init ? $year_end : $year_init;

        $obj= new Tevento($this->clink);
        $obj_reg= new Tregister_planning($this->clink);
        
        $obj->get_eventos_by_tarea($id_tarea, array($year_init, $year_end));
        
        $rowcmp= null;
        $fecha_inicio= null;
        $cronos= null;
        $ncumplimiento= 0;
        $n_outdate= 0;
        foreach ($obj->array_eventos as $evento) {  
            $obj_reg->SetYear(date('Y', strtotime($evento['fecha_inicio'])));
            $obj_reg->SetIdEvento($evento['id']);
            $row= $obj_reg->getEvento_reg($evento['id'], $evento);
            
            if (!empty($this->reg_fecha) 
                && (strtotime(date('Y-m-d', strtotime($evento['fecha_inicio']))) > strtotime($this->reg_fecha) && $row['cumplimiento'] != _COMPLETADO)) {
                if ($row['cumplimiento'] != _COMPLETADO && $row['cumplimiento'] != _CANCELADO && $row['cumplimiento'] != _SUSPENDIDO)
                    ++$n_outdate;
                continue;
            }              
            if (is_null($fecha_inicio) || (!empty($fecha_inicio) && strtotime($fecha_inicio) < strtotime($evento['fecha_inicio']))) {
                $fecha_inicio= $evento['fecha_inicio'];
                $rowcmp= $row;
            }  
            if (is_null($cronos) || (!empty($cronos) && strtotime($cronos) < strtotime($row['cronos']))) {
                $cronos= $row['cronos'];
            }             
            if ($row['cumplimiento'] == _COMPLETADO)
                ++$ncumplimiento;
        }
        
        return array($ncumplimiento, $n_outdate, $cronos, $rowcmp);
    }    
}
