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
require_once "../php/class/proceso.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/document.class.php";
require_once "../php/class/register_nota.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/tipo_auditoria.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/code.class.php";

require_once "../php/class/traza.class.php";

$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$year = !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_auditoria = !is_null($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;
$id_tipo_lista = !is_null($_GET['id_tipo_lista']) && strlen($_GET['id_tipo_lista']) > 0 ? $_GET['id_tipo_lista'] : null;
$id_lista = !is_null($_GET['id_lista']) ? $_GET['id_lista'] : null;
$componente = !is_null($_GET['componente']) && strlen($_GET['componente']) > 0 ? $_GET['componente'] : null;
$id_capitulo = !is_null($_GET['id_capitulo']) && strlen($_GET['id_capitulo']) > 0 ? $_GET['id_capitulo'] : null;

$obj_lista = new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->Set($id_lista);
$id_lista_code = $obj_lista->get_id_code();
$nombre_lista = $obj_lista->GetNombre();

$obj_audit = new Tauditoria($clink);
$obj_audit->SetYear($year);
$obj_audit->SetIdAuditoria($id_auditoria);
$obj_audit->Set();
$id_auditoria_code = $obj_audit->get_id_code();
$id_tipo_auditoria = $obj_audit->GetIdTipo_auditoria();

$fecha_inicio = $obj_audit->GetFechaInicioPlan();
$fecha_fin = $obj_audit->GetFechaFinPlan();

$obj_tipo_audit = new Ttipo_auditoria($clink);
$obj_tipo_audit->SetYear($year);
$obj_tipo_audit->SetIdTipo_auditoria($id_tipo_auditoria);
$obj_tipo_audit->Set();
$nombre_auditoria = $obj_tipo_audit->GetNombre();
$nombre_auditoria .= "<br/>Inicia: " . odbc2date($fecha_inicio) . "  Finaliza: " . odbc2date($fecha_fin);

$obj = new Tlista_requisito($clink);
$obj->SetYear($year);

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();
$conectado= $obj_prs->GetConectado();

$obj_reg = new Tregister_nota($clink);
$obj_reg->SetYear($year);
$obj_reg->SetIdAuditoria($id_auditoria);
$obj_reg->SetIdProceso($id_proceso);
$obj_reg->SetIdLista($id_lista);

$array_files = null;

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DE APLICACIÓN DE GUÍA DE CONTROL", "Corresponde a periodo año: $year");
?>

<!DOCTYPE html>
<html>

<head>
    <?php require "inc/print_top.inc.php";?>

    <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

    <style type="text/css">
        body {
            background: #fff;
        }
        table {
            border: 1px solid #ccc;
            margin: 0px 10px 0px 10px;
            border: 1px solid #ccc;
            width: 100%;
            margin-top: 20px;
        }
        th,
        td {
            border: 1px solid #ccc;
            border: 1px solid black;
            padding: 4px;
        }
        th {
            color: #000000;
            font-weight: bolder;
            text-align: left;
        }
        td {
            text-align: center;
        }
        .title {
            font-weight: bold;
        }
        td.title {
            text-align: left;
        }
        .container {
            width: 100px;
            height: 100px;
        }
    </style>

    <script language="javascript" type="text/javascript">
        function drownpie(id, title, data) {
            // Create the chart
            $('#container_' + id).highcharts({
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
                }],
            });
        }
    </script>


    <div class="page center">
        <div class="container-fluid">
            <div class="title-header justify-content-center">
                RESUMEN DE LA APLICACIÓN DE GUÍA DE CONTROL <br/>
                <?=$nombre_auditoria?> <br/>
                <?=$nombre_lista?> <br/>
                AÑO <?= $year ?>
            </div>    
        </div>


        <?php
        for ($i = 0; $i < 6; $i++) {
            $total[$i] = 0;
            $no_procede[$i] = 0;
            $no_se_cumple[$i] = 0;
            $en_proceso[$i] = 0;
            $se_cumple[$i] = 0;
            $no_definido[$i] = 0;
        }

        $obj = new Tlista_requisito($clink);
        $obj->SetIdAuditoria($id_auditoria);
        $obj->SetIdLista($id_lista);
        $obj->SetIdProceso($id_proceso);
        $obj->SetYear($year);
        $obj->SetComponente($componente);
        $obj->SetIdCapitulo($id_capitulo);

        $result = $obj->listar();

        $i = 0;
        $j = 0;
        $nshow = 0;
        $array_ids= array();
        while ($row = $clink->fetch_array($result)) {
            if (isset($array_ids[$row['_id']]))
                continue;
            $array_ids[$row['_id']] = 1;
            ++$nshow;

            $icomponente = !is_null($row['componente']) ? (int)$row['componente'] : 0;
            ++$total[$icomponente];
            ++$total[0];

            $obj_reg->SetIdAuditoria($id_auditoria);
            $obj_reg->SetIdRequisito($row['_id']);
            $array = $obj_reg->getNota_reg();

            switch ($array['cumplimiento']) {
                case _NO_PROCEDE:
                    ++$no_procede[0];
                    ++$no_procede[$icomponente];
                    break;
                case _NO_SE_CUMPLE:
                    ++$no_se_cumple[0];
                    ++$no_se_cumple[$icomponente];
                    break;
                case _EN_PROCESO:
                    ++$en_proceso[0];
                    ++$en_proceso[$icomponente];
                    break;
                case _SE_CUMPLE:
                    ++$se_cumple[0];
                    ++$se_cumple[$icomponente];
                    break;
                default:
                    ++$no_definido[0];
                    ++$no_definido[$icomponente];
                    break;
            }
        }
        ?>

        <div class="row col-12 mb-4">
            <table>
                <thead>
                    <tr>
                        <th>COMPONENTE</th>
                        <th>TOTAL</th>
                        <?php if ($year < 2021) { ?>
                        <th>NO PROCEDE</th>
                        <?php } ?>
                        <th>NO SE CUMPLE</th>
                        <?php if ($year < 2021) { ?>
                        <th>EN PROCESO</th>
                        <?php } ?>
                        <th>SE CUMPLE</th>
                        <th>NO DEFINIDO</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="title">AMBIENTE DE CONTROL</td>
                        <td><?= $total[1] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[1] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[1] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[1] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[1] ?></td>
                        <td><?= $no_definido[1] ?></td>
                    </tr>
                    <tr>
                        <td class="title">GESTIÓN Y PREVENCIÓN DE RIESGO</td>
                        <td><?= $total[2] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[2] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[2] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[2] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[2] ?></td>
                        <td><?= $no_definido[2] ?></td>
                    </tr>
                    <tr>
                        <td class="title">ACTIVIDADES DE CONTROL</td>
                        <td><?= $total[3] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[3] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[3] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[3] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[3] ?></td>
                        <td><?= $no_definido[3] ?></td>
                    </tr>
                    <tr>
                        <td class="title">INFORMACIÓN Y COMUNICACIÓN</td>
                        <td><?= $total[4] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[4] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[4] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[4] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[4] ?></td>
                        <td><?= $no_definido[4] ?></td>
                    </tr>
                    <tr>
                        <td class="title">SUPERVISIÓN Y MONITOREO</td>
                        <td><?= $total[5] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[5] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[5] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[5] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[5] ?></td>
                        <td><?= $no_definido[5] ?></td>
                    </tr>
                    <tr>
                        <td class="title">TOTAL</td>
                        <td><?= $total[0] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[0] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[0] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[0] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[0] ?></td>
                        <td><?= $no_definido[0] ?></td>
                    </tr>
                </tbody>

            </table>
        </div>

        <div class="row col-12">
            <div id="container_0" class="col-6">
            </div>
            <div id="container_1" class="col-6">
            </div>
        </div>

        <div class="row col-12">
            <div id="container_2" class="col-6">
            </div>
            <div id="container_3" class="col-6">
            </div>
        </div>

        <div class="row col-12">
            <div id="container_4" class="col-6">
            </div>
            <div id="container_5" class="col-6">
            </div>
        </div>


        <script type="text/javascript">
            <?php for ($i = 0; $i < 6; $i++) { ?>
                data_<?= $i ?> = [
                    <?php if ($year < 2021) { ?>
                    ['En proceso', <?= $total[$i] ? ($en_proceso[$i]/$total[$i])*100 : 0 ?>],
                    ['No procede', <?= $total[$i] ? ($no_procede[$i]/$total[$i])*100 : 0 ?>],
                    <?php } ?>
                    {
                        name: 'Se cumple',
                        y: <?= $total[$i] ? ($se_cumple[$i]/$total[$i])*100 : 0 ?>,
                        sliced: true,
                        selected: true
                    },
                    ['No se cumple', <?= $total[$i] ? ($no_se_cumple[$i]/$total[$i])*100 : 0 ?>]
                ];
            <?php } ?>

            drownpie(0, "RESUMEN GENERAL", data_0);

            drownpie(1, "AMBIENTE DE CONTROL", data_1);

            drownpie(2, "GESTIÓN Y PREVENCIÓN DE RIESGO", data_2);

            drownpie(3, "ACTIVIDADES DE CONTROL", data_3);

            drownpie(4, "INFORMACIÓN Y COMUNICACIÓN", data_4);

            drownpie(5, "SUPERVISIÓN Y MONITOREO", data_5);
        </script>
    </div>

    <?php require "inc/print_bottom.inc.php";?>