<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/registro.class.php";

require_once "../php/graphic.interface.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id= !empty($_GET['id']) ? $_GET['id'] : 0;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;
$item= !empty($_GET['item']) ? $_GET['item'] : 'indicador';

$radio_cumulative= !is_null($_GET['radio_cumulative']) ? $_GET['radio_cumulative'] : 1;
$radio_formulated= !is_null($_GET['radio_formulated']) ? $_GET['radio_formulated'] : 1;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];

$time= new TTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

if (empty($year))
    $year= date('Y');
if (empty($month))
    $month= date('m');
if (empty($day))
    $day= date('d');

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $year;
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $year;

if (!empty($_GET['day']))
    $day= $_GET['day'];
if (empty($day)) {
    if ($month != $actual_month || $year != $actual_year) {
        if ($month == $actual_month && $year == $actual_year)
            $day= $actual_day;
        else
            $day= $lastday;
    }
}
$lastmonth= $year >= date('Y') ? date('m') : 12;

$obj= new Tindicador($clink);
$obj->SetYear($year);
$obj->Set($id_indicador);

$nombre= $obj->GetNombre();
$_periodicidad= $obj->GetPeriodicidad();
$carga= $obj->GetCarga();
$trend= $obj->GetTrend();
$id_code= $obj->get_id_indicador_code();
$cumulative= $obj->GetIfCumulative();
$unidad= $obj->GetUnidad();

$_inicio= (int)$obj->GetInicio();
$_fin= (int)$obj->GetFin();

$cumulative= !empty($cumulative) ? 1 : 0;

$obj_data= new Tdata_formulated($clink);

$obj_data->SetYear($year);
$obj_data->SetMonth($month);
$obj_data->SetDay($day);

$obj_data->SetIdIndicador($id_indicador);
$obj_data->get();

$array_indicadores= $obj_data->array_indicadores;
$array_indicadores[$id_indicador]= array('nombre'=>$obj->GetNombre(), 'id_code'=>$obj->get_id_code(),
                                        'id_proceso'=>$obj->GetIdProceso());
$obj_data->array_indicadores[$id_indicador]= $array_indicadores[$id_indicador];

$obj_data->get_data();
$array_data= $obj_data->array_data;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>GRÁFICO DE BARRAS</title>

        <?php require 'inc/_page_init.inc.php'; ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

        <link rel="stylesheet" type="text/css" href="../css/table.css" />
        <link rel="stylesheet" type="text/css" href="../css/custom.css">

        <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

        <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
        <script type="text/javascript" src="../libs/hichart/js/modules/exporting.js"></script>

        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <script language="javascript" type="text/javascript" src="../js/tablero.js?version="></script>

        <script type="text/javascript" src="../js/form.js?version="></script>

        <style type="text/css">
            body {
                background: none;
            }
            .btn-options {
                position: fixed;
                top: 10px;
                left: 10px;
            }
            #graphic {
                display: block;
                position: relative;
                align-content: center;
            }
        </style>

        <script language="javascript" type="text/javascript">
            function refreshp(flag) {
                var id= $('#id').val();
                var year= $('#year').val();
                var month= $('#month').val();
                var radio_cumulative= $('#radio_cumulative').val();

                var url= 'fgraph_formulated.php?id='+id+'&year='+year+'&month='+month;
                url+= '&radio_cumulative='+radio_cumulative;
                self.location= url;
            }

            function imprimir() {
                var id= $('#id').val();
                var year= $('#year').val();
                var month= $('#month').val();
                var radio_cumulative= $('#radio_cumulative').val();

                var url= '../print/graph_formulated.php?id='+id+'&inicio='+inicio+'&fin='+fin+'&year='+year;
                url+= '&month='+month+'&radio_cumulative='+radio_cumulative;

                prnpage= window.open(url,"IMPRIMIENDO GRAFICO DEL INDICADOR","width=900,height=600,toolbar=no,location=no, scrollbars=yes");
            }
        </script>

        <script language="javascript" type="text/javascript">
            $(document).ready(function() {
                InitDragDrop();

                // Expand Panel
                $("#open").click(function() {
                    <?php if (!empty($signal)) { ?>
                        displayFloatingDiv('div-ajax-panel', 'OPCIONES', 99, 0, 0.1, 0.1);
                    <?php } else { ?>
                        displayFloatingDiv('div-ajax-panel', 'OPCIONES', 80, 0, 5, 10);
                    <?php } ?>
                });

                $(function () {
                    $('#graphic').highcharts({
                        chart: {
                            type: 'column'
                        },
                        title: {
                            text: '<?="$nombre / $meses_array[$month], $year"?>'
                        },
                        xAxis: {
                            categories: [
                                <?php
                                $i= 0;
                                $item= null;
                                foreach ($array_indicadores as $id => $row) {
                                    $nombre= $row['nombre'];
                                    $obj_prs->SetIdProceso($row['id_proceso']);
                                    $obj_prs->Set();
                                    $nombre.= "<br/>".textparse($obj_prs->GetNombre());

                                    ++$i;
                                    if ($i > 1)
                                        $item.= ", ";
                                    $item.= "'$nombre'";
                                }
                                echo $item;
                                ?>
                            ]
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: '<?=$unidad?>'
                            }
                        },
                        legend: {
                            shadow: false
                        },
                        tooltip: {
                            shared: true,
                            // valuePrefix: '$',
                            valueSuffix: ' <?=$unidad?>'
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0,
                                borderWidth: 0
                            }
                        },
                        series: [
                            {
                                name: 'Real',
                                data: [
                                    <?php
                                    reset($array_indicadores);
                                    $i= 0;
                                    foreach ($array_indicadores as $id => $row) {
                                        ++$i;
                                        if ($i > 1)
                                            echo ", ";
                                        echo !empty($array_data[$id]['real']) ? $array_data[$id]['real'] : 0;
                                    }
                                    ?>
                                ]
                            },
                            {
                                name: 'Plan',
                                data: [
                                    <?php
                                    reset($array_indicadores);
                                    $i= 0;
                                    foreach ($array_indicadores as $id => $row) {
                                        ++$i;
                                        if ($i > 1)
                                            echo ", ";
                                        echo !empty($array_data[$id]['plan']) ? $array_data[$id]['plan'] : 0;
                                    }
                                    ?>
                                ]
                            },
                            {
                                name: 'Real Acumulado',
                                data: [
                                    <?php
                                    reset($array_indicadores);
                                    $i= 0;
                                    foreach ($array_indicadores as $id => $row) {
                                        ++$i;
                                        if ($i > 1)
                                            echo ", ";
                                        echo !empty($array_data[$id]['acumulado_real']) ? $array_data[$id]['acumulado_real'] : 0;
                                    }
                                    ?>
                                ]
                            },
                            {
                                name: 'Plan Acumulado',
                                data: [
                                    <?php
                                    reset($array_indicadores);
                                    $i= 0;
                                    foreach ($array_indicadores as $id => $row) {
                                        ++$i;
                                        if ($i > 1)
                                            echo ", ";
                                        echo !empty($array_data[$id]['acumulado_plan']) ? $array_data[$id]['acumulado_plan'] : 0;
                                    }
                                    ?>
                                ]
                            }
                        ]
                    });
                });
            });
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <input type="hidden" id="id" name="id" value="<?=$id?>" />
        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
        <input type="hidden" id="item" name="item" value="<?=$item?>" />

        <input type="hidden" id="radio_cumulative" value="<?=$radio_cumulative?>" />
        <input type="hidden" id="radio_formulated" value="<?=$radio_formulated?>" />

        <div class="container">
            <div id="container" class="container-fluid">
                <button id="open" class="btn btn-app btn-primary btn-options">
                    <i class="fa fa-cogs"></i>Opciones
                </button>

                <div id="graphic" class="container-fluid">

                </div>
            </div>
        </div>


        <!-- div-ajax-panel -->
        <div id="div-ajax-panel" class="card card-primary ajax-panel" data-bind="draganddrop">
            <div class="card-header">
                <div class="row">
                    <div class="panel-title col-11 win-drag">OPCIONES</div>

                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href= "javascript:HideContent('div-ajax-panel')" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <div class="form-group row">
                    <label class="col-form-label col-md-3">
                        Origen (Año/Mes):
                    </label>
                    <div class="col-md-2">
                        <select name="year" id="year" class="form-control input-sm">
                            <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $year) echo "selected='selected'"; ?>><?= $i ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="month" id="month" class="form-control input-sm">
                            <?php for ($i = 1; $i <= $lastmonth; $i++) { ?>
                                <option value="<?= $i ?>" <?php if ($i == (int) $month) echo "selected='selected'"; ?>><?= $meses_array[$i] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app row">
                    <button class="btn btn-primary" type="button" onclick="refreshp()">Graficar</button>
                    <button class="btn btn-warning" type="reset" onclick="HideContent('div-ajax-panel')">Cancelar</button>

                    <button class="btn btn-success d-none d-lg-block" type="button" onclick="imprimir()">
                        <i class="fa fa-print"></i>Imprimir
                    </button>

                    <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                </div>
            </div> <!-- panel -->
        </div>  <!-- div-ajax-panel -->

    </body>

</html>