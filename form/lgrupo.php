<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/class/connect.class.php";
require_once "../php/class/grupo.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';

if ($action == 'add') $action= 'edit';

if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tgrupo($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$result= $obj->listar();

require_once "../php/config.inc.php";

$url_page= "../form/lgrupo.php?signal=$signal&action=$action&menu=grupo&id_proceso=$id_proceso&year=$year";
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

    <title>LISTADO DE GRUPOS DE USUARIOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <link rel="stylesheet" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
    function add() {
        self.location.href = 'fgrupo.php?version=&action=add&signal=list';
    }

    function imprimir() {
        var url = '../print/lgrupo.php';

        confirm("Desea imprimir la relación (nombres, apellidos, cargo y Correo electrónico) de los integrantes de cada grupo?",
            function(ok) {
                if (ok) {
                    url += '?signal=user';
                    show_imprimir(url, "IMPRIMIENDO RELACIÓN DE GRUPOS DE USUARIOS",
                        "width=750,height=500,toolbar=no,location=no, scrollbars=yes");
                } else {
                    show_imprimir(url, "IMPRIMIENDO RELACIÓN DE GRUPOS DE USUARIOS",
                        "width=750,height=500,toolbar=no,location=no, scrollbars=yes");
                }
            });
    }

    function enviar_grupo(id, action) {
        function _this() {
            parent.app_menu_functions = false;
            document.forms[0].exect.value = action;
            document.forms[0].action = '../php/grupo.interface.php?menu=grupo&ajax_win=0&id=' + id;
            document.forms[0].submit();
        }

        var msg =
            "El grupo será eliminado. Eliminar el grupo podria generar incoherencia en las tareas que este tenga asignado. Desea continuar?";

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
    </script>

    <script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>   
                <a href="#" class="navd-header">
                    GRUPOS DE USUARIOS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">

                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nuevo grupo de usuarios">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/02_usuarios.htm#02_4.3')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>    
        </nav>
    </div>


    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value='' />
        <input type="hidden" name="menu" id="menu" value="grupo" />

        <div class="app-body container-fluid table onebar">
            <table id="table" class="table table-striped" 
                data-toggle="table" 
                data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th width="50">No.</th>
                        <?php if ($action != 'list') { ?>
                        <th width="250"></th>
                        <?php } ?>
                        <th>GRUPO</th>
                        <th>DESCRIPCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    while ($row = $clink->fetch_array($result)) {
                    ?>
                    <tr>
                        <td><?=++$i?></td>

                        <?php if ($action != 'list') { ?>
                        <td>
                            <a class="btn btn-warning btn-sm"
                                href="javascript:enviar_grupo(<?= $row['id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-danger btn-sm"
                                href="javascript:enviar_grupo(<?= $row['id'] ?>,'delete');">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>

                        </td>
                        <?php } ?>

                        <td>
                            <?= $row['nombre']; ?>
                        </td>
                        <td>
                            <?= nl2br($row['descripcion']); ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>
</body>

</html>