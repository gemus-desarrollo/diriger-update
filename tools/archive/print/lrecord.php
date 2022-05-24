<?php
/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";
session_start();

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";
require_once "../../../php/class/escenario.class.php";
require_once "../../../php/class/proceso_item.class.php";
require_once "../../../php/class/usuario.class.php";
require_once "../../../php/class/document.class.php";

require_once "../php/class/organismo.class.php"; 
require_once "../php/class/archivo.class.php"; 
require_once "../php/class/persona.class.php";
require_once "../php/class/ref_archivo.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$year= date('Y');
$date_init= !empty($_GET['date_init']) ? urldecode($_GET['date_init']) : date('d/m/Y');
$date_end= !empty($_GET['date_end']) ? urldecode($_GET['date_end']) : date('d/m/Y');
$month= date('m', strtotime($date_init));
$day= date('d', strtotime($date_init));

$if_output= $_GET['if_output'];
if (!is_null($if_output) && empty($if_output)) 
    $_if_output= null;
if ($if_output == 1) 
    $_if_output= 0;
if ($if_output == 2) 
    $_if_output= 1;

$id_organismo= !empty($_GET['id_organismo']) ? $_GET['id_organismo'] : null; 
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null; 
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
$id_persona= !empty($_GET['id_persona']) ? $_GET['id_persona'] : null; 
$keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null; 
$persona_keywords= !empty($_GET['persona_keywords']) ? urldecode($_GET['persona_keywords']) : null; 
$numero_keywords= !empty($_GET['numero_keywords']) ? urldecode($_GET['numero_keywords']) : null; 
$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : null; 

$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : null; 

$_keywords= !empty($keywords) ? preg_split("/[\s]*[,;][\s]*/" , strtolower($keywords)) : null;
$_persona_keywords= !empty($persona_keywords) ? preg_split("/[\s]*[,;][\s]*/" , strtolower($persona_keywords)) : null;
$_numero_keywords= !empty($numero_keywords) ? preg_split("/[\s]*[,;][\s]*/" , strtolower($numero_keywords)) : null;

$_keywords= array_map('trim', $_keywords);
$_persona_keywords= array_map('trim', $_persona_keywords);

$obj_ref= new Tref_archivo($clink);
$obj_user= new Tusuario($clink);

$obj= new Tarchivo($clink);

$obj->SetYear($year);
$obj->SetIfOutput($_if_output);
$obj->SetIdResponsable($id_responsable);
$obj->SetIdPersona($id_persona);
$obj->SetIdProceso($id_proceso);

$obj->limited= true;
$obj->set_init_row_temporary($init_row_temporary);
$_date_init= $date_init ? time2odbc($date_init." 00:00") : null;
$_date_end= $date_end ? time2odbc($date_end." 23:59") : null;
       
$result_archive= $obj->listar($_date_init, $_date_end, 1, null, $_keywords, $_persona_keywords, $_numero_keywords, false);  

if ($if_output == 0) 
    $title= null;
if ($if_output == 1) 
    $title= "DE ENTRADAS";
if ($if_output == 2) 
    $title= "DE SALIDAS";

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['local_proceso_id']);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$url_page= "../form/lrecord.php?signal=$signal&action=$action&menu=evento&id_proceso=$id_proceso&year=$year&month=$month&day=$day";
$url_page.= "&exect=$action&date_init=". urlencode($date_init)."&date_end=". urlencode($date_end);

set_page($url_page);
?>

<html>
    <head>
        <title>REGISTROS <?=$title?> DE ARCHIVOS</title>
        
         <?php include "../../../print/inc/print_top.inc.php";?>
            
        <div class="container-fluid center">
            <div class="title-header">REGISTROS DE ENTRADA Y SALIDAS DE DOCUMENTOS</div>
        </div>

        <div class="page center"> 
                <table cellpadding="0" cellspacing="0">
                    <thead>
                        <th>No</th>
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
                    $array_register= array();
                    $array_archivos= array();
                    $obj_ref= new Tref_archivo($clink);
                    $obj_org= new Torganismo($clink);
                    
                    $obj_ref->if_tarchivo_personas= $obj->if_tarchivo_personas;
                    
                    $array_ids= array();
                    $participantes= null;                    
                    foreach ($result_archive as $row) {
                        $id_responsable_reg= null;
                        $_id_responsable= !empty($row['_id_responsable']) ? $row['_id_responsable'] : 0;

                        if (!empty($id_responsable) && ((!empty($row['_id_responsable']) && $row['_id_responsable'] != $id_responsable) 
                                || (!empty($row['_id_usuario']) && $row['_id_usuario'] != $id_responsable))) continue;

                        if (!empty($id_responsable)) {
                            $obj->SetIdArchivo($row['_id']);
                            $obj->SetIdUsuario($id_responsable);
                            $cant= $obj->getReg();
                            if (is_null($cant)) continue;                            
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
                                if (!empty($row['cargo'])) $usuarios.= ", {$row['cargo']}";
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
                            if (is_null($row[organismo])) 
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
                            if (!$colspan) $_no_ref= $no_ref;
                    ?>
                    <tr>    
                        <td class="plinner left">
                            <?=++$i?>
                        </td>

                        <?php  if (!$colspan) { ?>      
                            <td class="plinner" rowspan="<?=$nrows?>"><?= $no_ref?></td>

                            <td class="plinner" rowspan="<?=$nrows?>">
                                <?=odbc2time_ampm($row['cronos'])?>
                                <br/>
                                <?php
                                $email= $obj_user->GetEmail($row['id_user_asigna']);
                                $cargo= !empty($mail['cargo']) ? "<br />{$mail['cargo']}" : "";
                                echo "{$email['nombre']}{$cargo}";                            
                                ?>
                            </td>
                        <?php } ?>
                             
                        <?php if (!$colspan) { ?>
                            <td class="plinner" rowspan="<?=$nrows?>">
                                <?= implode('<br/>', $row['sender']) ?>        
                            </td>
                        <?php } ?>  

                        <td class="plinner">
                            <?= count($row['target']) > 1 ? $row['target'][$j] : $row['target'][0] ?>           
                        </td>
 
                        <?php if (!$colspan) { ?> 
                            <td class="plinner" rowspan="<?=$nrows?>">
                                <?= odbc2date($row['fecha_origen'])?>
                            </td>

                            <td class="plinner" rowspan="<?=$nrows?>">
                                <?= textparse($row['descripcion'])?>                               
                            </td>

                            <td class="plinner" rowspan="<?=$nrows?>">
                                <?= textparse($row['antecedentes'])?>
                            </td>

                            <td class="plinner" rowspan="<?=$nrows?>">
                                <?php
                                $row_cump= null;

                                if (!empty($row['indicaciones'])) { 
                                    $obj->SetIdArchivo($row['_id']);
                                    $obj->SetIdUsuario($id_responsable_reg);
                                    $row_cump= $obj->getReg();
                                ?>

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
                </tbody>
            </table>
        </div>
    
    <?php include "../../../print/inc/print_bottom.inc.php";?>
