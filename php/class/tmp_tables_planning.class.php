<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

if (!class_exists('Tbase_tables_planning'))
    include_once "base_tables_planning.class.php";

class Ttmp_tables_planning extends Tbase_tables_planning {

    public function  __construct($clink) {
        $this->clink= $clink;
        Tbase_tables_planning::__construct($clink);
    }

    /**
     * @param int $with_action
     * 0: la lista de procesos con jerarquia inferior al actual
     * 1: Lista de procesos a los que pertenece al usuario, con rango supeior al proceso actual
     * 2: los proceso en los que se incluye la auditori\a o el evento segun se trate
     */
    public function create_tmp_tprocesos($with_action, $mark_all= false, $id_proceso_sup= null) {
        if (isset($this->array_procesos)) 
            unset($this->array_procesos);
        $mark= $mark_all ? 1 : 'NULL';

        $obj= new Tproceso_item($this->clink);
        $obj->SetIdEntity($_SESSION['id_entity']);

        if ($with_action == 0) {
            $obj->SetIdProceso($this->id_proceso);
            $obj->SetTipo($this->tipo_prs);
            $obj->listar_in_order('eq_desc', true, null, false);
        }
        if ($with_action == 1) {
            $obj->SetIdUsuario($this->id_usuario);
            $obj->get_procesos_by_user('eq_asc');
        }
        if ($with_action == 2) {
            if (!empty($this->id_evento))
                $obj->GetProcesoEvento($this->id_evento);
            if (!empty($this->id_auditoria))
                $obj->GetProcesoAuditoria($this->id_auditoria, null, $id_proceso_sup);
        }

        $this->array_procesos= $obj->array_procesos;

        $sql= "drop table if exists ".stringSQL("_tprocesos");
        $result= $this->do_sql_show_error('create_tmp_tprocesos', $sql);
        if (!$result)
            $error= $this->error;

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_tprocesos")." ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_code ".field2pg("CHAR(12)").", ";
        $sql.= " tipo ".field2pg("TINYINT(2)").", ";
        $sql.= " marked ".field2pg("TINYINT(1)")." DEFAULT NULL ";
        $sql.= "); ";
        $result= $this->do_sql_show_error('create_tmp_tprocesos', $sql);

        reset($this->array_procesos);
        if (count($this->array_procesos) > 0) {
            foreach ($this->array_procesos as $row) {
                if (is_null($row))
                    continue;
                $id= $row['id'];
                $id_code= $row['id_code'];
                $tipo= $row['tipo'];

                $sql= "insert into _tprocesos ";
                $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
                $sql.= "values ($id, '$id_code',$tipo, $mark)";
                $result= $this->do_sql_show_error('create_tmp_tprocesos', $sql);
            }
        }

        $this->if_tprocesos= true;
        reset($this->array_procesos);
    }

    public function init_tmp_teventos() {
        $_teventos= !$this->if_auditoria ? '_teventos' : '_tauditorias';

        $sql= null;
        if (empty($this->id_proceso) && !empty($this->id_usuario)) {
            $sql= "update $_teventos set id_proceso= null, id_proceso_code= null ";
            if ($this->if_tauditoria)
                $sql.= ", id_evento= null, id_evento_code= null, toshow= null";
            $sql.= "; ";
        }
        if ($this->id_proceso == $this->id_proceso_asigna && $this->id_proceso_asigna != $_SESSION['id_entity'])
            $sql.= "update $_teventos set id_tipo_evento= NULL where id_proceso_asigna <> $this->id_proceso_asigna; ";

        if ($sql)
            $result= $this->do_multi_sql_show_error('init_tmp_teventos ', $sql);
    } 

    public function _create_tmp_tidx_tmp() {
        $this->if_tidx= false;
        $nums= 0;

        $this->debug_time('_create_tmp_tidx_tmp (group by)');
        $error= $this->_create_tmp_tidx();
        if (!is_null($error))
            return $error;

        $teventos= !$this->if_auditoria ? "_teventos" : "_tauditorias";
        $sql= "select distinct $teventos.* from $teventos ";
        $sql.= "order by indice asc, ";
        $sql.= $this->if_numering == _ENUMERACION_MANUAL || is_null($this->if_numering)? "numero asc, " : "numero_tmp asc, ";
        $sql.= "indice_plus asc";
        $result= $this->do_sql_show_error('_create_tmp_tidx_tmp', $sql);

        if (!is_null($this->error))
            return $this->error;
        $nums= $this->clink->num_rows($result);

        if ($this->toshow != _EVENTO_ANUAL || ($this->toshow == _EVENTO_ANUAL && $this->if_auditoria)) {
            $this->max_num_pages= (int)ceil((float)$nums/$this->max_row_in_page);
            if ($this->max_num_pages == 0)
                $this->max_num_pages= 1;
        }
        $this->debug_time('_create_tmp_tidx_tmp (group by)');

        $this->debug_time('fill_tmp_tidx');

        if (is_null($this->error))
            $this->fill_tmp_tidx($result);

        if (is_null($this->error))
            $this->if_tidx= true;
        $this->debug_time('fill_tmp_tidx');
    }

    private function fill_tmp_tidx($result) {
        $init_row= $this->init_row_temporary*$this->max_row_in_page;
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $id= null;

            if (!$this->if_auditoria) {
                if (!empty($row['id_auditoria'])) {
                    if (!isset($this->tidx_array_auditoria[$row['id_auditoria']]))
                        $this->tidx_array_auditoria[$row['id_auditoria']]= array($row['id'], false);
                    else
                        continue;
                }
                if (!empty($row['id_tarea'])) {
                    if (!isset($this->tidx_array_tarea[$row['id_tarea']]))
                        $this->tidx_array_tarea[$row['id_tarea']]= array($row['id'], false);
                    else
                        continue;
                }
                if (!empty($row['id_evento']) || boolean($row['tidx'])) {
                    $id= !empty($row['id_evento']) ? $row['id_evento'] : $row['id'];
                    if (!isset($this->tidx_array_evento[$id]))
                        $this->tidx_array_evento[$id]= array($row['id'], false);
                    else
                        continue;
            }   }

            if ($this->if_auditoria) {
                if (!empty($row['id_auditoria']) || boolean($row['tidx'])) {
                    $id= !empty($row['id_auditoria']) ? $row['id_auditoria'] : $row['id'];
                    if (!isset($this->tidx_array_auditoria[$id][$row['id_proceso']]))
                        $this->tidx_array_auditoria[$id][$row['id_proceso']]= array($row['id'], false);
                    else
                        continue;
            }   }

            $continue= true;
            if ($this->limited && ($this->toshow != _EVENTO_ANUAL || ($this->toshow == _EVENTO_ANUAL && $this->if_auditoria))) {
                if (($i < $init_row && $init_row != 0) || $i > ($init_row + $this->max_row_in_page)) {
                    $continue= false;
            }   }

            ++$i;
            if (!$continue)
                continue;

            $this->tidx_array[$row['id']][$row['id_proceso']]= array('month'=>null, 'flag'=>false);

            $numero= setNULL($this->if_numering == _ENUMERACION_MANUAL ? $row['numero'] : $row['numero_tmp']);
            $numero_plus= setNULL_str($row['numero_plus']);

            $id_tipo= 0;
            $indice= 0;
            $indice_plus= 0;
            $empresarial= 0;

            $id= setNULL($id);
            $id_auditoria= setNULL($row['id_auditoria']);
            $id_tarea= setNULL($row['id_tarea']);

            $sql.= "insert into _tidx ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "values ($id, $id_auditoria, $id_tarea, $numero, $numero_plus, $empresarial, ";
            $sql.= "$id_tipo, $indice, $indice_plus, '{$row['cronos_asigna']}' ";
            $sql.= $this->if_auditoria ? ", {$row['id_proceso']}" : ", NULL";
            $sql.= "); ";

            ++$j;
            if ($j > 1000) {
                $this->do_multi_sql_show_error('fill_tmp_tidx', $sql);
                $j= 0;
                $sql= null;
            }
        }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('fill_tmp_tidx', $sql);

        if ($this->limited && ($this->toshow != _EVENTO_ANUAL || ($this->toshow == _EVENTO_ANUAL && $this->if_auditoria))) {
            $this->max_num_pages= (int)ceil((float)$i/$this->max_row_in_page);
            if ($this->max_num_pages == 0)
                $this->max_num_pages= 1;
        }
    }

    public function create_tmp_treg_evento_tmp($limited=false, $toshow= null) {
        $error= $this->_create_tmp_treg_evento();
        if (!is_null($error))
            return $error;

        $date= null;
        if (empty($this->id_usuario)) {
            $time= new TTime();
            $month= !empty($this->month) ? $this->month : 12;
            $day= $time->longmonth($month, $this->year);
            $date= $this->year."-".str_pad($month,2,'0',STR_PAD_LEFT)."-".str_pad($day,2,'0',STR_PAD_LEFT)." 23:59:59";
        }

        $_teventos= !$this->if_auditoria ? '_teventos' : '_tauditorias';
        $sql= "select distinct $_teventos.id as _id, $_teventos.id_responsable as _id_responsable, id_responsable_2, ";
        $sql.= "responsable_2_reg_date, $_teventos.id_evento as _id_evento, $_teventos.id_auditoria as _id_auditoria from $_teventos ";
        if ($limited && $this->if_tidx) {
            if ($this->if_auditoria) {
                $sql.= ", _tidx  where (_tidx.id is not null and ($_teventos.id = _tidx.id or $_teventos.id_auditoria = _tidx.id)) ";
                $sql.= "or (_tidx.id_auditoria is not null and ($_teventos.id = _tidx.id_auditoria or $_teventos.id_auditoria = _tidx.id_auditoria))";
            } else {
                $sql.= ", _tidx  where (_tidx.id is not null and ($_teventos.id = _tidx.id or $_teventos.id_evento = _tidx.id)) ";
                $sql.= "or (_tidx.id_tarea is not null and $_teventos.id_tarea = _tidx.id_tarea)";
            }
        }
        $result= $this->do_sql_show_error('create_tmp_treg_evento_tmp', $sql);

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];
            $id_responsable= $row['_id_responsable'];
            $id_responsable_2= !empty($row['id_responsable_2']) ? $row['id_responsable_2'] : null;
            $responsable_date= !empty($row['responsable_2_reg_date']) ? $row['responsable_2_reg_date'] : null;

            $sql.= "insert into _treg_evento ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "select treg_evento_{$this->year}.* from treg_evento_{$this->year} ";
            $sql.= !$this->if_auditoria ? "where treg_evento_{$this->year}.id_evento = $id " : "where treg_evento_{$this->year}.id_auditoria = $id ";

            if (empty($this->id_usuario)) {
                $sql.= "and (treg_evento_{$this->year}.id_usuario = $id_responsable ";

                if (!empty($id_responsable_2) && !empty($responsable_date)) {
                    if (!empty($this->month) && (int)strtotime($date) <= (int)strtotime($responsable_date))
                        $sql.= "or (treg_evento_{$this->year}.cronos <= '$responsable_date' and treg_evento_{$this->year}.id_usuario = $id_responsable_2) ";

                    if (empty($this->month))
                        $sql.= "or (date(treg_evento_{$this->year}.cronos) <= date('$responsable_date') and treg_evento_{$this->year}.id_usuario = $id_responsable_2) ";
                }
                $sql.= ") ";
            } else
                $sql.= "and treg_evento_{$this->year}.id_usuario = $this->id_usuario ";
            if (!empty($this->date_eval_cutoff))
                $sql.= "and treg_evento_{$this->year}.cronos < '$this->date_eval_cutoff' ";
            $sql.= "order by treg_evento_{$this->year}.cronos desc; ";

            ++$i;
            ++$j;
            if ((int)$i >= 2000) {
                $this->do_multi_sql_show_error('create_tmp_treg_evento_tmp', $sql);
                $i= 0;
                $sql= null;
            }
        }
        if (!empty($sql))
            $this->do_multi_sql_show_error('create_tmp_treg_evento_tmp', $sql);

        if (empty($this->error))
            $this->if_treg_evento= true;

        $sql= "select count(*) from _treg_evento";
        $result= $this->do_sql_show_error('create_tmp_treg_evento_tmp', $sql);
        $row= $this->clink->fetch_array($result);
        $this->cant= $row[0];

        if (empty($this->cant))
            return _EMPTY;
    }

    protected function insert_into_teventos($row) {
        $_idx= setNULL($row['_idx']);

        $numero= setNULL($row['numero']);
        $id_responsable= setNULL($row['id_responsable']);
        $id_responsable_2= setNULL($row['id_responsable_2']);
        $responsable_2_reg_date= setNULL_str($row['responsable_2_reg_date']);

        $id_responsable_asigna= setNULL($row['id_responsable_asigna']);
        $id_proceso_asigna= setNULL($row['id_proceso_asigna']);
        $id_user_asigna= setNULL($row['id_user_asigna']);

        $nombre= setNULL_str($row['nombre']);
        $lugar= setNULL_str($row['lugar']);
        $origen_data= setNULL_str($row['origen_data_asina']);
        $periodicidad= setNULL($row['periodicidad']);
        $cronos_asigna= setNULL_str($row['cronos_asigna']);
        $funcionario= setNULL_str($row['funcionario']);
        $id_tipo_evento= setNULL($row['id_tipo_evento']);
        $user_check= setNULL($row['user_check']);
        $descripcion= setNULL_str($row['descripcion']);

        $id_evento= setNULL($row['id_evento']);
        $id_evento_code= setNULL_str($row['id_evento_code']);

        $id_tarea= setNULL($row['id_tarea']);
        $id_tarea_code= setNULL_str($row['id_tarea_code']);

        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);

        $id_tipo_reunion= setNULL($row['id_tipo_reunion']);
        $id_tipo_reunion_code= setNULL_str($row['id_tipo_reunion_code']);

        $id_tematica= setNULL($row['id_tematica']);
        $id_tematica_code= setNULL_str($row['id_tematica_code']);

        $id_copyfrom= setNULL($row['id_copyfrom']);
        $id_copyfrom_code= setNULL_str($row['id_copyfrom_code']);

        $ifassure= boolean2pg($row['ifassurre']);
        $id_secretaria= setNULL($row['id_secretary']);
        $id_archivo= setNULL($row['id_archivo']);
        $id_archivo_code= setNULL_str($row['id_archivo_code']);
        $numero_plus= setNULL_str($row['numero_plus']);

        $indice= setNULL($row['indice']);
        $indice_plus= setNULL($row['indice_plus']);
        $tidx= setNULL($row['tidx']);

        $toshow= setNULL($row['toshow']);

        $cumplimiento= setNULL($row['cumplimiento']);
        $observacion= setNULL_str($row['observacion']);
        $rechazado= setNULL_str($row['rechazado']);
        $aprobado= setNULL_str($row['aprobado']);
        $cronos= setNULL_str($row['cronos']);

        $sql= "insert into _teventos () values ({$row['id']}, $_idx, '{$row['id_code']}', $numero, $id_responsable, $id_responsable_2, ";
        $sql.= "$responsable_2_reg_date, $id_responsable_asigna, $id_proceso_asigna, $id_user_asigna, $origen_data, $cronos_asigna, ";
        $sql.= "$funcionario, $nombre, '{$row['fecha_inicio_plan']}', '{$row['fecha_fin_plan']}', $periodicidad, {$row['empresarial']}, ";
        $sql.= "$id_tipo_evento, $toshow, $user_check, $descripcion, $lugar, $id_evento, $id_evento_code, $id_tarea, $id_tarea_code, ";
        $sql.= "$id_auditoria, $id_auditoria_code, $id_tipo_reunion, $id_tipo_reunion_code, $id_tematica, $id_tematica_code, $id_copyfrom, ";
        $sql.= "$id_copyfrom_code, $ifassure, $id_secretaria, $id_archivo, $id_archivo_code, $numero_plus, {$row['_day']}, ";
        $sql.= "{$row['_month']}, {$row['_year']}, {$row['_id_proceso']}, '{$row['_id_proceso_code']}', $indice, $indice_plus, ";
        $sql.= "$tidx, NULL, $cumplimiento, $observacion, $aprobado, $rechazado, $cronos); ";

        return $sql;
    }

    protected function insert_into_treg_evento($row) {
        $id_usuario= setNULL($row['id_usuario']);
        $id_responsable= setNULL($row['id_responsable']);
        $origen_data= setNULL_str($row['origen_data']);
        $aprobado= setNULL_str($row['aprobado']);
        $rechazado= setNULL_str($row['rechazado']);
        $cumplimiento= setNULL($row['cumplimiento']);
        $observacion= setNULL_str($row['observacion']);
        $compute= setNULL($row['compute']);
        $toshow= setNULL($row['toshow']);
        $user_check= setNULL($row['user_check']);
        $hide_synchro= setNULL($row['hide_synchro']);

        $id_tarea= setNULL($row['id_tarea']);
        $id_tarea_code= setNULL_str($row['id_tarea_code']);

        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);

        $reg_fecha= setNULL_str($row['reg_fecha']);
        $horas= setNULL($row['horas']);
        $cronos_syn= setNULL_str($row['cronos_syn']);
        $outlook= boolean2pg($row['outlook']);

        $sql= "insert into _treg_evento () values ({$row['id']}, {$row['id_evento']}, '{$row['id_evento_code']}', $id_usuario, ";
        $sql.= "$id_responsable, $origen_data, $aprobado, $rechazado, $cumplimiento, $observacion, $compute, $toshow, $user_check, ";
        $sql.= "$hide_synchro, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, $reg_fecha, $horas, '{$row['cronos']}', ";
        $sql.= "$cronos_syn, '{$row['situs']}', $outlook); ";

        return $sql;
    }

    public function create_temporary_plantrab($result, $id_usuario= null) {
        $this->_create_tmp_teventos();
        $this->_create_tmp_treg_evento();

        $this->cumplimiento= null;
        $this->reg_fecha= null;

        $this->debug_time('create_temporary_plantrab_2');

        $i= 0;
        $j= 0;
        $sql= null; 
        while ($row= $this->clink->fetch_array($result)) {
            $row_reg= $this->getEvento_reg_user($row['id']);
            if (empty($row_reg))
                continue;
            if ($row_reg['hide_synchro'])
                continue;
            if (!$row_reg['toshow'])
                continue; 

            $row['id_proceso']= null;
            $row['id_proceso_code']= null;
            $row['cumplimiento']= $row_reg['cumplimiento'];
            $row['observacion']= $row_reg['observacion'];
            $row['aprobado']= $row_reg['aprobado'];
            $row['rechazado']= $row_reg['rechazado'];
            $row['cronos']= $row_reg['cronos'];

            $sql_evento= $this->insert_into_teventos($row);
            $sql_reg_evento= $this->insert_into_treg_evento($row_reg);
            $sql.= $sql_evento.$sql_reg_evento;

            ++$j;
            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('create_temporary_plantrab', $sql);

                $sql= null;
                $i= 0;
                if (!is_null($this->error))
                    break;
            }
        }
        if ($i > 0 && is_null($this->error))
            $this->do_multi_sql_show_error('create_temporary_plantrab', $sql);

        if (is_null($this->error)) {
            $this->if_teventos= true;
            $this->if_treg_evento= true;
        }
        return $j;
    }

    public function create_tmp_treg_evento_user($user_check= null, $limit_unique= false) {
        $limit_unique= !is_null($limit_unique) ? $limit_unique : false;
        $this->if_treg_evento= false;
        $error= $this->_create_tmp_treg_evento();
        if (!is_null($error))
            return $error;

        $teventos= $this->if_teventos ? "_teventos" : "teventos";
        $sql= "select id, id_responsable from $teventos";
        $result= $this->do_sql_show_error('create_tmp_treg_evento_user', $sql);

        $sql= null;
        $i= 0;
        $j= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $id_usuario= !empty($this->id_usuario) ? $this->id_usuario : $row['id_responsable'];

            $sql.= "insert into _treg_evento ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "select treg_evento_{$this->year}.* from treg_evento_{$this->year} ";
            $sql.= "where treg_evento_{$this->year}.id_evento = {$row['id']} and treg_evento_{$this->year}.id_usuario = $id_usuario ";
            if (!is_null($user_check))
                $sql.= "and treg_evento_{$this->year}.user_check = ".boolean2pg($user_check)." ";
            if (!empty($this->date_eval_cutoff))
                $sql.= "and treg_evento_{$this->year}.cronos <  '$this->date_eval_cutoff' ";
            $sql.= "order by cronos desc ";
            if ($limit_unique)
                $sql.= "limit 1 ";
            $sql.= "; ";

            ++$j;
            ++$i;
            if ($i >= 500) {
                $this->do_multi_sql_show_error('create_tmp_treg_evento_user', $sql);
                $sql= null;
                $i= 0;

                if (!is_null($this->error))
                    break;
            }
        }
        if (!empty($sql) && is_null($this->error))
            $this->do_multi_sql_show_error('create_tmp_treg_evento_user', $sql);

        if (is_null($this->error))
            $this->if_treg_evento= true;

        $sql= "drop table if exists ".stringSQL("_treg_evento_small");
        $result= $this->do_sql_show_error("create_tmp_treg_evento_user", $sql);

        $this->cant= $j;
        if (empty($this->cant))
            return _EMPTY;
    }

    private function _sql_tusuarios_copy($id_proceso, $write_prs, $mark, $false, $array_usuarios, $flag) {
        if (empty($this->id_usuario) && !empty($id_proceso)) {
           $prs= $write_prs ? $id_proceso : 0;

           $sql= "select distinct tusuarios.id as _id, nivel, $prs as prs, $mark as marked, tusuarios.id_proceso as _id_proceso, ";
           $sql= "tusuarios.id_proceso_code as _id_proceso_code from tusuario_procesos, tusuarios where id_grupo is NULL  ";
           $sql.= "and tusuario_procesos.id_usuario = tusuarios.id and tusuario_procesos.id_proceso = $id_proceso ";
           $sql.= "union  ";
           $sql.= "select tusuarios.id as _id, nivel, $prs as prs, $false as marked, tusuarios.id_proceso as _id_proceso,  ";
           $sql.= "tusuarios.id_proceso_code as _id_proceso_code from tusuarios, view_usuario_proceso_grupos ";
           $sql.= "where view_usuario_proceso_grupos.id_proceso = $id_proceso and tusuarios.id = view_usuario_proceso_grupos.id_usuario; ";

           if (!$flag)
               return $sql;
           $this->_to_array_usuarios('sql_tusuarios', $sql, $array_usuarios, $prs, $mark);
           return $this->array_reg_usuarios;
       }

       if ((empty($this->id_usuario) && empty($id_proceso)) && (!empty($this->id_evento) || !empty($this->id_auditoria))) {
           $prs= $write_prs ? 'tusuarios.id_proceso' : 0;

           $sql= "select distinct tusuarios.id as _id, nivel, $prs as prs, $mark as marked, tusuarios.id_proceso as _id_proceso, ";
           $sql.= "tusuarios.id_proceso_code as _id_proceso_code from tusuario_eventos_{$this->year}, ";
           $sql.= "tusuarios where id_grupo is NULL and tusuario_eventos_{$this->year}.id_usuario = tusuarios.id ";
           if (!empty($this->id_auditoria))
               $sql.= "and tusuario_eventos_{$this->year}.id_auditoria = $this->id_auditoria ";
           if (!empty($this->id_evento))
               $sql.= "and tusuario_eventos_{$this->year}.id_evento = $this->id_evento ";
           $sql.= "union  ";
           $sql.= "select distinct view_usuario_grupos.id as _id, nivel, $prs as prs, $mark as marked, view_usuario_grupos.id_proceso as _id_proceso, ";
           $sql.= "view_usuario_grupos.id_proceso_code as _id_proceso_code from tusuario_eventos_{$this->year}, view_usuario_grupos ";
           $sql.= "where view_usuario_grupos.id_grupo = tusuario_eventos_{$this->year}.id_grupo ";
           if (!empty($this->id_auditoria))
               $sql.= "and tusuario_eventos_{$this->year}.id_auditoria = $this->id_auditoria ";
           if (!empty($this->id_evento))
               $sql.= "and tusuario_eventos_{$this->year}.id_evento = $this->id_evento ";
           $sql.= "; ";

           if (!$flag)
               return $sql;
           $this->_to_array_usuarios('sql_tusuarios', $sql, $array_usuarios, $prs, $mark);
           return $array_usuarios;
       }
    }

    private function _sql_tusuarios($id_proceso, $write_prs, $mark, $false, $array_usuarios) {
        if (empty($this->id_usuario) && !empty($id_proceso)) {
            $prs= $write_prs ? $id_proceso : 0;

            $sql= "select distinct _ctusuarios.id as _id, nivel, $prs as prs, $mark as marked, _ctusuarios.id_proceso as _id_proceso, ";
            $sql.= "_ctusuarios.id_proceso_code as _id_proceso_code from tusuario_procesos, ";
            $sql.= "_ctusuarios where id_grupo is NULL  and _ctusuarios.id = tusuario_procesos.id_usuario ";
            $sql. "and tusuario_procesos.id_proceso = $id_proceso; ";
            $this->_to_array_usuarios('sql_tusuarios', $sql, $array_usuarios, $prs, $mark);

            $sql= "select _ctusuarios.id as _id, nivel, $prs as prs, $false as marked, _ctusuarios.id_proceso as _id_proceso, ";
            $sql.= "_ctusuarios.id_proceso_code as _id_proceso_code from _ctusuarios, view_usuario_proceso_grupos ";
            $sql.= "where _ctusuarios.id = view_usuario_proceso_grupos.id_usuario and view_usuario_proceso_grupos.id_proceso = $id_proceso; ";
            $this->_to_array_usuarios('sql_tusuarios', $sql, $array_usuarios, $prs, $mark);

            return $array_usuarios;
        }

        if ((empty($this->id_usuario) && empty($id_proceso)) && (!empty($this->id_evento) || !empty($this->id_auditoria))) {
            $prs= $write_prs ? 'tusuarios.id_proceso' : 0;

            $sql= "select distinct _ctusuarios.id as _id, nivel, $prs as prs, $mark as marked, _ctusuarios.id_proceso as _id_proceso, ";
            $sql.= "_ctusuarios.id_proceso_code as _id_proceso_code from tusuario_eventos_{$this->year},  ";
            $sql.= "_ctusuarios where id_grupo is NULL and tusuario_eventos_{$this->year}.id_usuario = _ctusuarios.id ";
            if (!empty($this->id_auditoria))
                $sql.= "and tusuario_eventos_{$this->year}.id_auditoria = $this->id_auditoria ";
            if (!empty($this->id_evento))
                $sql.= "and tusuario_eventos_{$this->year}.id_evento = $this->id_evento ";
            $sql.= "; ";
            $this->_to_array_usuarios('sql_tusuarios', $sql, $array_usuarios, $prs, $mark);

            $sql= "select distinct _ctusuarios.id as _id, nivel, $prs as prs, $mark as marked, _ctusuarios.id_proceso as _id_proceso, ";
            $sql.= "_ctusuarios.id_proceso_code as _id_proceso_code  from tusuario_eventos_{$this->year}, ";
            $sql.= "tusuario_grupos, _ctusuarios where tusuario_eventos_{$this->year}.id_usuario is NULL ";
            $sql.= "and tusuario_eventos_{$this->year}.id_grupo = tusuario_grupos.id_grupo ";
            if (!empty($this->id_auditoria))
                $sql.= "and tusuario_eventos_{$this->year}.id_auditoria = $this->id_auditoria ";
            if (!empty($this->id_evento))
                $sql.= "and tusuario_eventos_{$this->year}.id_evento = $this->id_evento ";
            $sql.= "and _ctusuarios.id = tusuario_grupos.id_usuario; ";
            $this->_to_array_usuarios('sql_tusuarios', $sql, $array_usuarios, $prs, $mark);

            return $array_usuarios;
        }
    }

    public function sql_tusuarios($id_proceso= null, $flag= false, $write_prs= false, $mark_all= false) {
        $flag= is_null($flag) ? false : $flag;
        $write_prs= is_null($write_prs) ? false : $write_prs;
        $mark_all= is_null($mark_all) ? false : $mark_all;

        $false= $_SESSION["_DB_SYSTEM"] == "mysql" ? 0 : 'false';
        $true= $_SESSION["_DB_SYSTEM"] == "mysql" ? 1 : 'true';

        $mark= $mark_all ? $true : $false;
        $_ctusuarios= ($this->if_copy_tusuarios && $this->use_copy_tusuarios) ? true : false;

        $sql= null;
        $array_usuarios= null;

        if (!$_ctusuarios) {
            return $this->_sql_tusuarios_copy($id_proceso, $write_prs, $mark, $false, $array_usuarios, $flag);
        }
        if ($_ctusuarios && $flag) {
            return $this->_sql_tusuarios($id_proceso, $write_prs, $mark, $false, $array_usuarios);
        }
    }

    private function _to_array_usuarios($function, $sql, &$array_usuarios, $prs, $mark) {
        static $i;
        if (is_null($array_usuarios))
            $i= 0;
        if ($mark == 0 || $mark == 'false')
            $mark= null;
        if ($prs == 0)
            $prs= null;

        $result= $this->do_sql_show_error($function, $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                'eliminado'=>$row['eliminado'], 'usuario'=>$row['usuario'], 'nivel'=>$row['nivel'],
                'id_proceso'=>$row['_id_proceso'], 'id_proceso_code'=>$row['_id_proceso_code'], '_id'=>$row['_id'],
                'prs'=>$prs, 'marked'=>$mark
            );
            $array_usuarios[$row['_id']]= $array;
            ++$i;
        }
        return $array_usuarios;
    }

    public function create_tmp_tusuarios_from_treg_evento($id, $id_usuario= null, $write_prs= false, $mark_all= false) {
        if (!$this->if_tusuarios) $this->_create_tmp_tusuarios();

        $result_user= null;
        if (empty($id_usuario)) {
            $sql= "select distinct id_usuario from treg_evento_{$this->year} ";
            $sql.= $this->if_auditoria ? "where treg_evento_{$this->year}.id_auditoria = $id " : "where treg_evento_{$this->year}.id_evento = $id; ";
            $result_user= $this->do_sql_show_error('sql_tusuarios_from_treg_evento', $sql);
        }

        $write_prs= is_null($write_prs) ? false : $write_prs;
        $prs= $write_prs ? 'tusuarios.id_proceso' : 'NULL';

        $mark_all= is_null($mark_all) ? false : $mark_all;
        $mark= $mark_all ? 1 : 'NULL';

        $sql= null;
        if (!empty($id_usuario)) {
            $sql= "insert into _tusuarios ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
            $sql.= "select id, nivel, $prs, $mark from tusuarios where id = $id_usuario; ";

        } else {
            while ($row= $this->clink->fetch_array($result_user)) {
                if (is_array($this->array_usuarios_entity) && !array_key_exists($row['id_usuario'], $this->array_usuarios_entity))
                    continue;
                $sql.= "insert into _tusuarios ";
                $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
                $sql.= "select distinct id, nivel, $prs, $mark from tusuarios where tusuarios.id = {$row['id_usuario']}; ";
            }
        }

        if (!is_null($sql))
            $result= $this->do_multi_sql_show_error('create_tmp_tusuarios_from_treg_evento', $sql);
        if (is_null($this->error))
            $this->if_tusuarios= true;
    }

    public function update_tmp_tprocesos_mark($id, $mark_all= false) {
        $mark= $mark_all ? 1 : 0;
        if (!$this->if_tprocesos)
            return;

        $sql= "update _tprocesos set marked= ".boolean2pg($mark)." where id = $id ";
        $this->do_sql_show_error('update_tmp_tprocesos_mark', $sql);
    }

    /**
     * son eliminados los usuarios con marked == null
     */
    public function update_tmp_tusuarios($id_proceso, $mark_all= false) {
        $mark= $mark_all ? 1 : 0;
        $sql= "update _tusuarios set marked= ".boolean2pg($mark)." where id_proceso = $id_proceso ";
        $this->do_sql_show_error('update_tmp_tusuarios', $sql);
    }

    public function add_to_tmp_tusuarios($id_proceso= null, $flag= false, $write_prs= false, $mark_all= false) {
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        if (!$flag && $this->use_copy_tusuarios)
            $flag= true;

        if (!$this->if_tusuarios)
            $this->_create_tmp_tusuarios();
        $sql= null;
        $i= 0;

        if ($flag) {
            $array_usuarios= $this->sql_tusuarios($id_proceso, $flag, $write_prs, $mark_all);

            foreach ($array_usuarios as $row) {
                $marked= boolean2pg($row['marked']);
                $prs= setNULL($row['prs']);

                $sql.= "insert into _tusuarios ";
                $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
                $sql.= " select id, nivel, $prs, $marked from tusuarios where id= {$row['_id']}; ";

                ++$i;
                if ($i >= 1000) {
                    $this->do_multi_sql_show_error('add_to_tmp_tusuarios', $sql);
                    $sql= null;
                    $i= 0;
        }   }   }

        if (!$flag && !$this->use_copy_tusuarios) {
            $sql.= "insert into _tusuarios ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
            $sql.= $this->sql_tusuarios($id_proceso, $flag, $write_prs, $mark_all);

            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('add_to_tmp_tusuarios', $sql);
                $sql= null;
                $i= 0;
            }
        }

        if (!is_null($sql))
            $this->do_multi_sql_show_error('add_to_tmp_tusuarios', $sql);

        $sql= "update _tusuarios set marked = NULL where marked = ".boolean2pg(0)."; ";
        $sql.= "update _tusuarios set id_proceso = NULL where id_proceso = 0;";
        $this->do_multi_sql_show_error('add_to_tmp_tusuarios', $sql);

        $this->if_tusuarios= true;
        return $this->error;
    }

    public function create_tmp_tproceso_eventos($result= null, $if_small= false, $init_tmp_table= true) {
        $plus= $if_small ? "_small" : null;
        $init_tmp_table= is_null($init_tmp_table) ? true : $init_tmp_table;
        if (!$this->if_tprocesos && empty($this->id_proceso))
            return null;

        $this->debug_time('create_tmp_tproceso_eventos');

        if ($init_tmp_table)
            $this->_create_tmp_tproceso_eventos(false);

        if (!$this->if_tproceso_eventos)
            return;

        if (is_null($result)) {
            $teventos= $this->if_teventos ? "_teventos$plus" : "teventos";
            $sql= "select *, id as _id from $teventos ";
            $result= $this->do_sql_show_error('_create_tmp_tproceso_eventos', $sql);
        }

        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if (!$if_small && $this->if_teventos && $this->if_tproceso_eventos && !$init_tmp_table)
                if (boolean($row['_idx']))
                    continue;

            if (is_array($this->array_procesos)) {
                reset($this->array_procesos);
                foreach ($this->array_procesos as $id => $prs) {
                    ++$i;
                    $sql.= "insert into _tproceso_eventos ";
                    $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
                    $sql.= "select * from tproceso_eventos_{$this->year} where tproceso_eventos_{$this->year}.id_evento = {$row['_id']} ";
                    $sql.= "and id_proceso = $id order by cronos desc limit 1; ";
                }
            } else {
                ++$i;
                $sql.= "insert into _tproceso_eventos ";
                $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
                $sql.= "select * from tproceso_eventos_{$this->year} where tproceso_eventos_{$this->year}.id_evento = {$row['_id']} ";
                $sql.= "order by cronos desc limit 1; ";
            }

            if ($i >= 1000) {
                $this->do_multi_sql_show_error("_create_tmp_tproceso_eventos", $sql);
                $i= 0;
                $sql= null;
            }
        }
        if ($sql)
            $this->do_multi_sql_show_error("_create_tmp_tproceso_eventos", $sql);

        $this->if_tproceso_eventos= is_null($this->error) ? true : false;
        $this->debug_time('create_tmp_tproceso_eventos');
    }

    public function _fix_indice_plus_in_tproceso_eventos($if_small= false) {
        $plus= $if_small ? "_small" : null;
        $teventos= $this->if_teventos ? "_teventos$plus" : "teventos";

        $sql= "update $teventos set indice_plus= 0 where indice_plus is null;";
        $result= $this->do_sql_show_error('_fix_indice_plus_in_tproceso_eventos', $sql);

        if ($this->if_tproceso_eventos) {
            $sql= "update _tproceso_eventos, $teventos set _tproceso_eventos.indice_plus= $teventos.indice_plus ";
            $sql.= "where _tproceso_eventos.id_evento= $teventos.id";
            $result= $this->do_sql_show_error('_fix_indice_plus_in_tproceso_eventos', $sql);

            $sql= "update _tproceso_eventos set indice_plus= 0 where indice_plus is null;";
            $result= $this->do_sql_show_error('_fix_indice_plus_in_tproceso_eventos', $sql);  
        }
    }
}


/*
 * Clases adjuntas o necesarias
 */
if (!defined('_CLASS_Tproceso_item') && !defined('_CLASS_Tproceso'))
    include_once "proceso_item.class.php";
