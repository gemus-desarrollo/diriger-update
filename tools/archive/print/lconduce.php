<?php
/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

session_start();
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";

include_once "../../../php/config.inc.php";
include_once "../../../php/class/connect.class.php";
include_once "../../../php/class/escenario.class.php";
include_once "../../../php/class/proceso.class.php";
include_once "../../../php/class/usuario.class.php";
include_once "../../../php/class/document.class.php";

include_once "../php/setup.ini.php"; 
include_once "../php/class/archivo.class.php"; 
include_once "../php/class/persona.class.php";
include_once "../php/class/ref_archivo.class.php";

$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null;
$id_ejecutante= !empty($_GET['id_ejecutante']) ? $_GET['id_ejecutante'] : null;
$id_usuario= $_SESSION['id_usuario'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$date_init= !empty($_GET['date_init']) ? urldecode($_GET['date_init']) : date('d/m/Y');
$date_end= !empty($_GET['date_end']) ? urldecode($_GET['date_end']) : date('d/m/Y');
$numero_keywords= !empty($_GET['numero_keywords']) ? $_GET['numero_keywords'] : null; 
$keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null; 

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y', strtotime(date2odbc($date_init)));
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
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null; 

$obj_evento= new Tevento($clink);
$obj_user= new Tusuario($clink);
$obj_ref= new Tref_archivo($clink);

$obj= new Tarchivo($clink);
$obj->SetYear($year);
$obj->SetIfOutput($_if_output);

$_keywords= $keywords ? preg_split("/[\s]*[,;][\s]*/" , strtolower($keywords)) : null;
$_numero_keywords= $numero_keywords ? preg_split("/[\s]*[,;][\s]*/" , strtolower($numero_keywords)) : null;
$obj->do_filter_by_fin_plan= true;
$obj->limited= false;
$result_register= $obj->listar(time2odbc($date_init." 00:00:00"), time2odbc($date_end." 23:59:00"), 2, null, $_keywords, null, $_numero_keywords);
$array_archivos= $obj->array_archivos;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($_SESSION['local_proceso_id']);
$lugar= $obj_prs->GetLugar();
$proceso= $obj_prs->GetNombre();

unset($obj_prs);
?>

<html>
    <head>
        <title>ESTADO DE CUMPLIMIENTO DE LAS INDICACIONES</title>
        
         <?php include "../../../print/inc/print_top.inc.php";?>
        
        <style type="text/css">
            table {
                width: 100%;
                border: none;
                margin-top: 10px;
            }
            td {
                border: none;
                padding: 4px;
            }
            td.th {
                width: 12%;
            }
            td.bold {
                font-weight: bold;
            }
            hr.line {
                margin: 0px;
                border: 1px solid black;
            }
        </style>
        
    <div class="page">
        <div class="text-center">
            <?php
            $total= 0;
            $cumplida= 0;
            $incumplida= 0;
            $suspendida= 0; 
            $encurso= 0;
            $array_archivo_ids= array();
            foreach ($result_register as $row) {
                if (array_key_exists($row['_id'], (array)$array_archivo_ids)) 
                        continue;

                if (!empty($id_organismo)) {
                    if (empty($row['id_organismo'])) 
                        continue;
                    if ($row['id_organismo'] != $id_organismo) 
                        continue;
                }  
                
                if (!empty($id_responsable)) {
                    $obj->SetIdArchivo($row['_id']);
                    $obj->SetIdUsuario($id_responsable);
                    $cant= $obj->getReg();
                    if (empty($cant)) 
                        continue;                            
                }   

                $id = $row['_id'];
                $array_archivo_ids[$row['_id']] = $row['_id'];

                $obj_ref->SetIdArchivo($row['_id']);
                $obj_ref->_get_user();
                $array_usuarios = $obj_ref->array_usuarios;

                foreach ($array_usuarios as $user) {
                    ++$i;
                    $obj->SetIdArchivo($row['_id']);
                    $obj->SetIdUsuario($user['id']);
                    $row_cump= $obj->getReg();  

                    if ((!empty($id_ejecutante) && !empty($user['id'])) && $id_ejecutante != $user['id']) 
                        continue;    
                 
                    ++$total;
                    switch ($row_cump['cumplimiento']) {
                        case _COMPLETADO:
                            ++$cumplida;
                            break;
                        case _INCUMPLIDO:
                            ++$incumplida;
                            break;
                        case _POSPUESTO:
                        case _SUSPENDIDO:
                        case _CANCELADO:
                            ++$suspendida;
                            break;
                        case _NO_INICIADO:
                        case _EN_CURSO:
                        case _ESPERANDO:
                            ++$encurso;
                            break;
                    }
                }
            }
            ?>
            
            <?php 
            if ($total && $total > 1) { 
                $date_init.= " 12:00 AM";
                $date_end.= " 11:59 PM";                
            ?>
                <table cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="plhead left top" colspan="5">
                            Período: <span style="font-weight: normal"><?="$date_init hasta $date_end"?></span>
                            <?php 
                                if (!empty($id_responsable)) { 
                                    $mail= $obj_user->GetEmail($id_responsable);
                                    $nombre= $mail['nombre'];
                                    $cargo= $mail['cargo'];
                                    if (!empty($cargo)) 
                                        $nombre.= ', '.$cargo;                                
                                ?>
                                <br/>Responsable:
                                <span style="font-weight: normal"><?=$nombre?></span>                                
                            <?php } ?>
                        </th>
                    </tr>
                    <tr>
                        <th class="plhead left" rowspan="2">Total<br/>planificadas</th>
                        <th class="plhead" colspan="4">De ellas</th>
                    </tr>    
                    <tr>
                        <th class="plhead left">Cumplidas</th>
                        <th class="plhead">Incumplidas</th>
                        <th class="plhead">Supendias o Pospuetas</th>
                        <th class="plhead">En curso</th>
                    </tr>                     
                </thead>

                    <tbody>
                        <tr>
                            <td class="plinner left"><?=$total?></td>
                            <td class="plinner">
                                <?=$cumplida?>
                                <?php
                                    if ($total) {
                                        $r= ((float)$cumplida/$total)*100;
                                        echo '   ('.number_format($r, 1, '.', '') . '%)';
                                    }
                                ?>
                            </td>
                            <td class="plinner">
                                <?=$incumplida?>
                                <?php
                                    if ($total) {
                                        $r= ((float)$incumplida/$total)*100;
                                        echo '   ('.number_format($r, 1, '.', '') . '%)';
                                    }
                                ?>                        
                            </td>
                            <td class="plinner">
                                <?=$suspendida?>
                                <?php
                                    if ($total) {
                                        $r= ((float)$suspendida/$total)*100;
                                        echo '   ('.number_format($r, 1, '.', '') . '%)';
                                    }
                                ?>                        
                            </td>
                            <td class="plinner">
                                <?=$encurso?>
                                <?php
                                    if ($total) {
                                        $r= ((float)$encurso/$total)*100;
                                        echo '   ('.number_format($r, 1, '.', '') . '%)';
                                    }
                                ?>                        
                            </td>
                        </tr>
                    </tbody>    
                </table>
                <br/>
                <hr class='line'/>            
            <?php } ?>

            <?php
            reset($result_register);
            $i = 0;
            $array_archivo_ids = array();
            foreach ($result_register as $row) {
                if (array_key_exists($row['_id'], (array)$array_archivo_ids)) 
                    continue;
                
                if (!empty($id_organismo)) {
                    if (empty($row['id_organismo'])) 
                        continue;
                    if ($row['id_organismo'] != $id_organismo) 
                        continue;
                }                
                
                if (!empty($numero)) {
                    $no_ref= $row['codigo'];
                    if (stripos($no_ref, $numero) === false) 
                        continue;
                }
                
                if (!empty($id_responsable)) {
                    $obj->SetIdArchivo($row['_id']);
                    $obj->SetIdUsuario($id_responsable);
                    $cant= $obj->getReg();
                    if (empty($cant)) 
                        continue;                            
                }   

                $id = $row['_id'];
                $array_archivo_ids[$row['_id']] = $row['_id'];

                $obj_ref->SetIdArchivo($row['_id']);
                $obj_ref->_get_user();
                $array_usuarios = $obj_ref->array_usuarios;

                foreach ($array_usuarios as $user) {
                    ++$i;
                    $obj->SetIdArchivo($row['_id']);
                    $obj->SetIdUsuario($user['id']);
                    $row_cump= $obj->getReg();  

                    if ((!empty($id_ejecutante) && !empty($user['id'])) && $id_ejecutante != $user['id']) 
                        continue;
                    if (!empty($cumplimiento) && $cumplimiento != $row_cump['cumplimiento']) 
                        continue;
                    
                    if ($i > 1) 
                        echo "<hr class='line'/>";
            ?> 

                    <table>
                        <tr>
                            <td class="th bold">Documento:</td>
                            <td class="">
                                <?php
                                $no_ref= boolean($row['if_output']) ? "RS-" : 'RE-';
                                $no_ref.= str_pad($row['numero'],6,"0",STR_PAD_LEFT).'-'.$row['year'];
                                if ($array_codigo_archives[$row['id_proceso']])
                                    $no_ref.= "-{$array_codigo_archives[$row['id_proceso']]}";   
                                echo $no_ref;    
                                ?>
                            </td>

                            <td class="th bold">Remitente:</td>
                            <td class="">
                                <?=$array_archivos[$row['_id']]['sender']?>                        
                            </td>
                        </tr>
                        <tr>
                            <td class="th bold">Fecha Doc:</td>
                            <td class="">
                                <?= odbc2date($row['fecha_origen'])?>
                            </td>

                            <td class="th bold">Procedencia:</td>
                            <td class="">
                                <?php
                                $sender= $obj_ref->GetOrganismo();
                                if (!empty($sender)) 
                                    $sender= $obj_ref->GetOrganismo();
                                $lugar= $obj_ref->GetLugar();
                                if (!empty($lugar)) 
                                    $sender.= !empty($sender) ? ", $lugar" : $lugar;   
                                echo $sender;
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="th bold">Ejecuta:</td>
                            <td colspan="3" class="">
                                <?php
                                $mail= null;
                                if (!empty($row['id_responsable'])) {
                                     $mail= $obj_user->GetEmail($user['id']);
                                     $nombre= $mail['nombre'];
                                     $cargo= $mail['cargo'];
                                     if (!empty($cargo)) 
                                         $nombre.= ', '.$cargo;
                                     echo $nombre;
                                }  
                                ?> 
                            </td>
                        </tr>

                        <tr>
                            <td class="th bold">Fecha Int:</td>
                            <td class="">
                                <?= odbc2date($row['fecha_entrega'])?>
                            </td>

                            <td class="th bold">Contenido:</td>
                            <td class="">
                                <?= textparse($row['descripcion'])?>
                            </td>
                        </tr>

                        <tr>
                            <td class="th bold">Fecha Cump:</td>
                            <td class="">
                                <?= odbc2date($row['fecha_fin_plan'])?>
                            </td>

                            <td class="th bold">
                                Estado: 
                            </td>
                            
                            <td class="">
                                <label class="alarm <?=$eventos_cump_class[$row_cump['cumplimiento']]?>"><?=$eventos_cump[$row_cump['cumplimiento']]?></label>
                            </td>
                        </tr>

                        <tr>
                            <td class="th bold">Indicación:</td>
                            <td class="">
                                <?= textparse($row['indicaciones'])?>
                            </td>
                        </tr>
                        <tr>
                            <td class="th bold">Observaciones:</td>
                            <td class="">
                                <?= textparse($row_cump['observacion'])?>
                            </td>                            
                        </tr>
                    </table> 
                    <br/>
            <?php } } ?>        
       
        </div>        
    </div>

    <?php include "../../../print/inc/print_bottom.inc.php";?>        