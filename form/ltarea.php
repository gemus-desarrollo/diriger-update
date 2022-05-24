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
require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

$_SESSION['debug']= 'no';

$id_redirect= 'ok';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if (!empty($_GET['id_redirect']))
    $id_redirect= $_GET['id_redirect'];
if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
} else {
    $obj= new Ttarea($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : null;
$planning= !is_null($_GET['planning']) ? $_GET['planning'] : 1;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['inicio']) ? date('m', strtotime(urldecode($_GET['inicio']))) : date('m');

$time= new TTime();
$time->splitTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

$fecha_inicio= !empty($_GET['inicio']) ? urldecode($_GET['inicio']) : "1/".$month."/".$year;
$fecha_final= !empty($_GET['fin']) ? urldecode($_GET['fin']) : $lastday."/".$month."/".$year;

$_SESSION['_fecha_inicio']= $fecha_inicio;
$_SESSION['_fecha_final']= $fecha_final;

//temporal
$id_usuario= $_SESSION['id_usuario'];
$id_responsable= $_SESSION['id_usuario'];

$obj->SetIdUsuario($id_usuario);
$obj->SetIdResponsable($id_responsable);

$url_page= "../form/ltarea.php?signal=$signal&action=$action&menu=tarea&id_proceso=$id_proceso&year=$year";
$url_page.= "&month=$month&day=$day&exect=$action";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE TAREAS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <link rel="stylesheet" type="text/css" href="../css/menu.css" />
    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript" type="text/javascript">
    function validar_date() {
        var year = $('#year').val();
        var text;

        var year1 = 0;
        fecha = new Fecha($('#fecha_inicio_list').val());
        year1 = fecha.anio;

        var year2 = 0;
        fecha = new Fecha($('#fecha_fin_list').val());
        year2 = fecha.anio;

        if (year1 != year2 || year1 != year || year2 != year) {
            text = "Debe escoger su intervalo de fechas dentro del año " + year +
                " selecionado. Puede seleccionar otro año para revizar.";
            alert(text);
            return false;
        }
        return true;
    }

    function validar_interval(flag) {
        var fecha_fin_list = $('#fecha_fin_list');
        var fecha_inicio_list = $('#fecha_inicio_list');

        if (!Entrada(fecha_inicio_list.val())) {
            alert('El campo de fecha de inicio del intervalo de busqueda no puede estar vacia.');
            return false;
        } else if (!isDate_d_m_yyyyy(fecha_inicio_list.val())) {
            alert('Fecha de inicio con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
            fecha_inicio_list.val() = '';
            return false;
        }

        if (!Entrada(fecha_fin_list.val())) {
            alert('El campo de fecha final del intervalo de busqueda no puede estar vacia');
            return false;
        } else if (!isDate_d_m_yyyyy(fecha_fin_list.val())) {
            alert('Fecha de finalización con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
            fecha_fin_list.val('');
            return false;
        }

        if (DiferenciaFechas(fecha_fin_list.val(), fecha_inicio_list.val(), 's') < 0) {
            alert('La fecha final del intervalo de busqueda de las tareas no puerde ser anterior a la de inicio.');
            fecha_fin_list.val('');
            return false;
        }

        if (!validar_date())
            return false;

        refreshp();
    }

    function refreshp() {
        var planning = 1;
        if ($('#planning1').is(':checked'))
            planning = 0;
        if ($('#planning2').is(':checked'))
            planning = 1;
        $('#planning').val(planning);

        var inicio = $('#fecha_inicio_list').val();
        var fin = $('#fecha_fin_list').val();
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        var url = 'ltarea.php?version=&action=<?= $action ?>&planning=' + planning +
            '&id_proceso=' + id_proceso;
        url += '&fecha_inicio=' + inicio + '&fecha_final=' + fin + '&year=' + year;

        self.location = url;
    }

    function displayWindow() {
        var w, h, l, t;
        l = screen.width / 4;
        t = 40;

        var title = "ESTADO DE EJECUCIÓN DE LA TAREA";
        w = 550;
        h = 285;

        $('#div-ajax-panel').style.display = 'block';
        displayFloatingDiv('div-ajax-panel', title, w, h, l, t);
    }

    function mostrar(id_tarea) {
        displayWindow();
        var url = 'ajax/ftarea_update.ajax.php?id_tarea=' + id_tarea + '&action=<?= $action ?>';

        var capa = 'div-ajax';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);
    }

    function ejecutar() {
        var url = '../php/tarea.interface.php?';
        var metodo = 'POST';
        var capa = 'div-ajax';
        var valores = $("#ftarea").serialize();
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);
    }

    function imprimir() {
        var fecha_inicio = $('#fecha_inicio_list').val();
        var fecha_fin = $('#fecha_fin_list').val();
        var id_proceso = $('#proceso').val();
        var planning = $('#planning').val();

        var url = '../print/ltarea.php';
        url += '?fecha_inicio=' + encodeURI(fecha_inicio) + '&fecha_final=' + encodeURI(fecha_fin);
        url += '&id_proceso=' + id_proceso + '&planning=' + planning;

        prnpage = window.open(url, "IMPRIMIENDO LISTADO DE TAREAS",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function add() {
        self.location.href =
            'ftarea.php?version=&action=add&signal=list&id_proceso=<?= $id_proceso ?>';
    }

    function enviar_tarea(id, action) {
        function _this() {
            parent.app_menu_functions = false;

            if (action == 'planning') {
                self.location.href = '../form/ftarea_planning.php?id_tarea=' + id + '&action=add';
            } else {
                document.forms[0].exect.value = action;
                document.forms[0].action = '../php/tarea.interface.php?id=' + id;
                document.forms[0].submit();
            }
        }

        var msg =
            "IMPORTANTE!! La tarea será eliminada y a igual que toda referencia a la misma en los proyectos, riesgos, notas y ";
        msg += "calendarios de todos los participantes. Desea continuar?";

        if (action == 'delete') {
            confirm(msg, function(ok) {
                if (!ok)
                    return;
                else
                    _this();
            });
        } else {
            _this();
        }
    }

    function show_filter() {
        displayFloatingDiv('div-filter', false, 50, 0, 10, 10);
    }
    </script>

    <script type="text/javascript" charset="utf-8">
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
        InitDragDrop();

        $('#div_fecha_inicio_list').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$year?>',
            maxDate: '31/12/<?=$year?>',
            autoclose: true,
            inline: true
        });
        $('#div_fecha_fin_list').datepicker({
            format: 'dd/mm/yyyy',
            minDate: '01/01/<?=$year?>',
            maxDate: '31/12/<?=$year?>',
            autoclose: true,
            inline: true
        });
        $('#fecha_inicio_list').change(function() {
            validar_interval();
        });
        $('#fecha_fin_list').change(function() {
            validar_interval();
        });

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
        $obj_prs= new Tproceso($clink);

        if ($id_proceso != -1) {
            $obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['local_proceso_id']);
            $obj_prs->Set();
            $id_proceso_code= $obj_prs->get_id_proceso_code();
            $id_proceso_sup= $obj_prs->GetIdProceso_sup();
            $conectado_prs= $obj_prs->GetConectado();
            $type= $obj_prs->GetTipo();

            $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
        } else {
            $nombre_prs= "Todos los procesos";
        }

         $obj_prs->SetIdProceso($_SESSION['id_entity']);
         $obj_prs->SetTipo($_SESSION['entity_tipo']);
         $obj_prs->SetIdResponsable(null);
         $obj_prs->SetConectado(null);
        ?>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>               
                <a href="#" class="navd-header">
                    LISTADO DE TAREAS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navd-collapse">
                        <?php if ($_SESSION['nivel'] >= _ADMINISTRADOR) { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nueva tarea">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="nav-item">
                            <a href="#" class="" onclick="show_filter()">
                                <i class="fa fa-filter"></i>Filtrar
                            </a>
                        </li>

                        <?php
                            $id_select_prs= $id_proceso;
                            if (empty($id_select_prs)) 
                                $id_select_prs= -1;
                            $restrict_prs= array(_TIPO_DEPARTAMENTO);
                            $show_dpto= true;
                            require "inc/_dropdown_prs.inc.php";
                            ?>

                        <?php
                            $inicio= $year - 5;
                            $fin= $year + 5;

                            $use_select_year= true;
                            $use_select_month= false;
                            $use_select_day= false;
                            require "../form/inc/_dropdown_date.inc.php";
                            ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/manual.html#listas')">
                                    <i class="fa fa-question"></i>Ayuda
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
                    <?=$fecha_inicio?> - <?=$fecha_final?>
                </label>
            </li>
            <li class="col">
                <div class="row">
                    <label class="label">En ejecución:</label>
                    <div class="badge badge-warning">
                        <?=$planning ? "Cualquiera" : "Si"?>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <div class="col-12">
                    <label class="badge badge-danger">
                        <?php if (!empty($id_proceso) && $id_proceso != -1) { ?>
                        <?php if ($_connect_prs && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                            class="fa fa-wifi"></i><?php } ?>
                        <?php } ?>
                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>


    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="tarea" />

        <input type="hidden" name="planning" id="planning" value="<?=$planning?>" />

        <div class="app-body container-fluid table threebar">
            <table id="table" class="table table-striped" data-toggle="table" data-pagination="true" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th></th>
                        <th>TAREA</th>
                        <th>%</th>
                        <th>VINCULACIÓN</th>
                        <th>RESPONSABLE</th>
                        <th>INICIO (plan)</th>
                        <th>FIN(plan)</th>
                        <th>INICIO (real)</th>
                        <th>FIN (real)</th>
                        <th>DESCRIPCIÓN</th>
                        <th>PARTICIPANTES</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
	   	$obj_pr= new Tproyecto($clink);

		$obj_reg= new Tregtarea($clink);
		$obj_reg->SetFecha($fecha_final);

		$obj->SetYear($year);
		$obj->SetIdPrograma($id_programa);
		(!empty($id_proceso) && $id_proceso != -1) ? $obj->SetIdProceso($id_proceso) : $obj->SetIdProceso(null);

		$obj->SetFechaInicioPlan(date2odbc($fecha_inicio));
		$obj->SetFechaFinPlan(date2odbc($fecha_final));

		$result= $obj->listar(true, $planning);

		$i= 0;
		while ($row= $clink->fetch_array($result)) {
                    if (!empty($planning)) if (!empty($row['fecha_fin_real']))
                        continue;
		?>
                    <tr>
                        <td><?= ++$i ?></td>
                        <td>
                            <a class="btn btn-warning btn-sm" href="javascript:#"
                                onclick="enviar_tarea(<?= $row['_id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <?php if ($action != 'list') { ?>
                            <a class="btn btn-danger btn-sm" href="javascript:#"
                                onclick="enviar_tarea(<?= $row['_id'] ?>, 'delete');">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } ?>

                            <?php if (!boolean($row['ifgrupo'])) { ?>
                            <a class="btn btn-info btn-sm" href="javascript:#" title="ver avance"
                                onclick="enviar_tarea(<?= $row['_id'] ?>,'planning');">
                                <i class="fa fa-eye"></i>ver
                            </a>
                            <a class="btn btn-primary btn-sm" href="javascript:#" title="actualizar avance"
                                onclick="mostrar(<?= $row['_id'] ?>,'planning');">
                                <i class="fa fa-check"></i>Registrar
                            </a>
                            <?php } else { ?>
                            <a class="btn btn-primary btn-sm" href="javascript:#" title="planificar avance"
                                onclick="enviar_tarea(<?= $row['_id'] ?>,'planning');">
                                <i class="fa fa-th-list"></i>Planificar
                            </a>
                            <a class="btn btn-primary btn-sm" href="javascript:#" title="actualizar avance"
                                onclick="mostrar(<?= $row['_id'] ?>)">
                                <i class="fa fa-check"></i>Registrar
                            </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?= $row['tarea'] ?>
                            <?php if (boolean($row['ifgrupo'])) { ?>
                            <i class="fa fa-group" title="Grupo de tareas. Agrupa a una o varias tareas"></i>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $avance = $obj_reg->getAvance($row['_id']);
                            echo $avance;
                            ?>
                        </td>
                        <td>
                            <?php
                            $string = $obj->get_riesgos($row['_id']);
                            if (!is_null($string)) {
                            ?>
                            <i class="fa fa-shield" title="<?= $string ?> Vinculada a la Gestión de Riesgos"></i>
                            <?php
                            }

                            $string = $obj->get_notas($row['_id']);
                            if (!is_null($string)) {
                                ?>
                            <i class="fa fa-neuter"
                                title="<?= $string ?> Vinculada a las Notas de Hallazgos (No conformidades, Oportunidades de Mejora)"></i>
                            <?php
                            }

                            if (!empty($row['id_proyecto'])) {
                                $string = $obj->get_proyectos($row['_id']);
                            ?>
                            <i class="fa fa-tasks"
                                title="<?= $string ?> Vinculada a las Notas de Hallazgos (No conformidades, Oportunidades de Mejora)"></i>
                            <?php } ?>

                        </td>
                        <td>
                            <?= $row['responsable'] ?>
                        </td>
                        <td>
                            <?php $fecha = odbc2date($row['fecha_inicio_plan']); ?>
                            <?= $fecha ?>
                        </td>
                        <td>
                            <?php $fecha = odbc2date($row['fecha_fin_plan']); ?>
                            <?= $fecha ?>
                        </td>

                        <?php
                        if (empty($row['fecha_inicio_real']))
                            $fecha = '&nbsp;';
                        else {
                            $fecha = odbc2date($row['fecha_inicio_real']);
                        }
                        ?>

                        <td><?= $fecha ?></td>

                        <?php
                        if (empty($row['fecha_fin_real']))
                            $fecha = '&nbsp;';
                        else {
                            $fecha = odbc2date($row['fecha_fin_real']);
                        }
                        ?>
                        <td>
                            <?= $fecha ?>
                        </td>

                        <td>
                            <?= textparse($row['descripcion']); ?>
                        </td>

                        <td>
                            <?php
                            $string = $obj->get_participantes($row['_id'], 'tarea');
                            echo $string;
                            ?>
                        </td>

                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <input type="hidden" id="menu" name="menu" value="tarea" />
    </form>

    <div id="div-ajax-panel" class="ajax-panel">

    </div>

    <!-- Panel2 -->
    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-titlecol-10 win-drag">FILTRADO DE LAS TAREAS</div>
                <div class="col-1 pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="form-inline col-md-12">
                    <label class="text">Las tareas que inician o finalizan entre:</label>
                </div>

                <div class="form-inline col-md-12 col-lg-12">
                    <div class="form-group row col-md-6 col-lg-6">
                        <label class="col-form-label col-md-3">Desde:</label>
                        <div class="col-md-8 col-lg-8">
                            <div id="div_fecha_inicio_list" class="input-group date" data-date-language="es">
                                <input id="fecha_inicio_list" name="fecha_inicio_list" class="form-control"
                                    value="<?=$fecha_inicio?>" />
                                <span class="input-group-text"><span
                                        class="fa fa-calendar"></span></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row col-md-6 col-lg-6">
                        <label class="col-form-label col-md-3">Hasta:</label>
                        <div class="col-md-8 col-lg-8">
                            <div id="div_fecha_fin_list" class="input-group date" data-date-language="es">
                                <input id="fecha_fin_list" name="fecha_fin_list" class="form-control"
                                    value="<?=$fecha_final?>" />
                                <span class="input-group-text"><span
                                        class="fa fa-calendar"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <label class="radio text">
                    <input type="radio" name="planning" id="planning1" value="0"
                        <?php if (empty($planning)) echo "checked='checked'" ?> />
                    Solo las que aún se están ejecutando
                </label>
                <label class="radio text">
                    <input type="radio" name="planning" id="planning2" value="1"
                        <?php if (!empty($planning)) echo "checked='checked'" ?> />
                    Todas las tarea planificadas en el periodos
                </label>
            </div>


            <hr />
            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="refreshp()">Filtrar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cerrar</button>
            </div>
        </div>
    </div>

</body>

</html>