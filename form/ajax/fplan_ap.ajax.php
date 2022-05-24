<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso_item.class.php";

require_once "../../php/class/base_evento.class.php";
require_once "../../php/class/plan_ci.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/code.class.php";

$year= $_GET['year'];
$month= !empty($_GET['month']) ? $_GET['month'] : 0;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;		
$action= !empty($_GET['action']) ? $_GET['action'] : 'object';
$if_jefe= !empty($_GET['if_jefe']) ? $_GET['if_jefe'] : 0;

$tipo_plan= !empty($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_PREVENCION;

$obj= new Tplan_ci($clink);

$obj->SetYear($year);
($tipo_plan != _PLAN_TIPO_ACTIVIDADES_MENSUAL && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) ? $obj->SetMonth(null) : $obj->SetMonth($month);
$obj->SetIdProceso($id_proceso);
$obj->SetTipoPlan($tipo_plan);
$id_plan= $obj->Set();
$id_plan_code= $obj->get_id_code();

$id_proceso_code= $obj->get_id_proceso_code();

if (empty($id_proceso_code)) {
    $id_proceso_code= get_code_from_table('tprocesos',$id_proceso);
    $obj->set_id_proceso_code($id_proceso_code);
}

$obj->SetIdResponsable($_SESSION['id_usuario']);

if (empty($id_plan)) $obj->add_plan();

$objetivo= $obj->GetObjetivo();

if ($action == 'eval') {
    $observacion= $obj->GetEvaluacion();
    $status= _EVALUADO;
}
if ($action == 'object' || $action == 'aprove') {
    $observacion= $obj->GetObjetivo();
    $status= ($action == 'aprove') ? _APROBADO : _OBJETIVO_FIJADO;
}
?>
   
    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>  

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar_ap() {
            var form= document.forms['frm_ap'];

            if (Entrada(form._objetivos.value) && form.objetivos.value.length < 5) {
                alert("Por favor, no se admite una explicación tan corta, explicaciones como -ok-, -si-, -no-, no son aceptados, por favor sea más explícito.");
                return;
            }		

            <?php if ($action == 'aprove') { ?>
                if (document.getElementById('radio_user').checked) document.getElementById('_radio_user').value= 1;
            <?php } ?>

            ejecutar('<?=$action?>');
        }
    </script>

        <script type="text/javascript">	
            $(document).ready(function() {
                focusin=function(_this) {       
                   tabId= $(_this).parents('* .tabcontent');         
                   $(".tabcontent").hide();
                   $('#nav-'+tabId.prop('id')).addClass('active');
                   tabId.show();
                   $(_this).focus();
               }

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
                    selector: '#objetivos',
                    theme: 'modern',
                    height: 160,
                    language: 'es',                 
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
                    $('#objetivos').val(<?= json_encode($observacion)?>);
                } catch(e) {;}                   
                
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });	
    </script>

    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <div class="row win-drag">
                    <div class="panel-title col-11 win-drag">
                        APROBAR PLAN <?= strtoupper($Ttipo_plan_array[$tipo])?>
                    </div>
                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href="#" onclick="CloseWindow('div-ajax-panel')">
                                <i class="fa fa-close"></i>
                            </a>                        
                        </div>
                    </div>                        
                </div>            
            </div>
            
            <div class="card-body info-panel">

                <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                    <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                    <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Registros Anteriores</a></li>
                </ul>  
    
                <form id="frm_ap" name="frm_ap" action="javascript:validar_ap()"  method=post class="form-horizontal">

                    <input type="hidden" name="id" value="<?= $id_plan ?>" />
                    <input type="hidden" name="id_code" value="<?= $id_plan_code ?>" />
                    <input type="hidden" name="tipo_plan" value="<?= $tipo_plan ?>" />

                    <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
                    <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

                    <input type="hidden" name="_objetivos" value="<?= textparse(purge_html($observacion), true) ?>" />

                    <input type="hidden" name="exect" value="<?= $action ?>" />
                    <input type="hidden" id="if_jefe" name="if_jefe" value="<?= $if_jefe ?>" />

                    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>" />
                    <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
                    <input type="hidden" id="_year" name="_year" value="<?= $year ?>" />
                    <input type="hidden" id="_month" name="_month" value="<?= $month ?>" />

                    <input type="hidden" id="_radio_user" name="_radio_user" value=0  />

                    <input type="hidden" name="menu" value="riesgo" />    

                    
                    <!-- tab1 -->
                     <div class="tabcontent" id="tab1">
                        <label class="alert alert-info"><?=strtoupper($title_plan)?>. Año: <?=$year?></label>   
                        
                        <?php if ($action == 'eval') { ?>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label class="col-form-label col-sm-3">
                                    Evaluacion de Plan de Riesgos:
                                </label>
                                <div class="col-sm-9">
                                    <?php if (!$if_jefe) $disabled = "disabled='disabled'"; ?>
                                    <select name="cumplimiento" id="cumplimiento" class="form-control" <?= $disabled ?>>
                                        <option value="0">selecione...</option>
                                        <?php for ($i = 1; $i < _MAX_EVAL_PLAN; ++$i) { ?>	
                                            <option value="<?= $i ?>" <?php if ($value == $i) echo "selected='selected'" ?>><?= $evaluacion_array[$i] ?></option>
                                        <?php } ?>
                                    </select>                         
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        
                        <?php if ($action == 'aprove') { ?>
                        <div class="col-12">
                            <label class="checkbox text">
                                <input type="checkbox" id="radio_user" name="radio_user" value="1" /> 
                                Aprobar las  tareas en los Planes Individuales de todos los subordinados implicados.
                            </label>                            
                        </div>
                        <?php } ?>

                        <div class="form-group row">
                            <div class="col-12">
                                <label class="col-form-label">
                                    Observaciones:
                                </label>
                                
                                <div class="col-12">
                                    <textarea id="objetivos" name="objetivos" class="form-control"><?= $observacion ?></textarea>               
                                </div>
                            </div>
                        </div>
                     </div>  <!-- tab1 -->  

                     <!-- tab2 -->  
                     <div class="tabcontent" id="tab2">
                         <table id="table-plan"class="table table-striped"
                                data-toggle="table"
                                data-height="330"
                                data-search="true"
                                data-show-columns="true"> 
                             <thead>
                                 <tr>
                                     <th>FECHA Y HORA</th>
                                     <th>RESPONSABLE</th>
                                     <th>OBSERVACIONES</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php
                                 $obj->SetCumplimiento(NULL);
                                 $result = $obj->listar_status_plan($status);

                                 while ($row = $clink->fetch_array($result)) {
                                ?>
                                     <tr>
                                         <td>
                                            <?= odbc2time_ampm($row['cronos']) ?>
                                         </td>
                                         <td>
                                            <?= textparse($row['responsable'])?>
                                            <?=!empty($row['cargo']) ? textparse($row['cargo']) : null?>
                                         </td>
                                         <td>
                                             EVALUACION: <strong><?= $evaluacion_array[$row['cumplimiento']] ?></strong><br />
                                            <?= textparse(purge_html($row['observacion'])) ?>
                                         </td>
                                     </tr>
                                 <?php } ?>
                             </tbody>
                         </table>
                     </div> <!-- tab2 -->  

                    <div id="_submit" class="btn-block btn-app">
                        <?php if ($action != 'list') { ?> <button class="btn btn-primary" type="submit"> Aceptar</button><?php } ?>  
                        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
                    </div>

                    <div id="_submited" class="submited" align="center" style="display:none">
                        <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
                    </div>                       
                     
                </form> 
            </div>
        </div>
    </div> 
                     
