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
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "class/list.signal.class.php";

require_once "../php/class/badger.class.php";

$signal= 'objetivo';
$restrict_prs= array(_TIPO_DIRECCION);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tobjetivo($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);
$obj_inductor= new Tinductor($clink);
$obj_indicador= new Tindicador($clink);
$obj_peso= new Tpeso($clink);

$url_page= "../form/lobjetivo.php?signal=$signal&action=$action&menu=objetivo&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action";

set_page($url_page);

$signal= 'objetivo';
$restrict_prs= null;
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

    <title>LISTADO DE OBJETVOS ESTRATEGICOS</title>

    <?php require_once "inc/_tree_head.inc.php"; ?>
    <?php require_once "inc/_tree_functions.inc.php"; ?>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Panel -->
    <?php require "inc/_tree_toppanel.inc.php"?>

    <div class="app-body container-fluid threebar">

        <ul class=ul_top>
            <?php
            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetYear($year);
            $obj_peso->SetMonth($month);
            $obj_peso->set_matrix();

            $obj->SetIdProceso($id_proceso);
            $obj->SetIdEscenario(null);
            $obj->SetYear($year);

            $result_obj_sup= $obj->listar();

            $k_obj= 0;
            $k_ind= 0;
            $k_indi= 0;

            _tree_objetivo($result_obj_sup, $k_obj, $k_ind, $k_indi, 0, 0);

            $obj_peso->close_matrix();
            ?>
        </ul>

    </div>

    <script type="text/javascript">
    document.getElementById('nshow').innerHTML = <?=$k_obj?>;
    </script>

    <?php require "inc/_tree_js_div.inc.php" ?>

</body>

</html>