    <?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/proyecto.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/document.class.php";

require_once "../php/class/time.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'tablero';
$error= !empty($_GET['error']) ? $_GET['error'] : $obj_doc->error;

$id_evento= !empty($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;
$id_tarea= !empty($_GET['id_tarea']) ? (int)$_GET['id_tarea'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? (int)$_GET['id_proceso'] : 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? (int)$_GET['id_proyecto'] : 0;
$id_auditoria= !empty($_GET['id_auditoria']) ? (int)$_GET['id_auditoria'] : 0;
$id_nota= !empty($_GET['id_nota']) ? (int)$_GET['id_nota'] : 0;
$id_riesgo= !empty($_GET['id_riesgo']) ? (int)$_GET['id_riesgo'] : 0;
$id_indicador= !empty($_GET['id_indicador']) ? (int)$_GET['id_indicador'] : 0;
$id_lista= !empty($_GET['id_lista']) ? (int)$_GET['id_lista'] : 0;
$id_requisito= !empty($_GET['id_requisito']) ? (int)$_GET['id_requisito'] : 0;

$year= !empty($_GET['year']) ? $_GET['year'] : null;
$month= !is_null($_GET['month']) ? $_GET['month'] : null;
if ($year == $_SESSION['current_year'] && is_null($month))
    $month= $_SESSION['current_month'];

$keywords= !empty($_GET['keywords']) ? $_GET['keywords'] : null;

$obj_doc= new Tdocumento($clink);
$obj_user= new Tusuario($clink);

$obj_doc->SetYear($year);
$obj_doc->SetMonth($month);

$obj_doc->SetIdEvento($id_evento);
$obj_doc->SetIdTarea($id_tarea);
$obj_doc->SetIdProceso($id_proceso);
$obj_doc->SetIdAuditoria($id_auditoria);
$obj_doc->SetIdProyecto($id_proyecto);
$obj_doc->SetIdNota($id_nota);
$obj_doc->SetIdRiesgo($id_riesgo);
$obj_doc->SetIdLista($id_lista);
$obj_doc->SetIdRequisito($id_requisito);
$obj_doc->SetIdIndicador($id_indicador);

$acc= 0;
$flag= false;
if (!empty($id_evento)) {
    $acc= $_SESSION['acc_planwork'] > $acc ? $_SESSION['acc_planwork'] : $acc;
    $flag= true;
}    
if (!empty($id_tarea)) {
    $acc= $_SESSION['acc_planwork'] > $acc ? $_SESSION['acc_planwork'] : $acc;
    $flag= true;
}   
if (!empty($id_riesgo)) {
    $acc= $_SESSION['acc_planrisk'] > $acc ? $_SESSION['acc_planrisk'] : $acc;
    $flag= true;
}    
if (!empty($id_nota)) {
    $acc= $_SESSION['acc_planheal'] > $acc ? $_SESSION['acc_planheal'] : $acc;
    $flag= true;
}    
if (!empty($id_proyecto)) {
    $acc= $_SESSION['acc_planproject'] > $acc ? $_SESSION['acc_planproject'] : $acc;
    $flag= true;
}    
if (!empty($id_auditoria)) {
    $acc= $_SESSION['acc_planaudit'] > $acc ? $_SESSION['acc_planaudit'] : $acc;
    $flag= true;
}  
if (!empty($id_requisito) || !empty($id_indicador)) {
    $flag= true;
}

if (!$flag) {
    $obj_doc->SetIdUsuario($_SESSION['id_usuario']);
    $obj_doc->listar_by_usuarios(true, $keywords);
} 

$obj_doc->listar(false, $keywords);
$array_files = $obj_doc->array_files;
    
$visible= (empty($action) || $action == 'list') ? 'hidden' : 'visible';
$display= (empty($action) || $action == 'list') ? 'none' : 'block';

$obj_prs= new Tproceso($clink);
$array_chief_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);

$if_jefe= false;
if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
    $if_jefe= true;
if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe= true;
if ($acc == _ACCESO_BAJA && ($id_proceso == $_SESSION['usuario_proceso_id'] && $id_proceso != $_SESSION['id_entity']))
    $if_jefe= true;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>GESTIÓN DE DOCUMENTOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
    <script src="../libs/bootstrap-table/extensions/toolbar/bootstrap-table-toolbar.js"></script>

    <link href="../libs/upload/upload.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/upload/upload.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js"></script>

    <script type="text/javascript" src="../js/general.js"></script>
    <script type="text/javascript" src="../js/string.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <link rel="stylesheet" href="../css/document.css?version=" />

    <style type="text/css">
    #idem-evento {
        display: block;
        overflow: hidden;
    }
    #btn-print {
        display: inline-block;
    }
    .ajax-panel {
        width: 70%;
    }
    </style>

    <script language="javascript" type="text/javascript">
    var ifnew = true;

    function form_doc(id) {
        ifnew = true;

        var id_doc = id > 0 ? $('#id_doc_' + id).val() : 0;
        var year = $("#year").val();
        var month = $("#month").val();
        var id_evento = $('#id_evento').val();
        var id_auditoria = $('#id_auditoria').val();
        var id_tarea = $('#id_tarea').val();
        var id_nota = $('#id_nota').val();
        var id_riesgo = $('#id_riesgo').val();
        var id_proyecto = $('#id_proyecto').val();
        var id_proceso = $('#id_proceso').val();
        var id_requisito = $('#id_requisito').val();
        var id_indicador = $('#id_indicador').val();

        $("#id").val(id_doc);

        var url = '../form/ajax/fdocumento.ajax.php?id=' + id_doc + '&id_evento=' + id_evento + '&id_nota=' + id_nota +
            '&id_riesgo=' + id_riesgo + '&id_tarea=' + id_tarea;
        url += '&id_auditoria=' + id_auditoria + '&id_proyecto=' + id_proyecto + '&id_proceso=' + id_proceso +
            '&year=' + year + '&month=' + month;
        url += '&id_requisito=' + id_requisito + '&id_indicador=' + id_indicador;

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);

        var title = id > 0 ? ": " + $("#nombre_doc_" + id).val() : "";
        displayFloatingDiv('div-ajax-panel', "CARGAR DOCUMENTO" + title, 80, 0, 3, 1);

        $("#tr-file").show();

        limpiar_doc();
    }

    function del_doc(id) {
        var id_usuario = parseInt($("#id_usuario").val());

        if (parseInt($("#id_usuario_doc_" + id).val()) != id_usuario && parseInt($("#id_responsable_doc_" + id)
                .val()) != id_usuario) {
            $('#id_usuario').focus(focusin($('#id_usuario')));
            alert(
                "Usted no tiene permiso para eliminar el documento. Solo el que lo subio al servidor o el responsble tiene este acceso."
                );
            return;
        }

        confirm("El documento será eliminado del sistema. ¿Está seguro de querer borrar esta versión del documento " +
            $("#nombre_doc_" + id).val() + " ?",
            function(ok) {
                if (!ok)
                    return;
                else {
                    var form = document.forms['form-doc'];
                    var keywords = encodeURI($("#keywords").val());

                    form.action = '../php/document.interface.php?signal=<?=$signal?>&action=delete&id=' + $(
                        "#id_doc_" + id).val() + '&keywords=' + keywords;

                    parent.app_menu_functions = false;
                    $('#_submit').hide()
                    $('#_submited').show();

                    form.submit();
                }
            });
    }

    function edit_doc(id) {
        var id_usuario = parseInt($("#id_usuario").val());

        if (parseInt($("#id_usuario_doc_" + id).val()) != id_usuario && parseInt($("#id_responsable_doc_" + id)
                .val()) != id_usuario) {
            alert(
                "Usted no tiene permiso para modificar el documento. Solo el que lo subio al servidor o el responsble tiene este acceso."
                );
            return;
        }

        form_doc(id);
        $("#tr-file").css("display", "none");
        ifnew = false;
    }

    function close_doc() {
        parent.app_menu_functions = false;
        CloseWindow('div-ajax-panel');
        ifnew = true;
    }

    function limpiar_doc() {
        ifnew = true;

        $("#usuario_doc").val(null);
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
        if (!parseInt($("#usuario_doc").val())) {
            $('#usuario_doc').focus(focusin($('#usuario_doc')));
            alert("No ha especificado el responsable del documento");
            return false;
        }
        /*
        if (!Entrada($("#descripcion_doc").val()) && !Entrada($("#keywords_doc").val())) {
            $('#descripcion_doc').focus(focusin($('#descripcion_doc')));
            alert("Debe de dar una breve descripción del documento o un grupo de palabras claves que ayuden en su búsqueda y rápida localización.");
            return false;
        }
        */
        var form = document.forms['form-doc'];
        var action = ifnew ? 'add' : 'update';
        var keywords = encodeURI($("#keywords").val());

        var url = '../php/document.interface.php?signal=<?=$signal?>&action=' + action + '&keywords=' + keywords;
        if (!ifnew && parseInt($("#id").val()) > 0)
            url += '&id=' + $("#id").val();

        form.action = url;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        form.submit();
    }
    </script>

    <script type="text/javascript">
    function refreshp(flag) {
        if (flag == 1) 
            return true;

        var id_proceso = $("#proceso").val();
        var year = $("#year").val();
        var month = $("#month").val();
        var action = $("#exect").val();
        var keywords = encodeURI($("#keywords_doc").val());

        var id_usuario= $('#id_usuario').val();
        var id_evento= $('#id_evento').val();
        var id_auditoria= $('#id_auditoria').val();
        var id_tarea = $('#id_tarea').val();
        var id_proyecto= $('#id_proyecto').val();
        var id_nota= $('#id_nota').val();
        var id_riesgo= $('#id_riesgo').val();
        var id_lista= $('#id_lista').val();
        var id_requisito= $('#id_requisito').val();
        var id_indicador= $('#id_indicador').val();

        if (month == -1)
            month = 0;
        $("#keywords").val($("#keywords_doc").val());

        var url = '&keywords=' + keywords + '&action=' + action + '&id_proceso=' + id_proceso + '&year=' + year;
        url+= '&id_usuario='+id_usuario+'&id_evento='+id_evento+'&id_auditoria='+id_auditoria+'&id_proyecto='+id_proyecto;
        url+= '&id_nota='+id_nota+'&id_riesgo='+id_riesgo+'&id_lista='+id_lista+'&id_requisito='+id_requisito;
        url += '&id_indicador='+id_indicador+'&month=' + month + '&signal=<?=$signal?>'+'&id_tarea='+id_tarea;

        self.location.href = 'fdocument.php?' + url;
    }

    function form_filter() {
        ifnew = true;
        displayFloatingDiv('div-ajax-panel-filter', "FILTRAR LA LISTA DE ARCHIVOS", 60, 0, 5, 5);
    }

    function close_filter() {
        parent.app_menu_functions = false;
        CloseWindow('div-ajax-panel-filter');
        ifnew = true;
    }

    function closep() {
        <?php if ($signal != 'fmatter') { ?>
        if (opener)
            opener.location.reload();
        <?php } ?> 
        
        <?=$signal == "tablero" || $signal == "fmatter" ? "self.close()" : "self.location.href='../html/background.php?csfr_token=<?={$_SESSION['csfr_token']}?>&'"?>;
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        InitDragDrop();

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n", " ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>


<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">GESTIÓN DE DOCUMENTOS</div>
                <div class="card-body">

                    <!-- documentos -->
                    <?php if (!empty($id_auditoria) || !empty($id_proyecto) || !empty($id_evento)) { ?>
                    <div class="alert alert-info" style="margin: 2px;">
                        <?php
                        if (!empty($id_evento)) {
                            $tobj = new Tevento($clink);
                            $tobj->Set($id_evento);
                        ?>
                        <strong>Reunion:</strong> <?=$tobj->GetNombre()?><br />
                        <strong>Fecha/Hora:</strong> <?=odbc2time_ampm($tobj->GetFechaInicioPlan())?>
                        <?php } ?>
                        <?php
                        if (!empty($id_proyecto)) {
                            $tobj= new Tproyecto($clink);
                            $tobj->Set($id_proyecto);
                        ?>
                        <strong>Proyecto:</strong> <?=$tobj->GetNombre()?><br />
                        <strong>Fecha de Inicio:</strong> <?=odbc2time_ampm($tobj->GetFechaInicioPlan())?>
                        <?php } ?>
                        <?php
                        if (!empty($id_auditoria)) {
                            $tobj= new Tauditoria($clink);
                            $tobj->Set($id_auditoria);
                        ?>
                        <strong>Auditoria:</strong>
                        <?="Origen:".$Ttipo_nota_origen_array[$tobj->GetOrigen()] ."  Tipo:". $Ttipo_auditoria_array[$tobj->GetTipo()]?><br />
                        <strong>Fecha de Inicio:</strong> <?=odbc2time_ampm($tobj->GetFechaInicioPlan())?>
                        <?php } ?>
                        <?php
                        if (!empty($id_lista)) {
                            $tobj= new Tlista($clink);
                            $tobj->Set($id_lista);
                        ?>
                        <strong>Lista:</strong> <?=$tobj->GetNombre()?> <br />
                        <?php } ?>
                        <?php
                        if (!empty($id_requisito)) {
                            $tobj= new Tlista_requisito($clink);
                            $tobj->Set($id_requisito);
                        ?>
                        <strong>Requisito:</strong> <?=$tobj->GetNombre()?> <br />
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <!-- documentos -->

                    <form id="form-doc" action='javascript:' class="form-horizontal" method=post
                        enctype="multipart/form-data">
                        <input type=hidden id="id" name="id" value="" />

                        <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                        <input type="hidden" id="menu" name="menu" value="document" />

                        <input type="hidden" id="id_usuario" name="id_usuario" value="<?=$_SESSION['id_usuario']?>" />
                        <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" id="id_evento" name="id_evento" value="<?=$id_evento ?>" />
                        <input type="hidden" id="id_tarea" name="id_tarea" value="<?=$id_tarea ?>" />
                        <input type="hidden" id="id_auditoria" name="id_auditoria" value="<?=$id_auditoria?>" />
                        <input type="hidden" id="id_proyecto" name="id_proyecto" value="<?=$id_proyecto?>" />
                        <input type="hidden" id="id_nota" name="id_nota" value="<?=$id_nota?>" />
                        <input type="hidden" id="id_riesgo" name="id_riesgo" value="<?=$id_riesgo ?>" />
                        <input type="hidden" id="id_lista" name="id_lista" value="<?=$id_lista ?>" />
                        <input type="hidden" id="id_requisito" name="id_requisito" value="<?=$id_requisito ?>" />
                        <input type="hidden" id="id_indicador" name="id_indicador" value="<?=$id_indicador ?>" />
                        <input type="hidden" id="keywords" name="keywords" value="<?=$keywords?>" />

                        <div id="toolbar">
                            <div class="btn-toolbar" role="form">
                                <button id="btn_agregar" type="button" onclick="form_doc(0);" class="btn btn-primary"
                                    style="display:<?= $display ?>;">
                                    <i class="fa fa-plus"></i>Agregar
                                </button>

                                <button class="btn btn-info ml-1" onclick="form_filter();">
                                    <i class="fa fa-filter"></i>Filtrado Avanzado
                                </button>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-12 col-lg-12">
                                <table class="table table-striped table-hover" data-toggle="table" data-height="420"
                                    data-toolbar="#toolbar" data-search="true" data-show-columns="true">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th></th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Asociado a:</th>
                                            <th>Fecha/Hora</th>
                                            <th>Responsable</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $array_class= array('');
                                        $i= 0;
                                        foreach ($array_files as $row) {
                                            ++$i;
                                        ?>
                                        <tr>
                                            <td>
                                                <?=$i?>
                                                <input type="hidden" id="id_doc_<?=$i?>" name="id_doc_<?=$i?>"
                                                    value="<?=$row['id']?>" />
                                                <input type="hidden" id="id_usuario_doc_<?=$i?>"
                                                    name="id_usuario_doc_<?=$i?>" value="<?=$row['id_usuario']?>" />
                                            </td>

                                            <td>
                                                <?php if ($if_jefe || ($_SESSION['id_usuario'] == $row['id_responsable'] || $_SESSION['id_usuario'] == $row['id_usuario'])) { ?>
                                                <a class="btn btn-danger btn-sm" href="#" title="Eliminar"
                                                    onclick="del_doc(<?=$i?>);"
                                                    style="cursor:pointer; visibility: <?=$visible?>">
                                                    <i class="fa fa-trash"></i>Eliminar
                                                </a>
                                                <a class="btn btn-warning btn-sm" href="#" title="Editar"
                                                    onclick="edit_doc(<?=$i?>);"
                                                    style="cursor:pointer; visibility: <?=$visible?>">
                                                    <i class="fa fa-edit"></i>Editar
                                                </a>
                                                <?php } ?>
                                            </td>

                                            <td>
                                                <?php
                                                if (isset($obj_doc)) 
                                                    unset($obj_doc);
                                                $obj_doc= new Tdocumento($clink);
                                                $obj_doc->Set($row['id']);

                                                $type= get_file_type($obj_doc->filename);
                                                $mime= mime_type($type['ext']);

                                                $array= get_file_type($row['nombre'])
                                                ?>

                                                <a href="../php/show.interface.php?id=<?=$row['id']?>"
                                                    name="<?=$obj_doc->filename?>" type="<?=$mime?>" target="_blank">
                                                    <img src="../img/<?=$array['img']?>" alt="<?=$array['type']?>"
                                                        title="<?=$array['type']?>" />
                                                    <?=$row['nombre']?>
                                                </a>

                                                <input type="hidden" id="nombre_doc_<?=$i?>" name="nombre_doc_<?=$i?>"
                                                    value="<?=$row['nombre']?>" />
                                            </td>

                                            <td>
                                                <?=$row['descripcion']?>
                                                <input type="hidden" id="descripcion_doc_<?=$i?>"
                                                    name="descripcion_doc_<?=$i?>" value="<?=$row['descripcion']?>" />
                                                <input type="hidden" id="keywords_doc_<?=$i?>"
                                                    name="keywords_doc_<?=$i?>" value="<?=$row['keywords']?>" />
                                            </td>

                                            <td>
                                                <?=$obj_doc->read_associeted_to($row)?>
                                            </td>

                                            <td>
                                                <?=odbc2time_ampm($row['cronos'])?>
                                            </td>

                                            <td>
                                                <?php
                                                $mail= $obj_user->GetEmail($row['id_responsable']);
                                                echo textparse($mail['nombre']);
                                                if (!empty($mail['cargo']))
                                                    textparse($mail['cargo']);
                                                ?>
                                                <input type="hidden" id="id_responsable_doc_<?=$i?>"
                                                    name="id_responsable_doc_<?=$i?>"
                                                    value="<?=$row['id_responsable']?>" />
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <input type="hidden" id="cant_doc" name="cant_doc" value="<?=$i?>">

                        <!-- Filtrado Avanzado -->
                        <div id="div-ajax-panel-filter" class="ajax-panel" data-bind="draganddrop">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="panel-title ajax-title col-11 m-0 win-drag">DOCUMENTO</div>

                                        <div class="col-1 m-0 close">
                                            <a href="javascript:close_filter();" title="cerrar ventana">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="col-12">
                                        <div class="form-group row">
                                            <label class="text col-12">
                                                Palabras claves, separadas por punto y coma(;):
                                            </label>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-12 text">
                                                <textarea rows="2" id="keywords_doc" name="keywords_doc" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-form-label col-md-2">
                                                Año:
                                            </label>
                                            <div class=" col-md-3">
                                                <?php $_year= (int)date('Y'); ?>
                                                <select name="year" id="year" class="form-control input-sm">
                                                    <?php for ($i = $_year - 5; $i <= $_year; ++$i) { ?>
                                                    <option value="<?= $i ?>" <?php if ($i == $year || (empty($year) && $i == $_year)) echo "selected" ?>>
                                                        <?= $i ?>
                                                    </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <label class="col-form-label col-md-2">
                                                Mes:
                                            </label>
                                            <div class="col-md-4">
                                                <select name="month" id="month" class="form-control input-sm">
                                                    <option value="-1">Select...</option>
                                                    <?php for ($i = 1; $i <= 12; ++$i) { ?>
                                                    <option value="<?= $i ?>"
                                                        <?php if ($i == $month) echo "selected" ?>>
                                                        <?= $meses_array[$i] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

        
                                        <label class="col-md-12 text">
                                            Proceso o dirección donde se originó o pertenece:
                                        </label>

                                        <div class="col-md-12">
                                            <?php
                                            $top_list_option= "seleccione........";
                                            $id_list_prs= null;
                                            $order_list_prs= 'eq_asc_desc';
                                            $reject_connected= false;
                                            $in_building= false;
                                            $only_additive_list_prs= false;
                                            $id_select_prs= $id_proceso;

                                            require_once "inc/_select_prs.inc.php";
                                            ?>
                                        </div>
                                      
                                    </div>

                                    <div class="btn-block btn-app">
                                        <button class="btn btn-primary" onclick="refreshp(0)" title="Filtrar documentos">Filtrar</button>
                                        <button class="btn btn-warning" onclick="close_filter()" title="Cerrar">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Filtrado Avanzado -->


                        <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

                        </div>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <button class="btn btn-warning" type="reset" onclick="closep()">Cerrar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
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