<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";

require_once "../../../inc.php";
require_once "../../../php/config.inc.php";

$nivel_user= $execfromshell ? _GLOBALUSUARIO : $_SESSION['nivel'];
require_once "../php/connect.class.php";

require_once "../../../php/class/usuario.class.php";
require_once "../../../php/class/proceso.class.php";
require_once "../../../php/class/code.class.php";
require_once "../../../php/class/time.class.php";

require_once "../php/lote.class.php";

$_SESSION['action']= 'export';
$_SESSION['debug']= 'no';

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'home';
$action= !empty($_GET['action']) ? $_GET['action'] : null;
$error= !empty($_GET['error']) ? $_GET['error'] : null;
$file= !empty($_GET['file']) ? $_GET['file'] : null;
$email= !empty($_GET['email']) ? urldecode($_GET['email']) : null;
$sendmail= !empty($_GET['sendmail']) ? $_GET['sendmail'] : false;

if (!is_null($error)) $file= null;

$obj_prs= new Tproceso($uplink);
$obj_code= new Tcode($uplink);
$obj= new Tlote($uplink);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>TRANSFERENCIA DE DATOS</title>

    <?php 
    $dirlibs= "../../../";
    require '../../../form/inc/_page_init.inc.php'; 
    ?>

    <link href="../../../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" rel="stylesheet"
        type="text/css">
    <script src="../../../libs/bootstrap-datetimepicker/moment.min.js"></script>
    <script src="../../../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js"></script>
    <script src="../../../libs/bootstrap-datetimepicker/bootstrap-datetimepicker.es.js"></script>

    <link rel="stylesheet" type="text/css" href="../../../css/general.css?version=">
    <link rel="stylesheet" type="text/css" href="../../../css/table.css?version=">

    <link href="../../../libs/bootstrap-table/bootstrap-table.min.css" media="screen" rel="stylesheet" />
    <script type="text/javascript" src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../../../libs/windowmove/windowmove.css" media="screen" rel="stylesheet" />
    <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version=">
    </script>

    <script type="text/javascript" src="../../../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../../../js/string.js?version="></script>
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
    function refreshp() {
        var id_proceso = $('#proceso').val();
        var id_proceso_code = $('#proceso_code_' + id_proceso).val();

        var url = 'resume.php?action=<?=$action?>&id_proceso=' + id_proceso + '&id_proceso_code=' + id_proceso_code;
        self.location = url;
    }

    $(document).ready(function() {
        <?php if (!is_null($error)) { ?>
        alert('<?=$error?>');
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body table onebar">
        <div class="container-fluid">
            <?php if (!empty($file)) { ?>
            <div class="alert alert-warning">
                <?php if (!$sendmail) { ?>
                <div class="text text-danger" style="font-weight: bold">EL LOTE NO HA SIDO ENVIADO AL CORREO DESTINO
                </div><br />
                <?php } else { ?>
                <div class="text text-success" style="font-weight: bold">EL LOTE FUE ENVIADO AL CORREO DESTINO</div>
                <br />
                <?php } ?>

                <a href="mailto:<?= $email ?>">
                    <i class="fa fa-mail-forward fa-3x"></i>
                    Para enviar por correo electrónico el fichero/lote creado, por favor, haga clic aquí…
                </a>
                <br />
                <a href="<?=_SERVER_DIRIGER?>tools/common/download.php?file=<?=$file?>&send_file=1">
                    <i class="fa fa-usb fa-3x"></i>
                    Para descargar a un soporte digital el último fichero/lote creado, por favor, haga clic aquí…
                </a>
            </div>
            <?php  } ?>

            <?php if (!is_null($error)) { ?>
            <div class="alert alert-danger"><?=urldecode($error) ?></div>
            <?php } ?>

            <div id="toolbar">
                <div class="alert alert-info">EXPORTACIONES E IMPORTACIONES ANTERIORES</div>
            </div>

            <table id="table" class="table table-hover table-striped" data-toggle="table" data-height="500"
                data-toolbar="#toolbar" data-search="true" data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>LOTE</th>
                        <th>USUARIO</th>
                        <th>ORIGEN</th>
                        <th>DESTINO</th>
                        <th>FECHA/HORA</th>
                        <th>CORTE</th>
                        <th>OBSERVACIÓN</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        $result = $obj->listar();
                        $i = 0;

                        while ($row = $uplink->fetch_array($result)) {
                            $ext= boolean($row['mcrypt']) ?  ".gz.mcrypt" : ".gz";
                            if ($row['action'] == "import")
                                $file = _IMPORT_DIRIGER_DIR . "~{$row['lote']}$ext";
                            if ($row['action'] == "export")
                                $file = _EXPORT_DIRIGER_DIR ."{$row['lote']}$ext";

                            $_origen = $obj_code->get_proceso_by_code($row['origen']);
                            $_destino = $obj_code->get_proceso_by_code($row['destino']);

                            $origen = $row['origen'] != $_SESSION['location'] ? $_origen['nombre'] . " (" . $row['origen'] . ")" : '&nbsp;';
                            $destino = $row['destino'] !=  $_SESSION['location'] ? $_destino['nombre'] . " (" . $row['destino'] . ")" : '&nbsp;';

                            $destino = ($row['destino'] == '_all') ? "EVERYONE" : $destino;
                            $cronos = (empty($row['cronos'])) ? '&nbsp;' : odbc2time_ampm($row['cronos']);
                            $cronos_syn = (empty($row['cronos_syn'])) ? '&nbsp;' : odbc2time_ampm($row['cronos_syn']);
                            ?>

                    <tr>
                        <td><?=++$i?></td>
                        <td>
                            <a href="<?=_SERVER_DIRIGER?>tools/common/download.php?file=<?= $file ?>&send_file=1">
                                <i class="fa <?=$row['action'] == 'export' ? "fa-download" : "fa-upload"?> fa-2x"></i>
                            </a>
                        </td>
                        <td>
                            <?php
                                    echo textparse($row['nombre']);
                                    if (!empty($row['cargo'])) textparse($row['cargo']); 
                                    ?>
                        </td>
                        <td>
                            <?php if ($row['tb_filter']) { ?>
                            <i class="fa fa-filter fa-2x" style="color:#00a7d0"></i>
                            <?php } ?>
                            <?= $origen ?>
                        </td>
                        <td>
                            <?= $destino ?>
                        </td>
                        <td>
                            <?= $cronos ?>
                        </td>
                        <td>
                            <?= $cronos_syn ?>
                        </td>
                        <td>
                            <?= textparse($row['observacion']) ?>
                            <?php if ($row['tb_filter']) ?>
                            <br />
                            <?= $row['tb_filter']?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>