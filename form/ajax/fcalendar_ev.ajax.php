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
require_once "../../php/class/plantrab.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/proceso.class.php";

$_SESSION['debug']= 'no';

$action= $_GET['action'];
$year= $_GET['year'];
$month= !empty($_GET['month']) ? (int)$_GET['month'] : 0;
$tipo_plan= $_GET['tipo_plan'];
$signal= $_GET['signal'];

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

$empresarial= null;
if ($signal == 'anual_plan') 
    $empresarial= 2;
if ($signal == 'mensual_plan') 
    $empresarial= 1;
if ($signal == 'calendar') 
    $empresarial= 0;

if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
   $tmp_text= 'PLAN DE TRABAJO';
   $id_usuario= 0; 
} else {
   $tmp_text= "TRABAJADOR"; 
}

$id_responsable= $_SESSION['id_usuario'];

if ($empresarial == 2 || is_null($empresarial)) 
    $month= 0;

$if_jefe= false;

if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $if_jefe= false;
    $obj_prs= new Tproceso($clink);
    $array= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, null);

    if (!is_null($array) && array_key_exists($id_proceso, (array)$array)) 
        $if_jefe= true;
    if ($_SESSION['acc_planwork'] == 3 || $_SESSION['nivel'] >= _SUPERUSUARIO) 
        $if_jefe= true;
    if ($_SESSION['acc_planwork'] >= 1 && $_SESSION['usuario_proceso_id'] == $id_proceso) 
        $if_jefe= true;
	
    $id_usuario= 0;
}

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $obj_org= new Torgtarea($clink);
    $obj_org->SetIdUsuario($id_usuario);
    $if_jefe= $obj_org->if_chief($_SESSION['id_usuario']);
 
    if (($_SESSION['nivel'] >= _SUPERUSUARIO || $if_jefe) && $action == 'eval') {
        $action= 'eval'; 
        $if_jefe= true;
    } 
    else if ($_SESSION['id_usuario'] == $id_usuario) {
        $action= 'auto_eval'; 
        $if_jefe= true;
    }
    if ($_SESSION['nivel'] < _SUPERUSUARIO && ($_SESSION['id_usuario'] == $id_usuario && $action == 'eval')) {
        $action= 'auto_eval'; 
        $if_jefe= true;
    }
	 
    $id_proceso= 0;  
}

$obj= new Tplantrab($clink);

($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) ? $obj->SetIdProceso(null) : $obj->SetIdProceso($id_proceso);
($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL)? $obj->SetIdUsuario($id_usuario) : $obj->SetIdUsuario(null);
$obj->SetYear($year);	
$obj->SetMonth($month);
$obj->SetTipoPlan($tipo_plan);

$id_plan= $obj->Set();
$id_plan_code= $obj->get_id_code();

$objetivos= $obj->GetObjetivo();
$cumplimiento= $obj->GetCumplimiento();
$evaluacion= $obj->GetEvaluacion();
$auto_evaluacion= $obj->GetAutoEvaluacion();

$id_proceso= $obj->GetIdProceso();
$id_proceso_code= $obj->get_id_proceso_code();

if ($action == 'eval') {
    $obj->SetCumplimiento(null);

    if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL) 
        $toshow= _EVENTO_ANUAL;
    elseif ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) 
        $toshow= _EVENTO_MENSUAL;
    else 
        $toshow= _EVENTO_INDIVIDUAL;

    $tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL ? $obj->list_reg($toshow) : $obj->list_reg_anual(true);
    $_SESSION['obj_plantrab']= serialize($obj);

    $ratio= null;
    if ($obj->efectivas > 0) 
        $ratio= ($obj->efectivas_incumplidas/$obj->efectivas)*100;

    $text= null;

    if (!is_null($ratio)) {
        $ratio= 100 - $ratio;
        $_ratio= number_format($ratio,1).'%';

        if ($ratio < _DEFICIENTE_CUTOFF) 
            $value= 1;
        if (_DEFICIENTE_CUTOFF <= $ratio && $ratio < _SOBRESALIENTE_CUTOFF) 
            $value= 2;
        if ($ratio >= _SOBRESALIENTE_CUTOFF) 
            $value= 3;

        $text.= "En fecha ". date('d/m/Y H:i')." Diriger reporta que el $tmp_text se merece la evaluación ";
        $text.= "de {$evaluacion_array[$value]} por el $_ratio de cumplimiento de las actividades.";  
    }

    if (!empty($cumplimiento)) 
        $text.= "\nEn la anterior evaluación hecha a este Plan este fue evaluado de {$evaluacion_array[$cumplimiento]}";
}

switch($empresarial) {
    case 0:
        $title= "INDIVIDUAL";
        break;
    case 1:
        $title= "GENERAL";
        break;
    case 2:
        $title= "ANUAL";
        break;										
}

$tab1= null; 
$tab3= null;
$readonly= null;

if ($action == 'eval') {
    $tab1= 'active';
}
if ($action == 'auto_eval') {
    $tab3= "active"; 
}

?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>    
    
    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>


    <script type="text/javascript" charset="utf-8">
        function validar_ev() {
            var text;
            var form= document.forms['frm_ev'];

            function _this_1() {
                form.evaluacion.value= trim_str(form.evaluacion.value);
                text= "Por favor, la explicación no debe ser tan corta, explicaciones como -ok-,  -si-, -no- no son aceptados, "
                text+= "por favor sea más explicito.";
                if (form.evaluacion.value.length < 5) {
                    alert(text);
                    return false;
                } 
                
                return true;
            }
            
            function _this_2() {
                if (form._value.value > 0 || form._cumplimiento.value > 0) {
                    if (form.cumplimiento.value != form._value.value && form.cumplimiento.value != form._cumplimiento.value) {
                        var text= "Usted está dando una evaluación de desempeño que no se corresponde con los cálculos del sistema o ";
                        text+= "con la última evaluación dada al Plan. Su jeje inmediato será alertado de este intento. Desea continuar?";
                        
                        confirm(text, function(ok) {
                            if (!ok) 
                                return false;
                            else 
                                ejecutar('eval');
                        });                            
                    } else {
                        ejecutar('eval');
                    }
                } else 
                    ejecutar('eval');
            }

            <?php if ($action == 'eval') { ?>
                if (form.cumplimiento.value == 0) {
                    alert('Debe evaluar el desempeño a partir del cumplimiento del Plan de Trabajo.');
                    return;
                }

                if (!Entrada(form.evaluacion.value)) {
                    
                    if (Entrada(form.auto_evaluacion.value)) {
                        text= "Faltan las observaciones para la evaluación dada. ";
                        text+= "Desea que el sistema asuma los comentarios y observaciones que aparecen en la auto-evaluación?";
                        confirm(text, function(ok) {
                            if (!ok) {
                                alert('Faltan las observaciones para la evaluación dada.');
                                return;
                            } else {
                                form.evaluacion.value= form.auto_evaluacion.value;
                                if (!_this_1()) 
                                    return;
                                else {
                                    if (!_this_2()) 
                                        return;    
                                }
                            } 
                        });
                    } else {
                        text= "Faltan las observaciones para la evaluación dada. ";
                        alert(text);
                        return;
                    }
                } else {                   
                    if (!_this_1()) {
                        return;
                    }  else {  
                        if (!_this_2()) 
                            return;  
                    } 
                }   
            <?php } ?>

            <?php if ($action == 'auto_eval') { ?>
                if (!Entrada(form.auto_evaluacion.value)) {
                    alert('Faltan las observaciones de la auto-evaluación.');
                    return;	
                }

                form.auto_evaluacion.value= trim_str(form.auto_evaluacion.value);

                if (form.auto_evaluacion.value.length < 5) {
                    alert("Por favor, la explicaciÃ³n no debe ser tan corta, explicaciones como -ok-,  -si-, -no- no son aceptados, por favor sea mÃ¡s explicito.");
                    return;
                }
                
                ejecutar('auto_eval');
            <?php } ?>
        }
    </script>

    <script type="text/javascript">	
        $(document).ready(function() {
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
                selector: '#evaluacion',
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
                $('#evaluacion').val(<?= json_encode($evaluacion)?>);
            } catch(e) {;}   
            
            <?php if (!is_null($tab3)) { ?>
            tinymce.init({
                selector: '#auto_evaluacion',
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
                $('#auto_evaluacion').val(<?= json_encode($auto_evaluacion)?>);
            } catch(e) {;}                 
            <?php } ?>
            
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });	
    </script>

    
    <div class="text text-info">
        <strong>EVALUACIÓN DEL DESEMPEÑO <?= $title ?> <?php if ($empresarial < 2) {echo "EN EL MES DE " . strtoupper($meses_array[(int)$month]);} ?> DEL AÑO <?= $year ?></strong>
    </div>
    
    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
        <?php if (is_null($tab3)) { ?>
        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Evaluación</a></li>
		<li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Observaciones/Recomendaciones</a></li>
        <?php } ?>
        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Autoevaluación</a></li>
		<li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Registros Anteriores</a></li>        
	 </ul>    
    
    <form id="frm_ev" name="frm_ev" class="form-horizontal" action="javascript:validar_ev()"  method=post>
        <input type="hidden" name="id" value="<?= $id_plan ?>" />
        <input type="hidden" name="id_code" value="<?= $id_plan_code ?>" />
        <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
        <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

        <input type="hidden" name="_evaluacion" value="<?= textparse($evaluacion, true) ?>" />
        <input type="hidden" name="_auto_evaluacion" value="<?= textparse($auto_evaluacion, true) ?>" />

        <input type="hidden" name="objetivos" id="objetivos" value="<?= textparse($objetivos, true) ?>" />
        
        <input type="hidden" name="exect" value="<?= $action ?>" />
        <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>" />
        <input type="hidden" name="id_responsable" value="<?= $id_responsable ?>" />

        <input type="hidden" id="year" name="year" value="<?= $year ?>" />
        <input type="hidden" id="month" name="month" value="<?= $month ?>" />
        <input type="hidden" name="empresarial" value="<?= $empresarial ?>" />
        <input type="hidden" name="tipo_plan" id="tipo_plan" value="<?= $tipo_plan ?>" />

        <input type="hidden" name="_cumplimiento" id="_cumplimiento" value="<?= $cumplimiento ?>" />
        <input type="hidden" name="_value" id="_value" value="<?= $value ?>" />
    
        <div class="container-fluid">
            <?php if (is_null($tab3)) { ?>
            <div class="tabcontent" id="tab1" <?php if (!is_null($tab3)) {?>style="display: none"<?php } ?>>
                <div class="form-group row">
                    <label class="text">Propuesta y observación del Sistema</label>
                    <div class="col-12">
                        <textarea class="form-control" id="_observacion" rows="2" name="_observacion" readonly="readonly" style="background-color:#FFFFB3; min-height: 150px;"><?= $text ?></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-form-label col-2">Evaluación:</label>
                    <div class="col-4">
                        <?php
                        if ($action != 'eval' || !$if_jefe)
                            $disabled = "disabled='disabled'";
                        ?>

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

            <?php if (is_null($tab3)) { ?>
            <div class="tabcontent" id="tab2" <?php if (!is_null($tab3)) {?>style="display: none"<?php } ?>>
                <textarea id="evaluacion" name="evaluacion"></textarea>
            </div>
            <?php } ?>

            <div class="tabcontent active" id="tab3">
                <?php if (!is_null($tab3)) { ?>
                    <textarea id="auto_evaluacion" name="auto_evaluacion"><?=$auto_evaluacion?></textarea>

                <?php } else { ?>
                    <input type="hidden" id="auto_evaluacion" name="auto_evaluacion" value="<?= textparse($auto_evaluacion)?>" />
                    <div class="alert alert-info" style="margin-top: 20px; min-height: 200px;"><?= textparse(purge_html($auto_evaluacion, false), true)?></div>
                <?php } ?>
            </div>


            <div class="tabcontent" id="tab4">
                <table id="table" class="table table-hover table-striped"
                       data-toggle="table"
                       data-height="350"
                       data-search="true"
                       data-show-columns="true">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>EVALUACIÓN</th>
                            <th>FECHA Y HORA</th>
                            <th>RESPONSABLE y ORIGEN</th>
                            <th>OBSERVACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $obj->SetIdUsuario($id_usuario);
                        $obj->SetCumplimiento(NULL);
                        $obj->SetIfEmpresarial($empresarial);
                        $flag = ($action == 'eval') ? _EVALUADO : _AUTO_EVALUADO;

                        $result = $obj->listar_status_plan($flag);
                        $i = 0;
                        while ($row = $clink->fetch_array($result)) {
                        ?>
                            <tr>
                                <td>
                                    <?= ++$i ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($row['cumplimiento']))
                                        echo $evaluacion_array[$row['cumplimiento']];
                                    else
                                        echo "AUTO EVALUACIÓN";
                                    ?>
                                </td>
                                <td><?= odbc2time_ampm($row['cronos']) ?></td>
                                <td>
                                    <?= $row['responsable']?> 
                                    <?=!empty($row['cargo']) ? textparse($row['cargo']) : ""?>
                                </td>
                                <td>
                                    <?= textparse($row['observacion']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div id="_submit" class="btn-block btn-app">
                <?php if ($if_jefe && ($action == 'eval' || $action == 'auto_eval')) { ?> 
                <button type="submit" class="btn btn-primary">Aceptar</button> 
                <?php } ?>
                <button type="reset" class="btn btn-warning" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
            </div>

            <div id="_submited" class="submited" align="center" style="display:none">
                <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
            </div>   
        </div>
        
    </form>
