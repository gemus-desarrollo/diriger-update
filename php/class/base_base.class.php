<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2021
 */

class Tbase_core {
    protected $id;
    protected $id_code;

    public function SetId($id) {
        $this->id = $id;
    }
    public function GetId() {
        return $this->id;
    }
    public function set_id_code($id) {
        $this->id_code = $id;
    }
    public function get_id_code() {
        return $this->id_code;
    }

    protected $tipo;

    public function SetTipo($id) {
        $this->tipo = $id;
    }
    public function GetTipo() {
        return $this->tipo;
    }

    protected $cant;

    public function GetCantidad() {
        return $this->cant;
    }
    protected $origen_data;

    public function SetOrigenData($id) {
        $this->origen_data = $id;
    }

    public $toshow;

    protected $clink;
    protected $cronos;
    protected $kronos;
    public $location;

    public $array_usuarios;
    public $array_grupos;
    public $array_procesos;
    public $array_eventos;
    public $array_tareas;
    public $array_pesos;
    public $array_indicadores;
    public $array_perspectivas;
    public $array_inductores;
    public $array_tableros;
    public $array_politicas;
    public $array_auditorias;
    public $array_notas;
    public $array_riesgos;
    public $array_month_items;
    public $array_proyectos;
    public $array_tematicas;
    public $array_asistencias;
    public $array_documentos;
    public $nums_proyectos;

    protected $time_hit;

    public function GetLink() {
        return $this->clink;
    }
    public function SetLink($id) {
        $this->clink = $id;
    }
    public function set_cronos($id = null) {
        $this->cronos = is_null($id) ? date('Y-m-d H:i:s') : $id;
    }
    public function get_cronos() {
        return $this->cronos;
    }
    public function get_kronos() {
        return $this->kronos;
    }

    public $action;
    public $signal;
    public $redirect;
    public $error,
            $error_system;

    public $obj_code;

    public function __construct($clink= null) {
        $this->action= 'list';
        $this->redirect= 'ok';
        $this->error= null;
        $this->cant= 0;
        $this->stop_by_die= false;

        if (!is_null($clink))
            $this->clink= $clink;

        $this->array_procesos= null;

        if ($_SESSION['id_usuario'] != -1 && (empty($_SESSION['ip_app']) || empty($_SESSION['mac']))) {
            if (is_null($_SESSION['output_signal'])
                || (!is_null($_SESSION['output_signal']) && ($_SESSION['output_signal'] != 'shell' && $_SESSION['output_signal'] != 'webservice'))) {
        ?>
            parent.location.href= '<?=_ROOT_DIRIGER_DIR."index.php"?>';
        <?php } }

        $this->cronos= date('Y-m-d H:i:s');
        $this->location= $_SESSION['location'];
        $this->user_date_ref= date('Y-m-d');

        $this->if_copy_tusuarios= false;
        $this->use_copy_tusuarios= false;

        $this->if_copy_tprocesos= false;
        $this->use_copy_tprocesos= false;
    }    

    protected $id_usuario;
    protected $role;
    protected $user_date_ref;
    public $if_copy_tusuarios;
    public $if_tusuarios;
    protected $use_copy_tusuarios;

    public function SetIdUsuario($id) {
        $this->id_usuario = $id;
    }
    public function SetRole($id) {
        $this->role = $id;
    }
    public function GetIdUsuario() {
        return $this->id_usuario;
    }
    public function GetRole() {
        return $this->role;
    }
    public function set_user_date_ref($date = null, $permit_null_value= false) {
        $this->user_date_ref = !empty($date) ? $date : !$permit_null_value ? date('Y-m-d') : null;
    }
    
    protected $email;

    public function GetMail_address() {
        return $this->email;
    }
    public function SetMail_address($id) {
        $this->email = strtolower($id);
    }

    protected $id_responsable;

    public function SetIdResponsable($id) {
        $this->id_responsable = $id;
    }
    public function GetIdResponsable() {
        return $this->id_responsable;
    }

    protected $numero;

    public function GetNumero() {
        return $this->numero;
    }
    public function SetNumero($id) {
        $this->numero = $id;
    }

    protected $id_grupo;
    protected $id_politica;
    protected $id_politica_code;

    public function SetIdGrupo($id) {
        $this->id_grupo = $id;
    }
    public function SetIdPolitica($id) {
        $this->id_politica = $id;
    }
    public function set_id_politica_code($id) {
        $this->id_politica_code = $id;
    }
    public function GetIdGrupo() {
        return $this->id_grupo;
    }
    public function GetIdPolitica() {
        return $this->id_politica;
    }
    public function get_id_politica_code() {
        return $this->id_politica_code;
    }

    protected $id_objetivo;
    protected $id_objetivo_code;

    public function SetIdObjetivo($id) {
        $this->id_objetivo = $id;
    }
    public function GetIdObjetivo() {
        return $this->id_objetivo;
    }
    public function set_id_objetivo_code($id) {
        $this->id_objetivo_code = $id;
    }
    public function get_id_objetivo_code() {
        return $this->id_objetivo_code;
    }

    protected $id_perspectiva;
    protected $id_perspectiva_code;

    public function SetIdPerspectiva($id) {
        $this->id_perspectiva = $id;
    }
    public function GetIdPerspectiva() {
        return $this->id_perspectiva;
    }
    public function set_id_perspectiva_code($id) {
        $this->id_perspectiva_code = $id;
    }

    public function get_id_perspectiva_code() {
        return $this->id_perspectiva_code;
    }

    protected $id_escenario;
    protected $id_escenario_code;

    public function SetIdEscenario($id) {
        $this->id_escenario = $id;
    }
    public function GetIdEscenario() {
        return $this->id_escenario;
    }
    public function set_id_escenario_code($id) {
        $this->id_escenario_code = $id;
    }
    public function get_id_escenario_code() {
        return $this->id_escenario_code;
    }

    protected $nombre;
    protected $descripcion;
    protected $observacion;

    public function SetNombre($id, $flag= true) {
        if ($flag) {
            $id= fullUpper($id);
        }
        $this->nombre= $id;
    }

    public function SetDescripcion($id) {
        $this->descripcion = $id;
    }
    public function SetObservacion($id) {
        $this->observacion = $id;
    }
    public function GetNombre() {
        $str = $this->nombre;
        return $str;
    }
    public function GetDescripcion() {
        $str = $this->descripcion;
        return $str;
    }
    public function GetObservacion() {
        $str = $this->observacion;
        return $str;
    }

    protected $color;

    public function GetColor() {
        return $this->color;
    }
    public function SetColor($id) {
        $this->color = $id;
    }

    protected $id_inductor;
    protected $id_inductor_code;

    public function GetIdInductor() {
        return $this->id_inductor;
    }
    public function SetIdInductor($id) {
        $this->id_inductor = $id;
    }
    public function set_id_inductor_code($id) {
        $this->id_inductor_code = $id;
    }
    public function get_id_inductor_code() {
        return $this->id_inductor_code;
    }

    protected $id_tablero;
    protected $cumplimiento;
    protected $value;

    public function SetIdTablero($id) {
        $this->id_tablero = $id;
    }
    public function SetCumplimiento($id) {
        $this->cumplimiento = $id;
    }
    public function SetValue($id) {
        $this->value = $id;
    }
    public function GetIdTablero() {
        return $this->id_tablero;
    }
    public function GetCumplimiento() {
        return $this->cumplimiento;
    }
    public function GetValue() {
        return $this->value;
    }

    protected $id_indicador;
    protected $id_indicador_code;

    public function SetIdIndicador($id) {
        $this->id_indicador = $id;
    }
    public function set_id_indicador_code($id) {
        $this->id_indicador_code = $id;
    }
    public function GetIdIndicador() {
        return $this->id_indicador;
    }
    public function get_id_indicador_code() {
        return $this->id_indicador_code;
    }

    protected $_orange;
    protected $_yellow;
    protected $_green;
    protected $_aqua;
    protected $_blue;

    public function set_orange($id) {
        $this->_orange = $id;
    }
    public function set_yellow($id) {
        $this->_yellow = $id;
    }
    public function set_green($id) {
        $this->_green = $id;
    }
    public function set_aqua($id) {
        $this->_aqua = $id;
    }
    public function set_blue($id) {
        $this->_blue = $id;
    }

    public function get_orange() {
        return $this->_orange;
    }
    public function get_yellow() {
        return $this->_yellow;
    }
    public function get_green() {
        return $this->_green;
    }
    public function get_aqua() {
        return $this->_aqua;
    }
    public function get_blue() {
        return $this->_blue;
    }

    protected $id_evento;
    protected $id_evento_code;
    protected $id_tipo_evento;
    protected $id_tipo_evento_code;
    public $if_treg_evento;
    public $if_teventos;
    public $if_tproceso_eventos;
    public $if_tproceso_eventos_small;

    public function SetIdEvento($id) {
        $this->id_evento = $id;
    }
    public function set_id_evento_code($id) {
        $this->id_evento_code = $id;
    }
    public function GetIdEvento() {
        return $this->id_evento;
    }
    public function get_id_evento_code() {
        return $this->id_evento_code;
    }

    protected $id_proyecto;
    protected $id_proyecto_code;

    public function SetIdProyecto($id) {
        $this->id_proyecto = $id;
    }
    public function set_id_proyecto_code($id) {
        $this->id_proyecto_code = $id;
    }
    public function GetIdProyecto() {
        return $this->id_proyecto;
    }
    public function get_id_proyecto_code() {
        return $this->id_proyecto_code;
    }

    protected $id_tarea;
    protected $id_tarea_code;
    public $if_ttareas;

    public function SetIdTarea($id) {
        $this->id_tarea = $id;
    }
    public function set_id_tarea_code($id) {
        $this->id_tarea_code = $id;
    }
    public function GetIdTarea() {
        return $this->id_tarea;
    }

    public function get_id_tarea_code() {
        return $this->id_tarea_code;
    }

    protected $id_tematica;
    protected $id_tematica_code;

    public function SetIdTematica($id) {
        $this->id_tematica = $id;
    }

    public function GetIdTematica() {
        return $this->id_tematica;
    }

    public function set_id_tematica_code($id) {
        $this->id_tematica_code = $id;
    }

    public function get_id_tematica_code() {
        return $this->id_tematica_code;
    }

    protected $id_programa;
    protected $id_programa_code;

    public function SetIdPrograma($id) {
        $this->id_programa = $id;
    }

    public function set_id_programa_code($id) {
        $this->id_programa_code = $id;
    }

    public function GetIdPrograma() {
        return $this->id_programa;
    }

    public function get_id_programa_code() {
        return $this->id_programa_code;
    }

    protected $id_archivo;
    protected $id_archivo_code;

    public function SetIdArchivo($id) {
        $this->id_archivo= $id;
    }
    public function set_id_archivo_code($id) {
        $this->id_archivo_code= $id;
    }
    public function GetIdArchivo() {
        return $this->id_archivo;
    }
    public function get_id_archivo_code() {
        return $this->id_archivo_code;
    }
    protected $id_tipo_auditoria;
    protected $id_tipo_auditoria_code;

    protected $id_tipo_reunion;
    protected $id_tipo_reunion_code;

    public function SetIdTipo_auditoria($id) {
        $this->id_tipo_auditoria= $id;
    }
    public function set_id_tipo_auditoria_code($id) {
        $this->id_tipo_auditoria_code= $id;
    }
    public function GetIdTipo_auditoria() {
        return $this->id_tipo_auditoria;
    }
    public function get_id_tipo_auditoria_code() {
        return $this->id_tipo_auditoria_code;
    }
    public function SetIdTipo_reunion($id) {
        $this->id_tipo_reunion= $id;
    }
    public function set_id_tipo_reunion_code($id) {
        $this->id_tipo_reunion_code= $id;
    }
    public function GetIdTipo_reunion() {
        return $this->id_tipo_reunion;
    }
    public function get_id_tipo_reunion_code() {
        return $this->id_tipo_reunion_code;
    }

    protected $id_proceso;
    protected $id_proceso_code;
    public $if_copy_tprocesos;
    protected $use_copy_tprocesos;
    public $if_tprocesos;

    public function GetIdProceso() {
        return $this->id_proceso;
    }

    public function set_id_proceso_code($id) {
        $this->id_proceso_code = $id;
    }

    public function SetIdProceso($id) {
        $this->id_proceso = $id;
    }

    public function get_id_proceso_code() {
        return $this->id_proceso_code;
    }   
    
    protected $id_entity;
    protected $id_entity_code;

    public function SetIdEntity($id) {
        $this->id_entity= $id;
    }
    public function GetIdEntity() {
        return $this->id_entity;
    }
    
    protected $id_kanban_column;
    protected $id_kanban_column_code;

    public function SetIdKanbanColumn($id) {
        $this->id_kanban_column= $id;
    }
    public function GetIdKanbanColumn() {
        return $this->id_kanban_column;
    }
    public function set_id_KanbanColumn_code($id) {
        $this->id_kanban_column_code= $id;
    }
    public function get_id_KanbanColumn_code() {
        return $this->id_kanban_column_code;
    }

    protected $id_riesgo;
    protected $id_riesgo_code;

    public function SetIdRiesgo($id) {
        $this->id_riesgo = $id;
    }
    public function set_id_riesgo_code($id) {
        $this->id_riesgo_code = $id;
    }
    public function GetIdRiesgo() {
        return $this->id_riesgo;
    }
    public function get_id_riesgo_code() {
        return $this->id_riesgo_code;
    }

    protected $id_plan;
    protected $id_plan_code;

    public function GetIdPlan() {
        return $this->id_plan;
    }
    public function set_id_plan_code($id) {
        $this->id_plan_code = $id;
    }
    public function SetIdPlan($id) {
        $this->id_plan = $id;
    }
    public function get_id_plan_code() {
        return $this->id_plan_code;
    }

    protected $id_nota;
    protected $id_nota_code;

    public function GetIdNota() {
        return $this->id_nota;
    }
    public function set_id_nota_code($id) {
        $this->id_nota_code = $id;
    }
    public function SetIdNota($id) {
        $this->id_nota = $id;
    }
    public function get_id_nota_code() {
        return $this->id_nota_code;
    }

    protected $id_auditoria;
    protected $id_auditoria_code;
    public $if_auditoria;
    public $if_tauditorias;

    public function GetIdAuditoria() {
        return $this->id_auditoria;
    }
    public function set_id_auditoria_code($id) {
        $this->id_auditoria_code = $id;
    }
    public function SetIdAuditoria($id) {
        $this->id_auditoria = $id;
    }

    public function get_id_auditoria_code() {
        return $this->id_auditoria_code;
    }

    protected $id_lista;
    protected $id_lista_code;

    public function GetIdLista() {
        return $this->id_lista;
    }
    public function SetIdLista($id) {
        return $this->id_lista= $id;
    }
    public function get_id_lista_code() {
        return $this->id_lista_code;
    }
    public function set_id_lista_code($id) {
        return $this->id_lista_code= $id;
    }

    protected $carga;
    protected $periodicidad;
    protected $dayweek;
    protected $sendmail;
    protected $toworkplan;
    public $sunday, $saturday, $freeday;
    protected $cant_days;
    public $fixed_day;
    protected $periodic;

    public function SetCarga($id) {
        $this->carga = $id;
    }
    public function SetPeriodicidad($id) {
        $this->periodicidad = $id;
    }
    public function SetSendMail($id = 1) {
        $this->sendmail = $id;
    }
    public function SetDayWeek($id) {
        $this->dayweek = $id;
    }
    public function SetCantidad_days($id) {
        $this->cant_days = $id > 0 ? $id : 0;
    }
    public function SetIfPeriodic($id = 1) {
        $this->periodic = $id;
    }
    public function GetCarga() {
        return $this->carga;
    }
    public function GetPeriodicidad() {
        return $this->periodicidad;
    }
    public function GetSendMail() {
        return $this->sendmail;
    }
    public function GetDayWeek() {
        return $this->dayweek;
    }
    public function GetCantidad_days() {
        return $this->cant_days;
    }
    public function GetIfPeriodic() {
        return $this->periodic;
    }

    protected $fecha_inicio_plan, 
            $fecha_fin_plan, 
            $duracion_plan;
    protected $fecha_inicio_real, 
            $fecha_fin_real, 
            $duracion_real;

    public function SetFechaInicioPlan($id) {
        $this->fecha_inicio_plan = $id;
    }
    public function SetFechaFinPlan($id) {
        $this->fecha_fin_plan = $id;
    }
    public function GetFechaInicioPlan() {
        return $this->fecha_inicio_plan;
    }
    public function GetFechaFinPlan() {
        return $this->fecha_fin_plan;
    }
    public function SetFechaInicioReal($id) {
        $this->fecha_inicio_real = $id;
    }
    public function GetFechaInicioReal() {
        return $this->fecha_inicio_real;
    }
    public function SetFechaFinReal($id) {
        $this->fecha_fin_real = $id;
    }
    public function GetFechaFinReal() {
        return $this->fecha_fin_real;
    }
    public function GetDuracionPlan() {
        return $this->duracion_plan;
    }
    public function GetDuracionReal() {
        return $this->duracion_real;
    }

    protected $day;
    protected $month;
    protected $year;

    protected $hh; //horas
    protected $mi; // minutos

    public function SetDay($id) {
        $this->day = (int) $id;
    }
    public function SetMonth($id) {
        $this->month = (int) $id;
    }
    public function SetYear($id) {
        $this->year = (int) $id;
    }
    public function SetHour($id) {
        $this->hh = $id;
    }
    public function SetMinute($id) {
        $this->mi = $id;
    }
    public function GetDay() {
        return $this->day;
    }
    public function GetMonth() {
        return $this->month;
    }
    public function GetYear() {
        return $this->year;
    }

    protected $peso;
    protected $fecha;
    protected $inicio;
    protected $fin;

    public function GetFecha() {
        return $this->fecha;
    }
    public function SetFecha($id) {
        $this->fecha = $id;
    }
    public function SetInicio($id) {
        $this->inicio = $id;
    }
    public function SetFin($id) {
        $this->fin = $id;
    }
    public function GetInicio() {
        return $this->inicio;
    }
    public function GetFin() {
        return $this->fin;
    }

    public function SetPeso($id) {
        $this->peso = $id;
    }
    public function GetPeso() {
        return $this->peso;
    }

    protected $id_objetivo_sup;
    protected $id_objetivo_sup_code;
    protected $if_objsup;

    public function SetIfObjetivoSup($id = 1) {
        $this->if_objsup = empty($id) ? 0 : 1;
    }
    public function GetIfObjetivoSup() {
        return $this->if_objsup;
    }
    public function SetIdObjetivo_sup($id) {
        $this->id_objetivo_sup = $id;
    }
    public function GetIdObjetivo_sup() {
        return $this->id_objetivo_sup;
    }
    public function set_id_objetivo_sup_code($id) {
        $this->id_objetivo_sup_code = $id;
    }
    public function get_id_objetivo_sup_code() {
        return $this->id_objetivo_sup_code;
    }    
}