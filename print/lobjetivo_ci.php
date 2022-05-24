<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug'] = 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/objetivo_ci.class.php";

require_once "../php/class/peso.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/plantrab.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$id_objetivo= !empty($_GET['id_objetivo']) ? $_GET['id_objetivo'] : null;

$obj_signal = new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$_id_proceso = $id_proceso;

$obj = new Tobjetivo_ci($clink);
$obj->SetIdProceso($_id_proceso);
$obj->SetYear($year);
$obj->SetMonth($month);
$result_obj = $obj->listar();

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($_id_proceso);
$obj_prs->Set();
$tipo_prs = $obj_prs->GetTipo();
$proceso = $obj_prs->GetNombre();

$array_objetivos = null;

$obj_user = new Tusuario($clink);

$obj_task = new Ttarea($clink);
$obj_task->SetYear($year);
$obj_task->SetMonth($month);

$obj_evento= new Tevento($clink);
$obj_evento->SetYear($year);

function array_concat(&$array1, $array2) {
    foreach ($array2 as $array) {
        $array1[$array['id']]= $array['id'];
    }
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RELACIÓN DE OBJETIVOS DE CONTROL", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>RELACIÓN DE OBJETIVOS DE CONTROL</title>

        <?php require "inc/print_top.inc.php";?>

        <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
        <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
        <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

        <script type="text/javascript">
           function drownpie(id, title, data) {
                // Create the chart
                $('#container'+id).highcharts({
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: title
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                format: '{point.name}: {point.y:.1f}%'
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px; text-align: left;">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
                    },

                    series: [{
                        type: 'pie',
                        name: 'Brands',
                        colorByPoint: true,
                        data: data
                    }]
                });
           }
        </script>

        <style>
            .graph-container {
                margin: 0px;
                min-height: 400px;
            }
        </style>

        <div class="container-fluid center">
            <div class="title-header">CUMPLIMIENTO DE LOS OBJETIVOS DE CONTROL<br/>
                <?= $year?>
            </div>
        </div>

        <div class="page center">

           <div class="panel-group" id="accordion">
                <?php
                $obj= new Tobjetivo_ci($clink);
                $obj->SetIdProceso($id_proceso);
                $obj->set_id_proceso_code($id_proceso_code);
                $obj->SetYear($year);
                $obj->SetMonth($month);
                $result_obj= empty($id_objetivo) ? $obj->listar() : $obj->listar_restrict_id($id_objetivo);

                $array_tareas= null;
                $array_objetivos= array();
                while ($row= $clink->fetch_array($result_obj)) {
                    unset($obj);
                    $obj= new Tobjetivo_ci($clink);
                    $obj->SetIdProceso($id_proceso);
                    $obj->set_id_proceso_code($id_proceso_code);
                    $obj->SetYear($year);
                    $obj->SetMonth($month);

                    $id_objetivo= $row['_id'];
                    $obj->SetIdObjetivo($row['_id']);
                    $obj->set_id_objetivo_code($row['_id_code']);

                    $value= $obj->calcular_objetivo_ci();
                    $array_objetivos[$id_objetivo]= array('id'=>$id_objetivo, 'numero'=>$row['numero'], 'value'=>$value,
                                                          'objetivo'=>$row['objetivo'], 'cant'=>$obj->total, 'array'=>$obj->array_tareas);

                    if ($obj->total > 0) {
                        $_cumplidas= ((float)$obj->cumplidas/$obj->total)*100;
                        $_incumplidas= ((float)$obj->incumplidas/$obj->total)*100;
                        $_noiniciadas= ((float)$obj->no_iniciadas/$obj->total)*100;
                        $_rechazadas= ((float)$obj->rechazadas/$obj->total)*100;
                        $_canceladas= ((float)$obj->canceladas/$obj->total)*100;

                        $value= ($obj->total > 0) ? ($obj->cumplidas/($obj->total/* - $ext*/))*100 : null;
                    } else {
                        $_cumplidas= 0;
                        $_incumplidos= 0;
                        $_noiniciadas= 0;
                        $_rechazadas= 0;
                        $_canceladas= 0;

                        $value= null;
                    }
                ?>
                    <div class="card card-default" style="margin-bottom: 15px; width: 100%;">
                        <div class="accordion">
                            <div class="row">
                                <div id="container<?=$row['_id']?>" class="col-md-6 col-lg-6 graph-container">

                                </div>

                                <div class="col-md-6 col-lg-6">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th rowspan="2">TOTAL DE TAREAS <br/>PLANIFICADAS</th>
                                                <th colspan="3">DE ELLAS</th>
                                            </tr>
                                            <tr>
                                                <th>CUMPLIDAS</th>
                                                <th>INCUMPLIDAS</th>
                                                <th>SUSPENDIDAS <br/>O<br/> POSPUESTAS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="plinner left"><?=$obj->total?></td>
                                                <td class="plinner"><?=$obj->cumplidas?></td>
                                                <td class="plinner"><?=$obj->incumplidas?></td>
                                                <td class="plinner"><?=$obj->canceladas?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <script type="text/javascript">
                                    data_<?=$row['_id']?>= [
                                        ['No iniciadas', <?=$_noiniciadas?>],
                                        ['Rechazada',    <?=$_rechazadas?>],
                                        {
                                            name: 'Cumplidas',
                                            y: <?=$_cumplidas?>,
                                            sliced: true,
                                            selected: true
                                        },
                                        ['Incumplidas',   <?=$_incumplidas?>],
                                        ['Canceladas',   <?=$_canceladas?>]
                                    ];
                                    drownpie(<?=$row['_id']?>, "<?="{$row['_numero']} {$row['_nombre']}"?>", data_<?=$row['_id']?>);
                              </script>
                            <?php } ?>
                        </div>

                       <br/>
                        <table cellpadding="0" cellspacing="0" width="900px">
                            <thead>
                                <tr>
                                    <th width="50" rowspan="2" class="plhead left">No.</th>
                                    <th rowspan="2" class="plhead">Objetivos</th>
                                    <th colspan="3" class="plhead">Cumplimiento</th>
                                    <th rowspan="2" class="plhead">Observaciones</th>
                                </tr>
                                <tr>
                                    <th class="plhead" width="50">B</th>
                                    <th class="plhead" width="50">R</th>
                                    <th class="plhead" width="50">M</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                            foreach ($array_objetivos as $row) {
                                ++$k_obj;
                                $numero = !empty($row['numero']) ? $row['numero'] : $k_obj;
                                $value= $row['value'];
                                ?>

                                <tr>
                                    <td class="plinner left"><?= $numero ?></td>
                                    <td class="plinner"><?= $row['objetivo'] ?></td>
                                    <td class="plinner signal">
                                        <?php
                                        if (!is_null($value) && ($value > _YELLOW)) {
                                            $obj_signal->get_alarm($value, null, null);
                                            if (!is_null($value))
                                                echo "<br /> " . number_format($value, 1, '.', '') . '%';
                                        }
                                        ?>
                                    </td>
                                    <td class="plinner signal">
                                        <?php
                                        if (!is_null($value) && ($value > _ORANGE && $value <= _YELLOW)) {
                                            $obj_signal->get_alarm($value, null, null);
                                            if (!is_null($value))
                                                echo "<br /> " . number_format($value, 1, '.', '') . '%';
                                        }
                                        ?>
                                    </td>
                                    <td class="plinner signal">
                                        <?php
                                        if (!is_null($value) && $value <= _ORANGE) {
                                            $obj_signal->get_alarm($value, null, null);
                                            if (!is_null($value))
                                                echo "<br /> " . number_format($value, 1, '.', '') . '%';
                                        }
                                        ?>
                                    </td>
                                    <td class="plinner"><?= $row['observacion'] ?></td>
                                </tr>

                                <?php } ?>
                            </tbody>
                        </table>

                        <?php
                        $k_obj= 0;
                        $clink->data_seek($result_obj);
                        while ($row= $clink->fetch_array($result_obj)) {
                            unset($obj);
                            $obj= new Tobjetivo_ci($clink);
                            $obj->SetIdProceso($id_proceso);
                            $obj->set_id_proceso_code($id_proceso_code);
                            $obj->SetYear($year);
                            $obj->SetMonth($month);

                            $id_objetivo= $row['_id'];
                            $obj->SetIdObjetivo($row['_id']);
                            $obj->set_id_objetivo_code($row['_id_code']);

                            $value= $obj->calcular_objetivo_ci();
                            ++$k_obj;
                            $numero = !empty($row['numero']) ? $row['numero'] : $k_obj;

                            if (!empty($obj->total)) {
                                ?>
                                <br />
                                <h1>OBJETIVO No.<?= $numero ?></h1>
                                <table cellpadding="0" cellspacing="0" width="900">
                                    <thead>
                                        <tr>
                                            <th class="plhead left" width="40px">No.</th>
                                            <th class="plhead">TAREA</th>
                                            <th class="plhead" width="200px">EJECUTANTES</th>
                                            <th class="plhead" width="200px">RESPONSABLE</th>
                                            <th class="plhead">FECHA</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td colspan="5" class="plinner left"><strong>TAREAS INCUMPLIDAS</strong></td>
                                        </tr>
                                        <?php if (!$obj->incumplidas) { ?>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <?php } ?>

                                        <?php
                                        foreach ($obj->incumplidas_list as $array) {
                                            $obj_evento->SetIdEvento($array['id_evento']);
                                            $obj_evento->Set();
                                        ?>
                                            <tr>
                                                <td class="plinner left">
                                                    <?= ++$k_task ?>
                                                </td>
                                                <td class="plinner">
                                                    <?=$obj_evento->GetNombre()?>
                                                </td>

                                                <td class="plinner">
                                                    <?php
                                                    $string = $obj_evento->get_participantes($array['id_evento']);
                                                    echo $string;
                                                    ?>
                                                </td>

                                                <td class="plinner">
                                                    <?php
                                                    $responsable = $obj_user->GetEmail($obj_evento->GetIdResponsable());
                                                    echo "{$responsable['nombre']}<br> {$responsable['cargo']}";
                                                    ?>
                                                </td>
                                                <td class="plinner">
                                                    <?= odbc2date($obj_evento->GetFechaInicioPlan()) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        <tr>
                                            <td colspan="5" class="plinner left"><strong>TAREAS SUSPENDIDAS O POSTPUESTAS</strong></td>
                                        </tr>
                                        <?php if (!$obj->canceladas) { ?>
                                        <tr>
                                            <td>&nbsp;</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <?php } ?>

                                        <?php
                                        foreach ($obj->canceladas_list as $array) {
                                            $obj_evento->SetIdEvento($array['id_evento']);
                                            $obj_evento->Set();
                                        ?>
                                            <tr>
                                                <td class="plinner left">
                                                    <?= ++$k_task ?>
                                                </td>
                                                <td class="plinner">
                                                    <?=$obj_evento->GetNombre()?>
                                                </td>

                                                <td class="plinner">
                                                    <?php
                                                    $string = $obj_evento->get_participantes($array['id_evento']);
                                                    echo $string;
                                                    ?>
                                                </td>

                                                <td class="plinner">
                                                    <?php
                                                    $responsable = $obj_user->GetEmail($obj_evento->GetIdResponsable());
                                                    echo "{$responsable['nombre']}<br> {$responsable['cargo']}";
                                                    ?>
                                                </td>
                                                <td class="plinner">
                                                    <?= odbc2date($obj_evento->GetFechaInicioPlan()) ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php }
                        } ?>
                    </div>
                </div>

    <?php require "inc/print_bottom.inc.php";?>
