<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

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

$action= 'export';
$signal= 'home';
$error= !empty($_GET['error']) ?  urldecode($_GET['error']) : null;
$observacion= !empty($_GET['observacion']) ?  urldecode($_GET['observacion']) : null;
$fecha= !empty($_GET['fecha']) ? urldecode($_GET['fecha']) : $initdate;

$year= date('Y', strtotime(date2odbc($initdate)));

$obj= new Tlote($uplink);

$exect = 'export';
$obj->action= $exect;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>EXPORTACIÓN (TODOS LOS PROCESOS)</title>

    <?php 
    $dirlibs= "../../../";
    require '../../../form/inc/_page_init.inc.php'; 
    ?>

    <link href="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css">
    <script src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../../../css/general.css?version=">
    <link rel="stylesheet" type="text/css" href="../../../css/main.css?version=">
    <link rel="stylesheet" type="text/css" href="../../../css/table.css?version=">

    <link href="../../../libs/bootstrap-table/bootstrap-table.min.css" media="screen" rel="stylesheet" />
    <script type="text/javascript" src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../../../libs/windowmove/windowmove.css" media="screen" rel="stylesheet" />
    <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version="></script>

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
    </style>

    <script language="javascript">
    function validar() {
        var form = document.forms[0];
        var url = '';

        if (!Entrada(form.fecha.value) && form.exect.value == 'export') {
            alert('Introduzca la fecha a partir de la cual se escogerán los datos a conformar el lote');
            return;
        } else if (!isDate_d_m_yyyyy(form.fecha.value) && form.exect.value == 'export') {
            alert('Fecha con formato incorrecto. (d/m/yyyy) Ejemplo: 01/01/<?=$year?>');
            return;
        }

        url += '&fecha=' + encodeURI(form.fecha.value);
        url += '&observacion=' + encodeURI(form.observacion.value);
        url += '&exect=' + form.exect.value + '&action=' + form.exect.value + '&menu=lote';

        parent.location.href = '../php/export.interface.php?signal=home' + url;
    }
    </script>

    <script type="text/javascript">
    var focusin;

    $(document).ready(function() {
        InitDragDrop();

        $('#div_fecha').datepicker({
            format: 'dd/mm/yyyy'
        });
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
        $title= "CREAR Y EXPORTAR LOTES DE DATOS (TODAS LAS UNIDADES)"; 
        $text= "exportados";
        ?>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header"><?=$title?></div>
                <div class="card-body">

                    <form name="flote" action='javascript:validar()' class="form-horizontal" method=post
                        enctype="multipart/form-data">
                        <input type="hidden" id="exect" name="exect" value="<?=$exect?>" />
                        <input type="hidden" name="menu" value="lote" />

                        <?php
                            $readonly = null;
                            $fecha = !is_null($cronos) ? $cronos : $fecha;
                            ?>

                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Fecha de Inicio:
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

                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                Observación:
                            </label>
                            <div class="col-10">
                                <textarea name="observacion" rows="4" id="observacion"
                                    class="form-control"><?=$observacion?></textarea>
                            </div>
                        </div>

                        <div class="btn-block btn-app">
                            <button class="btn btn-primary" type="submit">Generar</button>
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