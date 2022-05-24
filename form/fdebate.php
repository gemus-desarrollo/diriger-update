<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/asistencia.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/asistencia.class.php";
require_once "../php/class/debate.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add' && empty($_GET['id_redirect'])) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}
if (!empty($_GET['signal']))
    $signal = $_GET['signal'];

$id_evento= $_GET['id_evento'];
$ifaccords= !empty($_GET['ifaccords']) ? (int)$_GET['ifaccords'] : 0;
$id_tematica= !empty($_GET['id_tematica']) ? $_GET['id_tematica'] : 0;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'debate';
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$obj_evento= new Tevento($clink);

$obj_matter= new Ttematica($clink);
$obj_matter->SetIdEvento($id_evento);

$obj_user= new Tusuario($clink);

$obj_assist= new Tasistencia($clink);
$obj_assist->SetIdEvento($id_evento);

$obj_evento->Set($id_evento);
$id_evento_code= $obj_evento->get_id_evento_code($id);
$asunto= $obj_evento->GetNombre();
$fecha_inicio= $obj_evento->GetFechaInicioPlan();
$fecha_fin= $obj_evento->GetFechaFinPlan();

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? $_GET['error'] : $obj_tematica->error;

$obj_matter->SetIdEvento($id_evento);
$obj_matter->SetIdResponsable($id_usuario);
$obj_matter->SetIdProceso(null);

if (!empty($id_tematica)) {
    $obj_matter->SetIdTematica($id_tematica);
    $obj_matter->Set();
    $matter= $obj_matter->GetObservacion();
    $numero_matter= $obj_matter->GetNumero();
    $hora_matter= odbc2ampm($obj_matter->GetFechaInicioPlan());
    $id_tematica_code= $obj_matter->get_id_code();
}

$visible= ($action == 'update' || $action == 'add') ? 'visible' : 'hidden';

$title= "ACTA DE REUNIÓN";
$title_th= "Temática";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title><?=$title?></title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" href="../css/tematica.css?version=" type="text/css" />
    <script type="text/javascript" src="../js/tematica.js?version="></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script type="text/javascript">
    var id_interview;
    var hour_interview;
    var editor;

    function validar_interview(action) {
        if (!Entrada($('#observacion_interview').val())) {
            $('#observacion_interview').focus(focusin($('#observacion_interview')));
            alert(
                "Debe escribir un resumen breve y conciso de lo dicho por el ponente, o lo concluido de su intervención en la reunión.");
            return;
        }
        if ($('#tematica').val() == 0) {
            $('#tematica').focus(focusin($('#tematica')));
            alert('Debe seleccionar la temática sobre la que se realiza el debate.');
            return;
        }
        if ($('#ponente').val() == 0) {
            $('#ponente').focus(focusin($('#ponente')));
            alert('No ha especificado el ponente que ha generado esta intervención');
            return;
        }

        $('#id_tematica').val($('#tematica').val());
        $('#time').val($('#hora').val());
        $('#id_asistencia').val($('#ponente').val());
        $('#observacion').val($('#observacion_interview').val());

        document.forms[0].action = "../php/debate.interface.php?exect=" + $('#exect').val();
        document.forms[0].submit();
    }

    function validar_time() {

    }

    function form_interview() {
        $('#exect').val('add');
        $('#tematica').prop('disabled', false);

        displayFloatingDiv('div-ajax-panel', "REGISTRANDO NUEVA INTERVENCIÓN", 90, 0, 3, 3);

        $('#id_debate').val(0);
        $('#observacion_interview').val('');

        $('#tematica').val($('#id_tematica').val());
        $('#tematica').prop('disabled', false);

        $('#ponente').val(0);
        $('#hora').val(id_interview > 0 ? $('#_hora_' + id_interview).val() : hour_interview);

        $('#exect').val('add');
    }

    function close_interview() {
        CloseWindow('div-ajax-panel');
    }

    function del_debate(id) {
        confirm("Usted pretende eliminar una ponencia de la discución de una temática. Esta usted seguro de quere continuar?",
            function(ok) {
                if (!ok) 
                    return false;
                else {
                    $('#id_debate').val(id);
                    $('#exect').val('delete');

                    document.forms[0].action = "../php/debate.interface.php";
                    document.forms[0].submit();
                }
            });
    }

    function edit_debate(id) {
        form_interview();

        id_interview = id;
        $('#id_debate').val(id);

        $('#observacion_interview').val($('#_observacion_' + id).val());

        $('#tematica').val($('#id_tematica_' + id).val());
        $('#tematica').prop('disabled', true);
        $('#id_tematica').val($('#id_tematica_' + id).val());

        $('#ponente').val($('#id_ponente_' + id).val());
        $('#hora').val($('#_hora_' + id).val());

        $('#exect').val('update');
    }

    function closePage() {
        var id_tematica = $("#id_tematica").val();
        var id_evento = $("#id_evento").val();
        var id_proceso = $("#proceso").val();
        var action = $("#exect").val();

        var url = "?action=" + action + "&id_evento=" + id_evento + "&id_proceso=" + id_proceso + "&ifaccords=0";
        url += "&id=" + id_tematica;
        <?php if ($signal == 'tematica') {?>
        self.location.href = 'fmatter.php' + url;
        <?php } else { ?>
        self.close();
        <?php } ?>
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        InitDragDrop();

        $('#div_hora').timepicker({
            minuteStep: 5,
            showMeridian: true
        });
        $('#div_hora').timepicker().on('changeTime.timepicker', function(e) {
            $('#hora').val($(this).val());
        });

        $('#observacion_interview').tinymce({
            selector: '#observacion_interview',
            theme: 'modern',
            height: 200,
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

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header"><?=$title?></div>
                <div class="card-body">

                    <form name="form-debate" class="form-horizontal" action='' method="post">
                        <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                        <input type="hidden" id="id_debate" name="id_debate" value="" />
                        <input type="hidden" id="id_evento" name="id_evento" value="<?=$id_evento?>" />
                        <input type="hidden" id="menu" name="menu" value="tematica" />
                        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
                        <input type="hidden" id="id_usuario" name="id_usuario" value="<?=$_SESSION['id_usuario']?>" />
                        <input type="hidden" id="id_tematica" name="id_tematica" value="<?=$id_tematica?>" />
                        <input type="hidden" id="id_tematica_code" name="id_tematica_code"
                            value="<?=$id_tematica_code?>" />
                        <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" id="fecha_inicio" name="fecha_inicio"
                            value="<?= odbc2time_ampm($fecha_inicio)?>" />

                        <input type="hidden" id="id_asistencia" name="id_asistencia" value="" />
                        <input type="hidden" id="time" name="time" value="" />
                        <input type="hidden" id="observacion" name="observacion" value="" />

                        <div class="alert alert-info " style="margin-bottom: 0px;">
                            <strong>Reunion:</strong> <?= $asunto ?>
                            <strong style="margin-left: 20px;">Fecha y Hora:</strong>
                            <?= odbc2time_ampm($fecha_inicio) ?><br />
                            <?php if (!empty($id_tematica)) { ?>
                            <strong>Temática:</strong>
                            <?= "<strong>No.</strong>$numero_matter  $matter <strong>Hora:</strong> $hora_matter" ?>
                            <?php } ?>
                        </div>

                        <div class="col-md-12">
                            <?php
                            $obj= new Tdebate($clink);
                            $obj->SetIdEvento($id_evento);
                            $obj->set_id_evento_code($id_evento_code);
                            $obj->SetIdProceso(null);
                            $obj->SetIdResponsable($id_usuario);
                            $obj->SetIdTematica($id_tematica);

                            $result= $obj->listar();
                            while ($row= $clink->fetch_array($result)) {
                            ?>
                                <input type="hidden" id="_hora_<?= $row['_id'] ?>" value="<?= time2ampm($row['hora']) ?>" />
                                <input type="hidden" id="id_tematica_<?= $row['_id'] ?>"
                                    value="<?= $row['_id_tematica'] ?>" />
                                <input type="hidden" id="id_ponente_<?= $row['_id'] ?>"
                                    value="<?= $row['id_asistencia'] ?>" />
                                <input type="hidden" id="_observacion_<?= $row['_id'] ?>"
                                    value="<?= textparse($row['_observacion'], true) ?>" />
                                <?php } ?>

                                <?php if ($action == 'add') { ?>
                                <div id="toolbar-debate" class="btn-btn-group btn-app">
                                    <button id="btn_agregar" type="button" onclick="form_interview();"
                                        class="btn btn-primary" style="visibility:<?= $visible ?>;">
                                        <i class="fa fa-plus"></i>Agregar
                                    </button>
                                </div>
                            <?php } ?>

                            <table id="table-debate" class="table table-hover table-striped" data-toggle="table"
                                data-toolbar="#toolbar-debate" data-height="450" data-search="true"
                                data-show-columns="true">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <?php if ($action == 'add') { ?>
                                        <th></th>
                                        <?php } ?>
                                        <th>Hora </th>
                                        <th>Ponente</th>
                                        <?php if (empty($id_tematica)) {?>
                                        <th>Temática</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i= 0;
                                    $clink->data_seek($result);
                                    while ($row= $clink->fetch_array($result)) {
                                        $id_interview= $row['_id'];
                                        $hour_interview= odbc2ampm($row['hora']);
                                    ?>
                                    <tr>
                                        <td>
                                            <?=++$i?>
                                        </td>

                                        <?php if ($action == 'add') { ?>
                                        <td>
                                            <a class="btn btn-danger btn-sm" title="Eliminar"
                                                onclick="del_debate(<?=$row['_id']?>);">
                                                <i class="fa fa-trash"></i>Eliminar
                                            </a>
                                            <a class="btn btn-warning btn-sm" title="Editar"
                                                onclick="edit_debate(<?=$row['_id']?>);">
                                                <i class="fa fa-edit"></i>Editar
                                            </a>
                                        </td>
                                        <?php } ?>

                                        <td>
                                            <?=odbc2ampm($row['hora'], $config->hoursoldier)?>
                                        </td>
                                        <td>
                                            <?php
                                            $array= $obj_assist->GetAsistencia($row['id_asistencia']);
                                            echo textparse($array['nombre'], true);
                                            if (!empty($array['cargo']))
                                                echo ', '.textparse($array['cargo']);
                                            if (!empty($array['endidad']))
                                                echo ', '.textparse($array['entidad']);
                                            ?>
                                        </td>
                                        <?php if (empty($id_tematica)) {?>
                                        <td>
                                            <?="{$row['_numero']}.  {$row['tematica']} <strong>Hora:</strong>".odbc2ampm($row['_fecha_inicio_plan'])?>
                                        </td>
                                        <?php } ?>
                                    </tr>

                                    <tr>
                                        <td colspan="<?=empty($id_tematica) ? '5' : '4'?>"
                                            style="border-bottom: 1px inset #824100">
                                            <?=$row['_observacion']?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <script type="text/javascript">
                        hour_interview = '<?=!empty($hour_interview) ? $hour_interview : odbc2ampm($fecha_inicio)?>';
                        id_interview = <?=!empty($id_interview) ? $id_interview : 0?>;
                        </script>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="reset" onclick="closePage()">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset" onclick="closePage()">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->
    </div>


    <div id="div-ajax-panel" class="container-fluid ajax-panel" data-bind="draganddrop">
        <div class="card card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="panel-title ajax-title col-11 m-0 win-drag">INTERVENCIÓN</div>
                    <div class="col-1 m-0 close">
                        <a href="javascript:HideContent('div-ajax-panel')" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Temática:
                        </label>
                        <div class="col-10">
                            <?php
                            if (isset($obj_matter)) 
                                unset($obj_matter);

                            $obj_matter= new Ttematica($clink);
                            $obj_matter->SetIdEvento($id_evento);
                            $obj_matter->set_id_evento_code($id_evento_code);
                            $result= $obj_matter->listar();
                            ?>
                            <select form="form-debate" id="tematica" name="tematica" class="form-control">
                                <option value="0"> Seleccione... </option>
                                <?php while ($row= $clink->fetch_array($result)) { ?>
                                <option value="<?=$row['id']?>"
                                    <?php if ($row['id'] == $id_tematica) {?>selected="selected" <?php } ?>
                                    style="padding: 2px 6px 6px 4px;">
                                    <?="<strong>No.</strong>{$row['numero']}  {$row['_nombre']}  / <strong>Hora:</strong>"?><?= odbc2ampm($row['_fecha_inicio_plan'])?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Ponente:
                        </label>
                        <div class="col-7">
                            <?php
                            $obj_assist= new Tasistencia($clink);
                            $obj_assist->SetYear($year);
                            $obj_assist->SetIdEvento($id_evento);
                            $obj_assist->set_id_evento_code($id_evento_code);
                            $obj_assist->SetIdUsuario(null);
                            $obj_assist->SetIdProceso(null);
                            $obj_assist->set_user_date_ref($fecha_inicio);

                            $obj_assist->listar(false);

                            $obj_user= new Tusuario($clink);
                            ?>
                            <select form="form-debate" id="ponente" name="ponente" class="form-control">
                                <option value="0">seleccione ...</option>

                                <?php
                                 foreach ($obj_assist->array_asistencias as $user) {
                                    $nombre= $user['nombre'];
                                    $cargo= $user['cargo'];

                                    if (empty($nombre))
                                        continue;
                                    ?>
                                <option value="<?=$user['id']?>">
                                    <?=$nombre?> <?php if (!empty($cargo)) echo ", {$cargo}"?>
                                </option>
                                <?php  }  ?>
                            </select>
                        </div>

                        <label class="col-form-label col-1 m-0">
                            Hora:
                        </label>
                        <div class="col-2">
                            <div id="div_hora" class="input-group bootstrap-timepicker timepicker date">
                                <input form="form-debate" type="datetime" id="hora" name="hora" class="form-control" />
                                <span class="input-group-text"><span class="fa fa-calendar-times-o"></span></span>
                            </div>
                        </div>
                    </div>

                    <textarea form="form-debate" name="observacion_interview" id="observacion_interview"></textarea>

                    <!-- buttom -->
                    <div class="btn-block btn-app">
                        <?php if ($action == 'update' || $action == 'add') { ?>
                        <button type="button" class="btn btn-primary" onclick="validar_interview()">Aceptar</button>
                        <?php } ?>

                        <button type="button" class="btn btn btn-warning" onclick="close_interview()">Cancelar</button>
                    </div>
                </div> <!-- form-horizontal -->

            </div><!-- panel-body -->
        </div><!-- panel -->
    </div> <!-- container -->

</body>

</html>