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
require_once "../php/class/time.class.php";

require_once "../php/graphic.interface.php";

if (isset($_SESSION['obj']))  
    unset($_SESSION['obj']);

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
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
        if ($month == $actual_month && $year == $actual_year) 
            $day= $actual_day;
        else 
            $day= $lastday;
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
?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>GRÁFICOS DE CORRELACIÓN</title>

    <?php require 'inc/_page_init.inc.php'; ?>

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
    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    .container-fluid {
        position: relative;
    }

    #graphic {
        display: block;
        position: relative;
        align-content: center;

        top: 5px;
        left: 5px;
    }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    var dataArray = null;
    var array_item_name;
    var Tarray_item = Array();

    <?php foreach ($Tarray_item as $key => $item) { ?>
    Tarray_item['<?=$key?>'] = '<?=$item?>';
    <?php } ?>

    function create_json() {
        var cant = $('#cant_').val();
        if (cant == 0) {
            dataArray = null;
            return;
        }

        var index;
        var data = new Array();

        for (var i = 0; i < cant; i++) {
            index = i + 1;
            data[i] = new Array($('#year_' + index).val(), $('#id_' + index).val(), $('#id_proceso_' + index).val(), $(
                '#item_' + index).val());
        }

        dataArray = JSON.stringify(data);
    }

    function refreshp() {
        /*
        var inicio= $('#inicio').val();
        var fin= $('#fin').val();
        var periodicidad= $('#periodicidad').val();
        var year= $('#year').val();
        var month= $('#month').val();
        */
        create_json();
        var type_graph = $('#type_graph').val();
        var url = 'fcorrelacion.php?type_graph=' + type_graph + '&dataArray=' + dataArray;
        //    url+= '&inicio='+inicio+'&fin='+fin+'&year='+year+'&periodicidad='+periodicidad+'&month='+month;

        self.location = url;
    }

    function refresh_ajax() {
        var id_proceso = $('#proceso-serie').val();
        var year = $('#year-serie').val();

        if (parseInt($('#item-serie').val()) == 0) {
            alert("Debe selecionar el tipo de datos que va a graficar");
            return;
        }
        var url;

        switch ($('#item-serie').val()) {
            case 'politica':
                $('#label-item').html(Tarray_item['politica']);
                url = "../form/ajax/select_politica.ajax.php?id_politica=" + $('#politica').val();
                break;
            case 'objetivo':
                $('#label-item').html(Tarray_item['objetivo']);
                url = "../form/ajax/select_objetivo.ajax.php?id_objetivo=" + $('#objetivo').val();
                break;
            case 'perspectiva':
                $('#label-item').html(Tarray_item['perspectiva']);
                url = "../form/ajax/select_perspectiva.ajax.php?id_perspectiva=" + $('#perspectiva').val();
                break;
            case 'programa':
                $('#label-item').html(Tarray_item['programa']);
                url = "../form/ajax/select_programa.ajax.php?id_programa=" + $('#programa').val();
                break;
            case 'inductor':
                $('#label-item').html(Tarray_item['inductor']);
                url = "../form/ajax/select_inductor.ajax.php?id_inductor=" + $('#inductor').val();
                break;
            case 'indicador':
                $('#label-item').html(Tarray_item['indicador']);
                url = "../form/ajax/select_indicador.ajax.php?id_indicador=" + $('#indicador').val();
                break;
        }

        url += "&id_proceso=" + id_proceso + "&year=" + year;

        $.ajax({
            //   data:  parametros,
            url: url,
            type: 'get',
            beforeSend: function() {
                $("#ajax-panel-item").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#ajax-panel-item").html(response);
            }
        });
    }

    function imprimir() {
        create_json();
        var type_graph = $('#type_graph').val();
        var url = '../print/correlacion.php?type_graph=' + type_graph + '&dataArray=' + dataArray;
        prnpage = window.open(url, "IMPRIMIENDO GRÁFICO DE CORRELACIÓN",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function set_tipo() {
        var type_graph = $('#type_graph').val() == "line" ? "column" : "line";
        $('#type_graph').val(type_graph);
        refreshp();
    }
    </script>

    <script type="text/javascript">
    function del_graph(id) {
        oId = 0;

        var ids = new Array();
        ids.push(id);

        $table.bootstrapTable('remove', {
            field: 'id',
            values: ids
        });

        for (var i = id; i <= $('#cant_').val(); ++i) {
            if (arrayIndex['-' + i] == 'undefined') continue;
            arrayIndex['-' + i] = arrayIndex['-' + i] ? arrayIndex['-' + i] - 1 : 0;
            maxIndex = arrayIndex['-' + i];
        }
        arrayIndex['-' + id] = 'undefined';
    }

    function validar_graph() {
        if ($('#year-serie').val() == 0) {
            $('#year-serie').focus();
            alert("Debe selecionar el año para graficar.");
            return false;
        }
        if ($('#item-serie').val() == 0) {
            $('#item-serie').focus();
            alert("Debe selecionar el tipo de artículo a graficar.");
            return false;
        }
        if ($('#proceso-serie').val() == 0) {
            $('#proceso-serie').focus();
            alert("debe seleccionar la Unidad Organizativa al que pertenec el indicador.");
            return false;
        }
        if ($('#' + $('#item-serie').val()).val() == 0) {
            $('#' + $('#item-serie').val()).focus();
            alert("Debe selecionar el " + Tarray_item[$('#item-serie').val()] + " a graficar.");
            return false;
        }
        return true;
    }

    function add_graph() {
        if (!validar_graph()) return;

        if (ifnew) {
            ++numero;
            oId = numero;
            $('#cant_').val(numero);
        }

        var btn = '' +
            '<a href="#" class="btn btn-danger btn-sm" onclick="del_graph(' + oId + ')">' +
            '<i class="fa fa-trash"></i>Eliminar' +
            '</a>' +
            '<a class="btn btn-warning btn-sm" href="#" onclick="edit_graph(' + oId + ')">' +
            '<i class="fa fa-edit"></i>Editar';

        var _year = $('#year-serie').val() +
            '<input type="hidden" id="year_' + oId + '" name="year_' + oId + '" value="' + $('#year-serie').val() +
            '" />';

        var _item = Tarray_item[$('#item-serie').val()] +
            '<input type="hidden" id="item_' + oId + '" name="item_' + oId + '" value="' + $('#item-serie').val() +
            '" />';

        var id_proceso = $('#proceso-serie').val();
        var prs = array_prs[id_proceso] +
            '<input type="hidden" id="id_proceso_' + oId + '" name="id_proceso_' + oId + '" value="' + id_proceso +
            '" />';

        var id = $('#' + $('#item-serie').val()).val();
        var ind = array_item_name[id] +
            '<input type="hidden" id="id_' + oId + '" name="id_' + oId + '" value="' + id + '" />';

        if (ifnew) {
            index = ++maxIndex;
            arrayIndex['-' + oId] = index;

            $table.bootstrapTable('insertRow', {
                index: index,
                row: {
                    serie: oId,
                    btn: btn,
                    item: _item,
                    ind: ind,
                    year: _year,
                    prs: prs
                }
            });
        }

        if (!ifnew) {
            index = arrayIndex['-' + oId];

            $table.bootstrapTable('updateRow', {
                index: index,
                row: {
                    serie: oId,
                    btn: btn,
                    item: _item,
                    ind: ind,
                    year: _year,
                    prs: prs
                }
            });
        }

        var text = "Desea incorporar el " + Tarray_item[$('#item-serie').val()] + ": " + array_item_name[id] +
            " seleccionado a la grafica?";
        confirm(text, function(ok) {
            if (!ok)
                return;
            else
                refreshp();
        });

        CloseWindow('div-ajax-form');
    }

    function add() {
        displayFloatingDiv('div-ajax-form', '', 70, 0, 5, 15);

        ifnew = true;
        $('#year-serie').val($('#year').val());
        $('#item-serie').val('indicador');
        $('#proceso-serie').val($('#id_proceso').val());
        refresh_ajax();
    }

    function edit_graph(id) {
        displayFloatingDiv('div-ajax-form', '', 50, 0, 5, 15);

        ifnew = false;
        oId = id;
        $('#year-serie').val($('#year_' + id).val());
        $('#item-serie').val($('#item_' + id).val());
        $('#proceso-serie').val($('#id_proceso_' + id).val());
        $('#' + $('#item_' + id).val()).val($('#id_' + id).val());

        refresh_ajax();
    }
    </script>

    <script type="text/javascript">
    function formFilter() {
        displayFloatingDiv('div-ajax-panel-filter', 'OPCIONES', 60, 0, 2, 7);
    }
    </script>

    <script type="text/javascript">
    var oId;
    var ifnew;

    var $table;
    var row;

    var arrayIndex = new Array();
    var maxIndex = -1;
    var index = -1;
    var numero;

    $(document).ready(function() {
        InitDragDrop();

        $table = $("#table");
        $table.bootstrapTable('append', row);

        <?php if(!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->



    <div id="navbar-secondary">
        <nav class="navd-content">
            <a href="#" class="navd-header">GRÁFICOS</a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">
                    <li>
                        <a href="#" class="" onclick="add()">
                            <i class="fa fa-plus"></i>Curva
                        </a>
                    </li>
                    <li>
                        <a href="#" class="" onclick="refreshp()">
                            <i class="fa fa-pencil"></i>Graficar
                        </a>
                    </li>
                    <li>
                        <a href="#" class="" onclick="set_tipo()">
                            <i class="fa fa-line-chart"></i>/ <i class="fa fa-bar-chart"></i>Lineas/Barras
                        </a>
                    </li>
                    <li class="d-none d-lg-block">
                        <a href="#" class="" onclick="imprimir()">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>

                    <!--
                    <li>
                        <a href="#" class="" onclick="formFilter()">
                            <i class="fa fa-cogs"></i>Opciones
                        </a>
                    </li>
                    -->
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li>
                            <a href="#" onclick="open_help_window('../help/manual.html#listas')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <?php
    $obj_prs= new Tproceso($clink);

    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $tipo= $obj_prs->GetTipo();
    $nombre= $obj_prs->GetNombre();

    if($tipo == _TIPO_PROCESO_INTERNO && $id_proceso != $_SESSION['id_entity']) {
        $id_proceso= $_SESSION['id_entity'];
        $nombre= $_SESSION['empresa'];
    }

    $obj_prs->SetIdProceso($_SESSION['id_entity']);
    $obj_prs->SetConectado(null);
    $obj_prs->SetTipo(null);
    $obj_prs->SetIdResponsable(null);

    $result_prs= $obj_prs->listar_in_order('eq_desc', false, _TIPO_UEB, false);

    $obj_ind= new Tindicador($clink);
    $obj_ind->SetInicio($inicio);
    $obj_ind->SetFin($fin);
    $result_ind= $obj_ind->listar();
    ?>

    <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />

    <input type="hidden" id="type_graph" name="type_graph" value="<?=$type_graph?>" />
    <input type="hidden" id="day" name="day" value="<?=$day?>" />

            <div class="app-body table onebar container-fluid">

                <script type="text/javascript">
                var array_prs = new Array();
                <?php
                foreach ($result_prs as $row) {
                    $array_prs[$row['id']]= $row['nombre'];
                ?>
                    array_prs[<?=$row['id']?>] = '<?=$row['nombre']?>';
                    <?php } ?>

                    row = [
                    <?php
                    $i= 0;

                    foreach ($dataArray as $indi) {
                        if(isset($obj)) unset($obj);

                        switch ($indi[3]) {
                            case 'politica':
                                $obj= new Tpolitica($clink);
                                break;
                            case 'objetivo':
                                $obj= new Tobjetivo($clink);
                                break;
                            case 'perspectiva':
                                $obj= new Tperspectiva($clink);
                                break;
                            case 'programa':
                                $obj= new Tprograma($clink);
                                break;
                            case 'inductor':
                                $obj= new Tinductor($clink);
                                break;
                            case 'indicador':
                                $obj= new Tindicador($clink);
                                break;
                            default:
                                null;
                                break;
                        }

                        $obj->Set($indi[1]);
                        $name_indi= $obj->GetNombre();

                        ++$i;
                        if($i > 1) 
                            echo ",";
                    ?> {
                            serie: <?=$i?>,

                            btn: '' +
                                '<a href="#" class="btn btn-danger btn-sm" onclick="del_graph(<?=$i?>)">' +
                                '<i class="fa fa-trash"></i>Eliminar' +
                                '</a>' +
                                '<a class="btn btn-warning btn-sm" href="#" onclick="edit_graph(<?=$i?>)">' +
                                '<i class="fa fa-edit"></i>Editar' +
                                '</a>',

                            item: '<?=$Tarray_item[$indi[3]]?>' +
                                '<input type="hidden" id="item_<?=$i?>" name="item_<?=$i?>" value="<?=$indi[3]?>" />',

                            ind: '<?=$name_indi?>' +
                                '<input type="hidden" id="id_<?=$i?>" name="id_<?=$i?>" value="<?=$indi[1]?>" />',

                            year: '<?=$indi[0]?>' +
                                '<input type="hidden" id="year_<?=$i?>" name="year_<?=$i?>" value="<?=$indi[0]?>" />',

                            prs: '<?=$array_prs[$indi[2]]?>' +
                                '<input type="hidden" id="id_proceso_<?=$i?>" name="id_proceso_<?=$i?>" value="<?=$indi[2]?>" />'
                        }
                    <?php } ?>
                    ];
                </script>

                <div class="container-fluid">
                    <div class="row mt-2">
                        <div id="graphic" class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <script type="text/javascript">
                            $(function() {
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
                                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                                        ]
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

                                            if($i > 1) 
                                                echo ", ";
                                        ?> {
                                            name: "Serie <?=$i?>",
                                            data: [
                                                <?php
                                        $j= 0;
                                        foreach($obj_data->ydata_real as $val) {
                                            ++$j;
                                            if($j > 1) 
                                                echo ",";
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
                            <table id="table" class="table table-striped" data-toggle="table">
                                <thead>
                                    <tr>
                                        <th data-field="serie">Serie</th>
                                        <th data-field="btn"></th>
                                        <th data-field="item">Artículo</th>
                                        <th data-field="ind">Título</th>
                                        <th data-field="year">Año</th>
                                        <th data-field="prs">Unidad Organizativa</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>

                            <script type="text/javascript">
                            maxIndex = <?= $i-1 ?>;
                            numero = <?=$i?>;

                            <?php
                            $k= 0;
                            for($j= 1; $j <= $i; ++$j) {
                            ?>
                            arrayIndex['-' + <?=$j?>] = <?=$k++?>;
                            <?php } ?>
                            </script>

                            <input type="hidden" id="cant_" name="cant_" value="<?=$i?>" />
                        </div>
                    </div>
                </div>
            </div> <!-- container -->


            <!-- div-ajax-form -->
            <div id="div-ajax-form" class="card card-primary ajax-panel" data-bind="draganddrop">
                <div class="card-header">
                    <div class="row">
                        <div class="panel-title col-11 win-drag">INDICADOR</div>
                        <div class="col-1 pull-right">
                            <div class="close">
                                <a href="javascript:HideContent('div-ajax-form')" title="cerrar ventana">
                                    <i class="fa fa-close"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-form-label col-1">
                            Año
                        </label>
                        <div class="col-3">
                            <select name="year-serie" id="year-serie" class="form-control" onchange="refresh_ajax()">
                                <option value=0></option>
                                <?php for ($i = $inicio; $i <= $fin; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $year) echo "selected='selected'"; ?>>
                                    <?= $i ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <label class="col-form-label col-1">
                            Articulo:
                        </label>

                        <div class="col-xs-7 col-sm-7 col-md-7 col-lg-7">
                            <select name="item-serie" id="item-serie" class="form-control" onchange="refresh_ajax()">
                                <option value=0> .... </option>
                                <?php
                                reset($Tarray_item);
                                foreach ($Tarray_item as $key => $item) {
                                ?>
                                    <option value="<?=$key?>"><?=$item?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-xs-4 col-sm-4 col-md-3 col-lg-2">
                            Unidad Organizativa:
                        </label>
                        <div class="col-xs-8 col-sm-8 col-md-9 col-lg-10">
                            <select id="proceso-serie" name="proceso-serie" class="form-control"
                                onchange="refresh_ajax()">
                                <?php
                                reset($result_prs);
                                foreach ($result_prs as $row) {
                                ?>
                                    <option value="<?= $row['id'] ?>"
                                        <?php if ($row['id'] == $id_proceso) echo "selected='selected'"; ?>>
                                        <?= $row['nombre'] . ' (' . $Ttipo_proceso_array[$row['tipo']] . ')' ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label id="label-item" class="col-form-label col-2">
                            Indicador:
                        </label>

                        <div id="ajax-panel-item" class="col-10">
                        </div>
                    </div>

                    <!-- buttom -->
                    <div id="_submit" class="btn-block btn-app">
                        <button class="btn btn-primary" type="button" onclick="add_graph()">Adicionar</button>
                        <button class="btn btn-warning" type="reset"
                            onclick="HideContent('div-ajax-form')">Cancelar</button>
                    </div>
                </div> <!-- panel-body -->
            </div> <!-- div-ajax-form -->


            <!-- div-ajax-panel-filter -->
            <div id="div-ajax-panel-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
                <div class="card-header">
                    <div class="row">
                        <div class="panel-title col-11 win-drag">OPCIONES</div>

                        <div class="col-1 pull-right">
                            <div class="close">
                                <a href="javascript:HideContent('ajax-panel-filter')" title="cerrar ventana">
                                    <i class="fa fa-close"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-group row">
                        <label class="col-form-label col-1">
                            Año:
                        </label>
                        <label class="col-form-label col-1">
                            desde:
                        </label>
                        <div class="col-2">
                            <select name="inicio" id="inicio" class="form-control input-sm">
                                <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $inicio) echo "selected='selected'"; ?>>
                                    <?= $i ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <label class="col-form-label col-xs-1 col-sm-2 col-md-1 col-lg-1">
                            hasta:
                        </label>
                        <div class="col-2">
                            <select name="fin" id="fin" class="form-control input-sm">
                                <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>><?= $i ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-xs-2 col-sm-2 col-md-1 col-lg-1">
                            Escala:
                        </label>
                        <div class="col-xs-3 col-sm-3 col-md-4 col-lg-2">
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
                        <label class="col-form-label col-2">
                            Origen (Año/Mes):
                        </label>
                        <div class="col-2">
                            <select name="year" id="year" class="form-control input-sm">
                                <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $year) echo "selected='selected'"; ?>>
                                    <?= $i ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-3">
                            <select name="month" id="month" class="form-control input-sm">
                                <?php for ($i = 1; $i <= $lastmonth; $i++) { ?>
                                <option value="<?= $i ?>" <?php if ($i == (int) $month) echo "selected='selected'"; ?>>
                                    <?= $meses_array[$i] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- buttom -->
                    <div id="_submit" class="btn-block btn-app row">
                        <button class="btn btn-primary" type="button"
                            onclick="refreshp()"><?=$signal ? "Graficar" : "Refresca"?></button>
                        <button class="btn btn-warning" type="reset"
                            onclick="HideContent('ajax-panel-filter')">Cancelar</button>
                        <button class="btn btn-danger" type="button"
                            onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                    </div>
                </div> <!-- panel -->
            </div> <!-- div-ajax-panel-filter -->


</body>

</html>