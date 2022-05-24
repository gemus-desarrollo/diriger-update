<?php
/**
 * Description of baseExport
 *
 * @author mustelier
 */

include_once "baseLote.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/DBServer.class.php";

include_once "escenario.ext.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/usuario.class.php";
include_once _ROOT_DIRIGER_DIR."/php/class/evento.class.php";


class TbaseExport extends TbaseLote {
    protected $size_tmp_tprocesos;
    protected $array_treg_evento;
    public $if_tdocumentos;

    protected $array_usuarios_prs;     
    
    //put your code here    
    /**
     * transmitir de informacion de los usuarios cuyos registros no se envian
     */
    protected $array_usuarios_data;
    protected $array_procesos_data;
    
    public function __construct($clink= null) {
        TbaseLote::__construct($clink);
        $this->size_tmp_tprocesos= 0;
        $this->className= 'Tdb';

        $this->array_auditorias= null;
        $this->array_eventos= null;
        $this->array_tareas= null;
        
        $this->array_tmp_eventos= null;
        $this->finalized= true;
        $this->finalized_init= true;
    }    

    public function build_db() {
        global $array_dbtable;
        reset($array_dbtable);
        $this->cant_used_tables= count($array_dbtable);
        $obj= new Ttabla_anno($this->dblink, $this->year);
        
        $i= 0;
        while (list($key, $dbtable)= each($array_dbtable)) {
            $this->delete_table($key);
            if ($this->action == 'import' && !$dbtable['export']) 
                continue;
            function_progressCSS(++$i, "Configurando tabla: ".$key." ... ", $this->cant_used_tables, 2);
     
            $this->buildTable($key);
            if (!is_null($this->error)) 
                break;
        }
        if (!is_null($this->error)) 
            return "Creando tabla temporal $key => {$this->error}";
    }    
    
    protected function if_exist_tmp_table($table) {
        global $array_dbtable;     
        return $array_dbtable[$table]['export'] ? true : false;
    }
    
    protected function delete_table($table) {        
        $sql= "DROP TABLE IF EXISTS _$table";
        $this->db_sql_show_error('delete_table', $sql);
    }
     
    protected function initial_temp_tables() {
        global $array_dbtable;
        
        $this->delete_table("tmp_tprocesos");
        $this->create_tmp_tprocesos();
        $this->export_tmp_tprocesos();
        
        $this->delete_table("tmp_teventos");
        $this->delete_table("tmp_tauditorias");
        $this->delete_table("tmp_ttareas");
        
        if ($array_dbtable['teventos']['export']) {
            $this->debug_time("tmp_teventos");
            $this->buildTable("tmp_teventos");        
            $this->fill_image_teventos();
            $this->debug_time("tmp_teventos");

            $this->debug_time("tmp_tauditorias");
            $this->buildTable("tmp_tauditorias");        
            $this->fill_image_tauditorias();        
            $this->debug_time("tmp_tauditorias");

            $this->debug_time("tmp_ttareas");
            $this->buildTable("tmp_ttareas");        
            $this->fill_image_ttareas();         
            $this->debug_time("tmp_ttareas"); 
        }
    }
    
    protected function repare_teventos() {
        $sql= "alter table _teventos drop column flag_id_evento";
        $this->db_sql_show_error("repare_teventos", $sql);
    }
    
    protected function finish_temp_tables() {
        global $array_dbtable;
        
        if (is_null($this->error)) {
            $this->fix_tprocesos();
            $this->fix_tusuarios();
        
            if ($array_dbtable["teventos"]['export']) {
                $this->update_responsable("_teventos");
                $this->update_responsable("_tauditorias");  
                $this->update_responsable("_ttareas");  
            }
            
            $this->purge_table('_tusuarios');
            
            if ($array_dbtable["teventos"]['export']) {
                $this->fix_teventos("_teventos");
                $this->fix_field_toshow("_teventos");
                $this->fix_teventos("_tauditorias");
                $this->fix_field_toshow("_tauditorias");
            }
        }

        $this->set_export_procedure();

        $this->delete_table("tmp_treg_evento");
        $this->delete_table("tmp_teventos");
        $this->delete_table("tmp_tareas");
        $this->delete_table("tmp_tauditorias");   

        if ($array_dbtable["teventos"]['export']) {
            $this->repare_teventos();
        }       
    }       
    
    protected function add_to_tusuarios($table, $field, $id_usuario= null) {
        global $array_dbtable;
        
        $sql= "select * from _tusuarios";
        $result= $this->db_sql_show_error('add_to_tusuarios', $sql);
        
        $array_new_id_users= array();
        while ($row= $this->dblink->fetch_array($result)) {
            if (!empty($array_new_id_users[$row['id']])) 
                continue;
            else 
                $array_new_id_users[$row['id']]= $row['id'];            
        } 

        $sql= "select distinct $field from $table ";
        if (!empty($id_usuario)) 
            $sql.= "where $field = $id_usuario ";
        $result= $this->db_sql_show_error('add_to_tusuarios', $sql);
        $cant_j= $this->db_cant;
        
        $false= $this->if_mysql ? 0 : "false";
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            if (empty($row[0])) 
                continue;
            if (!empty($array_new_id_users[$row[0]])) 
                continue;

            $sql.= "insert into _tusuarios ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * from tusuarios where id = {$row[0]}; ";
            ++$array_dbtable['tusuarios']['size'];
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("add_to_tusuarios($table)", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "add_to_tusuarios($table) ... ", $r); 
                if (!is_null($this->error)) 
                    die("$sql =====> ERROR:$this->error");                
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("add_to_tusuarios($table)", $sql);
        
        bar_progressCSS(2, "add_to_tusuarios($table) ... ", 1);  
    }

    protected function build_origen_data_user($id_usuario, $mark= 'user') {
        $mark= is_null($mark) ? 'user' : $mark;

        $sql= "select tusuarios.nombre as _nombre, cargo, tprocesos.id_proceso as _id_proceso, tprocesos.nombre as proceso, ";
        $sql.= "tipo, tusuarios.email as _email from tusuarios, tprocesos where tusuarios.id = $id_usuario ";
        $sql.= "and tusuarios.id_proceso = tprocesos.id ";
        $result= $this->db_sql_show_error('build_origen_data_user', $sql);

        $row= $this->dblink->fetch_array($result);
        $data= "~$mark:".$row['_nombre'].':'.$row['cargo'].':'.$row['_email'].':'.$row['proceso'].':'.$row['tipo'];
        return $data;
    }

    protected function build_origen_data_prs($id_proceso) {
        $data= null;
        
        $sql= "select nombre, tipo, conectado, codigo, id_proceso from tprocesos where id = $id_proceso";
        $result= $this->db_sql_show_error('build_origen_data_prs', $sql);
        $row= $this->dblink->fetch_array($result);
        $data= "^process:".$row['nombre'].':'.$row['tipo'].':'.$row['conectado'].':'.$row['codigo'];
        
        $id_proceso_sup= $row['id_proceso'];
        if (empty($id_proceso_sup)) 
            return $data;
        
        $sql= "select nombre, tipo, conectado, codigo from tprocesos where id = $id_proceso_sup";
        $result= $this->db_sql_show_error('build_origen_data_prs', $sql);
        $row= $this->dblink->fetch_array($result);
        $data.= ':'.$row['nombre'].':'.$row['tipo'].':'.$row['conectado'].':'.$row['codigo'];
        return $data;
    }

    protected function update_id_usuario($table, $field= 'id_usuario', $mark= 'user') {
        $mark= is_null($mark) ? 'user' : $mark;

        $array_usuarios= array();
        $sql= "select distinct id from _tusuarios";
        $result= $this->db_sql_show_error("update_id_usuario($table)", $sql);
        
        while ($row= $this->dblink->fetch_array($result)) {
            $array_usuarios[(int)$row['id']]= $row['id'];
        }
        $this->dblink->free_result($result);
        
        $sql= "select id, $field, origen_data from $table where $field";
        $result= $this->db_sql_show_error("update_id_usuario($table)", $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $id_usuario= $row[$field];
            $id= $row['id'];
            $origen_data= $row['origen_data'];
            
            if (array_key_exists($id_usuario, $array_usuarios)) 
                continue;
            if (!is_null($origen_data)) {
                if (stripos($origen_data, "^$mark:") !== false) 
                    continue;
            }    
            $data= $this->array_usuarios_data[$id_usuario];

            if (is_null($data)) {
                $data= $this->build_origen_data_user($id_usuario, $mark);
                $this->array_usuarios_data[$id_usuario]= $data;
            }

            $_data= !is_null($origen_data) ? $origen_data."||^" : null;
            $_data.= $data;
            $_data= setNULL_str($_data);

            $sql.= "update $table set $field = null, origen_data= $_data where $field= $id_usuario and id = $id; ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("update_id_usuario($table)", $sql);
                $i= 0;
                $sql= null; 
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "update_id_usuario($table) ... ", $r);                 
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("update_id_usuario($table)", $sql);
        
        bar_progressCSS(2, "update_id_usuario($table) ... ", 1); 
    }

    protected function update_id_proceso($_table, $user_origen_data= true) {
        $user_origen_data= !is_null($user_origen_data) ? $user_origen_data : true;

        $sql= "select $_table.id as _id, $_table.id_proceso as _id_proceso ";
        if ($user_origen_data) 
            $sql.= ", origen_data ";
        $sql.= "from $_table where $_table.id_proceso != $this->id_destino and $_table.id_proceso != $this->id_origen ";
        $result= $this->db_sql_show_error("update_id_proceso($_table)", $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $id= $row['_id'];
            $id_proceso= $row['_id_proceso'];
            $origen_data= $row['origen_data'];
            
            if (!is_null($origen_data))
                if (stripos($origen_data, '^process:') !== false) 
                    continue;

            $data= $this->array_procesos_data[$id_proceso];

            if (empty($data)) {
                $data= $this->build_origen_data_prs($id_proceso);
                $this->array_procesos_data[$id_proceso]= $data;
            }
            $_data= !is_null($origen_data) ? $origen_data."||^" : null;
            $_data.= $data;
            $_data= setNULL_str($_data);

            $sql.= "update $_table set $_table.id_proceso= $this->id_origen, $_table.id_proceso_code= '$this->id_origen_code' ";
            if ($user_origen_data) 
                $sql.= ", origen_data= $_data ";
            $sql.= "where $_table.id_proceso = $id_proceso and $_table.id = $id; ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {            
                $this->db_multi_sql_show_error("update_id_proceso($_table)", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "update_id_proceso($_table) ... ", $r);                 
            }
        }

        if (!empty($sql)) 
            $this->db_multi_sql_show_error("update_id_proceso($_table)", $sql);
        bar_progressCSS(2, "update_id_proceso($_table) ... ", 1); 
    }

    protected function update_participantes($table= '_teventos') {
        global $config;
        
        $proceso= $config->empresa;
        $tipo= $config->local_proceso_tipo;
        $codigo= $config->location;

        $obj_evento= new Tevento($this->dblink);
        $date_cutoff= strtotime(add_date($this->date_cutoff, null, -3));
        $funct= ($table == '_teventos') ? 'evento' : 'tarea';

        $sql= "select distinct * from $table";
        $result= $this->db_sql_show_error("update_participantes($table)", $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $id= $row['id'];
            $origen_data= !is_null($row['origen_data']) ? stripslashes($row['origen_data'])."||^" : null;
            
            for ($year= $this->year_init; $year <= $this->year_end; $year++) {
                if ($year == $this->year_init && $year == date('Y', $date_cutoff)) {
                    $obj_evento->SetYear(date('Y', $date_cutoff));
                    $obj_evento->SetMonth(date('m', $date_cutoff));
                    $obj_evento->SetDay(1);
                } else {
                    $obj_evento->SetYear($year);
                    $obj_evento->SetMonth(1);
                    $obj_evento->SetDay(1);                    
                }
                $data= $obj_evento->get_participantes($id, $funct, null, null);
                $origen_data.= "^participant:"."$data:$proceso:$tipo:$codigo";
            }
            $origen_data= setNULL_str($origen_data);
            $sql.= "update $table set origen_data= $origen_data where id = $id; ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("update_participantes($table)", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "update_participantes($table) ... ", $r);                 
            }
        }

        if (!empty($sql)) $this->db_multi_sql_show_error("update_participantes($table)", $sql);
        bar_progressCSS(2, "update_participantes($table) ... ", 1);  
    }    

    protected function multiple_insert($table, $result, $cant_j, $sql_plus= null, $use_year= false) {
        $use_year= !is_null($use_year) ?$use_year : false;
        $_table= '_'.$table;
        
        $i= 0;
        $j= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->dblink->fetch_array($result)) {
            $table_year= $table;
            if ($use_year) {
                $id_evento= !empty($row['id_evento_code']) ? $row['id_evento_code'] : 0;
                $id_auditoria= !empty($row['id_auditoria_code']) ? $row['id_auditoria_code'] : 0;
                $id_tarea= !empty($row['id_tarea_code']) ? $row['id_tarea_code'] : 0;
                $year= $this->array_years[$id_evento][$id_auditoria][$id_tarea];
                $year= !empty($year) ? $year : $this->year;
                $table_year= "{$table}_{$year}";
                
                if ($array_ids[$id_evento][$id_auditoria][$id_tarea]) 
                    continue;
                $array_ids[$id_evento][$id_auditoria][$id_tarea]= 1;
            } 
            
            $sql.= "insert into $_table ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select * ";
            if ($table_year == "teventos") 
                $sql.= ", null ";
            $sql.= "from $table_year where id = {$row['_id']} ";
            if (!empty($sql_plus)) 
                $sql.= $sql_plus;
            $sql.= "; ";
            ++$i;
            ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("multiple_insert($_table)", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "multiple_insert($_table) ... ", $r);                  
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("multiple_insert($_table)", $sql);
        
        bar_progressCSS(2, "multiple_insert($_table) ... ", 1); 
        return $j;
    }
    
    protected function fix_tusuarios() {
        $sql= "update _tusuarios set _clave= NULL, acc_planwork= NULL, acc_planrisk= NULL, acc_planaudit= NULL, ";
        $sql.= "acc_planheal= NULL, acc_archive= NULL, nivel_archive2= NULL, nivel_archive3= NULL, ";
        $sql.= "nivel_archive4= NULL, freeassign= NULL, user_ldap= NULL, global_user= false, ";
        $sql.= "id_proceso_jefe= NULL, id_proceso_jefe_code= NULL ";
        if ($this->if_origen_down) 
            $sql.= ", nivel= "._MONITOREO;
        $result= $this->db_sql_show_error('fix_tusuarios', $sql);

        $this->update_id_proceso("_tusuarios");
    }
    
    protected function fix_tprocesos() {
        $sql= "update _tprocesos set local_archive= null, if_entity= NULL, id_entity= NULL, id_entity_code= NULL";
        $this->db_sql_show_error('fix_tprocesos', $sql);
    }
    
    protected function fix_tperspectivas($_table) {
        $sql= "update $_table set id_perspectiva= null, id_perspectiva_code= null";
        $this->db_sql_show_error("fix_tperspectivas($_table)", $sql);
    }
    
    /* si en el responsable de la actividad pertenece al destino */
    protected function if_belong_prs($id_responsable) {
        if (is_null($this->array_usuarios_prs)) 
            $this->get_usuarios_prs();
        
        if ($this->if_origen_down && $id_responsable == $this->id_origen_chief_prs) 
            return true;
        if ($this->if_origen_up && $id_responsable == $this->id_destino_chief_prs) 
            return true;
        $if_belong= array_key_exists($id_responsable, $this->array_usuarios_prs);
        
        return $belong ? true : false;
    }
    
    /* si el id_usuario participa de la actividad */
    protected function if_participant_event($table, $id, $id_usuario) {
        $id_field= null;
        
        switch ($table) {
            case 'teventos':
                $id_field= 'id_evento';
                break;
            case 'ttareas':
                $id_field= 'id_tarea';
                break;            
            case 'tauditorias':
                $id_field= 'id_auditoria';
                break;
            default:
                $id_field= 'id_evento';
        }
        $sql= "select * from _tmp_treg_evento where id_usuario = $id_usuario and $id_field = $id";
        $result= $this->db_sql_show_error("if_participant_event({$table})", $sql);
        return $this->db_cant > 0 ? true : false;
    }
    
    protected function update_id_responsable($table= '_teventos', $id, $id_responsable, $if_belong_prs) {
        $user_check= boolean2pg($if_belong_prs ? false : true);
        $if_participant= null;
        $id_field= null;
        
        switch ($table) {
            case '_teventos':
                $id_field= 'id_evento';
                break;
            case '_ttareas':
                $id_field= 'id_tarea';
                break;            
            case '_tauditorias':
                $id_field= 'id_auditoria';
                break;
            default:
                $id_field= 'id_evento';
        }
 
        if ($this->if_origen_up) 
            $id_user_prs= $if_belong_prs ? $id_responsable : $this->id_destino_chief_prs;
        if ($this->if_origen_down) 
            $id_user_prs= $if_belong_prs ? $id_responsable : $this->id_origen_chief_prs;

        $sql= "update {$table} set id_responsable = $id_user_prs ";
        if ($table == "_teventos") 
            $sql.= ", funcionario= '$this->data_origen_chief_prs' ";
        $sql.= "where id = $id and id_responsable != $id_user_prs";        
        $result= $this->db_sql_show_error("update_id_responsable({$table})", $sql);
        
        if ($table == "_teventos" || $table == "_tauditorias" || $table == "_ttareas") {
            if ($this->if_origen_up) {
                if ($id_user_prs == $id_responsable) {
                    $sql= "update _treg_evento set user_check= $user_check where $id_field = $id and id_usuario = $id_user_prs ";
                } else {
                    $sql= "update _treg_evento set user_check= $user_check, id_usuario = $id_user_prs where $id_field = $id ";
                    $sql.= "and id_usuario = $id_responsable ";
                }
                $this->db_sql_show_error("update_id_responsable({$table})", $sql);
            } 
            if ($this->if_origen_down) {
                $if_participant= $this->if_participant_event($table, $id, $id_user_prs);
                if (!$if_participant) {
                    if ($id_user_prs == $id_responsable) 
                        $sql= "update _treg_evento set hide_synchro = true where $id_field = $id and id_usuario = $id_user_prs";
                    else {
                        $sql= "update _treg_evento set hide_synchro = true, id_usuario = $id_user_prs where $id_field = $id ";
                        $sql.= "and id_usuario = $id_responsable";
                    }
                    $this->db_sql_show_error("update_id_responsable({$table})", $sql);
        }   }   } 
    }
    
    public function update_responsable($table) {
        $sql= "select * from $table";
        $result= $this->db_sql_show_error("update_responsable({$table})", $sql);
        
        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) 
            $this->fix_responsable($table, $row['id'], $row['id_responsable']);
    }
    
    protected function fix_responsable($table, $id, $id_responsable) {
        $id_field= null;
        switch ($table) {
            case '_teventos':
                $id_field= 'id_evento';
                break;
            case '_ttareas':
                $id_field= 'id_tarea';
                break;            
            case '_tauditorias':
                $id_field= 'id_auditoria';
                break;
            default:
                $id_field= 'id_evento';
        }
        $sql= "select * from _tmp_treg_evento where $id_field = $id and id_usuario = $id_responsable";
        $result= $this->db_sql_show_error("fix_responsable({$table})", $sql);
       
        if (empty($this->db_cant)) {
            $row= $this->dblink->fetch_array($result);
            
            $this->add_to_tusuarios('tusuarios', 'id', $id_responsable);
            $this->update_id_responsable($table, $id, $id_responsable, false);
            return;
        }
 
        while ($row= $this->dblink->fetch_array($result)) {
            $flag_user= boolean($row['flag_user']);
            $flag_user_prs= boolean($row['flag_user_prs']);
            $flag_resp_prs= boolean($row['flag_resp_prs']);            
            $flag_resp_event= boolean($row['flag_resp_event']);
         
            $participant_prs= ($flag_user_prs || $flag_resp_prs) ? true : $this->if_belong_prs($row['id_responsable_flag']);
            $_flag_user= false;
            
            if (!$flag_user) {
                $this->add_to_tusuarios('tusuarios', 'id', $row['id_usuario']);
                $this->update_id_responsable($table, $row[$id_field], $row['id_responsable_flag'], $participant_prs);
                $flag_user= true;
                $_flag_user= true;
            } 
            
            if (!$_flag_user && (($flag_resp_event && $flag_user) && (!$flag_resp_prs || !$flag_user_prs))) 
                $this->update_id_responsable($table, $row[$id_field], $row['id_responsable_flag'], $participant_prs);
        }
    }  
    
    private function _fill_image_teventos($result) {
        if ($this->finalized_init)
            $this->array_tmp_eventos= array();

        $sql= null;
        $i= 0;
        $j= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            if (!$this->finalized_init && !empty($this->array_tmp_eventos[$row['id']])) {
                continue;
            }
            $this->array_tmp_eventos[$row['id']]= $row['id'];
            
            $sql.= "insert into _tmp_teventos ";
            $sql.= $this->if_mysql ? "() " : ""; 
            $sql.= "select *, null from teventos where id = {$row['id']}; ";  

            ++$j;
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) { 
                $this->db_multi_sql_show_error("_fill_image_teventos", $sql);
                $i= 0;
                $sql= null;                  
            }
            if ($this->signal != 'webservice' && $j >= $this->steep_max)
                break;
        }
        if ($sql) 
            $this->db_multi_sql_show_error("_fill_image_teventos", $sql);  
        return $j;
    }
    
    protected function fill_image_teventos() {
        $sql= "select *, null from teventos where id_archivo is null and (id_proceso = $this->id_origen or id_proceso = $this->id_destino) ";
        $sql.= "and ((periodicidad is null or periodicidad = 0) and (carga is null or carga = 0) and dayweek is null) ";
        if (!empty($this->cronos_cut) && empty($this->date_cutoff)) {
            $sql.= "and (cronos >= '$this->cronos_cut' and fecha_inicio_plan >= '$this->year-01-01 00:00') ";
        }    
        if (empty($this->cronos_cut) && !empty($this->date_cutoff)) {
            $sql.= "and (fecha_inicio_plan >= '$this->date_cutoff' ";
            $sql.= "or (cronos >= '$this->date_cutoff' and fecha_inicio_plan >= '$this->year-01-01 00:00')) ";
        }    
        if (!empty($this->cronos_cut) && !empty($this->date_cutoff)) {
            $sql.= "and (fecha_inicio_plan >= '$this->date_cutoff' ";
            $sql.= "or (cronos >= '$this->cronos_cut' and fecha_inicio_plan >= '$this->year-01-01 00:00')) ";
        }    
        $sql.= "order by fecha_inicio_plan asc, cronos asc ";

        $result= $this->db_sql_show_error('fill_image_teventos', $sql);
        $this->steep_current= $this->_fill_image_teventos($result);    
       
        $j= $this->_add_to_image_teventos_from_eventos();
        $this->steep_max+= $j;
        $this->steep_current+= $j;

        bar_progressCSS(2, "Carga de tabla (temporal imagen _teventos) ($this->db_cant registros) .... ", 1);  
    }
    
    protected function _add_to_image_teventos_from_eventos() {        
        $sql= "select distinct id_evento from _tmp_teventos where id_evento is not null";
        $result= $this->db_sql_show_error('add_to_export_teventos_from_proceso', $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            if (!$this->finalized_init && !empty($this->array_tmp_eventos[$row['id_evento']])) {
                continue;
            }
            $this->array_tmp_eventos[$row['id_evento']]= $row['id_evento'];

            if (isset($array_ids[$row['id_evento']])) 
                continue;
            $array_ids[$row['id_evento']]= $row['id_evento'];
            
            $sql.= "insert into _tmp_teventos ";
            $sql.= $this->if_mysql ? "() " : "";
            $sql.= "select *, true from teventos where id = {$row['id_evento']}; ";

            ++$j;
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) { 
                $this->db_multi_sql_show_error("add_to_export_teventos_from_proceso", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Segunda carga de tabla (temporal imagen _teventos)  ... ", $r);                    
            }
        }
        if ($sql) 
            $this->db_multi_sql_show_error("add_to_image_teventos_from_eventos", $sql);   
       
        bar_progressCSS(2, "Segunda carga de tabla (temporal imagen _teventos)  ... ", 1); 
        $this->dblink->free_result($result);
        return $j;            
    }    
    
    protected function fill_image_ttareas() {
        $sql= "insert into _tmp_ttareas ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct ttareas.* from ttareas, _tmp_teventos where ttareas.id = _tmp_teventos.id_tarea "; 
        if (!empty($this->cronos_cut)) 
            $sql.= "and ttareas.cronos >= '$this->cronos_cut' ";
        else {
            if (!empty($this->date_cutoff)) 
                $sql.= "and (ttareas.fecha_inicio_plan >= '$this->date_cutoff' or ttareas.cronos >= '$this->date_cutoff') ";
        }
        $result= $this->db_sql_show_error('fill_image_ttareas', $sql);  
        bar_progressCSS(2, "Carga de tabla (temporal imagen _ttareas) ($this->db_cant registros) .... ", 1);  
    }

    protected function _fill_image_tauditorias_ext() {
        $sql= "select id_auditoria from _tmp_tauditorias where id_auditoria is not null";
        $result= $this->db_sql_show_error('fill_image_ttareas', $sql); 
       
        while ($row= $this->dblink->fetch_array($result)) {
             $sql= "insert into _tmp_tauditorias ";
             $sql.= $this->if_mysql ? "() " : "";
             $sql.= "select tauditorias.* from tauditorias where id = {$row['id_auditoria']} ";  
             if (!empty($this->cronos_cut)) 
                 $sql.= "and tauditorias.cronos >= '$this->cronos_cut' ";
             else {
                 if (!empty($this->date_cutoff)) 
                     $sql.= "and tauditorias.cronos >= '$this->date_cutoff' ";                 
             }
             $result= $this->db_sql_show_error('_fill_image_tauditorias_ext', $sql); 
        }
    }
    
    protected function fill_image_tauditorias() {
        $sql= "insert into _tmp_tauditorias ";
        $sql.= $this->if_mysql ? "() " : "";
        $sql.= "select distinct tauditorias.* from tauditorias, _tmp_teventos where tauditorias.id = _tmp_teventos.id_auditoria "; 
        if (!empty($this->cronos_cut)) 
            $sql.= "and tauditorias.cronos >= '$this->cronos_cut' ";
        else {
            if (!empty($this->date_cutoff)) 
                $sql.= "and (tauditorias.fecha_inicio_plan >= '$this->date_cutoff' or tauditorias.cronos >= '$this->date_cutoff') ";            
        }
        $result= $this->db_sql_show_error('fill_image_tauditorias', $sql); 
        
        $this->_fill_image_tauditorias_ext();
        bar_progressCSS(2, "Carga de tabla (temporal imagen _tauditorias) ($this->db_cant registros) .... ", 1);  
    }
    
    protected function _insert_tmp_treg_evento($id, $id_responsable_ref, $array_usuarios) {
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? " union " : "";
            $sql.= "select * from treg_evento_$year where id_evento = $id ";
            $sql.= "and convert(situs using utf8) != convert('$this->destino_code' using utf8) ";
            $sql.= "and (hide_synchro = false or hide_synchro is null) ";
        }
        $sql.= "order by cronos desc; ";
        $result= $this->db_sql_show_error('_insert_tmp_treg_evento', $sql);

        $array_treg_usuarios= array();
        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            if (isset($array_treg_usuarios[$row['id_usuario']])) 
                continue;
            ++$i;
            $array_treg_usuarios[$row['id_usuario']]= $row;
        } 
        $this->dblink->free_result($result);
        
        if ($i == 0) 
            return null;
        
        $sql= null;
        $array_ids= array();
        foreach ($array_treg_usuarios as $row) {
            if (!empty($array_ids[setZero($row['id_usuario'])][setZero($row['id_evento'])][setZero($row['id_auditoria'])][setZero($row['id_tarea'])])) 
                continue; 
            $array_ids[setZero($row['id_usuario'])][setZero($row['id_evento'])][setZero($row['id_auditoria'])][setZero($row['id_tarea'])]= 1;

            $flag_user= array_key_exists($row['id_usuario'], $array_usuarios) ? true : false;
            $flag_user_prs= $flag_user && (int)$array_usuarios[$row['id_usuario']]['id_proceso'] == $this->id_destino ? true : false;
            $flag_resp_event= $row['id_usuario'] == $id_responsable_ref ? true : false;
            $flag_resp_prs= $row['id_usuario'] == $this->id_destino_chief_prs ? true : false;

            $flag_user= boolean2pg($flag_user);
            $flag_user_prs= boolean2pg($flag_user_prs);
            $flag_resp_prs= boolean2pg($flag_resp_prs);
            $flag_resp_event= boolean2pg($flag_resp_event);  
            
            $id_evento= setNULL($row['id_evento']);
            $id_evento_code= setNULL_str($row['id_evento_code']);
            $id_auditoria= setNULL($row['id_auditoria']);
            $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
            $id_tarea= setNULL($row['id_tarea']);
            $id_tarea_code= setNULL_str($row['id_tarea_code']);
            
            $id_responsable= setNULL($row['id_responsable']);
            
            $origen_data= setNULL_str($row['origen_data']);
            $observacion= setNULL_str($row['observacion']);
            $aprobado= setNULL_str($row['aprobado']);
            $rechazado= setNULL_str($row['rechazado']);
            $cumplimiento= setNULL($row['cumplimiento']);
            
            $compute= setNULL($row['compute']);
            $toshow= setNULL($row['toshow']);
            $user_check= boolean2pg($row['user_check']);
            $outlook= boolean2pg($row['outlook']);
            $reg_fecha= setNULL_str($row['reg_fecha']);
            $horas= setNULL($row['horas']);
            
            $sql.= "insert into _tmp_treg_evento (id, id_usuario, id_responsable, origen_data, aprobado, rechazado, cumplimiento, ";
            $sql.= "observacion, compute, toshow, user_check, outlook, reg_fecha, horas, cronos, situs, id_evento, id_evento_code, ";
            $sql.= "id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, id_responsable_flag, flag_user, flag_user_prs, ";
            $sql.= "flag_resp_prs, flag_resp_event) values ({$row['id']}, {$row['id_usuario']}, $id_responsable, $origen_data, ";
            $sql.= "$aprobado, $rechazado, $cumplimiento, $observacion, $compute, $toshow, $user_check, $outlook, $reg_fecha, $horas, ";
            $sql.= "'{$row['cronos']}', '{$row['situs']}', $id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, ";
            $sql.= "$id_auditoria_code, $id_responsable_ref, $flag_user, $flag_user_prs, $flag_resp_prs, $flag_resp_event); ";
        } 

        return $sql;
    }
    
    protected function fill_image_treg_evento() {
        $sql= "select * from _tusuarios";
        $result= $this->db_sql_show_error('fill_image_treg_evento', $sql);

        $array_usuarios= array();
        while ($row= $this->dblink->fetch_array($result)) {
            if (isset($array_usuarios[$row['id']])) 
                continue;
            $array_usuarios[$row['id']]= array('id'=>$row['id'], 'id_proceso'=>$row['id_proceso']);
        }

        $sql= "select id, id_tarea, id_auditoria, id_responsable, fecha_inicio_plan, fecha_fin_plan, cronos from _tmp_teventos";      
        $result= $this->db_sql_show_error('fill_image_treg_evento', $sql);
        $cant_j= $this->db_cant;
    
        $i= 0;
        $j= 0;  
        $sql= null;
        $this->dblink->data_seek($result);
        while ($row= $this->dblink->fetch_array($result)) {       
            $sql_reg= $this->_insert_tmp_treg_evento($row['id'], $row['id_responsable'], $array_usuarios);
            if (empty($sql_reg)) 
                continue;
            
            $sql.= $sql_reg;
            ++$i;
            ++$j;
            if ($i >= 250) {               
                $this->db_multi_sql_show_error("fill_image_treg_evento", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Actualiza (temporal imagen _treg_evento) ($j registros) .... ", $r); 
            }           
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("fill_image_treg_evento", $sql); 
        
        bar_progressCSS(2, "Actualiza (temporal imagen _treg_evento) .... ", 1);        
    }  
    
    /**
     * Correlaciona el id_tipo_evento segun el origen del lote
     */
    protected function fix_teventos($table) {
        if ($this->if_origen_down) 
            return;
        
        $sql= "select * from {$table}";
        $result= $this->db_sql_show_error("fix_teventos({$table})", $sql);
        $cant_j= $this->db_cant;
        
        $sql= null;
        $j= 0;
        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $id= $row['id'];
            $empresarial= setZero($row['empresarial']);
            
            if ((($this->origen_type <= _TIPO_GAE && $this->destino_type <= _TIPO_EMPRESA) 
                    && ($this->origen_type < $this->destino_type)) && ($empresarial == 5 || $empresarial == 6)) 
                $empresarial= 4;

            $sql.= "update {$table} set id_tipo_evento= null, id_tipo_evento_code= null, empresarial= $empresarial where id = $id; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("fix_teventos({$table})", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "fix_teventos($table) ... ", $r);
            }           
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("fix_teventos({$table})", $sql);
            
        $this->dblink->free_result($result);
        bar_progressCSS(2, "fix_teventos($table) ... ", 1);
    }
    
    protected function fix_field_toshow($table) {
        $sql= "select id from $table";
        $result= $this->db_sql_show_error("fix_field_toshow({$table})", $sql);
        $cant_j= $this->db_cant; 
        
        $sql= null;
        $j= 0;
        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            if (empty($row['empresarial']) || empty($row['toshow'])) 
                continue;
            $year= $table == "_tauditorias" ? $this->array_years[0][$row['id_code']][0] : $this->array_years[$row['id_code']][0][0];
            if (empty($year)) 
                continue;
            $id_field= $table == "_tauditorias" ? "id_auditoria" : "id_evento";
            
            $sql.= "update $table set empresarial= 0, toshow= 0 where id not in (";
            $sql.= "select $id_field from tproceso_eventos_$year where $id_field = {$row['id']} ";
            if ($this->if_origen_up) 
                $sql.= "and tproceso_eventos_$year.id_proceso = $this->id_destino); ";
            else 
                $sql.= "and tproceso_eventos_$year.id_proceso = $this->id_origen); ";
            
            ++$i;     
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("fix_field_toshow", $sql);
                $i= 0;
                $sql= null;
                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "Revizando tabla temporal($table) ... ", $r);                 
            }
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("fix_field_toshow", $sql);  
    }   
    
    /**
     * Elimina los registros redundantes
     */
    private function _purge_table($table) {
        global $array_dbtable;
        
        $sql= "select id, count(*) as cant from $table group by id";
        $result= $this->db_sql_show_error('purge_table', $sql);
        if (empty($this->db_cant)) 
            return;
        $nums= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $n= 0;

        $sql= null;        
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            $cant= (int)$row['cant'];
            if ($cant == 1) continue;
            --$cant;
            $n+= $cant;
            $sql.= "delete from $table where id = {$row['id']} limit $cant; ";
            
            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("purge_table($table)", $sql);
                if ($table == "_tusuarios") 
                    $array_dbtable['tusuarios']['size']= $array_dbtable['tusuarios']['size'] - $this->db_cant;
                $i= 0;
                $sql= null;
             
                $r= (float)($j) / $nums;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "purge_table($table) ... deleted($n) ... ", $r);                 
            }
        }

        if (!empty($sql)) {
            $this->db_multi_sql_show_error("purge_table($table)", $sql);  
            if ($table == "_tusuarios") 
                $array_dbtable['tusuarios']['size']= $array_dbtable['tusuarios']['size'] - $this->db_cant;
        } 
        bar_progressCSS(2, "purge_table($table) ... deleted($n) ... ", 1);  
        
        $this->dblink->free_result($result);
        return $j;
    }
    
    protected function purge_table($table, $id_field1= 'id', $id_field2= null, $id_field3= null, $id_field4= null, $id_field5= null) {
        global $array_dbtable;
        
        if ($id_field1 == 'id' && (is_null($id_field2) && is_null($id_field3) && is_null($id_field4) && is_null($id_field5)))
            return $this->_purge_table ($table);

        $j= 0;
        $sql= "select $id_field1 ";
        if (!empty($id_field2)) 
            $sql.= ", $id_field2 ";
        if (!empty($id_field3)) 
            $sql.= ", $id_field3 ";
        if (!empty($id_field4)) 
            $sql.= ", $id_field4 ";
        if (!empty($id_field5)) 
            $sql.= ", $id_field5 ";
        $sql.= ", cronos from $table order by cronos desc";
        $result= $this->db_sql_show_error('purge_table', $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $array_ids= array();
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $value1= !empty($row[$id_field1]) ? $row[$id_field1] : 0;
            $value2= $id_field2 ? (!empty($row[$id_field2]) ? $row[$id_field2] : 0) : 0;
            $value3= $id_field3 ? (!empty($row[$id_field3]) ? $row[$id_field3] : 0) : 0;
            $value4= $id_field4 ? (!empty($row[$id_field4]) ? $row[$id_field4] : 0) : 0;
            $value5= $id_field5 ? (!empty($row[$id_field5]) ? $row[$id_field5] : 0) : 0;
            
            if (empty($array_ids[$value1][$value2][$value3][$value4][$value5])) {
                ++$j;
                $array_ids[$value1][$value2][$value3][$value4][$value5]= $row['cronos'];
            } else 
                continue;
            
            $value1= setNULL_equal_sql($row[$id_field1]);
            $value2= $id_field2 ? setNULL_equal_sql($row[$id_field2]) : null;
            $value3= $id_field3 ? setNULL_equal_sql($row[$id_field3]) : null;
            $value4= $id_field4 ? setNULL_equal_sql($row[$id_field4]) : null;
            $value5= $id_field5 ? setNULL_equal_sql($row[$id_field5]) : null;
            
            $sql.= "delete from $table where ";
                $sql.= "$id_field1 $value1 ";
                if (!empty($id_field2)) 
                    $sql.= "and $id_field2 $value2 ";
                if (!empty($id_field3)) 
                    $sql.= "and $id_field3 $value3 ";
                if (!empty($id_field4)) 
                    $sql.= "and $id_field4 $value4 ";
                if (!empty($id_field5)) 
                    $sql.= "and $id_field5 $value5 ";
            $sql.= "and cronos < '{$row['cronos']}'; ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("purge_table($table)", $sql);
                if ($table == "_tusuarios") $array_dbtable['tusuarios']['size']= $array_dbtable['tusuarios']['size'] - $this->db_cant;
                $i= 0;
                $sql= null;
             
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                
                bar_progressCSS(2, "purge_table($table) ... ", $r);                 
            }
        }

        if (!empty($sql)) {
            $this->db_multi_sql_show_error("purge_table($table)", $sql);  
            if ($table == "_tusuarios") 
                $array_dbtable['tusuarios']['size']= $array_dbtable['tusuarios']['size'] - $this->db_cant;
        } 
        bar_progressCSS(2, "purge_table($table) ... ", 1);  
        
        $this->dblink->free_result($result);
        return $j;
    } 
    
    protected function set_export_procedure() {
        global $array_dbtable;
        
        if ($array_dbtable['teventos']['export']) {
            $sql= "select max(fecha_inicio_plan), max(cronos) from _tmp_teventos where flag_id_evento is null";
            $result= $this->db_sql_show_error('set_export_procedure', $sql);
            $row= $this->dblink->fetch_array($result);
            $this->date_cutover= $row[0];        
            $this->cronos_cut= $row[1]; 
            $this->finalized= $this->steep_current <= $this->steep_max ? true : false;
        } else 
            $this->finalized= true;
        
        if (!empty($this->date_cutover) && !empty($this->cronos_cut)) {
            if ($array_dbtable['teventos']['export'] && $this->steep_current == $this->steep_max) {
                $sql= "select count(*) from teventos where (id_proceso = $this->id_origen or id_proceso = $this->id_destino) ";
                $sql.= "and (fecha_inicio_plan > '$this->date_cutover' ";
                $sql.=  "or (fecha_inicio_plan < '$this->date_cutover' and cronos > '$this->cronos_cut'))";

                $result= $this->db_sql_show_error('set_export_procedure', $sql);
                $row= $this->dblink->fetch_array($result);   
                $this->finalized= !empty($row[0]) ? false : true;
            } else 
                $this->finalized= true; 
        }
        
        if ($this->finalized) {
            $this->steep_current= null;
            $this->cronos_cut= null;
        }       
    } 
    
    protected function get_usuarios_prs() {
        $sql= "select id from tusuarios where id_proceso = $this->id_destino";
        $result= $this->db_sql_show_error('get_usuarios_prs', $sql);
        
        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$i;
            $this->array_usuarios_prs[$row['id']]= array('id'=>$row['id'], 'noIdentidad'=>$row['noIdentidad']);
        }
        return $i;
    }    
       
}    
