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

require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/class/badger.class.php";

$signal= 'perspectiva';
$restrict_prs= array(_TIPO_DIRECCION, _TIPO_PROCESO_INTERNO, _TIPO_GRUPO, _TIPO_DEPARTAMENTO);
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$search_process= _NO_LOCAL;
require_once "../php/inc_escenario_init.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);

$obj_persp= new Tperspectiva($clink);
$obj_peso= new Tpeso_calculo($clink);
$obj_inductor= new Tinductor($clink);
$obj_indicador= new Tindicador($clink);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo_prs= $obj_prs->GetTipo();
$nombre= $obj_prs->GetNombre();
unset($obj_prs);

$url_page= "../form/lperspectiva.php?signal=$signal&action=$action&menu=tablero";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day&id_tablero=$id_tablero";

set_page($url_page);

$id_proceso_ref= $id_proceso;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <meta http-equiv="refresh" content="<?= (int)$config->delay*60?>" />
    <title>LISTADO DE PESPECTIVAS</title>

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
            $k_per= 0;
            $k_ind= 0;
            $k_indi= 0;

            $time= new TTime();

            $obj_prs= new Tproceso($clink);
            $obj_prs->SetTipo($tipo_prs);

            $id_entity= !empty($array_procesos_entity[$id_proceso]['id_entity']) ? $array_procesos_entity[$id_proceso]['id_entity'] : $id_proceso;
            
            if ($id_proceso != $_SESSION['id_entity'] && $id_entity == $_SESSION['id_entity']) {
                $obj_prs->get_procesos_up_cascade($id_proceso_ref, null, null, true);
                $array_cascade_up= $obj_prs->array_cascade_up;
            } else {
                $obj_prs->Set($id_proceso);
                $array= array('id'=>$obj_prs->GetId(), 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(),
                            'tipo'=>$obj_prs->GetTipo(), 'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado());
                $array_cascade_up[$id_proceso]= $array;
            }

            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetYear($year);
            $obj_peso->set_matrix();

            $obj_peso->compute_traze= true;

            foreach ($array_cascade_up as $prs) {
                $id_proceso= $prs['id'];
                if (isset($obj_persp)) unset($obj_persp);
                $obj_persp= new Tperspectiva($clink);

                $obj_persp->SetYear($year);
                $obj_persp->SetIdProceso($id_proceso);
                $result_persp= $obj_persp->listar();
                $_cant_persp= $obj_persp->GetCantidad();

                if (empty($_cant_persp))
                    continue;

                while ($row_persp= $clink->fetch_array($result_persp)) {
                    $_prs= $array_procesos_entity[$row_persp['_id_proceso']];
                    $id_entity= !empty($_prs['id_entity']) ? $_prs['id_entity'] : $_prs['id'];
                    $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;                            
                    
                    ++$k_per;

                    $id_persp= $row_persp['_id'];
                    $peso= $row_persp['peso'];
                    $nombre= $row_persp['_nombre'];

                    $obj_peso->SetIdProceso($id_proceso);
                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);

                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->init_calcular();
                    $obj_peso->compute_traze= true;

                    $value2= $obj_peso->calcular_perspectiva($id_persp, $array_procesos_down_entity);
                    $array_register= $obj_peso->get_array_register();

                    $obj_signal->get_month($_month, $_year);
                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->SetYearMonth($_year, $_month);
                    $obj_peso->SetDay(null);
                    $obj_peso->init_calcular();
                    $value1= $obj_peso->calcular_perspectiva($id_persp, $array_procesos_down_entity);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'PER') {
                        $observacion= textparse($array_register['observacion'], true);

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo']))
                            $responsable.= ', '.textparse($email_user['cargo'], true);
                        $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                    }
            ?>

            <input type="hidden" form="treeForm" id="id_per_<?=$k_per?>" value="<?=$id_persp?>" />
            <input type="hidden" form="treeForm" id="observacion_per_<?=$k_per?>" value="<?=$observacion?>" />
            <input type="hidden" form="treeForm" id="registro_per_<?=$k_per?>" value="<?=$responsable?>" />
            <input type="hidden" form="treeForm" id="descripcion_per_<?=$k_per?>" value="<?=$nombre?>" />

            <input type="hidden" form="treeForm" id="if_entity_per_<?=$k_per?>" value="<?=$if_entity?>" />

            <input type="hidden" form="treeForm" id="page_perspectiva_<?=$id_persp?>"
                name="page_perspectiva_<?=$id_persp?>" value="<?=$id_persp?>" />

            <li id="per_li_<?=$k_per?>">
                <div class="ul_pol paneltableblock" style="background:#<?= $row_persp['color'] ?>">
                    <div class="div_inner_ul_li" onclick="refresh_per('per_ul_<?=$k_per?>')">

                        <div class="alarm-block">
                            <i id="img_per_li_<?=$k_per?>" class="fa fa-search-plus" title="expandir"></i>
                            <?php
                            $obj_signal->get_alarm($value2);
                            $obj_signal->get_flecha($value2, $value1);
                            ?>
                        </div>

                        <?php
                        $nombre= get_short_label($nombre);

                        $tipo_prs= $row_persp['tipo'];
                        $proceso= $row_persp['proceso'];
                        $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                        ?>
                    </div>

                    <div class="div_inner_ul_li" onclick="ShowContentItem('per', <?=$k_per?>, 0, 0);">
                        <span
                            class="_value"><?php if (!is_null($value2)) echo '('.number_format($value2, 1,'.','').'%)' ?></span>

                        <span class="flag">No.<?=$row_persp['numero']?></span>&nbsp;<?=$nombre?>

                        <br />
                        <img class="img-rounded" src="../img/<?=img_process($tipo_prs)?>"
                            title="<?=$Ttipo_proceso_array[$tipo_prs]?>" /><strong
                            class="strong-title"><?=$proceso?></strong>
                        <strong class="strong-title">periodo: </strong><?="{$row_persp['inicio']}-{$row_persp['fin']}"?>
                        <?php if (!is_null($peso)) { ?><strong class="strong-title">Ponderación: <span class="peso">
                                <?=$Tpeso_inv_array[$peso]?></span></strong><?php } ?>
                    </div>
                </div>

                <ul id="per_ul_<?=$k_per?>" style="display:none">
                    <?php
                    $obj_persp->SetIdPerspectiva($id_persp);

                    $obj_persp->SetYear($year);
                    $obj_persp->SetMonth($month);
                    $obj_persp->SetIdProceso($id_proceso);

                    $result_indi= $obj_persp->listar_indicadores();
                    $_cant_indi= $clink->num_rows($result_indi);
                    $_cant_indi= !empty($_cant_indi) ? $_cant_indi : 0;
                    ?>

                    <input type="hidden" id="_cant_per_ul_<?=$k_per?>" value=<?=$_cant_indi?> />

                    <?php
                    _tree_indicadores($result_indi, $k_indi, 'per', $k_per);

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);
                    $result_ind= $obj_peso->listar_inductores_ref_perspectiva($id_persp);

                    $_cant_ind= $clink->num_rows($result_ind);
                    $_cant_ind= !empty($_cant_ind) ? $_cant_ind : 0;
                    ?>

                    <?php
                    $if_top_inductor= false;
                    $id_item_sup= $k_per;
                    $item_sup= 'per';
                    include "inc/_tree_inductor.inc.php";
                    ?>

                </ul>
            </li>
            <?php
                }
            }

            $obj_peso->close_matrix();
            ?>
        </ul>

    </div>

    <?php require "inc/_tree_js_div.inc.php" ?>

</body>

</html>