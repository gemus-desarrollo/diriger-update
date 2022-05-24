<?php
/**
 * Description of base_plantrab
 *
 * @author mustelier
 */

if (!class_exists('Tbase_plantrab'))
    include_once "base_plantrab.class.php";

class Tplantrab extends Tbase_plantrab  {
    public $exec_automatic_event;
    public $cant_print_reject;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_plantrab::__construct($clink);

        $this->exec_automatic_event= true;
    }

    public function Set($id= null) {
        if (!empty($id))
            $this->id_plan= $id;

        $sql= "select * from tplanes where year = $this->year and tipo = $this->tipo_plan ";
        if (empty($this->id_plan)) {
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso = $this->id_proceso ";
            if (!empty($this->id_usuario))
                $sql.= "and id_usuario = $this->id_usuario ";
            if (!empty($this->month))
                $sql.= "and month = $this->month ";
            if (!empty($this->empresarial))
                $sql.= "and empresarial = $this->empresarial ";
        } else
            $sql.= "and id = $this->id_plan ";
        $sql.= "order by cronos desc LIMIT 1";

        $result= $this->do_sql_show_error('Set', $sql);
        $row= $this->clink->fetch_array($result);

        $this->id_plan= $row['id'];

        if (!empty($this->id_plan)) {
            $this->id_code= $row['id_code'];
            $this->id_plan_code= $this->id_code;

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

            $this->month= $row['month'];
            $this->year= $row['year'];
            $this->id_usuario= $row['id_usuario'];
            $this->tipo_plan= $row['tipo'];

            $this->id_responsable= $row['id_responsable'];
            $this->empresarial= $row['empresarial'];

            $this->cumplimiento= $row['cumplimiento'];
            $this->if_numering= $row['if_numering'];

            $this->total= $row['total'];
            $this->cumplidas= $row['cumplidas'];
            $this->incumplidas= $row['incumplidas'];
            $this->canceladas= $row['canceladas'];
            $this->modificadas= $row['modificadas'];
            $this->delegadas= $row['delegadas'];
            $this->reprogramadas= $row['reprogramadas'];

            $this->efectivas= $row['efectivas'];
            $this->efectivas_cumplidas= $row['efectivas_cumplidas'];
            $this->efectivas_incumplidas= $row['efectivas_incumplidas'];
            $this->efectivas_canceladas= $row['efectivas_canceladas'];

            $this->externas= $row['externas'];

            $this->extras= $row['extras'];
            $this->extras_externas= $row['extras_externas'];
            $this->extras_propias= $row['extras_propias'];

            $this->anual= $row['anual'];
            $this->anual_externas= $row['anual_externas'];
            $this->anual_propias= $row['anual_propias'];

            $this->anual= $row['anual'];
            $this->anual_externas= $row['anual_externas'];
            $this->anual_propias= $row['anual_propias'];

            $this->mensual= $row['mensual'];
            $this->mensual_externas= $row['mensual_externas'];
            $this->mensual_propias= $row['mensual_propias'];

            $this->assure= $row['assure'];
            $this->assure_externas= $row['assure_externas'];
            $this->assure_propias= $row['assure_propias'];

            $this->aprobado= $row['aprobado'];
            $this->evaluado= $row['evaluado'];
            $this->evaluacion= $row['evaluacion'];
            $this->auto_evaluado= $row['auto_evaluado'];
            $this->auto_evaluacion= $row['auto_evaluacion'];
            $this->objetivo= stripslashes($row['objetivos']);
            
            $this->id_responsable_aprb= $row['id_responsable_aprb'];
            $this->id_responsable_eval= $row['id_responsable_eval'];
            $this->id_responsable_auto_eval= $row['id_responsable_auto_eval'];

            for ($i= 1; $i < 7; $i++) {
                $num= number_format_to_roman($i);
                $this->anual_externas_array[$i]= $row["anual_externas_{$num}"];
                $this->anual_propias_array[$i]= $row["anual_propias_{$num}"];
                $this->anual_array[$i]= $this->anual_externas_array[$i] + $this->anual_propias_array[$i];

                $this->assure_externas_array[$i]= $row["assure_externas_{$num}"];
                $this->assure_propias_array[$i]= $row["assure_propias_{$num}"];
                $this->assure_array[$i]= $this->assure_externas_array[$i] + $this->assure_propias_array[$i];
            }

            $this->kronos= $row['cronos'];
        }

        return $this->id_plan;
    }

    public function add_plan() {
        $id_usuario = setNULL($this->id_usuario);
        $month = setNULL_empty($this->month);
        $empresarial = setNULL($this->empresarial);

        $this->cronos = date('Y-m-d H:i:s');

        $sql = "insert into tplanes (tipo, id_usuario, id_proceso, id_proceso_code, month, year, empresarial, ";
        $sql .= "cronos, situs) values ($this->tipo_plan, $id_usuario, $this->id_proceso, '$this->id_proceso_code', $month, ";
        $sql .= "$this->year, $empresarial, '$this->cronos', '$this->location'); ";
        $result = $this->do_sql_show_error('add_plan', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tplanes");
            $this->id_plan = $this->id;

            $this->obj_code->SetId($this->id_plan);
            $this->obj_code->set_code('tplanes', 'id', 'id_code');
            $this->id_plan_code = $this->obj_code->get_id_code();
            $this->id_code = $this->id_plan_code;

            $this->action = "CREADO";
            $this->update_reg_plan();
        }
        return $this->id_plan;
    }

    private function _add_plan($sql_field, $sql_data) {
        $error = NULL;
        $id_usuario = setNULL($this->id_usuario);
        $month = setNULL_empty($this->month);

        $sql = "insert into tplanes (id_usuario, id_proceso, id_proceso_code, month, year, empresarial, ";
        $sql .= $sql_field;
        $sql .= "cronos, situs) values ($id_usuario, $this->id_proceso, '$this->id_proceso_code', $month, $this->year, ";
        $sql .= "$this->empresarial, ";
        $sql .= $sql_data;
        $sql .= "'$this->cronos', '$this->location') ";
        $result = $this->do_sql_show_error('_add_plan', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tplanes");
            $this->id_plan = $this->id;
        }

        return $this->error;
    }

    public function addEval() {
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $evaluacion = setNULL_str($this->evaluacion);

        $sql_field = "cumplimiento, evaluado, evaluacion, id_responsable_eval, ";
        $sql_data = "$this->cumplimiento, '$this->cronos', $evaluacion, $this->id_responsable, ";

        $error = $this->_add_plan($sql_field, $sql_data);

        if (is_null($error)) {
            $this->observacion = $this->evaluacion;
            $this->action = 'EVALUADO';
            $error = $this->update_reg_plan();
        }

        return $error;
    }

    public function addAutoEval() {
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $auto_evaluacion = setNULL_str($this->auto_evaluacion);

        $sql_field = "cumplimiento, auto_evaluado, auto_evaluacion, id_responsable_auto_eval, ";
        $sql_data = "$this->cumplimiento, '$this->cronos', $auto_evaluacion, $this->id_responsable, ";

        $error = $this->_add_plan($sql_field, $sql_data);

        if (is_null($error)) {
            $this->action = 'AUTO_EVALUADO';
            $this->observacion = $this->auto_evaluacion;
            $error = $this->update_reg_plan();
        }

        return $error;
    }

    public function addAprove() {
        $objetivo = setNULL_str($this->objetivo);

        $sql_field = "objetivos, aprobado, id_responsable_aprb, ";
        $sql_data = "$objetivo, '$this->cronos', $this->id_responsable, ";

        $error = $this->_add_plan($sql_field, $sql_data);

        if (is_null($error)) {
            $to_users= $this->empresarial ? true : false;
            $this->aprove_to_users(null, $to_users);

            $this->action = 'APROBADO';
            $this->observacion = $this->objetivo;
            $error = $this->update_reg_plan();
        }

        return $error;
    }

    public function addObjective() {
        $objetivo = setNULL_str($this->objetivo);

        $sql_field = "objetivos, id_responsable, ";
        $sql_data = "$objetivo, $this->id_responsable, ";

        $error = $this->_add_plan($sql_field, $sql_data);

        if (is_null($error)) {
            $this->action = 'OBJETIVOS';
            $this->observacion = $this->objetivo;
            $error = $this->update_reg_plan();
        }
        return $error;
    }

    public function update() {
        $this->action = "PLAN ACTUALIZADO";
        $this->update_plan();
        $this->update_reg_plan();
    }

    private function _aprove_user($obj_reg, $row, $id_usuario) {
        $id_evento = (int) $row['_id'];
        $id_evento_code = $row['_id_code'];

        $obj_reg->SetIdEvento($id_evento);
        $obj_reg->set_id_code($id_evento_code);
        $obj_reg->set_id_evento_code($id_evento_code);
        $obj_reg->SetIdTarea($row['_id_tarea']);
        $obj_reg->set_id_tarea_code($row['_id_tarea_code']);
        $obj_reg->SetIdAuditoria($row['_id_auditoria']);
        $obj_reg->set_id_auditoria_code($row['_id_auditoria_code']);
        $obj_reg->SetIdArchivo($row['_id_archivo']);
        $obj_reg->set_id_archivo_code($row['_id_archivo_code']);

        $rowcmp = $this->get_last_reg($id_evento, $id_usuario);
        if (is_null($rowcmp))
            return null;

        if (!empty($rowcmp['rechazado'])
            && ($rowcmp['cumplimiento'] == _SUSPENDIDO || $rowcmp['cumplimiento'] == _CANCELADO || $rowcmp['cumplimiento'] == _DELEGADO))
            return null;

        $obj_reg->SetIdUsuario($id_usuario);
        $obj_reg->SetObservacion($rowcmp['observacion']);
        $obj_reg->SetCumplimiento($rowcmp['cumplimiento']);
        $obj_reg->SetRechazado($rowcmp['rechazado']);
        $obj_reg->compute = boolean($rowcmp['_compute']);
        $obj_reg->toshow = boolean($rowcmp['_toshow']);
        $obj_reg->set_user_check(boolean($rowcmp['_user_check']));
        $obj_reg->SetHours($rowcmp['horas']);
        $obj_reg->SetFecha($rowcmp['reg_fecha']);

        $sql= $obj_reg->add_cump(null, true);
        return $sql;
    }

    private function _aprove_to_users($result, $array_usuarios, $obj_reg, $to_users) {
        $i = 0;
        $sql = null;
        $rowcmp= array();
        while ($row = $this->clink->fetch_array($result)) {
            $in_prs = true;

            $rowcmp['_id'] = $row['_id'];
            $rowcmp['_id_code']= $row['_id_code'];
            $rowcmp['_id_tarea']= $row['_id_tarea'];
            $rowcmp['_id_tarea_code']= $row['_id_tarea_code'];
            $rowcmp['_id_auditoria']= $row['_id_auditoria'];
            $rowcmp['_id_auditoria_code']= $row['_id_auditoria_code'];
            $rowcmp['_id_archivo']= $row['_id_archivo'];
            $rowcmp['_id_archivo_code']= $row['_id_archivo_code'];

            reset($array_usuarios);
            foreach ($array_usuarios as $id_usuario) {
                if ($this->toshow || $to_users) {
                    $in_prs = $this->if_in_tmp_tusuarios($id_usuario);
                    if (!$in_prs)
                        continue;
                }

                if (isset($array[$id_usuario][$rowcmp['_id']]))
                    continue;
                $array[$id_usuario][$rowcmp['_id']]= 1;
                $sql.= $this->_aprove_user($obj_reg, $rowcmp, $id_usuario);
            }

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('aprove_to_users', $sql);
                $sql = null;
                $i = 0;
            }

            ++$i;
        }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('aprove_to_users', $sql);

        unset($obj_reg);
    }

    private function aprove_to_users($corte = 'all_month', $to_users= false) {
        $corte= !is_null($corte) ? $corte : 'all_month';
        $to_users= !is_null($to_users) ? $to_users : false;
        $this->date_interval($fecha_inicio, $fecha_fin, $corte);

        $result = $this->list_tmp_eventos(true, false); // from _teventos, _treg_evento
        if (empty($this->cant))
            return;

        $obj_reg= new Tregister_planning($this->clink);
        $obj_reg->SetYear($this->year);
        $obj_tables= new Ttmp_tables_planning($this->clink);
        $obj_tables->SetYear($this->year);

        if ($this->toshow > 0 || $to_users) {
            $this->set_use_copy_tusuarios(true);
            $obj_tables->add_to_tmp_tusuarios($this->id_proceso);
            $this->if_tusuarios= $obj_tables->if_tusuarios;
        }

        $obj_reg->set_cronos($this->cronos);
        $obj_reg->SetAprobado($this->cronos);
        $obj_reg->SetIdResponsable($this->id_responsable);

        $array = array();
        $array_usuarios= array();
        if ($this->toshow || empty($this->id_usuario)) {
            if ($this->if_treg_evento) {
                $sql = "select distinct tusuarios.id as id_usuario, id_proceso from _treg_evento, tusuarios ";
                $sql.= "where tusuarios.id = _treg_evento.id_usuario";
            } else {
                $sql = "select distinct tusuarios.id as id_usuario, id_proceso from treg_evento_{$this->year}, tusuarios ";
                $sql.= "where tusuarios.id = treg_evento_{$this->year}.id_usuario ";
                $sql.= "and treg_evento_{$this->year}.id_evento in (select id from _teventos) ";
            }
            $_result = $this->do_sql_show_error('aprove_to_users', $sql);

            while ($row = $this->clink->fetch_array($_result)) {
                if (!$this->test_if_proceso_in_entity($row['id_proceso']))
                    continue;
                $array_usuarios[$row['id_usuario']] = $row['id_usuario'];
            }
        } else
            $array_usuarios[$this->id_usuario] = $this->id_usuario;

        $this->_aprove_to_users($result, $array_usuarios, $obj_reg, $to_users);
    }

    private function aprove_to_process($corte) {
        $corte= !is_null($corte) ? $corte : 'all_month';
        $this->date_interval($fecha_inicio, $fecha_fin, $corte);

        $result = $this->list_tmp_eventos(true, false); // from _teventos, _treg_evento
        if (empty($this->cant))
            return;

        $obj_reg= new Tregister_planning($this->clink);
        $obj_reg->SetYear($this->year);
        $obj_reg->SetIdProceso($this->id_proceso);
        $obj_reg->set_id_proceso_code($this->id_proceso_code);
        $obj_reg->set_cronos($this->cronos);

        $i = 0;
        $sql = null;
        $array_ids= array();
        while ($row = $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= 1;

            $id_evento = (int) $row['_id'];
            $id_evento_code = $row['_id_code'];

            $obj_reg->SetIdEvento($id_evento);
            $obj_reg->set_id_code($id_evento_code);
            $obj_reg->set_id_evento_code($id_evento_code);
            $obj_reg->SetIdTarea($row['_id_tarea']);
            $obj_reg->set_id_tarea_code($row['_id_tarea_code']);
            $obj_reg->SetIdAuditoria($row['_id_auditoria']);
            $obj_reg->set_id_auditoria_code($row['_id_auditoria_code']);

            $rowcmp= $obj_reg->get_reg_proceso();

            $rowcmp['aprobado']= $this->cronos;
            $rowcmp['id_usuario']= $_SESSION['id_usuario'];
            $rowcmp['id_responsable_aprb']= $_SESSION['id_usuario'];
            $obj_reg->SetCumplimiento($rowcmp['cumplimiento']);
            $obj_reg->SetObservacion(!empty($this->observacion) ? $this->observacion : $rowcmp['observacion']);
            
            $sql.= $obj_reg->add_cump_proceso(null, null, $rowcmp, true);
            $sql.= $this->_aprove_user($obj_reg, $row, $rowcmp['id_responsable']);

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('aprove_to_process', $sql);
                $sql = null;
                $i = 0;
            }

            ++$i;
        }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('aprove_to_process', $sql);

        unset($obj_reg);
    }

    private function update_plan($multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $objetivos= setNULL_str($this->objetivo);
        $evaluacion= setNULL_str($this->evaluacion);
        $auto_evaluacion= setNULL_str($this->auto_evaluacion);

        $aprobado= setNULL_str($this->aprobado);
        $evaluado= setNULL_str($this->evaluado);
        $auto_evaluado= setNULL_str($this->auto_evaluado);

        $id_responsable_aprb= setNULL($this->id_responsable_aprb);
        $id_responsable_eval= setNULL($this->id_responsable_eval);
        $id_responsable_auto_eval= setNULL($this->id_responsable_auto_eval);

        $total= setZero($this->total);
        $cumplidas= setZero($this->cumplidas);
        $incumplidas= setZero($this->incumplidas);
        $canceladas= setZero($this->canceladas);
        $modificadas= setZero($this->modificadas);
        $delegadas= setZero($this->delegadas);
        $reprogramadas= setZero($this->reprogramadas);

        $externas= setNULL($this->externas);

        $extras= setZero($this->extras);
        $extras_externas= setNULL($this->extras_externas);
        $extras_propias= setNULL($this->extras_propias);

        $efectivas= setZero($this->efectivas);
        $efectivas_cumplidas= setNULL($this->efectivas_cumplidas);
        $efectivas_incumplidas= setNULL($this->efectivas_incumplidas);
        $efectivas_canceladas= setNULL($this->efectivas_canceladas);

        $mensual= setNULL($this->mensual);
        $mensual_propias= setNULL($this->mensual_propias);
        $mensual_externas= setNULL($this->mensual_externas);

        $anual= $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? setNULL($this->anual) : setNULL($this->anual);
        $anual_propias= $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? setNULL($this->anual_propias) : setNULL($this->anual_propias);
        $anual_externas= $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? setNULL($this->anual_externas) : setNULL($this->anual_externas);

        $assure= setNULL($this->assure);
        $assure_propias= setNULL($this->assure_propias);
        $assure_externas= setNULL($this->assure_externas);

        $this->cronos= !empty($this->cronos)? $this->cronos : date('Y-m-d H:i:s');

        $sql= "update tplanes set objetivos= $objetivos, total= $total, cumplidas= $cumplidas, incumplidas= $incumplidas, canceladas= $canceladas, ";
        $sql.= "modificadas= $modificadas, delegadas= $delegadas, reprogramadas= $reprogramadas, externas= $externas, extras= $extras, ";
        $sql.= "extras_externas= $extras_externas, extras_propias= $extras_propias, efectivas= $efectivas, efectivas_cumplidas= $efectivas_cumplidas, ";
        $sql.= "efectivas_incumplidas= $efectivas_incumplidas, efectivas_canceladas= $efectivas_canceladas, anual= $anual, ";
        $sql.= "anual_propias= $anual_propias, anual_externas= $anual_externas, mensual= $mensual, mensual_propias= $mensual_propias, ";
        $sql.= "mensual_externas= $mensual_externas, assure= $assure, assure_propias= $assure_propias, assure_externas= $assure_externas, ";
        $sql.= "id_responsable= $this->id_responsable,  cronos= '$this->cronos', situs= '$this->location' ";
        for ($i= 1; $i < 7; $i++) {
            $num= number_format_to_roman($i);
            $sql.= ", ".stringSQL("anual_externas_{$num}")."= ".setNULL($this->anual_externas_array[$i]);
            $sql.= ", ".stringSQL("anual_propias_{$num}")."= ".setNULL($this->anual_propias_array[$i]);

            $sql.= ", ".stringSQL("assure_externas_{$num}")."= ".setNULL($this->assure_externas_array[$i]);
            $sql.= ", ".stringSQL("assure_propias_{$num}")."= ".setNULL($this->assure_propias_array[$i])." ";
        }
        if ($this->action === 'APROBADO')
            $sql.= ", aprobado= $aprobado, id_responsable_aprb= $id_responsable_aprb ";
        if ($this->action === 'EVALUADO') {
            $sql.= ", evaluacion= $evaluacion, evaluado= $evaluado, id_responsable_eval= $id_responsable_eval ";
            if (!is_null($this->cumplimiento))
                $sql.= ", cumplimiento= $this->cumplimiento ";
        }
        if ($this->action === 'AUTO_EVALUADO')
            $sql.= ", auto_evaluacion= $auto_evaluacion, auto_evaluado= $auto_evaluado, id_responsable_auto_eval= $id_responsable_auto_eval ";
        $sql.= "where id = $this->id_plan; ";

        if ($multi_query)
            return $sql;

        $result= $this->do_sql_show_error('update_plan', $sql);
        return $this->error;
    }

    public function updateEval() {
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');

        $this->evaluado = $this->cronos;
        $this->id_responsable_eval = $this->id_responsable;

        $this->action = 'EVALUADO';
        $error = $this->update_plan();

        if (is_null($error)) {
            $this->observacion = $this->evaluacion;
            $error = $this->update_reg_plan();
        }

        return $error;
    }

    public function updateAutoEval() {
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');

        $this->auto_evaluado = $this->cronos;
        $this->id_responsable_auto_eval = $this->id_responsable;

        $this->action = 'AUTO_EVALUADO';
        $error = $this->update_plan();

        if (is_null($error)) {
            $this->observacion = $this->auto_evaluacion;
            $error = $this->update_reg_plan();
        }

        return $error;
    }

    public function updateAprove($corte = 'all_month', $reg_data = true) {
        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');
        $error = null;
        $this->aprobado = $this->cronos;
        $this->id_responsable_aprb = $this->id_responsable;

        $this->evaluado = null;
        $this->id_responsable_eval = null;
        $this->auto_evaluado = null;
        $this->id_responsable_auto_eval = null;
        $this->cumplimiento = null;

        if ($reg_data) {
            $this->action = 'APROBADO';
            $error= $this->update_plan();

            if (is_null($error)) {
                $this->observacion = $this->objetivo;
                $error = $this->update_reg_plan();        
        }   }

        $this->observacion= null;
        $this->debug_time('update_reg_plan');
        $to_users= $this->toshow ? true : false;

        if (is_null($error)) {
            $this->aprove_to_users($corte, $to_users);

            if ($this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL)
                $this->aprove_to_process($corte);
        }

        $this->debug_time('aprove_to_users');
        return $error;
    }

    public function updateObjective($multi_query = false) {
        $this->aprobado = null;
        $this->id_responsable_aprb = null;
        $this->evaluado = null;
        $this->id_responsable_eval = null;
        $this->auto_evaluado = null;
        $this->id_responsable_auto_eval = null;

        $this->observacion = $this->objetivo;
        $this->action = 'OBJETIVOS';

        $sql = null;
        $error = $this->update_plan($multi_query);
        if ($multi_query)
            $sql = $error;

        if ((!$multi_query && is_null($error)) || $multi_query)
            $error = $this->update_reg_plan($multi_query);
        if ($multi_query)
            $sql .= $error;

        return $multi_query ? $sql : $error;
    }

    public function update_objetive_to_users() {
        $this->listar_procesos_entity();

        $obj_org= new Torgtarea($this->clink);
        $obj_org->SetIdResponsable($this->id_responsable);
        $this->array_usuarios= $obj_org->get_subordinados_array();
        $count= count($this->array_usuarios);
        if (empty($count))
            return;

        $obj= new Tplantrab($this->clink);
        $obj->SetTipo(_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL);
        $obj->SetIfEmpresarial(_EVENTO_INDIVIDUAL);
        $obj->SetYear($this->year);
        $obj->SetMonth($this->month);

        $sql= null;
        $i= 0;
        foreach ($this->array_usuarios as $array) {
            if (!empty($this->id_usuario) && $this->id_usuario == $array['id'])
                continue;
            if (!$this->test_if_proceso_in_entity($array['id_proceso']))
                continue;

            $obj->SetIdProceso(null);
            $obj->set_id_proceso_code(null);
            $obj->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL);
            $obj->SetIdUsuario($array['id']);
            $obj->SetIdPlan(null);

            $id= $obj->Set();

            $obj->SetIdResponsable($this->id_responsable);

            $observacion= !empty($id) ? $obj->GetObjetivo() : NULL;

            if (empty($id)) {
                $obj->set_cronos($this->cronos);
                $obj->SetObjetivo($this->objetivo);
                $obj->SetIdProceso($_SESSION['id_entity']);
                $obj->set_id_proceso_code($_SESSION['id_entity_code']);

                $obj->add_plan();

                $obj->SetObjetivo($this->objetivo);
                $obj->set_cronos($this->cronos);
                $sql.= $obj->updateObjective(true);
            } else {
                if ($this->id_plan == $id)
                    continue;

                if (stripos($this->objetivo, $observacion) === false)
                    $obj->SetObjetivo($observacion."<br />".$this->objetivo);

                $obj->set_cronos($this->cronos);
                $sql.= $obj->updateObjective(true);
            }

            if ($i >= 1000) {
                $this->do_multi_sql_show_error('update_objetive_to_users', $sql);
                $sql= null;
                $i= 0;
            }

            ++$i;
        }

        if (!is_null($sql))
            $this->do_multi_sql_show_error('update_objetive_to_users', $sql);
    }

    private function update_reg_plan($multi_query = false) {
        $multi_query = !is_null($multi_query) ? $multi_query : false;
        $total = setZero($this->total);
        $cumplidas = setZero($this->cumplidas);
        $incumplidas = setZero($this->incumplidas);
        $canceladas = setZero($this->canceladas);
        $modificadas = setZero($this->modificadas);
        $delegadas = setZero($this->delegadas);
        $reprogramadas = setZero($this->reprogramadas);

        $externas = setNULL($this->externas);

        $extras = setZero($this->extras);
        $extras_externas = setNULL($this->extras_externas);
        $extras_propias = setNULL($this->extras_propias);

        $efectivas = setZero($this->efectivas);
        $efectivas_cumplidas = setNULL($this->efectivas_cumplidas);
        $efectivas_incumplidas = setNULL($this->efectivas_incumplidas);
        $efectivas_canceladas = setNULL($this->efectivas_canceladas);

        $anual = $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? setNULL($this->anual) : setNULL($this->anual);
        $anual_propias = $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? setNULL($this->anual_propias) : setNULL($this->anual_propias);
        $anual_externas = $this->tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? setNULL($this->anual_externas) : setNULL($this->anual_externas);

        $mensual = setNULL($this->mensual);
        $mensual_propias = setNULL($this->mensual_propias);
        $mensual_externas = setNULL($this->mensual_externas);

        $assure = setNULL($this->assure);
        $assure_propias = setNULL($this->assure_propias);
        $assure_externas = setNULL($this->assure_externas);

        $cumplimiento = setNULL($this->cumplimiento);
        $observacion = setNULL_str($this->observacion);

        $this->cronos = !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');

        $sql = "insert into treg_plantrab (id_plan, id_plan_code, id_proceso, id_proceso_code, action, cumplimiento, ";
        $sql .= "observacion, id_usuario, total, cumplidas, incumplidas, canceladas, modificadas, delegadas, reprogramadas, ";
        $sql .= "externas, extras, extras_externas, extras_propias, efectivas, efectivas_cumplidas, efectivas_incumplidas, ";
        $sql .= "efectivas_canceladas, anual, anual_propias, anual_externas, mensual, mensual_externas, mensual_propias, ";
        $sql .= "assure, assure_propias, assure_externas, cronos, situs ";
        for ($i = 1; $i < 7; $i++) {
            $num = number_format_to_roman($i);
            $sql .= ", " . stringSQL("anual_externas_{$num}");
            $sql .= ", " . stringSQL("anual_propias_{$num}");

            $sql .= ", " . stringSQL("assure_externas_{$num}");
            $sql .= ", " . stringSQL("assure_propias_{$num}");
        }
        $sql .= ") values ($this->id_plan, '$this->id_plan_code', ";
        $sql .= "$this->id_proceso, '$this->id_proceso_code', '$this->action', $cumplimiento, $observacion, $this->id_responsable, ";
        $sql .= "$total, $cumplidas, $incumplidas, $canceladas, $modificadas, $delegadas, $reprogramadas, $externas, $extras, ";
        $sql .= "$extras_externas, $extras_propias, $efectivas, $efectivas_cumplidas, $efectivas_incumplidas, ";
        $sql .= "$efectivas_canceladas, $anual, $anual_propias, $anual_externas, $mensual, $mensual_propias, $mensual_externas, ";
        $sql .= "$assure, $assure_propias, $assure_externas, '$this->cronos', '$this->location' ";
        for ($i = 1; $i < 7; $i++) {
            $sql .= ", " . setNULL($this->anual_externas_array[$i]);
            $sql .= ", " . setNULL($this->anual_propias_array[$i]);
            $sql .= ", " . setNULL($this->assure_externas_array[$i]);
            $sql .= ", " . setNULL($this->assure_propias_array[$i]);
        }
        $sql .= "); ";

        if ($multi_query)
            return $sql;

        $result= $this->do_sql_show_error('update_reg_plan', $sql);
        return $this->error;
    }

    public function listar_plan() {
        $sql = "select from tplanes where " . year2pg("cronos") . " >= $this->year and tipo = $this->tipo_plan ";
        if ($_SESSION['nivel'] < _ADMINISTRADOR) {
            if (!empty($this->id_responsable) && empty($this->id_usuario))
                $sql .= "and (tplanes.id_responsable = $this->id_responsable or tplanes.id_responsable_comp = $this->id_responsable)";
            if (empty($this->id_responsable) && !empty($this->id_usuario))
                $sql .= "tplanes.id_usuario = $this->id_usuario ";
        }
        if (empty($this->id_usuario))
            $sql = "and id_proceso = $this->id_proceso ";
        if (!empty($this->month))
            $sql .= "and " . month2pg("cronos") . " >= $this->month ";
        $sql .= "order by cronos desc ";

        $result = $this->do_sql_show_error('listar_plan', $sql);
        return $result;
    }

    public function listar_status_plan($flag = _APROBADO) {
        $sql = "select distinct treg_plantrab.* from tplanes, treg_plantrab where treg_plantrab.id_plan= tplanes.id ";
        $sql .= "and tplanes.year = $this->year and tplanes.tipo = $this->tipo_plan ";
        if (!empty($this->id_usuario))
            $sql .= "and tplanes.id_usuario = $this->id_usuario ";
        if (!empty($this->id_proceso) && (is_null($this->empresarial) || !empty($this->empresarial)))
            $sql .= "and tplanes.id_proceso = $this->id_proceso ";
        if (!is_null($this->empresarial))
            $sql .= "and empresarial = $this->empresarial ";
        if (!empty($this->month))
            $sql .= "and tplanes.month = $this->month ";
        if ($flag == _APROBADO)
            $sql .= "and " . instr2pg("upper(action)", "'APRO'") . " = 1 ";
        if ($flag == _EVALUADO)
            $sql .= "and " . instr2pg("upper(action)", "'EVAL'") . " = 1 ";
        if ($flag == _AUTO_EVALUADO)
            $sql .= "and " . instr2pg("upper(action)", "'AUTO'") . " = 1 ";
        if ($flag == _OBJETIVO_FIJADO)
            $sql .= "and " . instr2pg("upper(action)", "'OBJET'") . " = 1 ";
        $sql .= "order by treg_plantrab.cronos desc ";

        $result = $this->do_sql_show_error('listar_status_plan', $sql);
        return $result;
    }

    public function listar_objetivos() {
        $obj= new Tobjetivo_ci($this->clink);

        $obj->SetIdProceso($this->id_proceso);
        $obj->SetYear($this->year);

        $result= $obj->listar();
        return $result;
    }
}

/*
 * Definiciones adicionales
 */

include_once "../config.inc.php";
include_once "time.class.php";

if (!class_exists('Tevento'))
    include_once "evento.class.php";
if (!class_exists('Tproceso'))
    include_once "proceso.class.php";
if (!class_exists('Torgtarea'))
    include_once "orgtarea.class.php";
