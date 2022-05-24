<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/objetivo_ci.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/plantrab.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "class/list.signal.class.php";

$signal= 'objetivo_ci';
$restrict_prs= array(_TIPO_DIRECCION);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$permit_change= !is_null($_GET['permit_change']) ? $_GET['permit_change'] : false;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_ci= new Tobjetivo_ci($clink);
$obj_user= new Tusuario($clink);
$obj_peso= new Tpeso($clink);

$obj_evento= new Tevento($clink);
$obj_evento->SetYear($year);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$id_proceso_code= $obj_prs->get_id_proceso_code();
$id_proceso_sup= $obj_prs->GetIdProceso_sup();
$conectado= $obj_prs->GetConectado();
$type= $obj_prs->GetTipo();

$nombre= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
$acc= $_SESSION['acc_planrisk'];

$obj_prs->SetIdUsuario($_SESSION['id_usuario']);
$array_procesos= $obj_prs->get_procesos_by_user('eq_desc', _TIPO_PROCESO_INTERNO, false, null);

$edit= ($action == 'edit' || $action == 'add') ? true : false;
if ($edit && ($id_proceso != $_SESSION['local_proceso_id'] && $conectado != _LAN))
    $edit= false;
if ($edit && ($_SESSION['nivel'] < _SUPERUSUARIO && $_SESSION['id_usuario'] != $obj_prs->GetIdResponsable()))
    $edit= false;
if ($edit && (($id_proceso_sup == $_SESSION['local_proceso_id'] || empty($id_proceso_sup)) && $conectado == _LAN))
    $edit= true;
if ($signal == 'objetivo_sup' && $_SESSION['nivel'] >= _SUPERUSUARIO)
    $edit= empty($id_proceso_sup) ? false : true;
if (!empty($acc) && array_key_exists($id_proceso, $array_procesos) == true)
    $edit= true;

$url_page= "../form/lobjetivo_ci.php?signal=$signal&action=$action&menu=objetivo&id_proceso=$id_proceso&year=$year";
$url_page.= "&month=$month&day=$day&exect=$action&permit_change=$permit_change";

set_page($url_page);

function array_concat(&$array1, $array2) {
    foreach ($array2 as $array)
        $array1[$array['id']]= $array;
}
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

    <title>LISTADO DE OBJETVOS DE CONTROL INTERNO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/general.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/custom.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/alarm.css" />
 
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
        border: 1px solid #ccc;
        margin: 0px 10px 0px 10px;
        border: 1px solid #ccc;
    }
    th,
    td {
        text-align: center;
        padding: 2px;
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
    function refreshp() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var url = '&id_proceso=' + id_proceso + '&year=' + year;

        self.location = '../form/lobjetivo_ci.php?action=<?=$action?>' + url;
    }

    function enviar_obj(id, action) {
        var form = document.forms[0];
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var url = '../php/objetivo.interface.php?action=' + action;
        url += '&if_control_interno=1&id_proceso=' + id_proceso + '&year=' + year + '&id=' + id + '&id_objetivo=' + id;

        form.action = url;
        form.submit();
    }

    function add() {
        var form = document.forms[0];
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        var url = '../form/fobjetivo_ci.php?action=add&id_proceso=' + id_proceso + '&year=' + year;

        form.action = url;
        form.submit();
    }

    function closep() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();
        self.location.href = '../html/riesgo.php?action=<?=$action?>&id_proceso=' + id_proceso + '&year=' + year;
    }

    function imprimir(id_objetivo) {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        var url = '../print/lobjetivo_ci.php?signal=<?=$signal?>&id_proceso=' + id_proceso + '&year=' + year;
        url += '&id_objetivo=' + id_objetivo;

        prnpage = show_imprimir(url, "CUMPLIMIENTO DE LOS OBJETIVOS DE CONTROL",
            "width=800,height=500,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script language="javascript">
    parent.app_menu_functions = true;

    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp(0);
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp(0);
    }

    function _dropdown_month(year) {
        $('#month').val(year);
        refreshp(0);
    }

    function show_politica_filter() {
        displayFloatingDiv('div-filter', false, 70, 0, 10, 10);
    }

    $(document).ready(function() {
        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <form name="objForm" id="objForm" action='' method="post">

    </form>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>   

                <a href="#" class="navd-header">
                    OBJETIVOS DE CONTROL
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($action == 'edit' && $edit) { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nuevo <?=$signal?>">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <?php
                        if ($signal != 'objetivo_sup') {
                            $top_list_option= "seleccione........";
                            $id_list_prs= null;
                            $order_list_prs= 'eq_asc_desc';
                            $reject_connected= false;
                            $id_select_prs= $id_proceso;
                            $in_building= false;

                            $restrict_prs= array(_TIPO_GRUPO, _TIPO_DEPARTAMENTO);
                            include "inc/_dropdown_prs.inc.php";
                        }
                        ?>

                        <?php
                        $use_select_year= true;
                        require "inc/_dropdown_date.inc.php";
                        ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir(0)">
                                <i class="fa fa-print"></i>
                                Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('<?=$help?>')">
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
            </div>    
        </nav>
    </div>

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">

            <li class="col">
                <label class="badge badge-success">
                    <?=$year?>
                </label>
            </li>

            <li class="col">
                <label class="badge badge-danger">
                    <?php if ($_connect) { ?><i class="fa fa-wifi"></i><?php } ?>
                    <?= $nombre ?>
                </label>
            </li>
        </ul>
    </div>


    <div class="app-body container-fluid threebar">

        <div class="panel-group" id="accordion" style="margin: 20px 10px 10px 20px;">
            <?php
            $obj_ci->SetIdProceso($id_proceso);
            $obj_ci->set_id_proceso_code($id_proceso_code);
            $obj_ci->SetYear($year);
            $obj_ci->SetMonth(null);
            $result_obj= $obj_ci->listar();

            $array_tareas= null;
            $array_eventos_restricted= array();
            while ($row_obj= $clink->fetch_array($result_obj)) {
                $id_objetivo= $row_obj['_id'];
                $obj_ci->SetIdObjetivo($row_obj['_id']);
                $obj_ci->set_id_objetivo_code($row_obj['_id_code']);

                $array_tareas= $obj_ci->get_tareas(true, true);

                foreach ($array_tareas as $tarea) {
                    $obj_evento->get_eventos_by_tarea($tarea['id']);
                    array_concat($array_eventos_restricted, $obj_evento->array_eventos);
                }

                if (isset($obj_plan)) unset($obj_plan);
                $obj= new Tplantrab($clink);
                $obj->SetYear($year);
                $obj->SetMonth(null);
                $obj->SetIdUsuario(null);
                $obj->SetIdProceso($id_proceso);
                $obj->SetTipoPlan(_PLAN_TIPO_PREVENCION);
                $obj->toshow= _EVENTO_ANUAL;
                $obj->array_eventos_restricted= $array_eventos_restricted;
                $obj->set_print_reject($print_reject);
                $obj->list_reg(_EVENTO_ANUAL);

                $total = 0;
                $plan = 0;
                $cumplidas = 0;
                $incumplidas = 0;
                $noiniciadas = 0;
                $canceladas = 0;
                $rechazadas = 0;
                $externas = 0;

                foreach ($array_eventos_restricted as $id => $row) {
                    ++$total;
                    if (array_key_exists($id, $obj->cumplidas_list))
                        ++$cumplidas;
                    if (array_key_exists($id, $obj->incumplidas_list))
                        ++$incumplidas;
                    if (array_key_exists($id, $obj->no_iniciadas_list))
                        ++$noiniciadas;
                    if (array_key_exists($id, $obj->rechazadas_list))
                        ++$rechazadas;
                    if (array_key_exists($id, $obj->canceladas_list))
                        ++$canceladas;
                }

                if ($total > 0) {
                    $_cumplidas= ((float)$cumplidas/$total)*100;
                    $_incumplidas= ((float)$incumplidas/$total)*100;
                    $_noiniciadas= ((float)$noiniciadas/$total)*100;
                    $_rechazadas= ((float)$rechazadas/$total)*100;
                    $_canceladas= ((float)$canceladas/$total)*100;

                    $value= ($total > 0) ? ($cumplidas/($total/* - $ext*/))*100 : null;
                } else {
                    $_cumplidas= 0;
                    $_incumplidos= 0;
                    $_noiniciadas= 0;
                    $_rechazadas= 0;
                    $_canceladas= 0;

                    $value= null;
                }
                ?>

            <div class="card card-default" style="margin-bottom: 15px; width: 100%;">
                
                    <div class="accordion" style="padding: 20px;">
                    
                        <div id="container<?=$row_obj['_id']?>" class="container clearfix">

                        </div>

                        <div class="table clearfix">

                            <div class="row d-block d-flex flex-row-reverse">
                                <button type="button" class="btn btn-danger"
                                    onclick="enviar_obj(<?=$row_obj['_id']?>, 'delete')">
                                    <i class="fa fa-trash"></i> Eliminar
                                </button>
                                <button type="button" class="btn btn-warning"
                                    onclick="enviar_obj(<?=$row_obj['_id']?>, 'edit')">
                                    <i class="fa fa-pencil"></i> Editar
                                </button>
                                <button type="button" class="btn btn-primary d-none d-lg-block" onclick="imprimir(<?=$row_obj['_id']?>)">
                                    <i class="fa fa-print"></i> Imprimir
                                </button>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th rowspan="2">TOTAL DE TAREAS <br />PLANIFICADAS</th>
                                        <th colspan="3">DE ELLAS</th>
                                    </tr>
                                    <tr>
                                        <th>CUMPLIDAS</th>
                                        <th>INCUMPLIDAS</th>
                                        <th>SUSPENDIDAS <br />O<br /> POSPUESTAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?=$total?></td>
                                        <td><?=$cumplidas?></td>
                                        <td><?=$incumplidas?></td>
                                        <td><?=$canceladas?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
   
                </div>
            </div>

            <script type="text/javascript">
            data_<?=$row_obj['_id']?> = [
                ['No iniciadas', <?=$_noiniciadas?>],
                ['Rechazada', <?=$_rechazadas?>],
                {
                    name: 'Cumplidas',
                    y: <?=$_cumplidas?>,
                    sliced: true,
                    selected: true
                },
                ['Incumplidas', <?=$_incumplidas?>],
                ['Canceladas', <?=$_canceladas?>]
            ];
            drownpie(<?=$row_obj['_id']?>, "<?="{$row_obj['_numero']} {$row_obj['_nombre']}"?>",
                data_<?=$row_obj['_id']?>);
            </script>
            <?php } ?>
        </div>
    </div>

</body>

</html>