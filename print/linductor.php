<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/programa.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$obj_user= new Tusuario($clink);
$obj_indi= new Tindicador($clink);
$obj_um= new Tunidad($clink);

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj= new Tinductor($clink);
$obj->SetIdProceso($id_proceso);
$obj->SetYear($year);
$result= $obj->listar();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

$array_inductores= Array();

$obj_peso= new Tpeso_calculo($clink);

if (!empty($id_proceso_code)) {
    $obj_peso->SetIdProceso($id_proceso);
    $obj_peso->set_id_proceso_code($id_proceso_code);
}

$obj_peso->SetYear($year);
$obj_peso->set_matrix();

$obj_prs= new Tproceso_item($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$array_indicadores= $obj_prs->listar_indicadores();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "CUMPLIMIENTO DE LOS OBJETIVOS DE TRABAJO", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>CUMPLIMIENTO DE LOS OBJETIVOS DE TRABAJO</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                CUMPLIMIENTO DE LOS OBJETIVOS DE TRABAJO<br/>
                <?= $meses_array[(int)$month] ?>/<?= $year?>
            </div>
        </div>

        <div class="page center">

            <h1>OBJETIVOS DE TRABAJO</h1>
            <table cellpadding="0" cellspacing="0">
                <thead>
                   <tr>
                        <th class="plhead left" rowspan="2" width="30">No</th>
                        <th class="plhead" rowspan="2">OBJETIVO DE TRABAJO</th>
                        <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                    </tr>
                    <tr>
                        <th class="plhead">B</th>
                        <th class="plhead">R</th>
                        <th class="plhead">M</th>
                        <th class="plhead">OBSERVACIONES</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                while ($row= $clink->fetch_array($result)) {
                    $prs= $array_procesos_entity[$row['id_proceso']];
                    if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                        continue;
                    if (empty($prs['id_entity']) && (!$row['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
                        continue;
                    if (empty($prs['id_entity']) && (!$row['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
                        continue;                      
                    ++$i;
                    /*
                    $result_indi= $obj_peso->listar_indicadores_ref_inductor($row['_id'], true);
                    $_cant_indi= $obj_peso->GetCantidad();
                    $array_inductores[$row['_id']]= $_cant_indi;
                    */
                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);
                    $obj_peso->SetDay($day);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_inductor($row['_id']);

                    $array_register= $obj_peso->get_array_register();
                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];                    
                    
                    $obj_signal->get_month($_month, $_year);
                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->SetDay(null);
                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1= $obj_peso->calcular_inductor($row['_id']);
                ?>
                    <tr>
                        <td class="plinner left">
                            <?= !empty($row['_numero']) ? $row['_numero'] : $i;?>
                        </td>
                        <td class="plinner">
                            <?= nl2br(stripslashes($row['nombre'])) ?>
                        </td>

                        <td class="plinner signal">
                            <div class="alarm-block">
                                <?php
                                if (!is_null($value2) && ($value2 > _YELLOW)) {
                                    $obj_signal->get_alarm($value2);
                                    $obj_signal->get_flecha($value2, $value1);

                                    if (!is_null($value2))
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
                                }
                                ?>
                            </div>
                        </td>

                        <td class="plinner signal">
                            <div class="alarm-block">
                                <?php
                                if (!is_null($value2) && ($value2 > _ORANGE && $value2 <= _YELLOW)) {
                                    $obj_signal->get_alarm($value2);
                                    $obj_signal->get_flecha($value2, $value1);

                                    if (!is_null($value2))
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
                                }
                                ?>
                            </div>
                        </td>

                        <td class="plinner signal">
                            <div class="alarm-block">
                                <?php
                                if (!is_null($value2) && $value2 <= _ORANGE) {
                                    $obj_signal->get_alarm($value2);
                                    $obj_signal->get_flecha($value2, $value1);

                                    if (!is_null($value2))
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
                                }
                                ?>
                            </div>
                        </td>
                        
                        <td class="plinner">
                            <?php
                            $observacion= null;
                            if ($id_user && $item_reg == 'IND') {
                                $observacion= textparse($array_register['observacion'], true);

                                $email_user= $obj_user->GetEmail($id_user);
                                $responsable= $email_user['nombre'];
                                if (!is_null($email_user['cargo'])) 
                                    $responsable.= ', '.textparse($email_user['cargo'], true);
                                $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                            }
                            ?>
                            <?=nl2br($observacion)?>
                        </td>
                    </tr>
                  <?php } ?>
                </tbody>
            </table>


            <?php
            $clink->data_seek($result);

            $i= 0; 
            $j= 0;
            while ($row= $clink->fetch_array($result)) {
                ++$i;

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                $result_indi= $obj_peso->listar_indicadores_ref_inductor($row['id_inductor'], true);
                $_cant_indi= $obj_peso->GetCantidad();
                $_cant_indi= !empty($_cant_indi) ? $_cant_indi : 0;

                if (empty($_cant_indi)) 
                    continue;
            ?>
                <br /><br />
                <h1>OBJETIVO No.<?= !empty($row['_numero']) ? $row['_numero'] : $i;?></h1>
                <table id="table-res" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="plhead left" rowspan="3">No.</th>
                            <th class="plhead" rowspan="3">INDICADOR</th>
                            <th class="plhead" rowspan="3">UM</th>
                            <th class="plhead bottom" colspan="8">CUMPLIMIENTO</th>
                        </tr>
                        <tr>
                            <th class="plhead" colspan="3" class="bottom">MES</th>
                            <th class="plhead" colspan="4" class="bottom">ACUMULADO</th>
                            <th class="plhead" class="bottom">&nbsp;</th>
                        </tr>
                        <tr>
                            <th class="plhead">PLAN</th>
                            <th class="plhead">REAL</th>
                            <th class="plhead">%</th>
                            <th class="plhead">PLAN</th>
                            <th class="plhead">REAL</th>
                            <th class="plhead" colspan="2">%</th>
                            <th class="plhead">OBSERVACIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row_indi= $clink->fetch_array($result_indi)) {
                            $prs= $array_procesos_entity[$row_indi['_id_proceso']];
                            $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];

                            if ($id_entity != $_SESSION['id_entity']) {
                                if (!array_key_exists($row_indi['_id'], $array_indicadores))
                                    continue;
                            }                            
                            
                            ++$j;

                            $id= $row_indi['_id'];
                            $peso= $row_indi['peso'];
                            $obj_indi->SetYear($year);
                            $obj_indi->Set($id);

                            $trend= $obj_indi->GetTrend();
                            $ifcumulative= $obj_indi->GetIfCumulative();
                            $id_unidad= $obj_indi->GetIdUnidad();

                            $obj_peso->SetYear($year);
                            $obj_peso->SetMonth($month);

                            $obj_peso->init_calcular();
                            $obj_peso->SetIdPrograma($id_programa);
                            $obj_peso->SetIdIndicador($id);

                            $obj_peso->compute_traze= true;
                            $_array= $obj_peso->calcular_indicador($id);

                            $id_user_plan= $_array['id_user_plan'];
                            $email_plan= $obj_user->GetEmail($id_user_plan);

                            $id_user_real= $_array['id_user_real'];
                            $email_real= $obj_user->GetEmail($id_user_real);
                            ?>

                            <tr>
                                <td class="plinner left"><?= $j?></td>
                                <td class="plinner">
                                    <?php
                                    echo $obj_indi->GetNombre();

                                    if ($id_proceso != $row_indi['_id_proceso']) {
                                        $obj_prs->Set($row_indi['_id_proceso']);
                                        echo "<div class='comment' style='width: 400px;'>".$obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()].'</div>';
                                    }
                                    ?>
                                </td>

                                <td class="plinner">
                                    <?php
                                    $obj_um->SetIdUnidad($id_unidad);
                                    $obj_um->Set();
                                    echo $obj_um->GetNombre();
                                    ?>
                                </td>
                                <td class="plinner real"><?=$_array['plan']?></td>
                                <td class="plinner real"><?=$_array['real']?></td>

                                <td class="plinner signal">
                                    <div class="alarm-block">
                                        <?php if ($_array['alarm'] != 'blank') { ?>
                                            <div class="alarm-cicle small bg-<?=$_array['alarm']?>"></div>
                                            <div class="alarm-arrow vertical small bg-<?=$_array['arrow']?>"><i class="fa <?=arrow_direction($_array['arrow'])?>"></i></div>

                                            <br /><?php if (!is_null($_array['ratio'])) echo number_format($_array['ratio'], 1,'.','') ?>
                                        <?php } ?>
                                    </div>
                                </td>

                                <td class="plinner real"><?= $_array['acumulado_plan']?></td>
                                <td class="plinner real"><?= $_array['acumulado_real']?></td>

                                <td class="plinner signal" colspan="2">
                                    <div class="alarm-block">
                                        <?php if ($ifcumulative && $_array['alarm_cumulative'] != 'blank') { ?>
                                            <div class="alarm-cicle small bg-<?=$_array['alarm_cumulative']?>"></div>
                                           <div class="alarm-arrow vertical small bg-<?=$_array['arrow_cumulative']?>"><i class="fa <?=arrow_direction($_array['arrow_cumulative'])?>"></i></div>

                                            <br /><?php if (!is_null($_array['ratio_cumulative'])) echo number_format($_array['ratio_cumulative'], 1,'.','') ?>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td class="plinner"><?= nl2br($_array['observacion_real'])?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

        </div>

    <?php require "inc/print_bottom.inc.php";?>
