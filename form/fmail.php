<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();

$csfr_token='123abc';
require_once "../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../php/class/config.class.php";
require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/pop3/pop3.class.php";

$uid= $_GET['uid'];

$pop3= new POP3();
$maillink= $pop3->Connect();
if (!is_null($maillink)) $pop3->list_inbox();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>ACCESO AL BUZÓN DE CORREO ELECTRÓNICO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
    
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script language="javascript" type="text/javascript" charset="utf-8">
    var uid;
    var wmail;

    function refreshp() {
        self.location.href = 'fmail.php?uid=' + document.getElementById('uid').value;
    }

    function salir() {
        self.location.href = '../html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&';
    }

    function nuevo() {
        wmail = show_imprimir('fsendmail.php', null, "width=800,height=670,toolbar=no,location=no, scrollbars=yes");
    }

    function rowSelect(id) {
        uid = id;
        document.getElementById('e-body').src = 'ajax/readmail.ajax.php?uid=' + uid;
    }
    </script>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">ACCESO AL BUZÓN DE CORREO ELECTRÓNICO</div>
                <div class="card-body">

                    <?php if (is_null($maillink)) { ?>
                    <br /><br />
                    <div class="alert alert-danger">ERROR: El servidor de correo no responde. No se ha establecido la
                        conexión</div>
                    <?php } ?>

                    <form action='javascript:validar()' method=post enctype="multipart/form-data">
                        <input type="hidden" name="id" value="" <?= $_SESSION['id_usuario'] ?>" />
                        <input type="hidden" name="menu" value="email" />
                        <input type="hidden" name="uid" id="uid" value="0" />

                        <div id="toolbar" class="btn-group">
                            <button type="button" class="btn btn-default" onclick="refreshp()">
                                <i class="fa fa-refresh"></i>
                            </button>
                            <button type="button" class="btn btn-default" onclick="nuevo()">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-default" onclick="delete()">
                                <i class="fa fa-trash"></i>
                            </button>
                            <button type="button" class="btn btn-default" onclick="salir()">
                                <i class="fa fa-sign-out"></i>
                            </button>
                        </div>

                        <table id="table-matter" class="table table-hover table-striped" data-toggle="table"
                            data-toolbar="#toolbar" data-height="500" data-search="true" data-show-columns="true">
                            <thead>
                                <tr>
                                    <th data-field="id"></th>
                                    <th data-field="icons">De</th>
                                    <th data-field="nombre">Asunto</th>
                                    <th data-field="fecha">Fecha/Hora</th>
                                    <th data-field="responsable">Tamaño</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                   $i = 0;
                                   foreach ($pop3->array_emails as $email) {
                                       $id = $email['uid'];
                                       ++$i;
                                    ?>
                                <tr>
                                    <td>
                                        <i class="fa fa-mail"></i>
                                        <i class="fa fa-clipboard"></i>
                                    </td>
                                    <td onclick="rowSelect(<?= $id ?>)"><?= $email['from']; ?></td>
                                    <td onclick="rowSelect(<?= $id ?>)"><?= $email['subject']; ?></td>
                                    <td onclick="rowSelect(<?= $id ?>)"><?= $email['date'] ?></td>
                                    <td onclick="rowSelect(<?= $id ?>)"><?= $email['size'] ?></td>
                                </tr>
                                <?php  } ?>
                            </tbody>
                        </table>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.php')">Ayuda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>