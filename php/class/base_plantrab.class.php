<?php
/**
* @author Geraudis Mustelier Portuondo
* @copyright 2012
*/

if (!class_exists('Tbase_plantrab_db'))
    include_once "base_plantrab_db.class.php";

class Tbase_plantrab extends Tbase_plantrab_db {
    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_plantrab_db::__construct($clink);
    }    

    public function set_create_tmp_table_tidx($id= false) {
        $this->create_tmp_table_tidx= $id;
    }

    private function update_treg_evento($result) {
        $this->debug_time('update_treg_evento');

        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if ($this->toshow == _EVENTO_ANUAL)
                if (!empty($this->reg_fecha) && strtotime($row['fecha_fin_plan']) > strtotime($this->reg_fecha))
                    continue;

            $this->id_archivo= $row['id_archivo'];
            $this->id_archivo_code= $row['id_archivo_code'];
            $this->cumplimiento= null;
            $array_responsable= array('id_responsable'=>$row['id_responsable'], 'id_responsable_2'=>$row['id_responsable_2'],
                                    'responsable_2_reg_date'=>$row['responsable_2_reg_date']);

            $rowcmp= $this->getEvento_reg($row['id'], $array_responsable);
            $cumplimiento= !is_null($rowcmp['cumplimiento']) ? (int)$rowcmp['cumplimiento'] : null;
            $this->array_status_eventos_ids[$row['id']]= array($cumplimiento, $rowcmp['rechazado']);

            if (($cumplimiento == _EN_CURSO || $cumplimiento == _NO_INICIADO || $cumplimiento == _REPROGRAMADO) && empty($rowcmp['rechazado'])) {
                if (!empty($row['id_responsable_2']) && (!is_null($row['responsable_2_reg_date']) && (int)strtotime($this->reg_fecha) <= (int)strtotime($row['responsable_2_reg_date'])))
                    $id_responsable= $row['id_responsable_2'];
                else
                    $id_responsable= $row['id_responsable'];

                $id_usuario= empty($this->id_usuario) ? $id_responsable : $this->id_usuario;
                $array= $this->test_if_incumplida($row['id'], $id_usuario, $cumplimiento, $row['fecha_fin_plan'], $rowcmp);

                if ($array['updated']) {
                    if ($this->if_teventos && $this->if_treg_evento) {
                        ++$j;
                        $this->cumplimiento= _INCUMPLIDO;
                        $this->observacion= "Incumplimiento detectado por el Sistema $this->cronos";
                        $sql.= $this->_update_cump_tmp_teventos($row['id'], true);
                    }
                    ++$i;

                    ++$j;
                    if ($j > 500) {
                        $this->do_multi_sql_show_error('update_treg_evento', $sql);
                        $sql= null;
                        $j= 0;
                    }                    
                }    
            }
        }

        if ($sql)
            $this->do_multi_sql_show_error('update_treg_evento', $sql);

        $this->debug_time('update_treg_evento');

        $this->id_archivo= null;
        $this->id_archivo_code= null;
        return $i > 0 ? true : false;
    }

    private function update_tproceso_eventos($result) {
        global $config;

        $time= new TTime();
        $fecha_actual= $time->GetStrTime();

        $this->debug_time('update_tproceso_eventos');
        $i= 0;
        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            if ($this->toshow == _EVENTO_ANUAL)
                if (!empty($this->reg_fecha) && strtotime($row['fecha_fin_plan']) > strtotime($this->reg_fecha))
                    continue;

            $this->cumplimiento= null;

            $rowcmp= $this->get_reg_proceso($row['id']);
            $cumplimiento= !is_null($rowcmp['cumplimiento']) ? (int)$rowcmp['cumplimiento'] : null;
            $this->array_status_eventos_ids[$row['id']]= array($cumplimiento, $rowcmp['rechazado']);
            $_fecha_fin_plan= add_date($row['fecha_fin_plan'], (int)$config->breaktime);

            if (($cumplimiento == _EN_CURSO || $cumplimiento == _NO_INICIADO /*|| $cumplimiento == _REPROGRAMADO*/)
                    && (int)strtotime($_fecha_fin_plan) <= (int)strtotime($fecha_actual)) {
                ++$i;
                $this->cumplimiento= _INCUMPLIDO;
                $this->observacion= "Incumplimiento detectado por el Sistema $this->cronos";
                $rowcmp['cumplimiento']= $this->cumplimiento;
                $rowcmp['observacion']= $this->observacion;
                
                $sql.= $this->add_cump_proceso(null, null, $rowcmp, true);
                /*
                if ($this->if_tproceso_eventos)
                    $sql.= $this->add_cump_proceso(null, null, $rowcmp, true, true);
                */
                if ($this->if_teventos && $this->if_tproceso_eventos)
                        $sql.= $this->_update_cump_tmp_teventos($row['id'], true);

                ++$j;
                if ($j > 500) {
                    $this->do_multi_sql_show_error('update_tproceso_eventos', $sql);
                    $sql= null;
                    $j= 0;
                }
            }
            if (($cumplimiento == _SUSPENDIDO && !empty($rowcmp['rechazado']))
                && (int)strtotime($_fecha_fin_plan) <= (int)strtotime($fecha_actual)) {
                $this->cumplimiento= $rowcmp['cumplimiento'];
                $this->observacion= $rowcmp['observacion']; 
                $sql.= $this->_update_cump_tmp_teventos($row['id'], true);
            }            

        } 
        if ($sql)
            $this->do_multi_sql_show_error('update_tproceso_eventos', $sql);

        $this->debug_time('update_tproceso_eventos');
        return $i > 0 ? true : false;
    }

    private function _update_cump_tmp_teventos($id_evento, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $observacion= setNULL_str($this->observacion);

        $sql= "update _teventos set cumplimiento = $this->cumplimiento, observacion= $observacion ";
        $sql.= "where id = $id_evento; ";
        if (!$multi_query)
            $this->do_sql_show_error('update_cump_tmp_teventos', $sql);
        else
            return $sql;
    }

    private function _fix_array_procesos() {
        $this->array_procesos= array();
        if ($this->toshow == _EVENTO_MENSUAL || $this->toshow == _EVENTO_ANUAL) {
            $this->array_procesos[$this->id_proceso]= array('id'=> $this->id_proceso);
        } else {
            $obj_user= new Tusuario($this->clink);
            $obj_user->SetIdUsuario($this->id_usuario);
            $obj_user->Set();
            $id_proceso= $obj_user->GetIdProceso();
            unset($obj_user);

            $obj_prs= new Tproceso($this->clink);
            $obj_prs->SetIdProceso($id_proceso);
            $obj_prs->Set();
            $obj_prs->SetYear($this->year);
            $obj_prs->SetIdEntity($_SESSION['id_entity']);
            $obj_prs->get_procesos_up_cascade($id_proceso, null, null, true);

            foreach ($obj_prs->array_cascade_up as $prs) {
                if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                    continue;
                if (empty($prs['id_entity']) && $prs['id'] != $_SESSION['id_entity'])
                    continue;
                $this->array_procesos[$prs['id']]= $prs;
            }
        }
    }

    private function _select_teventos_tidx($filter_toshow= false) {
        $filter_toshow= !is_null($filter_toshow) ? $filter_toshow : false;
        
        $sql= "select distinct _teventos.*, _teventos.id as _id, _teventos.id_evento as _id_evento, ";
        $sql.= "_teventos.id_auditoria as _id_auditoria, _teventos.id_tarea as _id_tarea from _teventos ";
        if ($this->if_tidx) 
            $sql.= ", _tidx ";
        $sql.= $filter_toshow && $this->if_tproceso_eventos ? "where _teventos.toshow >= $this->toshow " : "where 1 ";
        if ($this->if_tidx) { 
            $sql.= "and ((_tidx.id is not null and (_teventos.id = _tidx.id or _teventos.id_evento = _tidx.id)) ";
            $sql.= "or ((_tidx.id_auditoria is not null and _teventos.id_auditoria = _tidx.id_auditoria) ";
            $sql.= "or (_teventos.id_tarea is not null and _teventos.id_tarea = _tidx.id_tarea))) ";
        }  
        $sql.= "order by fecha_inicio_plan asc";       

        $result= $this->do_sql_show_error('_select_teventos_tidx', $sql);
        return $result;
    }

    public function automatic_event_status($toshow, $limited= false, $flag= true, $update_cump= false) {
        $error= null;
        $limited= !is_null($limited) ? $limited : false;
        $this->limited= $limited;

        $flag= !is_null($flag) ? $flag : true;
        $update_cump= !is_null($update_cump) ? $update_cump : false;

        $this->if_auditoria= false;
        $array_id= null;
        $this->cronos= !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $this->reg_fecha= null;
        $this->toshow= $toshow;
        /*
        if ($this->toshow == _EVENTO_MENSUAL || $this->toshow == _EVENTO_INDIVIDUAL) {
            $time= new TTime();
            $month= !empty($this->month) ? $this->month : 12;
            $day= $time->longmonth($month, $this->year);
            $this->reg_fecha= $this->year."-".str_pad($month, 2, '0', STR_PAD_LEFT)."-".str_pad($day, 2, '0', STR_PAD_LEFT)." 23:59:59";
        } else {
            $this->reg_fecha= $this->year."-12-31 23:59:59";
        }
        */
        $this->_fix_array_procesos();

        $this->copy_in_object($this->obj_tables);
        $this->copy_in_object($this->obj_reg);
        $this->obj_reg->SetFecha($this->reg_fecha);

        $this->debug_time('create_tmp_teventos_steep_0');
        $this->debug_time('create_tmp_teventos_steep_1');
        if ($this->toshow == _EVENTO_MENSUAL || $this->toshow == _EVENTO_ANUAL) {
            if (!$this->if_teventos) {
                $this->debug_time('create_tmp_teventos');
                $this->create_tmp_teventos();  //eventos que pertenecen al proceso
                $this->debug_time('create_tmp_teventos');

                $this->obj_tables->max_num_pages= $this->max_num_pages;
                $this->obj_tables->max_row_in_page= $this->max_row_in_page;                
                
                if (empty($this->cant) || $this->cant == -1)
                    return _EMPTY;
            }
            
            if (($this->toshow != _EVENTO_MENSUAL || ($this->toshow == _EVENTO_MENSUAL && $this->tipo_plan == _PLAN_TIPO_MEETING))
                    && $this->create_tmp_table_tidx) {
                $this->debug_time('_create_tmp_tidx_tmp');
                $this->obj_tables->_create_tmp_tidx_tmp();
                $this->if_tidx= $this->obj_tables->if_tidx;
                $this->debug_time('_create_tmp_tidx_tmp');
            }
        } else {

            $this->debug_time('create_tmp_teventos_user');
            $this->create_temporary_plantrab();
            $this->debug_time('create_tmp_teventos_user');
            if (empty($this->cant))
                return _EMPTY;
            
            $this->if_teventos= $this->obj_tables->if_teventos;
            $this->if_treg_evento= $this->obj_tables->if_treg_evento;
        }
        $this->debug_time('create_tmp_teventos_steep_1');

        if (!is_null($error))
            return $error;

        return $this->_automatic_event_status($flag, $update_cump);
    }

    private function _automatic_event_status($flag, $update_cump) {
        $this->if_tidx= $this->obj_tables->if_tidx;
        $this->tidx_array= $this->obj_tables->tidx_array;
        $this->tidx_array_auditoria= $this->obj_tables->tidx_array_auditoria;
        $this->tidx_array_evento= $this->obj_tables->tidx_array_evento;
        $this->tidx_array_tarea= $this->obj_tables->tidx_array_tarea;

        if ($this->obj_tables->if_tidx && $this->limited) {
            $this->max_num_pages= $this->obj_tables->max_num_pages;
            $this->max_row_in_page= $this->obj_tables->max_row_in_page;
        }

        if ($this->limited && ($this->toshow == _EVENTO_MENSUAL && $this->tipo_plan != _PLAN_TIPO_MEETING)) {
            $sql= "select min(fecha_inicio_plan), max(fecha_inicio_plan) from _teventos";
            $result= $this->do_sql_show_error('_automatic_event_status', $sql);
            $row= $this->clink->fetch_array($result);
            $this->fecha_inicio_plan_page= date('Y-m-d', strtotime($row[0]));
            $this->fecha_fin_plan_page= date('Y-m-d', strtotime($row[1]));
        }

        $result= $this->_select_teventos_tidx(false);
        if ($this->limited && empty($this->cant)) {
            $this->exec_automatic_event= _EMPTY;
            return _EMPTY;
        }

        $this->debug_time('create_tmp_tproceso_eventos_1');
        if (!$this->if_tproceso_eventos || ($this->if_tproceso_eventos && $this->toshow == _EVENTO_ANUAL)) {
            $init_tmp_table= $this->toshow != _EVENTO_ANUAL || ($this->toshow == _EVENTO_ANUAL && $this->if_auditoria) ? true : false;
            $error= $this->obj_tables->create_tmp_tproceso_eventos($result, false, $init_tmp_table);  // registro de los eventos que pertenecen al proceso
            $this->clink->data_seek($result);
        }
        $this->if_tproceso_eventos= $this->obj_tables->if_tproceso_eventos;
        $this->debug_time('create_tmp_tproceso_eventos_1');

        $this->debug_time('fix_tmp_teventos_1');
        if ($this->toshow != _EVENTO_INDIVIDUAL) {
            $this->fix_tmp_teventos_prs($this->toshow != _EVENTO_ANUAL || ($this->toshow == _EVENTO_ANUAL && !$this->limited) ? $result : null);
            $this->clink->data_seek($result);
        }
        if ($this->toshow == _EVENTO_INDIVIDUAL) {
            $this->fix_tmp_teventos_user($result);
            $this->clink->data_seek($result);
        }
        $this->debug_time('fix_tmp_teventos_1');

        if ($flag || ($this->if_tidx && $this->create_tmp_table_tidx)) {
            $this->debug_time('test_if_incumplida');
            $if_updated_treg_evento= false;
            if ($this->toshow == _EVENTO_INDIVIDUAL && $update_cump) {
                $if_updated_treg_evento= $this->update_treg_evento($result);
            }
            if ($this->toshow != _EVENTO_INDIVIDUAL && $update_cump) {
                $if_updated_treg_evento= $this->update_tproceso_eventos($result);
            }
            $this->debug_time('test_if_incumplida');
        }

        if ($this->if_tidx) {
            $this->debug_time('fill_tidx_array');
            $result= $this->_select_teventos_tidx(true);
            $this->fill_tidx_array($result);
            $this->debug_time('fill_tidx_array');
        }

        $this->exec_automatic_event= false;
        if (!$flag)
            return $this->cant;

        if ($this->create_temporary_treg_evento_table && $update_cump) {
            $this->debug_time('create_tmp_treg_evento_user');
            if ($this->toshow == _EVENTO_INDIVIDUAL) {
                $error= $this->obj_tables->create_tmp_treg_evento_user(null, true);
            }
            $this->debug_time('create_tmp_treg_evento_user');
        }

        $this->if_treg_evento= $this->obj_tables->if_treg_evento;
        $this->if_tproceso_eventos= $this->obj_tables->if_tproceso_eventos;

        $this->debug_time('create_tmp_teventos_steep_0');
        if (!is_null($error))
            return $error;
    }

    /*
    /* diferencia las que vienen del anual de las adicionadas al mensual. Creando la lista de externas
    /* y propias del anual y del mensual
    */
    private function _list_reg_externas($row, $_row, $array, $if_extra) {
        $id_proceso= $this->if_teventos ? $row['id_proceso_asigna'] : $row['id_proceso'];

        if ($row['_toshow'] >= _EVENTO_ANUAL) {
            ++$this->anual;
            if (array_key_exists($id_proceso, $this->array_cascade_up) !== false || !empty($row['funcionario'])) {
                if (!is_array($this->externas_list))
                    $this->externas_list = array();
                $this->externas_list[$row['_id']] = $array;
                ++$this->externas;
                ++$this->anual_externas;
            } else
                ++$this->anual_propias;
        }

        if ($row['_toshow'] == _EVENTO_MENSUAL) {
            ++$this->mensual;
            if (array_key_exists($id_proceso, $this->array_cascade_up) !== false || !empty($row['funcionario'])) {
                if (!is_array($this->externas_list))
                    $this->externas_list = array();
                $this->externas_list[$row['_id']] = $array;
                ++$this->externas;
                ++$this->mensual_externas;
            } else
                ++$this->mensual_propias;
        }

        /* no se contabilizan las tareas rep_extra($row['_id'], $this->aprobado, $array_responsable);rogramadas */
        if ($if_extra) {
            ++$this->extras;

            if ($row['_toshow'] == _EVENTO_MENSUAL)
                ++$this->mensual_extras;

            if (!is_array($this->extras_list))
                $this->extras_list = array();
            $this->extras_list[$row['_id']] = $array;

            if (array_key_exists($this->id_proceso, $this->array_cascade_up) !== false || !empty($row['funcionario'])) {
                if (!is_array($this->externas_list))
                    $this->externas_list = array();
                $this->externas_list[$row['_id']] = $array;

                ++$this->externas;
                ++$this->extras_externas;

                if ($row['_toshow'] == _EVENTO_MENSUAL)
                    ++$this->mensual_externas_extras;

            } else {
                ++$this->extras_propias;

                if ($row['_toshow'] == _EVENTO_MENSUAL)
                    ++$this->mensual_propias_extras;
            }
            if (!empty($_row['rechazado']))
                ++$this->extras_rechazadas;
        }
    }

    private function _list_reg_efectivas($row, $_row, $array, $if_extra) {
        if (!$if_extra) {
            if (!is_array($this->efectivas_list))
                $this->efectivas_list = array();
            $this->efectivas_list[$row['_id']] = $array;
            ++$this->efectivas;
        }

        $new_rechazada= !isset($this->rechazadas_list[$row['_id']]) ? true : false;
        if (!empty($_row['rechazado'])) {
            if (!is_array($this->rechazadas_list))
                $this->rechazadas_list = array();
            $this->rechazadas_list[$row['_id']] = $array;

            if ($new_rechazada) {
                ++$this->rechazadas;

                if (!$if_extra) {
                    ++$this->efectivas_rechazadas;
                    if (!is_array($this->efectivas_rechazadas_list))
                        $this->efectivas_rechazadas_list = array();
                    $this->efectivas_rechazadas_list[$row['_id']] = $array;
        }   }   }
    }

    private function _list_reg_incumplidas($row, $_row, $array, $if_extra) {
        if ($_row['cumplimiento'] == _INCUMPLIDO) {
            $new_incumplida= !isset($this->incumplidas_list[$row['_id']]) ? true : false;
            if (!is_array($this->incumplidas_list))
                $this->incumplidas_list = array();
            $this->incumplidas_list[$row['_id']] = $array;

            if ($new_incumplida) {
                ++$this->incumplidas;

                if (!$if_extra) {
                    ++$this->efectivas_incumplidas;
                    if (!is_array($this->efectivas_incumplidas_list))
                        $this->efectivas_incumplidas_list = array();
                    $this->efectivas_incumplidas_list[$row['_id']] = $array;
        }   }   }

        if ($_row['cumplimiento'] == _COMPLETADO) {
            if (!is_array($this->cumplidas_list))
                $this->cumplidas_list = array();
            $this->cumplidas_list[$row['_id']] = $array;
            ++$this->cumplidas;

            if (!$if_extra) {
                ++$this->efectivas_cumplidas;
                if (!is_array($this->efectivas_cumplidas_list))
                    $this->efectivas_cumplidas_list = array();
                $this->efectivas_cumplidas_list[$row['_id']] = $array;
        }   }
    }

    private function _list_reg_modificadas($row, $_row, $array, $if_extra) {
        $array_tmp = $this->test_if_modified($row['_id']);

        if (!is_null($array_tmp)) {
            $array['id_responsable'] = $row['id_user_asigna'];

            if (!is_array($this->modificadas_list))
                $this->modificadas_list = array();
            $this->modificadas_list[$row['_id']] = $array;
            ++$this->modificadas;

            if (!$if_extra) {
                ++$this->efectivas_modificadas;
                if (!is_array($this->efectivas_modificadas_list))
                    $this->efectivas_modificadas_list = array();
                $this->efectivas_modificadas_list[$row['_id']] = $array;
            }
        }
    }

    private function _list_reg_canceladas($row, $_row, $array, $if_extra) {
        $new_cancelada= !isset($this->canceladas_list[$row['_id']]) ? true : false;
        if (($_row['cumplimiento'] == _CANCELADO || ($_row['cumplimiento'] == _POSPUESTO || $_row['cumplimiento'] == _SUSPENDIDO))
            || (!empty($row['rechazado']) && $_row['cumplimiento'] == _REPROGRAMADO)) {
            if (!is_array($this->canceladas_list))
                $this->canceladas_list = array();
            $this->canceladas_list[$row['_id']] = $array;

            if ($new_cancelada) {
                ++$this->canceladas;

                if (!$if_extra) {
                    ++$this->efectivas_canceladas;
                    if (!is_array($this->efectivas_canceladas_list))
                        $this->efectivas_canceladas_list = array();
                    $this->efectivas_canceladas_list[$row['_id']] = $array;
        }   }   }
    }

    private function _list_reg_reprogramadas($row, $_row, $array, $if_extra) {
        $obj= new Tevento($this->clink);
        // test si esta reprogramada la tarea
        if (empty($row['id_copyfrom']))
            return false;

        $obj->Set($row['id_copyfrom']);

        $array['fecha'] = $obj->GetFechaInicioPlan();
        $array['observacion'] = $obj->GetObservacion();
        $array['id_responsable'] = $obj->GetIdResponsable();

        if (!is_array($this->reprogramadas_list))
            $this->reprogramadas_list = array();
        $this->reprogramadas_list[$row['_id']] = $array;
        ++$this->reprogramadas;

        if (!$if_extra) {
            ++$this->efectivas_reprogramadas;
            if (!is_array($this->efectivas_reprogramadas_list))
                $this->efectivas_reprogramadas_list = array();
            $this->efectivas_reprogramadas_list[$row['_id']] = $array;
        }

        return true;
    }

    private function _list_reg_no_iniciadas($row, $_row, $array) {
        if ($_row['cumplimiento'] == _NO_INICIADO) {
            if (!is_array($this->no_iniciadas_list))
                $this->no_iniciadas_list= array();
            ++$this->no_iniciadas;
            $this->no_iniciadas_list[$row['_id']]= $array;
        }
    }

    private function _list_reg_delegadas($row, $_row, $array) {
        if ($row['_toshow'] == _EVENTO_INDIVIDUAL) {
            if (!empty($row['id_responsable_2'])
                && ($row['id_responsable'] != $row['id_responsable_2'] || $_row['cumplimiento'] == _DELEGADO)) {
                $array['id_responsable'] = $row['id_user_asigna'];

                if (!is_array($this->delegadas_list))
                    $this->delegadas_list = array();
                $this->delegadas_list[$row['_id']] = $array;
                ++$this->delegadas;
            }
            return true;
        }

        $sql= "select * from tproceso_eventos_$this->year where id_evento = {$row['_id']} and id_proceso = $this->id_proceso ";
        $sql.= "and cumplimiento = "._DELEGADO;
        $result = $this->do_sql_show_error('_list_reg_delegadas', $sql);

        if ($this->cant > 0) {
            if (!is_array($this->delegadas_list))
                $this->delegadas_list = array();
            $this->delegadas_list[$row['_id']] = $array;
            ++$this->delegadas;
            return true;
        }

        return false;
    }

    /*
    /* Muestra las tareas ocultas (suspendidas o rechazadas) segun el parametro print_reject
    /* por defecto print_reject= 2 ==> Lo muestra todo
    */
    private function _list_reg_if_continue($row, $_row) {
        $continue= true;

        switch ($this->print_reject) {
            case(_PRINT_REJECT_NO):
                if ($this->toshow == _EVENTO_INDIVIDUAL && $_row['user_check']) {
                    ++$this->cant_print_reject;
                    --$this->total;
                    $continue= true;
                    break;
                }

                if ((!empty($_row['rechazado']) && ($_row['cumplimiento'] == _SUSPENDIDO || $_row['cumplimiento'] == _REPROGRAMADO))
                        || ($this->toshow == _EVENTO_INDIVIDUAL && $row['_user_check'])) {
                    if (!$_row['user_check'] || $row['_user_check']) {
                        ++$this->cant_print_reject;
                        $continue= true;
                        break;
                }   }
                break;

            case(_PRINT_REJECT_OUT):
                if ((!empty($_row['rechazado']) && $_row['cumplimiento'] == _SUSPENDIDO)
                        || ($this->toshow == _EVENTO_INDIVIDUAL && $row['_user_check'])) {
                    ++$this->cant_print_reject;
                    $continue= true;
                    break;
                }
                break;
            default :
                $continue= true;
        }

        return $continue ? true : false;
    }

    protected function _list_reg($result) {
        $array_eventos= array();
        $this->total= 0;

        $this->externas= 0;
        $this->anual_externas= 0;
        $this->anual_propias= 0;
        $this->mensual_extras= 0;
        $this->mensual_externas= 0;
        $this->mensual_propias= 0;

        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($this->reg_fecha) && (strtotime($row['fecha_fin_plan']) > strtotime($this->reg_fecha)))
                continue;

            if (empty($array_eventos[$row['_id']]))
                ++$this->total;
            $array_eventos[$row['_id']]= 1;

            $this->cumplimiento = null;
            $array_responsable= null;
            if (empty($this->id_proceso)) {
                $array_responsable= array('id_responsable'=>$row['_id_responsable'], 'id_responsable_2'=>$row['id_responsable_2'],
                                    'responsable_2_reg_date'=>$row['responsable_2_reg_date']);
                $_row = $this->getEvento_reg($row['_id'], $array_responsable);
            }
            if (!empty($this->id_proceso)) {
                $_row = $this->get_reg_proceso($row['_id']);
            }

            if (!$this->_list_reg_if_continue($row, $_row))
                continue;

            $array = array('id'=>$row['_id'], 'evento' => $row['nombre'], 'plan' => $row['fecha_fin_plan'], 'real' => $row['_fecha_fin_real'],
                'observacion' => $_row['observacion'], 'descripcion' => $row['descripcion'], 'id_responsable' => $row['_id_responsable'],
                'id_user_asigna' => $row['id_user_asigna'], 'origen_data' => $row['origen_data'],
                'id_user_reg' => $_row['id_user_reg'], 'month' => $row['month'], 'year' => $row['year']);

        //    $if_frompast= !empty($row['id_copyform']) ? $this->test_if_from_past($row['id_copyfrom']) : false;
            /*
            /* true => tarea planificada. Entro antes de aprobar el plan
            /* false => No planificada. Entro despues de aprobar el plan
            */
            $if_extra= $this->test_if_extra($row['_id'], $this->aprobado, $array_responsable);

            $this->_list_reg_externas($row, $_row, $array, $if_extra);
            $this->_list_reg_efectivas($row, $_row, $array, $if_extra);
            $this->_list_reg_incumplidas($row, $_row, $array, $if_extra);
            $this->_list_reg_canceladas($row, $_row, $array, $if_extra);
            $this->_list_reg_reprogramadas($row, $_row, $array, $if_extra);
            $this->_list_reg_no_iniciadas($row, $_row, $array);
            $this->_list_reg_delegadas($row, $_row, $array);
        }
    }

    public function cumulative_plan($month= null, $id_usuario= null) {
        $array_planes= array();
        for ($i= 1; $i <= $month; $i++)
            $array_planes[$i]= null;

        $sql= "select * from tplanes where year = $this->year and tipo = $this->tipo_plan ";
        if (!empty($this->empresarial))
            $sql.= "and empresarial = $this->empresarial ";
        if (!empty($month))
            $sql.= "and (month is not null and month <= $month) ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($id_usuario))
            $sql.= "and id_usuario = $id_usuario ";
        $sql.= "order by year asc, month asc";
        $result= $this->do_sql_show_error('cumulative_plan', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array();
            $array= array_merge($array, $row);
            $array_planes[(int)$row['month']]= $array;
        }
        return $array_planes;
    }

    public function list_reg($toshow = _EVENTO_INDIVIDUAL) {
        $this->empresarial= null;
        $this->create_tmp_table_tidx= false;
        $this->init_list();

        if ($toshow == _EVENTO_ANUAL || $toshow == _EVENTO_MENSUAL) {
            $obj_prs = new Tproceso($this->clink);
            $obj_prs->get_procesos_up_cascade($this->id_proceso, null, null, false);
            $this->array_cascade_up = $obj_prs->array_cascade_up;
        } else {
            $this->id_proceso= null;
        }

        $this->automatic_event_status($toshow, null, null, true);
        $compute_all= $this->print_reject ? true : false;
        $result= $this->list_tmp_eventos($compute_all);
        if ($this->cant == 0)
            return _EMPTY;

        $this->total = $this->cant;
        $this->reg_fecha= null;
        $this->_list_reg($result);
    }

    public function list_reg_anual() {
        $this->toshow= _EVENTO_ANUAL;
        $this->empresarial= null;
        $this->month= null;

        $this->init_list();

        $obj= new Tproceso($this->clink);
        $obj->get_procesos_up_cascade($this->id_proceso,null,null,false);
        $this->array_cascade_up= $obj->array_cascade_up;

        $this->if_auditoria = false;
        $array_id = null;
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $this->reg_fecha= $this->cronos;

        $this->debug_time('create_tmp_teventos_steep_1');

        $_result= null;
        $_result= $this->automatic_event_status(_EVENTO_ANUAL, null, null, true);
        if ($_result == _EMPTY)
            return $this->error;

        $result= $this->list_tmp_eventos(null, false);
        if ($this->cant == 0)
            return _EMPTY;
        $this->total= $this->cant;

        $this->_list_reg($result);

        $this->anual= 0;
        $this->anual_externas= 0;
        $this->anual_propias= 0;

        $this->clink->data_seek($result);
        while ($row= $this->clink->fetch_array($result)) {
            $array_responsable= array('id_responsable'=>$row['_id_responsable'], 'id_responsable_2'=>$row['id_responsable_2'],
                                    'responsable_2_reg_date'=>$row['responsable_2_reg_date']);
            $_row = $this->getEvento_reg($row['_id'], $array_responsable);

            $continue= false;
            switch ($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if ($this->toshow == _EVENTO_INDIVIDUAL && $_row['user_check']) {
                        ++$this->cant_print_reject;
                        // temporalmente solo para los resumenes
                        --$this->total;
                        continue;
                    }

                    if ((!empty($_row['rechazado']) && ($_row['cumplimiento'] == _SUSPENDIDO || $_row['cumplimiento'] == _REPROGRAMADO))
                            || ($this->toshow == _EVENTO_INDIVIDUAL && $row['_user_check'])) {
                        if (!$_row['user_check'] || $row['_user_check']) ++$this->cant_print_reject;
                        $continue= true;
                    }
                    break;

                case(_PRINT_REJECT_OUT):
                    if ((!empty($_row['rechazado']) && ($_row['cumplimiento'] == _SUSPENDIDO || $_row['cumplimiento'] == _REPROGRAMADO))
                            || ($this->toshow == _EVENTO_INDIVIDUAL && $row['_user_check'])) {
                        ++$this->cant_print_reject;
                        $continue= true;
                    }
                    break;
            }

            if ($continue)
                continue;
            ++$this->anual;

            $empresarial= ($row['empresarial'] - 1);
            $ifassure= boolean($row['ifassure']) ? true : false;
            if ($ifassure) ++$this->assure;

            if (array_key_exists($this->id_proceso, $this->array_cascade_up) !== false || !empty($row['funcionario'])) {
                ++$this->anual_array[$empresarial];
                ++$this->anual_externas_array[$empresarial];
                ++$this->anual_externas;

                if ($ifassure) {
                    ++$this->assure_array[$empresarial];
                    ++$this->assure_externas_array[$empresarial];
                    ++$this->assure_externas;
                }
            } else {
                ++$this->anual_array[$empresarial];
                ++$this->anual_propias_array[$empresarial];
                ++$this->anual_propias;

                if ($ifassure) {
                    ++$this->assure_array[$empresarial];
                    ++$this->assure_propias_array[$empresarial];
                    ++$this->assure_propias;
                }
            }
        }
    }

    private function test_if_efectiva($id_evento) {
        if (array_key_exists($this->extras_list, $id_evento))
            return false;
        elseif (array_key_exists($this->canceladas, $id_evento))
            return false;

        return true;
    }

    public function list_puntualizacion() {
        if (isset($this->array_eventos)) unset($this->array_eventos);
        $this->array_eventos= array();

        $array_id_copyfrom= array();
        $this->toshow= _EVENTO_MENSUAL;
        $this->create_tmp_teventos();

        $result= $this->list_tmp_eventos();
        $this->fix_tmp_teventos_prs($result);
        unset($result);
        $result= $this->list_tmp_eventos();

        while ($row= $this->clink->fetch_array($result)) {
            if ($this->array_eventos[$row['_id']])
                continue;
            $observacion= null;
            $valid= false;

            $_row = $this->get_reg_proceso($row['_id']);

            $array = array('id'=>$row['_id'], 'evento' => $row['nombre'], 'plan' => $row['fecha_fin_plan'], 'real' => $row['_fecha_fin_real'],
                'observacion' => $_row['observacion'], 'descripcion' => $row['descripcion'], 'id_responsable' => $row['_id_responsable'],
                'id_user_asigna' => $row['id_user_asigna'], 'origen_data' => $row['origen_data'], 'observacion'=>$row['observacion'],
                'id_user_reg' => $_row['id_user_reg'], 'month' => $row['month'], 'year' => $row['year']);

            $if_extra= $this->test_if_extra($row['_id'], $this->aprobado);

            $this->_list_reg_externas($row, $_row, $array, $if_extra);
            $this->_list_reg_canceladas($row, $_row, $array, $if_extra);
            $reprogramada= $this->_list_reg_reprogramadas($row, $_row, $array, $if_extra);
            
            $delegada= $this->_list_reg_delegadas($row, $_row, $array);

            if ($if_extra) {
                $valid= true;
                $observacion.= "Actividad extra plan.<br/>";
            }
            if ($delegada) {
                $valid= true;
                $observacion.= "Asignado nuevo responsable o lugar.<br/>";
            }
            if ($row['cumplimiento'] == _SUSPENDIDO || $row['cumplimiento'] == _CANCELADO || $row['cumplimiento'] == _POSPUESTO
                                                                                            || $row['cumplimiento'] == _DELEGADO) {
                $valid= true;
                $observacion.= "Suspendida, Postpuesta o delegada.<br/>";
            }
            if ($row['_toshow'] == _EVENTO_MENSUAL) {
                $valid= true;
                $observacion.= "Agregada al Mensual.<br/>";
            }
            if ($reprogramada && $row['_toshow'] == _EVENTO_MENSUAL) {
                $valid= true;
                $observacion.= "Reprogramada.<br/>";
            }

            if ($valid) {
                if (!empty($row['id_copyfrom']) && $row['id_copyfrom'] != $row['_id']) {
                    $array_id_copyfrom[]= array($row['id_copyfrom'], $row['_id']);
                    $_row= $row['id_copyfrom'] != $row['_id'] ? $this->_set($row['id_copyfrom']) : array_merge($row, array());

                    $observacion.=($row['id_copyfrom'] != $row['_id'] ? $_row['_observacion'] : $row['_observacion']);
                    $fecha_plan= $_row['fecha_inicio_plan'];
                    $fecha_new= $row['fecha_inicio_plan'];
                }
                else {
                    $fecha_plan= null;
                    $fecha_new= $row['fecha_inicio_plan'];
                    $_row= array_merge($row, array());
                }

                $array= array('id'=>$row['_id'], 'numero'=>$_row['numero'], 'evento'=>$_row['nombre'], 'empresarial'=>$_row['empresarial'],
                        'id_tipo_evento'=>$_row['id_tipo_evento'], 'plan'=>$fecha_plan, 'new'=>$fecha_new, 'observacion'=>$observacion,
                        'fecha_inicio'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'], 'id_responsable'=>$row['_id_responsable']);
                $this->array_eventos[$row['_id']]= $array;
            }
        }
    }
}

/*
 * Definiciones adicionales
 */
include_once "code.class.php";

if (!class_exists('Tmp_table_planning'))
    include_once "tmp_tables_planning.class.php";
if (!class_exists('Tobjetivo_ci'))
    include_once "objetivo_ci.class.php";
if (!class_exists('Tproceso_item'))
    include_once "proceso_item.class.php";