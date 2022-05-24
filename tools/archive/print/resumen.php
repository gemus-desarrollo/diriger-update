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

include_once "../php/class/archivo.class.php"; 
include_once "../php/class/persona.class.php";
include_once "../php/class/ref_archivo.class.php";

$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null;
$id_usuario= $_SESSION['id_usuario'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$date_init= !empty($_GET['date_init']) ? urldecode($_GET['date_init']) : date('d/m/Y')." 12:00 AM";
$date_end= !empty($_GET['date_end']) ? urldecode($_GET['date_end']) : date('d/m/Y')." 11:59 PM";
$month= date('m', strtotime($date_init));
$day= date('d', strtotime($date_init));

$if_output= $_GET['if_output'];
if (!is_null($if_output) && empty($if_output)) $_if_output= null;
if ($if_output == 1) $_if_output= 0;
if ($if_output == 2) $_if_output= 1;

$organismo= !empty($_GET['organismo']) ? $_GET['organismo'] : null; 
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null; 

$obj_evento= new Tevento($clink);
$obj_user= new Tusuario($clink);
$obj_ref= new Tref_archivo($clink);

$obj= new Tarchivo($clink);
$obj->SetYear($year);
$obj->SetIfOutput($_if_output);

$result_archive= $obj->listar(time2odbc($date_init), time2odbc($date_end), false);

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
        
    <div class="page">
        <div class="text-center">
            <div>
                RESUMEN DEL CUMPLIMIENTO DE LAS INDICACIONES 
            </div>
            
            <table> 
               <thead>
                   <tr>
                       <th>No.</th>
                       <th>Cumplimiento</th>
                       <th>Indicación</th>
                       <th>Origen</th>
                       <th>Responsable</th>
                       <th>Fecha<br />Cumplimiento</th>
                       <th>Registro</th>
                   </tr>
               </thead>
               
               <tbody>
                    <?php
                    $i = 0;
                    $array_archivos = array();
                    foreach ($result_archive as $row) {
                        if (!empty($organismo)) {
                            if (isset($obj_pers)) unset($obj_pers);
                            $obj_pers= new Tpersona($clink);
                            $obj_pers->Set($row['id_persona']);

                            if ($obj_pers->GetOrganismo() != $organismo) continue;                                
                        }

                        if (array_key_exists($row['_id'], (array)$array_archivos)) continue;

                        if (!empty($id_responsable)) {
                            $obj->SetIdArchivo($row['_id']);
                            $obj->SetIdUsuario($id_responsable);
                            $cant= $obj->getReg();
                            if (is_null($cant)) continue;                            
                        }   

                        $id = $row['_id'];
                        $array_archivos[$row['_id']] = $row['_id'];

                        $obj_ref->SetIdArchivo($row['_id']);
                        $obj_ref->_get_user();
                        $array_usuarios = $obj_ref->array_usuarios;

                        foreach ($array_usuarios as $user) {
                            ++$i;
                            $obj->SetIdArchivo($row['_id']);
                            $obj->SetIdUsuario($user['id']);
                            $row_cump= $obj->getReg();  
                            
                            if (!empty($cumplimiento) && $cumplimiento != $row_cump['cumplimiento']) continue;
                    ?> 
                            <tr>
                                <td>
                                    <?=$i?>
                                </td>
                                <td>
                                    <?=$eventos_cump[$row_cump['cumplimiento']]?>
                                </td>
                                <td>
                                    <?=$row['indicaciones']?>
                                </td>
                                <td>
                                    <?php                                   
                                     if (isset($obj_ref)) unset($obj_ref);
                                     $obj_ref= new Tref_archivo($clink);
                                     $_id= !empty($row['id_destinatario']) ? $row['id_destinatario'] : $row['id_remitente'];
                                     $obj_ref->Set($_id);

                                     $nombre= $obj_ref->GetNombre().', '.$obj_ref->GetCargo();
                                     $organismo= $obj_ref->GetOrganismo();
                                     if (!empty($organismo)) $nombre.= "<p>".$organismo.'</p/>';

                                     echo $nombre;
                                    ?>                                     
                                </td>
                                <td>
                                    <?php
                                    $mail= null;
                                    if ($user['id'] != _USER_SYSTEM) {
                                         $mail= $obj_user->GetEmail($user['id']);
                                         echo $mail['nombre'].'<br />'.$mail['cargo'];
                                    }  
                                    ?>           
                                </td>
                                <td>
                                    <?= odbc2time_ampm($row['fecha_fin_plan'])?>
                                </td>
                                <td>
                                    <?= odbc2time_ampm($row['fecha_entrega'])?>
                                </td>
                            </tr>
                    <?php } } ?>        
               </tbody>
           </table>         
        </div>        
    </div>

    <?php include "../../../print/inc/print_bottom.inc.php";?>        