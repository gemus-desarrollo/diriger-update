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
require_once "../php/class/time.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/code.class.php";

$action = !empty($_GET['action']) ? $_GET['action'] : 'list';
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tescenario($clink);
}

$id= $obj->GetIdEscenario();
$id_escenario= $id;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new TTime();
$year= $time->GetYear();

$inicio= $obj->GetInicio();
if (empty($inicio)) $inicio= $year;
$_inicio= min($year- 5, $inicio);

$fin= $obj->GetFin();
if (empty($fin)) $fin= $year;
$_fin= max($year+20, $fin);

$id_proceso= !(empty($_GET['id_proceso'])) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso)) $id_proceso= $_SESSION['local_process_id'];

$url_page= "../form/fescenario.php?signal=$signal&action=$action&menu=escenario&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_escenario=$id_escenario";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>ESCENARIOS</title>

        <?php require 'inc/_page_init.inc.php'; ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->
        <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

        <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
        <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

        <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
        <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

        <script type="text/javascript" src="../js/ajax_core.js?version="></script>

        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <!-- Upload files -->
        <link rel="stylesheet" href="../libs/upload/upload.css" />
        <script type="text/javascript" src="../libs/upload/upload.js"></script>

        <script type="text/javascript" src="../js/form.js"></script>

        <style type="text/css">
            .div-image {
                filter:alpha(opacity=90);
                -moz-opacity:0.9;
                opacity: 0.9;
            }
        </style>

        <script type="text/javascript">
            function validar() {
                if ($('#proceso').val() == 0 || $('#proceso').val() == -1) {
                    $('#proceso').focus(focusin($('#proceso')));
                    alert("No ha especificado el proceso o Unidad Organizativa a la que pertenece el escenario descrito.");
                    return;
                }

                 if (!Entrada($('#mision').val())) {
                    $('#mision').focus(focusin($('#mision')));
                    alert("No ha descrito la misión de la organización.");
                    return;
                 }

                 if (!Entrada($('#vision').val())) {
                    $('#vision').focus(focusin($('#vision')));
                    alert("No ha descrito la visión de la organización.");
                    return;
                 }

                 if ($('#inicio').val() == 0 || $('#fin').val() == 0) {
                    $('#inicio').focus(focusin($('#inicio')));
                    alert('No ha sido especificado el periodo del escenario.');
                    return;
                 }

                 if ($('#inicio').val() > $('#fin').val()) {
                    $('#inicio').focus(focusin($('#inicio')));
                    alert('El año de inicio del periodo del escenario no puede ser superior al año final.');
                    return;
                 }

                if (!Entrada($('#descripcion').val())) {
                    $('#descripcion').focus(focusin($('#descripcion')));
                    alert('No ha descrito el escenario estratégico en el que desenvolverá la organización en el periodo de años que ha escogido.');
                    return;
                }

                parent.app_menu_functions= false;
                $('#_submit').hide();
                $('#_submited').show();

                document.forms[0].action= '../php/escenario.interface.php';
                document.forms[0].submit();
            }
        </script>

         <script type="text/javascript">
               function showimage(signal) {
                   var title;
                   var _url;

                   if (signal == 'strat') {
                       title= "MAPA ESTRATÉGICO";
                       _url= '<img class="img-fluid" id="img<?=$id?>" src="../php/image.interface.php?menu=escenario&signal=strat&id=<?=$id?>" border="0" />';
                   }
                   if (signal == 'proc') {
                       title= "MAPA DE PROCESOS";
                       _url= '<img class="img-fluid" id="img<?=$id?>" src="../php/image.interface.php?menu=escenario&signal=proc&id=<?=$id?>" border="0" />';
                   }
                   if (signal == 'org') {
                       title= "ORGANIGRAMA FUNCIONAL";
                       _url= '<img class="img-fluid" id="img<?=$id?>" src="../php/image.interface.php?menu=escenario&signal=org&id=<?=$id?>" border="0" />';
                   }

                   displayFloatingDiv('div-ajax-panel', title, 60,0,5,5);

                   $("#div-ajax-panel .panel-body").html(_url);
               }

            $(document).ready(function () {
                InitDragDrop();

                InitUploaderImage('strat_mapa', "<?= urlencode(_ROOT_DIRIGER_DIR)?>", 150, 150);
                InitUploaderImage('proc_mapa', "<?= urlencode(_ROOT_DIRIGER_DIR)?>", 150, 150);
                InitUploaderImage('org_mapa', "<?= urlencode(_ROOT_DIRIGER_DIR)?>", 150, 150);

                $('#strat_mapa-trash').change(function() {
                    if ($(this).val() == 1) $('#div-image-trash').hide();
                });
                $('#proc_mapa-trash').change(function() {
                    if ($(this).val() == 1) $('#div-image-proc').hide();
                });
                $('#org_mapa-trash').change(function() {
                    if ($(this).val() == 1) $('#div-image-org').hide();
                });

                tinymce.init({
                    selector: '#descripcion',
                    theme: 'modern',
                    height: 250,
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
                    $('#descripcion').val(<?= json_encode($obj->GetDescripcion())?>);
                } catch(e) {;}

                tinymce.init({
                    selector: '#proc_observacion',
                    theme: 'modern',
                    language: 'es',
                    height: 250,
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
                    $('#proc_observacion').val(<?= json_encode($obj->get_observacion('proc'))?>);
                } catch(e) {;}

                tinymce.init({
                    selector: '#org_observacion',
                    theme: 'modern',
                    height: 250,
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
                    $('#org_observacion').val(<?= json_encode($obj->get_observacion('org'))?>);
                } catch(e) {;}

                <?php if (!is_null($error)) { ?>alert("<?= str_replace("\n", " ", $error) ?>");<?php } ?>
            });
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="app-body form">
                <div class="container">
                <div class="card card-primary">
                    <div class="card-header">ESCENARIO ESTRATÉGICO</div>
                    <div class="card-body">

                        <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                            <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Visión y Misión</a></li>
                            <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Diseño Estratégico</a></li>
                            <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Mapa de Procesos Internos</a></li>
                            <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Organigrama Funcional</a></li>
                        </ul>

                        <form class="form-horizontal" action='javascript:validar()'  method="post" enctype="multipart/form-data">
                            <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                            <input type="hidden" name="id" value="<?=$id?>" />
                            <input type="hidden" name="menu" value="escenario" />
                            <input type="hidden" name="signal" value="<?=$signal ?>" />
                            <input type="hidden" name="imagen_strat" value="<?php $param= $obj->GetParam('strat'); echo $param['name']; ?>" />
                            <input type="hidden" name="imagen_proc" value="<?php $param= $obj->GetParam('proc'); echo $param['name']; ?>" />
                            <input type="hidden" name="imagen_org" value="<?php $param= $obj->GetParam('org'); echo $param['name']; ?>" />


                            <!-- generales -->
                            <div class="tabcontent" id="tab1">
                                <div class="form-group row">
                                    <label class="col-form-label col-md-2">
                                        Vigencia:
                                    </label>
                                    <label class="col-form-label col-md-2">
                                        desde:
                                    </label>
                                    <div class=" col-md-3">
                                        <select name="inicio" id="inicio" class="form-control input-sm" onchange="refreshp()">
                                            <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                                <option value="<?= $i ?>" <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <label class="col-form-label col-md-2">
                                        hasta:
                                    </label>
                                    <div class=" col-md-3">
                                        <select name="fin" id="fin" class="form-control input-sm" onchange="refreshp()">
                                            <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                                <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>><?= $i ?></option>
                                            <?php } ?>
                                        </select>
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
                                        $restrict_prs = !$config->dpto_with_objetive ? array(_TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_GRUPO) : array(_TIPO_ARC, _TIPO_PROCESO_INTERNO) ;

                                        include_once "inc/_select_prs_down.inc.php";
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-1">
                                        Misión:
                                    </label>
                                    <div class="col-lg-11">
                                        <textarea name="mision" rows="8" id="mision" class="form-control"><?=$obj->GetMision()?></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-1">
                                        Visión:
                                    </label>
                                    <div class="col-lg-11">
                                        <textarea name="vision" rows="8" id="vision" class="form-control"><?=$obj->GetVision() ?></textarea>
                                    </div>
                                </div>

                            </div> <!-- generales -->


                            <!-- mapa estrategico -->
                            <div class="tabcontent" id="tab2">
                                <div class="form-group row">
                                    <div class="col-8">
                                        <label class="col-form-label col-md-3">
                                            Mapa Estratégico (Imagen):
                                        </label>
                                        <div class="col-sm-7 col-md-7 col-lg-7">
                                            <div id="strat_mapa" class="panel-file">
                                                <div class="img">
                                                    <?php
                                                    $param_strat = $obj->GetImage('strat');
                                                    if (!empty($param_strat)) {
                                                        $dim= $obj->GetDim('strat');
                                                    ?>
                                                    <img src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&signal=strat&id=<?= $id_escenario ?>" <?=$dim?> />
                                                    <?php } ?>
                                                </div>

                                                <div class="title">Click para cargar imagen ...</div>
                                                <input type="file" id="strat_mapa-upload" name="strat_mapa-upload" />
                                                <div class="close img-thumbnail" onclick="closeFile('strat_mapa');">X</div>
                                            </div>

                                            <input type="hidden" id="strat_mapa-upload-init" name="strat_mapa-upload-init" value="1" />
                                        </div>

                                        <div class="col-2">
                                            <button type="button" id="strat_mapa-btn-trash" class="btn btn-default upload-trash" title="Eliminar Imagen">
                                                <i class="fa fa-trash fa-2x"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <?php
                                    $param_strat = $obj->GetImage('strat');
                                    if (!empty($param_strat)) {
                                        ?>
                                        <div id="div-image-strat" class="col-4">
                                            <div class="alert alert-success">
                                                <a href="#" onclick="showimage('strat')">Imagen -> Click Vista preliminar...</a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="form-group row">
                                   <div class="col-12">
                                       <textarea name="descripcion" class="form-control" id="descripcion"><?=$obj->GetDescripcion()?></textarea>
                                   </div>
                               </div>
                            </div> <!-- mapa estrategico -->


                            <!-- mapa de procesos -->
                            <div class="tabcontent" id="tab3">
                                <div class="form-group row">
                                    <div class="col-8">
                                        <label class="col-form-label col-md-3">
                                            Mapa de Procesos (Imagen):
                                        </label>
                                        <div class="col-sm-7 col-md-7 col-lg-7">

                                             <div id="proc_mapa" class="panel-file">
                                                <div class="img">
                                                    <?php
                                                    $param_proc = $obj->GetImage('proc');
                                                    if (!empty($param_proc)) {
                                                        $dim= $obj->GetDim('proc');
                                                    ?>
                                                    <img src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&signal=proc&id=<?= $id_escenario ?>" <?=$dim?> />
                                                    <?php } ?>
                                                </div>

                                                <div class="title">Click para cargar imagen ...</div>
                                                <input type="file" id="proc_mapa-upload" name="proc_mapa-upload" />
                                                <div class="close img-thumbnail" onclick="closeFile('proc_mapa');">X</div>
                                            </div>

                                            <input type="hidden" id="proc_mapa-upload-init" name="proc_mapa-upload-init" value="1" />
                                        </div>

                                        <div class="col-2">
                                            <button type="button" id="proc_mapa-btn-trash" class="btn btn-default upload-trash" title="Eliminar Imagen">
                                                <i class="fa fa-trash fa-2x"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <?php
                                    $param_proc = $obj->GetImage('proc');
                                    if (!empty($param_proc)) {
                                    ?>
                                    <div id="div-image-proc" class="col-4">
                                        <div class="alert alert-success">
                                            <a href="#" onclick="showimage('proc')">Imagen -> Click Vista preliminar...</a>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div class="form-group row">
                                   <div class="col-md-12 col-lg-12">
                                       <textarea name="proc_observacion" class="form-control" id="proc_observacion"><?=$obj->get_observacion('proc')?></textarea>
                                   </div>
                               </div>
                            </div> <!-- mapa de procesos -->


                            <!-- Organizagrama Funcional -->
                            <div class="tabcontent" id="tab4">
                                <div class="form-group row">
                                    <div class="col-8">
                                        <label class="col-form-label col-md-3">
                                            Organizagrama Funcional (Imagen):
                                        </label>
                                        <div class="col-sm-7 col-md-7 col-lg-7">
                                             <div id="org_mapa" class="panel-file">
                                                <div class="img">
                                                    <?php
                                                    $param_org = $obj->GetImage('org');
                                                    if (!empty($param_org)) {
                                                        $dim= $obj->GetDim('org');
                                                    ?>
                                                    <img src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=escenario&signal=org&id=<?= $id_escenario ?>" <?=$dim?> />
                                                    <?php } ?>
                                                </div>

                                                <div class="title">Click para cargar imagen ...</div>
                                                <input type="file" id="org_mapa-upload" name="org_mapa-upload" />
                                                <div class="close img-thumbnail" onclick="closeFile('org_mapa');">X</div>
                                            </div>

                                            <input type="hidden" id="org_mapa-upload-init" name="org_mapa-upload-init" value="1" />
                                        </div>

                                        <div class="col-2">
                                           <button type="button" id="org_mapa-btn-trash" class="btn btn-default upload-trash" title="Eliminar Imagen">
                                               <i class="fa fa-trash fa-2x"></i>
                                           </button>
                                       </div>
                                    </div>

                                    <?php
                                    $param_org = $obj->GetImage('org');
                                    if (!empty($param_org)) {
                                    ?>
                                    <div id="div-image-org" class="col-md-4 col-lg-4">
                                        <div class="alert alert-success">
                                            <a href="#" onclick="showimage('org')">Imagen -> Click Vista preliminar...</a>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div class="form-group row">
                                   <div class="col-md-12 col-lg-12">
                                       <textarea name="org_observacion" class="form-control" id="org_observacion"><?=$obj->get_observacion('org')?></textarea>
                                   </div>
                               </div>
                            </div> <!-- Organizagrama Funcional -->


                            <!-- buttom -->
                            <div id="_submit" class="btn-block btn-app">
                                <?php if ($action == 'add' || $action == 'update') { ?>
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                                <?php } ?>
                                <button class="btn btn-warning" type="reset" onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                                <button class="btn btn-danger" type="button" onclick="open_help_window('../help/06_escenario.htm#06_8')">Ayuda</button>
                            </div>

                            <div id="_submited" style="display:none">
                                <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                            </div>

                        </form>
                    </div> <!-- panel-body -->
                </div> <!-- panel -->
            </div>  <!-- container -->
        </div>



        <!-- div-ajax-panel -->
        <div id="div-ajax-panel" class="card card-primary ajax-panel div-image" data-bind="draganddrop">
            <div class="card-header">
                <div class="row win-drag">
                    <div class="panel-title ajax-title col-11 win-drag"></div>

                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href= "javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                               <i class="fa fa-close"></i>
                           </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">

            </div>
        </div>     <!-- div-ajax-panel -->

    </body>
</html>

