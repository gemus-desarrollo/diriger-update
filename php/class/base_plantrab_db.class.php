<?php
/**
* @author Geraudis Mustelier Portuondo
* @copyright 2020
*/

if (!class_exists('Tregister_planning'))
    include_once "register_planning.class.php";

class Tbase_plantrab_db extends Tregister_planning {
    protected $array_cascade_up;
    protected $obj_tables;
    protected $obj_reg;
    protected $create_tmp_table_tidx;

    public $array_eventos_restricted;

    public $monthstack;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tregister_planning::__construct($clink);

        $this->create_tmp_table_tidx= true;
        $this->obj_tables= new Ttmp_tables_planning($this->clink);
        $this->obj_reg= new Tregister_planning($this->clink);
    }

    protected function test_if_modified($id_evento) {
        $array = null;

        $sql = "select id_user_asigna as _id_responsable, _teventos.cronos as fecha from _teventos, _treg_evento ";
        $sql .= "where _teventos.id = _treg_evento.id_evento and _teventos.id = $id_evento and _treg_evento.compute = " . boolean2pg(1) . " ";
        $sql .= "and " . date2pg("_teventos.cronos") . " > " . date2pg("_treg_evento.cronos") . " and _treg_evento.rechazado is null ";
        $sql .= "order by _treg_evento.cronos desc";
        $result = $this->do_sql_show_error('test_if_modified', $sql);

        if ($this->cant == 0) {
            $sql = "select _treg_evento.id_responsable as _id_responsable, _treg_evento.cronos as fecha, _treg_evento.observacion as _observacion ";
            $sql .= "from _teventos, _treg_evento where _teventos.id = _treg_evento.id_evento and _teventos.id = $id_evento ";
            $sql .= "and _treg_evento.compute = ".boolean2pg(1)." and cumplimiento in (4, 5, 6, 7) and _treg_evento.rechazado is null ";
            $sql.= "order by fecha desc ";
            $result = $this->do_sql_show_error('test_if_modified', $sql);
        }
        if ($this->cant > 0) {
            $row = $this->clink->fetch_array($result);
            $array = array('fecha' => $row['fecha'], 'id_responsable' => $row['_id_responsable'], 'observacion' => $row['_observacion']);
        }
        return $array;
    }

    protected function test_if_reprogram($id_evento) {
        $array= null;

        $sql= "select _treg_evento.id_responsable as _id_responsable, _treg_evento.cronos as fecha, _treg_evento.observacion as _observacion ";
        $sql.= "from _teventos, _treg_evento where _teventos.id = _treg_evento.id_evento and _teventos.id = $id_evento ";
        $sql.= "and _teventos.id_copyfrom is not null and _treg_evento.cumplimiento = "._REPROGRAMADO;
        $result= $this->do_sql_show_error('test_if_reprogram', $sql);

        if ($this->cant > 0) {
            $row= $this->clink->fetch_array($result);
            $array= array('fecha'=>$row['fecha'], 'id_responsable'=>$row['_id_responsable'], 'observacion'=>$row['_observacion']);
        }
        return $array;
    }

    private function  _select_for_tmp_teventos($fecha_inicio=  null, $fecha_fin= null, $id_evento= null) {
        if ($this->toshow == _EVENTO_ANUAL
            || ($this->tipo_plan == _PLAN_TIPO_MEETING || $this->tipo_plan == _PLAN_TIPO_AUDITORIA || $this->tipo_plan == _PLAN_TIPO_SUPERVICION)) {
            $this->list_date_interval($fecha_inicio, $fecha_fin, 'all_year');
        } else {
            if (empty($fecha_inicio) && empty($fecha_fin)) {
                $this->list_date_interval($fecha_inicio, $fecha_fin, 'all_month');
            } else {
                if (!empty($fecha_inicio))
                    $fecha_inicio= $fecha_inicio." 00:00:00";
                if (!empty($fecha_fin))
                    $fecha_fin= $fecha_fin." 23:59:00";
        }   }

        $sql= "select distinct teventos.id, null, id_code, numero, ";
        if ((!empty($this->id_proceso) && empty($this->id_usuario)))
            $sql.= "null, null, null, ";
        else
            $sql.= "teventos.id_responsable, id_responsable_2, responsable_2_reg_date, ";

        $sql.= "teventos.id_responsable as id_responsable_asigna, teventos.id_proceso as id_proceso_asigna, teventos.id_usuario as id_user_asigna, ";
        $sql.= "teventos.origen_data, teventos.cronos as cronos_asigna, funcionario, nombre, fecha_inicio_plan, fecha_fin_plan, periodicidad, ";

        /*
        $sql.= (!empty($this->id_proceso) && empty($this->id_usuario)) ? "null, " : "teventos.empresarial, ";
        $sql.= (!empty($this->id_proceso) && empty($this->id_usuario)) ? "null, " : "teventos.id_tipo_evento, ";
        $sql.= (!empty($this->id_proceso) && empty($this->id_usuario)) ? "null, " : "teventos.toshow, ";
        */
        $sql.= "teventos.empresarial, teventos.id_tipo_evento, teventos.toshow, ";

        $sql.= "teventos.user_check, descripcion, lugar, teventos.id_evento as _id_evento, teventos.id_evento_code as _id_evento_code, ";
        $sql.= "teventos.id_tarea, teventos.id_tarea_code, teventos.id_auditoria, teventos.id_auditoria_code, id_tipo_reunion, id_tipo_reunion_code, ";
        $sql.= "teventos.id_tematica,teventos.id_tematica_code, teventos.id_copyfrom, teventos.id_copyfrom_code, ";
        $sql.= "ifassure, id_secretary, id_archivo, id_archivo_code, numero_plus, ";
        $sql.= day2pg("fecha_fin_plan")." as _day, ".month2pg("fecha_fin_plan")." as _month, ".year2pg("fecha_inicio_plan")." as _year, ";

        if (!empty($this->id_proceso) && empty($this->id_usuario)) {
            $sql.= "tproceso_eventos_{$this->year}.id_proceso as _id_proceso, tproceso_eventos_{$this->year}.id_proceso_code as _id_proceso_code, " ;
        } else {
            $sql.= "0 as _id_proceso, '0' as _id_proceso_code, ";
        }
        /*
        $sql.= (!empty($this->id_proceso) && empty($this->id_usuario)) ? "null, " : "teventos.indice, ";
        $sql.= (!empty($this->id_proceso) && empty($this->id_usuario)) ? "null, " : "teventos.indice_plus, ";
        */
        $sql.= "teventos.indice, teventos.indice_plus, ";

        $sql.= "tidx, NULL, NULL, NULL, NULL, NULL, NULL from teventos ";

        if (!empty($this->id_proceso) && empty($this->id_usuario)) {
            $sql.= ", tproceso_eventos_{$this->year} ";
        }
        if ($this->if_treg_evento) {
            $sql.= ", _treg_evento ";
        }
        $sql.= "where ";
        if (!empty($this->id_proceso) && empty($this->id_usuario)) {
            $sql.= "(teventos.id = tproceso_eventos_{$this->year}.id_evento and tproceso_eventos_{$this->year}.id_proceso = $this->id_proceso) and ";
        }
        if ($this->if_treg_evento) {
            $sql.= "teventos.id = _treg_evento.id_evento and ";
        }
        $sql.= "((periodicidad is null or periodicidad = 0) and (carga is null or carga = 0) and dayweek is null) ";
            $sql.= "and (" . date2pg("fecha_fin_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_fin_plan") . " <= '$fecha_fin') ";
        if (!is_null($this->id_tipo_reunion) || $this->tipo_plan == _PLAN_TIPO_MEETING) {
            $sql.= "and teventos.id_tipo_reunion is not null ";
        }
        /*
        if ($this->toshow == _EVENTO_INDIVIDUAL)
            $sql.= "and (teventos.user_check is null or teventos.user_check = 0) ";
        */
        if (empty($id_evento)) {
            if (empty($this->id_usuario) && !$this->if_auditoria) {
                if (is_null($this->empresarial) && empty($this->toshow)) {
                    $sql.= !empty($this->id_proceso) ? "and tproceso_eventos_{$this->year}.empresarial >= 0 " : "and teventos.empresarial >= 0 ";
                }
                else {
                    if (($this->toshow == _EVENTO_MENSUAL && empty($this->empresarial)) || !empty($this->id_tipo_reunion)) {
                        $sql.= "and tproceso_eventos_{$this->year}.empresarial >= "._EVENTO_MENSUAL." ";
                    } elseif ($this->toshow == _EVENTO_ANUAL && empty($this->empresarial)) {
                        $sql.= "and tproceso_eventos_{$this->year}.empresarial >= "._EVENTO_ANUAL." ";
                    }
                    if ($this->toshow && $this->empresarial) {
                        $sql.= "and tproceso_eventos_{$this->year}.empresarial = $this->empresarial ";
                    }
                }
                if (!empty($this->id_proceso)) {
                    if ($this->tipo_plan != _PLAN_TIPO_MEETING) {
                        $sql.= "and tproceso_eventos_{$this->year}.toshow >= $this->toshow ";
                    } else {
                        $sql.= "and tproceso_eventos_{$this->year}.toshow >= " . _EVENTO_MENSUAL . " ";
                        // $sql.= "and treg_evento.toshow > 0 ";
                    }
            }   }
            if (!empty($this->id_tipo_evento)) {
                $subcapitulos = $this->get_subcapitulos_evento();
                if (!empty($this->id_proceso) && empty($this->id_usuario)) {
                    $sql.= "and tproceso_eventos_{$this->year}.id_tipo_evento in ({$subcapitulos}) ";
                } else {
                    $sql.= "and teventos.id_tipo_evento in ({$subcapitulos}) ";
                }
            }
            if (!empty($this->like_name)) {
                $sql.= "and nombre like '%{$this->like_name}%' ";
            }
            if (!empty($this->date_eval_cutoff)) {
                $sql.= "and teventos.cronos <  '$this->date_eval_cutoff' ";
            }
            $sql.= "order by fecha_inicio_plan asc ";

        } else {
            $sql.= "and teventos.id = $id_evento ";
        }

        return $sql;
    }

    private function _sql_init($id_evento= null, $id_auditoria= null, $id_tarea= null) {
        $sql= "select distinct teventos.id, ";
        $sql.= empty($id_evento) && empty($id_auditoria) && empty($id_tarea) ? "true, " : "null, ";
        $sql.= "id_code, numero, null, null, null, ";
        $sql.= "teventos.id_responsable as id_responsable_asigna, teventos.id_proceso as id_proceso_asigna, ";
        $sql.= "teventos.id_usuario as id_user_asigna, teventos.origen_data, teventos.cronos as cronos_asigna, ";
        $sql.= "funcionario, nombre, fecha_inicio_plan, fecha_fin_plan, periodicidad, null, null, null, ";
        $sql.= "teventos.user_check, descripcion, lugar, teventos.id_evento, teventos.id_evento_code, teventos.id_tarea, ";
        $sql.= "teventos.id_tarea_code, teventos.id_auditoria, teventos.id_auditoria_code, id_tipo_reunion, id_tipo_reunion_code, ";
        $sql.= "teventos.id_tematica,teventos.id_tematica_code, teventos.id_copyfrom, teventos.id_copyfrom_code, ";
        $sql.= "ifassure, id_secretary, id_archivo, id_archivo_code, numero_plus, ";
        $sql.= day2pg("fecha_fin_plan")." as _day, ".month2pg("fecha_fin_plan")." as _month, ".year2pg("fecha_inicio_plan")." as _year, ";
        $sql.= "tproceso_eventos_{$this->year}.id_proceso as _id_proceso, tproceso_eventos_{$this->year}.id_proceso_code as _id_proceso_code, " ;
        $sql.= "null, teventos.indice_plus, tidx, NULL, NULL, NULL, NULL, NULL, NULL from teventos, tproceso_eventos_{$this->year}  ";
        return $sql;
    }

    private function _select_for_tmp_teventos_anual_plan($id= null, $id_evento= null, $id_auditoria= null, $id_tarea= null) {
        $sql= $this->_sql_init($id_evento, $id_auditoria, $id_tarea);
        if (empty($id_evento) && empty($id_auditoria) && empty($id_tarea))
            $sql.= "where tidx = 1 ";
        else {
            $sql.= "where (tidx = 0 or tidx is null) ";
            if (!empty($id_evento))
                $sql.= "and teventos.id_evento = $id_evento ";
            if (!empty($id_auditoria))
                $sql.= "and teventos.id_auditoria = $id_auditoria ";
            if (!empty($id_tarea)) {
                $sql.= "and teventos.id_tarea = $id_tarea ";
            }
        }
        if (!empty($id))
            $sql.= "and teventos.id = $id ";
        $sql.= "and (teventos.id = tproceso_eventos_{$this->year}.id_evento and tproceso_eventos_{$this->year}.id_proceso = $this->id_proceso) ";
        $sql.= "and YEAR(fecha_inicio_plan) = $this->year ";
        if (!is_null($this->id_tipo_reunion)) {
            $sql.= "and teventos.id_tipo_reunion is not null ";
        }
        if (empty($this->empresarial)) {
            $sql.= "and tproceso_eventos_{$this->year}.empresarial >= "._EVENTO_ANUAL." ";
        }
        else {
            $sql.= "and tproceso_eventos_{$this->year}.empresarial = $this->empresarial ";
        }
        $sql.= "and tproceso_eventos_{$this->year}.toshow >= $this->toshow ";
        if (!empty($this->id_tipo_evento)) {
            $subcapitulos = $this->get_subcapitulos_evento();
            if (!empty($this->id_proceso) && empty($this->id_usuario)) {
                $sql.= "and tproceso_eventos_{$this->year}.id_tipo_evento in ({$subcapitulos}) ";
            } else {
                $sql.= "and teventos.id_tipo_evento in ({$subcapitulos}) ";
            }
        }
        if (!empty($this->like_name)) {
            $sql.= "and nombre like '%{$this->like_name}%' ";
        }
        if (!empty($this->date_eval_cutoff)) {
            $sql.= "and teventos.cronos <  '$this->date_eval_cutoff' ";
        }
        return $sql;
    }

    private function _select_group_by_for_tmp_teventos_anual_plan() {
        $sql= $this->_sql_init();
        $sql.= "where ((tidx = 0 or tidx is null) and (teventos.id_auditoria is not null or teventos.id_tarea is not null)) ";
        $sql.= "and (teventos.id = tproceso_eventos_{$this->year}.id_evento and tproceso_eventos_{$this->year}.id_proceso = $this->id_proceso) ";
        $sql.= "and YEAR(fecha_inicio_plan) = $this->year ";
        if (empty($this->empresarial)) {
            $sql.= "and tproceso_eventos_{$this->year}.empresarial >= "._EVENTO_ANUAL." ";
        }
        else {
            $sql.= "and tproceso_eventos_{$this->year}.empresarial = $this->empresarial ";
        }
        $sql.= "and tproceso_eventos_{$this->year}.toshow >= $this->toshow ";
        if (!empty($this->id_tipo_evento)) {
            $subcapitulos = $this->get_subcapitulos_evento();
            if (!empty($this->id_proceso) && empty($this->id_usuario)) {
                $sql.= "and tproceso_eventos_{$this->year}.id_tipo_evento in ({$subcapitulos}) ";
            } else {
                $sql.= "and teventos.id_tipo_evento in ({$subcapitulos}) ";
            }
        }
        if (!empty($this->like_name)) {
            $sql.= "and nombre like '%{$this->like_name}%' ";
        }
        if (!empty($this->date_eval_cutoff)) {
            $sql.= "and teventos.cronos <  '$this->date_eval_cutoff' ";
        }
        $sql.= "group by teventos.id_auditoria, teventos.id_tarea ";
        return $sql;
    }

    private function _insert_into_tmp_teventos() {
        $sql= null;
        $i= 0;
        $j= 0;
        foreach ($this->array_eventos_restricted as $row) {
            if ($this->toshow == _EVENTO_INDIVIDUAL) {
                $rowcmp= $this->get_last_reg($row['id']);
                if (empty($rowcmp['toshow']))
                    continue;
            } else {
                $rowcmp= $this->get_reg_proceso($row['id']);
                if(empty($rowcmp['toshow']) 
                    && ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL || $this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL))
                    continue;
                if ($rowcmp['toshow'] < _EVENTO_MENSUAL && $this->toshow == _EVENTO_MENSUAL)
                    continue;
                if ($rowcmp['toshow'] < _EVENTO_ANUAL && ($this->toshow == _EVENTO_ANUAL
                    && ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL || $this->tipo_plan == _PLAN_TIPO_AUDITORIA 
                        || $this->tipo_plan == _PLAN_TIPO_SUPERVICION)))
                    continue;
            }

            $sql.= "insert into _teventos ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql_init= $this->_select_for_tmp_teventos(null, null, $row['id']);
            $sql.= $sql_init."; ";

            ++$i;
            ++$j;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('_insert_into_tmp_teventos', $sql);
                $i= 0;
                $sql= null;
            }
        }
        if (!empty($sql)) {
            $this->do_multi_sql_show_error('_insert_into_tmp_teventos', $sql);
        }

        $sql= "select * from _teventos";
        $result = $this->do_sql_show_error('_insert_into_tmp_teventos ', $sql);
        $cant = $this->clink->affected_rows($result);
        return $cant;
    }

    private function _create_tmp_eventos_plan_anual() {
        $this->obj_tables->_create_tmp_teventos(true);

        $sql_init= $this->_select_for_tmp_teventos_anual_plan(); 
        $sql= "insert into _teventos_small ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= $sql_init;
        $this->do_sql_show_error('_create_tmp_eventos_plan_anual', $sql);

        $sql_init= $this->_select_group_by_for_tmp_teventos_anual_plan();
        $sql= "insert into _teventos_small ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= $sql_init;
        $this->do_sql_show_error('_create_tmp_eventos_plan_anual', $sql);

        $sql= "select distinct *, id as _id from _teventos_small ";
        $result = $this->do_sql_show_error('_create_tmp_eventos_plan_anual', $sql);

        $this->debug_time('create_tmp_tproceso_eventos_1');
        $this->obj_tables->create_tmp_tproceso_eventos($result, true);  // registro de los eventos que pertenecen al proceso
        $this->clink->data_seek($result);
        $this->if_tproceso_eventos= $this->obj_tables->if_tproceso_eventos;
        $this->debug_time('create_tmp_tproceso_eventos_1');

        $this->debug_time('fix_tmp_teventos_0');
        $this->obj_tables->_fix_indice_plus_in_tproceso_eventos(true);
        $this->fix_tmp_teventos_prs(null, true);
        $this->debug_time('fix_tmp_teventos_0');

        $array_eventos= array();
        $cant= $this->_prepare_anual_plan($array_eventos);
        if ($cant) {
            $cant= $this->fill_tmp_eventos_plan_anual($array_eventos);
        }

        $this->obj_tables->drop_temporary_table("_teventos_small");
        $cant = $this->clink->table_size("_teventos");
        return $cant;
    }

    private function _prepare_anual_plan(&$array_eventos) {
        $sql= "select distinct * from _teventos_small where toshow >= $this->toshow ";
        $sql.= "order by indice asc, numero asc, indice_plus asc ";
        $result = $this->do_sql_show_error('_prepare_anual_plan', $sql);
        $max_nums_rows= $this->clink->num_rows($result);

        if (empty($max_nums_rows) || $max_nums_rows == -1) {
            $this->max_num_pages= 1;
            return;
        } else {
            $this->max_num_pages= (int)ceil((float)$max_nums_rows/$this->max_row_in_page);
        }
        $init_row= $this->init_row_temporary*$this->max_row_in_page;

        if ($this->limited) {
            if ($_SESSION["_DB_SYSTEM"] == "mysql")
                $sql.= "limit $init_row, $this->max_row_in_page ";
            else
                $sql.= "LIMIT $this->max_row_in_page OFFSET $init_row ";
        }
        $result = $this->do_sql_show_error('_prepare_anual_plan', $sql);

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if (((empty($row['id_auditoria']) && empty($row['id_tarea'])) && !empty($row['periodicidad']))
                or (!empty($row['id_auditoria']) || !empty($row['id_tarea']))) {
                ++$j;
                $array_eventos[]= array('id_evento'=>$row['id'], 'id_auditoria'=>$row['id_auditoria'], 'id_tarea'=>$row['id_tarea']);
            } else {
                ++$i;
                $sql.= "insert into _teventos ";
                $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
                $sql.= "select * from _teventos_small where id = {$row['id']}; ";
            }

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('_prepare_anual_plan', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql)
            $this->do_multi_sql_show_error('_prepare_anual_plan', $sql);

        return $j;
    }

    private function _create_tmp_teventos() {
        $sql_init= $this->_select_for_tmp_teventos();             
        $result = $this->do_sql_show_error('create_tmp_teventos ', $sql_init);
        $nums= $this->clink->num_rows($result);

        $sql= "insert into _teventos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= $sql_init;

        if ($this->tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL && $this->limited) {
            $this->max_num_pages= (int)ceil((float)$nums/$this->max_row_in_page);
            if ($this->max_num_pages == 0)
                $this->max_num_pages= 1;
            $init_row= $this->init_row_temporary*$this->max_row_in_page;

            if ($_SESSION["_DB_SYSTEM"] == "mysql")
                $sql.= "limit $init_row, $this->max_row_in_page ";
            else
                $sql.= "LIMIT $this->max_row_in_page OFFSET $init_row ";
        }

        $result = $this->do_sql_show_error('create_tmp_teventos ', $sql);
        $cant = $this->clink->table_size("_teventos");
        return $cant;
    }

    private function _compute_cant_tmp_eventos($result) {
        $nums= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['id']] || (!empty($row['_id_evento']) && $array_ids[$row['_id_evento']])) {
                if ($array_ids[$row['id']] && !empty($row['_id_evento']))
                    $array_ids[$row['_id_evento']]= $row['_id_evento'];
                continue;
            }

            $array_ids[$row['id']]= $row['id'];
            if (!empty($row['_id_evento']))
                $array_ids[$row['_id_evento']]= $row['_id_evento'];
            
            ++$nums; 
        }

        $this->max_num_pages= (int)ceil((float)$nums/$this->max_row_in_page);
        if ($this->max_num_pages == 0)
            $this->max_num_pages= 1;
        $init_row= $this->init_row_temporary*$this->max_row_in_page; 

        return $init_row;       
    }

    private function _create_tmp_eventos_stack() {
        $sql_init= $this->_select_for_tmp_teventos();      
        $result = $this->do_sql_show_error('_create_tmp_eventos_stack', $sql_init);

        $init_row= $this->_compute_cant_tmp_eventos($result);
        $this->clink->data_seek($result);

        $result_set = $this->do_sql_show_error('_create_tmp_eventos_stack', $sql_init);

        $sql= null;
        $array_ids= array();
        $j= 0;
        $i= 0;
        $k= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['id']] || (!empty($row['_id_evento']) && $array_ids[$row['_id_evento']])) {
                $array_ids[$row['id']]= $row['id'];
                if (!empty($row['_id_evento']))
                    $array_ids[$row['_id_evento']]= $row['_id_evento'];
                continue;
            }
            if ($k < $init_row) {
                ++$k;
                $array_ids[$row['id']]= $row['id'];
                if (!empty($row['_id_evento']))
                    $array_ids[$row['_id_evento']]= $row['_id_evento'];                
                continue;
            }
            $array_ids[$row['id']]= $row['id'];

            if ($k > $init_row + $this->max_row_in_page)
                break;

            if (empty($row['_id_evento'])) {
                ++$k;
                $sql_init= $this->_select_for_tmp_teventos(null, null, $row['id']);
                ++$j;
                $sql.= "insert into _teventos ";
                $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
                $sql.= $sql_init. "; ";                
            }
            else {
                ++$k;
                $this->clink->data_seek($result_set);
                $j+= $this->_create_stack_evento($result_set, $row['_id_evento'], $array_ids);
            }
            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('_create_tmp_eventos_stack', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql)
            $this->do_multi_sql_show_error('_create_tmp_eventos_stack', $sql);

        return $j;
    }

    private function _create_stack_evento($result, $id_evento, &$array_ids) {
        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if (empty($row['_id_evento']) || $row['_id_evento'] != $id_evento)
                continue;
            ++$i;
            $array_ids[$row['id']]= $row['id'];
            $array_ids[$id_evento]= $id_evento;
            $sql_init= $this->_select_for_tmp_teventos(null, null, $row['id']);

            $sql.= "insert into _teventos ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= $sql_init. "; ";
        }
        $this->do_multi_sql_show_error('_create_stack_evento', $sql);
        return $i;
    }

    private function fill_tmp_eventos_plan_anual($array_eventos) {
        $i= 0;
        $sql= null;
        foreach ($array_eventos as $row) {
            $id_evento= empty($row['id_auditoria']) && empty($row['id_tarea']) ? $row['id_evento'] : null;
            $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : null;
            $id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : null;

            $sql.= "insert into _teventos ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= $this->_select_for_tmp_teventos_anual_plan(null, $id_evento, $id_auditoria, $id_tarea);
            $sql.= "; ";

            ++$i;
            if ($i >= 1000) {
                $this->do_multi_sql_show_error('fill_tmp_eventos_plan_anual', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if ($sql)
            $this->do_multi_sql_show_error('fill_tmp_eventos_plan_anual', $sql);

        $cant = $this->clink->table_size("_teventos");      
        return $cant;
    }

    protected function create_tmp_teventos() {
        $this->obj_tables->_create_tmp_teventos();

        if ($this->toshow == _EVENTO_INDIVIDUAL)
            $this->obj_tables->SetIdUsuario($this->id_usuario);
        else
            $this->obj_tables->SetIdProceso($this->id_proceso);

        if (count($this->array_eventos_restricted) == 0) {
            if ($this->toshow == _EVENTO_ANUAL && !$this->if_auditoria) {
                $cant= $this->_create_tmp_eventos_plan_anual();
            } else {
                $cant= !$this->monthstack ? $this->_create_tmp_teventos() : $this->_create_tmp_eventos_stack();
            }
        } else {
            $cant= $this->_insert_into_tmp_teventos();
        }

        $this->obj_tables->_fix_indice_plus_in_tproceso_eventos(false);

        if (empty($this->error)) {
            $this->if_teventos = true;
            $this->obj_tables->if_teventos= true;
            $this->obj_tables->init_tmp_teventos();
            $this->error= $this->obj_tables->error;
        }

        $this->cant= $cant;
        return $this->error;
    }

    protected function fix_tmp_teventos_prs($result= null, $if_small= false) {
        $plus= $if_small ? "_small" : null;
        $is_null_result= empty($result) ? true : false;
        $table= "_teventos$plus";

        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdProceso($this->id_proceso);
        $obj_prs->if_tproceso_eventos= $this->if_tproceso_eventos;
        $obj_prs->toshow= $this->toshow;
        $obj_prs->if_auditoria= $this->if_auditoria;

        if (is_null($result)) {
            $sql= "select $table.*, $table.id as _id, $table.id_auditoria as _id_auditoria, $table.id_tarea as _id_tarea ";
            if ($this->if_tproceso_eventos) {
                $sql.= ", _tproceso_eventos.id_responsable as _id_responsable, _tproceso_eventos.empresarial as _empresarial, ";
                $sql.= "_tproceso_eventos.id_tipo_evento as _id_tipo_evento, _tproceso_eventos.toshow as _toshow, ";
                $sql.= "_tproceso_eventos.indice as _indice, _tproceso_eventos.indice_plus as _indice_plus, ";
                $sql.= "_tproceso_eventos.cumplimiento as _cumplimiento, _tproceso_eventos.observacion as _observacion, ";
                $sql.= "_tproceso_eventos.rechazado as _rechazado, _tproceso_eventos.aprobado as _aprobado, _tproceso_eventos.cronos as _cronos ";
            }
            $sql.= "from $table ";
            if ($this->if_tproceso_eventos) {
                $sql.= ", _tproceso_eventos where $table.id = _tproceso_eventos.id_evento ";
            }
            $result= $this->do_sql_show_error('fix_tmp_teventos_prs', $sql);
        }

        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $obj_prs->SetIdEvento($row['_id']);
            $obj_prs->SetIdAuditoria($row['_id_auditoria']);
            $obj_prs->SetIdTarea($row['_id_tarea']);

            $rowcmp= $is_null_result && $this->if_tproceso_eventos ? null : $obj_prs->get_reg_proceso();

            $id_responsable= !is_null($rowcmp) ? setNULL($rowcmp['id_responsable']) : setNULL($row['_id_responsable']);
            $empresarial= !is_null($rowcmp) ? setNULL($rowcmp['empresarial']) : setNULL($row['_empresarial']);
            $id_tipo_evento= !is_null($rowcmp) ? setNULL($rowcmp['id_tipo_evento']) : setNULL($row['_id_tipo_evento']);
            $toshow= !is_null($rowcmp) ? setNULL($rowcmp['toshow']) : setNULL($row['_toshow']);
            $indice= !is_null($rowcmp) ? setNULL($rowcmp['indice']) : setNULL($row['_indice']);
            
            $cumplimiento= !is_null($rowcmp) ? setNULL($rowcmp['cumplimiento']) : setNULL($row['_cumplimiento']);
            $observacion= !is_null($rowcmp) ? setNULL_str($rowcmp['observacion']) : setNULL_str($row['_observacion']);
            $rechazado= !is_null($rowcmp) ? setNULL_str($rowcmp['rechazado']) : setNULL_str($row['_rechazado']);
            $aprobado= !is_null($rowcmp) ? setNULL_str($rowcmp['aprobado']) : setNULL_str($row['_aprobado']);
            $cronos= !is_null($rowcmp) ? setNULL_str($rowcmp['cronos']) : setNULL_str($row['_cronos']);

            $indice_plus= !empty($rowcmp['indice_plus']) ? setNULL($rowcmp['indice_plus']) : setNULL($row['_indice_plus']);

            if (!boolean($row['_idx']) || (boolean($row['_idx']) && $if_small)) {
                ++$i;
                $sql.= "update $table set id_responsable= $id_responsable, empresarial= $empresarial, id_tipo_evento= $id_tipo_evento, ";
                $sql.= "toshow= $toshow, indice= $indice, indice_plus= $indice_plus, cumplimiento= $cumplimiento, observacion= $observacion, ";
                $sql.= "rechazado= $rechazado, aprobado= $aprobado, cronos= $cronos where id = {$row['_id']}; ";
            }
            if ($this->if_tidx) {
                ++$i;
                $sql.= "update _tidx set empresarial= $empresarial, id_tipo_evento= $id_tipo_evento, indice= $indice, indice_plus= $indice_plus ";
                $sql.= "where ";
                $sql.= !empty($row['_id']) ? "id = {$row['_id']} " : "id is null ";
                $sql.= !empty($row['_id_auditoria']) ? "and id_auditoria = {$row['_id_auditoria']} " : "and id_auditoria is null ";
                $sql.= !empty($row['_id_tarea']) ? "and id_tarea = {$row['_id_tarea']} " : "and id_tarea is null ";
                $sql.= "; ";
            }

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('fix_tmp_teventos_prs', $sql);
                $sql= null;
                $i= 0;
            }
        }

        if ($sql)
            $this->do_multi_sql_show_error('fix_tmp_teventos_prs', $sql);
    }

    protected function fix_tmp_teventos_user($result= null) {
        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdProceso($this->id_proceso);

        if (is_null($result)) {
            $sql= "select *, id as _id, id_auditoria as _id_auditoria, id_tarea as _id_tarea from _teventos";
            $result= $this->clink->do_sql_show_error('fix_tmp_teventos_user', $sql);
        }

        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $obj_prs->SetIdEvento($row['_id']);
            $obj_prs->SetIdAuditoria($row['_id_auditoria']);
            $obj_prs->SetIdTarea($row['_id_tarea']);

            reset($this->array_procesos);
            foreach ($this->array_procesos as $prs) {
                if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                    continue;
                if (empty($prs['id_entity']) && $prs['id'] != $_SESSION['id_entity'])
                    continue;
                $rowcmp= $obj_prs->get_reg_proceso();
                if (!empty($rowcmp['id_responsable']) && !is_null($rowcmp['toshow'])) {
                    $id_responsable= $rowcmp['id_responsable'];
                    break;
            }   }

            ++$i;
            if (!empty($id_responsable) && $id_responsable != $row['id_responsable']) {
                $sql.= "update _teventos set id_responsable= {$rowcmp['id_responsable']}, empresarial= {$row['empresarial']}, ";
                $sql.= "toshow= {$rowcmp['toshow']}, id_proceso= {$rowcmp['id_proceso']}, id_proceso_code= '{$rowcmp['id_proceso_code']}' ";
                $sql.= "where ";
                $sql.= !empty($row['_id']) ? "id = {$row['_id']} " : "id is null ";
                $sql.= !empty($row['_id_auditoria']) ? "and id_auditoria = {$row['_id_auditoria']} " : "and id_auditoria is null ";
                $sql.= !empty($row['_id_tarea']) ? "and id_tarea = {$row['_id_tarea']} " : "and id_tarea is null ";
                $sql.= "; ";
            }

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('fix_tmp_teventos_user', $sql);
                $sql= null;
                $i= 0;
            }
        }

        if ($sql)
            $this->do_multi_sql_show_error('fix_tmp_teventos_user', $sql);
    }

    protected function create_temporary_plantrab() {
        $sql_init= $this->_select_for_tmp_teventos($this->toshow);
        $result = $this->do_sql_show_error('create_tmp_teventos ', $sql_init);
        $nums= $this->clink->num_rows($result);

        $this->obj_tables->SetCumplimiento(null);
        $this->obj_tables->SetFecha(null);

        $this->debug_time('create_temporary_plantrab');
        $cant= $this->obj_tables->create_temporary_plantrab($result, $this->id_usuario);

        if (empty($this->error)) {
            $this->if_teventos= $this->obj_tables->if_teventos;
            $this->if_treg_evento= $this->obj_tables->if_treg_evento;
            $this->obj_tables->init_tmp_teventos();
            $this->error= $this->obj_tables->error;
        }
        $this->debug_time('create_temporary_plantrab');

        $this->cant= $cant;
        return $this->error;
    }

    protected function fill_tidx_array($result) {
        foreach ($this->tidx_array as $id => $array) {
            for ($m= 1; $m < 13; ++$m) {
                for ($dd= 1; $dd <= 31; $dd++) {
                    $this->tidx_array[$id]['month'][$m][$dd]= 0;
        }   }   }

        reset($this->tidx_array);
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= 1;

            if (empty($row['toshow']))
                continue;

            $id= null;
            if ($this->if_tidx) {
                if (!empty($row['_id_auditoria']))
                    $id= $this->tidx_array_auditoria[$row['_id_auditoria']][0];
                elseif (!empty($row['_id_tarea']))
                    $id= $this->tidx_array_tarea[$row['_id_tarea']][0];
                else
                    $id= boolean($row['tidx']) ? $row['_id'] : $this->tidx_array_evento[$row['_id_evento']][0];
            } else
                $id= $row['_id'];

            if (!empty($id))
                $this->tidx_array[$id]['month'][(int)$row['month']][(int)$row['day']]= $row['_id'];
        }       
    }

    protected function list_tmp_eventos($compute_all = false, $use_treg_evento = true) {
        $compute_all = !is_null($compute_all) ? $compute_all : false;

        $sql= "select distinct _teventos.id as _id, _teventos.id_code as _id_code, numero, nombre, fecha_inicio_plan, ";
        $sql.= "fecha_fin_plan, empresarial, id_tipo_evento, descripcion, month, year, id_user_asigna, origen_data_asigna, ";
        $sql.= "id_responsable_2, _teventos.id_tarea as _id_tarea, _teventos.id_responsable as _id_responsable, responsable_2_reg_date, ";
        $sql.= "_teventos.id_tarea_code as _id_tarea_code, _teventos.id_auditoria as _id_auditoria, _teventos.cronos, ";
        $sql.= "_teventos.cronos_asigna, _teventos.id_auditoria_code as _id_auditoria_code, _teventos.user_check as _user_check, ";
        $sql.= "id_copyfrom, id_copyfrom_code, _teventos.toshow as _toshow, funcionario, ifassure, id_secretary, null as user_check, ";
        $sql.= "_teventos.aprobado, _teventos.rechazado, _teventos.observacion as _observacion ";
        /*
        if ($use_treg_evento && $this->if_treg_evento)
            $sql .= ", _treg_evento.user_check as user_check ";
        */
        $sql.= "from _teventos ";
        if ($use_treg_evento && $this->if_treg_evento)
            $sql .= ", _treg_evento ";
        if ($use_treg_evento && $this->if_treg_evento) {
            $sql.= "where _teventos.id = _treg_evento.id_evento ";
            if (!$compute_all)
                $sql .= "and _treg_evento.compute = " . boolean2pg(1) . " ";
        }
        $sql.= "order by _teventos.fecha_fin_plan asc ";
        $result = $this->do_sql_show_error('list_tmp_eventos', $sql);

        if (empty($this->cant) || !empty($this->error))
            return _EMPTY;
        return $result;
    }
}
