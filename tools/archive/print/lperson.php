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

include_once "../php/class/organismo.class.php";
include_once "../php/class/persona.class.php";

$id_organismo= !empty($_GET['id_organismo']) ? $_GET['id_organismo'] : null; 
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null; 
$id_prov= !empty($_GET['id_prov']) ? $_GET['id_prov'] : "0";
$id_mcpo= !empty($_GET['id_mcpo']) ? $_GET['id_mcpo'] : "0";
$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : null; 

$keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null; 
$keywords= !empty($keywords) ? preg_split("/[\s]*[,;][\s]*/" , $keywords) : null;

$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : null; 

$obj_user= new Tusuario($clink);
$obj_user= new Torganismo($clink);
?>

<html>
    <head>
        <title>REGISTROS <?=$title?> DE ARCHIVOS</title>
        
         <?php include "../../../print/inc/print_top.inc.php";?>

            <table>
                <thead>
                    <th>No</th>
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
                    $personas= $obj_pers->listar();
                    $i= 0;
                    while ($row= $clink->fetch_array($personas)) {
                    ?>
                        <tr>
                            <td>
                                <?=++$i?>
                            </td>

                            <td>
                                <?php
                                    echo textparse($row['nombre']);
                                    if (!empty($row['cargo'])) echo "<br/>". textparse($row['cargo']);
                                ?>                            
                            </td>
                            <td>
                                <?php 
                                if (!empty($row['id_organismo'])) {
                                    $obj_org->Set($row['id_organismo']);
                                    echo $obj_org->GetNombre();
                                }
                                ?>
                            </td>
                            <td>
                                <?=$row['noIdentidad']?>        
                            </td>
                            <td>
                                <?php
                                echo Tarray_municipios[$row['provincia']][1][$row['municipio']];
                                if (!empty($row['municipio'])) echo "<br/>";
                                if (!empty($row['provincia'])) echo $Tarray_provincias[$row['provincia']];
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
    
    <?php include "../../../print/inc/print_bottom.inc.php";?>
