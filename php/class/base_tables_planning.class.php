<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2020
 */

if (!class_exists('Tplanning'))
    include_once "planning.class.php";

class Tbase_tables_planning extends Tplanning {
    protected $array_rows_eventos;

    public function  __construct($clink) {
        $this->clink= $clink;
        Tplanning::__construct($clink);

        $this->control_list= 0;

        $this->toshow= null;
        $this->toshow_plan= NULL;
        $this->compute= null;

        $time= new TTime;
        $this->today= $time->GetStrTime();

        $this->print_reject= true;
        $this->go_delete= _DELETE_YES;

        $this->max_num_pages= 1;
        $this->max_row_in_page= _MAX_ROW_IN_PAGE;
        $this->init_row_temporary= 0;

        $this->if_tidx= false;
        $this->tidx= null;
        $this->create_temporary_treg_evento_table= true;
    }

    public function drop_temporary_table($table) {
        $sql= "drop table if exists ".stringSQL($table);
        $result= $this->do_sql_show_error("drop_table($table)", $sql);
        if (!$result)
            return $this->error;
    }

    protected function _create_tmp_tusuarios() {
        $error= $this->drop_temporary_table("_tusuarios");
        //
        $sql= "CREATE TEMPORARY TABLE _tusuarios ( ";
        $sql.= "id ".field2pg("INTEGER(11)").", ";
        $sql.= "nivel ".field2pg("TINYINT(2)").", ";
        $sql.= "id_proceso ".field2pg("INTEGER(11)").", ";
        $sql.= "marked ".field2pg("TINYINT(1)")." DEFAULT NULL ";
        $sql.= ") ";

        $result= $this->do_sql_show_error('create_tmp_tusuarios', $sql);
        if ($result)
            $this->if_tusuarios= true;
        if (!$result)
            return $this->error;
    }

    /**
     * se crean las tablas virtuales que se utilizan para los planes de trabajo y de auditorias
     */
    public function _create_tmp_teventos($if_small= false) {
        $plus= $if_small ? "_small" : null;
        $error= $this->drop_temporary_table("_teventos".$plus);

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_teventos".$plus)." ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " _idx ".field2pg("TINYINT(1)").", ";
        $sql.= " id_code ".field2pg("CHAR(12)").", ";
        $sql.= " numero ".field2pg("MEDIUMINT(4)").", ";
        $sql.= " id_responsable ".field2pg("INTEGER(11)").", ";
        $sql.= " id_responsable_2 ".field2pg("INTEGER(11)").", ";
        $sql.= " responsable_2_reg_date ".field2pg("DATETIME").", ";
        $sql.= " id_responsable_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " id_user_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " origen_data_asigna ".field2pg("TEXT").", ";
        $sql.= " cronos_asigna ".field2pg("DATETIME").", ";
        $sql.= " funcionario ".field2pg("VARCHAR(120)").", ";
        $sql.= " nombre ".field2pg("TEXT").", ";
        $sql.= " fecha_inicio_plan ".field2pg("DATETIME").", ";
        $sql.= " fecha_fin_plan ".field2pg("DATETIME").", ";
        $sql.= " periodicidad ".field2pg("TINYINT(2)").", ";
        $sql.= " empresarial ".field2pg("TINYINT(2)").", ";
        $sql.= " id_tipo_evento ".field2pg("INTEGER(11)").", ";
        $sql.= " toshow ".field2pg("TINYINT(4)").", ";
        $sql.= " user_check ".field2pg("TINYINT(1)").", ";
        $sql.= " descripcion ".field2pg("LONGTEXT").", ";
        $sql.= " lugar ".field2pg("MEDIUMTEXT").", ";
        $sql.= " id_evento ".field2pg("INTEGER(11)").", ";
        $sql.= " id_evento_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_tarea ".field2pg("INTEGER(11)").", ";
        $sql.= " id_tarea_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_auditoria ".field2pg("INTEGER(11)").", ";
        $sql.= " id_auditoria_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_tipo_reunion ".field2pg("INTEGER(11)").", ";
        $sql.= " id_tipo_reunion_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_tematica ".field2pg("INTEGER(11)").", ";
        $sql.= " id_tematica_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_copyfrom ".field2pg("INTEGER(11)").", ";
        $sql.= " id_copyfrom_code ".field2pg("CHAR(12)").", ";

        $sql.= " ifassure ".field2pg("TINYINT(1)").", ";
        $sql.= " id_secretary ".field2pg("INTEGER(11)").", ";

        $sql.= " id_archivo ".field2pg("INTEGER(11)").", ";
        $sql.= " id_archivo_code ".field2pg("CHAR(12)").", ";

        $sql.= " numero_plus ".field2pg("VARCHAR(12)").", ";

        $sql.= " day ".field2pg("MEDIUMINT(9)").", ";
        $sql.= " month ".field2pg("MEDIUMINT(9)").", ";
        $sql.= " year ".field2pg("MEDIUMINT(9)").", ";

        $sql.= " id_proceso ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso_code ".field2pg("CHAR(12)").", ";

        $sql.= " indice ".field2pg("INTEGER(11)").", ";
        $sql.= " indice_plus ".field2pg("INTEGER(11)").", ";
        $sql.= " tidx ".field2pg("TINYINT(1)").", ";
        $sql.= " numero_tmp ".field2pg("MEDIUMINT(4)").", ";

        $sql.= " cumplimiento ".field2pg("TINYINT(4)").", ";
        $sql.= " observacion ".field2pg("LONGTEXT").", ";
        $sql.= " aprobado ".field2pg("DATETIME").", ";
        $sql.= " rechazado ".field2pg("DATETIME").", ";
        $sql.= " cronos ".field2pg("DATETIME")." ";
        $sql.= ") ";

        $result= $this->do_sql_show_error('create_tmp_teventos', $sql);
        if ($result) {
            if($if_small)
                $this->if_teventos_small= true;
            else
                $this->if_teventos= true;            
        }

        if (!$result)
            return $this->error;        
    }

    public function _create_tmp_tauditorias() {
        $error= $this->drop_temporary_table("_tauditorias");

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_tauditorias")." ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_evento ".field2pg("INTEGER(11)").", ";
        $sql.= " id_evento_code ".field2pg("CHAR(12)").", ";
        $sql.= " id_responsable_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " id_user_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " cronos_asigna ".field2pg("DATETIME").", ";
        $sql.= " id_responsable ".field2pg("INTEGER(11)").", ";
        $sql.= " id_responsable_2 ".field2pg("INTEGER(11)").", ";
        $sql.= " responsable_2_reg_date ".field2pg("DATETIME").", ";
        $sql.= " lugar ".field2pg("TEXT")." NOT NULL, ";
        $sql.= " objetivos ".field2pg("LONGTEXT").", ";
        $sql.= " organismo ".field2pg("VARCHAR(80)").", ";
        $sql.= " origen ".field2pg("SMALLINT(6)").", ";
        $sql.= " id_tipo_auditoria ".field2pg("INTEGER(11)").", ";
        $sql.= " id_tipo_auditoria_code ".field2pg("CHAR(12)").", ";
        $sql.= " jefe_auditor ".field2pg("VARCHAR(80)").", ";
        $sql.= " fecha_inicio_plan ".field2pg("DATETIME").", ";
        $sql.= " fecha_inicio_real ".field2pg("DATETIME").", ";
        $sql.= " fecha_fin_plan ".field2pg("DATETIME").", ";
        $sql.= " fecha_fin_real ".field2pg("DATETIME").", ";
        $sql.= " empresarial ".field2pg("TINYINT(2)").", ";
        $sql.= " id_tipo_evento ".field2pg("INTEGER(11)").", ";
        $sql.= " toshow ".field2pg("TINYINT(2)").", ";
        $sql.= " user_check ".field2pg("TINYINT(1)").", ";
        $sql.= " id_auditoria ".field2pg("INTEGER(11)").", ";
        $sql.= " id_auditoria_code ".field2pg("CHAR(12)").", ";
        $sql.= " periodic ".field2pg("TINYINT(1)").", ";

        $sql.= " numero ".field2pg("VARCHAR(12)").", ";
        $sql.= " numero_plus ".field2pg("VARCHAR(12)").", ";

        $sql.= " id_proceso ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso_code ".field2pg("CHAR(12)").", ";

        $sql.= " indice ".field2pg("INTEGER(11)").", ";
        $sql.= " indice_plus ".field2pg("INTEGER(11)").", ";
        $sql.= " tidx ".field2pg("TINYINT(1)").", ";

        $sql.= " cumplimiento ".field2pg("TINYINT(4)").", ";
        $sql.= " observacion ".field2pg("LONGTEXT").", ";
        $sql.= " aprobado ".field2pg("DATETIME").", ";
        $sql.= " rechazado ".field2pg("DATETIME").", ";
        $sql.= " cronos ".field2pg("DATETIME")." ";
        $sql.= ") ";

        if (empty($this->error))
            $result= $this->do_sql_show_error('_create_tmp_tauditorias', $sql);
  
        if ($result)
            $this->if_tauditorias= true;
        if (!$result)
            return $this->error;            
    }

    public function _create_tmp_ttareas() {
        $error= $this->drop_temporary_table("_ttareas");

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_ttareas")." ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_code ".field2pg("CHAR(12)").", ";
        $sql.= " nombre ".field2pg("TEXT").", ";
        $sql.= " id_responsable ".field2pg("INTEGER(11)").", ";
        $sql.= " id_responsable_2 ".field2pg("INTEGER(11)").", ";
        $sql.= " responsable_2_reg_date ".field2pg("DATETIME").", ";
        $sql.= " id_user_asigna ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso_code ".field2pg("CHAR(12)").", ";
        $sql.= " origen_data_asigna ".field2pg("TEXT").", ";
        $sql.= " ifgrupo ".field2pg("TINYINT(1)").", ";
        $sql.= " descripcion ".field2pg("TEXT").", ";

        $sql.= " fecha_inicio_plan ".field2pg("DATETIME").", ";
        $sql.= " fecha_fin_plan ".field2pg("DATETIME").", ";

        $sql.= " id_tarea ".field2pg("INTEGER(11)").", ";
        $sql.= " id_tarea_code ".field2pg("CHAR(12)").", ";
        $sql.= " cronos ".field2pg("DATETIME").", ";

        $sql.= " day ".field2pg("MEDIUMINT(9)").", ";
        $sql.= " month ".field2pg("MEDIUMINT(9)").", ";
        $sql.= " year ".field2pg("MEDIUMINT(9)")." DEFAULT NULL ";
        $sql.= ") ";

        if (empty($this->error))
            $this->do_sql_show_error('create_tmp_ttareas', $sql);
        if (is_null($this->error))
            $this->if_ttareas= true;
    }

    public function _create_tmp_tidx() {
        $error= $this->drop_temporary_table("_tidx");

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_tidx")." ( ";
        $sql.= "id ".field2pg("INTEGER(11)").", ";
        $sql.= "id_auditoria ".field2pg("INTEGER(11)").", ";
        $sql.= "id_tarea ".field2pg("INTEGER(11)").", ";
        $sql.= "numero ".field2pg("MEDIUMINT(4)").", ";
        $sql.= "numero_plus ".field2pg("VARCHAR(12)").", ";
        $sql.= "empresarial ".field2pg("TINYINT(2)")." NOT NULL DEFAULT '0', ";
        $sql.= "id_tipo_evento ".field2pg("INTEGER(11)").", ";
        $sql.= "indice ".field2pg("INTEGER(11)").", ";
        $sql.= "indice_plus ".field2pg("INTEGER(11)").", ";
        $sql.= "cronos ".field2pg("DATETIME"). ", ";
        $sql.= "id_proceso ".field2pg("INTEGER(11)");
        $sql.= ") ";

        $this->do_sql_show_error('_create_tmp_tidx', $sql);
        if (is_null($this->error))
            $this->if_tidx= true;

        return $this->error;
    }

    public function _create_tmp_treg_evento($if_small= false) {
        $plus= $if_small ? "_small" : null;
        $error= $this->drop_temporary_table("_treg_evento".$plus);

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_treg_evento".$plus)." ( ";
        $sql.= "id ".field2pg("INTEGER(11)").", ";
        $sql.= "id_evento ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_evento_code ".field2pg("CHAR(12)").", ";
        $sql.= "id_usuario ".field2pg("INTEGER(11)").", ";
        $sql.= "id_responsable ".field2pg("INTEGER(11)").", ";
        $sql.= "origen_data ".field2pg("TEXT").", ";
        $sql.= "aprobado ".field2pg("DATETIME").", ";
        $sql.= "rechazado ".field2pg("DATETIME").", ";
        $sql.= "cumplimiento ".field2pg("SMALLINT(6)").", ";
        $sql.= "observacion ".field2pg("LONGTEXT").", ";
        $sql.= "compute ".field2pg("TINYINT(1)")." DEFAULT '1', ";
        $sql.= "toshow ".field2pg("TINYINT(1)")." DEFAULT '1', ";
        $sql.= "user_check ".field2pg("TINYINT(1)").", ";
        $sql.= "hide_synchro ".field2pg("TINYINT(1)").", ";
        $sql.= "id_tarea ".field2pg("INTEGER(11)").", ";
        $sql.= "id_tarea_code ".field2pg("CHAR(12)").", ";
        $sql.= "id_auditoria ".field2pg("INTEGER(11)").", ";
        $sql.= "id_auditoria_code ".field2pg("CHAR(12)").", ";
        $sql.= "reg_fecha ".field2pg("DATE").", ";
        $sql.= "horas ".field2pg("MEDIUMINT(9)").", ";
        $sql.= "cronos ".field2pg("DATETIME").", ";
        $sql.= "cronos_syn ".field2pg("DATETIME").", ";
        $sql.= "situs ".field2pg("CHAR(2)").", ";
        $sql.= "outlook ".field2pg("TINYINT(1)");
        $sql.= ") ";

        $this->do_sql_show_error("_create_tmp_treg_evento".$plus, $sql);
        if (is_null($this->error)) {
            if($if_small)
                $this->if_treg_evento_small= true;
            else
                $this->if_treg_evento;            
        }


        return $this->error;
    }

    protected function _create_tmp_treg_evento_small() {
        $this->_create_tmp_treg_evento(true);
    }

    protected function _create_tmp_tproceso_eventos($if_small= false) {
        $plus= $if_small ? "_small" : null;
        $error= $this->drop_temporary_table("_tproceso_eventos".$plus);

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_tproceso_eventos".$plus)." ( ";
        $sql.= "id ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_evento ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_evento_code ".field2pg("CHAR(12)").", ";
        $sql.= "toshow ".field2pg("TINYINT(2)").", ";
        $sql.= "empresarial ".field2pg("TINYINT(4)").", ";
        $sql.= "id_tipo_evento ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_tipo_evento_code ".field2pg("CHAR(12)").", ";
        $sql.= "id_tarea ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_tarea_code ".field2pg("CHAR(12)").", ";
        $sql.= "id_responsable ".field2pg("INTEGER(11)")." , ";
        $sql.= "origen_data TEXT DEFAULT NULL, ";
        $sql.= "id_auditoria ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_auditoria_code ".field2pg("CHAR(12)").", ";
        $sql.= "id_proceso ".field2pg("INTEGER(11)")." , ";
        $sql.= "id_proceso_code ".field2pg("CHAR(12)").", ";
        $sql.= "cumplimiento ".field2pg("TINYINT(1)").", ";
        $sql.= "aprobado ".field2pg("DATETIME").", ";
        $sql.= "observacion ".field2pg("LONGTEXT").", ";
        $sql.= "id_responsable_aprb ".field2pg("INTEGER(11)").", ";
        $sql.= "rechazado ".field2pg("DATETIME").", ";
        $sql.= "id_usuario ".field2pg("INTEGER(11)")." , ";
        $sql.= "indice ".field2pg("INTEGER(11)")." , ";
        $sql.= "indice_plus ".field2pg("INTEGER(11)")." , ";
        $sql.= "cronos ".field2pg("DATETIME").", ";
        $sql.= "cronos_syn ".field2pg("DATETIME").", ";
        $sql.= "situs ".field2pg("CHAR(2)");
        $sql.= ") ";

        $this->do_sql_show_error("_create_tmp_tproceso_eventos".$plus, $sql);
        if ($if_small)
            $this->if_tproceso_eventos_small= is_null($this->error) ? true : false;
        else
            $this->if_tproceso_eventos= is_null($this->error) ? true : false;
        return $this->error;
    }

    protected function _create_tmp_tproceso_eventos_small() {
        $this->_create_tmp_tproceso_eventos(true);
    }

    private function create_array_rows_eventos() {
        $sql= "select * from treg_evento_{$this->year} where id_usuario = $this->id_usuario order by cronos desc, id desc";
        $result= $this->do_sql_show_error('create_array_rows_eventos', $sql);

        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($this->array_rows_eventos[$this->id_usuario][$row['id_evento']])
                continue;
            $this->array_rows_eventos[$this->id_usuario][$row['id_evento']]= $row;
        }
    }

    public function getEvento_reg_user($id){
        if (empty($this->array_rows_eventos[$this->id_usuario]))
            $this->create_array_rows_eventos();
        return $this->array_rows_eventos[$this->id_usuario][$id];
    }
}
