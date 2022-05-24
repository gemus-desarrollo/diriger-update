<?php

/**
 * @author muste
 * @copyright 2012
 */

include_once "base_db.class.php";


class Tdb extends Tbase_db {    
    
    public function __construct($clink= null) {
        Tbase_db::__construct($clink);
        $this->size_tmp_tprocesos= 0;
        $this->className= 'Tdb';
    }

    public function export_db() {        
        global $array_dbtable;
        global $array_tables_default;
        reset($array_dbtable);
        $cant_tables= count($array_dbtable);

        $this->initial_temp_tables();
        
        $i= 0;
        while (list($key, $dbtable)= each($array_dbtable)) {
            ++$i;
            if ($i == 1) 
                continue;
            
            if ($key == "tauditorias" && $array_dbtable["teventos"]['export']) {
                $this->debug_time("tmp_treg_evento");
                $this->delete_table('tmp_treg_evento');
                $this->buildTable("tmp_treg_evento", $this->year); 
                $this->set_array_years("_tmp_teventos");
                $this->fill_image_treg_evento();
                $this->debug_time("tmp_treg_evento");                  
            }  
            
            if ($key == "treg_evento") {
                $this->array_eventos= array();
                $this->array_auditorias= array();
                $this->array_tareas= array();

                $this->create_array_eventos ("_teventos");
                $this->create_array_eventos ("_tauditorias");
                $this->create_array_eventos ("_ttareas");                  
            }

            $this->delete_table($key);
            $r= (float)($i+1)/$cant_tables;
            bar_progressCSS(2, "Configurando tabla: ".$key." ... ", $r);            
            
            if ($this->tb_filter && (!$dbtable['export'] && array_search($key, $array_tables_default) === false))
                continue;
            
            $this->buildTable($key);
            if (!is_null($this->error)) 
                break;
            
            $this->debug_time("export_$key");
            $db_cant= 0;
            $function= "\$db_cant=\$this->export_".$key."();";
            eval($function);
            $this->debug_time("export_$key");
            
            if ($dbtable['export'] && $db_cant > 0) {
                $array_dbtable[$key]['size']= $db_cant;               
                ++$this->cant_used_tables;
            }
            
            if (!is_null($this->error)) 
                break;
        }

        if (!is_null($this->error))
            $this->error= $function." => ".$this->error;

        $this->finish_temp_tables();
       
        return $this->error;
    }

    private function export_tusuario_grupos() {
        $j= 0;
        $sql= "select distinct tusuario_grupos.*, tusuario_grupos.id as _id from tusuario_grupos, _tusuarios ";
        $sql.= "where tusuario_grupos.id_usuario = _tusuarios.id; ";
        $result= $this->db_sql_show_error('export_tusuario_grupos', $sql);
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("tusuario_grupos", $result, $cant_j);
        return $j;
    }

    private function export_tusuario_procesos() {
        global $config;
        
        $j= 0;
        $sql= "select distinct tusuario_procesos.*, tusuario_procesos.id as _id from _tusuarios, tusuario_procesos, _tprocesos ";
        $sql.= "where tusuario_procesos.id_proceso = _tprocesos.id and tusuario_procesos.id_usuario = _tusuarios.id ";
        $result= $this->db_sql_show_error('export_tusuario_procesos', $sql);
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("tusuario_procesos", $result, $cant_j);

        $sql= "select distinct tusuario_procesos.id, tusuario_procesos.id_proceso, tusuario_procesos.id_proceso_code, ";
        $sql.= "_tusuario_grupos.id_usuario, 0, tusuario_procesos.cronos, tusuario_procesos.cronos_syn, ";
        $sql.= "tusuario_procesos.situs from _tusuario_grupos, tusuario_procesos, _tprocesos ";
        $sql.= "where tusuario_procesos.id_proceso = _tprocesos.id and _tusuario_grupos.id_grupo = tusuario_procesos.id_grupo ";
        $result= $this->db_sql_show_error('export_tusuario_procesos', $sql);
        $j+= $this->db_cant;
        
        $i= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $situs= !is_null($row['situs']) ? "'{$row['situs']}'" : "'{$config->location}'";
            $sql.= "insert into _tusuario_procesos () values ({$row['id']}, {$row['id_proceso']}, ".setNULL_str($row['id_proceso_code']).", ";
            $sql.= "{$row['id_usuario']}, NULL, '{$row['cronos']}', ".setNULL_str($row['cronos_syn']).", $situs); ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_tusuario_procesos", $sql);
                $i= 0;
                $sql= null;
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("export_tusuario_procesos", $sql);
        return $j;
    }
    
    /**
     * Migrar los planes de trabajo. Instruccion No1 Planes de Trabajo y proyectos
     */
    private function export_tauditorias() {
        $j= 0;
        $j+= $this->export_tauditorias_from_procesos();
        $j+= $this->export_tauditorias_from_teventos();

        $sql= "update _tauditorias set copyto= NULL, id_responsable_2= null ";
        $this->db_sql_show_error('export_tauditorias', $sql);

        $j= $this->purge_table('_tauditorias');

        $this->add_to_tusuarios('_tauditorias', 'id_responsable');
        $this->update_id_usuario('_tauditorias', 'id_usuario');
        return $j;
    }

    private function export_tauditorias_from_procesos() {
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? "union " : "";
            $sql.= "select distinct id_auditoria from tproceso_eventos_{$year} where id_auditoria is not null ";
            if ($this->if_origen_up) 
                $sql.= "and id_proceso = $this->id_destino ";
            if ($this->if_origen_down) 
                $sql.= "and id_proceso = $this->id_origen ";  
        }
        $result= $this->db_sql_show_error('export_tauditorias_from_procesos', $sql);
        $cant_j= $this->db_cant;

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $sql.= "insert into _tauditorias ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct _tmp_tauditorias.* from _tmp_tauditorias where _tmp_tauditorias.id = {$row['id_auditoria']}; ";
            
            ++$j;
            ++$i;     
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_tauditorias_from_procesos", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tauditorias ... ", $r);
            }
        }

        if (!empty($sql)) 
            $this->db_multi_sql_show_error("export_tauditorias_from_procesos", $sql); 
        $this->dblink->free_result($result);
        return $j; 
    }
    
    private function export_tauditorias_from_teventos() {
        $sql= "select distinct * from _tmp_treg_evento where id_auditoria is not null and flag_user = ". boolean2pg(1);
        $result= $this->db_sql_show_error('export_tauditorias_from_teventos', $sql);
        $cant_j= $this->db_cant;

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {            
            $sql.= "insert into _tauditorias ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct _tmp_tauditorias.* from _tmp_tauditorias where _tmp_tauditorias.id = {$row['id_auditoria']} ";
            $sql.= "and id_proceso != $this->id_destino; ";
            
            ++$j;
            ++$i;     
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_tauditorias_from_teventos", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tauditorias ... ", $r);                 
            }
        }

        if (!empty($sql)) 
            $this->db_multi_sql_show_error("export_tauditorias_from_teventos", $sql); 
        $this->dblink->free_result($result);
        return $j;
    }
    
    private function export_tnotas() {
        $j= 0;
        $sql_end= null;    
        if (!empty($this->date_cutoff) && empty($this->cronos_cut)) 
            $sql_end.= "and tnotas.fecha_inicio_real >= '$this->date_cutoff' ";   
        if (empty($this->date_cutoff) && !empty($this->cronos_cut))   
            $sql_end.= "and tnotas.cronos >= '$this->cronos_cut' ";                 
        if (!empty($this->date_cutoff) && !empty($this->cronos_cut)) 
            $sql_end.= "and (tnotas.fecha_inicio_real >= '$this->date_cutoff' or tnotas.cronos >= '$this->cronos_cut') "; 
        
        $sql= "insert into _tnotas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select tnotas.* from tnotas, tproceso_riesgos where tnotas.id = tproceso_riesgos.id_nota ";
        if ($this->if_origen_down) 
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_origen ";
        if ($this->if_origen_up) 
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_destino ";
        $sql.= "and tnotas.tipo = "._NO_CONFORMIDAD." ";
        $sql.= $sql_end;
        // $sql.= " and tnotas.origen in ("._NOTA_TIPO_AUDITORIA_EXTERNA.", "._NOTA_TIPO_SUPERVICION_EXTERNA.") ";
        $result= $this->db_sql_show_error('export_tnotas', $sql);
        $j+= $this->db_cant;
        
        if ($this->if_origen_down && $this->size_tmp_tprocesos > 0) {
            $sql= "insert into _tnotas ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct tnotas.* from tnotas, tproceso_riesgos, _tmp_tprocesos ";
            $sql.= "where tnotas.id = tproceso_riesgos.id_nota and tproceso_riesgos.id_proceso = _tmp_tprocesos.id ";
            $sql.= "and (if_req_leg = ".boolean2pg(1)." and tnotas.tipo = "._NO_CONFORMIDAD.") ";
            $sql.= $sql_end;
            $result= $this->db_sql_show_error('export_tnotas', $sql);
            $j+= $this->db_cant;
        }
        
        $j= $this->purge_table('_tnotas');

        $this->update_id_usuario('_tnotas', 'id_usuario');
        $this->update_id_proceso('_tnotas');
        return $j;
    }

    private function export_triesgos() {
        $j= 0;
        $sql= "insert into _triesgos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select triesgos.* from triesgos, tproceso_riesgos where triesgos.id = tproceso_riesgos.id_riesgo ";
        if ($this->if_origen_down) 
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_origen ";
        if ($this->if_origen_up) 
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_destino ";
        if (!empty($this->date_cutoff) && empty($this->cronos_cut)) 
            $sql.= "and fecha_inicio_plan >= '$this->date_cutoff' ";   
        if (empty($this->date_cutoff) && !empty($this->cronos_cut)) 
            $sql.= "and triesgos.cronos >= '$this->cronos_cut' ";                 
        if (!empty($this->date_cutoff) && !empty($this->cronos_cut)) 
            $sql.= "and (fecha_inicio_plan >= '$this->date_cutoff' or triesgos.cronos >= '$this->cronos_cut') "; 
        $result= $this->db_sql_show_error('export_triesgos', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _triesgos set copyto= NULL ";
        $this->db_sql_show_error('export_triesgos', $sql);

        $this->update_id_usuario('_triesgos', 'id_usuario');
        $this->update_id_proceso('_triesgos');
        return $j;
    }

    private function export_tproyectos() {
        $j= 0;
        $sql= " insert into _tproyectos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tproyectos.* from tproyectos, tproceso_proyectos ";
        $sql.= "where tproyectos.id = tproceso_proyectos.id_proyecto and tproceso_proyectos.id_proceso = $this->id_destino ";
        $result= $this->db_sql_show_error('export_tproyectos', $sql);
        $j+= $this->db_cant;

        $this->add_to_tusuarios('_tproyectos', 'id_responsable');
        $this->update_id_usuario('_tproyectos', 'id_usuario');
        $this->update_id_proceso('_tproyectos');
        
        return $j;
    }

    private function export_ttipo_eventos() {
        if ($this->if_origen_up) 
            return null;
        
        if (!is_null($this->cronos_cut)) {
            $sql= "select distinct id_tipo_evento from _teventos where id_tipo_evento is not null";
            $result= $this->db_sql_show_error('export_ttipo_eventos', $sql);
            $cant_j= $this->db_cant;

            $i= 0;
            $j= 0;
            $sql= null;
            while ($row= $this->dblink->fetch_array($result)) {
                $sql.= "insert into _ttipo_eventos ";
                $sql.= $this->if_mysql ? "() " : "";
                $sql.= "select distinct * from ttipo_eventos where id = {$row['id_tipo_evento']} ";
                $sql.= "and id_proceso = $this->id_origen; ";
                
                ++$j;
                ++$i;
                if ($i >= $_SESSION["_max_register_block_db_input"]) {
                    $this->db_multi_sql_show_error("export_ttipo_eventos", $sql);
                    $i= 0;
                    $sql= null;

                    $r= (float)($j) / $cant_j;
                    $_r= $r*100; $_r= number_format($_r,1);                
                    bar_progressCSS(2, "Carga tabla temporal ttipo_teventos ($j registros) ... ", $r);                
                }
            }    
            if (!empty($sql)) 
                $this->db_multi_sql_show_error("export_ttipo_eventos", $sql);   
            
        } else {
            $j= 0; 
            $sql= "insert into _ttipo_eventos ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct * from ttipo_eventos where id_proceso = $this->id_origen ";
            $result= $this->db_sql_show_error('export_ttipo_eventos', $sql);
            $j+= $this->db_cant;              
        }

        return $j;
    }
    
    private function export_ttipo_reuniones() {        
        if (!is_null($this->cronos_cut)) {
            $sql= "select distinct id_tipo_reunion from _teventos where id_tipo_reunion is not null";
            $result= $this->db_sql_show_error('export_ttipo_reuniones', $sql);
            $cant_j= $this->db_cant;

            $i= 0;
            $j= 0;
            $sql= null;
            while ($row= $this->dblink->fetch_array($result)) {
                $sql.= "insert into _ttipo_reuniones ";
                $sql.= $this->if_mysql ? "() " : "";
                $sql.= "select distinct * from ttipo_reuniones where id = {$row['id_tipo_reunion']} ";
                $sql.= "and id_proceso = $this->id_origen; ";
                
                ++$j;
                ++$i;
                if ($i >= $_SESSION["_max_register_block_db_input"]) {
                    $this->db_multi_sql_show_error("export_ttipo_reuniones", $sql);
                    $i= 0;
                    $sql= null;

                    $r= (float)($j) / $cant_j;
                    $_r= $r*100; $_r= number_format($_r,1);                
                    bar_progressCSS(2, "Carga tabla temporal ttipo_reuniones ($j registros) ... ", $r);                
                }
            }    
            if (!empty($sql)) 
                $this->db_multi_sql_show_error("export_ttipo_reuniones", $sql);   
            
        } else {
            $j= 0; 
            $sql= "insert into _ttipo_reuniones ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct * from ttipo_reuniones where id_proceso = $this->id_origen ";
            $result= $this->db_sql_show_error('export_ttipo_reuniones', $sql);
            $j+= $this->db_cant;              
        }

        return $j;
    }    

    private function export_ttipo_auditorias() {
        if (!is_null($this->cronos_cut)) {
            $sql= "select distinct id_tipo_auditoria from _tauditorias where id_tipo_auditoria is not null";
            $result= $this->db_sql_show_error('export_ttipo_auditorias', $sql);
            $cant_j= $this->db_cant;

            $i= 0;
            $j= 0;
            $sql= null;
            while ($row= $this->dblink->fetch_array($result)) {
                $sql.= "insert into _ttipo_auditorias ";
                $sql.= $this->if_mysql ? "() " : "";
                $sql.= "select distinct * from ttipo_auditorias where id = {$row['id_tipo_auditoria']} ";
                $sql.= "and id_proceso = $this->id_origen; ";
                
                ++$j;
                ++$i;
                if ($i >= $_SESSION["_max_register_block_db_input"]) {
                    $this->db_multi_sql_show_error("export_ttipo_reuniones", $sql);
                    $i= 0;
                    $sql= null;

                    $r= (float)($j) / $cant_j;
                    $_r= $r*100; $_r= number_format($_r,1);                
                    bar_progressCSS(2, "Carga tabla temporal ttipo_auditorias ($j registros) ... ", $r);                
                }
            }    
            if (!empty($sql)) 
                $this->db_multi_sql_show_error("export_ttipo_auditorias", $sql);   
            
        } else {
            $j= 0; 
            $sql= "insert into _ttipo_auditorias ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct * from ttipo_auditorias where id_proceso = $this->id_origen ";
            $result= $this->db_sql_show_error('export_ttipo_auditorias', $sql);
            $j+= $this->db_cant;              
        }

        return $j;
    } 
    
    private function export_ttareas() {
        $sql= "select distinct id_tarea from _tmp_treg_evento where id_tarea is not null and flag_user = ".boolean2pg(1);
        $result= $this->db_sql_show_error('export_ttareas', $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $sql.= "insert into _ttareas ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct _tmp_ttareas.* from _tmp_ttareas where _tmp_ttareas.id = {$row['id_tarea']}; ";

            ++$i;     
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_ttareas", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _ttareas ... ", $r);                 
            }
        }
        
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("export_ttareas", $sql);
        
        $j+= $this->add_to_export_ttareas_from_proceso();
        $j+= $this->export_ttareas_from_notas();
        $j+= $this->export_ttareas_from_riesgos();

        $sql= "select distinct _tmp_ttareas.*, _tmp_ttareas.id as _id from _tmp_ttareas, _tproyectos where _tmp_ttareas.id_proyecto = _tproyectos.id ";
        $result= $this->db_sql_show_error('export_ttareas', $sql);
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("ttareas", $result, $cant_j);
        
        $sql= "update _ttareas set copyto= NULL, id_responsable_2= NULL ";
        $this->db_sql_show_error('export_ttareas', $sql);

        $j= $this->purge_table('_ttareas');
        
        $this->add_to_tusuarios('_ttareas', 'id_responsable');
        $this->update_id_usuario('_ttareas', 'id_usuario');
        $this->update_id_proceso('_ttareas');

        $this->update_participantes('_ttareas');
        
        $this->dblink->free_result($result);
        return $j;
    }

    private function add_to_export_ttareas_from_proceso() {
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? "union " : "";        
            $sql.= "select * from tproceso_eventos_{$year} where id_evento is null and id_tarea is not null ";
            $sql.= " and (id_proceso = $this->id_destino ";
            if ($this->if_origen_down) 
                $sql.= "or id_proceso = $this->id_origen ";
            $sql.= ") and cronos >= '$this->cronos_under' ";
            $sql.= "and situs != '$this->destino_code' ";
        } 
        $sql.= "order by cronos desc";
        $result= $this->db_sql_show_error('add_to_export_ttareas_from_proceso', $sql);
        $cant_j= $this->db_cant;
        
        $j= 0;
        $i= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {            
            if (empty($row['id_tarea'])) 
                continue;
            
            if (!empty($array_ids[$row['id_tarea']])) 
                continue;
            else 
                $array_ids[$row['id_tarea']]= 1;
            
            $sql.= "insert into _ttareas ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from ttareas where id = {$row['id_tarea']}; "; 
            
            ++$i;
            ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {             
                $_result= $this->db_multi_sql_show_error("_add_to_ttareas", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _ttareas desde tproceso_eventos ... ", $r);                
            }    
        } 
        if ($sql) 
            $result= $this->db_multi_sql_show_error("_add_to_ttareas", $sql); 

        bar_progressCSS(2, "Carga de tabla temporal _ttareas desde tproceso_eventos ... ", 1); 
        $this->dblink->free_result($result);
        return $j;
    }    
       
    
    private function export_ttareas_from_notas() {
        $sql= "select distinct id from _tnotas ";
        $result= $this->db_sql_show_error('export_ttareas_from_notas', $sql);
        
        $j= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            $sql= "select distinct _tmp_ttareas.*, _tmp_ttareas.id as _id from _tmp_ttareas, triesgo_tareas ";
            $sql.= "where _tmp_ttareas.id = triesgo_tareas.id_tarea and triesgo_tareas.id_nota = {$row['id']} ";
            $_result= $this->db_sql_show_error('export_ttareas_from_notas', $sql);
            $cant_j= $this->db_cant;
            $j+= $this->multiple_insert("ttareas", $_result, $cant_j);            
        }
        return $j;
    }
    
    private function export_ttareas_from_riesgos() {
        $sql= "select distinct id from _triesgos ";
        $result= $this->db_sql_show_error('export_ttareas_from_riesgos', $sql);
        
        $j= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            $sql= "select distinct _tmp_ttareas.*, _tmp_ttareas.id as _id from _tmp_ttareas, triesgo_tareas ";
            $sql.= "where _tmp_ttareas.id = triesgo_tareas.id_tarea and triesgo_tareas.id_riesgo = {$row['id']} ";
            $_result= $this->db_sql_show_error('export_ttareas_from_riesgos', $sql);
            $cant_j= $this->db_cant;
            $j+= $this->multiple_insert("ttareas", $_result, $cant_j);            
        }
        return $j;
    }

    private function export_teventos() {
        $sql= "select distinct id_evento from _tmp_treg_evento where id_evento is not null and flag_user = ". boolean2pg(1);
        $result= $this->db_sql_show_error('export_teventos', $sql);
        $cant_j= $this->db_cant;
     
        $i= 0;
        $j= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {
            if (empty($row['id_evento'])) 
                continue;
            if (!empty($array_ids[$row['id_evento']])) 
                continue;
            $array_ids[$row['id_evento']]= $row['id_evento'];
            
            $sql.= "insert into _teventos ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from _tmp_teventos where id = {$row['id_evento']}; ";

            ++$i;
            ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_teventos", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga primaria tabla temporal _teventos ($j registros) ... ", $r);                
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("export_teventos", $sql);

        $j+= $this->add_to_export_teventos_from_proceso();        
        $j+= $this->add_to_export_teventos_from_tareas();
        $j+= $this->add_to_export_teventos_from_tauditorias();
        
        bar_progressCSS(2, "Actualizando tabla temporal _teventos ... ", 0);  
        $sql= "update _teventos set copyto= null, id_responsable_2= null, id_archivo= null, id_archivo_code= null ";
        $this->db_sql_show_error('export_teventos', $sql);
        
        $i= $j;
        bar_progressCSS(2, "Actualizando tabla temporal _teventos ... ", 0.2);  
        $j= $this->purge_table('_teventos');
       
        $this->add_to_tusuarios('_teventos', 'id_responsable');
        $this->update_tmp_treg_evento();
        $this->update_id_usuario('_teventos', 'id_usuario');
        $this->update_id_proceso('_teventos');
        $this->update_participantes('_teventos');
      
        $this->dblink->free_result($result);
        return $j;
    }
    
    private function _add_to_teventos($id_evento, $id_auditoria, $id_tarea, &$array_ids_insert) {
        if (empty($id_evento) && empty($id_auditoria) && empty($id_tarea)) 
            return 0;
        
        $or= null;
        $sql= "select * from _tmp_teventos where 1 and (";
        if (!empty($id_evento)) {
            $sql.= "id = $id_evento ";
            $or= "or";
        }    
        if (!empty($id_auditoria)) {
            $sql.= "$or id_auditoria = $id_auditoria ";
            $or= "or";
        }    
        if (!empty($id_tarea)) 
            $sql.= "$or id_tarea = $id_tarea ";
        $sql.= "); ";
        $result= $this->db_sql_show_error('add_to_export_teventos_from_proceso', $sql);
        
        $j= 0;
        $i= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {  
            if ($array_ids_insert[$row['id']]) 
                continue;
            $array_ids_insert[$row['id']]= 1;
            ++$j;
            $sql.= "insert into _teventos ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from _tmp_teventos where id = {$row['id']}; "; 
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {             
                $_result= $this->db_multi_sql_show_error("_add_to_teventos", $sql);
                $i= 0;
                $sql= null;
            }    
        } 
        if ($sql) 
            $_result= $this->db_multi_sql_show_error("_add_to_teventos", $sql); 
        
        return $j;
    }

    private function add_to_export_teventos_from_proceso() {
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? "union " : "";        
            $sql.= "select * from tproceso_eventos_{$year} where (id_proceso = $this->id_destino ";
            if ($this->if_origen_down) 
                $sql.= "or id_proceso = $this->id_origen ";
            $sql.= ") and cronos >= '$this->cronos_under' ";
            $sql.= "and situs != '$this->destino_code' ";
        } 
        $sql.= "order by cronos desc";
        $result= $this->db_sql_show_error('add_to_export_teventos_from_proceso', $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $k= 0;
        $sql= null;
        $array_ids= array();
        $array_ids_insert= array();
        while ($row= $this->dblink->fetch_array($result)) {            
            $id_evento= !empty($row['id_evento']) ? $row['id_evento'] : 0;
            $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : 0;
            $id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : 0;
            if (empty($id_evento) && empty($id_auditoria) && empty($id_tarea)) 
                continue;
            
            if (!empty($array_ids[$id_evento][$id_auditoria][$id_tarea])) 
                continue;
            else 
                $array_ids[$id_evento][$id_auditoria][$id_tarea]= 1;
            
            $k+= $this->_add_to_teventos($id_evento, $id_auditoria, $id_tarea, $array_ids_insert);

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {                             
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _teventos desde tproceso_eventos ... ", $r);                    
            }
        }
        
        bar_progressCSS(2, "Carga de tabla temporal _teventos desde tproceso_eventos ... ", 1); 
        $this->dblink->free_result($result);
        return $k;
    }

    private function add_to_export_teventos_from_tareas() {
        $sql= "select id from _ttareas";
        $result= $this->db_sql_show_error('add_to_export_teventos_from_tarea', $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $k= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$k;
            $sql.= "insert into _teventos ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from _tmp_teventos where id_tarea = {$row['id']}; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("add_to_export_teventos_from_tarea", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _teventos desde las tareas ... ", $r);                    
            } 
        }     
        if ($sql) 
            $this->db_multi_sql_show_error("add_to_export_teventos_from_tarea", $sql);
        
        bar_progressCSS(2, "Carga de tabla temporal _teventos desde las tareas ... ", 1); 
        $this->dblink->free_result($result);
        return $k;      
    }
    
    private function add_to_export_teventos_from_tauditorias() {
        $j= 0;
        $sql= "select distinct _tmp_teventos.*, _tmp_teventos.id as _id from _tmp_teventos, _tauditorias ";
        $sql.= "where _tmp_teventos.id_auditoria = _tauditorias.id and _tmp_teventos.id_proceso != $this->id_destino ";
        $result= $this->db_sql_show_error('export_teventos', $sql);
        $cant_j= $this->db_cant;
        $j= $this->multiple_insert("teventos", $result, $cant_j); 
        return $j;
    }
    
    private function export_ttematicas() {
        $sql= "select id, id_proceso from _teventos where _teventos.id_tipo_reunion > 0 and if_send = ".boolean2pg(1);
        $result= $this->db_sql_show_error('export_ttematicas', $sql);
        $cant_j= $this->db_cant;        
        
        $i= 0;
        $k= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) { 
            if ($row['id_proceso'] != $this->id_origen || $row['id_proceso'] == $this->id_destino)
                continue;
            ++$k;
            $sql.= "insert into _ttematicas ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select distinct ttematicas.* from ttematicas where ttematicas.id_evento = {$row['id']} ";
            if (!empty($this->cronos_cut)) 
                $sql.= "and ttematicas.cronos >= '$this->cronos_cut'; ";
            else {
                if (!empty($this->date_cutoff)) 
                    $sql.= "and (ttematicas.fecha_inicio_plan >= '$this->date_cutoff' or ttematicas.cronos >= '$this->date_cutoff'); ";
            }   

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_ttematicas", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _ttematicas ... ", $r);                    
            }              
        }
        if ($sql) 
            $this->db_multi_sql_show_error("export_ttematicas", $sql); 

        $this->dblink->free_result($result);
        
        bar_progressCSS(2, "Carga de tabla temporal _ttematicas ... ", 1); 

        $this->update_id_usuario('_ttematicas', 'id_responsable_eval');

        return $k;
    }
    
    private function update_tasistencias() {
        $obj_user= new Tusuario($this->dblink);
        
        $sql= "select * from _tasistencias";
        $result= $this->db_sql_show_error('update_tasistencias', $sql);
        $cant_j= $this->db_cant; 
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;

            if (empty($row['id_usuario']) && empty($row['nombre'])) {
                $obj_user->SetIdUsuario($row['id_usuario']);
                $obj_user->Set();
                $nombre= setNULL_str($obj_user->GetNombre());
                $cargo= setNULL_str($obj_user->GetCargo());
                
                $sql.= "update _tasistencias set nombre= $nombre, cargo= $cargo where id = {$row['id']}; ";
                $sql.= "update tasistencias set nombre= $nombre, cargo= $cargo where id = {$row['id']}; ";
                
                ++$i;
                if ($i >= $_SESSION["_max_register_block_db_input"]) {
                    $this->db_multi_sql_show_error("update_tasistencias", $sql);
                    $i= 0;
                    $sql= null;

                    $r= (float)($j) / $cant_j;
                    $_r= $r*100; $_r= number_format($_r,1);                
                    bar_progressCSS(2, "Carga de tabla temporal update_tasistencias ... ", $r);                   
                }
            }    
        }
        if ($sql) 
            $this->db_multi_sql_show_error("update_tasistencias", $sql);
        bar_progressCSS(2, "Actualizando tabla _tasistencias ... ", 1);         
    }
    
    private function export_tasistencias() {
        $sql= "select distinct tasistencias.* from tasistencias, _teventos where tasistencias.id_evento = _teventos.id ";
        $result= $this->db_sql_show_error('export_ttematicas', $sql);
        $cant_j= $this->db_cant; 
        
        $i= 0;
        $k= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {  
            ++$k;
            $sql.= "insert into _tasistencias ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from tasistencias where id = {$row['id']}; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_tasistencias", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tasistencias ... ", $r);                    
            }              
        }
        if ($sql) 
            $this->db_multi_sql_show_error("export_tasistencias", $sql);
        bar_progressCSS(2, "Carga de tabla temporal _tasistencias ... ", 1); 

        $this->update_tasistencias();
        $this->update_id_usuario('_tasistencias', 'id_usuario');
        $this->update_id_proceso('_tasistencias');
    }
    
    private function update_tdebates() {
        $sql= "select _tdebates.id as _id, _tdebates.origen_data as _origen_data, nombre, cargo, entidad ";
        $sql.= "from _tdebates, tasistencias where _tdebates.id_asistencia = tasistencias.id ";
        $result= $this->db_sql_show_error('update_tdebates', $sql);
        
        while ($row= $this->dblink->fetch_array($result)) {
            $origen_data= null;
            $origen_data= stripslashes($row['_origen_data']);
            $origen_data.= "&user:{$row['nombre']}:{$row['cargo']}::{$row['entidad']}";
            $origen_data= setNULL_str($origen_data);
            
            $sql= "update tdebates set id_asistencia= null, id_asistencia_code= null, id_responsable= null, ";
            $sql.= "origen_data= $origen_data where id = {$row['_id']}";
            $_result= $this->db_sql_show_error('update_tdebates', $sql);        
        }
    }
    
    private function export_tdebates() {
        $j= 0;
        $sql= "select distinct tdebates.*, tdebates.id as _id from tdebates, _ttematicas where tdebates.id_tematica = _ttematicas.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tdebates.cronos >= '$this->cronos_cut' ";
        else 
            if (!empty($this->date_cutoff)) 
                $sql.= "and tdebates.cronos >= '$this->date_cutoff' ";
        $result= $this->db_sql_show_error('export_tdebates', $sql);      
        $cant_j= $this->db_cant;
        $j+= $this->multiple_insert("tdebates", $result, $cant_j);

        $this->update_id_usuario('_tdebates', 'id_responsable');
        $this->update_id_proceso('_tdebates');
        $this->update_tdebates();
        
        return $j;
    }

    private function export_treg_evento() {
        $j= 0;
        $i= $this->add_to_export_treg_evento_from_evento(); 
        $j= $this->purge_table("_treg_evento", "id_evento", "id_auditoria", "id_tarea", "id_usuario");
        $this->update_id_usuario('_treg_evento', 'id_responsable');         
        return $j;       
    }

    private function update_tmp_treg_evento() {
        $sql= "select distinct id, id_responsable from _teventos";
        $result= $this->db_sql_show_error('update_tmp_treg_evento', $sql);
        $cant_j= $this->db_cant;

        $i= 0;
        $k= 0;
        $sql= null;        
        while ($row= $this->dblink->fetch_array($result)) {
            $sql.= "update _tmp_treg_evento set flag_resp_event= 1, id_responsable_flag= {$row['id_responsable']} ";
            $sql.= "where id_evento = {$row['id']} and id_usuario = {$row['id_responsable']}; ";
            
            ++$k;
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {             
                $this->db_multi_sql_show_error("update_tmp_treg_evento", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Update de tabla temporal _tmp_treg_eventos ... ", $r);                    
            }        
        }     
        if ($sql) 
            $this->db_multi_sql_show_error("update_tmp_treg_evento", $sql);          
        bar_progressCSS(2, "Update de tabla temporal _tmp_treg_eventos ... ", 1); 
        $this->dblink->free_result($result);
        return $k; 
    } 
    
    private function _insert_into_treg_evento($row) {
        $sql= null;
        $id_evento= setNULL($row['id_evento']);
        $id_evento_code= setNULL_str($row['id_evento_code']);
        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
        $id_tarea= setNULL($row['id_tarea']);
        $id_tarea_code= setNULL_str($row['id_tarea_code']);

        $origen_data= setNULL_str(stripslashes($row['origen_data']));
        $observacion= setNULL_str($row['observacion']);
        $aprobado= setNULL_str($row['aprobado']);
        $rechazado= setNULL_str($row['rechazado']);
        $cumplimiento= setNULL($row['cumplimiento']);

        $compute= setNULL($row['compute']);
        $toshow= setNULL($row['toshow']);
        $user_check= boolean2pg($row['user_check']);
        $hide_synchro= boolean2pg($row['hide_synchro']);
        $outlook= boolean2pg($row['outlook']);
        $reg_fecha= setNULL_str($row['reg_fecha']);
        $horas= setNULL($row['horas']); 

        $id_responsable= setNULL($row['id_responsable']);
        
        $sql= "insert into _treg_evento (id, id_evento, id_evento_code, id_usuario, id_responsable, origen_data, aprobado, ";
        $sql.= "rechazado, cumplimiento, observacion, compute, toshow, user_check, hide_synchro, id_tarea, id_tarea_code, id_auditoria, ";
        $sql.= "id_auditoria_code, reg_fecha, horas, cronos, situs, outlook) values ({$row['id']}, $id_evento, $id_evento_code, ";
        $sql.= "{$row['id_usuario']}, $id_responsable, $origen_data, $aprobado, $rechazado, $cumplimiento, ";
        $sql.= "$observacion, $compute, $toshow, $user_check, $hide_synchro, $id_tarea, $id_tarea_code, $id_auditoria, ";
        $sql.= "$id_auditoria_code, $reg_fecha, $horas, '{$row['cronos']}', '{$row['situs']}', $outlook); ";   
        
        return $sql;
    }
    
    private function add_to_export_treg_evento_from_evento() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_treg_evento']) ? strtotime($last_time_tables['_treg_evento']) : null;
        $cronos_under= !empty($this->cronos_under) ? strtotime($this->cronos_under) : null; 
        
        $sql= "select distinct * from _tusuarios";
        $result= $this->db_sql_show_error('add_to_export_treg_evento_from_evento', $sql);

        $array_tusuarios= array();
        while ($row= $this->dblink->fetch_array($result)) {
            if (isset($array_tusuarios[$row['id']])) 
                continue;
            $array_tusuarios[$row['id']]= $row['id'];
        }
          
        $sql= "select * from _tmp_treg_evento where (flag_user = 1 or flag_resp_event = 1) ";
        $sql.= "and cronos >= '$this->cronos_under'";
        $result= $this->db_sql_show_error('add_to_export_treg_evento_from_evento', $sql);        
        $cant_j= $this->db_cant; 

        $i= 0;
        $j= 0; 
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            if (!array_key_exists($row['id_usuario'], $array_tusuarios)) 
                continue;
            
            $_id_evento= !empty($row['id_evento']) ? $row['id_evento'] : 0;
            $_id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : 0;
            $_id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : 0;
            if (!empty($array_ids[$row['id_usuario']][$_id_evento][$_id_auditoria][$_id_tarea])) 
                continue;
            $array_ids[$row['id_usuario']][$_id_evento][$_id_auditoria][$_id_tarea]= 1;
            
            $if_evento= !empty($row['id_evento']) ? ($this->if_in_table($row['id_evento'], "_teventos") ? true : false) : false;
            $if_auditoria= !empty($row['id_auditoria']) ? ($this->if_in_table($row['id_auditoria'], "_tauditorias") ? true : false) : false;
            $if_tarea= !empty($row['id_tarea']) ? ($this->if_in_table($row['id_tarea'], "_ttareas") ? true : false) : false;            
            
            $continue= false;
            if ($if_evento || $if_auditoria || $if_tarea) {
                $continue= true;
            } else {
                if (!empty($this->cronos_cut) && strtotime($row['cronos']) < $cronos_cut) 
                    continue; 
                if (strtotime($row['cronos']) < $cronos_under) 
                    continue; 
                $continue= true;
            }
            if (!$continue) 
                continue;             
            
            $sql.= $this->_insert_into_treg_evento($row);
          
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("add_to_export_treg_evento_from_evento", $sql);            
                $i= 0;
                $sql= null;
               
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _treg_evento desde evento ... ", $r);                
            }
        }
        if ($sql) 
            $this->db_multi_sql_show_error("add_to_export_treg_evento_from_evento", $sql);   
        
        bar_progressCSS(2, "Carga de tabla temporal _treg_evento desde evento ... ", 1); 
        $this->dblink->free_result($result);
        return $j; 
    }    

    private function export_tusuario_eventos() {
        $j= 0;
        $sql= "select distinct * from _treg_evento";
        $result= $this->db_sql_show_error('export_tusuario_eventos', $sql);
        $cant_j= $this->db_cant;  
        
        $j+= $this->add_to_export_tusuario_eventos_from_usuario($result, $cant_j);
        $this->dblink->data_seek($result);
        $j+= $this->add_to_export_tusuario_eventos_from_grupo($result, $cant_j);
        $j+= $this->add_to_tusuario_eventos_from_ttematicas();
        
        $sql= "update _tusuario_eventos set id_grupo= NULL";
        $this->db_sql_show_error('export_tusuario_eventos', $sql);
        $this->dblink->free_result($result);
        
        $j= $this->purge_table("_tusuario_eventos", "id_evento", "id_auditoria", "id_tarea", "id_tematica", "id_usuario");
        
        return $j;
    }

    private function add_to_tusuario_eventos_from_ttematicas() {
        $sql= "select id, id_evento from _ttematicas";
        $result= $this->db_sql_show_error('add_to_tusuario_eventos_from_ttrematicas', $sql);
        $cant_j= $this->db_cant;

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $year= $this->array_years[$row['id_evento_code']][0][0];
            if (empty($year)) 
                continue;
            
            $sql.= "insert into _tusuario_eventos ";
            $sql.= $this->if_mysql ? "() " : "";   
            $sql.= "select distinct tusuario_eventos_$year.* from tusuario_eventos_$year, _tusuarios ";
            $sql.= "where tusuario_eventos_$year.id_tematica = {$row['id']} and tusuario_eventos_$year.id_usuario = _tusuarios.id; ";
            
           ++$i;
           ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("add_to_tusuario_eventos_from_ttrematicas", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal tusuario_eventos (tematicas) ... ", $r);                  
            }
        }
        if ($sql) 
            $this->db_multi_sql_show_error("add_to_tusuario_eventos_from_ttrematicas", $sql);
        
        return $j;
    }
    
    private function add_to_export_tusuario_eventos_from_usuario($result, $cant_j) {
        $k= 0;
        $i= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {
            ++$k;
            if (empty($row['id_usuario'])) 
                continue;
            
            $id_evento= $row['id_evento'] ? $row['id_evento'] : 0;
            $id_evento_code= $row['id_evento_code'] ? $row['id_evento_code'] : 0;
            
            $id_tarea= $row['id_tarea'] ? $row['id_tarea'] : 0;
            $id_tarea_code= $row['id_tarea_code'] ? $row['id_tarea_code'] : 0;
            
            $id_auditoria= $row['id_auditoria'] ? $row['id_auditoria'] : 0;
            $id_auditoria_code= $row['id_auditoria_code'] ? $row['id_auditoria_code'] : 0;
            
            $id_tematica= $row['id_tematica'] ? $row['id_tematica'] : 0;
            
            if (!empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica])) 
                continue;
            $array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica]= 1;
            $year= $this->array_years[$row['id_evento_code']][$id_auditoria_code][$id_tarea_code];
            if (empty($year)) 
                continue;
                
            $sql.= "insert into _tusuario_eventos ";
            $sql.= $this->if_mysql ? "() " : "";   
            $sql.= "select distinct tusuario_eventos_$year.* from tusuario_eventos_$year where id_usuario = {$row['id_usuario']} ";
            $sql.= "and situs != '$this->destino_code' ";
            $sql.= "and (";
            $or= null;
            if (!empty($id_evento)) {
                $sql.= "id_evento = $id_evento ";
                $or= "and";
            }    
            if (!empty($id_tarea)) {
                $sql.= "$or id_tarea = $id_tarea ";
                $or= "and";
            }
            if (!empty($id_auditoria)) {
                $sql.= "$or id_auditoria = $id_auditoria ";
                $or= "and";
            }
            if (!empty($id_tematica)) 
                $sql.= "$or id_tematica = $id_tematica ";
            $sql.= ") ";
            if (!empty($this->cronos_under)) 
                $sql.= "and cronos >= '$this->cronos_under' ";
            $sql.= "order by cronos desc limit 1; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("add_to_export_tusuario_eventos_from_usuario", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal tusuario_eventos (usuarios) ... ", $r);                  
            }
        }
        if ($sql) 
            $this->db_multi_sql_show_error("add_to_export_tusuario_eventos_from_usuario", $sql);
        
        bar_progressCSS(2, "Carga de tabla temporal tusuario_eventos (usuarios) ... ", 1);        
        return $k;
    }
    
    private function _insert_image_tusuario_eventos($result, $k, $cant_j) {
        $i= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $sql.= "insert into _tmp_tusuario_eventos (id, id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "id_tematica, id_tematica_code, id_usuario, id_grupo, aprobado, indirect, cronos, cronos_syn, situs) values ";
            $sql.= "({$row[0]}, ".setNULL($row[1]).", ".setNULL_str($row[2]).", ".setNULL($row[3]).", ".setNULL_str($row[4]).", ";
            $sql.= setNULL($row[5]).", ".setNULL_str($row[6]).", ".setNULL($row[7]).", ".setNULL_str($row[8]).", ".setNULL($row[9]).", ";
            $sql.= "0, ". setNULL_str($row[11]).", ". setZero($row[12]).", ".setNULL_str($row[13]).", ".setNULL_str($row[14]).", ";
            $sql.= setNULL_str($row[15])."); ";
            
            ++$k;
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("_insert_image_tusuario_eventos", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tmp_tusuario_eventos (_insert_image_tusuario_eventos) ... ", $r);                  
            }
        }  
        if ($sql) 
            $this->db_multi_sql_show_error("_insert_image_tusuario_eventos", $sql);
        return $k;    
    }
    
    private function fill_image_tusuario_eventos() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_tusuario_eventos']) ? $last_time_tables['_tusuario_eventos'] : null;   
        
        $k= 0;
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql= "select distinct tusuario_eventos_$year.id, id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "id_tematica, id_tematica_code, _tusuario_grupos.id_usuario, 0, aprobado, indirect, tusuario_eventos_$year.cronos as _cronos, ";
            $sql.= "tusuario_eventos_$year.cronos_syn, tusuario_eventos_$year.situs from tusuario_eventos_$year, _tusuario_grupos ";
            $sql.= "where tusuario_eventos_$year.id_usuario is null and tusuario_eventos_$year.id_grupo = _tusuario_grupos.id_grupo ";
            $sql.= "and tusuario_eventos_$year.situs != '$this->destino_code' ";
            if (!empty($this->cronos_under)) 
                $sql.= "and tusuario_eventos_$year.cronos >= '$this->cronos_under' ";
            else 
                if (!empty($cronos_cut)) 
                    $sql.= "and tusuario_eventos_$year.cronos >= '$cronos_cut' ";
            $sql.= "order by _cronos desc"; 
            
            $result= $this->db_sql_show_error('fill_image_tusuario_eventos', $sql); 
            $k+= $this->_insert_image_tusuario_eventos($result, $k, $this->db_cant);    
        }

        bar_progressCSS(2, "Carga de tabla temporal _tmp_tusuario_eventos (fill_image_tusuario_eventos) ... ", 1); 
    }    
    
    private function add_to_export_tusuario_eventos_from_grupo($result, $cant_j) {
        $this->delete_table("tmp_tusuario_eventos");
        $this->buildTable("tmp_tusuario_eventos", $this->year_init);        
        $this->fill_image_tusuario_eventos();                
        
        $k= 0;
        $j= 0;
        $array_ids= array();
        $array_ids_user= array();
        while ($row= $this->dblink->fetch_array($result)) {
            ++$k;
            $id_evento= !empty($row['id_evento']) ? $row['id_evento'] : 0;
            $id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : 0;
            $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : 0;
            $id_tematica= !empty($row['id_tematica']) ? $row['id_tematica'] : 0;
            
            if (!empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica])) 
                continue;
            $array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica]= 1;
            
            $sql= "select distinct _tmp_tusuario_eventos.* from _tmp_tusuario_eventos where 1 ";
            $sql.= "and (";
            $or= null;
            if (!empty($id_evento)) {
                $sql.= "id_evento = $id_evento ";
                $or= "or";
            }    
            if (!empty($id_tarea)) {
                $sql.= "$or id_tarea = $id_tarea ";
                $or= "or";
            }
            if (!empty($id_auditoria)) {
                $sql.= "$or id_auditoria = $id_auditoria ";
                $or= "or";
            }
            if (!empty($id_tematica)) 
                $sql.= "$or id_tematica = $id_tematica ";
            $sql.= ") ";

            $_result= $this->db_sql_show_error('add_to_export_tusuario_eventos_from_grupo', $sql);

            $i= 0;
            $sql= null;
            while ($row= $this->dblink->fetch_array($_result)) {
                if (!empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica][$row['id_usuario']])) 
                    continue;
                $array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica][$row['id_usuario']]= 1;                
                
                $id_evento= setNULL($row['id_evento']);
                $id_evento_code= setNULL_str($row['id_evento_code']);

                $id_tarea= setNULL($row['id_tarea']);
                $id_tarea_code= setNULL_str($row['id_tarea_code']);

                $id_auditoria= setNULL($row['id_auditoria']);
                $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
                
                $id_tematica= setNULL($row['id_tematica']);
                $id_tematica_code= setNULL_str($row['id_tematica_code']);

                $aprobado= setNULL_str($row['aprobado']);
                $indirect= boolean2pg($row['indirect']);

                $sql.= "insert into _tusuario_eventos values ({$row['id']}, $id_evento, $id_evento_code, $id_tarea, $id_tarea_code, ";
                $sql.= "$id_auditoria, $id_auditoria_code, $id_tematica, $id_tematica_code, {$row['id_usuario']}, NULL, $aprobado, ";
                $sql.= "$indirect, '{$row['cronos']}', ".setNULL_str($row['cronos_syn']).", '{$row['situs']}'); ";

                ++$i;
                ++$j;
                if ($i >= $_SESSION["_max_register_block_db_input"]) {
                    $this->db_multi_sql_show_error("add_to_export_tusuario_eventos_from_grupo", $sql);
                    $i= 0;
                    $sql= null;
                    
                    $r= (float)($k) / $cant_j;
                    $_r= $r*100; $_r= number_format($_r,1);                
                    bar_progressCSS(2, "Carga de tabla temporal tusuario_eventos (grupos) ... ", $r);                          
                }
            }
            if (!empty($sql)) 
                $this->db_multi_sql_show_error("add_to_export_tusuario_eventos_from_grupo", $sql); 
            
            bar_progressCSS(2, "Carga de tabla temporal tusuario_eventos (grupos) ... ", 1);                
        }

        return $j;
    }
    
    private function create_array_eventos($table) {
        $sql= "select * from $table";
        $result= $this->db_sql_show_error("create_array_eventos($table)", $sql);
        
        while ($row= $this->dblink->fetch_array($result)) {
            if ($table == "_teventos") 
                $this->array_eventos[$row['id']]= $row['id'];
            if ($table == "_tauditorias") 
                $this->array_auditorias[$row['id']]= $row['id'];
            if ($table == "_ttareas") 
                $this->array_tareas[$row['id']]= $row['id'];
        }
    }
    
    private function if_in_table($id, $table) {        
        if ($table == "_teventos") 
            return array_key_exists($id, $this->array_eventos) ? true : false;
        if ($table == "_tauditorias") 
            return array_key_exists($id, $this->array_auditorias) ? true : false;
        if ($table == "_ttareas") 
            return array_key_exists($id, $this->array_tareas) ? true : false;
    }

    private function _insert_into_tproceso_eventos($row) {
        $sql= null;
        $id_evento= setNULL($row['id_evento']);
        $id_evento_code= setNULL_str($row['id_evento_code']);
        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
        $id_tarea= setNULL($row['id_tarea']);
        $id_tarea_code= setNULL_str($row['id_tarea_code']);

        $id_tipo_evento= setNULL($row['id_tipo_evento']);
        $id_tipo_evento_code= setNULL_str($row['id_tipo_evento_code']);
        
        $toshow= setNULL($row['toshow']);
        $empresarial= setNULL($row['empresarial']);
        $id_responsable= setNULL($row['id_responsable']);
        $cumplimiento= setNULL($row['cumplimiento']);
        
        $id_responsable_aprb= setNULL($row['id_responsable_aprb']);
        $aprobado= setNULL_str($row['aprobado']);
        $observacion= setNULL_str(stripslashes($row['observacion']));
        
        $rechazado= setNULL_str($row['rechazado']);
        
        $indice= setNULL($row['indice']);
        $indice_plus= setNULL($row['indice_plus']);
        
        $sql= "insert into _tproceso_eventos (id, id_evento, id_evento_code, toshow, empresarial, id_tipo_evento, id_tipo_evento_code, ";
        $sql.= "id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, id_proceso, id_proceso_code, cumplimiento, id_responsable, ";
        $sql.= "observacion, aprobado, id_responsable_aprb, rechazado, indice, indice_plus, cronos, situs) values ({$row['id']}, $id_evento, ";
        $sql.= "$id_evento_code, $toshow, $empresarial, $id_tipo_evento, $id_tipo_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, ";
        $sql.= "$id_auditoria_code, {$row['id_proceso']}, '{$row['id_proceso_code']}', $cumplimiento, $id_responsable, $observacion, ";
        $sql.= "$aprobado, $id_responsable_aprb, $rechazado, $indice, $indice_plus, '{$row['cronos']}', '{$row['situs']}'); ";  
        
        return $sql;
    }
    
    private function export_tproceso_eventos() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_tproceso_eventos']) ? strtotime($last_time_tables['_tproceso_eventos']) : null;        
        $cronos_under= !empty($this->cronos_under) ? strtotime($this->cronos_under) : null;  
        
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? "union " : "";
            $sql.= "select *, $year as _year from tproceso_eventos_$year where 1 ";
            if ($this->if_origen_down) 
                $sql.= "and id_proceso = $this->id_origen ";
            if ($this->if_origen_up || (!$this->if_origen_down && !$this->if_origen_up)) 
                $sql.= "and id_proceso = $this->id_destino ";
            if (!empty($this->cronos_cut) && !empty($this->cronos_under))
                $sql.= "and cronos >= '$this->cronos_under' ";
        }
        $sql.= "order by cronos desc";
        $result= $this->db_sql_show_error('export_tproceso_eventos', $sql);
        $cant_j= $this->db_cant;

        $j= 0;
        $i= 0; 
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {  
            $id_evento= !empty($row['id_evento']) ? (int)$row['id_evento'] : 0;
            $id_auditoria= !empty($row['id_auditoria']) ? (int)$row['id_auditoria'] : 0;
            $id_tarea= !empty($row['id_tarea']) ? (int)$row['id_tarea'] : 0;
            $year= $row['_year'];
            
            if ($array_ids[$id_evento][$id_auditoria][$id_tarea]) 
                continue;
            $array_ids[$id_evento][$id_auditoria][$id_tarea]= 1;
            
            if (!($row['id_proceso'] == $this->id_destino || ($this->if_origen_down && $row['id_proceso'] == $this->id_origen ))) 
                continue;
            
            $if_evento= !empty($row['id_evento']) ? ($this->if_in_table($row['id_evento'], "_teventos") ? true : false) : false;
            $if_auditoria= !empty($row['id_auditoria']) ? ($this->if_in_table($row['id_auditoria'], "_tauditorias") ? true : false) : false;
            $if_tarea= !empty($row['id_tarea']) ? ($this->if_in_table($row['id_tarea'], "_ttareas") ? true : false) : false;
            
            $continue= false;
            if ($if_evento || $if_auditoria || $if_tarea) {
                $continue= true;
            } else {
                if (!empty($this->cronos_cut) && strtotime($row['cronos']) < $cronos_cut) 
                    continue; 
                if (strtotime($row['cronos']) < $cronos_under) 
                    continue; 
                $continue= true;
            }
            if (!$continue) 
                continue; 
            
            $sql.= $this->_insert_into_tproceso_eventos($row);
            ++$j;
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_tproceso_eventos", $sql);            
                $i= 0;
                $sql= null;
               
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tproceso_eventos ... ", $r);                
            }         
        }
        if ($sql) 
            $this->db_multi_sql_show_error("export_tproceso_eventos", $sql);

        $this->dblink->free_result($result);
        
        $j= $this->purge_table("_tproceso_eventos", "id_evento", "id_auditoria", "id_tarea");
        
        $this->update_id_usuario('_tproceso_eventos', 'id_usuario');
        $this->update_id_usuario('_tproceso_eventos', 'id_responsable');
        $this->update_id_usuario('_tproceso_eventos', 'id_responsable_aprb');

        bar_progressCSS(2, "Carga de tabla temporal _tproceso_eventos ... ", 1); 
        return $j;
    }
    
    private function export_tplanes() {
        $j= 0;
        $sql= "insert into _tplanes ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tplanes.* from tplanes, _tusuarios where tplanes.year >= $this->year ";
        $sql.= "and tplanes.id_usuario = _tusuarios.id ";
        if ($this->if_origen_down) 
            $sql.= "and _tusuarios.id_proceso != $this->id_destino ";
        if ($this->if_origen_up || (!$this->if_origen_down && !$this->if_origen_up)) 
            $sql.= "and _tusuarios.id_proceso = $this->id_destino ";        
        if (!empty($this->cronos_cut)) 
            $sql.= "and tplanes.cronos >= '$this->cronos_cut' ";
        $result= $this->db_sql_show_error('export_tplanes', $sql);
        $j+= $this->db_cant;
        
        if (!$this->if_origen_down && !$this->if_origen_up) 
            return $j;
        
        $sql= "insert into _tplanes ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tplanes.* from tplanes, _tusuarios where tplanes.year >= $this->year ";
        if ($this->if_origen_down) 
            $sql.= "and (tplanes.id_proceso = $this->id_origen and id_usuario is null) ";
        if ($this->if_origen_up) 
            $sql.= "and (tplanes.id_proceso = $this->id_destino and id_usuario is null) ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tplanes.cronos >= '$this->cronos_cut' ";          
            
        $result= $this->db_sql_show_error('export_tplanes', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    private function export_treg_plantrab() {
        $j= 0;
        $sql= "insert into _treg_plantrab ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct treg_plantrab.* from treg_plantrab, _tplanes where treg_plantrab.id_plan = _tplanes.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and treg_plantrab.cronos >= '$this->cronos_cut' ";

        $result= $this->db_sql_show_error('export_treg_plantrab', $sql);

        $j= $this->clean_treg_plantrab();
        return $j;
    }

    private function clean_treg_plantrab() {
        $sql= "select distinct id_plan, cronos from _treg_plantrab order by cronos desc";
        $result= $this->db_sql_show_error('clean_treg_plantrab', $sql);
        $j= $this->db_cant;
        
        $j= 0;
        $array_ids= array();        
        while ($row= $this->dblink->fetch_array($result)) {
            $id_plan= $row['id_plan'];
            $cronos= $row['cronos'];

            if (!empty($array_ids[$id_plan])) 
                continue;
            $array_ids[$id_plan]= $cronos;

            $sql= "delete from _treg_plantrab where id_plan = $id_plan and cronos < '$cronos'";
            $_result= $this->db_sql_show_error('clean_treg_tarea', $sql);
            --$j;
        }
        
        $sql= "select * from _treg_plantrab order by cronos desc";
        $result= $this->db_sql_show_error('clean_treg_plantrab', $sql);
        $j= $this->db_cant;        
        return $j;
    }

    private function export_tusuario_proyectos() {
        $j= 0;
        $sql= "insert into _tusuario_proyectos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tusuario_proyectos.* from tusuario_proyectos, _tproyectos, _tusuarios ";
        $sql.= "where tusuario_proyectos.id_usuario = _tusuarios.id and tusuario_proyectos.id_proyecto = _tproyectos.id ";
        if (!empty($this->cronos_cut)) 
            $sql.= "and tusuario_proyectos.cronos >= '$this->cronos_cut' ";
        else 
            if (!empty($this->date_cutoff)) 
                $sql.= "and tusuario_proyectos.cronos >= '$this->date_cutoff' ";
        $result= $this->db_sql_show_error('export_tusuario_proyectos', $sql);
        $j+= $this->db_cant;
        
        $sql= "insert into _tusuario_proyectos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tusuario_proyectos.id, tusuario_proyectos.id_proyecto, tusuario_proyectos.id_proyecto_code, ";
        $sql.= "_tusuario_grupos.id_usuario, NULL, tusuario_proyectos.cronos, tusuario_proyectos.cronos_syn, ";
        $sql.= "tusuario_proyectos.situs from _tusuario_grupos, tusuario_proyectos, _tproyectos where ";
        $sql.= "tusuario_proyectos.id_proyecto =  _tproyectos.id and _tusuario_grupos.id_grupo = tusuario_proyectos.id_grupo";
        $result= $this->db_sql_show_error('export_tusuario_proyectos', $sql);
        $j+= $this->db_cant;
        
        $sql= "update _tusuario_proyectos set id_grupo= NULL";
        $result= $this->db_sql_show_error('export_tusuario_proyectos', $sql);
        
        return $j;
    }

    private function export_tinductor_eventos() {
        global $last_time_tables;
        $cronos_cut= !empty($last_time_tables['_tinductor_eventos']) ? $last_time_tables['_tinductor_eventos'] : null;        
        
        $j= 0;
        $sql= "insert into _tinductor_eventos ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tinductor_eventos.* from tinductor_eventos, _teventos, _tinductores ";
        $sql.= "where tinductor_eventos.id_evento = _teventos.id and tinductor_eventos.id_inductor = _tinductores.id  ";
        if (!empty($this->cronos_under)) 
            $sql.= "and tinductor_eventos.cronos >= '$this->cronos_under' ";
        else 
            if (!empty($this->date_cutoff)) 
                $sql.= "and tinductor_eventos.cronos >= '$cronos_cut' ";

        $result= $this->db_sql_show_error('export_tinductor_eventos', $sql);
        $j+= $this->db_cant;
        return $j;
    }

    private function export_treg_tarea() {
        $j= 0;
        $sql= "select treg_tarea.*, treg_tarea.id as _id from treg_tarea, _ttareas where treg_tarea.id_tarea = _ttareas.id ";
        $sql.= "and treg_tarea.cronos >= '$this->cronos_under' order by cronos desc";   
        $result= $this->db_sql_show_error('export_treg_tarea', $sql);
        $cant_j= $this->db_cant;
        $this->multiple_insert("treg_tarea", $result, $cant_j);

        $j= $this->clean_treg_tarea();
        $this->update_id_usuario("treg_tarea", "id_usuario");
        
        return $j;
    }

    private function clean_treg_tarea() {
        $array= array();

        $sql= "select distinct id_tarea, id_usuario, cronos from _treg_tarea order by cronos desc";
        $result= $this->db_sql_show_error('clean_treg_tarea', $sql);
        $j= $this->db_cant;
        
        $i= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $id_tarea= $row['id_tarea'];
            $id_usuario= $row['id_usuario'];
            $cronos= $row['cronos'];

            if (!is_null($array[$id_tarea][$id_usuario])) 
                continue;
            $array[$id_tarea][$id_usuario]= $cronos;

            $sql.= "delete from _treg_tarea where id_tarea = $id_tarea and id_usuario = $id_usuario and cronos < '$cronos'; ";

            ++$i;
            --$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $_result= $this->db_multi_sql_show_error("clean_treg_tarea", $sql);
                $i= 0;
                $sql= null;
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("clean_treg_tarea", $sql);
        
        return $j;
    }  
    
    private function _insert_tkanban_columns($result, $cant_j) {
        $i= 0;
        $k= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {  
            ++$k;
            $sql.= "insert into _tkanban_columns ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from tkanban_columns where id = {$row['_id']}; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("_insert_tkanban_columns", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tkanban_columns ... ", $r);                    
            }              
        }
        if ($sql) 
            $this->db_multi_sql_show_error("_insert_tkanban_columns", $sql);
    }

    private function export_tkanban_columns() {
        $j= 0;
        $sql= "select tkanban_columns.*, tkanban_columns.id as _id from tkanban_columns, _tproyectos ";
        $sql.= "where tkanban_columns.id_proyecto = _tproyectos.id ";
        $sql.= "and tkanban_columns.cronos >= '$this->cronos_under' order by cronos desc";   
        $result= $this->db_sql_show_error('export_tkanban_columns', $sql);
        $cant_j= $this->db_cant;

        $this->_insert_tkanban_columns($result, $cant_j);

        $sql= "select tkanban_columns.*, tkanban_columns.id as _id from tkanban_columns, _tusuarios ";
        $sql.= "where tkanban_columns.id_responsable = _tusuarios.id ";
        $sql.= "and tkanban_columns.cronos >= '$this->cronos_under' order by cronos desc";   
        $result= $this->db_sql_show_error('export_tkanban_columns', $sql);
        $cant_j+= $this->db_cant;

        $this->_insert_tkanban_columns($result, $cant_j);

        $this->add_to_tusuarios('tkanban_columns', 'id_responsable');
        
        return $cant_j;
    }
    
    private function export_tkanban_column_tareas() {
        $sql= "select tkanban_column_tareas.*, tkanban_column_tareas.id as _id from tkanban_column_tareas, ";
        $sql.= "_tkanban_columns where tkanban_column_tareas.id_kanban_column = _tkanban_columns.id ";
        $sql.= "and tkanban_column_tareas.cronos >= '$this->cronos_under' order by cronos desc";
        $result= $this->db_sql_show_error('export_tkanban_columns', $sql);
        $cant_j= $this->db_cant;

        $i= 0;
        $k= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {  
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= $row['id'];

            $sql.= "insert into _tkanban_column_tareas ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from tkanban_column_tareas where id = {$row['_id']}; ";  
             
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("export_tkanban_column_tareas", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($k) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Carga de tabla temporal _tkanban_column_tareas ... ", $r);                    
            }                       
        } 
        if ($sql) 
            $this->db_multi_sql_show_error("export_tkanban_column_tareas", $sql);   
        
        $this->add_to_tusuarios('tkanban_column_tareas', 'id_usuario');
        return $i;
    }
}
?>