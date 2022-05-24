<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
require_once "../php/config.inc.php";

$_SESSION['debug']= 'no';

require_once "../php/class/connect.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';

if ($action == 'add') 
    $action= 'edit';

if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Ttipo_reunion($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$obj->SetIdProceso($id_proceso);
$result= $obj->listar();

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso)) {
   $obj_prs->SetIdProceso($id_proceso);
   $obj_prs->Set();
   $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
   $conectado= $obj_prs->GetConectado();
   $tipo= $obj_prs->GetTipo();
}

$url_page= "../form/ltipo_reunion.php?signal=$signal&action=$action&menu=tipo_reunion&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>ÓRGANOS, COMISIONES O GRUPOS QUE SE REUNEN</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

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
    function refreshp() {
        var id_proceso = $('#proceso').val();
        self.location.href = 'ltipo_reunion.php?action=<?=$action?>&id_proceso=' + id_proceso;
    }

    function add() {
        self.location.href = 'ftipo_reunion.php?version=&action=add&signal=list';
    }

    function imprimir() {
        var url = '../print/ltipo_reunion.php?id_proceso=' + $('#id_proceso').val();
        prnpage = window.open(url, "IMPRIMIENDO TIPO DE REUNIONES",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");        
    }

    function enviar(id, action) {
        function _this() {
            parent.app_menu_functions = false;
            document.forms[0].exect.value = action;
            document.forms[0].action = '../php/tipo.interface.php?menu=tipo_reunion&ajax_win=0&id=' + id;
            document.forms[0].submit();
        }

        var text =
        "El tipo de acción de control será eliminado. Eliminar este tipo de accion de conrol podría generar ";
        text += "incoherencia en las tareas que este tenga asignado. Desea continuar?";

        if (action == 'delete') {
            confirm(text, function(ok) {
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
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }
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
                    CLASIFICACIÓN DE LAS REUNIONES
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nuevo Tipo de Reunión">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <?php
                        $show_only_connected= false;
                        $reject_connected= true;
                        $id_select_prs= $id_proceso;
                        require "../form/inc/_dropdown_prs.inc.php";
                        ?>
                        <!--
                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                        -->
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

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=date('Y')?>
                </label>
            </li>
            <li class="col">
                <div class="row">
                    <label class="label ml-3">Muestra:</label>
                    <div id="nshow" class="badge badge-warning">0</div>
                </div>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Ocultos:</label>
                    <div id="nhide" class="badge badge-warning">0</div>
                </div>
            </li>

            <li class="col">
                <div class="col-sm-12">
                    <label class="badge badge-danger">
                        <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                            class="fa fa-wifi"></i><?php } ?>
                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>

    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value='' />
        <input type="hidden" name="menu" id="menu" value="grupo" />

        <div class="app-body container-fluid table twobar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <?php if ($action != 'list') { ?>
                        <th></th>
                        <?php } ?>
                        <th>REUNIÓN</th>
                        <th>DESCRIPCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    while ($row = $clink->fetch_array($result)) {
                        ++$i;
                    ?>
                    <tr>
                        <td><?=$row['numero']?></td>

                        <td>
                        <?php if ($action != 'list' && $i != 1) { ?>
                            <a class="btn btn-warning btn-sm"
                                href="javascript:enviar(<?= $row['id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <a class="btn btn-danger btn-sm" href="javascript:enviar(<?= $row['id'] ?>,'delete');">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                        <?php } ?>    
                        </td>
                        

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