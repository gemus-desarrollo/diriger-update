<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

require_once "../php/inc.php";
session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'form';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$error= null;

if ($signal == 'execute') {
    $config->SetLink($clink);
    $error= $config->set_param();
}

if ($signal == 'form' || !is_null($error)) {
    $url_page= "../form/foptions.php?version=".$_SESSION['update_no']."&action=$action";
    add_page($url_page, $action, 'f');
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>OPCIONES DE CONFIGURACIÓN</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/moment.min.js"></script>
    <script src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    .form-group table {
        border: none;
    }

    .form-group table td {
        padding: 4px 4px;
    }
    </style>

    <script type="text/javascript">
    function validar_backup() {
        if (!Entrada($('#url_backup').val())) {
            $('#url_backup').focus(focusin($('#url_backup')));
            alert("Debe especificar la carpeta remota compartida en red para la salva del sistema");
            return false;
        }
        if (!Entrada($('#user_backup').val())) {
            $('#user_backup').focus(focusin($('#user_backup')));
            alert("Especifique el usuario con permiso de acceso a la carpeta remota");
            return false;
        }
        if (!Entrada($('#passwd_backup').val()) && !Entrada($('#passwd_backup').val())) {
            $('#passwd_backup').focus(focusin($('#passwd_backup')));
            alert("Debe especificar la clave de acceso del usuario con permiso de acceso a la carpeta remota.");
            return false;
        }

        return true;
    }

    function test_backup(flag) {
        if (!validar_backup())
            return;

        var url_backup = encodeURI($('#url_backup').val());
        var user_backup = encodeURI($('#user_backup').val());
        var passwd_backup = encodeURI($('#passwd_backup').val());
        var smb_version = $('#smb_version').val();

        var url = '../form/ajax/test_backup.ajax.php?&url_backup=' + url_backup + '&user_backup=' + user_backup;
        url += '&passwd_backup=' + passwd_backup + '&smb_version=' + smb_version + '&test=' + flag;

        var capa = 'div-ajax-body';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);

        displayFloatingDiv('div-ajax-panel', "PRUEBA DE CONEXIÓN A CARPETA REMOTA", 60, 0, 10, 10);
    }

    function validar() {
        var form = document.forms[0];
        var text = "";

        if (parseInt($('#delay').val()) >= parseInt($('#inactive').val())) {
            $('#inactive').focus(focusin($('#inactive')));
            alert(
                "El tiempo máximo de inactividad del sistema debe ser MAYOR al tiempo de refresco de las páginas de tableros y resúmenes.");
            return;
        }

        if (!Entrada($('#url_backup').val())) {
            text = "Desea especificar una carpeta compartida en la red para la proteccion de las salvas?";
            confirm(text, function(ok) {
                if (ok) {
                    if (!validar_backup())
                        return;
                    else
                        this_1();
                } else {
                    this_1();
                }
            });
        } else {
            if (!validar_backup())
                return;
            else
                this_1();
        }

        function this_1() {
            form.action = 'foptions.php?csfr_token=<?=$csfr_token?>&version=&action=add&signal=execute';
            form.submit();
        }
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-delay', 5, 65);
        new BootstrapSpinnerButton('spinner-inactive', 5, 65);
        new BootstrapSpinnerButton('spinner-daysbackup', 0, 30);
        new BootstrapSpinnerButton('spinner-monthpurge', 1, 7);
        new BootstrapSpinnerButton('spinner-maxexectime', 1, 24);
        new BootstrapSpinnerButton('spinner-maxwaittime', 1, 30);
        new BootstrapSpinnerButton('spinner-breaktime', 1, 16);

        $('#div_timepurge').timepicker({
            minuteStep: 15,
            showMeridian: true
        });
        $('#div_timepurge').timepicker().on('changeTime.timepicker', function(e) {
            $('#timepurge').val($(this).val());
        });
        $('#div_maxexectime').timepicker().on('changeTime.timepicker', function(e) {
            $('#maxexectime').val($(this).val());
        });

        $('#div_timesynchro').timepicker({
            minuteStep: 15,
            showMeridian: true
        });
        $('#div_timesynchro').timepicker().on('changeTime.timepicker', function(e) {
            $('#timesynchro').val($(this).val());
        });

        <?php
            if ($signal == 'execute') {
                if (is_null($error)) {
            ?>
        $('#_submit').css("display", "none");

        confirm("Para que algunos cambios se hagan efectivos deberá salir y entrar nuevamente al sistema Diriger. ¿Desea salir ahora del sistema?",
            function(ok) {
                if (!ok)
                    self.location.href = '../html/background.php?csfr_token=<?=$csfr_token?>&';
                else
                    parent.location.href = '../php/exit.php';
            });

        <?php } else { ?>
        alert("<?=$error?>");
        <?php } ?>
        <?php } ?>
    });
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">CONFIGURACIÓN DEL SISTEMA</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Seguridad</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Operaciones</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Planes de trabajo</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Plan General Mensual</a></li>
                        <li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Plan General Anual</a></li>
                        <li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Gestión de Riesgos y Notas</a></li>
                        <li id="nav-tab7" class="nav-item"><a class="nav-link" href="tab7">Planificación Estratégica</a></li>
                    </ul>

                    <?php if ($signal == 'form' || !is_null($error)) { ?>
                    <form action='javascript:validar()' class='form-horizontal' method=post>
                        <input type="hidden" name="menu" value="options" />

                        <input type="hidden" id="location" name="location" value="<?=$_SESSION['location']?>" />
                        <input type="hidden" id="local_proceso_tipo" name="local_proceso_tipo"
                            value="<?=$_SESSION['local_proceso_tipo']?>" />
                        <input type="hidden" id="empresa" name="empresa" value="<?=$_SESSION['empresa']?>" />
                        <input type="hidden" id="local_proceso_id" name="local_proceso_id"
                            value="<?=$_SESSION['local_proceso_id']?>" />
                        <input type="hidden" id="local_proceso_id_code" name="local_proceso_id_code"
                            value="<?=$_SESSION['local_proceso_id_code']?>" />

                        <!-- Seguridad -->
                        <div class="tabcontent" id="tab1">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="freeassign" name="freeassign" value="1"
                                        <?php if ($config->freeassign) echo "checked='checked'" ?> />
                                    Permitir que los usuarios puedan asignar tareas o delegar responsabilidades a otros
                                    usuarios no subordinados directos.
                                    No se aplica a aquellos usuarios que tengan permiso para gestionar los Planes
                                    Anuales, Mensuales, Planes de Auditoria y/o Controles, Planes de Prevención, etc.
                                </label>
                            </div>

                            <hr />
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    Refrescar los tableros y documentos resúmenes cada
                                </div>
                                <div class="col-sm-3">
                                    <div id="spinner-delay" class="input-group spinner">
                                        <input type="text" name="delay" id="delay" class="form-control"
                                            value="<?=$config->delay?>">
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
                                <div class="col-sm-5 ">
                                    minutos.
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-4">
                                    Tiempo máximo de inactividad
                                </div>
                                <div class="col-sm-3">
                                    <div id="spinner-inactive" class="input-group spinner">
                                        <input type="text" name="inactive" id="inactive" class="form-control"
                                            value="<?=$config->inactive?>">
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
                                <div class="col-sm-5">
                                    minutos. Pasado este tiempo sin interacción con el servidor, el usuario deberá de
                                    autenticarse nuevamente en el sistema.
                                </div>
                            </div>

                            <legend>Salva automática de la base de datos:</legend>
                            <div class="form-group row">
                                <div class="col-lg-4 ">
                                    Realizar salvas automáticas de la base de datos cada
                                </div>
                                <div class="col-lg-2 pull-left">
                                    <div id="spinner-daysbackup" class="input-group spinner">
                                        <input type="text" name="daysbackup" id="daysbackup" class="form-control"
                                            value="<?=$config->daysbackup?>">
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
                                <div class="col-lg-6 pull-left">
                                    días. Cero significa que no se harán salvas automáticas y usted se hace responsable
                                    de las consecuencias
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-sm-5 col-md-3 col-lg-3">
                                    Carpeta Windows remota para la Salva (solo sistemas unix/linux)
                                </label>
                                <div class="col-sm-7 col-md-9 col-lg-9">
                                    <input type="text" class="form-control" id="url_backup" name="url_backup"
                                        value="<?=$config->url_backup?>" />
                                </div>
                                <div class="row col-lg-12">
                                    <span class="text text-info">
                                        Ejemplo: Si fuese la pc con IP <strong>10.0.0.18</strong> y la carpeta
                                        compartida llamada <strong>salvas</strong> se deberá escribir:
                                        <strong>//10.0.0.18/salvas</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-3 col-lg-3">
                                    <label class="col-form-label col-md-4 col-lg-4">
                                        Versión SMB:
                                    </label>
                                    <div class="col-md-8 col-lg-8">
                                        <select id="smb_version" name="smb_version" class="form-control">
                                            <option value="0">Automático</option>
                                            <option value="1.0"
                                                <?php if ($config->smb_version == 1.0) echo "selected='selected'"?>>1.0
                                            </option>
                                            <option value="2.0"
                                                <?php if ($config->smb_version == 2.0) echo "selected='selected'"?>>2.0
                                            </option>
                                            <option value="2.1"
                                                <?php if ($config->smb_version == 2.1) echo "selected='selected'"?>>2.1
                                            </option>
                                            <option value="3.0"
                                                <?php if ($config->smb_version == 3.0) echo "selected='selected'"?>>3.0
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-md-4 col-lg-4">
                                            Usuario de Carpeta:
                                        </label>
                                        <div class="col-md-8 col-lg-8">
                                            <input type="text" class="form-control" id="user_backup" name="user_backup"
                                                value="<?= addslashes($config->user_backup)?>" />
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-md-4 col-lg-4">
                                            Clave:
                                        </label>
                                        <div class="col-md-8 col-lg-8">
                                            <input type="password" class="form-control" id="passwd_backup"
                                                name="passwd_backup" value="<?= addslashes($config->passwd_backup)?>" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 col-lg-3">
                                    <div class="d-none d-md-block">
                                        <br /><br /><br />
                                    </div>
                                    <button type="button" onclick="test_backup(1)" id="btn-bckup"
                                        class="btn btn-warning">Crear conexión</button>
                                </div>
                            </div>
                        </div> <!-- Seguridad -->

                        <!-- Operaciones y Mantenimiento -->
                        <div class="tabcontent" id="tab2">
                            <legend>GENERALES:</legend>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    Utilizar codificación de caracteres:
                                </div>
                                <div class="col-md-3 pull-left">
                                    <select class="form-control" id="charset" name="charset">
                                        <option value="" <?php if (empty($config->charset)) {?>selected="selected"
                                            <?php } ?>>... </option>
                                        <option value="utf8"
                                            <?php if ($config->charset == 'utf8') { ?>selected="selected" <?php } ?>>
                                            utf8</option>
                                        <option value="latin1"
                                            <?php if ($config->charset == 'latin1') { ?>selected="selected" <?php } ?>>
                                            latin1</option>
                                    </select>
                                </div>
                            </div>

                            <legend>OPERACIONES:</legend>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    El tiempo maximo de espera de una operación es
                                </div>
                                <div class="col-md-2 pull-left">
                                    <div id="spinner-maxexectime" class="input-group spinner">
                                        <input type="text" name="maxexectime" id="maxexectime" class="form-control"
                                            value="<?=$config->maxexectime?>">
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
                                <div class="col-md-6 pull-left">
                                    horas. Al terminar este tiempo se da por finalizada y se cierra la operación.
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-4">
                                    El tiempo maximo de espera del sistema para las tareas programadas es
                                </div>
                                <div class="col-md-2 pull-left">
                                    <div id="spinner-maxwaittime" class="input-group spinner">
                                        <input type="text" name="maxwaittime" id="maxwaittime" class="form-control"
                                            value="<?=$config->maxwaittime?>">
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
                                <div class="col-md-6 pull-left">
                                    minutos. Al terminar este tiempo se iniciarán las operaciones de salva de base de
                                    datos,
                                    limpieza o mantenimiento del sistema o la sincronización de datos según se requiera.
                                </div>
                            </div>

                            <legend>MANTENIMIENTO:</legend>
                            <div class="form-group row">
                                <div class="col-md-3">
                                    Realizar limpieza del sistema cada
                                </div>
                                <div class="col-md-2 pull-left">
                                    <div id="spinner-monthpurge" class="input-group spinner">
                                        <input type="text" name="monthpurge" id="monthpurge" class="form-control"
                                            value="<?=$config->monthpurge?>">
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
                                <div class="col-md-6 pull-left">
                                    meses. Elimina información redundante e innecesaria existente en la Base de Datos.
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    Realizar la salva, limpieza y el mantenimiento del sistema en la noche/madrugada
                                    despues de la hora:
                                </div>
                                <div class="col-md-2 pull-left">
                                    <?php
                                         $time= date("H:i:s", $config->timepurge);
                                         ?>
                                    <div id="div_timepurge" class="input-group bootstrap-timepicker timepicker">
                                        <select id="timepurge" name="timepurge" class="form-control input-small">
                                            <option value="0">Todo el día</option>
                                            <?php
                                                $i= 0;
                                                for ($ddtime= "1969-01-01 16:45:00"; strtotime($ddtime) < strtotime("1969-01-01 23:59:00"); ++$i) {
                                                    $ddtime= add_date($ddtime, 0, 0, 0, 0, 15);
                                                    $xtime= date('H:i:s', strtotime($ddtime));
                                                ?>
                                            <option value="<?=$xtime?>" <?php if ($xtime == $config->timepurge) {?>
                                                selected="selected" <?php } ?>><?=date('h:i A', strtotime($ddtime))?>
                                            </option>
                                            <?php } ?>
                                            <?php
                                                for ($ddtime= "1969-01-01 00:00:00"; strtotime($ddtime) < strtotime("1969-01-01 05:45:00"); ++$i) {
                                                    $ddtime= add_date($ddtime, 0, 0, 0, 0, 15);
                                                    $xtime= date('H:i:s', strtotime($ddtime));
                                                ?>
                                            <option value="<?=$xtime?>" <?php if ($xtime == $config->timepurge) {?>
                                                selected="selected" <?php } ?>><?=date('h:i A', strtotime($ddtime))?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr />

                            <div class="form-group row">
                                <div class="col-2">
                                    <select id="dirsize" name="dirsize" class="form-control">
                                        <?php for ($i= 2; $i <= 120; $i+=2) { ?>
                                        <option value="<?=$i?>" <?php if ($i == $config->dirsize) echo 'selected';?>>
                                            <?=$i?> GB</option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-10">
                                    Tamaño maximo, en gygabytes de los directorios 'export', 'import' y 'sql'. Es el
                                    tamaño máximo que pueden alcanzar
                                    los directorios para las salvas de la base de datos y la sincronización de lotes. Al
                                    alcansarce este tamaño máximo,
                                    los ficheros más viejos serán eliminados.
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-2">
                                    <select id="maxfilesize" name="maxfilesize" class="form-control">
                                        <?php for ($i= 5; $i <= 150; $i+=5) { ?>
                                        <option value="<?=$i?>" <?php if ($i == $config->maxfilesize) echo 'selected';?>>
                                            <?=$i?> MB
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-10">
                                    Tamaño maximo, en megabytes, de los ficheros y documentos que se pueden subir al Sistema de Diriger.
                                </div>
                            </div>                            
                        </div> <!-- Operaciones y Mantenimiento -->

                        <!-- Planes de trabajo -->
                        <div class="tabcontent" id="tab3">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="show_prs_plan" value="1"
                                        <?php if ($config->show_prs_plan) echo "checked='checked'" ?> />
                                    Permitir que los Procesos Internos subordinados directamente a la entidad tengan
                                    Planes de Trabajo Generales Anuales y Mensuales
                                </label>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" id="show_group_dpto_plan" name="show_group_dpto_plan"
                                                <?php if ($config->show_group_dpto_plan) {?>checked="yes" <?php } ?>
                                                value="1" />
                                            Permitir que los Grupos de Trabajo y Departamentos subordinados a las
                                            direcciones funcionales tengan Planes de Trabajo Generales Anuales y
                                            Mensuales
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr />

                            <div class="form-group row">
                                <div class="col-lg-5">
                                    Registrar las actividades y tareas como "INCUMPLIDAS" pasados los
                                </div>

                                <div class="col-lg-2">
                                    <div id="spinner-breaktime" class="input-group spinner">
                                        <input type="text" name="breaktime" id="breaktime" class="form-control"
                                            value="<?=$config->breaktime?>">
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
                                <div class="col-lg-5">
                                    días, posterior a la fecha y hora planificadas para su cumplimiento.
                                </div>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="summaryextend" value="1"
                                        <?php if ($config->summaryextend) echo "checked='checked'" ?> />
                                    Imprimir los datos relativos a las tareas y actividades que han sido modificadas,
                                    extra-planes (no aprobadas) e incumplidas.
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="hoursoldier" value="1"
                                        <?php if ($config->hoursoldier) echo "checked='checked'" ?> />
                                    Utilizar el formato de hora militar
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="onlypost" value="1"
                                        <?php if ($config->onlypost) echo "checked='checked'" ?> />
                                    Solo mostrar el cargo o función del usuario. No se muestran los nombres
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="sipac_format" value="1"
                                        <?php if ($config->sipac_format) echo "checked='checked'" ?> />
                                    Colocar el punto y coma (;) al final de cada participante. Para compatibilizar los
                                    documentos con el error del sistema SIPAC de la UCI.
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="responsable_planwork" value="1"
                                        <?php if ($config->responsable_planwork) echo "checked='checked'" ?> />
                                    En el resumen de los Planes especificar el <strong>RESPONSABLE</strong> de la tarea
                                    o actividad. Se elimina la columna <strong>QUIEN LAS ORIGINO</strong>.
                                </label>
                            </div>

                            <hr class="divider">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="accords_automatic" value="1"
                                        <?php if ($config->accords_automatic) echo "checked='checked'" ?> />
                                    Los acuerdos de las reuniones toman cumplimiento automaticamente desde los Planes mensuales e individuales.
                                </label>
                            </div>                            
                            <hr class="divider" style="margin: 20px 20px;">
                            </hr>

                        </div> <!-- Planes de trabajo -->

                        <!-- Planes de Mensual -->
                        <div class="tabcontent" id="tab4">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="seemonthplan" value="1"
                                        <?php if ($config->seemonthplan) echo "checked='checked'" ?> />
                                    Los usuarios pueden ver el Plan Mensual de su Organización y el de su Área
                                    funcional. (No pueden hacer cambios ni modificaciones, al menos que se especifique
                                    en particular para el usuario)
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="hourcolum" value="1"
                                        <?php if ($config->hourcolum) echo "checked='checked'" ?> />
                                    Mostrar la hora en columna separada con título "Hora"
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="datecolum" value="1"
                                        <?php if ($config->datecolum) echo "checked='checked'" ?> />
                                    Mostrar las fechas en columna separada con título "DIA". Se hace referencia al día
                                    de la semana y al día del mes
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="placecolum" value="1"
                                        <?php if ($config->placecolum) echo "checked='checked'" ?> />
                                    Mostrar el lugar en columna separada con título "LUGAR"
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="observcolum" value="1"
                                        <?php if ($config->observcolum) echo "checked='checked'" ?> />
                                    En el documento impreso incluir la columna "OBSERVACIONES"
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="monthstack" value="1"
                                        <?php if ($config->monthstack) echo "checked='checked'" ?> />
                                    Por omisión, mostrar las actividades agrupadas mostrandose los días separados por
                                    coma (,) o en intervalos sí son días consecutivos.
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="grouprows" value="1"
                                        <?php if ($config->grouprows) echo "checked='checked'" ?> />
                                    Imprimir con las tareas o actividades agrupadas según los capítulos de la
                                    Instrucción No.1 del 2012 para el Plan Anual
                                </label>
                            </div>
                            <br />

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="use_mensual_plan_organismo" value="1"
                                        <?php if ($config->use_mensual_plan_organismo) echo "checked='checked'" ?> />
                                    En el Plan Generales Mensuales de la Entidad Principal se mostrará los organismo e instituciones 
                                    a los que dirije las activiaddes  
                                </label>
                            </div>

                            <br />
                            <hr class="divider" style="margin: 20px 20px;" />
                        </div> <!-- Planes de Mensual -->

                        <!-- Planes de Anual -->
                        <div class="tabcontent" id="tab5">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="seeanualplan" value="1"
                                        <?php if ($config->seeanualplan) echo "checked='checked'" ?> />
                                    Los usuarios pueden ver el Plan Anual de su Organización y el de su Área funcional.
                                    (No pueden hacer cambios ni modificaciones, al menos que se especifique en
                                    particular para el usuario)
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="hourcolum_y" value="1"
                                        <?php if ($config->hourcolum_y) echo "checked='checked'" ?> />
                                    Mostrar la hora en columna separada con título "Hora"
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="placecolum_y" value="1"
                                        <?php if ($config->placecolum_y) echo "checked='checked'" ?> />
                                    Mostrar el lugar en columna separada con título "LUGAR"
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="observcolum_y" value="1"
                                        <?php if ($config->observcolum_y) echo "checked='checked'" ?> />
                                    En el documento impreso incluir la columna "OBSERVACIONES"
                                </label>
                            </div>

                            <br />
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="use_anual_plan_organismo" value="1"
                                        <?php if ($config->use_anual_plan_organismo) echo "checked='checked'" ?> />
                                    En el Plan Anual de la Entidad Principal se mostrará los organismo e instituciones 
                                    a los que dirije las activiaddes  
                                </label>
                            </div>

                            <hr class="divider" style="margin: 20px 20px;" />
                        </div><!-- Planes de Anual -->

                        <!-- Gestión de Riesgos y Notas -->
                        <div class="tabcontent" id="tab6">
                            <div class="form-group row">
                                <div class="col-12">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" id="show_group_dpto_risk" name="show_group_dpto_risk"
                                                <?php if ($config->show_group_dpto_risk) {?>checked="yes" <?php } ?>
                                                value="1" />
                                            Permitir que los Grupos de Trabajo y Departamentos subordinados a las
                                            direcciones funcionales tengan Planes de Prevencion y Planes de Medidas
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <legend class="col-md-12">
                                En el documento de "Resumen de Riesgos". Incluir las siguientes columnas:
                            </legend>

                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" checked="checked" disabled="disabled" />
                                            Frecuencia o Probabilidad
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseeactivity" value="1"
                                                <?php if ($config->riskseeactivity) echo "checked='checked'" ?> />
                                            Actividad
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" checked="checked" disabled="disabled" />
                                            Nivel del Riesgo
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseeprocess" value="1"
                                                <?php if ($config->riskseeprocess) echo "checked='checked'" ?> />
                                            Proceso
                                        </label>
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" checked="checked" disabled="disabled" />
                                            Riesgo
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseedetection" value="1"
                                                <?php if ($config->riskseedetection) echo "checked='checked'" ?> />
                                            Nivel de Detección
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseedescription" value="1"
                                                <?php if ($config->riskseedescription) echo "checked='checked'" ?> />
                                            Manifestación
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" checked="checked" disabled="disabled" />
                                            Prioridad/Clasificación
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseetype1" value="1"
                                                <?php if ($config->riskseetype1) echo "checked='checked'" ?> />
                                            Interno(I)/Externo(E)
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseestate" value="1"
                                                <?php if ($config->riskseestate) echo "checked='checked'" ?> />
                                            Estado
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" checked="checked" disabled="disabled" />
                                            Impacto (Perdida esperada)
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="riskseeobserv" value="1"
                                                <?php if ($config->riskseeobserv) echo "checked='checked'" ?> />
                                            Observaciones
                                        </label>
                                    </div>
                                </div>
                            </div>                

                            <legend class="col-md-12">Gestión automatizada:</legend>
                            <div class="form-group row col-md-12">
                                <div class="checkbox col-12">
                                    <label>
                                        <input type="checkbox" name="automatic_risk" value="1"
                                            <?php if ($config->automatic_risk) echo "checked='checked'" ?> />
                                        El estado de los <b>riesgos</b> sera determinada por el sistema
                                    </label>
                                </div>
                                <div class="checkbox col-12">
                                    <label>
                                        <input type="checkbox" name="automatic_note" value="1"
                                            <?php if ($config->automatic_note) echo "checked='checked'" ?> />
                                        El estado de los <b>notas</b> sera determinada por el sistema
                                    </label>
                                </div>
                                
                                <hr class="divider col-12">
                                <div class="checkbox col-12">
                                    <label>
                                        <input type="checkbox" name="risk_note_automatic" value="1"
                                            <?php if ($config->risk_note_automatic) echo "checked='checked'" ?> />
                                        Las tareas asociadas a notas y riesgos toman cumplimiento automaticamente desde los Planes mensuales e individuales.
                                    </label>
                                </div>                                
                            </div>
                        </div> <!-- Gestión de Riesgos y Notas -->

                        <!-- Planificacion estrategica -->
                        <div class="tabcontent" id="tab7">
                            <div class="form-group row">
                                <div class="checkbox col-12">
                                    <label>
                                        <input type="checkbox" id="hide_values" name="hide_values"
                                            <?php if ($config->hide_value) echo "checked='yes'" ?> />
                                        Ocultar los valores de real y plan de los indicadores. Solo se ven los
                                        porcientos de cumplimiento.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="checkbox col-12">
                                    <label>
                                        <input type="checkbox" id="dpto_with_objetive" name="dpto_with_objetive"
                                            value="1" <?php if ($config->dpto_with_objetive) echo "checked='yes'" ?> />
                                        Los departamentos y grupos de trabajo podrán tener su propia Planificación
                                        Estratégica.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.php')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                    <?php } ?>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>

    <div id="div-ajax-panel" class="ajax-panel panel panel-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="col-xs-12">
                    <div id="win-title"
                        class="panel-title ajax-title clear col-11 win-drag"></div>

                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href="javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="div-ajax-body" class="card-body">

        </div>
    </div>
</body>

</html>