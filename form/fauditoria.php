<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";

require_once "../php/class/proceso_item.class.php";

require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_auditoria.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug'] = 'no';

$signal = !empty($_GET['signal']) ? $_GET['signal'] : null;
$action = !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

$init_row_temporary = !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

$obj = null;

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj = new Tauditoria($clink);

    if ($action == 'update' && !empty($signal)) {
        $id_auditoria = $_GET['id'];
        $obj->Set($id_auditoria);
        $obj->action = 'update';
    }
}

$tipo_plan = !empty($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_AUDITORIA;
$empresarial = !empty($_GET['empresarial']) ? $_GET['empresarial'] : $obj->GetIfEmpresarial();
$toshow = !empty($_GET['toshow']) ? $_GET['toshow'] : $obj->get_toshow_plan();
$user_check = !is_null($_GET['user_check']) ? $_GET['user_check'] : $obj->get_user_check_plan();

$id_subcapitulo0 = 0;
$id_subcapitulo1 = 0;
$id_tipo_evento = !empty($_GET['id_tipo_evento']) ? $_GET['id_tipo_evento'] : $obj->GetIdTipo_evento();
$id_tipo_auditoria = !empty($_GET['id_tipo_auditoria']) ? $_GET['id_tipo_auditoria'] : $obj->GetIdTipo_auditoria();

if (!empty($id_tipo_evento)) {
    $obj_tipo_evento = new Ttipo_evento($clink);
    $obj_tipo_evento->Set($id_tipo_evento);
    $id_subcapitulo = $obj_tipo_evento->GetIdSubcapitulo();

    if (!empty($id_subtcapitulo)) {
        $id_subcapitulo0 = $id_subtcapitulo;
        $id_subcapitulo1 = $id_tipo_evento;
    } else {
        $id_subcapitulo0 = $id_tipo_evento;
        $id_subcapitulo1 = 0;
    }
}

if (!empty($_GET['id_proceso']))
    $id_proceso = $_GET['id_proceso'];
if (empty($id_proceso))
    $id_proceso = $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso = $_SESSION['id_proceso'];

$id_auditoria = $obj->GetIdAuditoria();

$origen = !is_null($_GET['origen']) ? $_GET['origen'] : $obj->GetOrigen();
$tipo = !is_null($_GET['tipo']) ? $_GET['tipo'] : $obj->GetTipo_auditoria();
$lugar = !is_null($_GET['lugar']) ? urldecode($_GET['lugar']) : $obj->GetLugar();
$jefe_equipo = !is_null($_GET['jefe_equipo']) ? urldecode($_GET['jefe_equipo']) : $obj->GetJefe_equipo();
$organismo = !is_null($_GET['organismo']) ? urldecode($_GET['organismo']) : $obj->GetOrganismo();
$objetivos = !is_null($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetObjetivo();

$numero = !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$numero_plus = !empty($_GET['numero_plus']) ? urldecode($_GET['numero_plus']) : $obj->GetNumero_plus();

$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
str_replace("\n", " ", addslashes($error));

$id_responsable = !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $obj->GetIdResponsable();
if (empty($id_responsable)) 
    $id_responsable = $_SESSION['id_usuario'];

$id_usuario = $_SESSION['id_usuario'];

$_radio_date = !is_null($_GET['_radio_date']) ? $_GET['_radio_date'] : 2;

$fecha_origen = !empty($_GET['fecha_origen']) ? time2odbc(urldecode($_GET['fecha_origen'])) : null;
if (!empty($fecha_origen)) 
    $init_year = date('Y', strtotime($fecha_origen));
$fecha_inicio = !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : $obj->GetFechaInicioPlan();
if (empty($fecha_inicio) && !empty($fecha_origen)) 
    $fecha_inicio = $fecha_origen;
$fecha_inicio = !empty($fecha_inicio) ? time2odbc($fecha_inicio) : date('Y-m-d') . ' 08:30';

$fecha_termino = !empty($_GET['fecha_termino']) ? time2odbc(urldecode($_GET['fecha_termino'])) : null;
if (!empty($fecha_termino)) 
    $end_year = date('Y', strtotime($fecha_termino));
$fecha_fin = !empty($_GET['fecha_fin']) ? urldecode($_GET['fecha_fin']) : $obj->GetFechaFinPlan();
if (empty($fecha_fin) && !empty($fecha_termino)) 
    $fecha_fin = $fecha_termino;
$fecha_fin = !empty($fecha_fin) ? time2odbc($fecha_fin) : date('Y-m-d') . ' 17:30';

$year = date('Y', strtotime($fecha_inicio));
$month = date('m', strtotime($fecha_inicio));

$time = new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday = $time->longmonth($month, $year);

$year = !empty($_GET['year']) ? $_GET['year'] : $year;
if ($year > date('Y')) {
    $fecha_inicio = !empty($fecha_inicio) ? $fecha_inicio : "$year-01-01 07:30";
    $fecha_fin = !empty($fecha_fin) ? $fecha_fin : "$year-12-31 11:59";
}

$time->SetYear($year);
$init_year = $year;
$end_year = $year;

$cant_days = !empty($_GET['cant_days']) ? $_GET['cant_days'] : $obj->GetCantidad_days();
if (empty($cant_days)) $cant_days = 1;

$periodic = !empty($_GET['periodic']) ? $_GET['periodic'] : $obj->GetIfPeriodic();
$periodicidad = !empty($_GET['periodicidad']) ? $_GET['periodicidad'] : $obj->GetPeriodicidad();
if (empty($periodicidad)) 
    $periodicidad = 0;

$days = date_diff(date_create($fecha_inicio), date_create($fecha_fin));
$days = $days->format('%a');

if ($days > 1 && $periodicidad == 0) 
    $periodicidad = 1;

$carga = !is_null($_GET['carga']) ? $_GET['carga'] : $obj->GetCarga();
if (empty($carga)) 
    $carga = null;

$dayweek = !is_null($_GET['dayweek']) ? $_GET['dayweek'] : $obj->GetdayWeek();
if (empty($dayweek)) 
    $dayweek = null;

$saturday = !is_null($_GET['saturday']) ? $_GET['saturday'] : $obj->saturday;
$sunday = !is_null($_GET['sunday']) ? $_GET['sunday'] : $obj->sunday;
$freeday = !is_null($_GET['freeday']) ? $_GET['freeday'] : $obj->freeday;

$sendmail = !empty($_GET['sendmail']) ? urldecode($_GET['sendmail']) : $obj->GetSendMail();

$obj->SetYear($year);

if (!empty($id_auditoria)) {
    $obj->SetIdProceso(null);
    /*
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;

    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;
     */
}

$disabled = ($action == 'update') ? "disabled='disabled'" : "";

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger = new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);
$badger->set_planaudit();

$obj_user = new Tusuario($clink);
if ($badger->freeassign)
    $obj_user->set_use_copy_tusuarios(false);
else
    $obj_user->set_use_copy_tusuarios(true);

$obj_user->SetIdProceso(null);
$obj_user->set_user_date_ref($fecha_inicio);
$result_user = $obj_user->listar();

$url_page = "../form/fauditoria.php?signal=$signal&action=$action&exect=$action&menu=nota&id_proceso=$id_proceso&year=$year";
$url_page .= "&month=$month&day=$day&id_tipo_auditoria=$id_tipo_auditoria&origen=$origen&init_row_temporary=$init_row_temporary";

add_page($url_page, $action, 'f');

$obj_prs = new Tproceso($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->get_procesos_up_cascade();
$array_procesos_up = array();
foreach ($obj_prs->array_cascade_up as $key => $array)
    $array_procesos_up[$array['id']] = $array;

$obj_prs = new Tproceso($clink);
$obj_prs->SetYear($year);

if ($action == 'add') {
    if (empty($id_proceso)) {
        $id_proceso = $_SESSION['id_entity'];
        $id_proceso_code = $_SESSION['id_entity_code'];
    }
    if (!empty($id_proceso) && $id_proceso != $_SESSION['id_entity']) {
        $id_proceso = $obj_prs->get_proceso_top($id_proceso, null, true);
        $obj_prs->Set($id_proceso);
        $id_proceso_code = $obj_prs->get_id_code();
    }
}

$if_jefe = false;
if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['freeassign'] || $config->freeassign)
    $if_jefe = true;
if (!empty($id_riesgo) && $_SESSION['acc_planaudit'] == 3)
    $if_jefe = true;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>AUDITORIA / CONTROLES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

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

    <script language='javascript' type="text/javascript" charset="utf-8">
        var tipo_form = 'auditoria';
        var text_form = 'de las actividades';

        function set_periodic() {
            if ($('#periodic1').is(':checked')) {
                $('#label_cant_days').show();
                $('#cant_days').show();
                if (parseInt($('#cant_days').val()) < 1)
                    $('#cant_days').val(1);
                $('#_periodic').val(1);

                var d = parseInt(DiferenciaFechas($("#fecha_fin").val(), $("#fecha_inicio").val(), 'd'));

                if (d == 0) {
                    $('#periodic1').prop('checked', false);
                    $('#periodic0').prop('checked', true);
                    $('#_periodic').val(0);

                    $('#periodic1').focus(focusin($('#periodic1')));
                    alert("No puede planificar una auditoría o acción de control repetitiva en un solo día");
                    return;
                }

            } else {
                $('#label_cant_days').hide();
                $('#cant_days').hide();
                $('#_periodic').val(0);
            }
        }

        function test_cant_days() {
            var d = 0;
            var cant_days = parseInt($('#cant_days').val());

            if (cant_days < 1) {
                $('#cant_days').focus(focusin($('#cant_days')));
                alert(
                    "Una acción de control o auditoría transcurre al menos en un día. El sistema asumirá que se trata de un solo día"
                );
                cant_days = 1;
            }

            function _this_1() {
                if ($('#periodic1').is(':checked')) {
                    if ($('#periodicidad0').is(':checked')) {
                        $("#periodic0").attr("checked", true);
                        d = parseInt(DiferenciaFechas($('#fecha_fin').val(), $('#fecha_inicio').val(), 'd'));
                        $('#cant_days').val(d == 0 ? 1 : (cant_days > 1 && cant_days <= d ? cant_days : d));
                        return true;
                    }
                    if ($('#periodicidad1').is(':checked') && (!Entrada($('#input_carga1').val()) || (Entrada($(
                            '#input_carga1').val()) && parseInt($('#input_carga1').val()) == 0))) {
                        $('#input_carga1').focus(focusin($('#input_carga1')));
                        text= "Usted ha escogido una periodicidad diaria. Debe especificar frecuencia en diás ";
                        text+= "con que se ejecutará de la tarea.";
                        alert(text);
                        return false;
                    }
                    if ($('periodicidad1').is(':checked') && cant_days >= $('#input_carga1').val()) {
                        $('#input_carga1').focus(focusin($('#input_carga1')));
                        text= "La duración de las auditorias no puede ser superior a los " + $('#input_carga1').val() + " ";
                        text+= "días según la periodicidad elegida.";
                        alert(text);
                        return false;
                    }
                    if ($('#periodicidad2').is(':checked')) {
                        var _days = 5;
                        if ($('#saturday').is(':checked'))
                            ++_days;
                        if ($('#sunday').is(':checked'))
                            ++_days;

                        if (cant_days >= _days) {
                            $('#cant_days').focus(focusin($('#cant_days')));
                            text= "La duración de las auditorias no puede ser superior a los " + _days + " ";
                            text+= "según la periodicidad semanal que ha elegido";
                            alert(text);
                            return false;
                        }
                    }
                    if ($('#periodicidad3').is(':checked')) {
                        _days = 24;
                        if ($('#saturday').is(':checked'))
                            ++_days;
                        if ($('#sunday').is(':checked'))
                            ++_days;

                        if (cant_days > _days) {
                            $('#cant_days').focus(focusin($('#cant_days')));
                            alert('La duración de las auditorias no puede ser superior a los ' + _days);
                            return false;
                        }

                        $('#cant_days').focus(focusin($('#cant_days')));
                        text= "Debe de ser cuidadoso con los días que dedicara a cada auditoria y los días que ha ";
                        text+= "seleccionado en el almanaque.";
                        alert(text);
                        return true;
                    }
                }

                return true;
            }

            function _this_2() {
                $('#cant_days').val(d == 0 ? 1 : (cant_days > 1 && cant_days <= d ? cant_days : d));
            }

            if ($('#periodic0').is(':checked') && $('#periodicidad0').is(':checked')) {
                d = parseInt(DiferenciaFechas($('#fecha_fin').val(), $('#fecha_inicio').val(), 'd'));

                if (cant_days == 0 || (cant_days > 1 && cant_days > d)) {
                    $('#cant_days').focus(focusin($('#cant_days')));
                    var text = "Es una acción no repetitiva en el periodo por lo que el sistema identifica una auditoría ";
                    text += "o acción de control que durará " + d + " días. ¿Desea continuar?";
                    confirm(text, function(ok) {
                        if (!ok)
                            return false;
                        else {
                            _this_2();
                            if (!_this_1())
                                return false;
                            else
                                return true;
                        }
                    });
                }

            } else {
                if (!_this_1())
                    return false;
                else
                    return true;
            }

            return true;
        }

        function validar() {
            var text;

            if ($('#origen').val() == 0) {
                $('#origen').focus(focusin($('#origen')));
                alert("Debe especificar sí se trata de una auditoría o acción de control, y sí su origen es interno o externo.");
                return;
            }
            if ($('#tipo').val() == 0) {
                $('#tipo').focus(focusin($('#tipo')));
                alert('No ha especificado el tipo de auditoria o de acción de control');
                return;
            }

            var origen = $('#origen').val();

            if (origen == <?= _NOTA_TIPO_SUPERVICION_EXTERNA ?> || origen == <?= _NOTA_TIPO_AUDITORIA_EXTERNA ?>) {
                if (!Entrada($('#organismo').val())) {
                    $('#organismo').focus(focusin($('#organismo')));
                    alert('Debe especificar el organismo o la entidad auditora.');
                    return;
                }
                if (!Entrada($('#jefe_equipo').val())) {
                    $('#jefe_equipo').focus(focusin($('#jefe_equipo')));
                    alert('Debe registrar el nombre y cargo del jefe del equipo auditor');
                    return;
                }
            }
            if ($('#responsable1').is(':checked') && !Entrada($('#custom-combobox-subordinado').val())) {
                $('#responsable1').focus(focusin($('#responsable1')));
                alert('No ha seleccionado al subordinado responsable.');
                return;
            }
            if ($('#responsable2').is(':checked') && $('#subordinado').val() == 0) {
                $('#subordinado').focus(focusin($('#subordinado')));
                alert('No se ha especificado el usuario responsable interno de la actividad de control');
                return;
            }
            if ($('#responsable2').is(':checked') && !Entrada($('#funcionario').val())) {
                $('#funcionario').focus(focusin($('#funcionario')));
                text= "No se ha especificado el cargo del funcionario externo a la Empresa, responsable de la cita o actividad";
                alert(text);
                return;
            }

            <?php if ($config->freeassign) { ?>
                if ($('#responsable3').is(':checked') && !Entrada($('#custom-combobox-usuario').val())) {
                    $('#usuario').focus(focusin($('#usuario')));
                    alert('No ha seleccionado al usuario del sistema que será el responsable.');
                    return;
                }
            <?php } ?>

            if (($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) && $('#nivel').val() < 3) {
                $('#toshow1').focus(focusin($('#toshow1')));
                text= "Solo un usuario con el nivel de de acceso al sistema de PLANIFICADOR o superior, ";
                text+= "puede incorporar eventos a los Planes de Trabajo Generales.";
                alert(text);
                return;
            }
            if ($('#toshow2').is(':checked') && $('#tipo_actividad1').val() == 0) {
                $('#toshow2').focus(focusin($('#toshow2')));
                text= "Cuando se trata de una actividad a incluirse en los Planes Generales debe especificar el tipo de actividad."
                alert(text);
                return;
            }

            if (Entrada($('#numero_plus').val()) && !validate_numero_plus($('#numero_plus').val())) {
                return false;
            }
            
            if (!Entrada($('#lugar').val())) {
                $('#lugar').focus(focusin($('#lugar')));
                alert('Debe definir el alcance (lugares, actividades, etc)');
                return;
            }
            if (!Entrada($('#descripcion').val())) {
                $('#descripcion').focus(focusin($('#descripcion')));
                alert("Debe definir los objetivos que se persiguen con esta acción de control o auditoría");
                return;
            }
            if (!$('#periodic0').is(':checked') && !$('#periodic1').is(':checked')) {
                $('#periodic0').focus(focusin($('#periodic0')));
                text= "Debe especificar si se trata de una sola auditoría o acción de control a realizar en el periodo; ";
                text+= "o si se están planificando varias auditorías o controles en el mismo periodo.";
                alert(text);
                return;
            }

            if (!validar_interval(0))
                return;

            if (!test_cant_days())
                return;

            function _this_4() {
                if ($('#periodicidad4').is(':checked')) {
                    if (create_chain() == 0) {
                        $('#periodicidad4').focus(focusin($('#periodicidad4')));
                        alert("No ha especificados los días en que se realizará la actividad");
                        return false;
                    }
                }
                if (parseInt($('#cant_multiselect-prs').val()) == 0) {
                    $('#cant_multiselect-prs').focus(focusin($('#cant_multiselect-prs')));
                    alert("Debe de especificar los procesos o Unidades Organizativas que serán supervisados o auditados");
                    return false;
                }

                document.forms[0].action = '../php/auditoria.interface.php?init_row_temporary=<?= $init_row_temporary ?>';

                parent.app_menu_functions = false;
                $('#_submit').hide();
                $('#_submited').show();

                document.forms[0].submit();
            } // _this_4()

            function _this() {
                if (parseInt($('#no_reject').val())) {
                    text = "Hay actividades que ya están aprobadas o registradas como cumplidas en el los Planes Individuales. "
                    text+= "Al cambiar las fechas y reprogramar se perderan la información asociada. ";
                    text+= "Desea realmente eliminarlas y perder esta información?";

                    confirm(text, function(ok) {
                        if (ok) {
                            $('input-calendar-go_delete').val(1);
                            _this_4();
                        } else {
                            $('input-calendar-go_delete').val(0);
                            _this_4();
                        }
                    });
                } else {
                    _this_4();
                }
            } // _this()

            if (!$('#periodicidad0').is(':checked') && ($('#periodic1').is(':checked') && parseInt($('#cant_days').val()) <=
                    1)) {
                $('#cant_days').val(1);
                $('#cant_days').focus(focusin($('#cant_days')));
                text = "Usted ha definido una acción periódica, por defecto el Sistema asume que se trata de una acción ";
                text+= "diaría dentro del intervalo de fechas seleccionado. ";
                text += "Puede cancelar la operación y modificar la cantidad de días o continuar. ¿Desea continuar?";

                confirm(text, function(ok) {
                    if (!ok)
                        return;
                    else {
                        if (!_this())
                            return;
                    }
                });
            } else {
                if (!_this())
                    return;
            }
        }

        function refresh_origen() {
            var origen = $('#origen').val();

            if (origen == <?= _NOTA_TIPO_SUPERVICION_EXTERNA ?> || origen == <?= _NOTA_TIPO_AUDITORIA_EXTERNA ?>) {
                $('#tr_organismo').show();
                $('#tr_jefe_equipo').show();
            } else {
                $('#tr_organismo').hide();
                $('#tr_jefe_equipo').hide();
            }
        }

        function select_lsub() {
            $('#tr_subordinado').hide();
            $('#option_usr').hide();
            $('#tr_usuario').hide();
            $('#tr_funcionario').hide();

            if ($('#responsable1').is(':checked')) {
                $('#tr_subordinado').show();

                if ($('#subordinado').val() == $('#id_usuario').val())
                    $('#subordinado').val(0);
            }

            if ($('#responsable2').is(':checked')) {
                $('#tr_funcionario').show();

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
                }

                if ($('#toshow1').is(':checked') || $('#toshow2').is(':checked')) {
                    $('#tr-tipo_actividad1').show();
                    $('#tr-tipo_actividad2').show();
                    $('#tr-tipo_actividad3').show();

                    $('#div-inductores').show();
                }
            } catch (e) {}

            validate_act();
        }

        function validate_act() {
            if ($('#toshow2').is(':checked')) {
                $('#toshow1').prop('checked', true);
                $('#toshow0').prop('checked', true);
            }
            if ($('#toshow1').is(':checked')) {
                $('#toshow0').prop('checked', true);
            }
        }

        function refreshp() {
            var action = $('#exect').val();
            var empresarial = $('#tipo_actividad1').val();
            var signal = $('#signal').val();
            var _radio_date = $('#_radio_date').val();
            var tipo = $('#tipo').val();
            var origen = $('#origen').val();
            var id = $('#id').val();
            var lugar = $('#lugar').val();
            var descripcion = $('#descripcion').val();

            var cant_days = $('#cant_days').val();

            if ($('#periodic0').is(':checked'))
                $('#_periodic').val(0);
            else
                $('#_periodic').val(1);

            var periodic = $('#_periodic').val();

            if ($('#responsable0').is(':checked'))
                id_responsable = $('#id_usuario').val();

            if ($('#responsable1').is(':checked'))
                id_responsable = $('#subordinado').val();

            if ($('#responsable2').is(':checked'))
                id_responsable = $('#usuario').val();

            var jefe_equipo;
            var organismo;

            if (origen == <?= _NOTA_TIPO_SUPERVICION_EXTERNA ?> || origen == <?= _NOTA_TIPO_AUDITORIA_EXTERNA ?>) {
                jefe_equipo = encodeURI($('#jefe_equipo').val());
                organismo = encodeURI($('#organismo').val());
            }

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
            for (i = 1; i < 5; i++) {
                if ($('#periodicidad' + i).is(':checked'))
                    periodicidad = $('#periodicidad[i]').val();
            }

            var dayweek;
            var carga;

            if (periodicidad == 1)
                carga = $('#input_carga1').val();

            if (periodicidad == 2)
                for (i = 1; i < 8; i++)
                    if ($('#dayweek' + i).is(':checked')) dayweek += '-' + i;

            if (periodicidad == 3) {
                carga = $('#input_carga4').val();
                dayweek = $('#dayweek0').val();
            }

            var saturday = $('#saturday').is(':checked') ? 1 : 0;
            var sunday = $('#sunday').is(':checked') ? 1 : 0;
            var freeday = $('#freeday').is(':checked') ? 1 : 0;

            var url = 'fauditoria.php?version=&action=' + action + '&id=' + id +
                '&empresarial=' + empresarial + '&id_responsable=' + id_responsable;
            url += '&year=' + year + '&month=' + month + '&_radio_date=' + _radio_date + '&lugar=' + encodeURI(lugar) +
                '&descripcion=' + encodeURI(descripcion);
            url += '&fecha_origen=' + encodeURI(fecha_origen) + '&fecha_termino=' + encodeURI(fecha_termino) +
                '&fecha_inicio=' + encodeURI(fecha_inicio);
            url += '&fecha_fin=' + encodeURI(fecha_fin) + '&tipo=' + tipo + '&origen=' + origen + '&cant_days=' +
                cant_days + '&periodic=' + periodic;
            url += '&periodicidad=' + periodicidad + '&saturday=' + saturday + '&freeday=' + freeday + '&sunday' + sunday;
            url += '&user_check=' + user_check;

            if (organismo != undefined)
                url += '&organismo=' + organismo;
            if (dayweek != undefined)
                url += '&dayweek=' + dayweek;
            if (carga != undefined)
                url += '&carga=' + carga;
            if (jefe_equipo != undefined)
                url += '&jefe_equipo=' + jefe_equipo;

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            self.location.href = url + '&signal=' + signal + '&toshow=' + toshow;
        }
    </script>

    <?php
    $id = $id_auditoria;
    $table = 'tauditorias';
    require "inc/calendar.inc.php";
    ?>

    <script type="text/javascript">
        var focusin;
        $(document).ready(function() {
            validate_act();
            set_periodic();
            refresh_origen();

            set_calendar();

            <?php
            $id = $id_auditoria;
            $user_ref_date = $fecha_fin;
            $restrict_prs = null;
            ?>

            var user_date_ref = $('#fecha_inicio').val();

            $.ajax({
                data: {
                    "signal": "auditoria",
                    "id_auditoria": <?= !empty($id) ? $id : 0 ?>,
                    "tipo_plan": <?= _PLAN_TIPO_AUDITORIA ?>,
                    "year": <?= !empty($year) ? $year : date('Y') ?>,
                    "user_ref_date": '<?= !empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s') ?>',
                    "id_user_restrict": <?= !empty($id_user_restrict) ? $id_user_restrict : 0 ?>,
                    "restrict_prs": <?= !empty($restrict_prs) ? '"' . serialize($restrict_prs) . '"' : 0 ?>,
                    "use_copy_tusuarios": <?= $use_copy_tusuarios ? $use_copy_tusuarios : 0 ?>,
                    /*
                    "array_usuarios" : <?= !empty($array_usuarios) ? '"' . urlencode(serialize($array_usuarios)) . '"' : 0 ?>,
                    "array_grupos" : <?= !empty($array_grupos) ? '"' . urlencode(serialize($array_grupos)) . '"' : 0 ?>,
                    */
                    "if_jefe": <?= $if_jefe ? 1 : 0 ?>,
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
                    "name": "usuario",
                    "plus_name": "",
                    "id_responsable": <?= !empty($id_responsable) ? $id_responsable : 0 ?>,
                    "id_proceso": <?= $_SESSION['id_entity'] ?>,
                    "year": <?= $year ?>,
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
                    "id_usuario": <?= !empty($id_usuario) ? $id_usuario : 0 ?>,
                    "id_responsable": <?= !empty($id_responsable) ? $id_responsable : 0 ?>,
                    "id_proceso": <?= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'] ?>,
                    "year": <?= $year ?>,
                    "fecha_inicio": '<?= $fecha_inicio_plan ?>'
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

            new BootstrapSpinnerButton('spinner-numero', 1, 5000);
            new BootstrapSpinnerButton('spinner-input_carga1', 1, 180);
            new BootstrapSpinnerButton('spinner-input_carga4', 1, 31);

            divYearCalendar(<?= $year ?>, <?= $init_year ?>, <?= $end_year ?>);
            InitCalendarEvent();
            InitDragDrop();

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

            if ($('#periodicidad4').is(':checked'))
                create_chain();

            tinymce.init({
                selector: '#lugar',
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
                $('#lugar').val(<?= json_encode($lugar) ?>);
            } catch (e) {
                ;
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
                $('#descripcion').val(<?= json_encode($objetivos) ?>);
            } catch (e) {
                ;
            }

            <?php if (!is_null($error)) { ?>
                alert("<?= str_replace("\n", " ", addslashes($error)) ?>");
            <?php } ?>
        });
    </script>

</head>


<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">AUDITORIAS</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                        <li id="nav-tab1" class="nav-item" 
                            title="'Alcance de las acciones de control. Actividades y lugares a ser auditados o supervisados">
                            <a class="nav-link" href="tab1">Identificación</a>
                        </li>
                        <li id="nav-tab5" class="nav-item" title="Objetivos definidos para la auditoria o supervisión">
                            <a class="nav-link" href="tab5">Alcance</a>
                        </li>
                        <li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Objetivos</a></li>
                        <li id="nav-tab7" class="nav-item" title="Seleccionar las listas de chequeo que serán aplicadas">
                            <a class="nav-link" href="tab7">Listas de chequeos</a>
                        </li>

                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Planificación del tiempo</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Participantes</a></li>
                        <li id="nav-tab4" class="nav-item" 
                            title="Entidades, direcciones o procesos internos que serán objeto de la auditoria o acción de control">
                            <a class="nav-link" href="tab4">Unidades Organizativas</a>
                        </li>
                    </ul>

                    <form class="form-horizontal" name="fevento" id="fevento" action="javascript:validar()" method="POST">
                        <input type="hidden" id="exect" name="exect" value="<?= $action ?>" />
                        <input type="hidden" id="id" name="id" value="<?= $id_auditoria ?>" />
                        <input type="hidden" id="id_usuario" name="id_usuario" value="<?= $id_usuario ?>" />
                        <input type="hidden" id="nivel" name="nivel" value="<?= $_SESSION['nivel'] ?>" />
                        <input type="hidden" id="menu" name="menu" value="auditoria" />

                        <input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />
                        <input type="hidden" id="id_calendar" name="id_calendar" value="<?= $id_calendar ?>" />

                        <input type="hidden" id="year" name="year" value="<?= $year ?>" />
                        <input type="hidden" id="month" name="month" value="<?= $month ?>" />
                        <input type="hidden" id="day" name="day" value="<?= $day ?>" />

                        <input type="hidden" id="_time_inicio" name="_time_inicio" />
                        <input type="hidden" id="_time_fin" name="_time_fin" />

                        <input type="hidden" id="_chain" name="_chain" value="">
                        <input type="hidden" id="changed_chain" name="changed_chain" value="0">

                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />
                        <input type="hidden" id="id_proceso_code" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

                        <input type="hidden" id="id_responsable" name="id_responsable" value="<?= $id_responsable ?>" />
                        <input type="hidden" id="_id_responsable" name="_id_responsable" value="<?= $obj->GetIdResponsable() ?>" />
                        <input type="hidden" id="_responsable_2_reg_date" name="_responsable_2_reg_date" value="<?= $obj->get_responsable_2_reg_date() ?>" />

                        <input type="hidden" id="_radio_date" name="_radio_date" value=<?= $_radio_date ?> />
                        <input type="hidden" id="fecha_origen" name="fecha_origen" value="<?= odbc2date($fecha_origen) ?>" />
                        <input type="hidden" id="fecha_termino" name="fecha_termino" value="<?= odbc2date($fecha_termino) ?>" />

                        <input type="hidden" id="init_year" name="init_year" value="<?= $init_year ?>" />
                        <input type="hidden" id="end_year" name="end_year" value="<?= $end_year ?>" />

                        <input type="hidden" id="no_reject" name="no_reject" value="<?= $no_reject ?>" />

                        <input type="hidden" id="if_jefe" name="if_jefe" value="<?= $if_jefe ? 1 : 0 ?>" />

                        <input type="hidden" id="acc" name="acc" value="<?= !empty($badger->acc) ? $badger->acc : 0 ?>" />

                        <input type="hidden" id="tipo_actividad_flag" value="" />
                        <input type="hidden" id="ifgrupo0" name="ifgrupo" value="0" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Origen:
                                </label>
                                <div class=" col-md-4">
                                    <select id="origen" name="origen" class="form-control" onchange="refresh_origen()">
                                        <option value="0">...</option>
                                        <?php for ($i = 1; $i < _MAX_TIPO_NOTA_ORIGEN; ++$i) { ?>
                                            <option class="form-control" value="<?= $i ?>" <?php if ($i == $origen) echo "selected='selected'"; ?>>
                                                <?= $Ttipo_nota_origen_array[$i] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-md-2">
                                    Tipo:
                                </label>
                                <div class=" col-md-4">
                                    <?php
                                    $obj_audit = new Ttipo_auditoria($clink);
                                    $obj_audit->SetIdProceso($id_proceso);
                                    $result_audit = $obj_audit->listar();

                                    while ($row = $clink->fetch_array($result_audit)) {
                                    ?>
                                        <input type="hidden" id="id_tipo_auditoria<?= $row['id'] ?>" name="id_tipo_auditoria<?= $row['id'] ?>" value="<?= $row['id_code'] ?>" />
                                    <?php } ?>

                                    <select id="tipo_auditoria" name="tipo_auditoria" class="form-control" onchange="refresh_origen()">
                                        <option value="0">...</option>
                                        <?php
                                        $clink->data_seek($result_audit);
                                        while ($row = $clink->fetch_array($result_audit)) {
                                        ?>
                                            <option value="<?= $row['id'] ?>" <?php if ($row['id'] == $id_tipo_auditoria) echo "selected" ?>>
                                                <?= $row['nombre'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <?php $display = ($origen == _NOTA_TIPO_AUDITORIA_EXTERNA || $origen == _NOTA_TIPO_SUPERVICION_EXTERNA) ? 'block-inline' : 'none'; ?>
                            <div id="tr_organismo" style="display:<?= $display ?>" class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Organismo/Entidad Auditora:
                                </label>
                                <div class=" col-md-9">
                                    <input type="text" name="organismo" id="organismo" class="form-control" value="<?= $organismo ?>" />
                                </div>
                            </div>
                            <div id="tr_jefe_equipo" style="display:<?= $display ?>" class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Jefe del Equipo:
                                </label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="jefe_equipo" name="jefe_equipo" value="<?= $jefe_equipo ?>" />
                                </div>
                            </div>
                            <hr>
                            </hr>

                            <?php unset($obj_user); ?>
                            <div class="form-group row">
                                <label class="col-form-label  col-lg-2">Responsable:</label>

                                <div class="col-10">
                                    <div class="checkbox">
                                        <label>
                                            <?php
                                            $visible1 = 'none';

                                            if (($id_responsable == $_SESSION['id_usuario'] && empty($funcionario)) || $signal == 'calendar') {
                                                $checked0 = "checked='checked'";
                                                $visible0 = 'block';
                                            } else {
                                                $checked0 = '';
                                                $visible0 = "none";
                                            }

                                            $obj_user = new Tusuario($clink);
                                            $email = $obj_user->GetEmail($id_usuario);
                                            ?>
                                            <input type="radio" id="responsable0" onclick="select_lsub()" name="responsable" value="0" <?= $checked0 ?> onclick="select_lsub()" />
                                            El actual usuario <?= stripslashes($email['nombre']) ?>
                                            <?= $email['cargo'] ? ', ' . textparse($email['cargo']) : '' ?>
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <?php
                                            $if_usuario_jefe = false;
                                            if (array_key_exists($id_responsable, (array) $badger->array_usuarios_sub))
                                                $if_usuario_jefe = true;

                                            if (($id_responsable != $id_usuario && $if_usuario_jefe) && empty($funcionario)) {
                                                $checked1 = "checked='checked'";
                                                $visible1 = 'inline-block';
                                            } else {
                                                $checked1 = '';
                                            }
                                            ?>

                                            <input type="radio" id="responsable1" onclick="select_lsub()" name="responsable" value="1" <?= $checked1 ?> onclick="select_lsub()" />
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
                                                $visible1 = 'inline-block';
                                                $visible2 = 'inline-block';
                                            }
                                            ?>
                                            <input type="radio" id="responsable2" onclick="select_lsub()" name="responsable" value="2" <?= $checked2 ?> onclick="select_lsub()" />
                                            Un funcionario externo a la Organización
                                        </label>
                                    </div>

                                    <?php
                                    if (($id_responsable != $id_usuario && !$if_usuario_jefe) && empty($funcionario)) {
                                        $checked3 = "checked='checked'";
                                        $visible3 = 'inline-block';
                                    } else {
                                        $checked3 = '';
                                        $visible3 = 'none';
                                    }

                                    if ($config->freeassign || !empty($badger->acc)) {
                                    ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="radio" id="responsable3" onclick="select_lsub()" name="responsable" value="3" <?= $checked3 ?> onclick="select_lsub()" />
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
                                <label class="col-form-label col-md-2">
                                    Responsable:
                                </label>
                                <div id="usuario-container" class="col-md-8">
                                    <select id="usuario" class="form-control">
                                    </select>
                                </div>
                            </div>

                            <div id="tr_funcionario" class="form-group row" style="display:<?= $visible2 ?>">
                                <label class="col-form-label col-md-4 col-lg-4">
                                    Cargo del funcionario Externo (Responsable):
                                </label>
                                <div class="col-md-8 col-lg-8">
                                    <input type="text" id="funcionario" name="funcionario" class="form-control" value="<?= $funcionario ?>" />
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Seguimiento y chequeo:
                                </label>

                                <div class="col-md-10 col-lg-10">
                                    <div class="form-group row">
                                        <label class="col-form-label col-sm-2">Número:</label>
                                        <div class="col-sm-3">
                                            <div id="spinner-numero" class="input-group spinner">
                                                <input type="text" name="numero" id="numero" class="form-control" value="<?= $numero ?>">
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

                                        <label class="col-form-label col-sm-3">
                                            Ampliar número (opcional):
                                        </label>

                                        <div class="col-sm-3">
                                            <input type="text" id="numero_plus" name="numero_plus" class="form-control" value="<?= $numero_plus ?>" />
                                        </div>

                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="toshow0" id="toshow0" value=0 <?php if (empty($toshow) || $badger->tr_display == 'none') echo "checked='checked'" ?> onclick="select_act(0)" onchange="refreshtab()" />
                                                    Incluir solo en los Planes de Trabajo Individuales
                                                </label>
                                            </div>
                                            <div class="checkbox" id="tr-estrat-1" style="display:<?= $badger->tr_display ?>">
                                                <label>
                                                    <input type="checkbox" name="toshow1" id="toshow1" value="1" <?php if ($toshow == 1) echo "checked='checked'" ?> onclick="select_act(1)" />
                                                    Incluirlo a partir de los Planes Mensuales. (<em>Se incluye en los
                                                        individuales</em>)
                                                </label>
                                            </div>
                                            <div class="checkbox" id="tr-estrat-2" style="display:<?= $badger->tr_display ?>">
                                                <label>
                                                    <input type="checkbox" name="toshow2" id="toshow2" value="2" <?php if ($toshow == 2) echo "checked='checked'" ?> onclick="select_act(2);" />
                                                    Incluirlo a partir de los Planes Anuales. (<em>Se incluye en los
                                                        mensuales y los individuales</em>)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php $visible = ($toshow >= 1) ? 'inline-flex' : 'none'; ?>
                            <div class="form-group row col-12" id="tr-tipo_actividad1" style="display:<?= $visible ?>;">
                                <label class="col-form-label col-md-2">
                                    Capítulo:
                                </label>
                                <div class=" col-md-10">
                                    <select id="tipo_actividad1" name="tipo_actividad1" class="form-control" onchange="refresh_ajax_select(<?= $id_subcapitulo0 ?>, 0, 0)">
                                        <option value=0>...</option>
                                        <?php for ($i = 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) { ?>
                                            <option value="<?= $i ?>" <?php if ($i == $empresarial) echo "selected='selected'" ?>>
                                                <?= number_format_to_roman($i - 1) . '. ' . $tipo_actividad_array[$i] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row col-12" id="tr-tipo_actividad2" style="display:<?= $visible ?>">
                                <label class="col-form-label col-md-2">Sub Capítulo:</label>
                                <div class="col-md-10 ajax-select" id="ajax-tipo-evento">
                                    <select id="tipo_actividad2" class="form-control">

                                    </select>
                                </div>
                            </div>
                            <div class="form-group row col-12" id="tr-tipo_actividad3" style="display:<?= $visible ?>">
                                <label class="col-form-label col-md-2">Epígrafe:</label>
                                <div class="col-md-10 ajax-select" id="ajax-subtipo-evento">
                                    <select id="tipo_actividad3" class="form-control">

                                    </select>
                                </div>
                            </div>
                            <div class="checkbox" id="tr-user_check" style="display:block">
                                <label class="col-form-label">
                                    <input type="checkbox" name="user_check" id="user_check" value="1" <?php if (!empty($user_check)) echo "checked='checked'" ?> />
                                    Las actividades o eventos programados aparecerán <strong>ocultas en los Planes de
                                        Trabajo Individuales</strong>, aun cuando el usuario participe.
                                </label>
                            </div>
                        </div><!-- generales -->

                        <!-- alacance -->
                        <div class="tabcontent" id="tab5">
                            <textarea name="lugar" id="lugar" class="form-control"><?= $lugar ?></textarea>
                        </div><!-- alacance -->

                        <!-- objetivo -->
                        <div class="tabcontent" id="tab6">
                            <textarea name="descripcion" id="descripcion" class="form-control"><?= $objetivos ?></textarea>
                        </div><!-- objetivo -->

                        <!-- Planificacion del tiempo -->
                        <div class="tabcontent" id="tab2">
                            <?php
                            $chk_date_block = false;
                            $chk_cant_day_block = true;
                            require "inc/period_select.inc.php";
                            ?>
                        </div><!-- tab2 Planificacion del Tiempo-->

                        <!-- Listado de procesos -->
                        <div class="tabcontent" id="tab4">
                            <div class="checkbox">
                                <label>
                                    <?php $toworkplan = $obj->GetToworkplan(); ?>
                                    <input type="checkbox" name="sendmail" id="sendmail" value="1" <?php echo empty($sendmail) ? '&nbsp;' : "checked='checked'"; ?> />
                                    Enviar citación o aviso por correo electrónico a todos los participantes
                                    (controladores/auditores y controlados/auditados)
                                </label>
                            </div>
                            <hr>
                            </hr>

                            <?php
                            $id = $id_auditoria;

                            $obj_prs = new Tproceso_item($clink);
                            $obj_prs->SetIdProceso($_SESSION['id_entity']);
                            $obj_prs->SetIdUsuario(null);
                            $obj_prs->SetTipo($_SESSION['entity_tipo']);

                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3)
                                $obj_prs->set_use_copy_tprocesos(false);
                            else
                                $obj_prs->set_use_copy_tprocesos(true);

                            $result_prs_array = $obj_prs->listar_in_order('eq_desc', true, _TIPO_ARC, false, 'asc');
                            $result_prs_array = $obj_prs->get_procesos_down_cascade(null, null, _TIPO_PROCESO_INTERNO, $result_prs_array);

                            $cant_prs = $obj_prs->GetCantidad();

                            if (!empty($id)) {
                                $obj_prs->SetYear($year);
                                $obj_prs->SetIdAuditoria($id);
                                $array_procesos = $obj_prs->GetProcesoAuditoria();
                            }

                            $name_form= "fauditoria";
                            $restrict_prs = array(_TIPO_ARC);
                            $restrict_up_prs = true;
                            $id_prs_restrict = null;
                            
                            $create_select_input= false;
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab4 Procesos-->

                        <!-- Participantes -->
                        <div class="tabcontent" id="tab3">
                            <div class="container-fluid">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="sendmail" id="sendmail" value="1" <?php echo empty($sendmail) ? '&nbsp;' : "checked='checked'"; ?> />
                                        Enviar citación o avisos por correo electrónico
                                    </label>
                                </div>
                                <hr style="margin-bottom: 0px; margin-top: 4px;" />
                            </div>

                            <div id="ajax-tab-users">

                            </div>
                        </div> <!-- tab3 Participantes-->

                        <!-- Listas de chequeo -->
                        <div class="tabcontent" id="tab7">
                            <?php
                            $obj_prs= new Tproceso_item($clink);
                            $obj_prs->SetYear($year);

                            $obj_lista = new Tlista($clink);
                            $obj_lista->SetYear($year);
                            $obj_lista->SetIdProceso($id_proceso);
                            $result = $obj_lista->listar(true);

                            if (!empty($id_auditoria)) {
                                $obj_lista->SetIdAuditoria($id_auditoria);
                                $array_listas = $obj_lista->get_lista_by_auditoria();
                            ?>
                                <?php foreach ($array_listas as $array) { ?>
                                    <input type="hidden" id="chk_list_init_<?= $array['id'] ?>" name="chk_list_init_<?= $array['id'] ?>" value="1" />
                            <?php }
                            } ?>

                            <table id="table-guest" class="table table-hover table-striped" data-toggle="table" data-height="420" data-toolbar="#toolbar" data-search="true" data-show-columns="true">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th></th>
                                        <th>TÍTULO</th>
                                        <th>DESCRIPCIÓN</th>
                                        <th>UNIDADES ORGANIZATIVAS</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $i = 0;
                                    $array_ids= array();
                                    while ($row = $clink->fetch_array($result)) {
                                        if ($array_ids[$row['_id']])
                                            continue;
                                        $array_ids[$row['_id']]= $row['_id'];

                                        $checked = array_key_exists($row['_id'], $array_listas) ? "checked" : null;
                                    ?>
                                        <tr>
                                            <td>
                                                <?= ++$i ?>
                                            </td>
                                            <td>
                                                <input type="hidden" id="id_lista_code_<?= $row['_id'] ?>" name="id_lista_code_<?= $row['_id'] ?>" value="<?= $row['_id_code'] ?>" />
                                                <input type="checkbox" class="chk_list" id="chk_list_<?= $row['_id'] ?>" name="chk_list_<?= $row['_id'] ?>" <?= $checked ?> value="1" />
                                            </td>
                                            <td>
                                                <?= $row['nombre'] ?>
                                            </td>
                                            <td>
                                                <?= textparse($row['descripcion']) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $obj_prs->SetIdLista($row['_id']);
                                                $array_procesos= $obj_prs->getProcesoLista();

                                                $j= 0;
                                                $prs_string= null;
                                                foreach ($array_procesos as $prs) {
                                                    echo $j > 0 ? "<br/>" : "";
                                                    echo "{$prs['nombre']}, {$Ttipo_proceso_array[$prs['tipo']]}";
                                                    ++$j;
                                                }
                                                ?>  
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div><!-- tab7 Listas de chequeo -->


                        <hr>
                        </hr>
                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset" onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button" onclick="open_help_window('../help/04_plan.htm#04_6.3')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div>

    </div> <!-- container -->

    <?php
    $obj = new Tauditoria($clink);
    $time = new Ttime();

    $id = $id_auditoria;
    $table = 'tauditorias';
    include "inc/calendar.inc.bottom.php";
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