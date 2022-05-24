<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug'] = 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/peso.class.php";

$force_user_process = true;
require_once "../php/inc_escenario_init.php";

$signal = !empty($_GET['signal']) ? $_GET['signal'] : 'anual_plan';
$acc = !empty($_SESSION['acc_planwork']) ? $_SESSION['acc_planwork'] : 0;
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

$time = new TTime();
$actual_year = $time->GetYear();
$actual_month = $time->GetMonth();
$actual_day = $time->GetDay();

$inicio = $actual_year - 5;
$fin = $actual_year + 3;
if (empty($hh))
    $hh = $time->GetHour();
if (empty($mi))
    $mi = $time->GetMinute();

$obj = new Tplantrab($clink);
$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetIdUsuario(null);
$obj->SetIdProceso($id_proceso);

$toshow = ($signal == 'anual_plan') ? 2 : 1;
$tipo_plan = ($signal == 'anual_plan') ? _PLAN_TIPO_ACTIVIDADES_ANUAL : _PLAN_TIPO_ACTIVIDADES_MENSUAL;
$obj->SetIfEmpresarial($toshow);

$obj->set_print_reject($print_reject);
$obj->list_reg($toshow);

$url_page = "../html/resume_objs_event.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page .= "&year=$year&month=$month&print_reject=$print_reject&tipo_plan=$tipo_plan";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>RESUMEN DE ACTIVIDADES POR OBJETIVOS DE TRABAJO</title>

    <?php require '../form/inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/general.css?version=">
    <link rel="stylesheet" type="text/css" media="screen" href="../css/custom.css?version=">

    <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css?version=">
    <script type="text/javascript" src="../js/widget.js?version=" charset="utf-8"></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/form.js?version="></script>

    <style type="text/css">
    body {
        background: #fff;
    }
    table {
        border: 1px solid grey;
        font-size: 11px;
        font-weight: normal;        
    }
    th,
    td {
        text-align: center;
        padding: 2px;
    }
    th {
        color: #000000;
        font-weight: bolder;
    }
    .title {
        font-weight: bold;
    }
    .acordion {
        width: 100%;
        height: max-content;
        position: relative;
        border: 1px solid darkgray;
        border-radius: 6px;
    }
    .container {
        position: relative;
        width: 60%;
        border: none;
        margin: 0px;
        padding: 8px 0px;
        float: left;
    }
    .table {
        width: 40%;
        border: none;
        float: right;
    }
    </style>

    <script type="text/javascript">
    function refreshp() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var month = $('#month').val();
        var print_reject = $('#print_reject').val();
        var tipo_plan = $('#tipo_plan').val();

        _alert(text);
        parent.app_menu_functions = false;

        var url = 'resume_objs_event.php?signal=<?= $signal ?>&id_proceso=' + id_proceso + '&year=' + year +
            '&month=' +
            month;
        url += '&print_reject=' + print_reject + '&tipo_plan=' + tipo_plan;
        self.location = url;
    }

    function imprimir(id_objetivo) {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var month = $('#month').val();
        var print_reject = $('#print_reject').val();
        var tipo_plan = $('#tipo_plan').val();

        var url = '../print/resume_objs_event.php?signal=<?= $signal ?>&id_proceso=' + id_proceso + '&year=' + year +
            '&month=' + month;
        url += '&print_reject=' + print_reject + '&tipo_plan=' + tipo_plan + '&id_objetivo=' + id_objetivo;

        prnpage = show_imprimir(url, "IMPRIMIENDO ACTIVIDADES POR OBJETIVOS DE TRABAJO",
            "width=800,height=500,toolbar=no,location=no, scrollbars=yes");
    }

    function closep() {
        var year = $('#year').val()
        var id_proceso = $('#proceso').val()

        var url = "../html/tablero_planning.php?signal=anual_plan&";
        url += '&yea=' + year + '&id_proceso=' + id_proceso;
        self.location.href = url;
    }
    </script>

    <script type="text/javascript">
    function drownpie(id, title, data) {
        // Create the chart
        $('#container' + id).highcharts({
            chart: {
                type: 'pie'
            },
            title: {
                text: title
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '{point.name}: {point.y:.1f}%'
                    }
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px; text-align: left;">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
            },

            series: [{
                type: 'pie',
                name: 'Brands',
                colorByPoint: true,
                data: data
            }],
        });
    }
    </script>

    <script type="text/javascript">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp();
    }

    function _dropdown_month(month) {
        $('#month').val(month);
        refreshp();
    }

    $(document).ready(function() {
        ;
    });
    </script>


</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>  
                <a href="#" class="navd-header">
                    RESUMEN DE ACTIVIDADES
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarUsuarios" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-<?= $signal == 'calendar' ? 'user' : 'industry' ?>"></i>
                                <?= $signal == 'calendar' ? "Usuarios" : "Unidades Organizativas" ?> <span
                                    class="caret"></span>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarUsuarios">
                                <?php
                                $obj_prs = new Tproceso($clink);
                                $obj_prs->SetIdResponsable(null);
                                $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
                                $obj_prs->SetConectado(null);
                                $obj_prs->SetTipo(null);

                                $corte_prs = _TIPO_GRUPO;

                                $exclude_prs = array();
                                $exclude_prs[_TIPO_PROCESO_INTERNO] = 1;

                                if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
                                    $obj_prs->SetIdUsuario(null);
                                    if ($acc == _ACCESO_ALTA && $_SESSION['nivel'] < _SUPERUSUARIO)
                                        $array_procesos = $obj_prs->listar_in_order('eq_desc', false,  $corte_prs, false);
                                    else
                                        $array_procesos = $obj_prs->get_procesos_down($_SESSION['local_proceso_id'], $corte_prs, null, true);
                                } else {
                                    $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
                                    $array_procesos = $obj_prs->get_procesos_by_user('eq_desc', $corte_prs, false, null, $exclude_prs);
                                }

                                ?>

                                <?php if (!is_null($array_procesos)) { ?>
                                <?php
                                if (!array_key_exists($id_proceso, (array)$array_procesos))
                                    $id_proceso = null;

                                foreach ($array_procesos as  $array) {
                                    if (empty($array['id']))
                                        continue;
                                    if (empty($id_proceso))
                                        $id_proceso = $array['id'];

                                    if ((($signal == "anual_plan" || $signal == "anual_plan_meeting"  || $signal == "anual_plan_audit") && !$config->seeanualplan)
                                        || ($signal == "mensual_plan" && !$config->seemonthplan)
                                    ) {

                                        if ((empty($acc) || $acc < 3) && ($_SESSION['local_proceso_id'] == $array['id'] || $array['tipo'] <= $_SESSION['local_proceso_tipo'])) {
                                            if (!array_key_exists($array['id'], $array_chief_procesos)) {
                                                if ($id_proceso == $array['id'])
                                                    $id_proceso = null;
                                                continue;
                                            }
                                        }
                                    }

                                    if ($array['tipo'] >= _TIPO_PROCESO_INTERNO && ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL))
                                        continue;

                                    require "../form/inc/_tablero_tabs_proceso.inc.php";
                                }

                                $_SESSION['id_proceso'] = $id_proceso;
                            }
                            ?>
                            </ul>
                        </li>

                        <?php
                        $use_select_year = true;
                        $use_select_month = ($signal == 'mensual_plan' || $signal == 'calendar') ? true : false;
                        $use_select_day = false;
                        require "../form/inc/_dropdown_date.inc.php";
                        ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir(0)">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('<?= $help ?>')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="#" onclick="closep()">
                                    <i class="fa fa-close"></i>Cerrar
                                </a>
                            </li>
                            </ul>
                        </div>
                    </div>    
                </nav>
            </div>    
        </div>

        <div class="app-body container-fluid table onebar">
            <input type="hidden" id="menu" name="menu" value="anual" />
            <input type="hidden" id="proceso" name="proceso" value="<?= $id_proceso ?>" />
            <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />

            <input type="hidden" id="day" name="day" value="<?= $day ?>" />

            <?php if ($signal === 'anual_plan' || $signal == 'anual_plan_audit' || $signal == 'anual_plan_meeting') { ?>
            <input type="hidden" id="month" name="month" value="<?= $month ?>" />
            <?php } ?>

            <input type="hidden" id="empresarial" value="<?= $empresarial ?>" />
            <input type="hidden" id="print_reject" value="<?= $print_reject ?>" />

            <input type="hidden" id="id_usuario" value="<?= $_SESSION['id_usuario'] ?>" />
            <input type="hidden" id="nivel" value="<?= $_SESSION['nivel'] ?>" />
            <input type="hidden" id="acc_planwork" name="acc_planwork" value="<?= $acc_planwork ?>" />
            <input type="hidden" id="tipo_plan" name="tipo_plan" value="<?= $tipo_plan ?>" />

            <div class="panel-group" id="accordion" style="margin: 20px 10px 10px 20px;">
                <?php
                $obj_objt = new Tinductor($clink);
                $obj_objt->SetIdProceso($id_proceso);
                $obj_objt->SetYear($year);

                $result_objt = $obj_objt->listar();

                $obj_peso = new Tpeso($clink);
                $obj_peso->toshow = $toshow;
                $obj_peso->if_teventos = $obj->if_teventos;
                $obj_peso->if_treg_evento = $obj->if_treg_evento;

                $obj_peso->SetIdProceso($id_proceso);
                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                while ($row = $clink->fetch_array($result_objt)) {
                    $obj_peso->listar_eventos_ref_inductor($row['_id'], false);

                    $total = 0;
                    $plan = 0;
                    $cumplidas = 0;
                    $incumplidas = 0;
                    $noiniciadas = 0;
                    $canceladas = 0;
                    $rechazadas = 0;
                    $externas = 0;

                    if (count($obj_peso->array_eventos) == 0)
                        continue;

                    foreach ($obj_peso->array_eventos as $evento) {
                        ++$total;
                        if (array_key_exists($evento['id'], $obj->cumplidas_list))
                            ++$cumplidas;
                        if (array_key_exists($evento['id'], $obj->incumplidas_list))
                            ++$incumplidas;
                        if (array_key_exists($evento['id'], $obj->no_iniciadas_list))
                            ++$noiniciadas;
                        if (array_key_exists($evento['id'], $obj->rechazadas_list))
                            ++$rechazadas;
                        if (array_key_exists($evento['id'], $obj->canceladas_list))
                            ++$canceladas;
                        if (array_key_exists($evento['id'], $obj->externas_list))
                            ++$externas;
                    }

                    if ($total > 0) {
                        $_cumplidas = ((float)$cumplidas / $total) * 100;
                        $_incumplidas = ((float)$incumplidas / $total) * 100;
                        $_noiniciadas = ((float)$noiniciadas / $total) * 100;
                        $_rechazadas = ((float)$rechazadas / $total) * 100;
                        $_canceladas = ((float)$canceladas / $total) * 100;
                        $_externas = ((float)$externas / $total) * 100;

                        $value = ($total > 0) ? ($cumplidas / ($total/* - $ext*/)) * 100 : null;
                    } else {
                        $_cumplidas = 0;
                        $_incumplidos = 0;
                        $_noiniciadas = 0;
                        $_rechazadas = 0;
                        $_canceladas = 0;
                        $_externas = 0;

                        $value = null;
                    }

                    $title = $row['_numero'] . ' ' . $row['_nombre'];
                    if ($total == 0)
                        $percent = 0;
                    else {
                        $percent = number_format(($cumplidas / $total) * 100, 1);
                        $title .= "<br/><p style='font-size:0.8em'>CUMPLIMIENTO:</p>$percent%";
                    }
                    ?>

                <div class="card card-default" style="margin-bottom: 15px;">

                    <div class="accordion" style="padding: 20px;">
                        
                            <div id="container<?= $row['id'] ?>" class="container clearfix">

                            </div>

                            <div class="table clearfix">
                                <div class="row d-block">
                                    <button type="button" class="btn btn-primary float-right d-none d-lg-block" onclick="imprimir(<?= $row['_id'] ?>)">
                                        <i class="fa fa-print">Imprimir</i>
                                    </button>
                                </div>

                                <table>
                                    <thead>
                                        <tr>
                                            <th rowspan="2">TOTAL DE TAREAS <br />PLANIFICADAS</th>
                                            <th colspan="3">DE ELLAS</th>
                                            <th rowspan="2">EXTERNAS</th>
                                        </tr>
                                        <tr>
                                            <th>CUMPLIDAS</th>
                                            <th>INCUMPLIDAS</th>
                                            <th>SUSPENDIDAS <br />O<br /> POSPUESTAS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= $total ?></td>
                                            <td><?= $cumplidas ?></td>
                                            <td><?= $incumplidas ?></td>
                                            <td><?= $canceladas ?></td>
                                            <td><?= $externas ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>

                <script type="text/javascript">
                data_<?= $row['id'] ?> = [
                    ['No iniciadas', <?= $_noiniciadas ?>],
                    ['Incumplidas', <?= $_incumplidas ?>],
                    // ['Rechazada', <?= $_rechazadas ?>],
                    {
                        name: 'Cumplidas',
                        y: <?= $_cumplidas ?>,
                        sliced: true,
                        selected: true
                    },
                    ['Suspendidas y Postpuestas', <?= $_canceladas ?>]
                ];
                drownpie(<?= $row['id'] ?>, "<?= $title ?>", data_<?= $row['id'] ?>);
                </script>
                <?php } ?>
            </div>
        </div>
</body>

</html>