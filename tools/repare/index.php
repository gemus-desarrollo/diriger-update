<?php
session_start();

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
            
            <title>HERRAMIENTAS DEL SISTEMA</title>
        
            <?php 
            $dirlibs= "../../";
            require '../../form/inc/_page_init.inc.php'; 
            ?>

            <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
            <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>               
            
            <link rel="stylesheet" href="../../libs/alert-panel/alert-panel.css">
            <script type="text/javascript" src="../../libs/alert-panel/alert-panel.js"></script>       

            <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
            <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>  

            <script type="text/javascript" src="../../js/home.js"></script>

            <script type="text/javascript" src="../../js/string.js?version="></script>
            <script type="text/javascript" src="../../js/general.js?version="></script> 
            
            <style type="text/css">
                td {
                    margin: 10px!important;
                }
            </style>
    </head>
    
    <body class="table">
        <!-- Docs master nav -->
        <div id="navbar-secondary">
            <nav class="navd-content">
                <div class="navd-container">
                    <div id="dismiss" class="dismiss">
                        <i class="fa fa-arrow-left"></i>
                    </div>   
                    <a href="#" class="navd-header">
                        HERRAMIENTAS DE REPARACIÓN
                    </a>

                    <div class="navd-menu" id="navbarSecondary">
                        <ul class="navbar-nav mr-auto">
                        </ul>
                    </div>
                </div>    
            </nav>
        </div>

        <div class="app-body container-fluid onebar">
            <div class="container">
                <blockquote style="margin-top: 30px;">
                    <table class="table" cellpadding="4px" cellspacing="0" border="0">
                        <tr>
                            <td class="align-top">
                                <a href="repare_id_user_ldap.php">repare_id_user_ldap</a>
                            </td>
                            <td>
                                <p>
                                    Identificar e intentar eliminar los usuarios que aparecen duplicados por la conexion al servidor LDAP.
                                    Mustra todos los usuarios
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="repare_duplicate_user.php">repare_duplicate_user</a>
                            </td>
                            <td>
                                <p>
                                    Elimina un usuario que este duplicado en el sistema. Unifica la informacion de los dos usuarios 
                                    antes de eliminar uno de los dos.
                                </p>
                            </td>
                        </tr>    
                        <tr>
                            <td class="align-top">
                                <a href="repare_serie_archive.php">repare_serie_archive</a>
                            </td>
                            <td>
                                <p>
                                    Reordena el numero de serie o numeracion continua de los archivos, registrados en la ofcina de archivo
                                    de las UEB o direcciones funcionales.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="repare_id.php">repare_id</a>
                            </td>
                            <td>
                                <p>
                                    Cambia el tamaño de los campos id_code a 12 caracteres y extiende la cadena id_code.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="repare_id_code.php">repare_id_code</a>
                            </td>
                            <td>
                                <p>
                                    Actualiza todos los campos id_(articulo) a partir del valor de los id_(articulos)_code no vacios.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="repare_utf8.php">repare_utf8</a>
                            </td>
                            <td>
                                <p>
                                    Actualiza los textos de un fichero. Cambiando la codificación a utf8 o latin1 segun se requiera.
                                    la base de datos debe descargarce con el <b>phpmyadmin</b> y posterior al cambio, subirse con el mismo <b>phpmyadmin</b>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="phpCrypt.php">phpCrypt</a>
                            </td>
                            <td>
                                <p>
                                    Para ver el contenido de los lotes comprimidos y encriptados. Los ficheros desencriptado y descomprimido 
                                    aparecen en la carpeta data/temp/
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="base64_decode.php">base64_decode</a>
                            </td>
                            <td>
                                <p>
                                    Descodifica cadenas codificada en base 64 bits
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="union_db/index.php">union_db</a>
                            </td>
                            <td>
                                <p>
                                    Une 2 base de datos para crear un solo sistema con la nueva entidad 
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">
                                <a href="repare_sql.php">repare_sql_function</a>
                            </td>
                            <td>
                                <p>
                                    Ejecuta sentencias SQL o funciones especificas para la ctualización del sistema
                                </p>
                            </td>
                        </tr>                    
                        <tr>
                            <td class="align-top">
                                <a href="repare_tproceso_eventos.php">repare_tproceso_eventos</a>
                            </td>
                            <td>
                                <p>
                                    Purga la tabal tproceso_eventos eliminando registros redundantes y achicando la tabla
                                </p>
                            </td>
                        </tr> 
                        <tr>
                            <td class="align-top">
                                <a href="plantilla_add_to_grupo.php">plantilla_add_al_grupo</a>
                            </td>
                            <td>
                                <p>
                                    Incorpora al Grupo de Trabajo a aquellos usuarios que participan de las actividades del grupo de trabajo.
                                    para aquellas empresas que tienen estructura la plantilla a nivel de grupos de trabajo.
                                </p>
                            </td>
                        </tr>      
                        <tr>
                            <td class="align-top">
                                <a href="repare_user_ldap_deleted.php">repare_user_ldap_deleted</a>
                            </td>
                            <td>
                                <p>
                                    Reincorpora los usuarios que han sido eliminados. Si los usuarios son del LDAP, estos serán reconectados. 
                                </p>
                            </td>
                        </tr>  
                        <tr>
                            <td class="align-top">
                                <a href="repare_id_tipo_evento_proceso.php">repare_id_tipo_evento_proceso</a>
                            </td>
                            <td>
                                <p>
                                    Corrige los id_tipo_evento en la tabla tproceso_eventos. Para ubicar correctamente en los Planes Anuales 
                                    y Mensuales de actividades. No utilizar en sistemas con entidades.
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <td class="align-top">
                                <a href="create_plantilla_from_grupo.php">create_plantilla_from_grupo</a>
                            </td>
                            <td>
                                <p>
                                    Corrige el posible error que se pierdan los usuarios de las ueb, direcciones y grupos de trabajo, 
                                    a pesar que estos estan en grupos de usuarios asignados a estos procesos.
                                </p>
                            </td>
                        </tr>  
                        
                        <tr>
                            <td class="align-top">
                                <a href="push_default_values_to_db.php?execute=1">push_default_values_to_db</a>
                            </td>
                            <td>
                                <p>
                                    Llenar la base de datos con los valores por defecto, despues que esta se vacia por razones de desarrolo o de prueba.
                                </p>
                            </td>
                        </tr> 
                        
                        <tr>
                            <td class="align-top">
                                <a href="repare_duplicate_register_table.php">repare_duplicate_register_table</a>
                            </td>
                            <td>
                                <p>
                                    Eliminar la duplicidad de registros para crear los indices unicos o restriciones en una tabla.
                                </p>
                            </td>
                        </tr>   

                        <tr>
                            <td class="align-top">
                                <a href="repare_copy_evento_to_year.php">repare_copy_evento_to_year</a>
                            </td>
                            <td>
                                <p>
                                    Corrige el error que se producce al copiar de un anno para otro. 
                                    Lo que sucede es que las tareas no cayeron en el mismo capitulo que la original.
                                    Solo es valido mpara la copia de la empresa, no para los grupos.
                                </p>
                            </td>
                        </tr>                                                   
                    </table>                  
                </blockquote>
            
            </div>        
        </div>

    </body>
</html>
