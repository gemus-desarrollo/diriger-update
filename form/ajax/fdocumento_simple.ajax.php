<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2017
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug'] = 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/document.class.php";

$id = !empty($_GET['id']) ? $_GET['id'] : 0;

$year = !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month = null;
$month = !is_null($_GET['month']) ? $_GET['month'] : 0;
if ($year == $_SESSION['current_year'] && empty($month))
    $month = $_SESSION['current_month'];

$id_evento = !empty($_GET['id_evento']) ? $_GET['id_evento'] : $_POST['id_evento'];
$id_tarea = !empty($_GET['id_tarea']) ? $_GET['id_tarea'] : $_POST['id_tarea'];
$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_POST['id_proceso'];
$id_proyecto = !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : $_POST['id_proyecto'];
$id_auditoria = !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : $_POST['id_auditoria'];
$id_nota = !empty($_GET['id_nota']) ? $_GET['id_nota'] : $_POST['id_nota'];
$id_riesgo = !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : $_POST['id_riesgo'];
$id_politica = !empty($_GET['id_politica']) ? $_GET['id_politica'] : $_POST['id_politica'];
$id_indicador = !empty($_GET['id_indicador']) ? $_GET['id_indicador'] : $_POST['id_indicador'];
$id_requisito = !empty($_GET['id_requisito']) ? $_GET['id_requisito'] : $_POST['id_requisito'];

$id_evento= !empty($id_evento) && $id_evento > 0 ? $id_evento : 0;
$id_tarea= !empty($id_tarea) && $id_tarea > 0 ? $id_tarea : 0;
$id_proceso= !empty($id_proceso) && $id_proceso > 0 ? $id_proceso : 0;
$id_proyecto= !empty($id_proyecto) && $id_proyecto > 0 ? $id_proyecto : 0;
$id_auditoria= !empty($id_auditoria) && $id_auditoria > 0 ? $id_auditoria : 0;
$id_nota= !empty($id_nota) && $id_nota > 0 ? $id_nota : 0;
$id_riesgo= !empty($id_riesgo) && $id_riesgo > 0 ? $id_riesgo : 0;
$id_politica= !empty($id_politica) && $id_politica > 0 ? $id_politica : 0;
$id_indicador= !empty($id_indicador) && $id_indicador > 0 ? $id_indicador : 0;
$id_requisito= !empty($id_requisito) && $id_requisito > 0 ? $id_requisito : 0;

$obj_doc = new Tdocumento($clink);
$obj_doc->SetYear($year);
$obj_doc->SetMonth($month);

$id_responsable = 0;
$keywords = null;
$descripcion = null;

if (!empty($id)) {
    $obj_doc->Set($id);
    $id_responsable = $obj_doc->GetIdResponsable();
    $descripcion = $obj_doc->GetDescripcion();
    $keywords = $obj_doc->GetKeywords();
}

if (!empty($id_evento))
    $obj_doc->SetIdEvento($id_evento);
if (!empty($id_tarea))
    $obj_doc->SetIdTarea($id_tarea);    
if (!empty($id_proceso))
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
    $obj_doc->SetIdPolitica($id_politica);
if (!empty($id_indicador))
    $obj_doc->SetIdIndicador($id_indicador);
if (!empty($id_requisito))
    $obj_doc->SetIdRequisito($id_requisito);
?>


<script type="text/javascript">
    $(document).ready(function () {
        InitUploaderFile('file_doc', "<?= urlencode(_SERVER_DIRIGER)?>", <?=$config->maxfilesize?>);

        try {
            $('#descripcion_doc').tinymce().destroy();
        } catch (e) {
            ;
        }

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
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
            '| bullist numlist outdent indent | removeformat | help',

            content_css: '../css/content.css'
        });

        try {
            $('#descripcion_doc').val(<?= json_encode($descripcion)?>);
        } catch (e) {
            ;
        }

        //When page loads...
        $(".tabcontent.tab-ajax").hide(); //Hide all content
        $("ul.nav-tabs-ajax li a:first").addClass("active").show(); //Activate first tab
        $(".tabcontent.tab-ajax:first").show(); //Show first tab content

        //On Click Event
        $("ul.nav.nav-tabs-ajax li a").click(function () {
            $("ul.nav.nav-tabs-ajax li a").removeClass("active"); //Remove any "active" class
            $(this).addClass("active"); //Add "active" class to selected tab
            $(".tabcontent.tab-ajax").hide(); //Hide all tab content

            var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content  
            $("#" + activeTab).fadeIn(); //Fade in the active ID content
//         $("#" + activeTab + " .form-control:first").focus();
            return false;
        });
    });
</script>

<div class="card card-primary">
    <div class="card-header">
        <div class="row">
            <div class="panel-title col-11 m-0 win-drag ajax-title">DOCUMENTO</div>
            <div class="col-1 m-0">
                <div class="close">
                    <a href="javascript:close_doc();" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="div-body-doc-panel" class="card-body" style="padding: 10px;">

        <ul class="nav nav-tabs-ajax" style="margin-bottom: 10px;">
            <li id="nav-tab1-ajax1" class="nav-item"><a class="nav-link" href="tab-ajax1">Documento</a></li>
            <li id="nav-tab2-ajax2" class="nav-item"><a class="nav-link" href="tab-ajax2">Descripción</a></li>
        </ul>

        <form id="form-doc" action='javascript:' class="form-horizontal" method="post" enctype="multipart/form-data">
            <input type=hidden id="id_doc" name="id" form="form-doc" value="<?= $id ?>"/>

            <input type="hidden" id="exect" name="exect" value="<?= !empty($id) ? 'update' : 'add' ?>"/>
            <input type="hidden" id="menu_doc" name="menu" value="lista"/>

            <input type="hidden" id="id_requisito_doc" name="id_requisito" value="<?= $id_requisito ?>"/>
            <input type="hidden" id="id_evento_doc" name="id_evento" value="<?= $id_evento ?>"/>
            <input type="hidden" id="id_tarea_doc" name="id_tarea" value="<?= $id_tarea ?>"/>
            <input type="hidden" id="id_proceso_doc" name="id_proceso" value="<?= $id_proceso ?>"/>
            <input type="hidden" id="id_proyecto_doc" name="id_proyecto" value="<?= $id_proyecto ?>"/>
            <input type="hidden" id="id_auditoria_doc" name="id_auditoria" value="<?= $id_auditoria ?>"/>
            <input type="hidden" id="id_riesgo_doc" name="id_riesgo" value="<?= $id_riesgo ?>"/>
            <input type="hidden" id="id_politica_doc" name="id_politica" value="<?= $id_politica ?>"/>
            <input type="hidden" id="id_indicador_doc" name="id_indicador" value="<?= $id_indicador ?>"/>
            <input type="hidden" id="id_nota_doc" name="id_nota" value="<?= $id_nota ?>"/>

            <input type="hidden" id="id_usuario_doc" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>"/>
            <input type="hidden" id="usuario_doc" name="usuario_doc" value="<?= $_SESSION['id_usuario'] ?>"/>

            <input type="hidden" name="month" id="month" value="<?= $month ?>"/>
            <input type="hidden" name="year" id="year" value="<?= $year ?>"/>

            <!-- documento -->
            <div class="tabcontent tab-ajax" id="tab-ajax1">
                <div id="tr-file" class="form-group row">
                    <label class="col-form-label col-2">
                        Documento:
                    </label>
                    <div class="col-6">
                        <div id="file_doc" class="panel-file">
                            <div class="img"></div>
                            <div class="title">Click para cargar DOCUMENTO ...</div>
                            <input type="file" id="file_doc-upload" name="file_doc-upload"/>
                            <div class="close img-thumbnail" onclick="closeUploaderFile('file_doc');">X</div>
                        </div>
                    </div>
                </div>    

                <div class="form-group row">
                    <div class="col-12">
                        <label class="text">
                            Palabras claves, separadas por punto y coma(;) :
                        </label>
                        <textarea name="keywords_doc" id="keywords_doc" class="form-control" rows="2"><?= $keywords ?></textarea><br/>
                        Sí incluye las palabras claves estan se escriben al final y son separadas por punto y coma.
                        <br/>Ejemplo: acta;informe;registro
                    </div>
                </div>
            </div> <!-- documento -->

            <!-- Descripcion -->
            <div class="tabcontent tab-ajax" id="tab-ajax2">
                <div class="form-group row">
                    <div class="col-12">
                        <label class="col-12 text">
                            Descripción (resumen corto) del documento :
                        </label>

                        <div class="col-12">
                            <textarea name="descripcion_doc" id="descripcion_doc" class="form-control" style="height: 300px;"></textarea>
                        </div>
                    </div>
                </div>
            </div><!-- Descripcion -->

            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button type="button" class="btn btn-primary" onclick="add_doc()"
                        title="Guardar documento en el servidor">Guardar
                </button>
                <button type="button" class="btn btn-warning" onclick="close_doc()" title="Cerrar">Cerrar</button>
            </div>
        </form>
    </div>
</div>
