<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";
include_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/code.class.php";
include_once _ROOT_DIRIGER_DIR."php/class/tabla_anno.class.php";


class TbaseLote extends Tfile {

    protected $dblink;
    protected $if_mysql;
    public $current_field;

    public $id_destino, 
            $id_destino_code, 
            $id_destino_escenario, 
            $id_destino_escenario_code, 
            $destino_name, 
            $destino_type;
    public $id_origen,  
            $id_origen_code,  
            $id_origen_escenario,  
            $id_origen_escenario_code,  
            $origen_name,  
            $origen_type;
    public $origen_motor_db;
    public $if_origen_up, 
            $if_origen_down;
    
    public $id_origen_chief_prs;
    public $data_origen_chief_prs;
    public $id_destino_chief_prs;
    
    public $chain_procesos;
    protected $array_dbtable;
    public $cant_used_tables;
    protected $db_cant;
    protected $db_id;

    protected $id_NO_LOCAL_proceso;
    protected $id_NO_LOCAL_proceso_code;
    protected $situs;

    public $date_cutoff;
    public $date_cutover;
    
    public $array_auditorias,
            $array_eventos,
            $array_tareas,
            $array_tmp_eventos;  

    public $cronos_cut; // cronos del ultimo evento del ultimo lote generado
    public $steep_current;
    public $finalized,
           $finalized_init; 
    public $steep_max;
    
    public $cronos_under;  // cronos que le corresponde al primer evento a colocar en el lote;
    
    public $array_years,
            $year_init,
            $year_end,
            $use_year;
    
    public $tb_filter;
    
    public $last_time_tables;  // los tiempos maximo de cronos en los que termina cada tabla
    
    public function GetDB() {
        return $this->dblink;
    }
    public function SetDB($clink) {
        $this->dblink = $clink;
    }
    
    public function __construct($clink= null) {
        global $config;
                
        $this->if_mysql= $_SESSION['_DB_SYSTEM'] == "mysql" ? true : false;

         if (!empty($clink)) {
             Tbase::__construct($clink);
             $this->clink= $clink;
             $this->dblink= $this->clink;

         } else {
            $this->dblink= new DBServer($_SESSION['db_ip'], $_SESSION['db_name'], $_SESSION['db_user'], $_SESSION['db_pass'], true);
            $this->dblink= empty($this->dblink->error) ? $this->dblink : false;
         }

        Tfile::__construct($this->dblink);

         $this->id_NO_LOCAL_proceso= $config->local_proceso_id;
         $this->id_NO_LOCAL_proceso_code= get_code_from_table('tprocesos', $this->id_NO_LOCAL_proceso, $this->dblink);
         $this->situs= $config->location;
         $this->cant_used_tables= 0;
         $this->origen_motor_db= null;
         
         $this->date_cutoff= date('Y').'-01-01';
         $this->date_cutover= null;
         
         $this->steep_current= 0;
         $this->steep_max= !empty($_SESSION["_max_register_block_lote"]) ? $_SESSION["_max_register_block_lote"] : 2500;

         $this->tb_filter= false;
     }

    public function _close() {
        $this->dblink->close();
    }

    public function db_sql_show_error($function, $sql, $stop_by_error= true) {
         $this->error= null;
         $this->db_cant= 0;
         $result= null;

        if (!is_null($sql)) {
             $result= @$this->dblink->query($sql);                   
        }  else {
            if ($_SESSION['debug'] === 'yes') {
                if ($_SESSION['output_signal'] != 'shell') 
                    writeLog(date('Y-m-d H:i'), "<p>OK CLASS: $this->className; FUNCTION: $function <br> SQL:".textparse($sql)."</p>", $this->divout);
            }

            $this->error= null;
            return null;
        }

         if (!$result && !is_null($sql)) {
             $errno= $this->dblink->errno();

             $this->error= "<br>CLASS: $this->className; <br> FUNCTION: $function <br> SQL:$sql  <br> ERROR: ";
             $this->error.= $this->dblink->error(). "\n";

             if (((strpos($function, 'refresh_t_db_id') !== false && ($this->if_mysql && $errno == 1054)) || ($this->if_mysql && $errno == 1062))
                || ((strpos($function, 'refresh_t_db_id') !== false || strpos($function, '_insert_in_db') !== false)
                     && (stripos($this->error, "duplicate") !== false || stripos($this->error, "duplicada") !== false
                     || stripos($this->error, "no existe la columna") !== false))) {

/* TEST */     //     if ($_SESSION['debug'] === 'yes') echo $sql."<BR><BR>";
             } else {
                    if ($this->signal == 'webservice' && !empty($this->error))
                        $this->error.". ERROR_WEB:6"; 
                        
                    if ($_SESSION['debug'] === 'yes') {
                        if ($stop_by_error) 
                            die($this->error."<BR/><BR/>");
                        else 
                            echo "<br/> $this->error<BR/><BR/>";
                    } else {
                        $text= textparse($this->error);
                        if ($_SESSION['output_signal'] != 'shell') 
                            writeLog(date('Y-m-d H:i'), json_encode("<div class=\'alert alert-danger text\'>$text</div>",JSON_PRETTY_PRINT), $this->divout);
                        else 
                            echo "\nERROR:".date('Y-m-d H:i')." ---> {$text}\n";
            }   }
        } else {
            if (!is_null($sql)) {
                $_sql= !is_null($sql) ? strtolower(substr($sql, 0,8)) : null;
                if (strpos($_sql, 'insert') !== false || strpos($_sql, 'update') !== false || strpos($_sql, 'delete') !== false)
                    $this->db_cant= $this->dblink->affected_rows($result); 
                else 
                    $this->db_cant= $this->dblink->num_rows($result);
            }  
            if ($_SESSION['debug'] === 'yes') {
                if ($_SESSION['output_signal'] != 'shell')
                    writeLog(date('Y-m-d H:i'), "<p>OK CLASS: $this->className; FUNCTION: $function <br> SQL:".textparse($sql)."</p>", $this->divout);
                else 
                    echo "<br>OK CLASS: $this->className; FUNCTION: $function <br> SQL: EMPTY<BR>";
            }
        }

        return $result;
    }

    public function db_multi_sql_show_error($function, $sql, $stop_by_error= true) {        
        $this->error= null;
        $this->db_cant= 0;
        $result= null;
        
        if (!empty($sql))
            $result= @$this->dblink->multi_query($sql);
        else {
            if ($_SESSION['debug'] === 'yes') {
                if ($_SESSION['output_signal'] != 'shell') 
                    writeLog(date('Y-m-d H:i'), "<p>OK CLASS: $this->className; FUNCTION: $function <br> SQL:".textparse($sql)."</p>", $this->divout);
            }
            $this->error= null;
            return null;
        }

        if (!$result) {
            $errno= $this->dblink->errno();

            $this->error= "<br>CLASS: $this->className; <br> FUNCTION: $function <br> SQL: $sql <br> ERROR: ";
            $this->error.= $this->dblink->error(). "\n";

            if (!is_null($this->error)) die("$this->error");
            
            if (((strpos($function, 'refresh_t_db_id') !== false && ($this->if_mysql && $errno == 1054)) || ($this->if_mysql && $errno == 1062))
                || ((strpos($function, 'refresh_t_db_id') !== false || strpos($function, '_insert_in_db') !== false)
                    && (stripos($this->error, "duplicate") !== false || stripos($this->error, "duplicada") !== false
                        || stripos($this->error, "no existe la columna") !== false))) {

                /* TEST */     //     if ($_SESSION['debug'] === 'yes') echo $sql."<BR><BR>";
            } else {
                if ($this->signal == 'webservice' && !empty($this->error))
                    $this->error.". ERROR_WEB:6"; 
                    
                if ($_SESSION['debug'] === 'yes') {
                    if ($stop_by_error) die($this->error."<BR><BR>");
                    else echo "<br/> $this->error<BR><BR>";
                } else {
                    $text= substr(trim($this->error), 0, 3072);
                    if ($_SESSION['output_signal'] != 'shell') {
                        $text= textparse($text);
                        writeLog(date('Y-m-d H:i'), json_encode("<div class=\'alert alert-danger text\'>$text</div>", JSON_PRETTY_PRINT), $this->divout);
                    } else 
                        echo "\nERROR:".date('Y-m-d H:i')." ---> {$text}\n";
                }
            }
        } else {
            if ($_SESSION['debug'] === 'yes') {
                if ($_SESSION['output_signal'] != 'shell') 
                    writeLog(date('Y-m-d H:i'), "<p>OK CLASS: $this->className; FUNCTION: $function <br> SQL:".textparse($sql)."</p>", $this->divout);
                else 
                    echo "<br>OK CLASS: $this->className; FUNCTION: $function <br> SQL:$sql<BR>";
            }
        }

        return $result;
    }

    public function get_last_date_synchronization($action= 'export', $finalized= null) {
        $action= !is_null($action) ? $action : 'export';
        
        $this->last_time_tables['_tdeletes']= array(null, null, null);
        $this->last_time_tables['_treg_tarea']= array(null, null, null);
        $this->last_time_tables['_treg_evento']= array(null, null, null);
        $this->last_time_tables['_tproceso_eventos']= array(null, null, null);
        $this->last_time_tables['_tusuario_eventos']= array(null, null, null);
        
        $this->last_time_tables['_treg_objetivo']= array(null, null, null);
        $this->last_time_tables['_treg_inductor']= array(null, null, null);
        $this->last_time_tables['_treg_perspectiva']= array(null, null, null);
        $this->last_time_tables['_treg_real']= array(null, null, null);
        $this->last_time_tables['_treg_plan']= array(null, null, null);        
        $this->last_time_tables['_tinductor_eventos']= array(null, null, null);       
        
        $sql= "select tsincronizacion.*, nombre from tsincronizacion, tprocesos where action = '$action' ";
        if (!is_null($finalized))
            $sql.= "and finalized = ". boolean2pg($finalized)." ";
        if (!empty($this->id_destino) || !empty($this->id_origen)) {
            if ($action == 'export') {
                $sql.= !empty($this->id_destino) ? "and (tprocesos.id = $this->id_destino " : " ";
                $sql.= "and ".convert_to("tprocesos.codigo", "utf8")." = ".convert_to("tsincronizacion.destino", "utf8");
                $sql.= !empty($this->id_destino) ? ") " : "";
            }
            if ($action == 'import') {
                $sql.= !empty($this->id_origen) ? "and (tprocesos.id = $this->id_origen " : " ";
                $sql.= "and ".convert_to("tprocesos.codigo", "utf8")." = ".convert_to("tsincronizacion.origen", "utf8");
                $sql.= !empty($this->id_origen) ? ") " : " "; 
        }   }
        $sql.= "and (tb_filter is null or (tb_filter is not null and finalized = false)) ";
        $sql.= "order by tsincronizacion.cronos desc limit 1";

        $result= Tbase::do_sql_show_error('get_last_date_synchronization', $sql);
        $cant= $this->clink->num_rows($result);
        $row= $this->clink->fetch_array($result);
        if (empty($cant)) {
            $this->finalized= true;
            return null;
        }
        
        $this->cronos_cut= !empty($row['cronos_cut']) ? $row['cronos_cut'] : null;
        $this->finalized= boolean($row['finalized']);
        $this->date_cutoff= !empty($row['date_cutoff']) ? $row['date_cutoff'] : null;
        $this->date_cutover= !empty($row['date_cutover']) ? $row['date_cutover'] : null;
        $this->steep_current= !empty($row['steep_current']) ? $row['steep_current'] : null;        
        
        $this->last_time_tables['_tdeletes']= array($row['tdeletes'], null, null);
        $this->last_time_tables['_treg_tarea']= array($row['date_treg_tarea'], null, null);
        $this->last_time_tables['_treg_evento']= array($row['date_treg_evento'], null, null);
        $this->last_time_tables['_tproceso_eventos']= array($row['date_tproceso_eventos'], null, null);
        $this->last_time_tables['_tusuario_eventos']= array($row['date_tusuario_eventos'], null, null);
        
        $this->last_time_tables['_treg_objetivo']= array($row['date_treg_objetivo'], null, null);
        $this->last_time_tables['_treg_inductor']= array($row['date_treg_inductor'], null, null);
        $this->last_time_tables['_treg_perspectiva']= array($row['date_treg_perspectiva'], null, null);
        $this->last_time_tables['_treg_real']= array($row['date_treg_real'], null, null);
        $this->last_time_tables['_treg_plan']= array($row['date_treg_plan'], null, null);
        $this->last_time_tables['_tinductor_eventos']= array($row['date_tinductor_eventos'], null, null);
        
        return $row['cronos'];
    }    
    
    /*
     * Solo para las prueba
     */
    protected function debug_listar_table($table) {
        $sql= "select *from $table";
        $result= $this->db_sql_show_error('debug_listar_table($table)', $sql);
        
        $j= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$j;
            echo "<br/><br/>";
            print_r($row);
        }
        echo "<br/><br/><br/> =====> {$table} == {$j}"; 
        exit;         
    }
    
    protected function set_array_years($table= "_teventos") {
        $table= !is_null($table) ? $table : "_teventos";
        $sql= "select id, id_tarea, id_auditoria, id_responsable, fecha_inicio_plan, fecha_fin_plan, cronos from $table";      
        $result= $this->db_sql_show_error('set_array_years', $sql);

        $this->create_array_years($result);        
    }
    
    protected function  create_array_years($result) {
        if (isset($this->array_years)) unset($this->array_years);
        $this->array_years= array();
        $this->cronos_under= $this->cronos_cut;
        
        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            ++$i;
            $id_tarea= !empty($row['id_tarea_code']) ? $row['id_tarea_code'] : 0;
            $id_auditoria= !empty($row['id_auditoria_code']) ? $row['id_auditoria_code'] : 0;
            if (is_null($this->cronos_under) || (!empty($this->cronos_under) && strtotime($this->cronos_under) > strtotime($row['cronos']))) 
                $this->cronos_under= $row['cronos'];

            $year_init= (int)date('Y', strtotime($row['fecha_inicio_plan']));
            $year_init= $year_init >= 2017 ? $year_init : 2017;  
            $year_end= (int)date('Y', strtotime($row['fecha_fin_plan']));
            $year_end= $year_end >= 2017 ? $year_end : 2017;            

            if (empty($this->year_init) || $year_init < $this->year_init) 
                $this->year_init= $year_init;
            if (empty($this->year_end) || $year_end > $this->year_end) 
                $this->year_end= $year_end;

            $this->array_years[$row['id_code']][$id_auditoria][$id_tarea]= $year_init;
            if (!empty($id_auditoria)) 
                $this->array_years[0][$id_auditoria][0]= $year_init;
            if (!empty($id_tarea)) 
                $this->array_years[0][0][$id_tarea]= $year_init;
        }
        $this->year= !empty($this->year) ? $this->year : $this->year_init;

        if (empty($this->year_init)) {
            $this->year= (int)date('Y');
            $this->year_init= $this->year;
            $this->year_end= $this->year;
        }
      
        $this->test_table_year();
        
        return $i;
    }

    private function test_table_year() {
        for ($year= $this->year_init; $year <= (int)$this->year_end; $year++)
            $obj= new Ttabla_anno($this->dblink, $year);
    }
      
    public function buildTable($table, $year= null) {
        $_table= $table;
        $year= !empty($year) ? $year : $this->year;
        $if_table_exist= true;

        if ($table == "tmp_tprocesos") 
            $_table= "tprocesos";
        if ($table == "tmp_teventos") 
            $_table= "teventos";
        if ($table == "tmp_tauditorias") 
            $_table= "tauditorias";
        if ($table == "tmp_ttareas") 
            $_table= "ttareas";

        if ($table == "treg_evento" || $table == "tmp_treg_evento") {
            $_table= "treg_evento_$year";
            $if_table_exist= $this->dblink->if_table_exist($_table);
        }
        if ($table == "tusuario_eventos" || $table == "tmp_tusuario_eventos") {
            $_table= "tusuario_eventos_$year";
            $if_table_exist= $this->dblink->if_table_exist($_table);
        } 
        if ($table == "tproceso_eventos" || $table == "tmp_tproceso_eventos") {
            $_table= "tproceso_eventos_$year";
            $if_table_exist= $this->dblink->if_table_exist($_table);
        }

        if (!$if_table_exist) 
            return null;

        $fields= $this->dblink->fields($_table);
        $nums_fields= count($fields);        
         
        $sql= "CREATE TEMPORARY TABLE _{$table} (\r\n";
        $i= 0;      
        foreach ($fields as $field) {
            if ($i == 1 && $this->action == 'import') 
                $sql.= " _idx ".field2pg("INTEGER(11)")." DEFAULT NULL, ";
            $item= showFieldSQL($field);
            if (stripos($item, 'DEFAULT') === false) 
                $item= str_replace('NOT NULL', 'DEFAULT NULL', $item); 
            $sql.= $item;
            ++$i;
            $sql.= $i < $nums_fields ? ", \r\n" : "";
        }
        
        if ($this->action == 'export') {
            $int= $this->if_mysql ? "int(11)" : "bigint";
            $bool= $this->if_mysql ? "tinyint(1)" : "boolean";  
            
            if ($table == "tmp_treg_evento") {
                $sql.= ", \r\n";
                $sql.= "flag_user $bool DEFAULT NULL, \r\n";          // Presente en _tusuarios
                $sql.= "flag_user_prs $bool DEFAULT NULL, \r\n";      // Presente en _tusaurios y pertenece al proceso destino
                $sql.= "flag_resp_prs $bool DEFAULT NULL, \r\n";      // responsable del proceso destino
                $sql.= "id_responsable_flag $int DEFAULT NULL, \r\n"; // id del responsable del evento
                $sql.= "flag_resp_event $bool DEFAULT NULL \r\n";     // responsable del evento            
            }
            if ($table == 'teventos' || $table == 'tmp_teventos') {
                $sql.= ", \r\n";                                      // true ==>  El evento fue agregado por tener desde un id_evento not null
                $sql.= "flag_id_evento $bool DEFAULT NULL \r\n";      //           en la seleccion inicial                                               
            }
        }    
        $sql.= "\r\n) ";
        $sql.= $this->if_mysql ? "CHARSET=utf8;\r\n" : "WITH (oids = true);\r\n";

        $result= $this->db_sql_show_error("buildTable(_{$table})", $sql);
        return $this->error;
    }    
}
       
?>