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
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/peso.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tinductor($clink);
}

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];

$time= new TTime;
$current_year= $time->GetYear();

if (empty($year)) 
    $year= $current_year;

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$if_send_up= !empty($_GET['if_send_up']) ? $_GET['if_send_up'] : $obj->GetIfSend_up();
$if_send_down= !empty($_GET['if_send_down']) ? $_GET['if_send_down'] : $obj->GetIfSend_down();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso = $_SESSION['id_entity'];
$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : $obj->GetIdPerspectiva();
$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$peso= !is_null($_GET['peso']) ? $_GET['peso'] : $obj->GetPeso();

$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);
$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();

$id_inductor= $obj->GetIdInductor();
$id= $id_inductor;
$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_proceso_sup= $id_proceso;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo= $obj_prs->GetTipo();
$nombre_prs= $obj_prs->GetNombre();
$conectado= $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

if (empty($inicio))
    $inicio= $year;
if (empty($fin))
    $fin= $year;

if ($year > $_fin) 
    $year= $_fin;
if ($year < $_inicio) 
    $year= $_inicio;

$obj_peso= new Tpeso($clink);
$obj_peso->SetYear($year);

if (!empty($id_inductor)) {
    $obj_peso->SetInicio($inicio);
    $obj_peso->SetFin($fin);
    $obj_peso->SetIdInductor($id);
}

$url_page= "../form/finductor.php?signal=$signal&action=$action&menu=inductor&exect=$action";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day&id_perspectiva=$id_perspectiva";
$url_page.= "&inicio=$inicio&fin=$fin";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>OBJETIVOS DE TRABAJO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    var id_entity= <?=$_SESSION['id_entity']?>;

    function refreshp() {
        var inicio = parseInt($("#inicio").val());
        var fin = parseInt($("#fin").val());
        var year = parseInt($("#year").val());

        if (inicio > fin) {
            $('#inicio').focus(focusin($('#inicio')));
            alert(
                "El año de inicio de la vigencia del Objetivo de Trabajo no puede ser superior al año de en el que termina"
            );
            return;
        }

        if (year < inicio || year > fin) {
            $('#year').focus(focusin($('#year')));
            alert(
                "El año de referencia debe de estar en entre los año en los que se gestionara el Objetivo de Trabajo."
            );
            if (year < inicio) 
                $("#year").val(inicio);
            if (year > fin) 
                $("#year").val(fin);
            return;
        }

        var id_proceso = $('#proceso').val();
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURI($('#descripcion').val());
        var id_perspectiva = $('#perspectiva').val();
        var numero = $('#numero').val();
        var if_send_up = $('#if_send_up').is(':checked') ? 1 : 0;
        var if_send_down = $('#if_send_down').is(':checked') ? 1 : 0;

        var peso= 0;
        if ($('#proceso').val() == id_entity) {
            $('#div-peso').show();
            peso= $('#peso').val();
        } else {
            $('#div-peso').hide();
            peso= 0;
        }
        
        var url = '&id_proceso=' + id_proceso + '&inicio=' + inicio + '&fin=' + fin + '&nombre=' + nombre +
            '&descripcion=' + descripcion + '&peso=' + peso;
        url += '&year=' + year + '&id_perspectiva=' + id_perspectiva + '&numero=' + numero + '&if_send_up=' +
            if_send_up + '&if_send_down=' + if_send_down;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        self.location.href = 'finductor.php?version=&action=<?php echo $action ?>' + url;
    }

    function validar() {
        var text;

        var inicio = parseInt($("#inicio").val());
        var fin = parseInt($("#fin").val());
        var year = parseInt($("#year").val());

        if (inicio > fin) {
            $('#fin').focus(focusin($('#fin')));
            alert(
                "El año de inicio de la vigencia del Objetivo de Trabajo no puede ser superior al año de en el que termina"
            );
            return;
        }
        if (year < inicio || year > fin) {
            $('#year').focus(focusin($('#year')));
            alert(
                "El año de referencia debe de estar en entre los año en los que se gestionará el Objetivo de Trabajo."
            );
            if (year < inicio) 
                $("#year").val(inicio);
            if (year > fin) 
                $("#year").val(fin);
            return;
        }
        if ($('#numero').val() == 0) {
            $('#numero').focus(focusin($('#numero')));
            alert(
                "No ha especificado un número para identificar este Objetivo. De este número dependerá el orden al listarlo."
            );
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el Objetivo de Trabajo.');
            return;
        }
        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert("No ha especificado la Unidad Organizativa o Ptroceso al que pertenece este Objetivo de Trabajo");
            return;
        }

        if ($('#proceso').val() == id_entity && $('#peso').val() == 0) {
            $('#peso').focus(focusin($('#peso')));
            alert("No ha definido el peso o importancia del Objetivo de Trabajo para la evaluación general de la entidad");
            return;            
        }

        var conectado = $('#proceso_conectado_' + $('#proceso').val()).val();
        conectado = conectado != <?=_NO_LOCAL?> ? true : false;

        <?php if ($_SESSION['if_send_up'] || $_SESSION['if_send_down']) { ?>
        if (conectado && (!$('#if_send_up').is(':checked') && !$('#if_send_down').is(':checked'))) {
            $('#if_send_up').focus(focusin($('#if_send_up')));

            text =
                "No ha especificado la dirección en que migrará la información relativa a este Objetivo de trabajo. ";
            text +=
                "Las direcciones superiores o subordinadas no recibirán información relativa a este Objetivo. ¿Desea continuar?";
            confirm(text, function(ok) {
                if (!ok)
                    return;
                else {
                    parent.app_menu_functions = false;
                    $('#_submit').hide();
                    $('#_submited').show();

                    document.forms[0].action = '../php/inductor.interface.php';
                    document.forms[0].submit();
                }
            });
        } else {
            <?php } ?>
            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            document.forms[0].action = '../php/inductor.interface.php';
            document.forms[0].submit();
            <?php if ($_SESSION['if_send_up'] || $_SESSION['if_send_down']) { ?>
        }
        <?php } ?>
    }
    </script>

    <script type="text/javascript">
    var trId;

    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        function set_numero(val) {
            $('#numero').val(val);
        }

        if ($('#t_cant_obji').val() == 0) {
            $('#div-objetivos').hide();
        }
        if ($('#t_cant_indi').val() == 0) {
            $('#div-indicadores').hide();
        }
        if ($('#proceso').val() == id_entity)
            $('#div-peso').show();
        else
            $('#div-peso').hide();

        <?php if (!$_SESSION['if_send_up']) { ?>
        $('#if_send_up').attr("disabled", "disabled");
        <?php } ?>
        <?php if (!$_SESSION['if_send_down']) { ?>
        $('#if_send_down').attr("disabled", "disabled");
        <?php } ?>

        tinymce.init({
            selector: '#descripcion',
            theme: 'modern',
            height: 300,
            language: 'es',
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
                '| bullist numlist outdent indent | removeformat | help',

            content_css: '../css/content.css'
        });

        try {
            $('#descripcion').val(<?= json_encode($descripcion)?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">OBJETIVO DE TRABAJO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"
                            title="Descripción de la estrategia a seguir, u observaciones relativas al Objetivo de Trabajo definido">
                            <a class="nav-link" href="tab2">Descripción</a>
                        </li>
                        <li id="nav-tab3" class="nav-item"
                            title="Ponderación del impacto o efecto sobre sus Objetivos Estratégicos o los que han sido  
                            definidos para las Direcciones y/o Procesos Superiores">
                            <a class="nav-link" href="tab3">Impacto sobre los Objetivos Estratégicos</a>
                        </li>
                        <!--
                        <li id="nav-tab5"  class="nav-item" class="nav-item" 
                            title="Definir el Impacto sobre los Objetivos de Trabajo de las unidades de dirección superiores, 
                            vigente en el año de referencia">
                            <a class="nav-link" href="tab5">Impacto sobre los Objetivos de Trabajo Superiores</a>
                        </li>   
                        -->                         
                        <li id="nav-tab4" class="nav-item"
                            title="Ponderación del impacto o efecto de los Indicadores sobre este Objetivo de Trabajo">
                            <a class="nav-link" href="tab4">Efecto de los Indicadores</a>
                        </li>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar()' method=post>
                        <input type="hidden" name="exect" value="<?= $action ?>" />
                        <input type="hidden" name="id" value="<?=$id_inductor ?>" />
                        <input type="hidden" name="menu" value="inductor" />

                        <input type="hidden" id="month" name="month" value="<?=$month ?>" />
                        <input type="hidden" id="day" name="day" value="<?=$day ?>" />

                        <input type="hidden" id="_id_perspectiva" name="_id_pespectiva"
                            value="<?=$obj->GetIdPerspectiva() ?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-1">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-lg-1">
                                    Desde:
                                </label>
                                <div class=" col-lg-2">
                                    <select name="inicio" id="inicio" class="form-control input-sm"
                                        onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-1">
                                    Hasta:
                                </label>
                                <div class=" col-lg-2">
                                    <select name="fin" id="fin" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-lg-5">
                                    <label class="alert alert-info">
                                        Periodo en el que será accesible el Objetivo de Trabajo. Es el intervalo de años en el
                                        que esta vigente y constituye una meta para la organización, por lo que es calculado o
                                        medible por el sistema.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Año de referencia:
                                </label>
                                <div class=" col-lg-2">
                                    <select name="year" id="year" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $year) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class=" col-lg-8">
                                    <label class="alert alert-info">
                                        Año para el cual son fijadas las ponderaciones del efecto del Objetivo de
                                        Trabajo sobre los Objetivos Estratégicos, y el de los Indicadores sobre este
                                        Objetivo de Trabajo.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-3 col-md-2 col-lg-2">
                                    Objetivo No.:
                                </label>
                                <div class=" col-sm-9 col-md-10 col-lg-10">
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

                                    $restrict_prs = !$config->dpto_with_objetive ? array(_TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_GRUPO) : null;
                                    $id_select_prs = $id_proceso;
                                    require_once "inc/_select_prs_down.inc.php";
                                    ?>
                                </div>
                            </div>

                            <div class="form-group row" id="div-peso">
                                <label class="col-form-label col-md-5">
                                    Impacto sobre la evaluación de <?="{$nombre_prs}, ".$Ttipo_proceso_array[$tipo]?>:
                                </label>
                                <div class=" col-4">
                                    <select name="peso" class="form-control">
                                        <?php for ($k= 0; $k < 8; ++$k) { ?>
                                        <option value="<?=$k?>" <?php if ($k == $peso) echo "selected='selected'"?>>
                                            <?=$Tpeso_inv_array[$k]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-sm-3 col-md-2 col-lg-2">
                                    Perspectiva:
                                </label>
                                <div class="col-sm-9 col-md-10 col-lg-10">
                                    <?php
                                    $refresh_function= null;
                                    $persp_id_proceso= $id_proceso;
                                    $year_persp= $year;
                                    require_once "inc/_select_perspectiva.inc.php";
                                    ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-1">
                                    Objetivo:
                                </label>
                                <div class="col-md-11 col-lg-11">
                                    <textarea id="nombre" name="nombre" class="form-control input-sm" rows="4"><?= $nombre ?></textarea>
                                </div>
                            </div>
                        </div><!-- generales -->


                        <!-- Descripcion -->
                        <div class="tabcontent" id="tab2">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="if_send_up" name="if_send_up" value="1"
                                        <?php if (!empty($if_send_up)) echo "checked='checked'" ?> />
                                    Transmitir este objetivo de trabajo a la Dirección Empresarial o Proceso superior.
                                    Será transmitido su estado de cumplimiento periódicamente.
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="if_send_down" name="if_send_down" value="1"
                                        <?php if (!empty($if_send_down)) echo "checked='checked'" ?> />
                                    Transmitir este objetivo de trabajo a las Direcciones Empresariales o Procesos
                                    subordinados. No será transmitido su estado de cumplimiento.
                                </label>
                            </div>

                            <hr>
                            </hr>
                            <div class="form-group row">
                                <div class="col-lg-12">
                                    <textarea name="descripcion" rows=15 id="descripcion"
                                        class="form-control input-sm"><?= $descripcion ?></textarea>
                                </div>
                            </div>
                        </div><!-- Descripcion -->


                        <!-- Efecto sobre los objetivos estrategicos de la unidad o proceso superior-->
                        <div class="tabcontent" id="tab3">
                            <?php
                            $array_pesos= null;

                            if (!empty($id_inductor))
                                $array_pesos= $obj_peso->listar_objetivos_ref_inductor($id_inductor, false);

                            require_once "inc/_objetivo_tabs.inc.ini.php";
                            ?>
                            <div id="div-objetivos">
                                <table class="table table-striped" data-toggle="table" data-height="300"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        reset($obj_prs->array_cascade_up);
                                        foreach ($obj_prs->array_cascade_up as $prs) {
                                            $proceso= $prs['nombre'].', '. $Ttipo_proceso_array[$prs['tipo']];
                                            $_connect= is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                            if ($prs['_id'] != $_SESSION['local_proceso_id'])
                                                $_connect= ($_connect != 1) ? 1 : 0;
                                            else
                                                $_connect= 0;

                                            $id_list_prs= $prs['id'];
                                            $restrict_list_to_entity= false;
                                            include "inc/objetivo_obji_tabs.inc.php";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <input type="hidden" name="cant_obji" id="cant_obji" value="<?=$i_obji?>" />
                            <input type="hidden" name="t_cant_obji" id="t_cant_obji" value="<?=$cant_obji?>" />

                            <script language="javascript">
                            if (document.getElementById('t_cant_obji').value == 0) {
                                box_alarm(
                                    "En el sistema no se han definido Objetivos Estratégicos para esta Dirección o proceso o para las Direcciones o procesos de nivel superior."
                                );
                            }
                            </script>
                        </div><!-- Efecto sobre los objetivos estrategicos de la unidad o proceso superior-->

                        <!-- impacto de sus inductores o el de los procesos subordinados -->
                        <!--
                        <div class="tabcontent" id="tab5">
                            <legend>
                                Ponderación del Impacto sobre los Objetivos de Trabajo de la Direccion o Proceso de nivel o jerarquia superior
                            </legend>

                            <?php // require "inc/_objetivot_tabs.inc.ini.php";?>

                            <div id="div-inductores">
                                <table class="table table-striped" data-toggle="table" data-height="330"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo</th>
                                    </thead>

                                    <?php
                                    /*
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
                                    */
                                    ?>
                                </table>
                            </div>

                            <input type="hidden" name="cant_objt" id="cant_objt" value="<?=$i_objt?>" />
                            <input type="hidden" name="t_cant_objt" id="t_cant_objt" value="<?=$cant_objt?>" />
                                -->

                        <!-- indicadors del inductor -->
                        <div class="tabcontent" id="tab4">
                            <legend>
                                Ponderación del Impacto de los indicadores sobre este Objetivo de Trabajo
                            </legend>

                            <div id="div-indicadores">
                                <?php
                                    $obj_peso->SetIdInductor($id);
                                    $obj_peso->SetYear($year);

                                    $array_indicadores= null;
                                    if (!empty($id))
                                        $array_indicadores= $obj_peso->listar_indicadores_ref_inductor($id, false);

                                    $create_select_input= true;
                                    include "inc/indicador.inc.php";
                                    ?>
                            </div>

                            <script language="javascript">
                            if (document.getElementById('t_cant_multiselect-inds').value == 0) {
                                box_alarm(
                                    "No existen indicadores definidos en el sistema. Por favor, deberá definir los indicadores y luego acceder a esta funcionalidad."
                                );
                            }
                            </script>
                        </div> <!-- indicadors del inductor -->

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/10_inductores.htm#10_12.1')">Ayuda</button>
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