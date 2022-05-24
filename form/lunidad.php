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
require_once "../php/class/proceso.class.php";
require_once "../php/class/unidad.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add') $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
}
else {
    $obj= new Tunidad($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['local_proceso_id'];

$obj->SetIdProceso($id_proceso);
$result= $obj->listar();

$url_page= "../form/lunidad.php?signal=$signal&action=$action&menu=unidad&id_proceso=$id_proceso";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE UNIDADES DE MEDIDAS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css" />

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
    function refreshp() {
        self.location.href = 'lunidad.php?action=<?=$action?>';
    }

    function add() {
        self.location.href = 'funidad.php?version=&action=add&signal=list';
    }

    function imprimir() {
        var url = '../print/lunidad.php';
        prnpage = window.open(url, "IMPRIMIENDO RELACIÓN DE UNIDADES DE MEDIDAS",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
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
                    UNIDADES DE MEDIDA
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO) { ?>
                        <li class="nav-item">
                            <a href="#" class="" onclick="add()" title="">
                                <i class="fa fa-plus"></i>
                                Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>
                                Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/manual.html#listas')">
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
        <input type="hidden" name="menu" id="menu" value="unidad" />

        <div class="app-body container-fluid table onebar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>No.</th>
                        <th>UNIDAD</th>
                        <th>DECIMALES</th>
                        <th>NOMBRE/DESCRIPCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0;
                        while ($row = $clink->fetch_array($result)) { ?>
                    <tr>
                        <td><?=++$i?></td>
                        <td>
                            <?php if ($action != 'list') { ?>
                            <a class="btn btn-warning btn-sm"
                                href="javascript:enviar(<?= $row['id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-danger btn-sm" href="javascript:enviar(<?= $row['id'] ?>,'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?=stripslashes($row['nombre'])?>
                        </td>
                        <td>
                            <?=$row['decimales']?>
                        </td>
                        <td>
                            <?=nl2br(stripslashes($row['descripcion']))?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
</body>

</html>