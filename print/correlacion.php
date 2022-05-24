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
require_once "../php/class/indicador.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/time.class.php";

require_once "../php/graphic.interface.php";

require_once "../php/class/traza.class.php";

if (isset($_SESSION['obj']))  
    unset($_SESSION['obj']);

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$actual_year= date('Y');
$actual_month= date('m');
$actual_day= date('d');

$year= !empty($_GET['year']) ? $_GET['year'] : $actual_year;

$month= !empty($_GET['month']) ? $_GET['month'] : $actual_month;
$day= !empty($_GET['day']) ? $_GET['day'] :1;
$type_graph= !empty($_GET['type_graph']) ? $_GET['type_graph'] : 'line';
$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $year;
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $year;

$_inicio= date('Y') - 5;
$_fin= date('Y') + 2;

$inicio= $year - 3;
$fin= $year + 3;

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

$lastmonth= $year >= date('Y') ? date('m') : 12;

if (!empty($_GET['day']))
    $day= $_GET['day'];
if (empty($day)) {
    if ($month != $actual_month || $year != $actual_year) {
        if ($month == $actual_month && $year == $actual_year) $day= $actual_day;
        else $day= $lastday;
    }
}

$dataArray= !empty($_GET['dataArray']) ? $_GET['dataArray'] : null;
$dataArray= json_decode($dataArray);

$obj= new Tindicador($clink);

$Tarray_item['politica']= 'Lineamiento o Pólitica';
$Tarray_item['objetivo']= 'Objetivo Estratégico';
$Tarray_item['perspectiva']= 'Perspectiva';
$Tarray_item['programa']= 'Programa';
$Tarray_item['inductor']= 'Objetivo de Trabajo';
$Tarray_item['indicador']= 'Indicador';

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "GRÁFICOS DE CORRELACIÓN");
?>

<html>
    <head>
        <title>GRÁFICOS DE CORRELACIÓN</title>

        <?php require "inc/print_top.inc.php";?>

        <style>
        body{
            background:none;
        }
        </style>

        <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
        <script type="text/javascript" src="../libs/hichart/js/modules/exporting.js"></script>

        <?php
        $obj_prs= new Tproceso($clink);

        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $tipo= $obj_prs->GetTipo();
        $nombre= $obj_prs->GetNombre();
        ?>

        <div class="container-fluid">
            <div id="graphic" class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <script type="text/javascript">
                    $(function () {
                        $('#graphic').highcharts({
                            chart: {
                                type: '<?=$type_graph?>'
                            },
                            title: {
                                text: 'Gráficos',
                                x: -20 //center
                            },
                            xAxis: {
                                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                            },
                            yAxis: {
                                title: {
                                    text: 'Porcientos (%)'
                                },
                                plotLines: [{
                                    value: 0,
                                    width: 1,
                                    color: '#808080'
                                }]
                            },
                            tooltip: {
                                valueSuffix: '%'
                            },
                            legend: {
                                layout: 'vertical',
                                align: 'right',
                                verticalAlign: 'middle',
                                borderWidth: 0
                            },

                            series: [
                                <?php
                                $i= 0;
                                reset($dataArray);
                                foreach ($dataArray as $indi) {
                                    ++$i;
                                    $obj_data= new Tdata($clink, $indi[3]);

                                    $obj_data->SetIdProceso($indi[2]);
                                    $id_proceso_code= get_code_from_table('tprocesos', $indi[2]);
                                    $obj_data->set_id_proceso_code($id_proceso_code);

                                    $obj_data->SetYear($indi[0]);
                                    $obj_data->SetMonth(1);
                                    $obj_data->SetInicio($indi[0]);
                                    $obj_data->SetFin($indi[0]);

                                    $obj_data->SetScale($periodicidad);

                                    $obj_data->SetId($indi[1]);
                                    $obj_data->set();

                                    $obj_data->create_intervals();
                                    $obj_data->get();
                                    $obj_data->create_data();

                                    if ($i > 1) echo ", ";
                                ?>
                                {
                                    name: "Serie <?=$i?>",
                                    data: [
                                        <?php
                                        $j= 0;
                                        foreach ($obj_data->ydata_real as $val) {
                                            ++$j;
                                            if ($j > 1) echo ",";
                                            echo !is_null($val) ? $val : "null";
                                        }
                                        ?>
                                    ]
                                }
                                <?php } ?>
                                ]
                        });
                    });
                </script>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <?php
            $i= 1;
            foreach ($dataArray as $indi){
                if (isset($obj)) unset($obj);

                switch ($indi[3]){
                    case 'politica':
                        $obj = new Tpolitica($clink);
                        break;
                    case 'objetivo':
                        $obj = new Tobjetivo($clink);
                        break;
                    case 'perspectiva':
                        $obj = new Tperspectiva($clink);
                        break;
                    case 'programa':
                        $obj = new Tprograma($clink);
                        break;
                    case 'inductor':
                        $obj = new Tinductor($clink);
                        break;
                    case 'indicador':
                        $obj = new Tindicador($clink);
                        break;
                    default:
                        null;
                        break;
                }

                $obj->Set($indi[1]);
                $name_indi = $obj->GetNombre();

                echo "<p><strong>Serie $i (".strtoupper($indi[3])."):</strong> $name_indi </p>";
                ++$i;
            }
            ?>
            </div>
        </div>

    </div>

    <?php require "inc/print_bottom.inc.php";?>