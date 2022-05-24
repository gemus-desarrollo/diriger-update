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
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/plan_ci.class.php";
require_once "../php/class/nota.class.php";

require_once "../form/class/nota.signal.class.php";

require_once "../php/class/traza.class.php";

$_SESSION['debug']= 'no';
$signal= ' ';

require_once "../php/inc_escenario_init.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_proceso'];
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

if (empty($month) || $month == -1 || $month == 13)
    $month= 12;
$show_all_notes= !empty($_GET['show_all_notes']) ? 1 : 0;

$fin= $actual_year + 2;
if ($year == $actual_year)
    $end_month= $actual_month;
else
    $end_month= 12;

$obj= new Tnota($clink);
$obj_signal= new Tnota_signals($clink);

$obj_user= new Tusuario($clink);

$obj->SetDay(NULL);
$obj->SetMonth($month);
$obj->SetYear($year);

$date_cut= $year.'-'.str_pad($month,'0',2,STR_PAD_LEFT).'-'.str_pad($day,'0',2,STR_PAD_LEFT);

$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;


$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$tipo_prs= $obj_prs->GetTipo();
$proceso= $obj_prs->GetNombre();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN OBJETIVOS DE CONTROL INTERNO", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>LISTADO DE RIESGOS</title>

        <?php require "inc/print_top.inc.php";?>

        <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>

        <style>
            body {
                background: white;
            }
            .prs-text {
                color: #828282;
                font-weight: bold;
            }
        </style>

        <div class="page center">
            <div class="container-fluid center">
                <h1>
                    <p>RESUMEN OBJETIVOS DE CONTROL INTERNO</p>
                    <?= strtoupper($meses_array[(int)$month]) ?><br/>
                    <p>A&Ntilde;O <?= $year ?></p>
                </h1>
            </div>


            <?php
            $cant_show= 0;
            $cant_print_reject= 0;

            $obj_event= new Tevento($clink);
            $obj->SetTipo(null);
            $obj->SetIdProceso(null);
            $obj->SetYear($year);
            $obj->SetMonth(null);
            $obj->SetIdAuditoria($id_auditoria);
            $obj->SetChkApply(true);
            
            $obj_prs= new Tproceso($clink);
            $obj_prs->SetYear($year);
            $obj_prs->SetIdProceso($id_proceso);

            $obj_prs->get_procesos_down(null, null, null, true);
            $array_procesos_down= $obj_prs->array_cascade_down;

            if (isset($array)) unset($array);
            $array= array();
            foreach ($array_procesos_down as $key => $prs)
                $array[]= $key;

            $obj->set_show_all_notes($show_all_notes);
            $result= $obj->listar($noconf, $mej, $observ, true, $array);

            if (count($array) == 1) {
                $obj->SetIdProceso($id_proceso);
                $obj->set_id_proceso_code($id_proceso_code);
            }

            $ranking= $obj->list_ranking($result, $config->automatic_note);

            for ($i= 1; $i < 13; $i++)
                $array_month[$i]= array(0, 0);

            $total= 0;
            $cerradas= 0;
            foreach ($ranking as $nota) {
                ++$total;
                $mm= (int)date('m', strtotime($nota['fecha']));
                $array_month[$mm][0]+= 1;
                if ($nota['estado'] == _CERRADA) {
                    ++$cerradas;
                    $array_month[$mm][1]+=1;
                }
            }
            ?>

            <?php
            if ($noconf && $observ && $mej)
                $title= "Notas de hallazgos";
            else {
                $i= 0;
                if ($noconf) {
                    ++$i;
                    $title= "No Conformidades";
                }
                if ($observ) {
                    $title.= $i ? ", " : null;
                    ++$i;
                    $title.= "Observaciones ";
                }
                if ($mej) {
                    $title.= $i ? ", " : null;
                    $title.= "Notas de mejoras";
                }
            }
            ?>

            <script type="text/javascript">
                $(function () {
                    $('#container').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: '<?=$title?>'
                        },
                        xAxis: {
                            categories: [
                                'Ene',
                                'Feb',
                                'Mar',
                                'Abr',
                                'May',
                                'Jun',
                                'Jul',
                                'Ago',
                                'Sep',
                                'Oct',
                                'Nov',
                                'Dic'
                            ]
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Cantidad'
                            }
                        },
                        tooltip: {
                            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            }
                        },
                        series: [{
                            name: 'Detectadas',
                            data: [
                                <?php
                                for ($i= 1; $i < 13; $i++) {
                                    echo $i > 1 ? "," : null;
                                    echo $array_month[$i][0];
                                }
                                ?>
                            ]
                        }, {
                            name: 'Cerradas',
                            data: [
                                <?php
                                for ($i= 1; $i < 13; $i++) {
                                    echo $i > 1 ? "," : null;
                                    echo $array_month[$i][1];
                                }
                                ?>
                            ]
                        }]
                    });
                });
            </script>

            <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

        </div>

    <?php require "inc/print_bottom.inc.php";?>



