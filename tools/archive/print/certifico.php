<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";
session_start();

include_once "../../../php/config.inc.php";
include_once "../../../php/class/connect.class.php";
include_once "../../../php/class/usuario.class.php";
include_once "../../../php/class/proceso.class.php";

include_once "../php/class/archivo.class.php";

$obj_prs= new Tproceso($clink);
$obj_prs->Set($_SESSION['local_proceso_id']);
$lugar= $obj_prs->GetLugar();
$proceso= $obj_prs->GetNombre();

$obj_arch= new Tarchivo($clink);

$list= null;
$if_immediate= 0;

$id= !empty($_GET['id']) ? $_GET['id'] : null;

$obj_prs= new Tproceso($clink);

if (!empty($id)) {
    $obj_arch->Set ($id);
    
    $if_output= $obj_arch->GetIfOutput();
    $fecha_entrega= odbc2date($obj_arch->GetFechaEntrega());
    $fecha_fin_plan= odbc2date($obj_arch->GetFechaFinPlan());
    $contenido= $obj_arch->GetDescripcion();
    $indicaciones= $obj_arch->GetIndicaciones();
    $if_immediate= $obj_arch->GetIfImmediate();
    $id_responsable= $obj_arch->GetIdResponsable();
    $id_proceso= $obj_arch->GetIdProceso();
  
    $numero= $obj_arch->GetCodigo();
                        
} else {
    $if_output= $_GET['if_output'] ;
    $list= !empty($_GET['list']) ? urldecode($_GET['list']) : null;
    
    $numero= urldecode($_GET['numero']);
    $fecha_entrega= urldecode($_GET['fecha_entrega']);
    $fecha_fin_plan= urldecode($_GET['fecha_fin_plan']);
    $contenido= urldecode($_GET['contenido']);
    $indicaciones= urldecode($_GET['indicaciones']);
    $if_immediate= !empty($_GET['if_immediate']) ? 1 : 0;
    $id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
}

$sender= !empty($_GET['sender']) ? urldecode($_GET['sender']) : null; 
$target= !empty($_GET['target']) ? urldecode($_GET['target']) : null; 

$nota= urldecode($_GET['nota']);
?>

<html>
    <head>
        <title>CERTIFICO</title>

        <?php include "../../../print/inc/print_top.inc.php";?>
        
            <style type="text/css">
                span.plabel {
                    font-weight: bold;
                    font-size: 1.1em;
                    margin: 6px 6px 12px 0px;
                    color: #000;
                }
                .nota {
                    padding: 10px;
                    margin-top: 5px;
                    border: 1px solid #000;
                }
            </style>
            
            <div class="page">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <span class="plabel">A:</span>
                            <?= nl2br($target)?>    
                        </div>
                    </div>      
                    <div class="row">
                        <div class="col-12">
                            <span class="plabel">De:</span>
                            <?php
                            $nombre_user= null;
                            
                            if (!$if_output) {
                                $obj_user= new Tusuario($clink);
                                $obj_user->Set($id_responsable);
                                $nombre_user= $obj_user->GetNombre();
                                $cargo_user= $obj_user->GetCargo();
                                if (!empty($cargo_user)) 
                                    $nombre_user.= ", $cargo_user";
                            }
                            
                            if ($if_output && !empty($id_responsable))
                                echo $nombre_user;
                            else {
                                if (!empty($list)) 
                                    echo $if_output ? $nombre_prs : $list;
                                else 
                                    echo nl2br ($sender);                                
                            }
                            ?>
                        </div>
                    </div> 
                    
                    <br/>
                    <div class="form-horizontal">
                         <div class="form-group row">
                            <div class="col-sm-4 col-md-3 col-lg-3">
                                <span class="plabel">Ref:</span>
                                <?=$numero?>
                            </div>
                            <div class="col-sm-3 col-md-4 col-lg-4">
                                <span class="plabel">Fecha:</span>
                                <?= substr($fecha_entrega, 0, 10)?>
                            </div>
                            <div class="col-5">
                                <span class="plabel">Fecha de Cumplimiento:</span>
                                <?=$if_immediate ? "INMEDIATA" : substr($fecha_fin_plan, 0, 10)?>
                            </div>                     
                         </div>
                    </div>    

                     <div class="row">
                        <div class="col-12">
                            <span class="plabel">Asunto:</span>
                            <?=$contenido?>
                        </div>
                    </div>           
                     <div class="row">
                        <div class="col-11">
                            <span class="plabel">Traslado a usted el asunto de referencia cuya indicación es:</span>
                            <div class="nota">
                                <?=$indicaciones?>
                            </div>    
                        </div>
                    </div>           
                     <div class="row">
                         <div class="col-12" style="width: fit-content;">
                            <span class="plabel">Nota:</span>
                            <p style="margin-top: 10px;">
                                <?=$nota?>
                            </p>

                        </div>
                    </div> 
                </div>
            </div>
    
            <?php include "../../../print/inc/print_bottom.inc.php";?>
