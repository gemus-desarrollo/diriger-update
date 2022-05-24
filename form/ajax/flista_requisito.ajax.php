<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";

require_once "../../php/class/proceso.class.php";
require_once "../../php/class/peso.class.php";

require_once "../../php/class/tipo_lista.class.php";
require_once "../../php/class/lista.class.php";
require_once "../../php/class/lista_requisito.class.php";
require_once "../../php/class/register_nota.class.php";

require_once "../php/class/code.class.php";

$id= !empty($_GET['id']) ? $_GET['id'] : 0; 
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0; 

$obj_requisito= new Tlista_requisito($clink);
$obj_requisito->SetIdRequisito($id);
$obj_requisito->Set();

$id_lista= $obj_requisito->GetIdLista();

$obj_reg= new Tregister_nota($clink);
$obj_reg->SetYear($year);
$obj_reg->SetIdAuditoria($id_auditoria);
$obj_reg->SetIdLista($id_lista);
$obj_reg->SetIdRequisito($id);

$obj_reg->getAvance();

$obj_prs= new Tproceso($clink);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>  

    <script type="text/javascript">	
        $(document).ready(function() {
            $('#div_reg_fecha').datepicker({
                format: 'dd/mm/yyyy'             
            });   

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
                selector: '#observacion',
                theme: 'modern',
                language: 'es',                
                height: 160,
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
                $('#observacion').val(<?= json_encode(textparse($obj_reg->GetObservacion(), true))?>);
            } catch(e) {;} 

            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });	
    </script>    
    
    <div class="card card-primary">
        <div class="card-header win-drag">
            <div class="row">
                <div class="col-12">
                     <div class="panel-title col-11 win-drag">ESTADO DE REQUISITO DE LISTAS DE CHEQUEO</div>

                     <div class="col-1 pull-right">
                         <div class="close">
                             <a href= "javascript:HideContent('div-panel-register');" title="cerrar ventana">
                                 <i class="fa fa-close"></i>
                             </a>                                                        
                         </div>    
                     </div> 
                 </div>              
            </div>
        </div>

        <div class="card-body">
            
            <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Registros Anteriores</a></li>
            </ul>

            <div class="tabcontent" id="tab1"> 
                <div class="form-horizontal" style="margin-top: 10px;">
                    <div class="form-group row">
                        <div class="col-6">
                            <label class="col-form-label col-3">
                                Estado:
                            </label>
                            <div class="col-9">
                                <select class="form-control" id="cumplimiento" name="cumplimiento">
                                    <option value="-1">...  </option>
                                    <?php 
                                    for ($i= 1; $i < 5; $i++) { 
                                        $row= $Tcriterio_array[$i];
                                    ?>
                                    <option value="<?=$row[1]?>" <?php if ($i == $cumplimiento) { ?>selected<?php } ?> title="<?=$row[2]?>"><?=$row[0]?></option>
                                   <?php } ?> 
                                </select>                  
                            </div> 
                        </div>
                        
                        <div class="col-5">
                            <label class="radio text col-xs-3 col-sm-3 col-md-2 col-lg-2">
                                Registro:
                            </label>

                            <div class="col-xs-7 col-sm-7 col-md-7 col-lg-7">
                                <div class='input-group date' id='div_reg_fecha' data-date-language="es">
                                    <input type='text' id="reg_fecha" name="reg_fecha" class="form-control" readonly value="<?=$reg_fecha?>" />
                                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                                </div>                
                            </div>                            
                        </div> 

                    </div>  
                    
                    <div class="form-group row">
                        <label class="col-form-label col-12 pull-left">
                            Observaciones:
                        </label>

                        <div class="col-12">
                            <textarea name="observacion" id="observacion" class="form-control"><?= textparse($obj_reg->GetObservacion()) ?></textarea>
                        </div>
                    </div>                    
                </div>
            </div>
            
            <div class="tabcontent" id="tab2">
                <table id="table-plan"class="table table-striped"
                       data-toggle="table"
                       data-height="320"
                       data-search="true"
                       data-show-columns="true"> 
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>ESTADO</th>
                            <th>FECHA Y HORA</th>
                            <th>REGISTRADO POR</th>
                            <th>OBSERVACIÓN</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $obj_reg->SetIdUsuario(null);
                        $obj_reg->SetCumplimiento(null);
                        $result = $obj_reg->getAvance($id, true);
                        $cant = $obj_reg->GetCantidad();
                        $i = 0;

                        if ($cant > 0) {
                            while ($row = $clink->fetch_array($result)) {
                                ?>
                                <tr>
                                    <td>
                                        <?= ++$i ?>
                                    </td>
                                    <td>
                                        <?= $eventos_cump[$row['cumplimiento']] ?>
                                    </td>
                                    <td>
                                        <?= odbc2time_ampm($row['cronos']) ?>
                                    </td>
                                    <td>
                                        <?= ($row['id_responsable'] == _USER_SYSTEM) ? "SISTEMA DIRIGER" : $row['responsable'] ?>
                                    </td>
                                    <td>
                                        <?= textparse($row['observacion']) ?>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="ejecutar()">Aceptar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-panel-register');">Cerrar</button>
            </div>

            <div id="_submited" style="display:none">
                <img src="../../img/loading.gif" alt="cargando" />     Por favor espere ..........................
            </div>            
        </div>
    </div>    

  

