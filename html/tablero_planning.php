<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 3/22/15
 * Time: 5:32 a.m.
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/base_usuario.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/proyecto.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/auditoria.class.php";

require_once "../php/class/tipo_auditoria.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../php/class/evento_numering.class.php";

require_once "../php/class/plantrab.class.php";
require_once "../php/class/plan_ci.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/peso.class.php";

require_once "../form/class/evento.signal.class.php";
require_once "../form/class/auditoria.signal.class.php";

require_once "../tools/archive/php/class/organismo.class.php";

require_once "../php/class/badger.class.php";

$time= new TTime();
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$auto_refresh_stop= !is_null($_GET['auto_refresh_stop']) ? $_GET['auto_refresh_stop'] : 0;

if (isset($_SESSION['obj'])) 
    unset($_SESSION['obj']);

$force_user_process= true;
require_once "../php/inc_escenario_init.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'anual_plan';
$calendar_type= !empty($_GET['calendar_type']) ? $_GET['calendar_type'] : 0;

if (!empty($_GET['id_calendar']))
    $id_calendar= $_GET['id_calendar'];

if ($signal == 'calendar') {
    if (empty($id_calendar))
        $id_calendar= $_SESSION['id_calendar'];
    if (empty($id_calendar))
        $id_calendar= $_SESSION['id_usuario'];
    $_SESSION['id_calendar']= $id_calendar;
}

if ($signal != 'calendar')
    $id_calendar= 0;

$actual_year= (int)$actual_year;
$actual_month= (int)$actual_month;
$actual_day= (int)$actual_day;

$inicio= $actual_year - 5;
$fin= $actual_year + 3;
if (empty($hh))
    $hh= $time->GetHour();
if (empty($mi))
    $mi= $time->GetMinute();

$_SESSION['current_month']= $month;
$_SESSION['current_year']= $year;

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

$obj_signal= null;
if ($signal == 'anual_plan_audit')
    $obj_signal= new Taudit_signals($clink);
if ($signal != 'anual_plan_audit')
    $obj_signal= new Tevento_signals($clink);

$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);
$obj_pry= new Tproyecto($clink);

if (isset($_SESSION['obj_plantrab'])) 
    unset($_SESSION['obj_plantrab']);
$_SESSION['obj_plantrab']= null;

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

if (empty($print_reject)) {
    switch ($signal) {
        case ('calendar') :
        case ('anual_plan_meeting') :
            $print_reject = _PRINT_REJECT_NO;
            break;
        case ('mensual_plan') :
            $print_reject = _PRINT_REJECT_NO;
            break;
        default:
            $print_reject = _PRINT_REJECT_DEFEAT;
            break;
    }
}

$user_date_ref= $year.'-'.str_pad($month, 2, "0", STR_PAD_LEFT).'-'.str_pad($day, 2, "0" ,STR_PAD_LEFT);

$acc= null;
$acc_planwork= !empty($_SESSION['acc_planwork']) ? $_SESSION['acc_planwork'] : 0;
$acc_planaudit= !is_null($_SESSION['acc_planaudit']) ? $_SESSION['acc_planaudit'] : 0;
$tipo_plan= !is_null($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;

$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;
$if_numering= !is_null($_GET['if_numering']) ? $_GET['if_numering'] : null;

$origen= !empty($_GET['origen']) ? $_GET['origen'] : 0;
$tipo= !empty($_GET['tipo']) ? $_GET['tipo'] : 0;
$organismo= !empty($_GET['organismo']) ? urldecode($_GET['organismo']) : null;

$capitulo= !empty($_GET['capitulo']) ? $_GET['capitulo'] : 0;
$id_tipo_evento= !empty($_GET['id_tipo_evento']) ? $_GET['id_tipo_evento'] : 0;
$like_name= !empty($_GET['like_name']) ? $_GET['like_name'] : null;
$chk_cump= !empty($_GET['chk_cump']) ? $_GET['chk_cump'] : 0;

if ($signal == 'anual_plan_audit')
    if ($tipo_plan != _PLAN_TIPO_SUPERVICION && $tipo_plan != _PLAN_TIPO_AUDITORIA)
        $tipo_plan= _PLAN_TIPO_AUDITORIA;

$monthstack= !is_null($_GET['monthstack']) ? $_GET['monthstack'] : $config->monthstack;

$obj_signal->print_reject= $print_reject;

$url_page= "../html/tablero_planning.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_calendar=$id_calendar&print_reject=$print_reject";
$url_page.= "&tipo_plan=$tipo_plan&init_row_temporary=$init_row_temporary&tipo=$tipo&origen=$origen";
$url_page.= "&monthstack=$monthstack&if_numering=$if_numering&capitulo=$capitulo&id_tipo_evento=$id_tipo_evento";
$url_page.= "&like_name=".(!empty($like_name) ? $like_name : null)."&chk_cump=$chk_cump&calendar_type=$calendar_type";
$url_page.= "&organismo=".urlencode($organismo);

set_page($url_page);

$help= null;

if ($signal == 'anual_plan' || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL) {
    $title= "PLAN ANUAL DE ACTIVIDADES";
    $tipo_plan= _PLAN_TIPO_ACTIVIDADES_ANUAL;
    $empresarial= 2;
    $help= '../help/04_plan.htm#04_6.7';
    $acc= $acc_planwork;
}
if ($signal == 'anual_plan_meeting' || $tipo_plan == _PLAN_TIPO_MEETING) {
    $title= "CRONOGRAMA DE REUNIONES";
    $tipo_plan= _PLAN_TIPO_MEETING;
    $empresarial= 2;
    $help= '../help/05_reuniones.htm#05_7';
    $acc= $acc_planwork;
}
if ($signal == 'mensual_plan' || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) {
    $title= "PLAN MENSUAL DE ACTIVIDADES";
    $tipo_plan= _PLAN_TIPO_ACTIVIDADES_MENSUAL;
    $empresarial= 1;
    $help= '../help/04_plan.htm#04_6.6';
    $acc= $acc_planwork;
}
if ($signal == 'calendar' || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $title= "PLAN DE TRABAJO INDIVIDUAL";
    $tipo_plan= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;
    $empresarial= 0;
    $help= '../help/04_plan.htm#04_6.5';
    $acc= $acc_planwork;
}
if ($tipo_plan == _PLAN_TIPO_SUPERVICION) {
    $title= "PROGRAMA ANUAL DE SUPERVISIONES";
    $td1= "ORGANIZACIONES PARTICIPANTES";
    $td2= "TIPO DE ACCIÓN";
    $td3= "RESPONSABLE";
    $legend= "Supervisión";
    $help= '../help/16_notas.htm#16_27';
    $acc= $acc_planaudit;
}
if ($tipo_plan == _PLAN_TIPO_AUDITORIA) {
    $title= "PROGRAMA ANUAL DE AUDITORIAS";
    $td1= "ORGANIZACIONES PARTICIPANTES";
    $td2= "TIPO DE AUDITORÍA";
    $td3= "JEFE DEL EQUIPO AUDITOR";
    $legend= "Auditoría";
    $help= '../help/15_auditorias.htm#15_26';
    $acc= $acc_planaudit;
}

$title_plan= $title;
$array_id_show= null;
$nums_id_show= 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <?php 
    if (!$auto_refresh_stop && (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no')) { 
        $delay= (int)$config->delay*60;
        header("Refresh: $delay; url=$url_page&csfr_token=123abc"); 
    } 
    ?>

    <title><?=$title_plan?></title>

    <?php require '../form/inc/_page_init.inc.php';?>

    <link rel="stylesheet" type="text/css" href="../css/menu.css?version=">

    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/general.css?version="> 
    <link rel="stylesheet" type="text/css" href="../css/table.css?version=">

    <link rel="stylesheet" type="text/css" href="../css/tablero.css?version="  />
    <script type="text/javascript" src="../js/tablero.js?version="></script>
    <link rel="stylesheet" type="text/css" href="../css/scheduler.css?version=" />

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>
    <script type="text/javascript" src="../js/windowcontent.js?version="></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css?version=">
    <script type="text/javascript" src="../js/widget.js?version=" charset="utf-8"></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>

    <link rel="stylesheet" href="../css/menu.css">
    <script type="text/javascript" src="../js/menu.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/time.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/form.js?version="></script>

    <?php require_once "inc/_tablero_planning_css_js.inc.php"; ?>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php require_once "inc/_tablero_planning_init.inc.php"; ?>

    <?php
    /*
    $obj->divout= 'win-log';
    $_SESSION['in_javascript_block']= false;
    $_SESSION['debug']= 'no';
    $_SESSION['trace_time']= 'yes';
    */
    if ($_SESSION['debug'] == 'yes' || $_SESSION['trace_time'] == 'yes') {
    ?>
    <!-- Div por mostrar los logs  -win-log- -->
    <div id="win-log" class="card card-danger win-log" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="panel-title ajax-title col-11 win-drag">TRAZA DEL SISTEMA</div>
                <div class="col-1">
                    <div class="close">
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('win-log');">
                            <i class="fa fa-close"></i>
                        </a>
                   </div>
                </div>
            </div>
        </div>

        <div id="body-log" class="body-log">
        </div>
    </div> <!-- win-log -->
    <?php } ?>

    <!-- Docs master nav -->
    <?php
    require_once "inc/_tablero_planning_header.inc.php";
    ?>

    <?php
    $_SESSION['in_javascript_block']= false;
    if (!empty($id_plan))
        require_once "inc/_tablero_planning_body.inc.php";
    ?>

    <?php if ($limited) { ?>
    <div class="row app-pagination d-none d-md-block" style="<?php if ($title_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {?>display: inline-block;<?php } ?>">
        <div class="toolbar">
            <div class="toolbar-center">
                <div class="center-inside">
                    <?php for ($i=0; $i < $max_num_pages; $i++) { ?>
                    <a href="javascript:refreshTab(<?=$i?>)" class="btn btn-default <?php if ($i == $init_row_temporary) echo "active"?>">
                        <?=($i+1)?>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <div class="btn-left">
                <div class="btn btn-default double">
                    <i class="fa fa-angle-double-left fa-2x"></i>
                </div>
                <div class="btn btn-default single">
                    <i class="fa fa-angle-left fa-2x"></i>
                </div>
            </div>

            <div class="btn-right">
                <div class="btn btn-default single">
                    <i class="fa fa-angle-right fa-2x"></i>
                </div>
                <div class="btn btn-default double">
                    <i class="fa fa-angle-double-right fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>


    <?php 
    $classbar= $limited ? 'fourbar' : 'fivebar';
    if (($config->use_anual_plan_organismo && $signal == 'anual_plan') 
        || ($config->use_mensual_plan_organismo && $signal == 'mensual_plan'))
        $classbar= 'ninebar';
    ?>

    <div class="app-body container-fluid <?=$classbar?> horizontal-scroll">
        <input type="hidden" id="menu" name="menu" value="anual" />
        <input type="hidden" id="signal" value="<?=$signal?>" />

        <input type="hidden" id="id_entity" name="id_entity" value="<?= $_SESSION['id_entity'] ?>" />
        <input type="hidden" id="entity_tipo" name="entity_tipo" value="<?= $_SESSION['entity_tipo'] ?>" />
        <input type="hidden" id="proceso" name="proceso" value="<?= $id_proceso ?>" />
        <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />
        <input type="hidden" id="tipo_proceso" name="tipo_proceso" value="<?= $array_procesos_entity[$id_proceso]['tipo'] ?>" />    
            
        <input type="hidden" id="day" name="day" value="<?= $day ?>" />

        <?php if ($signal === 'anual_plan' || $signal == 'anual_plan_audit' || $signal == 'anual_plan_meeting') { ?>
            <input type="hidden" id="month" name="month" value="<?= $month ?>" />
        <?php } ?>

        <input type="hidden" id="empresarial" value="<?= $empresarial ?>" />
        <input type="hidden" id="toshow" value="" />

        <input type="hidden" id="print_reject" value="<?= $print_reject ?>" />
        <input type="hidden" id="if_jefe" value="<?= $if_jefe ? '1' : '0' ?>" />
        <input type="hidden" id="init_row_temporary" value="<?= $init_row_temporary ?>" />

        <input type="hidden" id="id_calendar" value="<?= $id_calendar ?>" />
        <input type="hidden" id="id_usuario" value="<?= $_SESSION['id_usuario'] ?>" />
        <input type="hidden" id="usuario_proceso_tipo" value="<?= $array_procesos_entity[$_SESSION['usuario_proceso_id']]['tipo'] ?>" />
        <input type="hidden" id="nivel" value="<?= $_SESSION['nivel'] ?>" />
        <input type="hidden" id="acc_planwork" name="acc_planwork" value="<?= $acc_planwork ?>" />
        <input type="hidden" id="acc_planaudit" name="acc_planwork" value="<?= $acc_planaudit ?>" />
        <input type="hidden" id="tipo_plan" name="tipo_plan" value="<?= $tipo_plan ?>" />

        <input type="hidden" id="acc_planwork_user" value="" />
        <input type="hidden" id="acc_planaudit_user" value="" />
        <input type="hidden" id="id_proceso_user" value="" />
        <input type="hidden" id="tipo_proceso_user" value="" />

        <input type="hidden" id="aprobado" name="aprobado" value="<?= $date_aprb ?>" />
        <input type="hidden" id="evaluado" name="evaluado" value="<?= $date_eval ?>" />

        <input type="hidden" id="id_asignado" value="" />

        <input type="hidden" id="id_responsable" value="" />
        <input type="hidden" id="id" value="" />

        <input type="hidden" id="id_evento" value="" />
        <input type="hidden" id="id_auditoria" value="" />
        <input type="hidden" id="id_tarea" value="" />
        <input type="hidden" id="id_tematica" value="" />

        <input type="hidden" id="id_riesgo" value="0" />
        <input type="hidden" id="id_nota" value="0" />
        <input type="hidden" id="id_politica" value="0" />
        <input type="hidden" id="id_indicador" value="" />
        <input type="hidden" id="id_archivo" value="" />

        <input type="hidden" id="cumplimiento" value="" />

        <input type="hidden" id="secretary" value=""/>
        <input type="hidden" id="id_secretary" value=""/>
        <input type="hidden" id="ifmeeting" value="" />
        <input type="hidden" id="if_participant" value="0" />
        <input type="hidden" id="fecha_origen" value="" />
        <input type="hidden" id="fecha_termino" value="" />

        <input type="hidden" id="id_proyecto" value="0" />
        <input type="hidden" id="id_responsable_proyecto" value="0" />
        <input type="hidden" id="proyecto" value="" />

        <input type="hidden" id="outlook" value="" />
        <input type="hidden" id="email" value="<?=$email_user?>" />

        <input type="hidden" id="permit_change" value="<?= $permit_change ? '1' : '0' ?>" />
        <input type="hidden" id="permit_repro" value="<?= $permit_repro ? '1' : '0' ?>" />

        <input type="hidden" id="date_eval" name="date_eval" value="<?= !is_null($date_eval) ? $date_eval : "" ?>"/>

        <input type="hidden" id="monthstack" name="monthstack" value="<?=$monthstack?>" />
        <input type="hidden" id="id_proceso_asigna" name="id_proceso_asigna" value="<?=$id_proceso_asigna?>" />

        <input type="hidden" id="if_numering" value="<?=$if_numering?>" />
        <input type="hidden" id="if_synchronize" value="" />
        <input type="hidden" id="if_entity" value="" />
        <input type="hidden" id="entity_tipo_user" value="" />
        <input type="hidden" id="fixed" value="" />

        <input type="hidden" id="id_plan" value="<?=$id_plan?>" />

        <input type="hidden" id="auto_refresh_stop" value="<?=$auto_refresh_stop?>" />
        <input type="hidden" id="calendar_type" value="<?=$calendar_type?>" />

        <input type="hidden" id="tipo_actividad_flag" value="" />

        <?php
        $obj_plan->debug_time('render body');
        $obj_tipo = new Ttipo_evento($clink);
        $obj_tipo->SetYear($year);
        $obj_tipo->SetIdProceso($id_proceso_asigna);

        $obj= $signal == 'anual_plan_audit' ? new Tauditoria($clink) : new Tevento($clink);
        $obj->SetYear($year);
        $user_date_ref= !empty($month) ? $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.$lastday : date('Y-m-d');
        $obj->set_user_date_ref($user_date_ref);

        $obj_plan->copy_in_object($obj);
        $obj_plan->SetYear($year);

        $obj->SetIdProceso(null);
        $obj_signal->obj_doc= new Tdocumento($clink);

        $obj->tidx_array= $obj_plan->tidx_array;
        $obj->tidx_array_evento= $obj_plan->tidx_array_evento;
        $obj->tidx_array_auditoria= $obj_plan->tidx_array_auditoria;
        $obj->tidx_array_tarea= $obj_plan->tidx_array_tarea;

        $obj_signal->tipo_plan= $tipo_plan;
        
        $obj->set_procesos($id_proceso);
        
        if ($signal == 'anual_plan' && !empty($id_plan)) {
            $obj->SetIdProceso($id_proceso);

            if ($config->use_anual_plan_organismo && $id_proceso == $_SESSION['local_proceso_id'])
                include_once "inc/_anual_plan_oaces.php";
            else
                include_once "inc/_anual_plan.php"; 
        }

        if ($signal == 'mensual_plan' && !empty($id_plan)) {
            $obj->SetIdProceso($id_proceso);
            if ($monthstack) {
                if (!$config->grouprows) {
                    if ($config->use_mensual_plan_organismo && $id_proceso == $_SESSION['local_proceso_id'])
                        include_once "inc/_mensual_plan_stack_oaces.php";
                    else
                        include_once "inc/_mensual_plan_stack.php";    
                }
                else
                    include_once "inc/_mensual_plan_stack_gearh.php";
            } else {
                if (!$config->grouprows) {
                    if ($config->use_mensual_plan_organismo && $id_proceso == $_SESSION['local_proceso_id'])
                        include_once "inc/_mensual_plan_oaces.php";
                    else
                        include_once "inc/_mensual_plan.php";   
                } 
                else
                    include_once "inc/_mensual_plan_gearh.php";
        }   }

        if ($signal == 'calendar' && !empty($id_plan)) {
            if (empty($calendar_type))
                include_once "inc/_calendar.php";
            if ($calendar_type == 1)
                include_once "inc/_calendar_week.php";
            if ($calendar_type == 2)
                include_once "inc/_calendar_day.php";
        }
        
        if ($signal == 'anual_plan_audit' && !empty($id_plan)) {
            $obj->SetIdProceso($id_proceso);
            reset($array_procesos);
            include_once "inc/_anual_plan_audit.php";
        }
        if ($signal == 'anual_plan_meeting' && !empty($id_plan)) {
            $obj->SetIdProceso($id_proceso);
            include_once "inc/_anual_plan_meeting.php";
        }
        $obj->debug_time('render body');
        ?>

        <?php if (empty($id_plan)) { ?>
            <div class="col-12">
                <div class="alert alert-danger col-12 mt-2">
                    USTED NO TIENE ACCESO A NINGÚN PLAN GENERAL. DEBE CONSULTAR AL ADMINISTRADOR DEL SISTEMA.
                </div>                
            </div>
        <?php } ?>

    </div>
    <!-- Panel -->

    <!-- Beguin Panel Filter-->
    <div id="panel-filter" class="card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row clear win-drag">
                <div class="panel-title col-11 win-drag">FILTRAR TAREAS</div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('panel-filter');" title="cerrar ventana">
                           <i class="fa fa-close"></i>
                       </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body form">
            <div class="form-horizontal">
                <?php if ($signal == 'anual_plan_audit') { ?>
                <div class="form-group row">
                    <label class="col-form-label col-2">Origen:</label>
                    <div class=" col-xs-10 col-sm-10 col-md-9 col-lg-9">
                        <select id="origen" name="origen" class="form-control">
                            <option value="0">...</option>
                            <?php for ($i = 1; $i < _MAX_TIPO_NOTA_ORIGEN; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $origen) echo "selected='selected'"; ?> ><?= $Ttipo_nota_origen_array[$i] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">Tipo:</label>
                    <div class=" col-xs-10 col-sm-10 col-md-9 col-lg-9">
                        <select id="tipo" name="tipo" class="form-control">
                            <option value="0">...</option>
                            <?php for ($i = 1; $i <= _MAX_TIPO_AUDITORIA; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $tipo) echo "selected='selected'"; ?> ><?= $Ttipo_auditoria_array[$i] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">Entidad Auditora:</label>
                    <div class=" col-10">
                        <input type="text" id="organismo" name="organismo" class="form-control" value="<?= $organismo ?>">
                    </div>
                </div>

                <?php } else { ?>

                <div class="checkbox">
                    <label>
                         <input type="checkbox" id="chk_cump" name="chk_cump" value="1">
                         Mostrar el estado de cumplimiento de las tareas y Actividades. Hace la carga del Plan de Actividades más lento.
                     </label>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-3">Clasificación de la Actividad:</label>
                    <div class="col-9">
                        <select id="tipo_actividad1" name="tipo_actividad1" class="form-control">
                            <option value="0">...</option>
                            <?php for ($i= 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) { ?>
                                <option value="<?=$i?>" <?php if ($i == $capitulo) echo "selected='selected'" ?>><?=number_format_to_roman($i-1).'. '.$tipo_actividad_array[$i] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-sm-2 col-md-3 col-lg-3">Sub-clasificación:</label>
                    <div class="col-sm-11 col-sm-10 col-md-9 col-lg-9">
                        <div class="ajax-select" id="ajax-tipo-evento">
                            <select id="tipo_actividad2" name="tipo_actividad2" class="form-control">
                                <option value="0" selected="selected">...</option>
                           </select>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-3 col-lg-2">Contiene el texto:</label>
                    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-10">
                        <input type="text" id="like_name" name="like_name" class="form-control" value="<?=$like_name?>">
                    </div>
                </div>

                <?php } ?>
            </div>

            <div class="btn-block btn-app">
                <button type="button" class="btn btn-primary" onclick="filtrar(1)">Filtrar</button>
                <button type="button" class="btn btn-warning" onclick="filtrar(0)">Limpiar</button>
                <button type="button" class="btn btn-warning" onclick="CloseWindow('panel-filter')">Cerrar</button>
            </div>
        </div>
    </div> <!-- Beguin Panel Filter-->


    <?php
    $win= null;
     $text_programing= "reprogramar en el mes";

    if ($signal == 'mensual_plan')
        $win= 'm_';
    if ($signal == 'anual_plan' || $signal == 'anual_plan_meeting') {
        $win = 'y_';
        $text_programing = "copiar para el próximo año";
    }
    if ($signal == 'anual_plan_audit') {
        $win = 'y_audit_';
        $text_programing = "copiar para el próximo año";
    }
?>


    <div id="win-board-signal" class="card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="signal-title" class="panel-title ajax-title col-md-11 m-0 win-drag"></div>
                <div class="col-md-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('win-board-signal');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>    
            </div>
        </div>

        <div class="card-body">
            <?php if ($signal != "mensual_plan" || ($signal == "mensual_plan" && !$monthstack)) { ?>
                <div id="win-board-signal-icons" class="btn-toolbar btn-block list-group-horizontal">
                    <?php if ($if_jefe || !empty($acc) || $signal == 'calendar') { ?>
                    <a id="img_print_tematica" class="btn btn-app btn-success btn-sm d-none d-lg-inline-block" onclick="imprimir_matter('matter')" title="Imprimir el Plan Temático del año">
                        <i class="fa fa-print"></i>Plan Temático
                    </a>

                    <a id="img_print_debate" class="btn btn-app btn-success btn-sm d-none d-lg-inline-block" onclick="imprimir_matter('debate')" title="Imprimir todas las Intervenciones">
                        <i class="fa fa-print"></i>Intervenciones
                    </a>

                    <a id="img_print_acuerdo" class="btn btn-app btn-success btn-sm d-none d-lg-inline-block" onclick="imprimir_matter('accords')" title="Imprimir todos los Acuerdos. Todo el año">
                        <i class="fa fa-print"></i>Acuerdos
                    </a>

                    <a id="img_tematica" class="btn btn-app btn-primary btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('matter')" title="Temáticas del Orden del día">
                        <i class="fa fa-briefcase"></i>Temáticas
                    </a>

                    <a id="img_debate" class="btn btn-app btn-primary btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('debate')" title="Intervenciones en la reunión">
                        <i class="fa fa-comments"></i>Intervenciones
                    </a>

                    <a  id="img_acuerdo" class="btn btn-app btn-primary btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('accords')" title="Acuerdos de la reunión">
                        <i class="fa fa-legal"></i>Acuerdos
                    </a>

                    <a id="img_dox" class="btn btn-app btn-primary btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('docs')" title="Gestión de documentos y actas adjuntos">
                        <i class="fa fa-file-text"></i>Documentos
                    </a>

                    <a  id="img_assist" class="btn btn-app btn-primary btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('assist')"  title="Asistencia a la reunión">
                        <i class="fa fa-male"></i>Asistencia
                    </a>

                    <?php if ($signal != 'anual_plan') { ?>
                    <a id="img_register" class="btn btn-app btn-primary btn-sm d-inline-block" onclick="<?=$win?>show2mostrar('register')" title="registrar situación o cumplimiento">
                        <i class="fa fa-check"></i>Registrar
                    </a>
                    <?php } ?>

                    <?php if ($signal == 'calendar') { ?>
                    <a id="img_reject" class="btn btn-app btn-warning btn-sm d-inline-block" onclick="<?=$win?>show2mostrar('reject')" title="rechazar/suspender/eliminar de este plan">
                        <i class="fa fa-power-off"></i>Rechazar
                    </a>
                    <?php } ?>
                    <?php } ?>

                    <?php if ($permit_repro) { ?>
                    <a id="img_copy" class="btn btn-app btn-warning btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('copy')" title="<?="Copiar al ".($year+1)?>">
                        <i class="fa fa-copy"></i><?="Copiar al ".($year+1)?>
                    </a>

                    <a id="img_repro" class="btn btn-app btn-warning btn-sm d-inline-block" onclick="<?=$win?>show2mostrar('repro')" title="Reprogramar">
                        <i class="fa fa-clock-o"></i>Reprogramar
                    </a>

                    <a id="img_delegate" class="btn btn-app btn-warning btn-sm d-inline-block" onclick="<?=$win?>show2mostrar('delegate')" title="Delegar">
                        <i class="fa fa-hand-o-down"></i><?=$tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL ? "Delegar" : "Modificar"?>
                    </a>
                    <?php } ?>

                    <a id="img_edit" class="btn btn-app btn-warning btn-sm d-none d-md-inline-block" onclick="<?=$win?>show2mostrar('edit')" title="editar/modificar">
                        <i class="fa fa-edit"></i>Editar
                    </a>
                    <a id="img_delete" class="btn btn-app btn-danger btn-sm d-inline-block" onclick="<?=$win?>show2mostrar('delete')" title="eliminar">
                        <i class="fa fa-trash"></i>Eliminar
                    </a>
                </div>
            <?php } ?>

            <div class="row d-md-flex d-flex-horizontal d-none d-md-block">
                <div class="col-1">
                    <div id="_status" class="rotate-90">
                    </div>                   
                </div>

                <div class="col-11">
                    <div class="list-group">
                        <div class="list-group-item">
                            <strong>Lugar</strong>: <label class="text" id="_lugar"></label>
                        </div>
                        <div class="list-group-item">
                            <strong>Fecha y hora referenciada</strong>: <label class="text" id="_fecha"></label>
                        </div>
                        <div class="list-group-item">
                            <strong>Responsable</strong>: <label class="text" id="_responsable"></label>
                        </div>
                        <div class="list-group-item">
                            <strong>Asigna</strong>: <label class="text" id="_asignado"></label>
                        </div>
                        <div class="list-group-item" id="div_secretary">
                            <strong>Secretario(a)</strong>: <label class="text" id="_secretary"></label>
                        </div>
                    </div>
                    <div class="col-12">
                        <strong>Descripción</strong>: <p id="_descripcion"></p>
                    </div>   
                </div>
            </div>
        </div>
    </div>


    <div id="win-board-signal-project" class="card card-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div id="win-ptitle" class="panel-title ajax-title col-11 win-drag"></div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href= "javascript:CloseWindow('win-board-signal-project');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="win-board-signal-project-icon" class="btn-toolbar">

                <a id="img_dox" class="btn btn-app btn-primary btn-sm d-md-inline-block" onclick="<?=$win?>show2mostrar('docs')" title="Gestión de documentos y actas adjuntos">
                    <i class="fa fa-file-text"></i>Documentos
                </a>

                <a class="btn btn-app btn-primary btn-sm d-md-inline-block" onclick="p_show2mostrar('hour')" title="registrar registrar situación o cumplimiento y horas dedicadas">
                    <i class="fa fa-check"></i>Registrar
                </a>

                <a class="btn btn-app btn-primary btn-sm d-md-inline-block" onclick="p_show2mostrar('advance')" title="registrar % de avance en la tarea">
                    <i class="fa fa-percent"></i>% de avanace
                </a>

                <a class="btn btn-app btn-danger btn-sm d-md-inline-block" onclick="p_show2mostrar('delete')" title="eliminar">
                    <i class="fa fa-trash"></i>Eliminar
                </a>
            </div>

            <div class="row">
                <div id="p_status" class="rotate-90 col-lg-1 col-md-1 col-sm-2 col-xs-2">

                </div>
                <div class="col-lg-11 col-md-11 col-sm-10 col-xs-10">
                    <div class="list-group">
                        <label class="text" id="p_lugar"></label><br/>
                        <strong>Proyecto</strong>: <label class="text" id="p_proyecto"></label><br/>
                        <strong>Responsable</strong>: <label class="text" id="p_responsable"></label><br/>
                        <strong>Asigna</strong>: <label class="text" id="p_asignado"></label>
                    </div>
                    <label>Descripción</label>: <p id="p_descripcion"></p>
                </div>
            </div>
        </div>
    </div>


    <div id="bit" class="loggedout-follow-normal d-none d-md-block">
        <a class="bsub" href="javascript:void(0)"><span id="bsub-text">Leyenda</span></a>

        <div id="bitsubscribe">
            <div class="row">
                <div class="col-md-4 col-lg-4">
                    <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img src="../img/sq-blank.ico" />
                            No iniciada
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/sq-yellow.ico" />
                            En curso/ejecutándose
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/sq-green.ico" />
                            Completado/cumplido
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/sq-orange.ico" />
                            A la espera o detenido por tiempo definido
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/sq-red.ico" alt="" />
                            Pospuesto o suspendido por tiempo indefinido
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/sq-dark.ico" />
                            Actividad o tarea incumplida
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/sq-grey.ico" title="evento o tarea rechazada" />
                            Actividad o tarea rechazada o delegada
                        </li>
                    </ul>
                </div>

                <div class="col-md-4 col-lg-4">
                    <ul class="list-group-item item">
                         <li class="list-group-item item">
                            <?php if ($signal != 'anual_plan') { ?>
                                <img src='../img/process.ico' title='incluida en el Plan General de Actividades' />
                                Incluida en el Plan General
                            <?php } ?>
                         </li>
                        <li class="list-group-item item">
                            <img src='../img/user-comment.ico' title='asignada por alguin que no es su inmediato superior' />
                            No asignada por el Jefe inmediato superior
                        </li>
                        <li class="list-group-item item">
                            <img src='../img/user-go.ico' title='asignada por el jefe inmediato' />
                            Asignada por el Jefe inmediato superior
                        </li>
                        <li class="list-group-item item">
                            <img src='../img/accept.ico' title='aprobada por el jefe' />
                            Aprobada por el Jefe
                        </li>
                        <li class="list-group-item item">
                            <i class="fa fa-fire"></i>
                            Auditoría o acción de control
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/tematica.ico" alt="El usuario no participa" />
                            Acuerdo de una reunión, Consejo de dirección, asamblea, etc
                        </li>
                        <li class="list-group-item item">
                            <img src='../img/meeting.ico' title='es una reunión, asamblea, consejo o consejillo' />
                            Incluida en el Programa de Reuniones
                        </li>
                        <li class="list-group-item item">
                            <img src='../img/archive.ico' title='es una indicación emitida desde la gestión de archivos' />
                            Indicación recibida a través de la oficina de Archivo o del Despacho
                        </li>
                    </ul>
                </div>

                <div class="col-md-4 col-lg-4">
                     <ul class="list-group-item item">
                        <li class="list-group-item item">
                            <img src='../img/docx-mac.ico' title='tiene algún documento o archivo adjunto' />
                            Tiene documentos o archivos adjuntos
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/icon-eye.ico" alt="El usuario no participa" />
                            Es responsable de la actividad pero NO participa. O actividad que nadie ejecuta "fantasma"
                        </li>
                        <li class="list-group-item item">
                            <img src="../img/monitor-edit.ico" alt="tarea generada por Diriger" />
                            Planificada desde (Planes de Prevención de Riesgos, Planes de Medidas o de Acciones, Proyectos, etc)
                        </li>
                        <li class="list-group-item item">
                            <img src='../img/hour-add.ico' title='es el resultado de una reprogramación (puntualización)' />
                            Puntualización o resultado de una reprogramación
                        </li>
                        <li class="list-group-item item">
                            <img src='../img/transmit.ico' title='recivido desde servidor remoto' />
                            Recibido desde servidor remoto (por sincronización)
                        </li>
                    </ul>
                </div>
            </div>
        </div><!-- #bitsubscribe -->
    </div><!-- #bit -->


    <div id="info-panel-plan" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title col-11 m-0 win-drag">
                    Estado del Plan
                </div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="#" onclick="CloseWindow('info-panel-plan')">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body info-panel">
            <div class="list-group">
                <a href="#" class="list-group-item">
                    <legend>Aprobación</legend>

                    <p><strong>Aprueba: </strong><?php if (!is_null($array_aprb)) echo $array_aprb['nombre'].', '.textparse($array_aprb['cargo'])?></p>
                    <p><strong>En fecha: </strong><?=odbc2time_ampm($date_aprb)?></p>
                    <strong>Tareas principales:</strong>
                    <p><?= textparse($objetivos, false)?></p>
                </a>

                <?php if ($signal != 'anual_plan_audit') { ?>
                    <a href="#" class="list-group-item">
                        <legend>Auto-evaluación</legend>

                        <p><strong>Evalua: </strong><?php if (!is_null($array_auto_eval)) echo $array_auto_eval['nombre'].', '.textparse($array_auto_eval['cargo']) ?></p>
                        <p><strong>En fecha: </strong><?=odbc2time_ampm($date_auto_eval)?></p>

                        <p><?= textparse($auto_evaluacion, false)?></p>
                    </a>
                    <a href="#" class="list-group-item">
                        <legend>Evaluacion</legend>
                        <p><strong>Evalua: </strong><?php if (!is_null($array_auto_eval)) echo $array_eval['nombre'].', '.textparse($array_eval['cargo']) ?></p>
                        <p><strong>En fecha: </strong><?=odbc2time_ampm($date_eval)?></p>
                        <p><strong>Evaluación: </strong><?php if (!empty($date_eval)) echo $evaluacion_array[$cumplimiento] ?></p>

                        <p><?= textparse($evaluacion, false)?></p>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>


    <div id='div-ajax-panel' class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag"></div>
                <div class="col-1 m-0">
                    <div class='close'>
                        <a href="#" title="cerrar ventana" onclick="CloseWindow('div-ajax-panel');">
                            <i class="fa fa-close"></i>
                        </a>
                   </div>
                </div>
            </div>
        </div>
        <div id='div-ajax' class='card-body info-panel'>
        </div>
    </div>

    
    <script type="text/javascript">
        document.getElementById('nshow').innerHTML= <?=$obj_signal->cant_show?>;
        document.getElementById('nhide').innerHTML= <?=$obj_signal->cant_print_reject?>;
        /*
        <?php if ($obj_signal->cant_print_reject > 0) { ?>
        alert("Existen tareas ocultas en el Plan. Por favor, para verlas seleccione la opción mostrar en la barra superior");
        <?php } ?>
        */

        if (parseInt($('#permit_change').val())) {
            document.getElementById("win-board-signal-project-icon").style.display= "block";
            try {
                document.getElementById("win-board-signal-icons").style.display= "block";
            } catch(e) {;}
        }
    </script>

    <input type="hidden" id="nums_id_show" name="nums_id_show" value="<?=$nums_id_show?>"/>
    <input type="hidden" id="array_id_show" name="array_id_show" value="<?=$array_id_show?>"/>

</body>
</html>