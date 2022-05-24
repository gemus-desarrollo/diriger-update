<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/riesgo.class.php";
require_once "../php/class/nota.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";

require_once "../php/class/inductor.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$signal= !empty($_GET['signal'])?  $_GET['signal'] : "ftarea";
$action= !empty($_GET['action']) ? $_GET['action'] : "list";
$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : 0;
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : 0;

if ($action == 'add') {
    if (isset($_SESSION['obj'])) 
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Ttarea($clink);
}

$toshow= !is_null($_GET['toshow']) ? $_GET['toshow'] : $obj->get_toshow_plan();
if (is_null($toshow)) 
    $toshow= 0;
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : $obj->GetIdPrograma();
if (empty($id_programa)) 
    $id_programa= 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : $obj->GetIdProyecto();
if (empty($id_proyecto)) 
    $id_proyecto= 0;
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $obj->GetIdResponsable();
if (empty($id_responsable)) 
    $id_responsable= 0;

$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $year == $_SESSION['current_year'] ? $_SESSION['current_month'] : date('m');

$time= new Ttime();
if (empty($year)) 
    $year= $time->GetYear();
if (empty($month)) 
    $month= $time->GetMonth();
$init_year= $year - 1;
$end_year= $year + 1;

$id_tarea= $obj->GetIdTarea();
if (empty($id_tarea)) 
    $id_tarea= 0;

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$ifgrupo= null;
$id_tarea_grupo= null;

$ifgrupo= !is_null($_GET['ifgrupo']) ? $_GET['ifgrupo'] : $obj->GetIfGrupo();
if (is_null($ifgrupo) || empty($id_proyecto)) 
    $ifgrupo= 0;  
$id_tarea_grupo= !empty($_GET['id_tarea_grupo']) ? $_GET['id_tarea_grupo'] : $obj->GetIdTarea_grupo();
if (empty($id_tarea_grupo))
    $id_tarea_grupo= null;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
if ($obj_prs->GetTipo() == _TIPO_PROCESO_INTERNO) 
    $id_proceso= $obj_prs->GetIdProceso_sup();

$planning= ($action == 'add') ? 'false' : 'true';
$ifassure= !empty($_GET['ifassure']) ? urldecode($_GET['ifassure']) : $obj->GetIfAssure();

$fecha_origen= !empty($_GET['fecha_origen']) ? time2odbc(urldecode($_GET['fecha_origen'])) : null;
if (!empty($fecha_origen)) 
    $init_year= date('Y', strtotime($fecha_origen));
$fecha_inicio= !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : $obj->GetFechaInicioPlan();
if (empty($fecha_inicio) && !empty($fecha_origen))  
    $fecha_inicio= $fecha_origen;
$fecha_inicio= !empty($fecha_inicio) ? time2odbc($fecha_inicio) : date('Y').'-01-01 08:30';

$fecha_termino= !empty($_GET['fecha_termino']) ? time2odbc(urldecode($_GET['fecha_termino'])) : null;
if (!empty($fecha_termino)) 
    $end_year= date('Y', strtotime($fecha_termino));
$fecha_fin= !empty($_GET['fecha_fin']) ? urldecode($_GET['fecha_fin']) : $obj->GetFechaFinPlan();
if (empty($fecha_fin) && !empty($fecha_termino)) 
    $fecha_fin= $fecha_termino;
$fecha_fin= !empty($fecha_fin) ? time2odbc($fecha_fin) : date('Y').'-12-31 17:30';

$year= date('Y', strtotime($fecha_inicio));

$year= !empty($_GET['year']) ? $_GET['year'] : $year;
if ($year > date('Y')) {
    $fecha_inicio= !empty($fecha_inicio) ? $fecha_inicio : "$year-01-01 07:30";
    $fecha_fin= !empty($fecha_fin) ? $fecha_fin : "$year-12-31 11:59";
}

$init_year= $year;
$end_year= $year;

$obj_proy= new Tproyecto($clink);

if (!empty($id_proyecto)) {
    $tipo_plan= _PLAN_TIPO_PROYECTO;

    $obj_proy->Set($id_proyecto);
    $id_programa= $obj_proy->GetIdPrograma();
    $fecha_origen= $obj_proy->GetFechaInicioPlan();
    $fecha_termino= $obj_proy->GetFechaFinPlan();
}
if (!empty($id_nota)) {
    $tipo_plan= _PLAN_TIPO_AUDITORIA;

    $obj_tmp= new Tnota($clink);
    $obj_tmp->Set($id_nota);
    $fecha_origen= $obj_tmp->GetFechaInicioReal();
    $fecha_termino= $obj_tmp->GetFechaFinPlan();
    unset($obj_tmp);
}
if (!empty($id_riesgo)) {
    $tipo_plan= _PLAN_TIPO_PREVENCION;
    $obj_tmp= new Triesgo($clink);
    $obj_tmp->Set($id_riesgo);
    $fecha_origen= $obj_tmp->GetFechaInicioPlan();
    $fecha_termino= $obj_tmp->GetFechaFinPlan();
    unset($obj_tmp);
}

$init_date= !empty ($fecha_origen) ? date("d/m/Y", strtotime ($fecha_origen)) : "01/01/$init_year";
$end_date= !empty ($fecha_termino) ? date("d/m/Y", strtotime ($fecha_termino)) : "01/01/$end_year";

$_radio_date= !is_null($_GET['_radio_date']) ? $_GET['_radio_date'] : 3;

$days= date_diff(date_create($fecha_inicio), date_create($fecha_fin));
$days= $days->format('%a');

$periodicidad= !is_null($_GET['periodicidad']) ? $_GET['periodicidad'] : $obj->GetPeriodicidad();
if (empty($periodicidad)) 
    $periodicidad= 0;
if ($days > 1 && $periodicidad == 0) 
    $periodicidad= 1;

$carga= !is_null($_GET['carga']) ? $_GET['carga'] : $obj->GetCarga();
if (empty($carga)) 
    $carga= null;

$dayweek= !is_null($_GET['dayweek']) ? $_GET['dayweek'] : $obj->GetdayWeek();
if (empty($dayweek)) 
    $dayweek= null;

$saturday= !is_null($_GET['saturday']) ? $_GET['saturday'] : $obj->saturday;
$sunday= !is_null($_GET['sunday']) ? $_GET['sunday'] : $obj->sunday;
$freeday= !is_null($_GET['freeday']) ? $_GET['freeday'] : $obj->freeday;

$chk_date= !is_null($_GET['chk_date']) ? $_GET['chk_date'] : $obj->GetChkDate();
$chk_date= is_null($chk_date) ? 1 : $chk_date;

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger();
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_origen);

if (!empty($id_nota))
    $badger->set_planheal();
elseif (!empty($id_riesgo))
    $badger->set_planrisk();
else
    $badger->set_planwork();

$if_jefe= false;
if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['freeassign'] || $config->freeassign)
    $if_jefe= true;
if (!empty($id_nota) && $_SESSION['acc_planheal'] == _ACCESO_ALTA || $_SESSION['acc_planaudit'] == _ACCESO_ALTA)
    $if_jefe= true;
if (!empty($id_riesgo) && $_SESSION['acc_planrisk'] == _ACCESO_ALTA)
    $if_jefe= true;
if (!empty($id_proyecto) && $_SESSION['acc_planproject'] == _ACCESO_ALTA)
    $if_jefe= true;

if (!empty($id_responsable)) {
    $obj_user= new Tusuario($clink);
    $obj_user->SetIdUsuario($id_responsable);
    $obj_user->Set();
    $id_proceso= $obj_user->GetIdProceso();
    $id_proceso_code= $obj_user->get_id_proceso_code();
}

$array_procesos_init= null;

if (!empty($id_proyecto)) {
    $obj_prs= new  Tproceso_item($clink);
    $obj_prs->SetIdProyecto($id_proyecto);
    $array_procesos_init= $obj_prs->GetProcesosProyecto();
}
if (!empty($id_nota) || !empty($id_riesgo)) {
    $obj_prs= new  Tproceso_item($clink);
    $obj_prs->SetIdNota($id_nota);
    $obj_prs->SetIdRiesgo($id_riesgo);
    $array_procesos_init= $obj_prs->GetProcesosRiesgo();    
}

$url_page= "../form/ftarea.php?signal=$signal&action=$action&menu=tarea&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_riesgo=$id_riesgo&id_nota=$id_nota&id_proyecto=$id_proyecto";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>TAREAS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../libs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8"
        src="../libs/multiselect/multiselect.js?version="></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <link href="../libs/year-calendar/year-calendar.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/year-calendar/year-calendar.js"></script>

    <link href="../libs/combobox/jquery-combobox.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/combobox/jquery-combobox.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script type="text/javascript" src="../js/general.js?version="></script>
    <script type="text/javascript" src="../js/time.js?version="></script>
    <script type="text/javascript" src="../js/calendar.js?version="></script>
    <script type="text/javascript" src="../js/tipo_evento.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    var text_form = 'de la tarea o actividad';
    var tipo_form = 'evento';

    function valida_date(flag) {
        var form = document.forms['ftarea'];

        var fecha_inicio = $('#fecha_inicio').val() + ' ' + $('#hora_inicio').val();
        var fecha_fin = $('#fecha_fin').val() + ' ' + $('#hora_fin').val();
        var text;

        try {
            id_proyecto = $('#proyecto').val();
            id_riesgo = $('#id_riesgo').val();
            id_nota = $('#id_nota').val();
        } catch (e) {
            id_proyecto = 0;
        }

        var fecha_inicio = $('#fecha_inicio').val() + ' ' + $('#hora_inicio').val();
        var fecha_fin = $('#fecha_fin').val() + ' ' + $('#hora_fin').val();

        var name = null;
        if (id_proyecto > 0)
            name = 'del proyecto';
        if (id_nota > 0)
            name = 'registro de la Nota de Hallazgo';

        if (flag == 1) {
            if (DiferenciaFechas(fecha_inicio, $('#fecha_origen').val(), 'd') < 0) {
                $('#fecha_inicio').focus(focusin($('#fecha_inicio')));

                if (id_proyecto > 0) {
                    text = "Hay incongruencia en las fechas. La fecha de inicio de la tarea es anterior a la ";
                    text += "fecha de inicio del proyecto. Será asignada la fecha de inicio del proyecto a la tarea.";
                }
                if (id_nota > 0) {
                    text = "Hay incongruencia en las fechas. La fecha de inicio de la tarea es anterior a la ";
                    text += "fecha de inicio registro de la Nota de Halazgo.";
                }
                if (id_riesgo > 0) {
                    text = "Hay incongruencia en las fechas. La fecha de inicio de la tarea es anterior a la ";
                    text += "fecha de identificación del riesgo.";
                }

                alert(text);
                $('#fecha_inicio').val($('fecha_inicio').val());
                return false;
            }
        }

        if (flag == 2) {
            if (DiferenciaFechas(fecha_fin, $('#fecha_termino').val(), 'd') > 0) {
                $('#fecha_final').focus(focusin($('#fecha_final')));

                if (id_proyecto > 0) {
                    text = "Hay incongruencia en las fechas. La fecha de culminación de la tarea es posterior a la ";
                    text +=
                        "fecha de culminación del proyecto. Será asignada la fecha de culminación del proyecto a la tarea.";
                }
                if (id_nota > 0) {
                    text = "Hay incongruencia en las fechas. La fecha de culminación de la tarea es posterior a la ";
                    text +=
                        "fecha planificada para el cierre de la Nota de hallazgo. Será asignada la fecha de cierre de la Nota de Hallazgo.";
                }
                if (id_riesgo > 0) {
                    text = "Hay incongruencia en las fechas. La fecha de culminación de la tarea es posterior a la ";
                    text += "fecha planificada para la eliminación, mitigación del riesgo o cierre de la nota.";
                }

                alert(text);
                $('#fecha_final').val($('fecha_final').val());
                return false;
            }
        }

        if (flag == 3) {
            if (DiferenciaFechas(fecha_fin, $('#fecha').val(), 'd') < 0) {
                text =
                    "Hay incongruencia en la fecha del hito planificado. La fecha del hito planificado no puede ser posterior a la ";
                text += "fecha de culminación de la tarea.";
                alert(text);
                $('#fecha').val("");
                return false;
            }

            if (DiferenciaFechas(fecha_inicio, $('#fecha').val(), 'd') > 0) {
                text =
                    "Hay incongruencia en la fecha del hito planificado. La fecha del hito planificado no puede ser inferior a la ";
                text += "fecha de inicio de la tarea.";
                alert(text);
                $('#fecha').val("");
                return false;
            }
        }

        return true;
    }

    var planning = <?=$planning ?>;

    function validar() {
        var form = document.forms[0];
        var ifgrupo= $('#ifgrupo0').is(':checked') ? true : false;

        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el título o nombre de la tarea');
            return;
        }
        if ($('#signal').val() == 'fproyecto' && (!$('#ifgrupo0').is(':checked') && !$('#ifgrupo1').is(':checked'))) {
            $('#ifgrupo0').focus(focusin($('#ifgrupo0')));
            alert(
                'Especifique sí se trata de un nuevo grupo de tareas. Es decir, sí esta tarea contendrá a otras tareas(subtareas)?');
            return;
        }
        if ($('#ifproyecto').is(':checked') && ($('#programa').val() == 0 && $('#id_proyecto').val() == 0)) {
            $('#programa').focus(focusin($('#programa')));
            alert('Selecione el programa al que pertenece la tarea.');
            return;
        }
        if (!Entrada($('#custom-combobox-responsable').val())) {
            $('#responsable').focus(focusin($('#responsable')));
            alert('Selecione el responsable de la ejecución de la tarea');
            return;
        }

        if (!validar_interval(true))
            return;

        if (!ifgrupo && ($('#periodicidad1').is(':checked') && (!Entrada($('#input_carga1').val()) 
            || (Entrada($('#input_carga1').val()) && parseInt($('#input_carga1').val()) == 0)))) {
            $('#input_carga1').focus(focusin($('#input_carga1')));
            alert(
                "Usted ha escogido una periodicidad diaria. Debe especificar la frecuencia en días con que se ejecutará la tarea.");
            return;
        }
        if (!ifgrupo && $('#periodicidad2').is(':checked')) {
            check = false;
            for (i = 1; i < 8; ++i) {
                if ($("#dayweek" + i).is(':checked')) {
                    check = true;
                    break;
                }
            }

            if (!check) {
                $('#input_carga1').focus(focusin($('#input_carga1')));
                alert(
                    "Usted ha escogido la periodicidad semanal. Debe seleccionar los días de la semana en los que se trabajará.");
                return;
            }
        }
        if (!ifgrupo && $('#periodicidad3').is(':checked')) {
            if (!Entrada($('#input_carga4').val()) && ($('#sel_carga').val() == 0 || $('#dayweek0').val() == 0)) {
                $('#input_carga4').focus(focusin($('#input_carga4')));
                alert("Usted ha escogido la periodicidad mensual. Debe especificar la frecuencia de días en el mes.");
                return false;
            }
        }
        if (!ifgrupo && $('#periodicidad4').is(':checked')) {
            if (create_chain() == 0) {
                $('#periodicidad4').focus(focusin($('#periodicidad4')));
                alert("No ha especificados los días en que se trabajará en la tarea");
                return;
            }
        }

        $('#id_programa').val($('#programa').val());

        if (parseInt($('#no_reject').val())) {
            text =
            "Hay actividades que ya están aprobadas o registradas como cumplidas en el los Planes Individuales. ";
            text +=
                "Al cambiar las fechas y reprogramar se perderan la información asociada. Desea realmente eliminarlas y perder esta información?";

            confirm(text, function(ok) {
                if (ok) {
                    $('input-calendar-go_delete').val(1);
                    _this();
                } else {
                    $('input-calendar-go_delete').val(0);
                    _this();
                }
            });
        } else {
            _this();
        }

        function _this() {
            document.forms[0].action = '../php/tarea.interface.php';

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            document.forms[0].submit();
        } // _this()
    }

    function refreshpry() {
        if ($('#ifproyecto').is(':checked')) {
            <?php if ($action != 'update' && !empty($id_proyecto)) { ?>
            $('#tr_ifgrupo0').show();
            <?php } ?>

            $('#tr_programa').show();
            $('#tr_proyecto').show();
            $('#programa').prop('disabled', false);
            try {
                $('#proyecto').prop('disabled', false);
            } catch (e) {
                ;
            }
        } else {
            $('#tr_ifgrupo0').hide();
            $('#tr_programa').hide();
            $('#tr_proyecto').hide();
            $('#programa').prop('disabled', true);
            try {
                $('#proyecto').prop('disabled', true);
            } catch (e) {
                ;
            }
        }
    }

    function usuario_tabs_ajax() {
        <?php
        $id= $id_tarea;
        $user_ref_date= $fecha_fin;
        $restrict_prs= !empty($id_riesgo) ? array(_TIPO_PROCESO_INTERNO) : null;
        ?>

        var user_date_ref = $('#fecha_inicio').val();

        $.ajax({
            data: {
                "signal": "tarea",
                "id_tarea": <?=!empty($id) ? $id : 0?>,
                "tipo_plan": <?=$tipo_plan ? $tipo_plan : 0?>,
                "year": <?=!empty($year) ? $year : date('Y')?>,
                "user_ref_date": '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                "id_user_restrict": <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                "restrict_prs": <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                "use_copy_tusuarios": <?=$use_copy_tusuarios ? $use_copy_tusuarios : 0?>,
                /*
                "array_usuarios" : <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                "array_grupos" : <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>,
                */
                "if_jefe": <?=$if_jefe ? 1 : 0?>,
                "user_date_ref": user_date_ref
            },
            url: 'ajax/usuario_tabs.ajax.php',
            type: 'post',
            beforeSend: function() {
                $("#ajax-tab-users").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#ajax-tab-users").html(response);
            }
        });
    }

    function set_toshow() {
        <?php 
        $ifgrupo_id= ($action == 'add' && !empty($id_proyecto)) ? "$('#ifgrupo0').is(':checked')" : "parseInt($('#ifgrupo').val())";
        ?>        
        if (<?=$ifgrupo_id?>) {
            $('#div-toshow').hide();            
        } else {
            $('#div-toshow').show(); 
        }
    }

    function set_ifgrupo() {
        <?php 
        $ifgrupo_id= ($action == 'add' && !empty($id_proyecto)) ? "$('#ifgrupo1').is(':checked')" : "!parseInt($('#ifgrupo').val())";
        ?>
        if (<?=$ifgrupo_id?>) {
            $('#nav-tab3').show();
            $('#nav-tab5').show();
            $('#div-frecuency').show();
            $('#div-freeday').show();
        } else {
            $('#nav-tab3').hide();
            $('#nav-tab5').hide();
            $('#div-frecuency').hide();
            $('#div-freeday').hide();            
        }        
    }

    function select_act() {
        if ($('#toshow2').is(':checked')) {
            $('#toshow1').attr('checked', true);
            $('#toshow0').attr('checked', true);
        }
        if ($('#toshow1').is(':checked')) {
            $('#toshow0').attr('checked', true);
        }

        if ($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) {
            $("#nav-tab5").show();
            $("#tab5").css("visibility", "visible");
            $("#nav-tab6").show();
            $("#tab6").css("visibility", "visible");          
        } else {
            $("#nav-tab5").hide();
            $("#tab5").css("visibility", "hidden");
            $("#nav-tab6").hide();
            $("#tab6").css("visibility", "hidden");  
        }
    }
    </script>

    <script language="javascript" type="text/javascript">
    function refreshp(index) {
        if (index == 0)
            refresh_ajax_project();
        if (index == 1)
            refresh_ajax_users();
    }

    function refresh_ajax_project() {
        var id_programa = $('#programa').val();
        var id_proyecto = $('#id_proyecto').val();
        var year = $('#year').val();

        var _url = '../form/ajax/select_project.ajax.php?id_programa=' + id_programa + '&id_proyecto=' + id_proyecto +
            '&year=' + year;

        $.ajax({
            //   data:  parametros,
            url: _url,
            type: 'get',
            beforeSend: function() {
                $("#ajax-proyect").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#ajax-project").html(response);
            }
        });
    }

    function refresh_ajax_users() {
        var id_responsable = $('#id_responsable').val();
        var year = $('#year').val();

        $.ajax({
            data: {
                "name": "responsable",
                "id_responsable": id_responsable,
                "year": year
            },
            url: 'ajax/select_users.ajax.php',
            type: 'get',
            beforeSend: function() {
                $("#responsable-container").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#responsable-container").html(response);
                $("#responsable").combobox();
            }
        });
    }
    </script>

    <?php
    $id= $id_tarea;
    $table= 'ttareas';
    require "inc/calendar.inc.php";
    ?>

    <script type="text/javascript">
    var focusin;

    $(document).ready(function() {
        refreshpry();
        refresh_ajax_users();
        refresh_ajax_project();

        set_calendar();
        set_toshow();
        set_ifgrupo();

        <?php if ($action == 'add' && !empty($id_proyecto)) { ?>
        if ($('#ifgrupo1').is(':checked')) {
            usuario_tabs_ajax();
        }
        <?php } else { ?>
        if (parseInt($('#ifgrupo').val()) == 0) {
            usuario_tabs_ajax();
        }
        <?php } ?>

        new BootstrapSpinnerButton('spinner-real', 1, 100);
        new BootstrapSpinnerButton('spinner-input_carga1', 1, 180);
        new BootstrapSpinnerButton('spinner-input_carga4', 1, 31);

        divYearCalendar(<?=$year?>, <?=$init_year?>, <?=$end_year?>);
        InitCalendarEvent();
        InitDragDrop();

        select_act();

        <?php if (!empty($id_proyecto)) { ?>
        set_ifgrupo();
        set_toshow();
        <?php } ?>  

        <?php if ($action == 'add' && !empty($id_proyecto)) { ?>
        $('#ifgrupo0').click(function() {
            set_ifgrupo();
            set_toshow();
        });
        $('#ifgrupo1').click(function() {
            set_ifgrupo();
            set_toshow();
        });
        <?php } ?>

        $('#div_fecha_inicio').datepicker({
            format: 'dd/mm/yyyy',
            startDate: '<?= $init_date ?>'
        });
        $('#div_hora_inicio').timepicker({
            minuteStep: 1,
            showMeridian: true
        });
        $('#div_hora_inicio').timepicker().on('changeTime.timepicker', function(e) {
            $('#hora_inicio').val($(this).val());
        });
        $('#div_fecha_fin').datepicker({
            format: 'dd/mm/yyyy',
            startDate: '<?= $init_date ?>'
        });

        $('#div_hora_fin').timepicker({
            minuteStep: 1,
            showMeridian: true
        });
        $('#div_hora_fin').timepicker().on('changeTime.timepicker', function(e) {
            $('#hora_fin').val($(this).val());
        });

        $('#fecha_inicio').on('change', function() {
            validar_interval(1);
        });
        $('#fecha_fin').on('change', function() {
            validar_interval(2);
        });
        $('#hora_inicio').on('change', function() {
            validar_interval(1);
        });
        $('#hora_fin').on('change', function() {
            validar_interval(2);
        });

        $('#div_fecha').datepicker({
            format: 'dd/mm/yyyy',
            startDate: '<?= $init_date ?>'
        });
        $('#fecha').on('change', function() {
            validar_interval(3);
        });

        if ($('#periodicidad4').is(':checked')) {
            create_chain();
        }

        tinymce.init({
            selector: '#descripcion',
            theme: 'modern',
            height: 300,
            language: 'es',
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
                '| bullist numlist outdent indent | removeformat | help',

            content_css: '../css/content.css'
        });

        try {
            $('#descripcion').val(<?= json_encode($descripcion)?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n", " ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">TAREA</div>

                <div class="card-body">
                    <?php
                    $check= !empty($id_programa) ? "checked='checked'" : null;
                    $disable = !empty($id_programa) ? "disabled='disabled'" : "";
                    $display= $check ? 'block' : 'none';
                    $ifgrupo = ($signal != 'fproyecto') ? 0 : $ifgrupo;
                    ?>

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Descripción</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Ejecutantes</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Planificación del Tiempo</a></li>
                        <li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Empresas/UEB/Direcciones</a></li>
                        <li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Objetivos de Trabajo</a></li>
                    </ul>

                    <form class="form-horizontal" name="ftarea" action="javascript:validar()" method="post">
                        <input type="hidden" name="exect" id="exect" value="<?=$action ?>" />
                        <input type="hidden" name="id" id="id" value="<?=$id_tarea ?>" />
                        <input type="hidden" id="menu" name="menu" value="tarea" />

                        <input type="hidden" id="signal" name="signal" value="<?=$signal ?>" />
                        <input type="hidden" id="id_riesgo" name="id_riesgo" value="<?=$id_riesgo ?>" />
                        <input type="hidden" id="id_nota" name="id_nota" value="<?=$id_nota ?>" />
                        <input type="hidden" id="id_auditoria" name="id_auditoria" value="0" />
                        <input type="hidden" id="id_tarea" name="id_tarea" value="0" />

                        <input type="hidden" name="id_programa" id="id_programa" value="<?=$id_programa?>" />
                        <input type="hidden" name="id_proyecto" id="id_proyecto" value="<?=$id_proyecto?>" />

                        <input type="hidden" name="id_responsable" id="id_responsable" value="<?=$id_responsable?>" />

                        <input type="hidden" id="month" name="month" value="<?=$month ?>" />
                        <input type="hidden" id="year" name="year" value="<?=$year ?>" />

                        <input type="hidden" id="_radio_date" name="_radio_date" value=<?=$_radio_date?> />
                        <input type="hidden" id="fecha_origen" name="fecha_origen"
                            value="<?=odbc2date($fecha_origen)?>" />
                        <input type="hidden" id="fecha_termino" name="fecha_termino"
                            value="<?=odbc2date($fecha_termino)?>" />

                        <input type="hidden" id="year" name="year" value="<?=$year?>" />
                        <input type="hidden" id="month" name="month" value="<?=$month ?>" />

                        <input type="hidden" id="_time_inicio" name="_time_inicio" />
                        <input type="hidden" id="_time_fin" name="_time_fin" />

                        <input type="hidden" id="_chain" name="_chain" value="">
                        <input type="hidden" id="changed_chain" name="changed_chain" value="0">

                        <input type="hidden" id="init_year" name="init_year" value="<?=$init_year?>" />
                        <input type="hidden" id="end_year" name="end_year" value="<?=$end_year?>" />

                        <input type="hidden" id="no_reject" name="no_reject" value="<?=$no_reject?>" />

                        <input type="hidden" id="if_jefe" name="if_jefe" value="<?=$if_jefe ? 1 : 0?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Tarea:
                                </label>
                                <div class="col-md-10">
                                    <textarea name="nombre" class="form-control" rows="3" id="nombre"><?= $nombre ?></textarea>
                                </div>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id=ifassure name=ifassure
                                        <?php if ($ifassure) echo "checked" ?>>
                                    Es una actividad o tarea de aseguramiento técnico o logístico.
                                </label>
                            </div>
                            
                            <?php if ($action == 'add' && !empty($id_proyecto)) { ?>
                            <div id="tr_ifgrupo0" class="radio">
                                <label>
                                    <input type="radio" id="ifgrupo0" name="ifgrupo" value="1"
                                        <?php if ($ifgrupo) echo "checked='checked'" ?> onchange="refreshform()" />
                                    Es un grupo de tareas. Contendrá a otras tareas y su avance dependerá del avance de
                                    las mismas
                                </label>
                            </div>

                            <div class="radio">
                                <label>
                                    <input type="radio" id="ifgrupo1" name="ifgrupo" value="0"
                                        <?php if (!$ifgrupo) echo "checked='checked'" ?> onchange="refreshform()" />
                                    Es una tarea simple. No contendrá a otras tareas o subtareas
                                </label>
                            </div>
                            <?php } else { ?>
                            <input type="hidden" id="ifgrupo" name="ifgrupo" value="<?=!empty($ifgrupo) ? 1 : 0?>" />
                            <?php } ?>    

                            <?php
                            $obj_task= new Ttarea($clink);
                            $obj_task->SetYear($year);
                            $obj_task->SetIdProyecto($id_proyecto);
                            $obj_task->SetIfGrupo(true);
                            $obj_task->SetFechaInicioPlan($fecha_inicio);
                            $obj_task->SetFechaFinPlan($fecha_termino);

                            $result= $obj_task->listar(true, true);
                            ?>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Pertenece al grupo de tareas:
                                </label>    
                                <div class="col-md-10">
                                    <select id="tarea_grupo" name="tarea_grupo" class="form-control">
                                        <option value="0">... </option>
                                        <?php 
                                        while ($row = $clink->fetch_array($result)) {
                                            if ($row['_id'] == $id_tarea)
                                                continue;
                                        ?>
                                            <option value="<?=$row['_id']?>" <?php if ($row['_id'] == $id_tarea_grupo) {?>selected<?php } ?>>
                                            <?=textparse($row['nombre']) ." (".date('d/m/Y', strtotime($row['fecha_inicio_plan']))." -- ".date('d/m/Y', strtotime($row['fecha_fin_plan'])).")"?>
                                            </option>
                                        <?php } ?>
                                    </select>                                
                                </div>
                            </div>

                            <?php 
                            $check= !empty($id_proyecto) ? "checked='checked'" : null; 
                            $disabled= !empty($id_proyecto) ? "disabled" : null; 
                            ?>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="ifproyecto" name="ifproyecto" value="1" <?= $check ?>
                                        onchange="refreshpry()" disabled />
                                    La tarea forma parte de un proyecto
                                </label>
                            </div>

                            <div id="tr_programa" class="form-group d-flex row">
                                <label class="col-form-label col-md-2">
                                    Programa:
                                </label>
                                <div class="text text-info col-md-10">
                                    <?php
                                    $obj_prog = new Tprograma($clink);
                                    $obj_prog->SetYear($year);
                                    if (!empty($id_programa)) {
                                        $obj_prog->SetIdPrograma($id_programa);
                                        $obj_prog->Set();
                                        echo textparse($obj_prog->GetNombre());
                                    }
                                    ?>
                                </div>
                                <label class="col-form-label col-md-2">
                                    Proyecto:
                                </label>
                                <div class="text text-info col-md-10">
                                    <?php
                                    $obj_proy = new Tproyecto($clink);
                                    $obj_proy->SetYear($year);
                                    if (!empty($id_proyecto)) {
                                        $obj_proy->SetIdProyecto($id_proyecto);
                                        $obj_proy->Set();
                                        echo $obj_proy->GetCodigo() . ' : ' . textparse($obj_proy->GetNombre());
                                    }
                                    ?>
                                </div>                                
                            </div>

                            <hr>
                            </hr>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3 col-lg-2">
                                    Responsable:
                                </label>
                                <div id="responsable-container" class="col-8">
                                    <select name="responsable" id="responsable" class="form-control">
                                        <option value="0"
                                            <?php if (empty($id_responsable)) echo "selected='selected'" ?>></option>
                                    </select>
                                </div>
                            </div>

                            <div id="div-toshow" class="row">
                                <div class="checkbox col-12">
                                    <label class="col-form-label">
                                        <input type="checkbox" name="toshow0" id="toshow0" value="0"
                                            <?php if (empty($toshow) || $badger->tr_display == 'none') echo "checked='checked'" ?>
                                            onclick="select_act(0)" />
                                        Incluir solo en los Planes de Trabajo Individuales
                                    </label>
                                </div>
                                <div class="checkbox col-12" id="tr-estrat-1" style="display:<?= $badger->tr_display ?>">
                                    <label>
                                        <input type="checkbox" name="toshow1" id="toshow1" value="1"
                                            <?php if ($toshow == 1) echo "checked='checked'" ?>
                                            onclick="select_act(1)" />
                                        Incluirlo a partir de los Planes Mensuales. (<em>Se incluye en los
                                            individuales</em>)
                                    </label>
                                </div>
                                <div class="checkbox col-12" id="tr-estrat-2" style="display:<?= $badger->tr_display ?>">
                                    <label>
                                        <input type="checkbox" name="toshow2" id="toshow2" value="2"
                                            <?php if ($toshow == 2) echo "checked='checked'" ?>
                                            onclick="select_act(2);" />
                                        Incluirlo a partir de los Planes Anuales. (<em>Se incluye en los mensuales y los
                                            individuales</em>)
                                    </label>
                                </div>
                            </div>
                        </div> <!-- generales -->


                        <!-- descripcion -->
                        <div class="tabcontent" id="tab2">
                            <textarea name="descripcion" id="descripcion"><?=$descripcion; ?></textarea>
                        </div> <!-- descripcion -->


                        <!-- participantes de la tarea -->
                        <div class="tabcontent" id="tab3">
                            <div id="ajax-tab-users">

                            </div>
                        </div> <!-- participantes de la tarea -->


                        <!-- planificacion del tiempo -->
                        <div class="tabcontent" id="tab4">
                            <?php
                            $chk_date_block = true;
                            $chk_cant_day_block = false;
                            require "inc/period_select.inc.php";
                            ?>
                        </div> <!-- planificacion del tiempo -->


                        <!-- Listado de procesos -->
                        <div class="tabcontent" id="tab5">
                            <?php
                            $id = $id_tarea;

                            $obj_prs = new Tproceso_item($clink);
                            $obj_prs->set_acc($badger->acc);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3) {
                                $obj_prs->SetIdProceso($_SESSION['id_entity']);
                                $obj_prs->set_use_copy_tprocesos(false);
                            } else {
                                $exits = $obj_prs->set_use_copy_tprocesos(true);
                            }

                            $corte= $config->show_prs_plan ? _TIPO_PROCESO_INTERNO : _TIPO_DEPARTAMENTO;
                            if ($config->show_group_dpto_plan || $config->show_prs_plan) {
                                $result_prs_array = $obj_prs->listar_in_order('eq_desc', true, $corte, false, 'asc');
                                $result_prs_array= $obj_prs->get_procesos_down_cascade(null, null, $corte, $result_prs_array);
                            } else {
                                $result_prs_array = $obj_prs->listar_in_order('eq_desc', false, _TIPO_DIRECCION, false, 'asc');
                            }
                            $cant_prs = $obj_prs->GetCantidad();

                            if ($cant_prs > 0)
                                reset($result_prs_array);

                            if (!empty($id)) {
                                $obj_prs->SetIdTarea($id);
                                $array_procesos= $obj_prs->GetProcesoTarea();
                            }

                            $restrict_prs = $config->show_prs_plan ? array(_TIPO_ARC) : array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
                            $restrict_up_prs = true;
                            $filter_by_toshow = true;

                            $create_select_input= false;
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab5 Procesos-->


                        <!-- Listado de inductores -->
                        <div class="tabcontent" id="tab6">
                            <input type="hidden" name="cant_objt" id="cant_objt" value="0" />
                            <input type="hidden" name="t_cant_objt" id="t_cant_objt" value="0" />

                            <script language="javascript">
                            document.getElementById('cant_objt').value = 0;
                            document.getElementById('t_cant_objt').value = 0;

                            function set_cant_objt(id) {
                                var nvalue = parseInt($('#cant_objt').val());

                                if (parseInt($('#select_objt' + id).val()) > 0 && parseInt($('init_objt' + id).val()) == 0)
                                    ++nvalue;
                                if (parseInt($('#select_objt' + id).val()) == 0 && parseInt($('init_objt' + id).val()) > 0)
                                    --nvalue;

                                $('#cant_objt').val(nvalue);
                            }
                            </script>

                            <div class="container-fluid" style="max-height: 500px; overflow: auto;">
                                <table class="table table-hover table-bordered table-striped" data-toggle="table" data-height="400">
                                    <thead>
                                        <th class="col-1">No.</th>
                                        <th class="col-3">Ponderación</th>
                                        <th class="col-auto">Objetivos de Trabajo</th>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $j_objt= 0;
                                        $i_objt= 0;
                                        $cant_objt= 0;
                                        $t_cant_objt= 0;
                                        $display = ($empresarial == 0) ? 'none' : 'block';

                                        $array_pesos = null;
                                        $obj_peso= new Tpeso($clink);
                                        $obj_peso->SetIdEscenario(null);
                                        $obj_peso->SetIdTarea($id_tarea);
                                        if (!empty($id_tarea))
                                            $array_pesos = $obj_peso->listar_inductores_ref_evento($id_tarea);

                                        $title_obj = "Ponderación del Impacto sobre los Objetivos de Trabajo de la Entidad ";
                                        $if_jefe= $_SESSION['acc_planwork'] == 3 || $_SESSION['nivel'] >= _SUPERUSUARIO? true : false;

                                        include "inc/inductor_tabs.inc.php";
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- buttom -->
                        <?php
                        $jumppage = false;
                        $overwrite = true;

                        if (!empty($id_riesgo) || !empty($id_nota)) {
                            if (!empty($id_tarea))
                                $overwrite = false;
                            $jumppage = !empty($id_tarea) ? true : false;
                        }
                        ?>

                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="button"
                                onclick="self.location.href='<?php prev_page(null, $overwrite, $jumppage)?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/19_tareas.htm#19')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>

    <?php
        $obj= new Ttarea($clink);
        $time= new Ttime();

        $id= $id_tarea;
        $table= 'ttareas';
        require "inc/calendar.inc.bottom.php";
        ?>


    <div id="div-panel-calendar" class="card card-primary calendar-year-container ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title col-11 win-drag">SELECCIONE LOS DÍAS DEL AÑO
                </div>

                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="#" title="cerrar ventana" onclick="HideContent('div-panel-calendar');">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="ajax-calendar" class="card-body"></div>
    </div>

</body>

</html>