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
require_once "../php/class/indicador.class.php";
require_once "../php/class/cell.class.php";

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

require_once "../php/inc_escenario_init.php";
if (empty($id_proceso)) $id_proceso= $_SESSION['local_proceso_id'];
$signal= "proceso";

$obj= new Tcell($clink);
$obj->SetDay($day);
$obj->SetMonth($month);
$obj->SetYear($year);

$array_criterio= array(null, '&ge;','&le;','[]');

$obj_ind= new Tindicador($clink);
$obj_user= new Tusuario($clink);
$obj_user->set_use_copy_tprocesos(false);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj_prs->SetIdProceso($id_tablero);
$obj_prs->Set();

include "inc/_tablero_indicador_mensual.inc.php";

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN MENSUAL DE INDICADORES", "Corresponde a periodo mes/año: $month/$year");
?>

<html>
    <head>
        <title>RESUMEN MENSUAL DE INDICADORES</title>

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
            $obj_peso->SetIdProceso($id_tablero);

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
            $value2= $obj_peso->calcular_proceso($id_tablero, _TIPO_PROCESO_INTERNO);
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
                            <label class="col-xs-3 col-sm-4 col-md-6 col-lg-6 label <?=!empty($value2) ? ($if_eficaz) ? "label-success" : "label-danger" : ''?>">
                                <?=!empty($value2) ? ($if_eficaz ? "EFICAZ" : "NO EFICAZ") : ''?>
                            </label>
                        </div>
                    </div>
                </dd>
            </dl>
        <?php } ?>

        <?php
        unset($obj_prs);
        $obj_prs= new Tproceso_item($clink);

        $obj_prs->SetYear($year);
        $obj_prs->SetIdProceso($id_tablero);
        $result_indi= $obj_prs->listar_indicadores(false);
        $cantidad= $obj_prs->GetCantidad();

        if ($cantidad > 0) 
            write_html($result_indi);
        ?>

    </div>

    <?php require "inc/print_bottom.inc.php";?>
