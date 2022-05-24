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

require_once "../php/class/indicador.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/programa.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$obj_user = new Tusuario($clink);
$obj_indi = new Tindicador($clink);
$obj_prog= new Tprograma($clink);

$obj_signal = new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$tipo_prs = $obj_prs->GetTipo();
$proceso = $obj_prs->GetNombre();

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
$obj_traza->add("IMPRIMIR", "RESUMEN DE LOS PROGRAMAS", "Corresponde a periodo año: $year");
?>

<html>

<head>
    <title>RESUMEN DE LOS PROGRAMAS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div class="title-header">
            EVALUACIÓN DE LOS PROGRAMAS <br /><?= $meses_array[(int)$month] ?>/<?= $year ?>
        </div>
    </div>

    <div class="page center">

        <h1>PROGRAMAS</h1>
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30">No</th>
                    <th class="plhead" rowspan="2">PROGRAMA</th>
                    <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                </tr>
                <tr>
                    <th class="plhead signal">B</th>
                    <th class="plhead signal">R</th>
                    <th class="plhead signal">M</th>
                    <th class="plhead">OBSERVACIONES</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $obj = new Tprograma($clink);
                $obj->SetIdProceso($id_proceso);
                $obj->SetYear($year);

                $result = $obj->listar();

                $k_obj = 0;
                while ($row = $clink->fetch_array($result)) {
                    ++$k_obj;

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2 = $obj_peso->calcular_programa($row['_id']);

                    $array_register= $obj_peso->get_array_register();
                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];  

                    $obj_signal->get_month($_month, $_year);

                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1 = $obj_peso->calcular_programa($row['_id']);

                    $numero = !empty($row['_numero']) ? $row['_numero'] : $k_obj;
                ?>
                <tr>
                    <td class="plinner left"><?= $numero ?></td>
                    <td class="plinner"><?= stripslashes($row['nombre']) ?></td>

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

                    <td class="plinner">
                    <?php
                    $observacion= null;
                    if ($id_user && $item_reg == 'PROG') {
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

        $k_obj = 0;
        $j = 0;
        $k = 0;
        while ($row = $clink->fetch_array($result)) {
            ++$k_obj;
            $k = 0;
            $id_programa = $row['_id'];

            $obj_peso->SetYear($year);
            $with_programa_null = _PERSPECTIVA_NOT_NULL;
            $_result = $obj_peso->listar_indicadores_ref_programa($id_programa);
            $cant = $clink->num_rows($_result);
            if (empty($cant))
                continue;

            $numero = !empty($row['_numero']) ? $row['_numero'] : $k_obj;
        ?>
        <br /><br />

        <h1>PROGRAMA No. <?= $numero ?></h1>
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2">No.</th>
                    <th class="plhead" rowspan="2">INDICADOR</th>
                    <th class="plhead" rowspan="2">UM</th>
                    <th class="plhead bottom" colspan="5">CUMPLIMIENTO</th>
                </tr>
                <tr>
                    <th class="plhead">PLAN</th>
                    <th class="plhead">REAL</th>
                    <th class="plhead signal" colspan="2">%</th>
                    <th class="plhead" style="min-width: 300px;">OBSERVACIONES</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $j = 0;

                // Inicio de verificacion si contiene indicadores el programa
                $obj_prog->SetIdPrograma($id_programa);
                $obj_prog->SetYear($year);
                $obj_prog->SetMonth($month);

                $result_indi = $obj_prog->listar_indicadores(null);
                $_cant_indi = $clink->num_rows($result_indi);

                if (empty($_cant_indi))
                    continue;
                // fin de la comprobacion

                $obj_prog->Set($id_programa);
                $nombre = $obj_prog->GetNombre();

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);
                $obj_peso->SetMonth($day);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($year, $month);
                $obj_peso->compute_traze= true;
                $value2 = $obj_peso->calcular_programa($id_programa);
                $array_register = $obj_peso->get_array_register();

                $id_user = $array_register['id_usuario'];
                $item_reg = $array_register['signal'];

                $obj_signal->get_month($_month, $_year);

                $obj_peso->SetYear($_year);
                $obj_peso->SetMonth($_month);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($_year, $_month);
                $obj_peso->SetDay(null);
                $value1 = $obj_peso->calcular_programa($id_programa);
                ?>

                <?php
                $array_ids= array();
                while ($row_indi = $clink->fetch_array($result_indi)) {
                    $id= $row_indi['_id'];
                    if (!empty($array_ids[$id]))
                        continue;
                    $array_ids[$id]= 1;

                    ++$k;
                    $peso = $row_indi['peso'];
                    $obj_indi->SetYear($year);
                    $obj_indi->Set($id);

                    $trend = $obj_indi->GetTrend();
                    $ifcumulative = $obj_indi->GetIfCumulative();

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetIdPrograma($id_programa);
                    $obj_peso->SetIdIndicador($id);

                    $obj_peso->compute_traze= true;
                    $_array = $obj_peso->calcular_indicador($id);

                    $id_user_plan = $_array['id_user_plan'];
                    $email_plan = $obj_user->GetEmail($id_user_plan);

                    $id_user_real = $_array['id_user_real'];
                    $email_real = $obj_user->GetEmail($id_user_real);
                ?>
                <tr>
                    <td class="plinner left"><?= $k ?></td>

                    <td class="plinner" style="vertical-align: text-top!important;" valign="top">
                        <?php
                        echo $obj_indi->GetNombre();

                        if ($id_proceso != $row_indi['_id_proceso']) {
                            $obj_prs->Set($row_indi['_id_proceso']);
                            echo "<div class='comment' style='width: 400px;'>" . $obj_prs->GetNombre() . ', ' . $Ttipo_proceso_array[$obj_prs->GetTipo()] . '</div>';
                        }
                        ?>
                    </td>

                    <td class="plinner" style="text-align: center"><?= $unidad = $obj_indi->GetUnidad() ?></td>

                    <td class="plinner plan"><?= $_array['plan'] ?></td>
                    <td class="plinner real"><?= $_array['real'] ?></td>

                    <td class="plinner signal" colspan="2" style="min-width:40px; text-align:center">
                        <div class="alarm-block">
                            <div class="alarm-cicle small bg-<?=$_array['alarm']?>"></div>
                            <div class="alarm-arrow vertical small bg-<?=$_array['arrow']?>"><i
                                    class="fa <?=arrow_direction($_array['arrow'])?>"></i></div>

                            <br /><?php if (!is_null($_array['ratio'])) echo number_format($_array['ratio'], 1, '.', '') ?>
                        </div>

                    </td>
                    <td class="plinner"><?= nl2br($_array['observacion_real']) ?></td>
                </tr>
                <?php
                    }
                ?>
            <tbody>
        </table>
        <?php } ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>