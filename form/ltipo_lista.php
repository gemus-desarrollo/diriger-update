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
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_lista.class.php";

require_once "../php/class/badger.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add' || $action == 'update')
    $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$obj_prs= new Tproceso($clink);

$id_proceso= empty($_GET['id_proceso']) || $_GET['id_proceso'] == -1 ? $_SESSION['id_entity'] : $_GET['id_proceso'];

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
}
else {
    $obj= new Ttipo_lista($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();
if (empty($id_lista))
    $id_lista= 0;

$year= date('Y');
$inicio= $year - 5;
$fin= $year + 5;

$year= date('Y');
$inicio= $year - 5;
$fin= $year + 5;

$year= !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
if (empty($year))
    $year= date('Y');

$obj->SetYear($year);

$obj_lista= new Tlista($clink);
$obj_lista->SetIdLista($id_lista);
$obj_lista->Set();
$nombre_lista= $obj_lista->GetNombre();
$min_year= $obj_lista->GetInicio();
$max_year= $obj_lista->GetFin();

// determinar si el usuario es jefe
unset($obj_prs);
$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
$array_chief_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);

$if_jefe= false;
$acc= $_SESSION['acc_planaudit'];
if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
    $if_jefe= true;
if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe= true;
if ($acc == _ACCESO_BAJA && ($id_proceso == $_SESSION['usuario_proceso_id'] && $id_proceso != $_SESSION['id_entity']))
    $if_jefe= true;
// if ($acc == _ACCESO_MEDIA && ($id_proceso == $_SESSION['local_proceso_id'])) $if_jefe= true;

$url_page= "../form/ltipo_lista.php?signal=$signal&action=$action&menu=tipo_lista&year=$year";
$url_page.= "&id_proceso=$id_proceso&id_lista=$id_lista&if_jefe=$if_jefe";
add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>CLASIFICACIÓN DE REQUISITOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <link rel="stylesheet" type="text/css" href="../css/menu.css" />
    <script type="text/javascript" src="../js/form.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/lista.css" />

    <script language="javascript">
    function refreshp() {
        var id_proceso = $('#proceso').val();
        var id_lista = $('#id_lista').val();
        var signal = $("#signal").val();

        var url = 'ltipo_lista.php?action=<?=$action?>&year=' + $('#year').val() + '&id_proceso=' + id_proceso;
        url += '&id_lista=' + id_lista + '&signal=' + signal;

        self.location.href = url;
    }

    function add(componente, id_capitulo, numero) {
        if (id_capitulo && numero == 0) {
            alert("Este capítulo aun no tiene un número asignado. Antes deberá editarlo y asignarle un número.");
            return;
        }

        var id_lista = $('#id_lista').val();
        var id_proceso = $('#id_proceso').val();
        var signal = $("#signal").val();
        var year = $("#year").val();

        var url = 'ftipo_lista.php?version=&action=add&signal=list';
        url += '&componente=' + componente + '&id_capitulo=' + id_capitulo + '&id_lista=' + id_lista;
        url += '&id_proceso=' + id_proceso + '&signal=' + signal + '&year=' + year;
        url += '&min_year=<?=$min_year?>&max_year=<?=$max_year?>';

        self.location.href = url;
    }

    function _delete(id) {
        var url = "../php/interface.php?action=delete&menu=tipo_lista&id=" + id + '';
        self.location.href = url;
    }

    function edit(id) {
        var url = "../php/interface.php?action=edit&menu=tipo_lista&id=" + id + '';
        self.location.href = url;
    }

    function imprimir() {
        var id_lista = $('#id_lista').val();
        var id_proceso = $('#id_proceso').val();

        var url = '../print/ltipo_lista.php?id_lista=' + id_lista + '&id_proceso=' + id_proceso;
        prnpage = window.open(url, "IMPRIMIENDO ESTRUCTURA DE GUIA DE CONTROL",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function closep() {
        var signal = $("#signal").val();

        var url = 'llista.php?action=<?=$action?>&id_proceso=<?=$id_proceso?>&year=<?=$year?>';
        url += '&signal=' + signal;

        self.location.href = url;
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

    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>

                <a href="#" class="navd-header">
                    ESTRUCTURA DE LA GUÍA DE CONTROL
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item align-content-center mt-3">
                            <div class="badge badge-warning">
                                <?= textparse($nombre_lista)?>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="" onclick="imprimir()">
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
                            <li class="nav-item">
                                <a href="#" onclick="self.location.href = '<?php prev_page() ?>'">
                                    <i class="fa fa-close"></i>Cerrar
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

    <form action='javascript:' method=post class="form-horizontal">
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="tipo_lista" />
        <input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />

        <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />
        <input type="hidden" name="id_lista" id="id_lista" value="<?= $id_lista ?>" />
        <input type="hidden" name="year" id="year" value="<?= $year ?>" />

        <div class="app-body container-fluid twobar">
            <ul>
                <?php
                $nshow= 0;
                $nhide= 0;

                $obj->SetIdLista($id_lista);
                $obj->SetYear($year);
                $obj->SetIdProceso($id_proceso);

                for ($i = 1; $i < _MAX_COMPONENTES_CI; ++$i) {
                ?>
                <li>
                    <br />
                    <strong style="font-size: 1.3em;">
                        <?= number_format_to_roman($i) ?>
                        .&nbsp;

                        <?php if ($if_jefe && $action == 'edit') { ?>
                        <a class="btn btn-primary btn-sm" href="#" onclick="add(<?= $i ?>,0,0)">
                            <i class="fa fa-plus" title="nueva clasificacion de requisitos"></i>Agregar
                        </a>
                        &nbsp;
                        <?php } ?>
                        <?= $Tambiente_control_array[$i] ?>
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
                            <a name="<?= $row['id'] ?>"></a>

                            <span class="year-label">
                                (<?="{$row['inicio']} - {$row['fin']}"?>)
                            </span>

                            <?php if ($if_jefe && $action == 'edit') { ?>
                            <a class="btn btn-primary btn-sm" href="#"
                                onclick="add(<?= $i ?>, <?= $row['id'] ?>, <?= !empty($row['numero']) ? $row['numero'] : 0 ?>)">
                                <i class="fa fa-plus" title="nueva clsificación de requisitos"></i>
                                Agregar
                            </a>

                            <a class="btn btn-warning btn-sm" href="#"
                                onclick="edit(<?= $row['id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>

                            <?php if ($action != 'list') { ?>
                            <a class="btn btn-danger btn-sm" href="#"
                                onclick="_delete(<?= $row['id'] ?>)">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } ?>
                            <?php } ?>

                            <?= $row['nombre'] ?>
                            &nbsp;
                            <?= $row['descripcion'] ?>

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

                                    <span class="year-label">
                                        (<?="{$row_sub['inicio']} - {$row_sub['fin']}"?>)
                                    </span>

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


        <script type="text/javascript" language="JavaScript">
        document.getElementById('nshow').innerHTML = '<?=$nshow?>';
        document.getElementById('nhide').innerHTML = '<?=$nhide?>';
        </script>

    </form>
</body>

</html>