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

$_SESSION['debug']= 'no';

$id_evento= $_GET['id'];
$signal= $_GET['signal'];
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

$obj= new Tevento($clink);
$obj->SetIdEvento($id_evento);
$obj->SetIdUsuario($id_usuario);
$obj->Set();

$nombre= $obj->GetNombre();
$fecha= $obj->GetFechaInicioPlan();
$fecha_inicio= date('d/m/Y', strtotime($fecha));
$hora_inicio= date('h:i A', strtotime($fecha));
$lugar= $obj->GetLugar();
$id_responsable= $obj->GetIdResponsable();
$id_tipo_reunion= $obj->GetIdTipo_reunion();
$id_secretary= $obj->GetIdSecretary();

$fecha= $obj->GetFechaFinPlan();
$hora_fin= date('h:i A', strtotime($fecha));

if ($signal == 'calendar')
    $plan= "Anuales y Mensuales";
if ($signal == 'mensual_plan')
    $plan= "Anuales";

$if_responsable= false;
if (($_SESSION['id_usuario'] == $obj->GetIdResponsable() || $_SESSION['id_usuario'] == $obj->GetIdUsuario())
    || $_SESSION['nivel'] >= _ADMINISTRADOR)
    $if_responsable= true;

if ($id_tipo_reunion && ($id_secretary == $_SESSION['id_usuario'] || $id_responsable == $_SESSION['id_usuario']))
    $if_responsable= true;

$prs_name= null;

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $proceso= $obj_prs->GetNombre().", ";
    unset($obj_prs);
}

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

$obj_user= new Tusuario($clink);
if ($badger->freeassign)
    $obj_user->set_use_copy_tusuarios(false);
else
    $obj_user->set_use_copy_tusuarios(true);

$obj_user->SetIdProceso(null);
$obj_user->set_user_date_ref($fecha_inicio);
$result_user= $obj_user->listar();

$year= date('Y', strtotime($fecha));
$month= date('m', strtotime($fecha));
$day= date('d', strtotime($fecha));
?>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['freproevento'];
            var text;

            <?php if ($signal == 'calendar' && $if_responsable) { ?>
                if ($('#radio_user').is(':checked'))
                    $('#_radio_user').val(1);
                else
                    $('#_radio_user').val(0);
            <?php } ?>
            <?php if ($signal != 'calendar' || !empty($id_tipo_reunion)) { ?>
                $('#_radio_user').val(1);
            <?php } ?>

            $('#_radio_prs').val(0);
            <?php if ((($signal == 'calendar' || $signal == 'mensual_plan') && $toshow) && (!is_null($array_procesos) || $if_responsable)) { ?>
                if ($('#radio_prs2').is(':checked'))
                    $('#_radio_prs').val(2);
                if ($('#radio_prs1').is(':checked'))
                    $('#_radio_prs').val(1);
            <?php } ?>

            $('#_radio_toshow').val(0);
            if ($('#radio_toshow').is(':checked'))
               $('#_radio_toshow').val(1);

            var fecha_inicio= $('#fecha').val()+' '+$('#hora_inicio').val();
            var _fecha_inicio= $('#_fecha_inicio').val()+' '+$('#_hora_inicio').val();
            var diff_inicio= DiferenciaFechas(fecha_inicio, _fecha_inicio, 's');

            var fecha_fin= $('#fecha').val()+' '+$('#hora_fin').val();
            var _fecha_fin= $('#_fecha_inicio').val()+' '+$('#_hora_fin').val();
            var diff_fin= DiferenciaFechas(fecha_fin, _fecha_fin, 's');

            if ($('#_id_responsable').val() == $('#responsable').val()
                && $('#_lugar_init').val().toString().toLowerCase().indexOf($('#lugar').val().toString().toLowerCase()) != -1) {
                if (diff_inicio == 0 && diff_fin == 0) {
                    alert("No se puede reprogramar la actividad o tarea a la misma fecha y hora.");
                    return;
                }
            }

            form.observacion.value= trim_str(form.observacion.value);

            var date_inicio= $('#fecha').val()+' '+$('#hora_inicio').val();
            var date_fin= $('#fecha').val()+' '+$('#hora_fin').val();
            var diff= DiferenciaFechas(date_fin, date_inicio, 's');

            if (diff < 0) {
                alert("la hora de inicio no puede ser igual o posterior a la hora en la que finaliza");
                return;
            }
            if (!Entrada($('#observacion').val())) {
                alert('Faltan las observaciones sobre las razones del la reprogramación de la actividad.');
                return;
            }

            $('#observacion').val(trim_str($('#observacion').val()));

            if ($('#observacion').val().length < 5) {
                text= "Por favor, la explicación no debe ser tan corta, explicaciones como -ok-,  -si-, -no- no son aceptados, ";
                text+= "por favor sea más explicito.";
                alert(text);
                return;
            }

            $('#sendmail').val($('#_sendmail').is(':checked') ? 1 : 0);

            ejecutar('repro');
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#div_fecha').datepicker({
                format: 'dd/mm/yyyy',
                startDate: '01/01/<?=$year?>',
                endDate: '31/12/<?=$year?>'
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

            focusin=function(_this) {
               tabId= $(_this).parents('* .tabcontent');
               $(".tabcontent").hide();
               $('#nav-'+tabId.prop('id')).addClass('active');
               tabId.show();
               $(_this).focus();
           }

           <?php if ($if_responsable && $id_tipo_reunion) { ?>
            $('#radio_user').is(':checked', true);
           <?php } ?>

           <?php if (!empty($id_tipo_reunion)) { ?>
           $('#radio_prs2').is(':checked', true);
           $('#div-radio_prs1').hide();
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
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify '+
                        '| bullist numlist outdent indent | removeformat',

                content_css: '../css/content.css'
            });

            try {
                $('#observacion').val(<?= json_encode($observacion)?>);
            } catch(e) {;}

            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });
    </script>

    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
		<li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Generales</a></li>
		<li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Lugar</a></li>
        <li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Observaciones</a></li>
	 </ul>    

    <form id="freproevento" name="freproevento" class="form-horizontal" action="javascript:validar()"  method=post>
        <input type="hidden" name="exect" value="set" />
        <input type="hidden" name="id_calendar" id="id_calendar" value="<?= $id_usuario ?>" />
        <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
        <input type="hidden" name="id" value="<?= $id_evento ?>" />

        <input type=hidden name="id_proceso" value="<?= $id_proceso ?>" />
        <input type=hidden name="id_proceso_code" value="<?= $id_proceso_code ?>" />

        <input type="hidden" id="year" name="year" value="<?= $year ?>" />

        <input type="hidden" id="_fecha_inicio" name="_fecha_inicio" value="<?= $fecha_inicio ?>" />
        <input type="hidden" id="_hora_inicio" name="_hora_inicio" value="<?= $hora_inicio ?>" />
        <input type="hidden" id="_hora_fin" name="_hora_fin" value="<?= $hora_fin ?>" />

        <input type="hidden" id="sendmail" name="sendmail" value="0" />
        <input type="hidden" id="extend" name="extend" value="A" />

        <input type="hidden" id="_radio_user" name="_radio_user" value="0" />
        <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />
        <input type="hidden" id="_radio_toshow" name="_radio_toshow" value="0" />

        <input type="hidden" id="_lugar_init" name="_lugar_init" value="<?=$lugar?>" />
        <input type="hidden" id="_id_responsable" name="_id_responsable" value="<?=$id_responsable?>" />

        <input type="hidden" name="menu" value="freproevento" />

        <div class="tabcontent" id="tab3">
            <div class="alert alert-info" style="margin-top: 8px;">
                <strong>Actividad: </strong><?=$nombre?><br />
                <div class="row">
                    <div class="col-6">
                        <strong>Inicio: </strong><?=odbc2date($obj->GetFechaInicioPlan())?>
                    </div>
                    <div class="col-6 pull-left">
                        <strong>Fin: </strong><?=odbc2date($obj->GetFechaFinPlan())?>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="row col-4">
                    <label class="col-form-label col-3">
                        Fecha:
                    </label>
                    <div class="col-9">
                        <div class='input-group date' id='div_fecha' data-date-language="es">
                            <input type='text' id="fecha" name="fecha" class="form-control" readonly value="<?=$fecha_inicio?>" />
                            <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                </div>

                <div class="row col-4">
                    <label class="col-form-label col-3">
                        Inicio:
                    </label>

                    <?php
                    $date= $obj->GetFechaInicioPlan();
                    $time= date('h:i A', strtotime($date));
                    ?>
                    <div class="col-9">
                        <div id='div_hora_inicio' class="input-group bootstrap-timepicker timepicker">
                            <input  type="text" id="hora_inicio" name="hora_inicio" class="form-control input-small" value="<?=$time?>" />
                            <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                        </div>
                    </div>
                </div>

                <div class="row col-4">
                    <label class="col-form-label col-3">
                        Fin:
                    </label>

                    <?php
                    $date= $obj->GetFechaFinPlan();
                    $time= date('h:i A', strtotime($date));
                    ?>
                    <div class="col-9">
                        <div id='div_hora_fin' class="input-group bootstrap-timepicker timepicker">
                            <input  type="text" id="hora_fin" name="hora_fin" class="form-control input-small" value="<?=$time?>" />
                            <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <?php if ($signal != 'calendar') { ?>
                    <div class="checkbox col-12">
                        <label class="col-12">
                            <input type="radio" name="radio_prs" id="radio_prs2" value="2" checked='checked' />
                            Reprogramar en la Unidad Organizativa <strong><?=$proceso?></strong> y todas las Unidades subordinadas.
                        </label>
                    </div>

                    <div id="div-radio_prs1" class="checkbox col-12">
                        <label>
                            <input type="radio" name="radio_prs" id="radio_prs1" value="1" />
                            Reprogramar solo en la Unidad Organizativa <strong><?=$proceso?></strong>.
                        </label>
                    </div>
                    <div id="div-radio_toshow" class="checkbox col-12" style="margin-top: 8px; margin-left: 20px;">
                        <label>
                            <input type="checkbox" id="radio_toshow" name="radio_toshow" value="1" />
                            La reprogramación afecta a los Planes Anuales
                        </label>
                    </div>
                <?php } ?>

                <?php if ($signal == 'calendar' && $if_responsable) { ?>
                    <div class="checkbox col-12" style="margin-top: 8px; margin-left: 20px;">
                        <label>
                            <input type="checkbox" name="radio_user" id="radio_user" value="1" <?=$id_tipo_reunion ? "checked='checked'" : ""?> />
                            Reprogramar para todos los implicados en la actividad.
                        </label>
                    </div>
                <?php } ?>

                <div class="checkbox col-12" style="margin-top: 8px; margin-left: 20px;">
                    <label>
                        <input type="checkbox" id="_sendmail" value="_sendmail" value="1" />
                        Será enviado un aviso por correo electrónico a todos los implicados.
                    </label>
                </div>

            </div>

            <?php if ((($signal == 'calendar' || $signal == 'mensual_plan')  && $toshow) && (!is_null($array_procesos) || $if_responsable)) { ?>
            <!--
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="radio_prs" id="radio_prs" value="1" />
                    Aplicar los cambios a los Planes <?= $plan ?>.
                </label>
            </div>
            -->
            <?php } ?>

        </div>


        <div class="tabcontent" id="tab4">
            <div class="form-group row">

            </div>
            <div class="form-group row">
                <label class="col-form-label col-1">
                    Lugar:
                </label>
                <div class="col-11">
                    <input type="text" name="lugar" id="lugar" class="form-control" value="<?= $lugar ?>" />
                </div>
            </div>
        </div>


        <div class="tabcontent" id="tab5">
            <div class="form-group row">
                <div class="col-xs-12 col-12">
                    <textarea id="observacion" name="observacion" class="form-control"></textarea>
                </div>
            </div>
         </div>


        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="submit"> Aceptar</button>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
        </div>
    </form>

