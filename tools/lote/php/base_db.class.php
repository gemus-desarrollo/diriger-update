<?php

/**
 * @author muste
 * @copyright 2012
 */

include_once "baseLote.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/DBServer.class.php";

include_once "escenario.ext.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/usuario.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/evento.class.php";

include_once "baseExport.class.php";
include_once "deleteExport.class.php";

class Tbase_db extends TbaseExport {
    
    public function __construct($clink= null) {
        TbaseExport::__construct($clink);
        $this->size_tmp_tprocesos= 0;
        $this->className= 'Tdb';
    }

    /**
     * Tabla Generales
    */
    protected function create_tmp_tprocesos() {
        $sql= "CREATE TEMPORARY TABLE _tmp_tprocesos ( ";
        $sql.= " id ".field2pg("INTEGER(11)")." DEFAULT NULL, ";
        $sql.= " id_code ".field2pg("CHAR(12)")." DEFAULT NULL, ";
        $sql.= " codigo ".field2pg("CHAR(2)")." DEFAULT NULL, ";
        $sql.= " nombre ".field2pg("VARCHAR(120)").", ";
        $sql.= " tipo ".field2pg("TINYINT(2)")." NOT NULL, ";
        $sql.= " lugar ".field2pg("MEDIUMTEXT").", ";
        $sql.= " conectado ".field2pg("TINYINT(2)")." DEFAULT NULL, ";
        $sql.= " descripcion ".field2pg("TEXT")." NOT NULL ";
        $sql.= ") ";
        $this->db_sql_show_error('create_tmp_tprocesos', $sql);
    }

    protected function export_tmp_tprocesos() {
        $obj_prs= new Tproceso($this->dblink);
        $obj_prs->get_procesos_down($this->id_origen);

        foreach ($obj_prs->array_cascade_down as $prs) {
            if (empty($prs['codigo']) || strlen($prs['codigo']) == 0) 
                continue;
            if ($prs['conectado'] == _LAN || $prs['conectado'] == _NO_LOCAL_WAN_NODO) 
                continue;

            $sql= "insert into _tmp_tprocesos(id, id_code, codigo, nombre, tipo, lugar, conectado, descripcion) values (";
            $sql.= "{$prs['id']}, '{$prs['id_code']}', '{$prs['codigo']}', '{$prs['nombre']}', {$prs['tipo']}";
            $sql.= ", '{$prs['lugar']}', {$prs['conectado']}, '{$prs['descripcion']}')";
            $result= $this->db_sql_show_error('export_tmp_tprocesos', $sql);
            if ($result) 
                ++$this->size_tmp_tprocesos;
        }
        unset($obj_prs);
    }

    protected function export_tprocesos() {
        $j= 0;
        $sql= "insert into _tprocesos ";
        $sql.= $this->if_mysql ? "() " : "";		
        $sql.= "select * from tprocesos where id = $this->id_origen or id = $this->id_destino ";
        $this->db_sql_show_error('export_tprocesos', $sql);
        $j= $this->db_cant ? 1 : 0;
        return $j;
    }

    protected function export_tescenarios() {
        $j= 0;
        $sql= "select distinct tescenarios.* from tescenarios where tescenarios.id_proceso = $this->id_origen ";
        $result= $this->db_sql_show_error('export_tescenarios', $sql); 
        
        $obj_esc= new _Tescenario($this->dblink);
        $j= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            $obj_esc->Set($row['id']);
            $this->error= $obj_esc->add();
            
            if (!empty($this->error)) 
                writeLog(date('Y-m-d H:i'), "<div class='alert alert-danger text'>{$this->error}</div>", 'winlog');
            else
                ++$j;
        }
        return $j;
    }    

    protected function export_tusuarios() {
        $j= 0;
        $sql= " select distinct tusuarios.*, tusuarios.id as _id from tusuarios, tusuario_procesos ";
        $sql.= "where ((tusuario_procesos.id_proceso = $this->id_destino and tusuarios.id = tusuario_procesos.id_usuario) ";
        if ($this->if_origen_up) 
            $sql.= "or global_user = true ";
        $sql.= ") and ".stringSQL("noIdentidad")." is not null ";
        if (!empty($this->date_cutoff)) 
            $sql.= "and (tusuarios.eliminado is null or tusuarios.eliminado >= '$this->date_cutoff')";
        $result= $this->db_sql_show_error('export_tusuarios', $sql);
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("tusuarios", $result, $cant_j);
        bar_progressCSS(2, "Cargando de tabla temporal _tusuarios ($j registros) .... ", 0.3); 
        
        $sql= "select distinct tusuarios.*, tusuarios.id as _id from tusuarios, tusuario_grupos, tusuario_procesos  where ";
        $sql.= "tusuario_procesos.id_proceso = $this->id_destino and ".stringSQL("noIdentidad")." is not null ";
        $sql.= "and (tusuarios.id = tusuario_grupos.id_usuario and tusuario_grupos.id_grupo = tusuario_procesos.id_grupo) ";
        if (!empty($this->date_cutoff))
            $sql.= "and (tusuarios.eliminado is null or tusuarios.eliminado >= '$this->date_cutoff')";
        $result= $this->db_sql_show_error('export_tusuarios', $sql);
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("tusuarios", $result, $cant_j);
        bar_progressCSS(2, "Cargando de tabla temporal _tusuarios ($j registros) .... ", 0.7); 
        
        $sql= "select tusuarios.*, tusuarios.id as _id from tusuarios where global_user = true ";
        $sql.= "or (id_proceso_jefe = $this->id_origen or id_proceso = $this->id_destino)";
        $result= $this->db_sql_show_error('export_tusuarios', $sql);
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("tusuarios", $result, $cant_j);
        bar_progressCSS(2, "Cargando de tabla temporal _tusuarios ($j registros) .... ", 1); 
        
        return $j;
    }       
    
    /**
     * Migrar la Planificacion estrategica. Incluye la informacion general del Cuadro de Mando
     */
    protected function export_tpoliticas() {
        $j= 0;
        $sql= "insert into _tpoliticas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tpoliticas.* from tpoliticas where if_inner = ".boolean2pg(1)." and tpoliticas.id_proceso = $this->id_destino  ";

        $this->db_sql_show_error('export_tpoliticas', $sql);
        $j+= $this->db_cant;
        
        if ($this->if_origen_up) {
            $sql= "insert into _tpoliticas ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select tpoliticas.* from tpoliticas where if_inner = ".boolean2pg(1)." ";
            $sql.= "and (tpoliticas.id_proceso is not null and tpoliticas.id_proceso != $this->id_destino)";
            
            $this->db_sql_show_error('export_tpoliticas', $sql);
            $j+= $this->db_cant;
        }
        
        return $j;
    }

    protected function export_treg_politica() {
        $j= 0;
        $sql= "insert into _treg_politica ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select treg_politica.* from treg_politica where treg_politica.id_proceso = $this->id_origen ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and treg_politica.cronos >='$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_politica', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario("_treg_politica");
        return $j;
    }

    protected function export_tprogramas() {
        $j= 0;
        $sql= "insert into _tprogramas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tprogramas.* from tprogramas, tproceso_proyectos where tprogramas.id = tproceso_proyectos.id_programa ";
        $sql.= "and tproceso_proyectos.id_proceso = $this->id_destino ";
        $this->db_sql_show_error('export_tprogramas', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_proceso('_tprogramas', false);
        return $j;
    }

    protected function export_treg_programa() {
        $j= 0;
        if (!$this->if_origen_down) {
            $this->db_sql_show_error('export_treg_programa', null);
            return null;
        }

        $sql= "insert into _treg_programa ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select treg_programa.* from treg_programa, _tprogramas where treg_programa.id_proceso = $this->id_origen ";
        $sql.= "and treg_programa.id_programa = _tprogramas.id ";
        if (!empty($this->date_cutoff) && empty($this->cronos_cut)) 
            $sql.= "and treg_programa.reg_fecha >= '$this->date_cutoff' ";
        if (empty($this->date_cutoff) && !empty($this->cronos_cut)) 
            $sql.= "and treg_programa.cronos >= '$this->cronos_cut' ";
        if (empty($this->date_cutoff) && !empty($this->cronos_cut)) 
            $sql.= "and (treg_programa.reg_fecha >= '$this->date_cutoff' or treg_programa.cronos >= '$this->cronos_cut') ";
        $result= $this->db_sql_show_error('export_treg_programa', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario("_treg_programa");
        return $j;
    }

    protected function export_tref_programas() {
        $j= 0;
        $sql= "insert into _tref_programas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tref_programas.* from tref_programas, _treg_programa ";
        $sql.= "where tref_programas.id_programa = _treg_programa.id_programa ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tref_programas.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tref_programas', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_tobjetivos() {
        $j= 0;
        if (!$this->if_origen_up && !$this->if_origen_down) {
            $this->db_sql_show_error('export_tobjetivos', null);
            return null;
        }

        $sql= "insert into _tobjetivos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tobjetivos.* from tobjetivos where tobjetivos.id_proceso = $this->id_origen ";
        if ($this->if_origen_up) 
            $sql.= "and tobjetivos.if_send_down = ".boolean2pg(1)." ";
        if ($this->if_origen_down) 
            $sql.= "and tobjetivos.if_send_up = ".boolean2pg(1)." ";
        $result= $this->db_sql_show_error('export_tobjetivos', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _tobjetivos set if_send_up= null, if_send_down= null ";
        $this->db_sql_show_error('export_tobjetivos', $sql);
        return $j;
    }

    protected function export_treg_objetivo() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_treg_objetivo']) ? $last_time_tables['_treg_objetivo'] : null;
        
        $j= 0;
        if (!$this->if_origen_down) {
            $this->db_sql_show_error('export_treg_objetivo', null);
            return null;
        }

        $sql= "insert into _treg_objetivo ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select treg_objetivo.* from treg_objetivo, _tobjetivos where treg_objetivo.id_objetivo = _tobjetivos.id  ";
        $sql.= "and treg_objetivo.id_proceso = $this->id_origen ";
        if (!empty($cronos_cut)) 
            $sql.= "and treg_objetivo.cronos >= '$cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_objetivo', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario("_treg_objetivo");
    }

    protected function export_tpolitica_objetivos() {
        if (!$this->if_origen_down) {
            $this->db_sql_show_error('export_tpolitica_objetivos', null);
            return null;
        }

        $sql= "insert into _tpolitica_objetivos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tpolitica_objetivos.* from tpolitica_objetivos, _tobjetivos where _tobjetivos.id_proceso = $this->id_origen ";
        $sql.= "and tpolitica_objetivos.id_objetivo = _tobjetivos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tpolitica_objetivos.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tpolitica_objetivos', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_tperspectivas() {
        $j= 0;
        if (!$this->if_origen_up && $this->if_origen_down) {
            $this->db_sql_show_error('export_tperspectivas', null);
            return null;
        }

        $sql= "insert into _tperspectivas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tperspectivas.* from tperspectivas where tperspectivas.id_proceso = $this->id_origen ";
        $result= $this->db_sql_show_error('export_tperspectivas', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_treg_perspectiva() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_treg_perspectiva']) ? $last_time_tables['_treg_perspectiva'] : null;
        
        $j= 0;
        if (!$this->if_origen_down) {
            $this->db_sql_show_error('export_treg_perspectiva', null);
            return null;
        }

        $sql= "insert into _treg_perspectiva ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select treg_perspectiva.* from treg_perspectiva, _tperspectivas where treg_perspectiva.id_perspectiva = _tperspectivas.id  ";
        $sql.= "and treg_perspectiva.id_proceso = $this->id_origen ";
        if (!empty($cronos_cut)) 
            $sql.= "and treg_perspectiva.cronos >= '$cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_perspectiva', $sql);

        $this->update_id_usuario("_treg_perspectiva");
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_tinductores() {
        $j= 0;
        $sql= "insert into _tinductores ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tinductores.* from tinductores where tinductores.id_proceso = $this->id_origen ";
        if ($this->if_origen_up) 
            $sql.= "and tinductores.if_send_down = ".boolean2pg(1)." ";
        if ($this->if_origen_down) 
            $sql.= "and tinductores.if_send_up = ".boolean2pg(1)." ";
        $result= $this->db_sql_show_error('export_tinductores', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _tinductores set id_perspectiva = NULL where id_perspectiva not in ";
        $sql.= "(select id from _tperspectivas) ";
        $result= $this->db_sql_show_error('export_tinductores', $sql);
        
        $sql= "update _tinductores set if_send_up= null, if_send_down= null ";
        $this->db_sql_show_error('export_tinductores', $sql);

        if (!$this->if_origen_up && $this->if_origen_down) {
            $this->fix_tperspectivas("_tinductores");
        }        
        
        return $j;
    }

    protected function export_treg_inductor() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_treg_inductor']) ? $last_time_tables['_treg_inductor'] : null;
        
        $j= 0;
        if (!$this->if_origen_down) {
            $this->db_sql_show_error('export_treg_inductor', null);
            return null;
        }

        $sql= "insert into _treg_inductor ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select treg_inductor.* from treg_inductor, _tinductores where treg_inductor.id_inductor = _tinductores.id ";
        if (!empty($cronos_cut)) 
            $sql.= "and treg_inductor.cronos >= '$cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_inductor', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario("_treg_inductor");
        return $j;
    }

    protected function export_tobjetivo_inductores() {
        $j= 0;
        if (!$this->if_origen_up && !$this->if_origen_down) {
            $this->db_sql_show_error('export_tobjetivo_inductores', null);
            return null;
        }

        $sql= "insert into _tobjetivo_inductores ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tobjetivo_inductores.* from tobjetivo_inductores, _tobjetivos, _tinductores ";
        $sql.= "where tobjetivo_inductores.id_objetivo = _tobjetivos.id and tobjetivo_inductores.id_inductor = _tinductores.id  ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tobjetivo_inductores.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tobjetivo_inductores', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_tobjetivo_tareas() {
        $j= 0;
        $sql= "insert into _tobjetivo_tareas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tobjetivo_tareas.* from tobjetivo_tareas, _tobjetivos, _ttareas ";
        $sql.= "where tobjetivo_tareas.id_objetivo = _tobjetivos.id and tobjetivo_tareas.id_tarea = _ttareas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tobjetivo_tareas.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tobjetivo_tareas', $sql);
        $j+= $this->db_cant;
        return $j;
    }    
    
    /**
     * Migrar los indicadores. Incluye la informacion relativa a los indicadores y valores
     */
    protected function export_tunidades() {
        $j= 0;
        $sql= "insert into _tunidades ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select * from tunidades where tunidades.id_proceso = $this->id_origen ";
        
        $this->db_sql_show_error('export_tunidades', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_tproceso_indicadores() {
        if ($this->if_origen_up && !$this->if_origen_down) {
            $this->db_sql_show_error('export_tproceso_indicadores', null);
            return null;
        }
        
        $j= 0;
        $sql= "insert into _tproceso_indicadores ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select * from tproceso_indicadores where tproceso_indicadores.id_proceso = $this->id_destino ";
        $result= $this->db_sql_show_error('export_tproceso_indicadores', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_tindicadores() {
        if ($this->if_origen_up && !$this->if_origen_down) {
            $this->db_sql_show_error('export_tproceso_indicadores', null);
            return null;
        }        
        
        $j= 0;
        $sql= " insert into _tindicadores ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tindicadores.* from tindicadores, _tproceso_indicadores ";
        $sql.= "where tindicadores.id = _tproceso_indicadores.id_indicador ";
        $this->db_sql_show_error('export_tindicadores', $sql);
        $j+= $this->db_cant;
        
        if ($this->if_exist_tmp_table("tref_programas")) {
            $sql= " insert into _tindicadores ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct tindicadores.* from tindicadores, _tref_programas ";
            $sql.= "where tindicadores.id = _tref_programas.id_indicador ";
            $result= $this->db_sql_show_error('export_tindicadores', $sql);
            $j+= $this->db_cant;
        }
        
        $j= $this->purge_table('_tindicadores');

        $this->update_id_proceso("_tindicadores");
        $this->update_id_usuario('_tindicadores', 'id_usuario_plan', 'user_plan');
        $this->update_id_usuario('_tindicadores', 'id_usuario_real', 'user_real');
        
        $sql= "update _tindicadores set inicio_origen= inicio, fin_origen= fin; "; 
        $sql.= "update _tindicadores set inicio= NULL, fin= NULL, id_usuario_real= null, id_usuario_plan= null; ";
        $this->db_multi_sql_show_error('export_tindicadores', $sql);
        
        return $j;
    }

    protected function export_tref_indicadores() {
        $j= 0;
        
        $sql= " insert into _tref_indicadores ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= " select tref_indicadores.* from tref_indicadores, _tindicadores ";
        $sql.= " where tref_indicadores.id_indicador = _tindicadores.id ";
        $result= $this->db_sql_show_error('export_tref_indicadores', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _tref_indicadores set id_inductor_code= NULL ";
        if ($this->if_exist_tmp_table("tinductores"))     
            $sql.= ", id_inductor = NULL where id_inductor not in (select id from _tinductores) ";            
        $result= $this->db_sql_show_error('export_tref_indicadores', $sql);

        return $j;
    }

    protected function export_tindicador_criterio() {
        $j= 0;
        $sql= "insert into _tindicador_criterio ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tindicador_criterio.* from tindicador_criterio, _tindicadores ";
        if ($this->size_tmp_tprocesos > 0) 
            $sql.= ", _tmp_tprocesos ";
        $sql.= " where tindicador_criterio.id_indicador = _tindicadores.id and tindicador_criterio.id_proceso != $this->id_destino ";
        $sql.= "and (tindicador_criterio.id_proceso = $this->id_origen ";
        if ($this->size_tmp_tprocesos > 0) 
            $sql.=  "or tindicador_criterio.id_proceso = _tmp_tprocesos.id ";
        $sql.= ") ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tindicador_criterio.cronos >= '$this->cronos_cut' ";

        $result= $this->db_sql_show_error('export_tindicador_criterio', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _tindicador_criterio set id_perspectiva_code= NULL ";
        if ($this->if_exist_tmp_table("tperspectivas")) 
            $sql.= ", id_perspectiva = NULL where id_perspectiva not in (select id from _tperspectivas) ";
        $this->db_sql_show_error('export_tindicador_criterio', $sql);

        $this->update_id_proceso("_tindicador_criterio", false);
        
        if (!$this->if_origen_up && $this->if_origen_down) {
            $this->fix_tperspectivas("_tindicador_criterio");
        }    

        return $j;
    }    
    
    protected function export_tregistro() {
        $j= 0;
        $sql= "insert into _tregistro ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tregistro.* from tregistro, _tindicadores where tregistro.id_indicador = _tindicadores.id  ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tregistro.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tregistro', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario("_tregistro", "id_usuario_real", 'user_real');
        $this->update_id_usuario("_tregistro", "id_usuario_plan", 'user_plan');
        return $j;
    }
    /**
     * Termina la migracion de indicadores
     */

    protected function export_treg_plan() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_treg_plan']) ? $last_time_tables['_treg_plan'] : null;        
        
        $j= 0;
        $sql= "insert into _treg_plan ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct treg_plan.* from treg_plan, _tindicadores where treg_plan.id_indicador = _tindicadores.id ";
        if (!empty($cronos_cut)) 
            $sql.= "and treg_plan.cronos >= '$cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_plan', $sql);

        $j+= $this->clean_treg_plan();
        $this->update_id_usuario("_treg_plan");
        return $j;
    }

    protected function clean_treg_plan() {
        $j= 0;
        $array= array();

        $sql= "select distinct ".str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))")." as _date, ";
        $sql.= "id_indicador, cronos from _treg_plan order by cronos desc";
        $result= $this->db_sql_show_error('clean_treg_plan', $sql);
        $j+= $this->db_cant;

        while ($row= $this->dblink->fetch_array($result)) {
            $id_indicador= $row['id_indicador'];
            $date= $row['_date'];
            $cronos= $row['cronos'];

            if (!is_null($array[$id_indicador][$date])) 
                continue;
            $array[$id_indicador][$date]= $cronos;

            $sql= "delete from _treg_plan where id_indicador = $id_indicador and cronos < '$cronos' ";
            $sql.= "and ".str_to_datetime2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))")." = '$date' ";
            $this->db_sql_show_error('clean_treg_plan', $sql);
            --$j;
        }
        
        return $j;
    }

    protected function export_treg_real() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_treg_real']) ? $last_time_tables['_treg_real'] : null;        
        
        $j= 0;
        $sql= "insert into _treg_real ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct treg_real.* from treg_real, _tindicadores where treg_real.id_indicador = _tindicadores.id ";
        if (!empty($cronos_cut)) 
            $sql.= "and treg_real.cronos >= '$cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_real', $sql);

        $j+= $this->clean_treg_real();
        $this->update_id_usuario("_treg_real");
        return $j;
    }

    protected function clean_treg_real() {
        $j= 0;
        $array= array();

        $sql= "select distinct ".str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))")." as _date, ";
        $sql.= "id_indicador, cronos from _treg_real order by cronos desc ";
        $result= $this->db_sql_show_error('clean_treg_real', $sql);
        $j+= $this->db_cant;
        
        while ($row= $this->dblink->fetch_array($result)) {
            $id_indicador= $row['id_indicador'];
            $date= $row['_date'];
            $cronos= $row['cronos'];

            if (!is_null($array[$id_indicador][$date])) 
                continue;
            $array[$id_indicador][$date]= $cronos;

            $sql= "delete from _treg_real where id_indicador = $id_indicador and cronos < '$cronos' ";
            $sql.= "and ".str_to_datetime2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))")." = '$date' ";
            $this->db_sql_show_error('clean_treg_real', $sql);
            --$j;
        }
        return $j;
    }
    
    /**
     * Migrar los riesgos. Incluye la informacion relativa a los indicadores y valores
     */
    protected function export_tproceso_riesgos() {
        $j= 0;
        $sql= "insert into _tproceso_riesgos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tproceso_riesgos.* from tproceso_riesgos, _triesgos, _tnotas ";
        $sql.= "where (tproceso_riesgos.id_riesgo = _triesgos.id or tproceso_riesgos.id_nota = _tnotas.id) ";
        if ($this->if_origen_up) 
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_destino ";
        if ($this->if_origen_down) 
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_origen ";
        $result= $this->db_sql_show_error('export_tproceso_riesgos', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_treg_riesgo() {
        $j= 0;
        $id_proceso= $this->if_origen_down ? $this->id_origen : $this->id_destino;
        $sql= "insert into _treg_riesgo ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct treg_riesgo.* from treg_riesgo, _triesgos where treg_riesgo.id_proceso = $id_proceso ";
        $sql.= "and treg_riesgo.id_riesgo = _triesgos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and treg_riesgo.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_riesgo', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario('_treg_riesgo', 'id_usuario');
        return $j;
    }

    protected function export_triesgo_tareas() {
        $j= 0;
        $sql= "insert into _triesgo_tareas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct triesgo_tareas.* from triesgo_tareas, _triesgos, _ttareas ";
        $sql.= "where triesgo_tareas.id_riesgo = _triesgos.id and triesgo_tareas.id_tarea = _ttareas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and triesgo_tareas.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_triesgo_tareas', $sql);
        $j+= $this->db_cant;
        
        $sql= "insert into _triesgo_tareas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct triesgo_tareas.* from triesgo_tareas, _tnotas, _ttareas ";
        $sql.= "where triesgo_tareas.id_nota = _tnotas.id and triesgo_tareas.id_tarea = _ttareas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and triesgo_tareas.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_triesgo_tareas', $sql);
        $j+= $this->db_cant;
        
        $sql= "insert into _triesgo_tareas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct triesgo_tareas.* from triesgo_tareas, _tpoliticas, _ttareas ";
        $sql.= "where triesgo_tareas.id_politica = _tpoliticas.id and triesgo_tareas.id_tarea = _ttareas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and triesgo_tareas.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_triesgo_tareas', $sql);
        $j+= $this->db_cant;
        
        $j= $this->purge_table('_triesgo_tareas');
        $this->update_id_usuario('_triesgo_tareas');
        
        return $j;
    }

    protected function export_tinductor_riesgos() {
        $j= 0;
        $sql= "insert into _tinductor_riesgos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tinductor_riesgos.* from tinductor_riesgos, _triesgos, _tinductores ";
        $sql.= "where tinductor_riesgos.id_riesgo = _triesgos.id and tinductor_riesgos.id_inductor = _tinductores.id  ";
        if (!empty($this->cronos_cut)) 
            $sql.= " and tinductor_riesgos.cronos >= '$this->cronos_cut' ";

        $this->db_sql_show_error('export_tinductor_riesgos', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    protected function export_treg_proceso() {
        $j= 0;
        $sql= "insert into _treg_proceso ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select * from treg_proceso where treg_proceso.id_proceso = $this->id_origen ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and treg_proceso.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_treg_proceso', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario('_treg_proceso');
        return $j;
    }

    protected function export_tnota_causas() {
        $j= 0;
        $sql= "insert into _tnota_causas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tnota_causas.* from tnota_causas, _tnotas where tnota_causas.id_nota = _tnotas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tnota_causas.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tnota_causas', $sql);
        $j+= $this->db_cant;
        
        $this->update_id_usuario('_tnota_causas');
        return $j;
    }

    protected function export_tproceso_proyectos() {
        $j= 0;
        $sql= "insert into _tproceso_proyectos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tproceso_proyectos.* from tproceso_proyectos, _tprogramas where tproceso_proyectos.id_programa = _tprogramas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tproceso_proyectos.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tproceso_proyectos', $sql);
        $j+= $this->db_cant;
        
        $sql= "insert into _tproceso_proyectos ";
	    $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tproceso_proyectos.* from tproceso_proyectos, _tproyectos where tproceso_proyectos.id_proyecto = _tproyectos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tproceso_proyectos.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tproceso_proyectos', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    private function _create_tmp_tref_documentos() {
        $sql= "DROP TABLE IF EXISTS _tmp_tref_documentos ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);

        $sql= " CREATE TEMPORARY TABLE _tmp_tref_documentos ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_documento ".field2pg("INTEGER(11)").", ";
        $sql.= " cronos ".field2pg("DATETIME")." ";
        $sql.= ") ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);        
    }
    
    protected function create_tmp_tref_documentos() {
        $this->_create_tmp_tref_documentos();

        $sql= "insert into _tmp_tref_documentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tref_documentos.id, tref_documentos.id_documento, tref_documentos.cronos from tref_documentos, _teventos ";
        $sql.= "where tref_documentos.id_evento = _teventos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tref_documentos.cronos >= '$this->cronos_cut' ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);

        $sql= "insert into _tmp_tref_documentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tref_documentos.id, tref_documentos.id_documento, tref_documentos.cronos from tref_documentos, _tauditorias ";
        $sql.= "where tref_documentos.id_auditoria = _tauditorias.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tref_documentos.cronos >= '$this->cronos_cut' ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);

        $sql= "insert into _tmp_tref_documentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tref_documentos.id, tref_documentos.id_documento, tref_documentos.cronos from tref_documentos, _tproyectos ";
        $sql.= "where tref_documentos.id_proyecto = _tproyectos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tref_documentos.cronos >= '$this->cronos_cut' ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);

        $sql= "insert into _tmp_tref_documentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tref_documentos.id, tref_documentos.id_documento, tref_documentos.cronos from tref_documentos, _triesgos ";
        $sql.= "where tref_documentos.id_riesgo = _triesgos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tref_documentos.cronos >= '$this->cronos_cut' ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);

        $sql= "insert into _tmp_tref_documentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tref_documentos.id, tref_documentos.id_documento, tref_documentos.cronos from tref_documentos, _tnotas ";
        $sql.= "where tref_documentos.id_nota = _tnotas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tref_documentos.cronos >= '$this->cronos_cut' ";
        $this->db_sql_show_error('create_tmp_tref_documentos', $sql);
    }

    protected function export_tref_documentos() {
        $j= 0;
        $sql= "insert into _tref_documentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tref_documentos.* from tref_documentos, _tmp_tref_documentos where tref_documentos.id = _tmp_tref_documentos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and _tmp_tref_documentos.cronos >= '$this->cronos_cut' ";
        $this->db_sql_show_error('export_tref_documentos', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _tref_documentos set id_requisito = null, id_requisito_code = null";
        $this->db_sql_show_error('export_tref_documentos', $sql);
        
        return $j;
    }

    protected function export_tdocumentos() {
        $j= 0;
        $this->create_tmp_tref_documentos();

        $sql= "insert into _tdocumentos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tdocumentos.* from tdocumentos, _tmp_tref_documentos where tdocumentos.id = _tmp_tref_documentos.id_documento ";
        $sql.= "and id_archivo is null ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and _tmp_tref_documentos.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tdocumentos', $sql);
        $j+= $this->db_cant;
        
        $this->if_tdocumentos= $j > 0 ? true : false;
        return $j;
    }
    
    protected function export_tdeletes() {  
        $obj_delete= new TdeleteExport($this->dblink);
        $obj_delete->if_mysql= $this->if_mysql;
        $obj_delete->cronos_cut= $this->cronos_cut;
        $obj_delete->date_cutoff= $this->date_cutoff;
        $obj_delete->origen_code= $this->origen_code;
        
        $obj_delete->id_destino= $this->id_destino;
        $obj_delete->if_origen_up= $this->if_origen_up;
        $obj_delete->if_origen_down= $this->if_origen_down;
        
        $db_cant= $obj_delete->_tdeletes();
        return $db_cant;
    }
}
