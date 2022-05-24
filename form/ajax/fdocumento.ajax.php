<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2017
 */

 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/proceso_item.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/riesgo.class.php";
require_once "../../php/class/proyecto.class.php";
require_once "../../php/class/document.class.php";

$id= !empty($_GET['id']) ? $_GET['id'] : 0;

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= null;
$month= !is_null($_GET['month']) ? $_GET['month'] : 0;
if ($year == $_SESSION['current_year'] && is_null($month)) 
    $month= $_SESSION['current_month'];

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : 0;
$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : 0;
$id_politica= !empty($_GET['id_politica']) ? $_GET['id_politica'] : 0;
$id_indicador= !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : 0;

$obj_doc= new Tdocumento($clink);
$obj_user= new Tusuario($clink);

$obj_doc->SetYear($year);
$obj_doc->SetMonth($month);

$id_responsable= 0;
$keywords= null;
$descripcion= null;
$array_usuarios_init= array();
$array_usuarios= array();
$array_grupos= array();

if (!empty($id_evento) || !empty($id_auditoria) || !empty($id_tarea)) {
    $obj= new Tevento($clink);
    $obj->SetYear($year);
    $obj->SetIdEvento($id_evento);
    $obj->SetIdAuditoria($id_auditoria);
    $obj->SetIdTarea($id_tarea);
    
    $array_usuarios_init= $obj->get_usuarios_array_from_evento($id_evento, $year, true);
} 
elseif (!empty($id_proyecto)) {
    $obj= new Tproyecto($clink);
    $obj->SetYear($year);
    $obj->SetIdProyecto($id_proyecto);
    
    $array_usuarios_init= $obj->get_array_usuarios();
} 
elseif (!empty($id_riesgo) || !empty($id_nota)) {
    $obj= new Triesgo($clink);
    $obj->SetYear($year);
    $obj->SetIdRiesgo($id_riesgo);
    $obj->SetIdNota($id_nota);
    
    $array_usuarios_init= $obj->get_array_usuarios();
} 
else {
    $obj= new Tusuario($clink);
    $obj->SetYear($year);
    $array_usuarios_init= $obj->listar(false);
}

if (!empty($id)) {
    $obj_doc->Set($id);
    $id_responsable= $obj_doc->GetIdResponsable();
    $descripcion= $obj_doc->GetDescripcion();
    $keywords= $obj_doc->GetKeywords();
    
    $obj_doc->listar_usuarios();
    $array_usuarios= $obj_doc->array_usuarios;
    $obj_doc->listar_grupos();
    $array_grupos= $obj_doc->array_grupos;
}

if (!empty($id_evento)) 
    $obj_doc->SetIdEvento($id_evento);
if (!empty($id_tarea)) 
    $obj_doc->SetIdTarea($id_tarea);    
if (!empty($id_procesoo)) 
    $obj_doc->SetIdProceso($id_proceso);
if (!empty($id_auditoria)) 
    $obj_doc->SetIdAuditoria($id_auditoria);
if (!empty($id_proyecto)) 
    $obj_doc->SetIdProyecto($id_proyecto);
if (!empty($id_nota)) 
    $obj_doc->SetIdNota($id_nota);
if (!empty($id_riesgo)) 
    $obj_doc->SetIdRiesgo($id_riesgo);
if (!empty($id_politica)) 
    $obj_doc->SetIdRiesgo($id_politica);
if (!empty($id_indicador)) 
    $obj_doc->SetIdIndicador($id_indicador);
?>

<style type="text/css">
.panel-multiselect {
    min-height: 270px;
    max-height: 320px;
}
</style>


<script type="text/javascript">
    var focusin;
    $(document).ready(function() {
        InitUploaderFile('file_doc', "<?= urlencode(_SERVER_DIRIGER)?>", <?=$config->maxfilesize?>);
        
        <?php
        $user_ref_date= $fecha_fin;
        $restrict_prs= array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
        ?>        
        
        $.ajax({
            data:  {
                    "id" : <?=!empty($id) ? $id : 0?>, 
                    "tipo_plan" : <?=_PLAN_TIPO_INFORMATIVO?>,
                    "year" : <?=!empty($year) ? $year : date('Y')?>,
                    "user_ref_date" : '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',                 
                    "id_user_restrict" : <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>, 
                    "restrict_prs" : <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                    "use_copy_tusuarios" : <?=$use_copy_tusuarios ? $use_copy_tusuarios : 0?>,
                    "array_usuarios" : <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                    "array_grupos" : <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>,
            
                    "id_evento" : <?=!empty($id_evento) ? $id_evento : 0?>,
                    "id_auditoria" : <?=!empty($id_auditoria) ? $id_auditoria : 0?>,
                    "id_tarea" : <?=!empty($id_tarea) ? $id_tarea : 0?>,
                    "id_proyecto" : <?=!empty($id_proyecto) ? $id_proyecto : 0?>,
                    "id_riesgo" : <?=!empty($id_riesgo) ? $id_riesgo : 0?>,
                    "id_nota" : <?=!empty($id_nota) ? $id_nota : 0?>,
                    "id_politica" : <?=!empty($id_politica) ? $id_politica : 0?>,
                    "id_indicador" : <?=!empty($id_indicador) ? $id_indicador : 0?>
                },
            url:   'ajax/usuario_documento_tabs.ajax.php',
            type:  'post',
            beforeSend: function () {
                $("#ajax-tab-users").html("Procesando, espere por favor...");
            },
            success:  function (response) {
                $("#ajax-tab-users").html(response);
            }
        });  
                
        focusin=function(_this) {       
            tabId= $(_this).parents('* .tabcontent');         
            $(".tabcontent").hide();
            $('#nav-'+tabId.prop('id')).addClass('active');
            tabId.show();
            $(_this).focus();
        }

        try {
            $('#descripcion_doc').tinymce().destroy();
        } catch(e) {;} 

        tinymce.init({
            selector: '#descripcion_doc',
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
            $('#descripcion_doc').val(<?= json_encode($descripcion)?>);
        } catch(e) {;} 
    
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
    });    
</script>

<div class="container col-12" style="margin: 10px;">
    <div class="card card-primary">
        <div class="card-header">
            <div class="row">
            <div class="panel-title col-11 m-0 win-drag ajax-title">DOCUMENTO</div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href= "javascript:close_doc();" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>                      
            </div>    
        </div>        
        
        <div class="card-body">
            <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Documento</a></li>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Descripción</a></li>
                <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Usuarios con Acceso</a></li>
            </ul>

            <!-- documento -->
            <div class="tabcontent" id="tab1">
                <div id="tr-file" class="form-group row">
                    <label class="col-form-label col-2">
                        Archivo: 
                    </label>
                    <div class="col-6">
                        <div id="file_doc" class="panel-file">
                        <div class="img"></div>
                        <div class="title">Click para cargar IMAGE ...</div> 
                        <input type="file" id="file_doc-upload" name="file_doc-upload" />
                        <div class="close img-thumbnail" onclick="closeUploaderImage('file_doc');">X</div>
                    </div>  
                    </div>
                </div>
                
                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Responsable:
                    </label>
                    <div class="col-10">
                        <select name="usuario_doc" id="usuario_doc" class="form-control">
                            <option value="0">selecciona...</option>
                            <?php
                            foreach ($array_usuarios_init as $id => $row_user) {
                                if (empty($row_user['nombre'])) 
                                    continue;
                                $cargo= !empty($row_user['cargo']) ? ", ".textparse($row_user['cargo']) : "";
                            ?>
                                <option value="<?= $row_user['id'] ?>" <?php if ($row_user['id'] == $id_responsable) echo "selected" ?>><?= "{$row_user['nombre']}{$cargo}"?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-12">
                        <label class="text">
                        Palabras claves, separadas por punto y coma(;) :
                    </label> 
                        <textarea name="keywords_doc" id="keywords_doc" class="form-control" rows="2"><?= $keywords ?></textarea><br />
                        Sí incluye las palabras claves estan se escriben al final y son separadas por punto y coma. <br/>Ejemplo: acta;informe;registro
                    </div>
                </div>  
            </div> <!-- documento -->    
            
            
            <!-- Descripcion -->
            <div class="tabcontent" id="tab2">
                <div class="form-group row">
                    <div class="col-12">
                        <label class="col-12 text">
                            Descripción (resumen corto) del documento :
                        </label>                    

                        <div class="col-12">
                            <textarea name="descripcion_doc" id="descripcion_doc" class="form-control" rows="8"><?= $descripcion ?></textarea>
                        </div>                 
                    </div>                 
                </div>
            </div><!-- Descripcion -->
            
            
            <!-- Participantes -->
            <div class="tabcontent" id="tab3">
                <div class="col-12">
                    <div class="checkbox">
                        <label class="text" style="margin-bottom: 0px;">
                            <input type="checkbox" name="sendmail" id="sendmail" value="1" <?php echo empty($sendmail) ? '&nbsp;' : "checked='checked'"; ?>  />
                            Enviar aviso sobre documento por correo electrónico
                        </label>
                    </div>                 
                </div>

                <div id="ajax-tab-users">

                </div>
            </div> <!-- tab3 Participantes-->        
            
            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" onclick="add_doc()" title="Guardar documento en el servidor">Guardar</button>
                <button class="btn btn-warning" onclick="close_doc()" title="Cerrar">Cerrar</button>            
            </div>
        </div>
    </div>
</div>


