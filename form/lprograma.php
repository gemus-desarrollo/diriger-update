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

require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/programa.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

$signal= 'programa';
$restrict_prs= array(_TIPO_DIRECCION, _TIPO_PROCESO_INTERNO, _TIPO_GRUPO, _TIPO_DEPARTAMENTO);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$search_process= _LOCAL;
require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);
$obj_indicador= new Tindicador($clink);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$nombre= $obj_prs->GetNombre();
unset($obj_prs);

$url_page= "../form/lprograma.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_tablero=$id_tablero";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <meta http-equiv="refresh" content="<?= (int)$config->delay*60?>" />
    <title>LISTADO DE PROGRAMAS</title>

    <?php require_once "inc/_tree_head.inc.php"; ?>
    <?php require_once "inc/_tree_functions.inc.php"; ?>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Panel -->
    <?php require "inc/_tree_toppanel.inc.php"?>

    <div class="app-body container-fluid twobar">
        <ul class=ul_top>
            <?php
            $obj_prs= new Tproceso($clink);

            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetYear($year);
            $obj_peso->set_matrix();
            $obj_peso->compute_traze= true;

            $obj_prog= new Tprograma($clink);
            $obj_prog->SetYear($year);
            $obj_prog->SetIdProceso($id_proceso);
            $result_prog= $obj_prog->listar(_LOCAL);

            $k_prog= 0; $k_indi= 0;
            while ($row_prog= $clink->fetch_array($result_prog)) {
                ++$k_prog;
                $id_programa= $row_prog['_id'];

                $obj_prog->Set($id_programa);
                $nombre= $obj_prog->GetNombre();

                $obj_peso->SetIdProceso($id_proceso);
                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                $obj_peso->init_calcular();
                $obj_peso->compute_traze= true;
                $obj_peso->SetYearMonth($year, $month);
                $value2= $obj_peso->calcular_programa($id_programa);
                $array_register= $obj_peso->get_array_register();

                $obj_signal->get_month($_month, $_year);

                $obj_peso->SetYear($_year);
                $obj_peso->SetMonth($_month);

                $obj_peso->init_calcular();
                $obj_peso->SetDay(null);
                $obj_peso->SetYearMonth($_year, $_month);
                $value1= $obj_peso->calcular_programa($id_programa);

                $id_user= $array_register['id_usuario'];
                $item_reg= $array_register['signal'];

                $responsable= null;
                $observacion= null;

                $obj_prs->Set($row_prog['_id_proceso']);
                $tipo_prs= $obj_prs->GetTipo();
                $proceso= $obj_prs->GetNombre();
                $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                if ($id_user && $item_reg == 'PROG') {
                    $observacion= textparse($array_register['observacion'], true);

                    $email_user= $obj_user->GetEmail($id_user);
                    $responsable= $email_user['nombre'];
                    if (!is_null($email_user['cargo'])) 
                        $responsable.= ', '.textparse($email_user['cargo'], true);
                    $responsable.= '  <br /><strong>corte:</strong>'.odbc2date($array_register['reg_fecha']).'   <strong>registrado:</strong>'.odbc2time_ampm($array_register['cronos']);
                }
            ?>

            <input type="hidden" form="treeForm" id="id_prog_<?=$k_prog?>" value="<?=$id_programa?>" />
            <input type="hidden" form="treeForm" id="observacion_prog_<?=$k_prog?>" value="<?=$observacion?>" />
            <input type="hidden" form="treeForm" id="registro_prog_<?=$k_prog?>" value="<?=$responsable?>" />
            <input type="hidden" form="treeForm" id="descripcion_prog_<?=$k_prog?>" value="<?=$nombre?>" />

            <input type="hidden" form="treeForm" id="page_programa_<?=$id_programa?>"
                name="page_programa_<?=$id_programa?>" value="<?=$id_programa?>" />

            <li id="prog_li_<?=$k_prog?>">
                <div class=ul_ind onmouseover="this.className='ul_ind rover-ind'" onmouseout="this.className='ul_ind'">
                    <div class="div_inner_ul_li" onclick="refresh_prog('prog_ul_<?=$k_prog?>')">
                        <div class="alarm-block">
                            <i id="img_prog_li_<?= $k_prog ?>" class="fa fa-search-plus" title="expandir"></i>
                            <?php
                            $obj_signal->get_alarm($value2);
                            $obj_signal->get_flecha($value2, $value1);
                            ?>
                        </div>

                        <?php
                        $nombre= get_short_label($nombre);

                        $obj_prs->Set($row_prog['_id_proceso']);
                        $tipo_prs= $obj_prs->GetTipo();
                        $proceso= $obj_prs->GetNombre();
                        $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                        ?>
                    </div>

                    <div class="div_inner_ul_li" onclick="ShowContentItem('prog', <?=$k_prog?>, 0, 0);">
                        <span class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1, '.', '').'%)' ?></span>
                        <span class="flag"><?=$nombre?></span>

                        <br />
                        <img src="../img/<?=img_process($tipo_prs)?>" title="<?=$Ttipo_proceso_array[$tipo_prs]?>" />
                        <strong class="strong-title"><?=$proceso?></strong>
                        <strong class="strong-title">periodo: </strong><?="{$row_prog['inicio']}-{$row_prog['fin']}"?>
                        <?php if (!is_null($peso)) { ?>
                            <strong class="strong-title">Ponderación: <span class="peso"><?=$Tpeso_inv_array[$peso]?></span></strong>
                        <?php } ?>
                    </div>
                </div>

                <ul id="prog_ul_<?=$k_prog?>" style="display:none">
                    <?php
                    $obj_prog->SetIdPrograma($id_programa);

                    $obj_prog->SetYear($year);
                    $obj_prog->SetMonth($month);

                    $result_indi= $obj_prog->listar_indicadores();
                    $_cant_indi= $clink->num_rows($result_indi);
                    $_cant_indi= !empty($_cant_indi) ? $_cant_indi : 0;
                    ?>

                    <input type="hidden" id="_cant_prog_ul_<?=$k_prog?>" value=<?= $_cant_indi?> />

                    <?php
                    if ($_cant_indi > 0)
                        _tree_indicadores($result_indi, $k_indi, 'prog', $k_prog);
                    ?>

                </ul>
            </li>
            <?php
            }

            $obj_peso->close_matrix();
            ?>
        </ul>
    </div>

    <?php require "inc/_tree_js_div.inc.php" ?>

</body>

</html>