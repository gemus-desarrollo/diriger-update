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
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/registro.class.php";
require_once "../php/class/unidad.class.php";

require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'edit')
    $action= 'add';

$actual_year= $_SESSION['current_year'];
$actual_month= $_SESSION['current_month'];
$actual_day= $_SESSION['current_day'];

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'real';
$year= !empty($_GET['year']) ? $_GET['year'] : $actual_year;
if (!empty($_GET['month'])) 
    $month= $_GET['month'];

if (empty($month) || $month == -1)
    $month= $year == $actual_year ? $actual_month : 12;

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

if (!empty($_GET['day']))
    $day= $_GET['day'];
if (empty($day)) {
    if ($month != $actual_month || $year != $actual_year) {
        if ($month == $actual_month && $year == $actual_year)
            $day= $actual_day;
        else
            $day= $lastday;
    }
}

if (isset($_SESSION['obj']))  
    unset($_SESSION['obj']);
$obj= new Tregistro($clink);
$obj->SetYear($year);

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : 0;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;
$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : 0;
$id_inductor= !empty($_GET['id_inductor']) ? $_GET['id_inductor'] : 0;

$obj_um= new Tunidad($clink);

$unidad= null;
$unidad_um= null;
$cumulative= null;
$chk_cumulative= null;
$formulated= false;

if (!empty($id_indicador)) {
    $obj->SetYear($year);
    $obj->Set($id_indicador);

    $nombre= $obj->GetNombre();
    $periodicidad= $obj->GetPeriodicidad();
    $trend= $obj->GetTrend();
    $id_indicador_code= $obj->get_id_indicador_code();
    $cumulative= $obj->GetIfCumulative();
    $_chk_cumulative= $obj->GetChkCumulative();
    $formulated= $obj->GetIfFormulated();

    $id_unidad= $obj->GetIdUnidad();
    
    $obj_um->Set($id_unidad);
    $unidad= $obj_um->GetNombre();
    $unidad = preg_replace("#<p>(.*)<\/p>#", '$1', $unidad);
    $descripcion_um= $obj_um->GetDescripcion();
    $unidad_um= "($unidad) $descripcion_um";  
}

$cumulative= !empty($cumulative) ? 1 : 0;
$_chk_cumulative= !empty($_chk_cumulative) ? 1 : 0;
$formulated= !empty($formulated) ? 1 : 0;

$time= new TTime();
$fin= $time->GetYear();
$actual_year= $time->GetYear();
$actual_month= (int)$time->GetMonth();
$actual_day= $time->GetDay();

$year= 0;
$year= !empty($_GET['year']) ? $_GET['year'] : $fin;
$month= !empty($_GET['month']) ? $_GET['month'] : 0;
if ($month == -1 || empty($month))
    $month= $actual_month;

$obj->SetMonth($month);
$obj->SetYear($year);

$month_init= 1;
$month_end= ($year == $fin) ? $actual_month : 12;

/*
if ($actual_month == 1) $year_init= $fin-1;
else $year_init= $year;
*/
$year_init= $actual_year - 3;
// if ($year == ($fin-1)) $month_init= 12;
$day= !empty($_GET['day']) ? $_GET['day'] : $actual_day;

$time->SetYear($year);
$time->SetMonth($month);
$time->SetDay($day);
$_iday= $time->weekDay();
$lastday= $time->longmonth();

if ($day > $lastday && $month < $actual_month)
    $end_day= $lastday;
else if (($month < $actual_month && $year == $actual_year) || $year < $actual_year)
    $end_day= $lastday;
else if ($day < $actual_day && ($month == $actual_month && $year == $actual_year))
    $end_day= $actual_day;
else
    $end_day= $day;

if (!empty($year))
    $obj->SetYear($year);

$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

$url_page= "../form/real.php?signal=$signal&action=$action&menu=real&year=$year";
$url_page.= "&month=$month&day=$day&exect=$action&id_tablero=$id_tablero";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>INGRESO DE DATOS REALES</title>

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
    
    <style>
    .panel-multiselect {
        min-height: 440px;
        max-height: 550px;
    }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    var _fixed = false;

    function refreshp(flag) {
        var id_perspectiva = $('#perspectiva').val();
        var id_inductor = $('#inductor').val();
        var day = $('#day').val();
        var month = $('#month').val();
        var year = $('#year').val();
        var signal = $('#signal').val();

        switch (flag) {
            case 1:
                month = 0;
                day = 0;
                break;
            case 2:
                day = 0;
                break;
        }

        var url = 'real.php?version=&action=<?= $action ?>&id_perspectiva=' + id_perspectiva;
        url += '&id_inductor=' + id_inductor + '&day=' + day + '&month=' + month + '&year=' + year; 
        url += '&signal=' + signal;
        self.location = url;
    }


    var trId;

    function revisar() {
        var i = 0;
        var cant = $('#cant').val();
        var text;

        for (i = 0; i < cant; ++i) {
            if (Entrada($('#real_' + i).val())) {
                if (!IsNumeric($('#real_' + i).val())) {
                    text = "Al menos para un indicador el valor real no fue introducido correctamente. ";
                    text += "Por favor, el valor real solo admite datos numericos.";
                    alert(text);
                    return;
        }   }   }
        
        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        document.forms[0].action = '../php/real.interface.php';
        document.forms[0].submit();
    }

    function enviar() {
        if (trId != null) {
            $('#chk_cumulative_' + trId).val($('#chk_cumulative').is(':checked') ? 1 : 0);
            $('#real_' + trId).val($('#real').val());
            $('#observacion_' + trId).val($('#observacion').val());
        } else {
            alert("Nada que hacer. Seleccione el boton Cancelar si desea terminar.");
            return;
        }

        revisar();
    }

    function rowSelect(id, name) {
        if (_fixed == false) {
            <?php if ($signal == 'real') { ?> 
            $('#fieldset-filtro').hide();
            <?php } ?>
            _fixed = true;
        }

        if (trId != undefined) {
            $('div.item').removeClass('active');

            $('#chk_cumulative_' + trId).val($('#chk_cumulative').is(':checked') ? 1 : 0);
            $('#_chk_cumulative_' + trId).val($('#_chk_cumulative').val());
            $('#real_' + trId).val($('#real').val());
            $('#observacion_' + trId).val($('#observacion').val());

            $('#cumulative_' + trId).val($('#cumulative').val());
            $('#name_' + trId).val($('#name').val());
            $('#unidad_' + trId).val($('#unidad').val());
            $('#unidad_um_' + trId).val($('#unidad_um').val());
        }

        trId = id;
        $('#div-id_' + id).addClass('active');

        $('#chk_cumulative').prop('disabled', false);
        $('#real').prop('disabled', false);
        $('#real').removeClass('input-unactive');
        $('#observacion').prop('disabled', false);
        $('#observacion').removeClass('input-unactive');
        
        $('#chk_cumulative').is(':checked', parseInt($('#chk_cumulative_' + id).val()) ? true : false);
        $('#chk_cumulative').prop('checked', parseInt($('#chk_cumulative_' + id).val()) ? true : false);
        $('#_chk_cumulative').val($('#_chk_cumulative_' + id).val());

        $('#real').val($('#real_' + id).val());
        $('#observacion').val($('#observacion_' + id).val());

        $('#cumulative').val($('#cumulative_' + id).val());
        $('#formulated').val($('#formulated_' + id).val());
        $('#name').val($('#name_' + id).val());
        $('#unidad').val($('#unidad_' + id).val());
        $('#unidad_um').val($('#unidad_um_' + id).val());

        $('#perspectiva').prop('disabled', true);
        $('#inductor').prop('disabled', true);
        $('#year').prop('disabled', true);
        $('#month').prop('disabled', true);
        $('#day').prop('disabled', true);
        $('#fieldset-filtro').hide();

        showlabels();

        $('#real').focus();
    }

    function setNULL() {
        $('#real').val(undefined);
        $('#observacion').val("Fijado nulo por el usuario");
    }

    function showlabels() {
        $('#label_indicador').html($('#name').val());

        if (parseInt($('#formulated').val())) {
            $('#alert-formulated').show();
            $('#tr-real-label').hide();
            $('#tr-real-input').hide();
            $('#tr-button').removeClass('col-3');
            $('#tr-button').addClass('col-12');
        } else {
            $('#alert-formulated').hide();
            $('#tr-real-label').show();
            $('#tr-real-input').show();
            $('#tr-button').removeClass('col-12');
            $('#tr-button').addClass('col-3');
        }

        if (parseInt($('#_chk_cumulative').val()))
            $('#tr-chk_cumulative').show();
        else 
            $('#tr-chk_cumulative').hide();    

        if (parseInt($('#cumulative').val()))
            $('#alert-cumulative').show();
        else
            $('#alert-cumulative').hide();

        $('#label_unidad').html($('#unidad').val());
        $('#label_unidad_um').html($('#unidad_um').val());
    }

    function mostrar() {
        if (trId != null) {
            var id_indicador = $('#indicador_' + trId).val();
            var imonth = $('#_month').val();
            var iyear = $('#_year').val();
            var iday = $('#_day').val();

            var url = '../form/ajax/reg_real.ajax.php?id_indicador=' + id_indicador + '&day=' + iday;
            url += '&month=' + imonth + '&year=' + iyear;

            var capa = 'div-ajax-panel';
            var metodo = 'GET';
            var valores = '';
            var funct= '';
   
            FAjax(url, capa, valores, metodo, funct);
            displayFloatingDiv('div-ajax-panel', "REGISTRO HISTÓRICO DE VALORES MODIFICADOS", 70, 0, 10, 15);

        } else {
            alert("Debe selecionar un indicador.");
            return;
        }
    }

    function selectday(d) {
        $('#day').val(d);
        refreshp(0);
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        InitDragDrop();

        <?php if (empty($id_indicador)) { ?>
        $('#chk_cumulative').prop('disabled', true);    
        $('#real').prop('disabled', true);
        $('#observacion').prop('disabled', true);

        <?php } else { ?>
        trId = undefined;
        rowSelect(0, '<?=$nombre?>');
        showlabels();
        $('#real').focus();
        <?php } ?>

        <?php if (!empty($id_indicador) && $formulated) { ?>
        $('#tr-real-label').hide();
        $('#tr-real-input').hide();
        $('#tr-button').removeClass('col-3');
        $('#tr-button').addClass('col-12');

        rowSelect(0, '<?=$nombre?>');
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
        <form action='javascript:enviar()' class="form-horizontal" method=post>
            <input type="hidden" name="exect" value="<?= $action ?>" />
            <input type="hidden" id="id" name="id" value="<?= $id_indicador ?>" />
            <input type="hidden" id="id_code" name="id_code" value="<?= $id_indicador_code ?>" />

            <input type=hidden name="id_tablero" id="id_tablero" value="<?=$id_tablero?>" />
            <input type=hidden name="_perspectiva" id="_perspectiva" value="<?=$id_perspectiva?>" />
            <input type=hidden name="_inductor" id="_inductor" value="<?=$id_inductor?>" />

            <input type="hidden" name="menu" value="indicador" />
            <input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />

            <input type="hidden" id="_year" name="_year" value="<?= $year ?>" />
            <input type="hidden" id="_month" name="_month" value="<?= $month ?>" />
            <input type="hidden" id="_day" name="_day" value="<?= $day ?>" />

            <input type="hidden" name="periodicidad" id="periodicidad" value="<?= $periodicidad ?>" />
            <input type="hidden" id="trend" name=trend value="<?= $trend ?>" />
            <input type="hidden" id="cumulative" name=cumulative value="<?= $cumulative ?>" />
            <input type="hidden" id="_chk_cumulative" name=_chk_cumulative value="<?= $_chk_cumulative ?>" />
            <input type="hidden" id="formulated" name=formulated value="<?= $formulated ?>" />

            <input type=hidden name="name" id="name" value="<?=$nombre?>" />
            <input type=hidden name="unidad" id="unidad" value="<?=$unidad?>" />
            <input type=hidden name="unidad_um" id="unidad_um" value="<?=$unidad_um?>" />

            <?php if ($signal != 'real') { ?>
            <input type="hidden" id="perspectiva" name="perspectiva" value="<?=$id_perspectiva?>" />
            <input type="hidden" id="inductor" name="inductor" value="<?=$id_inductor?>" />
            <input type="hidden" id="year" name="year" value="<?=$year?>" />
            <input type="hidden" id="month" name="month" value="<?=$month?>" />
            <input type="hidden" id="day" name="day" value="<?=$day?>" />
            <?php } ?>


            <?php  if ($signal == 'real') { ?>
            <!-- form for filter -->
            <div class="container pl-5 pr-5">
                <div id="fieldset-filtro" class="card card-primary">
                    <div class="card-header">FECHA A ACTUALIZAR</div>

                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Año:
                            </label>
                            <div class="col-2">
                                <select name="year" id="year" class="form-control" onchange="javascript:refreshp(1)">
                                    <?php  for ($i= $year_init; $i <= $fin; ++$i) { ?>
                                    <option value="<?=$i?>" <?php if ($i == $year) echo "selected='selected'"; ?>><?=$i?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Perspectiva:
                            </label>
                            <div class="col-8">
                                <select name="perspectiva" id="perspectiva" class="form-control"
                                    onchange="javascript:refreshp(0)">
                                    <option value="0"></option>
                                    <?php
                                    $obj_perspectiva= new Tperspectiva($clink);
                                    $obj_perspectiva->SetYear($year);
                                    $result_persp= $obj_perspectiva->listar();

                                    while ($row= $clink->fetch_array($result_persp)) {
                                        if ($array_procesos_entity[$row['_id_proceso']]['id_entity'] != $_SESSION['id_entity'])
                                            continue;
                                        $nombre_prs= $row['proceso'].',  '.$Ttipo_proceso_array[$row['tipo']];
                                    ?>
                                    <option value="<?=$row['_id']?>"
                                        <?php if ($row['_id'] == $id_perspectiva) echo "selected='selected'"; ?>>
                                        <?= "{$row['nombre']} ({$row['inicio']}-{$row['fin']}) / {$nombre_prs}" ?>
                                    </option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Objetivo de trabajo:
                            </label>
                            <div class="col-10">
                                <select name=inductor id=inductor class="form-control" onchange="javascript:refreshp(0)">
                                    <option value=0> ... </option>
                                    <?php
                                    $obj_inductor= new Tinductor($clink);
                                    if (!empty($id_perspectiva))
                                        $obj_inductor->SetIdPerspectiva($id_perspectiva);
                                    $obj_inductor->SetYear($year);

                                    $with_null_perspectiva= !empty($id_perspectiva) ? _PERSPECTIVA_NOT_NULL : _PERSPECTIVA_ALL;
                                    $result_inductor= $obj_inductor->listar($with_null_perspectiva);

                                    while ($row= $clink->fetch_array($result_inductor)) {
                                        if ($array_procesos_entity[$row['id_proceso']]['id_entity'] != $_SESSION['id_entity'])
                                            continue;                                    
                                        $obj_prs->Set($row['id_proceso']);
                                        $nombre_prs= $obj_prs->GetNombre().',  '.$Ttipo_proceso_array[$obj_prs->getTipo()];
                                    ?>
                                    <option value="<?=$row['_id']?>"
                                        <?php if ($row['_id'] == $id_inductor) echo "selected='selected'"; ?>>
                                        <?="{$row['nombre']} ({$row['inicio']}-{$row['fin']}) / {$nombre_prs}"?>
                                    </option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-2 col-offset-2">
                                Mes:
                            </label>
                            <div class="col-3">
                                <select name="month" id="month" class="form-control" onchange="javascript:refreshp(2)">
                                    <?php for ((int)$i = $month_init; $i <= $month_end; ++$i) { ?>
                                    <option value="<?= $i ?>" <?php if ($i == (int)$month) echo "selected='selected'"; ?>>
                                        <?= $meses_array[$i] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-5">
                                <div class="btn-group">
                                    <input type="hidden" name="day" id="day" value="<?= $day ?>" />
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                        Día
                                        <span class="caret"></span>
                                    </button>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <div class="daypicker">
                                                <?php
                                                $d = 1;
                                                $time->SetDay($d);
                                                $firstday = $time->weekDay();
                                                $lastday = $time->longmonth();
                                                $mm = str_pad($month, 2, '0', STR_PAD_LEFT);
                                                $im = (int) $month;
                                                ?>
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th colspan="7"><?=$meses_array[$im]?> / <?=$year?></th>
                                                        </tr>
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
                                                                $active= ($d == (int)$day) ? "active" : null;
                                                            ?>
                                                            <td class="day <?=$work?> <?=$active?>"
                                                                onclick='selectday(<?=$d?>)'>
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
                                                            $active= ($d == (int)$day) ? "active" : null;
                                                            ?>
                                                            <td class="day <?=$work?> <?=$active?>"
                                                                onclick='selectday(<?=$d?>)'>
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
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div id="_submit" class="btn-block btn-app">
                            <!--
                            <button class="btn btn-warning" type="reset" onclick="self.location.href='<?php prev_page() ?>'">Cerrar</button>
                            -->
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>
                    </div>
                </div> 
            </div>
            <?php } ?>


            <div class="container mt-3">
                <div class="card card-primary">
                    <div class="card-header">DATOS REALES</div>

                    <div class="card-body">
                        <div class="col-12">
                            <label id="alert-formulated" class="alert alert-danger text" style="display:none">
                                <strong>Indicador formulado</strong>. El valor calculado por el sistema a partir de
                                otros indicadores con información primaria.
                            </label>
                            <label id="alert-cumulative" class="alert alert-danger text" style="display:none">
                                <strong>Indicador acumulativo</strong>. El valor registrado será sumado al acumulado del
                                año, el que será automáticamente calculado por el sistema.
                            </label>
                        </div>

                        <div class="row">
                            <!-- right side -->
                            <div class="col-6">
                                <label>Seleccionar Indicador:</label>

                                <div class="panel-multiselect left">
                                    <?php
                                    if ($year > 0 && $month > 0) {
                                        
                                        $obj->SetYear($year);
                                        $obj->SetRole($_SESSION['nivel']);
                                        $obj->SetIdUsuario($_SESSION['id_usuario']);
                                
                                        if (!empty($id_perspectiva)) {
                                            $obj->SetIdPerspectiva($id_perspectiva);
                                        }
                                        if (!empty($id_inductor)) {
                                            $obj->SetIdInductor($id_inductor);
                                        }
                                
                                        $corte= array('year'=>$year, 'month'=>$month, 'day'=>$day);
                                        $i= 0;
                                        $result= $obj->listar_by_nivel();
                                        while ($row= $clink->fetch_array($result)) {
                                            if (!empty($id_indicador) && $id_indicador != $row['_id']) 
                                                continue;
                                            if ($array_procesos_entity[$row['id_proceso']]['id_entity'] != $_SESSION['id_entity']) 
                                                continue;                          
                                            /*
                                            if (boolean($row['formulated'])) 
                                                continue;
                                            */
                                            $obj_prs->Set($row['id_proceso']);
                                            $nombre_prs= $obj_prs->GetNombre().',  '.$Ttipo_proceso_array[$obj_prs->getTipo()];

                                            $obj_um->Set($row['id_unidad']);
                                            $unidad= $obj_um->GetNombre();
                                            $unidad = preg_replace("#<p>(.*)<\/p>#", '$1', $unidad);
                                            $descripcion_um= $obj_um->GetDescripcion();
                                            $unidad_um= "($unidad) $descripcion_um";
                                    ?>

                                            <div class="item" id="div-id_<?=$i?>" onClick="rowSelect(<?=$i?>,'<?=$row['nombre']?>')">
                                                <?php if (boolean($row['formulated'])) { ?>
                                                    <img class="img-rounded" src="../img/calculator.ico" alt="Calculado por el Sistema" title="Calculado por el Sistema" />
                                                <?php } ?>
                                                <strong><?=$row['nombre']?></strong>
                                                <?= "({$row['inicio']}-{$row['fin']}) / {$nombre_prs}" ?>
                                            </div>

                                            <input type="hidden" id="indicador_<?=$i?>" name="indicador_<?=$i?>" value="<?=$row['_id']?>" />
                                            <input type="hidden" id="indicador_code_<?=$i?>" name="indicador_code_<?=$i?>" value="<?=$row['_id_code']?>" />

                                            <?php
                                            $obj->SetIdIndicador($row['_id']);
                                            $rowcmp= $obj->listar_reg_real($corte, $row['periodicidad'], true);

                                            $valor= !is_null($rowcmp['valor']) ? $rowcmp['valor'] : null;
                                            $observacion= !empty($rowcmp['observacion']) ? $rowcmp['observacion'] : null;
                                            $date= !empty($rowcmp['reg_date']) ? date('Y-m-d', strtotime($rowcmp['reg_date'])) : null;
                                            
                                            if ($row['chk_cumulative'])
                                                $chk_cumulative= !is_null($rowcmp['chk_cumulative']) ? ($rowcmp['chk_cumulative'] ? 1 : 0) : 1;
                                            else 
                                                $chk_cumulative= 0;
                                            ?>
 
                                            <input type="hidden" id="real_<?=$i?>" name="real_<?=$i?>" value="<?=$valor?>" />
                                            <input type="hidden" id="observacion_<?=$i?>" name="observacion_<?=$i?>" value="<?=$observacion?>" />
                                            <input type="hidden" id="_chk_cumulative_<?=$i?>" name="_chk_cumulative_<?=$i?>" value="<?=boolean($row['chk_cumulative']) ? 1 : 0?>" />
                                            <input type="hidden" id="chk_cumulative_<?=$i?>" name="chk_cumulative_<?=$i?>" value="<?=$chk_cumulative?>" />

                                            <input type="hidden" id="trend_<?=$i?>" name="trend_<?=$i?>" value="<?=$row['trend']?>" />
                                            <input type="hidden" id="periodicidad_<?=$i?>" name="periodicidad_<?=$i?>" value="<?=$row['periodicidad']?>" />
                                            <input type="hidden" id="cumulative_<?=$i?>" name="cumulative_<?=$i?>" value="<?=boolean($row['cumulative'])?>" />
                                            <input type="hidden" id="formulated_<?=$i?>" name="formulated_<?=$i?>" value="<?=boolean($row['formulated'])?>" />
                                            <input type="hidden" id="name_<?=$i?>" name="name_<?=$i?>" value="<?=$row['nombre']?>" />
                                            <input type="hidden" id="unidad_<?=$i?>" name="unidad_<?=$i?>" value="<?=$unidad?>" />
                                            <input type="hidden" id="unidad_um_<?=$i?>" name="unidad_um_<?=$i?>" value="<?=$unidad_um?>" />
                                    <?php
                                            ++$i;
                                        }
                                    }
                                    ?>
                                    <input type="hidden" name="cant" id="cant" value="<?=$i?>" />
                                </div>
                            </div> <!-- right side -->

                            <!-- right side -->
                            <div class="col-6">
                                <label>Para actualizar Indicador seleccionado:</label>

                                <div class="row">
                                    <label class="col-form-label col-3">Unidad de medida:</label>
                                    <div id="label_unidad" class="col-8 alert text alert-info">
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-form-label col-3">Fecha:</label>
                                    <div id="datetxt" class="col-8 alert text alert-info">
                                        <?="{$dayNames[$_iday]}, {$day} de {$meses_array[(int)$month]}, {$year}" ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-form-label col-3">Indicador:</label>
                                    <div id="label_indicador" class="col-8 alert text alert-info"></div>
                                </div>

                                <div class="row mt-3">
                                    <div id="tr-real-input" class="col-9">
                                        <div id="tr-chk_cumulative" class="checkbox mt-2 mb-3" style="display: <?=$_chk_cumulative ? "block" : "none"?>">
                                            <input type="checkbox" id="chk_cumulative" name="chk_cumulative" value="1" />
                                            Este valor se sumará al enterior para cálcular el valor del correspondiente al corte de este período
                                        </div> 

                                        <div class="row">
                                            <label id="tr-real-label" class="col-form-label col-2">
                                                Valor:
                                            </label>
                                            <div class="col-6">
                                                <input type="text" name="real" id="real" class="form-control input-unactive"
                                                    value="" disabled="disabled" />
                                            </div>  
                                            <div class="col-4">
                                                <button type="button" class="btn btn-info" onclick="setNULL()">
                                                    Fijar Nulo
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="tr-button" class="row">
                                        <button type="button" class="btn btn-md btn-success" onclick="mostrar()">
                                            Ver Histórico
                                        </button>           
                                    </div>
                                </div>

                                <div class="row">
                                    <label class="col-form-label pull-left">Observación:</label>
                                </div>

                                <div class="form-group row">
                                    <div class="col-12">
                                        <textarea name="observacion" rows="7" id="observacion"
                                            class="form-control input-unactive"></textarea>
                                    </div>
                                </div>
                            </div> <!-- right side -->
                        </div>

                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page()?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

        </div>
    </div>
</body>

</html>