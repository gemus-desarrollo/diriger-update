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

require_once "../../../php/config.inc.php";
require_once "../../../php/class/connect.class.php";
require_once "../../../php/class/escenario.class.php";
require_once "../../../php/class/proceso.class.php";
require_once "../../../php/class/usuario.class.php";
require_once "../../../php/class/document.class.php";

require_once "../php/class/archivo.class.php"; 
require_once "../php/class/persona.class.php";
require_once "../php/class/ref_archivo.class.php";

$id= !empty($_GET['id']) ? $_GET['id'] : null;
$procedencia= !empty($_GET['procedencia']) ? urldecode($_GET['id']) : null;

$obj_arch= new Tarchivo($clink);
$obj_arch->SetIdArchivo($id);
$obj_arch->Set();

$codigo_ref= $obj_arch->GetCodigo();
$procedencia= $obj_arch->GetAntecedentes();
$array_traze= $obj_arch->traze_archive($codigo_ref, $procedencia);

$obj_user= new Tusuario($clink);
$obj_prs= new Tproceso($clink);
?>

<html>
    <head>
        <title>HISTORIAL DEL ARCHIVO <?=$codigo_ref?></title>
        
         <?php include "../../../print/inc/print_top.inc.php"; ?>
        
        <div class="container-fluid center">
            <div class="title-header">TRAZABILIDAD DEL DOCUMENTO: <?=$codigo_ref?></div>
        </div>
    
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
            <table cellpadding="0" cellspacing="0">
                <thead>    
                    <tr>
                        <th class="plhead left">No.</th>
                        <th class="plhead left">Código</th>
                        <th class="plhead">Usuario</th>
                        <th class="plhead">Unidad Organizativa</th>
                        <th class="plhead">Fecha y Hora</th>
                    </tr>                     
                </thead>

                <tbody>
                    <?php
                    $i= 0;
                    foreach ($array_traze as $row) {
                    ?>
                    <tr>
                        <td class="plinner left"><?=++$i?></td>
                        <td class="plinner">
                            <?=$row['codigo']?>
                        </td>
                        <td class="plinner">
                            <?php
                            $email= $obj_user->GetEmail($row['id_usuario']);
                            echo $email['nombre'];
                            if (!empty($email['cargo']))
                                echo ", {$email['cargo']}";
                            ?>
                        </td>
                        <td class="plinner">  
                            <?php
                            $obj_prs->SetIdProceso($row['id_proceso']);
                            $obj_prs->Set();
                            echo $obj_prs->GetNombre();
                            if (!empty($obj_prs->GetTipo()))
                                echo $Ttipo_proceso_array[$obj_prs->GetTipo()];
                            ?>
                        </td>
                        <td class="plinner">  
                            <?= odbc2time_ampm($row['cronos'])?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>    
            </table>

        </div>        
    </div>

    <?php include "../../../print/inc/print_bottom.inc.php";?>        