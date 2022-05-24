<?php
/**
 * Description of register_planning
 *
 * @author mustelier
 */

if (!class_exists('Tregister_planning_base'))
    include_once "register_planning_base.class.php";

class Tregister_planning  extends Tregister_planning_base {
    private $past,
            $past_month,
            $present,
            $future;
    private $cumplimiento_aux;
    protected $subcapitulos_evento;
    public $exec_automatic_event;

    public function  __construct($clink) {
        $this->clink= $clink;
        Tregister_planning_base::__construct($clink);
        $this->control_list= 0;

        $this->toshow= null;
        $this->toshow_plan= NULL;
        $this->compute= null;

        $time= new TTime;
        $this->c_year= (int)$time->GetYear();
        $this->c_month= (int)$time->GetMonth();
        $this->c_day= (int)$time->GetDay();
        $this->today= $time->GetStrTime();

        $this->print_reject= null;
        $this->go_delete= _DELETE_YES;
        $this->exec_automatic_event= true;
    }

    public function _set($id) {
        $table= $this->if_teventos ? "_teventos" : "teventos";

        $sql= "select distinct {$table}.id as _id, {$table}.id_code as _id_code, numero, nombre, fecha_inicio_plan, fecha_fin_plan, ";
        $sql.= "empresarial, id_tipo_evento, descripcion, id_responsable_2, id_tipo_reunion, id_tipo_reunion_code, ";
        if ($this->if_teventos)
            $sql.= "month, year, id_responsable_asigna, id_user_asigna, origen_data_asigna, ";
        $sql.= "{$table}.id_tarea as _id_tarea, {$table}.id_responsable as _id_responsable, responsable_2_reg_date, ";
        $sql.= "{$table}.id_tarea_code as _id_tarea_code, {$table}.id_auditoria as _id_auditoria, ";
        $sql.= "{$table}.id_auditoria_code as _id_auditoria_code, {$table}.user_check as _user_check, id_copyfrom, ";
        $sql.= "{$table}.descripcion as _observacion, id_copyfrom_code, {$table}.toshow as _toshow, funcionario, ";
        $sql.= "ifassure, id_secretary from {$table} where id = $id ";

        $result= $this->do_sql_show_error('_set', $sql);
        $row= $this->clink->fetch_array($result);
        return $row;
    }

    public function add_cump($id_responsable= null, $multi_query= false) {
        $observacion= !empty($this->_observacion) ? $this->_observacion.". \n\n" : $this->observacion;

        $observacion= setNULL_str($observacion);
        $aprobado= setNULL_str($this->aprobado);
        $rechazado= setNULL_str($this->rechazado);

        $id_tarea= setNULL_empty($this->id_tarea);
        $id_tarea_code= setNULL_str($this->id_tarea_code);

        $id_auditoria= setNULL_empty($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);

        $hour= setNULL($this->hour);
        $reg_fecha= setNULL_str($this->reg_fecha);

        $id_usuario= setNULL($this->id_usuario);

        $this->cronos= !empty($this->cronos)? $this->cronos : date('Y-m-d H:i:s');

        if (is_null($this->compute))
            $this->compute= 1;
        $compute= boolean2pg($this->compute);

        if (is_null($this->toshow))
            $this->toshow= 1;
        $toshow= boolean2pg($this->toshow);

        $this->user_check= $this->user_check ? 1 : 0;
        $user_check= boolean2pg($this->user_check);
        $this->hide_synchro= $this->hide_synchro ? 1 : 0;
        $hide_synchro= boolean2pg($this->hide_synchro);

        $outlook= boolean2pg($this->outlook);
        //if ($this->user_check) $this->compute= 0;

        $this->error= null;

        $sql= "insert into treg_evento_{$this->year} (id_evento, id_evento_code, id_usuario, aprobado, rechazado, ";
        $sql.= "cumplimiento, id_responsable, observacion, cronos, situs, id_tarea, id_tarea_code, id_auditoria, ";
        $sql.= "id_auditoria_code, horas, reg_fecha, compute, toshow, user_check, hide_synchro, outlook) values ";
        $sql.= "($this->id_evento, '$this->id_evento_code', $id_usuario, $aprobado, $rechazado, $this->cumplimiento, ";
        $sql.= "{$_SESSION['id_usuario']}, $observacion, '$this->cronos', '$this->location', $id_tarea, ";
        $sql.= "$id_tarea_code, $id_auditoria, $id_auditoria_code, $hour, $reg_fecha, $compute, ";
        $sql.= "$toshow, $user_check, $hide_synchro, $outlook); ";

        $_sql= null;
        if (!empty($this->id_archivo))
            $_sql= $this->add_archivo_cump($_SESSION['id_usuario'], $multi_query);

        if ($multi_query)
            return $sql.$_sql;

        $result= $this->do_sql_show_error('add_cump', $sql);

        if (!$result) {
            if (stristr($this->error, "duplicate") !== false  || stripos($this->error, 'duplicada') !== false) {
                $this->error= null;
                $this->update_cump($id_responsable);
            }
            if (stristr($this->error, "duplicate") !== false  || stripos($this->error, 'duplicada') !== false)
                $this->error= "Este comentario o justificación ya fue utilizado una vez. Por favor, sea más explicito.";
        }
        return $this->error;
    }

    protected function get_if_evaluado($id_usuario= null, $year= null, $month= null) {
        if (empty($year))
            $year= $this->year;
        if (empty($month))
            $month= $this->month;
        if (empty($id_usuario))
            $id_usuario= $this->id_usuario;

        $sql= "select * from tplanes where evaluado is not null and id_usuario = $id_usuario ";
        $sql.= "and month = $month and year = $year LIMIT 1";
        $result= $this->do_sql_show_error('get_if_evaluado', $sql);
        return $this->cant;
    }

    public function getEvento_reg_match($id, $id_responsable, $id_responsable_2= null, $responsable_date= null) {
        $date= null;

        if ($this->toshow == _EVENTO_MENSUAL || $this->toshow == _EVENTO_INDIVIDUAL) {
            $time= new TTime();
            $month= !empty($this->month) ? $this->month : 12;
            $day= $time->longmonth($month, $this->year);
            $date= $this->year."-".str_pad($month,2,'0',STR_PAD_LEFT)."-".str_pad($day,2,'0',STR_PAD_LEFT)." 23:59:59";
        }

        $treg_evento= $this->if_treg_evento ? "_treg_evento" : "treg_evento_{$this->year}";

        $sql= "select * from $treg_evento ";
        $sql.= !$this->if_auditoria ? "where id_evento = $id " : "where id_auditoria = $id ";
        if (empty($this->id_usuario)) {
            $sql.= "and (id_usuario = $id_responsable ";

            if (!empty($id_responsable_2) && !empty($responsable_date)) {
                if (!empty($this->month) && (int)strtotime($date) <= (int)strtotime($responsable_date))
                    $sql.= "or id_usuario = $id_responsable_2 ";
                if (empty($this->month))
                    $sql.= "or (date(cronos) <= date('$responsable_date') and id_usuario = $id_responsable_2) ";
            }
            $sql.= ") ";
        } else {
            $sql.= "and id_usuario = $this->id_usuario ";
        }
        $sql.= "order by id desc limit 1;";

        $result= $this->do_sql_show_error('getEvento_reg_match', $sql);
        $row= $this->clink->fetch_array($result);
        return $row;
    }

    public function get_last_reg($id_evento= null, $id_usuario= null, $year= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $id_usuario= !empty($id_usuario) ? $id_usuario : $this->id_usuario;
        $year= !empty($year) ? $year : $this->year;
        $this->inicio= !empty($year) ? $year : $this->inicio;
        $this->fin= !empty($year) ? $year : $this->fin;
        if (empty($this->inicio))
            $this->inicio= !empty($_SESSION['current_year']) ? $_SESSION['current_year'] : date('Y');
        if (empty($this->fin))
            $this->fin= !empty($_SESSION['current_year']) ? $_SESSION['current_year'] : date('Y');

        $sql= null;
        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            $sql.= $year > $this->inicio ? " union " : "";
            $sql.= "select treg_evento_$year.*, cronos as _cronos, user_check as _user_check, compute as _compute, toshow as _toshow, ";
            $sql.= "outlook from treg_evento_$year where id_usuario = $id_usuario ";
            if (!empty($id_evento))
                $sql.= "and id_evento = $id_evento ";
            if (!empty($this->id_tarea))
                $sql.= "and id_tarea = $this->id_tarea ";
        }
        $sql.= "order by _cronos desc limit 1";
        $result= $this->do_sql_show_error('get_last_reg', $sql);

        $rows= $this->clink->fetch_array($result);
        $nrows= $this->clink->num_rows($result);
        $rows= $nrows > 0 ? $rows : null;
        return $rows;
    }

    /*
    public function getEvento_reg_anual($id, $id_evento, $id_auditoria, $id_tarea, $cicle= false) {
        $id_string= null;
        $treg_evento= ($this->if_treg_evento) ? "_treg_evento" : "treg_evento_{$this->year}";
        $teventos= ($this->if_teventos) ? "_teventos" : "teventos";

        $sql= "select distinct $treg_evento.*, fecha_inicio_plan from $treg_evento, $teventos where ";
        if ($id)
            $sql.= "$teventos.id = $id ";
        elseif ($id_evento)
            $sql.= "$teventos.id_evento = $id_evento ";
        elseif ($id_auditoria)
            $sql.= "$teventos.id_auditoria = $id_auditoria ";
        elseif ($id_tarea)
            $sql.= "$teventos.id_tarea = $id_tarea ";
        $sql.= "and ($treg_evento.id_usuario = $teventos.id_responsable ";
        $sql.= "or ($treg_evento.cronos < $teventos.responsable_2_reg_date and $teventos.id_responsable_2 = $treg_evento.id_usuario)) ";
        $sql.= "and $treg_evento.id_evento = $teventos.id ";
        if ($this->if_auditoria)
            $sql.= "and $treg_evento.id_auditoria = $teventos.id_auditoria ";
        if (!$cicle)
            $sql.= "and $teventos.fecha_inicio_plan <= '$this->cronos' order by $teventos.fecha_inicio_plan desc, ";
        else
            $sql.= "order by $teventos.fecha_inicio_plan asc, ";
        $sql.= "$treg_evento.cronos desc LIMIT 1";

        $result= $this->do_sql_show_error('getEvento_reg_anual', $sql);

        if ($this->cant > 0)
            return $this->clink->fetch_array($result);
        else {
            if (!$cicle)
                return $this->getEvento_reg_anual($id, $id_evento, $id_auditoria, $id_tarea, true);
            else
                return null;
        }
    }
    */

    public function getEvento_reg_anual($id, $id_evento, $id_auditoria, $id_tarea, $cicle= false) {
        $tproceso_eventos= ($this->if_tproceso_eventos) ? "_tproceso_eventos" : "tproceso_eventos_{$this->year}";
        $teventos= ($this->if_teventos) ? "_teventos" : "teventos";

        $sql= "select distinct $tproceso_eventos.*, fecha_inicio_plan from $tproceso_eventos, $teventos where ";
        if ($id)
            $sql.= "$teventos.id = $id ";
        elseif ($id_evento)
            $sql.= "$teventos.id_evento = $id_evento ";
        elseif ($id_auditoria)
            $sql.= "$teventos.id_auditoria = $id_auditoria ";
        elseif ($id_tarea)
            $sql.= "$teventos.id_tarea = $id_tarea ";
        $sql.= "and ($tproceso_eventos.id_proceso = $this->id_proceso ";
        $sql.= "and $tproceso_eventos.id_evento = $teventos.id) ";
        if ($this->if_auditoria)
            $sql.= "and $tproceso_eventos.id_auditoria = $teventos.id_auditoria ";
        if (!$cicle)
            $sql.= "and $teventos.fecha_inicio_plan <= '$this->cronos' order by $teventos.fecha_inicio_plan desc, ";
        else
            $sql.= "order by $teventos.fecha_inicio_plan asc, ";
        $sql.= "$tproceso_eventos.cronos desc LIMIT 1";

        $result= $this->do_sql_show_error('getEvento_reg_anual', $sql);

        if ($this->cant > 0)
            return $this->clink->fetch_array($result);
        else {
            if (!$cicle)
                return $this->getEvento_reg_anual($id, $id_evento, $id_auditoria, $id_tarea, true);
            else
                return null;
        }
    }

    public function setEvento_reg($row) {
        $this->empresarial= $row['empresarial'];
        $this->id_code= $row['id_evento_code'];

        $this->aprobado= $row['aprobado'];
        $this->rechazado= $row['rechazado'];

        $this->id_tarea= $row['id_tarea'];
        $this->id_tarea_code= $row['id_tarea_code'];

        $this->id_auditoria= $row['id_auditoria'];
        $this->id_auditoria_code= $row['id_auditoria_code'];

        $this->hour= $row['horas'];
        $this->reg_fecha= $row['reg_fecha'];

        $this->outlook= boolean($row['outlook']);
        $this->toshow= boolean($row['toshow']);
        $this->compute= boolean($row['compute']);
        $this->user_check= boolean($row['_user_check']);
        $this->hide_synchro= boolean($row['hide_synchro']);
        $this->user_check_plan= boolean($row['user_check']);
        $this->cumplimiento= $row['cumplimiento'];

        $this->_id_aux= $row['_id'];
    }

    public function getTarea_reg($id_tarea= null, $array= null, $flag= false) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $flag= !is_null($flag) ? $flag : false;

        $obj_task= new Ttarea($this->clink);
        $obj_task->Set($id_tarea);
        $fecha_inicio= $obj_task->GetFechaInicioPlan();
        $fecha_fin= $obj_task->GetFechaFinPlan();

        $inicio= date('Y', strtotime($fecha_inicio));
        $fin= date('Y', strtotime($fecha_fin));

        $sql= null;
        for ($year= $inicio; $year <= $fin; $year++) {
            $sql.= $year > $inicio ? "union " : "";
            $sql.= "select *, compute as _compute, id_responsable as id_user_reg, horas, id as _id, toshow as _toshow, ";
            $sql.= "user_check as user_check from treg_evento_$year where id_tarea = $id_tarea ";
            if (!empty($this->id_usuario))
                $sql.= "and id_usuario = $this->id_usuario ";
            else {
                $sql.= "and (id_usuario = {$array['id_responsable']} ";
                if (!empty($array['id_responsable_2']))
                    $sql.= "or (id_usuario = {$array['id_responsable_2']} and ".date2pg("cronos")." <= '{$array['responsable_2_reg_date']}') ";
                $sql.= ") ";
            }
        }
        $sql.= "order by cronos desc limit 1";
        $result= $this->do_sql_show_error('getTarea_reg', $sql);
        $cant= $this->clink->num_rows($result);

        if ($flag) return $result;
        return $cant > 0 ? $this->clink->fetch_array($result) : NULL;
    }

    public function _update_aprobado($id) {
        $sql= "select * from treg_evento_{$this->year} where id_evento = $id and aprobado is not null order by cronos desc limit 1";
        $result= $this->do_sql_show_error('_update_aprobado', $sql);
        $cant= (int)$this->clink->num_rows($result);

        if (empty($cant))
            return null;
        $row= $this->clink->fetch_array($result);

        $sql= "update treg_evento_{$this->year} set aprobado= '{$row['aprobado']}' where id_evento = $id and cronos > '{$row['cronos']}' ";
        $result= $this->do_sql_show_error('_update_aprobado', $sql);
    }

    public function getEvento_reg($id= null, $array= null, $flag= false, $recursive= false) {
        $id= !empty($id) ? $id : $this->id_evento;
        $flag= !is_null($flag) ? $flag : false;
        $this->_id_aux= null;
        $recursive= !is_null($recursive) ? $recursive : false;
        $treg_evento= $this->if_treg_evento ? "_treg_evento" : "treg_evento_{$this->year}";

        if (is_array($array) && !is_null($array)) {
            $sql= "select *, compute as _compute, id_responsable as id_user_reg, horas, id as _id, toshow as _toshow, ";
            $sql.= "user_check as _user_check from $treg_evento where id_evento is not null ";
            if (!empty($id))
                $sql.= "and id_evento = $id ";
            if (!empty($this->id_tarea))
                $sql.= "and id_tarea= $this->id_tarea ";
            if (!empty($this->id_auditoria))
                $sql.= "and id_auditoria= $this->id_auditoria ";
            if (!empty($this->id_usuario))
                $sql.= "and id_usuario = $this->id_usuario ";
            else {
                $sql.= "and (id_usuario = {$array['id_responsable']} ";
                if (!empty($array['id_responsable_2']))
                    $sql.= "or (id_usuario = {$array['id_responsable_2']} and ".date2pg("cronos")." <= '{$array['responsable_2_reg_date']}') ";
                $sql.= ") ";
            }
            if (!empty($this->cumplimiento))
                $sql.= "and cumplimiento = $this->cumplimiento ";
            if (!empty($this->reg_fecha))
                $sql.= "and ".date2pg("cronos")." <= '$this->reg_fecha' ";
            $sql.= "order by cronos desc limit 1";

        } else {
            $teventos= ($this->if_teventos) ? "_teventos" : "teventos";
            $sql= "select $treg_evento.*, empresarial, $teventos.id_responsable as _id_responsable, $treg_evento.compute as _compute, ";
            $sql.= "$treg_evento.id_responsable as id_user_reg, horas, $treg_evento.id as _id, $treg_evento.toshow as _toshow, ";
            $sql.= "$treg_evento.user_check as _user_check, $teventos.user_check as user_check ";
            $sql.= "from $treg_evento, $teventos where ";
            $sql.= "$treg_evento.id_evento = $id and $teventos.id = $treg_evento.id_evento ";

            if (!empty($this->id_usuario))
                $sql.= "and $treg_evento.id_usuario = $this->id_usuario ";
            else {
                $sql.= "and ($treg_evento.id_usuario = $teventos.id_responsable or ";
                $sql.= "($teventos.id_responsable_2 is not null and ".date2pg("$treg_evento.cronos")." <= ".date2pg("$teventos.responsable_2_reg_date")." ";
                $sql.= "and $treg_evento.id_usuario = $teventos.id_responsable_2)) ";
            }
            if (!empty($this->cumplimiento))
                $sql.= "and $treg_evento.cumplimiento = $this->cumplimiento ";
            if (!empty($this->reg_fecha))
                $sql.= "and ".date2pg("$treg_evento.cronos")." <= '$this->reg_fecha' ";
            $sql.= "order by $treg_evento.cronos desc limit 1";
        }

        $result= $this->do_sql_show_error('getEvento_reg', $sql);
        $cant= $this->clink->num_rows($result);
        $row= $cant > 0 ? $this->clink->fetch_array($result) : NULL;

        if (!is_null($row) && !$this->if_treg_evento && !$recursive) {
            if (is_null($row['aprobado']) && !empty($id)) {
                $this->_update_aprobado($id);
                return $this->getEvento_reg($id, $array, $flag, true);
        }   }

        return $flag ? $result : $row;
    }

    public function add_cump_users($cumplimiento, &$array_usuario= null, $array_only_users= null) {
        $error= null;
        $cant= $this->_get_users(null, $array_usuarios, $array_only_users= null);
        if (empty($cant))
            return null;

        foreach ($array_usuarios as $array) {
            if (is_array($array_only_users) && !array_key_exists($array['id_usuario'], $array_only_users))
                continue;

            $this->id_usuario= $array['id_usuario'];
            if (($cumplimiento == _CANCELADO || $cumplimiento == _DELEGADO) && $this->id_usuario == $this->id_responsable_ref)
                continue;
            // $ifeval= $this->get_if_evaluado();

            $this->cumplimiento= null;
            $row= $this->getEvento_reg($this->id_evento);
            $this->setEvento_reg($row);

            $this->cumplimiento= $cumplimiento;

            if ($cumplimiento != _CANCELADO && $cumplimiento != _DELEGADO) {
                if ($this->cumplimiento == _COMPLETADO || $this->cumplimiento == _INCUMPLIDO || $this->cumplimiento == _EN_CURSO) {
                    $this->SetRechazado(null);
                    $this->compute= 1;
                }
            } else {
                if ($this->cumplimiento != _COMPLETADO)
                    $this->SetRechazado($this->cronos);
            }

            $error= $this->add_cump();
        }

        return $error;
    }

    public function update_cump($id_responsable= null, $multi_query= false, $id= null) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $observacion= setNULL_str($this->observacion);
        $aprobado= setNULL_str($this->aprobado);
        $rechazado= setNULL_str($this->rechazado);

        $id_responsable= !empty($id_responsable) ? $id_responsable : $this->id_responsable;
        if (empty($id_responsable))
            $id_responsable= $_SESSION['id_usuario'];

        $hour= setNULL($this->hour);
        $reg_fecha= setNULL_str($this->reg_fecha);

        $this->cronos= !empty($this->cronos) ? $this->cronos : date('Y-m-d H:i:s');

        if (is_null($this->compute))
            $this->compute= 1;
        if (is_null($this->toshow))
            $this->toshow= 1;

        if (empty($this->toshow))
            $this->toshow= 0;
        $toshow= boolean2pg($this->toshow);

        $user_check= boolean2pg($this->user_check);
        $this->compute= $this->user_check ? 0 : $this->compute;
        $compute= boolean2pg($this->compute);
        $outlook= boolean2pg($this->outlook);

        $sql= "update treg_evento_{$this->year} set cumplimiento= $this->cumplimiento, observacion= $observacion, ";
        $sql.= "id_responsable= $id_responsable, aprobado= $aprobado, rechazado= $rechazado, cronos= '$this->cronos', ";
        $sql.= "situs= '$this->location', compute= $compute, toshow= $toshow, user_check= $user_check, outlook=$outlook ";

        if (!is_null($this->hour))
            $sql.= ", horas= $hour ";
        $sql.= "where id_evento = $this->id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_usuario))
            $sql.= "and id_usuario = $this->id_usuario ";
        if (!is_null($this->hour))
            $sql.= "and ".date2pg("reg_fecha")." = ".str_to_date2pg("$reg_fecha")." ";
        if (!empty($id))
            $sql.= "and id = $id ";
        $sql.= "; ";

        if ($multi_query)
            return $sql;

        $this->do_sql_show_error('update_cump', $sql);
        return $this->error;
    }

    private function set_time() {
        $this->cronos= !empty($this->cronos)? $this->cronos : date('Y-m-d H:i:s');

        if (empty($this->fecha_inicio_plan)) {
            $obj= new Tevento($this->clink);
            $obj->Set($this->id_evento);
            $this->fecha_inicio_plan= $obj->GetFechaInicioPlan();
        }

        $time= new TTime;
        $time->splitTime($this->fecha_inicio_plan);
        $this->_year= (int)$time->GetYear();
        $this->_month= (int)$time->GetMonth();
        $this->_day= (int)$time->GetDay();

        $this->get_plan();

        $this->past= false;
        $this->past_month= false;
        $this->present= false;
        $this->future= false;

        if ($this->_year < $this->c_year || ($this->_year == $this->c_year && $this->_month < $this->c_month))
            $this->past_month= true;
        if (($this->_year == $this->c_year && $this->_month == $this->c_month) && $this->_day < $this->c_day)
            $this->past= true;
        if ($this->_year == $this->c_year && $this->_month == $this->c_month)
            $this->present= true;
        if ((!$this->past_month && !$this->past) && !$this->present)
            $this->future= true;
    }

    private function set_add() {
        $this->set_time();
        $observacion= !empty($this->_observacion) ? $this->_observacion.". \n\n" : null;

        if ($this->past_month) {
            $this->compute= 0;
            $this->cumplimiento= _SUSPENDIDO;
            $this->toshow= 1;
            $this->rechazado= $this->today;
            $this->observacion= $observacion."Rechazado por el sistema en fecha ".odbc2date($this->today);
        }
        if ($this->present) {
            $this->compute= 1;
            $this->cumplimiento= ($this->cumplimiento == _REPROGRAMADO) ? _REPROGRAMADO :_NO_INICIADO;
            $this->toshow= 1;
            $this->rechazado= null;
            $this->observacion= $observacion;
        }
        if ($this->future) {
            $this->compute= 1;
            $this->cumplimiento= ($this->cumplimiento == _REPROGRAMADO) ? _REPROGRAMADO :_NO_INICIADO;
            $this->toshow= 1;
            $this->rechazado= null;
            $this->observacion= $observacion;
        }
    }

    private function set_delete() {
        $this->go_delete= _DELETE_NO;
        $this->set_time();

        if ($this->past || $this->past_month) {
            if (/*($this->_evaluado || $this->_aprobado) || */$this->_aprobado_event) {
                $this->compute= 1;
                $this->cumplimiento= !empty($this->cumplimiento) ? $this->cumplimiento : _SUSPENDIDO;
                $this->toshow= 0;
            }
            else {
                $this->go_delete= _DELETE_YES;
            }
        }
        if ($this->present) {
            if (!empty($this->_aprobado_event)) {
                $this->compute= 1;
                $this->cumplimiento= _SUSPENDIDO;
                $this->toshow= 0;
            }
            else {
                $this->go_delete= _DELETE_YES;
            }
        }
        if ($this->future) {
            if ($this->_aprobado_event) {
                $this->compute= 1;
                $this->cumplimiento= _SUSPENDIDO;
                $this->toshow= 0;
            } else {
                $this->go_delete= _DELETE_YES;
            }
        }
    }

    private function _update_reg_delete($go_delete, $id_responsable, $fix_user, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        if ($go_delete != _DELETE_PHY) {
            if ($go_delete != _DELETE_NO ) {
                $this->set_delete();

            } else {
                $this->compute= 1;
                $this->cumplimiento= ($this->cumplimiento == _REPROGRAMADO) ? _POSPUESTO : _SUSPENDIDO;
                $this->toshow= $fix_user ? 1 : 0;
                $this->rechazado= $this->cronos;
                $this->observacion= "TAREA REPROGRAMADA EN FECHA $this->cronos OBSERVACIÓN:".$this->observacion;
            }

            if (!$this->go_delete || !$go_delete) {
                $this->cumplimiento= !empty($this->cumplimiento_aux) ? $this->cumplimiento : _SUSPENDIDO;
                $sql.= $this->add_cump($id_responsable, $multi_query);
            }

            if (($this->go_delete && $go_delete) && !$fix_user) {
                $sql.= "delete from treg_evento_{$this->year} where 1 ";
                if (!empty($this->id_evento))
                    $sql.= "and id_evento = $this->id_evento ";
                if (!empty($this->id_auditoria))
                    $sql.= "and id_auditoria = $this->id_auditoria ";
                if (!empty($this->id_tarea))
                    $sql.= "and id_tarea = $this->id_tarea ";
                if (!empty($this->id_usuario))
                    $sql.= "and id_usuario = $this->id_usuario ";
                if (!empty($this->_id_aux) && $this->cumplimiento_aux == _COMPLETADO)
                    $sql.= "and id <> $this->_id_aux ";
                $sql.= "; ";

                if (!$multi_query) {
                    $result= $this->do_sql_show_error('_update_reg_delete', $sql);
                    if ($result && $this->cant > 0) {
                        $this->add_to_tdelete("treg_evento");
                    }                    
                } else {
                    $sql.= $this->add_to_tdelete("treg_evento", $multi_query);
                }
            }
            
            return $multi_query ? $sql : $this->error;
        }

        if ($this->go_delete == _DELETE_PHY && !$fix_user) {
            $sql.= "delete from treg_evento_{$this->year} where 1 ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento = $this->id_evento ";
            if (!empty($this->id_auditoria))
                $sql.= "and id_auditoria = $this->id_auditoria ";
            if (!empty($this->id_tarea))
                $sql.= "and id_tarea = $this->id_tarea ";
            if (!empty($this->id_usuario))
                $sql.= "and id_usuario = $this->id_usuario ";
            $sql.= "; ";

            if (!$multi_query) {
                $result= $this->do_sql_show_error('update_reg', $sql);
                if ($result && $this->cant > 0) {
                    $this->add_to_tdelete("treg_evento");
                }
            } else {
                $sql.= $this->add_to_tdelete("treg_evento", $multi_query);
            }

            $this->SetAlert('delete');
        }

        return $multi_query ? $sql : null;
    }

    /**
     * Borra el registro de usuarios asociados al evento
     */
    public function _clean_user_reg($array= null, $go_delete= _DELETE_YES, $id_responsable= null, $array_usuarios= null) {
        if (!is_null($array['go_delete'])) {
            if (is_null($go_delete))
                $go_delete= _DELETE_YES;
        }
        if (empty($this->id_evento) && empty($this->id_auditoria))
            return;

        if (is_null($array_usuarios)) {
            $array_usuarios= array();
            $this->get_array_reg_usuarios();
            foreach ($this->array_reg_usuarios as $user) {
                $array_usuarios[$user['id']]= $user;
        }   }

        $array_ids= array();
        foreach ($array_usuarios as $row) {
            $this->id_usuario= $row['id_usuario'];
            $id_evento= !empty($this->id_evento) ? $this->id_evento : 0;
            $id_auditoria= !empty($this->id_auditoria) ? $this->id_auditoria : 0;

            if (!is_null($array_ids[$this->id_usuario][$id_evento][$id_auditoria]))
                continue;
            $array_ids[$this->id_usuario][$id_evento][$id_auditoria]= true;
            $this->_aprobado_event= $row['aprobado'];
            $this->rechazado= !empty($row['rechazado']) ? $row['rechazado'] : null;
            $this->cumplimiento= $row['cumplimiento'];

            $this->update_reg('delete', $go_delete, $id_responsable);
        }
    }

    public function update_reg($action, $go_delete= _DELETE_YES, $id_responsable= null, $multi_query= false) {
        $go_delete= !is_null($go_delete) ? $go_delete : _DELETE_YES;
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        if ($action == 'add') {
            $this->set_add();
            $sql.= $this->add_cump($id_responsable, $multi_query);
            $this->SetAlert('add');
        }

        $fix_user= false;
        if (!empty($this->id_usuario) && ($this->id_usuario == $id_responsable && $go_delete == _DELETE_NO))
            $fix_user= true;

        if ($this->go_delete_fix || $go_delete == _DELETE_PHY) {
            $this->go_delete= _DELETE_PHY;
            $go_delete= _DELETE_PHY;
            $fix_user= false;
        }

        if ($action == 'delete') {
            $sql.= $this->_update_reg_delete($go_delete, $id_responsable, $fix_user, $multi_query);
            if (!empty($this->error))
                return $this->error;
        }
        /*
        if ($action == 'add'
            || ($fix_user && (($this->go_delete && $go_delete) || ($action == 'delete' && $this->go_delete == _DELETE_PHY)))) {
            $this->compute= 1;
            $this->cumplimiento= _SUSPENDIDO;
            $this->toshow= $this->toshow_plan == _EVENTO_ANUAL && $this->id_usuario == $id_responsable ? 1 : 0;
            $this->rechazado= $this->cronos;
            $this->observacion= "Tarea reprogramada en fecha $this->cronos";

            $result= $this->add_cump($id_responsable);
        }
        */
        return $multi_query ? $sql : $this->error;
    }

    /*
    * Registro del estado de lo0s eventos por procesos
    */
    public function setEvento_reg_proceso(&$rowcmp) {
        $rowcmp['id_proceso']= $this->id_proceso;
        $rowcmp['id_proceso_code']= $this->id_proceso_code;
        
        $rowcmp['id_evento']= $this->id_evento;
        $rowcmp['id_evento_code']= $this->id_evento_code;
        $rowcmp['id_tarea']= $this->id_tarea;
        $rowcmp['id_tarea_code']= $this->id_tarea_code;
        $rowcmp['id_auditoria']= $this->id_auditoria;
        $rowcmp['id_auditoria_code']= $this->id_auditoria_code;
        
        $rowcmp['aprobado']= $this->aprobado;
        $rowcmp['id_responsable_aprb']= $this->id_responsable_aprb;
        $rowcmp['rechazado']= $this->rechazado;
        $rowcmp['id_responsable']= $this->id_responsable;
        
        $rowcmp['toshow']= $this->toshow;
        $rowcmp['empresarial']= $this->empresarial;
        $rowcmp['id_tipo_evento']= $this->id_tipo_evento;
        $rowcmp['id_tipo_evento_code']= $this->id_tipo_evento_code;
        $rowcmp['indice']= $this->indice;
        $rowcmp['indice_plus']= $this->indice_plus;
    }
    
    public function update_reg_proceso($action, $go_delete= _DELETE_YES, $id_proceso= null, $id_proceso_code= null, 
                                                                                    $array= null, $multi_query= false) {
        global $array_procesos_entity;
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);
        
        $go_delete= !is_null($go_delete) ? $go_delete : _DELETE_YES;       
        $this->id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $this->id_proceso_code= !empty($id_proceso_code) ? $id_proceso_code : $this->id_proceso_code;
        
        $obj_prs->SetIdProceso($this->id_proceso);
        $obj_prs->set_id_proceso_code($this->id_proceso_code);
        
        $id_entity= !empty($array_procesos_entity[$this->id_proceso]['id_entity']) ? $array_procesos_entity[$this->id_proceso]['id_entity'] : $this->id_proceso;
        
        $rowcmp= $this->get_reg_proceso();
        if (empty($rowcmp)) {
            $rowcmp= array();
            $this->setEvento_reg_proceso($rowcmp);
        } else {
            if (($rowcmp['empresarial'] != $this->empresarial || empty($rowcmp['indice']))
                || ($rowcmp['id_tipo_evento'] != $this->id_tipo_evento && $id_entity == $_SESSION['id_entity'])) {
                $rowcmp['id_tipo_evento']= $this->id_tipo_evento;
                $rowcmp['id_tipo_evento_code']= $this->id_tipo_evento_code;
                $rowcmp['indice']= $this->indice;
                $rowcmp['indice_plus']= $this->indice_plus;
            }
        }
 
        if ($action == 'add') {
            $this->set_add();
            $this->toshow= !is_null($array['toshow']) ? $array['toshow'] : $rowcmp['toshow'];
            $this->empresarial= !is_null($array['empresarial']) ? $array['empresarial'] : $rowcmp['empresarial'];
            $this->id_responsable= !is_null($array['id_responsable']) ? $array['id_responsable'] : $rowcmp['id_responsable'];

            $rowcmp['toshow']= $this->toshow;
            $rowcmp['empresarial']= $this->empresarial;
            $rowcmp['id_responsable']= $this->id_responsable;
            $rowcmp['cumplimiento']= $this->cumplimiento;
            $rowcmp['rechazado']= $this->rechazado;
            $rowcmp['observacio']= $this->observacion;
            $sql.= $this->add_cump_proceso(null, null, $rowcmp, $multi_query);
        } 
        
        if ($action == 'delete') {
            if ($go_delete != _DELETE_PHY) {
                if ($go_delete != _DELETE_NO ) {
                    $this->set_delete();

                } else {
                    $this->cumplimiento= ($this->cumplimiento == _REPROGRAMADO) ? _POSPUESTO : _SUSPENDIDO;
                    $this->rechazado= $this->cronos;
                    $this->observacion= "TAREA REPROGRAMADA EN FECHA $this->cronos OBSERVACIÓN:".$this->observacion;
                }
                $this->toshow= $rowcmp['toshow'];
                $rowcmp['cumplimiento']= $this->cumplimiento;
                $rowcmp['rechazado']= $this->rechazado;
                $rowcmp['observacio']= $this->observacion;
                $sql.= $this->add_cump_proceso(null, null, $rowcmp, $multi_query);  
                
            } else {
                $obj_prs->SetIdEvento($rowcmp['id_evento']);
                $obj_prs->set_id_evento_code($rowcmp['id_evento_code']);
                $obj_prs->SetIdAuditoria($rowcmp['id_auditoria']);
                $obj_prs->set_id_auditoria_code($rowcmp['id_auditoria_code']);
                $obj_prs->SetIdTarea($rowcmp['id_tarea']);
                $obj_prs->set_id_tarea_code($rowcmp['id_tarea_code']);
                
                $sql.= $obj_prs->setEvento('delete', null, null, null, $multi_query);
            }   
        }

        return $multi_query ? $sql : null;
    }    
    

}
