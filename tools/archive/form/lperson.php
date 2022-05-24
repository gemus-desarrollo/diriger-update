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

require_once "../php/class/organismo.class.php";
require_once "../php/class/persona.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$id_organismo= !empty($_GET['id_organismo']) ? $_GET['id_organismo'] : null; 
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null; 
$id_prov= !empty($_GET['id_prov']) ? $_GET['id_prov'] : "0";
$id_mcpo= !empty($_GET['id_mcpo']) ? $_GET['id_mcpo'] : "0";
$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : null; 

$acc= $_SESSION['acc_archive'];
if ($acc == _ACCESO_ALTA || ($acc == _ACCESO_MEDIA && $_SESSION['nivel_archive2'] == _USER_REGISTRO_ARCH))
    $action= 'edit';
else
    $action= 'list';

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $nombre_prs= $obj_prs->GetNombre();
    $tipo_prs= $Ttipo_proceso_array[(int)$obj_prs->GetTipo()];
    
    $nombre_prs.= ", $tipo_prs";
}

$obj_user= new Tusuario($clink);

$obj_pers= new Tpersona($clink);
$lugares= $obj_pers->listar_lugares();

$obj_org= new Torganismo($clink);

$url_page= "../form/lperson.php?signal=$signal&action=$action&id_prov=$id_prov&id_mcpo=$id_mcpo&lugar=". urlencode($lugar);
$url_page.= "&menu=lperson&id_proceso=$id_proceso&exect=$action&id_proceso=$id_proceso";

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

    <!-- ================================================== -->
        <link rel="stylesheet" href="../../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../../libs/bootstrap-table/bootstrap-table.min.js"></script>  

        <link rel="stylesheet" type="text/css" media="screen" href="../../../css/alarm.css?">

        <link href="../../../libs/windowmove/windowmove.css" rel="stylesheet" />
        <script type="text/javascript" src="../../../libs/windowmove/windowmove.js?version="></script>        
        
        <script type="text/javascript" charset="utf-8" src="../../../js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="../../../js/general.js?version="></script> 

        <script type="text/javascript" charset="utf-8" src="../../../js/form.js?version="></script>
       
        <style type="text/css">
            .panel-filter {
         /*       position: absolute; */
                top: 0px;
                padding: 10px 10px 10px 6px;
                background: #fffff;
                /*         
                opacity: 0.9;
                -moz-opacity: 0.9;
                filter: alpha(opacity=90);
                -khtml-opacity: 0.9;
               */ 
            }
            div.table {
         /*       margin-top: 300px; */
            }
            #div-ajax-print {
                width: 60%;
                display: none;
            }
        </style>
        
        <script type="text/javascript">
            function FAjaxMcpo() {
                var id_prov= $('#provincia').val();
                var id_mcpo= $('#id_mcpo').val();
                
                if (id_prov == 0 || id_prov == null) {
                    $('#municipio').empty();
                    $('#municipio').val(0);
                    $('#id_mcpo').val(0);
                    refreshp();                    
                }

                $.ajax({
                    url: 'ajax/municipio.ajax.php?csfr_token=123abc',
                    data: {id_prov: id_prov, id_mcpo: id_mcpo},
                    type: 'get',
                    dataType: 'html',
                    
                    success: function (response) {
                        $('#ajax-municipio').html(response);
                        refreshp();
                    },
                    
                    error: function (xhr, status) {
                        alert('Disculpe, existió un problema -- FAjaxMcpo');
                    }                    
                });
            }
            
            function refreshp(index) {
                var lugar= $('#lugar').val() ? encodeURI($('#lugar').val()) : '';
                var id_prov= $('#provincia').val();
                var id_mcpo= $('#municipio').val();
                var id_proceso= $('#proceso').val();
                var id_organismo= $('#organismo').val();
                var action= $('#exect').val();

                var url= 'lperson.php?id_prov='+id_prov+'&id_mcpo='+id_mcpo+'&id_organismo='+id_organismo;
                url+= '&id_proceso='+id_proceso+'&action='+action+'&lugar='+lugar;

                self.location.href= url;                
            }
            
            function imprimir() {
                var lugar= $('#lugar').val() ? encodeURI($('#lugar').val()) : '';
                var id_prov= $('#provincia').val();
                var id_mcpo= $('#municipio').val();
                var id_proceso= $('#proceso').val();
                var id_organismo= $('#organismo').val();

                var url= '../print/lperson.php?id_prov='+id_prov+'&id_mcpo='+id_mcpo+'&id_organismo='+id_organismo;
                url+= '&id_proceso='+id_proceso+'&lugar='+lugar;

                self.location.href= url;   
                
                show_imprimir(url,"LISTADO DE DESTINATARIOS Y REMITENTES EXTERNOS","width=800,height=600,toolbar=no,location=no,scrollbars=yes");
            }
            
            function edit(id) {                
                var action= $('#exect').val() == 'add' || $('#exect').val() == 'edit' ? 'edit' : 'list';
                self.location.href= '../php/persona.interface.php?menu=person&action='+action+'&id='+id;
            }
            
            function eliminar(id) {                  
                var text= "Esta seguro de querrer eliminar este registro. Desea continuar?";
                confirm(text, function(ok) {
                    if (ok) 
                        self.location.href= '../php/persona.interface.php?menu=person&action=delete&id='+id;
                    else 
                        return;
                });                
            }
        </script>
        
        <script type="text/javascript">
            $(document).ready(function () {
                InitDragDrop();
               
                $('#provincia').change(function() {                   
                    if ($(this).val() == 0) {
                        $('#municipio').empty();
                    }
                    
                    FAjaxMcpo();
                });
                
                window.parent.show_proceso("<?=$nombre_prs?>");
                
                <?php if (!is_null($error)) { ?>
                alert("<?=str_replace("\n"," ", addslashes($error))?>");
                <?php } ?>                  
            });
        </script>
            
    </head>

    <body class="table">
        <input type="hidden" id="exect" value="<?=$action?>" />
        <input type="hidden" id="menu" value="lperson" />
        <input type="hidden" id="id_mcpo" value="<?=$id_mcpo?>" />
        <input type="hidden" id="id_prov" value="<?=$id_prov?>" />

    <div class="app-body container-fluid" style="overflow-y: hidden;">
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
                        <th>NOMBRE/CARGO</th>
                        <th>ORGANISMO</th>
                        <th>No. IDENTIDAD</th>
                        <th>MUNICIPIO/PROVINCIA</th>
                        <th>TELÉFONO</th>
                        <th>MOVIL</th>
                        <th>CORREO ELECTRÓNICO</th>
                        <th>DIRECCIÓN</th>
                    </thead>
                    
                    <tbody>
                        <?php 
                        $obj_pers->SetIdOrganismo($id_organismo);
                        $obj_pers->SetProvincia($id_prov);
                        $obj_pers->SetMunicipio($id_mcpo);
                        $obj_pers->SetIdProceso($_SESSION['id_entity']);
                        $obj_pers->SetLugar($lugar);
                        
                        $personas= $obj_pers->listar(false);
                        $i= 0;
                        while ($row= $clink->fetch_array($personas)) {
                        ?>
                            <tr>
                                <td>
                                    <?=++$i?>
                                </td>

                                <?php if ($action == 'add' || $action == 'edit') { ?>
                                <td>
                                    <a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="eliminar(<?=$row['_id']?>);">
                                        <i class="fa fa-trash"></i>Eliminar
                                    </a>

                                     <a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="edit(<?=$row['_id']?>);" >
                                         <i class="fa fa-edit"></i>Editar
                                     </a>
                                </td>
                            <?php } ?>
                            
                            <td>
                                <?php
                                    echo textparse($row['nombre']);
                                    if (!empty($row['cargo'])) echo "<br/>". textparse($row['cargo']);
                                ?>                            
                            </td>
                            <td>
                                <?php 
                                if (!empty($row['id_organismo'])) {
                                    $result_org= $obj_org->Set($row['id_organismo']);
                                    echo $obj_org->GetNombre();
                                }
                                ?>
                            </td>
                            <td>
                                <?=$row['noIdentidad']?>        
                            </td>
                            <td>
                                <?php
                                echo utf8_encode(Tarray_municipios[$row['provincia']][1][$row['municipio']]);
                                if (!empty($row['municipio'])) echo "<br/>";
                                if (!empty($row['provincia'])) echo utf8_encode ($Tarray_provincias[$row['provincia']]);
                                ?>          
                            </td>
                            <td>
                                <?=$row['telefono']?>
                            </td>
                            <td>
                                <?=$row['movil']?>                            
                            </td>
                            <td>
                                <?=$row['email']?>
                            </td>
                            <td>
                                <?php
                                if (!empty($row['lugar'])) echo textparse ($row['lugar']);
                                if (!empty($row['lugar']) && !empty($row['direccion'])) echo "<br/>"; 
                                if (!empty($row['direccion'])) echo textparse($row['direccion']);
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>                
            </div>
        </div>

    <div id="div-ajax-print" class="card card-primary" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="panel-title ajax-title col-11 lg-11 win-drag ">DATOS DE CONTACTO</div>
                <div class="col-1 close pull-right">
                    <div class="close">
                        <a href="#" onclick="HideContent('div-ajax-print')">
                            <i class="fa fa-close"></i>
                        </a>                             
                    </div>
                </div>                      
            </div>                 
        </div>
        
        <div class="card-body"> 
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="form-horizontal container-fluid">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3 col-lg-3">
                                 Provincia:
                             </label> 
                            <div class="col-md-9 col-lg-9">
                                <select id="provincia" name="provincia" class="form-control">
                                    <option value="0">... </option>
                                    <?php foreach ($Tarray_provincias as $key => $prov) { ?>
                                    <option value="<?=$key?>" <?php if ($id_prov == $key) { ?>selected="selected"<?php } ?>><?= utf8_encode($prov)?></option>
                                    <?php } ?>
                                </select>                                        
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3 col-lg-3">
                                 Municipio:
                             </label>                                 
                            <div id="ajax-municipio" class="ajax-select col-md-9 col-lg-9">
                                <select id="municipio" name="municipio" class="form-control">
                                    <option value="0">... </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6">
                    <div class="form-horizontal container-fluid">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                 Organismos:
                             </label>                                     

                            <div class="col-md-9">
                                <select class="form-control" id="organismo" name="organismo" onchange="refreshp()">
                                    <option value="0">Seleccione ... </option>
                                    <?php 
                                    $obj_org= new Torganismo($clink);
                                    $result_org= $obj_org->listar();

                                    while ($row= $clink->fetch_array($result_org)) { ?>
                                       <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_organismo) echo "selected='selected'"?>><?=$row['nombre']?></option>
                                    <?php } ?>
                                 </select>                                       
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-md-3 col-sm-3">Lugar:</label>
                            <div class="col-md-9 col-sm-9">
                                <select id="lugar" name="lugar" class="form-control">
                                    <option value="0">... </option>
                                    <?php foreach ($lugares as $site) { ?>
                                        <option value="<?=$site?>"><?= textparse($site)?> </option>
                                    <?php } ?>
                                </select>
                            </div>                                    
                        </div>

                        <div class="form-group row">
                            <label class="col-form-label col-md-3 col-lg-2">Direcciones Funcionales:</label>                                
                            <div class="col-md-9 col-lg-10">
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

                    </div>
                </div>                     
            </div>
        </div>    
    </div>
        
    </body>
</html>