<?php
/**
 * Description of basesBond
 *
 * @author mustelier
 */

include_once "base.class.php";

class TbaseBond  extends TbaseLote {
    protected $array_id_primary;
    private $prs_down_chain;
    
    public function __construct($dblink) {
        TbaseLote::__construct($dblink);
        $this->dblink= $dblink;
    }    
    
    private function set_prs_down_chain() {
        $obj_prs= new Tproceso($this->dblink);
        $obj_prs->SetIdProceso($_SESSION['id_proceso']);
        $obj_prs->Set();
        $obj_prs->set_acc(3);
        $obj_prs->SetIdUsuario(null);
        $obj_prs->SetIdResponsable(null);
        $obj_prs->get_procesos_down_cascade();
        $this->array_procesos_down= $obj_prs->array_cascade_down;

        $i= 0;
        $this->prs_down_chain= null;
        foreach ($this->array_procesos_down as $key => $prs) {
            if ($i == 0) 
                $this->prs_down_chain= "($key";
            if ($i > 0) 
                $this->prs_down_chain.= ",$key";
            ++$i;
        }
        if (!is_null($this->prs_down_chain)) 
            $this->prs_down_chain.= ")";        
    }    
    
    /*
     *  Actualiza la BD desde las tablas temporales. Inserta registros o actualiza los registros existentes
     */
    protected function update_t_key($table, $id_code) {
        $this->table= $table;
        $array_id= array();
        $id= null;

        if (isset($this->array_new_id)) {
            unset($this->array_new_id);
            $this->array_new_id = array();
        }

        $result= $this->refresh_t_table_from_db('id', 'id_code');
        $this->insert_idx('id_code');

        $i= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            $id= $row['id'];

            if (empty($id)) {
                $id= $this->_insert_in_db('id_code',$row['id_code']);

                if (!empty($id)) {
                    $this->update_idx($id, $row['id_code']);
                    $this->array_new_id[]= array('id'=>$id, 'id_code'=>$row['id_code'], 'id_proceso'=>$row['id_proceso'], 
                                                 'id_proceso_code'=>$row['id_proceso_code']);
                }
            } else 
                $array_id[]= array('id'=>$id, 'id_code'=>$row['id_code']);

            if (strcmp($this->table, "tprocesos") == 0) 
                $this->id_origen= $id;
            if (strcmp($this->table, "tescenarios") == 0) 
                $this->id_origen_escenario= $id;

            function_progressCSS(++$i, "Actualizando BD desde tabla temporal (update_t_key: $this->table)", $this->array_size_tables[$this->table], 2);
        }

        $this->refresh_t_db_id($id_code);

        foreach ($array_id as $array) {
            $this->_update_in_db('id', $array['id'], false, 'id_code', $array['id_code'], true);
            $this->update_idx($array['id'], $array['id_code']);
        } 
        
        if (strcmp($this->table, "tprocesos") == 0) 
            $this->update_tprocesos ($array_ids);     
    }  
    
    private function update_tprocesos($array_ids) {
        reset($array_ids);
        foreach ($array_ids as $array) {
            if ($array['id'] == $_SESSION['local_proceso_id'])
                continue;
            $sql= "update tprocesos set id_entity= {$_SESSION['local_proceso_id']}, id_entity_code= '{$_SESSION['local_proceso_id_code']}' ";
            $sql.= "where id = {$array['id']}";
            $result= $this->db_sql_show_error('update_real_table', $sql);
        }
    }
    
    /**
     * se actualiza la tabla $table a partir de la $_table utilizando el campo $id
     * actualiza el campo $t_id de $table con el valor del campo id de $_table
     */
    /**
     * se actualiza la tabla $table a partir de la $_table utilizando el campo $id
     * actualiza el campo $t_id de $table con el valor del campo id de $_table
     */
    protected function update_real_table_year($table, $_table, $id, $t_id= null) {
        $t_id= !is_null($t_id) ? $t_id : $id;
        $idx_table= "_".$table;

        $sql= null;
        for ($year= $this->year_init; $year < $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? "union " : "";
            $sql.= "select distinct t2.id as _id, id_evento_code, id_auditoria_code, id_tarea_code ";
            $sql.= "from {$table}_{$year} as t2, _tidx where t2.id = _tidx.id and '$idx_table' = _tidx._table ";
        }
        $result= $this->db_sql_show_error('update_real_table_year', $sql);
        $size= $this->dblink->num_rows($result);

        $i= 0; 
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $id_i= $row['_id'];
            $id_evento= $row['id_evento_code'] ? $row['id_evento_code'] : 0;
            $id_auditoria= $row['id_auditoria_code'] ? $row['id_auditoria_code'] : 0;
            $id_tarea= $row['id_tarea_code'] ? $row['id_tarea_code'] : 0;
            $year= $this->array_years[$id_evento][$id_auditoria][$id_tarea];
            if (empty($year)) 
                continue;
            
            if ($this->if_mysql) {
                $sql.= "update {$table}_{$year} as t1, $_table as t2 set t1.$t_id = t2.id ";
            } else {
                $sql.= "update {$table}_{$year} as t1 set $t_id = t2.id from $_table as t2 ";
            }
            $sql.= "where t1.{$id}_code is not null and t1.id = $id_i ";
            $sql.= "and ".convert_to("t1.{$id}_code", "utf8")." = ".convert_to("t2.id_code", "utf8")."; ";

            ++$i; 
            ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("update_real_table_year($table)", $sql);
                $i= 0;
                $sql= null;

                function_progressCSS($j, "Actualizando en BD campo $id (update_real_table_year: {$table}_{$year})", $size, 2);
            }
        }

        if (!empty($sql)) {
            $this->db_multi_sql_show_error("update_real_table_year($table)", $sql);
            function_progressCSS($j, "Actualizando en BD campo $id (update_real_table_year: $table)", $size, 2);
        }
    }
    
    protected function update_real_table($table, $_table, $id, $t_id= null) {
        if ($this->use_year) {
            $this->update_real_table_year($table, $_table, $id, $t_id);
            return;
        }
        
        $t_id= !is_null($t_id) ? $t_id : $id;
        $idx_table= "_".$table;
        
        // salen los id de $table que fueron insertados o modificados 
        $sql= "select t2.id as _id from $table as t2, _tidx where t2.id = _tidx.id and '$idx_table' = _tidx._table";
        $result= $this->db_sql_show_error('update_real_table', $sql);
        $size= $this->dblink->num_rows($result);

        $i= 0; 
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $id_i= $row['_id'];

            if ($this->if_mysql) {
                $sql.= "update $table as t1, $_table as t2 set t1.$t_id = t2.id ";
            } else {
                $sql.= "update $table as t1 set $t_id = t2.id from $_table as t2 ";
            }
            $sql.= "where t1.{$id}_code is not null and t1.id = $id_i ";

            if (strcmp($_table, 'tpoliticas') != 0)
                $sql.= "and ".convert_to("t1.{$id}_code", "utf8")." = ".convert_to("t2.id_code", "utf8")."; ";
            else {
                $sql.= "and ((".convert_to("t1.{$id}_code", "utf8")." = ".convert_to("t2.id_code", "utf8")." and if_inner = ".boolean2pg(1).") ";
                $sql.= "or (t1.$id = t2.id and if_inner is null)); ";
            }

            ++$i; 
            ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("update_real_table($table)", $sql);
                $i= 0;
                $sql= null;

                function_progressCSS($j, "Actualizando en BD campo $id (update_real_table: $table)", $size, 2);
            }
        }

        if (!empty($sql)) {
            $this->db_multi_sql_show_error("update_real_table($table)", $sql);
            function_progressCSS($j, "Actualizando en BD campo $id (update_real_table: $table)", $size, 2);
        }
    }
    
    /*
     * actualiza el campo $id de la tabla $table
     */
    protected function update_real_usuario_year($table, $id) {
        $idx_table= "_".$table;
        
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? "union " : "";
            $sql.= "select {$table}_{$year}.id as _id, id_evento_code, id_auditoria_code, id_tarea_code ";
            $sql.= "from {$table}_{$year}, _tidx where {$table}_{$year}.id = _tidx.id and '$idx_table' = _tidx._table ";
        }
        $result= $this->db_sql_show_error('update_real_usuario_year', $sql);
        $size= $this->dblink->num_rows($result);

        $i= 0; 
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $id_i= $row['_id'];
            $id_evento= $row['id_evento_code'] ? $row['id_evento_code'] : 0;
            $id_auditoria= $row['id_auditoria_code'] ? $row['id_auditoria_code'] : 0;
            $id_tarea= $row['id_tarea_code'] ? $row['id_tarea_code'] : 0;
            $year= $this->array_years[$id_evento][$id_auditoria][$id_tarea];
            if (empty($year)) 
                continue;
            
            if ($this->if_mysql) {
                $sql.= "update {$table}_{$year} as t1, _tusuarios set t1.$id= _tusuarios.id ";
            } else {
                $sql.= "update {$table}_{$year} as t1 set $id= _tusuarios.id from _tusuarios ";
            }
            $sql.= "where t1.$id is not null and t1.id = $id_i and t1.$id = _tusuarios._idx; ";

            ++$i; 
            ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("update_real_usuario_year($table)", $sql);
                $i= 0;
                $sql= null;

                function_progressCSS($j, "Actualizando en BD referencia a usuario ($id) (update_real_usuario_year: $table)", $size, 2);
            }
        }

        if (!empty($sql)) {
            $this->db_multi_sql_show_error("update_real_usuario($table)", $sql);
            function_progressCSS($j, "Actualizando en BD referencia a usuario ($id) (update_real_table: $table)", $size, 2);
        }
    }
        
    protected function update_real_usuario($table, $id) {
        if ($this->use_year) {
            $this->update_real_usuario_year($table, $id);
            return;
        }        
        
        $idx_table= "_".$table;

        $sql= "select $table.id as _id from $table, _tidx where $table.id = _tidx.id and '$idx_table' = _tidx._table ";
        $result= $this->db_sql_show_error('update_real_usuario', $sql);
        $size= $this->dblink->num_rows($result);

        $i= 0; 
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            $id_i= $row['_id'];

            if ($this->if_mysql) {
                $sql.= "update $table, _tusuarios set $table.$id= _tusuarios.id ";
            } else {
                $sql.= "update $table set $id= _tusuarios.id from _tusuarios ";
            }
            $sql.= "where $table.$id is not null and $table.id = $id_i and $table.$id = _tusuarios._idx; ";

            ++$i; ++$j;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("update_real_usuario($table)", $sql);
                $i= 0;
                $sql= null;

                function_progressCSS($j, "Actualizando en BD referencia a usuario ($id) (update_real_table: $table)", $size, 2);
            }
        }

        if (!empty($sql)) {
            $this->db_multi_sql_show_error("update_real_usuario($table)", $sql);
            function_progressCSS($j, "Actualizando en BD referencia a usuario ($id) (update_real_table: $table)", $size, 2);
        }
    }
    
    public function set_foreign_key_check($id) {
        if ($_SESSION['_DB_SYSTEM'] != "mysql") return null;
        $sql= "SET FOREIGN_KEY_CHECKS=$id";
        $this->db_sql_show_error('set_foreign_key_check', $sql);
    }

    public function set_table_struct($array_names) {
        $k= 0;
        if (isset($this->array_dbstruct)) unset($this->array_dbstruct);

        foreach ($array_names as $cell) {
            $this->array_dbstruct[$k]['name']= $cell['name'];
            $this->array_dbstruct[$k]['type']= $cell['type'];
            $this->array_dbstruct[$k]['length']= $cell['length'];
            ++$k;
        }
    }

    protected function remove_idx() {
        $_table= "_".$this->table;
        $sql= "ALTER TABLE $_table DROP COLUMN _idx";
        $this->db_sql_show_error('remove_idx', $sql);
    }

    public function create_idx() {
        $sql= "DROP TABLE IF EXISTS _tidx";
        $this->db_sql_show_error('create_idx', $sql);

        $sql= " CREATE TEMPORARY TABLE _tidx ( ";
        $sql.= " _table ".field2pg("VARCHAR(80)")." DEFAULT NULL, ";
        $sql.= " _idx ".field2pg("INTEGER(11)")." DEFAULT NULL, ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_code ".field2pg("VARCHAR(15)")." DEFAULT NULL";
        $sql.= ") ";

        $this->db_sql_show_error('create_idx', $sql);
    }
    /*
     * registra el _tidx la refrencia al registro y tabla temporal que acaba de ser modificado
     */
    protected function insert_idx($id_code= null, $id= null) {
        $_table= "_".$this->table;

        $sql= "insert into _tidx ";
        $sql.= $this->if_mysql ? "() " : "";
        if (empty($id) && !is_null($id_code)) 
            $sql.= "select '$_table', _idx, id, ".stringSQL($id_code)." from $_table ";
        elseif (!empty($id) && is_null($id_code)) 
            $sql.= "values ('$_table', null, $id, null) ";

        $this->db_sql_show_error("insert_idx($_table)", $sql);
    }

    protected function update_idx($id, $id_code, $quote= true) {
        $_table= "_".$this->table;
        $id_code= ($quote) ? "'$id_code'" : $id_code;

        $sql= "update _tidx set id = $id where _table = '$_table' ";
        if ($quote) 
            $sql.= "and ".convert_to("id_code", "utf8")." = ".convert_to("$id_code", "utf8")." ";
        else 
            $sql.= "and id_code = $id_code ";

        $result= $this->db_sql_show_error('update_idx', $sql);
    }
    
    // inserta en la tabla fisica lo que no fue encontrado con refresh_in_db
    protected function _insert_in_db($field= null, $id_code= null, $quote= true) {
        global $config;
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;

        $_table= "_".$this->table;
        $id= null; 
        $result= null;
        $id_code= ($quote && !is_null($id_code)) ? "'$id_code'" : $id_code;

        $sql= "select * from $_table ";
        if (!is_null($id_code)) {
            if ($quote) 
                $sql.= "where ".convert_to(stringSQL($field), "utf8")."  = ".convert_to("$id_code", "utf8")."  ";
            else 
                $sql.= "where ".stringSQL($field)." = $id_code ";
        }

        $result= $this->db_sql_show_error('_insert_in_db', $sql);
        $cant= $this->clink->num_rows($result);

        $count= count($this->array_dbstruct);
        $numj= 0;

        if ($cant > 0) {
            $row= $this->dblink->fetch_array($result);
            $i= 0; 
            $j= 0; 
            $_id= null; 
            $_idx= null;
            reset($this->array_dbstruct);

            $sqli= "insert into $this->table (";
            foreach ($this->array_dbstruct as $cell) {
                $_field= $cell['name'];

                if ($_field == 'id') {
                    $_id = $i;
                    ++$i;
                    continue;
                }
                if ($_field == '_idx') {
                    $_idx = $i;
                    ++$i;
                    continue;
                }

                if ($j > 0 && $j < $count) 
                    $sqli.= ", ";
                $sqli.= stringSQL($_field);
                ++$i; 
                ++$j;
            }
            $sqli.= ") values ( ";

            $i= 0; 
            $numj= $j; 
            $j= 0;
            $z= 0;
            reset($this->array_dbstruct);
            foreach ($this->array_dbstruct as $cell) {
                if (!is_null($_id) && $i == $_id) {
                    ++$i;
                    continue;
                }
                if (!is_null($_idx) && $i == $_idx) {
                    ++$i;
                    continue;
                }

                $_field= $cell['name'];
                $type= $cell['type'];
                $length= $cell['length'];
                $value= $row[$_field];

                if ($_field == 'cronos_syn') 
                    $value= $this->cronos;
                if ($_field == 'situs' && $value == $config->location) 
                    return null;

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

                if ($j > 0 && $j < $count) 
                    $sqli.= ", ";
                $sqli.= "$value";
                ++$i; 
                ++$j;
            }
            $sqli.= ") ";

            $resulti= null;
            if ($numj == $j) {
                $resulti= $this->db_sql_show_error('_insert_in_db', $sqli, false);
                function_progressCSS(++$z, "Insertando en BD desde tabla temporal (_insert_in_db: $this->table)", $cant, 2);
            }

            if ($resulti) {
                $id= $this->dblink->inserted_id($this->table);
                $this->array_insert[(string)$this->table][]= array($id, null);
            }
        }

        return $id;
    }

    protected function update_t_from_db($id, $primary_table= null, $field= null) {
        if (is_null($primary_table) || is_null($field)) 
            return null;
        if (strcmp($primary_table, "tpoliticas") == 0) 
            return null;
            
        $_table= "_".$this->table;
        $array_ids_field= array();
        
        if (!isset($this->array_id_primary[$primary_table])) {
            $sql= "select distinct id, id_code from $primary_table";
            $result= $this->db_sql_show_error("update_t_from_db($id)($primary_table)", $sql); 
            if (!$result) 
                return null;

            while ($row= $this->dblink->fetch_array($result)) {
                $this->array_id_primary[$primary_table][$row['id_code']]= array($row['id'], $row['id_code']);
            }
        }
        
        $sql= "select distinct $field from $_table where $field is not null";
        $result= $this->db_sql_show_error("update_t_from_db($id)($primary_table)", $sql); 
        if (!$result) 
            return null;
        
        $cant_j= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            if (empty($row[$field])) 
                continue;
            ++$cant_j;
            $array_ids_field[$row[$field]]= array($row[$field], 0);
        }

        $i= 0;
        $j= 0;
        $sql= null;
        reset($this->array_id_primary[$primary_table]);
        foreach ($this->array_id_primary[$primary_table] as $key => $row) {
            if (array_key_exists($key, $array_ids_field)) {
                $array_ids_field[$key][1]= 1;
                $sql.= "update $_table set $id = {$row[0]} where $field = '{$row[1]}'; ";
            } else {
                continue;
            }

            ++$j;
            ++$i;
            if ($i >= 500) {
                $this->db_multi_sql_show_error("update_t_from_db($id)($primary_table)", $sql, false);
                $sql= null;
                $i= 0;                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "update_t_from_db($id)($primary_table)-> cant:$j de $cant_j... ", $r);                 
            }           
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("update_t_from_db($primary_table)", $sql);  
        
        $i= 0;
        $sql= null;
        reset($array_ids_field);
        foreach ($array_ids_field as $row) {
            if ($row[1] == 1) 
                continue;
            $sql.= "delete from $_table where $field = '{$row[0]}'; ";
            ++$i;
            if ($i >= 500) {
                $this->db_multi_sql_show_error("update_t_from_db(borrando registros....)", $sql, false);
                $sql= null;
                $i= 0;  
            }    
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("update_t_from_db(borrando registros ....)", $sql);  
    }

    protected function _insert_flush($primary_table= null, $field= null, $primary_table1= null, $field1= null,
                                   $primary_table2= null, $field2= null, $primary_table3= null, $field3= null, $primary_table4= null, $field4= null) {

        $_table= "_".$this->table;
        $this->array_no_inserted= array();

        $sql= "select distinct $_table.* from $_table ";
        if (strcmp($primary_table, "tpoliticas") == 0) 
                $sql.= ", tpoliticas ";
        $sql.= "where ".convert_to("$_table.situs", "utf8")." != ".convert_to("'$this->location'", "utf8")." ";
        if (strcmp($primary_table, "tpoliticas") == 0) {
            $sql.= "and ($_table.id_politica is not null and ((tpoliticas.id = $_table.id_politica and tpoliticas.if_inner is null) ";
            $sql.= "or (".convert_to("tpoliticas.id_code", "utf8")." = ".convert_to("$_table.id_politica_code", "utf8")." and tpoliticas.if_inner = ".boolean2pg(1).")) ";
        } else {
            if (!is_null($primary_table)) 
                $sql.= "and ($_table.$field is not null ";
        }
        if (!is_null($primary_table1)) 
            $sql.= "or $_table.$field1 is not null ";
        if (!is_null($primary_table2)) 
            $sql.= "or $_table.$field2 is not null ";
        if (!is_null($primary_table3)) 
            $sql.= "or $_table.$field3 is not null ";
        if (!is_null($primary_table4)) 
            $sql.= "or $_table.$field4 is not null ";
        if (!is_null($primary_table)) 
            $sql.= ")";

        $result= $this->db_sql_show_error("_insert_flush($_table)", $sql);
        $cant= $this->clink->num_rows($result);

        reset($this->array_dbstruct);
        $count= count($this->array_dbstruct);
      
        $this->_execute_flush($result, $count, $cant, $primary_table, $field, $field1, $field2, $field3, $field4);
    }

    protected function _execute_flush($result, $count, $cant, $primary_table, $field, $field1, $field2, $field3, $field4) {
        global $SQL_texttypes, $SQL_blobtypes, $SQL_timetypes, $SQL_numtypes, $SQL_booltypes;
        $no_insert= 0;
        $inserted= 0;
    
        $z= 0;
        while ($row= $this->dblink->fetch_array($result)) {
            $i= 0; 
            $j= 0; 
            $_id= null; 
            $_idx= null;
            reset($this->array_dbstruct);
            $sql_stop= false;

            $table= $this->table;
            if ($this->use_year) {
                $id_evento= !empty($row['id_evento_code']) ? $row['id_evento_code'] : 0;
                $id_auditoria= !empty($row['id_auditoria_code']) ? $row['id_auditoria_code'] : 0;
                $id_tarea= !empty($row['id_tarea_code']) ? $row['id_tarea_code'] : 0;                
                $year= $this->array_years[$id_evento][$id_auditoria][$id_tarea];           
                if (empty($year)) 
                    continue;
                $table= "{$this->table}_{$year}";
            }

            $sql= "insert into $table (";
            foreach ($this->array_dbstruct as $cell) {
                $_field= $cell['name'];

                if ($_field == 'id') {
                    $_id = $i;
                    ++$i;
                    continue;
                }
                if ($j > 0 && $j < $count) 
                    $sql.= ", ";
                $sql.= "$_field";
                ++$i; 
                ++$j;
            }
            $sql.= ") values ( ";

            $i= 0; 
            $j= 0; 
            $id= null;
            reset($this->array_dbstruct);
            foreach ($this->array_dbstruct as $cell) {
                $_field= $cell['name'];
                $type= $cell['type'];
                $length= $cell['length'];
                $value= $row[$_field];

                if (!is_null($_id) && $i == $_id) {
                    ++$i;
                    $id = $value;
                    continue;
                }

                if ($_field == 'cronos_syn') 
                    $value= $this->cronos;
                if ($_field == 'situs' && $value == $this->location) {
                    $sql_stop = true;
                    break;
                }
                if (($this->origen_motor_db == "mysql" && !$this->if_mysql) && (array_search($type, $SQL_booltypes) !== false && $length == 1)) {
                    $value= boolean2pg(boolean($value));
                }
                if (($this->origen_motor_db == "postgres" && $this->if_mysql) && (array_search($type, $SQL_booltypes) !== false)) {
                    $value= boolean($value);
                }
                if (array_search($type, $SQL_texttypes) !== false || array_search($type, $SQL_timetypes) !== false) {
                    $value= setNULL_str($value, false);
                }
                elseif (array_search($type, $SQL_numtypes) !== false) {
                    if (!is_null($value) && $value == 0) 
                        $value= setZero($value);
                    else 
                        $value= setNULL($value);

                } elseif (array_search($type, $SQL_blobtypes) !== false) {
                    if (is_null($value)) 
                        $value= 'NULL';

                } else {
                    $value= setNULL($value);
                }
                if ($j > 0 && $j < $count) 
                    $sql.= ", ";
                $sql.= "$value";
                ++$i; 
                ++$j;
            }
            if ($sql_stop) 
                continue;
            $sql.= ") ";

            $_result= $this->db_sql_show_error('_execute_flush', $sql, false);
            function_progressCSS(++$z, "Insertando en BD desde tabla temporal (_execute_flush: $table)", $cant, 2);

            if (!$_result) {
                $this->array_no_inserted[]= array($id, $year);
                ++$no_insert;
            } else {
                $_cant= $this->db_cant;
                if ($_cant > 0) {
                    ++$inserted;
                    $id= $this->dblink->inserted_id();
                    $this->array_insert[(string)$this->table][]= array($id, $year);
                }
            }
        }

        $z= 0;
        foreach ($this->array_insert[(string)$this->table] as $key) {
            $this->insert_idx(null, $key[0]);
            
            function_progressCSS(++$z, "Actualizando BD desde tabla temporal (insert_idx: $this->table)", $no_insert, 2);
        }
        
        if ($no_insert == 0) 
            return;

        $z= 0;
        foreach ($this->array_no_inserted as $key) {
            $this->_update_flush($key, $primary_table, $field, $field1, $field2, $field3, $field4);
            $this->insert_idx(null, $key[0]);

            function_progressCSS(++$z, "Actualizando BD desde tabla temporal (_update_flush: $this->table)", $no_insert, 2);
        }
    }

    protected function _update_flush($key, $primary_table= null, $field= null, $field1= null, $field2= null, $field3= null, $field4= null) {
        global $array_dbtable;
        $_table= "_".$this->table;
        $i= 0;
        $sql_stop= false;

        $id= $key[0];
        reset($this->array_dbstruct);
        $count= count($this->array_dbstruct);

        $table= $this->table;
        if ($this->use_year) {
            $year= $key[1];
            if (empty($year)) 
                return null;
            $table= "{$this->table}_{$year}";
        }

        $sql= "update $table";
        $sql.= $this->if_mysql ? ", $_table " : " ";
        if (strcmp($primary_table, "tpoliticas") == 0) 
            $sql.= $this->if_mysql ? ", tpoliticas " : " ";
        $sql.= "set ";
        foreach ($this->array_dbstruct as $cell) {
            $ifield= $cell['name'];
            if ($ifield == 'id' || $ifield == '_idx') 
                continue;

            if ($i > 0 && $i < $count) 
                $sql.= ", ";
            if ($this->if_origen_down &&  $ifield == 'situs') {
                $sql_stop= true; 
                break;
            }
            if ($ifield == 'cronos_syn') {
                $sql.= $this->if_mysql ? "$table.$ifield= '$this->cronos' " : "$ifield= '$this->cronos' ";
            } else {
                $sql.= $this->if_mysql ? "$table.$ifield= $_table.$ifield " : "$ifield= $_table.$ifield";
            }
            ++$i;
        }
        $sql.= $this->if_mysql ? "" : " from $_table ";
        if (strcmp($primary_table, "tpoliticas") == 0) 
            $sql.= $this->if_mysql ? " " : ", tpoliticas ";
        
        if ($sql_stop) 
            return null;

        $sql.= "where $table.id = $id and ($_table.situs != '$this->location' and $table.cronos < $_table.cronos) ";
        if ($array_dbtable[$this->table]['proceso']) {
            $sql.= "and (".convert_to("$table.id_proceso_code", "utf8")." = ".convert_to("$_table.id_proceso_code","utf8")." ";
            $sql.= "and ".convert_to("$table.id_proceso_code", "utf8")." = ".convert_to("'$this->id_origen_code'", "utf8").") ";
        }
        if (strcmp($primary_table, "tpoliticas") == 0) {
            $sql.= "and (($_table.id_politica is not null and (($_table.id_politica = tpoliticas.id and tpoliticas.if_inner is null) ";
            $sql.= "or ($_table.id_politica_code = tpoliticas.id_code and tpoliticas.if_inner = true))) ";
        } else {
            if (!is_null($primary_table)) 
                $sql.= "and ($_table.$field is not null ";
        }
        if (!is_null($field1)) 
            $sql.= "or $_table.$field1 is not null ";
        if (!is_null($field2)) 
            $sql.= "or $_table.$field2 is not null ";
        if (!is_null($field3)) 
            $sql.= "or $_table.$field3 is not null ";
        if (!is_null($field4)) 
            $sql.= "or $_table.$field4 is not null ";
        if (!is_null($primary_table)) 
            $sql.= ")";

        $this->db_sql_show_error('_update_flush', $sql);
    }

    private function if_set_responsable($field, $id) {
        if (is_null($this->prs_down_chain)) {
            $this->set_prs_down_chain();
            if (is_null($this->prs_down_chain))
                return false;
        }
        $_table= "_".$this->table;
        $sql= "select id_responsable from $this->table where $this->table.$field = $id";
        $result= $this->db_sql_show_error('if_set_responsable', $sql);
        $row= $this->dblink->fetch_array($result);
        if (empty($row[0])) 
            return true;
            
        $sql= "select * from tusuarios where id = {$row[0]} and id_proceso in $this->prs_down_chain";
        $result= $this->db_sql_show_error('if_set_responsable', $sql);
        return $this->db_cant > 0 ? true : false;
    }     
    
    protected function _update_in_db($field, $id, $quote, $_field, $id_code, $_quote= true) {
        global $array_dbtable;

        $_table= "_".$this->table;
        $i= 0;
        $sql_stop= false;

        $id= ($quote) ? "'$id'" : $id;
        $id_code= ($_quote) ? "'$id_code'" : $id_code;

        reset($this->array_dbstruct);
        $count= count($this->array_dbstruct);

        $if_set_responsable= false;
        if ($this->table == "teventos" || $this->table == "ttareas" || $this->table == "tauditorias") {
            $if_set_responsable= $this->if_set_responsable($field, $id);
        }

        $sql= "update $this->table ";
        $sql.= $this->if_mysql ? ", $_table set " : " set ";

        foreach ($this->array_dbstruct as $cell) {
            $ifield= $cell['name'];
            if ($ifield == 'id' || $ifield == '_idx') 
                continue;

            if ($i > 0 && $i < $count) 
                $sql.= ", ";
            if ($ifield == 'cronos_syn') {
                $sql.= $this->if_mysql ? "$this->table.$ifield= '$this->cronos' " : "$ifield= '$this->cronos' ";
            }
            else {
                if (strcmp($this->table, "teventos") == 0) {
                    if (strcmp($this->table.$field, "teventos.id_tipo_evento") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "teventos.id_tipo_evento_code") == 0) 
                        continue;
                    if (!$if_set_responsable) {
                        if (strcmp($this->table.$field, "teventos.id_responsable") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "teventos.id_responsable_2") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "teventos.responsable_2_reg_date") == 0) 
                            continue;
                    }    
                }
                if (strcmp($this->table, "tauditorias") == 0) {
                    if (strcmp($this->table.$field, "tauditorias.id_tipo_evento") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tauditorias.id_tipo_evento_code") == 0) 
                        continue;
                    if (!$if_set_responsable) {
                        if (strcmp($this->table.$field, "tauditorias.id_responsable") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tauditorias.id_responsable_2") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tauditorias.responsable_2_reg_date") == 0) 
                            continue;
                    }                       
                }
                if (strcmp($this->table, "ttareas") == 0) {
                    if (!$if_set_responsable) {
                        if (strcmp($this->table.$field, "ttareas.id_responsable") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "ttareas.id_responsable_2") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "ttareas.responsable_2_reg_date") == 0) 
                            continue;
                    }                       
                }                
                if (strcmp($this->table, "tindicadores") == 0) {
                    if (strcmp($this->table.$field, "tindicadores.inicio") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tindicadores.fin") == 0) 
                        continue;
                }
                if (strcmp($this->table, "tusuarios") == 0) {
                    if (strcmp($this->table.$field, "tusuarios.clave") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios._clave") == 0) 
                            continue;
                    if (strcmp($this->table.$field, "tusuarios.nivel") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.cargo") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.conectado") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.acc_sys") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.eliminado") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.id_proceso") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.id_proceso_code") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.user_ldap") == 0) 
                        continue;
                    if (strcmp($this->table.$field, "tusuarios.id_proceso_jefe") == 0) 
                        continue;
                    
                    if (!$this->if_origen_up) {
                        if (strcmp($this->table.$field, "tusuarios.global_user") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tusuarios.acc_planwork") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tusuarios.acc_planrisk") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tusuarios.acc_planaudit") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tusuarios.acc_planheal") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tusuarios.acc_archive") == 0) 
                            continue;
                        if (strcmp($this->table.$field, "tusuarios.freeassign") == 0) 
                            continue;
                    }
                }
                if (strcmp($this->table, "tprocesos") == 0) 
                    if (strcmp($this->table.$field, "tprocesos.id_responsable") == 0) 
                        continue;
                if (strcmp($field, "copyto") == 0) 
                    continue;
                if ($array_dbtable[$this->table]['proceso'] && (strcmp($field, "id_proceso") == 0 || strcmp($field, "id_proceso_code") == 0)) 
                    continue;

                $sql.= $this->if_mysql ? "{$this->table}.".stringSQL($ifield)." = {$_table}.".stringSQL($ifield)." " : stringSQL($ifield)." = {$_table}.".stringSQL($ifield)." ";
            }

            ++$i;
        }

        $sql.= $this->if_mysql ? "" : " from $_table ";

        if ($sql_stop) 
            return null;

        $sql.= "where $this->table.$field = $id and (".convert_to("$_table.".stringSQL($_field), "utf8")." = ".convert_to("$id_code", "utf8")." ";
        $sql.= "and ".convert_to("{$this->table}.".stringSQL($_field), "utf8")." = ".convert_to("$_table.".stringSQL($_field), "utf8").") ";
        $sql.= "and ($this->table.cronos < $_table.cronos and ".convert_to("$_table.situs", "utf8")." != ".convert_to("'$this->location'","utf8").") ";

        if ($array_dbtable[$this->table]['proceso'] && strcmp($this->table, "tplanes") != 0) {
            $sql.= "and (".convert_to("$this->table.id_proceso_code", "utf8")." = ".convert_to("$_table.id_proceso_code", "utf8")." ";
            $sql.= "and ".convert_to("$this->table.id_proceso_code", "utf8")." = ".convert_to("'$this->id_origen_code'", "utf8").") ";
        }
        if (strcmp($this->table, "tprocesos") == 0)
            $sql.=  "and ".convert_to("$this->table.id_code", "utf8")." = ".convert_to("'$this->id_origen_code'","utf8")." ";
        if (strcmp($this->table, "tusuarios") == 0) 
            $sql.=  "and $this->table.id != 1 ";

        $result= $this->db_sql_show_error('_update_in_db', $sql);
        $cant= $this->db_cant;
        if ($cant > 0) 
            $this->array_insert[(string)$this->table][]= array($id, null);
    }

    protected function _update_in_db_tusuarios() {
        if ($this->if_mysql) {
            $sql= "update tusuarios, _tusuarios set tusuarios.acc_sys= _tusuarios.acc_sys, tusuarios.eliminado= _tusuarios.eliminado, ";
            $sql.= "tusuarios.clave= _tusuarios.clave ";
        } else {
            $sql= "update tusuarios  set acc_sys= _tusuarios.acc_sys, eliminado= _tusuarios.eliminado, clave= _tusuarios.clave from _tusuarios ";
        }
        $sql.= "where ".convert_to("tusuarios.".stringSQL("noIdentidad"), "utf8")." = ".convert_to("_tusuarios.".stringSQL("noIdentidad"),"utf8")." ";
        $sql.= "and ".convert_to("tusuarios.id_proceso_code", "utf8")." = ".convert_to("_tusuarios.id_proceso_code", "utf8")." ";
        $sql.= "and ".convert_to("tusuarios.id_proceso_code", "utf8")." = ".convert_to("'$this->id_origen_code'", "utf8")." ";
        $result= $this->db_sql_show_error('_update_in_db_tusuarios', $sql);

        if ($this->if_mysql) 
            $sql = "update tusuario_grupos, tusuarios set tusuario_grupos.eliminado= tusuarios.eliminado ";
        else 
            $sql = "update tusuario_grupos set eliminado= tusuarios.eliminado from tusuarios ";
        $sql.= "where tusuario_grupos.id_usuario = tusuarios.id and ".convert_to("tusuarios.id_proceso_code", "utf8")." = ".convert_to("'$this->id_origen_code'", "utf8")." ";
        $result= $this->db_sql_show_error('_update_in_db_tusuarios', $sql);
    }

    /*
     *  actualiza el id de la tabla temporal si existe el id_code en la tabla fisica
     */
    protected function refresh_t_table_from_db($id, $id_code) {
        global $array_dbtable;
        $_table= "_".$this->table;

        $sql= "update $_table set $id= NULL ";
        $this->db_sql_show_error('refresh_t_table_from_db', $sql);

        $sql= "select distinct $id, $id_code from $this->table";
        $result= $this->db_sql_show_error('refresh_t_table_from_db', $sql);
        $cant_j= $this->db_cant;

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) {
            if ($this->table == "tusuarios" && (empty($row[$id_code]) || $row['id'] == _USER_SYSTEM)) 
                continue;
            $_id= setNULL($row[$id]);
            $_id_code= setNULL_equal_sql($row[$id_code], true);
            $sql.= "update $_table set $id= $_id where $id_code $_id_code; ";
            
            ++$j;
            ++$i;
            if ($i >= 500) {
                $this->db_multi_sql_show_error("refresh_t_table_from_db($this->table)($id, $id_code)", $sql, false);
                $sql= null;
                $i= 0;                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "refresh_t_table_from_db($this->table)($id, $id_code)... ", $r);                 
            } 
        }   
        if ($sql) 
            $this->db_multi_sql_show_error("refresh_t_table_from_db($this->table)($id, $id_code)", $sql, false);

        $id_proceso= $array_dbtable[$this->table]['proceso'] ? ", id_proceso, id_proceso_code" : null;

        $sql= "select $id, ".stringSQL($id_code)." $id_proceso from $_table ";
        $result= $this->db_sql_show_error('refresh_t_table_from_db', $sql);
        return $result;
    }

    /*
     *  actualiza el id de la tabla temporal con el id que le corresponde en la fisica
     */
    protected function refresh_t_db_id($id) {
        global $array_dbtable;
        reset($array_dbtable);
        $this->current_field= $id;

        if (strcmp($id, "id_politica") != 0) {
            while (list($key, $dbtable)= each($array_dbtable)) {
                $_table= "_".$key;
                if (!$dbtable['export']) 
                    continue;
                $key= !$dbtable['use_year'] ? $key : "{$key}_{$this->year}";
                if (!$this->dblink->field_exists($key, $id)) 
                    continue;
                $this->_refresh_t_db_id($_table, $id); 
            }
        } else {
            while (list($key, $dbtable)= each($array_dbtable)) {
                $_table= "_".$key;
                if (!$dbtable['export']) 
                    continue;
                if ($_table != "_tpolitica_objetivos" && $_table != "_treg_politica") 
                    continue;
                if ($this->if_mysql) {
                    $sql= "update $_table, tpoliticas set $_table.id_politica= tpoliticas.id ";
                } else {
                    $sql= "update $_table set id_politica= tpoliticas.id from tpoliticas";
                }
                $sql.= "where ($_table.id_politica = tpoliticas.id  ";
                $sql.= "and tpoliticas.if_innner is null) or (".convert_to("$_table.id_politica_code", "utf8")." = ".convert_to("tpoliticas.id_code", "utf8")." and if_inner = ".boolean2pg(1).") ";

                $this->db_sql_show_error('refresh_t_db_id', $sql, false);
            }
        }

        $this->current_field= null;
    }
    /*
     * Actualiza el campo $id en la tabla temporal $_table utilizando el registro de la tabla _tidx
     */
    protected function _refresh_t_db_id($_table, $id) {
        $table= '_'.$this->table;
        
        $sql= "select id, _idx from _tidx where _table = '$table' and _tidx.id is not null";
        $result= $this->db_sql_show_error("_refresh_t_db_id($table, $id)", $sql);
        $cant_j= $this->db_cant;
        
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->dblink->fetch_array($result)) { 
            $sql.= "update $_table set $_table.$id= {$row['id']} where $_table.$id = {$row['_idx']}; ";
            ++$j;
            ++$i;
            if ($i >= 500) {
                $this->db_multi_sql_show_error("_refresh_t_db_id($table, $id)", $sql, false);
                $sql= null;
                $i= 0;                
                $r= (float)($j) / $cant_j;
                $_r= $r*100; $_r= number_format($_r,1);                 
                bar_progressCSS(2, "_refresh_t_db_id -> $_table.$id x $table... ", $r);                 
            }           
        }
        if (!empty($sql)) 
            $this->db_multi_sql_show_error("_refresh_t_db_id($table,$id)", $sql, false); 
        
        bar_progressCSS(2, "_refresh_t_db_id -> $_table.$id x $table ... ", 1);         
    }
    
    protected function refresh_from_idx($table, $field, $_table, $_quote= true) {
        $this->current_field= $field;

        $i= 0;
        $sql= null;
        foreach ($this->array_insert[(string)$table] as $key) {
            $id= ($_quote) ? "'{$key[0]}'" : $key[0];

            if ($this->if_mysql) {
                $sql.= "update $table, _tidx set _tidx.$field= _tidx.id ";
            } else {
                $sql.= "update $table set $field= _tidx.id from _tidx ";
            }
            $sql.= "where ($table.$field = _tidx._idx and $table.id = $id) and _tidx._table = '$_table'; ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->db_multi_sql_show_error("refresh_from_idx($table)", $sql);
                $i= 0;
                $sql= null;
            }
        }

        if (!empty($sql)) 
            $this->db_multi_sql_show_error("refresh_from_idx($table)", $sql);

        $this->current_field= null;
    }

}
