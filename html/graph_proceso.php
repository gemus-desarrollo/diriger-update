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
require_once "../php/class/cell.class.php";

require_once "../form/class/list.signal.class.php";

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : null;

require_once "../php/inc_escenario_init.php";

if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];
$signal= "graph_proceso";

$obj_user= new Tusuario($clink);

$obj= new Tcell($clink);
$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetDay($day);

$array_criterio= array(null, '&ge;','&le;','[]');

$obj_peso= new Tpeso_calculo($clink);

$obj_peso->SetDay($day);
$obj_peso->SetMonth($month);
$obj_peso->SetYear($year);

$obj_peso->SetIdProceso($id_proceso);
$obj_peso->set_id_proceso_code($id_proceso_code);

$obj_prs= new Tproceso($clink);
$array= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, null);

$if_jefe= false;
if (!is_null($array) || $_SESSION['nivel'] >= _ADMINISTRADOR) 
    $if_jefe= true;
else 
    $if_jefe= false;

unset($obj_prs);

$url_page= "../html/graph_proceso.php?signal=$signal&action=$action&menu=proceso";
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
    #container {
        margin-top: 30px;
        background: white;
    }
    #container table {
        border: 1px solid black;
    }
    #container table td,
    th {
        padding: 4px;
        text-align: center;
        vertical-align: middle;
    }
    #container table td.name {
        padding: 4px;
        text-align: left;
        vertical-align: middle;
    }
    #container table thead td {
        font-weight: bold;
        font-size: 1.2em;
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

    function go_proceso(id_tablero) {
        var month = $('#month').val();
        var year = $('#year').val();
        var day = $('#day').val();
        var id_proceso = $('#proceso').val();

        var url = 'proceso.php?id_tablero=' + id_tablero + '&id_proceso=' + id_proceso;
        url += '&day=' + day + '&month=' + month + '&year=' + year;

        self.location.href = url;
    }
    </script>
</head>

<body onload="setInterval('blinkIt()',500)">


    <div id="navbar-secondary" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <a href="#" class="navd-header">INDICADORES DEL PROCESO</a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">
                    <?php $action= ($_SESSION['nivel'] > _ADMINISTRADOR) ? 'edit' : 'list'?>

                    <li>
                        <a href="#" title="Recalcular Indicadores" onclick="recompute()">
                            <i class="fa fa-recycle"></i>Recalcular
                        </a>
                    </li>

                    <?php
                    $tipo_prs= null;
                    $obj_prs= new Tproceso($clink);

                    $obj_prs->SetIdResponsable(null);
                    $obj_prs->SetIdProceso($id_proceso);
                    $obj_prs->SetTipo($_SESSION['entity_tipo']);
                    $obj_prs->SetIdUsuario(null);

                    $j= 0;
                    $pos= 0;
                    $restrict_prs= array();
                    for ($i=0; $i <= _MAX_TIPO_PROCESO; ++$i) {
                        if ($i >= _TIPO_GRUPO || $i < $_SESSION['entity_tipo'])
                            $restrict_prs[] = $i;
                    }

                    $array_procesos= $obj_prs->listar(false, null, 'desc');
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

                    $id_proceso= $_id_proceso;
                    $id_select_prs= $id_proceso;
                    $function= null;
                    require "../form/inc/_dropdown_prs.inc.php";
                    ?>

                    <?php
                    $use_select_year= true;
                    $use_select_month= true;
                    $use_select_day= true;
                    require "../form/inc/_dropdown_date.inc.php";
                    ?>

                    <?php if (!empty($id_proceso)) { ?>
                    <li class="d-none d-lg-inline-block">
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
        $_connect_prs= $obj_prs->GetConectado();
    }
    ?>

    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row p-2 row col-12">
            <li class="col">
                <label class="badge badge-success">
                    <?=(int)$day?> de <?=$meses_array[(int)$month]?>, <?=$year?>
                </label>
            </li>
            <li class="col">
                <label class="badge badge-danger">
                    <?php if ($_connect_prs != _LAN && ($id_proceso != -1 && $id_proceso != $_SESSION['local_proceso_id'])) { ?><i
                        class="fa fa-wifi"></i><?php } ?>
                    <?= $nombre_prs ?>
                </label>
            </li>
        </ul>
    </div>

    <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
    <input type="hidden" id="tablero" name="tablero" value="<?=$id_tablero?>" />
    <input type="hidden" id="page_proceso_<?=$id_tablero?>" name="page_proceso_<?=$id_tablero?>"
        value="<?=$id_tablero?>" />

    <form action='javascript:' method="post">
        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
        <input type="hidden" name="menu" id="menu" value="tablero" />
        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />

        <input id="tablero" type="hidden" value="<?=$id_tablero?>" />
        <input id=actual_year type="hidden" value="<?=$actual_year?>" />

        <input type="hidden" id="recompute" name="recompute" value="0" />

        <!-- app-body -->
        <div class="app-body container-fluid threebar">

            <div id="container" class="container-fluid">
                <?php
                $obj_signal= new Tlist_signals($clink);
                $obj_signal->SetYear($year);

                $obj_peso= new Tpeso_calculo($clink);
                $obj_peso->SetYear($year);
                $obj_peso->set_matrix();

                $obj_prs= new Tproceso($clink);
                $obj_prs->SetYear($year);
                $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
                $obj_prs->get_procesos_by_user('eq_desc', _TIPO_PROCESO_INTERNO);
                $array_procesos= $obj_prs->array_procesos;
                
                unset($obj_prs);
                $obj_prs= new Tproceso($clink);
                $obj_prs->SetYear($year);    
                $obj_prs->SetTipo(_TIPO_PROCESO_INTERNO);
                $result= $obj_prs->listar(false);
                $cant_prs= $obj_prs->GetCantidad();                    
                ?>

                <table width="100%" border="1">
                    <thead>
                        <tr>
                            <td rowspan="2">PROCESOS</td>
                            <td colspan="12">MESES</td>
                            <td rowspan="2">OBSERVACIONES</td>
                        </tr>
                        <tr>
                            <td>Ene</td>
                            <td>Feb</td>
                            <td>Mar</td>
                            <td>Abr</td>
                            <td>May</td>
                            <td>Jun</td>
                            <td>Jul</td>
                            <td>Ago</td>
                            <td>Sep</td>
                            <td>Oct</td>
                            <td>Nov</td>
                            <td>Dic</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($obj_prs->array_procesos as $row) {
                        ?>
                        <tr>
                            <td class="name">
                                <?php if (array_key_exists($row['id'], $array_procesos) || $if_jefe) { ?>
                                <a href="#" class="btn btn-info btn-sm" title="ver detallkes del estado"
                                    onclick="go_proceso(<?=$row['id']?>);">
                                    <i class="fa fa-eye"> Detalles</i>
                                </a>
                                <?php } ?>
                                <?=$row['nombre']?>
                            </td>
                            <?php
                            $obj_signal->SetIdProceso($row['id']);
                            $obj_signal->set_criterio();
                            
                            $j= 0;
                            $value= null;
                            $observacion= null;
                            for ($mm= 1; $mm < 13; $mm++) {
                                $value2= null;
                                $observacion2= null;
                                if ($mm <= $month) {
                                    ++$j;
                                    $obj_peso->SetMonth($mm);
                                    $obj_peso->SetDay(null);

                                    $obj_peso->init_calcular();
                                    $obj_peso->SetYearMonth($year, $mm);

                                    $value2= $obj_peso->calcular_proceso($row['id'], $row['tipo'], $observacion2);
                                    $value= $value2;
                                    if ($mm == $month)
                                        $observacion.= !empty($observacion2) ? "$observacion2" :  null;
                                    $if_eficaz= $obj_peso->get_if_eficaz();
                                }
                                ?>
                                <td class="cell-alarm">
                                    <?=!is_null($value2) ? number_format($value2, 1,'.','').'%' : ''?>
                                    <?php
                                    if (!is_null($value2))
                                        $obj_signal->get_alarm_prs($value2, true, false);
                                    ?>
                                </td>
                            <?php } ?>

                            <td>
                                <?php
                                if (empty($value))
                                    $danger= "default";
                                else 
                                    $danger= $if_eficaz ? "success" : "danger";
                                ?>
                                <div class="alert alert-<?=$danger?>">
                                    <?=!empty($value) ? ($if_eficaz ? "EFICAZ" : "NO EFICAZ") : ''?>
                                </div>
                                <div style="text-align: left;">
                                    <?=!empty($observacion) ? $observacion : null?>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </form>

    <?php require_once "../form/inc/_tablero_div.inc.php"; ?>
</body>

</html>