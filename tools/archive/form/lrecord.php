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
$_SESSION['trace_time']= 'no';

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";
require_once "../../../php/class/escenario.class.php";
require_once "../../../php/class/proceso_item.class.php";
require_once "../../../php/class/usuario.class.php";
require_once "../../../php/class/document.class.php";

require_once "../php/class/organismo.class.php";
require_once "../php/class/persona.class.php";
require_once "../php/class/archivo.class.php"; 
require_once "../php/class/ref_archivo.class.php";

$acc= $_SESSION['acc_archive'];
if ($acc == _ACCESO_ALTA 
        || ($acc == _ACCESO_BAJA && $_SESSION['nivel_archive3'] == _USER_REGISTRO_ARCH)
        || ($acc == _ACCESO_MEDIA && $_SESSION['nivel_archive2'] == _USER_REGISTRO_ARCH))
    $action= "add";
else
    $action= "list";

$date_init= !empty($_GET['date_init']) ? urldecode($_GET['date_init']) : null;
$date_end= !empty($_GET['date_end']) ? urldecode($_GET['date_end']) : null;

$id_organismo= !empty($_GET['id_organismo']) ? $_GET['id_organismo'] : null; 
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null; 
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
$id_persona= !empty($_GET['id_persona']) ? $_GET['id_persona'] : null; 
$keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null; 
$persona_keywords= !empty($_GET['persona_keywords']) ? urldecode($_GET['persona_keywords']) : null; 
$numero_keywords= !empty($_GET['numero_keywords']) ? urldecode($_GET['numero_keywords']) : null; 
$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : null;

if ((empty($date_init) || empty($date_end)) && empty($numero_keywords)) {
    $date_init= date('d/m/Y');
    $date_end= $date_init;
}
if ((empty($date_init) || empty($date_end)) && !empty($numero_keywords)) {
    $date_init= '01/01/'.date('Y');
    $date_end= '31/12/'.date('Y');
}

$year= !empty($date_init) ? date('Y', strtotime(date2odbc($date_init))) : date('Y');
$month= $date_init ? date('m', strtotime($date_init)) : 0;
$day= $date_init ? date('d', strtotime($date_init)) : 0;

$if_output= $_GET['if_output'];
if (!is_null($if_output) && empty($if_output)) 
    $_if_output= null;
if ($if_output == 1) 
    $_if_output= 0;
if ($if_output == 2) 
    $_if_output= 1;

if (!empty($numero_keywords)) {
    $keywords= null;
    $persona_keywords= null;
}

$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

$obj_ref= new Tref_archivo($clink);
$obj_user= new Tusuario($clink);

$obj_pers= new Tpersona($clink);
$lugares= $obj_pers->listar_lugares();

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso)) {
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $nombre_prs= $obj_prs->GetNombre();
    $tipo_prs= $Ttipo_proceso_array[(int)$obj_prs->GetTipo()];
    $nombre_prs.= ", $tipo_prs";
}

$url_page= "../form/lrecord.php?signal=$signal&action=$action&menu=evento&id_proceso=$id_proceso&year=$year&month=$month&day=$day";
$url_page.= "&exect=$action&date_init=". urlencode($date_init)."&date_end=". urlencode($date_end)."&numero_keywords=$numero_keywords";
$url_page.= "&persona_keywords=$persona_keywords&keywords=$keywords&lugar=$lugar&id_organismo=$id_organismo&id_proceso=$id_proceso";

set_page($url_page);
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>

        <link rel="stylesheet" href="../../../css/menu.css" />

        <link rel="stylesheet" href="../../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>  

        <link href="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>    

        <link href="../../../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">  
        <script type="text/javascript" src="../../../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>  
        
        <link rel="stylesheet" href="../../../libs/btn-toolbar/btn-toolbar.css" />
        <script type="text/javascript" src="../../../libs/btn-toolbar/btn-toolbar.js"></script>          
       
        <link rel="stylesheet" type="text/css" href="../../../css/general.css?version=">
        <link rel="stylesheet" type="text/css" href="../../../css/table.css?version=">
        <link rel="stylesheet" type="text/css" media="screen" href="../../../css/alarm.css?" />

        <link href="../../../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version="></script>        
        
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js?version="></script> 
        
        <script type="text/javascript" charset="utf-8" src="../../../js/ajax_core.js"></script>
        
        <script type="text/javascript" charset="utf-8" src="../../../js/form.js?version="></script>

        <script type="text/javascript">
            var _id_print;
            
            function valid_filter() {
                if ((!Entrada($('#date_init').val()) && !Entrada($('#date_init').val())) && !Entrada($('#keywords').val())) {
                    form_filter(1);
                }
                return true;
            }
            
            function refreshp() {
                if (!valid_filter()) 
                    return;

                var date_init= encodeURI($('#date_init').val());
                var date_end= encodeURI($('#date_end').val());
                var keywords= $('#keywords').val() ? encodeURI($('#keywords').val()) : '';
                var persona_keywords= $('#persona_keywords').val() ? encodeURI($('#persona_keywords').val()) : '';
                var numero_keywords= $('#numero_keywords').val() ? encodeURI($('#numero_keywords').val()) : '';
                var lugar= $('#lugar').val() ? encodeURI($('#lugar').val()) : '';
                var id_responsable= $('#responsable').val();
                var id_proceso= $('#id_proceso').val();
                var id_organismo= $('#organismo').val();
                var id_persona= $('#persona').val();
                var action= $('#exect').val();
                var init_row_temporary= $('#init_row_temporary').val();
                
                var if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2; 
                
                var url= 'lrecord.php?date_init='+date_init+'&date_end='+date_end+'&keywords='+keywords+'&persona_keywords='+persona_keywords;
                url+= '&id_responsable='+id_responsable+'&id_organismo='+id_organismo+'&id_proceso='+id_proceso+'&numero_keywords='+numero_keywords;
                url+= '&id_persona='+id_persona+'&action='+action+'&if_output='+if_output+'&lugar='+lugar+'&init_row_temporary='+init_row_temporary;
                url+= '&id_proceso='+id_proceso;
                self.location.href= url;                
            }
            
            function imprimir() {
                var date_init= encodeURI($('#date_init').val());
                var date_end= encodeURI($('#date_end').val());
                var keywords= $('#keywords').val() ? encodeURI($('#keywords').val()) : '';
                var persona_keywords= $('#persona_keywords').val() ? encodeURI($('#persona_keywords').val()) : '';
                var numero_keywords= $('#numero_keywords').val() ? encodeURI($('#numero_keywords').val()) : '';
                var lugar= $('#lugar').val() ? encodeURI($('#lugar').val()) : '';
                var id_responsable= $('#responsable').val();
                var id_proceso= $('#id_proceso').val();
                var id_organismo= $('#organismo').val();
                var id_persona= $('#persona').val();
                
                var if_output= 0;
                if ($('#if_output0').is(':checked')) 
                    if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2;
                
                var url= '../print/lrecord.php?date_init='+date_init+'&date_end='+date_end+'&keywords='+keywords+'&id_proceso='+id_proceso;
                url+= '&id_responsable='+id_responsable+'&id_organismo='+id_organismo+'&numero_keywords='+numero_keywords;
                url+= '&id_persona='+id_persona+'&if_output='+if_output+'&lugar='+lugar+'&persona_keywords='+persona_keywords;;
                
                show_imprimir(url,"LISTADO DE OPERACIONES","width=800,height=600,toolbar=no,location=no,scrollbars=yes");
            }
            
            function edit(id) {
                var date_init= encodeURI($('#date_init').val());
                var date_end= encodeURI($('#date_end').val());
                var keywords= $('#keywords').val() ? encodeURI($('#keywords').val()) : ''; 
                var lugar= $('#lugar').val() ? encodeURI($('#lugar').val()) : '';
                var id_organismo= $('#organismo').val();
                var id_responsable= $('#responsable').val();
                var id_proceso= $('#id_proceso').val();
                
                var if_output= 0;
                if ($('#if_output0').is(':checked')) 
                    if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2;
                
                var url= "&date_init="+date_init+"&date_end="+date_end+"&keywords="+keywords+"&id_organismo="+id_organismo;
                url+= "&id_responsable="+id_responsable+'&if_output='+if_output+'&lugar='+lugar+'&id_proceso='+id_proceso;             
                
                var action= $('#exect').val() == 'add' || $('#exect').val() == 'edit' ? 'edit' : 'list';
                self.location.href= '../php/interface.php?menu=lrecord&action='+action+'&id='+id+url;
            }
            
            function eliminar(id, id_ref) {
                var date_init= encodeURI($('#date_init').val());
                var date_end= encodeURI($('#date_end').val());
                var keywords= $('#keywords').val() ? encodeURI($('#keywords').val()) : ''; 
                var lugar= $('#lugar').val() ? encodeURI($('#lugar').val()) : '';
                var id_organismo= $('#organismo').val();
                var id_responsable= $('#responsable').val();
                var id_proceso= $('#id_proceso').val();
                
                var if_output= 0;
                if ($('#if_output0').is(':checked')) 
                    if_output= 0;
                if ($('#if_output1').is(':checked')) 
                    if_output= 1;
                if ($('#if_output2').is(':checked')) 
                    if_output= 2;
                
                var url= "&date_init="+date_init+"&date_end="+date_end+"&keywords="+keywords+"&id_organismo="+id_organismo;
                url+= "&id_responsable="+id_responsable+'&if_output='+if_output+'&lugar='+lugar+'&id_proceso='+id_proceso;
    
                var text= "Esta seguro de querrer eliminar este registro. Esta es una operación irreversible y quedará registrada en el sistema. ";
                text+= "Desea continuar?";
                confirm(text, function(ok) {
                    if (ok) 
                        self.location.href= '../php/register.interface.php?menu=lrecord&action=delete&id='+id+'&id_ref='+id_ref+url;
                    else 
                        return;
                });                
            }
            
            function formPrint(id) {
                _id_print= id;
                displayFloatingDiv('div-ajax-print', '', 60, 0, 10, 15);
                
                var str= $('#sender_'+id).val();
                if (str.length > 0) 
                    str= str.replace(/<br \/>/gi, "\n");

                if (Entrada($('#responsable_'+id).val())) 
                    $('#sender').val($('#responsable_'+id).val());
                else 
                    $('#sender').val(str);
                
                $('#_if_output').val($('#if_output_'+id).val());
                $('#id_responsable').val($('#id_responsable_'+id).val());

                var _url= 'ajax/target.ajax.php?target='+encodeURI($('#target_'+id).val());

                $.ajax({
            //   data:  parametros,
                   url:   _url,
                   type:  'get',
                   beforeSend: function () {
                        $('#ajax-target').html('Procesando, espere por favor...');
                   },
                   success:  function (response) {
                        $('#ajax-target').html(response);                     
                    },
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- ajax-target');
                    }                    
                });            
            }
            
            function imprimir_waybill() {
                var target= encodeURI($('#target').val());
                var sender= encodeURI($('#sender').val());
                var nota= encodeURI($('#nota').val());
                var if_output= $('#_if_output').val();
                var id_responsable= $('#id_responsable').val();
                
                if (parseInt($('#target').val()) == 0) {
                    alert("Especifique el destinatario de este documento");
                    return;
                }
                
                var url= '../print/certifico.php?id='+_id_print+'&sender='+sender+'&target='+target+'&nota='+nota+'&if_output='+if_output;  
                url+= '&id_responsable='+id_responsable;
                show_imprimir(url,"CONDUCE DE ENTREGA","width=800,height=600,toolbar=no,location=no,scrollbars=yes");
            }
            
            function reg_accords(id, date) {
                var date_init= encodeURI($('#date_init').val());
                var date_end= encodeURI($('#date_end').val());
                var keywords= $('#keywords').val() ? encodeURI($('#keywords').val()) : ''; 
                var numero_keywords= $('#numero_keywords').val() ? encodeURI($('#numero_keywords').val()) : '';
                var id_proceso= $('#id_proceso').val();
                
                var url= "faccords.php?action=<?=$action?>&id_archivo="+id+"&date_init="+date_init+'&date_end='+date_end;
                    url+= '&keywords='+keywords+'&numero_keywords='+numero_keywords+'&id_proceso='+id_proceso;
                parent.faccords= document.open(url, "_blank", "width=1024,height=840,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=yes");           
            }
            
            function refreshTab(id) {
                if (id < 0) id= 0;
                $('#init_row_temporary').val(id);         
                refreshp();
            }  
            
            function show_procedencia(id, procedencia) {
                var url= '../print/lprocedencia.php?id='+id+'&procedencia='+encodeURIComponent(procedencia);
                show_imprimir(url,"CONDUCE DE ENTREGA","width=800,height=600,toolbar=no,location=no,scrollbars=yes");            
            }
        </script>
        
        <script type="text/javascript">
            function form_filter(index) {
                if (index == 1)
                    displayFloatingDiv('div-ajax-panel', "FILTRADO DE LOS REGISTROS", 60, 0, 15, 15); 
                else 
                    HideContent('div-ajax-panel');
           }   
        </script>
        
        <script type="text/javascript">
        function writeLog(date, line, divout) {
            document.write(line);
        }
        </script>
        
        <script type="text/javascript">
            $(document).ready(function () {
                InitDragDrop();
                
                $('#div_date_init').datepicker({
                    format: 'dd/mm/yyyy'
                });      
                $('#div_date_end').datepicker({
                    format: 'dd/mm/yyyy'
                });   

                var availableTags = [
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
                    source: availableTags
                });
                
                window.parent.show_proceso("<?=$nombre_prs?>");
                
                valid_filter();
                
                <?php if (!is_null($error)) { ?>
                alert("<?=str_replace("\n"," ", addslashes($error))?>");
                <?php } ?>                  
            });
        </script>     
    </head>

    <body class="table">
        <input type="hidden" id="exect" value="<?=$action?>" />
        <input type="hidden" id="menu" value="frecord" />
        <input type="hidden" id="_if_output" value="" />
        <input type="hidden" id="id_responsable" value="" />
        <input type="hidden" id="responsable" value="" />
        
        <input type="hidden" id="_target" value="" />
        <input type="hidden" id="id_proceso" value="<?=$id_proceso?>" />
        <input type="hidden" id="init_row_temporary" value="<?= $init_row_temporary ?>" />

        <div id="navbar-third" class="app-nav d-none d-md-block">
            <ul class="navd-static list-unstyled d-flex flex-row p-2 row col-12">	
                <li class="col-3">
                    <div class="row">
                        <label class="label ml-3">Muestra:</label>
                        <div id="nshow" class="badge badge-warning">0</div>                    
                    </div>
                </li>

                <li class="col-3">
                    <div class="row">
                        <label class="label ml-3">Ocultas:</label>
                        <div id="nhide" class="badge badge-warning">0</div>                
                    </div>
                </li>
                <li class="col-auto">
                    <div class="col-12">
                        <label id="nombre_prs" class="badge badge-danger">
                            <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?>
                                <i class="fa fa-wifi"></i><?php } ?>
                            <?=$nombre_prs?>
                        </label>
                    </div>
                </li>
            </ul>  
        </div>

        <?php 
        $array_register= array();
        $_keywords= !empty($keywords) ? preg_split("/[\s]*[,;][\s]*/" , strtolower($keywords)) : null;
        $_persona_keywords= !empty($persona_keywords) ? preg_split("/[\s]*[,;][\s]*/" , strtolower($persona_keywords)) : null;
        $_numero_keywords= !empty($numero_keywords) ? preg_split("/[\s]*[,;][\s]*/" , strtolower($numero_keywords)) : null;

        $_keywords= array_map('trim', $_keywords);
        $_persona_keywords= array_map('trim', $_persona_keywords);
        $_numero_keywords= array_map('trim', $_numero_keywords);

        $obj= new Tarchivo($clink);
        if ($_SESSION['trace_time'] == 'yes') {
            $obj->divout= 1;       
            $_SESSION['in_javascript_block']= false;
        }
        
        $obj->SetYear($year);
        $obj->SetIfOutput($_if_output);
        $obj->SetIdResponsable($id_responsable);
        $obj->SetIdPersona($id_persona);
        $obj->SetIdProceso($id_proceso);
        $obj->SetLugar($lugar);
        $obj->SetIdOrganismo(null);
       
        $obj->limited= true;
        $obj->set_init_row_temporary($init_row_temporary);
        $_date_init= $date_init ? time2odbc($date_init." 00:00") : null;
        $_date_end= $date_end ? time2odbc($date_end." 23:59") : null;

        $obj->debug_time('listar'); 
        if ((!empty($_date_init) || !empty($_date_end)) || ($keywords || $persona_keywords || $numero_keywords)) 
            $result_archive= $obj->listar($_date_init, $_date_end, 1, null, $_keywords, $_persona_keywords, $_numero_keywords, false);                    
        else 
            $result_archive= null;
        $obj->debug_time('listar'); 

        $max_num_pages= $obj->max_num_pages;  
       ?>   
        
    <!-- Docs master nav -->
    <div class="row app-pagination d-none d-md-block">
        <div class="toolbar">
            <div class="toolbar-center">
                <div class="center-inside">
                    <?php for ($i=0; $i < $max_num_pages; $i++) { ?>
                    <a href="javascript:refreshTab(<?=$i?>)" class="btn btn-default <?php if ($i == $init_row_temporary) echo "active"?>">
                        <?=($i+1)?>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <div class="btn-left">
                <div class="btn btn-default double">
                    <i class="fa fa-angle-double-left fa-2x"></i>
                </div>
                <div class="btn btn-default single">
                    <i class="fa fa-angle-left fa-2x"></i>
                </div>
            </div>

            <div class="btn-right">
                <div class="btn btn-default single">
                    <i class="fa fa-angle-right fa-2x"></i>
                </div>
                <div class="btn btn-default double">
                    <i class="fa fa-angle-double-right fa-2x"></i>
                </div>
            </div>
        </div>
    </div>      
    
    <div id="toolbar" class="btn-btn-group btn-app d-inline-block" style="margin: 6px 0px 6px 0px;">
        <button id="btn_filter1" class="btn btn-success ml-2" type="button" onclick="form_filter(1);">
            <i class="fa fa-angle-double-up"></i>Ver Filtro
        </button>

        <button type="button" class="btn btn-info d-none d-md-inline-block" onclick="imprimir()">
            <i class="fa fa-print"></i>Imprimir
        </button>      

        <button class="btn btn-sm" type="button" onclick="form_filter(1)">
            <?="Desde:$_date_init Hasta:$_date_end"?>
        </button>
    </div>    
   
    <div class="app-body container-fluid onebar" style="overflow-y: hidden; font-size: 0.9em;">
        <table id="table" class="table table-hover table-striped"
               data-toggle="table"
               data-toolbar="#toolbar"
               data-search="true"
               data-show-columns="true">
            <thead>
                <th>No</th>
                <?php if ($action == 'add' || $action == 'edit') { ?>
                <th></th>
                <?php } ?>
                <th>No. Reg</th>
                <th>Tramitador</th>
                <th>Procedencia</th>
                <th>Destino</th>
                <th>Fecha del Documento</th>
                <th>Contenido</th>
                <th>Antecedentes</th>
                <th>Indicación</th>
            </thead>
                <?php
                $i= 0;
                $k= 0;
                $array_archivos= array();
                $obj_ref= new Tref_archivo($clink);
                $obj_ref->if_tarchivos= $obj->if_tarchivos;
                $obj_ref->if_tarchivo_personas= $obj->if_tarchivo_personas;
                
                $obj_org= new Torganismo($clink);
                
                $obj_ref->if_tarchivo_personas= $obj->if_tarchivo_personas;
                
                $array_ids= array();
                $participantes= null;
                
                foreach ($result_archive as $row) { 
                    $id_responsable_reg= null;
                    $_id_responsable= !empty($row['_id_responsable']) && $row['_id_responsable'] != _USER_SYSTEM ? $row['_id_responsable'] : 0;
                    
                    if (!empty($id_responsable) && ((!empty($row['_id_responsable']) && $row['_id_responsable'] != $id_responsable) 
                        || (!empty($row['_id_usuario']) && $row['_id_usuario'] != $id_responsable))) 
                        continue;

                    if (!empty($id_responsable)) {
                        $obj->SetIdArchivo($row['_id']);
                        $obj->SetIdUsuario($id_responsable);
                        $cant= $obj->getReg();
                        if (is_null($cant)) 
                            continue;                            
                    }

                    $target= null;
                    $sender= null;
                    $usuarios= null;
                    
                    if (empty($array_ids[$row['_id']])) {
                        $array_ids[$row['_id']]= $row['_id'];
                        $participantes= $obj_ref->getParticipantes($row['_id'], true);
                    } else {
                        $participantes= null;
                    }
                    
                    if (!boolean($row['if_sender'])) {
                        if (!empty($row['id_grupo'])) {;
                            $usuarios= $row['nombre'];
                            $id_responsable_reg= $row['id_responsable'];
                        } 
                        if (!empty($row['_id_usuario'])) {
                            $usuarios= $row['nombre'];
                            if (!empty($row['cargo'])) 
                                $usuarios.= ", {$row['cargo']}";
                            $id_responsable_reg= $row['_id_usuario'];
                        }
                        if (!empty($row['id_persona'])) {      
                            $usuarios= $row['nombre'];
                            if (!empty($row['cargo'])) 
                                $usuarios.= ", {$row['cargo']}";    
                            if (!empty($row['id_organismo'])) {
                                $obj_org->Set($row['id_organismo']);
                                $usuarios.= ", ".$obj_org->GetNombre(); 
                            }    
                             $id_responsable_reg= $row['id_responsable_reg'];
                        } 
                    }
                    
                    $sender= $participantes;
                    $target= $usuarios;    
                    
                    $no_ref= $row['codigo'];
                        
                    $array_register[$no_ref]['id']= $row['_id'];
                    $array_register[$no_ref]['id_user_asigna']= $row['_id_user_asigna'];
                    $array_register[$no_ref]['id_ref']= $row['id_ref'];
                    $array_register[$no_ref]['if_output']= boolean($row['if_output']);
                    $array_register[$no_ref]['fecha_origen']= $row['fecha_origen'];
                    $array_register[$no_ref]['fecha_fin_plan']= $row['fecha_fin_plan'];
                    if (!empty($sender)) 
                        $array_register[$no_ref]['sender'][0]= $sender;
                    if (!empty($target)) 
                        $array_register[$no_ref]['target'][]= $target;
                    $array_register[$no_ref]['cronos']= $row['_cronos'];
                    if (!empty($row['id_organismo'])) 
                        $array_register[$no_ref]['organismo'][]= $row['id_organismo'];
                    
                    $array_register[$no_ref]['id_responsable']= $_id_responsable;
                    $array_register[$no_ref]['id_documento']= $row['id_documento'];
                    $array_register[$no_ref]['indicaciones']= $row['indicaciones'];
                    $array_register[$no_ref]['antecedentes']= $row['antecedentes'];
                    $array_register[$no_ref]['descripcion']= $row['descripcion'];
                } 
                
                foreach ($array_register as $no_ref => $row) {
                    if (empty($row['sender'][0])) 
                        $array_register[$no_ref]['sender'][0]= "ANÓNIMO";
                    if (count($row['target']) == 0) 
                        $array_register[$no_ref]['targer'][0]= "ARCHIVO";
                }
                ?>

            <tbody> 
                <?php  
                reset($array_register);
                foreach ($array_register as $no_ref => $row) { 
                    if (!empty($id_organismo)) {
                        if (is_null($row['organismo'])) 
                            continue;
                        if (array_search($id_organismo, $row['organismo']) === false) 
                            continue;
                    }
                    
                    $j= 0;
                    $row['target']= array_unique($row['target'], SORT_STRING);
                    $row['sender']= array_unique($row['sender'], SORT_STRING);
                    $nrows= count($row['target']);

                    do {
                        $colspan= ($_no_ref == $no_ref) ? true : false;
                        if (!$colspan) 
                            $_no_ref= $no_ref;
                ?>

                <tr>
                    <td>
                        <?=++$i?>
                        <?php if (!$colspan) { ?>
                            <input type="hidden" id="if_output_<?=$row['id']?>" value="<?=!empty($row['if_output']) ? 1 : 0?>" />
                            <input type="hidden" id="sender_<?=$row['id']?>" value="<?=implode('<br/>', $row['sender'])?>" />
                            <input type="hidden" id="target_<?=$row['id']?>" value="<?= implode('||', $row['target'])?>" />                                
                            <input type="hidden" id="id_responsable_<?=$row['id']?>" value="<?=$row['id_responsable']?>" />  

                            <?php
                            $responsable= null;
                            if (!empty($row['id_responsable'])) {
                                $email= $obj_user->GetEmail($row['id_responsable']);
                                $cargo= !empty($mail['cargo']) ? "<br />{$mail['cargo']}" : "";
                                $responsable= "{$email['nombre']}{$cargo}";    
                            }
                            ?>

                            <input type="hidden" id="responsable_<?=$row['id']?>" value="<?=$responsable?>" />  
                        <?php } ?>
                    </td>

                    <?php if (!$colspan) { ?>    
                        <td rowspan="<?=$nrows?>">
                            <?php if ($action == 'add' || $action == 'edit') { ?>
                                
                                <a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="eliminar(<?=$row['id']?>, <?=$row['id_ref']?>);">
                                    <i class="fa fa-trash"></i>Eliminar
                                </a>

                                 <a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="edit(<?=$row['id']?>);" >
                                     <i class="fa fa-edit"></i>Editar
                                 </a>

                                <?php 
                                if (!empty($row['id_documento'])) { 
                                    if (isset($obj_doc)) unset($obj_doc);
                                    $obj_doc= new Tdocumento($clink);
                                    $obj_doc->Set($row['id_documento']);

                                    $type= get_file_type($obj_doc->filename);
                                    $mime= mime_type($type['ext']);
                                    $url= urlencode(_UPLOAD_DIRIGER_DIR.$obj_doc->url)."&send_file=0&filename=".urlencode($obj_doc->filename);
                                ?>
                                <a href="<?="../../common/download.php?file=$url"?>" name="<?=$obj_doc->filename?>" type="<?=$mime?>"  target="_blank" class="btn btn-success btn-sm" title="Archivo digital" >
                                     <i class="fa fa-file-text"></i><br />Documento
                                 </a>
                                <?php } ?>  

                                 <a href="#" class="btn btn-success btn-sm" title="Editar" onclick="formPrint(<?=$row['id']?>);" >
                                     <i class="fa fa-envelope"></i>Conduce
                                 </a> 
                                <?php if (!empty($row['indicaciones']) && !empty($row['fecha_fin_plan'])) { ?>
                                 <a href="#" class="btn btn-info btn-sm" title="Editar" onclick="reg_accords(<?=$row['id']?>, '<?=odbc2date($row['fecha_origen'])?>');" >
                                     <i class="fa fa-check"></i>Registrar
                                 </a> 
                                <?php } ?>
                            <?php }  ?>

                            <a href="#" class="btn btn-success btn-sm" title="Editar" onclick="show_procedencia(<?=$row['id']?>, '<?=$row['antecedentes']?>');" >
                                <i class="fa fa-search"></i>Procedencia
                            </a> 
                        </td>
                    <?php }  ?>
                            
                    <?php  if (!$colspan) { ?>      
                        <td rowspan="<?=$nrows?>"><?= $no_ref?></td>

                        <td rowspan="<?=$nrows?>">
                            <?=odbc2time_ampm($row['cronos'])?>
                            <br/>
                            <?php
                            $email= $obj_user->GetEmail($row['id_user_asigna']);
                            $cargo= !empty($mail['cargo']) ? "<br />".textparse ($mail['cargo']) : "";
                            echo "{$email['nombre']}{$cargo}";       
                            ?>
                        </td>
                    <?php } ?>

                    <?php if (!$colspan) { ?>
                        <td rowspan="<?=$nrows?>">
                            <?= implode('<br/>', $row['sender']) ?>        
                        </td>
                    <?php } ?>  

                    <td>
                        <?= count($row['target']) > 1 ? $row['target'][$j] : $row['target'][0] ?> 
                    </td>

                    <?php if (!$colspan) { ?> 
                        <td rowspan="<?=$nrows?>">
                            <?= odbc2date($row['fecha_origen'])?>
                        </td>

                        <td rowspan="<?=$nrows?>">
                            <?= textparse($row['descripcion'])?>            
                        </td>

                        <td rowspan="<?=$nrows?>">
                            <?= textparse($row['antecedentes'])?>
                        </td>

                        <td rowspan="<?=$nrows?>">
                            <?php
                            $row_cump= null;

                            if (!empty($row['indicaciones'])) { 
                                $obj->SetIdArchivo($row['_id']);
                                $obj->SetIdUsuario($id_responsable_reg);
                                $row_cump= $obj->getReg();
                            ?>
                                <?=$row['fecha_fin_plan'] ? odbc2time_ampm($row['fecha_fin_plan']) : "INMEDIATO"?>
                                <br/>
                                <label class="text alarm btn-sm <?=$eventos_cump_class[$row_cump['cumplimiento']]?>" id="cumplimiento_text_<?=$i?>">
                                    <?=$eventos_cump[$row_cump['cumplimiento']]?>
                                </label>  
                                <br />
                                <?= textparse($row['indicaciones'])?>
                            <?php } ?> 
                        </td>
                    <?php } ?>    
                </tr>

            <?php 
                        ++$j;
                    }
                    while ($j < $nrows);
                }  
            ?>
                
                <tr>
                    <td><br/><br/><br/><br/></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>  
        <br/>
        <br/>
    </div>
        
        
    <?php require_once "inc/filter.inc.php";?>       
        
    <!-- div-ajax-print -->
    <div id="div-ajax-print" class="card card-primary" data-bind="draganddrop">
        <div class="card-header">
            <div class="row">
                <div class="panel-title ajax-title col-11 m-0 win-drag ">DATOS DE CONTACTO</div>
                <div class="col-1 m-0">
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
                    <div class="col-10">
                        <textarea id="sender" class="form-control" row="2"></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Destino:
                    </label>
                    <div class="col-10">
                        <div id="ajax-target">
                            <select name="target" id="target" class="form-control">
                                <option value=0>Seleccione ... </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-2">
                        Notas:
                    </label>
                    <div class="col-10">
                       <textarea id="nota" name="nota" class="form-control" rows="5"></textarea>
                   </div>                   
                </div>

                <div class="btn-block btn-app">
                    <button type="button" class="btn btn-primary d-none d-lg-block" onclick="imprimir_waybill()">Imprimir</button>
                    <button type="button" class="btn btn-warning" onclick="HideContent('div-ajax-print')">Cerrar</button>
                </div>                     
            </div>  
        </div>
    </div> <!-- div-ajax-print -->
    </body>
</html>