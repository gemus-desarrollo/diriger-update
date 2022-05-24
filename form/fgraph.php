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
require_once "../php/class/indicador.class.php";
require_once "../php/class/registro.class.php";

require_once "../php/class/connect.class.php";
require_once "../php/graphic.interface.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$id= !empty($_GET['id']) ? $_GET['id'] : 0;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;
$item= !empty($_GET['item']) ? $_GET['item'] : 'indicador';

$radio_cumulative= !is_null($_GET['radio_cumulative']) ? $_GET['radio_cumulative'] : 1;
$radio_formulated= !is_null($_GET['radio_formulated']) ? $_GET['radio_formulated'] : 1;

if (!empty($_GET['id_indicador'])) {
    $id= (int)$id_indicador;
    $item= 'indicador';
}

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
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
            else
                $day= $lastday;
        }
    }
    $lastmonth= $year >= date('Y') ? date('m') : 12;
} else
    $lastmonth= 12;

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
        $obj = new Tinductor($clink);
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
        $obj = null;
}

$obj->SetYear($year);
if (!empty($id))
    $obj->Set($id);

$periodicidad= 0;
$carga= null;
$trend= null;
$cumulative= false;
$formulated= false;
$unidad= null;

$_inicio= date('Y') - 5;
$_fin= date('Y') + 2;

if ($item == 'indicador' & !empty($id)) {
    $obj->SetIdIndicador($id);

    $nombre= $obj->GetNombre();
    $_periodicidad= $obj->GetPeriodicidad();
    $carga= $obj->GetCarga();
    $trend= $obj->GetTrend();
    $id_code= $obj->get_id_indicador_code();
    $cumulative= $obj->GetIfCumulative();
    $formulated= $obj->GetIfFormulated();
    $unidad= $obj->GetUnidad();

    $_inicio= (int)$obj->GetInicio();
    $_fin= (int)$obj->GetFin();
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

    <script language="javascript" type="text/javascript" src="../js/tablero.js?version=">
    </script>

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
        var id = $('#id').val();
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var periodicidad = $('#periodicidad').val();
        var year = $('#year').val();
        var month = $('#month').val();

        if (flag == 1)
            id = 0;

        var url = 'fgraph.php?id=' + id + '&inicio=' + inicio + '&fin=' + fin + '&year=' + year;
        url += '&periodicidad=' + periodicidad + '&month=' + month + '&signal=<?=$signal?>+&item=<?=$item?>';
        self.location = url;
    }

    function imprimir() {
        var id = $('#id').val();
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var periodicidad = $('#periodicidad').val();
        var year = $('#year').val();
        var month = $('#month').val();
        var radio_cumulative = $('#radio_cumulative').val();
        var radio_formulated = $('#radio_formulated').val();

        var url = '../print/graph.php?id=' + id + '&inicio=' + inicio + '&fin=' + fin + '&year=' + year;
        url += '&periodicidad=' + periodicidad + '&month=' + month + '&signal=<?=$signal?>+&item=<?=$item?>';
        url += '&radio_cumulative=' + radio_cumulative + '&radio_formulted=' + radio_formulated;

        prnpage = window.open(url, "IMPRIMIENDO GRAFICO DEL INDICADOR",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script language="javascript" type="text/javascript">
    function _dropdown_indi(id) {
        $('#id').val(id);
        refreshp();
    }

    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp();
    }

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

        <?php if (!empty($nombre)) { ?>
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
                        if ($i > 1)
                            echo ",";
                        echo !is_null($val) ? $val : "null";
                    }
                    ?>
                ]
            }]
        });
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
    if (!empty($id_proceso)) {
        $obj_prs= new Tproceso($clink);

        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $tipo= $obj_prs->GetTipo();
        $nombre_prs_ref= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[(int)$obj_prs->GetTipo()];
    }

    if (($tipo == _TIPO_PROCESO_INTERNO && $id_proceso != $_SESSION['local_proceso_id']) || empty($id_proceso)) {
        $id_proceso= $_SESSION['local_proceso_id'];
        $nombre_prs_ref= $_SESSION['empresa'].', '.$Ttipo_proceso_array[(int)$_SESSION['local_proceso_tipo']];
    }
    ?>

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
                        <a href="javascript:HideContent('div-ajax-panel')" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="form-group row">

                <label class="col-form-label col-md-2">
                    Desde:
                </label>
                <div class="col-md-2">
                    <select name="inicio" id="inicio" class="form-control input-sm" onchange="javascript:refreshp()">
                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                        <option value="<?= $i ?>" <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <label class="col-form-label col-md-2">
                    Hasta:
                </label>
                <div class="col-md-2">
                    <select name="fin" id="fin" class="form-control input-sm" onchange="javascript:refreshp()">
                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>><?= $i ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <?php if ($item == 'indicador') { ?>
            <div class="form-group row">
                <label class="col-form-label col-lg-1">
                    Escala:
                </label>
                <div class=" col-lg-2">
                    <select id="periodicidad" name="periodicidad" class="form-control input-sm">
                        <option value=0>Seleccione...</option>
                        <?php
                                foreach ($periodo as $p) {
                                    if (!empty($carga) && $periodo_month[$p] < $periodo_month[$carga])
                                        continue;
                                    ?>
                        <option value="<?= $p ?>" <?php if ($periodicidad == $p) echo "selected='selected'" ?>>
                            <?= $periodo_inv[$p] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <label class="col-form-label col-lg-2">
                    Origen (Año/Mes):
                </label>
                <div class="col-lg-2">
                    <select name="year" id="year" class="form-control input-sm">
                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                        <option value="<?= $i ?>" <?php if ($i == $year) echo "selected='selected'"; ?>><?= $i ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select name="month" id="month" class="form-control input-sm">
                        <?php for ($i = 1; $i <= $lastmonth; $i++) { ?>
                        <option value="<?= $i ?>" <?php if ($i == (int) $month) echo "selected='selected'"; ?>>
                            <?= $meses_array[$i] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <?php } else { ?>
            <input type="hidden" id="periodicidad" name="periodicidad" value="M">
            <input type="hidden" name="year" id="year" value="<?=$year?>">
            <input type="hidden" name="month" id="month" value="<?=$month?>">
            <?php } ?>

            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app row">
                <button class="btn btn-primary" type="button"
                    onclick="refreshp()"><?=$signal ? "Graficar" : "Refresca"?></button>
                <button class="btn btn-warning" type="reset" onclick="HideContent('div-ajax-panel')">Cancelar</button>

                <button class="btn btn-success d-none d-lg-block" type="button" onclick="imprimir()">
                    <i class="fa fa-print"></i>Imprimir
                </button>

                <button class="btn btn-danger" type="button"
                    onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
            </div>
        </div> <!-- panel -->
    </div> <!-- div-ajax-panel -->

</body>

</html>