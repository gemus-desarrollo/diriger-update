<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
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
require_once "../php/class/indicador.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/calculator.class.php";

$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$day= !empty($_GET['day']) ? $_GET['day'] : $_SESSION['current_day'];

$_calculo= !empty($_GET['_calculo']) ? $_GET['_calculo'] : null;
$calculo= !empty($_GET['calculo']) ? $_GET['calculo'] : null;
$cumulative= !empty($_GET['cumulative']) ? $_GET['cumulative'] : null;

$nombre= strtoupper($_GET['nombre']);
$id_proceso= $_GET['id_proceso'];
$periodicidad= $_GET['periodicidad'];
$carga= $_GET['carga'];
$inicio= $_GET['inicio'];
$fin= $_GET['fin'];
$id_perspectiva_ref= $_GET['id_perspectiva_ref'];
$trend= $_GET['trend'];
$peso= $_GET['peso'];
$um= $_GET['um'];
$descripcion= urldecode($_GET['descripcion']);
$id_perspectiva= $_GET['id_perspectiva'];

$url= "&nombre=".urlencode($nombre)."&descripcion=".urlencode($descripcion)."&trend=$trend&peso=$peso&um=$um";
$url.= "&inicio=$inicio&fin=$fin&carga=$carga&periodicidad=$periodicidad&id_proceso=$id_proceso";
$url.= "&id_perspectiva_ref=$id_perspectiva_ref&year=$year&month=$month&day=$day&id_perspectiva=$id_perspectiva";

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$nombre_prs= $obj_prs->GetNombre();
$type_prs= $obj_prs->GetTipo();

$obj_indi= new Tindicador($clink);

$obj_cal= new Tcalculator($clink);
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GENERADOR DE FORMULA</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/calculator.css?version="  />
    <script language="javascript" type="text/javascript" src="../js/calculator.js?version="></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
        div.alert {
            margin: 0px 10px 0px 0px;
        }
        table.table tbody {
            background: #fff!important;
        }
        td.title {
            font-weight: bold;
            color: #000000;
            background: #E0AD80;
        }
    </style>

    <script type="text/javascript">
        <?php
        $str= !empty($calculo) ? $calculo : $_calculo;

        if (!empty($str))
            preg_match_all('/\_[A-Z]{2}[0-9]{8}/i', $str, $array_code);

        foreach ($array_code[0] as $code) {
        ?>
            array_indicadores.push('<?=substr($code, 1)?>');
        <?php } ?>

        function add_formula() {
            var text;
            var html= trim_str($("#editor").val());
            using_indicadores= array_indicadores.length ? true : false;

            function _this() {
                var editor= html.length && html != 'undefined' ? $("#editor").val() : $('#_editor').val();
                var calculo= $("#calculo").val();

                if (!using_indicadores) {
                    calculo= '';
                    editor= '';
                }

                opener.input_formula(calculo, editor);
                self.close();
            }

            if (!using_indicadores) {
                text= "Esta formula no contiene indicadores por lo que no es aceptable. ";
                text+= "No se incorporará al sistema. Desea continuar?";
                confirm(text, function(ok) {
                    if (!ok)
                        return;
                    else {
                        _this();
                        self.close();
                    }
                });
            } else
                _this();
        }

        function refreshp() {
            var year= $('#year').val();
            var id_proceso= $('#proceso').val();
            var id_indicador= $('#id_indicador').val();
            var cumulative= $('#cumulative').val();

            $('#ajax-indicador').html('<div class="loading"><img src="../img/loading.gif" alt="cargando" /><br/>Un momento, por favor...</div>');

            $.ajax({
                url: 'ajax/select_indicador_explain.ajax.php?csfr_token=123abc',
                data: {
                    year: year,
                    id_proceso: id_proceso,
                    id_indicador: id_indicador,
                    cumulative: cumulative
                },
                type: 'get',
                dataType: 'html',

                success: function (response) {
                    $('#ajax-indicador').fadeIn(1000).html(response);
                },
                error: function (xhr, status) {
                    alert('Disculpe, existió un problema -- FAjaxMcpo');
                }
            });
        }

        function cancel() {
            self.close();
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            init();
            InitDragDrop();

            var calculo;

            $('#btn-indicador').click(function() {
                $('#indicador').val(0);
                displayModalDiv('dimmingdivGrey', 'AGREGAR INDICADOR A LA FORMULA', 60, 0, 10, 10);

                $('#proceso').val($('#id_proceso').val());
                refreshp();
            });

            <?php
            $str= !empty($calculo) ? $calculo : $_calculo;

            if (!empty($str)) {
                preg_match_all('/\_[A-Z]{2}[0-9]{8}/i', $str, $array_code);
                $len= strlen($str);
                for ($i=0; $i <= $len; $i++) {
                ?>
                   // calculo=
                   // $('#calculo').val('<?=$str[$i]?>');
            <?php
            }  }

            $obj_prs= new Tproceso($clink);

            if (isset($obj_indi)) unset($obj_indi);
            $obj_indi= new Tindicador($clink);
            $obj_indi->SetYear($year);
            $result= $obj_indi->listar($year, _PERSPECTIVA_ALL);

            $i= 0;
            while ($row= $clink->fetch_array($result)) {
                if ($row['_id'] == $id_indicador)
                    continue;
                ++$i;
            ?>
                array_id_code[<?=$row['_id']?>]= '<?=$row['_id_code']?>';
                array_nombre[<?=$row['_id']?>]= '<?=$row['_nombre']?>';
            <?php } ?>
        });
    </script>

</head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">CALCULADORA</div>

                <div class="card-body">
                    <nav style="margin-bottom: 10px;">
                        <ul class="nav nav-tabs" role="tablist">
                            <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Calculadora</a></li>
                            <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Indicadores</a></li>
                        </ul>
                    </nav>


                    <form class="form-horizontal" id="frm-calculo" name="frm-calculo" action="javascript:validar()" method="POST">
                        <input type="hidden" id="_editor" name="_editor" value="<?= $obj_cal->convert2code_str($_calculo) ?>" />
                        <input type="hidden" id="_calculo" name="_calculo" value="<?= $_calculo ?>" />

                        <input type="hidden" id="year" name="year" value="<?= $year ?>" />
                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />
                        <input type="hidden" id="id_indicador" name="id_indicador" value="<?= $id_indicador ?>" />
                        <input type="hidden" id="cumulative" name="cumulative" value="<?= $cumulative ?>" />

                        <div class="tabcontent" id="tab1">
                            <div class="form-group row col-12">
                                <div class="row col-12">
                                    <div class="alert alert-danger col-4">
                                        <?= $nombre ?>
                                    </div>
                                    <div class="alert alert-info col-3">
                                        <?= $meses_array[(int) $month] ?>, <?= $year ?>
                                    </div>
                                    <div class="alert alert-warning col-4">
                                        <?= $nombre_prs . ', ' . $Ttipo_proceso_array[(int) $type_prs] ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-12">
                                    <div id="result" class="form-control"></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12">
                                    <input type="hidden" id="calculo" name="calculo" value="<?= $calculo ? $calculo : $_calculo ?>" />
                                    <textarea class="form-control" id="editor" name="editor" rows="5" readonly="yes"><?= $obj_cal->convert2code_str($calculo ? $calculo : $_calculo) ?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-8">
                                    <div class="btn-toolbar function-key">
                                        <button title="Raíz cuadrada x^1/2" onClick="teclaPulsada('SQRT2')" type="button" class="btn btn-default">sqrt2(x)</button>
                                        <button title="Raíz n-esíma F x^1/y" onclick="teclaPulsada('SQRT')" type="button" class="btn btn-default">sqrt(x,y)</button>
                                        <button title="Logaritmo neperiano" onClick="teclaPulsada('LN')" type="button" class="btn btn-default">ln(x)</button>
                                        <button title="Logaritmo común" onClick="teclaPulsada('LOG')" type="button" class="btn btn-default">log(x)</button>

                                        <button title="Cuadrado x^2" onclick="teclaPulsada('POT2')" type="button" class="btn btn-default">pot2(X)</button>
                                        <button title="Potencia Pot(x,y)" onclick="teclaPulsada('POT')" type="button" class="btn btn-default">pot(x,y)</button>
                                        <button title="Antilogaritmo neperiano aLn(x)" onClick="teclaPulsada('EXP')" type="button" class="btn btn-default">exp(x)</button>
                                        <button title="Antilogaritmo aLog(x)" onClick="teclaPulsada('ALOG')" type="button" class="btn btn-default">alog(x)</button>

                                        <button title="1/x" onclick="teclaPulsada('INV')" type="button" class="btn btn-default">inv(x)</button>
                                        <button title="x! Factorial de x" onclick="teclaPulsada('FACTORIAL')" type="button" class="btn btn-default">factorial(x)</button>
                                        <button title="Constante PI" onclick="teclaPulsada('PI')" type="button" class="btn btn-default">Pi</button>
                                        <button title="Constante de Euler" onclick="teclaPulsada('E')" type="button" class="btn btn-default">E</button>

                                        <button title="Tangente" onclick="teclaPulsada('TAN')" type="button" class="btn btn-default">tan(x)</button>
                                        <button title="Arco tangente" onclick="teclaPulsada('ATAN')" type="button" class="btn btn-default">atan(x)</button>
                                        <button title="Coseno" onclick="teclaPulsada('COS')" type="button" class="btn btn-default">cos(x)</button>
                                        <button title="Arcocoseno" onclick="teclaPulsada('ACOS')" type="button" class="btn btn-default">acos(x)</button>

                                        <button title="Seno" onclick="teclaPulsada('SIN')" type="button" class="btn btn-default">sin(x)</button>
                                        <button title="Arcoseno" onclick="teclaPulsada('ASIN')" type="button" class="btn btn-default">asin(x)</button>
                                        <button title="Maximo valor" onclick="teclaPulsada('MAX')" type="button" class="btn btn-default">max</button>
                                        <button title="Mínimo valor" onclick="teclaPulsada('MIN')" type="button" class="btn btn-default">min</button>

                                        <button title="Cambia signo +/-" onclick="teclaPulsada('SIGNO')" type="button" class="btn btn-default">+/-</button>

                                        <button onclick="teclaPulsada('(')" type="button" class="btn btn-default">(</button>
                                        <button onclick="teclaPulsada(')')" type="button" class="btn btn-default">)</button>
                                        <button onclick="teclaPulsada(',')" type="button" class="btn btn-default">,</button>
                                    </div>

                                     <div class="btn-toolbar panel-key">
                                        <button id='btn-indicador' title="Agregar referencia a Indicador" type="button" class="btn btn-default">Indicador</button>
                                        <button title="Limpiar Pantalla" type="button" onclick="cls()" class="btn btn-danger">Limpiar</button>
                                        <button title="Borrar el ultimo caracter" type="button" onclick="backspace()" class="btn btn-danger"><- Retroceso</button>
                                        <button onclick="calcular()" type="button" class="btn btn-danger">=</button>
                                    </div>
                                </div>
                                <div class="col-4">
                                     <div class="btn-toolbar number-key">
                                         <button onclick="teclaPulsada(7)" type="button" class="btn btn-default">7</button>
                                         <button onclick="teclaPulsada(8)" type="button" class="btn btn-default">8</button>
                                         <button onclick="teclaPulsada(9)" type="button" class="btn btn-default">9</button>
                                         <button onclick="teclaPulsada('/')" type="button" class="btn btn-default">/</button>
                                         <button onclick="teclaPulsada(4)" type="button" class="btn btn-default">4</button>
                                         <button type="button" onclick="teclaPulsada(5)" class="btn btn-default">5</button>
                                         <button type="button" onclick="teclaPulsada(6)" class="btn btn-default">6</button>
                                         <button type="button" onclick="teclaPulsada('*')" class="btn btn-default">*</button>
                                         <button type="button" onclick="teclaPulsada(1)" class="btn btn-default">1</button>
                                         <button type="button" onclick="teclaPulsada(2)" class="btn btn-default">2</button>
                                         <button type="button" onclick="teclaPulsada(3)" class="btn btn-default">3</button>
                                         <button type="button" onclick="teclaPulsada('-')" class="btn btn-default">-</button>

                                         <button onclick="teclaPulsada(0)" type="button" class="btn btn-default">0</button>
                                         <button onclick="teclaPulsada('.')" type="button" class="btn btn-default">.</button>
                                         <button title="Porcentaje de x con respecto a y" onclick="teclaPulsada('PERCENT');" type="button" class="btn btn-default">percent(x,y)</button>
                                         <button onclick="teclaPulsada('+')" type="button" class="btn btn-default">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="tabcontent" id="tab2">
                            <?php if ($cumulative) { ?>
                           <div id="toolbar" class="alert alert-danger" style="margin: 0px; padding: 6px 10px; font-size: 1.2em;">
                               Solo se consideran los indicadores acumulativos
                           </div>
                           <?php } ?>

                            <table class="table table-hover table-striped"
                                   data-toggle="table"
                                   data-height="460"
                                   <?php if ($cumulative) {?>data-toolbar="#toolbar"<?php } ?>
                                   data-search="true"
                                   data-row-style="rowStyle">
                                <thead>
                                    <th>No.</th>
                                    <th>Indicador</th>
                                    <th>UM</th>
                                    <th>ORIGEN</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>PERIODO</th>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php
                                    $obj_um= new Tunidad($clink);

                                    $obj_prs= new Tproceso($clink);
                                    $obj_prs->SetYear($year);
                                    $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
                                    $obj_prs->SetTipo($_SESSION['local_proceso_tipo']);

                                    $array_procesos= $obj_prs->listar_in_order('eq_desc', false);
                                    $i= 0;
                                    foreach ($array_procesos as $id_prs => $prs) {

                                        $obj_indi= new Tindicador($clink);
                                        $obj_indi->SetYear($year);
                                        $obj_indi->SetIdProceso($id_prs);

                                        $result= $obj_indi->listar();
                                        $cant= $obj_indi->GetCantidad();
                                        if (empty($cant))
                                            continue;
                                        ?>
                                        <tr>
                                            <td colspan="6" class="title"><?="{$prs['nombre']}, {$Ttipo_proceso_array[$prs['tipo']]}"?></td>
                                        </tr>
                                        <?php
                                        $array_ids= array();
                                        while ($row= $clink->fetch_array($result)) {
                                            if ($row['_id'] == $id_indicador)
                                                continue;
                                         //   if (boolean($row['formulated'])) continue;
                                            if ($cumulative && !boolean($row['cumulative']))
                                                continue;
                                            ++$i;
                                            if($array_ids[$row['_id']])
                                                continue;
                                            $array_ids[$row['_id']]= $row['_id'];
                                        ?>
                                        <tr>
                                            <td>
                                            <?=$i?>
                                            </td>
                                            <td>
                                                <?=$row['_nombre']?>
                                            </td>
                                            <td>
                                                <?php
                                                $obj_um->Set($row['unidad']);
                                                echo "(".$obj_um->GetNombre().")  ".$obj_um->GetDescripcion();
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $obj_prs->Set($row['_id_proceso']);
                                                echo $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
                                                ?>
                                            </td>
                                            <td>
                                                <?=textparse($row['_descripcion'])?>
                                            </td>
                                            <td>
                                                <?="{$row['_inicio']}-{$row['_fin']}"?>
                                            </td>
                                        </tr>
                                    <?php } }   ?>
                                </tbody>
                            </table>
                       </div>

                        <div class="btn-block btn-app">
                            <button type="button" class="btn btn-primary" onclick="add_formula()">Aceptar</button>
                            <button type="button" class="btn btn-warning" onclick="cancel()">Cerrar</button>
                        </div>
                    </form>
                 </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div>  <!-- container -->

        <div id="dimmingdivGrey" class="container-fluid" data-bind="draganddrop">
            <div class="card card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="panel-title ajax-title col-11 win-drag">INDICADOR</div>
                        <div class="col-1 pull-right">
                            <div class="close">
                                <a href="#" onclick="CloseWindow('dimmingdivGrey')">
                                    <i class="fa fa-close"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body ">
                    <div class="form-horizontal">
                        <div class="form-group row">
                            <label class="col-form-label col-sm-3 col-md-2 col-lg-2">
                                 Unidad Organizativa:
                            </label>
                            <div class="col-sm-9 col-md-10 col-lg-10">
                                <?php
                                $top_list_option = "seleccione........";
                                $id_list_prs = null;
                                $order_list_prs = 'eq_desc';
                                $reject_connected = false;
                                $in_building = ($action == 'add' || $action == 'update') ? true : false;
                                $only_additive_list_prs = ($action == 'add') ? true : false;

                                $restrict_prs = array(_TIPO_DIRECCION, _TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_GRUPO, _TIPO_PROCESO_INTERNO);
                                $id_select_prs = $id_proceso;
                                include_once "inc/_select_prs_down.inc.php";
                                ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-2">
                                 Indicadores:
                            </label>
                            <div id="ajax-indicador" class="col-10">
                                <select id="indicador" name="indicador" class="form-control" onchange="select_indi()">
                                    <option value="0">Seleccione....</option>
                                </select>
                            </div>
                        </div>

                        <div class="btn-block btn-app">
                            <button class="btn btn-primary" onclick="add_indicador()">Agregar</button>
                            <button class="btn btn-warning" onclick="CloseWindow('dimmingdivGrey')">Cerrar</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </body>
</html>



