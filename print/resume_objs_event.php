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
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/traza.class.php";

$force_user_process= true;

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'anual_plan';
$toshow = ($signal == 'anual_plan') ? 2 : 1;

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$tipo_plan= ($signal == 'anual_plan') ? _PLAN_TIPO_ACTIVIDADES_ANUAL : _PLAN_TIPO_ACTIVIDADES_MENSUAL;
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

$id_objetivo= !empty($_GET['id_objetivo']) ? $_GET['id_objetivo'] : null;
$acc= !empty($_SESSION['acc_planwork']) ? $_SESSION['acc_planwork'] : 0;

if(empty($tipo_plan))
    $tipo_plan= ($signal == 'anual_plan') ? _PLAN_TIPO_ACTIVIDADES_ANUAL : _PLAN_TIPO_ACTIVIDADES_MENSUAL;

switch($toshow) {
    case 0:
        $title= "INDIVIDUAL";
        break;
    case 1:
        $title= "MENSUAL";
        break;
    case 2:
        $title= "ANUAL";
        break;
}

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_peso= new Tpeso($clink);
$obj_peso->toshow= $toshow;
$obj_peso->SetIdProceso($id_proceso);
$obj_peso->SetYear($year);
$obj_peso->SetMonth($month);

$obj= new Tplantrab($clink);
$obj->SetIdUsuario(null);
$obj->SetIdProceso($id_proceso);
$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetIfEmpresarial($toshow);

$array_eventos_restricted= null;

if (!empty($id_objetivo)) {
    $array_eventos_restricted= array();
    $result= $obj_peso->listar_id_eventos_ref_inductor($id_objetivo);
    while ($row= $clink->fetch_array($result)) {
        $array_eventos_restricted[]= array('id'=>$row['id'], 'id_responsable'=>$row['id_responsable']);
    }
    unset($obj_peso);
}

$obj->array_eventos_restricted= $array_eventos_restricted;
$obj->set_print_reject($print_reject);
$obj->list_reg($toshow);

$obj_user= new Tusuario($clink);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DE ACTIVIDADES POR OBJETIVOS TRABAJO $title", "Corresponde a periodo mes/año: $month/$year");
?>

<html>

<head>
    <title>RESUMEN DE ACTIVIDADES POR OBJETIVOS TRABAJO</title>

    <?php require "inc/print_top.inc.php"; ?>

    <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

    <style type="text/css">
    body {
        background: #fff;
    }
    table {
        border: 1px solid grey;
        font-size: 11px;
        font-weight: normal;        
    }
    th,
    td {
        text-align: center;
        padding: 2px;
    }
    th {
        color: #000000;
        font-weight: bolder;
    }
    .title {
        font-weight: bold;
    }
    .acordion {
        width: 100%;
        height: max-content;
        position: relative;
        border: 1px solid darkgray;
        border-radius: 6px;
    }
    .container {
        position: relative;
        width: 60%;
        border: none;
        margin: 0px;
        padding: 8px 0px;
        float: left;
    }
    .table {
        width: 40%;
        border: none;
        float: right;
    }
    </style>    

    <script type="text/javascript">
    function drownpie(id, title, data) {
        // Create the chart
        $('#container' + id).highcharts({
            chart: {
                type: 'pie'
            },
            title: {
                text: false
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
            }],
        });
    }
    </script>

    <style type="text/css">
    h1.title {
        font-size: 1.3em;
        margin: 20px 0px 10px 0px;
        text-align: left !important;
    }
    </style>

    <div class="page center" style="width: 90%;">
        <h1>UNIDAD ORGANIZATIVA: <?=$proceso?></h1>
        <h1>RESUMEN DE ACTIVIDADES POR OBJETIVOS DE TRABAJO</h1>
        <h1>PLAN DE TRABAJO DE <?= $title ?></h1>
        <h1>AÑO: <?= $year ?></h1>
        <?php if (($empresarial < 2) && !empty($month)) { ?>
        <h1>MES DE <?=strtoupper($meses_array[(int)$month])?></h1>
        <?php } ?>
        <br />

        <?php
        $obj_objt= new Tinductor($clink);
        $obj_objt->SetIdProceso($id_proceso);
        $obj_objt->SetYear($year);

        $result_objt= $obj_objt->listar();

        $obj_peso= new Tpeso($clink);
        $obj_peso->toshow= $toshow;
        $obj_peso->if_teventos= $obj->if_teventos;
        $obj_peso->if_treg_evento= $obj->if_treg_evento;

        $obj_peso->SetIdProceso($id_proceso);
        $obj_peso->SetYear($year);
        $obj_peso->SetMonth($month);

        while ($row= $clink->fetch_array($result_objt)) {
            if (!empty($id_objetivo) && $id_objetivo != $row['_id'])
                continue;
            $obj_peso->listar_eventos_ref_inductor($row['_id'], false);

            $total = 0;
            $plan = 0;
            $cumplidas = 0;
            $incumplidas = 0;
            $noiniciadas = 0;
            $canceladas = 0;
            $rechazadas = 0;
            $externas = 0;

            if (count($obj_peso->array_eventos) == 0)
                continue;
            foreach ($obj_peso->array_eventos as $evento) {
                ++$total;
                if (array_key_exists($evento['id'], $obj->cumplidas_list))
                    ++$cumplidas;
                if (array_key_exists($evento['id'], $obj->incumplidas_list))
                    ++$incumplidas;
                if (array_key_exists($evento['id'], $obj->no_iniciadas_list))
                    ++$noiniciadas;
                if (array_key_exists($evento['id'], $obj->rechazadas_list))
                    ++$rechazadas;
                if (array_key_exists($evento['id'], $obj->canceladas_list))
                    ++$canceladas;
                if (array_key_exists($evento['id'], $obj->externas_list))
                    ++$externas;
            }

            if ($total > 0) {
                $_cumplidas= ((float)$cumplidas/$total)*100;
                $_incumplidas= ((float)$incumplidas/$total)*100;
                $_noiniciadas= ((float)$noiniciadas/$total)*100;
                $_rechazadas= ((float)$rechazadas/$total)*100;
                $_canceladas= ((float)$canceladas/$total)*100;
                $_externas= ((float)$externas/$total)*100;

                $value= ($total > 0) ? ($cumplidas/($total/* - $ext*/))*100 : null;
            } else {
                $_cumplidas= 0;
                $_incumplidos= 0;
                $_noiniciadas= 0;
                $_rechazadas= 0;
                $_canceladas= 0;
                $_externas= 0;

                $value= null;
            }

            $title= $row['_numero'].' '.$row['_nombre'];
            if ($total == 0)
                $percent= null;
            else {
                $percent= number_format(($cumplidas/$total)*100, 1).'%';
                $title.= " <strong>CUMPLIMIENTO:</strong>$percent";
            }
            ?>

        <div class="card card-default" style="margin-bottom: 15px; width: 100%;">
            <div class="card-header">
                <h4 class="panel-title" style="text-align: left!important">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                        <strong><?=$row['_numero']?></strong> <?=$row['_nombre']?>
                    </a>
                </h4>
            </div>

            <div class="card-body">
                <div class="row mt-2 p-3">
                    <div class="row col-md-12 col-lg-12">
                        <div id="container<?=$row['id']?>" class="col-md-6 col-lg-6" style="min-height:300px!important;">

                        </div>

                        <div class="col-md-6 col-lg-6">
                            <div class="col-12">
                                <strong>CUMPLIMIENTO:</strong> <?=$percent?>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th rowspan="2">TOTAL DE TAREAS <br />PLANIFICADAS</th>
                                        <th colspan="3">DE ELLAS</th>
                                        <th rowspan="2">EXTERNAS</th>
                                    </tr>
                                    <tr>
                                        <th>CUMPLIDAS</th>
                                        <th>INCUMPLIDAS</th>
                                        <th>SUSPENDIDAS <br />O<br /> POSPUESTAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="plinner left"><?=$total?></td>
                                        <td class="plinner"><?=$cumplidas?></td>
                                        <td class="plinner"><?=$incumplidas?></td>
                                        <td class="plinner"><?=$canceladas?></td>
                                        <td class="plinner"><?=$externas?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        <script type="text/javascript">
                        data_<?=$row['_id']?> = [
                            ['No iniciadas', <?=$_noiniciadas?>],
                            ['Rechazada', <?=$_rechazadas?>],
                            {
                                name: 'Cumplidas',
                                y: <?=$_cumplidas?>,
                                sliced: true,
                                selected: true
                            },
                            ['Incumplidas', <?=$_incumplidas?>],
                            ['Canceladas', <?=$_canceladas?>]
                        ];
                        drownpie(<?=$row['_id']?>, "<?=$title?>", data_<?=$row['_id']?>);
                        </script>
                    </div>

                    <div class="col-md-12 col-lg-12">
                        <table class="center none-border">
                            <tr>
                                <td class="plinner left top" colspan="2"><strong>OBSERVACIONES DEL CUMPLIMIENTO</strong>
                                </td>
                                <?php if ($config->responsable_planwork) { ?>
                                <td class="plinner top" width="200px"><strong>RESPONSABLE</strong></td>
                                <?php } else { ?>
                                <td class="plinner top" width="200px"><strong>QUIEN LAS ORIGINO</strong></td>
                                <?php } ?>
                                <td class="plinner top"><strong>CAUSAS</strong></td>
                            </tr>

                            <tr>
                                <td colspan="4" class="inner"><strong>TAREAS INCUMPLIDAS</strong></td>
                            </tr>
                            <?php
                            $i = 0;
                            reset($obj->incumplidas_list);
                            foreach ($obj->incumplidas_list as $evento) {
                                if (array_key_exists($evento['id'], $obj_peso->array_eventos) == false)
                                    continue;

                                if (isset($obj_event)) unset($obj_event);
                                $obj_event= new Tevento($clink);
                                $obj_event->SetIdEvento($evento['id']);
                                $obj_event->Set();

                                $obj_event->SetCumplimiento(null);
                                $obj_event->SetIdUsuario($obj_event->GetIdResponsable());
                                $obj_event->if_teventos= $obj->if_teventos;
                                $obj_event->if_treg_evento= $obj->if_treg_evento;
                                ?>
                            <tr>
                                <td class="plinner left" width="30px"><?= ++$i ?></td>
                                <td class="plinner">
                                    <?= textparse($obj_event->GetNombre()) ?>
                                    <br />
                                    <?= odbc2time_ampm($obj_event->GetFechaInicioPlan()) ?>
                                </td>

                                <td class="plinner">
                                    <?php
                                    $email = $obj_user->GetEmail($obj_event->get_id_user_asigna());
                                    echo textparse("{$email['nombre']}, {$email['cargo']}");
                                    ?>
                                </td>

                                <td class="plinner">
                                    <?php
                                    $array= array('id_responsable'=>$obj->GetIdResponsable(),
                                                'id_responsable_2'=>$obj_event->get_id_responsable_2(), 'responsable_2_reg_date'=>$obj_event->get_responsable_2_reg_date());

                                    $row_cump= $obj_event->getEvento_reg($evento['id'], $array);
                                    echo textparse($row_cump['observacion']);
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>

                            <tr>
                                <td colspan="4" class="inner">
                                    <strong>SUSPENDIDAS O POSPUESTAS</strong>
                                </td>
                            </tr>
                            <?php
                            $i = 0;
                            reset($obj->canceladas_list);
                            foreach ($obj->canceladas_list as $evento) {
                                if (array_key_exists($evento['id'], $obj_peso->array_eventos) == false)
                                    continue;

                                if (isset($obj_event)) unset($obj_event);
                                $obj_event= new Tevento($clink);
                                $obj_event->SetIdEvento($evento['id']);
                                $obj_event->Set();

                                $obj_event->SetCumplimiento(null);
                                $obj_event->SetIdUsuario($obj_event->GetIdResponsable());
                                $obj_event->if_teventos= $obj->if_teventos;
                                $obj_event->if_treg_evento= $obj->if_treg_evento;
                                ?>
                            <tr>
                                <td class="plinner left" width="30px"><?= ++$i ?></td>
                                <td class="plinner">
                                    <?= textparse($obj_event->GetNombre()) ?>
                                    <br />
                                    <?= odbc2time_ampm($obj_event->GetFechaInicioPlan()) ?>
                                </td>

                                <?php if ($config->responsable_planwork) { ?>
                                <td class="plinner">
                                    <?php
                                    $email = $obj_user->GetEmail($obj_event->GetIdResponsable());
                                    $nombre= $email['nombre'];
                                    $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                                    echo $nombre;
                                    ?>
                                </td>

                                <?php } else { ?>

                                <td class="plinner">
                                    <?php
                                    $email = $obj_user->GetEmail($obj_event->get_id_user_asigna());
                                    $nombre= $email['nombre'];
                                    $nombre.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                                    echo $nombre;
                                    ?>
                                </td>
                                <?php } ?>
                                <td class="plinner">
                                    <?php
                                    $array= array('id_responsable'=>$obj_event->GetIdResponsable(),
                                                'id_responsable_2'=>$obj_event->get_id_responsable_2(), 'responsable_2_reg_date'=>$obj_event->get_responsable_2_reg_date());

                                    $row_cump= $obj_event->getEvento_reg($evento['id'], $array);
                                    echo textparse($row_cump['observacion']);
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>


                            <tr>
                                <td colspan="4" class="inner"><strong>EXTERNAS</strong></td>
                            </tr>

                            <?php
                            $i = 0;
                            reset($obj->externas_list);
                            foreach ($obj->externas_list as $evento) {
                                if (array_key_exists($evento['id'], $obj_peso->array_eventos) == false)
                                    continue;

                                if (isset($obj_event)) unset($obj_event);
                                $obj_event= new Tevento($clink);
                                $obj_event->SetIdEvento($evento['id']);
                                $obj_event->Set();

                                $obj_event->SetCumplimiento(null);
                                $obj_event->SetIdUsuario($obj_event->GetIdResponsable());
                                $obj_event->if_teventos= $obj->if_teventos;
                                $obj_event->if_treg_evento= $obj->if_treg_evento;
                                ?>
                            <tr>
                                <td class="plinner left" width="30px"><?= ++$i ?></td>
                                <td class="plinner">
                                    <?= textparse($obj_event->GetNombre()) ?>
                                    <br />
                                    <?= odbc2time_ampm($obj_event->GetFechaInicioPlan()) ?>
                                </td>

                                <td class="plinner">
                                    <?php
                                    $email = $obj_user->GetEmail($obj_event->get_id_user_asigna());
                                    $name= textparse($email['nombre']);
                                    $name.= !empty($email['cargo']) ? textparse($email['cargo']) : null;
                                    echo $name;
                                    ?>
                                </td>

                                <td class="plinner">
                                    <?php
                                    $array= array('id_responsable'=>$obj->GetIdResponsable(),
                                                'id_responsable_2'=>$obj_event->get_id_responsable_2(), 'responsable_2_reg_date'=>$obj_event->get_responsable_2_reg_date());

                                    $row_cump= $obj_event->getEvento_reg($evento['id'], $array);
                                    echo textparse($row_cump['observacion']);
                                    ?>
                                </td>
                            </tr>
                            <?php } ?>

                            <tr>
                                <td class="none-border top"></td>
                                <td class="none-border top"></td>
                                <td colspan="2" class="none-border top"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div> <!-- panel-body -->
        </div> <!-- panel -->
        <?php } ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>