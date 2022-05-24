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
require_once "../php/class/usuario.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/tablero.class.php";
require_once "../php/class/cell.class.php";
require_once "../php/class/cell_list.class.php";

require_once "../php/inc_escenario_init.php";

$signal= 'resumen';

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : $_SESSION['id_tablero'];
if (empty($id_tablero)) 
    $id_tablero= 0;

$_SESSION['tablero']= $id_tablero;

$obj_cell= new Tcell_list($clink);
$obj_cell->SetIdProceso($id_proceso);
$obj_cell->SetYear($year);
$obj_cell->SetDay($day);
$obj_cell->SetMonth($month);

$obj_tab= new Ttablero($clink);
$obj_tab->SetIdUsuario($_SESSION['id_usuario']);
$obj_tab->SetIdEntity($_SESSION['id_entity']);
$use_perspectiva= null;

$obj_user= new Tusuario($clink);
$obj_ind= new Tindicador($clink);

$is_not_for_print= true;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="refresh" content="<?=(int)$config->delay*60?>" />
    <title>RESUMEN DE INDICADORES</title>

    <?php require_once "inc/_tablero_top.inc.php" ?>

    <link rel="stylesheet" type="text/css" media="screen"
        href="../css/table_resumen.css?version=">

    <style type="text/css">
    body {
        background: none;
    }

    .app-body.container-fluid.threebar,
    .app-body.container.threebar,
    .app-body.content.threebar {
        overflow: scroll !important;
        margin-top: 95px !important;
    }

    #table-res td.header,
    td.header {
        text-align: center;
        align-content: center;
        padding: 3px;
    }
    </style>
</head>

<body onload="setInterval('blinkIt()',500)">

    <!-- Docs master nav -->
    <div id="navbar-secondary" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <a href="#" class="navd-header">
            TABLERO DE INDICADORES
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">

                    <li class="navd-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-industry"></i>Tableros Definidos<b class="caret"></b>
                        </a>

                        <ul class="dropdown-menu">
                            <?php
                            $j = 0;
                            $pos = 0;
                            $_id_tablero = null;
                            $_use_perspectiva = null;

                            $array_tableros = $obj_tab->get_procesos_by_user();

                            if (!is_null($array_tableros)) {
                                foreach ($array_tableros as $array) {
                                    if ($j == 0) {
                                        $_id_tablero = $array['id'];
                                        $tablero= $array['nombre'];
                                        $_use_perspectiva = $array['use_perspectiva'];
                                    }
                                    if (empty($id_tablero)) {
                                        $id_tablero = $array['id'];
                                        $tablero= $array['nombre'];
                                    }

                                    ++$j;
                                    if ($id_tablero == $array['id']) {
                                        $tablero= $array['nombre'];
                                        $use_perspectiva = $array['use_perspectiva'];
                                    }
                                    $str = !is_null($array['descripcion']) ? str_replace("\r", "", str_replace("\n", "", nl2br($array['descripcion']))) : null;
                            ?>
                            <li class="<?php if ($array['id'] == $id_tablero) echo "active" ?>">
                                <a class="tooltip-viewport-left" href="#" title="<?= $str ?>"
                                    onclick="loadtablero(<?= $array['id'] ?>)"><?= $array['nombre'] ?></a>
                            </li>
                            <?php
                            }

                            if (!is_array($array_tableros))
                                $id_tablero = 0;
                            if (is_array($array_tableros) && $pos == 0) {
                                $id_tablero = $_id_tablero;
                                $use_perspectiva = $_use_perspectiva;
                            }

                            $_SESSION['id_tablero'] = $id_tablero;
                        }
                        ?>
                        </ul>

                        <input type="hidden" id="tablero" name="tablero" value="<?=$id_tablero?>" />
                    </li>

                    <?php
                    $use_select_year= true;
                    $use_select_month= false;
                    $use_select_day= false;
                    require "../form/inc/_dropdown_date.inc.php";
                    ?>

                    <?php if (!empty($id_tablero)) { ?>
                    <li class="d-none d-lg-block">
                        <a href="#" class="" onclick="imprimir()">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>
                    <?php } ?>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li>
                            <a href="#" onclick="open_help_window('../help/11_indicadores.htm#11_16.2')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                    </ul>    
                </div>
            </div>

            <div id="navbar-third" class="row app-nav d-none d-md-block">
                <nav class="navd-content">
                    <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
                        <li class="col-3">
                            <label class="badge badge-success">
                                <?=(int)$day?> de <?=$meses_array[(int)$month]?>, <?=$year?>
                            </label>
                        </li>
                        <li class="col-auto">
                            <label class="label"><span style="margin-left: 20px;">Tablero:</span></label>
                            <label class="badge badge-danger">
                                <?=$tablero?>
                            </label>
                        </li>
                    </ul>
                </nav>
            </div>    
        </div>
    </nav>

    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="tablero" />
        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />

        <input type="hidden" id="id_indicador" name="id_indicador" value="0" />
        <input type="hidden" id="id_persp" name="id_persp" value="0" />
        <input type="hidden" id="id_user_real" name="id_user_real" value="0" />
        <input type="hidden" id="id_user_plan" name="id_user_plan" value="0" />
        <input type="hidden" id="trend" name="trend" value="0" />

        <input type="hidden" id="cumulative" name="cumulative" value="" />
        <input type="hidden" id="formulated" name="formulated" value="" />

        <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />
        <input type="hidden" id="id_usuario" name="id_usuario" value="<?=$_SESSION['id_usuario'] ?>" />
        <input type="hidden" id="nivel" name="nivel" value="<?=$_SESSION['nivel']?>" />

        <input id="tablero" type="hidden" value="<?=$id_tablero?>" />
        <input id="actual_year" type="hidden" value="<?=$actual_year?>" />

        <!-- app-body -->
        <div id="app-body" class="app-body container-fluid tenbar">
            <?php
            if (!empty($id_tablero) && !is_null($array_tableros)) {

                if ($use_perspectiva) {
                    $obj_pers= new Tperspectiva($clink);
                    $obj_pers->SetYear($year);
                    $result_persp= $obj_pers->listar();

                    if (isset($obj_indi)) 
                        unset($obj_indi);
                    $obj_indi= new Tindicador($clink);

                    $i= 0;
                    $j= 0;
                    while ($row_perspectiva= $clink->fetch_array($result_persp)) {
                        $id_perspectiva= $row_perspectiva['_id'];
                        $perspectiva= $row_perspectiva['nombre'];
                        $color= '#'.$row_perspectiva['color'];

                        $obj_tab->SetYear($year);
                        $obj_tab->SetIdTablero($id_tablero);
                        $obj_tab->SetIdPerspectiva($id_perspectiva);
                        $obj_tab->SetIdProceso(null);

                        $result_indi= $obj_tab->listar_indicadores(true, true);
                        $cantidad= $obj_tab->GetCantidad();

                        if ($cantidad == 0) 
                            continue;
                    ?>
            <div class="label label-default" style="background-color:<?=$color?>; text-align:left;">
                <strong>PERSPECTIVA:</strong>
                <?=$row_perspectiva['nombre']?> / <span class="text-persp"> <?=$nombre_prs?></span>
            </div>
            <?php
                    include "../print/inc/_table_resumen.inc.php";
                }
            ?>

            <br />
            <?php
                $id_perspectiva= null;
                $obj_tab->SetIdTablero($id_tablero);
                $obj_tab->SetIdPerspectiva(null);
                $obj_tab->SetIdProceso(null);
                $obj_tab->SetYear($year);

                $result_indi= $obj_tab->listar_indicadores(true, false);
                $cantidad= $obj_tab->GetCantidad();

                if ($cantidad > 0) {
                    include "../print/inc/_table_resumen.inc.php";
                }
            }
            ?>
            <br />

            <?php
            if (!$use_perspectiva) {
                $obj_tab->SetYear($year);
                $obj_tab->SetIdTablero($id_tablero);
                $obj_tab->SetIdPerspectiva(null);
                $obj_tab->SetIdProceso(null);

                $result_indi= $obj_tab->listar_indicadores(null, false);
                $cantidad= $obj_tab->GetCantidad();

                if ($cantidad > 0) {
                    include "../print/inc/_table_resumen.inc.php";
                }
            ?>

            <?php }  } ?>

            <?php if (empty($id_tablero) || is_null($array_tableros)) { ?>
            <?php if ($id_proceso == $_SESSION['local_proceso_id'] || $conectado == _NO_LOCAL) { ?>
            <div class="alert alert-danger">
                USTED NO TIENE ACCESO A NINGÚN TABLERO. DEBE CONSULTAR AL ADMINISTRADOR DEL SISTEMA.
            </div>
            <?php } } ?>

            <?php if (empty($cant_indi)) { ?>
            <?php if ($id_proceso != $_SESSION['local_proceso_id'] && $conectado != _NO_LOCAL) { ?>
            <div class="alert alert-danger">
                AL PARECER NO SE HAN RECIBIDO INFORMACIÓN DE INDICADORES DESDE ESTA LOCALIZACIÓN. DEBE CONSULTAR AL
                ADMINISTRADOR DEL SISTEMA.
            </div>
            <?php } ?>
            <?php } ?>
        </div>
    </form>

</body>

</html>