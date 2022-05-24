<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

include_once "../../common/file.class.php";
include_once "base.class.php";
include_once "baseBond.class.php";
include_once "../../../php/class/time.class.php";
include_once "../../../php/class/code.class.php";

include_once "deleteImport.class.php";

class Tbond extends TbaseBond {
    public $table;
    protected $array_dbstruct;
    protected $array_insert, $array_no_inserted;
    protected $array_new_id;
    public  $array_size_tables;

    public function __construct($dblink) {
        TbaseBond::__construct($dblink);
        $this->dblink= $dblink;
    }

    private function update_id_politica_code($table) {
        if ($this->if_mysql) 
            $sql= "update $table, tpoliticas set $table.id_politica_code= tpoliticas.id_code ";
        else 
            $sql= "update $table set id_politica_code= tpoliticas.id_code from tpoliticas ";
        $sql.= "where $table.id_politica = tpoliticas.id and if_inner is null";

        $this->db_sql_show_error("_update_id_politica_code($table)", $sql, false);
    }
    /* USUARIOS, PROCESOS Y ESCENARIOS *******************************************************************************************/
    public function _tprocesos() {
        $this->update_t_key("tprocesos", "id_proceso");
    }
    public function _tusuarios() {
        // inicio
        $this->table= "tusuarios";
        $array_id= array();
        if (isset($this->array_new_id)) {
            unset($this->array_new_id); 
            $this->array_new_id= array();
        }

        $sql= "select _clave from tusuarios where id = 1";
        $result= $this->db_sql_show_error('_tusuario', $sql);
        $row= $this->dblink->fetch_array($result);
        $_clave= $row['_clave'];

        $result= $this->refresh_t_table_from_db('id', 'noIdentidad');
        $this->insert_idx('noIdentidad');

        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            if (empty($row['noIdentidad']) || !strlen($row['noIdentidad'])) 
                continue;

            if (empty($row['id'])) {
                $id= $this->_insert_in_db('noIdentidad', $row['noIdentidad']);
                if (!is_null($id)) {
                    $this->update_idx($id, $row['noIdentidad']);
                    $this->array_new_id[]= array('id'=>$id, 'id_code'=>$row['noIdentidad'], 'id_proceso'=>$row['id_proceso'], 
                                                'id_proceso_code'=>$row['id_proceso_code']);
                }
            } else 
                $array_id[]= array('id'=>$row['id'], 'id_code'=>$row['noIdentidad']);

            function_progressCSS(++$i, "Cargando tabla temporal de usuarios", $this->array_size_tables[$this->table], 2);
        }
        
        $sql= null;
        foreach ($this->array_new_id as $user) 
            $sql.= "update _tusuarios set id = {$user['id']} where noIdentidad = '{$user['id_code']}'; ";
        if ($sql) 
            $this->db_multi_sql_show_error("_tusuarios", $sql);

        foreach ($array_id as $array) {
            $this->_update_in_db('id', $array['id'], false, 'noIdentidad', $array['id_code'], true);
            $this->update_idx($array['id'], $array['id_code']);
        }
        
        reset($this->array_new_id);
        
        $this->refresh_from_idx('tprocesos', 'id_responsable', '_tusuarios');
 
        $this->refresh_t_db_id('id_usuario');
        $this->refresh_t_db_id('id_responsable');
        $this->refresh_t_db_id('id_usuario_real');
        $this->refresh_t_db_id('id_usuario_plan');
        $this->refresh_t_db_id('id_responsable_2');
        $this->refresh_t_db_id('id_responsable_eval');
        $this->refresh_t_db_id('id_responsable_auto_eval');
        $this->refresh_t_db_id('id_responsable_aprb');

        $sql= "update tusuarios set _clave= '$_clave' where _clave is null";
        $this->db_sql_show_error('_tusuarios', $sql);

        $this->_tusuario_procesos();
        $this->_update_in_db_tusuarios();
    }
    private function _tusuario_procesos() {
        reset($this->array_new_id);
        foreach ($this->array_new_id as $array) {
            $id= $array['id'];
            $sql= "insert into tusuario_procesos (id_usuario, id_proceso, id_proceso_code, cronos, situs) ";
            $sql.= "values ($id, $this->id_origen, '$this->id_origen_code', '$this->cronos', '$this->situs'); ";
            $this->db_sql_show_error('_tusuario_procesos', $sql);
        }
    }
    public function _tescenarios() {
        $this->update_t_key("tescenarios", "id_escenario");
        // $this->refresh_t_db_id('id_escenario');
    }
    public function _tauditorias() {       
        $this->update_t_key("tauditorias", "id_auditoria");        
        // $this->refresh_t_db_id('id_auditoria');
    }
    public function _tnotas() {
        $this->update_t_key("tnotas", "id_nota");
        // $this->refresh_t_db_id('id_nota');
        $this->_tproceso_riesgos("tnotas");
    }
    public function _triesgos() {
        $this->update_t_key("triesgos", "id_riesgo");
        // $this->refresh_t_db_id('id_riesgo');
        $this->_tproceso_riesgos("triesgos");
    }
    public function _tproyectos() {
        $this->update_t_key("tproyectos", "id_proyecto");
        // $this->refresh_t_db_id('id_proyecto');
    }
    
    private function _get_year_riesgo($table, $id) {
        $sql= "select year(fecha_fin_plan) as _year, cronos from $table where id = $id";
        $result= $this->db_sql_show_error("_get_year_riesgo($table, $id)", $sql);
        $row= $this->dblink->fetch_array($result);
        return array($row['_year'], $row['cronos']);
    }
    private function _tproceso_riesgos($table) {
        if (strcmp("$table", "triesgos") == 0) {
            $field= "id_riesgo"; 
            $field_code= "id_riesgo_code";
        }
        if (strcmp("$table", "tnotas") == 0) {
            $field= "id_nota"; 
            $field_code= "id_nota_code";
        }
        
        reset($this->array_new_id);
        foreach ($this->array_new_id as $array) {
            $id= $array['id'];
            $id_code= $array['id_code'];
            $row= $this->_get_year_riesgo($table, $id);
            $year= setNULL_empty($row[0]);
            $cronos= setNULL_str($row[1]);
            $id_proceso= $this->if_origen_up ? $this->id_NO_LOCAL_proceso : $array['id_proceso'];
            $id_proceso_code= $this->if_origen_up ? $this->id_NO_LOCAL_proceso_code : $array['id_proceso_code'];

            $sql= "insert into tproceso_riesgos ($field, $field_code, id_proceso, id_proceso_code, year, cronos, cronos_syn, situs) ";
            $sql.= "values ($id, '$id_code', $id_proceso, '$id_proceso_code', $year, '$this->cronos', $cronos, '$this->situs')";
            $this->db_sql_show_error("_tproceso_riesgos($table)", $sql);
        }
    }
    public function _ttareas() {
        $this->update_t_key("ttareas", "id_tarea");
        $this->refresh_t_db_id('id_target');
        $this->refresh_t_db_id('id_source');
        sleep(30);
    }
    public function _ttipo_eventos() {
        $this->update_t_key("ttipo_eventos", "id_tipo_evento");
        // $this->refresh_t_db_id('id_tipo_evento');
        $this->refresh_t_db_id('id_subcapitulo');
    }
    public function _ttipo_auditorias() {
        $this->update_t_key("ttipo_auditorias", "id_tipo_auditoria");
    }    
    public function _ttipo_reuniones() {
        $this->update_t_key("ttipo_reuniones", "id_tipo_reunion");
    }
    public function _teventos() {
        $this->update_t_key("teventos", "id_evento");
        // $this->refresh_t_db_id('id_evento');
        $this->refresh_t_db_id('id_copyfrom');
        sleep(30);
    }
    public function _ttematicas() {
        $this->update_t_key("ttematicas", "id_tematica");
        // $this->refresh_t_db_id('id_tematica');
    }
    public function _tasistencias() {
        $this->update_t_key("tasistencias", "id_asistencia");
        $this->refresh_t_db_id('id_asistencia_resp');
    }    
    public function _tdebates() {
        $this->update_t_key("tdebates", "id_debate");
    }
    public function _treg_evento() {
        $this->table= "treg_evento";
        $this->remove_idx();
        $this->update_t_from_db("id_evento", "teventos", 'id_evento_code');
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_auditoria", "tauditorias", 'id_auditoria_code');       
        $this->_insert_flush("teventos", 'id_evento_code', "ttareas", 'id_tarea_code', "tauditorias", 'id_auditoria_code');
    }
    public function _tusuario_eventos() {
        $this->table= "tusuario_eventos";
        $this->remove_idx();
        $this->update_t_from_db("id_evento", "teventos", 'id_evento_code');
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_auditoria", "tauditorias", 'id_auditoria_code');
        $this->update_t_from_db("id_tematica", 'ttematicas', 'id_tematica_code');
        $this->_insert_flush("teventos", 'id_evento_code', "ttareas", 'id_tarea_code', "tauditorias", 'id_auditoria_code', 'ttematicas', 'id_tematica');
    }
    public function _tproceso_eventos() {
        $this->table= "tproceso_eventos";
        $this->remove_idx();
        $this->update_t_from_db("id_evento", "teventos", 'id_evento_code');
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_auditoria", "tauditorias", 'id_auditoria_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("teventos", 'id_evento_code', "tauditorias", 'id_auditoria_code', "ttareas", 'id_tarea_code');
    }
    public function _tusuario_proyectos() {
        $this->table= "tusuario_proyectos";
        $this->remove_idx();
        $this->update_t_from_db("id_proyecto", 'tproyectos', 'id_proyecto_code');
        $this->_insert_flush('tproyectos', 'id_proyecto_code');
    }
    public function _ttarea_tarea() {
        $this->table= "ttarea_tarea";
        $this->remove_idx();
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_tarea", "ttareas", 'id_depend_code');
        $this->_insert_flush("ttareas", 'id_depend_code');
    }
    public function _tplanes() {
        $this->update_t_key("tplanes", "id_plan");
        // $this->refresh_t_db_id('id_plan');
    }
    public function _treg_plantrab() {
        $this->table= "treg_plantrab";
        $this->remove_idx();
        $this->update_t_from_db("id_plan", 'tplanes', 'id_plan_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("tplanes", "id_plan_code");
    }
    public function _tpoliticas() {
        $this->update_t_key("tpoliticas", "id_politica");
        // $this->refresh_t_db_id('id_politica');
    }
    public function _treg_politica() {
        $this->table= "treg_politica";
        $this->update_id_politica_code('_treg_politica');
        $this->remove_idx();
        $this->update_t_from_db("id_politica", "tpoliticas", 'id_politica_code');
        $this->_insert_flush("tpoliticas", "id_politica_code");
    }
    public function _tprogramas() {
        $this->update_t_key("tprogramas", "id_programa");
        // $this->refresh_t_db_id('id_programa');
    }
    public function _treg_programa() {
        $this->table= "treg_programa";
        $this->remove_idx();
        $this->update_t_from_db("id_programa", 'tprogramas', 'id_programa_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("tprogramas", "id_programa_code");
    }
    public function _tref_programas() {
        $this->table= "tref_programas";
        $this->remove_idx();
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_indicador", 'tindicadores', 'id_indicador_code');
        $this->update_t_from_db("id_programa", 'tprogramas', 'id_programa_code');
        $this->_insert_flush("tprogramas", "id_programa_code");
    }
    public function _tobjetivos() {
        $this->update_t_key("tobjetivos", "id_objetivo");
        // $this->refresh_t_db_id('id_objetivo');
        $this->refresh_t_db_id('id_objetivo_sup');
    }
    public function _treg_objetivo() {
        $this->table= "treg_objetivo";
        $this->remove_idx();
        $this->update_t_from_db("id_objetivo", 'tobjetivos', 'id_objetivo_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');        
        $this->_insert_flush("tobjetivos", "id_objetivo_code");
    }
    public function _tobjetivo_tareas() {
        $this->table= "tobjetivo_tareas";
        $this->remove_idx();
        $this->update_t_from_db("id_objetivo", 'tobjetivos', 'id_objetivo_code');
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->_insert_flush("tobjetivos", "id_objetivo_code", "ttareas", "id_tarea_code");
    }
    public function _tpolitica_objetivos() {
        $this->table= "tpolitica_objetivos";
        $this->update_id_politica_code('_tpolitica_objetivos');
        $this->remove_idx();
        $this->update_t_from_db("id_objetivo", 'tobjetivos', 'id_objetivo_code');
        $this->update_t_from_db("id_politica", "tpoliticas", 'id_politica_code');
        $this->_insert_flush("tpoliticas", "id_politica_code", "tobjetivos", "id_objetivo_code");
    }
    public function _tperspectivas() {
        $this->update_t_key("tperspectivas", "id_perspectiva");
        // $this->refresh_t_db_id('id_perspectiva');
    }
    public function _treg_perspectiva() {
        $this->table= "treg_perspectiva";
        $this->remove_idx();
        $this->update_t_from_db("id_perspectiva", 'tperspectivas', 'id_perspectiva_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("tperspectivas", "id_perspectiva_code");
    }
    public function _tinductores() {
        $this->update_t_key("tinductores", "id_inductor");
        // $this->refresh_t_db_id('id_inductor');
    }
    public function _treg_inductor() {
        $this->table= "treg_inductor";
        $this->remove_idx();
        $this->update_t_from_db("id_inductor", 'tinductores', 'id_inductor_code');
        $this->_insert_flush("tinductores", "id_inductor_code");
    }
    public function _tobjetivo_inductores() {
        $this->table= "tobjetivo_inductores";
        $this->remove_idx();
        $this->update_t_from_db("id_objetivo", 'tobjetivos', 'id_objetivo_code');
        $this->update_t_from_db("id_inductor", 'tinductores', 'id_inductor_code');
        $this->_insert_flush("tinductores", "id_inductor_code", "tobjetivos", "id_objetivo_code");
    }
    public function _tinductor_eventos() {
        $this->table= "tinductor_eventos";
        $this->remove_idx();
        $this->update_t_from_db("id_inductor", 'tinductores', 'id_inductor_code');
        $this->update_t_from_db("id_evento", "teventos", 'id_evento_code');
        $this->_insert_flush("tinductores", "id_inductor_code", "teventos", "id_evento_code");
    }
    public function _tunidades() {
        $this->table= "tunidades";
        $this->update_t_key("tunidades", "id_unidad");
    }
    public function _tindicadores() {
        $this->update_t_key("tindicadores", "id_indicador");
        // $this->refresh_t_db_id('id_indicador');
        $this->_tproceso_indicadores();
    }
    private function _tproceso_indicadores() {
        $year= date('Y');
        reset($this->array_new_id);

        foreach ($this->array_new_id as $array) {
            $id= $array['id'];
            $id_code= $array['id_code'];

            $sql= "insert into tproceso_indicadores (id_indicador, id_indicador_code, id_proceso, id_proceso_code, ";
            $sql.= "year, cronos, cronos_syn, situs) values ($id, '$id_code', ";
            $sql.= "$this->id_origen, '$this->id_origen_code', ";
            $sql.= "$year, '$this->cronos', '$this->cronos', '$this->location') ";

            $this->db_sql_show_error('_tproceso_indicadores', $sql);
        }
    }
    public function _tref_indicadores() {
        $this->table= "tref_indicadores";
        $this->remove_idx();
        $this->update_t_from_db("id_indicador", 'tindicadores', 'id_indicador_code');
        $this->update_t_from_db("id_inductor", 'tinductores', 'id_inductor_code');
        $this->_insert_flush("tindicadores", "id_indicador_code");
    }
    public function _tindicador_criterio() {
        $this->table= "tindicador_criterio";
        $this->remove_idx();
        $this->update_t_from_db("id_indicador", 'tindicadores', 'id_indicador_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("tindicadores", "id_indicador_code");
    }
    public function _treg_real() {
        $this->table= "treg_real";
        $this->remove_idx();
        $this->update_t_from_db("id_indicador", 'tindicadores', 'id_indicador_code');
        $this->_insert_flush("tindicadores", "id_indicador_code");
    }
    public function _treg_plan() {
        $this->table= "treg_plan";
        $this->remove_idx();
        $this->update_t_from_db("id_indicador", 'tindicadores', 'id_indicador_code');
        $this->_insert_flush("tindicadores", "id_indicador_code");
    }
    public function _tregistro() {
        $this->table= "tregistro";
        $this->remove_idx();
        $this->update_t_from_db("id_indicador", 'tindicadores', 'id_indicador_code');
        $this->_update_tregistro_in_db();
    }
    
    private function _update_tregistro_in_db() {
        $sql= "select * from _tregistro";
        $result= $this->db_sql_show_error('update_real_table', $sql);
        $size= $this->dblink->num_rows($result);

        while ($row= $this->dblink->fetch_array($result)) {
            $sql= "select * from tregistro where id_indicador_code = '{$row['id_indicador_code']}' ";
            $sql.= "and year = {$row['year']} and month = {$row['month']} ";
            $_result= $this->db_sql_show_error('_insert_tregistro', $sql);
            $cant= $this->dblink->num_rows($_result);
            
            $_row= $this->dblink->fetch_array($_result); 
            
            if ($cant) 
                $sqli= $this->_update_tregistro ($_row['id'], $row);
            else
                $sqli= $this->_insert_tregistro ($row);
            
            $this->db_sql_show_error('update_real_table', $sqli);
        }          
    }
    
    private function _typeof($cell, $row) {
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;
        
        $_field= $cell['name'];
        $type= $cell['type'];
        $length= $cell['length'];
        $value= $row[$_field];

        $type= strtoupper($type);

        if (($this->origen_motor_db == "mysql" && !$this->if_mysql) && (array_search($type, $SQL_booltypes) !== false && $length == 1))
            $value= boolean2pg(boolean($value));
        if (($this->origen_motor_db == "postgres" && $this->if_mysql) && (array_search($type, $SQL_booltypes) !== false)) 
            $value= boolean($value);
        if (array_search($type, $SQL_texttypes) !== false || array_search($type, $SQL_timetypes) !== false) {
            $addslash= array_search($type, $SQL_texttypes) !== false ? true : false;
            $value= setNULL_str($value, $addslash);
        }
        elseif (array_search($type, $SQL_numtypes) !== false) {
            if (!is_null($value) && $value == 0) 
                $value= setZero($value);
            else 
                $value= setNULL($value);

        } elseif (array_search($type, $SQL_blobtypes) !== false) {
            if (is_null($value)) 
                $value= 'NULL';
            else {
                if ($this->if_mysql) {
                    $value= addslashes($value);
                    $value= "'$value'";
                }
                else 
                    $value= "'$value'";
            }
        } else 
            $value= setNULL($value);  
        
        return $value;
    }

    private function _insert_tregistro($row) {     
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;
        $count= count($this->array_dbstruct);
        
        $i= 0;
        $sql= "insert into tregistro (";
        reset($this->array_dbstruct);
        foreach ($this->array_dbstruct as $cell) {
            $field= $cell['name'];

            if ($field == 'id') {
                ++$i;
                continue;
            }    
            if ($i > 1 && $i < $count) 
                $sql.= ", ";

            $_field= stringSQL($field);
            $sql.= "$_field";
            ++$i;
        }

        $sql.= ") values ( ";  
        
        $j= 0;
        $i= 0;
        reset($this->array_dbstruct);
        foreach ($this->array_dbstruct as $cell) {
            if ($i == 0) {
                ++$i;
                continue;
            }    
            
            $value= $this->_typeof($cell, $row);

            if ($i > 1 && $i < $count) 
                $sql.= ", ";
            $sql.= "$value";
            ++$i; 
            ++$j;
        }
        $sql.= ") ";
        
        return $sql;
    }
    
    private function _update_tregistro($id, $row) {
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;
        $count= count($this->array_dbstruct);
        
        $i= 0;
        $sql= "update tregistro set ";
        reset($this->array_dbstruct);
        foreach ($this->array_dbstruct as $cell) {
            $field= $cell['name'];

            if ($field == 'id') {
                ++$i;
                continue;
            }    
            if ($i > 1 && $i < $count) 
                $sql.= ", ";

            $_field= stringSQL($field);
            $value= $this->_typeof($cell, $row); 
            $sql.= "$_field= $value";
            ++$i;
        }
        $sql.= " where id = $id; ";
        
        return $sql;
    }
    
    public function _treg_riesgo() {
        $this->table= "treg_riesgo";
        $this->remove_idx();
        $this->update_t_from_db("id_riesgo", 'triesgos', 'id_riesgo_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("triesgos", "id_riesgo_code");
    }
    public function _triesgo_tareas() {
        $this->table= "triesgo_tareas";
        $this->remove_idx();
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_riesgo", 'triesgos', 'id_riesgo_code');
        $this->update_t_from_db("id_nota", 'tnotas', 'id_nota_code');
        $this->update_t_from_db("id_politica", "tpoliticas", 'id_politica_code');
        $this->_insert_flush("ttareas", "id_tarea_code", "triesgos", "id_riesgo_code", "tnotas", "id_nota_code");
    }
    public function _tinductor_riesgos() {
        $this->table= "tinductor_riesgos";
        $this->remove_idx();
        $this->update_t_from_db("id_riesgo", 'triesgos', 'id_riesgo_code');
        $this->update_t_from_db("id_inductor", 'tinductores', 'id_inductor_code');
        $this->_insert_flush("tinductores", "id_inductor_code", "triesgos", "id_riesgo_code");
    }
    public function _tproceso_objetivos() {
        $this->table= "tproceso_objetivos";
        $this->remove_idx();
        $this->update_t_from_db("id_objetivo", 'tobjetivos', 'id_objetivo_code');
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("tobjetivos", "id_objetivo_code");
    }
    public function _tproceso_proyectos() {
        $this->table= "tproceso_proyectos";
        $this->remove_idx();
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->update_t_from_db("id_programa", 'tprogramas', 'id_programa_code');
        $this->update_t_from_db("id_proyecto", 'tproyectos', 'id_proyecto_code');
        $this->_insert_flush("tprocesos", "id_proceso_code", "tprogramas", "id_programa_code", "tproyectos", "id_proyecto_code");
    }
    public function _treg_proceso() {
        $this->table= "treg_proceso";
        $this->remove_idx();
        $this->update_t_from_db("id_proceso", 'tprocesos', 'id_proceso_code');
        $this->_insert_flush("tprocesos", "id_proceso_code");
    }
    public function _tnota_causas() {
        $this->table= "tnota_causas";
        $this->remove_idx();
        $this->update_t_from_db("id_nota", 'tnotas', 'id_nota_code');
        $this->_insert_flush("tnotas", "id_nota_code");
    }
    public function _tdocumentos() {
        $this->update_t_key("tdocumentos", "id_documento");
        // $this->refresh_t_db_id('id_documento');
    }
    public function _tref_documentos() {
        $this->table= "tref_documentos";
        $this->remove_idx();
        $this->update_t_from_db("id_evento", "teventos", 'id_evento_code');
        $this->update_t_from_db("id_auditoria", "tauditorias", 'id_auditoria_code');
        $this->update_t_from_db("id_proyecto", 'tproyectos', 'id_proyecto_code');
        $this->update_t_from_db("id_nota", 'tnotas', 'id_nota_code');
        $this->_insert_flush("tdocumentos", "id_documento_code", "teventos", "id_evento_code", "tauditorias", "id_auditoria_code", "tproyectos", "id_proyecto_code", "tnotas", "id_nota_code");
    }
    
    public function _treg_tarea() {
        $this->table= "treg_tarea";
        $this->remove_idx();
        $this->update_t_from_db("id_tarea", "ttareas", 'id_tarea_code');
        $this->update_t_from_db("id_kanban_column", 'tkanban_columns', 'id_kanban_column_code');
        $this->_insert_flush("ttareas", 'id_tarea_code');
    }
    public function _tkanban_columns() {
        $this->update_t_key("tkanban_columns", "id_kanban_column");
        // $this->refresh_t_db_id('id_kanban_column');
    }
    public function _tkanban_column_tareas() {
        $this->table= "tkanban_column_tareas";
        $this->remove_idx();
        $this->update_t_from_db("id_kanban_column", 'tkanban_columns', 'id_kanban_column_code');
        $this->update_t_from_db("id_tarea", 'ttareas', 'id_tarea_code');
        $this->_insert_flush("tkanban_columns", "id_kanban_column_code");
    }

    public function _tdeletes() {
        $obj_delete= new TdeleteImport($this->dblink);
        $obj_delete->if_mysql= $this->if_mysql;
        $obj_delete->year_init= $this->year_init;
        $obj_delete->year_end= $this->year_end;
        $obj_delete->id_origen_code= $this->id_origen_code;
        $obj_delete->if_origen_down= $this->if_origen_down;
        $obj_delete->if_origen_up= $this->if_origen_up;
        
        $obj_delete->_tdeletes();
    }
} 
