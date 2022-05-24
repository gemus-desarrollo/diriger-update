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
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/indicador.class.php";
require_once "../php/class/programa.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/objetivo.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$obj_signal = new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user = new Tusuario($clink);

$_id_proceso = $id_proceso;

$obj = new Tobjetivo($clink);
$obj->SetIdProceso($_id_proceso);
$obj->SetYear($year);
$result = $obj->listar();

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($_id_proceso);
$obj_prs->Set();
$tipo_prs = $obj_prs->GetTipo();
$proceso = $obj_prs->GetNombre();

$array_objetivos = null;

$obj_peso= new Tpeso_calculo($clink);

if (!empty($id_proceso_code)) {
    $obj_peso->SetIdProceso($id_proceso);
    $obj_peso->set_id_proceso_code($id_proceso_code);
}

$obj_peso->SetYear($year);
$obj_peso->set_matrix();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RELACIÓN DE OBJETIVOS ESTRATEGICOS", "Corresponde a periodo año: $year");
?>

<html>

<head>
    <title>RELACIÓN DE OBJETIVOS ESTRATEGICOS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div class="title-header"> CUMPLIMIENTO DE LOS OBJETIVOS ESTRATÉGICOS<br />
            <?= $meses_array[(int)$month] ?>/<?= $year ?>
        </div>
    </div>

    <div class="page center">
        <h1>OBJETIVOS ESTRATÉGICOS</h1>
        <table cellpadding="0" cellspacing="0" width="800px">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30">No</th>
                    <th class="plhead" rowspan="2">OBJETIVO</th>
                    <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                </tr>
                <tr>
                    <th class="plhead">B</th>
                    <th class="plhead">R</th>
                    <th class="plhead">M</th>
                    <th class="plhead">OBSERVACIONES</th>
                </tr>
            </thead>

            <?php
            while ($row = $clink->fetch_array($result)) {
                $prs= $array_procesos_entity[$row['id_proceso']];
                if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                    continue;
                if (empty($prs['id_entity']) && (!$row['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
                    continue;
                if (empty($prs['id_entity']) && (!$row['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
                    continue;
                ++$i;

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                $obj_peso->init_calcular();
                $observacion = null;
                $obj_peso->SetYearMonth($year, $month);
                $value2 = $obj_peso->calcular_objetivo($row['_id']);

                $register = $obj_peso->get_array_register();
                $observacion = $register['observacion'];

                $obj_signal->get_month($_month, $_year);

                $obj_peso->SetYear($_year);
                $obj_peso->SetMonth($_month);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($_year, $_month);
                $value1 = $obj_peso->calcular_objetivo($row['_id']);
                ?>
            <tr>
                <td class="plinner left"><?=!empty($row['_numero']) ? $row['_numero'] : $i; ?></td>

                <td class="plinner">
                    <?= nl2br(stripslashes($row['_nombre'])) ?>
                    <?php
                    $array = $obj->get_politicas($row['_id']);

                    if (count($array) > 0) {
                        echo "<br/><strong>Lineamientos: </strong>";
                        $j = 0;
                        foreach ($array as $cell) {
                            if ($j > 0) echo ", ";
                            echo 'L' . $cell['numero'];
                            ++$j;
                        }
                    }
                    ?>
                </td>

                <td class="plinner signal">
                    <div class="alarm-block">
                        <?php
                        if (!is_null($value2) && ($value2 > _YELLOW)) {
                            $obj_signal->get_alarm($value2);
                            $obj_signal->get_flecha($value2, $value1);

                            if (!is_null($value2))
                                echo "<br /> " . number_format($value2, 1, '.', '') . '%';
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
                                echo "<br /> " . number_format($value2, 1, '.', '') . '%';
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
                                echo "<br /> " . number_format($value2, 1, '.', '') . '%';
                        }
                        ?>
                    </div>
                </td>

                <td class="plinner"><?= textparse($observacion) ?></td>
            </tr>
            <?php } ?>
        </table>


        <?php
        $obj_inductor = new Tinductor($clink);

        $clink->data_seek($result);

        $i = 0;
        $j = 0;
        while ($row = $clink->fetch_array($result)) {
            $prs= $array_procesos_entity[$row['id_proceso']];
            if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if (empty($prs['id_entity']) && (!$row['if_send_down'] && $prs['tipo'] < $_SESSION['entity_tipo']))
                continue;
            if (empty($prs['id_entity']) && (!$row['if_send_up'] && $prs['tipo'] > $_SESSION['entity_tipo']))
                continue;
            ++$i;

            $obj_peso->SetYear($year);
            $obj_peso->SetMonth($month);

            $result_indi = $obj_peso->listar_inductores_ref_objetivo($row['_id'], true);
            $_cant_indi = $clink->num_rows($result_indi);
            $_cant_indi = !empty($_cant_indi) ? $_cant_indi : 0;

            if (empty($_cant_indi))
                continue;
            ?>
        <br /><br />
        <h1>OBJETIVO ESTRATÉGICO No.<?= !empty($row['_numero']) ? $row['_numero'] : $i ?></h1>
        <table cellpadding="0" cellspacing="0" width="800px">
            <thead>
                <thead>
                    <tr>
                        <th class="plhead left" rowspan="2" width="30">No</th>
                        <th class="plhead" rowspan="2">OBJETIVO DE TRABAJO</th>
                        <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                    </tr>
                    <tr>
                        <th class="plhead" width="50">B</th>
                        <th class="plhead" width="50">R</th>
                        <th class="plhead" width="50">M</th>
                        <th class="plhead">OBSERVACIONES</th>
                    </tr>
                </thead>
            </thead>

            <?php
            while ($row_indi = $clink->fetch_array($result_indi)) {
                ++$j;

                $id = $row_indi['_id'];
                $peso = $row_indi['peso'];

                $obj_inductor->SetYear($year);
                $obj_inductor->Set($id);

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                $obj_peso->init_calcular();
                $observacion = null;
                $value2 = $obj_peso->calcular_inductor($row_indi['_id'], $row_indi['_id_code']);

                $register = $obj_peso->get_array_register();
                $observacion = $register['observacion'];

                $obj_signal->get_month($_month, $_year);

                $obj_peso->SetYear($_year);
                $obj_peso->SetMonth($_month);

                $obj_peso->init_calcular();
                $value1 = $obj_peso->calcular_inductor($row_indi['_id'], $row_indi['_id_code']);
                ?>

            <tr>
                <td class="plinner left"><?= !empty($row_indi['_numero']) ? $row_indi['_numero'] : $j ?></td>
                <td class="plinner"><?= $obj_inductor->GetNombre() ?></td>

                <td class="plinner signal">
                    <div class="alarm-block">
                        <?php
                        if (!is_null($value2) && ($value2 > _YELLOW)) {
                            $obj_signal->get_alarm($value2);
                            $obj_signal->get_flecha($value2, $value1);

                            if (!is_null($value2))
                                echo "<br /> " . number_format($value2, 1, '.', '') . '%';
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
                                echo "<br /> " . number_format($value2, 1, '.', '') . '%';
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
                                echo "<br /> " . number_format($value2, 1, '.', '') . '%';
                        }
                        ?>
                    </div>
                </td>

                <td class="plinner"><?= $observacion ?></td>
            </tr>
            <?php } ?>
        </table>
        <?php } ?>

    </div>

    <?php require "inc/print_bottom.inc.php";?>