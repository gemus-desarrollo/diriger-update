<?php
/**
 * Description of register_planning_base
 *
 * @author mustelier
 */

if (!class_exists('Tbase_planning'))
    include_once "base_planning.class.php";

class Tregister_planning_base extends Tbase_planning {

    public function  __construct($clink) {
        $this->clink= $clink;
        Tbase_planning::__construct($clink);
    }

    public function get_plan($tipo_plan= null) {
        if (is_null($tipo_plan)) {
            $tipo_plan= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;
            if (empty($this->id_usuario) && empty($this->month))
                $tipo_plan= _PLAN_TIPO_ACTIVIDADES_ANUAL;
            if (empty($this->id_usuario) && !empty($this->month))
                $tipo_plan= _PLAN_TIPO_ACTIVIDADES_MENSUAL;
        }

        $sql= "select * from tplanes where year = $this->_year and tipo = $tipo_plan ";
        $sql.= !empty($this->id_usuario) ? "and id_usuario = $this->id_usuario " : "and id_usuario is null ";
        $sql.= !empty($this->_month) ? "and month = $this->_month " : "and month is null ";
        $sql.= "order by cronos desc LIMIT 1";

        $result= $this->do_sql_show_error('get_plan', $sql);
        $row= $this->clink->fetch_array($result);

        $this->_aprobado= $row['aprobado'];
        $this->_evaluado= $row['evaluado'];

        $row= $this->get_last_reg();
        $this->_aprobado_event= $row['aprobado'];
        $this->rechazado= !empty($row['rechazado']) ? $row['rechazado'] : null;
        $this->cumplimiento= $row['cumplimiento'];
    }

    /*
     * lee todos los usuarios que participan de un evento o una auditoria
     */
    public function _get_users($id, &$array_usuarios, $funct= 'evento', $array_only_users= null) {
        $funct= !empty($funct) ? $funct : 'evento';

        $sql= "select distinct id_usuario, id_tarea, id_evento, id_auditoria, id_evento_code, id_tarea_code, id_auditoria_code ";
        $sql.= "from treg_evento_{$this->year} ";
        if ($this->if_tusuarios)
            $sql.= ", _tusuarios ";
        $sql.= "where ";
        if ($funct == 'evento') {
            $id= !empty($id) ? $id : $this->id_evento;
            $sql.= "id_evento = $id ";
        }
        if ($funct == 'tarea') {
            $id= !empty($id) ? $id : $this->id_tarea;
            $sql.= "id_tarea = $id ";
        }
        if ($funct == 'auditoria') {
            $id= !empty($id) ? $id : $this->id_auditoria;
            $sql.= "id_auditoria = $id ";
        }
        if ($this->if_tusuarios)
            $sql.= "and id_usuario = _tusuarios.id ";
        $result= $this->do_sql_show_error('_get_users', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($array_usuarios[$row['id_usuario']]))
                continue;
            if (is_array($array_only_users) && !array_key_exists($row['id_usuario'], $array_only_users))
                continue;
            ++$i;
            $array= array('id'=>$row['id_usuario'], 'id_usuario'=>$row['id_usuario'], 'id_tarea'=>$row['id_tarea'],
                'id_tarea_code'=>$row['id_tarea_code'], 'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'],
                'id_auditoria'=>$row['id_auditoria'], 'id_auditoria_code'=>$row['id_auditoria_code'], );

            $array_usuarios[$row['id_usuario']]= $array;
        }

        return $i;
    }

    protected function get_array_reg_objs($year= null) {
        $year= !empty($year) ? $year : $this->year + 1;

        $sql= "select distinct id_inductor, id_inductor_code, peso from tinductor_eventos, tinductores where peso > 0 ";
        $sql.= "and tinductor_eventos.id_inductor = tinductores.id and (inicio <= $year and fin >= $year) ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        $result= $this->do_sql_show_error('get_array_reg_objs', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_inductores[]= array('id'=>$row['id_inductor'], 'id_code'=>$row['id_inductor_code'], 'peso'=>$row['peso']);
        }
    }

    protected function get_array_reg_tematicas() {
        $sql= "select distinct id, id_code, id_asistencia_resp, fecha_inicio_plan, id_tematica, id_tematica_code from ttematicas ";
        $sql.= "where (ifaccords is null or ifaccords = 0) ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        $result= $this->do_sql_show_error('get_array_reg_tematicas', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_tematicas[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_asistencia_resp'=>$row['id_asistencia_resp'],
                                            'fecha_inicio_plan'=>$row['fecha_inicio_plan'], 
                                            'id_tematica'=>$row['id_tematica'], 'id_tematica_code'=>$row['id_tematica_code']);
        }
        return $this->array_tematicas;
    }

    protected function get_array_reg_asistencias() {
        if (empty($this->id_evento))
            return null;

        $sql= "select distinct * from tasistencias where id_evento = $this->id_evento";
        $result= $this->do_sql_show_error('get_array_reg_asistencias', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_asistencias[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_usuario'=>$row['id_usuario'],
                                        'nombre'=>$row['nombre'], 'cargo'=>$row['cargo'], 'entidad'=>$row['entidad'],
                                        'invitado'=>boolean($row['invitado']));
        }
        return $this->array_asistencias;
    }

    protected function test_if_from_past($id_copyfrom) {
        if (empty($id_copyfrom))
            return false;

        $teventos= $this->if_teventos ? "_teventos" : "teventos";
        $sql= "select fecha_inicio_plan, ".month2pg("fecha_inicio_plan")." as _month, ".year2pg("fecha_inicio_plan")." as _year ";
        $sql.= "from $teventos where where id = $id_copyfrom";
        $result= $this->do_sql_show_error('test_if_from_past', $sql);
        $row= $this->clink->fetch_array($result);

        if (!empty($this->year) && $this->year < $row['_year'])
            return true;
        if (!empty($this->month) && $this->month < $row['_month'])
            return true;
        return false;
    }

    private function _test_if_extra_user($id_evento, $approved_date_plan, $array= null) {
        $sql= "select aprobado, cronos from treg_evento_{$this->year} where id_evento = $id_evento ";
        if (!empty($this->id_usuario))
            $sql.= "and id_usuario = $this->id_usuario ";
        else {
            $sql.= "and (id_usuario = {$array['id_responsable']} ";
            if (!empty($array['id_responsable_2']))
                $sql.= "or (id_usuario = {$array['id_responsable_2']} and ".date2pg("cronos")." <= '{$array['responsable_2_reg_date']}') ";
            $sql.= ") ";
        }
        $sql.= "order by aprobado desc limit 1";

        $result= $this->do_sql_show_error('test_if_extra', $sql);
        $row= $this->clink->fetch_array($result);

        return !empty($row['aprobado']) ? false : true;        
    }
    
    /*
    /* Tarea extra-plan la que se incorporo despues de aprobar el plan
    */
    protected function test_if_extra($id_evento, $approved_date_plan, $array= null) {
        if ($this->if_teventos) {
            $sql= "select * from _teventos where id = $id_evento";
            $result= $this->do_sql_show_error('test_if_extra', $sql);
            $row= $this->clink->fetch_array($result); 
            
            if (!empty($approved_date_plan)) {
                return strtotime($row['cronos_asigna']) > strtotime($approved_date_plan) ? true : false;
            }
            
            return !empty($row['aprobado']) ? false : true;
        }
        
        if (!$this->if_teventos && !empty($this->id_usuario))
            return $this->_test_if_extra_user($id_evento, $approved_date_plan, $array);
    }

    protected function test_if_incumplida($id_evento, $id_usuario, $value, $fecha_fin_plan, $row= null) {
        global $config;
        $xvalue= null;
        $xobservacion= null;
        $updated= false;

        $this->cronos= !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $cronos= odbc2time_ampm($this->cronos);

        $time= new TTime();
        $fecha_actual= $time->GetStrTime();
        $this->list_date_interval($fecha_inicio, $fecha_fin);
        $_fecha_fin_plan= add_date($fecha_fin_plan, (int)$config->breaktime);

        if (is_null($row))
            $row= $this->get_last_reg($id_evento, $id_usuario);

        if (((int)strtotime($fecha_fin_plan) <= (int)strtotime($fecha_fin)) && ($value == _NO_INICIADO || $value == _EN_CURSO || $value == _REPROGRAMADO)
            && ((int)strtotime($_fecha_fin_plan) <= (int)strtotime($fecha_actual))) {

            $xvalue= _INCUMPLIDO;
            $xobservacion= "Incumplimiento detectado por el Sistema $cronos";

            if ($value != _INCUMPLIDO) {
                $obj= new Tregister_planning($this->clink);

                $obj->SetYear($this->year);
                $obj->SetIdEvento($id_evento);
                $obj->set_id_evento_code($row['id_evento_code']);
                $obj->SetIdAuditoria($row['id_auditoria']);
                $obj->set_id_auditoria_code($row['id_auditoria_code']);
                $obj->SetIdTarea($row['id_tarea']);
                $obj->set_id_tarea_code($row['id_tarea_code']);
                $obj->SetIdArchivo($this->id_archivo);
                $obj->set_id_archivo_code($this->id_archivo_code);

                $obj->SetRechazado($row['rechazado']);
                $obj->SetAprobado($row['aprobado']);
                $obj->compute= boolean($row['_compute']);
                $obj->toshow= boolean($row['_toshow']);
                $obj->set_user_check(boolean($row['user_check']));
                $obj->SetHours($row['horas']);

                $obj->set_cronos($this->cronos);
                $obj->SetObservacion($xobservacion);
                $obj->SetIdUsuario($id_usuario);

                $obj->SetIdResponsable(_USER_SYSTEM);
                $obj->SetCumplimiento($xvalue);

                $obj->add_cump();
                $updated= true;
            }
        }
        else {
            $xvalue= $value;
        }

        $array= array('value'=>$xvalue, 'observacion'=>$xobservacion, 'updated'=>$updated);
        return $array;
    }

    protected function add_archivo_cump($id_responsable= NULL, $multi_query= false) {
        $this->error= null;
        if (empty($this->id_archivo))
            return null;

        $observacion= setNULL_str($this->observacion);
        $id_responsable= !empty($id_responsable) ? $id_responsable : $this->id_responsable;
        if (empty($id_responsable))
            $id_responsable= $_SESSION['id_usuario'];

        $sql= "insert into treg_archivo (id_archivo, id_archivo_code, cumplimiento, observacion, reg_fecha, id_usuario, ";
        $sql.= "id_responsable, cronos, situs) values ($this->id_archivo, '$this->id_archivo_code', $this->cumplimiento, $observacion,";
        $sql.= "'$this->cronos', $this->id_usuario, $id_responsable, '$this->cronos', '$this->location'); ";

        if ($multi_query)
            return $sql;
        $result= $this->do_sql_show_error('add_archivo_cump', $sql);
        return $this->error;
    }

    protected function SetAlert($action) {
        if (empty($this->periodicidad) && date('Y-m-d', strtotime($this->fecha_inicio_plan)) == date('Y-m-d')) {
            $obj_alert= new Talert($this->clink);
            $obj_alert->SetIdUsuario($this->id_usuario);

            if ($action == 'add')
                $obj_alert->add($this->id_evento);
            if ($action == 'delete')
                $obj_alert->delete($this->id_evento);
        }
    }

    protected function get_subcapitulos_evento() {
        $obj = new Ttipo_evento($this->clink);
        $this->subcapitulos_evento = null;
        $array = $obj->get_subcapitulos($this->id_tipo_evento);

        if (!is_null($array) && is_array($array))
            $this->subcapitulos_evento = $this->id_tipo_evento . ',' . implode(",", $array);

        return $this->subcapitulos_evento;
    }

    public function get_array_reg_usuarios($year= null) {
        $year= !empty($year) ? $year : $this->year;
  
        $sql= "select distinct id_usuario, id_evento, id_auditoria, id_tarea, user_check from treg_evento_$year where 1 ";
        if (!empty($this->id_evento)) 
            $sql.= "and id_evento = $this->id_evento ";
        if (!empty($this->id_tarea)) 
            $sql.= "and id_tarea = $this->id_tarea ";
        if (!empty($this->id_auditoria)) 
            $sql.= "and id_auditoria = $this->id_auditoria ";
        $sql.= "order by cronos desc";
        $result= $this->do_sql_show_error('get_array_reg_usuarios', $sql);

        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['id_usuario']])
                continue;
            $array_ids[$row['id_usuario']]= 1;
            $this->array_reg_usuarios[$row['id_usuario']]= array('id'=>$row['id_usuario'], 'user_check'=>boolean($row['user_check']),
                                                            'aprobado'=>$row['aprobado'], 'rechazado'=>$row['rechazado'], 
                                                            'cumplimiento'=>$row['cumplimiento'], 'id_usuario'=>$row['id_usuario']);
        }
        
        return $this->array_reg_usuarios;
    }
    
    
    /*
     * fijar todos los procesos inferiores aun id proceso determinado
     */
    public function set_procesos($id_proceso= null) {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso(!empty($id_proceso) ? $id_proceso : $_SESSION['id_entity']);
        $obj_prs->Set();
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdResponsable(null);
        $obj_prs->SetIdUsuario(null);
        $obj_prs->set_use_copy_tprocesos(false);

        $obj_prs->listar_in_order("eq_desc", true);
        $this->array_procesos= $obj_prs->array_procesos;

        $time= new TTime();
        $month= !empty($this->month) ? $this->month : 12;

        if (empty($this->month)) 
            $this->month= $this->year < $time->GetYear() ? 12 : $time->GetMonth();
        if (empty($this->day)) {
            if ($this->year < $time->GetYear() || ($this->year == (int)$time->GetYear() && $this->month < (int)$time->GetMonth())) {
                $time->SetYear($this->year);
                $time->SetMonth($this->month);
                $this->day= $time->longmonth();
            }
            else {
                $this->day= $time->GetDay();
        }   }         
    }
    
    /*
    * cumplimiento y aprobado para los procesos
    */
    public function get_array_reg_procesos($year= null, $id_proceso=null, $tipo= null) {
        $year= !empty($year) ? $year : $this->year;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];

        if (isset($this->array_reg_procesos)) unset($this->array_procesos);
        $this->array_reg_procesos= array();
        
        $id_proceso_code= null;
        $toshow= $this->empresarial;
        
        if (empty($this->array_procesos) && !empty($id_proceso)) {
            $this->set_procesos($id_proceso);
        }
        
        $sql= "select distinct tproceso_eventos_$year.*, tproceso_eventos_$year.id_proceso as _id_proceso, ";
        $sql.= "tproceso_eventos_$year.id_proceso_code as _id_proceso_code, id_usuario, tipo, conectado ";
        $sql.= "from tproceso_eventos_$year, tprocesos where tproceso_eventos_$year.id_proceso = tprocesos.id ";
        if (!empty($this->id_evento)) {
            $sql.= "and id_evento = $this->id_evento ";
        }    
        if (!empty($this->id_tarea)) {
            $sql.= "and id_tarea = $this->id_tarea ";
        }    
        if (!empty($this->id_auditoria) && $this->if_auditoria) {
            $sql.= "and id_auditoria = $this->id_auditoria ";
        }    
        $sql.= "order by tproceso_eventos_$year.cronos desc";

        $result= $this->do_sql_show_error('get_array_reg_procesos', $sql);

        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id_proceso']])
                continue;
            $array_ids[$row['_id_proceso']]= $row['_id_proceso'];

            if ((!array_key_exists($row['_id_proceso'], (array)$this->array_procesos) && (int)$id_proceso != (int)$row['_id_proceso']))
                continue;
            if (($row['tipo'] < $tipo && $row['conectado'] != 1) && $id_proceso != $_SESSION['local_proceso_id'])
                continue;

            $array= array('id'=>$row['_id_proceso'], 'id_code'=>$row['_id_proceso_code'], 'if_entity'=>$row['if_entity'],
                          'id_responsable'=>$row['id_responsable'], 'toshow'=>$row['toshow'], 'empresarial'=>$row['empresarial'],
                          'id_tipo_evento'=>$row['id_tipo_evento'], 'id_tipo_evento_code'=>$row['id_tipo_evento_code'],
                          'indice'=>$row['indice'], 'indice_plus'=>$row['indice_plus'], 'id_usuario'=>$row['id_usuario'],
                          'cumplimiento'=>$row['cumplimiento'], 'aprobado'=>$row['aprobado'], 'rechazado'=>$row['rechazado'], 
                          'id_proceso'=>$row['_id_proceso'], 'id_proceso_code'=>$row['_id_proceso_code'],
                          'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 'id_auditoria'=>$row['id_auditoria'],   
                          'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code']);

            $this->array_reg_procesos[$row['_id_proceso']]= $array;
        }
        return $this->array_reg_procesos;
    }

    public function get_reg_proceso($id_evento= null, $id_auditoria= null, $id_tarea= null, $id_proceso= null) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $id_auditoria= !empty($id_auditoria) ? $id_auditoria : $this->id_auditoria;
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;

        $tproceso_eventos= $this->if_tproceso_eventos ? "_tproceso_eventos" : "tproceso_eventos_{$this->year}";

        $sql= "select * from $tproceso_eventos where id_proceso = $id_proceso ";
        if (!empty($id_evento))
            $sql.= "and id_evento = $id_evento ";
        if (!empty($id_auditoria))
            $sql.= "and id_auditoria = $id_auditoria ";
        if (!empty($id_tarea))
            $sql.= "and id_tarea = $id_tarea ";
        if (!empty($this->reg_fecha))
            $sql.= "and ".date2pg("$tproceso_eventos.cronos")." <= '$this->reg_fecha' ";
        if ($this->toshow != _EVENTO_ANUAL || ($this->toshow == _EVENTO_ANUAL && (!$this->if_tproceso_eventos || $this->if_auditoria)))
            $sql.= "order by cronos desc limit 1";

        $result= $this->do_sql_show_error('get_reg_proceso', $sql);
        $row= $this->clink->fetch_array($result);
        return $row;
    }

    public function add_cump_to_procesos($array_procesos) {
        foreach ($array_procesos as $prs) {
            $row_cump= $this->get_reg_proceso(null, null, null, $prs['id']);
            if (!empty($this->aprobado))
                $row_cump['aprobado']= $this->aprobado;
            if (!empty($this->rechazado))
                $row_cump['rechazado']= $this->rechazado;
            $row_cump['observacion']= $this->observacion;

            if ($this->toshow == -1)
                $row_cump['toshow']= null;

            $this->add_cump_proceso($prs['id'], $prs['id_code'], $row_cump);
        }
    }

    public function add_cump_proceso($id_proceso= null, $id_proceso_code= null, $row_cump= null, $multi_query= false, 
                                                                                                $use_tmp_table= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $use_tmp_table= !is_null($use_tmp_table) ? $use_tmp_table : false;

        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_proceso_code= !empty($id_proceso_code) ? $id_proceso_code : $this->id_proceso_code;

        $id_evento= setNULL(!empty($row_cump) ? $row_cump['id_evento'] : $this->id_evento);
        $id_evento_code= setNULL_str(!empty($row_cump) ? $row_cump['id_evento_code'] : $this->id_evento_code);

        $id_tarea= setNULL(!empty($row_cump) ? $row_cump['id_tarea'] : $this->id_tarea);
        $id_tarea_code= setNULL_str(!empty($row_cump) ? $row_cump['id_tarea_code'] : $this->id_tarea_code);

        $id_auditoria= setNULL(!empty($row_cump) ? $row_cump['id_auditoria'] : $this->id_auditoria);
        $id_auditoria_code= setNULL_str(!empty($row_cump) ? $row_cump['id_auditoria_code'] : $this->id_auditoria_code);

        $toshow= setNULL(!empty($row_cump) ? $row_cump['toshow'] : $this->toshow);

        $rechazado= setNULL_str(!empty($row_cump) ? $row_cump['rechazado'] : $this->rechazado);
        $aprobado= setNULL_str(!empty($row_cump) ? $row_cump['aprobado'] : $this->aprobado);
        $id_responsable_aprb= setNULL(!empty($row_cump) ? $row_cump['id_responsable_aprb'] : $this->id_responsable_aprb);

        $id_responsable= setNULL(!empty($row_cump) ? $row_cump['id_responsable'] : $this->id_responsable);
        $empresarial= setNULL(!empty($row_cump) ? $row_cump['empresarial'] : $this->empresarial);
        $id_tipo_evento= setNULL(!empty($row_cump) ? $row_cump['id_tipo_evento'] : $this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str(!empty($row_cump) ? $row_cump['id_tipo_evento_code'] : $this->id_tipo_evento_code);
        $indice= setNULL(!empty($row_cump) ? $row_cump['indice'] : $this->indice);
        $indice_plus= setNULL(!empty($row_cump) ? $row_cump['indice_plus'] : $this->indice_plus);
        
        $observacion= setNULL_str($this->observacion);

        $table= $use_tmp_table && $this->if_tproceso_eventos ? "_tproceso_eventos" : "tproceso_eventos_{$this->year}";

        $sql= "insert into $table (id_evento, id_evento_code, id_auditoria, id_auditoria_code, ";
        $sql.= "id_tarea, id_tarea_code, id_proceso, id_proceso_code, toshow, cumplimiento, aprobado, observacion, ";
        $sql.= "id_responsable, empresarial, id_tipo_evento, id_tipo_evento_code, indice, indice_plus, id_responsable_aprb, ";
        $sql.= "rechazado, id_usuario, cronos, situs) values ($id_evento, $id_evento_code, $id_auditoria, $id_auditoria_code, ";
        $sql.= "$id_tarea, $id_tarea_code, $id_proceso, '$id_proceso_code', $toshow, $this->cumplimiento, $aprobado, ";
        $sql.= "$observacion, $id_responsable, $empresarial, $id_tipo_evento, $id_tipo_evento_code, $indice, $indice_plus, ";
        $sql.= "$id_responsable_aprb, $rechazado, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location'); ";

        if (!$multi_query)
            $this->do_sql_show_error('add_cump_proceso', $sql);
        else
            return $sql;
    }
}

/*
 * Clases adjuntas o necesarias
 */

include_once "code.class.php";
if (!class_exists('Tbase_alert'))
    include_once "../../tools/alert/php/base_alert.class.php";
if (!class_exists('Talert'))
    include_once "../../tools/alert/php/alert.class.php";