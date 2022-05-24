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

require_once "../../php/class/code.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;
$signal= !empty($_GET['signal']) ?  $_GET['signal']: 'tablero';
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id= !empty($_GET['id']) ? $_GET['id'] : 0; 

$obj= new Tlista_requisito($clink);

if (!empty($id) && $action == 'update') {
    $obj->SetIdRequisito($id);
    $obj->Set();
}

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
if (empty($numero)) 
    $numero= 0;

$componente= !is_null($_GET['componente']) ? $_GET['componente'] : $obj->GetComponente();
if (empty($componente)) 
    $componente= 0;

$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();

$peso= !is_null($_GET['peso']) ? $_GET['peso'] : $obj->GetPeso();
$nombre= !is_null($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$indicacion= !is_null($_GET['indicacion']) ? urldecode($_GET['indicacion']) : $obj->GetIndicacion();
$evidencia= !is_null($_GET['evidencia']) ? urldecode($_GET['evidencia']) : $obj->GetEvidencia();

$id_subcapitulo0= 0;
$id_subcapitulo1= 0;
$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : $obj->GetIdTipo_lista();

if (!empty($id_tipo_lista)) {
    $obj_tipo= new Ttipo_lista($clink);
    $obj_tipo->Set($id_tipo_lista);
    $id_subcapitulo= $obj_tipo->GetIdSubcapitulo();

    if (!empty($id_subcapitulo)) {
        $id_subcapitulo0= $id_subcapitulo;
        $id_subcapitulo1= $id_tipo_lista;
    } else {
        $id_subcapitulo0= $id_tipo_lista;
        $id_subcapitulo1= 0;
    }
}

$_inicio= $year -3;
$_fin= $year + 3;

if (empty($inicio)) 
    $inicio= $_inicio;
if (empty($fin)) 
    $fin= $_fin;

$obj->SetYear($year);

$obj_prs= new Tproceso($clink);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>    
   
   <script language='javascript' type="text/javascript" charset="utf-8">
        function select_chk_list(id) {
            $('#id_requisito').val(id > 0 ? id : 0);
            if (id == 0) {
                $('#selected').val(0);
                return;
            }
            
            $('#selected').val(1);
            $('#id_requisito_code').val($('#id_requisito_code_'+id).val());          
            $('#requisito_text').val($('#requisito_text_'+id).val());
            $('#nav-tab2').hide();
            $('#tab2').hide();
        }  
        
        function validar_list() {
            if ($('#selected').val() == 0) {
                alert("Debe seleccionar un requisito ha ser verificado.");
                return;
            }
            
           HideContent('div-ajax-panel'); 
        }
        
        function closeLista() {
            if ($('#selected').val() == 0) {
                $('#nav-tab2').show();
                $('#tab2').show();                
            }
        }
    </script>  
    
    <div class="card card-primary">
        <div class="card-header win-drag">
            <div class="row">
                <div class="col-md-12">
                     <div class="panel-title col-11 win-drag">LISTAS DE CHEQUEO O GUÍAS DE CONTROL</div>

                     <div class="col-1 pull-right">
                         <div class="close">
                             <a href= "javascript:HideContent('div-ajax-panel');" title="cerrar ventana">
                                 <i class="fa fa-close"></i>
                             </a>                                                        
                         </div>    
                     </div> 
                 </div>              
            </div>
        </div>

        <div class="card-body">
            <?php
            $obj_item= new Tlista_requisito($clink);
            $obj_item->SetYear($year);
            $obj_item->SetIdTipo_lista($id_tipo_lista);
            $obj_item->SetIdLista($id_lista);

            $result= $obj_item->listar();

            if (isset($obj_tipo)) unset($obj_tipo);
            $obj_tipo= new Ttipo_lista($clink);
            ?>

            <input type="hidden" name="selected" id="selected" value="0" />
                
            <table class="table table-hover table-striped"
                  data-toggle="table"
                  data-toolbar="#toolbar"
                  data-height="400"
                  data-search="true"
                  data-show-columns="true"> 
               <thead>
                   <tr>
                       <th>No.</th>
                       <th></th>
                       <th>Requisitos a Evaluar</th>
                       <th>Evidencias</th>
                       <th>Indicaciones al Equipo Evaluador</th>
                   </tr>
               </thead>

               <tbody>
                   <?php 
                   $i= 0;
                   while ($row= $clink->fetch_array($result)) { 
                       ++$i;
                   ?>
                   <tr>
                       <td>
                           <?php
                           $numero= $row['componente'];
                           if (!empty($row['id_tipo_lista'])) {
                               $obj_tipo->Set($row['id_tipo_lista']);
                               $capitulo= $obj_tipo->GetCapitulo();
                               $subcapitulo= $obj_tipo->GetSubcapitulo();
                           }
                           if (!empty($capitulo)) 
                                $numero.= ".$capitulo";
                           if (!empty($subcapitulo)) 
                                $numero.= ".$subcapitulo";
                           $numero.= ") {$row['numero']}";
                           echo $numero;
                           
                           $text= $numero;
                           $text.= " ".$row['nombre'];
                           ?>
                       </td>

                       <td>
                           <input type="hidden" name="requisito_text_<?=$row['_id']?>" id="requisito_text_<?=$row['_id']?>" value="<?= textparse($text, true)?>" />
                           
                           <input type="hidden" name="id_requisito_code_<?=$row['_id']?>" id="id_requisito_code_<?=$row['_id']?>" value="<?=$row['_id_code']?>" />                                                  
                           <input type="radio" name="chk_list_" id="chk_list_<?=$row['_id']?>" value="1" onclick="select_chk_list(<?=$row['_id']?>)" />                                                  
                       </td>

                       <td>
                           <?= textparse($row['nombre'])?>  
                       </td>
                       <td>
                           <?= textparse($row['evidencia'])?> 
                       </td>
                       <td>
                           <?= textparse($row['indicacion'])?> 
                       </td>
                   </tr>
                   <?php } ?>
                  
               </tbody>
           </table>            
            
            
            <!-- buttom -->
            <div id="_submit" class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="validar_list()">Aceptar</button>
                <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel');">Cerrar</button>
            </div>

            <div id="_submited" style="display:none">
                <img src="../../img/loading.gif" alt="cargando" />     Por favor espere ..........................
            </div>            
        </div>
    </div>    

  

