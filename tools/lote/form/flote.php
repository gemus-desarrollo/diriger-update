<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';

$nivel_user= $execfromshell ? _GLOBALUSUARIO : $_SESSION['nivel'];

require_once "../../../php/config.inc.php";
require_once "../php/connect.class.php";
require_once "../../../php/class/usuario.class.php";
require_once "../../../php/class/proceso.class.php";
require_once "../../../php/class/time.class.php";

require_once "../php/lote.class.php";
require_once "../php/export.class.php";

$date= add_date(date('Y-m-d'), null, -3);
$initdate= date('d/m/Y', strtotime($date));

$action= !empty($_GET['action']) ? $_GET['action'] : 'export';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'home';
$error= !empty($_GET['error']) ?  urldecode($_GET['error']) : null;
$filename= !empty($_GET['filename']) ? urldecode($_GET['filename']) : NULL;

$id_destino= !empty($_GET['id_destino']) ? $_GET['id_destino'] : null;
$origen= !empty($_GET['origen']) ? $_GET['origen'] : null;
$fecha= !empty($_GET['fecha']) ? urldecode($_GET['fecha']) : $initdate;
$observacion= !empty($_GET['observacion']) ?  urldecode($_GET['observacion']) : null;

$upload= !is_null($_GET['upload']) ? $_GET['upload'] : null;

$year= date('Y', strtotime(date2odbc($initdate)));

$obj= new Tlote($uplink);

if ($action == 'download') {
    $exect = 'export';
    $obj->action = 'export';

    $tb_deletes= !empty($_GET['tb_deletes']) ? 1 : 0; 
    $tb_eventos= !empty($_GET['tb_eventos']) ? 1 : 0;  
    $tb_objetivos= !empty($_GET['tb_objetivos']) ? 1 : 0;  
    $tb_programas= !empty($_GET['tb_programas']) ? 1 : 0;  
    $tb_indicadores= !empty($_GET['tb_indicadores']) ? 1 : 0;
    $tb_riesgos= !empty($_GET['tb_riesgos']) ? 1 : 0;  
    $tb_notas== !empty($_GET['tb_notas']) ? 1 : 0;      
}
if ($action == 'upload') {
    $exect = 'import';
    $obj->action = 'import';
}

$obj->action= $exect;

if (!empty($id_destino)) {
    $obj_prs= new Tproceso($uplink);
    $obj_prs->Set($id_destino);
    $obj->destino_code= $obj_prs->GetCodigo();
}

$result= !empty($id_destino) ? $obj->listar() : null;
$cant= !empty($id_destino) ? $obj->GetCantidad() : 0;

if ($action == 'download' && $cant == 0) {
    $fecha= strtotime(date2odbc($initdate)) < strtotime(date2odbc($fecha)) ? $initdate : $fecha;
}

if ($action == 'upload' && !empty($filename)) {
    $filename= $_GET['filename'];
    list($str1,$str2,$year,$month,$day,$hour,$minute)= split("_",$filename);
    $fecha= $day."/".$month."/".$year." ".$hour.":".$minute;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>EXPORTACIÓN/TRANSFERENCIA DE DATOS</title>

    <?php 
    $dirlibs= "../../../";
    require '../../../form/inc/_page_init.inc.php'; 
    ?>

    <link href="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../../../css/general.css?version=">
    <link rel="stylesheet" type="text/css" href="../../../css/table.css?version=">

    <link href="../../../libs/bootstrap-table/bootstrap-table.min.css" media="screen" rel="stylesheet" />
    <script type="text/javascript" src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../../../libs/windowmove/windowmove.css" media="screen" rel="stylesheet" />
    <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version=">
    </script>

    <script type="text/javascript" src="../../../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../../../js/string.js?version="></script>
    <script type="text/javascript" src="../../../js/time.js?version="></script>
    <script type="text/javascript" src="../../../js/general.js?version="></script>

    <script type="text/javascript" src="../../../js/form.js?version="></script>

    <style type="text/css">
    body {
        background-color: #fff;
    }

    #toolbar .alert {
        margin: 0px;
        padding: 4px;
    }

    .jumbotron.form {
        padding: 2px 4px;
        margin: 3px 2px;
        border: grove 1px rgba(192, 192, 192, .8);
        font-size: 12px;
    }
    </style>

    <script language="javascript">
    function refreshp() {
        var url = 'flote.php?action=<?=$action?>&observacion=' + encodeURI($('#observacion').val());
        <?php if ($action == 'download') { ?>
        url += '&id_destino=' + $('#destino').val() + '&fecha=' + encodeURI($('#fecha').val());

        url += '&tb_deletes=' + ($('#tb_deletes').is(':checked') ? 1 : 0);
        url += '&tb_eventos=' + ($('#tb_eventos').is(':checked') ? 1 : 0);
        url += '&tb_objetivos=' + ($('#tb_objetivos').is(':checked') ? 1 : 0);
        url += '&tb_programas=' + ($('#tb_programas').is(':checked') ? 1 : 0);
        url += '&tb_indicadores=' + ($('#tb_indicadores').is(':checked') ? 1 : 0);
        url += '&tb_riesgos=' + ($('#tb_riesgos').is(':checked') ? 1 : 0);
        url += '&tb_notas=' + ($('#tb_notas').is(':checked') ? 1 : 0);
        <?php } else { ?>
        var upload = $('#upload1').is(':checked') ? 1 : 2;

        url += '&origen=' + $('#origen').val() + '&fecha=' + encodeURI($('#fecha').val()) + '&upload=' + upload;
        <?php } ?>

        self.location = url;
    }

    function validar() {
        var form = document.forms[0];
        var url = '';
        var text;

        <?php if ($action == 'download') { ?>
        if (form.destino.value == 0) {
            $('#destino').focus(focusin($('#destino')));
            alert(
                "Debe especificar el proceso o Unidad Organizativa que sera destino de los datos que pretende exportar."
                );
            return;
        }

        url += '&fecha=' + encodeURI(form.fecha.value) + '&id_destino=' + form.destino.value;
        <?php } ?>

        <?php if ($action == 'upload') { ?>
        if (!$('#upload1').is(':checked') && !$('#upload2').is(':checked') <?php if (!$config->off_mail_server) { ?> &&
            !$('#upload3').is(':checked') <?php } ?>) {
            alert("Debe de especificar sí cargará un fichero lote o sí se conectará a un servidor remoto");
            return;
        }

        if ($('#upload1').is(':checked')) {
            if (!Entrada(form.lote.value) && form.exect.value == 'import') {
                alert('Debe selecionar el fichero lote que desea cargar al sistema.');
                return;
            }

            var file = form.lote.value;

            if (file.substr(file.length - 7, 7) != '.xml.gz' && file.substr(file.length - 14, 14) != '.xml.gz.mcrypt') {
                text = "Existe un error en el formato del fichero lote. La extención del fichero indica ";
                text += "que no fue generado por el sistema Diriger.";
                alert(text);
                return;
            }

            url += '&lote=' + encodeURI(form.lote.value);
        }

        if ($('#upload2').is(':checked') && form.origen.value == 0) {
            $('#origen').focus(focusin($('#origen')));
            alert("Debe especificar el origen de los datos");
            return
        }
        <?php } ?>

        if (!Entrada(form.fecha.value) &&
            (form.exect.value == 'export' || (form.exect.value == 'import' && $('#upload2').is(':checked')))) {
            alert('Introduzca la fecha a partir de la cual se escogerán los datos a conformar el lote');
            return;
        } else if (!isDate_d_m_yyyyy(form.fecha.value) && form.exect.value == 'export') {
            alert('Fecha con formato incorrecto. (d/m/yyyy) Ejemplo: 01/01/2010');
            return;
        }

        url += '&observacion=' + encodeURI(form.observacion.value) + '&fecha=' + encodeURI($('#fecha').val());
        url += '&exect=' + form.exect.value + '&action=' + form.exect.value + '&menu=lote';

        if (form.exect.value != 'import')
            form.action = '../php/export.interface.php?signal=form' + url;
        else {
            if ($('#upload1').is(':checked')) {
                form.action = '../php/file.interface.php?signal=form' + url;
            }
            if ($('#upload2').is(':checked')) {
                var origen = $('#origen').val();
                form.action = '../php/read_write.interface.php?signal=webservice&origen=' + origen + url;
            }
            if ($('#upload3').is(':checked')) {
                form.action = '../php/import.interface.php?signal=home&origen=&' + url;
            }
        }

        form.submit();
    }
    </script>

    <script type="text/javascript">
    var focusin;

    $(document).ready(function() {
        //When page loads...
        $(".tabcontent").hide(); //Hide all content
        $("ul.nav li:first").addClass("active").show(); //Activate first tab
        $(".tabcontent:first").show(); //Show first tab content

        //On Click Event
        $("ul.nav.nav-tabs li").click(function() {
            $("ul.nav.nav-tabs li").removeClass("active"); //Remove any "active" class
            $(this).addClass("active"); //Add "active" class to selected tab
            $(".tabcontent").hide(); //Hide all tab content

            var activeTab = $(this).find("a").attr(
                "href"); //Find the href attribute value to identify the active tab + content          
            $("#" + activeTab).fadeIn(); //Fade in the active ID content
            //         $("#" + activeTab + " .form-control:first").focus();
            return false;
        });

        InitDragDrop();

        $('#div_fecha').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('#lote').prop('disabled', 'disabled');
        $('#origen').prop('disabled', 'disabled');
        $('#div_fecha').show();

        if ($('#upload1').is(':checked')) {
            $('#lote').prop('disabled', false);
            $('#origen').prop('disabled', 'disabled');
            $('#div_fecha').hide();
        }
        if ($('#upload2').is(':checked')) {
            $('#lote').prop('disabled', 'disabled');
            $('#origen').prop('disabled', false);
            $('#div_fecha').show();
        }
        if ($('#upload3').is(':checked')) {
            $('#lote').prop('disabled', 'disabled');
            $('#origen').prop('disabled', 'disabled');
        }

        $('#upload1').click(function() {
            if ($('#upload1').is(':checked')) {
                $('#lote').prop('disabled', false);
                $('#origen').prop('disabled', 'disabled');
                $('#div_fecha').hide();
            }
        });
        $('#upload2').click(function() {
            if ($('#upload2').is(':checked')) {
                $('#origen').prop('disabled', false);
                $('#div_fecha').show();
                $('#lote').prop('disabled', 'disabled');
            }
        });

        <?php if (!$config->off_mail_server) { ?>
        $('#upload3').click(function() {
            if ($('#upload3').is(':checked')) {
                $('#origen').prop('disabled', 'disabled');
                $('#lote').prop('disabled', 'disabled');
            }
        });
        <?php } ?>

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
        if ($action == 'download'){
            $title = "CREAR Y EXPORTAR LOTE DE DATOS ";
            $text = "exportados";
        } else{
            $title = "IMPORTAR LOTE DE DATOS";
            $text = "importados";
        }
        ?>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header"><?=$title?></div>
                <div class="card-body">

                    <nav>
                        <ul class="nav nav-tabs" role="tablist">
                            <li id="nav-tab1"><a href="tab1">Generales</a></li>
                            <li id="nav-tab2" <?php if ($action != "download") { ?>class="hidden" <?php } ?>><a
                                    href="tab2">Filtrado</a></li>
                            <li id="nav-tab3"><a href="tab3"><strong>Anteriores:</strong> <?= strtoupper($text)?></a>
                            </li>
                        </ul>
                    </nav>

                    <form name="flote" action='javascript:validar()' class="form-horizontal" method="post"
                        enctype="multipart/form-data">
                        <input type="hidden" id="exect" name="exect" value="<?=$exect?>" />
                        <input type="hidden" name="menu" value="lote" />

                        <div class="tabcontent" id="tab1">

                            <?php if ($action == 'upload') { ?>
                            <div id="div-upload1" class="jumbotron form">
                                <label class="checkbox text">
                                    <input type="radio" id="upload1" name="upload" value="1"
                                        <?php if ($upload == 1) echo "checked"?> />
                                    Cargar fichero lotes
                                </label>

                                <div class="form-group row">
                                    <label class="col-form-label col-2">
                                        Fichero(lote):
                                    </label>
                                    <div class="col-10">
                                        <input type="file" name="lote" class="btn btn-info" id="lote"
                                            value="Fichero Lote" />
                                    </div>
                                </div>
                            </div>

                            <div id="div-upload2" class="jumbotron form">
                                <label class="checkbox text">
                                    <input type="radio" id="upload2" name="upload" value="2"
                                        <?php if ($upload == 2) echo "checked"?> />
                                    Hacer conexión directa a servidor remoto
                                </label>

                                <div class="form-group row">
                                    <?php        
                                    if ($action == 'upload') {
                                       $obj_lote= new Tlote($uplink);
                                       $obj_lote->SetYear($year);
                                       $array_pr= $obj_lote->set_array_procesos(_SYNCHRO_AUTOMATIC_HTTP);
                                    }   
                                    ?>
                                    <label class="col-form-label col-2">
                                        Origen:
                                    </label>
                                    <div class="col-10">
                                        <select id="origen" name="origen" class="form-control" onchange="refreshp()">
                                            <option value="0">selecione .... </option>

                                            <?php foreach ($array_pr as $array) { ?>
                                            <option value="<?= $array['codigo'] ?>"
                                                <?php if ($array['codigo'] == $origen) echo "selected='selected'" ?>>
                                                <?="{$array['nombre']}, {$Ttipo_proceso_array[$array['tipo']]}, {$array['codigo']}"?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                            <?php if ($action == 'upload' && !$config->off_mail_server) { ?>
                            <div id="div-upload3" class="jumbotron form">
                                <label class="checkbox text">
                                    <input type="radio" id="upload3" name="upload" value="2"
                                        <?php if ($upload == 3) echo "checked"?> />
                                    Subir todos los lotes que esten el buzón de correo electrónico de diriger en el
                                    servidor <?=$config->incoming_mail_server?>
                                </label>
                            </div>
                            <?php } ?>

                            <?php
                               $readonly = null;

                               if ($action == 'download') {
                                   $fecha = !is_null($cronos) ? $cronos : $fecha;
                               }
                               if ($action == 'upload') {
                                   $readonly = "readonly='readonly'";
                                   if (is_null($filename))
                                       $fecha = null;
                               }
                               ?>
                            <br />

                            <div class="form-group row">
                                <label class="col-form-label col-2">
                                    A partir de la fecha:
                                </label>
                                <div class="col-3">
                                    <div id="div_fecha" class="input-group date" data-date-language="es">
                                        <input id="fecha" name="fecha" class="form-control" value="<?=$fecha?>"
                                            <?=$readonly ?>>
                                        <span class="input-group-text"><span
                                                class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                            </div>

                            <?php
                               if ($action == 'download') {
                                   $obj_lote= new Tlote($uplink);
                                   $obj_lote->SetYear($year);
                                   $array_pr= $obj_lote->set_array_procesos();
                                ?>

                            <div class="form-group row">
                                <label class="col-form-label col-2">
                                    Destino:
                                </label>
                                <div class="col-10">
                                    <select id="destino" name="destino" class="form-control" onchange="refreshp()">
                                        <option value="0">selecione .... </option>

                                        <?php foreach ($array_pr as $array) { ?>
                                        <option value="<?= $array['id'] ?>"
                                            <?php if ($array['id'] == $id_destino) echo "selected='selected'" ?>>
                                            <?="{$array['nombre']}, {$Ttipo_proceso_array[$array['tipo']]}, {$array['codigo']}"?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="form-group row">
                                <label class="col-form-label col-2">
                                    Observación:
                                </label>
                                <div class="col-10">
                                    <textarea name="observacion" rows="4" id="observacion"
                                        class="form-control"><?=$observacion?></textarea>
                                </div>
                            </div>
                        </div> <!-- End tab 1-->

                        <div class="tabcontent" id="tab2" <?php if ($action != "download") {?>style="display: none"
                            <?php } ?>>
                            <fieldset style="height: 400px;">
                                <legend>
                                    Exportar solo la informacion relativa a las siguientes tablas:
                                </legend>

                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_deletes" name="tb_deletes" value="1"
                                            <?php if ($tb_deletes) {?> checked="checked" <?php } ?> />
                                        <strong>tdeletes</strong> Las acciones de borrado que se han realizado a partir
                                        de la fecha seleccionada.
                                    </label>
                                </div>
                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_eventos" name="tb_eventos" value="1"
                                            <?php if ($tb_eventos) {?> checked="checked" <?php } ?> />
                                        <strong>teventos</strong> Las tareas, actividades y auditorias con fecha de
                                        inicio a partir de la fecha seleccionada.
                                    </label>
                                </div>
                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_objetivos" name="tb_objetivos" value="1"
                                            <?php if ($tb_objetivos) {?> checked="checked" <?php } ?> />
                                        <strong>tobjetivos</strong> Todos los objetivos y los valores registrados a
                                        partir de la fecha seleccionada.
                                    </label>
                                </div>
                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_programas" name="tb_programas" value="1"
                                            <?php if ($tb_programas) {?> checked="checked" <?php } ?> />
                                        <strong>tprogramas</strong> Todos los programas y los valores registrados a
                                        partir de la fecha seleccionada.
                                    </label>
                                </div>
                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_indicadores" name="tb_indicadores" value="1"
                                            <?php if ($tb_indicadores) {?> checked="checked" <?php } ?> />
                                        <strong>tindicadores</strong> Todos los indicadores y los valores registrados a
                                        partir de la fecha seleccionada.
                                    </label>
                                </div>
                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_riesgos" name="tb_riesgos" value="1"
                                            <?php if ($tb_riesgos) {?> checked="checked" <?php } ?> />
                                        <strong>triesgos</strong> Todos los riesgos y sus estados registrados a partir
                                        de la fecha seleccionada.
                                    </label>
                                </div>
                                <div class="checkbox alert alert-default">
                                    <label>
                                        <input type="checkbox" id="tb_notas" name="tb_notas" value="1"
                                            <?php if ($tb_notas) {?> checked="checked" <?php } ?> />
                                        <strong>tnotas</strong> Todos las Notas de hallazgo y sus estados registrados a
                                        partir de la fecha seleccionada.
                                    </label>
                                </div>
                            </fieldset>
                        </div>

                        <div class="tabcontent" id="tab3">
                            <div id="toolbar">
                                <div class="alert alert-info">Lotes <?=$text?>:</div>
                            </div>

                            <table id="table" class="table table-hover table-striped" data-toggle="table"
                                data-height="400" data-toolbar="#toolbar" data-search="true">
                                <thead>
                                    <tr>
                                        <th>FILE</th>
                                        <th>USUARIO</th>
                                        <th>FECHA</th>
                                        <th>REFERENCIA</th>
                                        <th>OBSERVACIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                       $i = 0;
                                       while ($row = $uplink->fetch_array($result)) {
                                           $file = _EXPORT_DIRIGER_DIR . $row['lote'];
                                       ?>
                                    <tr>
                                        <td>
                                            <a href="../download.php?file=<?= $file ?>&send_file=1">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <?= stripslashes($row['nombre']) ?>
                                        </td>
                                        <td>
                                            <?= odbc2time($row['cronos'])?>
                                        </td>
                                        <td>
                                            <?php
                                                   if ($action == 'download')
                                                       echo !empty($row['date_cutoff']) ? odbc2time($row['date_cutoff']) : "";
                                                   ?>
                                        </td>
                                        <td>
                                            <?php if ($row['tb_filter']) { ?>
                                            <i class="fa fa-filter fa-2x" style="color:#00a7d0"></i>
                                            <?php } ?>
                                            <?= stripslashes($row['observacion']) ?>
                                            <?php if ($row['tb_filter']) { ?>
                                            <br /><?=$row['tb_filter']?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php ++$i;
                                       } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="btn-block btn-app">
                            <?php if ($action == 'download') { ?>
                            <button class="btn btn-primary" type="submit">Generar</button>
                            <?php } ?>
                            <?php if ($action == 'upload') { ?>
                            <button class="btn btn-primary" type="submit">Cargar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='resume.php?signal=home&action=resume'">Cancelar</button>
                        </div>
                    </form>

                    <?php if ($action == 'download' && !empty($file)) { ?>
                    <iframe style="display:none" src="download.php?file=<?="$file"?>"></iframe>
                    <?php  } ?>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->
    </div>
</body>

</html>