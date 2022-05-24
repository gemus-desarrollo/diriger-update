<?php

/**
 * Description of base_alert
 *
 * @author mustelier
 */

defined('_TIME_ALARM') or define('_TIME_ALARM', 15);

if (!class_exists('Tbase')) 
    include_once "../../../php/class/base.class.php";

class Tbase_alert extends Tbase {
    protected $date_cutoff;
    public $minute_alarm;

    public function __construct($clink) {
        $this->clink= $clink;
        Tbase::__construct($clink);
        
        if (empty($this->minute_alarm)) 
            $this->minute_alarm= _TIME_ALARM;

        $this->cronos= date('Y-m-d H:i:s');
        $this->date_cutoff= date('Y-m-d')." 23:59:59";      
        $this->fecha_inicio_plan= add_date($this->cronos, 0, 0, 0, 0, (-1)*_TIME_ALARM);
        $this->fecha_fin_plan= add_date($this->cronos, 0, 0, 0, 0, _TIME_ALARM);
    }
    
    public function select_events() {
        $this->create_tmp_teventos_alert();
        if (empty($this->cant) || $this->cant == -1) 
            return _EMPTY;
        
        $this->create_tmp_treg_evento_user_alert();
        $this->purge_teventos();
        
        $this->clean_talerts();
        $cant= $this->create_alert();
        
        return $cant;
    }    
    
    private function _create_tmp_teventos_alert() {
        $sql= "drop table if exists ".stringSQL("_teventos_alert");
        $this->do_sql_show_error('_create_tmp_teventos_alert', $sql);
//
        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_teventos_alert")." ( ";
            $sql.= " id ".field2pg("INTEGER(11)").", ";
            $sql.= " id_responsable ".field2pg("INTEGER(11)").", ";
            $sql.= " id_user_asigna ".field2pg("INTEGER(11)").", ";
            $sql.= " origen_data_asigna ".field2pg("TEXT").", ";
            $sql.= " funcionario ".field2pg("VARCHAR(120)").", ";
            $sql.= " nombre ".field2pg("TEXT").", ";
            $sql.= " fecha_inicio_plan ".field2pg("DATETIME").", ";
            $sql.= " fecha_fin_plan ".field2pg("DATETIME").", ";
            $sql.= " user_check ".field2pg("TINYINT(1)").", ";
            $sql.= " descripcion ".field2pg("LONGTEXT").", ";
            $sql.= " lugar ".field2pg("MEDIUMTEXT").", ";
            $sql.= " id_tarea ".field2pg("INTEGER(11)").", ";
            $sql.= " id_auditoria ".field2pg("INTEGER(11)").", ";
            $sql.= " id_tipo_reunion ".field2pg("TINYINT(4)").", ";
            $sql.= " id_tematica ".field2pg("INTEGER(11)").", ";
            $sql.= " cronos ".field2pg("DATETIME").", ";
            $sql.= " ifassure ".field2pg("TINYINT(1)").", ";
            $sql.= " id_secretary ".field2pg("INTEGER(11)").", ";
            $sql.= " id_archivo ".field2pg("INTEGER(11)");
        $sql.= ") ";

        $result= $this->do_sql_show_error('_create_tmp_teventos_alert', $sql);    
    }

    private function create_tmp_teventos_alert() {
        $this->_create_tmp_teventos_alert();

        $sql= "insert into _teventos_alert ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= "select distinct id, id_responsable, id_usuario, origen_data, funcionario, nombre, fecha_inicio_plan, ";
        $sql.= "fecha_fin_plan, user_check, descripcion, lugar, id_tarea, id_auditoria, id_tipo_reunion, id_tematica, ";
        $sql.= "cronos, ifassure, id_secretary, id_archivo ";
        $sql.= "from teventos ";
        $sql.= "where ((periodicidad is null or periodicidad = 0) and (carga is null or carga = 0) and dayweek is null) ";
        $sql.= "and (fecha_inicio_plan >= '$this->fecha_inicio_plan' and fecha_fin_plan <= '".date('Y-m-d')." 23:59:59"."') ";
        $sql.= "order by fecha_inicio_plan desc";

        $result= $this->do_sql_show_error('create_tmp_teventos_alert ', $sql);
        $this->cant= $this->clink->affected_rows($result);

        return $this->error;
    }

    private function _create_tmp_treg_evento_alert() {
        $sql= "drop table if exists ".stringSQL("_treg_evento_alert");
        $result= $this->do_sql_show_error('_create_tmp_treg_evento_alert', $sql);
        if (!$result) 
            $error= $this->error;
//
        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_treg_evento_alert")." ( ";
            $sql.= "id ".field2pg("INTEGER(11)").", ";
            $sql.= "id_evento ".field2pg("INTEGER(11)")." , ";
            $sql.= "id_usuario ".field2pg("INTEGER(11)").", ";
            $sql.= "origen_data ".field2pg("TEXT").", ";
            $sql.= "cronos ".field2pg("DATETIME");
        $sql.= ") ";

        $this->do_sql_show_error('_create_tmp_treg_evento_alert', $sql);
        return $this->error;
    }  
    
    private function create_tmp_treg_evento_user_alert() {
        $error= $this->_create_tmp_treg_evento_alert();
        if (!is_null($error)) 
            return $error;
        $year= date('Y');
        $sql= "select distinct treg_evento_$year.* from treg_evento_$year, _teventos_alert ";
        $sql.= "where treg_evento_$year.id_usuario = $this->id_usuario and toshow = true ";
        $sql.= "and treg_evento_$year.id_evento = _teventos_alert.id ";
        $sql.= "order by treg_evento_$year.cronos desc, id desc";
        $result= $this->do_sql_show_error('create_tmp_treg_evento_user_alert', $sql);
        
        $array_id_evento= array();
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if (empty($row['toshow'])) 
                continue;
            if (!empty($row['rechazado'])) 
                continue;
            if (!empty($row['user_check'])) 
                continue;
            if ($row['cumplimiento'] == _COMPLETADO || $row['cumplimiento'] == _SUPERUSUARIO 
                    || $array['cumplimiento'] == _DELEGADO || $row['cumplimiento'] == _CANCELADO) 
                continue;
            if (array_key_exists($row['id_evento'], $array_id_evento)) 
                continue;
            else 
                $array_id_evento[$row['id_evento']]= $row['id_evento'];
            
            ++$j;
            ++$i;
            $sql.= "insert into _treg_evento_alert ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "select id, id_evento, id_usuario, origen_data, cronos from treg_evento_$year ";
            $sql.= "where id = '{$row['id']}'; ";
            
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('create_tmp_treg_evento_user_alert', $sql);
                $cant+= $this->clink->affected_rows($result);
                $sql= null;
                $i= 0;
            }
        }
        
        if (!is_null($sql)) 
            $this->do_multi_sql_show_error('create_tmp_treg_evento_user_alert', $sql);
        
        return $j;
    }    
    
    private function purge_teventos() {
        $sql= "delete from _teventos_alert where id not in (select id_evento from _treg_evento_alert)";
        $result= $this->do_sql_show_error('purge_teventos', $sql);
        return $this->clink->affected_rows($result);
    } 
    
    private function clean_talerts() {
        $sql= "delete from talerts where fecha_inicio_plan < '$this->fecha_inicio_plan' ";
        $this->do_sql_show_error('_create_tmp_talerts', $sql);    
    }
    
    private function create_alert() {
        $sql= "select * from  _teventos_alert";
        $result= $this->do_sql_show_error('create_alert', $sql);
        
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) 
            if ($this->add($row['id'])) 
                ++$i;
        
        return $i;
    }
    
    public function add($id) {
        if (empty($this->id_usuario)) 
            return false;
        $sql= "insert into talerts ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= "select $this->id_usuario, id, nombre, lugar, id_responsable, funcionario, fecha_inicio_plan, ";
        $sql.= "null, true, true, '$this->cronos' from teventos where id = $id";
        
        $result= $this->do_sql_show_error('add', $sql);
        if (!empty($this->error) 
            && (stripos($this->error, 'duplicate') !== false || stripos($this->error, 'duplicada') !== false)) {
            $result= true;
            $this->error= null;
        }    
        return $result;
    }
    
    public function delete($id_evento, $id_usuario= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $id_usuario= !empty($id_usuario) ? $id_usuario : $this->id_usuario;
        
        if (empty($id_evento) && empty($id_usuario)) 
            return false;
        
        $sql= "delete from talerts where ";
        if (!empty($id_evento)) 
            $sql.= "id_evento = $id_evento ";
        if (!empty($id_evento) && !empty($id_usuario)) 
            $sql.= "and ";
        if (!empty($id_usuario)) 
            $sql.= "id_usuario = $id_usuario";
        
        $result= $this->do_sql_show_error('delete', $sql);     
    }
}


/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Ttime')) 
    include_once "../../../php/class/time.class.php";