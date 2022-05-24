<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/registro.class.php";
require_once "../php/class/unidad.class.php";

require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$actual_year= $_SESSION['current_year'];
$actual_month= $_SESSION['current_month'];
$actual_day= $_SESSION['current_day'];

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'plan';
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;
$year= !empty($_GET['year']) ? $_GET['year'] : $actual_year;
if (empty($year)) $year= date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;

if (empty($month) || $month == -1) {
    if ($year == $actual_year)
        $month= $actual_month;
    else
        $month= 12;
}

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

$day= !empty($_GET['day']) ? $_GET['day'] : null;
if (empty($day)) {
    if ($month != $actual_month || $year != $actual_year) {
        if ($month == $actual_month && $year == $actual_year)
            $day= $actual_day;
        else
            $day= $lastday;
    }
}

$obj= new Tregistro($clink);

$obj->SetYear($year);
$periodicidad= 0;
$trend= null;
$cumulative= false;
$formulated= false;
$unidad= null;

if (!empty($id_indicador)) {
    $obj->SetYear($year);
    $obj->SetIdIndicador($id_indicador);
    $obj->Set($id_indicador);

    $nombre= $obj->GetNombre();
    $periodicidad= $obj->GetPeriodicidad();
    $trend= $obj->GetTrend();
    $id_indicador_code= $obj->get_id_indicador_code();
    $cumulative= $obj->GetIfCumulative();
    $formulated= $obj->GetIfFormulated();

    $id_unidad= $obj->GetIdUnidad();
}

$cumulative= !empty($cumulative) ? 1 : 0;
$formulated= !empty($formulated) ? 1 : 0;

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : 0;
$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : 0;
$id_inductor= !empty($_GET['id_inductor']) ? $_GET['id_inductor'] : 0;

if (!empty($id_perspectiva))
    $obj->SetIdPerspectiva($id_perspectiva);
if (!empty($id_inductor))
    $obj->SetIdInductor($id_inductor);

$obj->SetYear($year);

$inicio= $actual_year - 1;
$fin= $actual_year + 1;

$_SESSION['obj']= serialize($obj);

$pmonth= empty($periodicidad) ? 1 : $periodicidad_value[$periodicidad];

if (!empty($_GET['error']))
    $error= urldecode($_GET['error']);

$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

$url_page= "../form/plan.php?signal=$signal&action=$action&menu=plan&year=$year&month=$month&day=$day";
$url_page.= "&exect=$action&id_tablero=$id_tablero";
add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>PLANIFICACIÓN DE INDICADORES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <link rel="stylesheet" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
    label.thin,
    div.thin {
        padding: 3px 3px 3px 5px !important;
        margin: 2px 2px 4px 2px !important;
    }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    var month = null;
    var day = null;

    var _month = null;
    var _day = null;

    function refreshp(index) {
        var id_perspectiva = $('#perspectiva').val();
        var id_inductor = $('#inductor').val();
        var id_indicador = $('#indicador').val();
        var year = $('#_year').val();

        try {
            month = $('#month').val();
            day = $('#day').val();
        } catch (e) {
            month = $('#_month').val();
            day = $('#_day').val();
        }

        var signal = $('#signal').val();

        if (index == 1) {
            id_inductor = 0;
            id_indicador = 0;
        }
        if (index == 2)
            id_indicador = 0;

        url = 'plan.php?version=&action=<?= $action ?>&id_perspectiva=' + id_perspectiva;
        url += '&id_inductor=' + id_inductor + '&id_indicador=' + id_indicador + '&year=' + year + '&month=' + month;
        url += '&day=' + day + '&signal=' + signal;

        self.location = url;
    }

    function validar() {
        var pmonth = <?= $pmonth;?>;
        var i;
        var date;
        var text;

        if (pmonth == 0)
            return false;

        for (i = pmonth; i <= 12; i += pmonth) {
            for (j = 1; j <= 31; ++j) {

                if (Entrada($('#plan_' + i + '_' + j).val())) {
                    if (!IsNumeric($('#plan_' + i + '_' + j).val())) {
                        date = j + " de " + monthNames[i];
                        text = "Existe un error en el formato del valor plan correspondiente al día " + date;
                        text += ". Por favor, el valor del plan solo admite datos numéricos.";
                        alert(text);
                        return false;
                    }
                    if ($('#trend').val() == 3) {
                        if (Entrada($('#plan_cot_' + i + '_' + j).val())) {
                            if (!IsNumeric($('#plan_cot_' + i + '_' + j).val())) {
                                date = j + " de " + monthNames[i];
                                text =
                                    "Existe un error en el formato del valor plan cota inferior correspondiente al día " +
                                    date;
                                text += ". Por favor, el valor del plan solo admite datos numéricos.";
                                alert(text);
                                return false;
                            }
                        }
                    }
                    if ($('#trend').val() == 3 && (Entrada($('#plan_' + i + '_' + j).val()) || Entrada($('#plan_cot_' +
                            i + '_' + j).val()))) {
                        if (Number($('#plan_cot_' + i + '_' + j).val()) >= Number($('#plan_' + i + '_' + j).val())) {
                            date = j + " de " + monthNames[i];
                            text = "Existe una incongruencia en los valores del plan correspondiente al día " + date;
                            text +=
                                ". El valor del plan cota inferior debe ser estrictamente menor que la cota superior o Plan.";
                            alert(text);
                            return false;
                        }
                        if (!Entrada($('#plan_' + i + '_' + j).val()) || !Entrada($('#plan_cot_' + i + '_' + j)
                        .val())) {
                            date = j + " de " + monthNames[i];
                            text = "Existe una incongruencia en los valores del plan correspondiente al día " + date;
                            text += ". Las dos cotas para el intervalo Plan deben de estar definidas";
                            alert(text);
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    function enviar() {
        var text;

        if (month != null || day != null) {
            $('#observacion_' + month + '_' + day).val($('#observacion').val());
            $('#plan_' + month + '_' + day).val($('#plan').val());

            if ($('#trend').val() == 3) {
                $('#plan_cot_' + month + '_' + day).val($('#plan_cot').val());
            }

            _this();
        } else {
            text =
                "No se le ha asignado valor al Plan, ni se ha registrado observación alguna. En ese caso solo se registraró el criterio ";
            text += "para la comparación de la tendencia entre periodos consecutivos. Desea continuar?";
            confirm(text, function(ok) {
                if (!ok)
                    return;
                else
                    _this();
            });
        }

        function _this() {
            if (validar()) {
                clean_form();

                parent.app_menu_functions = false;
                $('#_submit').hide();
                $('#_submited').show();

                document.forms[0].action = '../php/plan.interface.php';
                document.forms[0].submit();
            }
        }
    }

    function clean_form() {
        var pmonth = monthValues[$('#periodicidad').val()];

        for (i = pmonth; i <= 12; i += pmonth) {
            for (j = 1; j <= 31; ++j) {
                obj_observacion = $('#observacion_' + i + '_' + j);
                obj_plan = $('#plan_' + i + '_' + j);
                obj_plan_cot = $('#plan_cot_' + i + '_' + j);

                if (!Entrada(obj_observacion.value) && !Entrada(obj_plan.value) && !Entrada(obj_plan_cot.value)) {
                    form = obj_observacion.parentNode;
                    form.removeChild(obj_observacion);
                    form.removeChild(obj_plan);
                    form.removeChild(obj_plan_cot);
                }
            }
        }
    }

    function selectday(imonth, iday, year) {
        $('#plan').prop('disabled', false);
        $('#observacion').prop('disabled', false);

        if ($('#trend').val() == 3) {
            $('#plan_cot').prop('disabled', false);
        }
        if (((month != null || day != null) && (month != _month || day != _day)) && (month == $('#imonth').val())) {
            $('#observacion_' + month + '_' + day).val($('#observacion').val());
            $('#plan_' + month + '_' + day).val($('#plan').val());

            if ($('#trend').val() == 3) {
                //$('#plan_cot_'+month+'_'+day).val()= $('#plan_cot').val();
            }
        }

        $('#observacion').val($('#observacion_' + imonth + '_' + iday).val());
        $('#observacion').removeClass('input-unactive');

        $('#plan').val($('#plan_' + imonth + '_' + iday).val());
        $('#plan').removeClass('input-unactive');

        if ($('#trend').val() == 3) {
            $('#plan_cot').val($('#plan_cot_' + imonth + '_' + iday).val());
            $('#plan_cot').removeClass('input-unactive');
        }

        $('#datetxt').val(iday + ' de ' + monthNames[imonth] + ' de ' + year);

        month = imonth;
        day = iday;

        $('#plan').focus();
    }

    function refreshmonth() {
        if ($('#id').val() == 0)
            return;

        try {
            $('#_month').val($('#imonth').val());
            pmonth = monthValues[$('#periodicidad').val()];
            imonth = $('#imonth').val();
        } catch (e) {

        }
        if ((month != null || day != null) && (month != _month || day != _day)) {
            $('#observacion_' + month + '_' + day).val($('#observacion').val());
            $('#plan_' + month + '_' + day).val($('#plan').val());

            if ($('#trend').val() == 3)
                $('#plan_cot_' + month + '_' + day).val($('#plan_cot').val());

            _month = month;
            _day = day;
        }

        for (i = pmonth; i <= 12; i += pmonth) {
            if (i != imonth) $('#cal-' + i).hide();
            else $('#cal-' + i).show();
        }

        $('#datetxt').val("");

        $('#plan').val("");
        $('#plan').prop('disabled', true);
        $('#plan').addClass("input-unactive");

        if ($('#trend').val() == 3) {
            $('#plan_cot').val("");
            $('#plan_cot').prop('disabled', true);
            $('#plan_cot').addClass("input-unactive");
        }

        $('#observacion').val("");
        $('#observacion').prop('disabled', true);
        $('#observacion').addClass("input-unactive");
    }

    function displayWindow() {
        var id_indicador = $('#indicador').val();
        var imonth = $('#imonth').val();
        var iyear = $('#_year').val();

        var iday = day;
        if (day == null)
            iday = 0;

        var url = '../form/ajax/reg_plan.ajax.php?id_indicador=' + id_indicador + '&day=' + iday + '&month=' + imonth +
            '&year=' + iyear;

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);
        displayFloatingDiv('div-ajax-panel', "REGISTRO HISTÓRICOS DE VALORES PLANIFICADOS", 70, 0, 10, 15);
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        var text;
        InitDragDrop();

        //selectday(<?= "$month,$day,$year" ?>);
        refreshmonth();

        if ($('#indicador').val() > 0) {
            $('#fieldset-filtro').hide();
            $('#year').val($('#_year').val());
        }

        if ($('#trend').val() == 0 && $('#id').val() != 0) {
            year = $('#year').val();
            month = $('#month').val();

            text = "Ha este indicador aún no se le ha definido la escala de colores. Deberá de especificar ";
            text += "sí el indicador mejora cuando crece su valor, o cuando disminuye, o si este alcanza los ";
            text += "valores deseables solo dentro de un intervalo especifico.";
            alert(text);
            var url =
                "../php/indicador.interface.php?signal=tablero&action=edit&id=<?= $id_indicador ?>&year=" +
                year + "&month=" + month;
            self.location.href = url;
        }

        <?php if (!empty($id_indicador) && $formulated) { ?>
        $('#tr-plan').hide();
        $('#tr-plan_cot').hide();
        <?php } ?>

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <form action='javascript:enviar()' class="form-horizontal" method=post>
                <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
                <input type="hidden" id="id" name="id" value="<?= $id_indicador ?>" />
                <input type="hidden" id="id_code" name="id_code" value="<?= $id_indicador_code ?>" />
                <input type="hidden" name="menu" value="plan" />
                <input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />

                <input type=hidden name="id_tablero" id="id_tablero" value="<?=$id_tablero?>" />
                
                <input type="hidden" id="year" name="year" value="<?= $year ?>" />
                <input type="hidden" id="_month" name="_month" value="<?= $month ?>" />
                <input type="hidden" id="_day" name="_day" value="<?= $day ?>" />

                <input type="hidden" id="month" name="month" value="<?= $month ?>" />
                <input type="hidden" id="day" name="day" value="<?= $day ?>" />

                <input type="hidden" name="periodicidad" id="periodicidad" value="<?= $periodicidad ?>" />
                <input type="hidden" id="trend" name="trend" value="<?= $trend ?>" />
                <input type="hidden" id="cumulative" name="cumulative" value="<?= $cumulative ?>" />
                <input type="hidden" id="formulated" name="formulated" value="<?= $formulated ?>" />

                <!-- form for filter -->
                <div id="fieldset-filtro" class="row">
                    <div class="col-md-10 col-md-offset-3">
                        <!-- form for filter -->
                        <div class="card card-primary">
                            <div class="card-header">PLANIFICAR INDICADOR</div>

                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Año:</label>
                                    <div class="col-md-2">
                                        <select name=_year id=_year class="form-control"
                                            onchange="javascript:refreshp()">
                                            <option value=0> .... </option>
                                            <?php for ($i = $inicio; $i <= $fin; ++$i) { ?>
                                            <option value="<?= $i ?>"
                                                <?php if ($i == $year) echo "selected='selected'"; ?>><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Perspectiva:</label>
                                    <div class="col-md-8">
                                        <select name="perspectiva" id="perspectiva" class="form-control"
                                            onchange="javascript:refreshp(1)">
                                            <option value="0"></option>
                                            <?php
                                            $obj_perspectiva = new Tperspectiva($clink);
                                            $obj_perspectiva->SetYear($year);
                                            $result_persp = $obj_perspectiva->listar();

                                            while ($row = $clink->fetch_array($result_persp)) {
                                                if ($array_procesos_entity[$row['_id_proceso']]['id_entity'] != $_SESSION['id_entity'])
                                                    continue;
                                                $nombre_prs = $row['proceso'] . ',  ' . $Ttipo_proceso_array[$row['tipo']];
                                            ?>

                                            <option value="<?= $row['_id'] ?>"
                                                <?php if ($row['_id'] == $id_perspectiva) echo "selected='selected'"; ?>>
                                                <?= "{$row['nombre']} ({$row['inicio']}-{$row['fin']}) / {$nombre_prs}" ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Objetivo de Trabajo:</label>
                                    <div class="col-md-10">
                                        <select name="inductor" id="inductor" class="form-control"
                                            onchange="javascript:refreshp(2)">
                                            <option value="0"></option>
                                            <?php
                                            $obj_inductor = new Tinductor($clink);
                                            if (!empty($id_perspectiva))
                                                $obj_inductor->SetIdPerspectiva($id_perspectiva);

                                            $obj_inductor->SetYear($year);
                                            $with_null_perspectiva = !empty($id_perspectiva) ? _PERSPECTIVA_NOT_NULL : _PERSPECTIVA_ALL;
                                            $result_inductor = $obj_inductor->listar($with_null_perspectiva);

                                            while ($row = $clink->fetch_array($result_inductor)) {
                                                if ($array_procesos_entity[$row['id_proceso']]['id_entity'] != $_SESSION['id_entity'])
                                                    continue;
                                                $obj_prs->Set($row['id_proceso']);
                                                $nombre_prs = $obj_prs->GetNombre() . ',  ' . $Ttipo_proceso_array[$obj_prs->getTipo()];
                                            ?>

                                            <option value="<?= $row['_id'] ?>"
                                                <?php if ($row['_id'] == $id_inductor) echo "selected='selected'"; ?>>
                                                <?= "{$row['nombre']} ({$row['inicio']}-{$row['fin']}) / $nombre_prs" ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">Indicador:</label>
                                    <div class="col-md-10">
                                        <select name="indicador" id="indicador" class="form-control"
                                            onchange="javascript:refreshp()">
                                            <option value="0"></option>
                                            <?php
                                            $obj_indicador = new Tindicador($clink);
                                            $obj_indicador->SetYear($year);

                                            if (!empty($id_perspectiva))
                                                $obj_indicador->SetIdPerspectiva($id_perspectiva);
                                            if (!empty($id_inductor))
                                                $obj_indicador->SetIdInductor($id_inductor);

                                            if ($_SESSION['nivel'] == _PLANIFICADOR)
                                                $obj_indicador->SetIdUsrPlan($_SESSION['id_usuario']);
                                            if ($_SESSION['nivel'] > _PLANIFICADOR)
                                                $obj_indicador->SetIdUsrPlan(null);

                                            $result_indi = $obj_indicador->listar();
                                            
                                            $array_ids= array();
                                            while ($row = $clink->fetch_array($result_indi)) {
                                                if ($array_ids[$row['_id']]) 
                                                    continue;
                                                $array_ids[$row['_id']]= true;

                                                if ($array_procesos_entity[$row['id_proceso']]['id_entity'] != $_SESSION['id_entity'])
                                                    continue;

                                                $obj_prs->Set($row['id_proceso']);
                                                $nombre_prs = $obj_prs->GetNombre() . ',  ' . $Ttipo_proceso_array[$obj_prs->getTipo()];
                                            ?>
                                            <option value="<?= $row['_id'] ?>"
                                                <?php if ($row['_id'] == $id_indicador) echo "selected='selected'"; ?>>
                                                <?="{$row['_nombre']} ({$row['_inicio']}-{$row['_fin']}) / {$nombre_prs}" ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div id="_submit" class="btn-block btn-app">
                                    <button class="btn btn-warning" type="reset"
                                        onclick="self.location.href='<?php prev_page()?>'">Cerrar</button>
                                    <button class="btn btn-danger" type="button"
                                        onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                                </div>
                            </div>
                        </div> <!-- form for filter -->
                    </div>
                </div> <!-- form for filter -->


                <div class="row">
                    <?php
                    $alert= "alert('Este dia no se corresponde con la periodicidad asignada al indicador.')";
                    if (!empty($periodicidad) && $year > 0) {
                    ?>
                    <input type=hidden name=index id=index value='' />

                    <!-- bloque de indicador -->
                    <div class="card card-primary">
                        <div class="card-header">PLANIFICAR</div>
                        <div class="card-body">
                            <label class="alert alert-info col-md-12"> <?=$nombre?></label>

                            <?php if ($formulated) { ?>
                            <label class="alert alert-warning col-md-12 thin">
                                Indicador formulado. El sistema calcula el valor del indicador a partir de otros
                                indicadores con información primaria.
                            </label>
                            <?php } ?>

                            <?php if ($cumulative) { ?>
                            <label class="alert alert-warning col-md-12 thin">
                                Indicador acumulativo. El valor registrado será sumado al acumulado del año, el que será
                                automáticamente calculado por el sistema.
                            </label>
                            <?php } ?>

                            <?php
                            for ($i = $pmonth; $i <= 12; $i += $pmonth) {
                                for ($j = 1; $j <= 31; ++$j) {
                            ?>
                            <input type="hidden" name="plan_cot_<?=$i?>_<?=$j?>" id="plan_cot_<?=$i?>_<?=$j?>" />
                            <input type="hidden" name="plan_<?=$i?>_<?=$j?>" id="plan_<?=$i?>_<?=$j?>" />
                            <input type="hidden" name="observacion_<?=$i?>_<?=$j?>" id="observacion_<?=$i?>_<?=$j?>" />

                            <?php } } ?>

                            <div class="row">
                                <!-- left side -->
                                <div class="col">
                                    <div class="form-group row">
                                        <label class="col-md-3 col-lg-3 thin text">Criterio de Éxito:</label>
                                        <div class="alert alert-info col-md-6 thin">
                                            REAL
                                            <?php if ($trend == 1) { ?> &ge; mayor o igual que el PLAN <?php } ?>
                                            <?php if ($trend == 2) { ?> &le; menor o igual que el PLAN<?php } ?>
                                            <?php if ($trend == 3) { ?> [..] dentro de un intervalo <?php } ?>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-4 text">Periodicidad:</label>
                                        <div class="alert alert-info col-md-3 col-lg-3 thin">
                                            <?=$periodo_inv[$periodicidad]?></div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-4 col-lg-4 pull-left">Seleccione el Mes:</label>
                                        <div class="col-md-5 pull-left">
                                            <select name=imonth id=imonth class="form-control" onchange="javascript:refreshmonth()">
                                                <?php for ($im = $pmonth; $im <= 12; $im += $pmonth) { ?>
                                                <option value="<?= $im ?>"
                                                    <?php if ($im == (int) $month) echo "selected='selected'"; ?>>
                                                    <?= $meses_array[$im] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="alert alert-info col-md-2 col-lg-2 thin"><?=$year?></div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-2 pull-left">Días:</label>
                                        <div class="col-md-5 col-lg-5">
                                            <?php
                                            for ($im = $pmonth; $im <= 12; $im += $pmonth) {
                                                if ($im == (int) $month)
                                                    $link = "block";
                                                else
                                                    $link = "none";
                                                ?>
                                            <div class="daypicker panel" style="display:<?= $link ?>" id="cal-<?=$im?>">
                                                <?php
                                                $d = 1;
                                                $time->SetMonth($im);
                                                $time->SetDay($d);
                                                $firstday = $time->weekDay();
                                                $lastday = $time->longmonth();
                                                $mm = str_pad($im, 2, '0', STR_PAD_LEFT);
                                                ?>
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th>Lu</th>
                                                            <th>Ma</th>
                                                            <th>Mi</th>
                                                            <th>Ju</th>
                                                            <th>Vi</th>
                                                            <th>Sa</th>
                                                            <th>Do</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <?php for ($i= 1; $i < $firstday && $i < 8; ++$i) { ?>
                                                            <td class="day new"></td>
                                                            <?php
                                                                }

                                                            for ($i= $firstday; $i < 8; $i++) {
                                                                    $fecha = $year . '-' . $mm . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                                                    $work= !get_work_day($fecha, $weekday) ? "free" : null;
                                                                    $time->SetDay($d);
                                                                if ($time->ifDayPeriodo($periodicidad)) {
                                                                    $class = 'active';
                                                                    $onclick = "selectday($im,$d,$year)";
                                                                } else {
                                                                    $class = 'unactive';
                                                                    $onclick = $alert;
                                                                }
                                                                ?>
                                                                <td class="day <?=$work?> <?=$class?>"
                                                                    onclick="<?=$onclick?>">
                                                                    <?=$d++?>
                                                                </td>
                                                            <?php
                                                            }
                                                            ?>
                                                        </tr>

                                                    <?php
                                                    $col= 1;
                                                    for ($i= $d; $i <= $lastday; ++$i) {
                                                        if ($col == 1) {
                                                    ?>
                                                        <tr>
                                                            <?php
                                                            }

                                                            $fecha = $year . '-' . $mm . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                                                            $work = !get_work_day($fecha, $weekday) ? "free" : null;
                                                            $time->SetDay($d);
                                                            if ($time->ifDayPeriodo($periodicidad)) {
                                                                $class = 'active';
                                                                $onclick = "selectday($im,$d,$year)";
                                                            } else {
                                                                $class = 'unactive';
                                                                $onclick = $alert;
                                                            }
                                                            ?>
                                                            <td class="day <?=$work?> <?=$class?>"
                                                                onclick="<?=$onclick?>">
                                                                <?=$d++?>
                                                            </td>
                                                            <?php
                                                            ++$col;
                                                            if ($col > 7) {
                                                                $col= 1;
                                                            ?>
                                                        </tr>
                                                        <?php
                                                            }
                                                        }
                                                        if ($col < 7) {
                                                            for ($i= $col; $i < 8; $i++) {
                                                        ?>
                                                        <td class="day new"></td>
                                                        <?php
                                                            }
                                                        ?>
                                                        </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div> <!-- daypicker -->
                                            <?php } ?>

                                        </div>

                                        <div class="col-12 text-warning">
                                            Clic en el día de cierre para activar las caja de texto
                                        </div>

                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-success" onclick="displayWindow()">
                                                Ver Histórico
                                            </button>
                                        </div>
                                    </div>
                                </div> <!-- left side -->

                                <!-- right side -->
                                <div class="col">
                                    <?php
                                    $obj_um= new Tunidad($clink);
                                    $obj_um->Set($id_unidad);
                                    $unidad= $obj_um->GetUnidad();
                                    $unidad = preg_replace("#<p>(.*)<\/p>#", '$1', $unidad);
                                    $descripcion_um= $obj_um->GetDescripcion();
                                    ?>
                                    
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-4">Unidad de
                                                medida:</label>
                                            <div class="col-8 alert alert-info thin">
                                                <?= "({$unidad}) {$descripcion_um}" ?></div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-md-4">Fecha:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" readonly="yes" id="datetxt"
                                                value="" />
                                        </div>
                                    </div>
                                    <div id="tr-plan" class="form-group row">
                                        <label class="col-form-label col-md-4">
                                            Plan <sub>valor</sub>:
                                        </label>
                                        <div class="col-md-7">
                                            <div class="input-group">
                                                <input type="text" name="plan" id="plan"
                                                    class="form-control input-unactive" value="" disabled="disabled" />
                                                <span class="input-group-text"><?=$unidad?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tr-plan_cot" class="form-group row">
                                        <label class="col-form-label col-md-4">
                                            Plan <sub>valor cota inferior</sub>
                                        </label>
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <input type="text" name="plan_cot" id="plan_cot"
                                                    class=" form-control input-unactive" value="" disabled="disabled" />
                                                <span class="input-group-text"><?= $unidad ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-12 pull-left">

                                        </label>
                                        <div class="col-md-12">
                                            <textarea name="observacion" rows="11" id="observacion"
                                                class="form-control input-unactive" disabled="disabled"></textarea>
                                        </div>
                                    </div>
                                </div> <!-- right side -->
                            </div>

                            <div id="_submit" class="btn-block btn-app">
                                <?php if ($action == 'update' || $action == 'add') { ?><button class="btn btn-primary"
                                    type="submit">Aceptar</button><?php } ?>
                                <button class="btn btn-warning" type="reset"
                                    onclick="self.location.href='<?php prev_page()?>'">Cancelar</button>
                                <button class="btn btn-danger" type="button"
                                    onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                            </div>

                            <div id="_submited" style="display:none">
                                <img src="../img/loading.gif" alt="cargando" /> Por favor espere
                                ..........................
                            </div>

                        </div>
                    </div> <!-- bloque de indicador -->
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>

    <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

    </div>

</body>

</html>