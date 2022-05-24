<?php
/**
* @author Geraudis Mustelier Portuondo
* @copyright 2012
*/

include_once "../config.inc.php";

if (!class_exists('Tplantrab'))
    include_once "plantrab.class.php";

class Tplan_ci extends Tplantrab {

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tplantrab::__construct($clink);

        if (empty($this->cronos)) 
            $this->cronos= date('Y-m-d H:i:s');
    }
    
    public function aprove_task_to_users() {      
        $obj= new Tevento($this->clink);
        $obj->SetIdResponsable($_SESSION['id_usuario']);
        $obj->set_cronos($this->cronos);
        
        $observacion= "Aprobado Plan de Prevencion en fecha ".$this->cronos;
        $obj->SetObservacion($observacion);
        
        $sql= "select distinct triesgos.id as _id from triesgos, tproceso_riesgos where triesgos.id = tproceso_riesgos.id_riesgo ";
        $sql.= "and year(fecha_inicio_plan) = $this->year and tproceso_riesgos.id_proceso = $this->id_proceso ";
    
        $result_r= $this->do_sql_show_error('get_all_tareas', $sql);
        if (!$this->cant) 
            return;
        
        while ($row_r= $this->clink->fetch_array($result_r)) {
            $id_riesgo= $row_r['_id'];
            $sql= "select distinct teventos.id_tarea, teventos.id_tarea_code, teventos.id, teventos.id_code from teventos, triesgo_tareas ";
            $sql.= "where triesgo_tareas.id_riesgo = $id_riesgo and teventos.id_tarea = triesgo_tareas.id_tarea ";  
           
            $result_e= $this->do_sql_show_error('get_all_tareas', $sql);
            if (!$this->cant) 
                continue;
            
            while ($row_e= $this->clink->fetch_array($result_e)) {
                $id_evento= $row_e['id']; 
                $id_evento_code= $row_e['id_code'];
                $id_tarea= $row_e['id_tarea']; 
                $id_tarea_code= $row_e['id_tarea_code'];
                
                $obj->SetIdTarea($id_tarea); 
                $obj->set_id_tarea_code($id_tarea_code);
                $obj->SetIdEvento($id_evento); 
                $obj->set_id_evento_code($id_evento_code); 
                
                $obj->set_id_code($id_evento_code);  
                
                $sql= "select id_usuario from treg_evento where id_evento = $id_evento and id_tarea = $id_tarea";
                $result_u= $this->do_sql_show_error('get_all_tareas', $sql);
                if (!$this->cant) 
                    continue;
                
                while ($row_u= $this->clink->fetch_array($result_u)) {
                    $id_usuario= $row_u['id_usuario'];
                    $obj->SetIdUsuario($id_usuario);
                    
                    $row_reg= $obj->get_last_reg($id_evento, $id_usuario);
                    if (!is_null($row_reg['aprobado'])) 
                        continue;
                    
                    $obj->SetCumplimiento($row_reg['cumplimiento']);
                    $obj->SetRechazado($row_reg['rechazado']);
                    $obj->SetHours($row_reg['horas']);
                    $obj->SetAprobado($this->cronos);
                    $obj->SetFecha($row_reg['reg_fecha']);
                    $obj->set_user_check($row_reg['_user_check']);
                    $obj->toshow= $row_reg['_toshow'];
                    $obj->compute= $row_reg['_compute'];
                    
                    $obj->add_cump();
                }
            }       
        }
        
        unset($obj);    
    }

    private function create_tmp_tauditorias() {
        $fecha_inicio= null;
        $fecha_fin= null;
        $this->obj_tables->_create_tmp_tauditorias();
        $this->list_date_interval($fecha_inicio, $fecha_fin, 'all_year');

        $sql= "insert into _tauditorias ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= "select distinct tauditorias.id, tauditorias.id_code, NULL, NULL, tauditorias.id_responsable, tauditorias.id_proceso, ";
        $sql.= "tauditorias.id_usuario, tauditorias.cronos, tauditorias.id_responsable, id_responsable_2, responsable_2_reg_date, lugar, ";
        $sql.= "objetivos, organismo, origen, id_tipo_auditoria, id_tipo_auditoria_code, jefe_auditor, fecha_inicio_plan, fecha_inicio_real, ";
        $sql.= "fecha_fin_plan, fecha_fin_real, tproceso_eventos_{$this->year}.empresarial, tproceso_eventos_{$this->year}.id_tipo_evento, ";
        $sql.= (!empty($this->id_proceso) && empty($this->id_usuario)) ? "tproceso_eventos_{$this->year}.toshow, " : "0, ";
        $sql.= "user_check, tauditorias.id_auditoria, tauditorias.id_auditoria_code, periodic, numero, numero_plus, ";
        if (!empty($this->id_proceso) && empty($this->id_usuario))
            $sql.= "tproceso_eventos_{$this->year}.id_proceso, tproceso_eventos_{$this->year}.id_proceso_code, ";
        else    
            $sql.= "0, '0', ";
        if (!empty($this->id_proceso) && empty($this->id_usuario)) 
            $sql.= "tproceso_eventos_{$this->year}.indice, tproceso_eventos_{$this->year}.indice_plus, ";
        else 
            $sql.= "tauditorias.indice, tauditorias.indice_plus, ";
        $sql.= "tidx ";
        if (!empty($this->id_proceso) && empty($this->id_usuario)) {
            $sql.= ", cumplimiento, tproceso_eventos_{$this->year}.observacion, aprobado, rechazado, ";
            $sql.= "tproceso_eventos_{$this->year}.cronos ";
        }
        $sql.= "from tauditorias ";
        if (!empty($this->id_proceso)) 
            $sql.= ", tproceso_eventos_{$this->year}, _tprocesos ";
   //     $sql.= "where (".date2pg("fecha_fin_plan")." >= '$fecha_inicio' and ".date2pg("fecha_fin_plan")." <= '$fecha_fin')";
        $sql.= "where (".year2pg("fecha_inicio_plan")." = $this->year or ".year2pg("fecha_fin_plan")." = $this->year) ";

        if (!empty($this->id_proceso))
            $sql.= "and (tauditorias.id = tproceso_eventos_{$this->year}.id_auditoria and tproceso_eventos_{$this->year}.id_proceso = _tprocesos.id) ";
        if (!empty($this->origen)) 
            $sql.= "and tauditorias.origen = $this->origen ";
        if (!empty($this->tipo)) 
            $sql.= "and tauditorias.tipo = $this->tipo ";
        if (!empty($this->organismo)) 
            $sql.= "and tauditorias.organismo = '$this->organismo' ";
        $sql.= "order by fecha_inicio_plan desc";

        $this->do_sql_show_error('create_tmp_tauditorias', $sql);

        if (empty($this->error)) {
            $this->if_tauditorias= true;
            $this->obj_tables->if_tauditorias= $this->if_tauditorias;
            $this->obj_tables->init_tmp_teventos();
        }
        return $this->error;
    }
    
    public function create_tmp_teventos($date_cut= null) {
        $this->obj_tables->_create_tmp_teventos();

        $sql= "insert into _teventos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;

        $sql.= "select distinct teventos.id, null, teventos.id_code, teventos.numero, teventos.id_responsable, teventos.id_responsable_2, ";
        $sql.= "teventos.responsable_2_reg_date, teventos.id_responsable, teventos.id_proceso, teventos.id_usuario, teventos.cronos, ";
        $sql.= "teventos.origen_data, funcionario, teventos.nombre, teventos.fecha_inicio_plan, teventos.fecha_fin_plan, teventos.periodicidad, ";
        if ($this->if_tauditorias) 
            $sql.= "_tauditorias.empresarial, _tauditorias.id_tipo_evento, ";
        else
            $sql.= "teventos.empresarial, teventos.id_tipo_evento, ";
        $sql.= "0, teventos.user_check, teventos.descripcion, teventos.lugar, teventos.id_evento, ";
        $sql.= "teventos.id_evento_code, teventos.id_tarea, teventos.id_tarea_code, teventos.id_auditoria, teventos.id_auditoria_code, ";
        $sql.= "id_tipo_reunion, id_tipo_reunion_code, id_tematica, id_tematica_code, id_copyfrom, id_copyfrom_code, teventos.ifassure, ";
        $sql.= " teventos.id_secretary, teventos.id_archivo, teventos.id_archivo_code, teventos.numero_plus, ";
        $sql.= day2pg("teventos.fecha_fin_plan").", ".month2pg("teventos.fecha_fin_plan").", ".year2pg("teventos.fecha_fin_plan").", ";
        if ($this->if_tauditorias) {
            $sql.= "_tauditorias.id_proceso, _tauditorias.id_proceso_code, _tauditorias.indice, _tauditorias.indice_plus, _tauditorias.tidx, ";
            $sql.= "null ,null, null, null, null, null ";
            $sql.= "from teventos, _tauditorias where teventos.id_auditoria = _tauditorias.id ";
        }
        if ($this->if_ttareas) {
            $sql.= "_ttareas.id_proceso, _ttareas.id_proceso_code, 0, 0, 0, ";
            $sql.= "null ,null, null, null, null, null ";
            $sql.= "from teventos, _ttareas where teventos.id_tarea = _ttareas.id ";
        }
        if (!empty($date_cut)) 
            $sql.= " and ".date2pg("teventos.fecha_inicio_plan")." <= '$date_cut' "; 
        $sql.= "order by fecha_inicio_plan desc";

        $result= $this->do_sql_show_error('create_tmp_teventos', $sql);
        if (empty($this->error)) {
            $this->if_teventos= true;
            $this->obj_tables->if_teventos= $this->if_teventos;
        }
        return $this->error;
    }

    public function automatic_audit_status($limited= false, $flag= true) {
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $this->if_auditoria = true;

        $this->copy_in_object($this->obj_tables);
        
        $this->debug_time('create_tmp_tauditorias_steep_1');

        $this->obj_tables->create_tmp_tprocesos(0);
        $this->if_tprocesos= $this->obj_tables->if_tprocesos;
        $this->array_procesos= $this->obj_tables->array_procesos;
        
        $this->create_tmp_tauditorias();
        
        if ($this->if_tauditorias) {
            $this->create_tmp_teventos();
            $this->if_teventos= $this->obj_tables->if_teventos;
        }
/*
        if ($this->if_teventos) {
            $this->obj_tables->create_tmp_tproceso_eventos();
            $this->if_tproceso_eventos= $this->obj_tables->if_tproceso_eventos;
        }
*/    
        if ($this->if_teventos)
            $this->fix_tmp_teventos_prs();
        
        if (empty($this->cant) || $this->cant == -1) 
            return _EMPTY;

        if ($limited) 
            $this->obj_tables->_create_tmp_tidx_tmp();
        $this->if_tidx= $this->obj_tables->if_tidx;
        $error= $this->obj_tables->create_tmp_treg_evento_tmp(false);
        $this->if_treg_evento= $this->obj_tables->if_treg_evento;
        
        $sql= "select * from _tauditorias ";
        if ($this->if_tidx) 
            $sql.= ", _tidx where _tauditorias.id = _tidx.id or _tauditorias.id_auditoria = _tidx.id ";
        $sql.= "order by _tauditorias.cronos desc ";

        $this->do_sql_show_error('automatic_audit_status', $sql);
        $this->num_rows_in_page= $this->cant;
        if (empty($this->cant)) 
            return _EMPTY;

        $this->debug_time("create_tmp_tauditorias_steep_1");
        if (!is_null($error)) 
            return $error;

        $this->automatic_status($flag, $limited);
    }

    private function automatic_status($flag= true, $limited= false) {
        $flag= !is_null($flag) ? $flag : true;
        $array_eventos = array();
        $array_id = NULL;
        $this->copy_in_object($this->obj_tables);
        
        $sql= "select * from _teventos order by cronos desc ";
        $result= $this->do_sql_show_error('automatic_audit_status', $sql);

        if (empty($this->cant)) 
            return _EMPTY;
        if (!$flag) 
            return $this->cant;

        $this->debug_time('test_if_incumplida');

        while ($row= $this->clink->fetch_array($result)) {
            if (isset($array_eventos[$row['id']])) 
                continue;
            $array_eventos[$row['id']]= $row['id'];

            $this->cumplimiento= null;
            $array_responsable= array('id_responsable'=>$row['id_responsable'], 'id_responsable_2'=>$row['id_responsable_2'],
                'responsable_2_reg_date'=>$row['responsable_2_reg_date']);
            $rowcmp= $this->getEvento_reg($row['id'], $array_responsable);
            $cumplimiento= $rowcmp['cumplimiento'];

            if (($cumplimiento == _EN_CURSO || $cumplimiento == _NO_INICIADO || $cumplimiento == _REPROGRAMADO) && empty($rowcmp['rechazado'])) {
                $id_responsable= $row['id_responsable'];
                $id_usuario= empty($this->id_usuario) ? $id_responsable : $this->id_usuario;

                $this->test_if_incumplida($row['id'], $id_usuario, $cumplimiento, $row['fecha_fin_plan'], $rowcmp);
            }
        }

        $this->debug_time('test_if_incumplida');
        $this->debug_time('create_tmp_teventos_steep_1');

        $error= $this->obj_tables->create_tmp_treg_evento_tmp($limited);
        $this->if_treg_evento= $this->obj_tables->if_treg_evento;
        
        $this->debug_time('create_tmp_teventos_steep_1');
        $this->debug_time('purge_treg_evento');

        $this->obj_tables->purge_treg_evento();
        $this->debug_time('purge_treg_evento');

        $this->error= $error;
    }

    public function list_reg() {
        $this->init_list();

        $this->if_auditoria= ($this->tipo_plan == _PLAN_TIPO_AUDITORIA || $this->tipo_plan == _PLAN_TIPO_SUPERVICION) ? true : false;

        if ($this->tipo_plan == _PLAN_TIPO_PREVENCION || $this->tipo_plan == _PLAN_TIPO_ACCION) 
            $_result= $this->automatic_status();
        if ($this->tipo_plan == _PLAN_TIPO_AUDITORIA || $this->tipo_plan == _PLAN_TIPO_SUPERVICION) 
            $_result= $this->automatic_audit_status();

        if ($_result == _EMPTY) 
            return $this->error;

        $result= $this->list_tmp_eventos(true);
        if ($this->cant == 0) 
            return _EMPTY;

        $this->total= $this->cant;
        $this->_list_reg($result);
    }

    private function add_to_tmp_ttareas($init_array= false) {
        $this->copy_in_object($this->obj_tables);
        $this->obj_tables->SetYear($this->year);
        
        if (!$this->if_ttareas) {
           $this->obj_tables->_create_tmp_ttareas();
           $this->if_ttareas= $this->obj_tables->if_ttareas;
        }   
        $obj= null;
        if ($init_array) 
            if (isset($this->array_tareas)) unset($this->array_tareas);

        if (!empty($this->id_riesgo)) {
            $obj= new Triesgo($this->clink);
            $obj->SetIdRiesgo($this->id_riesgo);
        }
        if (!empty($this->id_nota)) {
            $obj= new Tnota($this->clink);
            $obj->SetIdNota($this->id_nota);
        }

        $obj->SetYear($this->year);
        $obj->listar_tareas(null, $this->id_proceso, true);
        
        $sql= null;
        foreach ($obj->array_tareas as $array) {
            if (!array_key_exists($array['id'], (array)$this->array_tareas)) {
                $this->array_tareas[$array['id']]= $array;

                $id= $array['id'];
                $id_code= $array['id_code'];
                $id_proceso= $array['id_proceso'];
                $id_proceso_code= $array['id_proceso_code'];
                $id_usuario= $array['id_usuario'];
                $nombre= setNULL_str($array['nombre']);
                $ifgrupo= boolean2pg($array['ifgrupo']);
                $descripcion= setNULL_str($array['descripcion']);
                $fecha_inicio_plan= setNULL_str($array['fecha_inicio']);
                $fecha_fin_plan= setNULL_str($array['fecha_fin']);

                $sql.= "insert into _ttareas (id, id_code, nombre, id_user_asigna, id_proceso, id_proceso_code, ifgrupo, ";
                $sql.= "descripcion, fecha_inicio_plan, fecha_fin_plan) values ($id, '$id_code', $nombre, $id_usuario, $id_proceso, ";
                $sql.= "'$id_proceso_code', $ifgrupo, $descripcion, $fecha_inicio_plan, $fecha_fin_plan);  ";
            }
        }
        if (!empty($sql)) {
            $this->do_multi_sql_show_error('add_to_tmp_ttareas', $sql);
        }        
    }

    public function create_tmp_teventos_from_($table, $result= null, $date_cut= null) {
        $init_array= true;
        $this->copy_in_object($this->obj_tables);
        $obj= null;
        
        if (is_null($result)) {
            if ($table == "triesgos") 
                $obj= new Triesgo($this->clink);
            if ($table == "tnotas") 
                $obj= new Tnota($this->clink);

            $obj->SetYear($this->year);
            $obj->SetIdProceso($this->id_proceso);
            $result= $obj->listar();
        }

        while ($row= $this->clink->fetch_array($result)) {
            if ($table == "triesgos") {
                $this->id_riesgo= $row['_id'];
                $this->id_riesgo_code= $row['_id_code'];
            }
            if ($table == "tnotas") {
                $this->id_nota = $row['_id'];
                $this->id_nota_code = $row['_id_code'];
            }

            $this->add_to_tmp_ttareas($init_array);
            $init_array= false;
        }

        $this->if_auditoria= false;
        $this->obj_tables->if_auditoria= $this->if_auditoria;
        $cant= count($this->array_tareas);
       
        $this->create_tmp_teventos($date_cut);       
        if (empty($this->cant)) 
            return _EMPTY;

        $error= $this->obj_tables->create_tmp_treg_evento_tmp();
        $this->if_treg_evento= $this->obj_tables->if_treg_evento;
        return $error;
    }
}

/*
 * Definiciones adicionales
 */
include_once "time.class.php";
include_once "code.class.php";

if (!class_exists('Triesgo'))
    include_once "riesgo.class.php";
if (!class_exists('Tauditoria'))
    include_once "auditoria.class.php";
if (!class_exists('Tobjetivo_ci'))
    include_once "objetivo_ci.class.php";
if (!class_exists('Tevento'))
    include_once "evento.class.php";