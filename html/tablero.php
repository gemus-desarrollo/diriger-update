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
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/perspectiva.class.php";
require_once "../php/class/tablero.class.php";
require_once "../php/class/cell.class.php";

require_once "../php/class/peso_calculo.class.php";
require_once "../php/class/unidad.class.php";

$_SESSION['debug']= 'no';

$signal= "tablero";
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add')
    $action= 'edit';

$force_user_process= false;
$id_proceso= $_SESSION['id_entity'];

require_once "../php/inc_escenario_init.php";

$fin= $actual_year;

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : $_SESSION['id_tablero'];
$_SESSION['tablero']= $id_tablero;

$recompute= !is_null($_GET['recompute']) ? $_GET['recompute'] : 0;

$obj= new Tcell($clink);
$obj->recompute= (int)$recompute;

$obj_tab= new Ttablero($clink);
$obj_tab->SetIdUsuario($_SESSION['id_usuario']);

$obj_cal= new Tcalculator($clink);
$obj_user= new Tusuario($clink);

$time->SetYear($year);
$time->SetMonth($month);
$longmonth= (int)$time->longmonth();
$day= (int)$day > $longmonth ? $longmonth : (int)$day;
$time->SetDay($day);
$iday= $time->weekDay();

$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetDay($day);

$array_criterio= array(null, '&ge;','&le;','[]');
$use_perspectiva= null;
$array_tableros= null;

$obj_unidad= new Tunidad($clink);
$obj_indi= new Tindicador($clink);
$obj_indi->SetYear($year);

$obj_tab= new Ttablero($clink);

if (!empty($id_tablero)) {
    $obj_tab->SetIdTablero($id_tablero);
    $obj_tab->Set();
    $tablero= $obj_tab->GetNombre();
    $use_perspectiva= $obj_tab->use_perspectiva;
}

$url_page= "../html/tablero.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_tablero=$id_tablero+&recompute=$recompute";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <?php 
    if (!$auto_refresh_stop && (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no')) { 
        $delay= (int)$config->delay*60;
        header("Refresh: $delay; url=$url_page&csfr_token=123abc"); 
    } 
    ?>

    <title>TABLERO DE CONTROL</title>

    <?php require_once "inc/_tablero_top.inc.php" ?>

</head>

<body onload="setInterval('blinkIt()',500)">

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>              
                <a href="#" class="navd-header">
                    TABLERO DE INDICADORES
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navd-collapse">

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarTableros" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-dashboard"></i>Tableros Definidos<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarTableros">
                                <?php
                                $j = 0;
                                unset($obj_tab);
                                $obj_tab= new Ttablero($clink);
                                $obj_tab->SetIdUsuario($_SESSION['id_usuario']);
                                $array_tableros = $obj_tab->get_procesos_by_user();

                                if (!is_null($array_tableros)) {
                                    foreach ($array_tableros as $array) {
                                        if (empty($id_tablero)) {
                                            $id_tablero = $array['id'];
                                            $tablero= $array['nombre'];
                                            $use_perspectiva = $array['use_perspectiva'] ? true : null;
                                        }

                                        ++$j;
                                        if ($id_tablero == $array['id']) {
                                            $tablero= $array['nombre'];
                                            $use_perspectiva = $array['use_perspectiva'] ? true : null;
                                        }
                                        $str = !is_null($array['descripcion']) ? str_replace("\r", "", str_replace("\n", "", nl2br($array['descripcion']))) : null;
                                        ?>
                                        <li class="nav-item" <?php if ($array['id'] == $id_tablero) echo "active" ?>">
                                            <a class="" href="#" title="<?= $str ?>"
                                                onclick="_dropdown_tablero(<?= $array['id'] ?>)"><?= $array['nombre'] ?></a>
                                        </li>
                                    <?php
                                    }

                                    if (!is_array($array_tableros))
                                        $id_tablero = 0;
                                    $_SESSION['id_tablero'] = $id_tablero;
                                }
                                ?>
                            </ul>

                            <input type="hidden" id="tablero" name="tablero" value="<?=$id_tablero?>" />
                        </li>

                        <li class="nav-item">
                            <a href="#" class="" onclick="recompute()">
                                <i class="fa fa-recycle"></i>Recalcular
                            </a>
                        </li>

                        <?php
                        $use_select_year= true;
                        $use_select_month= true;
                        $use_select_day= true;
                        require "../form/inc/_dropdown_date.inc.php";
                        ?>

                        <?php if (!empty($id_tablero)) { ?>
                        <li class="nav-item d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                        <?php } ?>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/11_indicadores.htm#11_16.2')">
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
                    <?=(int)$day?> de <?=$meses_array[(int)$month]?>, <?=$year?>
                </label>
            </li>

            <li class="col-auto">
                <div class="row">
                    <label class="label ml-3">Tablero:</label>
                    <label id="label_tablero" class="badge badge-danger">
                        <?=$tablero?>
                    </label>
                </div>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Muestra:</label>
                    <div id="nhide" class="badge badge-warning"></div>
                </div>
            </li>
        </ul>
    </div>


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

        <input type="hidden" id="id_usuario" name="id_usuario" value="<?=$_SESSION['id_usuario'] ?>" />
        <input type="hidden" id="nivel" name="nivel" value="<?=$_SESSION['nivel']?>" />

        <input id=tablero type="hidden" value="<?=$id_tablero?>" />
        <input id=actual_year type="hidden" value="<?=$actual_year?>" />

        <input type="hidden" id="id_proceso" name="id_proceso" value="0" />
        <input type="hidden" id="proceso" name="proceso" value="0" />

        <input type="hidden" id="id" name="id" value="0" />
        <input type="hidden" id="id_proyecto" name="id_proyecto" value="0" />
        <input type="hidden" id="id_evento" name="id_evento" value="0" />
        <input type="hidden" id="id_auditoria" name="id_auditoria" value="0" />
        <input type="hidden" id="id_riesgo" name="id_riesgo" value="0" />
        <input type="hidden" id="id_nota" name="id_nota" value="0" />
        <input type="hidden" id="id_politica" name="id_politica" value="0" />

        <input type="hidden" id="recompute" name="recompute" value="0" />

        <input type="hidden" id="if_entity" value="" />

        <!-- app-body -->
        <div class="app-body container-fluid threebar">
            <br />            
            <?php
            $cant_indi= 0;
            $obj_prs= new Tproceso($clink);

            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetYear((int)$year);
            $obj_peso->SetMonth((int)$month);
            $obj_peso->SetDay((int)$day);
            $obj_peso->set_matrix();

            if (!empty($id_tablero) && !is_null($array_tableros)) {
                $k= 0;
                $array_indicadores= array();

                if ($use_perspectiva) {
                    $obj_persp = new Tperspectiva($clink);
                    $obj_persp->SetIdProceso(null);
                    $obj_persp->SetYear($year);
                    $obj_persp->listar(null, false);
                    $array_perspectivas= $obj_persp->array_perspectivas;

                    foreach ($array_perspectivas as $row_perspectiva) {
                        $id_perspectiva = $row_perspectiva['id'];
                        $perspectiva = $row_perspectiva['nombre'] . '  (' . $row_perspectiva['inicio'] . '-' . $row_perspectiva['fin'] . ') ';
                        $color = '#' . $row_perspectiva['color'];

                        $obj_tab->SetIdTablero($id_tablero);
                        $obj_tab->SetIdPerspectiva($id_perspectiva);
                        $obj_tab->SetIdProceso(null);
                        $obj_tab->SetYear($year);

                        $result_indi = $obj_tab->listar_indicadores(true, true);
                        $cantidad = $obj_tab->GetCantidad();

                        if ($cantidad == 0)
                            continue;
                        $cant_indi += $cantidad;

                        $nombre_prs = $row_perspectiva['proceso'].'  '.$Ttipo_proceso_array[$row_perspectiva['tipo']];
                        ?>

                        <a name="<?= $id_perspectiva ?>"></a>
                        <div class="perspectiva" style="background:<?= $color ?>;">
                            <div class="alert alert-default center" style="padding: 4px; margin: 4px;"><?= $perspectiva ?> / <span
                                    class="text-persp"> <?= $nombre_prs ?></span></div>

                            <?php include "../form/inc/_tablero_indicador.inc.php"; ?>

                            <br style="clear:both" />
                        </div>
                        <?php
                        }

                        $id_perspectiva= null;
                        $obj_tab->SetIdTablero($id_tablero);
                        $obj_tab->SetIdPerspectiva(null);
                        $obj_tab->SetIdProceso(null);
                        $obj_tab->SetYear($year);

                        $result_indi= $obj_tab->listar_indicadores(true, false);
                        $cantidad= $obj_tab->GetCantidad();

                        if ($cantidad > 0) {
                            $cant_indi+= $cantidad;
                            include "../form/inc/_tablero_indicador.inc.php";
                        }
                    }

                    if (!$use_perspectiva) {
                        $id_perspectiva= null;
                        $obj_tab->SetIdTablero($id_tablero);
                        $obj_tab->SetIdPerspectiva(null);
                        $obj_tab->SetIdProceso(null);
                        $obj_tab->SetYear($year);

                        $result_indi= $obj_tab->listar_indicadores(null, null);
                        $cantidad= $obj_tab->GetCantidad();

                        if ($cantidad > 0) {
                            $cant_indi+= $cantidad;
                            include "../form/inc/_tablero_indicador.inc.php";
                        }
                    }
                }
                ?>

                <script type="text/javascript">
                document.getElementById('nhide').innerHTML = <?=$cant_indi?>;
                </script>

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
        </div> <!-- app-body -->
    </form>

    <?php require_once "../form/inc/_tablero_div.inc.php"; ?>
</body>

</html>