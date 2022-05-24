<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


include_once("../../php/setup.ini.php");
include_once("../../php/class/config.class.php");
session_start();

include('../../php/config.inc.php');
include_once('../../php/class/connect.class.php');
include_once('../../php/class/usuario.class.php');
include_once('../../php/class/tarea.class.php');
include_once('../../php/class/orgtarea.class.php');
include_once('../../php/class/evento.class.php');

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : 0;
$id_tarea= !empty($_GET['id_tarea']) ? $_GET['id_tarea'] : 0;
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'proyecto';
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : $_SESSION['id_usuario'];

$id_responsable= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

if(!empty($id_evento)) {
    $obj_evento= new Tevento($clink);
    $obj_evento->SetIdEvento($id_evento);
    $obj_evento->Set();
    $nombre= $obj_evento->GetNombre();
    $id_responsable= $obj_evento->GetIdResponsable();
    $id_tarea= $obj_evento->GetIdTarea();
    $cumplimiento= $obj_evento->GetCumplimiento();
    $observacion= $obj_evento->GetObservacion();
    $fecha_inicio_plan= $obj_evento->GetFechaInicioPlan();
}

$obj_task= new Ttarea($clink);
$obj_task->Set($id_tarea);
$id_proyecto= $obj_task->GetIdProyecto();
$id_proyecto= !empty($id_proyecto) ? $id_proyecto : 0;
if(empty($id_responsable))
    $id_responsable= $obj_task->GetIdResponsable ();
if(empty($fecha_inicio_plan)) 
    $fecha_inicio_plan= $obj_task->GetFechaInicioPlan ();
if(empty($observacion)) 
    $observacion= $obj_task->GetObservacion ();

if(empty($nombre))
    $obj_task->GetNombre ();

$year= date('Y', strtotime($fecha_inicio_plan));

if($signal == 'proyecto') {
    if(empty($obj_evento))
        $obj_evento= new Tevento($clink);
    $obj_evento->SetYear($year);
    $obj_evento->get_eventos_by_tarea($id_tarea);
    $array_eventos= $obj_evento->array_eventos;
}

$time= new TTime();
$fecha_fin_plan= $obj_evento->GetFechaFinPlan();
$fecha_fin_plan= add_date($fecha_fin_plan, _DIAS_MARGEN);

$actual_date= $time->GetStrTime();
$init= _NO_INICIADO;

if(strtotime($fecha_fin_plan) <= strtotime($actual_date)) 
    $init= _COMPLETADO;

$obj_user= new Tusuario($clink);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         


    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            <?php if($signal == 'proyecto') { ?>
            if(parseInt($('#eventos').val()) == 0) {
                alert("Debe de especificar el día al que hace referencia la evaluación");
                return;
            }        
            <?php } ?>
            if((parseInt($('#horas').val()) == 0 && parseInt($('#minute').val()) == 0) && parseInt($('#cumplimiento').val()) == <?= _COMPLETADO ?>) {
                alert('Debe especificar las horas trabajadas en la tarea durante el día.');
                return;
            }
            if(parseInt($('#horas').val()) > 20) {
                alert('Error en el número de horas. El día solo tiene 24 horas y usted duerme al menos 4.');
                return;
            }
            if(parseInt($('#cumplimiento_').val()) == 0) {
                alert('Debe deinir el estado de avance o cumplimiento de la tarea.');
                return;
            }

            if($('#signal').val() == 'proyecto') {  
                if(parseInt($('#id_evento').val()) == 0) {
                    $('#id').val($('#eventos').val());
                    $('#reg_date').val($("select[name='eventos'] option:selected").text());
                }    
            } else {
                if(parseInt($('#id_evento').val()) != 0) {
                    $('#id').val($('#id_evento').val());
                    $('#id_tarea').val($('#id_tarea').val());
            }   }
     
            ejecutar('hour');          
        }
    </script>

    <script type="text/javascript">	
	$(document).ready(function() {
            //When page loads...
            $(".tab-content").hide(); //Hide all content
            $("ul.nav li:first").addClass("active").show(); //Activate first tab
            $(".tab-content:first").show(); //Show first tab content

            //On Click Event
            $("ul.nav.nav-tabs li").click(function () {
                $("ul.nav.nav-tabs li").removeClass("active"); //Remove any "active" class
                $(this).addClass("active"); //Add "active" class to selected tab
                $(".tab-content").hide(); //Hide all tab content

                var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content          
                $("#" + activeTab).fadeIn(); //Fade in the active ID content
        //         $("#" + activeTab + " .form-control:first").focus();
                return false;
            });     
            
            try {
                $('#observacion_hour').tinymce().destroy();
            } catch(e) {;} 
            
            tinymce.init({
                selector: '#observacion_hour',
                theme: 'modern',
                language: 'es',                
                height: 200,
                plugins: [
                   'advlist autolink lists link image charmap print preview anchor textcolor',
                   'searchreplace visualblocks code fullscreen',
                   'insertdatetime table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify '+
                        '| bullist numlist outdent indent | removeformat | help',

                content_css: '../css/content.css'
            });

            try {
                $('#observacion').val(<?= json_encode($observacion)?>);
            } catch(e) {;}              
            
            <?php if(!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>  
	});
		
    </script>

    <nav>
        <ul class="nav nav-tabs" role="tablist">
            <li id="nav-tab3"><a href="tab3">Generales</a></li>
            <li id="nav-tab5"><a href="tab4">Observaciones</a></li>
            <li id="nav-tab2"><a href="tab5">Registros Anteriores</a></li>
        </ul>
    </nav>
    
 
    <form class="form-horizontal" id="fhoras" name="fhoras" action="javascript:validar()" method=post>
        <input type="hidden" name="exect" value="set" />	
        <input type="hidden" name="id_calendar" id="id_calendar" value="<?=$id_usuario?>" />
        <input type="hidden" name="id_responsable" value="<?=$id_responsable?>" /> 
        <input type="hidden" id="id" name="id" value="<?=!empty($id_evento) ? $id_evento : $id_tarea?>)" />
        <input type="hidden" id="id_evento" name="id_evento" value="<?=!empty($id_evento) ? $id_evento : 0 ?>" />
        <input type="hidden" name="year" value="<?=$year ?>" />
        <input type="hidden" id="id_tarea" name="id_tarea" value="<?=$id_tarea?>" />
        <input type="hidden" id="id_proyecto" name="id_proyecto" value="<?=$id_proyecto?>" />
        <input type="hidden" name="menu" value="horas" />	

        <input type="hidden" name="reg_fecha" id="reg_fecha" value="<?=$fecha_inicio_plan?>" />

        <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />

        <?php 
        $obj_user= new Tusuario($clink);
        $obj_user->SetIdUsuario($id_responsable);
        $email= $obj_user->GetEmail($id_responsable);

        $responsable= $email['nombre'];
        if(!empty($email['cargo'])) 
            $responsable.= ", ".textparse($email['cargo']);
        unset($obj_user);
        ?>
        
        <div id="tab3" class="tab-content">
            <div class="alert alert-info">
                <strong>Tarea: </strong><?=$nombre?>
                <br/>
                <strong>Fecha de Inicio: </strong> <?=odbc2date($fecha_inicio_plan)?>
                <br/>
                <strong>Responsable: </strong> <?=$responsable?>
            </div>             
            
            <?php if($signal == 'proyecto') { ?>
            <div class="form-group">
                <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    Fecha de corte:
                </label> 
                
                <?php foreach ($array_eventos as $id => $evento) { ?>
                <input type="hidden" id="id_evento_code_<?=$id?>" name="id_evento_code_<?=$evento['id_code']?>" />
                <?php } ?>
                
                <div class="col-sm-4 col md-3 col-lg-3">
                    <select id="eventos" name="eventos" class="form-control">
                        <option value="0">seleccione ... </option>
                        <?php 
                        reset($array_eventos);
                        foreach ($array_eventos as $id => $evento) { 
                        ?>
                        <option value="<?=$id?>"><?= odbc2date($evento['fecha_inicio'])?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <?php } ?>
            
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <label class="text col-xs-3 col-sm-3 col-md-2 col-lg-2">
                        Tiempo invertido:
                    </label>
                    
                    <div class="col-xs-3 col-sm-3 col-md-2 col-lg-2">
                        <select name="horas" id="horas" class="form-control">
                            <?php for ($i = 0; $i < 24; ++$i) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $h) echo "selected='selected'" ?>> <?= $i ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    
                    <label class="text col-xs-1 col-sm-1 col-md-1 col-lg-1">
                       horas con
                    </label> 
                    
                    <div class="col-xs-3 col-sm-3 col-md-2 col-lg-2">
                        <select name="minute" id="minute" class="form-control">
                            <?php for ($i = 0; $i < 59; $i += 5) { ?>
                                <option value="<?= $i ?>" <?php if ($i == $m) echo "selected='selected'" ?>> <?= $i ?></option>
                            <?php } ?>       
                        </select>
                    </div>   
                    
                    <label class="text col-xs-1 col-sm-1 col-md-1 col-lg-1">
                        minutos
                    </label>                     
                </div>
            </div>
            
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <label class="radio text col-xs-3 col-sm-3 col-md-2 col-lg-2">
                        Estado:
                    </label>
                    
                    <div class="col-xs-7 col-sm-5 col-md-5 col-lg-4">
                        <select name="cumplimiento" id="cumplimiento" class="form-control">
                            <option value="0">Seleccione ... </option>
                           <?php for($i= $init; $i < _MAX_STATUS_EVENTO; $i++) { 
                                //if($i == $cumplimiento) continue;
                           ?>
                             <option value="<?= $i?>"><?= $eventos_cump[$i] ?></option>
                            <?php } ?>  
                        </select>                         
                    </div>
                </div>
            </div>
        </div>    
            
            
        <div id="tab4" class="tab-content">
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <textarea name="observacion" rows="6" id="observacion_hour" class="form-control"><?=$observacion?></textarea>
                </div>
            </div>
        </div>

        
        <div id="tab5" class="tab-content">
            <table id="table" class="table table-hover table-striped"
                   data-toggle="table"
                   data-height="400"
                   data-pagination="true"
                   data-search="true"
                   data-show-columns="true">
                
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ESTADO</th>
                            <th>FECHA Y HORA</th>
                            <th>RESPONSABLE</th>
                            <th>DURACIÓN</th>
                            <th>OBSERVACIÓN</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $obj_evento->SetIdUsuario(null);
                        $obj_evento->SetCumplimiento(NULL);
                        $result = $obj_evento->listEvento_reg($id_tarea, true);

                        $i = 0;
                        while ($row = $clink->fetch_array($result)) {
                            ?>
                            <tr>
                                <td><?= ++$i ?></td>
                                <td><?= $eventos_cump[$row['cumplimiento']] ?></td>
                                <td><?= odbc2time_ampm($row['cronos']) ?></td>
                                <td>
                                    <?php
                                    if (!empty($row['id_responsable']))
                                        echo $row['responsable'];
                                    elseif (!empty($row['_origen_data'])) {
                                        $origen_data = $obj->GetOrigenData('user', $row['_origen_data']);
                                        if (!empty($origen_data))
                                            echo merge_origen_data_user($origen_data);
                                    }
                                    ?>
                                </td>

                                <td><?php
                                    $hour = (int) floor($row['horas'] / 60);
                                    $hour = !empty($hour) ? "$hour horas<br />" : null;

                                    $min = $row['horas'] - ($hour * 60);
                                    $min = !empty($min) ? "$min minutos" : null;

                                    if (!empty($hour) || !empty($min))
                                        echo "$hour  $min";
                                    ?></td>

                                <td><?= $row['observacion'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
            </table>
        </div>


        <div id="_submit" class="btn-block btn-app">
            <?php if ($action != 'list') { ?> 
                <button class="btn btn-primary" type="submit"> Aceptar</button>
            <?php } ?>  
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
        </div>
        
    </form>
