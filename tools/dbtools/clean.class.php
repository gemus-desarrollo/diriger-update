<?php

/**
 * @author muste
 * @copyright 2013
 */

include_once "base_clean.class.php";

defined('_BLOCK_SIZE_CLEAN') or define('_BLOCK_SIZE_CLEAN', 33000);

class Tclean extends Tbase_clean {

    public function __construct($clink) {
        Tbase_clean::__construct($clink);

        $this->max_multi_query= 500;
    }

    //Just add month(s) on the orginal date.
    public function set_date() {
        global $config;

        $cd = strtotime(date('Y-m-d'));
        $mth= $config->monthpurge;
        $y= date('Y',$cd);
        $this->reg_fecha= date('Y-m-d', mktime(0, 0, 0, date('m',$cd)-$mth, date('d',$cd), $y));
        $this->last_purge_time= $this->get_system('purge');
        if (empty($this->last_purge_time))
            $this->last_purge_time= date('Y-m-d', mktime(0,0,0,date('m',$cd), date('d',$cd), $y-1));

        if (!is_null($this->fecha))
            $this->writeLog("Ultima purga realizada el ".odbc2time_ampm($this->fecha)." con fecha de referencia ".odbc2date($this->last_purge_time)."\n\n");

        $this->year_init= (int)date('Y', strtotime($this->last_purge_time));
        $this->year_end= (int)date('Y', strtotime($this->reg_fecha));
        $this->last_purge_time= strtotime($this->last_purge_time) >= strtotime($this->reg_fecha) ? $this->reg_fecha : $this->last_purge_time;
    }

    private function create_array_ids($num_rows, $result, &$array_ids,
                                      &$array_evento_ids, &$array_tarea_ids, &$array_auditoria_ids, &$array_item_ids, $use_process) {
        $cronos_cut= strtotime($this->reg_fecha);

        $array_evento_ids[0]= 0;
        $array_tarea_ids[0]= 0;
        $array_auditoria_ids[0]= 0;

        $i= 0;
        $j= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $id_evento= !empty($row['id_evento']) ? $row['id_evento'] : 0;
            $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : 0;
            $id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : 0;
            $id_proceso= $row['id_proceso'];
            $id_usuario= $row['id_usuario'];

            if (!empty($id_evento))
                $array_evento_ids[$id_evento]= $id_evento;
            if (!empty($id_tarea))
                $array_tarea_ids[$id_tarea]= $id_tarea;
            if (!empty($id_auditoria))
                $array_auditoria_ids[$id_auditoria]= $id_auditoria;

            if ($use_process && !empty($id_proceso)) {
                $array_item_ids[$id_proceso]= $id_proceso;
                $id= $id_proceso;
            }
            if (!$use_process && !empty($id_usuario)) {
                $array_item_ids[$id_usuario]= $id_usuario;
                $id= $id_usuario;
            }

            if (strtotime($row['cronos']) < $cronos_cut && empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][0])) {
                $array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][0]= $row['id'];
                continue;
            }
            if (strtotime($row['cronos']) >= $cronos_cut) {
                if (empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][1])) {
                    $array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][1]= $row['id'];
                    continue;
                }
                if (empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][2])) {
                    $array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][2]= $row['id'];
                    continue;
                }
                if (empty($array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][3])) {
                    $array_ids[$id_evento][$id_auditoria][$id_tarea][$id]['ids'][3]= $row['id'];
                    continue;
                }
            }
        }
    }

    private function size_table($table) {
        $sql= "select count(*) from $table";
        $result= $this->do_sql_show_error('size_table', $sql);
        $row= $this->clink->fetch_array($result);
        return $row[0];
    }
    
    public function dbclean() {
        $this->max_steep= 9;
        $this->steep= 0;

        $this->clean_treg_evento();
        memory_usage("clean_treg_evento");

        $this->clean_tproceso_eventos();
        memory_usage("clean_tproceso_eventos");

        $this->clean_tusuario_eventos();
        memory_usage("clean_tusuario_eventos");

        $this->clean_teventos();
        memory_usage("clean_teventos");

        $this->clean_treg_plantrab();
        $this->clean_treg_indicador();
        $this->clean_table('tperspectivas', 'treg_perspectiva', 'id_perspectiva');
        $this->clean_table('tinductores', 'treg_inductor', 'id_inductor');
        $this->clean_table('tobjetivos', 'treg_objetivo', 'id_objetivo');
        $this->clean_table('triesgos', 'treg_riesgo', 'id_riesgo', false);
        $this->clean_table('tnotas', 'treg_nota', 'id_nota', false);

        $this->nrows= 0;
        bar_progressCSS(0, "Purgando registros de trazas", 0.9);
        $this->_tdeletes();

        $line= "\nCantidad de registros eliminados (trazas): $this->nrows \n";
        $this->writeLog($line);

        bar_progressCSS(0, "Terminada la operacion", 1);
    }

    private function clean_treg_evento() {
        $this->nrows= 0;
        $r= (float)(++$this->steep)/$this->max_steep;
        $_r= $r*100;
        $_r= number_format($_r,3);

        bar_progressCSS(0, "Purgando registros de eventos ($_r%)", $r);

        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= "select *, $year as _year from treg_evento_$year ";
            $sql.= "where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." ";
            // $sql.= "and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'");
            $sql.= " order by cronos desc";
            $result= $this->do_sql_show_error('clean_treg_evento', $sql);
            $num_rows= $this->clink->num_rows($result);
            $this->clink->free_result($result);
            if (empty($num_rows))
                continue;

            $j= 0;
            for ($i= 0; $i <= $num_rows; $i+= _BLOCK_SIZE_CLEAN) {
                $i-= $j;
                $j= $this->_clean_treg_evento($year, $i, $num_rows);
                memory_usage("_clean_treg_evento($year --> $i:$j)");
                if ($j == 0)
                    break;                
            }
            memory_usage("_clean_treg_evento($year --> null)");

            $sql= "optimize table treg_evento_$year";
            $this->do_sql_show_error('clean_treg_evento', $sql);
        }

        $line= "Cantidad de registros eliminados (actividades):{$this->nrows}  Fecha y Hora:".date("Y-m-d H:i")." \n";
        $this->writeLog($line);
    }

    private function _clean_treg_evento($year, $init, $total_rows) {
        $size_init= $this->size_table("treg_evento_$year");
        
        $sql.= "select *, $year as _year from treg_evento_$year ";
        $sql.= "where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." ";
        // $sql.= "and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'");
        $sql.= " order by cronos desc ";
        if (!is_null($init))
            $sql.= "limit $init, "._BLOCK_SIZE_CLEAN;
        $result= $this->do_sql_show_error('_clean_treg_evento', $sql);
        $num_rows= $this->clink->num_rows($result);

        $array_ids= array();
        $array_evento_ids= array();
        $array_tarea_ids= array();
        $array_auditoria_ids= array();
        $array_usuario_ids= array();

        bar_progressCSS(1, "Creando registros de ID create_array_ids (0%)....", 0);

        $this->create_array_ids($num_rows, $result, $array_ids, $array_evento_ids, $array_tarea_ids, $array_auditoria_ids, $array_usuario_ids, false);
        $this->clink->free_result($result);

        bar_progressCSS(1, "Creando registros de ID create_array_ids (100%)....", 1);

        $i= 0;
        $j= 0;
        $sql= null;
        foreach ($array_evento_ids as $id_evento) {
            ++$j;
            reset($array_auditoria_ids);
            foreach ($array_auditoria_ids as $id_auditoria) {
                reset($array_tarea_ids);
                foreach ($array_tarea_ids as $id_tarea) {
                    reset($array_usuario_ids);
                    foreach ($array_usuario_ids as $id_usuario) {
                        $array= $array_ids[$id_evento][$id_auditoria][$id_tarea][$id_usuario]['ids'];
                        if (empty($array))
                            continue;
                        $_id_evento= setNULL_empty_equal_sql($id_evento);
                        $_id_auditoria= setNULL_empty_equal_sql($id_auditoria);
                        $_id_tarea= setNULL_empty_equal_sql($id_tarea);

                        $sql.= "delete from treg_evento_{$year} where id_evento $_id_evento and id_auditoria $_id_auditoria ";
                        $sql.= "and id_tarea $_id_tarea and id_usuario = $id_usuario ";
                        if (!empty($array[0]))
                            $sql.= "and id != {$array[0]} ";
                        if (!empty($array[1]))
                            $sql.= "and id != {$array[1]} ";
                        if (!empty($array[2]))
                            $sql.= "and id != {$array[2]} ";
                        if (!empty($array[3]))
                            $sql.= "and id != {$array[3]} ";
                        $sql.= ";";

                        ++$i;
                        if ($j >= $this->max_multi_query) {
                            $this->do_multi_sql_show_error('_clean_treg_eventos', $sql);
                            $this->nrows+= $j;
                            $sql= null;
                            $j= 0;

                            $r= (float)$i / $num_rows;
                            $_r= $r*100;
                            $_r= number_format($_r,3);
                            bar_progressCSS(1, "Procesando tablas treg_eventos_$year init= $init total=$total_rows ($_r%)....", $r);
                        }
        }   }   }   }

        if (!empty($sql)) {
            $this->do_multi_sql_show_error('_clean_treg_evento', $sql);
            $this->nrows+= $j;
        }

        bar_progressCSS(1, "Procesando tablas treg_eventos_$year init=$init total=$total_rows(100%)....", 1);

        unset($array_ids);
        unset($array_evento_ids);
        unset($array_tarea_ids);
        unset($array_auditoria_ids);
        unset($array_usuario_ids);

        $size_end= $this->size_table("treg_evento_$year");
        
        return $size_init-$size_end;
    }

    private function clean_tproceso_eventos() {
        $this->nrows= 0;
        $r= (float)(++$this->steep)/$this->max_steep;
        $_r= $r*100;
        $_r= number_format($_r,3);
        bar_progressCSS(0, "Purgando registros de tproceso_eventos ($_r%)", 0.2);

        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql= "select *, $year as _year from tproceso_eventos_$year ";
            $sql.= "where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." ";
            // $sql.= "and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'");
            $sql.= " order by cronos desc ";
            $result= $this->do_sql_show_error('clean_tproceso_eventos', $sql);
            $cant= $this->clink->num_rows($result);
            $this->clink->free_result($result);
            if (empty($this->cant))
                continue;

            $j= 0;
            for ($i= 0; $i <= $cant; $i+= _BLOCK_SIZE_CLEAN) {
                $i-= $j;
                $j= $this->_clean_tproceso_eventos($year, $i);              
                memory_usage("_clean_tproceso_eventos($year --> $i:$j)");
                if ($j == 0)
                    break;
            }
            $this->_clean_tproceso_eventos($year, null);
            memory_usage("_clean_tproceso_eventos($year --> null)");

            $sql= "optimize table tproceso_eventos_$year";
            $this->do_sql_show_error('clean_tproceso_eventos', $sql);
        }
        $line= "Cantidad de registros eliminados tabla tproceso_eventos:{$this->nrows} Fecha y Hora:".date("Y-m-d H:i");
        $this->writeLog("{$line}\n");
    }

    private function _clean_tproceso_eventos($year, $init) {
        $size_init= $this->size_table("tproceso_eventos_$year");
        
        $sql= "select *, $year as _year from tproceso_eventos_$year ";
        $sql.= "where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." ";
        // $sql.= "and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'");
        $sql.= " order by cronos desc ";
        if (!is_null($init))
            $sql.= "limit $init, "._BLOCK_SIZE_CLEAN;
        $result= $this->do_sql_show_error('_clean_tproceso_eventos', $sql);
        $num_rows= $this->clink->num_rows($result);

        $array_ids= array();
        $array_evento_ids= array();
        $array_tarea_ids= array();
        $array_auditoria_ids= array();
        $array_proceso_ids= array();

        $this->create_array_ids($num_rows, $result, $array_ids, $array_evento_ids, $array_tarea_ids, $array_auditoria_ids, $array_proceso_ids, true);
        $this->clink->free_result($result);

        $i= 0;
        $j= 0;
        $sql= null;
        foreach ($array_evento_ids as $id_evento) {
            reset($array_auditoria_ids);
            foreach ($array_auditoria_ids as $id_auditoria) {
                reset($array_tarea_ids);
                foreach ($array_tarea_ids as $id_tarea) {
                    reset($array_proceso_ids);
                    foreach ($array_proceso_ids as $id_proceso) {
                        $array= $array_ids[$id_evento][$id_auditoria][$id_tarea][$id_proceso]['ids'];
                        if (empty($array))
                            continue;
                        $id_evento= setNULL_empty_equal_sql($id_evento);
                        $id_auditoria= setNULL_empty_equal_sql($id_auditoria);
                        $id_tarea= setNULL_empty_equal_sql($id_tarea);

                        $sql.= "delete from tproceso_eventos_{$year} where id_evento $id_evento and id_auditoria $id_auditoria ";
                        $sql.= "and id_tarea $id_tarea and id_proceso = $id_proceso ";
                        if (!empty($array[0]))
                            $sql.= "and id != {$array[0]} ";
                        if (!empty($array[1]))
                            $sql.= "and id != {$array[1]} ";
                        if (!empty($array[2]))
                            $sql.= "and id != {$array[2]} ";
                        if (!empty($array[3]))
                            $sql.= "and id != {$array[3]} ";
                        $sql.= ";";

                        ++$i;
                        ++$j;
                        if ($j >= $this->max_multi_query) {
                            $this->do_multi_sql_show_error('_clean_tproceso_eventos', $sql);
                            $this->nrows+= $j;
                            $sql= null;
                            $j= 0;

                            $r= (float)$i / $num_rows;
                            $_r= $r*100;
                            $_r= number_format($_r,3);
                            bar_progressCSS(1, "Procesando tablas tproceso_eventos_$year ($_r%)....", $r);
                        }
        }   }   }   }

        if ($j) {
            $this->do_multi_sql_show_error('_clean_tproceso_eventos', $sql);
            $this->nrows+= $j;
        }

        bar_progressCSS(1, "Procesando tablas tprocesos_eventos (100%)....", 1);

        unset($array_ids);
        unset($array_evento_ids);
        unset($array_tarea_ids);
        unset($array_auditoria_ids);
        unset($array_proceso_ids);

        $size_end= $this->size_table("tproceso_eventos_$year");
        
        return $size_init-$size_end;
    }


    private function clean_tusuario_eventos() {
        $this->nrows= 0;

        $r= (float)(++$this->steep)/$this->max_steep;
        $_r= $r*100;
        $_r= number_format($_r,3);
        bar_progressCSS(0, "Purgando registros de tusuario_eventos ($_r%)", $r);

        $this->_clean_tusuario_eventos();

        $line= "\nCantidad de registros eliminados tabla tusuario_eventos:{$this->nrows} Fecha y Hora:".date("Y-m-d H:i")."\n";
        $this->writeLog($line);
    }

    private function _clean_tusuario_eventos() {
        $sql= null;
        for ($year= $this->year_init; $year <= $this->year_end; $year++) {
            $sql.= $year > $this->year_init ? " union " : "";
            $sql.= "select *, $year as _year from tusuario_eventos_$year ";
            $sql.= "where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." ";
            $sql.= "and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'");
        }
        $sql.= " order by cronos desc ";
        $result= $this->do_sql_show_error('_clean_tusuario_eventos', $sql);

        $i= 0;
        $j= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $id_evento= !empty($row['id_evento']) ? $row['id_evento'] : 0;
            $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : 0;
            $id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : 0;
            $id_tematica= !empty($row['id_tematica']) ? $row['id_tematica'] : 0;
            $id_usuario= !empty($row['id_usuario']) ? $row['id_usuario'] : 0;
            $id_grupo= !empty($row['id_grupo']) ? $row['id_grupo'] : 0;

            if ($array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica][$id_grupo][$id_usuario][$row['_year']])
                continue;
            $array_ids[$id_evento][$id_auditoria][$id_tarea][$id_tematica][$id_grupo][$id_usuario][$row['_year']]= $row['cronos'];

            $id_evento= setNULL_equal_sql($row['id_evento']);
            $id_auditoria= setNULL_equal_sql($row['id_auditoria']);
            $id_tarea= setNULL_equal_sql($row['id_tarea']);
            $id_tematica= setNULL_equal_sql($row['id_tematica']);
            $id_usuario= setNULL_equal_sql($row['id_usuario']);
            $id_grupo= setNULL_equal_sql($row['id_grupo']);

            $sql.= "delete from tusuario_eventos_{$row['_year']} where cronos < '{$row['cronos']}' and id != {$row['id']} ";
            $sql.= "and id_evento $id_evento and id_auditoria $id_auditoria and id_tarea $id_tarea and id_tematica $id_tematica ";
            $sql.= "and id_usuario $id_usuario and id_grupo $id_grupo; ";

            ++$j;
            if ($j >= $this->max_multi_query) {
                $this->do_multi_sql_show_error('_clean_tusuario_eventos', $sql);
                $this->nrows+= $j;
                $sql= null;
                $j= 0;

                $r= (float)$i / $nums_tb;
                $_r= $r*100;
                $_r= number_format($_r,3);
                bar_progressCSS(1, "Procesando tablas tusuario_eventos ($_r%)....", $r);
            }
        }

        if ($j) {
            $this->do_multi_sql_show_error('_clean_tusuario_eventos', $sql);
            $this->nrows+= $j;
        }

        bar_progressCSS(1, "Procesando tablas tusuario_eventos (100%)....", 1);
    }

    private function clean_treg_plantrab() {
        $r= (float)(++$this->steep)/$this->max_steep;
        $_r= $r*100; $_r= number_format($_r,3);

        $this->nrows= 0;
        bar_progressCSS(0, "Purgando registros de planes de trabajo ($_r%)....", $r);

        $this->_clean_treg_plantrab();

        $line= "\nCantidad de registros eliminados (planes): $this->nrows \n";
        $this->writeLog($line);
    }

    private function _clean_treg_plantrab() {
        $sql= "select * from treg_plantrab where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." ";
        $sql.= "and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'")." ";
        $sql.= "order by cronos desc";
        $result= $this->do_sql_show_error('_clean_treg_plantrab', $sql);

        $i= 0;
        $j= 0;
        $sql= null;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            if ($array_ids[$row['id_plan']])
                continue;
            $array_ids[$row['id_plan']]= $row['cronos'];

            $sql.= "delete from treg_plantrab where cronos < '{$row['cronos']}' and id != {$row['id']}; ";

            ++$j;
            if ($j >= $this->max_multi_query) {
                $this->do_multi_sql_show_error('_clean_treg_plantrab', $sql);
                $this->nrows+= $j;
                $sql= null;
                $j= 0;

                $r= (float)$i / $nums_tb;
                $_r= $r*100;
                $_r= number_format($_r,3);
                bar_progressCSS(1, "Procesando tablas treg_plantrab ($_r%)....", $r);
            }
        }
        if ($j) {
            $this->do_multi_sql_show_error('_clean_treg_plantrab', $sql);
            $this->nrows+= $j;
        }

        bar_progressCSS(1, "Procesando tablas treg_plantrab (100%)....", 1);
    }

    private function clean_treg_indicador() {
        $r= (float)(++$this->steep)/$this->max_steep;
        $_r= $r*100;
        $_r= number_format($_r,3);

        $this->nrows= 0;
        bar_progressCSS(0, "Purgando registros de indicadores ($_r%)....", $r);
        $sql= "select id, nombre from tindicadores";
        $result= $this->do_sql_show_error("cleandb", $sql);
        $nums_tb= $this->cant;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $this->id_indicador= $row['id'];
            $nombre= $row['nombre'];

            $this->treg_indicador("treg_real");
            $this->treg_indicador("treg_plan");

            $r= (float)(++$i) / $nums_tb;
            $_r= $r*100;
            $_r= number_format($_r,3);
            bar_progressCSS(1, "Procesando el indicador ($nombre) ($_r%)....", $r);
        }

        $line= "\nCantidad de registros eliminados (indicadores): ".$this->nrows;
        $this->writeLog($line);
    }

    private function treg_indicador($table) {
        $sql_plus= str_to_date2pg("concat_ws('-',".literal2pg("year").",lpad(".literal2pg("month").",2,'0'),lpad(".literal2pg("day").",2,'0'))");

        $sql= "select id, id_indicador, $sql_plus as _reg_date, cronos from $table ";
        $sql.= "where id_indicador = $this->id_indicador and $sql_plus < ".str_to_date2pg("'$this->reg_fecha'")." ";
        $sql.= "and (".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'").") ";
        $sql.= "order by id desc limit 1";
        $result= $this->do_sql_show_error("treg_indicador($table)", $sql);

        if (empty($this->cant))
            return;

        $row= $this->clink->fetch_array($result);
        $id= $row['id'];
        $reg_date= $row['_reg_date'];

        $sql= "delete from $table where $table.id < $id and $table.id_indicador = $this->id_indicador ";
        $sql.= "and CONCAT_WS('-',".literal2pg("$table.year").", ".literal2pg("$table.month").", ".literal2pg("$table.day").") = '$reg_date' ";
        $sql.= "and (".date2pg("$table.cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." and ".date2pg("$table.cronos")." < ".str_to_date2pg("'$this->reg_fecha'").") ";

        $this->do_sql_show_error("treg_indicador($table)", $sql);
        $this->nrows+= $this->clink->affected_rows();
    }

    private function clean_table($table, $treg_table, $field, $use_reg_date= true) {
        $r= (float)(++$this->steep)/$this->max_steep;
        $_r= $r*100; $_r= number_format($_r,3);

        $this->nrows= 0;
        bar_progressCSS(0, "Purgando registros de $table ($_r%)....", $r);

        $sql= "select id from $table";
        $result= $this->do_sql_show_error("clean_table", $sql);
        $nums_tb= $this->cant;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $this->id= $row['id'];
            $this->treg_table($treg_table, $field, $use_reg_date);

            $r= (float)(++$i) / $nums_tb;
            $_r= $r*100;
            $_r= number_format($_r,3);
            bar_progressCSS(1, "Procesando el ($_r%)....", $r);
        }

        $line= "Cantidad de registros eliminados ($table):{$this->nrows} Fecha y Hora:".date("Y-m-d H:i")."\n";
        $this->writeLog($line);
    }

    private function treg_table($table, $field, $use_reg_date= true) {
        $sql= "select id, $field ";
        if ($use_reg_date)
            $sql.= ", CONCAT_WS('-', ".literal2pg("year").", ".literal2pg("month").", 01) as _reg_date, ";
        $sql.= "cronos from $table ";
        $sql.= "where $field = $this->id ";
        if ($use_reg_date)
            $sql.= "and ".str_to_date2pg("CONCAT_WS('-',".literal2pg("year").", ".literal2pg("month").", '01')")." < ".str_to_date2pg("'$this->reg_fecha'")." ";
        $sql.= "and (".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'").") ";
        $sql.= "order by id desc limit 1";
        $result= $this->do_sql_show_error("treg_table($table)", $sql);

        $cant= $this->cant;
        if (empty($cant))
            return;

        $row= $this->clink->fetch_array($result);
        $id= $row['id'];

        if (empty($row[$field]))
            return;

        $reg_date= $row['_reg_date'];

        $sql= "delete from $table where $table.id < $id and $table.$field = $this->id ";
        if ($use_reg_date)
            $sql.= "and CONCAT_WS('-',".literal2pg("$table.year").", ".literal2pg("$table.month").", '01') = '$reg_date' ";
        $sql.= "and (".date2pg("$table.cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." and ".date2pg("$table.cronos")." < ".str_to_date2pg("'$this->reg_fecha'").") ";

        $this->do_sql_show_error("treg_table($table)", $sql);
        $this->nrows+= $this->clink->affected_rows($result);
    }

    private function _tdeletes() {
        $sql= "delete from tdeletes ";
        $sql.= "where ".date2pg("cronos")." >= ".str_to_date2pg("'$this->last_purge_time'")." and ".date2pg("cronos")." < ".str_to_date2pg("'$this->reg_fecha'")." ";
        $result= $this->do_sql_show_error('_tdeletes', $sql);
        $this->nrows+= $this->clink->affected_rows($result);
    }

    private function test_reg_evento($id_evento, $id_usuario= null, $year) {
        $sql= "select count(*) as _count from treg_evento_$year where id_evento = $id_evento ";
        if (!empty($id_usuario))
            $sql.= "and id_usuario = $id_usuario";
        $result= $this->clink->query($sql);
        $nums= $this->clink->fetch_array($result)[0];
        return $nums;
    }

    /**
     * Eliminar los registros de teventos que no tienen refrencia el id_responsable en la tabla treg_evento
     */
    private function clean_teventos() {
        $sql= "select *, year(fecha_inicio_plan) as _year from teventos ";
        $sql.= "where year(fecha_inicio_plan) >= $this->year_init and year(fecha_inicio_plan) <= $this->year_end";
        $result= $this->clink->query($sql);
        $nums_tb = $this->clink->num_rows($result);

        $t= 0;
        $i= 0;
        $d= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            ++$t;
            if (empty($row['id_responsable']))
                continue;
            $nums= $this->test_reg_evento($row['id'], $row['id_responsable'], $row['_year']);
            if (!empty($nums))
                continue;

            $nums= $this->test_reg_evento($row['id'], null, $row['_year']);

            if (!empty($nums)) {
                ++$i;
                $sql= "insert into treg_evento_{$row['_year']} (id_evento, id_evento_code, id_usuario, id_responsable, compute, toshow, ";
                $sql.= "cumplimiento, user_check, rechazado, cronos, situs) values ({$row['id']}, '{$row['id_evento_code']}', ";
                $sql.= "{$row['id_responsable']}, 1, 0, 0, 6, 1, '{$row['cronos']}', '{$row['cronos']}', '{$row['situs']}'); ";
                $this->clink->query($sql);

            } else {
                ++$d;
                $sql= "delete from teventos where id = {$row['id']}";
                $_result= $this->clink->query($sql);

                if ($_result) {
                    $sql= "delete from tproceso_eventos_{$row['_year']} where id_evento = {$row['id']}";
                    $this->clink->query($sql);
                    $sql= "delete from tusuario_eventos_{$row['_year']} where id_evento = {$row['id']}";
                    $this->clink->query($sql);
                }
            }

            ++$j;
            if ($j > 500) {
                $j= 0;
                $r= (float)($t) / $nums_tb;
                $_r= $r*100;
                $_r= number_format($_r,3);
                bar_progressCSS(1, "clean_teventos ---> total=$t  insert=$i   delete=$d ($_r%)....", $r);
            }
        }

        if ($j)
            bar_progressCSS(1, "set_56 ---> total=$t    insert=$i    delete=$d Terminada 100%", 1);
    }
}

?>