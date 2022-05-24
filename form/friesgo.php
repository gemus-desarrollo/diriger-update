<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
;
require_once "../php/class/peso.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/riesgo.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$signal= 'riesgo';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list') {
    $acc= $_SESSION['acc_planrisk'];
    $year= date('Y');
    $id_proceso= $_SESSION['usuario_proceso_id'];

    include_once "inc/_form_pemit.inc.php";
}

if ($action == 'add') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Triesgo($clink);
}

$id_riesgo= $obj->GetIdRiesgo();
$ifestrategico= $obj->GetIfEstrategico();

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
str_replace("\n", " ", addslashes($error));
/*
if (is_null($error) &&!empty($id_riesgo)) {
    $error= "Debe asignar las tareas ha realizar para la gestión del riesgo.";
}
*/
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];

$ifestrategico= !is_null($_GET['estrategico']) ? $_GET['estrategico'] : $obj->GetIfEstrategico();
$sst= !is_null($_GET['sst']) ? $_GET['sst'] : $obj->GetIfSST();
$ma= !is_null($_GET['ma']) ? $_GET['ma'] : $obj->GetIfMedioambiental();
$econ= !is_null($_GET['econ']) ? $_GET['econ'] : $obj->GetIfEconomico();
$reg= !is_null($_GET['reg']) ? $_GET['reg'] : $obj->GetIfRegulatorio();
$info= !is_null($_GET['info']) ? $_GET['info'] : $obj->GetIfInformatico();
$calidad= !is_null($_GET['calidad']) ? $_GET['calidad'] : $obj->GetIfCalidad();
$origen= !is_null($_GET['origen']) ? $_GET['origen'] : $obj->GetIfExterno();

$descripcion= !is_null($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();

$fecha_inicio= $obj->GetFechaInicioPlan();
$year= !empty($_GET['year']) ? $_GET['year'] : (!empty($fecha_inicio) ? date('Y', strtotime($obj->GetFechaInicioPlan())) : date('Y'));
$month= !empty($_GET['month']) ? $_GET['month'] : (!empty($fecha_inicio) ? date('m', strtotime($obj->GetFechaInicioPlan())) : date('m'));

$_fecha_inicio= "{$year}-{$month}-01";
$_fecha_fin= "{$year}-12-31";
if (empty($fecha_inicio))
    $fecha_inicio= $_fecha_inicio;

$fecha_fin= $obj->GetFechaFinPlan();
if (empty($fecha_fin))
    $fecha_fin= $_fecha_fin;

$array= DateTime_object($fecha_inicio);
$_year= $array['y'];
$month_inicio= $array['m'];
$day_inicio= $array['d'];

$array= DateTime_object($fecha_fin);
$month_fin= $array['m'];
$day_fin= $array['d'];

$year_init= $_year - 4;
$year_end= $_year + 4;

$month_init= 1;
$month_end= 12;


$obj_peso= new Tpeso($clink);
/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger();
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);
$badger->set_planrisk();

$url_page= "../form/friesgo.php?signal=$signal&action=$action&exect=$action&menu=riesgo&id_proceso=$id_proceso&year=$year";
$url_page.= "&month=$month&day=$day&estrategico=$ifestrategico&sst=$sst&ma=$ma&econ=$econ&origen=$origen&reg=$reg";

add_page($url_page, $action, 'f');

$text_title= "el riesgo";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>RIESGO EMPRESARIAL</title>

    <?php require_once "inc/tarea_tabs.ini.inc.php";?>

    <script type="text/javascript" src="../js/causas.js?"></script>

    <script language="javascript" type="text/javascript">
    function validar() {
        var text;

        if (DiferenciaFechas($('#fecha_fin').val(), $('#fecha_inicio').val(), 's') < 0) {
            $('#fecha_fin').focus(focusin($('#fecha_fin')));
            alert(
                'La fecha de finalización de la gestión del riesgo  no puede ser anterior a la fecha en la que se identifica o se inicia la gestión del mismo.'
                );
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if (!Entrada($('#lugar').val())) {
            $('#lugar').focus(focusin($('#lugar')));
            alert('Debe identificar el lugar, proceso o actividad donde se manifestaría el riesgo.');
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert('Debe especificar el proceso en el que se origina el riesgo o el que lo gestiona.');
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Debe definir el riesgo.');
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if (!Entrada($('#descripcion').val())) {
            $('#descripcion').focus(focusin($('#descripcion')));
            alert('Debe describir la manifestación del riesgo.');
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if (!$('#ifestrategico0').is(':checked') && !$('#ifestrategico1').is(':checked')) {
            text = 'Debe especificar sí el riesgo puede afectar o no a los objetivos estratégicos de la organización. ';
            text += 'Identifique el riesgo como Estratégico si afecta el cumplimiento de los objetivos estratégicos.'
            $('#ifestrategico0').focus(focusin($('#ifestrategico0')));
            alert(text);
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if ($('#ifestrategico1').is(':checked') && parseInt(document.getElementById('t_cant_objt').value) == 0) {
            text =
                "Sí se trata de un riesgo estratégico deberá definir el impacto sobre al menos uno de los Objetivos estratégícos.";
            $('#t_cant_objt').focus(focusin($('#t_cant_objt')));
            alert(text);
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if (!$("#origen0").is(':checked') && !$("#origen1").is(':checked')) {
            $('#origen0').focus(focusin($('#origen0')));
            text = 'Debe especificar sí el riesgo tiene su origen en el accionar o gestión de la propia organización. ';
            text +=
                'Identifique el riesgo como de origen externo si se origina a partir del accionar de entes externos a la organización.';
            alert(text);
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }
        if ($("#origen0").is(':checked') && $("#origen1").is(':checked'))
            $("#origen").val(2);
        else {
            if ($("#origen0").is(':checked'))
                $("#origen").val(0);
            else
                $("#origen").val(1);
        }

        function _this() {
            if ($('#frecuencia').val() == 0) {
                $('#frecuencia').focus(focusin($('#frecuencia')));
                alert(
                    'Por favor, especifique la ponderación para la probabilidad de manifestación o frecuencia de acurrencia del riesgo.'
                    );
                return false;
            }
            if ($('#impacto').val() == 0) {
                $('#impacto').focus(focusin($('#impacto')));
                alert(
                    'Por favor, especifique la ponderación para la severidad o el daño a producirse de manifestarce el riesgo.'
                    );
                return false;
            }

            document.forms[0].action = '../php/riesgo.interface.php';

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            document.forms[0].submit();
        }

        if (!$('#sst').is(':checked') && !$('#ma').is(':checked') && !$('#econ').is(':checked') && !$('#reg').is(
                ':checked') && !$('#info').is(':checked') && !$('#calidad').is(':checked')) {
            text = "No ha especificado los sistemas de gestión en los que tendría impacto el riesgo de manifestarse. ";
            text += "Ejemplo: Debería de especificar sí de manifestarse tendría impacto en la gestión medioambiental. ";
            text += "Por defecto será considerado como una posible violación de un requisito legal o regulatorio. ";
            text += "¿Desea continuar?";

            confirm(text, function(ok) {
                if (!ok)
                    return;
                else {
                    $('#reg').prop('checked', true);
                    if (!_this())
                        return;
                }
            });
        } else {
            if (!_this())
                return;
        }
    }

    function refresh_ind() {
        if ($('#ifestrategico1').is(':checked')) {
            $('#nav-tab6').show();
        } else {
            $('#nav-tab6').hide();
        }
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        refresh_ind();
        refresh_ajax_causas();

        InitDragDrop();

        $('#div_fecha_reg').datepicker({
            format: 'dd/mm/yyyy'
        });

        if (parseInt($('#t_cant_objt').val()) == 0) {
            $('#div-inductores').hide();
        }

        <?php if (!empty($id_riesgo)) { ?>
        $('ul.nav.nav-tabs li').removeClass('active');
        $(".tabcontent").hide();
        $('#nav-tab8').addClass('active');
        $('#tab8').show();

        tarea_table_ajax('<?=$action?>');
        <?php } ?>

        tinymce.init({
            selector: '#descripcion',
            theme: 'modern',
            height: 260,
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

        try {
            $('#causa').tinymce({
                selector: '#causa',
                theme: 'modern',
                height: 200,
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
        } catch (e) {

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
                <div class="card-header">RIESGO EMPRESARIAL</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Identificación</a></li>
                        <li id="nav-tab9" class="nav-item"><a class="nav-link" href="tab9">Manifestaciones / Consecuencias</a></li>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Clasificación</a></li>
                        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Frecuencia</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Impacto</a></li>
                        <li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Detección</a></li>
                        <li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Objetivos de Trabajo afectados</a></li>
                        <li id="nav-tab7" class="nav-item"><a class="nav-link" href="tab7">Unidades Organizativas Involucradas</a></li>
                        <?php if (!empty($id_riesgo)) { ?>
                        <li id="nav-tab8" class="nav-item"
                            title="Analísis de las causas o condiciones para la manifestación del riesgo">
                            <a class="nav-link" href="tab8">Causas y Condiciones / Peligros</a></li>
                        <li id="nav-tab10" class="nav-item" title="Acciones o tareas que deberán ser ejecutadas">
                            <a class="nav-link" href="tab10">Tareas</a></li>
                        <?php } ?>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar_main()' method='post'>
                        <input type="hidden" id="exect" name="exect" value="<?=$action ?>" />
                        <input type="hidden" name="menu" id="menu" value="riesgo" />

                        <input type="hidden" name="id" id="id" value="<?=$id_riesgo ?>" />
                        <input type="hidden" name="signal" id="signal" value="<?=$signal ?>" />

                        <input type="hidden" id="day_inicio" name="day_inicio" value="<?=$day_inicio?>" />
                        <input type="hidden" id="day_fin" name="day_fin" value="<?=$day_fin?>" />

                        <input type="hidden" id="fecha_inicio" name="fecha_inicio" value="<?=$fecha_inicio?>">
                        <input type="hidden" id="fecha_fin" name="fecha_fin" value="<?=$fecha_fin?>">

                        <input type="hidden" name="id_riesgo" id="id_riesgo" value="<?=$id_riesgo?>" />
                        <input type="hidden" name="id_nota" id="id_nota" value="0" />
                        <input type="hidden" name="id_proyecto" id="id_proyecto" value="0" />
                        <input type="hidden" id="id_auditoria" name="id_auditoria" value="0" />
                        <input type="hidden" id="id_auditoria_code" name="id_auditoria_code" value="0" />

                        <input type="hidden" id="id_causa" name="id_causa" value="0" />

                        <input type="hidden" id="origen" name="origen" value="<?=$origen?>" />

                        <input type="hidden" name="control_page_origen" id="control_page_origen" value="0" />
                        <input type="hidden" name="id_tarea" id="id_tarea" value="0" />
                        <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />
                        
                        
                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-12">
                                    Período de gestión
                                </label>
                                <label class="col-form-label col-1">
                                    Año:
                                </label>
                                <div class="col-2">
                                    <select name="year" id="year" class="form-control input-sm"
                                        onchange="javascript:build_date();">
                                        <?php for ($i = $year_init; $i <= $year_end; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $year) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-1">
                                    Inicio:
                                </label>
                                <div class="col-2">
                                    <select name="month_inicio" id="month_inicio" class="form-control input-sm"
                                        onchange="javascript:build_date();">
                                        <?php for ((int) $i = $month_init; $i <= $month_end; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == (int) $month_inicio) echo "selected='selected'"; ?>>
                                            <?= $meses_array[$i] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-1">
                                    Término:
                                </label>
                                <div class="col-2">
                                    <select name="month_fin" id="month_fin" class="form-control input-sm"
                                        onchange="javascript:build_date();">
                                        <?php for ((int) $i = $month_init; $i <= $month_end; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == (int) $month_fin) echo "selected='selected'"; ?>>
                                            <?= $meses_array[$i] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Actividad o Area:
                                </label>
                                <div class="col-md-10">
                                    <input type="text" name="lugar" rows="2" id="lugar" class="form-control"
                                        value="<?=$obj->GetLugar() ?>" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Unidad Organizativa o Proceso:
                                </label>
                                <div class="col-md-9">
                                    <?php
                                    $top_list_option= "seleccione........";
                                    $id_list_prs= null;
                                    $order_list_prs= 'eq_asc_desc';
                                    $reject_connected= false;
                                    $in_building= ($action == 'add' || $action == 'update') ? true : false;
                                    $only_additive_list_prs= ($action == 'add') ? true : false;

                                    $id_select_prs= $id_proceso;
                                    if (!$config->show_group_dpto_risk)
                                        $restrict_prs= $_SESSION['entity_tipo'] < _TIPO_UEB ? array(_TIPO_ARC, _TIPO_GRUPO, _TIPO_DEPARTAMENTO) : array(_TIPO_ARC);
                                    else
                                        $restrict_prs= array(_TIPO_ARC);
                                    $use_copy_tprocesos= true;

                                    require_once "inc/_select_prs.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Riesgo:
                                </label>
                                <div class="col-md-10">
                                    <textarea id="nombre" name="nombre" rows="6"
                                        class="form-control"><?=$obj->GetNombre()?></textarea>
                                </div>
                            </div>
                        </div><!-- generales -->

                        <!-- Manifestacion del riesgo-->
                        <div class="tabcontent" id="tab9">
                            <textarea name="descripcion" id="descripcion"><?=$descripcion?></textarea>
                        </div> <!-- Manifestacion del riesgo-->

                        <!-- probabilidad -->
                        <div class="tabcontent" id="tab3">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Frecuencia:
                                </label>
                                <div class="col-md-3">
                                    <select name="frecuencia" id="frecuencia" class="form-control">
                                        <option value="0">Seleccione.....</option>
                                        <?php for ($i= 1; $i < 6; ++$i) {?>
                                        <option value="<?=$i?>"
                                            <?php if ($i == $obj->getFrecuencia()) echo "selected='selected'" ?>>
                                            <?=$frecuencia_array[$i]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <label class="col-form-label">
                                Descripción (Razones o criterios por la que se escoge el valor de probabilidad de
                                manifestación o frecuencia de ocurrencia):
                            </label>
                            <hr class="divider" />
                            <div>
                                <textarea name="frecuencia_memo" rows="8" id="frecuencia_memo"
                                    class="form-control"><?=$obj->getFrecuencia_memo()?></textarea>
                            </div>
                        </div> <!-- probabilidad -->

                        <!-- severidad -->
                        <div class="tabcontent" id="tab4">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Severidad:
                                </label>
                                <div class="col-md-3">
                                    <select name="impacto" id="impacto" class="form-control">
                                        <option value="0">Seleccione.....</option>
                                        <?php for ($i = 1; $i < 6; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $obj->getImpacto()) echo "selected='selected'" ?>>
                                            <?= $impacto_array[$i] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <label class="col-form-label">
                                Descripción (Valoración del daño u oportunidad):
                            </label>
                            <hr class="divider" />
                            <div>
                                <textarea name="impacto_memo" rows="8" id="impacto_memo"
                                    class="form-control"><?=$obj->getImpacto_memo()?></textarea>
                            </div>
                        </div><!-- severidad -->

                        <!-- nivel de deteccion del riesgo -->
                        <div class="tabcontent" id="tab5">
                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Nivel de deteción:
                                </label>
                                <div class="col-md-4">
                                    <select name="deteccion" id="deteccion" class="form-control">
                                        <option value="0">Seleccione.....</option>
                                        <?php for ($i = 1; $i < 6; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $obj->getDeteccion()) echo "selected='selected'" ?>>
                                            <?= $deteccion_array[$i] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <label class="col-form-label">
                                Descripción (Alarmas o elementos, o situaciones que permiten su detección):
                            </label>
                            <hr class="divider" />
                            <div>
                                <textarea name="deteccion_memo" rows="8" id="deteccion_memo"
                                    class="form-control"><?=$obj->getDeteccion_memo()?></textarea>
                            </div>
                        </div><!-- nivel de deteccion del riesgo -->

                        <!-- relacion de los procesos a los que pertenece -->
                        <div class="tabcontent" id="tab7">
                            <?php
                            $id= $id_riesgo;

                            $obj_prs= new Tproceso_item($clink);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3)
                                $obj_prs->set_use_copy_tprocesos(false);
                            else
                                $exits = $obj_prs->set_use_copy_tprocesos(true);

                            $result_prs_array= $obj_prs->listar_in_order('eq_desc', true, null, false, 'asc');
                            $cant_prs = $obj_prs->GetCantidad();

                            $array_procesos= null;
                            if (!empty($id_riesgo)) {
                                $obj_prs->SetIdRiesgo($id_riesgo);
                                $array_procesos= $obj_prs->GetProcesosRiesgo();
                            }

                            $name_form= "friesgo";
                            $restrict_up_prs= true;
                            $restrict_prs= $_SESSION['id_entity'] < _TIPO_UEB && !$config->show_group_dpto_risk ? array(_TIPO_ARC, _TIPO_GRUPO, _TIPO_DEPARTAMENTO) : array(_TIPO_ARC);
                            
                            $create_select_input= false;
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- relacion de los procesos a los que pertenece -->

                        <!-- clasificacion del riesgo por categorias -->
                        <div class="tabcontent" id="tab2">
                            <fieldset class="fieldset">
                                <legend>
                                    Relación con los Objetivos Estratégicos
                                </legend>

                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="ifestrategico" id="ifestrategico0" value=0
                                            <?php if (empty($ifestrategico)) echo "checked='checked'" ?>
                                            onchange="refresh_ind()" />
                                        <strong>Operativo.</strong> No afecta el logro de los objetivos Estratégicos
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="radio" name="ifestrategico" id="ifestrategico1" value=1
                                            <?php if (!empty($ifestrategico)) echo "checked='checked'" ?>
                                            onchange="refresh_ind()" />
                                        <strong>Estratégico.</strong> Afecta el cumplimiento de al menos uno de los
                                        Objetivos Estratégicos y Políticas o Lineamientos asociados a la gestión de la
                                        organización
                                    </label>
                                </div>

                            </fieldset>

                            <fieldset class="fieldset">
                                <legend>
                                    Relación con otros Sistemas de Gestión Implementados (Tipo de Riesgo)
                                </legend>

                                <div class="form-group row">
                                    <div class="col-md-5 col-lg-5">
                                        <div class="checkbox">
                                            <label>
                                                <input id="econ" name="econ" type="checkbox" value="1"
                                                    <?php if (!empty($econ)) echo "checked='checked'" ?> />
                                                <strong>Económico o Financiero.</strong> De manifestarse provocaría
                                                perdidas económicas.
                                            </label>
                                        </div>
                                    </div>

                                    <label class="col-form-label col-md-4 col-lg-4 pull-left">
                                        Con una perdidad en Moneda Nacional (CUP) con valor aproximado de
                                    </label>
                                    <div class="col-md-3 col-lg-3 pull-left">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="text" name="valor" id="valor" class="form-control"
                                                value="<?= $obj->GetValue()?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input id="sst" name="sst" type="checkbox" value="1"
                                            <?php if (!empty($sst)) echo "checked='checked'" ?> />
                                        <strong>Seguridad y Salud en el Trabajo.</strong> Está relacionado con la
                                        Seguridad y Salud en el Trabajo
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input id="ma" name="ma" type="checkbox" value="1"
                                            <?php if (!empty($ma)) echo "checked='checked'" ?> />
                                        <strong>Medioambiental.</strong> Tendría impacto en la Gestión Medioambiental
                                        y/o sobre el entorno natural o el medioambiente
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input id="info" name="info" type="checkbox" value="1"
                                            <?php if (!empty($info)) echo "checked='checked'" ?> />
                                        <strong>Informático.</strong> De manifestarse se dañarían los medios o recursos
                                        informáticos o sería una violación de la Seguridad Informática
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input id="calidad" name="calidad" type="checkbox" value="1"
                                            <?php if (!empty($calidad)) echo "checked='checked'" ?> />
                                        <strong>Calidad.</strong> De manifestarse sería una NO CONFORMIDAD en el Sistema
                                        de Gestión de la Calidad.
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input id="reg" name="reg" type="checkbox" value="1"
                                            <?php if (!empty($reg)) echo "checked='checked'" ?> />
                                        <strong>Ilegalidad o Contravención.</strong> Sería una violación de un requisito
                                        legal, regulatorio o interno, explícito o implícito
                                    </label>
                                </div>
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend>Origen</legend>

                                <div class="checkbox">
                                    <label>
                                        <input id="origen0" type="checkbox" value="0"
                                            <?php if (empty($origen) || $origen == 2) echo "checked='checked'" ?> />
                                        <strong>Interno.</strong> Se origina a partir de las propia operaciones y
                                        accionar de la organización.
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input id="origen1" type="checkbox" value="1"
                                            <?php if ($origen >= 1) echo "checked='checked'" ?> />
                                        <strong>Externo.</strong> Su origen esta en el funcionamiento y accionar de
                                        entes o factotes externos a la Organización.
                                    </label>
                                </div>
                            </fieldset>
                        </div> <!-- clasificacion del riesgo por categorias -->

                        <!-- relacion de inductores -->
                        <div class="tabcontent" id="tab6">
                            <label class="alert alert-info">
                                Ponderación del Impacto sobre los Objetivos de Trabajo de la Direccion o Proceso y sobre
                                los definidos para las Direcciones o Procesos de nivel o jerarquia superior
                            </label>

                            <?php require "inc/_objetivot_tabs.inc.ini.php";?>

                            <div id="div-inductores">
                                <table class="table table-striped" data-toggle="table" data-height="330"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <th>No</th>
                                        <th>Ponderación</th>
                                        <th>Objetivo</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $display= 'block';

                                            $array_pesos= null;
                                            $obj_peso->SetIdEscenario(null);
                                            if (!empty($id_riesgo))
                                                $array_pesos= $obj_peso->listar_inductores_ref_riesgo($id_riesgo, false);

                                            if (isset($obj_prs)) unset($obj_prs);
                                            $obj_prs= new Tproceso($clink);
                                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                                            $_id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];
                                            $obj_prs->SetIdProceso($_id_proceso);
                                            $obj_prs->listar_in_order('eq_asc', false, null, null, 'asc');

                                            foreach ($obj_prs->array_procesos as $prs) {
                                                $proceso= $prs['nombre'].', '. $Ttipo_proceso_array[$prs['tipo']];
                                                $_connect= is_null($prs['conectado']) ? 1 : $prs['conectado'];

                                                if ($prs['_id'] != $_SESSION['local_proceso_id'])
                                                    $_connect= ($_connect != 1) ? 1 : 0;
                                                else
                                                    $_connect= 0;

                                                $id_list_prs= $prs['id'];
                                                $with_null_perspectiva= _PERSPECTIVA_ALL;
                                                require "inc/inductor_tabs.inc.php";
                                            }
                                            ?>
                                    </tbody>
                                </table>
                            </div>

                            <input type="hidden" name="cant_objt" id="cant_objt" value="<?=$i_objt?>" />
                            <input type="hidden" name="t_cant_objt" id="t_cant_objt" value="<?=$cant_objt?>" />

                            <script type="text/javascript">
                            if (parseInt(document.getElementById('t_cant_objt').value) == 0) {
                                box_alarm(
                                    "Aun no se han definidos Objetivos de Trabajo en el sistema a este nivel de Dirección o Proceso, o para sus niveles o procesos superiores. Deberá definirlos para poder acceder a esta funcionalidad."
                                );
                            }
                            </script>
                        </div><!-- relacion de inductores -->


                        <!-- Tareas asociadas-->
                        <?php if (!empty($id_riesgo)) { ?>
                        <div class="tabcontent" id="tab10">
                            <div id="ajax-task-table"  class="ajax-task-table">

                            </div>
                        </div>
                        <?php } ?>

                        <!-- descripcion de la causa o Nota de mejora -->
                        <?php if (!empty($id_riesgo)) { ?>
                        <div class="tabcontent" id="tab8">
                            <div id="div-ajax-panel-causa-table" class="container-fluid">

                            </div>

                            <div id="div-ajax-panel-causa-form" class="ajax-panel" data-bind="draganddrop">
                                <div class="card card-primary">
                                    <div class="card-header win-drag">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div
                                                    class="panel-title col-11 win-drag">
                                                    CONDICIÓN O CAUSA</div>

                                                <div class="col-1 pull-right">
                                                    <div class="close">
                                                        <a href="javascript:HideContent('div-ajax-panel-causa-form');"
                                                            title="cerrar ventana">
                                                            <i class="fa fa-close"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="form-group row">
                                            <label class="col-md-12 col-lg-12">
                                                Descripción de la Condición o Causa:
                                            </label>
                                            <div class="col-md-12 col-lg-12">
                                                <textarea id="causa" name="causa" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-sm-5 col-md-4 col-lg-3">
                                                Fecha de manifestación (aproximada):
                                            </label>
                                            <div class="col-sm-4 col-md-3 col-lg-3">
                                                <div class='input-group date' id='div_fecha_reg'
                                                    data-date-language="es">
                                                    <input id="fecha_reg" name="fecha_reg" type="text"
                                                        class="form-control" value="" />
                                                    <span class="input-group-text"><span
                                                            class="fa fa-calendar"></span></span>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (($action == 'update' || $action == 'add') && empty($fecha_final_real)) { ?>
                                        <div id="_button" class="btn-block btn-app">
                                            <button id="btn_agregar" type="button" class="btn btn-primary"
                                                onclick="validar_causa();">Aceptar</button>
                                            <button id="btn_limpiar" type="button" class="btn btn-warning"
                                                onclick="HideContent('div-ajax-panel-causa-form'); limpiar();">Cancelar</button>
                                        </div>
                                        <?php } ?>

                                        <div id="_button_active" class="submited" align="left" style="display:none">
                                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la
                                            operación puede tardar unos minutos ........
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- div-ajax-panel-causa-form -->
                        </div>
                        <?php } ?>
                        <!-- div-ajax-panel-causa-form -->

                        <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

                        </div>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'add' || $action == 'update') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/14_riesgos.htm#14_25.1')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>

                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div>
    </div> <!-- container -->
</body>

</html>