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
require_once "../php/class/unidad.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/cell.class.php";
require_once "../php/class/cell_list.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/class/traza.class.php";

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$id_tablero= $_GET['id_tablero'];
$id_proceso= $_GET['id_proceso'];

$obj_cell= new TCell_list($clink);

$obj_cell->SetDay($day);
$obj_cell->SetMonth($month);
$obj_cell->SetYear($year);

$obj_ind= new Tindicador($clink);
$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tprocesos(false);

$obj_prs= new Tproceso_item($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj_prs->SetIdProceso($id_tablero);
$obj_prs->Set();

$obj_peso= new Tpeso($clink);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN PROCESO INTERNO", "Corresponde a periodo año: $year");
?>

<html>
    <head>

        <title>RESUMEN PROCESO INTERNO</title>

         <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                RESUMEN DE ESTADO DEL PROCESO<br />
                PROCESO: <?=$obj_prs->GetNombre()?><br/>
                <?=$meses_array[(int)$month]?>/<?=$year?>
            </div>
        </div>

        <div class="page center">
        <?php
        $obj_peso= new Tpeso_calculo($clink);
        $obj_peso->SetYear($year);
        $obj_peso->set_matrix();

        if (!empty($id_tablero)) {
           $obj_signal= new Tlist_signals($clink);
           $obj_signal->SetYear($year);
           $obj_signal->SetMonth($month);
           $obj_signal->SetIdProceso($id_tablero);

           $obj_peso->SetYear($year);
           $obj_peso->SetMonth($month);

           $obj_peso->init_calcular();
           $value2= $obj_peso->calcular_proceso($id_tablero, _TIPO_PROCESO_INTERNO);
           $if_eficaz= $obj_peso->get_if_eficaz();

           $if_eficaz= (!$if_eficaz || !$obj_signal->if_eficaz) ? false : true;
           $obj_signal->if_eficaz= $if_eficaz;
           $obj_signal->update_eficaz_prs();

           $obj_signal->get_month($_month, $_year);

           $obj_peso->SetYear($_year);
           $obj_peso->SetMonth($_month);

           $obj_peso->init_calcular();
           $value1= $obj_peso->calcular_proceso($id_tablero);
        }
        ?>

        <?php if (!empty($id_tablero)) { ?>

            <dl class="dl-horizontal col-md-offset-0 col-lg-offset-0">
                <dt class="pull-left">
                   ESTADO:
                </dt>

                <dd>
                    <div class="col-xs-5 col-5">
                        <div class="row">
                            <div class="col-xs-3 col-sm-3 col-md-2 col-lg-2">
                                <?=!empty($value2) ? number_format($value2, 1,'.','').'%' : ''?>
                            </div>
                            <div class="col-xs-3 col-sm-3 col-md-1 col-lg-1">
                                <?php
                                $obj_signal->get_alarm_prs($value2);
                                ?>
                            </div>
                            <div class="col-xs-3 col-sm-2 col-md-1 col-lg-1">
                                <?php
                                $obj_signal->get_flecha($value2, $value1);
                                ?>
                            </div>
                            <label class="col-xs-3 col-sm-4 col-md-6 col-lg-6 label <?=!empty($value2) ? ($if_eficaz ? "label-success" : "label-danger") : ''?>">
                                <?=!empty($value2) ? ($if_eficaz ? "EFICAZ" : "NO EFICAZ") : ''?>
                            </label>
                        </div>
                    </div>
                </dd>
            </dl>
        <?php } ?>

        <?php
        $obj_prs->SetIdProceso($id_tablero);
        $obj_prs->SetYear($year);

        $result_indi = $obj_prs->listar_indicadores(null, null);
        $cantidad = $obj_prs->GetCantidad();

        if ($cantidad > 0)
            include "inc/_table_resumen.inc.php";
            ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>