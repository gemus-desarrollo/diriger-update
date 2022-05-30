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
require_once "../php/class/tablero.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';

if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Ttablero($clink);
}

$id_integral= $obj->GetIdIntegral();

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$result= $obj->listar();

$url_page= "../form/ltablero.php?signal=$signal&action=$action&menu=tablero&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE TABLEROS </title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css" />

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/menu.css" />
    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
    function add() {
        self.location.href = 'ftablero.php?version=&action=add&signal=list';
    }

    function imprimir() {
        var url = '../print/ltablero.php';
        show_imprimir(url, "IMPRIMIENDO RELACIÓN DE TABLEROS DE CONTROL",
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
                    TABLEROS DE CONTROL
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO) { ?>
                        <li class="d-none d-md-block d-none d-lg-block ">
                            <a href="#" class="" onclick="add()" title="nuevo tablero de indicadores">
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
                                <a href="#" onclick="open_help_window('../help/11_indicadores.htm#11_16.1')">
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
        <input type="hidden" name="menu" id="menu" value="tablero" />

        <div class="app-body container-fluid table onebar">
            <table id="table" class="table table-hover table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th></th>
                        <th>TABLERO</th>
                        <th>PROPÓSITO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    while ($row = $clink->fetch_array($result)) { 
                    ?>
                    <tr>
                        <td>
                            <?=++$i?>
                            <a name="<?= $row['id'] ?>"></a>
                        </td>
                        <td>
                            <a class="btn btn-warning btn-sm"
                                href="javascript:enviarTablero(<?= $row['id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <?php if ($action != 'list' && $row['id'] != $id_integral) { ?>
                            <a class="btn btn-danger btn-sm"
                                href="javascript:enviarTablero(<?= $row['id'] ?>,'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } ?>
                        </td>
                        <td>
                            <?=stripslashes($row['nombre'])?>
                        </td>
                        <td>
                            <?=nl2br(stripslashes($row['descripcion']))?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>
</body>

</html>