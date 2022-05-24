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

require_once "../php/class/unidad.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/proyecto.class.php";

require_once "../php/class/peso.class.php";

require_once "../php/class/code.class.php";

require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal= !empty($_GET['signal']) ?  $_GET['signal']: 'indicador';

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (empty($error))
    $error= $obj->error;

$actual_year= $_SESSION['current_year'];
$actual_month= $_SESSION['current_month'];
$actual_day= $_SESSION['current_day'];

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];

$time= new TTime;
$current_year= $time->GetYear();

if (empty($year))
    $year= $current_year;

if (!empty($_GET['month']))
    $month= $_GET['month'];
if (empty($month) || $month == -1)
    $month= $actual_month;

if (!empty($_GET['day']))
    $day= $_GET['day'];
else if ($month != $actual_month || $year != $actual_year)
    $day= $lastday;
else
    $day= $actual_day;

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tindicador($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : $obj->GetIdIndicador();

$if_exist= false;

if (!empty($id_indicador))
    $obj->SetIdIndicador($id_indicador);

if (!isset($_SESSION['obj']) && !empty($id_indicador)) {
    $obj->SetYear($year);
    $obj->Set();
}

$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : $obj->GetIdPerspectiva();
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso_ref();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$id_proceso_ref= $obj->GetIdProceso_ref();
$id_proceso_ref_code= $obj->get_id_proceso_ref_code();
if (empty($id_proceso_ref)) 
    $id_proceso_ref= 0;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo= $obj_prs->GetTipo();
$nombre_prs= $obj_prs->GetNombre();
$conectado= $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

$inicio_esc= $_inicio;
$fin_esc= $_fin;

$periodicidad= !empty($_GET['periodicidad']) ? $_GET['periodicidad'] : $obj->GetPeriodicidad();
if (empty($periodicidad)) 
    $periodicidad= 'X';
$carga= !empty($_GET['carga']) ? $_GET['carga'] : $obj->GetCarga();
if (empty($carga)) 
    $carga= 'X';
$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
if (empty($inicio)) 
    $inicio= $year;
$fin= !empty($_GET['fin']) ? $_GET['fin']: $obj->GetFin();
if (empty($fin)) 
    $fin= $year;

$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$id_perspectiva_ref= !empty($_GET['id_perspectiva_ref']) ? $_GET['id_perspectiva_ref'] : 0;
$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : $obj->GetIdPerspectiva();
$trend= !is_null($_GET['trend']) ? $_GET['trend'] : $obj->GetTrend();
$peso= !is_null($_GET['peso']) ? $_GET['peso'] : $obj->GetPeso();
$id_unidad= !empty($_GET['id_unidad']) ? $_GET['id_unidad'] : $obj->GetIdUnidad();
$nombre= !is_null($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !is_null($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();

$formulated= !empty($_GET['formulated']) ? $_GET['formulated'] : $obj->GetIfFormulated();
$from_calculator= !is_null($_GET['from_calculator']) ? $_GET['from_calculator'] : null;
$calculo= !is_null($_GET['calculo']) ? rawurldecode($_GET['calculo']) : $obj->GetFormCalculo();

$cumulative= !is_null($_GET['cumulative']) ? $_GET['cumulative'] : $obj->GetIfCumulative();

$chk_cumulative= !is_null($_GET['chk_cumulative']) ? $_GET['chk_cumulative'] : $obj->GetChkCumulative();
if ($action == 'add' && is_null($chk_cumulative))
    $chk_cumulative= 1;

$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : $obj->GetIdProyecto();

$inicio_origen= $obj->GetIndicio_origen();
$fin_origen= $obj->GetFin_origen();

$_inicio= !is_null($inicio_origen) ? max($inicio_esc, $inicio_origen) : $inicio_esc;
$_fin= !is_null($fin_origen) ? min($fin_esc, $fin_origen) : $fin_esc;

if ($year > $_fin)
    $year= $_fin;
if ($year < $_inicio)
    $year= $_inicio;
if ($year > $fin)
    $year= $fin;

$ind_definido= empty($inicio_origen) ? 0 : 1;

$obj_perspectiva= new Tperspectiva($clink);
$obj_inductor= new Tinductor($clink);

$id_proceso_sup= $id_proceso;

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso_ref)) {
    $obj_prs->Set($id_proceso_ref);
    $tipo_ref= $obj_prs->GetTipo();
    $nombre_prs_ref= $obj_prs->GetNombre();
}

$nombre_prs= $nombre_prs_ref;
$tipo= $tipo_ref;

if ($id_proceso_ref != $id_proceso) {
    $obj_prs->Set($id_proceso);
    $tipo= $obj_prs->GetTipo();
    $nombre_prs= $obj_prs->GetNombre();
}

$obj_peso= new Tpeso($clink);
$obj_peso->SetYear($year);
$obj_peso->SetInicio($_inicio);
$obj_peso->SetFin($_fin);

$url_page= "../form/findicador.php?signal=$signal&action=$action&menu=indicador&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_perspectiva=$id_perspectiva&id_inductor=$id_inductor";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>INDICADOR</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/custom.css">

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
    .in-percentil {
        width: 40px;
        text-align: right;
        padding-right: 2px;
    }

    .label {
        color: #6B6B6B;
        background-color: #D9ECFF;
    }

    fieldset .fieldset {
        padding: 10px 20px 10px 20px;
    }
    </style>

    <script language='javascript' type="text/javascript" charset="utf-8">
    var array_carga = new Array(7);
    array_carga['D'] = 1;
    array_carga['S'] = 2;
    array_carga['Q'] = 3;
    array_carga['M'] = 4;
    array_carga['T'] = 5;
    array_carga['E'] = 6;
    array_carga['A'] = 7;

    function _validar() {
        var text;
        $('#formulated').val($('#if_calculated1').is(':checked') ? 1 : 0);

        if (!$('#trend1').is(':checked') && !$('#trend2').is(':checked') && !$('#trend3').is(':checked')) {
            text = "Debe especificar la configuración de la escala de colores. Especifique si el indicador mejora ";
            text += "cuando el valor real CRECE o DISMINUYE, o esta dentro de un INTERVALO especifico.";
            $('#trend1').focus(focusin($('#trend1')));
            alert(text);
            return false;
        }

        for (var j = 1; j < 4; ++j) {
            for (var k = 1; k < 6; ++k) {
                if (Entrada($('#_c' + j + k).val())) {
                    if (!IsNumeric($('#_c' + j + k).val())) {
                        $('#trend1').focus(focusin($('#trend1')));
                        alert('Error en el formato de los valores para la escala de colores.');
                        return false;
        }   }   }   }
        
        j = 1;
        if ($('#trend1').is(':checked')) {
            for (var i = 1; i < 6; ++i) {
                if (Number($('#_c1' + i).val()) < Number($('#_c1' + j).val())) {
                    text = "Error en el orden de los valores de la escala de colores. ";
                    text += "Los valores deben de crecer de Rojo(menor) a Azul fuerte (mayor)";
                    $('#trend1').focus(focusin($('#trend1')));
                    alert(text);
                    return false;
                }
                j = i;
            }

            if (Number($('#_c14').val()) < 100 || Number($('#_c15').val()) < 100) {
                $('#trend1').focus(focusin($('#trend1')));
                alert(
                    'Error en el orden de los valores de la escala de colores (Los colores azules NO deben estar por debajo del 100%).'
                    );
                return false;
            }
            if (Number($('#_c13').val()) > 100) {
                $('#trend1').focus(focusin($('#trend1')));
                alert(
                    'Error en el orden de los valores de la escala de colores (El color verde no debe estar por encima del 100%).'
                    );
                return false;
            }
        }

        if ($('#trend2').is(':checked')) {
            for (i = 1; i < 5; ++i) {
                j = i + 1;
                if (Number($('#_c2' + i).val()) < Number($('#_c2' + j).val())) {
                    text = "Error en el orden de los valores de la escala de colores. ";
                    text += "Los valores deben de disminuir de Rojo(mayor) a Azul fuerte (menor)";
                    $('#trend2').focus(focusin($('#trend2')));
                    alert(text);
                    return false;
                }
            }
            if (Number($('#_c24').val()) > 100 || Number($('#_c25').val()) >= 100) {
                $('#trend2').focus(focusin($('#trend2')));
                alert(
                    'Error en el orden de los valores de la escala. Los colores Azules NO debería estar por encima del 100%).'
                    );
                return false;
            }
        }

        if ($('#trend3').is(':checked')) {
            if (Number($('#_c31').val()) > Number($('#_c32').val())) {
                text = "Error en el orden de los valores de la escala de colores para la escala inferior. ";
                text += "Los valores deben de disminuir de Amarillo(mayor) a Rojo(menor)";
                $('#trend3').focus(focusin($('#trend3')));
                alert(text);
                return false;
            }
            if (Number($('#_c31').val()) >= 100 || Number($('#_c32').val()) >= 100) {
                text = "Error en el orden de los valores de la escala de colores para la escala inferior ";
                text += "(En la escala inferior los dos valores deben estar por debajo del 100%).";
                $('#trend3').focus(focusin($('#trend3')));
                alert(text);
                return false;
            }
            if (Number($('#_c33').val()) > Number($('#_c34').val())) {
                text = "Error en el orden de los valores de la escala de colores para la escala superior. ";
                text += "Los valores deben de aumentar de Amarillo(menor) a Rojo(mayor)";
                $('#trend3').focus(focusin($('#trend3')));
                alert(text);
                return false;
            }
            if (Number($('#_c33').val()) <= 100 || Number($('#_c34').val()) <= 100) {
                text = "Error en el orden de los valores de la escala de colores para la escala superior ";
                text += "En la escala superior los dos valores deben estar por encima del 100%).";
                $('#trend3').focus(focusin($('#trend3')));
                alert(text);
                return false;
            }
        }

        document.forms[0].action = '../php/indicador.interface.php';

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        document.forms[0].submit();
    }

    function validar() {
        if (parseInt($('#year').val()) < parseInt($('#inicio').val()) || parseInt($('#year').val()) > parseInt($('#fin')
                .val())) {
            $('#year').focus(focusin($('#year')));
            alert("El año de referencia tiene que estar entre los años de vigencia del Indicador.");
            return;
        }
        if (parseInt($('#inicio').val()) > parseInt($('#fin').val())) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio de la vigencia del indicador no puede ser superior al año en que finaliza.");
            return;
        }
        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert('Selecione el Proceso o Unidad Organizativa donde se genera y gestiona el indicador.');
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el nombre del indicador');
            return;
        }
        if ($('#unidad').val() == 0) {
            $('#unidad').focus(focusin($('#unidad')));
            alert("Escoja la unidad de medida para el indicador");
            return;
        }
        if ($('#periodicidad').val() == 'X') {
            $('#periodicidad').focus(focusin($('#periodicidad')));
            alert('Seleccione la periodicidad que tendrá el indicador.');
            return;
        }
        if ($('#carga').val() == 'X') {
            $('#carga').focus(focusin($('#carga')));
            alert('Seleccione la frecuencia con que se actualizarán los datos del indicador.');
            return;
        }
        if (array_carga[$('#periodicidad').val()] < array_carga[$('#carga').val()]) {
            $('#periodicidad').focus(focusin($('#periodicidad')));
            alert("La periodicidad del indicador no puede ser menor que la frecuencia con la que se actualiza.");
            return;
        }
        if ($('#inicio').val() > $('#fin').val()) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio de la vigencia del indicador no puede ser superior al año en que finaliza.");
        }
        if (!Entrada($('#calculo').val())) {
            if ($('#if_calculated1').is(':checked')) {
                $('#if_calculated1').focus(focusin($('#if_calculated1')));
                alert('Introduzca la formula de cálculo del indicador');
                return;

            } else {
                confirm("No ha introducido la explicación del método de obtención de los datos de este indicador. ¿Desea continuar?",
                    function(ok) {
                        if (!ok)
                            return;
                        else if (!_validar())
                            return;
                    });
            }
        } else {
            _validar();
        }
    }

    function refreshprs() {
        var id_proceso = $('#proceso').val();
        refreshp();
    }

    function urlpage(flag) {
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();

        if (parseInt(inicio) > parseInt(fin)) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio de la vigencia del indicador no puede ser superior al año de en el que termina");
            return false;
        }

        var id_perspectiva = $('#perspectiva').val();
        var ind_definido = $('#ind_definido').val();
        var unidad = $('#unidad').val();

        var year = $('#year').val();
        var month = $('#month').val();
        var periodicidad = $('#periodicidad').val();
        var carga = $('#carga').val();
        var signal = $('#signal').val();
        var peso = $('#peso').val();

        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURI($('#descripcion').val());
        var calculo = encodeURIComponent($('#calculo').val());
        var _calculo = encodeURIComponent($('#_calculo').val());

        var cumulative = $('#cumulative').is(':checked') ? 1 : 0;
        var formulated = $('#if_calculated1').is(':checked') ? 1 : 0;
        var chk_cumulative = $('#chk_cumulative').is(':checked') ? 1 : 0;

        var trend = 0;
        for (var i = 1; i < 4; i++) {
            if ($('#trend' + i).is(':checked'))
                trend = $('#trend' + i).val();
        }

        var numero = $('#numero').val();

        var url = '&id_perspectiva=' + id_perspectiva + '&_calculo=' + _calculo + '&calculo=' + calculo;
        url += '&ind_definido=' + ind_definido + '&inicio=' + inicio + '&fin=' + fin + '&peso=' + peso;
        url += '&periodicidad=' + periodicidad + '&carga=' + carga + '&signal=' + signal + '&nombre=' + nombre;
        url += '&descripcion=' + descripcion + '&id_unidad=' + unidad + '&year=' + year + '&trend=' + trend;
        url += '&cumulative=' + cumulative + '&chk_cumulative=' + chk_cumulative + '&formulated=' + formulated;
        url += '&numero=' + numero;

        return url;
    }

    function refreshp() {
        var url = urlpage(true);
        if (url == false)
            return;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        self.location = 'findicador.php?version=&action=<?=$action?>' + url;
    }

    function refresh_per() {
        if ($('#perspectiva').val() > 0) {
            $('#peso').show();
            $('#label-peso').show();
        } else {
            $('#peso').hide();
            $('#label-peso').hide();
        }
    }

    function refreshscale(id) {
        $('#tr_trend' + id).show();
        $('#trend' + id).prop('checked', true);

        for (i = 1; i < 4; ++i) {
            if (i != id) {
                $('#tr_trend' + i).hide();
                $('#trend' + i).prop('checked', false);
            }
        }

        if (id == 1) {
            for (i = 1; i < 4; i++)
                if ($('#_c1' + i).val() > 100)
                    $('#_c1' + i).val(200 - $('#_c1' + i).val());
        }
        if (id == 2) {
            for (i = 1; i < 3; i++)
                if ($('#_c2' + i).val() < 100)
                    $('#_c2' + i).val(200 - $('#_c2' + i).val());
        }
    }

    function refreshcal() {
        if ($('#if_calculated1').is(':checked')) {
            $('#formula_editor').prop('readOnly', true);
            $('#verformula').css('visibility', 'visible');
        }
        if ($('#if_calculated0').is(':checked')) {
            $('#formula_editor').prop('readOnly', false);
            $('#verformula').css('visibility', 'hidden');
            $('#cumulative').prop('disabled', false);
        }
        if ($('#cumulative').is(':checked')) {
            $('#chk_cumulative').is(':checked', true);
            $('#chk_cumulative').prop('checked', true);
            /*
            $('#if_calculated1').prop('disabled', true);
            $('#formula_editor').prop('readOnly', false);
            $('#verformula').css('visibility', 'hidden');
            */
        } else
            $('#if_calculated1').prop('disabled', false);
    }

    function go_formula() {
        var url = urlpage(false);
        if (url == false)
            return;
        var id = $('#id').val() ? $('#id').val() : 0;
        show_imprimir("calculator.php?id_indicador=" + id + url, "INDICADOR AUTO CALCULADO",
            "dialogHeight:470px;dialogWidth:900px;toolbar:no;directories:no");
    }

    function input_formula(calculo, formula_editor) {
        $("#calculo").val(calculo);
        $("#formula_editor").val(formula_editor);
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        <?php if (empty($from_calculator)) {?>
        $('#nav-tab1').addClass('active');
        $('#tab1').show();
        <?php } else { ?>
        $('#nav-tab2').addClass('active');
        $('#tab2').show();
        <?php } ?>

        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        function set_numero(val) {
            $('#numero').val(val);
        }

        if (parseInt($('#t_cant_objt').val()) == 0)
            $('#div-inductores').hide();

        refreshcal();
        refreshscale(<?=$trend?>);

        $("#formula_editor").tinymce({
            selector: '#formula_editor',
            theme: 'modern',
            height: 150,
            language: 'es',
            toolbar: false,
            menubar: false
        });

        try {
            $('#formula_editor').val(<?= json_encode($obj->replace_formulate($calculo))?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>alert("<?= str_replace("\n", " ", $error) ?>") <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">INDICADOR</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales">
                            <a class="nav-link" href="tab1">Generales</a>
                        </li>
                        <li id="nav-tab2" class="nav-item" title="Formula de cálculo e Interpretación de los valores">
                            <a class="nav-link" href="tab2">Descripción</a>
                        </li>
                        <li id="nav-tab3" class="nav-item"
                            title="Definir los porcientos de cumplimiento asociado a la escala de colores o alarmas">
                            <a class="nav-link" href="tab3">Escala de Colores</a>
                        </li>
                        <li id="nav-tab5" class="nav-item" class="nav-item" 
                            title="Definir el Impacto sobre los Objetivos de Trabajo vigente en el año de referencia">
                            <a class="nav-link" href="tab5">Objetivos de trabajo (Año:<?=$year?>)</a>
                        </li>
                        <li id="nav-tab4" class="nav-item"
                            title="Definir el Impacto sobre los procesos internos o Unidades Organizativas fuera de la  
                            intranet a las que sera transmitido el valor asignado">
                            <a class="nav-link" href="tab4">Procesos Internos/Sincronización</a>
                        </li>
                    </ul>
  
                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                        <input type="hidden" id="id" name="id" value="<?=$id_indicador?>" />
                        <input type="hidden" name="menu" value="indicador" />

                        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
                        <input type="hidden" id="month" name="month" value="<?=$month?>" />
                        <input type="hidden" id="day" name="day" value="<?=$day?>" />
                        <input type="hidden" name="ind_definido" id="ind_definido" value="<?=$ind_definido?>" />
                        <input type="hidden" name="formulated" id="formulated" value="<?=$formulated?>" />

                        <input type="hidden" name="id_proceso_ref" value="<?=$id_proceso_ref?>" />
                        <input type="hidden" name="proceso_code_<?=$id_proceso_ref?>"
                            value="<?=$id_proceso_ref_code?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <?php if (!empty($id_proceso_ref)) { ?>
                            <div class="form-group row">
                                <label class="col-form-label col-sm-4 col-md-3 col-lg-3">
                                    Dirección/Proceso de Origen:
                                </label>
                                <div class="col-9">
                                    <div class="alert alert-danger" style="margin: 0px;">
                                        <?=$nombre_prs_ref?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-1">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-lg-1">
                                    Desde:
                                </label>
                                <div class="col-lg-2">
                                    <input type="hidden" id="init_inicio" name="init_inicio" value="<?=$inicio?>" />

                                    <select name="inicio" id="inicio" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-1">
                                    Hasta:
                                </label>
                                <div class="col-lg-2">
                                    <input type="hidden" id="init_fin" name="init_fin" value="<?=$fin?>" />

                                    <select name="fin" id="fin" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-lg-5">
                                    <label class="alert alert-info text" style="margin: 0px;">
                                        Periodo en el que será accesible el indicador. Es el intervalo de años  en el
                                        que el indicador será gestionado.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Año de referencia:
                                </label>
                                <div class=" col-lg-2">
                                    <input type="hidden" id="init_year" name="init_year" value="<?=$year?>" />

                                    <select name="year" id="year" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $year) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class=" col-lg-8">
                                    <label class="alert alert-info text" style="margin: 0px;">
                                        Este es el año para el cual serán fijados los valores de la escala de colores y
                                        las ponderaciones de las relaciones del indicador con los Objetivos de Trabajo y
                                        Procesos Internos
                                    </label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Indicador No.:
                                </label>
                                <div class="col-lg-10">
                                    <div id="spinner-numero" class="input-group spinner">
                                        <input type="text" name="numero" id="numero" class="form-control"
                                            value="<?=$numero?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa fa-arrow-up"></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Perspectiva:
                                </label>
                                <div class="col-lg-10">
                                    <?php
                                    $persp_inicio= $inicio;
                                    $persp_fin= $fin;
                                    $persp_id_proceso= $id_proceso;
                                    $persp_corte_prs= null;
                                    $refresh_function= "refresh_per()";

                                    require_once "inc/_select_perspectiva.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">
                                    Impacto sobre la perspectiva selecionada:
                                </label>
                                <div class="col-lg-5">
                                    <select id="peso" name="peso" class="form-control" style="display:<?= $display_peso ?>">
                                        <?php for ($k = 0; $k < 8; ++$k) { ?>
                                        <option value="<?= $k ?>" <?php if ($k == $peso) echo "selected='selected'" ?>>
                                            <?= $Tpeso_inv_array[$k] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <?php
                                    $obj_proj= new Tproyecto($clink);
                                    $obj_proj->SetIdProceso($id_proceso);
                                    $obj_proj->SetYear($year);
                                    $result= $obj_proj->listar();

                                while ($row= $clink->fetch_array($result)) {
                                ?>
                                    <input type="hidden" id="id_proyecto_code_<?=$row['id']?>"
                                        name="id_proyecto_code_<?=$row['id']?>" value="<?=$row['id_code']?>" />
                                <?php } ?>

                                <label class="col-form-label col-lg-2">
                                    Proyecto:
                                </label>
                                <div class="col-lg-10">
                                    <select id="proyecto" name="proyecto" class="form-control">
                                        <option value="0">.... </option>
                                        <?php
                                        $clink->data_seek($result);
                                        while ($row= $clink->fetch_array($result)) {
                                            $proj_inicio= date('m/Y', strtotime($row['fecha_inicio_plan']));
                                            $proj_fin= date('m/Y', strtotime($row['fecha_fin_plan']));
                                        ?>
                                        <option value="<?=$row['_id']?>"
                                            <?php if ($row['_id'] == $id_proyecto) echo "selected='selected'" ?>>
                                            <?= "{$row['_nombre']}  / $proj_inicio -- $proj_fin" ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Nombre:
                                </label>
                                <div class="col-lg-10">
                                    <input type="text" id="nombre" name="nombre" maxlength="80" class="form-control"
                                        value="<?=$nombre?>" <?php if ($ind_definido) echo "readonly='readonly'"?>>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-sm-4 col-md-2 col-lg-2">
                                    Unidad de Medida:
                                </label>
                                <div class="col-sm-8 col-md-5 col-lg-5">
                                    <?php
                                    $obj_unidad = new Tunidad($clink);
                                    $obj_unidad->SetIdProceso($_SESSION['id_entity']);
                                    $result = $obj_unidad->listar();

                                    while ($row = $clink->fetch_array($result)) {
                                    ?>
                                    <input type="hidden" id="id_unidad<?=$row['id']?>" name="id_unidad<?=$row['id']?>"
                                        value="<?=$row['id_code']?>" />
                                    <?php
                                    }

                                    $clink->data_seek($result);
                                    ?>

                                    <?php if (!$ind_definido) { ?>
                                    <select name="unidad" id="unidad" class="form-control" style="width:300px">
                                        <option value="0">Seleccione...</option>
                                        <?php while ($row = $clink->fetch_array($result)) { ?>
                                        <option value="<?= $row['id'] ?>"
                                            <?php if ($row['id'] == $id_unidad) echo "selected" ?>>
                                            <?php echo "(" . html_entity_decode($row['nombre']) . ") " . $row['descripcion'] ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                    <?php
                                     } else {
                                         $obj_unidad->GetUnidadById($id_unidad);
                                         $unidad= $obj_unidad->GetNombre();
                                         $descripcion= $obj_unidad->GetDescripcion();
                                     ?>
                                    <input type="hidden" id="unidad" name="unidad" value="<?=$unidad?>" />
                                    <label class="alert alert-danger">
                                        <?="(".html_entity_decode($unidad).") ".$descripcion_um?>
                                    </label>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Periodicidad:
                                </label>
                                <label class="col-form-label col-lg-2">
                                    Plan (corte):
                                </label>
                                <div class="col-lg-3">
                                    <?php if (!$ind_definido) { ?>

                                    <select id="periodicidad" name="periodicidad" class="form-control">
                                        <option value='X'>Seleccione...</option>
                                        <?php foreach ($periodo as $p) { ?>
                                        <option value="<?= $p ?>"
                                            <?php if ($periodicidad == $p) echo "selected='selected'" ?>>
                                            <?= $periodo_inv[$p] ?></option>
                                        <?php } ?>
                                    </select>

                                    <?php } else { ?>
                                    <input type="hidden" id="periodicidad" name="periodicidad"
                                        value="<?= $periodicidad ?>" />
                                    <label class="label"><?= $periodo_inv[$periodicidad] ?></label>
                                    <?php } ?>
                                </div>
                                <label class="col-form-label col-lg-2">
                                    Carga:
                                </label>
                                <div class="col-lg-3">
                                    <?php if (!$ind_definido) { ?>

                                    <select id="carga" name="carga" class="form-control">
                                        <option value="X">Seleccione...</option>
                                        <?php foreach ($periodo as $p) { ?>
                                        <option value="<?= $p ?>" <?php if ($carga == $p) echo "selected='selected'" ?>>
                                            <?= $periodo_inv[$p] ?></option>
                                        <?php } ?>
                                    </select>

                                    <?php } else { ?>
                                    <input type="hidden" id="carga" name="carga" value="<?= $carga ?>" />
                                    <label class="label"><?= $periodo_inv[$carga] ?></label>
                                    <?php } ?>
                                </div>
                            </div>

                        </div><!-- generales -->

                        <!-- descripcion  -->
                        <div class="tabcontent" id="tab2">
                            <div class="checkbox mt-1 mb-2">
                                <label class="col-form-label">
                                    <input type="checkbox" id="chk_cumulative" name="chk_cumulative" value="1" <?php if ($chk_cumulative) echo "checked='checked'"?>/>
                                    Los valores ingresados serán acumulados dentro del corte para determinar el valor al final del corte correspondiente.
                                    Cada corte se inicializa en nulo.
                                </label>    
                            </div>                         
                            <div class="checkbox mt-1">
                                <label class="col-form-label"
                                    title="A partir de los cortes periódicos, o sea a partir de los valores planificados de plan y de real que se reporten para cada uno de los cortes del año, el sistema calculará los acumulados en el año hasta la fecha seleccionada.<br />Ejemplo: se suma el resultado reportado para el cierre de cada mes, sí  la periodicidad escogida fuese mensual.">
                                    <input type="checkbox" id="cumulative" name="cumulative" value="1"
                                        onclick="refreshcal()" <?php if ($cumulative) echo "checked='checked'"?> />
                                    El sistema calculará los acumulados de plan y real, sumando los valores al final de cada corte.
                                </label>
                            </div>

                            <div class="radio mt-2">
                                <label
                                    title="Los valores de plan y real son registrados directamente por los usuarios. No se requiere de cálculos previos.">
                                    <input type="radio" id="if_calculated0" name="if_calculated" value="1"
                                        <?php if (empty($formulated)) echo "checked='checked'" ?>
                                        onclick="refreshcal()" />
                                    Es un dato primario. No requiere de cálculo previo por el sistema.
                                </label>
                            </div>
                            <div id="tr-calculated1" class="radio">
                                <label
                                    title="El valor del sistema se calcula utilizando una fórmula que incluye a otros indicadores con datos primarios.<br />Ejemplo: UTILIDADES= INGRESOS - GASTOS">
                                    <input type="radio" id="if_calculated1" name="if_calculated" value="1"
                                        <?php if (!empty($formulated)) echo "checked='checked'" ?>
                                        onclick="refreshcal()" />
                                    El sistema calculará el valor del indicador a partir de otros indicadores que
                                    contienen datos primarios.
                                </label>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-5 col-md-5 col-lg-3">
                                    Descripción del metodo de cálculo u origen de datos:
                                </label>
                                <div class=" col-3">
                                    <button id="verformula" type="button" class="btn btn-success"
                                        style="margin-left:30px;" title="Escribir Formula de cálculo del indicador"
                                        onclick="go_formula()">
                                        <i class="fa fa-code"></i>Editor de Formula
                                    </button>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12 col-lg-12">
                                    <input type="hidden" id="_calculo" name="_calculo" value="<?=$calculo?>" />
                                    <input type="hidden" id="calculo" name="calculo" value="<?=$calculo?>" />

                                    <textarea name="formula_editor" id="formula_editor"
                                        class="form-control"><?=$obj->replace_formulate($calculo)?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-12">
                                    Descripción u objetivos de la medición:
                                </label>
                                <div class="col-12">
                                    <textarea name="descripcion" rows="4" id="descripcion" class="form-control"
                                        <?php if ($ind_definido) echo "readonly='readonly'"?>><?=$descripcion?></textarea>
                                </div>
                            </div>
                        </div><!-- descripcion  -->


                        <!-- criterio de medicion del indicador -->
                        <div class="tabcontent" id="tab3">
                            <?php
                            $_orange = ($trend != 3 && !empty($id_indicador)) ? $obj->get_orange() : _ORANGE;
                            $_yellow = ($trend != 3 && !empty($id_indicador)) ? $obj->get_yellow() : _YELLOW;
                            $_green = ($trend != 3 && !empty($id_indicador)) ? $obj->get_green() : _GREEN;
                            $_aqua = ($trend != 3 && !empty($id_indicador)) ? $obj->get_aqua() : _AQUA;
                            $_blue = ($trend != 3 && !empty($id_indicador)) ? $obj->get_blue() : _BLUE;

                            $checked = ($trend == 1) ? "checked='checked'" : "";
                            $display = ($trend == 1) ? "table-row-group" : "none";
                            ?>

                            <div class="radio">
                                <label onclick="refreshscale(1)">
                                    <input type="radio" name="trend" id=trend1 value=1 <?= $checked ?> />
                                    El indicador mejora al AUMENTAR los valores reales con respecto al plan. Real (&gt;,
                                    &ge;) Plan. Se calcula Porciento(%)= (Real/Plan)*100
                                </label>
                            </div>
                            <fieldset id="tr_trend1" class="fieldset">
                                <div class="form-group row">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="row ml-4">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-red"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-red">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row col-12 mt-3">
                                                Fracaso
                                            </div> 
                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-orange"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-orange">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c11" name="_c11" value="<?=$_orange?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <!--<span class="input-group-text">%</span>-->
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-yellow"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-yellow">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c12" name="_c12" value="<?=$_yellow?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div> 
                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-green"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-green">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c13" name="_c13" value="<?=$_green?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-aqua"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-blue">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c14" name="_c14" value="<?=$_aqua?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-blue"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-blue">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c15" name="_c15" value="<?=$_blue?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <?php
                            $checked= ($trend == 2) ? "checked='checked'" : "";
                            $display= ($trend == 2)? "table-row-group" : "none";
                            ?>
                            <div class="radio">
                                <label onclick="refreshscale(2)">
                                    <input type="radio" name="trend" id=trend2 <?=$checked?> value=2 />
                                    El indicador mejora al DISMINUIR los valores reales con respecto al plan. Real(&lt;,
                                    &le;) Plan. Se calcula Porciento(%)= (Real/Plan)*100%
                                </label>
                            </div>
                            <fieldset id="tr_trend2" class="fieldset">
                                <div class="form-group row">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-blue">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-blue"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c25" name="_c25"
                                                        value="<?php echo (200 - $_blue);?>" class="form-control in-percentil"
                                                        maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-blue">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-aqua"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c24" name="_c24"
                                                        value="<?php echo (200 - $_aqua);?>" class="form-control in-percentil"
                                                        maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-green"><i
                                                            class="fa fa-arrow-left"></i></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-green"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c23" name="_c23"
                                                        value="<?php echo (200 - $_green);?>" class="form-control in-percentil"
                                                        maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-yellow"><i
                                                            class="fa fa-arrow-left"></i></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-yellow"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c22" name="_c22"
                                                        value="<?php echo (200 - $_yellow);?>" class="form-control in-percentil"
                                                        maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-orange">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-orange"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c21" name="_c21"
                                                        value="<?php echo (200- $_orange);?>" class="form-control in-percentil"
                                                        maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-1">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-red">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-red"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                Fracaso
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <?php
                            if ($trend == 3) {
                                $_yellow= $obj->get_yellow();
                                $_orange= $obj->get_orange();
                                $c_yellow= $obj->get_yellow_cot();
                                $c_orange= $obj->get_orange_cot();
                            } else {
                                $_orange= _ORANGE;
                                $_yellow= _YELLOW;
                                $c_orange= 200 - _ORANGE;
                                $c_yellow= 200 - _YELLOW;
                            }
                            $checked= ($trend == 3)?"checked='checked'":"";
                            $display= ($trend == 3)?"table-row-group":"none";
                            ?>

                            <div class="radio">
                                <label onclick="refreshscale(3)">
                                    <input type="radio" name="trend" id=trend3 value=3 <?=$checked?> />
                                    El indicador solo es óptimo dentro de un INTERVALO definido por dos valores de plan.
                                    Real (&gt;, &ge;) Plan <sub>(cota inferior)</sub> y Real (&lt;, &le;) Plan
                                    <sub>(cota superior)</sub>. Se calcula Porciento(%)= (Real/Plan)*100%
                                </label>
                            </div>

                            <fieldset id="tr_trend3" class="fieldset">
                                <div class="form-group row">
                                    <div class="row">
                                        <div class="col-1">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-red"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-red">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                Fracaso
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-orange"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-orange">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c31" name="_c31" value="<?=$_orange?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-yellow"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-yellow">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c32" name="_c32" value="<?=$_yellow?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-green"></div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-green">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                100% .... 100%
                                                <input type=hidden id="_c35" value=100 />
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-yellow">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-yellow"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c33" name="_c33" value="<?=$c_yellow?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-2">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-orange">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-orange"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                                <div class="input-group">
                                                    <input type="text" id="_c34" name="_c34" value="<?=$c_orange?>"
                                                        class="form-control in-percentil" maxlength="6" />
                                                    <div class="input-group input-group-sm col-sm">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="inputGroup-sizing-sm">%</span>
                                                        </div></div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-1">
                                            <div class="row col-12">
                                                <div class="col-6">
                                                    <div class="alarm-arrow horizontal text-red">
                                                        <i class="fa fa-arrow-left"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="alarm-cicle small bg-red"></div>
                                                </div>
                                            </div>
                                            <div class="row mt-3 mr-1">
                                            Fracaso
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <hr>
                            </hr>
                        </div> <!-- criterio de medicion del indicador -->

                        <!-- impacto de sus inductores o el de los procesos subordinados -->
                        <div class="tabcontent" id="tab5">
                            <legend>
                                Ponderación del Impacto sobre los Objetivos de Trabajo de la Direccion o Proceso y sobre
                                los definidos para las Direcciones o Procesos de nivel o jerarquia superior
                            </legend>

                            <?php require "inc/_objetivot_tabs.inc.ini.php";?>

                            <div id="div-inductores">
                                <table class="table table-striped" data-toggle="table" data-height="330"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo</th>
                                    </thead>

                                    <?php
                                    $cant_objt= 0;
                                    $array_pesos = null;
                                    if (!empty($id_indicador))
                                        $array_pesos = $obj_peso->listar_inductores_ref_indicador($id_indicador, false);

                                    if (isset($obj_prs)) unset($obj_prs);
                                    $obj_prs= new Tproceso($clink);
                                    !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                                    $_id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];
                                    $obj_prs->SetIdProceso($_id_proceso);
                                    $obj_prs->listar_in_order('eq_asc_desc', false, null, null, 'asc');

                                    foreach ($obj_prs->array_procesos as $prs) {
                                        $proceso = $prs['nombre'] . ', ' . $Ttipo_proceso_array[$prs['tipo']];
                                        $_connect = is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                        if ($prs['id'] != $_SESSION['local_proceso_id'])
                                            $_connect = ($_connect != 1) ? 1 : 0;
                                        else
                                            $_connect = 0;

                                        $id_list_prs = $prs['id'];
                                        $restrict_list_to_entity= false;
                                        $with_null_perspectiva = _PERSPECTIVA_ALL;
                                        include "inc/inductor_tabs.inc.php";
                                    }
                                    ?>
                                </table>
                            </div>

                            <input type="hidden" name="cant_objt" id="cant_objt" value="<?=$i_objt?>" />
                            <input type="hidden" name="t_cant_objt" id="t_cant_objt" value="<?=$cant_objt?>" />

                            <script language="javascript">
                            if (parseInt(document.getElementById('t_cant_objt').value) == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Trabajo en el sistema a este nivel de Dirección o Procesos o para sus niveles subordinados. Deberá definirlos para poder acceder a esta funcionalidad."
                                );
                            }
                            </script>
                        </div><!-- impacto de sus inductores o el de los procesos subordinados -->


                        <!-- Listado de procesos -->
                        <div class="tabcontent" id="tab4">
                            <?php
                            unset($obj_prs);
                            unset($array_procesos);
                            $array_procesos= null;

                            $obj_prs= new Tproceso_item($clink);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                            $obj_prs->SetIdProceso($_SESSION['id_entity']);
                            $obj_prs->SetTipo($_SESSION['entity_tipo']);
                            $obj_prs->SetConectado(null);
                            $obj_prs->SetIdResponsable(null);

                            $result_prs_array= $obj_prs->listar_in_order('eq_asc_desc', false, null, false);

                            if (!empty($id_indicador)) {
                                $obj_prs->SetIdIndicador($id_indicador);
                                $obj_prs->SetYear($year);
                                $array_procesos= $obj_prs->GetProcesoIndicador();
                            }

                            $name_form= "findicador";
                            $restrict_prs = null;
                            $restrict_up_prs = false;
                            $restrict_down_prs = true;
                            $filter_by_toshow = true;
                            $create_select_input= true;

                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab4 Procesos-->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/11_indicadores.htm#11_13.1')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>
                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>


</body>

</html>

<?php $_SESSION['obj']= serialize($obj); ?>