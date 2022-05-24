<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2018
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/registro.class.php";

require_once "../php/class/connect.class.php";
require_once "../php/graphic.interface.php";

require_once "../php/class/traza.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id= !empty($_GET['id']) ? $_GET['id'] : 0;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;
$item= !empty($_GET['item']) ? $_GET['item'] : 'indicador';

$radio_cumulative= !is_null($_GET['radio_cumulative']) ? $_GET['radio_cumulative'] : 1;
$radio_formulated= !is_null($_GET['radio_formulated']) ? $_GET['radio_formulated'] : 1;

if (!empty($_GET['id_indicador'])) {
    $id= $id_indicador;
    $item= 'indicador';
}

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : ($item == 'indicador' ? $_SESSION['current_month'] : 1);

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

if (empty($year))
    $year= date('Y');
if (empty($month))
    $month= date('m');
if (empty($day))
    $day= $item == 'indicador' ? date('d') : $lastday;

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $year;
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $year;

if ($item == 'indicador') {
    if (!empty($_GET['day']))
        $day= $_GET['day'];
    if (empty($day)) {
        if ($month != $actual_month || $year != $actual_year) {
            if ($month == $actual_month && $year == $actual_year)
                $day= $actual_day;
            else $day= $lastday;
        }
    }

    $lastmonth= $year >= date('Y') ? date('m') : 12;
} else {

    $lastmonth= 12;
}

switch ($item) {
    case 'politica':
        $obj = new Tpolitica($clink);
        break;
    case 'objetivo':
        $obj = new Tobjetivo($clink);
        break;
    case 'objetivo_ci':
        $obj = new Tobjetivo($clink);
        break;
    case 'perspectiva':
        $obj = new Tperspectiva($clink);
        break;
    case 'programa':
        $obj = new Tprograma($clink);
        break;
    case 'inductor':
        $obj = new Tregistro($clink);
        break;
    case 'indicador':
        $obj = new Tindicador($clink);
        break;
    case 'proceso':
        $obj = new Tproceso($clink);
        break;
    case 'empresa':
        $obj = new Tproceso($clink);
        break;
    default:
        $obj = new Tregistro($clink);
}

$obj->SetYear($year);
if (!empty($id)) $obj->Set($id);

$periodicidad= 0;
$carga= null;
$trend= null;
$cumulative= false;
$formulated= false;
$unidad= null;

$_inicio= date('Y') - 5;
$_fin= date('Y') + 2;

if ($item == 'indicador') {
    $obj->SetIdIndicador($id);

    $nombre= $obj->GetNombre();
    $_periodicidad= $obj->GetPeriodicidad();
    $carga= $obj->GetCarga();
    $trend= $obj->GetTrend();
    $id_code= $obj->get_id_indicador_code();
    $cumulative= $obj->GetIfCumulative();
    $formulated= $obj->GetIfFormulated();
    $unidad= $obj->GetUnidad();

    $_inicio= $obj->GetInicio();
    $_fin= $obj->GetFin();
}

if ((!empty($item) && !empty($id)) && $item != 'indicador') {
    $obj->SetYear($year);
    $obj->Set($id);

    $nombre= $obj->GetNombre();
    $unidad= '%';
    $_periodicidad= 'M';

    $_inicio= $obj->GetInicio();
    $_fin= $obj->GetFin();
}

$periodicidad= !empty($_GET['periodicidad']) ? $_GET['periodicidad'] : $_periodicidad;
if (empty($periodicidad))
    $periodicidad= 'M';

$cumulative= !empty($cumulative) ? 1 : 0;
$formulated= !empty($formulated) ? 1 : 0;

$id_proceso_code= get_code_from_table('tprocesos', $id_proceso, $clink);

$obj_data= new Tdata($clink, $item);
$obj_data->SetIdProceso($id_proceso);
$obj_data->set_id_proceso_code($id_proceso_code);

$obj_data->SetYear($year);
$obj_data->SetMonth($month);
$obj_data->SetInicio($inicio);
$obj_data->SetFin($fin);
$obj_data->SetScale($periodicidad);

if (!empty($id)) {
    if ($item == 'indicador') {
        $obj_data->radio_cumulative= $radio_cumulative;
        $obj_data->radio_formulated= $radio_formulated;
    }
    $obj_data->SetId($id);
    $obj_data->set();
    $obj_data->create_intervals();
    $obj_data->get();
    $obj_data->create_data();
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "ORDEN DEL DÍA y DEBATES", "Corresponde a $fecha_inicio_plan");
?>

<html>
    <head>
        <title>GRÁFICO INDICADOR</title>

         <?php require "inc/print_top.inc.php";?>

        <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
        <script type="text/javascript" src="../libs/hichart/js/modules/exporting.js"></script>

        <script language="javascript" type="text/javascript">
            $(document).ready(function() {
                $('#graphic').highcharts({
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '<?=$nombre?> (<?=$unidad?>)'
                    },
                    subtitle: {
                        text: '<?=$year?>'
                    },
                    xAxis: {
                        categories: [
                            <?php
                            $i= 0;
                            foreach ($obj_data->xlabels as $x) {
                                ++$i;
                                if ($i > 1)
                                    echo ",";
                                echo "'{$x}'";
                            }
                            ?>
                        ]
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '<?=$unidad?>'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                            '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
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
                        name: 'Real',
                        data: [
                            <?php
                            $i= 0;
                            foreach ($obj_data->ydata_real as $val) {
                                ++$i;
                                if ($i > 1)
                                    echo ",";
                                echo !is_null($val) ? $val : "null";
                            }
                            ?>
                        ]

                    }, {
                        name: 'Plan',
                        data: [
                            <?php
                            $i= 0;
                            foreach ($obj_data->ydata_plan as $val) {
                                ++$i;
                                if ($i > 1) echo ",";
                                echo !is_null($val) ? $val : "null";
                            }
                            ?>
                        ]
                    }]
                });
            });
        </script>


        <div class="container-fluid center">
            <div align="center" class="title-header">
                GRÁFICO INDICADOR: <?=$obj->GetNombre()?>, <?=$year?>
            </div>
        </div>

    <div class="page center">
        <div id="graphic" class="container-fluid">

        </div>
    </div>

    <?php require "inc/print_bottom.inc.php";?>
