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

require_once "../php/class/tipo_reunion.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tipo_evento.class.php";

require_once "../php/class/inductor.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/code.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../tools/archive/php/class/organismo.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_calendar= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : null;

$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

if ($action == 'add') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
   $obj= new Tevento($clink);

   if ($action == 'update' && !empty($signal)) {
        $id_evento= $_GET['id'];
        $obj->Set($id_evento);
        $obj->action= 'update';
    }
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new Ttime();
$time->splitTime();
$actual_year= $time->GetYear();

$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $obj->GetIdResponsable();
if (empty($id_responsable))
    $id_responsable= $_SESSION['id_usuario'];

$id_evento= $obj->GetIdEvento();

$_radio_date= !is_null($_GET['_radio_date']) ? $_GET['_radio_date'] : 2;

$fecha_origen= !empty($_GET['fecha_origen']) ? time2odbc(urldecode($_GET['fecha_origen'])) : null;
if (!empty($fecha_origen))
    $init_year= date('Y', strtotime($fecha_origen));
$fecha_inicio= !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : $obj->GetFechaInicioPlan();
if (!empty($fecha_origen) && (empty($fecha_inicio) || ($_radio_date == 1 || $_radio_date == 2)))
    $fecha_inicio= $fecha_origen;
$fecha_inicio= !empty($fecha_inicio) ? time2odbc($fecha_inicio) : date('Y-m-d').' 08:30';

$fecha_termino= !empty($_GET['fecha_termino']) ? time2odbc(urldecode($_GET['fecha_termino'])) : null;
if (!empty($fecha_termino))
    $end_year= date('Y', strtotime($fecha_termino));
$fecha_fin= !empty($_GET['fecha_fin']) ? urldecode($_GET['fecha_fin']) : $obj->GetFechaFinPlan();
if (empty($fecha_fin) && !empty($fecha_termino))
    $fecha_fin= $fecha_termino;
$fecha_fin= !empty($fecha_fin) ? time2odbc($fecha_fin) : date('Y-m-d').' 17:30';

$asunto= !empty($_GET['asunto']) ? urldecode($_GET['asunto']) : $obj->GetNombre();
$empresarial= !empty($_GET['empresarial']) ? $_GET['empresarial'] : $obj->GetIfEmpresarial();
$toshow= !is_null($_GET['toshow']) ? $_GET['toshow'] : $obj->get_toshow_plan();
$user_check= !is_null($_GET['user_check']) ? $_GET['user_check'] : $obj->get_user_check_plan();

$id_subcapitulo0= 0;
$id_subcapitulo1= 0;
$id_tipo_evento= !empty($_GET['id_tipo_evento']) ? $_GET['id_tipo_evento'] : $obj->GetIdTipo_evento();

if (!empty($id_tipo_evento)) {
    $obj_tipo_evento= new Ttipo_evento($clink);
    $obj_tipo_evento->Set($id_tipo_evento);
    $id_subcapitulo= $obj_tipo_evento->GetIdSubcapitulo();

    if (!empty($id_subcapitulo)) {
        $id_subcapitulo0= $id_subcapitulo;
        $id_subcapitulo1= $id_tipo_evento;
    } else {
        $id_subcapitulo0= $id_tipo_evento;
        $id_subcapitulo1= 0;
    }
}

$_year= date('Y', strtotime($fecha_inicio));

$year= !empty($_GET['year']) ? $_GET['year'] : $_year;
if ($year > date('Y')) {
    $fecha_inicio= !empty($fecha_inicio) && $year == $_year ? $fecha_inicio : "$year-01-01 07:30";
    $fecha_fin= !empty($fecha_fin) && $year == $_year ? $fecha_fin : "$year-12-31 11:59";
}

$init_year= $year;
$end_year= $year;

$funcionario= !empty($_GET['funcionario']) ? urldecode($_GET['funcionario']) : $obj->GetFuncionario();

$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : $obj->GetLugar();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$numero_plus= !empty($_GET['numero_plus']) ? urldecode($_GET['numero_plus']) : $obj->GetNumero_plus();

if ($action == 'add')
    $_tipo_reunion= $signal == 'anual_plan_meeting' ? 1 : 0;
else
    $_tipo_reunion= !is_null($_GET['_tipo_reunion']) ? $_GET['_tipo_reunion'] : $obj->GetIdTipo_reunion();

$id_tipo_reunion= !empty($_GET['id_tipo_reunion']) ? $_GET['id_tipo_reunion'] : $obj->GetIdTipo_reunion();
$id_secretary= !empty($_GET['id_secretary']) ? $_GET['id_secretary'] : $obj->GetIdSecretary();
$ifassure= !empty($_GET['ifassure']) ? urldecode($_GET['ifassure']) : $obj->GetIfAssure();

$if_send= !empty($_GET['if_send']) ? urldecode($_GET['if_send']) : $obj->GetIfSend();
$sendmail= !empty($_GET['sendmail']) ? urldecode($_GET['sendmail']) : $obj->GetSendMail();

if (!empty($id_evento)) {
    $obj->SetIdProceso(null);
    /*
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;

    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;
    */
    $id_tarea= $obj->GetIdTarea();
    $id_tarea_code= $obj->get_id_tarea_code();
}

$obj_peso= new Tpeso($clink);
$obj_peso->SetIdEvento($id_evento);

$periodicidad= !is_null($_GET['periodicidad']) ? $_GET['periodicidad'] : $obj->GetPeriodicidad();
if (empty($periodicidad))
    $periodicidad= 0;

$days= date_diff(date_create($fecha_inicio), date_create($fecha_fin));
$days= $days->format('%a');
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

$id_usuario= $_SESSION['id_usuario'];
$disabled= ($action == 'update') ? "disabled='disabled'" : "";

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);
$badger->set_planwork();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
$id_proceso_code= !empty($_GET['id_proceso_code']) ? $_GET['id_proceso_code'] : get_code_from_table('tprocesos', $id_proceso);

$obj_user= new Tusuario($clink);
if ($badger->freeassign)
    $obj_user->set_use_copy_tusuarios(false);
else
    $obj_user->set_use_copy_tusuarios(true);

$obj_user->SetIdProceso(null);
$obj_user->set_user_date_ref($fecha_inicio);
$result_user= $obj_user->listar(null, null, null, null, null, null, $action == 'add' ? true : false);

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);

$obj_prs->get_procesos_up_cascade();
$array_procesos_up= array();
foreach ($obj_prs->array_cascade_up as $key => $array)
    $array_procesos_up[$array['id']]= $array;

$url_page= "../form/fevento.php?signal=$signal&action=$action&menu=evento&exect=$action";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day&id_usuario=$id_usuario";
$url_page.= "&id_responsable=$id_responsable&id_calendar=$id_calendar";

add_page($url_page, $action, 'f');

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);

if ($action == 'add') {
    if (empty($id_proceso)) {
        $id_proceso= $_SESSION['id_entity'];
        $id_proceso_code= $_SESSION['id_entity_code'];
    }
    if (!empty($id_proceso) && $id_proceso != $_SESSION['id_entity']) {
        $id_proceso= $obj_prs->get_proceso_top($id_proceso, null, true);
        $obj_prs->Set($id_proceso);
        $id_proceso_code= $obj_prs->get_id_code();
    }
}

$id_tipo_reunion_otra= !empty($_GET['id_tipo_reunion_otra']) ? $_GET['id_tipo_reunion_otra'] : null;
if (empty($id_tipo_reunion_otra)) {
    $obj_meeting= new Ttipo_reunion($clink);
    $obj_meeting->SetIdProceso($id_proceso);
    $id_tipo_reunion_otra= $obj_meeting->get_id_tipo_reunion_otra();
}

$if_jefe= false;
if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['freeassign'] || $config->freeassign)
    $if_jefe= true;
if (!empty($id_riesgo) && $_SESSION['acc_planwork'] == 3)
    $if_jefe= true;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>ACTIVIDAD</title>

    <?php require 'inc/_page_init.inc.php'; ?>

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
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <link href="../libs/year-calendar/year-calendar.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/year-calendar/year-calendar.js"></script>

    <link href="../libs/combobox/jquery-combobox.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/combobox/jquery-combobox.js"></script>

    <script type="text/javascript" src="../js/time.js?version="></script>
    <script type="text/javascript" src="../js/calendar.js?version="></script>
    <script type="text/javascript" src="../js/tipo_evento.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script type="text/javascript" charset="utf-8">
    var text_form = 'de la tarea o actividad';
    var tipo_form = 'evento';

    function _refreshp() {
        var action = $('#exect').val();
        var empresarial = $('#tipo_actividad1').val();
        var signal = $('#signal').val();
        var id_calendar = $('#id_calendar').val();
        var _radio_date = $('#_radio_date').val();
        var lugar = $('#lugar').val();
        var numero_plus = $('#numero_plus').val();

        var descripcion = $('#descripcion').val();
        var id = $('#id').val();
        var id_responsable = id_calendar;

        if ($('#responsable0').is(':checked'))
            id_responsable = $('#id_usuario').val();

        if ($('#responsable1').is(':checked') || $('#responsable2').is(':checked'))
            id_responsable = $('#subordinado').val();

        if ($('#responsable3').is(':checked'))
            id_responsable = $('#usuario').val();

        var funcionario = encodeURI($('#funcionario').val());
        var asunto = encodeURI($('#nombre').val());

        if ($('#toshow0').is(':checked'))
            toshow = 0;
        if ($('#toshow1').is(':checked'))
            toshow = 1;
        if ($('#toshow2').is(':checked'))
            toshow = 2;
        var user_check = $('#user_check').is(':checked') ? 1 : 0;

        var year = $('#year').val();
        var month = $('#month').val();

        var form = document.forms[0];

        var fecha_origen = $('#fecha_origen').val();
        var fecha_termino = $('#fecha_termino').val();

        var fecha_inicio = $('#fecha_inicio').val() + ' ' + $('#hora_inicio').val();
        fecha_inicio = ampm2time(fecha_inicio);
        var fecha_fin = $('#fecha_fin').val() + ' ' + $('#hora_fin').val();
        fecha_fin = ampm2time(fecha_fin);

        var periodicidad = 0;

        for (i = 1; i < 5; i++)
            if ($('#periodicidad' + i).is(':checked'))
                periodicidad = $('#periodicidad' + i).val();

        var dayweek;
        var carga;

        if (periodicidad == 1)
            carga = $('#input_carga1').val();

        if (periodicidad == 2)
            for (i = 1; i < 8; i++)
                if ($('#dayweek' + i).is(':checked'))
                    dayweek += '-' + i;

        if (periodicidad == 3) {
            carga = $('#input_carga4').val();
            dayweek = $('#dayweek0').val();
        }

        var saturday = $('#saturday').is(':checked') ? 1 : 0;
        var sunday = $('#sunday').is(':checked') ? 1 : 0;
        var freeday = $('#freeday').is(':checked') ? 1 : 0;

        var _tipo_reunion = $("#_tipo_reunion").is(':checked') ? 1 : 0;
        var id_tipo_reunion = $("#tipo_reunion").val();

        var if_send = $('#if_send').is(':checked') ? 1 : 0;
        var sendmail = $('#sendmail').is(':checked') ? 1 : 0;

        var url = '?version=&action=' + action + '&id=' + id + '&empresarial=' +
            empresarial;
        url += '&asunto=' + asunto + '&id_responsable=' + id_responsable + '&year=' + year + '&month=' + month +
            '&_radio_date=' + _radio_date;
        url += '&lugar=' + encodeURI(lugar) + '&descripcion=' + encodeURI(descripcion) + '&fecha_origen=' + encodeURI(
            fecha_origen);
        url += '&fecha_termino=' + encodeURI(fecha_termino) + '&fecha_inicio=' + encodeURI(fecha_inicio) +
            '&fecha_fin=' + encodeURI(fecha_fin);
        url += '&periodicidad=' + periodicidad + '&dayweek=' + dayweek + '&carga=' + carga + '&saturday=' + saturday +
            '&freeday=' + freeday + '&sunday' + sunday;
        url += '&user_check=' + user_check + '&_tipo_reunion=' + _tipo_reunion + '&id_tipo_reunion=' + id_tipo_reunion +
            '&init_row_temporary=<?=$init_row_temporary?>';
        url += '&numero_plus=' + encodeURIComponent(numero_plus) + '&if_send=' + if_send + '&sendmail=' + sendmail;

        return url;
    }

    function refreshp() {
        var form = document.forms[0];
        var url = 'fevento.php' + _refreshp();
        var id_calendar = $('#id_calendar').val();
        var funcionario = encodeURI($('#funcionario').val());
        var signal = encodeURI($('#signal').val());

        var toshow = 0;
        if ($('#toshow0').is(':checked'))
            toshow = 0;
        if ($('#toshow1').is(':checked'))
            toshow = 1;
        if ($('#toshow2').is(':checked'))
            toshow = 2;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        self.location.href = url + '&signal=' + signal + '&funcionario=' + funcionario + '&id_calendar=' + id_calendar +
            '&toshow=' + toshow;
    }

    function to_auditoria_form() {
        var form = document.forms[0];
        var url = 'fauditoria.php' + _refreshp();
        var id_proceso = $('#id_proceso').val();
        var id_calendar = $('#id_calendar').val();
        var funcionario = encodeURI($('#funcionario').val());
        var signal = encodeURI($('#signal').val());

        var toshow = 0;
        if ($('#toshow0').is(':checked'))
            toshow = 0;
        if ($('#toshow1').is(':checked'))
            toshow = 1;
        if ($('#toshow2').is(':checked'))
            toshow = 2;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        url += '&signal=' + signal + '&funcionario=' + funcionario + '&id_calendar=';
        url += id_calendar + '&toshow=' + toshow + '&id_proceso=' + id_proceso;
        self.location.href = url;
    }

    function refreshtab() {
        if ($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) {
            $("#nav-tab4").show();
            $("#tab4").css("visibility", "visible");

            $("#nav-tab5").show();
            $("#tab5").css("visibility", "visible");

            try {
                $("#nav-tab7").show();
                $("#tab7").css("visibility", "visible");
            } catch(e) {}

        } else {
            if (!$("#_tipo_reunion").is(':checked')) {
                $("#nav-tab4").hide();
                $("#tab4").css("visibility", "hidden");
            }

            $("#nav-tab5").hide();
            $("#tab5").css("visibility", "hidden");

            try {
                $("#nav-tab7").hide();
                $("#tab7").css("visibility", "hidden"); 
            } catch(e) {}           
        }

        if (parseInt($("#t_cant_objt").val()) == 0)
            $("#div-inductores").hide();
    }

    function select_lsub() {
        $('#tr_subordinado').hide();
        $('#option_usr').hide();
        $('#tr_usuario').hide();
        $('#tr_funcionario').hide();
        $('#label-responsable').text("Responsable:");

        if ($('#responsable1').is(':checked')) {
            $('#tr_subordinado').show();

            if ($('#subordinado').val() == $('#id_usuario').val())
                $('#subordinado').val(0);
        }

        if ($('#responsable2').is(':checked')) {
            $('#tr_funcionario').show();
            $('#label-responsable').text("Responsable interno:");

            if (parseInt($('#acc').val()) == 3)
                $('#tr_usuario').show();
            else {
                $('#tr_subordinado').show();
                $('#option_usr').show();
            }
        }

        if ($('#responsable3').is(':checked'))
            $('#tr_usuario').show();
    }

    function select_act(index) {
        try {
            if (!$('#toshow1').is(':checked') && !$('#toshow2').is(':checked')) {
                $('#tr-tipo_actividad1').hide();
                $('#tipo_actividad1').val(0);
                $('#tr-tipo_actividad2').hide();
                $('#tipo_actividad2').val(0);
                $('#tr-tipo_actividad3').hide();
                $('#tipo_actividad3').val(0);
                $('#tr-user_check').hide();
            }

            if ((!$('#toshow1').is(':checked') && !$('#toshow2').is(':checked')) || parseInt($('#t_cant_objt').val()) ==
                0)
                $('#div-inductores').hide();

            if ($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) {
                $('#tr-tipo_actividad1').show();
                $('#tr-tipo_actividad2').show();
                $('#tr-tipo_actividad3').show();

                $('#div-inductores').show();

                $('#tr-user_check').show();
            }
        } catch (e) {}

        validate_act();

        if (index == 1 || index == 2)
            refreshtab();
    }

    var meeting_array = Array();

    <?php
    $i= 0;
    foreach ($meeting_array as $array) {
        if ($i == 0) {
            ++$i;
            continue;
        }
    ?>
    meeting_array[<?= $i ?>] = '<?= $meeting_array[$i++] ?>';
    <?php } ?>

    function validar() {
        var form = document.forms[0];
        var text;

        function _this_1() {
            <?php if ($config->freeassign) { ?>
            if ($('#responsable3').is(':checked') && !Entrada($('#custom-combobox-usuario').val())) {
                $('#usuario').focus(focusin($('#usuario')));
                alert('No ha seleccionado al usuario del sistema responsable de la cita o actividad.');
                return false;
            }
            <?php } ?>
            if (($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) && parseInt($('#numero').val()) == 0) {
                $('#numero').focus(focusin($('#numero')));
                alert(
                    "Debe especificar el número de la actividad o tarea. De ello depende el orden en el que será mostrada en los Planes de Trabajo Generales."
                );
                return false;
            }
            if (($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) && $('#nivel').val() < 3) {
                $('#numero').focus(focusin($('#numero')));
                alert(
                    "Solo un usuario con el nivel de de acceso al sistema de PLANIFICADOR o superior, puede incorporar eventos a los Planes de Trabajo Generales."
                );
                return false;
            }
            if (Entrada($('#numero_plus').val()) && !validate_numero_plus($('#numero_plus').val())) {
                return false;
            }

            if ($('#toshow2').is(':checked') && $('#tipo_actividad1').val() == 0) {
                $('#tipo_actividad1').focus(focusin($('#tipo_actividad1')));
                alert(
                    "Cuando se trata de una actividad a incluirse en los Planes Generales debe especificar el tipo de actividad."
                );
                return false;
            }
            return true;
        }

        function _this_2() {
            if (!$('#toshow0').is(':checked')) {
                text =
                    "Las actividades o eventos no serán mostradas en los planes de trabajo individuales de los participantes.";
                text +=
                    "Eso es una decisión “oscura”, pues se trata de tareas “fantasmas”, que por lo general, nadie cumple. ¿Desea continuar?"
                confirm(text, function(ok) {
                    if (!ok)
                        return;
                    else {
                        if (!_this_3())
                            return false;
                        else
                            _this_4();
                    }
                });
            } else {
                if (!_this_3())
                    return false;
                else
                    _this_4();
            }

            return true;
        } // _this_2()

        function _this_5() {
            form.action = '../php/evento.interface.php?init_row_temporary=<?=$init_row_temporary?>';

            parent.app_menu_functions = false;
            $('#_submit').css('display', 'none');
            $('#_submited').css('display', 'block');
            form.submit();
        } // _this_5()

        function _this_4() {
            if (parseInt($('#no_reject').val())) {
                text =
                    "Hay actividades que ya están aprobadas o registradas como cumplidas en los Planes Individuales. "
                text +=
                    "Al cambiar las fechas y reprogramar se perderá la información asociada. Desea realmente eliminarlas y perder esta información?";

                confirm(text, function(ok) {
                    if (ok) {
                        $('input-calendar-go_delete').val(1);
                        _this_5();
                    } else {
                        $('input-calendar-go_delete').val(0);
                        _this_5();
                    }
                });
            } else {
                _this_5();
            }
        } // _this_4()

        function _this_3() {
            if (!Entrada($('#lugar').val())) {
                $('#lugar').focus(focusin($('#lugar')));
                alert('Introduzca el lugar donde se efectuará la cita o actividad.');
                return false;
            }

            if (!validar_interval(true))
                return;

            if ($('#periodicidad1').is(':checked') && (!Entrada($('#input_carga1').val()) || (Entrada($('#input_carga1')
                    .val()) && parseInt($('#input_carga1').val()) == 0))) {
                $('#input_carga1').focus(focusin($('#input_carga1')));
                alert(
                    "Usted ha escogido una periodicidad diaria. Debe especificar frecuencia en días con que se ejecutará la actividad."
                );
                return;
            }
            if ($('#periodicidad2').is(':checked')) {
                var check = false;
                for (i = 1; i < 8; ++i) {
                    if ($("#dayweek" + i).is(':checked')) {
                        check = true;
                        break;
                    }
                }

                if (!check) {
                    $('#periodicidad2').focus(focusin($('#periodicidad2')));
                    alert(
                        "Usted ha escogido la periodicidad semanal para el evento. Debe seleccionar los días de la semana en los que se repetirá la actividad."
                    );
                    return false;
                }
            } // _this_3()

            if ($('#periodicidad3').is(':checked')) {
                if (!Entrada($('#input_carga4').val()) && ($('#sel_carga').val() == 0 || $('#dayweek0').val() == 0)) {
                    $('#periodicidad3').focus(focusin($('#periodicidad3')));
                    alert(
                        "Usted ha escogido la periodicidad mensual para el evento. Debe especificar la frecuencia de días en el mes."
                    );
                    return false;
                }
            }

            if ($('#periodicidad3').is(':checked') && $('#input_carga4').val() > 31) {
                $('#input_carga4').focus(focusin($('#input_carga4')));
                alert("Error en el día del mes selecionado. No existe un mes con más de 31 días.");
                return false;
            }
            if ($('#periodicidad4').is(':checked')) {
                if (create_chain() == 0) {
                    $('#periodicidad4').focus(focusin($('#periodicidad4')));
                    alert("No ha especificado los días en que se realizará la actividad");
                    return false;
                }
            }

            return true;
        } // _this_3()

        if ($('#_tipo_reunion').is(':checked')) {
            if (!parseInt($('#tipo_reunion').val())) {
                $('#tipo_reunion').focus(focusin($('#tipo_reunion')));
                alert('Debe especificar el tipo de reunión');
                return;
            }
            if (!parseInt($('#secretary').val())) {
                $('#secretary').focus(focusin($('#secretary')));
                alert('Debe definir el secretario de la reunión');
                return;
            }
        }

        if (!Entrada($('#nombre').val()) && (!$('#_tipo_reunion').is(':checked')
                || ($('#_tipo_reunion').is(':checked')
                    && ($('#tipo_reunion').val() == 0 || $('#tipo_reunion').val() == <?= _MEETING_TIPO_OTRA ?>)))) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el asunto de la cita o actividad');
            return;
        }
        if (!Entrada($('#nombre').val()) && ($('#_tipo_reunion').is(':checked') && $('#tipo_reunion').val() > 0)) {
            $('#nombre').val($('#tipo_reunion option:selected').text());
            $('#nombre').focus(focusin($('#nombre')));
        }
        if ($('#responsable1').is(':checked') && !Entrada($('#custom-combobox-subordinado').val())) {
            $('#subordinado').focus(focusin($('#subordinado')));
            alert('No ha seleccionado al subordinado responsable de la cita o actividad');
            return;
        }
        if ($('#responsable2').is(':checked') && !Entrada($('#funcionario').val())) {
            $('#funcionario').focus(focusin($('#funcionario')));
            alert(
                'No se ha especificado el cargo del funcionario externo a la Empresa, responsable de la cita o actividad'
            );
            return;
        }

        if ($('#responsable2').is(':checked') && !Entrada($('#custom-combobox-usuario').val())) {
            confirm('No ha seleccionado al subordinado responsable por la Empresa de la cita o actividad. Usted asumirá la responsabilidad?',
                function(ok) {
                    if (!ok) {
                        $('#subordinado').focus(focusin($('#subordinado')));
                        alert("Deberá selecionar a un subordinado como responsable de la cita o actividad.");
                        return;
                    } else {
                        $('#subordinado').val($('#id_usuario').val());
                        if (!_this_1())
                            return;
                        else {
                            if (!_this_2())
                                return;
                        }
                    }
                });
        } else {
            if (!_this_1())
                return;
            else {
                if (!_this_2())
                    return;
            }
        }
    }

    function set_meeting(flag) {
        if ($("#_tipo_reunion").is(':checked')) {
            $("#tr-meeting").css('display', 'block');
            $("#tr-if_assure").hide();
            $("#tr-if_audit").hide();

            $("#ifassure").attr("checked", false);
            $("#if_audit").attr("checked", false);

            $("#nav-tab4").css('display', 'block');
            $("#tab4").css('visibility', 'visible');

        } else {
            $("#tr-meeting").css('display', 'none');
            $('#nombre').prop('readOnly', false);
            $("#tr-if_assure").show();
            $("#tr-if_audit").show();

            if (!$('#toshow1').is(':checked') && !$('#toshow2').is(':checked')) {
                $("#nav-tab4").css('display', 'none');
                $("#tab4").css('visibility', 'hidden');
            }
        }

        if (flag == 2 || (flag == 0 && $("#if_audit").is(':checked'))) {
            $("#ifassure").attr("checked", false);
            $("#_tipo_reunion").is(':checked', false);
            $("#tr-meeting").css('display', 'none');
        }
        if (flag == 3 || (flag == 0 && $("#ifassure").is(':checked'))) {
            $("#if_audit").attr("checked", false);
            $("#_tipo_reunion").is(':checked', false);
            $("#tr-meeting").css('display', 'none');
        }

        if ($('#exect').val() == 'add' && $("#if_audit").is(':checked'))
            to_auditoria_form();
    }

    function refresh_tipo_reunion() {
        if ($('#tipo_reunion').val() == 0 || $('#tipo_reunion').val() == <?=$id_tipo_reunion_otra?>) {
            $('#nombre').prop('readOnly', false);
        } else {
            $('#nombre').prop('readOnly', true);
            $('#nombre').val($('#tipo_reunion option:selected').text());
        }
    }
    </script>

    <?php
    $id= $id_evento;
    $table= 'teventos';
    require "inc/calendar.inc.php";
    ?>

    <script type="text/javascript">
    var focusin;

    $(document).ready(function() {
        /*
        if ($('#freeassign').val()) {
            $('option.option-usuario').prop('disabled', false);
            $('option.option-usuario').show();

            $('option.option-subordinado').prop('disabled', true);
            $('option.option-subordinado').hide();
        } else {
            $('option.option-usuario').prop('disabled', true);
            $('option.option-usuario').hide();

            $('option.option-subordinado').prop('disabled', false);
            $('option.option-subordinado').show();
        }
        */
        select_act(-1);
        select_lsub();
        refreshtab();
        set_meeting();
        set_calendar();

        <?php
        $id = $id_evento;
        $user_ref_date = $fecha_fin;
        $restrict_prs = array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
        ?>

        var user_date_ref = $('#fecha_inicio').val();

        $.ajax({
            data: {
                "signal": "evento",
                "id_evento": <?=!empty($id) ? $id : 0?>,
                "id_tipo_reunion": <?=!empty($id_tipo_reunion) ? 1 : 0?>,
                "tipo_plan": <?=_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL?>,
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

        $.ajax({
            data: {
                "name": "secretary",
                "plus_name": "",
                "id_responsable": <?=!empty($id_secretary) ? $id_secretary : 0?>,
                "id_proceso": <?=$_SESSION['id_entity']?>,
                "year": <?=$year?>,
                "nivel": 0
            },
            url: 'ajax/select_users.ajax.php',
            type: 'get',
            beforeSend: function() {
                $("#secretary-container").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#secretary-container").html(response);
                $("#secretary").combobox();
            }
        });

        $.ajax({
            data: {
                "name": "usuario",
                "plus_name": "",
                "id_responsable": <?=!empty($id_responsable) ? $id_responsable : 0?>,
                "id_proceso": <?=$_SESSION['id_entity']?>,
                "year": <?=$year?>,
                "nivel": 0
            },
            url: 'ajax/select_users.ajax.php',
            type: 'get',
            beforeSend: function() {
                $("#usuario-container").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#usuario-container").html(response);
                $("#usuario").combobox();
            }
        });

        $.ajax({
            data: {
                "name": "subordinado",
                "id_usuario": <?=!empty($id_usuario) ? $id_usuario: 0?>,
                "id_responsable": <?=!empty($id_responsable) ? $id_responsable : 0?>,
                "id_proceso": <?=!empty($id_proceso) ? $id_proceso : $_SESSION['id_entity']?>,
                "year": <?=$year?>,
                "fecha_inicio": '<?=$fecha_inicio_plan?>'
            },
            url: 'ajax/select_subordinados.ajax.php',
            type: 'get',
            beforeSend: function() {
                $("#subordinado-container").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#subordinado-container").html(response);
                $("#subordinado").combobox();
            }
        });

        <?php if ($action == 'add' || $action == 'update') { ?>
        refresh_ajax_select(<?= $id_subcapitulo0 ?>, <?= $numero ?>, <?= $id_subcapitulo1 ?>);
        <?php } ?>

        new BootstrapSpinnerButton('spinner-numero', 0, 5000);
        new BootstrapSpinnerButton('spinner-input_carga1', 0, 180);
        new BootstrapSpinnerButton('spinner-input_carga4', 0, 31);

        divYearCalendar(<?=$year?>, <?=$init_year?>, <?=$end_year?>);
        InitCalendarEvent();
        InitDragDrop();

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

        $('#div_fecha_inicio').datepicker({
            format: 'dd/mm/yyyy',
            startDate: '01/01/<?= $init_year ?>',
            endDate: '31/12/<?= $end_year ?>'
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
            startDate: '01/01/<?= $init_year ?>',
            endDate: '31/12/<?= $end_year ?>'
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

        if ($('#periodicidad4').is(':checked')) {
            create_chain();
        }
        $('#tipo_reunion').change(function() {
            refresh_tipo_reunion();
        });

        if (parseInt($("#t_cant_objt").val()) == 0) {
            $("#div-inductores").hide();
        }

        refresh_tipo_reunion();

        <?php if (!$_SESSION['if_send_up'] && !$_SESSION['if_send_down']) { ?>
        $('#if_send').attr("disabled", "disabled");
        <?php } ?>

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body class="form">
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">ACTIVIDAD</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Fechas/Hora y Periodicidad</a></li>
                        <li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Descripción</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Participantes</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Empresas/UEB/Direcciones</a></li>
                        <li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Objetivos de Trabajo</a></li>

                        <?php if ($config->use_anual_plan_organismo && $_SESSION['id_entity'] == $_SESSION['local_proceso_id']) { ?>
                        <li id="nav-tab7" class="nav-item"><a class="nav-link" href="tab7">Instituciones y Organismos</a></li>
                        <?php } ?>    
                    </ul>


                    <form class="form-horizontal" name="fevento" id="fevento" action="javascript:validar()" method="POST">
                        <input type="hidden" id="exect" name="exect" value="<?= $action ?>" />
                        <input type="hidden" id="id" name="id" value="<?= $id_evento ?>" />
                        <input type="hidden" id="id_usuario" name="id_usuario" value="<?= $id_usuario ?>" />
                        <input type="hidden" id="nivel" name="nivel" value="<?= $_SESSION['nivel'] ?>" />
                        <input type="hidden" id="menu" name="menu" value="evento" />

                        <input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />
                        <input type="hidden" id="id_calendar" name="id_calendar" value="<?= $id_calendar ?>" />

                        <input type="hidden" id=year name="year" value="<?= $year ?>" />
                        <input type="hidden" id=month name="month" value="<?= $month ?>" />
                        <input type="hidden" id=day name="day" value="<?= $day ?>" />

                        <input type="hidden" id="id_tarea" name="id_tarea" value="<?= $id_tarea ?>" />
                        <input type="hidden" id="id_tarea_code" name="id_tarea_code" value="<?= $id_tarea_code ?>" />

                        <input type="hidden" id="id_auditoria" name="id_auditoria" value="<?= $id_auditoria ?>" />
                        <input type="hidden" id="id_auditoria_code" name="id_auditoria_code"
                            value="<?= $id_auditoria_code ?>" />

                        <input type="hidden" id="_time_inicio" name="_time_inicio" />
                        <input type="hidden" id="_time_fin" name="_time_fin" />

                        <input type="hidden" id="_chain" name="_chain" value="">
                        <input type="hidden" id="changed_chain" name="changed_chain" value="0">

                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />
                        <input type="hidden" id="id_proceso_code" name="id_proceso_code"
                            value="<?= $id_proceso_code ?>" />

                        <input type="hidden" id="id_responsable" name="id_responsable" value="<?= $id_responsable ?>" />
                        <input type="hidden" id="_id_responsable" name="_id_responsable" value="" />
                        <input type="hidden" id="_responsable_2_reg_date" name="_responsable_2_reg_date" value="" />

                        <input type="hidden" id="_radio_date" name="_radio_date" value=<?= $_radio_date ?> />
                        <input type="hidden" id="fecha_origen" name="fecha_origen"
                            value="<?= odbc2date($fecha_origen) ?>" />
                        <input type="hidden" id="fecha_termino" name="fecha_termino"
                            value="<?= odbc2date($fecha_termino) ?>" />

                        <input type="hidden" id="ifaccords" name="ifaccords" value="0" />
                        <input type="hidden" id="id_secretary" name="id_secretary" value="<?= $id_secretary ?>" />

                        <input type="hidden" id="init_year" name="init_year" value="<?=$init_year?>" />
                        <input type="hidden" id="end_year" name="end_year" value="<?=$end_year?>" />

                        <input type="hidden" id="freeassign" name="freeassign"
                            value="<?=($_SESSION['freeassign'] || $config->freeassign) ? true  : false?>" />

                        <input type="hidden" id="no_reject" name="no_reject" value="<?=$no_reject?>" />

                        <input type="hidden" id="if_jefe" name="if_jefe" value="<?=$if_jefe ? 1 : 0?>" />

                        <input type="hidden" id="acc" name="acc" value="<?=!empty($badger->acc) ? $badger->acc : 0?>" />

                        <input type="hidden" id="tipo_actividad_flag" value="" />
                        <input type="hidden" id="ifgrupo0" name="ifgrupo" value="0" />

                        <input type="hidden" id="id_tipo_reunion_otra" name="id_tipo_reunion_otra" value="<?=$id_tipo_reunion_otra?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">

                            <div class="checkbox">
                                <label class="col-form-label">
                                    <input type="checkbox" id="_tipo_reunion" onclick="set_meeting(1)"
                                        name="_tipo_reunion" value="1" <?php if ($_tipo_reunion) echo "checked" ?> />
                                    Es una reunión, asamblea o consejillo
                                </label>
                            </div>

                            <div id="tr-meeting" class="form-group row">
                                <div class="row col-md-12">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-md-4">Se reúne el(la):</label>
                                            <div class="col-md-8">
                                                <?php
                                                $obj_meeting= new Ttipo_reunion($clink);
                                                $obj_meeting->SetIdProceso($id_proceso);
                                                $result_meeting= $obj_meeting->listar();

                                                while ($row= $clink->fetch_array($result_meeting)) {
                                                ?>
                                                <input type="hidden" id="id_tipo_reunion<?=$row['id']?>"
                                                    name="id_tipo_reunion<?=$row['id']?>" value="<?=$row['id_code']?>" />
                                                <?php } ?>
                                                <select id="tipo_reunion" name="tipo_reunion" class="form-control input-sm">
                                                    <option value="0">... </option>
                                                    <?php
                                                    $clink->data_seek($result_meeting);
                                                    while ($row= $clink->fetch_array($result_meeting)) {
                                                    ?>
                                                    <option value="<?= $row['id'] ?>"
                                                        <?php if ($row['id'] == $id_tipo_reunion) echo "selected" ?>>
                                                        <?= $row['nombre'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row col-md-6">
                                        <label class="col-form-label col-md-3">Secretario(a):</label>
                                        <div class="col-md-8">
                                            <div id="secretary-container" class="col-md-12 col-lg-12">
                                                <select id="secretary" name="secretary" class="form-control">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row col-12">
                                    <div class="checkbox text col-12">
                                        <label class="col-sm-offset-2 col-md-offset-1 col-lg-offset-1">
                                            <input type="checkbox" id="if_send" name="if_send" value="1"
                                                <?php if ($if_send) echo "checked='checked'" ?> />
                                            Toda la información de la reunión, temáticas y debates, será transmitida
                                            durante la sincronización de datos.
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row col-12">
                                <div class="checkbox text" id="tr-if_audit">
                                    <label>
                                        <input type="checkbox" id="if_audit" onclick="set_meeting(2)" name="if_audit"
                                            value="1" <?php if (!empty($id_auditoria)) echo "checked='checked'" ?> />
                                        Es una acción de control (supervisión, auditoria o auto-control).
                                    </label>
                                </div>

                                <div class="w-100"></div>
                                <div class="checkbox row col-12" id="tr-if_assure">
                                    <label>
                                        <input type="checkbox" id="ifassure" onclick="set_meeting(3)" name="ifassure"
                                            <?php if ($ifassure) echo "checked" ?>>
                                        Es una actividad o tarea de aseguramiento técnico o logístico.
                                    </label>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Título o Asunto:</label>
                                <div class="col-md-10">
                                    <textarea id="nombre" name="nombre" rows="2"
                                        class="form-control"><?= $asunto ?></textarea>
                                </div>
                            </div>

                            <hr />
                            <?php unset($obj_user); ?>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Responsable:</label>

                                <div class="col-md-10">
                                    <div class="checkbox">
                                        <label>
                                            <?php
                                            $visible1 = 'none';

                                            if ($id_responsable == $_SESSION['id_usuario'] && empty($funcionario)) {
                                                $checked0 = "checked='checked'";
                                                $visible0 = 'block';
                                            } else {
                                                $checked0 = '';
                                                $visible0 = "none";
                                            }

                                            $obj_user = new Tusuario($clink);
                                            $email = $obj_user->GetEmail($id_usuario);
                                            ?>
                                            <input type="radio" id="responsable0" onclick="select_lsub()"
                                                name="responsable" value="0" <?= $checked0 ?> onclick="select_lsub()" />
                                            El actual usuario <?= stripslashes($email['nombre']) ?>
                                            <?= $email['cargo'] ? ', '.textparse($email['cargo']) : '' ?>
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <?php
                                            $if_usuario_jefe = false;
                                            if (array_key_exists($id_responsable, (array)$badger->array_usuarios_sub))
                                                $if_usuario_jefe = true;

                                            if (((int)$id_responsable != (int)$id_usuario && $if_usuario_jefe) && empty($funcionario)) {
                                                $checked1 = "checked='checked'";
                                                $visible1 = 'flex';
                                            } else {
                                                $checked1 = '';
                                            }
                                            ?>
                                            <input type="radio" id="responsable1" onclick="select_lsub()"
                                                name="responsable" value="1" <?= $checked1 ?> />
                                            Un subordinado directo
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <?php
                                            if (empty($funcionario)) {
                                                $checked2 = '';
                                                $visible2 = 'none';
                                                $option_user = "none";
                                            } else {
                                                $checked2 = "checked='checked'";
                                                $option_user = "display";
                                                $visible1 = 'flex';
                                                $visible2 = 'flex';
                                            }
                                            ?>
                                            <input type="radio" id="responsable2" onclick="select_lsub()"
                                                name="responsable" value="2" <?= $checked2 ?> />
                                            Un funcionario externo a la Organización
                                        </label>
                                    </div>

                                    <?php
                                    if (($id_responsable != $id_usuario && !$if_usuario_jefe) && empty($funcionario)) {
                                        $checked3 = "checked='checked'";
                                        $visible3 = 'flex';
                                    } else {
                                        $checked3 = '';
                                        $visible3 = 'none';
                                    }

                                    if ($config->freeassign || !empty($badger->acc)) {
                                    ?>
                                    <div class="checkbox">
                                        <label>
                                            <input type="radio" id="responsable3" onclick="select_lsub()"
                                                name="responsable" value="3" <?= $checked3 ?> />
                                            Otro usuario del sistema. (No subordinado directamente al actual usuario)
                                        </label>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div id="tr_subordinado" class="form-group row" style="display:<?= $visible1 ?>">
                                <label class="col-form-label col-md-4">
                                    Responsable interno:
                                </label>
                                <div id="subordinado-container" class="col-md-7">
                                    <select id="subordinado" name="subordinado" class="form-control">
                                    </select>
                                </div>
                            </div>

                            <div id="tr_usuario" class="form-group row" style="display:<?= $visible3 ?>">
                                <label id="label-responsable" class="col-form-label col-md-2">
                                    Responsable:
                                </label>
                                <div id="usuario-container" class="col-md-8">
                                    <select id="usuario" name="usuario" class="form-control">
                                    </select>
                                </div>
                            </div>

                            <div id="tr_funcionario" class="form-group row" style="display:<?= $visible2 ?>">
                                <label class="col-form-label col-md-4">
                                    Cargo del funcionario Externo (Responsable):
                                </label>
                                <div class="col-md-8">
                                    <input type="text" id="funcionario" name="funcionario" class="form-control"
                                        value="<?= $funcionario ?>" />
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Seguimiento y chequeo:</label>

                                <div class="col-lg-10">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Número:</label>
                                        <div class="col-lg-3">
                                            <div id="spinner-numero" class="input-group spinner">
                                                <input type="text" name="numero" id="numero" class="form-control"
                                                    value="<?=$numero?>">
                                                <div class="input-group-btn-vertical">
                                                    <button class="btn btn-default" type="button" data-bind="up">
                                                        <i class="fa fa-arrow-up"></i>
                                                    </button>
                                                    <button class="btn btn-default" type="button" data-bind="down">
                                                        <i class="fa fa-arrow-down"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <label class="col-form-label col-lg-4">Ampliar número (opcional):</label>

                                        <div class="col-lg-3">
                                            <input type="text" id="numero_plus" name="numero_plus" class="form-control"
                                                value="<?=$numero_plus?>" />
                                        </div>

                                    </div>

                                    <div class="row">
                                        <div class="checkbox col-12">
                                            <label class="col-form-label">
                                                <input type="checkbox" name="toshow0" id="toshow0" value=0
                                                    <?php if (empty($toshow) || $badger->tr_display == 'none') echo "checked='checked'" ?>
                                                    onclick="select_act(0)" onchange="refreshtab()" />
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
                                                Incluirlo a partir de los Planes Anuales. (<em>Se incluye en los
                                                    mensuales y los individuales</em>)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <?php $visible = ($toshow >= 1) ? 'flex' : 'none'; ?>
                            <div class="form-group row" id="tr-tipo_actividad1" style="display:<?= $visible ?>">
                                <label class="col-form-label col-md-2">
                                    Capítulo:
                                </label>
                                <div class="col-md-10">
                                    <select id="tipo_actividad1" name="tipo_actividad1" class="form-control"
                                        onchange="refresh_ajax_select(<?= $id_subcapitulo0 ?>, 0, 0)">
                                        <option value=0>...</option>
                                        <?php for ($i = 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $empresarial) echo "selected='selected'" ?>>
                                            <?= number_format_to_roman($i - 1) . '. ' . $tipo_actividad_array[$i] ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row" id="tr-tipo_actividad2" style="display:<?= $visible ?>">
                                <label class="col-form-label col-md-2">Sub Capítulo:</label>
                                <div class="col-md-10 ajax-select" id="ajax-tipo-evento">
                                    <select id="tipo_actividad2" class="form-control input-sm">

                                    </select>
                                </div>
                            </div>
                            <div class="form-group row" id="tr-tipo_actividad3" style="display:<?= $visible ?>">
                                <label class="col-form-label col-md-2">Epígrafe:</label>
                                <div class="col-md-10 ajax-select" id="ajax-subtipo-evento">
                                    <select id="tipo_actividad3" class="form-control input-sm">

                                    </select>
                                </div>
                            </div>

                            <div id="tr-user_check" class="form-group row" style="display:flex">
                                <div class="checkbox col-md-12">
                                    <label>
                                        <input type="checkbox" name="user_check" id="user_check" value="1"
                                            <?php if (!empty($user_check)) echo "checked='checked'" ?> />
                                        Las actividades o eventos programados no aparecerán en los Planes de Trabajo
                                        Individuales</strong>, aun cuando el usuario participe.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-1">
                                    Lugar:
                                </label>
                                <div class="col-md-11">
                                    <input type="text" name="lugar" id="lugar" class="form-control"
                                        value="<?= $lugar ?>" />
                                </div>
                            </div>
                        </div> <!-- generales -->

                        <!-- Descripcion -->
                        <div class="tabcontent" id="tab6">
                            <textarea name="descripcion" id="descripcion"
                                class="form-control"><?= $descripcion ?></textarea>
                        </div><!-- tab6 Descripcion-->


                        <!-- Planificacion del tiempo -->
                        <div class="tabcontent" id="tab2">
                            <?php
                            $chk_date_block= false;
                            $chk_cant_day_block= false;
                            require "inc/period_select.inc.php";
                            ?>
                        </div><!-- tab2 Planificacion del Tiempo-->


                        <!-- Listado de procesos -->
                        <div class="tabcontent" id="tab4">
                            <?php
                            $id = $id_evento;

                            $obj_prs = new Tproceso_item($clink);
                            $obj_prs->set_acc($badger->acc);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3) {
                                $obj_prs->SetIdProceso($_SESSION['id_entity']);
                                $obj_prs->set_use_copy_tprocesos(false);
                            } else {
                                $exits = $obj_prs->set_use_copy_tprocesos(true);
                            }

                            if ($config->show_prs_plan)
                                $corte= _TIPO_PROCESO_INTERNO;
                            else 
                                $corte= $config->show_group_dpto_plan ? _TIPO_DIRECCION : _TIPO_DEPARTAMENTO;

                            $result_prs_array = $obj_prs->listar_in_order('eq_desc', true, $corte, false, 'asc');
                            $cant_prs = $obj_prs->GetCantidad();

                            if ($cant_prs > 0)
                                reset($result_prs_array);

                            if (!empty($id)) {
                                $obj_prs->SetIdEvento($id);
                                $array_procesos= $obj_prs->GetProcesoEvento();
                            }

                            $restrict_prs = $config->show_prs_plan ? array(_TIPO_ARC) : array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
                            $restrict_up_prs = true;
                            $filter_by_toshow = true;

                            $create_select_input= false;
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab4 Procesos-->


                        <!-- Participantes -->
                        <div class="tabcontent" id="tab3">
                            <div class="container-fluid">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="sendmail" id="sendmail" value="1"
                                            <?=$sendmail ? "checked='checked'" : ""?> />
                                        Enviar citación o avisos por correo electrónico
                                    </label>
                                </div>

                                <hr style="margin-bottom: 0px; margin-top: 4px;" />
                            </div>

                            <div id="ajax-tab-users">

                            </div>
                        </div> <!-- tab3 Participantes-->


                        <!-- Listado de inductores -->
                        <div class="tabcontent" id="tab5">
                            <input type="hidden" name="cant_objt" id="cant_objt" value=0 />
                            <input type="hidden" name="t_cant_objt" id="t_cant_objt" value=0 />

                            <script language="javascript">
                            document.getElementById('cant_objt').value = 0;
                            document.getElementById('t_cant_objt').value = 0;

                            function set_cant_objt(id) {
                                var nvalue = parseInt($('#cant_objt').val());

                                if (parseInt($('#select_objt' + id).val()) > 0 && parseInt($('init_objt' + id).val()) ==
                                    0)
                                    ++nvalue;
                                if (parseInt($('#select_objt' + id).val()) == 0 && parseInt($('init_objt' + id).val()) >
                                    0)
                                    --nvalue;

                                $('#cant_objt').val(nvalue);
                            }
                            </script>


                            <div class="container-fluid" style="max-height: 500px; overflow: auto;">
                                <table class="table table-hover table-bordered table-striped">

                                    <thead>
                                        <th class="col-1">No.</th>
                                        <th class="col-3">Ponderación</th>
                                        <th>Objetivos de Trabajo</th>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $j_objt= 0;
                                        $i_objt= 0;
                                        $cant_objt= 0;
                                        $t_cant_objt= 0;
                                        $display = ($empresarial == 0) ? 'none' : 'block';

                                        $array_pesos = null;
                                        $obj_peso->SetIdEscenario(null);
                                        $obj_peso->SetIdEvento($id_evento);
                                        if (!empty($id_evento))
                                            $array_pesos = $obj_peso->listar_inductores_ref_evento($id_evento);

                                        $title_obj = "Ponderación del Impacto sobre los Objetivos de Trabajo de la Direccion o Proceso ";
                                        $title_obj.= "y sobre los definidos para las Direcciones o Procesos de nivel o jerarquia superior ";

                                        if (isset($obj_prs)) unset($obj_prs);
                                        $obj_prs = new Tproceso($clink);
                                        $_id_proceso = !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];
                                        $obj_prs->SetIdProceso($_id_proceso);
                                        $obj_prs->SetYear($year);
                                        $if_jefe= $_SESSION['acc_planwork'] == 3 || $_SESSION['nivel'] >= _SUPERUSUARIO? true : false;

                                        if ($if_jefe) {
                                            $obj_prs->listar_in_order('eq_asc', false, null, null, 'asc');
                                        } elseif ($_SESSION['acc_planwork'] == 1 || $_SESSION['acc_planwork'] == 2) {
                                            $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
                                            $obj_prs->get_procesos_by_user('eq_asc');
                                        }

                                        foreach ($obj_prs->array_procesos as $prs) {
                                            $proceso = $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']];
                                            $_connect = is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                            if ($prs['_id'] != $_SESSION['id_entity'])
                                                $_connect = ($_connect != 1) ? 1 : 0;
                                            else
                                                $_connect = 0;

                                            $id_list_prs = $prs['id'];
                                            $with_null_perspectiva = _PERSPECTIVA_ALL;

                                            include "inc/inductor_tabs.inc.php";
                                            ?>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>


                            <script language="javascript">
                            document.getElementById('cant_objt').value = document.getElementById('cant_objt').value +
                                <?= (int) $i_objt ?>;
                            document.getElementById('t_cant_objt').value = document.getElementById('t_cant_objt')
                                .value + <?= (int) $j_objt ?>;

                            if (parseInt(document.getElementById("t_cant_objt").value) == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Trabajo en el sistema a este nivel de Dirección o Proceso, o para sus niveles o procesos superiores. Deberá definirlos para poder acceder a esta funcionalidad."
                                );
                            }
                            </script>
                        </div><!-- tab5 Listado de Inductores-->

                        <!-- Instituciones y organismos -->
                        <div class="tabcontent" id="tab7">
                            <?php
                            require "inc/organismo_tabs.inc.php";
                            ?>
                        </div><!-- tab2 Instituciones y organismos-->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/04_plan.htm#04_6.3')">Ayuda</button>
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
    $obj= new Tevento($clink);
    $time= new Ttime();

    $id= $id_evento;
    $table= 'teventos';
    require "inc/calendar.inc.bottom.php";
    ?>

    <div id="div-panel-calendar" class="card card-primary calendar-year-container ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title col-11 win-drag">SELECCIONE LOS DÍAS DEL AÑO</div>

                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="#" title="cerrar ventana" onclick="HideContent('div-panel-calendar');">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="ajax-calendar" class="card-body ajax-calendar">

        </div>
    </div>

</body>

</html>