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
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/document.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/code.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tlista_requisito($clink);
}

$id_lista= !empty($obj->GetIdLista()) ? $obj->GetIdLista() : 0;
$id_requisito= !empty($obj->GetIdRequisito()) ? $obj->GetIdRequisito() : 0;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= !empty($_GET['year']) ? $_GET['year'] : $obj->GetYear();
if (empty($year))
    $year= date('Y');
$_inicio= !empty($_GET['_inicio']) ? $_GET['_inicio'] : $year-5;
$_fin= !empty($_GET['_fin']) ? $_GET['_fin'] : $year+5;

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();
$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$evidencia= !empty($_GET['evidencia']) ? urldecode($_GET['evidencia']) : $obj->GetEvidencia();
$indicacion= !empty($_GET['indicacion']) ? urldecode($_GET['indicacion']) : $obj->GetIndicacion();
$peso= !is_null($_GET['peso']) ? $_GET['peso'] : $obj->GetPeso();

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
if (empty($numero))
    $numero= 0;
$id_lista= !is_null($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();
$componente= !is_null($_GET['componente']) ? $_GET['componente'] : $obj->GetComponente();
$id_capitulo= !is_null($_GET['id_capitulo']) ? $_GET['id_capitulo'] : $obj->GetIdCapitulo();

$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : $obj->GetIdTipo_lista();
if (empty($id_tipo_lista))
    $id_tipo_lista= 0;

if (!empty($id_tipo_lista && empty($id_capitulo))) {
    $obj_tipo= new Ttipo_lista($clink);
    $obj_tipo->SetIdTipo_lista($id_tipo_lista);
    $id_capitulo= $obj_tipo->GetIdCapitulo();
}

if (empty($inicio))
    $inicio= $year;
if (empty($fin))
    $fin= $_fin;

$month= null;
$obj->SetMonth(null);
$obj->SetYear($year);

$obj_prs= new Tproceso($clink);
$obj_doc= new Tdocumento($clink);
$obj_user= new Tusuario($clink);

$obj_doc->SetYear($year);
$obj_doc->SetIdProceso($id_proceso);
$array_files= null;

if (!empty($id_requisito)) {
    $obj_doc->SetIdRequisito($id_requisito);
    $array_files= $obj_doc->listar(false);
}

$url_page= "../form/flista_requisito.php?signal=$signal&action=$action&menu=frequisito&exect=$action";
$url_page.= "&indicacion=$indicacion&year=$year&evidencia=$evidencia&componente=$componente";
$url_page.= "&id_tipo_lista=$id_tipo_lista&id_lista=$id_lista&id_capitulo=$id_capitulo";

add_page($url_page, $action, 'f');

if ($action == 'edit')
    $action= 'add';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>REQUISITO DE LISTA DE CHEQUEO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/custom.css">

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <link href="../libs/upload/upload.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/upload/upload.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/lista.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <style type="text/css">
    #div-ajax-table {
        height: 400px !important;
        margin-top: 6px;
    }
    </style>

    <script language="javascript" type="text/javascript">
    var ifnew = true;

    function form_doc(id) {
        ifnew = true;

        var title = id > 0 ? ": " + $("#nombre_doc_" + id).val() : "";
        displayFloatingDiv('div-panel-requisito', "CARGAR DOCUMENTO" + title, 70, 0, 10, 5);

        var id_doc = id > 0 ? $('#id_doc_' + id).val() : 0;
        var year = $("#year").val();
        var month = $("#month").val();
        var id_requisito = $('#id_requisito').val();

        $("#id").val(id_doc);

        var url = '../form/ajax/fdocumento_simple.ajax.php?ajax_win=1&id=' + id_doc;
        url += '&id_requisito=' + id_requisito + '&year=' + year + '&month=' + month;

        var capa = 'div-panel-requisito';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);

        $("#tr-file").show();

        limpiar_doc();
    }

    function del_doc(id) {
        confirm("El documento " + $("#nombre_doc_" + id).val() +
            " será eliminado como referencia para este requisito. ¿Desea continuar?",
            function(ok) {
                if (!ok) 
                    return;
                else {
                    var url = '../php/document.interface.php?ajax_win=1&signal=<?=$signal?>&action=delete&id=' +
                         $("#id_doc_" + id).val();

                    var capa = 'div-ajax-table';
                    var metodo = 'GET';
                    var valores = '';
                    var funct= '';

                    FAjax(url, capa, valores, metodo, funct);
                }
            });
    }

    function edit_doc(id) {
        form_doc(id);

        $("#tr-file").css("display", "none");
        ifnew = false;
    }

    function close_doc() {
        parent.app_menu_functions = false;
        CloseWindow('div-panel-requisito');
        ifnew = true;
    }

    function limpiar_doc() {
        ifnew = true;
        $("#file_doc").val(null);
        $("#descripcion_doc").val(null);
        $("#keywords_doc").val(null);
    }

    function add_doc() {
        if (!Entrada($("#file_doc-upload").val()) && ifnew) {
            $('#file_doc').focus(focusin($('#file_doc')));
            alert("Aún no ha cargado el documento a guardar.");
            return false;
        }
        if (!Entrada($("#descripcion_doc").val()) && !Entrada($("#keywords_doc").val())) {
            $('#descripcion_doc').focus(focusin($('#descripcion_doc')));
            alert(
                "Debe de dar una breve descripción del documento o un grupo de palabras claves que ayuden en su búsqueda y rápida localización.");
            return false;
        }

        var form = document.forms['form-doc'];
        var action = ifnew ? 'add' : 'update';
        var keywords = encodeURI($("#keywords_doc").val());

        var url = '../php/document.interface.php?ajax_win=1&signal=<?=$signal?>&action=' + action + '&keywords=' +
            keywords;
        if (!ifnew && parseInt($("#id").val()) > 0)
            url += '&id=' + $("#id").val();

        tinymce.get('descripcion_doc').save();

        form.action = url;
        FAjax_upload("form-doc", url, 'div-body-doc-panel');
    }

    function list_doc() {
        var year = $("#year").val();
        var month = $("#month").val();
        var id_requisito = $('#id_requisito').val();
        var action = $('#exect').val();

        var url = '../form/ajax/ldocumento_simple.ajax.php?id=' + id_requisito + '&id_requisito=' + id_requisito;
        url += '&year=' + year + '&month=' + month + '&action=' + action;

        var capa = 'div-ajax-table';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct); 
    }
    </script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        if (parseInt($('#inicio').val()) > parseInt($('#fin').val())) {
            $('#inicio').focus(focusin($('#inicio')));
            alert("El año de inicio de la aplicación del Requisito no puede ser superior al año en que finaliza.");
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el enunciado del Requisito.');
            return;
        }
        if ($('#peso').val() == 0) {
            alert("Debe selecionar el impacto del requsito en el resultado de la lista de chequeo");
            return;
        }
        if ($("#cant_multiselect-prs").val() == 0) {
            alert("No ha seleccionado los procesos que utilizan esta Guia de Control o GLista de Chequeo.");
            return;
        }

        document.forms[0].action = '../php/lista_requisito.interface.php';
        document.forms[0].submit();
    }

    function cerrar(error) {
        table_ajax($('#exect').val());
        CloseWindow('div-panel-requisito');

        if (error.length > 0) {
            alert(error);
        }
    }
    </script>

    <script type="text/javascript">
    function set_numero(val) {
        $('#numero').val(val);
    }

    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        refresh_ajax_select('', <?= !empty($id_capitulo) ? $id_capitulo : 0 ?>, <?= $numero ?>);
        refresh_ajax_select_capitulo('', <?= $id_tipo_lista ?>, <?= $numero ?>);

        $('#componente').on('change', function() {
            refresh_ajax_select('', 0, 0);
        });

        try {
            $('#nav-tab4').click(function() {
                if (parseInt($('#id_requisito').val()) == 0) {
                    alert(
                        "Debe ingresar el requisito y posteriormente editarlo para adjuntarle documentos.");
                    $("#nav-tab4").removeClass("active");
                    $("#tab4").hide();
                    $("#nav-tab1").addClass("active");
                    $("#tab1").show();
                    
                } else {
                    list_doc();
                }
            });
        } catch (e) {

        }

        tinymce.init({
            selector: '#evidencia',
            theme: 'modern',
            height: 300,
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
            $('#evidencia').val(<?= json_encode($evidencia)?>);
        } catch (e) {
            ;
        }

        tinymce.init({
            selector: '#indicacion',
            theme: 'modern',
            height: 300,
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
            $('#indicacion').val(<?= json_encode($indicacion)?>);
        } catch (e) {
            ;
        }

        <?php if ($action != 'add') { ?>
        list_doc();
        <?php } ?>

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
                <div class="card-header">REQUISITO DE LISTA DE CHEQUEO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"
                            ><a class="nav-link" href="tab1">Generales</a>
                        </li>
                        <li id="nav-tab2" class="nav-item" title="Evidencia a encontrar">
                            <a class="nav-link" href="tab2">Evidencias</a>
                        </li>
                        <li id="nav-tab3" class="nav-item" title="Indicaciones al Equipo Evaluador">
                            <a class="nav-link" href="tab3">Indicaciones</a>
                        </li>
                        <li id="nav-tab4" class="nav-item">
                            <a class="nav-link" href="tab4">Unidades Organizativas</a>
                        </li>
                        <?php if ($action != 'add') { ?>
                        <li id="nav-tab5" class="nav-item" title="Documentos adjuntos">
                            <a class="nav-link" href="tab5">Documentos</a>
                        </li>
                        <?php } ?>
                    </ul>

                    <form id="formRequisito" class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                        <input type="hidden" id="id" name="id" form="formRequisito" value="<?=$id_requisito?>" />
                        <input type="hidden" id="id_requisito" name="id_requisito" form="formRequisito" value="<?=$id_requisito?>" />
                        <input type="hidden" id="signal" name=signal value="<?=$signal?>" />
                        <input type="hidden" name="menu" value="frequisito" />

                        <input type="hidden" id="id_lista" name="id_lista" value="<?=$id_lista?>" />
                        <input type="hidden" id="id_lista_code" name="id_lista_code" value="<?=$id_lista_code?>" />

                        <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" name="year" id="year" value="<?=$year?>" />

                        <input type="hidden" name="id_tipo_lista" id="id_tipo_lista" value="<?=$id_tipo_lista > 0 ? $id_tipo_lista : 0?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-sm-2 ">
                                    Inicia
                                </label>
                                <div class=" col-sm-4">
                                    <select name="inicio" id="inicio" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $inicio) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <label class="col-form-label col-sm-2 ">
                                    Termina
                                </label>
                                <div class=" col-sm-4 ">
                                    <select name="fin" id="fin" class="form-control" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $fin) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Componente:
                                </label>
                                <div class="col-md-6">
                                    <select id="componente" name="componente" class="form-control">
                                        <option value="0"> ... </option>
                                        <?php for ($i=1; $i < _MAX_COMPONENTES_CI; ++$i) { ?>
                                        <option value="<?=$i?>"
                                            <?php if ($i == $componente) echo "selected='selected'" ?>>
                                            <?=$Tambiente_control_array[$i]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Capítulo:
                                </label>
                                <div id="ajax-capitulo" class="col-md-10">
                                    <select id="capitulo" name="capitulo" class="form-control">
                                        <option value="0"> ... </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Epigrafe:
                                </label>
                                <div id="ajax-subcapitulo" class="col-md-10">
                                    <select id="subcapitulo" name="subcapitulo" class="form-control">
                                        <option value="0"> ... </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div id="ajax-numero" class="div-ajax">

                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Requisito No.:
                                </label>
                                <div class="col-md-3">
                                    <div id="spinner-numero" class="input-group spinner">
                                        <input type="text" name="numero" id="numero" class="form-control"
                                            value="<?=$numero?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa">
                                                    <span class="fa fa-caret-up"></span></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa">
                                                    <span class="fa fa-caret-down"></span>
                                                </i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-4">
                                    Impacto sobre el resultado de la aplicación de la guia:
                                </label>
                                <div class=" col-md-4">
                                    <select id="peso" name="peso" class="form-control">
                                        <option value="0"> ... </option>

                                        <?php for ($k= 1; $k < 8; ++$k) { ?>
                                        <option value="<?=$k?>" <?php if ($k == $peso) echo "selected='selected'"?>>
                                            <?=$Tpeso_inv_array[$k]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Requisito:
                                </label>
                                <div class="col-md-10">
                                    <textarea type="text" id="nombre" name="nombre" rows="3"
                                        class="form-control"><?=$nombre?></textarea>
                                </div>
                            </div>

                        </div> <!-- generales -->

                        <!-- evidencia -->
                        <div class="tabcontent" id="tab2">
                            <textarea name="evidencia" id="evidencia" class="form-control"><?=$evidencia?></textarea>
                        </div><!-- evidencia -->

                        <!-- evidencia -->
                        <div class="tabcontent" id="tab3">
                            <textarea name="indicacion" id="indicacion" class="form-control"><?=$indicacion?></textarea>
                        </div><!-- evidencia -->

                        <!-- documentos adjuntos -->
                        <div class="tabcontent" id="tab5">
                            <div id="toolbar">
                                <div class="btn-toolbar" role="form">
                                    <button id="btn_agregar" type="button" onclick="form_doc(0);"
                                        class="btn btn-primary" style="visibility:<?= $visible ?>;">
                                        <i class="fa fa-plus"></i>Agregar
                                    </button>
                                </div>
                            </div>

                            <div id="div-ajax-table">

                            </div>
                        </div><!-- documentos adjuntos -->

                        <div class="tabcontent" id="tab4">
                            <?php
                            unset($obj_prs);
                            unset($array_procesos);
                            $array_procesos= null;

                            $obj_prs= new Tproceso_item($clink);
                            $obj_prs->SetIdProceso($_SESSION['id_entity']);
                            $obj_prs->SetTipo($_SESSION['entity_tipo']);
                            $obj_prs->SetConectado(null);
                            $obj_prs->SetIdResponsable(null);

                            if (!empty($id_lista)) {
                                $obj_prs->SetIdLista($id_lista);
                                $obj_prs->SetYear($year);
                                $array_procesos_lista= $obj_prs->GetProcesoLista();
                                $result_prs_array = $obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], _TIPO_PROCESO_INTERNO, $array_procesos_lista);
                                $cant_prs = $obj_prs->GetCantidad();
                            }                            

                            if (!empty($id_requisito)) {
                                $obj_prs->SetIdRequisito($id_requisito);
                                $obj_prs->SetYear($year);
                                $array_procesos= $obj_prs->GetProcesoRequisito();
                            }

                            $name_form= "flista";
                            $restrict_prs = null;
                            $restrict_up_prs = true;
                            $filter_by_toshow = true;
                            $create_select_input= false;

                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab5 Procesos-->

                        <!-- panel-requisito -->
                        <div id="div-panel-requisito" class="ajax-panel" data-bind="draganddrop">

                        </div> <!-- panel-requisito -->

                        <!-- buttom -->'
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add'|| $action == 'edit') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-danger" type="button"
                                onclick="self.location.href='<?php prev_page()?>'">Cancelar</button>
                            <button class="btn btn-warning" type="reset"
                                onclick="open_help_window('../help/manual.html')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>
                    </form>

                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->
    </div>

</body>

</html>

<?php $_SESSION['obj']= serialize($obj); ?>