<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();

require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";
require_once "../../../php/class/proceso_item.class.php";
require_once "../../../php/class/escenario.class.php";
require_once "../../../php/class/usuario.class.php";

require_once "../../../php/class/base_evento.class.php";
require_once "../../../php/class/evento.class.php";

require_once "../php/class/organismo.class.php";
require_once "../php/class/archivo.class.php";
require_once "../php/class/ref_archivo.class.php";


$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$acc= $_SESSION['acc_archive'];
if ($acc == _ACCESO_ALTA 
        || ($acc == _ACCESO_BAJA && $_SESSION['nivel_archive3'] == _USER_REGISTRO_ARCH)
        || ($acc == _ACCESO_MEDIA && $_SESSION['nivel_archive2'] == _USER_REGISTRO_ARCH))
    $action= "add";
else
    $action= "list";

if ($action == 'add' && empty($_GET['id_redirect'])) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$id_archivo= !empty($_GET['id_archivo']) ? $_GET['id_archivo'] : null;
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null;
$id_usuario= $_SESSION['id_usuario'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$date_init= !empty($_GET['date_init']) ? urldecode($_GET['date_init']) : date('d/m/Y');
$date_end= !empty($_GET['date_end']) ? urldecode($_GET['date_end']) : date('d/m/Y');
$year= !empty($date_init) ? date('Y', strtotime(date2odbc($date_init))) : date('Y');

$month= date('m', strtotime($date_init));
$day= date('d', strtotime($date_init));

if (!empty($id_archivo)) 
    $date_end= "31/12/".((int)$year+1);

$if_output= $_GET['if_output'];
if (!is_null($if_output) && empty($if_output)) 
    $_if_output= null;
if ($if_output == 1) 
    $_if_output= 0;
if ($if_output == 2) 
    $_if_output= 1;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null; 
$id_organismo= !empty($_GET['id_organismo']) ? $_GET['id_organismo'] : null; 
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
$id_ejecutante= !empty($_GET['id_ejecutante']) ? $_GET['id_ejecutante'] : null; 
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null; 
$numero= !empty($_GET['numero']) ? $_GET['numero'] : null; 
$keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null; 
$numero_keywords= !empty($_GET['numero_keywords']) ? urldecode($_GET['numero_keywords']) : null; 

$obj_evento= new Tevento($clink);
$obj_user= new Tusuario($clink);

$obj= new Tarchivo($clink);
$obj->SetYear($year);
$obj->SetIfOutput($_if_output);
$obj->SetIdProceso($id_archivo ? null : $id_proceso);
$obj->SetIdOrganismo(null);
$obj->SetIdResponsable($id_responsable);

$_keywords= $keywords ? preg_split("/[\s]*[,;][\s]*/" , strtolower($keywords)) : null;
$_numero_keywords= $numero_keywords ? preg_split("/[\s]*[,;][\s]*/" , strtolower($numero_keywords)) : null;
$obj->do_filter_by_fin_plan= true;
$obj->limited= false;
$result_register= $obj->listar(time2odbc($date_init." 00:00:00"), time2odbc($date_end." 23:59:00"), 2, null, $_keywords, null, $_numero_keywords);  
$array_archivos= $obj->array_archivos;

$max_numero_accords= $obj->GetCantidad();
if (empty($max_numero_accords)) 
    $max_numero_accords= 0;

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso)) {    
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $nombre_prs= $obj_prs->GetNombre();
    $tipo_prs= $Ttipo_proceso_array[(int)$obj_prs->GetTipo()];
    
    $nombre_prs.= ", $tipo_prs";
}

$obj_ref= new Tref_archivo($clink);

$url_page= "../form/faccords.php?signal=$signal&action=$action&menu=evento&id_proceso=$id_proceso&year=$year";
$url_page .= "&id_organismo=$id_organismo&keywords=". urlencode($keywords)."&date_init=".urlencode($date_init);
$url_page .= "&date_end=".urlencode($date_end)."&exect=$action&if_output=$_if_output&id_responsable=$id_responsable";
$url_page.= "&keywords=".urlencode($keywords)."&numero_keywords=$numero_keywords";
        
set_page($url_page); 
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title><?=$title?></title>

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>
        
        <link rel="stylesheet" href="../../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script> 

        <link href="../../../libs/spinner-button/spinner-button.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/spinner-button/spinner-button.js"></script>  
        
        <link href="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>    

        <link href="../../../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">  
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>  
        
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js?version="></script>

        <link href="../../../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version="></script>    
        
        <script type="text/javascript" src="../../../js/time.js?version="></script>

        <script type="text/javascript" src="../../../js/form.js?version="></script>

        <link rel="stylesheet" href="../../../css/alarm.css" />

        <style type="text/css">
            .alarm {
                cursor: pointer;
            }
            .comment-text {
                font-size: 0.9em;
                color: #E74C3C;
            }
        </style>
        
        <script language="javascript">
            var eventos_cump= Array();
            eventos_cump[0]= null;
            var eventos_cump_class= Array();
            eventos_cump_class[0]= null;

            <?php
            for ($j= 1; $j < _MAX_STATUS_EVENTO; ++$j) {
                echo "eventos_cump[$j]= '".$eventos_cump[$j]."'; ";
                echo "eventos_cump_class[$j]= '".$eventos_cump_class[$j]."'; ";
            }
            ?>
        </script>

        <script type="text/javascript">
            var oId;
            var $table;
            var row_accords;
            
            var arrayIndex= new Array();
            var maxIndex=-1;
            var index= -1;
           
            function form_filter(index) {
                if (index == 1) {
                    $('#panel-filter').show();
                    $('#btn_filter0').show();
                    $('#btn_filter1').hide();
                    $('#btn_filter2').show();
                }
                if (index == 0) {
                    $('#panel-filter').hide();
                    $('#btn_filter0').hide();
                    $('#btn_filter1').show();
                    $('#btn_filter2').hide();
                }
                
                if (index == 2) 
                    refreshp();
            }

            function refreshp() {
                var date_init= new Fecha($('#date_init').val());
                var date_end= new Fecha($('#date_end').val());
                
                if (date_init.anio != date_end.anio) {
                    alert("La busqueda no puede incluir a más de un año. Realice la busqueda para cada año involucrado.");
                    return;
                }                
                
                var id_proceso= $('#id_proceso').val();
                var id_ejecutante= $('#ejecutante').val();
                var date_init= $('#date_init').val();
                var date_end= $('#date_end').val();
                var exect= $('#exect').val();
                var id_organismo= $('#organismo').val();
                var cumplimiento= $('#cumplimiento_filter').val();
                var numero_keywords= $('#numero_keywords').val();
                var keywords= $('#keywords').val();
                fix_numero();
                
                var if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2; 
                
                var url= 'faccords.php?action='+exect+'&id_ejecutante='+id_ejecutante+'&id_organismo='+id_organismo;
                url+= '&date_init='+encodeURIComponent(date_init)+'&date_end='+encodeURIComponent(date_end)+'&if_output='+if_output;
                url+= '&cumplimiento='+cumplimiento+'&numero_keywords='+numero_keywords+'&keywords='+encodeURIComponent(keywords);
                url+= '&id_proceso='+id_proceso;
                
                self.location= url;
            }
        
            function reg_accords(index) {
                <?php if ($action == 'add' || $action == 'edit') { ?>
                oId= index;
                displayFloatingDiv('div-ajax-panel-accords', "ESTADO DEL ACUERDO", 80, 0, 5, 10);
                
                $('#time').val($("#time_"+oId).val());
                $('#observacion').val($("#observacion_"+oId).val());
                $('#cumplimiento').val($("#cumplimiento_"+oId).val());
                <?php } else { ?>
                    alert("No tiene permiso para cambiar el estado de cumplimiento de la indicación.");
                <?php } ?>    
            }

            function update_accords() {
                if ($('#cumplimiento').val() == 0) {
                    $("#cumplimiento").focus();
                    alert("Especifique el estado de cumplimiento de la indicación");
                    return;
                }
                
                if (!Entrada($('#observacion').val())) {
                    $("#observacion").focus();
                    alert("Debe describir en un breve resumen lo hecho para el cmplimiento de las indicaciones.");
                    return;
                } 
                
                var value= parseInt($("#cumplimiento").val());
                $("#tab_"+oId).val(0);
                $("#cumplimiento_text_"+oId).removeClass();
                $("#cumplimiento_text_"+oId).addClass(eventos_cump_class[value]);
                $("#cumplimiento_text_"+oId).text(eventos_cump[value]);
                $("#cumplimiento_"+oId).val(value);
                $("#observacion_"+oId).val($('#observacion').val());
                $("#time_"+oId).val($('#fecha_cump').val()+' '+$('#hora_cump').val());
                
                HideContent('div-ajax-panel-accords');
            }
            
            function submit_accords(to_print) {
                var form= document.forms['form-accords'];
                
                var date_init= encodeURI($('#date_init').val());
                var date_end= encodeURI($('#date_end').val());
                var id_organismo= $('#organismo').val();
                var id_ejecutante= $('#ejecutante').val();
                var cumplimiento= $('#cumplimiento').val();
                var numero_keywords= $('#numero_keywords').val();
                var keywords= $('#keywords').val();
                var numero_keywords= $('#numero_keywords').val();
                var id_proceso= $('#id_proceso').val();
                
                fix_numero();
                
                var if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2; 
                
                var url= "&date_init="+date_init+"&date_end="+date_end+"&id_organismo="+id_organismo+'&numero_keywords='+numero_keywords;
                url+= "&id_ejecutante="+id_ejecutante+'&if_output='+if_output+'&cumplimiento='+cumplimiento;  
                url+= "&keywords="+encodeURIComponent(keywords)+'&id_proceso='+id_proceso;
                
                form.action= '../php/register.interface.php?action=add&to_print='+to_print+url;

                parent.app_menu_functions= false;
                $('#_submit').hide();
                $('#_submited').show();

                form.submit();
            }
            
            function closep() {
                var exect= $('#exect').val();
                var date_init= $('#date_init').val();
                var date_end= $('#date_end').val();
                
                var if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2; 
                
                var url= 'lrecord.php?action='+exect+'&date_init='+encodeURIComponent(date_init);
                url+= '&date_end='+encodeURIComponent(date_end)+'&if_output='+if_output;               
                
                var text= "¿Está seguro de querer salir? Si ha realizado algún cambio deberá seleccionar el botón ACEPTAR o perderá los cambios.";
                confirm(text, function(ok) {
                    if (ok) 
                        window.close();
                    else 
                        return false;
                });
            }
            
            function fix_numero() {
                if ($('#numero').val() == 'undefined') 
                    $('#numero').empty();
                if (Entrada($('#numero').val())) {
                    $('#if_output0').is(':checked', true);
                    $('#if_output1').is(':checked', false);
                    $('#if_output2').is(':checked', false);                     
                }
            }
        </script>
        
        <script type="text/javascript">
            var focusin;
            var row_accords;
            
           $(document).ready(function() {
               new BootstrapSpinnerButton('spinner-numero_matter',0,5000);

               InitDragDrop();

                $('#div_date_init').datepicker({
                    format: 'dd/mm/yyyy'
                });      

                $('#div_date_end').datepicker({
                    format: 'dd/mm/yyyy'
                });   
                $('#div_fecha_cump').datepicker({
                    format: 'dd/mm/yyyy'
                });   
                 $('#div_hora_cump').timepicker({
                     minuteStep: 1,
                     showMeridian: true
                });
                $('#div_hora_cump').timepicker().on('changeTime.timepicker', function(e) {
                    $('#hora_cump').val($(this).val());
                });                

               $('#btn_filter1').show(); 
               $('#btn_filter0').hide(); 
               $('#panel-filter').hide(); 
      
                $table= $("#table-accords");
                $table.bootstrapTable('append', row_accords);
                
               <?php if (!is_null($error)) { ?>
               alert("<?=str_replace("\n"," ", addslashes($error))?>");
               <?php } ?>
           });
       </script>       
    </head>

    <body>
        <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <div class="app-body form">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">INDICACIONES / <span class="label label-info" style="font-size: 1.1em"><?=$nombre_prs?></span></div>
                    <div class="card-body">  

                        <form id="form-accords" class="form-horizontal" action='#' method="post">
                            <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                            <input type="hidden" id="id" name="id" value="" />
                            <input type="hidden" id="menu" name="menu" value="form-accords" />

                            <input type="hidden" id="id_usuario" name="id_usuario" value="" />
                            <input type="hidden" name="nivel" id="nivel" value="<?=$_SESSION['nivel']?>" />

                            <input type="hidden" id="id_evento" name="id_evento" value="" />
                            <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
                            <input type="hidden" id="id_proceso_code" name="id_proceso_code" value="<?=$id_proceso_code?>" />


                            <script type="text/javascript">
                                array_usuarios= Array();
                            </script>

                            <div id="panel-filter" class="row">
                               <div class="col-4">
                                   <fieldset class="fieldset" style="margin-bottom: 10px;">
                                        <div class="checkbox">
                                            <label>
                                                <input type="radio" name="if_output" id="if_output0" <?php if ($if_output == 0) echo "checked='checked'"?> value="0" />
                                                Todos las indicaciones                                            
                                            </label>
                                        </div>                                   
                                        <div class="checkbox">
                                            <label>
                                                <input type="radio" name="if_output" id="if_output1" value="1" <?php if ($if_output == 1) echo "checked='checked'"?> />
                                                Solo las recibidas
                                            </label>
                                        </div>                                   
                                        <div class="checkbox">
                                            <label>
                                                <input type="radio" name="if_output" id="if_output2" value="2" <?php if ($if_output == 2) echo "checked='checked'"?> />
                                                Solo las emitidas                                           
                                            </label>
                                        </div>                                   
                                   </fieldset>

                                   <div class="col-12">
                                        <div class="form-group row">
                                             <label class="col-form-label col-5">
                                                Estado de Cumplimiento:
                                            </label> 

                                            <div class="col-7">
                                                <select name="cumplimiento" id="cumplimiento_filter" class="form-control">
                                                     <option value="0">Seleccione...</option>
                                                     <?php
                                                     for ($j = 1; $j < _MAX_STATUS_EVENTO; ++$j) {
                                                         if ($j == _CANCELADO || $j == _DELEGADO || $j == _ESPERANDO) 
                                                             continue;
                                                         ?>
                                                         <option value="<?=$j?>" <?php if ($j == $cumplimiento) echo "selected='selected'"?>><?=$eventos_cump[$j]?></option>
                                                 <?php } ?>
                                                 </select>                                      
                                            </div>
                                        </div>                                   
                                   </div>
                                   
                                   <div class="col-12">
                                        <div class="form-group row">
                                             <label class="col-form-label col-4">
                                                Número:
                                            </label> 
                                            <div class="col-8">  
                                                <input type="text" class="form-control" id="numero_keywords" name="numero_keywords" value="<?=$numero_keywords?>" />
                                            </div>
                                        </div>                                   
                                   </div>
                                   
                                   <div class="col-12">
                                        <button type="button" class="btn btn-primary" id="btn_filter2" onclick="form_filter(2);">
                                            <i class="fa fa-filter"></i>Filtrar
                                        </button>
                                   </div>   
                               </div>   

                                <div class="col-8">
                                    <fieldset class="fieldset">
                                        <div class="container-fluid">
                                            <div class="form-group row">
                                                <div class="col-12" style="margin-bottom: 6px;">
                                                    Cumplimiento en el intervalo de tiempo
                                                </div> 
                                                <label class="col-form-label col-2">Desde:</label>
                                                <div class="col-3">
                                                    <div id="div_date_init" class="input-group date" data-date-language="es">
                                                        <input type="text" class="form-control" id="date_init" name="date_init" value="<?=$date_init?>" readonly="yes" />
                                                        <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                                    </div>                            
                                                </div>

                                                <label class="col-form-label col-2">Hasta:</label>
                                                <div class="col-3">
                                                    <div id="div_date_end" class="input-group date" data-date-language="es">
                                                        <input type="text" class="form-control" id="date_end" name="date_end" value="<?=$date_end?>" readonly="yes" />
                                                        <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                                    </div>                            
                                                </div>   
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-form-label col-3">
                                                   Organismo Involucrado:
                                               </label>

                                                <div class="col-9">
                                                     <select class="form-control" id="organismo" name="organismo">
                                                         <option value="0">Seleccione ... </option>
                                                         <?php 
                                                         $obj_org= new Torganismo($clink);
                                                         $result_org= $obj_org->listar();
                                                         
                                                         while ($row= $clink->fetch_array($result_org)) { ?>
                                                             <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_organismo) echo "selected='selected'"?>><?=$row['nombre']?></option>
                                                         <?php } ?>
                                                     </select>                                       
                                                </div>
                                            </div>  

                                            <div class="form-group row">
                                                <label class="col-form-label col-2">
                                                    Ejecuta:
                                                </label>
                                                <div class="col-10">
                                                    <select class="form-control" id="ejecutante" name="ejecutante">
                                                        <option value="0">Seleccione ... </option>

                                                        <?php
                                                        $obj_user= new Tusuario($clink);
                                                        $obj_user->set_user_date_ref($_date_init);
                                                        $result= $obj_user->listar();
                                                        
                                                        while ($row= $clink->fetch_array($result)) {
                                                        ?>
                                                            <option value="<?=$row['_id']?>" <?php if ($row['_id'] == $id_ejecutante) echo "selected='selected'"?>>
                                                                <?php
                                                                $name= stripslashes($row['nombre']);
                                                                if (!empty($row['cargo'])) 
                                                                    $name.= ", {$row['cargo']}";
                                                                echo textparse($name);
                                                                ?>
                                                            </option>
                                                        <?php } ?>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>    
                                    </fieldset>  

                                    <div class="form-group row mt-3">
                                        <label class="col-form-label col-4">
                                            La indicación contiene el texto:<br/>
                                            <span class="comment-text">separadas por comas (,) o punto y comas (;)</span>
                                        </label>
                                        <div class="col-md-8 col-sm-8">  
                                            <input type="text" class="form-control" id="keywords" name="keywords" value="<?=$keywords?>" />
                                        </div>
                                    </div>    
                               </div>  
                            </div>


                            <!-- indicaciones -->
                            <div class="btn-btn-group btn-app" style="margin: 6px 0px 6px 0px;">
                                <!-- buttom -->
                                <div id="_submit" class="row col-12"> 
                                    <div class="col-4">
                                        <div class="row btn-group d-inline-block">
                                            <button id="btn_filter1" class="btn btn-success" type="button" onclick="form_filter(1);">
                                                <i class="fa fa-angle-double-up"></i>Ver Filtro
                                            </button>

                                            <button id="btn_filter0" class="btn btn-success" type="button" onclick="form_filter(0);">
                                                <i class="fa fa-angle-double-down"></i>Ocultar Filtro
                                            </button>

                                            <button id="btn-print" class="btn btn-info" type="button" style="margin-left: 4px;" onclick="submit_accords(1);">
                                                <i class="fa fa-print"></i>Imprimir
                                            </button>                                         
                                        </div>                                    
                                    </div>

                                    <label class="text-danger col-5" style="font-size: 1.3em;">
                                        Aceptar para que todos los cambios se hagan efectivos
                                    </label>

                                    <div class="col-3">
                                        <?php if ($action == 'update' || $action == 'add') { ?>
                                           <button class="btn btn-primary" type="submit" onclick="submit_accords(0)">Aceptar</button>
                                       <?php } ?>
                                       <button class="btn btn-warning" type="reset" onclick="closep();">Cerrar</button>
                                       <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>                                       
                                    </div>
                                </div>

                                <div id="_submited" style="display:none">
                                    <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                                </div>                                
                            </div>

                            <hr class="divider" />
                            
                            <?php
                            if (isset($obj_user)) unset($obj_user);
                            $obj_user= new Tusuario($clink);
                                
                            $i= 0;
                            $array_archivo_ids= array();
                        
                            foreach ($result_register as $row) {     
                                if (!empty($id_archivo)) {
                                    if ($row['_id'] != $id_archivo) 
                                        continue;
                                } 
                              
                                if (!empty($id_organismo)) {
                                    if (empty($row['id_organismo'])) 
                                        continue;
                                    if ($row['id_organismo'] != $id_organismo) 
                                        continue;
                                }
                                
                                if (array_key_exists($row['_id'], (array)$array_archivo_ids)) 
                                    continue;
                                $array_archivo_ids[$row['_id']]= $row['_id'];    

                                $obj_ref->SetIdArchivo($row['_id']);
                                $obj_ref->_get_user();
                                $array_usuarios= $obj_ref->array_usuarios;

                                if ($row['_id_responsable'] != _USER_SYSTEM) {
                                    $email= $obj_user->GetEmail($row['_id_responsable']);
                                    $responsable= $email['nombre'];
                                    if (!empty($email['cargo'])) 
                                        $responsable.= ", {$email['cargo']}";
                                } else {
                                    $responsable= "ARCHIVO";
                                }
                                
                                foreach ($array_usuarios as $user) {
                                    if (!empty($id_ejecutante) && $id_ejecutante != $user['id']) 
                                        continue;
                                    $obj->SetIdArchivo($row['_id']);
                                    $obj->SetIdUsuario($user['id']);
                                    $row_cump= $obj->getReg();
                                    
                                    if (!empty($cumplimiento) && $cumplimiento != $row_cump['cumplimiento']) 
                                        continue;
                                    
                                    ++$i;
                            ?>
                                    <input type="hidden" id="tab_<?=$i?>" name="tab_<?=$i?>" value="1">
                                    <input type="hidden" id="id_<?=$i?>" name="id_<?=$i?>" value="<?=$row['_id']?>">
                                    <input type="hidden" id="id_code_<?=$i?>" name="id_code_<?=$i?>" value="<?=$row['_id_code']?>">
                                    <input type="hidden" id="id_evento_<?=$i?>" name="id_evento_<?=$i?>" value="<?=$row['id_evento']?>">
                                    <input type="hidden" id="id_evento_code_<?=$i?>" name="id_evento_code_<?=$i?>" value="<?=$row['id_evento_code']?>">

                                    <input type="hidden" id="id_usuario_<?=$i?>" name="id_usuario_<?=$i?>" value="<?=$user['id']?>">
                            <?php } } ?>


                            <script type='text/javascript'>
                                row_accords= [
                                <?php
                                $array_class = array('');
                                reset($result_register);

                                $i = 0;
                                $array_archivo_ids = array();
                                foreach ($result_register as $row) {
                                    if (!empty($id_archivo)) {
                                        if ($row['_id'] != $id_archivo) 
                                            continue;
                                    }
                                    
                                    if (!empty($id_organismo)) {
                                        if (empty($row['id_organismo'])) 
                                            continue;
                                        if ($row['id_organismo'] != $id_organismo) 
                                            continue;
                                    }
                                
                                    $no_ref= $row['codigo'];
                        
                                    if (!empty($numero) && stripos($no_ref, $numero) === false) 
                                        continue;

                                    if (array_key_exists($row['_id'], (array)$array_archivo_ids)) 
                                        continue;
                                    $array_archivo_ids[$row['_id']]= $row['_id'];    

                                    $obj_ref->SetIdArchivo($row['_id']);
                                    $obj_ref->_get_user();
                                    $array_usuarios= $obj_ref->array_usuarios;

                                    foreach ($array_usuarios as $user) {
                                        if (!empty($id_ejecutante) && $id_ejecutante != $user['id']) 
                                            continue;
                                        $obj->SetIdArchivo($row['_id']);
                                        $obj->SetIdUsuario($user['id']);
                                        $row_cump= $obj->getReg();

                                        if (!empty($cumplimiento) && $cumplimiento != $row_cump['cumplimiento']) 
                                            continue;
                                        
                                        ++$i;
                                        if ($i > 1)  
                                            echo ", ";
                                        ?>                                    
                                        {   
                                           id: <?=$i?>,
                                           numero: ''+
                                                '<?=$no_ref?>'+
                                                '',
                                           <?php if ($action == 'add' || $action == 'edit') { ?> 
                                                btn: ''+
                                                     '<a href="#" class="btn btn-info btn-sm" title="registrar situación o cumplimiento" onclick="reg_accords(<?=$i?>,0);">'+
                                                         '<i class="fa fa-check"></i>Registrar'+
                                                     '</a>'+
                                                     '',
                                            <?php } ?>   

                                           cumplimiento: ''+
                                                '<label class="alarm <?=$eventos_cump_class[$row_cump['cumplimiento']]?>" id="cumplimiento_text_<?=$i?>" onclick="reg_accords(<?=$i?>)">'+
                                                     '<?=$eventos_cump[$row_cump['cumplimiento']]?>'+
                                                '</label>'+                                         
                                                '',

                                            nombre: ''+
                                                 '<p>'+
                                                     '<?= textparse($row['indicaciones'], true)?>'+
                                                '</p>'+
                                                  '<input type="hidden" id="observacion_<?=$i?>" name="observacion_<?=$i?>" value="<?=$row_cump['observacion']?>"/>'+
                                                  '<input type="hidden" id="cumplimiento_<?=$i?>" name="cumplimiento_<?=$i?>" value="<?=$row_cump['cumplimiento']?>" />'+ 
                                                  '<input type="hidden" id="cumplimiento_init_<?=$i?>" name="cumplimiento_init_<?=$i?>" value="<?=$row_cump['cumplimiento']?>">'+
                                                '',

                                            responsable: ''+
                                                 '<?=$responsable?>'+   
                                                 '',

                                            ejecuta:  ''+             
                                               <?php
                                               $mail= null;
                                               if ($user['id'] != _USER_SYSTEM) {
                                                    $mail= $obj_user->GetEmail($user['id']);
                                                    echo "'".textparse($mail['nombre'], true).'<br />'.textparse($mail['cargo'], true)."'+";
                                               }
                                               ?>
                                               '<input type="hidden" id="id_responsable_<?=$i?>" name="id_responsable_<?=$i?>" value="<?=$row['id_responsable'] ?>" />'+                                                        
                                              '',

                                            fecha: ''+
                                               '<?= odbc2time_ampm($row['fecha_fin_plan'])?>'+
                                               '<input type="hidden" id="time_<?=$i?>" name="time_<?=$i?>" value="<?=odbc2time_ampm($row['fecha_fin_plan'], null, true)?>" />'+
                                                '',

                                            registro: ''+
                                               '<?= odbc2time_ampm($row['fecha_entrega'])?>'+
                                                ''                                                
                                    }
                                    <?php } } ?>
                                ];
                            </script>                            

                            <table id="table-accords" class="table table-hover table-striped"
                                  data-toggle="table"
                                  data-height="680"
                                  data-unique-id="id"> 
                               <thead>
                                   <tr>
                                       <th data-field="id">No.</th>
                                       <th data-field="numero">Número</th>
                                       <?php if ($action == 'add' || $action == 'edit') { ?> 
                                            <th data-field="btn"></th>
                                       <?php } ?>
                                       <th data-field="cumplimiento">Cumplimiento</th>
                                       <th data-field="nombre">Indicación</th>
                                       <th data-field="responsable">Responsable</th>
                                       <th data-field="ejecuta">Ejecuta</th>
                                       <th data-field="fecha">Fecha<br />Cumplimiento</th>
                                       <th data-field="registro">Registro</th>
                                   </tr>
                               </thead>
                           </table>

                           <input type="hidden" id="cant_" name="cant_" value="<?=$i?>" />

                            <script type="text/javascript">
                                maxIndex= <?= $i-1 ?>;

                                <?php 
                                $k= 0;
                                for ($j= 1; $j <= $i; ++$j) { 
                                ?>
                                    arrayIndex['-'+<?=$j?>]= <?=$k++?>;
                                <?php } ?>  
                            </script> 

                            <!-- div-ajax-panel-accords -->
                            <div id="div-ajax-panel-accords" class="card card-primary ajax-panel" data-bind="draganddrop">
                                 <div class="card-header">
                                     <div class="row win-drag">
                                         <div class="panel-title ajax-title col-11 win-drag">ESTADO DE LA INDICACION</div>

                                         <div class="col-1 pull-right close">
                                             <a href= "javascript:HideContent('div-ajax-panel-accords');" title="cerrar ventana">
                                                 <i class="fa fa-close"></i>
                                             </a>
                                         </div> 
                                     </div>              
                                 </div>

                                 <div class="card-body"> 
                                     <div class="form-group row">
                                         <div class="col-md-7 col-lg-7">
                                             <label class="col-form-label col-3">Fecha de cumplimiento: </label>
                                             <div class="col-xs-5 col-5">
                                                 <div id="div_fecha_cump" class="input-group date" data-date-language="es">
                                                     <input type="text" class="form-control" id="fecha_cump" name="fecha_cump" readonly value="" />
                                                     <span class="input-group-text"><span class="fa fa-calendar"></span></span>                            
                                                 </div>                        
                                             </div>   
                                             <div class="col-xs-5 col-4">
                                                 <div class="input-group bootstrap-timepicker timepicker" id='div_hora_cump'>
                                                     <input  type="text" id="hora_cump" name="hora_cump" class="form-control input-small" readonly value="" />
                                                     <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                                                 </div>	      				
                                             </div>                                            
                                         </div>

                                         <div class="col-md-5 col-lg-5">
                                             <label class="col-form-label col-md-4 col-lg-4">Estado de ejecución:</label>
                                             <div class="col-md-8 col-lg-8">
                                                 <select name="cumplimiento" id="cumplimiento" class="form-control">
                                                     <option value="0">Seleccione...</option>
                                                     <?php
                                                     for ($j = 1; $j < _MAX_STATUS_EVENTO; ++$j) {
                                                         if ($j == _CANCELADO || $j == _DELEGADO || $j == _ESPERANDO) 
                                                             continue;
                                                         ?>
                                                         <option value="<?= $j ?>"><?=$eventos_cump[$j]?></option>
                                                 <?php } ?>
                                                 </select>                       
                                             </div>                    
                                         </div>
                                     </div>

                                     <div class="form-group row">
                                        <label class="col-form-label col-md-12 pull-left">
                                             Observación:
                                         </label>                                    
                                     </div>

                                     <div class="form-group row">
                                         <div class="col-md-12">
                                             <textarea name="observacion" id="observacion" class="form-control" rows="7"></textarea>
                                         </div>
                                     </div>

                                     <hr></hr>
                                     <div class="btn-block btn-app" style="margin-top: 10px;">
                                         <button type="button" class="btn btn-primary" onclick="update_accords()" title="Registrar estado del acuerdo">Aceptar</button>
                                         <button type="button" class="btn btn-warning" onclick="HideContent('div-ajax-panel-accords')" title="Cerrar">Cerrar</button>
                                     </div>
                                 </div>
                             </div> <!--div-ajax-panel-accords -->                                 
                                
                        </form>
                    </div> <!-- panel-body -->  
                </div>
            </div>

        </div>

    </body>
</html>
