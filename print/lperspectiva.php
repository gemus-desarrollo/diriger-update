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

require_once "../php/class/objetivo.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_user= new Tusuario($clink);

$_id_proceso= $id_proceso;

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($_id_proceso);
$obj_prs->Set();
$tipo_prs= $obj_prs->GetTipo();
$proceso= $obj_prs->GetNombre();

$array_perspectivas= null;

$obj_peso= new Tpeso_calculo($clink);

if (!empty($id_proceso_code)) {
    $obj_peso->SetIdProceso($id_proceso);
    $obj_peso->set_id_proceso_code($id_proceso_code);
}

$obj_prs= new Tproceso_item($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$array_indicadores= $obj_prs->listar_indicadores();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RELACIÓN DE PERSPECTIVAS", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>RELACIÓN DE PERSPECTIVAS</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header"> ESTADO DE LA DIRECCIÓN POR PERSPECTIVAS. MES DE <?= strtoupper($meses_array[(int)$month]) ?> AÑO <?= $year ?></div>
        </div>

        <div class="page center">

            <h1>PERSPECTIVAS</h1>
            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="plhead left" rowspan="2" width="30">No</th>
                        <th class="plhead" rowspan="2">PERSPECTIVA</th>
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
                    $obj_prs = new Tproceso($clink);
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

                    $obj_peso->SetYear($year);
                    $obj_peso->set_matrix();

                    foreach ($array_cascade_up as $prs) {
                        $id_proceso = $prs['id'];
                        if (isset($obj_persp)) unset($obj_persp);
                        $obj_persp = new Tperspectiva($clink);

                        $obj_persp->SetYear($year);
                        $obj_persp->SetIdProceso($id_proceso);
                        $result_persp = $obj_persp->listar();
                        $_cant_persp = $obj_persp->GetCantidad();

                        if (empty($_cant_persp))
                            continue;

                        while ($row = $clink->fetch_array($result_persp)) {
                            ++$i;

                            $array_perspectivas[] = array('id' => $row['_id'], 'numero' => $row['_numero'], 'nombre' => $row['_nombre']);
                            $obj_peso->SetYear($year);
                            $obj_peso->SetMonth($month);

                            $obj_peso->init_calcular();
                            $observacion = null;
                            $obj_peso->SetYearMonth($year, $month);
                            $obj_peso->compute_traze= true;
                            $value2 = $obj_peso->calcular_perspectiva($row['_id'], $array_procesos_down_entity);

                            $register = $obj_peso->get_array_register();
                            $observacion = $register['observacion'];

                            $obj_signal->get_month($_month, $_year);

                            $obj_peso->SetYear($_year);
                            $obj_peso->SetMonth($_month);
                            $obj_peso->SetDay(null);

                            $obj_peso->init_calcular();
                            $obj_peso->SetYearMonth($_year, $_month);
                            $value1 = $obj_peso->calcular_perspectiva($row['_id'], $array_procesos_down_entity);
                            ?>

                            <tr>
                                <td class="plinner left"><?= !empty($row['_numero']) ? $row['_numero'] : $i ?></td>

                                <td class="plinner">
                                    <?php
                                    echo nl2br(stripslashes($row['_nombre']));
                                    if ($prs['id'] != $_id_proceso)
                                        echo "<br /><span class='comment'>" . $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']] . '</span>';
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

                                <td class="plinner"><?= nl2br($observacion) ?></td>
                            </tr>
                    <?php } } ?>
                </tbody>

            </table>

            <?php
            $obj_indicador = new Tindicador($clink);
            $obj_persp = new Tperspectiva($clink);

            $i = 0;
            $j = 0;
            foreach ($array_perspectivas as $row) {
                ++$i;

                $obj_persp->SetYear($year);
                $obj_persp->SetMonth($month);
                $obj_persp->SetIdPerspectiva($row['id']);
                $obj_persp->SetIdProceso(null);

                $result_indi = $obj_persp->listar_indicadores();
                $_cant_indi = $clink->num_rows($result_indi);
                $_cant_indi = !empty($_cant_indi) ? $_cant_indi : 0;

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);

                if (empty($_cant_indi)) 
                    continue;
                ?>
                <br /><br />
                <h1>PERSPECTIVA No.<?php echo!empty($row['numero']) ? $row['numero'] : $i; ?> <?php echo $row['nombre'] ?></h1>
                <table cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="plhead left" rowspan="3">No.</th>
                            <th class="plhead" rowspan="3">INDICADOR</th>
                            <th class="plhead" rowspan="3">UM</th>
                            <th class="plhead bottom" colspan="8">CUMPLIMIENTO</th>
                        </tr>
                        <tr>
                            <th class="plhead bottom" colspan="3">MES</th>
                            <th class="plhead bottom" colspan="4">ACUMULADO</th>
                            <th class="plhead" rowspan="2" style="vertical-align: bottom">OBSERVACIONES</th>
                        </tr>
                        <tr>
                            <th class="plhead">PLAN</th>
                            <th class="plhead">REAL</th>
                            <th class="plhead">%</th>
                            <th class="plhead">PLAN</th>
                            <th class="plhead">REAL</th>
                            <th class="plhead" colspan="2">%</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        while ($row_indi = $clink->fetch_array($result_indi)) {
                            $prs= $array_procesos_entity[$row_indi['_id_proceso']];
                            $id_entity= !empty($prs['id_entity']) ? $prs['id_entity'] : $prs['id'];
                            $if_entity= $id_entity == $_SESSION['id_entity'] ? 1 : 0;  

                            if ($id_entity != $_SESSION['id_entity']) {
                                if (!array_key_exists($row_indi['_id'], $array_indicadores))
                                    continue;
                            }                            
                            
                            ++$j;

                            $id = $row_indi['_id'];
                            $peso = $row_indi['peso'];
                            $obj_indicador->SetYear($year);
                            $obj_indicador->SetIdProceso(null);
                            $obj_indicador->Set($id);
                            $nombre = $obj_indicador->GetNombre();

                            $trend = $obj_indicador->GetTrend();
                            $ifcumulative = $obj_indicador->GetIfCumulative();
                            $unidad = $obj_indicador->GetUnidad();

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
                                <td class="plinner left"><?= $j ?></td>
                                <td class="plinner"><?= $obj_indicador->GetNombre() ?></td>
                                <td class="plinner"><?= $unidad ?></td>
                                <td class="plinner real"><?= $_array['plan'] ?></td>
                                <td class="plinner real"><?= $_array['real'] ?></td>

                                <td class="plinner signal">
                                    <div class="alarm-block">
                                        <?php if ($_array['alarm'] != 'blank') { ?>
                                            <div class="alarm-cicle small bg-<?=$_array['alarm']?>"></div>
                                            <div class="alarm-arrow vertical small bg-<?=$_array['arrow']?>"><i class="fa <?=arrow_direction($_array['arrow'])?>"></i></div>

                                            <br /><?php if (!is_null($_array['ratio'])) echo number_format($_array['ratio'], 1, '.', '') ?>
                                        <?php } ?>
                                    </div>
                                </td>

                                <td class="plinner real"><?= $_array['acumulado_plan'] ?></td>
                                <td class="plinner real"><?= $_array['acumulado_real'] ?></td>

                                <td class="plinner signal" colspan="2">
                                    <div class="alarm-block">
                                        <?php if ($ifcumulative && $_array['alarm_cumulative'] != 'blank') { ?>
                                            <div class="alarm-cicle small bg-<?=$_array['alarm_cumulative']?>"></div>
                                            <div class="alarm-arrow vertical small bg-<?=$_array['arrow_cumulative']?>"><i class="fa <?=arrow_direction($_array['arrow_cumulative'])?>"></i></div>

                                            <br /><?php if (!is_null($_array['ratio_cumulative'])) echo number_format($_array['ratio_cumulative'], 1, '.', '') ?>
                                        <?php } ?>
                                    </div>
                                </td>

                                <td class="plinner right"><?= nl2br($_array['observacion_real']) ?></td>
                            </tr>
                        <?php } ?>
                    </body>
                </table>
            <?php } ?>

        </div>

    <?php require "inc/print_bottom.inc.php";?>
