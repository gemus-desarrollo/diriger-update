<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 22/12/14
 * Time: 12:03
 */

if (!class_exists('Tbase_usuario'))
    include_once "base_usuario.class.php";

class Tbase_planning extends Tbase_usuario {
    protected $_id_aux;

    protected $numero;
    protected $numero_plus;

    protected $lugar;
    protected $control_list;

    protected $empresarial;
    protected $id_tipo_evento;
    protected $id_tipo_evento_code;
    protected $id_tipo_auditoria;

    public $array_reg_usuarios;
    protected $array_reg_procesos;
    protected $array_reg_tareas;

    protected $id_evento_ref,
            $id_evento_ref_code;
    protected $id_auditoria_ref,
            $id_auditoria_ref_code;

    protected $aprobado,
            $rechazado;
    protected $_aprobado,
            $_rechazado,
            $_evaluado;
    protected $_aprobado_event;
    protected $hour;

    protected $_year,
            $_month,
            $_day,
            $c_year,
            $c_month,
            $c_day,
            $today;
    protected $go_delete,
            $go_delete_fix,
            $print_reject;

    protected $toshow_plan;
    public $compute;
    public $_observacion;

    public $limited;
    public $monthstack;
    public $fecha_inicio_plan_page,
            $fecha_fin_plan_page;

    public $indice,
            $indice_plus,
            $tidx;
    protected $id_responsable_2,
            $responsable_2_reg_date,
            $id_responsable_ref;

    public $array_procesos_entity;

    public $create_temporary_treg_evento_table;

    public $array_status_eventos_ids;

    public function set_go_delete($id) {
        $this->go_delete = $id;
    }
    public function get_go_delete() {
        return $this->go_delete;
    }
    public function set_print_reject($id) {
        $this->print_reject = $id;
    }
    public function get_print_reject() {
        return $this->print_reject;
    }
    public function SetToworkplan($id = 1) {
        $this->toworkplan = $id;
    }
    public function GetToworkplan() {
        return $this->toworkplan;
    }
    public function SetNumero($id) {
        $this->numero = $id;
    }
    public function GetNumero() {
        return $this->numero;
    }
    public function SetNumero_plus($id) {
        $this->numero_plus = $id;
    }
    public function GetNumero_plus() {
        return $this->numero_plus;
    }
    public function GetLugar() {
        return $this->lugar;
    }
    public function SetLugar($id, $toupper = false) {
        if (!is_null($id)) {
            $this->lugar = ($toupper) ? fullUpper($id) : $id;
        } else
            $this->lugar = null;
    }
    public function SetIfEmpresarial($id = 0) {
        $this->empresarial = $id;
    }
    public function GetIfEmpresarial() {
        return $this->empresarial;
    }
    public function set_toshow_plan($id = 0) {
        $this->toshow_plan = $id;
    }
    public function get_toshow_plan() {
        return $this->toshow_plan;
    }

    protected $objetivo,
            $evaluacion,
            $auto_evaluacion;
    protected $evaluado,
            $auto_evaluado;
    protected $id_responsable_eval,
            $id_responsable_aprb,
            $id_responsable_auto_eval;

    public function GetAprobado() {
        return $this->aprobado;
    }
    public function GetEvaluado() {
        return $this->evaluado;
    }
    public function GetAutoEvaluado() {
        return $this->auto_evaluado;
    }
    public function GetIdResponsable_aprb() {
        return $this->id_responsable_aprb;
    }
    public function GetIdResponsable_eval() {
        return $this->id_responsable_eval;
    }
    public function GetIdResponsable_auto_eval() {
        return $this->id_responsable_auto_eval;
    }
    Public function GetObjetivo() {
        return $this->objetivo;
    }
    Public function GetEvaluacion() {
        return $this->evaluacion;
    }
    Public function GetAutoEvaluacion() {
        return $this->auto_evaluacion;
    }
    public function SetObjetivo($id) {
        $this->objetivo = $id;
    }
    public function SetEvaluacion($id) {
        $this->evaluacion = $id;
    }
    public function SetAutoEvaluacion($id) {
        $this->auto_evaluacion = $id;
    }
    public function SetEvaluado($id) {
        $this->evaluado = $id;
    }
    public function SetIdResponsable_eval($id) {
        $this->id_responsable_eval = $id;
    }
    public function SetIdResponsable_aprb($id) {
        $this->id_responsable_aprb = $id;
    }
    public function SetIdTipo_evento($id = 0) {
        $this->id_tipo_evento = $id;
    }
    public function set_id_tipo_evento_code($id = null) {
        $this->id_tipo_evento_code = $id;
    }
    public function GetIdTipo_evento() {
        return $this->id_tipo_evento;
    }
    public function get_id_tipo_evento_code() {
        return $this->id_tipo_evento_code;
    }
    public function SetTipo_auditoria($id = 0) {
        $this->id_tipo_auditoria = $id;
    }
    public function GetTipo_auditoria() {
        return $this->id_tipo_auditoria;
    }
    public function set_id_evento_ref_code($id) {
        $this->id_evento_ref_code = $id;
    }
    public function get_id_evento_ref_code() {
        return $this->id_evento_ref_code;
    }
    public function set_id_auditoria_ref_code($id) {
        $this->id_auditoria_ref_code = $id;
    }
    public function get_id_auditoria_ref_code() {
        return $this->id_auditoria_ref_code;
    }
    public function set_id_evento_ref($id) {
        $this->id_evento_ref = $id;
    }
    public function get_id_evento_ref() {
        return $this->id_evento_ref;
    }
    public function set_id_auditoria_ref($id) {
        $this->id_auditoria_ref = $id;
    }
    public function get_id_auditoria_ref() {
        return $this->id_auditoria_ref;
    }
    public function SetAprobado($id) {
        $this->aprobado = $id;
    }
    public function SetRechazado($id) {
        $this->rechazado = $id;
    }
    public function GetRechazado() {
        return $this->rechazado;
    }
    public function SetHours($id) {
        $this->hour = $id;
    }
    public function GetHours() {
        return $this->hour;
    }
    public function setControl_list() {
        $this->control_list = 1;
    }
    public function GetIdResponsable_ref() {
        return $this->id_responsable_ref;
    }
    public function SetIdResponsable_ref($id) {
        $this->id_responsable_ref = $id;
    }
    public function get_id_responsable_2() {
        return $this->id_responsable_2;
    }
    public function set_id_responsable_2($id) {
        $this->id_responsable_2 = $id;
    }
    public function get_responsable_2_reg_date() {
        return $this->responsable_2_reg_date;
    }
    public function set_responsable_2_reg_date($id) {
        $this->responsable_2_reg_date = $id;
    }

    /**
     * @var user_check
     * true en el plan de trabajo individual se oculta la tarea por que el usuario no es un participante
     * pude visualizar la tarea para darle cumplimiento
     */
    protected $user_check;
    public $hide_synchro;
    protected $user_check_plan;

    public function get_user_check() {
        return $this->user_check;
    }
    public function set_user_check($id) {
        $this->user_check = $id;
    }
    public function get_user_check_plan() {
        return $this->user_check_plan;
    }
    public function set_user_check_plan($id) {
        $this->user_check_plan = $id;
    }
    public function SetIfAuditoria($id = 1) {
        $this->if_auditoria = $id ? 1 : 0;
    }
    public function GetIfAuditoria() {
        return $this->if_auditoria;
    }

    protected $outlook;

    public function get_outlook() {
        return $this->outlook;
    }
    public function set_outlook($id = true) {
        $this->outlook = $id;
    }

    protected $reg_fecha;
    protected $id_user_asigna;

    public function SetFecha($id) {
        $this->reg_fecha = $id;
    }
    public function GetFecha() {
        return $this->reg_fecha;
    }
    public function SetId($id) {
        $this->id = $id;
        $this->id_proyecto = $id;
    }
    public function get_id_user_asigna() {
        return $this->id_user_asigna;
    }
    public function set_id_user_asigna($id) {
        $this->id_user_asigna = $id;
    }

    protected $id_proceso_asigna,
            $id_proceso_asigna_code;

    public function get_id_proceso_asigna() {
        return $this->id_proceso_asigna;
    }
    public function set_id_proceso_asigna($id) {
        $this->id_proceso_asigna = $id;
    }

    protected $origen;
    protected $tipo_prs;

    public function SetOrigen($id) {
        $this->origen = $id;
    }
    public function GetOrigen() {
        return $this->origen;
    }
    public function SetTipo_prs($id) {
        $this->tipo_prs = $id;
    }
    public function GetTipo_prs() {
        return $this->tipo_prs;
    }

    protected $id_copyfrom;
    protected $id_copyfrom_code;

    public function set_id_copyfrom($id) {
        $this->id_copyfrom = $id;
    }

    public function get_id_copyfrom() {
        return $this->id_copyfrom;
    }
    public function set_id_copyfrom_code($id) {
        $this->id_copyfrom_code = $id;
    }
    public function get_id_copyfrom_code() {
        return $this->id_copyfrom_code;
    }

    protected $id_secretary;
    protected $ifassure;

    public function SetIdSecretary($id) {
        $this->id_secretary = $id;
    }
    public function GetIdSecretary() {
        return $this->id_secretary;
    }
    public function SetIfAssure($id = true) {
        $this->ifassure = $id;
    }
    public function GetIfAssure() {
        return $this->ifassure;
    }

    public $max_rows_temporary;
    public $max_num_pages;
    protected $init_row_temporary;
    protected $max_row_in_page;
    public $num_rows_in_page;

    public $if_tidx;
    public $tidx_array;
    public $tidx_array_evento;
    public $tidx_array_auditoria;
    public $tidx_array_tarea;

    protected $tipo_plan;

    public function SetTipoPlan($id) {
        $this->tipo_plan = $id;
    }
    public function GetTipoPlan() {
        return $this->tipo_plan;
    }
    public function set_init_row_temporary($id = 0) {
        $this->init_row_temporary = $id;
    }
    public function get_init_row_temporary($id = 0) {
        $this->init_row_temporary = $id;
    }

    protected $date_eval_cutoff;

    public function SetDate_eval_cutoff($id) {
        $this->date_eval_cutoff = $id;
    }
    public function GetDate_eval_cutoff() {
        return $this->date_eval_cutoff;
    }

    protected $ifaccords;
    protected $if_send;

    public function SetIfSend($id) {
        $this->if_send = $id;
    }
    public function GetIfSend() {
        return $this->if_send;
    }
    public function SetIfaccords($id) {
        $this->ifaccords = $id;
    }
    public function GetIfaccords() {
        return $this->ifaccords;
    }
    public function get_toshow($id_proceso= null) {
        if (empty($id_proceso))
            return $this->toshow_plan;

        $sql= "select * from tproceso_eventos where id_evento= $this->id_evento and id_proceso = $id_proceso";
        $result= $this->do_sql_show_error('get_toshow', $sql);
        $row= $this->clink->fetch_array($result);
        return $row['toshow'];
    }

    protected $id_requisito;
    protected $id_requisito_code;

    public function SetIdRequisito($id) {
        $this->id_requisito= $id;
    }
    public function GetIdRequisito() {
        return $this->id_requisito;
    }
    public function get_id_requisito_code() {
        return $this->id_requisito_code;
    }
    public function set_id_requisito_code($id) {
        $this->id_requisito_code= $id;
    }

    public $array_usuarios_entity;

    public $total, $total_cumulative;

    public $no_iniciadas, $no_iniciadas_list;
    public $cumplidas, $cumplidas_list;
    public $incumplidas, $incumplidas_list;
    public $canceladas, $canceladas_list;
    public $modificadas, $delegadas_list;
    public $delegadas, $modificadas_list;
    public $rechazadas, $rechazadas_list;
    public $reprogramadas, $reprogramadas_list;

    public $efectivas, $efectivas_cumplidas, $efectivas_cumplidas_list;
    public $efectivas_incumplidas, $efectivas_incumplidas_list;
    public $efectivas_canceladas, $efectivas_canceladas_list;
    public $efectivas_modificadas, $efectivas_modificadas_list;
    public $efectivas_delegadas, $efectivas_delegadas_list;
    public $efectivas_rechazadas, $efectivas_rechazadas_list;
    public $efectivas_list;

    public $extras_rechazadas;
    public $externas, $externas_list;
    public $extras, $extras_externas, $extras_propias, $extras_list;
    
    public $anual, $anual_externas, $anual_propias;
    public $mensual, $mensual_externas, $mensual_propias;
    public $mensual_extras, $mensual_externas_extras, $mensual_propias_extras;

    public $anual_array, $anual_externas_array, $anual_propias_array;
    public $assure_array, $assure_externas_array, $assure_propias_array;

    public $assure, $assure_externas, $assure_propias;

    protected function init_list() {
        $this->cant_print_reject= 0;

        $this->total= 0;
        $this->no_iniciadas= 0;
        $this->cumplidas= 0;
        $this->incumplidas= 0;
        $this->canceladas= 0;
        $this->modificadas= 0;
        $this->delegadas= 0;
        $this->rechazadas= 0;
        $this->reprogramadas= 0;

        $this->efectivas= 0;
        $this->efectivas_cumplidas= 0;
        $this->efectivas_incumplidas= 0;
        $this->efectivas_rechazadas= 0;
        $this->efectivas_canceladas= 0;

        $this->externas= 0;

        $this->extras= 0;
        $this->extras_externas= 0;
        $this->extras_propias= 0;

        $this->anual= 0;
        $this->anual_propias= 0;
        $this->anual_externas= 0;

        $this->mensual= 0;
        $this->mensual_extras= 0;
        $this->mensual_propias= 0;
        $this->mensual_externas= 0;
        $this->mensual_externas_extras= 0;
        $this->mensual_propias_extras= 0;

        $this->anual_array= array();
        $this->anual_externas_array= array();
        $this->anual_propias_array= array();

        $this->assure= 0;                 $this->assure_array= array();
        $this->assure_externas= 0;        $this->assure_externas_array= array();
        $this->assure_propias= 0;         $this->assure_propias_array= array();

        for ($i= 2; $i < _MAX_TIPO_ACTIVIDAD; $i++) {
            $this->anual_array[$i]= 0;
            $this->anual_externas_array[$i]= 0;
            $this->anual_propias_array[$i]= 0;

            $this->assure_array[$i]= 0;
            $this->assure_externas_array[$i]= 0;
            $this->assure_propias_array[$i]= 0;
        }

        $this->extras_rechazadas= 0;

        $this->no_iniciadas_list= array();
        $this->cumplidas_list= array();
        $this->incumplidas_list= array();
        $this->canceladas_list= array();
        $this->delegadas_list= array();
        $this->modificadas_list= array();
        $this->rechazadas_list= array();
        $this->reprogramadas_list= array();
        $this->efectivas_list= array();
        $this->externas_list= array();
        $this->extras_list= array();
        $this->efectivas_cumplidas_list= array();
        $this->efectivas_incumplidas_list= array();
        $this->efectivas_canceladas_list= array();
        $this->efectivas_modificads_list= array();
        $this->efectivas_delegadas_list= array();
        $this->efectivas_reprogramadas_list= array();
    }

    protected $if_numering;

    public function GetIfNumering() {
        return $this->if_numering;
    }
    public function SetIfNumering($id) {
        $this->if_numering= $id;
    }

    public function  __construct($clink) {
        $this->clink= $clink;
        Tbase_usuario::__construct($clink);
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

        $this->max_num_pages= 1;
        $this->max_row_in_page= _MAX_ROW_IN_PAGE;
        $this->init_row_temporary= 0;

        $this->if_tidx= false;
        $this->array_status_eventos_ids= array();
        $this->create_temporary_treg_evento_table= true;

        $this->obj_code= new Tcode($this->clink);
    }

    protected function date_interval(&$fecha_inicio, &$fecha_fin, $corte= null) {
        $time= new TTime();
        $year= $time->GetYear();
        $month= $time->GetMonth();
        $day= $time->GetDay();

        $time->SetYear($this->year);

        if (empty($this->month) || $corte == 'all_year') {
            $month_init= 1;

            if ($this->year < $year || $corte == 'all_year') {
                $month_end= 12;
                $time->SetMonth(12);
            } else
                $month_end= $time->GetMonth();

        } else {
            $month_init= $this->month; $month_end= $this->month;
            $time->SetMonth($this->month);
        }

        if (empty($this->day)) {
            $day_init= 1;

            $end_day= $day;
            if ((($this->month < $month && $this->year == $year) || $this->year < $year)
                || ($corte == 'all_month' || $corte == 'all_year'))
                $end_day= $time->longmonth();
        } else {
            $day_init= $this->day;
            $end_day= $this->day;
        }

        if (is_null($this->hh))
            $this->hh= 23;
        if (is_null($this->mi))
            $this->mi= 59;

        unset($time);

        $fecha_inicio= $this->year.'-'.str_pad($month_init, 2, "0", STR_PAD_LEFT).'-'.str_pad($day_init, 2, "0", STR_PAD_LEFT).' 00:00:00';
        $fecha_fin= $this->year.'-'.str_pad($month_end, 2, "0", STR_PAD_LEFT).'-'.str_pad($end_day, 2, "0", STR_PAD_LEFT).' '.$this->hh.':'.$this->mi.':59';
    }

    protected function list_date_interval(&$fecha_inicio, &$fecha_fin, $corte= null) {
        if (empty($this->month) || $corte == 'all_year')
            $fecha_inicio= $this->year."-01-01";
        else
            $fecha_inicio= $this->year.'-'.str_pad($this->month, 2, "0", STR_PAD_LEFT).'-01';
        $this->date_interval($fecha, $fecha_fin, $corte);
    }

    public function copy_in_object(/*Tregister_planning*/ &$object) {
        $object->toshow= $this->toshow;
        $object->monthstack= $this->monthstack;
        $object->SetIfEmpresarial($this->empresarial);
        $object->SetIdTipo_reunion($this->id_tipo_reunion);
        $object->SetIdTipo_evento($this->id_tipo_evento);

        $object->if_tidx= $this->if_tidx;
        $object->if_teventos= $this->if_teventos;
        $object->if_treg_evento= $this->if_treg_evento;
        $object->if_auditoria= $this->if_auditoria;
        $object->if_tauditorias= $this->if_tauditorias;
        $object->if_tusuarios= $this->if_tusuarios;
        $object->if_tproceso_eventos= $this->if_tproceso_eventos;

        $object->set_use_copy_tusuarios($this->use_copy_tusuarios);
        $object->if_copy_tusuarios= $this->if_copy_tusuarios;

        $object->limited= $this->limited;
        $object->max_num_pages= $this->max_num_pages;
        $object->max_row_in_page= $this->max_row_in_page;
        $object->set_init_row_temporary($this->init_row_temporary);

        $object->fecha_inicio_plan_page= $this->fecha_inicio_plan_page;
        $object->fecha_fin_plan_page= $this->fecha_fin_plan_page;
        $object->print_reject= $this->print_reject;

        $object->SetIdProceso($this->id_proceso);
        $object->set_id_proceso_code($this->id_proceso_code);

        $object->SetIdEvento($this->id_evento);
        $object->set_id_evento_code($this->id_evento_code);

        $object->SetIdAuditoria($this->id_auditoria);
        $object->set_id_auditoria_code($this->id_auditoria_code);

        $object->SetIdTarea($this->id_tarea);
        $object->set_id_tarea_code($this->id_tarea_code);

        $object->SetIdUsuario($this->id_usuario);
        $object->SetRole($this->role);
        $object->SetIdResponsable($this->id_responsable);
        $object->SetIdResponsable_ref($this->id_responsable_ref);

        $object->set_id_proceso_asigna($this->id_proceso_asigna);

        $object->set_cronos($this->cronos);
        $object->SetDay($this->day);
        $object->SetMonth($this->month);
        $object->SetYear($this->year);

        $object->periodicidad= $this->periodicidad;
        $object->SetFechaInicioPlan($this->fecha_inicio_plan);
        $object->SetFechaFinPlan($this->fecha_fin_plan);

        $object->SetTipoPlan($this->tipo_plan);
        $object->SetOrigen($this->origen);
        $object->SetTipo($this->tipo);
        $object->SetOrganismo($this->organismo);

        $object->divout= $this->divout;

        foreach ($this->time_hit as $key => $value)
            $object->time_hit[$key]= $value;
        foreach ($this->array_procesos as $key => $value)
            $object->array_procesos[$key]= $value;
        foreach ($this->array_usuarios as $key => $value)
            $object->array_usuarios[$key]= $value;
        foreach ($this->array_eventos as $key => $value)
            $object->array_eventos[$key]= $value;
        foreach ($this->array_auditorias as $key => $value)
            $object->array_auditorias[$key]= $value;
        foreach ($this->array_tareas as $key => $value)
            $object->array_tareas[$key]= $value;

        foreach ($this->array_status_eventos_ids as $key => $value)
            $object->array_status_eventos_ids[$key]= $value;

        $object->create_temporary_treg_evento_table= $this->create_temporary_treg_evento_table;
        $object->set_use_copy_tusuarios($this->use_copy_tusuarios);
        $object->like_name= $this->like_name;

        $object->SetAprobado($this->aprobado);
        $object->SetRechazado($this->rechazado);
        $object->compute= $this->compute;
        $object->set_user_check($this->user_check);
        $object->set_user_date_ref($this->user_date_ref);

        $object->SetObservacion($this->observacion);
        $object->SetDescripcion($this->descripcion);
        $object->SetCumplimiento($this->cumplimiento);

        $object->set_go_delete($this->go_delete);
    }

    /*
     * adicionar a latabla tdeletes los registros de brrado
     */

    public $array_evento_data;

    protected function push_array_evento_data($tabla) {
        if (empty($this->id_evento) && empty($this->id_tarea) && empty($this->id_auditoria))
            return null;

        $id_evento= !empty($this->id_evento) ? $this->id_evento : 0;
        $id_tarea= !empty($this->id_tarea) ? $this->id_tarea : 0;
        $id_auditoria= !empty($this->id_auditoria) ? $this->id_auditoria : 0;

        if (is_array($this->array_evento_data[$id_evento][$id_tarea][$id_auditoria]))
            return $this->array_evento_data[$id_evento][$id_tarea][$id_auditoria];

        $obj= null;
        if (!empty($this->id_evento)) {
            $obj= new Tevento($this->clink);
            $obj->Set($this->id_evento);
        } elseif (!empty($this->id_tarea)) {
            $obj= new Ttarea($this->clink);
            $obj->Set($this->id_tarea);
        } elseif ($this->id_auditoria) {
            $obj= new Tauditoria($this->clink);
            $obj->Set($this->id_auditoria);
        }

        if (is_null($obj))
            return null;

        $array= array('id'=>$obj->GetId(), 'id_code'=>$obj->get_id_code(), 'nombre'=>$obj->GetNombre(),
                'fecha_inicio_plan'=>$obj->GetFechaInicioPlan(), 'fecha_fin_plan'=>$obj->GetFechaFinPlan(),
                'id_tipo_reunion'=>$obj->GetIdTipo_reunion());

        $this->array_evento_data[$id_evento][$id_tarea][$id_auditoria]= $array;
        return $array;
    }

    protected function add_to_tdelete($tabla, $multi_query= false) {
        global $meeting_array;
        
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $array= array();
        $i= 0;

        $array_tematicas= array("tusuario_eventos");
        $array_procesos= array("tproceso_eventos");

        if (!empty($this->id_evento) && empty($this->id_evento_code))
            $this->id_evento_code= get_code_from_table ("teventos", $this->id_evento);
        if (!empty($this->id_auditoria) && empty($this->id_auditoria_code))
            $this->id_auditoria_code= get_code_from_table ("tauditorias", $this->id_auditoria);
        if (!empty($this->id_tarea) && empty($this->id_tarea_code))
            $this->id_tarea_code= get_code_from_table ("ttareas", $this->id_tarea);
        if (!empty($this->id_tematica) && empty($this->id_tematica_code))
            $this->id_tematica_code= get_code_from_table ("ttematicas", $this->id_tematica);

        if (!empty($this->id_evento))
            $array[]= array('id_evento_code', setNULL_str($this->id_evento_code));
        if (!empty($this->id_auditoria))
            $array[]= array('id_auditoria_code', setNULL_str($this->id_auditoria_code));
        if (!empty($this->id_tarea))
            $array[]= array('id_tarea_code', setNULL_str($this->id_tarea_code));
        if (!empty($this->id_usuario))
            $array[]= array('id_usuario', setNULL_str($this->id_usuario));
        if (!empty($this->id_tematica) && array_search($tabla, $array_tematicas) !== false)
            $array[]= array('id_tematica_code', setNULL_str($this->id_tematica_code));
        if (!empty($this->id_proceso) && array_search($tabla, $array_procesos) !== false)
            $array[]= array('id_proceso_code', setNULL_str($this->id_proceso_code));

        $_array= $this->push_array_evento_data($tabla);
        if ($_array) {
            $observacion= !empty($_array['id_tipo_reunion']) ? $meeting_array[$_array['id_tipo_reunion']] : "";
            $observacion.= $_array['nombre'];
            $observacion.= "<br />Inicio: {$_array['fecha_inicio_plan']}  Fin: {$_array['fecha_fin_plan']}";
        }
        $observacion= setNULL_str($observacion);

        $ncells= count($array);
        $sql= "insert into tdeletes (tabla";
        $i= 0;
        foreach ($array as $field) {
            ++$i;
            $sql.= ", campo{$i}, valor{$i}";
        }
        $sql.= ", id_responsable, observacion, cronos ,situs) values ('$tabla' ";

        reset($array);
        foreach ($array as $field)
            $sql.= ", '{$field[0]}', $field[1] ";
        $sql.= ", {$_SESSION['id_usuario']}, $observacion, '$this->cronos', '$this->location'); ";

        if (!$multi_query)
            $result= $this->do_sql_show_error('set_delete', $sql);

        return $multi_query ? $sql : null;    
    }

    protected function set_tidx($id, $className= "Tevento") {
        $className= !empty($className) ? $className : $this->className;
        $this->indice= null;
        $this->indice_plus= null;

        if (empty($this->id_tipo_evento))
            $this->indice= !empty($this->empresarial) ? $this->empresarial*pow(10,6) : null;
        else {
            $obj= new Ttipo_evento($this->clink);
            $obj->Set($this->id_tipo_evento);
            $this->indice= $obj->indice;
        }

        if (!empty($this->numero_plus))
            $this->indice_plus= index_to_number($this->numero_plus);

        $this->tidx= null;
        if (($className == "Tevento" && (!empty($this->id_auditoria) || !empty($this->id_tarea))) || !empty($id))
            $this->tidx= false;
        if (empty($this->periodicidad) && (empty($id) && ($className != "Tevento"
                                                        || ($className == "Tevento" && empty($this->id_auditoria) && empty($this->id_tarea)))))
            $this->tidx= true;
        if (!empty($this->periodicidad))
            $this->tidx= true;
    }

    public function listar_procesos_entity($id_proceso= null) {
        if (is_null($this->array_procesos_entity)) {
            $this->array_procesos_entity= array();

            $obj_prs= new Tproceso($this->clink);
            $obj_prs->SetYear($this->year);
            $obj_prs->listar_procesos_entity();

            $this->array_procesos_entity= $this->array_procesos_entity + $obj_prs->array_procesos_entity;
        }

        if (!empty($id_proceso))
            return $this->array_procesos_entity;
        else
            return $obj_prs->test_if_proceso_in_entity($id_proceso);
    }

    public function test_if_proceso_in_entity($id_proceso) {
        return $this->listar_procesos_entity($id_proceso);
    }
}

/*
 * Clases adjuntas o necesarias
 */

include_once "../config.inc.php";
include_once "time.class.php";
include_once "code.class.php";
include_once "library.php";
include_once "library_string.php";
include_once "library_style.php";

if (!class_exists('Tbase_alert'))
    include_once "../../tools/alert/php/base_alert.class.php";
if (!class_exists('Talert'))
    include_once "../../tools/alert/php/alert.class.php";
