<?php
/**
* @author Geraudis Mustelier Portuondo
* @copyright 2012
*/

include_once "../config.inc.php";

if (!class_exists('Tbase_evento'))
    include_once "base_evento.class.php";


class Tauditoria extends Tbase_evento {

    private $jefe_equipo;
    protected $organismo;

    protected $obj_event;

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase_evento::__construct($clink);

        $this->className= 'Tauditoria';
        $this->if_auditoria= true;

        $this->obj_event= new Tmirror_evento($this->clink);
    }

    public function SetOrganismo($id) {
        $this->organismo = fullUpper($id);
    }
    public function GetOrganismo() {
        return $this->organismo;
    }
    public function GetJefe_equipo() {
        return $this->jefe_equipo;
    }
    public function SetJefe_equipo($id) {
        $this->jefe_equipo = $id;
    }

    public function Set($id = null) {
        if (!empty($id)) {
            $this->id = $id;
            $this->id_auditoria = $id;
        }

        $sql = "select tauditorias.*, ttipo_auditorias.nombre as _nombre from tauditorias, ttipo_auditorias ";
        $sql.= "where tauditorias.id = $this->id_auditoria and tauditorias.id_tipo_auditoria = ttipo_auditorias.id ";
        $result = $this->do_sql_show_error('Set', $sql);
        $row = $this->clink->fetch_array($result);

        if ($result) {
            $this->id= $this->id_auditoria;
            $this->id_code = $row['id_code'];
            $this->id_auditoria_code = $this->id_code;

            $this->origen = $row['origen'];
            $this->id_tipo_auditoria = $row['tipo'];

            $this->organismo = !is_null($row['organismo']) ? stripslashes($row['organismo']) : null;
            $this->jefe_equipo = !empty($row['jefe_auditor']) ? stripslashes($row['jefe_auditor']) : null;
            $this->objetivo = stripslashes($row['objetivos']);
            $this->lugar = stripslashes($row['lugar']);

            $this->nombre= stripslashes($row['_nombre']);

            $this->numero= $row['numero'];
            $this->numero_plus= $row['numero_plus'];

            $this->id_responsable = $row['id_responsable'];
            $this->id_responsable_2 = $row['id_responsable_2'];
            $this->responsable_2_reg_date = $row['id_responsable_2_reg_date'];

            $this->id_user_asigna= $row['id_usuario'];
            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

            $this->toshow_plan = $row['toshow'];
            $this->empresarial = $row['empresarial'];

            $this->id_tipo_evento = $row['id_tipo_evento'];
            $this->id_tipo_evento_code= $row['id_tipo_evento_code'];
            $this->id_tipo_auditoria = $row['id_tipo_auditoria'];
            $this->id_tipo_auditoria_code= $row['id_tipo_auditoria_code'];

            $this->fecha_inicio_plan = $row['fecha_inicio_plan'];
            $this->fecha_fin_plan = $row['fecha_fin_plan'];

            $this->fecha_inicio_real = $row['fecha_inicio_real'];
            $this->fecha_fin_real = $row['fecha_fin_real'];

            $this->cant_days = $row['cant_days'];
            $this->periodic = boolean($row['periodic']);

            $this->periodicidad = $row['periodicidad'];
            $this->carga = $row['carga'];
            $this->dayweek = $row['dayweek'];
            $this->sunday = boolean($row['sunday']);
            $this->saturday = boolean($row['saturday']);
            $this->freeday = boolean($row['freeday']);

            $this->sendmail = boolean($row['sendmail']);
            $this->toworkplan = boolean($row['toworkplan']);

            $this->id_auditoria_ref = $row['id_auditoria'];
            $this->id_auditoria_ref_code = $row['id_auditoria_code'];

            $this->copyto = $row['copyto'];
            $this->id_copyfrom = $row['id_copyfrom'];
            $this->id_copyfrom_code = $row['id_copyfrom_code'];

            $this->origen_data = $row['origen_data'];
            $this->kronos = $row['cronos'];

            $this->indice= $row['indice'];
            $this->indice_plus= $row['indice_plus'];
            $this->tidx= boolean($row['tidx']);
        }

        return $this->error;
    }

    public function add($id_auditoria= null, $id_auditoria_code= null, &$error_duplicate= null) {
        new Ttabla_anno($this->clink, date('Y', strtotime($this->fecha_inicio_plan)));
        $this->set_tidx($id_auditoria, "Tauditoria");

        $organismo= setNULL_str($this->organismo);
        $jefe_equipo= setNULL_str($this->jefe_equipo);
        $objetivo= setNULL_str($this->objetivo);
        $lugar= setNULL_str($this->lugar);

        $this->periodicidad= setNULL($this->periodicidad);
        $this->carga= setNULL($this->carga);
        $dayweek= setNULL_str($this->dayweek);
        $periodic= boolean2pg($this->periodic);

        $sendmail= boolean2pg($this->sendmail);
        $toworkplan= boolean2pg($this->toworkplan);

        $saturday= boolean2pg($this->saturday);
        $sunday= boolean2pg($this->sunday);
        $freeday= boolean2pg($this->freeday);

        $id_tipo_evento= setNULL_empty($this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);

        $id_tipo_auditoria= setNULL_empty($this->id_tipo_auditoria);
        $id_tipo_auditoria_code= setNULL_str($this->id_tipo_auditoria_code);

        $toshow= setZero($this->toshow_plan);
        $this->empresarial= setZero($this->empresarial);

        $id_auditoria= setNULL($id_auditoria);
        $id_auditoria_code= setNULL_str($id_auditoria_code);

        $cant_days= setNULL($this->cant_days);

        $numero= setNULL($this->numero);
        $numero_plus= setNULL_str($this->numero_plus);

        $indice= setNULL($this->indice);
        $indice_plus= setNULL($this->indice_plus);
        $tidx= setNULL($this->tidx);

        $sql= "insert into tauditorias (lugar, objetivos, organismo, origen, id_responsable, id_usuario, id_proceso, ";
        $sql.= "id_proceso_code, jefe_auditor, fecha_inicio_plan, fecha_fin_plan, empresarial, id_tipo_evento, id_tipo_evento_code, ";
        $sql.= "toshow, id_auditoria, id_auditoria_code, periodic, periodicidad, carga, dayweek, saturday, sunday, freeday, ";
        $sql.= "sendmail, toworkplan, cant_days, cronos, situs, numero, numero_plus, id_tipo_auditoria, id_tipo_auditoria_code, ";
        $sql.= "indice, indice_plus, tidx) values ($lugar, $objetivo, $organismo, $this->origen, $this->id_responsable, ";
        $sql.= "$this->id_usuario, {$_SESSION['id_entity']}, '{$_SESSION['id_entity_code']}', $jefe_equipo, ";
        $sql.= "'$this->fecha_inicio_plan', '$this->fecha_fin_plan', $this->empresarial, $id_tipo_evento, $id_tipo_evento_code, ";
        $sql.= "$toshow, $id_auditoria, $id_auditoria_code, $periodic, $this->periodicidad, $this->carga, $dayweek, $saturday, ";
        $sql.= "$sunday, $freeday, $sendmail, $toworkplan, $cant_days, '$this->cronos', '$this->location', ";
        $sql.= "$numero, $numero_plus, $id_tipo_auditoria, $id_tipo_auditoria_code, $indice, $indice_plus, $tidx) ";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id= $this->clink->inserted_id("tauditorias");
            $this->id_auditoria= $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tauditorias','id','id_code');
            $this->id_code= $this->obj_code->get_id_code();
            $this->id_auditoria_code= $this->id_code;

            $this->id_evento= null;
            $this->id_evento_code= null;

        } else {
            $error= $this->clink->error();
            if (stripos($error,'duplicate') !== false || stripos($error,'duplicada') !== false)
                $error_duplicate= true;
            return $this->error;
        }

        return null;
    }

    public function add_evento() {
        $this->obj_event->set_evento_object($this);
        $this->obj_event->SetLink($this->clink);

        $empresarial= !empty($this->empresarial) ? $this->empresarial : _FUNCIONAMIENTO_INTERNO;
        $this->numero= !empty($this->numero) ? $this->numero : $this->obj_event->find_numero($this->year, $empresarial);
        $this->obj_event->SetNumero($this->numero);
        $this->obj_event->SetNumero_plus($this->numero_plus);

        $this->obj_event->SetIdAuditoria($this->id_auditoria);
        $this->obj_event->set_id_auditoria_code($this->id_auditoria_code);

        $this->obj_event->SetIdTarea($this->id_tarea);
        $this->obj_event->set_id_tarea_code($this->id_tarea_code);

        $this->obj_event->add($this->id_evento, $this->id_evento_code);

        if (!is_null($this->obj_event->error)) {
            if (stripos($this->obj_event->error,'duplicate') === false && stripos($this->obj_event->error,'duplicada') === false)
                return $this->obj_event->error;
            else {
                $this->obj_event->className= "Tevento";
                $row= $this->obj_event->find_evento($this->fecha_inicio_plan, null, $this->id_auditoria);
                $this->id_evento= $row['id'];
                $this->id_evento_code= $row['id_code'];
            }
        } else {
            $this->id_evento= $this->obj_event->GetId();
            $this->id_evento_code= $this->obj_event->get_id_code();
        }
    }

    public function update_event($id) {
        $this->obj_event->set_evento_object($this);
        $this->obj_event->SetLink($this->clink);
        $this->obj_event->SetIdEvento($id);

        $this->obj_event->periodicidad= null;
        $this->obj_event->carga= null;
        $this->obj_event->periodic= null;

        if (empty($this->numero)) {
            $array= $this->obj_event->find_numero_by_id($id);
            $this->numero= $array[0];
            $this->numero_plus= $array[1];
        }
        $this->obj_event->SetNumero($this->numero);
        $this->obj_event->SetNumero_plus($this->numero_plus);

        if (empty($this->numero)) {
            $empresarial= !empty($this->empresarial) ? $this->empresarial : _FUNCIONAMIENTO_INTERNO;
            $this->numero= !empty($this->numero) ? $this->numero : $this->obj_event->find_numero($this->year, $empresarial);
        }
        $this->obj_event->toshow= $this->toshow;
        $this->obj_event->SetIfEmpresarial($this->empresarial);
        $this->obj_event->SetNumero($this->numero);

        $this->obj_event->update();
    }

    public function update($id_auditoria= null) {
        new Ttabla_anno($this->clink, date('Y', strtotime($this->fecha_inicio_plan)));
        $this->set_tidx($this->id_auditoria_ref);

        $id_auditoria= !empty($id_auditoria) ? $id_auditoria : $this->id_auditoria;

        $organismo= setNULL_str($this->organismo);
        $jefe_equipo= setNULL_str($this->jefe_equipo);
        $objetivo= setNULL_str($this->objetivo);
        $lugar= setNULL_str($this->lugar);

        $id_responsable_2= setNULL($this->id_responsable_2);
        $responsable_2_reg_date= setNULL_str($this->responsable_2_reg_date);

        $this->periodicidad= setNULL($this->periodicidad);
        $this->carga= setNULL($this->carga);
        $dayweek= setNULL_str($this->dayweek);

        $saturday= boolean2pg($this->saturday);
        $sunday= boolean2pg($this->sunday);
        $freeday= boolean2pg($this->freeday);

        $sendmail= boolean2pg($this->sendmail);
        $toworkplan= boolean2pg($this->toworkplan);

        $this->empresarial= setZero($this->empresarial);
        $toshow= setZero($this->toshow_plan);

        $id_tipo_evento= setNULL_empty($this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);

        $id_tipo_auditoria= setNULL_empty($this->id_tipo_auditoria);
        $id_tipo_auditoria_code= setNULL_str($this->id_tipo_auditoria_code);

        $fecha_inicio= setNULL_str($this->fecha_inicio_real);
        $fecha_fin= setNULL_str($this->fecha_fin_real);

        $cant_days= setNULL($this->cant_days);
        $periodic= boolean2pg($this->periodic);

        $numero= setNULL($this->numero);
        $numero_plus= setNULL_str($this->numero_plus);

        $indice= setNULL($this->indice);
        $indice_plus= setNULL($this->indice_plus);
        $tidx= setNULL($this->tidx);

        $sql= "update tauditorias set objetivos= $objetivo, fecha_inicio_plan= '$this->fecha_inicio_plan', ";
        $sql.= "fecha_fin_plan= '$this->fecha_fin_plan', fecha_inicio_real= $fecha_inicio, fecha_fin_real= $fecha_fin, ";
        $sql.= "lugar= $lugar, organismo= $organismo, jefe_auditor= $jefe_equipo, cant_days= $cant_days, ";
        $sql.= "saturday= $saturday, sunday= $sunday, freeday= $freeday, periodicidad= $this->periodicidad, ";
        $sql.= "carga= $this->carga, dayweek= $dayweek, cronos= '$this->cronos', situs= '$this->location', ";
        $sql.= "id_responsable= $this->id_responsable, id_usuario= $this->id_usuario, origen= $this->origen, ";
        $sql.= "empresarial= $this->empresarial, id_tipo_evento= $id_tipo_evento, toshow= $toshow, periodic= $periodic, ";
        $sql.= "sendmail= $sendmail, toworkplan= $toworkplan, numero= $numero, numero_plus= $numero_plus, ";
        $sql.= "id_tipo_evento_code= $id_tipo_evento_code, id_tipo_auditoria= $id_tipo_auditoria, ";
        $sql.= "id_tipo_auditoria_code= $id_tipo_auditoria_code ";
        if (!empty($this->id_responsable_2))
            $sql.= ", id_responsable_2= $id_responsable_2, responsable_2_reg_date= $responsable_2_reg_date ";
        $sql.= "where id = $id_auditoria ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function update_exclusive($id, $fecha) {
        $sql= "select id, id_code from tauditorias where id_auditoria= $id and ".str_to_date2pg("fecha_inicio_plan")." = ".str_to_date2pg("'$fecha'");
        $result= $this->do_sql_show_error('Tauditorias::update_exclusive', $sql);

        if (empty($this->cant))
            return null;

        $id= $this->clink->fetch_result($result, 0, 'id');
        $id_code= $this->clink->fetch_result($result, 0, 'id_code');

        $organismo= setNULL_str($this->organismo);
        $jefe_equipo= setNULL_str($this->jefe_equipo);
        $objetivo= setNULL_str($this->objetivo);
        $lugar= setNULL_str($this->lugar);

        $saturday= boolean2pg($this->saturday);
        $sunday= boolean2pg($this->sunday);
        $freeday= boolean2pg($this->freeday);

        $sendmail= boolean2pg($this->sendmail);
        $toworkplan= boolean2pg($this->toworkplan);

        $this->empresarial= setNULL_empty($this->empresarial);
        $toshow= setZero($this->toshow_plan);

        $id_tipo_evento= setNULL_empty($this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);
        $id_tipo_auditoria= setNULL_empty($this->id_tipo_auditoria);
        $id_tipo_auditoria_code= setNULL_str($this->id_tipo_auditoria_code);

        $fecha_inicio= setNULL_str($this->fecha_inicio_real);
        $fecha_fin= setNULL_str($this->fecha_fin_real);

        $cant_days= setNULL($this->cant_days);

        $sql= "update tauditorias set objetivos= $objetivo, fecha_inicio_plan= '$this->fecha_inicio_plan', ";
        $sql.= "fecha_fin_plan= '$this->fecha_fin_plan', fecha_inicio_real= $fecha_inicio, fecha_fin_real= $fecha_fin, ";
        $sql.= "lugar= $lugar, organismo= $organismo, jefe_auditor= $jefe_equipo, id_responsable= $this->id_responsable,";
        $sql.= "saturday= $saturday, sunday= $sunday, freeday= $freeday, cant_days= $cant_days, ";
        $sql.= "id_usuario= $this->id_usuario, origen= $this->origen, empresarial= $this->empresarial, ";
        $sql.= "id_tipo_evento= $id_tipo_evento, tipo= $this->id_tipo_auditoria, toshow= $toshow, ";
        $sql.= "sendmail= $sendmail, toworkplan= $toworkplan, periodic= 0, id_auditoria= NULL, id_auditoria_code= NULL, ";
        $sql.= "id_tipo_auditoria= $id_tipo_auditoria, id_tipo_auditoria_code= $id_tipo_auditoria_code, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location', id_tipo_evento_code= $id_tipo_evento_code where id = $id ";

        $result= $this->do_sql_show_error('Tauditorias::update_exclusive', $sql);
        return array($id, $id_code);
    }

    public function update_date($fecha_inicio=null, $fecha_fin= null) {
        $fecha_inicio= !empty($fecha_inicio) ? $fecha_inicio : $this->fecha_inicio_plan;
        $fecha_fin= !empty($fecha_fin) ? $fecha_fin : $this->fecha_fin_plan;

        if (empty($fecha_inicio) || empty($fecha_fin))
            return "Las fechas no pueden estar vacias";

        $sql= "update tauditorias set fecha_inicio_plan= '$fecha_inicio', fecha_fin_plan= '$fecha_fin' ";
        $sql.= "where id = $this->id";
        $result= $this->do_sql_show_error('update_date', $sql);
        return $this->error;
    }

    private function purge_tmp_tusuarios() {
        if (!$this->if_tusuarios) return;

        $sql= "select * from _tusuarios where marked is not null";
        $result= $this->do_sql_show_error('purge_tmp_tusuarios', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['id'];
            $sql= "delete from _tusuarios where id = $id";
            $this->do_sql_show_error('purge_tmp_tusuarios', $sql);
        }
    }

    public function delete($id, $id_code, $parent= false) {
        $result= $this->_delete_if_empty_audit(array('id'=>$id, 'id_code'=>$id_code), $parent);
        return $result ? true : false;
    }

    public function set_copyto($id, $year, $id_code) {
        $this->copyto= "$year($id_code)-";
        $sql= "update tauditorias set copyto= '$this->copyto' where id = $id ";
        $result= $this->do_sql_show_error('set_copyto', $sql);
    }

// solo se compian eventos simples o individuales
    public function this_copy($id_proceso=null, $id_proceso_code= null, $tipo= null, $radio_prs= null, $to_year= null, $array_id= null) {
        $plus_year= empty($to_year) ? 1 : ($to_year - $this->year);
        if ($plus_year <= 0)
            $plus_year= 1;
        $to_year= !empty($to_year) ? $to_year : ($this->year + $plus_year);

        $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];
        $id_proceso_code= !empty($id_proceso_code) ? $id_proceso_code : get_code_from_table('tprocesos', $id_proceso);
        $tipo= !empty($tipo) ? $tipo : $_SESSION['entity_tipo'];

        $obj= $this->_this_copy();

        $obj->SetLink($this->clink);
        $obj->obj_code->SetLink($this->clink);
        $obj->SetId(null);
        $obj->SetIdAuditoria(null);
        $obj->set_id_code(null);
        $obj->set_id_auditoria_code(null);
        $obj->SetFechaInicioReal(null);
        $obj->SetFechaFinReal(null);
        $obj->SetIdProceso($id_proceso);
        $obj->set_id_proceso_code($id_proceso_code);
        $obj->SetIdUsuario($_SESSION['id_usuario']);
        $obj->set_cronos($this->cronos);

        $obj->set_id_responsable_2(null);
        $obj->set_responsable_2_reg_date(null);

        $this->id_proceso= $radio_prs == 2 ? null : $id_proceso;
        $this->id_entity= $radio_prs == 2 ? null : $this->id_entity;
        $this->listar_usuarios();
        $this->listar_grupos();

        $this->get_array_reg_usuarios($this->year);
        $this->get_array_reg_procesos($this->year, $id_proceso, $tipo);

        $obj_sched= new Tschedule;

        $obj_sched->SetPeriodicidad($this->periodicidad);
        $obj_sched->SetCarga($this->carga);
        $obj_sched->SetDayWeek($this->dayweek);
        $obj_sched->SetIfPeriodic($this->periodic);
        $obj_sched->SetCantidad_days($this->cant_days);
        $obj_sched->saturday= $this->saturday;
        $obj_sched->sunday= $this->sunday;
        $obj_sched->freeday= $this->freeday;
        if ($this->periodicidad == 3)
            $obj_sched->fixed_day= empty($this->dayweek) ? 0 : 1;

        $this->get_child_events_by_table('tauditorias', $this->id);

        if ($this->periodicidad == 4) {
            foreach ($this->array_eventos as $evento) {
                $fecha= $evento['fecha_inicio_plan'];
                $obj_sched->input_array_dates[]= add_date($fecha, 0, 0, $plus_year);
            }
        }
        return $this->_this_copy_audit($obj, $obj_sched, $plus_year, $to_year, $array_id);
    }

    private function _this_copy_audit($obj, $obj_sched, $plus_year, $to_year, $array_id) {
        $fecha= add_date($this->fecha_inicio_plan, 0, 0, $plus_year);
        $obj->SetFechaInicioPlan($fecha);
        $obj_sched->SetFechaInicioPlan($fecha);
        $obj->SetFechaFinPlan(add_date($this->fecha_fin_plan, 0, 0, $plus_year));
        $obj_sched->SetFechaFinPlan($obj->GetFechaFinPlan());

        $obj_sched->set_dates();
        $obj_sched->create_array_dates();
        if ($this->cant_days && $this->periodic)
            $obj_sched->add_days_to_schudele();

        if (is_null($array_id)) {
            $this->error= $obj->add();
            if (!is_null($this->error))
                return null;

            $id= $obj->GetId();
            $id_code= $obj->get_id_code();
            $this->set_copyto($this->id, $to_year, $id_code);

        } else {
            $id= $array_id['id'];
            $id_code= $array_id['id_code'];

            $obj->SetIdAuditoria($id);
            $obj->set_id_auditoria_code($id_code);

            $this->error= $obj->update($id);
        }

        $this->_copy_reg($to_year, $id, $id_code, null, null);

        $array= array('id'=>$id, 'id_code'=>$id_code);
        $_id= $id;
        $_id_code= $id_code;

        foreach ($obj_sched->array_dates as $date) {
            $error= null;
            $obj->set_null_periodicity();
            $obj->SetFechaInicioPlan($date['inicio']);
            $obj->SetFechaFinPlan($date['fin']);

            if ($date['hit']) {
                $obj->SetCantidad_days($this->cant_days);
                $id_evento= null;
                $id_evento_code= null;

                if (is_null($array_id)) {
                    $error= $obj->add($id, $id_code);
                    $_id= $obj->GetId();
                    $_id_code= $obj->get_id_code();
                }

                if (!is_null($array_id) || (is_null($array_id) && (stripos($error,'duplicate') !== false || stripos($error,'duplicada') !== false))) {
                    $_array_id= $obj->find_evento($date['inicio'], null, $id);

                    if (!is_null($_array_id)) {
                        $_id= $_array_id['id'];
                        $_id_code= $_array_id['id_code'];
                        $obj->SetIdAuditoria($_id);
                        $obj->set_id_auditoria_code($_id_code);
                    } else
                        continue;
                }
            } else {
                $obj->SetCantidad_days(null);
                $obj->className= 'Tevento';
                $_array_id= $obj->find_evento($date['inicio'], null, $_id);
                $obj->className= 'Tauditoria';

                if (is_null($_array_id)) {
                    $obj->add_evento();
                    $id_evento= $obj->GetIdEvento();
                    $id_evento_code= $obj->get_id_evento_code();
                } else {
                    $id_evento= $_array_id['id'];
                    $id_evento_code= $_array_id['id_code'];
                }
            }

            $this->_copy_reg($to_year, $_id, $_id_code, $id_evento, $id_evento_code);
        }

        return $array;
    }

    public function _copy_reg($to_year, $id_auditoria, $id_auditoria_code, $id_evento= null, $id_evento_code= null) {
        $error= null;
        /* copia tusuario_eventos */
        $error= $this->_copy_evento_usuarios($to_year, $id_evento, $id_evento_code, $id_auditoria, $id_auditoria_code);

        /* copia treg_evento */
        if (!empty($_id_evento)) {
            $error.= $this->_copy_reg_usuarios($to_year, $id_evento, $id_evento_code, $id_auditoria, $id_auditoria_code);
        }

        /* copia tproceso_eventos */
        $error.= $this->_copy_proceso($to_year, $id_evento, $id_evento_code, $id_auditoria, $id_auditoria_code);

        return $error;
    }

    public function listar($date_filter = null, $array= null) {
        $fecha_inicio = !is_null($this->fecha_inicio_plan) ? $this->fecha_inicio_plan : $this->year . '-01-01';
        $fecha_fin = !is_null($this->fecha_fin_plan) ? $this->fecha_fin_plan : $this->year . '-12-31';
        $year_init= (int)date('Y', strtotime($fecha_inicio));
        $year_fin= (int)date('Y', strtotime($fecha_fin));

        $sql= null;
        for ($year= $year_init; $year <= $year_fin; $year++) {
            if ($year > $year_init)
                $sql.= " union ";
            $sql.= "select distinct tauditorias.*, tauditorias.id as _id, tauditorias.id_code as _id_code, ";
            $sql.= "tproceso_eventos_{$year}.id_proceso as _id_proceso, tproceso_eventos_{$year}.id_proceso as _id_proceso_code ";
            $sql.= "from tauditorias, tproceso_eventos_{$year} where tauditorias.id = tproceso_eventos_{$year}.id_auditoria ";
            if (!empty($this->id_proceso))
                $sql .= "and tproceso_eventos_{$year}.id_proceso = $this->id_proceso ";
            if (!empty($this->origen))
                $sql .= "and origen= $this->origen ";
            if (empty($this->id_proceso) && !is_null($array)) {
                $list= implode(",", $array);
                $sql.= "and tproceso_eventos_{$year}.id_proceso in ($list) ";
            }
            if (is_null($date_filter)) {
                if (!empty($this->fecha_inicio_plan))
                    $sql.= "and (".date2pg("fecha_inicio_plan")." >= '$fecha_inicio' and ".date2pg("fecha_fin_plan")." <= '$fecha_fin') ";
            } else {
                $sql.= "and (".date2pg("fecha_inicio_plan")." <= '$date_filter' and ".date2pg("fecha_fin_plan")." >= '$date_filter') ";
            }
        }
        $sql.= "order by fecha_inicio_plan asc ";

        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function listyear($origen= null, $tipo= null, $id_proceso= null) {
        if (isset($this->array_auditorias)) unset($this->array_auditorias);
        $this->copy_in_object($this->obj_reg);

        $sql= "select distinct _tauditorias.* from _tauditorias ";
        if ($this->if_tidx)
            $sql.= ", _tidx ";
        $sql.= "where 1 ";
        if (!empty($id_proceso))
            $sql.= "and _tauditorias.id_proceso = $id_proceso ";
        if (!empty($origen))
            $sql.= "and origen = $origen ";
        if (!empty($tipo))
            $sql.= "and tipo = $tipo ";
        if ($this->if_tidx) {
            $sql.= "and ((_tidx.id is not null and (_tauditorias.id = _tidx.id or _tauditorias.id_auditoria = _tidx.id)) ";
            $sql.= "or (_tidx.id_auditoria is not null and (_tauditorias.id_auditoria = _tidx.id_auditoria))) ";
        }
        $sql.= "order by fecha_inicio_plan asc ";

        $result= $this->do_sql_show_error('listyear', $sql);
        if (empty($this->cant))
            return null;

        $array_auditorias= array();
        $fecha_inicio= null;
        $fecha_fin= null;
        $cumplimiento= null;
        $fecha= null;
        $memo= null;
        $rechazado= null;
        $aprobado= null;

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $fecha_inicio= $row['fecha_inicio_plan'];
            $fecha_fin= $row['fecha_fin_plan'];

            reset($array_auditorias);
            if (isset($array_auditorias[$row['id']]))
                continue;
            $array_auditorias[$row['id']]= $row['id'];

// es un intervalo de auditoria
            $cumplimiento= null;
            $fecha= null;
            $memo= null;
            $rechazado= null;
            $aprobado= null;
            $rowcmp= null;

            $rowcmp= $this->obj_reg->getEvento_reg_anual(null, null, $row['id'], null);

            $cumplimiento= !is_null($rowcmp) ? $rowcmp['cumplimiento'] : _NO_INICIADO;
            $fecha= !is_null($rowcmp) ? $rowcmp['fecha_inicio_plan'] : $fecha_inicio;
            $memo= !is_null($rowcmp) ? $rowcmp['observacion'] : "No existe usuario con la tarea asignada y cumplimiento reportado. Detectedado por el Sistema ". date('d/m/Y H:s');
            $rechazado= !is_null($rowcmp) ? $rowcmp['rechazado'] : null;
            $aprobado= !is_null($rowcmp) ? $rowcmp['aprobado'] : null;

            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'fecha_inicio_plan'=>$fecha_inicio, 'fecha_fin_plan'=>$fecha_fin,
                'id_auditoria'=>$row['id_auditoria'], 'id_auditoria_code'=>$row['id_auditoria_code'], 'id_evento'=>$rowcmp['id_evento'],
                'id_evento_code'=>$rowcmp['id_evento_code'], 'periodic'=>boolean($row['periodic']), 'toshow'=>$row['toshow'],
                'lugar'=>$row['lugar'], 'objetivos'=>$row['objetivos'], 'organismo'=>$row['organismo'], 'jefe_auditor'=>$row['jefe_auditor'],
                'id_responsable'=>$row['id_responsable'], 'id_usuario'=>$row['id_user_asigna'], 'memo'=>stripslashes($memo),
                'cumplimiento'=>$cumplimiento, 'rechazado'=>$rechazado, 'aprobado'=>$aprobado, 'fecha'=>$fecha, 'id_proceso'=>$row['id_proceso'],
                'id_tipo_auditoria'=>$row['id_tipo_auditoria'], 'id_tipo_auditoria_code'=>$row['id_tipo_auditoria_code'],
                'id_proceso_asigna'=>$row['id_proceso_asigna'], 'origen'=>$row['origen'], 'jefe_auditor'=>$row['jefe_auditor'], 'hit'=>0, 'flag'=>0);

            $this->array_auditorias[$i]= $array;

            if (!empty($row['id_auditoria'])) {
                $j= 0;
                while ($tmp_array= @current($this->array_auditorias)) {
                    if ($tmp_array['id'] == $row['id_auditoria'])  {
                        $this->array_auditorias[$j]['hit']+= 1;

                        if (is_null($this->array_auditorias[$j]['fecha'])
                            || (strtotime($this->array_auditorias[$j]['fecha']) <= strtotime($fecha)) && strtotime($fecha) <= strtotime($this->cronos)) {

                            $this->array_auditorias[$j]['cumplimiento']= $cumplimiento;
                            $this->array_auditorias[$j]['rechazado']= $rechazado;
                            $this->array_auditorias[$j]['aprobado']= $aprobado;
                            $this->array_auditorias[$j]['fecha']= $fecha;
                            $this->array_auditorias[$j]['memo']= stripslashes($memo);
                        }
                    }

                    next($this->array_auditorias);
                    ++$j;
                }
            }

            ++$i;
            reset($this->array_auditorias);
        }

        $this->cant= $i;
        return $this->cant;
    }

/* tauditoria_notas  */
    public function listar_notas($id_auditoria= NULL, $flag= true) {
        if (empty($id_auditoria)) $id_auditoria= $this->id_auditoria;

        $sql= "select tnotas.*, tnotas.descripcion as nota, tnotas.fecha_inicio_real as _fecha_inicio, tnotas.id as _id, ";
        $sql.= "from tnotas where tnotas.id_auditoria = $id_auditoria ";
        $sql.= "order by fecha_inicio_real asc";
        $result= $this->do_sql_show_error('listar_notas', $sql);

        if ($flag)
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nota'], 'fecha_inicio'=>$row['fecha_inicio_real'],
                    'id_responsable'=>$row['id_responsable'], 'id_proceso'=>$row['id_proceso']);
            $this->array_notas[$row['_id']]= $array;
        }
        return $this->array_notas;
    }

    public function cleanNotas() {
        $sql= "delete from tnotas where id_auditoria= $this->id_auditoria ";
        $result= $this->do_sql_show_error('cleanNotas', $sql);
    }

    public function delete_nota($id_nota) {
        $id_nota= !empty($id_nota) ? $id_nota : $this->id_nota;

        $sql= "delete from tnotas where id_nota = $id_nota and id_auditoria= $this->id_auditoria ";
        $result= $this->do_sql_show_error('delete_nota', $sql);
        return $this->error;
    }

    public function add_nota() {
        $sql= "update tnotas set id_auditoria= $this->id_auditoria, id_auditoria_code= '$this->id_auditoria_code', ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' where id_nota= $this->id_nota ";

        $this->do_sql_show_error('add_nota', $sql);
        return $this->error;
    }

    public function find_auditoria_duplicated() {
        $sql= "select * from tauditorias where id_tipo_auditoria = $this->id_tipo_auditoria ";
        $sql.= "and lower(lugar) = '".strtolower($this->lugar)."' and id_proceso_code = '$this->id_proceso_code' ";
        $sql.= "and fecha_inicio_plan = '$this->fecha_inicio_plan' and fecha_fin_plan = '$this->fecha_fin_plan' ";
        $result= $this->do_sql_show_error('find_evento_duplicated', $sql);
        $row= $this->clink->fetch_array($result);

        return $row['id'];
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";
include_once "code.class.php";

if (!class_exists('Tproceso'))
    include_once "proceso_item.class.php";
if (!class_exists('Tnota'))
    include_once "nota.class.php";
if (!class_exists('Tmirror_evento'))
    include_once "mirror_evento.class.php";
