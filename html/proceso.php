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
require_once "../php/class/escenario.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";
require_once "../php/class/calculator.class.php";
require_once "../php/class/cell.class.php";
require_once "../php/class/unidad.class.php";

require_once "../form/class/list.signal.class.php";

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : null;

require_once "../php/inc_escenario_init.php";

if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];
$signal= "proceso";

$obj_user= new Tusuario($clink);
$obj_cal= new Tcalculator($clink);

$obj= new Tcell($clink);
$obj->SetDay($day);
$obj->SetMonth($month);
$obj->SetYear($year);

$array_criterio= array(null, '&ge;','&le;','[]');

$obj_peso= new Tpeso_calculo($clink);

$obj_peso->SetDay($day);
$obj_peso->SetMonth($month);
$obj_peso->SetYear($year);

$obj_peso->SetIdProceso($id_proceso);
$obj_peso->set_id_proceso_code($id_proceso_code);

$obj_unidad= new Tunidad($clink);

$obj_prs= new Tproceso($clink);
$array= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, null);

$if_jefe= false;
if (!is_null($array) || $_SESSION['nivel'] >= _ADMINISTRADOR) 
    $if_jefe= true;
else 
    $if_jefe= false;

unset($obj_prs);

$url_page= "../html/proceso.php?signal=$signal&action=$action&menu=proceso";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day&id_tablero=$id_tablero";

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

    <title>PROCESOS INTERNOS</title>

    <?php require_once "inc/_tablero_top.inc.php" ?>

    <style type="text/css">
    .container-fluid div.body {
        margin-top: 10px !important;
    }
    .alarm.badge {
        padding: 10px 20px;
        color: white;
        font-weight: bolder;
        margin-top: -10px;
    }
    td.signal {
        padding: 2px 4px;
        background: transparent;
    }
    </style>    


    <script type="text/javascript" language="javascript">
    function recompute() {
        var month = $('#month').val();
        var year = $('#year').val();
        var day = $('#day').val();
        var id_proceso = $('#proceso').val();
        var id_tablero = $('#tablero').val();

        var url = '../php/recompute.interface.php?id_tablero=' + id_tablero + '&id_proceso=' + id_proceso;
        url += '&day=' + day + '&month=' + month + '&year=' + year + '&item_recompute=proceso';

        self.location.href = url;
    }

    function closep() {
        var month = $('#month').val();
        var year = $('#year').val();
        var day = $('#day').val();
        var id_proceso = $('#proceso').val();
        var id_tablero = $('#tablero').val();

        var url = 'graph_proceso.php?id_tablero=' + id_tablero + '&id_proceso=' + id_proceso;
        url += '&day=' + day + '&month=' + month + '&year=' + year;

        self.location.href = url;
    }
    </script>
</head>

<body onload="setInterval('blinkIt()',500)">

    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>  

                <a href="#" class="navd-header">
                    INDICADORES DEL PROCESO
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php $action= ($_SESSION['nivel'] > _ADMINISTRADOR) ? 'edit' : 'list'?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-cogs"></i>Opciones<span class="caret"></span>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <li class="nav-item">
                                    <a href="#" title="editar proceso"
                                        onclick="enviar_proceso(<?=$id_tablero?>,'<?=$action?>')">
                                        <i class="fa fa-edit"></i>Editar
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" title="Recalcular Indicadores" onclick="recompute()">
                                        <i class="fa fa-recycle"></i>Recalcular
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php
                        $tipo_prs= null;
                        $obj_prs= new Tproceso($clink);
                        $obj_prs->SetYear($year);
                        $obj_prs->SetIdResponsable(null);
                        $obj_prs->SetIdProceso($id_proceso);
                        $obj_prs->SetTipo(_TIPO_PROCESO_INTERNO);
                        $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
                        $obj_prs->SetIdEntity($_SESSION['id_entity']);

                        $j= 0;
                        $pos= 0;
                        $exclude_prs_type= array();
                        for ($i=0; $i <= _MAX_TIPO_PROCESO; ++$i) {
                            if ($i == _TIPO_PROCESO_INTERNO || $i == _TIPO_ARC) 
                                continue;
                            $exclude_prs_type[$i]= 1;
                        }
                    
                        if (!$if_jefe) {
                            $array_procesos= $obj_prs->get_procesos_by_user('desc', _TIPO_ARC, null, null, $exclude_prs_type);
                        } else {
                            $array_procesos= $obj_prs->listar(false);
                        }
                        
                        $cant_prs= $obj_prs->GetCantidad();

                        if ((!empty($id_tablero) && array_key_exists($id_tablero, $array_procesos) == false) || empty($id_tablero)) {
                            list($key, $value) = each($array_procesos);
                            $id_tablero= $key;
                        }
                        if (!empty($id_tablero)) {
                            $obj_prs->SetYear($year);
                            $obj_prs->Set($id_tablero);
                            $nombre_tablero= $obj_prs->GetNombre();
                            $tipo_prs= $obj_prs->GetTipo();
                            if ($tipo_prs != _TIPO_PROCESO_INTERNO && $tipo_prs != _TIPO_ARC) 
                                $id_tablero= null;

                            $id_responsable= $obj_prs->GetIdResponsable();
                            $email= $obj_user->GetEmail($id_responsable);
                            $responsable= $email['nombre'];
                            $responsable.= ', '.textparse($email['cargo']);

                            $descripcion= $obj_prs->GetDescripcion();
                        }

                        $_id_proceso= $id_proceso;
                        $id_proceso= $id_tablero;

                        if ($cant_prs > 0) {
                        ?>

                        <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-industry"></i>Procesos Internos<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarOpciones">
                                <?php
                                reset($array_procesos);
                                foreach ($array_procesos as $array) {
                                    if (empty($array['id'])) 
                                        continue;
                                    if (!empty($array['id_proceso']) && $array['id_proceso'] != $_id_proceso) 
                                        continue;
                                    if ($array['tipo'] != _TIPO_PROCESO_INTERNO && $array['tipo'] != _TIPO_ARC) 
                                        continue;

                                    $function= "_dropdown_tablero";
                                    include "../form/inc/_tablero_tabs_proceso.inc.php";
                                }
                                ?>
                            </ul>
                        </li>
                        <?php } ?>

                        <?php
                        $id_proceso= $_id_proceso;
                        $id_select_prs= $id_proceso;
                        $function= null;
                        $restrict_prs= array(_TIPO_PROCESO_INTERNO, _TIPO_DEPARTAMENTO, _TIPO_GRUPO);
                        require "../form/inc/_dropdown_prs.inc.php";
                        ?>

                            <?php
                        $use_select_year= true;
                        $use_select_month= true;
                        $use_select_day= true;
                        require "../form/inc/_dropdown_date.inc.php";
                        ?>

                        <?php if (!empty($id_tablero)) { ?>
                        <li class="d-none d-md-inline-block">
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
                            <li class="nav-item">
                                <a href="#" onclick="closep()">
                                    <i class="fa fa-close"></i>Cerrar
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>    
        </nav>    
    </div>        

    <?php
    $id_proceso= $_id_proceso;
    if (!empty($id_proceso)) {
        if (isset($obj_prs)) unset($obj_prs);
        $obj_prs= new Tproceso($clink);
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $nombre_prs= $obj_prs->GetNombre().", ".$Ttipo_proceso_array[$obj_prs->GetTipo()];
    }  
    ?>

    <div id="navbar-third" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
                <li class="col">
                    <label class="badge badge-success">
                        <?=(int)$day?> de <?=$meses_array[(int)$month]?>, <?=$year?>
                    </label>
                </li>
                <li class="row col">
                    <label class="label"><span style="margin-left: 20px;">Proceso Interno:</span></label>
                    <label class="badge badge-danger">
                        <?=$nombre_tablero?>
                    </label>
                </li>
                <li class="row col-auto">
                    <label class="label">
                        <span style="margin-left: 20px;">Responsable:</span>
                    </label>
                    <label class="badge badge-warning">
                        <?=$responsable?>
                    </label>
                </li>
                <li class="col">
                    <label class="label"><span style="margin-left: 20px;">Unidad Organizativa:</span></label>
                    <label class="badge badge-danger">
                        <?=$nombre_prs?>
                    </label>
                </li>
            </ul>
        </nav>
    </div>


    <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
    <input type="hidden" id="tablero" name="tablero" value="<?=$id_tablero?>" />
    <input type="hidden" id="page_proceso_<?=$id_tablero?>" name="page_proceso_<?=$id_tablero?>"
        value="<?=$id_tablero?>" />

    <form action='javascript:' method='post'>
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

        <input type="hidden" id="id_usuario" name="id_usuario"
            value="<?=$_SESSION['id_usuario'] ?>" />
        <input type="hidden" id="nivel" name="nivel" value="<?=$_SESSION['nivel']?>" />

        <input id="tablero" type="hidden" value="<?=$id_tablero?>" />
        <input id=actual_year type="hidden" value="<?=$actual_year?>" />

        <!-- app-body -->
        <div class="app-body container-fluid threebar">
            <?php
            $observacion= null;
            $obj_peso->SetYear($year);
            $obj_peso->SetMonth($month);
            $obj_peso->set_matrix();

            if (!empty($id_tablero) && $cant_prs > 0) {
                $obj_signal= new Tlist_signals($clink);
                $obj_signal->SetYear($year);
                $obj_signal->SetMonth($month);
                $obj_signal->SetIdProceso($id_tablero);

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);
                $obj_peso->SetDay($day);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($year, $month);
                $obj_peso->compute_traze= true;
                $value2= $obj_peso->calcular_proceso($id_tablero, $tipo_prs, $observacion);
                $if_eficaz= $obj_peso->get_if_eficaz();

                $if_eficaz= (!$if_eficaz || !$obj_signal->if_eficaz) ? false : true;
                $obj_signal->if_eficaz= $if_eficaz;
                $obj_signal->update_eficaz_prs();

                $obj_signal->get_month($_month, $_year);

                $obj_peso->SetYear($_year);
                $obj_peso->SetMonth($_month);
                $obj_peso->SetDay(null);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($_year, $_month);
                $value1= $obj_peso->calcular_proceso($id_tablero, $tipo_prs);
            }
            ?>

            <?php if (!empty($id_tablero)) { ?>
            <div class="col-12 alert alert-dark">
                <div class="row">
                    <div class="row col-6">
                        <label class="col-2" style="font-weight: bold;">
                            PROCESO:
                        </label>
                        <label class="col-10 align-content-start">
                            <span class="text pull-left col-lg-offset-0"><?=$nombre_tablero?></span>
                        </label>
                    </div>
                
                    <div class="row col-4">
                        <div class="col-2">
                            <?=!empty($value2) ? number_format($value2, 1,'.','').'%' : ''?>
                        </div>

                        <div class="row col-4">
                            <table>
                                <tr>
                                    <td class="signal">
                                        <?php $obj_signal->get_alarm_prs($value2); ?>
                                    </td>
                                    <td class="signal">
                                        <?php $obj_signal->get_flecha($value2, $value1); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-2">
                        <label class="alarm badge badge-<?=!empty($value2) ? ($if_eficaz ? "success" : "danger") : ''?>">
                            <?=!empty($value2) ? ($if_eficaz ? "EFICAZ" : "NO EFICAZ") : ''?>
                        </label>
                    </div>
                </div>

                <div class="row mt-1">
                    <div class="col-1" style="font-weight: bold;">
                        RESPONSABLE:
                    </div>
                    <div class="col-auto text"><?=$responsable?></div>
                </div>

                <div class="row mt-1">
                    <div class="col-1" style="font-weight: bold;">
                        DESCRIPCIÓN:
                    </div>
                    <div class="col align-content-start">
                        <?=$descripcion?>
                        <?=!empty($observacion) ? "<br/>$observacion" : null?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <div class="body">
                <div class="inner">
                    <!-- CONSTRUCCION DE LOS INDICADORES-->
                    <?php
                    $cantidad= 0;
                    $cargo= null;

                    $obj_prs= new Tproceso_item($clink);
                    $obj_prs->SetIdUsuario($_SESSION['id_usuario']);

                    if (!empty($id_tablero)) {
                        $obj_prs->SetYear($year);
                        $obj_prs->SetIdProceso($id_tablero);

                        $obj_prs->Set($id_tablero);
                        $nombre_prs= $obj_prs->GetNombre();
                        $id_responsable= $obj_prs->GetIdResponsable();

                        $obj_user= new Tusuario($clink);
                        $obj_user->Set($id_responsable);
                        $responsable= $obj_user->GetNombre();
                        $cargo= $obj_user->GetCargo();
                        $responsable= "$responsable ({$cargo})";

                        $result_indi= $obj_prs->listar_indicadores(false);
                        $cantidad= $obj_prs->GetCantidad();

                        include "../form/inc/_tablero_indicador.inc.php";
                    }
                    ?>
                </div>

                <?php if ($cantidad == 0 && !empty($id_tablero)) { ?>
                    <div class="alert alert-danger">
                        NO EXISTEN INDICADORES ASIGNADOS A ESTE PROCESO. DEBE CONSULTAR AL ADMINISTRADOR
                        DEL SISTEMA.
                    </div>
                <?php } ?>

                <?php if ($cant_prs == 0) { ?>
                    <div class="alert alert-danger">
                        USTED NO ESTÁ ASIGNADO A NINGÚN PROCESO INTERNO. DEBE CONSULTAR AL ADMINISTRADOR
                        DEL SISTEMA.
                    </div>
                <?php } ?>
            </div>
        </div>    
    </form>
                    
    <?php require_once "../form/inc/_tablero_div.inc.php"; ?>
</body>

</html>