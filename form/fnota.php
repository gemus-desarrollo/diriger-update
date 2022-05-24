<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/tarea.class.php";
require_once "../php/class/nota.class.php";
require_once "../php/class/register_nota.class.php";
require_once "../php/class/lista.class.php";

require_once "../php/class/auditoria.class.php";

$_SESSION['debug']= 'no';

$signal= 'nota';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list') {
    $acc= $_SESSION['acc_planrisk'];
    $year= date('Y');
    $id_proceso= $_SESSION['usuario_proceso_id'];

    include_once "inc/_form_pemit.inc.php";
}

if ($action == 'add')
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else
    $obj= new Tnota($clink);

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$id_nota= $obj->GetIdNota();
$id_nota= !empty($id_nota) ? $id_nota : 0;
$redirect= $obj->redirect;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
str_replace("\n", " ", addslashes($error));

$fecha_final_real= $obj->GetFechaFinReal();

$id_auditoria= !is_null($_GET['id_auditoria']) ? $_GET['id_auditoria'] : $obj->GetIdAuditoria();
if (empty($id_auditoria))
    $id_auditoria= 0;
$auditoria= null;

if (!empty($id_auditoria)) {
    $obj_auditoria= new Tauditoria($clink);
    $obj_auditoria->Set($id_auditoria);
    $id_auditoria_code= $obj_auditoria->get_id_code();

    $origen= $obj_auditoria->GetOrigen();
    $tipo= $obj_auditoria->GetTipo_auditoria();
    $origen= "Origen:".$Ttipo_nota_origen_array[$origen] ."     Tipo:". $Ttipo_auditoria_array[$tipo];

    $inicio= odbc2date($obj_auditoria->GetFechaInicioPlan());
    $fin= odbc2date($obj_auditoria->GetFechaFinPlan());
    $organismo= $obj_auditoria->GetOrganismo();
    $auditoria= "$origen \nInicio: $inicio  fin: $fin \nOrganismo: $organismo";

    $obj_user= new Tusuario($clink);
    $email= $obj_user->GetEmail($obj_auditoria->GetIdResponsable());
    $responsable= $email['nombre'];
    if (!empty($email['cargo']))
        $responsable.= ', '.textparse($email['cargo']);
    $auditoria.= "\nResponsable: $responsable";

    $auditoria.= "\nObjetivos: ".$obj_auditoria->GetObjetivo();
}

$origen= !is_null($_GET['origen']) ? $_GET['origen'] : $obj->GetOrigen();
$tipo= !is_null($_GET['tipo']) ? $_GET['tipo'] : $obj->GetTipo();
$lugar= !is_null($_GET['lugar']) ? urldecode($_GET['lugar']) : $obj->GetLugar();
$descripcion= !is_null($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$observacion= !is_null($_GET['observacion']) ? urldecode($_GET['observacion']) : $obj->GetObservacion();
$requisito= !is_null($_GET['requisito']) ? $_GET['requisito'] : $obj->GetRequisito();
$cumplimiento= !is_null($_GET['cumplimiento']) ? $_GET['cumplimiento'] : $obj->GetCumplimiento();

$fecha_origen= !empty($_GET['fecha_origen']) ? time2odbc(urldecode($_GET['fecha_origen'])) : null;
$fecha_inicio= !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : $obj->GetFechaInicioReal();
if (empty($fecha_inicio) && !empty($fecha_origen)) 
    $fecha_inicio= $fecha_origen;

if (!empty($fecha_inicio)) {
    $year= date('Y', strtotime($fecha_inicio));
    $month= date('m', strtotime($fecha_inicio));
    $day= date('d', strtotime($fecha_inicio));
} else {
    $year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
    if ($year == date('Y')) {
        $month= date('m');
        $day= date('d');
    } else {
        $month= '01';
        $day= '01';
    }
}

$fecha_inicio= "$year-$month-$day".' 08:30';

$fecha_termino= !empty($_GET['fecha_termino']) ? time2odbc(urldecode($_GET['fecha_termino'])) : null;
$fecha_fin= !empty($_GET['fecha_fin']) ? urldecode($_GET['fecha_fin']) : $obj->GetFechaFinPlan();
if (empty($fecha_fin) && !empty($fecha_termino)) 
    $fecha_fin= $fecha_termino;

if (!empty($fecha_fin)) {
    $year= date('Y', strtotime($fecha_fin));
    $month= date('m', strtotime($fecha_fin));
    $day= date('d', strtotime($fecha_fin));
} else {
    $month= '12';
    $day= '31';
}
$fecha_fin= "$year-$month-$day".' 17:30';

$fecha_final_real= $obj->GetFechaFinReal();

$leg= !is_null($_GET['leg']) ? $_GET['leg'] : $obj->GetIfRequisito_leg();
$reg= !is_null($_GET['reg']) ? $_GET['reg'] : $obj->GetIfRequisito_reg();
$proc= !is_null($_GET['proc']) ? $_GET['proc'] : $obj->GetIfRequisito_proc();

$requisito= !is_null($_GET['requisito']) ? urldecode($_GET['requisito']) : $obj->GetRequisito();
$norma= !is_null($_GET['norma']) ? urldecode($_GET['norma']) : $obj->GetNorma();
$sst= !is_null($_GET['sst']) ? urldecode($_GET['sst']) : $obj->GetObservacion_sst();
$ma= !is_null($_GET['ma']) ? urldecode($_GET['ma']) : $obj->GetObservacion_ma();

if (!empty($id_auditoria)) {
    $fecha_auditoria= odbc2date($obj_auditoria->GetFechaInicioPlan()).' - '.odbc2date($obj_auditoria->GetFechaFinPlan());
    $objetivo= $obj_auditoria->GetObjetivo();
}

$id_lista= !is_null($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdlista();
if (empty($id_lista))
    $id_lista= 0;
$id_requisito= !empty($_GET['id_requisito']) ? $_GET['id_requisito'] : $obj->GetIdRequisito();
if (empty($id_requisito))
    $id_requisito= 0;

$lista_text= null;

if (!empty($id_requisito)) {
    $obj_requisito= new Tlista_requisito($clink);
    $obj_requisito->SetIdRequisito($id_requisito);
    $obj_requisito->Set();

    $requisito_text= $obj_requisito->GetNombre();
    $id_lista= $obj_requisito->GetIdLista();
    $id_lista_code= $obj_requisito->get_id_lista_code();
    $id_requisito_code= $obj_requisito->get_id_requisito_code();
}

if (!empty($id_lista)) {
    $obj_list= new Tlista($clink);
    $obj_list->SetIdLista($id_lista);
    $obj_list->Set();
    $id_lista_code= $obj_list->get_id_lista_code();
    $lista_text= $obj_list->GetNombre();
}

if ((!empty($id_nota) && !empty($id_requisito)) && empty($cumplimiento)) {
    $obj_reg= new Tregister_nota($clink);
    $obj_reg->SetIdNota($id_nota);
    $obj_reg->SetIdRequisito($id_requisito);
    $row=  $obj_reg->getNota_reg(null, false, null, null, false);

    $cumplimiento= $row['cumplimiento'];
}

$url_page= "../form/fnota.php?signal=$signal&action=$action&exect=$action&menu=nota&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&noconf=$noconf&obser=$obser&mej=$mej";
$url_page.= "&id_lista=$id_lista&id_requisito=$id_requisito&cumplimiento=$cumplimiento";

add_page($url_page, $action, 'f');

$text_title= "el hallazgo";
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>NOTA DE HALLAZGO</title>

    <?php require_once "inc/tarea_tabs.ini.inc.php";?>

    <script type="text/javascript" src="../js/causas.js?"></script>

    <script language="javascript">
    function valida_date(flag) {
        if (flag == 1 || flag == 0) {
            if (!Entrada($('#fecha_inicio').val())) {
                $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
                alert(
                    'Introduzca la fecha en la que se identificó la nota o en la que se debe iniciar la gestión o seguimiento a la nota (No conformidad, observación o Mejora'
                    );
                return false;

            } else if (!isDate_d_m_yyyyy($('#fecha_inicio').val())) {
                $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
                alert('Fecha de identificación con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
                return false;
            }
        }

        if (flag == 2 || flag == 0) {
            if (!Entrada($('#fecha_fin').val())) {
                $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
                alert(
                    'Introduzca la propuesta de fecha para el cierre de la nota (No conformidad, observación o Mejora'
                    );
                return false;

            } else if (!isDate_d_m_yyyyy($('#fecha_fin').val())) {
                $('#fecha_fin').focus(focusin($('#fecha_fin')));
                alert('Fecha de cierre con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
                return false;
            }
        }

        if (DiferenciaFechas($('#fecha_fin').val(), $('#fecha_inicio').val(), 's') < 0) {
            $('#fecha_fin').focus(focusin($('#fecha_fin')));
            alert(
                'La fecha propuesta para el cierre no puerde ser anterior a la fecha en la que se identificó el Hallazgo.'
                );
            if (flag == 1)
                $('#fecha_inicio').empty();
            if (flag == 2)
                $('#fecha_fin').empty();
            return false;
        }

        return true;
    }

    function validar() {
        var form = document.forms[0];
        var text;
        var in_auditoria= false;

        if ($('#proceso').val() == 0) {
            $('#proceso').focus(focusin($('#proceso')));
            alert('Debe especificar el proceso interno o Unidad Organizativa en el que se hizo el hallazgo.');
            if (parseInt($("#control_page_origen").val()) == 0) 
                return;
            else     
                return false;
        }

        if ($('#exect').val() == "add") {
            if (!this_0())
                if (parseInt($("#control_page_origen").val()) == 0) 
                    return;
                else     
                    return false;   
        } else {
            in_auditoria= parseInt($('#origen').val()) ? true : false;
            if (!this_1())
                if (parseInt($("#control_page_origen").val()) == 0) 
                    return;
                else     
                    return false;               
        }

        function this_0() {
            if ($('#origen').val() == 0) {
                text = "¿Esta Nota de Hallazgo se ha producido en el contexto de una auditoría o control? ";
                confirm(text, function(ok) {
                    if (!ok) {
                        in_auditoria= false;
                        if (!this_1()) 
                            return false;                    
                    } else {
                        in_auditoria= true;
                        $('#origen').focus(focusin($('#origen')));
                        alert("Debe especificar el origen del hallazgo");  
                        return false;
                    }                    
                });
            } else {
                in_auditoria= true;
                if (!this_1())  
                    return false;
            }  
        }        

        function this_1() {
            if (parseInt($('#id_requisito').val()) != 0) {
                if ($('#cumplimiento').val() == 0) {
                    $('#cumplimiento').focus(focusin($('#cumplimiento')));
                    alert("Debe especificar el estado de cumplimiento del requisito");    
                    return false;
                }
            }    
            
            if (in_auditoria && parseInt($('#id_auditoria').val()) == 0) {
                text = "¿Esta Nota de Hallazgo se ha producido en el contexto de una auditoría o control? ";
                text += "¿Desea registrar esta nota como parte de los resultados de una auditoría o control?";
                confirm(text, function(ok) {
                    if (ok) {
                        mostrar(0);    
                        return false;
                    } else {
                        if (!this_2())   
                            return false;
                    }
                });
            } else {
                if (!this_2())    
                    return false;
            }
            
            return true;
        }

        function this_2() {
            if ($('#tipo').val() == 0) {
                $('#tipo').focus(focusin($('#tipo')));
                alert("Debe especificar el tipo de hallazgo (No Conformidad, Observación o Nota de Mejora)");  
                return false;
            }
            if ($('#id_requisito').val() == 'undefined' && !Entrada($('#lugar').val())) {
                $('#lugar').focus(focusin($('#lugar')));
                alert('Debe identificar el lugar o area funcional en la que se hizo el hallazgo.');   
                return false;
            }
            if ($('#id_requisito').val() == 'undefined') {
                $('#id_requisito').val(0);
                $('#id_requisito_code').val('');
            }
            if (parseInt($('#id_requisito').val()) == 0 && !Entrada($('#descripcion').val())) {
                $('#descripcion').focus(focusin($('#descripcion')));
                alert("Debe describir la Nota de hallazgo (No Conformidad, Observación o Nota de Mejora).");  
                return false;
            }

            if (!valida_date(0))   
                return false;

            form.action = '../php/nota.interface.php';

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            form.submit();
        }
    }
    </script>

    <script language="javascript">
    function refreshtype() {
        if (parseInt($('#tipo').val()) != <?= _NO_CONFORMIDAD?>)
            $('#tbody_incump').hide();
        else
            $('#tbody_incump').show();
    }

    function validar_lista() {
        if (parseInt($('#lista').val()) == 0) {
            $('#id_lista_code').val('');
            $('#id_lista').val(0);
            $('#id_lista_code').val('');
            $('#lista_text').val('');

            HideContent('div-ajax-panel-lista');

            alert("Debe definir la Lista de chequeo o Guía de control desde la que selecionarán los requisitos");
            return;
        }

        var id_lista = $('#lista').val();
        $('#id_lista').val(id_lista);
        $('#id_lista_code').val($('#id_lista_code_' + id_lista).val());
        $('#lista_text').val($('#lista option:selected').text());

        HideContent('div-ajax-panel-lista');
    }

    function show_lista() {
        var id_lista = $('#lista').val();
        var year = $('#year').val();

        $('#id_lista').val(id_lista);
        $('#id_lista_code').val($('#id_lista_code_' + id_lista).val());

        var url = 'ajax/select_lista.ajax.php?year=' + year + '&id_lista=' + id_lista;
        url += '&id_auditoria=' + $('#id_auditoria').val() + '&id_proceso=' + $('#proceso').val();

        displayFloatingDiv('div-ajax-panel-lista', "LISTAS DE CHEQUEO", 80, 0, 5, 10);

        var capa = 'div-ajax-lista';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);
    }

    function show_requisito() {
        var id_lista = $('#lista').val();

        if (parseInt(id_lista) == 0) {
            $('#id_lista_code').val('');
            alert("Debe definir la Lista de chequeo o Guía de control desde la que selecionarán los requisitos");
            return;
        }

        var year = $('#year').val();
        $('#id_lista').val(id_lista);
        $('#id_lista_code').val($('#id_lista_code_' + id_lista).val());

        var url = 'ajax/llista_requisito.ajax.php?year=' + year + '&id_lista=' + id_lista;
        url += '&id_requisito=' + $('#id_requisito').val();

        displayFloatingDiv('div-ajax-panel', "REQUISITOS EN LISTA DE CHEQUEO", 80, 0, 5, 10);

        var capa = 'div-ajax-panel';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);
    }
    </script>

    <script type="text/javascript">
    var focusin;
    $(document).ready(function() {
        refreshtype();
        refresh_ajax_causas();

        InitDragDrop();

        $('#div_fecha_inicio').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('#div_fecha_fin').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('#div_fecha_inicio').on('change', function() {
            valida_date(1);
        });
        $('#div_fecha_fin').on('change', function() {
            valida_date(2);
        });
        $('#div_fecha_reg').datepicker({
            format: 'dd/mm/yyyy'
        });

        <?php if (!empty($id_nota)) { ?>
        $('ul.nav.nav-tabs li').removeClass('active');
        $(".tabcontent").hide();
        $('#nav-tab6').addClass('active');
        $('#tab6').show();

        tarea_table_ajax('<?=$action?>');
        <?php } ?>

        if (parseInt($('#id_auditoria').val()) == 0) {
            $('#nav-tab8').hide();
            $('#tab8').hide();
        }

        refreshtype();

        try {
            tinymce.init({
                selector: '#descripcion',
                theme: 'modern',
                height: 260,
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
        } catch (e) {

        }

        try {
            $('#descripcion').val(<?= json_encode(!$id_requisito ? $descripcion : '')?>);
        } catch (e) {
            ;
        }

        try {
            tinymce.init({
                selector: '#observacion',
                theme: 'modern',
                height: 260,
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
        } catch (e) {
            ;
        }

        try {
            $('#observacion').val(<?= json_encode($observacion)?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n", " ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">NOTA DE HALLAZGO</div>
                <div class="card-body">
       
                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Datos generales">
                            <a class="nav-link" href="tab1">Identificación</a></li>
                        <li id="nav-tab8"  class="nav-item"
                            title="Requisito de revisado si se aplico la lista de cheque o guía de control">
                            <a class="nav-link" href="tab8">Lista de Chequeo</a></li>
                        <?php if (empty($id_requisito)) { ?>    
                        <li id="nav-tab2" class="nav-item" title="Descripción de la nota">
                            <a class="nav-link" href="tab2">Nota</a></li>
                        <?php } ?>    
                        <li id="nav-tab7" class="nav-item" title="Recomendaciones a tomar en concideración en el cierre de la nota">
                            <a class="nav-link" href="tab7">Recomendaciones</a>
                        </li>
                        <li id="nav-tab3" class="nav-item"
                            title="Impacto en el los Sistemas de Gestion medioambiental y/o en el de Seguridad y Salud en el trabajo">
                            <a class="nav-link" href="tab3">Riesgo laboral / Impacto ambiental</a>
                        </li>
                        <li id="nav-tab4" class="nav-item" title="Procesos en los que se identifico el hallazgo">
                            <a class="nav-link" href="tab4">Procesos Involucrados</a></li>
                        <?php if (!empty($id_nota)) { ?>
                        <li id="nav-tab5" class="nav-item" title="Analísis de causa o Oportunidad de ser una Nota de mejora">
                            <a class="nav-link" href="tab5">Análisis de causas/Oportunidades</a></li>
                        <li id="nav-tab6" class="nav-item" title="Acciones o tareas que deberán ser ejecutadas">
                            <a class="nav-link" href="tab6">Acciones o Tareas</a></li>
                        <?php } ?>
                    </ul>

                    <form class="form-horizontal" name="fevento" id="fevento" action="javascript:validar_main()" method="POST">
                        <input type="hidden" id="exect" name="exect" value="<?= $action ?>" />

                        <input type="hidden" name="menu" id="menu" value="nota" />
                        <input type="hidden" name="id" id="id" value="<?= $id_nota ?>" />
                        <input type="hidden" id="id_riesgo" name="id_riesgo" value="0" />
                        <input type="hidden" name="id_proyecto" id="id_proyecto" value="0" />
                        <input type="hidden" name="id_nota" id="id_nota" value="<?= $id_nota ?>" />

                        <input type="hidden" id="_id_auditoria" name="_id_auditoria" value="<?= $id_auditoria ?>" />
                        <input type="hidden" id="_id_auditoria_code" name="_id_auditoria_code"
                            value="<?= $id_auditoria_code ?>" />
                        <input type="hidden" id="id_auditoria" name="id_auditoria" value="<?= $id_auditoria ?>" />
                        <input type="hidden" id="id_auditoria_code" name="id_auditoria_code" value="<?= $id_auditoria_code ?>" />

                        <input type="hidden" id="id_causa" name="id_causa" value="0" />

                        <input type="hidden" name="signal" id="signal" value="<?= $signal ?>" />
                        <input type="hidden" name="year" id="year" value="<?= $year ?>" />
                        <input type="hidden" name="month" id="month" value="<?= $month ?>" />

                        <input type="hidden" name="fecha_final_real" id="fecha_final_real" value="<?=$fecha_final_real?>" />

                        <input type="hidden" name="id_lista" id="id_lista" value="<?= $id_lista ?>" />
                        <input type="hidden" name="id_lista_code" id="id_lista_code" value="<?= $id_lista_code ?>" />
                        <input type="hidden" name="id_requisito" id="id_requisito" value="<?= $id_requisito ?>" />
                        <input type="hidden" name="id_requisito_code" id="id_requisito_code" value="<?= $id_requisito_code ?>" />

                        <input type="hidden" name="control_page_origen" id="control_page_origen" value="0" />
                        <input type="hidden" name="id_tarea" id="id_tarea" value="0" />
                        <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />

                        <!-- Identificación -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-3 col-lg-3">
                                    Unidad Organizativa(Proceso Interno) auditado:
                                </label>
                                <div class="col-md-9 col-lg-9">
                                    <?php
                                    $top_list_option= "seleccione........";
                                    $id_list_prs= null;
                                    $order_list_prs= 'eq_desc';
                                    $reject_connected= false;
                                    $in_building= ($action == 'add' || $action == 'update') ? true : false;
                                    $only_additive_list_prs= ($action == 'add') ? true : false;
                                    if (!$config->show_group_dpto_risk)
                                        $restrict_prs= $_SESSION['entity_tipo'] < _TIPO_UEB ? array(_TIPO_ARC, _TIPO_GRUPO, _TIPO_DEPARTAMENTO) : array(_TIPO_ARC);
                                    else
                                        $restrict_prs= array(_TIPO_ARC);

                                    $id_select_prs= $id_proceso;
                                    $use_copy_tprocesos= false;
                                    require_once "inc/_select_prs.inc.php";
                                    ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="row col-lg-6">
                                    <label class="col-form-label col-md-3">
                                        Origen:
                                    </label>
                                    <div class="col-md-9">
                                        <select id="origen" name="origen" class="form-control">
                                            <option value="0">...</option>
                                            <?php for ($i = 1; $i < _MAX_TIPO_NOTA_ORIGEN; ++$i) { ?>
                                            <option value="<?= $i ?>"
                                                <?php if ($i == $obj->GetOrigen()) echo "selected='selected'"; ?>>
                                                <?= $Ttipo_nota_origen_array[$i] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row col-lg-6">
                                    <label class="col-form-label col-md-3">
                                        <?php
                                        $tipo = $obj->GetTipo();
                                        $display_tipo = ($tipo == _NO_CONFORMIDAD) ? 'block' : 'none';
                                        ?>
                                        Tipo de Nota:
                                    </label>
                                    <div class="col-md-9">
                                        <select id="tipo" name="tipo" class="form-control" onchange="refreshtype()">
                                            <option value="0">...</option>
                                            <?php for ($i = 1; $i < _MAX_TIPO_NOTA; ++$i) { ?>
                                            <option value="<?= $i ?>"
                                                <?php if ($i == $tipo) echo "selected='selected'"; ?>>
                                                <?= $Ttipo_nota_array[$i] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-5">
                                    Fecha de detección <span class="note">(Fecha inicial para la búsqueda de la
                                        auditoría o acción de control)</span>:
                                </label>
                                <div class="col-md-2">
                                    <div id="div_fecha_inicio" class="input-group date" data-date-language="es">
                                        <input type="datetime" class="form-control input-sm" id="fecha_inicio"
                                            name="fecha_inicio" readonly value="<?=odbc2date($fecha_inicio)?>">
                                        <span class="input-group-text"><span
                                                class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                                <label class="col-form-label col-md-3">
                                    Fecha propuesta para el cierre:
                                </label>
                                <div class="col-md-2">
                                    <div id="div_fecha_fin" class="input-group date" data-date-language="es">
                                        <input type="datetime" class="form-control input-sm" id="fecha_fin"
                                            name="fecha_fin" readonly value="<?=odbc2date($fecha_fin)?>">
                                        <span class="input-group-text"><span
                                                class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <div class="pull-right col-md-12">
                                        <button type="button" class="btn btn-success" onclick="mostrar(0)">Selecionar:
                                            <p>auditoría/control</p>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-10 col-lg-10">
                                    <div class="pull-left col-md-12">
                                        <textarea class="form-control" id="auditoria" name="auditoria" rows="6"
                                            readonly="readonly"><?=$auditoria?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Lugar:
                                </label>
                                <div class="col-md-10 col-lg-10">
                                    <input type="text" name="lugar" rows="2" id="lugar" class="form-control"
                                        value="<?=$obj->GetLugar()?>" />
                                </div>
                            </div>

                            <div id="tbody_incump" class="row">
                                <div class="form-group row">
                                    <label class="col-form-label col-md-3 col-lg-3">
                                        Tipo de incumplimiento o violación:
                                    </label>
                                    <div class="col-md-9 col-lg-9">
                                        <fieldset class="fieldset">
                                            <div class="col-md-12">
                                                <label class="checkbox text">
                                                    <input type="checkbox" id="req_leg" name="req_leg" value="1"
                                                        <?php if ($leg) echo "checked='checked'" ?> />
                                                    Requisito legal. (Se constituye en una ilegalidad)
                                                </label>
                                                <label class="checkbox text">
                                                    <input type="checkbox" id="req_reg" name="req_reg" value="1"
                                                        <?php if ($reg) echo "checked='checked'" ?> />
                                                    Requisito regulatorio. (Asociado a una norma de requisitos para un
                                                    sistema de gestión)
                                                </label>
                                                <label class="checkbox text">
                                                    <input type="checkbox" id="req_proc" name="req_proc" value="1"
                                                        <?php if ($proc) echo "checked='checked'" ?> />
                                                    Requisito asociado a un Procedimiento Interno. (Se incumple una
                                                    decisión administrativa)
                                                </label>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>

                                <div class="form-group row col-12 form-inline">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="mr-2">
                                                Procedimiento/Norma/Legislacion incumplido:
                                            </label>
                    
                                            <input type="text" class="form-control" id="norma" name="norma"
                                                maxlength="120" value="<?=$norma?>" />                                                
                                        </div>
                                    </div>
                                    <div class="col-6 ">
                                        <div class="form-group">
                                            <label class="mr-2">
                                                Requisito/Articulo incumplido:
                                            </label>
                                            <input type="text" class="form-control" id="requisito" name="requisito"
                                                maxlength="10" value="<?=$requisito;?>" />                                        
                                        </div>      
                                    </div>
                                </div>
                            </div>

                        </div> <!-- Identificación -->

                        <!-- descripcion de la nota -->
                        <div class="tabcontent" id="tab2">
                            <textarea name="descripcion" id="descripcion"><?=$descripcion?></textarea>
                        </div> <!-- descripcion de la nota -->

                        <!-- recomendaciones -->
                        <div class="tabcontent" id="tab7">
                            <textarea name="observacion" id="observacion"><?=$observacion?></textarea>
                        </div> <!-- recomendaciones -->

                        <!-- Listado de procesos -->
                        <div class="tabcontent" id="tab4">
                            <?php
                            $id = $id_nota;

                            $obj_prs = new Tproceso_item($clink);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3)
                                $obj_prs->set_use_copy_tprocesos(false);
                            else
                                $exits = $obj_prs->set_use_copy_tprocesos(true);

                            if (!$config->show_group_dpto_risk) {
                                $result_prs_array = $obj_prs->listar_in_order('eq_desc', true, null, false, 'asc');
                            } else {
                                $obj_prs->set_acc($badger->acc);
                                $result_prs_array = $obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], _TIPO_PROCESO_INTERNO);
                            }
                            $cant_prs = $obj_prs->GetCantidad();

                            if (!empty($id_nota)) {
                                $obj_prs->SetIdNota($id_nota);
                                $array_procesos= $obj_prs->GetProcesosRiesgo();
                            }

                            $name_form= "fnota";
                            $restrict_up_prs = true;
                            $restrict_prs = $_SESSION['local_proceso_tipo'] < _TIPO_UEB && !$config->show_group_dpto_risk ? array(_TIPO_ARC, _TIPO_GRUPO, _TIPO_DEPARTAMENTO) : array(_TIPO_ARC);
                            
                            $create_select_input= false;
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- tab4 Procesos-->

                        <!-- la gestion mediambiental y seguridad y salus en el trabbajo -->
                        <div class="tabcontent" id="tab3">
                            <div class="form-group row">
                                <label class="col-md-12 col-lg-12 pull-left">
                                    Riesgo laboral asociado:
                                </label>
                                <div class="col-md-12 col-lg-12">
                                    <textarea class="form-control" id="_observacion_sst" name="observacion_sst"
                                        rows="6"><?=$sst?></textarea>
                                </div>
                                <label class="col-md-12 col-lg-12 pull-left" style="margin-top: 20px;">
                                    Impacto ambiental asociado:
                                </label>
                                <div class="col-md-12 col-lg-12">
                                    <textarea class="form-control" id="_observacion_ma" name="observacion_ma"
                                        rows="6"><?=$ma?></textarea>
                                </div>
                            </div>
                        </div> <!-- la gestion mediambiental y seguridad y salus en el trabbajo -->

                        <!-- lista de chequeo -->
                        <div class="tabcontent" id="tab8">
                            <div class="form-group row">
                                <div class="col-sm-3 col-md-3 col-lg-2">
                                    <button type="button" class="btn btn-info" onclick="show_lista()">Lista de Chequeo</button>
                                </div>

                                <div class="col-sm-9 col-md-9 col-lg-10">
                                    <textarea class="form-control" id="lista_text" name="lista_text" readonly="yes"
                                        rows="3"><?=$lista_text?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-2">
                                    <button type="button" class="btn btn-info"
                                        onclick="show_requisito()">Requisito</button>
                                </div>

                                <div class="col-10">
                                    <textarea class="form-control" id="requisito_text" name="requisito_text"
                                        readonly="yes" rows="3"><?=$requisito_text?></textarea>
                                </div>
                            </div>
                            
                            <?php
                            $_year= date('Y', strtotime($fecha_inicio));
                            $_month= date('m', strtotime($fecha_inicio));
                            ?>

                            <div class="form-group row">
                                <label class="col-form-label col-2">Evaluación:</label>
                                <div class="col-4">
                                    <select class="form-control" id="cumplimiento" name="cumplimiento">
                                        <option value="0">... </option>
                                        <?php
                                        for ($i= 1; $i < 5; $i++) {
                                            $row= $Tcriterio_array[$i];
                                            if (((int)$_year > 2021 || ((int)$_year == 2021 && (int)$_month >= 5)) 
                                                && ($row[1] == _NO_PROCEDE || $row[1] == _EN_PROCESO))
                                                continue;
                                        ?>
                                            <option value="<?=$row[1]?>" <?php if ((int)$row[1] == (int)$cumplimiento) { ?>selected="selected"<?php } ?> title="<?=$row[2]?>"><?=$row[0]?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div> <!-- lista de chequeo -->

                        <!-- tareas asociadas -->
                        <?php if (!empty($id_nota)) { ?>
                        <div class="tabcontent" id="tab6">
                            <div id="ajax-task-table" class="ajax-task-table">

                            </div>
                        </div>
                        <?php } ?>

                        <!-- descripcion de la causa o Nota de mejora -->
                        <?php if (!empty($id_nota)) { ?>
                        <div class="tabcontent" id="tab5">
                            <div id="div-ajax-panel-causa-table" class="container-fluid">

                            </div>

                            <div id="div-ajax-panel-causa-form" class="ajax-panel" data-bind="draganddrop">
                                <div class="card card-primary">
                                    <div class="card-header win-drag">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div
                                                    class="panel-title col-11 win-drag">
                                                    CAUSA / OBSERVACIÓN</div>

                                                <div class="col-1 pull-right">
                                                    <div class="close">
                                                        <a href="javascript:HideContent('div-ajax-panel-causa-form');"
                                                            title="cerrar ventana">
                                                            <i class="fa fa-close"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group row">
                                            <div class="col-md-12 col-lg-12">
                                                <textarea id="causa" name="causa" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-sm-5 col-md-4 col-lg-3">
                                                Fecha de manifestación (aproximada):
                                            </label>
                                            <div class="col-sm-4 col-md-3 col-lg-3">
                                                <div class='input-group date' id='div_fecha_reg'
                                                    data-date-language="es">
                                                    <input id="fecha_reg" name="fecha_reg" type="text"
                                                        class="form-control" value="" />
                                                    <span class="input-group-text"><span
                                                            class="fa fa-calendar"></span></span>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (($action == 'update' || $action == 'add') && empty($fecha_final_real)) { ?>
                                        <div id="_button" class="btn-block btn-app">
                                            <button id="btn_agregar" type="button" class="btn btn-primary"
                                                onclick="validar_causa();">Aceptar</button>
                                            <button id="btn_limpiar" type="button" class="btn btn-warning"
                                                onclick="HideContent('div-ajax-panel-causa-form'); limpiar();">Cancelar</button>
                                        </div>
                                        <?php } ?>

                                        <div id="_button_active" class="submited" align="left" style="display:none">
                                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la
                                            operación puede tardar unos minutos ........
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- div-ajax-panel-causa-form -->

                        </div>
                        <?php } ?>
                        <!-- div-ajax-panel-causa-form -->

                        <!-- form para lista de chequeo -->
                        <div id="div-ajax-panel-lista" class="ajax-panel" data-bind="draganddrop">
                            <div class="card card-primary">
                                <div class="card-header win-drag">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="panel-title col-11 win-drag">
                                                LISTAS DE CHEQUEO O GUÍAS DE CONTROL</div>

                                            <div class="col-1 pull-right">
                                                <div class="close">
                                                    <a href="javascript:HideContent('div-ajax-panel-lista');"
                                                        title="cerrar ventana">
                                                        <i class="fa fa-close"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="div-ajax-lista" class="card-body">


                                </div>
                            </div>
                        </div> <!-- div-ajax-panel-lista -->

                        <!-- buttom -->
                        <hr />
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
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

    <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">


    </div>

</body>

</html>