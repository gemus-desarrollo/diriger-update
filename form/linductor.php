<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']='no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "class/list.signal.class.php";

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
    $obj= new Tinductor($clink);
}

$search_process= _NO_LOCAL;
require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$url_page= "../form/linductor.php?signal=$signal&action=$action&menu=inductor&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action&id_perspectiva=$id_perspectiva";

set_page($url_page);

$signal= 'inductor';
$restrict_prs= array(_TIPO_DIRECCION);
$id_proceso_ref= $id_proceso;

require "inc/_tree_functions.inc.php";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <meta http-equiv="refresh" content="<?= (int)$config->delay*60?>" />

    <title>RELACIÓN DE OBJETIVOS DE TRABAJO</title>

    <?php require_once "inc/_tree_head.inc.php"; ?>
    <?php require_once "inc/_tree_functions.inc.php"; ?>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Panel -->
    <?php require "inc/_tree_toppanel.inc.php"?>

    <div class="app-body container-fluid threebar">
        <?php
        $id_politica= null;
        $id_objetivo= null;
        $id_perspectiva= null;

        $obj->SetYear($year);
        $obj->SetIdProceso($id_proceso);
        $result_ind= $obj->listar(_PERSPECTIVA_NULL);
        $cant= $obj->GetCantidad();

        $obj_indicador= new Tindicador($clink);
        $obj_inductor= new Tinductor($clink);

        $obj_peso= new Tpeso_calculo($clink);
        $obj_peso->SetYear($year);
        $obj_peso->SetMonth($month);
        $obj_peso->set_matrix();

        $k_ind= 0;
        $k_per= 0;

        if ($cant) {
        ?>

        <ul id="per_ul_<?= $k_per?>" style="display:block">
            <?php
            $if_top_inductor= true;
            $id_item_sup= null;
            $item_sup= null;
            if ($cant)
                include "inc/_tree_inductor.inc.php";
            ?>
        </ul>
        <?php } ?>

        <?php
        unset($obj_prs);
        $obj_prs= new Tproceso($clink);

        $obj_persp= new Tperspectiva($clink);
        $obj_persp->SetYear($year);
        $result_persp= $obj_persp->listar();
        $cant_persp= $obj_persp->GetCantidad();

        if ($cant_persp) {
            while ($row_persp= $clink->fetch_array($result_persp)) {
                $id_perspectiva= $row_persp['_id'];
                $color= '#'.$row_persp['color'];

                $obj->SetIdPerspectiva($id_perspectiva);
                $obj->SetIdProceso($id_proceso);
                $obj->SetYear($year);

                $result_ind= $obj->listar(_PERSPECTIVA_NOT_NULL);
                $cant= $obj->GetCantidad();
                if (empty($cant))
                    continue;
                $nombre= $row_persp['nombre'];
                $inicio= $row_persp['inicio'];
                $fin= $row_persp['fin'];
                $nombre_prs= $row_persp['proceso'].', '.$Ttipo_proceso_array[$row_persp['tipo']];

                ++$k_per;
            ?>

            <div class="paneltableblock" style="background:<?= $color?>;">
                <strong>PERSPECTIVA: </strong><?= "$nombre ($inicio-$fin), <i>$nombre_prs</i><br />" ?>
            </div>

            <ul id="per_ul_<?=$k_per?>" style="display:block">
                <?php
                $if_top_inductor= true;
                $id_item_sup= null;
                $item_sup= null;
                include "inc/_tree_inductor.inc.php";
                ?>
            </ul>

        <?php
            }
        }

        $obj_peso->close_matrix();
        ?>

    </div>

    <script type="text/javascript">
    document.getElementById('nshow').innerHTML = <?=$k_ind?>;
    </script>

    <?php require "inc/_tree_js_div.inc.php" ?>

</body>

</html>