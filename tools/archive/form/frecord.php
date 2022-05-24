<?php
/*
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */


session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";

require_once "../../../php/class/escenario.class.php";
require_once "../../../php/class/proceso_item.class.php";
require_once "../../../php/class/usuario.class.php";

require_once "../php/class/serie.class.php";
require_once "../php/class/organismo.class.php";
require_once "../php/class/persona.class.php";

require_once "../../../php/class/document.class.php";

require_once "../php/class/ref_archivo.class.php";
require_once "../php/class/archivo.class.php";

$year = !empty($_GET['year']) ? $_GET['year'] : date('Y');
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_archivo= !empty($_GET['id_archivo']) ? $_GET['id_archivo'] : $_GET['id'];

if ($action == 'add' || ($action == 'update' && !empty($id_archivo))) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$obj= null;
if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tarchivo($clink);
    if (!empty($_GET['id_archivo'] || !empty($_GET['id'])) && $action == 'update') {
        $obj->SetIdArchivo($id_archivo);
        $obj->Set();
    }
}

$if_output= null;

if ($action == 'add') {
    $if_output = !empty($_GET['if_output']) ? 1 : 0;
    $obj->SetIfOutput($if_output);
    $obj->SetYear($year);
} else {
    $if_output = $obj->GetIfOutput();
    $id_archivo= $obj->GetIdArchivo();
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$fecha_origen= !empty($_GET['fecha_origen']) ? urldecode($_GET['fecha_origen']) : $obj->GetFechaOrigen();
if (empty($fecha_origen)) 
    $fecha_origen= date('Y-m-d');

$fecha_entrega= !empty($_GET['fecha_entrega']) ? urldecode($_GET['fecha_entrega']) : $obj->GetFechaEntrega();
if (empty($fecha_entrega)) 
    $fecha_entrega= date('Y-m-d h:i A');

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity']; 
    
$codigo= !is_null($_GET['codigo']) ? $_GET['codigo'] : $obj->GetCodigo();
$tipo= !is_null($_GET['tipo']) ? $_GET['tipo'] : $obj->GetTipo();
$descripcion= !is_null($_GET['descripcion']) ? $_GET['descripcion'] : $obj->GetDescripcion();
$antecedentes= !is_null($_GET['antecedentes']) ? urldecode($_GET['antecedentes']) : $obj->GetAntecedentes();
$indicaciones= !is_null($_GET['indicaciones']) ? urldecode($_GET['indicaciones']) : $obj->GetIndicaciones();
$keywords= !is_null($_GET['keywords']) ? $_GET['keywords'] : $obj->GetKeywords();
$sendmail= !is_null($_GET['sendmail']) ? $_GET['sendmail'] : $obj->GetSendMail();
$toshow= !is_null($_GET['toshow']) ? $_GET['toshow'] : $obj->toshow;
$if_immediate= !is_null($_GET['if_immediate']) ? $_GET['if_immediate'] : $obj->GetIfImmediate();
$id_responsable= !is_null($_GET['id_responsable']) ? $_GET['id_responsable'] : $obj->GetIdResponsable();

$fecha_fin_plan= !is_null($_GET['fecha_fin_plan']) ? urldecode($_GET['fecha_fin_plan']) : $obj->GetFechaFinPlan();
$prioridad= !empty($_GET['prioridad']) ? $_GET['prioridad'] : $obj->GetPrioridad();
$clase= !empty($_GET['clase']) ? $_GET['clase'] : $obj->GetClase();

if (is_null($toshow)) 
    $toshow= 0;

$id_archivo= $obj->GetIdArchivo();
$id_archivo_code= $obj->get_id_archivo_code();
$id_documento= $obj->GetIdDocumento();
$id_documento_code= $obj->get_id_documento_code();
$id_evento= $obj->GetIdEvento();
$id_evento_code= $obj->get_id_evento_code();
        
$obj_pers= new Tpersona($clink);
$lugares= $obj_pers->listar_lugares();
$personas= $obj_pers->listar(true, true);

$obj_ref= new Tref_archivo($clink);

if (!empty($id_archivo)) {
    $obj_ref->SetIdArchivo($id_archivo);
    $if_sender= $if_output ? false : true;
    $result_ref= $obj_ref->listar_personas($if_sender);
    
    $obj_ref->listar_usuarios();
    $array_usuarios= $obj_ref->array_usuarios;

    $obj_ref->listar_grupos();
    $array_grupos= $obj_ref->array_grupos;
    
    $i= 0;
    $list_senders= null;
    foreach ($array_usuarios as $user) {
        ++$i;
        $list_senders.= $i > 1 ? "\n" : "";
        $list_senders.= "{$user['nombre']}";
        if (!empty($user['cargo'])) 
            $list_senders.= ", {$user['cargo']}";
    }
    foreach ($array_grupos as $grp) {
        ++$i;
        $list_senders.= $i > 1 ? "\n" : "";
        $list_senders.= "{$grp['nombre']}";
    }
    if ($i == 0) 
        $list_senders= "ARCHIVO";
}

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$id_proceso_code= $obj_prs->get_id_proceso_code();
$codigo_archive= $obj_prs->GetCodigoArchive();
$nombre_prs= $obj_prs->GetNombre();
$nombre_prs.= ", ".$Ttipo_proceso_array[(int)$obj_prs->GetTipo()];

$numero= null;
if ($action == 'add' && is_null($error)) {
    $numero= null;
} else {
    $numero= $obj->GetNumero();
}

$responsable_init= null;

if ($action == 'update' && !empty($id_responsable)) {
    $obj_user= new Tusuario($clink);
    $obj_user->Set($id_responsable);
    $responsable_init= $obj_user->GetNombre();
}

$url_page= "../form/frecord.php?signal=$signal&action=$action&menu=frecord&if_output=$if_output&year=$year";
$url_page.= "&id_proceso=$id_proceso&exect=$action";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>

        <link rel="stylesheet" href="../../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>  

        <link href="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>    

        <link href="../../../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">  
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script> 

        <link rel="stylesheet" type="text/css" media="screen" href="../../../libs/multiselect/multiselect.css?version=" />
        <script type="text/javascript" charset="utf-8" src="../../../libs/multiselect/multiselect.js?version="></script>
    
        <link href="../../../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version="></script>

        <script type="text/javascript" src="../../../js/ajax_core.js?version="></script>  
        
        <link href="../../../libs/upload/upload.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/upload/upload.js"></script>
        
        <script type="text/javascript" src="../../../libs/tinymce/tinymce.min.js"></script> 
        <script type="text/javascript" src="../../../libs/tinymce/jquery.tinymce.min.js"></script> 
        
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js?version="></script> 

        <script type="text/javascript" charset="utf-8" src="../../../js/form.js?version="></script>
        
        <style type="text/css">
            .win-board {
                position: absolute;
                width: 90%;
            }
            .panel-heading strong.text-danger {
                font-size: 1.2em!important;
                color: #ffbf55!important;
            } 
            #div-ajax-print {
                width: 60%;
                display: none;
            }            
        </style>

        <script language="javascript" type="text/javascript">
            var array_organismos= new Array();
        <?php
        $obj_org= new Torganismo($clink);
        $result_org= $obj_org->listar();
        while ($row= $clink->fetch_array($result_org)) {
        ?>
            array_organismos[<?=$row['id']?>]= '<?= textparse($row['nombre'])?>';
        <?php } ?>
        
        </script>
        
        <script language="javascript" type="text/javascript">
            function refreshp(index) {
                if (index == 1) {
                    $('proceso_filter').val($('#proceso').val());
                    refresh_ajax_users();
                }
                if (index == 2) 
                    refresh_ajax_users_responsable();
            }

            function refresh_ajax_users() {
                var id_proceso= $('#proceso').val();
                var id_usuario= $('#id_usuario').val();
                var year= $('#year').val();
                
                if (id_proceso == 0|| id_proceso == null) {
                    $('#usuario').empty();
                    return;
                }

                var _url= 'ajax/select_users.ajax.php?name_select=usuario&id_proceso='+id_proceso+'&id_usuario='+id_usuario;
                    _url+= '&year='+year+'&csfr_token=123abc';

                $.ajax({
            //   data:  parametros,
                    url:   _url,
                    type:  'get',
                    beforeSend: function () {
                        $('#ajax-users').html('Procesando, espere por favor...');
                    },
                    success:  function (response) {
                        $('#ajax-users').html(response);
                        if (id_usuario > 0) 
                            FAjaxUser(); 
                        
                        $('#usuario').change(function(){
                            FAjaxUser(); 
                        });
                        
                        if (oId > 0) 
                            set_data(oId);
                    },
                    
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- refresh_ajax_users');
                    }                    
                });
            }
                    
            function FAjaxMcpo() {
                var id_prov= $('#provincia').val();
                var id_mcpo= $('#id_mcpo').val();
                
                if (id_prov == 0 || id_prov == null) {
                    $('#municipio').empty();
                    $('#municipio').val(0);
                    $('#id_mcpo').val(0);
                    return false;                    
                }

                $.ajax({
                    url: 'ajax/municipio.ajax.php?csfr_token=123abc',
                    data: {id_prov: id_prov, id_mcpo: id_mcpo},
                    type: 'get',
                    dataType: 'html',
                    
                    success: function (response) {
                        $('#ajax-municipio').html(response);
                    },
                    
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- FAjaxMcpo');
                    }                    
                });
            }
                
            function FAjaxPerson() {
                var id_persona= $('#id_persona').val();
                
                $.ajax({
                    url: 'ajax/persons.ajax.php?csfr_token=123abc',
                    data: {id_persona: id_persona},
                    type: 'get',
                    dataType: 'json',
                    
                    success: function (response) {
                        $('#telefono').val(response['telefono']);
                        $('#movil').val(response['movil']);
                        $('#email').val(response['email']);
                        oId > 0 ? $('#provincia').val($('#provincia_'+oId).val()) : $('#provincia').val(response['provincia']);
                        var id_mcpo= oId > 0 ? $('#municipio_'+oId).val() : response['municipio'];
                        $('#municipio').val(id_mcpo);
                        $('#id_mcpo').val(id_mcpo);
                        
                        $('#lugar').val(response['lugar']);
                        $('#direccion').val(response['direccion']);
                        
                        $('#noIdentidad').val(response['noIdentidad']);
                        $('#nombre').val(response['nombre']);
                        
                        $('#organismo').val(response['id_organismo']);
                        $('#cargo').val(response['cargo']);
                        
                        FAjaxMcpo();
                        
                        if (oId > 0) 
                            set_data(oId);
                    },
                    
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- FAjaxPerson');
                    }                    
                });       
            }

            function FAjaxUser() {
                var id_user= $('#usuario').val() == 'undefined' ? 0 : $('#usuario').val();
                
                $.ajax({
                    url: 'ajax/usuario.ajax.php?csfr_token=123abc',
                    data: {id_usuario: id_user},
                    type: 'get',
                    dataType: 'json',
                    
                    success: function (response) {
                        $('#telefono').val('');
                        $('#movil').val('');
                        $('#email').val(response['email']);
                        
                        oId > 0 ? $('#provincia').val($('#provincia_'+oId).val()) : $('#provincia').val(response['provincia']);
                        var id_mcpo= oId > 0 ? $('#municipio_'+oId).val() : response['municipio'];
                        $('#municipio').val(id_mcpo);
                        $('#id_mcpo').val(id_mcpo);
                        $('#lugar').val('');
                        $('#direccion').val('');
                        
                        $('#noIdentidad').val(response['noIdentidad']);
                        $('#nombre').val(response['nombre']);
                        
                        $('#organismo').val(response['id_organismo']);
                        $('#cargo').val(response['cargo']);
                        
                        FAjaxMcpo();
                        
                        if (oId > 0) 
                            set_data(oId);
                    },
                    
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- FAjaxUser');
                    }                    
                });       
            }

            function _validar() {                
                    if ($("#tipo").val() == 0) {
                    $('#tipo').focus(focusin($('#tipo')));
                    alert("No ha especificado el tipo de documento");
                    return false;
                } 
                if (!Entrada($("#fecha_origen").val())) {
                    $('#fecha_origen').focus(focusin($('#fecha_origen')));
                    alert("No ha especificado la fecha en que fue emitido o aprobado el documento");
                    return false;
                }
                if (!Entrada($("#fecha_entrega").val()) || !Entrada($("#hora_entrega").val())) {
                    $('#fecha_entrega').focus(focusin($('#fecha_entrega')));
                    alert("No ha especificado la fecha en la que entrega del documento");
                    return false;
                }
                if (!isCodigo($('#antecedentes').val())) {
                    $('#antecedentes').focus(focusin($('#antecedentes')));
                    alert("El antecedente o procedencia debe ser un número de referencia o registro valido.");
                    return false;                     
                }
                if (!Entrada($('#descripcion').val())) {
                    $('#descripcion').focus(focusin($('#descripcion')));
                    alert("No han descrito el contenido o asunto del documento.");
                    return false;                   
                }
                    
                return true;
            }
            
            function validar() {
                if (!_validar()) 
                    return;

                if ($('#cant_multiselect-users').val() == 0 && $('#if_output').val() == 1) {
                        alert("Debe selecionar a lo(s) <?=!$if_output ? 'Destinatario(s)' : 'Remitente(s)'?> de este documento");
                        return;
                }
                    
                function _this() {
                    if ($('#cant_personas').val() == 0 && $('#if_output').val() == 1) {
                        alert("No ha especificado a lo(s) Destinatario(s) de este documento");
                        return false;
                    } 

                    if ($('#cant_personas').val() == 0 && $('#if_output').val() == 0) {
                        text= "No ha especificado los Remitentes de este documento. El documento será considerado como anonímo. Desea continuar?";
                        confirm(text, function(ok) {
                            if (!ok) 
                                return false; 
                            else {
                                document.forms['frecord'].action= '../php/interface.php';
                                document.forms['frecord'].submit();                                 
                            }
                        }); 
                    } else {
                        document.forms['frecord'].action= '../php/interface.php';
                        document.forms['frecord'].submit();                          
                    }
                }

                if (($('#sendmail').is(':checked') || $('#toshow').is(':checked')) && !Entrada($("#indicaciones").val())) {
                    $('#indicaciones').focus(focusin($('#indicaciones')));
                    alert("No ha escrito las indicaciones derivadas del documento");
                    return;
                }
                if (($('#toshow').is(':checked')) && !Entrada($("#fecha_fin_plan").val())) {
                    $('#fecha_fin_plan').focus(focusin($('#fecha_fin_plan')));
                    text= "No ha definido la fecha de cumplimiento de la indicación. ";
                    text+= "Difina la fecha de cumplimiento para agregarla a los Planes de Trabajo Individuales"
                    alert(text);
                    return;
                } 
                if ($('#toshow').is(':checked') && parseInt($("#responsable").val()) == 0) {
                    $('#responsable').focus(focusin($('#responsable')));
                    alert("Debe elegir al responsable de la tramitación y/o el cumplimiento de la indicación.");
                    return;
                } 
                if (Entrada($('#indicaciones')) && !$('#if_immediate').is(':checked') 
                        && (!Entrada($('#fecha_fin_plan').val())) || !Entrada($('#hora_fin_plan').val())) {
                    $('#fecha_fin_plan').focus(focusin($('#fecha_fin_plan')));
                    var text= "No ha especificado sí se trata de una indicación para su cumplimiento inmediato en una indicación ";
                    text+= "que no tiene fecha y hora de cumplimiento. El sistema lo considerará como una comunicación. ¿Desea continuar?";
                    confirm(text, function(ok) {
                        if (ok) {
                            if (!_this()) 
                                return;
                        } else 
                            return;
                    });
                } else {
                    if (!_this()) 
                        return;
                }               
            } 
                
            var list;
            
            function formPrint(index) {
                if (!_validar()) 
                    return;
            
                displayFloatingDiv('div-ajax-print', '', 0, 0, 20, 20);          
                list= '';
                
                if (index == 0) 
                    for(var i= 1; i <= $('#cant_personas').val(); ++i) 
                        list+= $('#nombre_'+i).val()+', '+$('#cargo_'+i).val()+'<br/>';                 
                else 
                    list = $('#nombre_'+index).val()+', '+$('#cargo_'+index).val();
                
                var id_responsable= $('#responsable').val();

                if (parseInt($('#if_output').val()) == 0) {
                    if (!Entrada(list) && id_responsable > 0) 
                        $('#sender').val(array_usuarios[id_responsable]);
                    else 
                        $('#sender').val(list);
                        
                    $('#target').val($('#list_senders').val());
                }    
                else {
                    $('#target').val(list);
                    $('#sender').val($('#list_senders').val());
                }    
            }
            
            function formPrint_user(id, usuario) {
                if (!_validar()) 
                    return;
            
                displayFloatingDiv('div-ajax-print', '', 0, 0, 20, 20);            
                list= usuario;
                var id_responsable= $('#responsable').val();

                if (parseInt($('#if_output').val()) == 0) {
                    if (!Entrada(list) && id_responsable > 0) 
                        $('#sender').val(array_usuarios[id_responsable]);
                    else 
                        $('#sender').val(list);
                        
                    $('#target').val($('#list_senders').val());
                }    
                else {
                    $('#target').val(list);
                    $('#sender').val($('#list_senders').val());
                }    
            }

            function imprimir_waybill() {
                var indicaciones= encodeURI($('#indicaciones').val());
                var contenido= encodeURIComponent($('#descripcion').val());
                var fecha_entrega= encodeURI($('#fecha_entrega').val() + ' ' + $('#hora_entrega').val());
                var fecha_fin_plan= encodeURI($('#fecha_fin_plan').val() + ' ' + $('#hora_fin_plan').val());
                var if_immediate= $('#if_immediate').is(':checked') ? 1 : 0;
                var numero= encodeURI($('#_numero').val());
                var id_responsable= $('#responsable').val();
                
                var target= encodeURI($('#target').val());
                var sender= encodeURI($('#sender').val());
                var nota= encodeURI($('#nota').val());
                
                var url= '../print/certifico.php?sender='+sender+'&target='+target+'&nota='+nota+'&if_immediate='+if_immediate;
                url+= '&indicaciones='+indicaciones+'&contenido='+contenido+'&fecha_fin_plan='+fecha_fin_plan;
                url+= '&numero='+numero+'&fecha_entrega='+fecha_entrega+'&if_output=<?=$if_output?>&list='+encodeURI(list);
                url+= '&id_responsable='+id_responsable;
                
                show_imprimir(url,"CONDUCE DE <?=$if_output ? "ENTREGA" : 'RECEPCIÓN'?>","width=900,height=600,toolbar=no,location=no,scrollbars=yes");
            }
            
            function set_clase() {
                $('#sendmail').attr('disabled', false);
                if ($('#clase').val() == 4 || $('#clase').val() == 5) 
                    $('#sendmail').attr('disabled', 'disabled');
            }
        </script>
        
        <script type="text/javascript">
            function available_data() {
                $('#organismo').attr('readonly', false);
                $('#lugar').attr('readonly', false);

                $('#telefono').attr('readonly', false);
                $('#movil').attr('readonly', false);

                $('#lugar').attr('readonly', false); 
                $('#direccion').attr('readonly', false); 

                $('#provincia').attr('readonly', false);
                $('#municipio').attr('readonly', false);                 
            } 
            
            function set_person_form() {
                if (!$('#if_anonymous0').is(':checked') && !$('#if_anonymous1').is(':checked') &&!$('#if_anonymous2').is(':checked') && !$('#if_anonymous3').is(':checked')) {
                    $('#if_anonymous0').prop('checked',true);
                }
                
                function _this(_status) {
                    if (_status) $('#tr-datos').hide();
                    else $('#tr-datos').show();
        
                    $('#noIdentidad').attr('readonly', _status);
                    $('#nombre').attr('readonly', _status);
     
                    $('#provincia').attr('readonly', _status);
                    $('#municipio').attr('readonly', _status);
                }
 
                _this(true);

                $('#personas').attr('disabled', 'disabled');
                $("#proceso").attr('disabled', 'disabled');
                $("#usuario").attr('disabled', 'disabled');

                function _this_1() {
                    $('#personas').val('');
                    $('#id_persona').val(0);
                    $('#tr-person').hide(); 
                    
                    $('#proceso').val(0); 
                    $('#usuario').val(0);
                    $('#tr-usuario').hide();                     
                }
                
                function _this_2() {
                    $('#personas').attr('disabled', 'disabled');
                    $('#tr-person').hide();                    
                }

                if ($('#if_anonymous0').is(':checked')) {
                    _this_1();
                    _this_2();
                }              

                if ($('#if_anonymous1').is(':checked')) {
                    _this_1();
                    
                    $('#personas').attr('disabled', false);
                    $('#personas').val('');
                    $('#id_persona').val(0);
                    $('#tr-person').show();
                    FAjaxPerson();
                    $('#tr-datos').show();
                } 

                if ($('#if_anonymous2').is(':checked')) {
                    _this_2();
                    
                    $('#tr-usuario').show();
                    $('#proceso').val(0); 
                    $('#usuario').val(0);
                    $("#proceso").attr('disabled', false);
                    $("#usuario").attr('disabled', false);
                    $('#tr-datos').show();
                    
                    FAjaxUser();
                    available_data();
                }  

                if ($('#if_anonymous3').is(':checked')) {
                     _this_1();

                    $('#tr-datos').show();
                     _this(false);
                }
            }
            
            function refresh_person_form() {
                set_person_form();
                
                if ($('#if_anonymous0').is(':checked')) {
                     $('#noIdentidad').val('');
                    $('#nombre').val();

                    $('#organismo').val(0);
                    $('#cargo').val('');  
                    $('#lugar').val('');
                    
                    $('#telefono').val('');
                    $('#movil').val('');
                    $('#email').val('');  
                    
                    $('#lugar').val(''); 
                    $('#direccion').val(''); 
                    
                    $('#provincia').val(0);
                    $('#municipio').val(0);                    
                }

                $('#personas').val('');
                $('#id_persona').val(0);
                $('#proceso').val(0); 
                $('#usuario').val(0);
            }
            
            function set_data(index) {
                if (index > 0) {
                    $('#noIdentidad').val($('#noIdentidad_'+index).val());
                    $('#nombre').val($('#nombre_'+index).val());    
                    $('#cargo').val($('#cargo_'+index).val());

                    $('#organismo').val($('#organismo_'+index).val());
                    $('#lugar').val($('#lugar_'+index).val());
                    $('#provincia').val($('#provincia_'+index).val());
                    $('#municipio').val($('#municipio_'+index).val());

                    $('#direccion').val($('#direccion_'+index).val());
                    $('#movil').val($('#movil_'+index).val());
                    $('#telefono').val($('#telefono_'+index).val());
                    $('#email').val($('#email_'+index).val());
                } else {
                    $('#noIdentidad').val('');
                    $('#nombre').val('');
                    $('#cargo').val('');

                    $('#organismo').val(0);
                    $('#lugar').val('');
                    $('#provincia').val(0);
                    $('#municipio').val(0);

                    $('#direccion').val('');
                    $('#movil').val('');
                    $('#telefono').val('');
                    $('#email').val('');                    
                }    
            }
            
            function form_person(index) {
                $('#if_anonymous0').attr('checked', false);
                $('#if_anonymous1').attr('checked', false);
                $('#if_anonymous2').attr('checked', false);
                $('#if_anonymous3').attr('checked', false);

                set_data(0);
                
                displayFloatingDiv('div-ajax-panel', "DATOS DEL <?= $if_output ? "DESTINATARIO" : "REMITENTE" ?>", 0, 0, 2, 2);
                
                if (index == 0) {
                    ifnew= true;
                    oId= 0;
                    
                    set_person_form();
                } 
                
                if (index > 0) {
                    ifnew= false;
                    oId= index;
                    
                    if ($('#if_anonymous_'+index).val() == 1) 
                        $('#if_anonymous1').prop('checked', true);
                    if ($('#if_anonymous_'+index).val() == 2) 
                        $('#if_anonymous2').prop('checked', true);
                    if ($('#if_anonymous_'+index).val() == 3) 
                        $('#if_anonymous3').prop('checked', true);   
                    
                    set_person_form();

                    function _this() {
                        if ($('#if_anonymous_'+index).val() == 1) {
                            $('#id_persona').val($('#id_persona_'+index).val());
                            FAjaxPerson();
                            $('#personas').val($('#remitente_'+index).val());
                        }
                        if ($('#if_anonymous_'+index).val() == 2) {
                            $('#proceso').val($('#id_proceso_'+index).val());
                            $('#id_usuario').val($('#id_responsable_'+index).val());
                            refresh_ajax_users();
                        } 
                    }

                    _this();
                }       
            }
                       
            function validar_person() {
                <?php if (!$if_output) { ?>
                if ($('#if_anonymous0').is(':checked')) {
                    CloseWindow('div-ajax-panel');
                    alert("Es un documento anónimo. No podrá especificar el remitente");
                    return false; 
                }
                <?php } ?>
                
                 if (!$('#if_anonymous1').is(':checked') && !$('#if_anonymous2').is(':checked') && !$('#if_anonymous3').is(':checked')) {
                    alert("Debe de especificar los datos del <?= $if_output ? "Destinatario" : "Remitente" ?> del documento.");
                    return false;                         
                 }
                if ($('#if_anonymous1').is(':checked') && $('#personas').val() == 0) {
                    $('#personas').focus();
                    alert("Debe selecionar <?= !$if_output ? "Destinatario" : "Remitente" ?> del documento.");
                    return false;                     
                 }
                if ($('#if_anonymous2').is(':checked') && $('#proceso').val() == 0) {
                    $('#person').focus();
                    alert("Debe selecionar la Unidad Organizativa a la que pertenece el <?= !$if_output ? "Destinatario" : "Remitente" ?> del documento.");
                    return false;                     
                }
                if ($('#if_anonymous2').is(':checked') && $('#usuario').val() == 0) {
                    $('#person').focus();
                    alert("Debe selecionar de los usuarios del sistema al <?= $if_output ? "Destinatario" : "Remitente" ?> del documento.");
                    return false;                     
                }
                if ($('#if_anonymous3').is(':checked')) {                
                    if (!Entrada($('#nombre').val())) {
                        $('#nombre').focus();
                        alert('Debe de escribir el nombre del <?=$if_output ? "Destinatario" : "Remitente"?>');
                        return false
                    }
                    if (Entrada($('#noIdentidad').val()) && !AlphaNumeric_abs($('#noIdentidad').val())) {
                        $('#noIdentidad').focus();
                        alert('Numero de Identidad o de Pasaporte del <?=$if_output ? "Destinatario" : "Remitente"?> incorrecto');
                        return false                
                    }
                } 
                
                return true;
            }
            
            var array_personas= [];
                            
            function get_id_persona(persona) {
                for(var row in array_personas) {
                    if (array_personas[row][0].indexOf(persona) != -1) 
                        return array_personas[row][1];
                }
                return -1;
            }
                
            function add_person() {
                if (!validar_person()) 
                    return;

                if (ifnew) {
                    ++numero_person;
                    oId= numero_person;
                    $('#cant_personas').val(numero_person);
                }
                
                var nombre= $('#nombre').val();
                var cargo= $('#cargo').val();
                var noIdentidad= $('#noIdentidad').val();
                var id_organismo=  parseInt($('#organismo').val());
                var provincia= $('#provincia').val();
                var municipio= $('#municipio').val() != null && $('#municipio').val().length > 1 ? $('#municipio').val() : '';
                var telefono= $('#telefono').val();
                var movil= $('#movil').val();
                var email= $('#email').val();
                var lugar= $('#lugar').val();
                var direccion= $('#direccion').val();
                
                var id_persona= 0
                var persona= '';
                var id_responsable= 0;
                var id_proceso= 0;

                var if_anonymous= 0;
                if ($('#if_anonymous1').is(':checked')) 
                    if_anonymous= 1;
                if ($('#if_anonymous2').is(':checked')) 
                    if_anonymous= 2;
                if ($('#if_anonymous3').is(':checked')) 
                    if_anonymous= 3;
                
                if (if_anonymous == 1) {
                    persona= $('#personas').val();
                    id_persona= get_id_persona(persona);
                }
                if (if_anonymous == 2) {
                    id_proceso= $('#proceso').val();
                    id_responsable= $('#usuario').val();
                }
                
                var remitente= '';
                if (nombre.length > 0)
                    remitente+= nombre;
                if (cargo.length)
                    remitente+= ', '+cargo;
                
                var html= ''+
                        '<a href="#" class="btn btn-danger btn-sm" onclick="del_person('+oId+')">'+
                            '<i class="fa fa-trash"></i>Eliminar'+
                        '</a>'+    
                        '<a class="btn btn-warning btn-sm" href="#" onclick="form_person('+oId+')">'+
                            '<i class="fa fa-edit"></i>Editar'+
                        '</a>'+   
                        <?php if (!empty($numero)) { ?>
                        '<a class="btn btn-info btn-sm d-none d-lg-inline-block" href="#" onclick="formPrint('+oId+')">'+
                            '<i class="fa fa-print"></i>Imprimir'+
                        '</a>'+ 
                        <?php } ?>
                        '<label class="text">'+nombre+'</label>'+
                        '<input type="hidden" id="remitente_'+oId+'" name="remitente_'+oId+'" value="'+remitente+'" />'+
                        '<input type="hidden" id="nombre_'+oId+'" name="nombre_'+oId+'" value="'+nombre+'" />'+
                        '<input type="hidden" id="cargo_'+oId+'" name="cargo_'+oId+'" value="'+cargo+'" />'+
                        '<input type="hidden" id="noIdentidad_'+oId+'" name="noIdentidad_'+oId+'" value="'+noIdentidad+'" />'+
                        '<input type="hidden" id="organismo_'+oId+'" name="organismo_'+oId+'" value="'+id_organismo+'" />'+
                        '<input type="hidden" id="provincia_'+oId+'" name="provincia_'+oId+'" value="'+provincia+'" />'+
                        '<input type="hidden" id="municipio_'+oId+'" name="municipio_'+oId+'" value="'+municipio+'" />'+
                        '<input type="hidden" id="telefono_'+oId+'" name="telefono_'+oId+'" value="'+telefono+'" />'+
                        '<input type="hidden" id="movil_'+oId+'" name="movil_'+oId+'" value="'+movil+'" />'+
                        '<input type="hidden" id="email_'+oId+'" name="email_'+oId+'" value="'+email+'" />'+
                        '<input type="hidden" id="lugar_'+oId+'" name="lugar_'+oId+'" value="'+lugar+'" />'+
                        '<input type="hidden" id="direccion_'+oId+'" name="direccion_'+oId+'" value="'+direccion+'" />'+
                        '<input type="hidden" id="if_anonymous_'+oId+'" name="if_anonymous_'+oId+'" value="'+if_anonymous+'"  />'+
                        '<input type="hidden" id="id_persona_'+oId+'" name="id_persona_'+oId+'" value="'+id_persona+'"  />'+
                        '<input type="hidden" id="persona_'+oId+'" name="persona_'+oId+'" value="'+persona+'"  />'+
                        '<input type="hidden" id="id_proceso_'+oId+'" name="id_proceso_'+oId+'" value="'+id_proceso+'"  />'+
                        '<input type="hidden" id="id_responsable_'+oId+'" name="id_responsable_'+oId+'" value="'+id_responsable+'"  />';

                if (ifnew) {
                    index= ++maxIndex;
                    arrayIndex['-'+oId]= index;                   
                    
                    $table.bootstrapTable('insertRow', {
                        index: index,
                        row: {
                            id: oId,
                            nombre: html,
                            cargo:  cargo,   
                            lugar:  lugar,   
                            organismo:  array_organismos[id_organismo],   
                            noIdentidad: noIdentidad
                        }
                    });                    
                }        
  
                if (!ifnew) {
                    index= arrayIndex['-'+oId];
                    $("#cant_person"+oId).val(2);

                    $table.bootstrapTable('updateRow', {
                        index: index,
                        row: {
                            id: oId,
                            nombre: html,
                            cargo:  cargo,   
                            lugar:  lugar,   
                            organismo:  array_organismos[id_organismo],    
                            noIdentidad: noIdentidad
                        }
                    });                    
                } 
                
                CloseWindow('div-ajax-panel');
            } 
            
            function del_person(id) {
                function _this() {
                    $("#tab_persona_" + id).val(0);
                    oId = 0;

                    var ids= new Array();
                    ids.push(id);

                     $table.bootstrapTable('remove', {
                         field: 'id',
                         values: ids
                     });

                     for(var i= id; i <= $('#cant_personas').val(); ++i) {
                         if (arrayIndex['-'+i] == 'undefined') 
                             continue;
                         arrayIndex['-'+i]= arrayIndex['-'+i] ? arrayIndex['-'+i] - 1 : 0;
                         maxIndex= arrayIndex['-'+i];
                     }
                     arrayIndex['-'+id]= 'undefined';       
                }

                if (parseInt($("#ifaccords").val()) == 1) {
                    confirm('Realmente desea eliminar a este <?=$if_output ? "Destinatario" : "Remitente"?>?', function(ok) {
                        if (!ok) return false;
                        else {
                            confirm('Realmente desea eliminar esta temática del Orden del Día?', function(ok) {
                                if (!ok) 
                                    return false;
                                else 
                                    _this();
                            });                
                        }       
                    }); 
                } else {
                    _this();
                }
            } 
            
            function if_immediate() {
                if ($('#if_immediate').is(':checked')) {
                    $('#fecha_fin_plan').val('<?=date('d/m/Y')?>');
                    $('#hora_fin_plan').val('<?=date('h:i A')?>');
                    $('#fecha_fin_plan').prop('readonly', true);
                    $('#hora_fin_plan').prop('readonly', true);
                } else {
                    $('#fecha_fin_plan').prop('readonly', false);
                    $('#hora_fin_plan').prop('readonly', false);                    
                }
            }
            
            function isCodigo(str) {
                var patter1= /^[R][ES]-\d{6}-\d{4}(-\w{2,6})?$/i;
                
                if (!Entrada(str))
                    return true;

                str= trim_str(str);
                if (str != '' && str.match(patter1))
                    return true;
                else
                    return false;				
            }
                        
        </script>
        
        <script type="text/javascript">
            var oId;
            var ifnew;
            
            var $table;
            var row_persons;
            
            var arrayIndex= new Array();
            var maxIndex=-1;
            var index= -1;            
            var numero_person;

            $(document).ready(function () {
                InitDragDrop();
                set_clase();
                
                <?php
                $id = $id_archivo;
                $user_ref_date = $fecha_fin;
                $restrict_prs = array(_TIPO_PROCESO_INTERNO);

                $create_user_btn= true;
                ?>                
                
                $.ajax({
                    data:  {
                            "create_user_btn" : 1,
                            "id_user_restrict" : <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>, 
                            "restrict_prs" : <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                            "use_copy_tusuarios" : <?=$use_copy_tusuarios ? $use_copy_tusuarios : 0?>,
                            "array_usuarios" : <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                            "array_grupos" : <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>
                        },
                    url:   'ajax/usuario_tabs.ajax.php',
                    type:  'post',
                    beforeSend: function () {
                        $("#ajax-tab-users").html("Procesando, espere por favor...");
                    },
                    success:  function (response) {
                        $("#ajax-tab-users").html(response);
                    }
                });  
                
                $('#div_fecha_origen').datepicker({
                   format: 'dd/mm/yyyy',
                   endDate: '<?= date('Y-m-d H:i') ?>'
                });   
                $('#div_fecha_entrega').datepicker({
                   format: 'dd/mm/yyyy'
                });   
                $('#div_fecha_fin_plan').datepicker({
                   format: 'dd/mm/yyyy'
                }); 
                $('#div_hora_entrega').timepicker({
                    minuteStep: 5,
                    showMeridian: true
                });     
                $('#div_hora_fin_plan').timepicker({
                    minuteStep: 5,
                    showMeridian: true
                });                 
                $('#div_hora_entrega').timepicker().on('changeTime.timepicker', function() {
                    $('#hora_entrega').val($(this).val());
                });
                $('#div_hora_fin_plan').timepicker().on('change', function() {
                    $('#hora_fin_plan').val($(this).val());
                });               
                $('#if_immediate').on('click', function(){
                   if_immediate(); 
                });
               
                var availableTags_lugar = [
                    <?php 
                    $i= 0;
                    foreach ($lugares as $row) { 
                        ++$i;
                        if ($i > 1) 
                            echo ",";
                        echo "'{$row}'";
                    }    
                    ?>
                ];
                $("#lugar").autocomplete({
                    source: availableTags_lugar
                });
                
                var availableTags_persona = [
                    <?php 
                    $i= 0;                
                    foreach ($personas as $row) { 
                        ++$i;
                        if ($i > 1) 
                            echo ",";
                        $name= "'{$row['nombre']}";
                        $name.= !empty($row['cargo']) ? ", {$row['cargo']}'" : "'";
                        echo $name;
                    }    
                    ?>
                ];
                
                $("#personas").autocomplete({
                    source: availableTags_persona
                });                

                <?php
                reset($personas);
                foreach ($personas as $row) {
                    $name= "'{$row['nombre']}";
                    $name.= !empty($row['cargo']) ? ", {$row['cargo']}'" : "'";                
                ?>
                    array_personas.push([<?=$name?>, <?=$row['_id']?>]);
                <?php } ?>
                    
                InitUploaderFile('file_doc', "<?= urlencode(_SERVER_DIRIGER)?>", <?=$config->maxfilesize?>);

                $('#provincia').change(function() {
                    if ($('#if_anonymous').is(':checked')) $(this).val(0);
                    
                    if ($(this).val() == 0) {
                        $('#municipio').empty();
                        return;
                    }
                    
                    FAjaxMcpo();
                });
          
                $("#personas" ).autocomplete({
                    close: function(event, ui ) {
                        var id_persona= get_id_persona($('#personas').val());
                        $('#id_persona').val(id_persona);
                        FAjaxPerson(); 
                    }
                });

                $('#usuario').change(function(){
                   FAjaxUser(); 
                });
                
                set_person_form();
                
                refreshp(1);
                /*
                refreshp(2);
                */
               
               <?php if ($action == 'update' && !empty($responsable_init)) { ?>
                $('#responsable').change(function() {
                    var text;
                    text= "Debe quitar al usuario <?=$responsable_init?> del destino si no deseas que siga siendo destinatario";
                    alert(text);
                });
               <?php } ?>
               
                $table= $("#table-persons");
                $table.bootstrapTable('append', row_persons); 
                
                <?php if (!empty($id_archivo)) { ?>
                    $('ul.nav.nav-tabs li').removeClass('active');
                    $(".tabcontent").hide();
                    $('#nav-tab3').addClass('active');
                    $('#tab3').show();
                <?php } ?> 
                
                tinymce.init({
                    selector: '#descripcion',
                    theme: 'modern',
                    height: 200,
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
                    $('#descripcion').val(<?= json_encode($descripcion)?>);
                } catch(e) {;}                 
                
                tinymce.init({
                    selector: '#indicaciones',
                    theme: 'modern',
                    height: 200,
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
                    $('#indicaciones').val(<?= json_encode($indicaciones)?>);
                } catch(e) {;} 
                
                
                <?php if (!is_null($error)) { ?>
                alert("<?=str_replace("\n"," ", addslashes($error))?>");
                <?php } ?>                
            }); 
        </script>   
    </head>

    <body>
        <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <div class="app-body form">
            <div class="container">
                <div class="card card-primary">
                    <div class="card-header"> 
                        REGISTRO DE <strong class="text text-danger"><?= $if_output ? "SALIDA" : "ENTRADA" ?></strong>
                        <strong style="margin-left: 20px; font-size: 1.4; color: #fbd850"><?=$nombre_prs?></strong>
                    </div>

                    <div class="card-body">

                        <nav style="margin-bottom: 10px;">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item" id="nav-tab1"><a class="nav-link" href="tab1">Registro</a></li>
                                <li class="nav-item" id="nav-tab2"><a class="nav-link" href="tab2">Contenido</a></li>
                                <li class="nav-item" id="nav-tab3"><a class="nav-link" href="tab3"><?= $if_output ? "Destino" : "Procedencia" ?></a></li>
                                <li class="nav-item" id="nav-tab4"><a class="nav-link" href="tab4"><?= !$if_output ? "Destino" : "Procedencia" ?></a></li>
                                <li class="nav-item" id="nav-tab5"><a class="nav-link" href="tab5">Indicaciones y Responsable</a></li>
                            </ul>
                        </nav>

                        <form class="form-horizontal" name="frecord" id="frecord" action="javascript:validar()" method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="exect" name="exect" value="<?=$action?>" /> 
                            <input type="hidden" id="menu" name="menu" value="frecord" />
                            <input type="hidden" id="id" name="id" value="<?=$id_archivo?>" />

                            <input type="hidden" id="year" name="year" value="<?=$year?>" />
                            <input type="hidden" id="id_prov" name="id_prov" value="<?=$id_prov?>" />
                            <input type="hidden" id="id_mcpo" name="id_mcpo" value="<?=$id_mcpo?>" />   

                            <input type="hidden" id="id_usuario" value="0" />                        

                            <input type="hidden" id="if_output" name="if_output" value="<?= $if_output ?>" />

                            <input type="hidden" id="id_documento" name="id_documento" value="<?=$id_documento?>" />
                            <input type="hidden" id="id_documento_code" name="id_documento_code" value="<?=$id_documento_code?>" />

                            <input type="hidden" id="id_evento" name="id_evento" value="<?=$id_evento?>" />
                            <input type="hidden" id="id_evento_code" name="id_evento_code" value="<?=$id_evento_code?>" />

                            <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
                            <input type="hidden" id="id_proceso_code" name="id_proceso_code" value="<?=$id_proceso_code?>" />
                            
                            <input type="hidden" id="list_senders" name="list_senders" value="<?=$list_senders?>" />
                            
                            <input type="hidden" id="id_persona" name="id_persona" value="0" />

                            <!-- Generales -->
                            <div id="tab1" class="tabcontent">
                                <div class="form-group row">
                                    <div class="row col-4">
                                        <label class="col-form-label col-4">Referencia:</label>
                                        <div class="input-group col-8">
                                            <!--
                                            <input type="hidden" id="numero" name="numero" value="" />
                                            
                                            <input type="text" id="_numero" class="form-control" readonly="yes" value="" />
                                            -->
                                            <input type="hidden" id="numero" name="numero" value="<?=$numero?>" />
                                            <input type="text" id="_numero" class="form-control" readonly="yes" value="<?=$codigo?>" />
                                            
                                        </div>
                                    </div> 

                                    <div class="row col-8">
                                        <label class="col-form-label col-4">Tipo de Documento:</label>
                                        <div class="col-7">
                                            <select class="form-control" id="tipo" name="tipo" required >
                                                <option value="0">... </option>
                                                <?php 
                                                foreach ($Tarray_tipo_documento as $key => $value) { 
                                                    if (empty($key))
                                                        continue;
                                                ?>
                                                <option value="<?= $key ?>" <?php if ($key == $tipo) {?>selected="selected"<?php } ?>><?= utf8_encode($value) ?></option>
                                                <?php } ?>        
                                            </select>   
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="row col-5">
                                        <label class="col-form-label col-5">Fecha del Documento:</label>
                                        <div class="col-7">
                                            <div id="div_fecha_origen" class="input-group date" data-date-language="es">
                                                <input type="datetime" class="form-control" id="fecha_origen" name="fecha_origen" value="<?=date('d/m/Y', strtotime($fecha_origen))?>" readonly />
                                                <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row col-7">
                                        <label class="col-form-label col-2">Fecha de <?=$if_output ? 'Entrega' : 'Recepción'?>:</label>

                                        <div class=" col-5">
                                            <div id="div_fecha_entrega" class="input-group date" data-date-language="es">
                                                <input type="datetime" class="form-control" id="fecha_entrega" name="fecha_entrega" value="<?=date('d/m/Y', strtotime($fecha_entrega))?>" />
                                                <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="input-group bootstrap-timepicker timepicker" id='div_hora_entrega'>
                                                <input  type="text" id="hora_entrega" name="hora_entrega" class="form-control" value="<?=date('h:i A', strtotime($fecha_entrega))?>" />
                                                <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                                            </div>	      				
                                        </div> 
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="row col-6">
                                        <label class="col-form-label col-4">
                                            Prioridad:
                                        </label>
                                        <div class="col-7">
                                            <select id="prioridad" name="prioridad" class="form-control">
                                                <option value="0">... </option>
                                                <?php for ($i=1; $i <= _MAX_PRIORIDAD; $i++) { ?>
                                                <option value="<?=$i?>" <?php if ($i == (int)$prioridad) echo "selected='selected'"?>><?=$Tarray_prioridad[$i]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>   
                                    
                                    <div class="row col-6">
                                        <label class="col-form-label col-4">
                                            Clasificación:
                                        </label>
                                        <div class="col-7">
                                            <select id="clase" name="clase" class="form-control" onchange="set_clase()">
                                                <option value="0">... </option>
                                                <?php for ($i=1; $i <= _MAX_DOCUMENTO_CLASS; $i++) { ?>
                                                    <option value="<?=$i?>" <?php if ($i == (int)$clase) echo "selected='selected'"?>><?=$Tarray_clase_archive[$i]?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>                                      
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-form-label col-4">Palabras claves (separadas por comas):</label>
                                    <div class="col-7">
                                        <textarea class="form-control" id="keywords" name="keywords" rows="2"><?=$keywords?></textarea>
                                    </div> 
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-1">
                                        Archivo: 
                                    </label>
                                    <div class="col-md-7 col-lg-7">
                                        <div id="file_doc" class="panel-file">
                                            <div class="img">
                                                <?php
                                                if (!empty($id_documento)) { 
                                                    $obj_doc= new Tdocumento($clink);
                                                    $obj_doc->SetIdDocumento($id_documento);
                                                    $obj_doc->Set();

                                                    $array= get_file_type($obj_doc->filename);
                                                    $array['img']= str_replace(".ico", ".png", $array['img']);
                                                ?>
                                                <img src="<?=_SERVER_DIRIGER?>libs/upload/img/<?=$array['img']?>" alt="<?=$array['type']?>" title="<?=$array['type']?>" />
                                                <?php } ?>
                                            </div>
                                            
                                            <input type="hidden" id="file_doc-upload-init" name="file_doc-upload-init" value="<?=$obj->url?>" />
                                            
                                           <div class="title">Click para cargar IMAGE ...</div> 
                                           <input type="file" id="file_doc-upload" name="file_doc-upload" />
                                           <div class="close img-thumbnail" onclick="closeFile('file_doc');">X</div>
                                       </div> 
                                    </div>
                                    
                                    <div class="col-2">
                                        <button type="button" id="file_doc-btn-trash" class="btn btn-default upload-trash" title="Eliminar Adjunto">
                                            <i class="fa fa-trash fa-2x"></i>
                                        </button>                                           
                                    </div>  
                                    
                                    <?php if (!empty($id_documento)) { ?>
                                    <div class="col-md-4 col-sm-4">
                                        <a href="<?=_SERVER_DIRIGER?>php/download.interface.php?id=<?=$id_documento?>" target="_blank">
                                            <div class="alert alert-success">
                                                <img src="<?=_SERVER_DIRIGER?>libs/upload/img/<?=$array['img']?>" alt="<?=$array['type']?>" title="<?=$array['type']?>" />
                                                <?=$obj_doc->filename?>                                            
                                            </div>
                                        </a> 
                                    </div>
                                    <?php } ?>
                                </div>  
                                                           
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="sendmail" id="sendmail" value="1" <?php echo empty($sendmail) ? '&nbsp;' : "checked='checked'"; ?>  />
                                        Enviar a los destinatarios aviso y documento adjunto via <strong>correo electrónico</strong>.
                                    </label>
                                </div>                                 
                            </div><!-- tab 1 Generales -->

                            <!-- tab 2 Contenido -->
                            <div id="tab2" class="tabcontent">
                                <div class="form-group row col-12">
                                    <label class="col-form-label col-4">
                                        Código anterior (Procedencia):
                                    </label>
                                    <div class="col-4">
                                        <input type="text" class="form-control" id="antecedentes" name="antecedentes" maxlength="20" value="<?=$antecedentes?>" />
                                    </div>
                                </div>                                
                                
                                <div class="form-group row col-12">
                                    <label class="col-form-label">Contenido o Asunto:</label>
                                    <textarea class="form-control" rows="6" id="descripcion" name="descripcion" ><?=$descripcion?></textarea>
                                </div>
                            </div>
                            <!-- tab 2 Contenido -->

                            <!-- tab 3 Remitente/Destinatario -->
                            <div id="tab3" class="tabcontent">
                                <div id="toolbar">
                                    <button type="button" class="btn btn-success" onclick="form_person(0)">
                                        <i class="fa fa-plus"></i>Agregar
                                    </button>
                                </div>

                                <?php
                                $i= 0;
                                while ($row= $clink->fetch_array($result_ref)) {
                                    ++$i;
                                ?>
                                    <input type="hidden" id="tab_persona_init_<?=$i?>" name="tab_persona_init_<?=$i?>" value="<?=$row['_id']?>"  />
                                    <input type="hidden" id="tab_persona_<?=$i?>" name="tab_persona_<?=$i?>" value="1" />
                                <?php } ?>

                                <script type="text/javascript">
                                    row_persons= [
                                        <?php
                                        $i= 0;
                                        $clink->data_seek($result_ref);
                                        while ($row= $clink->fetch_array($result_ref)) {
                                            $remitente= !empty($row['nombre']) ? textparse($row['nombre']) : null;
                                            $remitente.= !empty($row['cargo']) ? ", ".textparse($row['cargo']) : null;
                                            
                                            ++$i;
                                            if ($i > 1) 
                                                echo ",";
                                        ?>
                                        {
                                            id: <?=$i?>,

                                            nombre: ''+
                                                '<a href="#" class="btn btn-danger btn-sm" onclick="del_person(<?=$i?>)">'+
                                                    '<i class="fa fa-trash"></i>Eliminar'+
                                                '</a>'+    
                                                '<a class="btn btn-warning btn-sm" href="#" onclick="form_person(<?=$i?>)">'+
                                                    '<i class="fa fa-edit"></i>Editar'+
                                                '</a>'+ 
                                                <?php if (!empty($numero)) { ?>
                                                '<a class="btn btn-info btn-sm d-none d-lg-inline-block" href="#" onclick="formPrint(<?=$i?>)">'+
                                                    '<i class="fa fa-print"></i>Imprimir'+
                                                '</a>'+
                                                <?php } ?>
                                                '<label class="text"><?=$row['nombre']?></label>'+
                                                '<input type="hidden" id="remitente_<?=$i?>" name="remitente_<?=$i?>" value="<?=$remitente?>" />'+
                                                '<input type="hidden" id="nombre_<?=$i?>" name="nombre_<?=$i?>" value="<?=stripslashes($row['nombre'])?>" />'+
                                                '<input type="hidden" id="cargo_<?=$i?>" name="cargo_<?=$i?>" value="<?=stripslashes($row['cargo'])?>" />'+
                                                '<input type="hidden" id="noIdentidad_<?=$i?>" name="noIdentidad_<?=$i?>" value="<?=$row['noIdentidad']?>" />'+
                                                '<input type="hidden" id="organismo_<?=$i?>" name="organismo_<?=$i?>" value="<?=$row['id_organismo']?>" />'+
                                                '<input type="hidden" id="provincia_<?=$i?>" name="provincia_<?=$i?>" value="<?=$row['provincia']?>" />'+
                                                '<input type="hidden" id="municipio_<?=$i?>" name="municipio_<?=$i?>" value="<?=$row['municipio']?>" />'+
                                                '<input type="hidden" id="telefono_<?=$i?>" name="telefono_<?=$i?>" value="<?=$row['telefono']?>" />'+
                                                '<input type="hidden" id="movil_<?=$i?>" name="movil_<?=$i?>" value="<?=$row['movil']?>" />'+
                                                '<input type="hidden" id="email_<?=$i?>" name="email_<?=$i?>" value="<?=$row['email']?>" />'+
                                                '<input type="hidden" id="lugar_<?=$i?>" name="lugar_<?=$i?>" value="<?=stripslashes($row['lugar'])?>" />'+
                                                '<input type="hidden" id="direccion_<?=$i?>" name="direccion_<?=$i?>" value="<?=stripslashes($row['direccion'])?>" />'+
                                                '<input type="hidden" id="if_anonymous_<?=$i?>" name="if_anonymous_<?=$i?>" value="<?=$row['if_anonymous'] != 3 ? $row['if_anonymous'] : 1 ?>"  />'+
                                                '<input type="hidden" id="id_persona_<?=$i?>" name="id_persona_<?=$i?>" value="<?=$row['id_persona']?>"  />'+
                                                '<input type="hidden" id="persona_<?=$i?>" name="persona_<?=$i?>" value=""  />'+
                                                '<input type="hidden" id="id_proceso_<?=$i?>" name="id_proceso_<?=$i?>" value="<?=$row['id_proceso']?>"  />'+
                                                '<input type="hidden" id="id_responsable_<?=$i?>" name="id_responsable_<?=$i?>" value="<?=$row['id_responsable']?>"  />'+
                                                '',

                                            cargo:  '<?=stripslashes($row['cargo'])?>',   
                                            lugar:  '<?=stripslashes($row['lugar'])?>',   
                                            organismo:  <?=!empty($row['id_organismo']) ? "array_organismos[{$row['id_organismo']}]" : "''"?>,   
                                            noIdentidad:  '<?=$row['noIdentidad']?>'
                                        }         
                                        <?php } ?>
                                    ];
                                </script>

                                <table id="table-persons" class="table table-hover table-striped" 
                                    data-toggle="table"
                                    data-toolbar="#toolbar"
                                    data-height="300"
                                    data-search="true"
                                    data-unique-id="id"
                                    data-show-columns="true"> 
                                    <thead>
                                        <tr>
                                            <th data-field="id">No.</th>
                                            <th data-field="nombre">Nombre y Apellidos</th>
                                            <th data-field="cargo">Cargo</th>
                                            <th data-field="lugar">Lugar</th>
                                            <th data-field="organismo">Organismo</th>
                                            <th data-field="noIdentidad">No. Identidad</th>
                                        </tr>
                                    </thead>
                                </table>
                                
                                <hr/>

                                <script type="text/javascript">
                                    maxIndex= <?= $i-1 ?>;
                                    numero_person= <?=$i?>;

                                    <?php 
                                    $k= 0;
                                    for ($j= 1; $j <= $i; ++$j) { 
                                    ?>
                                        arrayIndex['-'+<?=$j?>]= <?=$k++?>;
                                    <?php } ?>  
                                </script>  

                                <input type="hidden" id="cant_personas" name="cant_personas" value="<?=$i?>" />
                            </div>
                            <!-- tab 3 Remitente/Destinatario -->


                            <!-- Participantes -->
                            <div class="tabcontent" id="tab4">
                                <div id="ajax-tab-users">

                                </div>
                            </div> <!-- tab4 Participantes-->

                            <!-- indicacion -->
                            <div id="tab5" class="tabcontent">
                               <!-- Origen -->  
                                <div class="row col-12">
                                    <label class="checkbox text">
                                        <input type="checkbox" id="toshow" name="toshow" <?=$toshow ? "checked='checked'" : ""?> value="1" />
                                       Agregar a los <strong>Planes de Trabajo Individuales</strong> de los destinatarios. Se puede gestionar la indicación como una tarea del Plan.
                                   </label>                                            
                                </div>                 

                                <div class="form-group row">
                                    <div class="row col-12">

                                        <label class="col-form-label col-2">Fecha de cumplimiento:</label>

                                        <div class="col-3">
                                            <div id="div_fecha_fin_plan" class="input-group date" data-date-language="es">
                                                <input type="datetime" class="form-control" id="fecha_fin_plan" name="fecha_fin_plan" value="<?= !empty($fecha_fin_plan) ? date('d/m/Y', strtotime($fecha_fin_plan)) : null?>" readonly />
                                                <span class="input-group-text"><span class="fa fa-calendar"></span></span> 
                                            </div>
                                        </div> 
                                        <div class="col-3">
                                            <div class="input-group bootstrap-timepicker timepicker" id='div_hora_fin_plan'>
                                                <input  type="text" id="hora_fin_plan" name="hora_fin_plan" class="form-control" value="<?=!empty($fecha_fin_plan) ? date('h:i A', strtotime($fecha_entrega)) : null?>" />
                                                <span class="input-group-text"><i class="fa fa-calendar-times-o"></i></span>
                                            </div>	      				
                                        </div> 

                                        <div class="col-4">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" id="if_immediate" name="if_immediate" value="1" <?php if ($if_immediate) echo "checked='checked'"?> />
                                                    Para su cumplimiento inmediato
                                                </label>
                                            </div>                                        
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="row col-12">
                                        <label class="col-form-label col-2">Responsable (seguimiento)</label>

                                        <div class="col-9">
                                            <select class="form-control" id="responsable" name="responsable">
                                                <option value="0">Seleccione ... </option>

                                                <?php
                                                $obj_user= new Tusuario($clink);
                                                $result= $obj_user->listar();
                                                while ($row= $clink->fetch_array($result)) {
                                                    if (empty($row['acc_archive']) && !boolean($row['nivel_archive4'])) 
                                                        continue;
                                                ?>
                                                    <option value="<?=$row['_id']?>" <?php if ($row['_id'] == $id_responsable) echo "selected='selected'"?>>
                                                        <?php
                                                        $name= stripslashes($row['nombre']);
                                                        if (!empty($row['cargo']))
                                                            $name.= ", {$row['cargo']}";
                                                        echo $name;
                                                        ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>                                      
                                    </div>
                                </div>    

                                <div class="form-group row container-fluid">
                                    <textarea class="form-control" id="indicaciones" name="indicaciones"><?=$indicaciones?></textarea> 
                                </div>                            
                            </div> <!-- tab5 indicacion -->

                            <div class="btn-block btn-app">
                                <?php if ($action == 'add' || $action == 'update') { ?>
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                                <?php } ?> 
                                <button class="btn btn-warning" type="button" onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                                <!--
                                <?php if (!$if_output) { ?>
                                    <button class="btn btn-info" type="button" onclick="formPrint(0)">
                                        <i class="fa fa-print"></i>
                                       Resumen
                                   </button>  
                                <?php } ?>
                                -->
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>        
        
 
        <!-- div-ajax -->
        <div id="div-ajax-panel" class="card card-primary ajax-panel win-board" data-bind="draganddrop">
            <div class="card-header">
                <div class="row">
                    <div class="panel-title ajax-title col-11 m-0 win-drag ">DATOS DE CONTACTO</div>
                    <div class="col-1 m-0">
                        <div class="close">
                            <a href="#" onclick="CloseWindow('div-ajax-panel')">
                                <i class="fa fa-close"></i>
                            </a>                             
                        </div>
                    </div>                      
                </div>                                     
            </div>

            <div class="card-body">
                <div class="form-horizontal">
                    <div class="row">
                        <div class="col-md-4 col-ms-4">
                               <?php if (!$if_output) { ?>
                                   <div class="checkbox">
                                       <label>
                                           <input type="radio" value="0" id="if_anonymous0" name="if_anonymous" onclick="refresh_person_form()">
                                           El remitente Anónimo
                                       </label>
                                   </div>
                               <?php } else { ?>
                               <input type="hidden" id="if_anonymous0" value="0" />
                               <?php } ?>                            

                               <div class="checkbox">
                                   <label>
                                       <input type="radio" value="1" id="if_anonymous1" name="if_anonymous" onclick="refresh_person_form()">
                                       <?=!$if_output ? 'Remitente' : 'Destinatario'?> ya registrado por el sistema
                                   </label>
                               </div> 

                               <div class="checkbox">
                                   <label>
                                       <input type="radio" value="2" id="if_anonymous2" name="if_anonymous" onclick="refresh_person_form()">
                                       El <?=!$if_output ? 'remitente' : 'destinatario'?> es un usuario del sistema
                                   </label>
                               </div>        
                        </div> 

                        <div class="col-md-8 col-ms-8">
                            <!-- remitente -->
                            <div id="tr-person" class="form-group row">    
                                <label class="col-form-label col-3"><?= $if_output ? "Destinatario" : "Remitente" ?>:</label>
                                <div class="col-9">
                                    <input type="text" id="personas" name="personas" class="form-control ui-autocomplete-input" autocomplete="no" />
                                </div>
                            </div> 

                            <!-- Procesos -->
                            <div id="tr-usuario">    
                                <div class="form-group row">
                                    <label class="col-form-label col-2">Unidad Organizativa:</label>                                
                                    <div class="col-10">
                                        <?php
                                        $top_list_option = "seleccione........";
                                        $id_list_prs = null;
                                        $order_list_prs = 'eq_asc_desc';
                                        $reject_connected = false;
                                        $id_select_prs = !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];
                                        $show_only_connected = false;
                                        $in_building = ($action == 'add' || $action == 'update') ? true : false;

                                        $id_select_prs = $id_proceso;
                                        require_once "../../../form/inc/_select_prs.inc.php";
                                        ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-2">
                                        Usuario:
                                    </label>
                                    <div class="col-10">
                                        <div class="ajax-select" id="ajax-users">
                                            <select name="usuario" id="usuario" class="form-control">
                                                <option value=0 <?php if (empty($id_usuario)) echo "selected='selected'" ?>>Selecione ... </option>
                                           </select>
                                        </div>
                                    </div>
                                </div>
                            </div>    
                        </div>                            
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="radio" value="3" id="if_anonymous3" name="if_anonymous" onclick="refresh_person_form()">
                            Es un nuevo <?= $if_output ? "Destinatario" : "Remitente" ?>. Completar los <strong>Datos del <?= $if_output ? "Destinatario" : "Remitente" ?></strong> para registrarlo en el sisistema.
                        </label>
                    </div>  


                    <!-- Destino -->
                    <div id="tr-datos" class="row col-12">
                        <legend style="margin-top: 10px;"> Datos del <?= $if_output ? "Destinatario" : "Remitente" ?></legend>                    
                        <div class="form-group row col-12">
                            <label class="col-form-label col-2">No. Identidad:</label>
                            <div class="col-3">
                                <input type="text" class="form-control" id="noIdentidad" name="noIdentidad" maxlength="11" />  
                            </div>

                            <label class="col-form-label col-2">Nombre y Apellidos:</label>
                            <div class="col-5">
                                <input type="text" class="form-control" id="nombre" name="nombre" />  
                            </div>                            
                        </div>    

                        <div class="form-group row col-12">
                            <label class="col-form-label col-2">Cargo:</label>
                            <div class="col-8">
                                <input type="text" class="form-control" id="cargo" name="cargo" />  
                            </div>
                        </div>

                        <div class="form-group row col-12"> 
                            <label class="col-form-label col-2">Organismo:</label>
                            <div class="col-4">
                                <select class="form-control" id="organismo" name="organismo" >
                                    <option value="0">Seleccione ... </option>
                                    <?php 
                                    $clink->data_seek($result_org);
                                    while ($row= $clink->fetch_array($result_org)) { 
                                    ?>
                                    <option value="<?=$row['id']?>"><?=$row['nombre']?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <label class="col-form-label col-2">Lugar:</label>
                            <div class="col-4">
                                <input type="text" id="lugar" name="lugar" class="form-control ui-autocomplete-input" autocomplete="no" />
                            </div>
                        </div>

                        <div class="form-group row col-12">
                            <div class="row col-6">
                                <label class="col-form-label col-2">Provincia:</label>
                                <div class="col-9">
                                    <select class="form-control" id="provincia" name="provincia">
                                        <option value="0"></option>
                                        <?php foreach ($Tarray_provincias as $key => $value) { ?>
                                            <option value="<?= $key ?>"><?= utf8_encode($value) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>     
                            </div>

                            <div class="row col-6">
                                <label class="col-form-label col-2">Municipio:</label>
                                <div id="ajax-municipio" class="ajax-select col-10">
                                    <select class="form-control" id="municipio" name="municipio">
                                    </select>
                                </div>     
                            </div>
                        </div>

                        <div class="form-group row col-12">
                            <div class="row col-3">
                                <label class="col-form-label col-4">Teléfono:</label>
                                <div class="col-8">
                                    <input type="tel" class="form-control" id="telefono" name="telefono" />
                                </div>
                            </div>

                            <div class="row col-3">
                                <label class="col-form-label col-3">Movil:</label>
                                <div class="col-9">
                                    <input type="tel" class="form-control" id="movil" name="movil" />
                                </div>
                            </div>

                            <div class="row col-6">
                                <label class="col-form-label col-2">Correo:</label>
                                <div class="col-10">
                                    <input type="email" class="form-control" id="email" name="email" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group row col-12">
                            <label class="col-form-label col-1">Dirección:</label>
                            <div class="col-11">
                                <input type="text" class="form-control" id="direccion" name="direccion"> 
                            </div>
                        </div>                        
                    </div>
                </div>

                <div class="btn-block btn-app">
                    <button class="btn btn-primary" type="button" onclick="add_person()">Aceptar</button>
                    <button class="btn btn-warning" type="button" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
                </div>                    
            </div> <!-- panel-body -->
        </div> <!-- div-ajax -->            

            
        <!-- div-ajax-print -->
        <div id="div-ajax-print" class="card card-primary" data-bind="draganddrop">
            <div class="card-header">
                <div class="row win-drag">
                    <div class="panel-title ajax-title col-11 win-drag ">DATOS DE CONTACTO</div>
                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href="#" onclick="HideContent('div-ajax-print')">
                                <i class="fa fa-close"></i>
                            </a>                             
                        </div>
                    </div>                      
                </div>                 
            </div>
            <div class="card-body">
                <div class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Origen:
                        </label>
                        <div class="col-md-10 col-lg-10">
                            <textarea id="sender" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Destino:
                        </label>
                        <div class="col-md-10">
                            <textarea id="target" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Notas:
                        </label>
                        <div class="col-md-10 col-lg-10">
                           <textarea id="nota" name="nota" class="form-control" rows="3"></textarea>
                       </div>                   
                    </div>

                    <div class="btn-block btn-app">
                        <button type="button" class="btn btn-primary d-none d-lg-block" onclick="imprimir_waybill()">Imprimir</button>
                        <button type="button" class="btn btn-warning" onclick="HideContent('div-ajax-print')">Cerrar</button>
                    </div>                     
                </div>  
            </div>
        </div> <!-- div-ajax-print -->    

        <script type="text/javascript">
            var array_usuarios= Array();
            <?php
            $obj_user= new Tusuario($clink);
            $result= $obj_user->listar();
            
            while ($row= $clink->fetch_array($result)) {
                $nombre= textparse($row['nombre'], true);
                if (!empty($row['cargo'])) 
                    $nombre.= ", ". textparse($row['cargo'], true);
            ?>
                array_usuarios[<?=$row['_id']?>]= '<?=$nombre?>';
            <?php } ?>
        </script>
            
        
    </body>
</html>