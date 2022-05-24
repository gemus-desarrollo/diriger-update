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
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/badger.class.php";


$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add')
    $action= 'edit';

$id_proceso= empty($_GET['id_proceso']) || $_GET['id_proceso'] == -1 ? $_SESSION['id_entity'] : $_GET['id_proceso'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($id_proceso != $_SESSION['id_entity'])
    $action= 'list';
if ($id_proceso == $_SESSION['id_entity'] && ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['acc_planwork'] == 3))
    $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso)) {
   $obj_prs->SetIdProceso($id_proceso);
   $obj_prs->Set();
   $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
   $conectado= $obj_prs->GetConectado();
   $tipo= $obj_prs->GetTipo();
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Ttipo_evento($clink);
}

$obj->SetYear($year);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= date('Y');
$inicio= $year - 5;
$fin= $year + 5;

$year= !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
if (empty($year))
    $year= date('Y');

$url_page= "../form/ltipo_evento.php?signal=$signal&action=$action&menu=tipo_evento&year=$year&id_proceso=$_id_proceso";
set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE TIPOS DE ACTIVIDADES</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css" />

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
    body {
        background: white;
    }
    ul {
        list-style: none;
        text-align: left;
    }
    li {
        list-style: none;
        text-align: left;
    }
    .img-thumbnail {
        cursor: pointer;
        margin: 0px 2px 2px 4px;
    }
    a {
        text-underline: none;
        text-decoration: none;
        text-underline-style: none;
        background: node;
        border: none;
    }
    .app-body {
        padding-bottom: 30px;
    }
    .label-year {
        margin: 3px 8px 3px 8px;
    }
    </style>

    <script language="javascript">
    function refreshp() {
        var id_proceso = $('#proceso').val();
        self.location.href = 'ltipo_evento.php?action=<?=$action?>&year=' + $('#year').val() + '&id_proceso=' +
            id_proceso;
    }

    function add(empresarial, id_subcapitulo, numero) {
        var id_proceso = $('#proceso').val();

        if (id_subcapitulo && numero == 0) {
            alert("Este sub-capítulo aun no tiene un número asignado. Antes deberá editarlo y asignarle un número.");
            return;
        }

        var url = 'ftipo_evento.php?version=&action=add&signal=list&year='+$('#year').val();
        url += '&empresarial=' + empresarial + '&id_subcapitulo=' + id_subcapitulo + '&id_proceso=' + id_proceso;

        self.location.href = url;
    }

    function imprimir() {
        var id_proceso = $('#proceso').val();
        var url = '../print/ltipo_evento.php?year=' + $('#year').val() + '&id_proceso=' + id_proceso
        prnpage = window.open(url, "IMPRIMIENDO RELACIÓN DE TIPOS DE ACTIVIDADES",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp();
    }

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
                    TIPO DE ACTIVIDADES EMPRESARIALES
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <!--
                            <?php if ($action == 'add' || $action == 'edit') { ?>
                            <li>
                                <a href="#" class="" onclick="add()" title="nuevo tipo de actividad">
                                    <i class="fa fa-plus"></i>Agregar
                                </a>
                            </li>
                        <?php } ?>
                        -->

                        <?php
                        $show_only_connected= false;
                        $reject_connected= true;
                        $id_select_prs= $id_proceso;
                        require "../form/inc/_dropdown_prs.inc.php";
                        ?>

                        <?php
                        $use_select_year= true;
                        $use_select_month= false;
                        $use_select_day= false;
                        require "../form/inc/_dropdown_date.inc.php";
                        ?>

                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
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

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=$year?>
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

    <form action='javascript:' method=post class="intable">
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="tipo_evento" />

        <div class="app-body container-fluid twobar">
            <div>
                <ul>
                    <?php
                    $nshow= 0;
                    $nhide= 0;

                    $obj->SetYear($year);
                    $obj->SetIdProceso($id_proceso);

                    for ($i = 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) {
                    ?>
                    <li>
                        <br />
                        <strong style="font-size: 1.3em;">
                            <?= number_format_to_roman($i - 1) ?>
                            .&nbsp;

                            <?php if (($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['acc_planwork'] == 3) && $action == 'edit') { ?>
                            <a class="btn btn-primary btn-sm" href="#" onclick="add(<?= $i ?>,0,0)">
                                <i class="fa fa-plus" title="nuevo tipo de actividad"></i>Agregar
                            </a>
                            &nbsp;
                            <?php } ?>
                            <?= $tipo_actividad_array[$i] ?>
                        </strong>

                        <?php
                            $result = $obj->listar($i, 0);
                            $cant = $obj->GetCantidad();
                            if (!empty($cant)) {
                            ?>
                        <br />
                        <ul>
                            <?php
                            while ($row = $clink->fetch_array($result)) {
                                if (!empty($year) && (!empty($row['year']) && $row['year'] < $year)) {
                                    ++$nhide;
                                    continue;
                                }
                                ++$nshow;
                            ?>
                            <br />
                            <li>
                                <strong><?= $row['numero'] ?></strong>
                                &nbsp;

                                <?php if (!empty($row['inicio'])) { ?>
                                <span class="year-label">
                                    (<?="{$row['inicio']} - {$row['fin']}"?>)
                                </span>
                                <?php } ?>

                                <a name="<?= $row['id'] ?>"></a>

                                <?php if (($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['acc_planwork'] == 3) && $action == 'edit') { ?>
                                <a class="btn btn-primary btn-sm" href="#"
                                    onclick="add(<?= $i ?>, <?= $row['id'] ?>, <?= !empty($row['numero']) ? $row['numero'] : 0 ?>)">
                                    <i class="fa fa-plus" title="nueva tipo de actividad"></i>Agregar
                                </a>

                                <a class="btn btn-warning btn-sm"
                                    href="javascript:enviar(<?= $row['id'] ?>,'<?= $action ?>');">
                                    <i class="fa fa-edit"></i>Editar
                                </a>

                                <a class="btn btn-danger btn-sm" href="#" onclick="enviar(<?= $row['id'] ?>,'delete')">
                                    <i class="fa fa-trash"></i>Eliminar
                                </a>
                                <?php } ?>

                                <?= textparse($row['nombre']) ?>
                                &nbsp;
                                <?= textparse($row['descripcion']) ?>

                                <?php
                                $result_sub = $obj->listar($i, $row['id']);
                                $cant = $obj->GetCantidad();

                                if (!empty($cant)) {
                                ?>
                                <ul>
                                    <?php
                                    while ($row_sub = $clink->fetch_array($result_sub)) {
                                        if (!empty($year) && (!empty($row_sub['year']) && $row_sub['year'] < $year)) {
                                            ++$nhide;
                                            continue;
                                        }
                                        ++$nshow;
                                    ?>
                                    <br />
                                    <li>
                                        <strong><?= $row_sub['numero'] ?></strong>
                                        &nbsp;

                                        <?php if (!empty($row_sub['year'])) { ?>
                                        <span class="year-label">
                                            (<?=$row_sub['year']?>)
                                        </span>
                                        <?php } ?>

                                        <a class="btn btn-warning btn-sm"
                                            href="javascript:enviar(<?= $row_sub['id'] ?>,'<?= $action ?>');">
                                            <i class="fa fa-edit"></i>Editar
                                        </a>

                                        <a name="<?= $row_sub['id'] ?>"></a>

                                        <?php if ($action != 'list') { ?>
                                        <a class="btn btn-danger btn-sm" href="#"
                                            onclick="enviar(<?= $row_sub['id'] ?>,'delete')">
                                            <i class="fa fa-trash"></i>Eliminar
                                        </a>
                                        <?php } ?>
                                        &nbsp;
                                        <?= textparse($row_sub['nombre']) ?>
                                        &nbsp;
                                        <?= textparse($row_sub['descripcion']) ?>
                                    </li>
                                    <?php } ?>
                                </ul>
                                <?php } ?>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>


        <script type="text/javascript" language="JavaScript">
        document.getElementById('nshow').innerHTML = '<?=$nshow?>';
        document.getElementById('nhide').innerHTML = '<?=$nhide?>';
        </script>

    </form>
</body>

</html>