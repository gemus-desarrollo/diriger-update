<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/politica.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "class/list.signal.class.php";

require_once "../php/class/badger.class.php";

$signal= 'objetivo_sup';
$restrict_prs= array(_TIPO_DIRECCION, _TIPO_PROCESO_INTERNO, _TIPO_GRUPO, _TIPO_DEPARTAMENTO);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) 
        unset($_SESSION['obj']);
}

$capitulo= !empty($_GET['capitulo']) ? $_GET['capitulo'] : 0;
$grupo= !empty($_GET['grupo']) ? $_GET['grupo'] : 0;
$chk_sys= !empty($_GET['chk_sys']) ? $_GET['chk_sys'] : 1;

$error= !empty($_GET['error']) ? $_GET['error'] : null;

$id_proceso= $_SESSION['superior_proceso_id'];
require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);
$obj_inductor= new Tinductor($clink);
$obj_indicador= new Tindicador($clink);
$obj_objetivo= new Tobjetivo($clink);

$obj_peso= new Tpeso_calculo($clink);

$obj= new Tobjetivo($clink);
$obj->SetIfObjetivoSup(true);
$obj->SetYear($year);

$id_proceso= $_SESSION['id_entity'];

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdEntity(null);
$obj_prs->SetIdUsuario(null);
$id_proceso_sup= $obj_prs->get_proceso_top($id_proceso);

$url_page= "../form/lobjetivo_sup.php?signal=$signal&action=$action&menu=politica";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day&exect=$action&";

set_page($url_page);
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <meta http-equiv="refresh" content="<?php echo (int)$config->delay*60?>" />

    <title>LISTADO DE OBJETIVOS ESTRATÉGICOS DEL ORGANO DE DIRECCIÓN SUPERIOR</title>

    <?php require_once "inc/_tree_head.inc.php"; ?>
    <?php require_once "inc/_tree_functions.inc.php"; ?>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Panel -->
    <?php require "inc/_tree_toppanel.inc.php"?>

    <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />

    <div class="app-body container-fluid twobar">

        <?php if (empty($id_proceso_sup)) { ?>
        <div class="alert alert-danger m-3">
            No existe Órgano Superior de Dirección declarado en el sistema.
            Debe ir a la configuración de los procesos o Unidades organizativas y registrar esa nueva Unidad
            organizativa
            y si ya está registrada, entonces subordinar su Organización a su Órgano Superior.
        </div>
        <?php } ?>

        <ul class=ul_top>
            <?php
            $obj_peso->set_id_proceso_code($id_proceso_code);
            $obj_peso->SetIdProceso($id_proceso);

            $obj_peso->SetYear($year);
            $obj_peso->set_matrix();

            $obj->SetIdProceso($id_proceso_sup);
            $obj->SetYear($year);
            $result_obj_sup= !empty($id_proceso_sup) ? $obj->listar() : null;

            $if_obj_sup_LOCAL= true;
            $k_obj= 0; 
            $k_ind= 0; 
            $k_indi= 0; 
            $k_obj_sup= 0;
            _tree_objetivo($result_obj_sup, $k_obj, $k_ind, $k_indi, 0, $k_obj_sup, true);

            $obj_peso->close_matrix();
            ?>
        </ul>

    </div>

    <script type="text/javascript">
    document.getElementById('nshow').innerHTML = <?=$k_obj_sup?>;
    </script>

    <?php require "inc/_tree_js_div.inc.php" ?>

</body>

</html>