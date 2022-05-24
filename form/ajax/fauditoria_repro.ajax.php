<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/auditoria.class.php";

$_SESSION['debug']= 'no';

$id_auditoria= $_GET['id'];
$signal= $_GET['signal'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;

$time= new TTime();
$actual_year= $time->GetYear();
$actual_month= (int)$time->GetMonth();
$actual_day= $time->GetDay();

$time->SetYear($year);
$time->SetMonth($month);
$time->SetDay($day);
$lastday= $time->longmonth();

if ($signal == 'calendar' && empty($id_usuario)) 
    $id_usuario= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

$obj= new Tauditoria($clink);
$obj->SetIdAuditoria($id_auditoria);
$obj->Set();
$obj->SetYear($year);

$fecha_inicio_plan= $obj->GetFechaInicioPlan();
$fecha_inicio= date('d/m/Y', strtotime($fecha_inicio_plan));
$hora_inicio= date('h:i A', strtotime($fecha_inicio_plan));

$fecha_fin_plan= $obj->GetFechaFinPlan();
$fecha_fin= date('d/m/Y', strtotime($fecha_fin_plan));
$hora_fin= date('h:i A', strtotime($fecha_fin_plan));

if ($signal == 'calendar') 
    $plan= "Anuales y Mensuales";
if ($signal == 'mensual_plan') 
    $plan= "Anuales";

$if_responsable= false;
if (($_SESSION['id_usuario'] == $obj->GetIdResponsable() || $_SESSION['id_usuario'] == $obj->GetIdUsuario()) 
    || $_SESSION['nivel'] >= _ADMINISTRADOR) $if_responsable= true;


$prs_name= null;

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);	
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $prs_name= $obj_prs->GetNombre().", ";
    unset($obj_prs);
} 

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$id_proceso_code= $obj_prs->get_id_code();
$nombre_prs= $obj_prs->GetNombre();
$nombre= $Ttipo_auditoria_array[$obj->GetTipo_auditoria()].', '.$nombre_prs.', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
    
$array_procesos= null;
$obj_prs= new Tproceso($clink);	
$array_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, _TIPO_DIRECCION);

if (!is_null($array_procesos)) {
    $i= 0;
    foreach ($array_procesos as $array) {
        if ($i >0) 
            $prs_name= ",";
        $prs_name.= " ".$array['nombre'];
        ++$i;
    }
    reset($array_procesos);
}

$toshow= false;
$toshow= $obj->get_toshow_plan($id_proceso);
$observacion= $obj->GetObservacion();

$obj->SetIdResponsable($_SESSION['id_usuario']);
?>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>

<script language='javascript' type="text/javascript" charset="utf-8">
function validar() {
    var text;
    var form = document.forms['freproevento'];

    <?php if ($signal == 'calendar' && $if_responsable) { ?>
    if ($('#radio_user').is(':checked')) 
        $('#_radio_user').val(1);
    else 
        $('#_radio_user').val(0);
    <?php } else { ?>
    $('#_radio_user').val(1);
    <?php } ?>

    <?php if ((($signal == 'calendar' || $signal == 'mensual_plan') && $toshow) && (!is_null($array_procesos) || $if_responsable)) { ?>
    if ($('#radio_prs').is(':checked')) 
        $('#_radio_prs').val(1);
    else 
        $('#_radio_prs').val(0);
    <?php } else { ?>
    $('#_radio_prs').val(1);
    <?php } ?>

    var diff1 = DiferenciaFechas($('#fecha_inicio').val(), $('#_fecha_inicio').val(), 'd');
    var diff2 = DiferenciaFechas($('#fecha_fin').val(), $('#_fecha_fin').val(), 'd');
    var diff = (Math.abs(diff1) > 0 || Math.abs(diff2) > 0) ? true : false;

    if (!diff && ($('#hora_inicio').val() == $('#_hora_inicio').val() || $('#hora_fin').val() == $('#_hora_fin')
    .val())) {
        alert("No se puede reprogramar la actividad o tarea a la misma fecha y hora.");
        return;
    }

    var date_inicio = $('#fecha_inicio').val() + ' ' + $('#hora_inicio').val();
    var date_fin = $('#fecha_fin').val() + ' ' + $('#hora_fin').val();
    diff = DiferenciaFechas(date_fin, date_inicio, 's');

    if (diff < 0) {
        alert("La fecha y hora de inicio no puede ser igual o posterior a la fecha y hora en la que finaliza");
        return;
    }
    if (!Entrada($('#observacion').val())) {
        alert('Faltan las observaciones sobre las razones del cambio de fecha.');
        return;
    }

    $('#observacion').val(trim_str($('#observacion').val()));

    if ($('#observacion').val().length < 5) {
        text = "Por favor, la explicación no debe ser tan corta, explicaciones como -ok-,  -si-, -no- ";
        text += "no son aceptados, por favor sea más explicito."
        alert(text);
        return;
    }

    ejecutar('repro');
}
</script>

<script type="text/javascript">
$(document).ready(function() {
    $('#div_fecha_inicio').datepicker({
        format: 'dd/mm/yyyy'
    });
    $('#div_fecha_fin').datepicker({
        format: 'dd/mm/yyyy'
    });
    $('#div_hora_inicio').timepicker({
        minuteStep: 5,
        showMeridian: true
    });
    $('#div_hora_inicio').timepicker().on('changeTime.timepicker', function(e) {
        $('#hora_inicio').val($(this).val());
    });
    $('#div_hora_fin').timepicker({
        minuteStep: 5,
        showMeridian: true
    });
    $('#div_hora_fin').timepicker().on('changeTime.timepicker', function(e) {
        $('#hora_fin').val($(this).val());
    });

    focusin = function(_this) {
        tabId = $(_this).parents('* .tabcontent');
        $(".tabcontent").hide();
        $('#nav-' + tabId.prop('id')).addClass('active');
        tabId.show();
        $(_this).focus();
    }

    <?php if ($if_responsable && $id_tipo_reunion) { ?>
    $('#radio_user').is(':checked', true);
    <?php } ?>

    //When page loads...
    $(".tabcontent").hide(); //Hide all content
    $("ul.nav li:first a").addClass("active").show(); //Activate first tab
    $(".tabcontent:first").show(); //Show first tab content

    //On Click Event
    $("ul.nav li a").click(function() {
        $("ul.nav li a").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tabcontent").hide(); //Hide all tab content

        var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content          
        $("#" + activeTab).fadeIn(); //Fade in the active ID content
        //         $("#" + activeTab + " .form-control:first").focus();
        return false;
    });	

    tinymce.init({
        selector: '#observacion',
        theme: 'modern',
        height: 130,
        language: 'es',
        plugins: [
            'advlist autolink lists link image charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
            '| bullist numlist outdent indent | removeformat',

        content_css: '../css/content.css'
    });

    try {
        $('#observacion').val(<?= json_encode($observacion)?>);
    } catch (e) {
        ;
    }

    <?php if (!is_null($error)) { ?>
    alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>


<ul class="nav nav-tabs" style="margin-bottom: 10px;">
    <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Generales</a></li>
    <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Observaciones</a></li>
</ul>

<form id="freproevento" name="freproevento" class="form-horizontal" action="javascript:validar()" method=post>
    <input type="hidden" name="exect" value="set" />

    <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
    <input type="hidden" name="id" value="<?= $id_auditoria ?>" />

    <input type=hidden name="id_proceso" value="<?= $id_proceso ?>" />
    <input type=hidden name="id_proceso_code" value="<?= $id_proceso_code ?>" />

    <input type="hidden" id="year" name="year" value="<?= $year ?>" />

    <input type="hidden" id="_fecha_inicio" name="_fecha_inicio" value="<?= $fecha_inicio ?>" />
    <input type="hidden" id="_fecha_fin" name="_fecha_fin" value="<?= $fecha_fin ?>" />
    <input type="hidden" id="_hora_inicio" name="_hora_inicio" value="<?= $hora_inicio ?>" />
    <input type="hidden" id="_hora_fin" name="_hora_fin" value="<?= $hora_fin ?>" />

    <input type="hidden" id="extend" name="extend" value="A" />

    <?php
        $radio_user= 0;
        if ($signal != 'calendar') 
            $radio_user= 1;
        ?>

    <input type="hidden" id="_radio_user" name="_radio_user" value="<?=$radio_user?>" />
    <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />

    <input type="hidden" name="menu" value="freproevento" />

    <div class="tabcontent" id="tab3">
        <div class="alert alert-info">
            <strong>Actividad: </strong><?=$nombre?><br />
            <div class="row">
                <div class="col-6">
                    <strong>Inicio: </strong><?=odbc2date($fecha_inicio_plan)?>
                </div>
                <div class="col-6 pull-left">
                    <strong>Fin: </strong><?=odbc2date($fecha_fin_plan)?>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-xs-12 col-2">
                Nueva Inicio:
            </label>
            <div class="col-xs-6 col-4">
                <div class='input-group date' id='div_fecha_inicio' data-date-language="es">
                    <input type='text' id="fecha_inicio" name="fecha_inicio" class="form-control" readonly
                        value="<?=$fecha_inicio?>" />
                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                </div>
            </div>

            <?php
                $time= date('h:i A', strtotime($fecha_inicio_plan));
                ?>
            <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
                <div id='div_hora_inicio' class="input-group bootstrap-timepicker timepicker">
                    <input type="text" id="hora_inicio" name="hora_inicio" class="form-control input-small"
                        value="<?=$time?>" />
                    <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-xs-12 col-2">
                Nueva Fin:
            </label>
            <div class="col-xs-6 col-4">
                <div class='input-group date' id='div_fecha_fin' data-date-language="es">
                    <input type='text' id="fecha_fin" name="fecha_fin" class="form-control" readonly
                        value="<?=$fecha_fin?>" />
                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                </div>
            </div>

            <?php
                $time= date('h:i A', strtotime($fecha_fin_plan));
                ?>
            <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3">
                <div id='div_hora_fin' class="input-group bootstrap-timepicker timepicker">
                    <input type="text" id="hora_fin" name="hora_fin" class="form-control input-small"
                        value="<?=$time?>" />
                    <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                </div>
            </div>
        </div>

        <?php if ((!is_null($array_procesos) || $if_responsable) && $toshow) { ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="radio_prs" id="radio_prs" value="1" />
                Aplicar los cambios a los Planes Anuales de Actividades.
            </label>
        </div>
        <?php } ?>
    </div>

    <div class="tabcontent" id="tab4">
        <div class="form-group row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                <textarea id="observacion" name="observacion" class="form-control"></textarea>
            </div>
        </div>
    </div>

    <div id="_submit" class="btn-block btn-app">
        <button class="btn btn-primary" type="submit"> Aceptar</button>
        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos minutos
        ........
    </div>
</form>