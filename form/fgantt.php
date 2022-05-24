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
require_once "../php/class/time.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";
require_once "../php/class/orgtarea.class.php";

if (!empty($_GET['action'])) $action= $_GET['action'];
else $action= 'list';

if ($action == 'add') {if (isset($_SESSION['obj']))  unset($_SESSION['obj']);}

if (isset($_SESSION['obj'])) {
	$obj= unserialize($_SESSION['obj']);
	$obj->SetLink($clink);
	$action= $obj->action;
} else {
 $obj= new Tregtarea($clink);
}

if (!empty($_GET['id_tarea'])) 
    $id_tarea= $_GET['id_tarea'];
if (empty($id_tarea)) 
    $id_tarea= $obj->GetIdTarea();

if (!empty($_GET['id_ref'])) 
    $id_ref= $_GET['id_ref'];
else 
    $id_ref= 0;

if (!empty($id_tarea)) {
	$obj->SetIdTarea($id_tarea);
	$obj->Set();
}

$id_responsable= $obj->GetIdResponsable();
$fecha_inicio_real= odbc2date($obj->GetFechaInicioReal());
$fecha_fin_real= odbc2date($obj->GetFechaFinReal());
$reg_fecha= odbc2date($obj->GetFecha());
$value= $obj->GetCumplimiento();

if (!empty($_GET['gantt'])) $id_responsable= $_GET['gantt'];
if (empty($id_responsable)) $id_responsable= $_SESSION['id_usuario'];

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;


$time= new TTime();
$fin= $time->GetYear();
$actual_month= (int)$time->GetMonth();
$actual_day= (int)$time->GetDay();

if (!empty($_GET['year'])) $year= $_GET['year'];
if (empty($year)) $year= $obj->GetYear();
if (empty($year)) $year= $fin;

if (!empty($_GET['month']))$month= $_GET['month'];
if (empty($month)) $obj->GetMonth();
if (empty($month) || $month == -1) $month= $actual_month;

if ($year == $fin) 
    $month_end= $actual_month;
else 
    $month_end= 12;

if (!empty($_GET['day'])) 
    $day= $_GET['day'];
if (empty($day)) 
    $day= $obj->GetDay();
if ($day == -1 || empty($day)) 
    $day= $actual_day;

if ($month == $actual_month && $year == $fin) $end_day= $actual_day;
else $end_day= 31;

$init_year= $year - 4;
$end_year= $year + 8;

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>ACTUALIZACIN EL AVANCE DE LAS TAREA (INDIVIDUAL)</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->

    <link href="../libs/bootstrap/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap/js/moment.min.js"></script>
    <script src="../libs/bootstrap/js/bootstrap-datetimepicker.min.js"></script>
    <script src="../libs/bootstrap/js/bootstrap-datetimepicker.es.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        if (!Entrada($('#tarea').val())) {
            $('#tarea').focus(focusin($('#tarea')));
            alert('Debe selecionar la tarea.');
            return;
        }

        if (!Entrada($('#real').val())) {
            $('#real').focus(focusin($('#real')));
            alert('No ha especificado el avance en el cumplimiento de la tarea.');
            return;
        } else {
            if (!IsNumeric($('#real').val()) || $('#real').val() < 0) {
                $('#real').focus(focusin($('#real')));
                alert('Error en el estado de avance de la tarea. Debe especificar un valor n&uacute;merico');
                return;
            }
        }

        if ($('#real').val() > 0 && !Entrada($('#fecha_inicio').val())) {
            $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
            alert('Debe especificar la fecha en la que inicio la ejecución real de la tarea.');
            return;
        }

        if ($('#begun').is(':checked') && !Entrada($('#fecha_inicio').val())) {
            $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
            alert('Debe especificar la fecha en la que inició la ejecución real de la tarea.');
            return;
        }

        if (Entrada($('#real').val()) && !Entrada($('#fecha').val())) {
            $('#real').focus(focusin($('#real')));
            alert('Especifique la fecha a la que corresponde esta actualización del avance de la tarea.');
            return;
        }

        if (!Entrada($('#descripcion').val())) {
            $('#descripcion').focus(focusin($('#descripcion')));
            alert('No ha realizado ninguna descripción relativa al estado de la tarea');
            return;
        }

        if ($('#real').val() >= 100 && !Entrada($('#fecha_fin').val())) {
            $('#real').focus(focusin($('#real')));
            alert('Ha alcanzado el 100%. Debe especificar la fecha en la que terminá la ejecución real de la tarea.');
            return;
        }

        if ($('#ended').is(':checked') && !Entrada($('#fecha_final').val())) {
            $('#ended').focus(focusin($('#ended')));
            alert('Debe especificar la fecha en la termin la ejecucin real de la tarea.');
            return;
        }

        if (Entrada($('#fecha_inicio').val())) {
            if (!isDate_d_m_yyyyy($('#fecha_inicio').val())) {
                $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
                alert('Fecha de inicio con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
                return;
            }
        }

        if (Entrada($('#fecha').val())) {
            if (!isDate_d_m_yyyyy($('#fecha').val())) {
                $('#fecha').focus(focusin($('#fecha')));
                alert('Fecha de de actualizacin con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
                return;
            }
        }

        if (Entrada($('#fecha_final').val())) {
            if (!isDate_d_m_yyyyy($('#fecha_final').val())) {
                $('#fecha_final').focus(focusin($('#fecha_final')));
                alert('Fecha de finalizacin con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
                return;
            }
        }

        document.forms[0].action = '../php/tarea.interface.php';
        document.forms[0].submit();
    }


    function onchange_fecha_inicio() {
        if (Entrada($('#fecha_inicio').val())) {
            if (!$('#begun').is(':checked')) $('#begun').prop('checked', true);
        }
    }


    function onclick_begun() {
        if ($('#begun').is(':checked')) {
            $('#begun').focus(focusin($('#begun')));
            alert("Debe especificar la fecha en la que inici la tarea.");
            show_calendar('document.ftarea.fecha_inicio', $('#fecha_inicio').val());
        } else {
            $('#fecha_inicio').prop('disabled', true);
        }
    }

    function onchange_fecha_final() {
        if (Entrada($('#fecha_final').val())) {
            if (!$('#ended').is(':checked')) $('#ended').prop('checked', true);
        }
    }

    function onclick_ended() {
        if ($('#ended').is(':checked')) {
            alert("Debe especificar la fecha en la que se termin la tarea.");
            show_calendar('document.ftarea.fecha_final', $('#fecha_inicio').val());
        } else {
            $('#fecha_final').drop('disabled', true);
        }
    }

    function refreshp() {
        var id_responsable = $('#responsable').val();
        var month = $('#month').val();
        var year = $('#year').val();
        var day = $('#day').val();

        var id_tarea = 0;
        var url = 'fgantt_user.php?version=&action=<?= $action ?>&id_tarea=' + id_tarea +
            '&id_responsable=' + id_responsable;
        url += '&month=' + month + '&year=' + year + '&day=' + day;

        self.location = url;
    }

    function refreshta() {
        var id_tarea = $('#tarea').val();
        var month = $('#month').val();
        var year = $('#year').val();
        var day = $('#day').val();
        var url = 'fgantt_user.php?version=&action=<?= $action ?>&id_tarea=' + id_tarea;
        url += '&month=' + month + '&year=' + year + '&day=' + day;
        self.location = url;
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {

        $('#div_fecha').datetimepicker({
            format: 'DD/MM/YYYY H:mm A',
            minDate: '01/01/<?=$init_year?> 00:01',
            maxDate: '31/12/<?=$end_year?> 23:59',
            autoclose: true,
            inline: true,
            sideBySide: true
        });
        $('#div_fecha').click(function() {
            $(this).data("DateTimePicker").show();
        });
        $('#div_fecha').on('change', function() {
            validar_interval(1);
        });

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body container">
        <div class="card card-primary">
            <div class="card-header">ACTUALIZAR EL AVANCE DE LAS TAREAS (INDIVIDUAL)</div>
            <div class="card-body">

                <form name="ftarea" action='javascript:validar()' method=post>
                    <input type="hidden" name="exect" value="<?= $action ?>" />
                    <input type="hidden" name="id" value="<?=$id_tarea ?>" />
                    <input type="hidden" name="menu" value="regtarea" />

                    <div class="form-group row">
                        <label class="col-form-label col-md-3">
                            Tareas en la fecha:
                        </label>
                        <div class="col-md-9">
                            <div id="div_fecha" class="input-group date">
                                <input type="datetime" class="form-control input-sm" id="fecha" name="fecha" readonly
                                    value="<?= odbc2time($fecha)?>">
                                <span class="input-group-text"><span
                                        class="fa fa-calendar"></span></span>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (!empty($fecha)) {
                         $tarea_obj= new Ttarea($clink);
                         $tarea_obj->SetIdResponsable($id_responsable);

                         $tarea_obj->SetYear($year);
                         $tarea_obj->SetMonth($month);
                         $tarea_obj->SetDay($day);

                         $result_task= $tarea_obj->listar();
                     ?>
                    <div class="form-group row">
                        <label class="col-form-label col-md-1">

                        </label>
                        <div class="col-md-11">
                            <select name="tarea" id="tarea" size="4" class="form-control" onchange="refreshta()">
                                <?php
                                    while ($row = $clink->fetch_array($result_task)) {
                                        ?>
                                <option style="padding-top:3px;" value="<?=$row['id_tarea']?>"
                                    <?php if ($row['id_tarea'] == $id_tarea) echo "selected='selected'"; ?>>
                                    <?=$row['tarea']?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3">
                            Descripción:
                        </label>
                        <div class="col-md-9">
                            <label class="alert alert-info">
                                <?= $obj->GetDescripcion() ?>
                            </label>
                        </div>
                    </div>



                    <!-- buttom -->
                    <div class="btn-block btn-app">
                        <?php if ($action == 'update' || $action == 'add') { ?><button type="submit"
                            class="btn btn-primary">Aceptar</button><?php } ?>
                        <button type="reset" class="btn btn btn-warning"
                            onclick="self.location.href='<?php prev_page()?>'">Cancelar</button>
                        <button class="btn btn-danger" type="button"
                            onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                    </div>
                </form>

            </div><!-- panel-body -->
        </div><!-- panel -->
    </div> <!-- container -->

</body>

</html>

</tr>


<tr>
    <td width="95"><label for="nombre">Inici&oacute;?:</label></td>
    <td colspan="9">
        <input type="hidden" name="init_fecha_inicio" value="<?php echo $fecha_inicio_real; ?>" />
        <?php $begun= (empty($fecha_inicio_real)) ? NULL : "checked" ?>
        <input type="checkbox" name="begun" id="begun" value="1" <?php echo $begun ?> onclick="onclick_begun()" />
        <input id="fecha_inicio" name="fecha_inicio" type="text" class="form-control" style="width:100px;"
            value="<?php echo $fecha_inicio_real; ?>" onchange="onchange_fecha_inicio()" />
        &nbsp;<img src="../img/cal.gif" onclick="javascript:NewCssCal('fecha_inicio','ddMMyyyy')" style="cursor:pointer"
            alt="Click aqui para seleccionar la fecha" /></a>
    </td>
</tr>

<tr>
    <td><label for="label">Avance en %:</label></td>
    <td colspan="9">

        <table>
            <tr>
                <td width="70" valign="middle">
                    <input type="hidden" name="init_fecha" value="<?php echo $reg_fecha; ?>" />

                    <input name="real" style="width:50px; color:#FF0000; font-weight:bold;" type="text" id="real"
                        maxlength="6" value="<?php echo $value ?>" /><strong>%</strong>&nbsp;
                </td>

                <td width="90" valign="middle">&nbsp;<label for="label">hasta la fecha</label>&nbsp;</td>
                <td width="140" valign="middle">
                    <input id="fecha" name="fecha" type="text" class="form-control" style="width:100px;"
                        value="<?php echo $reg_fecha; ?>" onchange="onchange_fecha()" />
                    &nbsp;<img src="../img/cal.gif" onclick="javascript:NewCssCal('fecha','ddMMyyyy')"
                        style="cursor:pointer" alt="Click aqui para seleccionar la fecha" />
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td><label for="label">Observaciones:</label></td>
    <td colspan="9">
        <form-controlrea name="descripcion" rows="4" id="descripcion" class="form-control" style="width:100%;">
            <?php echo $obj->GetObservacion(); ?></form-controlrea>
    </td>
</tr>
<tr>
    <td width="95"><label for="nombre">Termin&oacute;?:</label></td>
    <input type="hidden" name="init_fecha_final" value="<?php echo $fecha_fin_real; ?>" />
    <?php $ended= (empty($fecha_fin_real)) ? NULL : "checked" ?>
    <td colspan="9">

        <input type="checkbox" name="ended" id="ended" value="1" <?php echo $ended ?> onclick="onclick_ended()" />
        <input id="fecha_final" name="fecha_final" type="text" class="form-control" style="width:100px;"
            value="<?php echo $fecha_fin_real; ?>" onchange="onchange_fecha_final()" />
        &nbsp;<img src="../img/cal.gif" onclick="javascript:NewCssCal('fecha_final','ddMMyyyy')" style="cursor:pointer"
            alt="Click aqui para seleccionar la fecha" />
    </td>
</tr>

<tr>
    <td></td>
    <td colspan="9"></td>
</tr>
</table>

<span class="submit" align="center">
    <?php if ($action == 'update' || $action == 'add') { ?><input value="Aceptar" type="submit">&nbsp; <?php } ?>

    <?php
			if ($action == 'update' || $action == 'list') {
				if ($action == 'update') 
                    $action= 'edit';

				if ($id_ref == 'gantt') 
                    $back= "../html/gantt_user.php";
				else 
                    $back= "../html/gantt.php";
			}
			else 
                $back= "../html/background.php?csfr_token=<?={$_SESSION['csfr_token']}?>&";
			?>

    <input value="Cancelar" type="reset" onclick="self.location.href='<?php echo $back ?>'">
</span>

</form>
</fieldset>