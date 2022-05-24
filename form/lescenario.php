<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";


$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') $action= 'edit';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
        if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
    }

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tescenario($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

if (!empty($_GET['id_proceso'])) 
    $id_proceso= $_GET['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];

$_SESSION['id_proceso']= $id_proceso;

if (!empty($_GET['id_escenario'])) 
    $id_escenario= $_GET['id_escenario'];
if (empty($id_escenario)) 
    $id_escenario= $_SESSION['id_escenario'];

$obj->SetYear(null);
$obj->SetIdProceso($id_proceso);
$result= $obj->listar();

unset($obj_prs);
$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$nombre_prs= $obj_prs->GetNombre();
$nombre_prs.= ", ".$Ttipo_proceso_array[$obj_prs->GetTipo()];

$inicio= $year - 10;
$fin= $year + 20;

$url_page= "../form/lescenario.php?signal=$signal&action=$action&menu=escenario&id_proceso=$id_proceso&year=$year&month=$month&day=$day";
$url_page.= "&exect=$action&id_escenario=$id_escenario";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>LISTADO DE ESCENARIOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" type="text/css" href="../css/general.css" />
    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
    img {
        cursor: pointer;
    }
    </style>

    <script type="text/javascript">
    function enviar(id, action) {
        var msg =
            "El escenario ser eliminado, y con el todo el diseo estratégico e indicadores asociados. Desea continuar?";

        function _this() {
            $('#exect').val(action);
            document.forms[0].action = '../php/escenario.interface.php?id=' + id + '&action=' + action;
            document.forms[0].submit();
        }

        if (action == 'delete') {
            confirm(msg, function(ok) {
                if (!ok) return;
                else _this();
            });
        } else {
            _this();
        }
    }

    var LeftRef = 310;
    var ToptRef = 7;
    </script>

    <script language="javascript" type="text/javascript">
    function refreshp() {
        var action = $('#exect').val();
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        self.location.href = 'lescenario.php?version=&action=' + action + '&id_proceso=' + id_proceso + '&year=' + year;
    }

    function imprimir() {
        var id_proceso = $('#proceso').val();
        var year = $('#year').val();

        var url = '../print/lescenario.php?id_proceso=' + id_proceso + '&year=' + year;
        show_imprimir(url, "IMPRIMIENDO ESCENARIOS", "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function add() {
        self.location.href = 'fescenario.php?version=&action=add&signal=list&id_proceso=' + $('#proceso').val();
    }

    function mapa(id_escenario, id_tablero) {
        self.location.href = '../html/mapaestrategico.php?signal=escenario&id_escenario=' + id_escenario + '&tablero=' +
            id_tablero;
    }
    </script>

    <script type="text/javascript">
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

    <?php
        $obj_prs= new Tproceso($clink);

        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $id_proceso_sup= $obj_prs->GetIdProceso_sup();
        $conectado= $obj_prs->GetConectado();

        $type= $obj_prs->GetTipo();
        $nombre= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];

        $edit= false;
        if ($_SESSION['nivel'] >= _ADMINISTRADOR && ($id_proceso == $_SESSION['local_proceso_id']
                    || (($id_proceso_sup == $_SESSION['local_proceso_id'] || empty($id_proceso_sup)) && $conectado == _NO_LOCAL)))
            $edit= true;

        $edit= ($action == 'edit') ? true : false;
        if ($edit && ($id_proceso != $_SESSION['local_proceso_id'] && $conectado != _NO_LOCAL))
            $edit= false;
        if ($edit && ($_SESSION['nivel'] < _SUPERUSUARIO && $_SESSION['id_usuario'] != $obj_prs->GetIdResponsable()))
            $edit= false;
        if ($edit && (($id_proceso_sup == $_SESSION['local_proceso_id'] || empty($id_proceso_sup)) && $conectado == _NO_LOCAL))
            $edit= true;
        if ($signal == 'objetivo_sup' && $_SESSION['nivel'] >= _SUPERUSUARIO)
            $edit= empty($id_proceso_sup) ? false : true;
        ?>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>               
                <a href="#" class="navd-header">
                    ESCENARIOS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nuevo escenario estratégico">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <?php
                        $use_select_year= true;
                        $id_select_prs= $id_proceso;
                        $restrict_prs = !$config->dpto_with_objetive ? array(_TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_GRUPO) : array(_TIPO_ARC, _TIPO_PROCESO_INTERNO) ;

                        require "inc/_dropdown_prs.inc.php";
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
                                <a href="#" onclick="open_help_window('../help/06_escenario.htm#06_8')">
                                    <i class="fa fa-question"></i>
                                    Ayuda
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
                    <?=$meses_array[(int)$month]?>, <?=$year?>
                </label>
            </li>

            <li class="col-auto">
                <div class="col-sm-12">
                    <label class="badge badge-danger">
                        <?php if (!empty($id_proceso) && $id_proceso != -1) { ?>
                        <?php if ($_connect_prs && $id_proceso != $_SESSION['local_proceso_id']) { ?>
                        <i class="fa fa-wifi"></i>
                        <?php } } ?>

                        <?=$nombre_prs?>
                    </label>
                </div>
            </li>
        </ul>
    </div>


    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value='<?=$action?>' />
        <input type="hidden" name="menu" id="menu" value="escenario" />
        <input type="hidden" name="year" id="year" value="<?=$year?>" />

        <div class="app-body container-fluid table twobar">
            <?php if ($edit) { ?>
            <div class="col-12" style="margin-top: 20px!important">
                <div class="alert red">
                    Para que los cambios tomen efecto deberá salir y autoenticarse nuevamente en el sistema.
                </div>
            </div>
            <?php } ?>

            <table id="table" class="table table-hover table-striped" data-toggle="table" data-search="false"
                data-show-columns="true">

                <tbody>
                    <?php while ($row= $clink->fetch_array($result)) { ?>
                    <tr>
                        <td>
                            <p>
                                <?php if ($edit) { ?>
                                <a class="btn btn-warning btn-sm" href="#"
                                    onclick="enviar(<?= $row['id'] ?>,'<?= $action ?>')">
                                    <i class="fa fa-edit"></i>Editar
                                </a>

                                <?php if ($action != 'list' && $row['id'] != $_SESSION['id_escenario']) { ?>
                                <a class="btn btn-danger btn-sm" href="#" onclick="enviar(<?= $row['id'] ?>,'delete')">
                                    <i class="fa fa-trash"></i>Eliminar
                                </a>
                                <?php } } ?>
                            </p>

                            <p>
                                <strong>PERIODO: </strong><?="{$row['inicio']} -  {$row['fin']}"?>
                            </p>
                            <p>
                                <strong>MISIÓN:</strong><br />
                                <?= nl2br(stripslashes($row['mision'])) ?>
                            </p>
                            <p>
                                <strong>VISIÓN:</strong><br />
                                <?= nl2br(stripslashes($row['vision'])) ?>
                            </p>
                            <p>
                                <strong>DESCRIPCIÓN:</strong><br />
                                <?= nl2br(stripslashes($row['descripcion'])) ?>
                            </p>
                        </td>

                        <td>
                            <div class="container-fluid">
                                <div class="col-md-4">
                                    <label>MAPA ESTRATÉGICO</label>
                                    <br />
                                    <?php if (!is_null($row['mapa'])) { ?>
                                    <img class="thumbnail img-fluid" id="img<?= $row['id'] ?>_1"
                                        src="../php/image.interface.php?menu=escenario&signal=strat&id=<?= $row['id'] ?>"
                                        onclick="mapa(<?=$row['id']?>, 1)" />
                                    <?php } ?>
                                </div>
                                <div class="col-md-4">
                                    <label>MAPA DE PROCESOS INTERNOS</label>
                                    <br />
                                    <?php if (!is_null($row['proc_mapa'])) {?>
                                    <img class="thumbnail img-fluid" id="img<?=$row['id']?>_2"
                                        src="../php/image.interface.php?menu=escenario&signal=proc&id=<?=$row['id']?>"
                                        onclick="mapa(<?=$row['id']?>, 2)" />
                                    <?php } ?>
                                </div>
                                <div class="col-md-4">
                                    <label>ORGANIGRAMA FUNCIONAL</label>
                                    <br />
                                    <?php if (!is_null($row['org_mapa'])) { ?>
                                    <img class="thumbnail img-fluid" id="img<?= $row['id'] ?>_3"
                                        src="../php/image.interface.php?menu=escenario&signal=org&id=<?= $row['id'] ?>"
                                        onclick="mapa(<?=$row['id']?>, 3)" />
                                    <?php } ?>
                                </div>
                            </div>

                        </td>
                    </tr>
                    <?php ++$i; } ?>

                </tbody>
            </table>
        </div>
    </form>
</body>

</html>