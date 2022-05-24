<?php
/**
 * @author muste
 * @copyright 2013
 */


set_time_limit(432000);
session_cache_expire(720);
session_start();  

include_once "../../php/config.inc.php";
include_once "../../php/setup.ini.php";
include_once "../../php/class/config.class.php";

$_SESSION['trace_time']= 'no';
$_SESSION['debug']= 'no';

include_once _PHP_DIRIGER_DIR."config.ini";
include_once "../../inc.php";
include_once "../../php/config.inc.php";
include_once "../../php/class/connect.class.php";
include_once "../../php/class/time.class.php";

include_once "update.class.php";

include_once "../../php/class/pop3/pop3.class.php";
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>ACTUALIZACION DEL SISTEMA POR EMAIL</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>    

        <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
        <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>  
        
        <script type="text/javascript" src="../../js/home.js"></script>

        <script type="text/javascript" src="../../js/string.js?version="></script>
        <script type="text/javascript" src="../../js/general.js?version="></script>

        <script type="text/javascript" src="../../js/form.js?version="></script> 
        
        <style type="text/css">
            .panel-body .form-group {
                margin-left: 20px;
                margin-right: 20px;
            }

            .winlog {
                max-height: 450px;
                min-height: 200px;
            }            
        </style> 
        
        <script type="text/javascript">
            parent.app_menu_functions= false;
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">ACTUALIZANDO ARCHIVOS DEL SISTEMA</div>
                <div class="card-body"> 
                    
                     <div id="divlog" class="container-fluid winlog">
                        <div id="winlog" class="textlog"> 
                            <?php
                            $cant_upgrades= 0;
                            $result= null;

                            $obj= new Tupdate_sys($clink);

                            if ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $obj->test_winrar()) || strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                                $result= $obj->get_emails();
                                $error= $obj->error;
                            }
                            elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                $error= "El ejecutable winrar.exe no ha sido encontrado. Por favor, asegurase que la aplicación WinRAR esté instalada en el ";
                                $error.= "servidor y que la ruta este definida en el fichero de configuración config.ini.";
                            }

                            if (is_null($error) && $result) {
                                foreach ($obj->array_files as $update) {
                                    $error= $obj->copy_src($update['pack']);
                                    if (!is_null($error)) break;

                                    ++$cant_upgrades;
                                    $obj->DeleteMail($update['email_number']);
                                }
                            }
                            ?>
                        </div>
                     </div>
                    
                    <?php
                    if (is_null($error)) $obj->Close();
                    else { ?>
                        <div class="alert alert-danger"><?= $error?></div>
                    <?php } ?>

                    <?php if (!is_null($error) && $result) $error= null; ?>
                        
                    <?php if (is_null($error) && $cant_upgrades > 0) { ?>
                        <script language='javascript' type="text/javascript" charset="utf-8">
                            self.location.href= '../../html/home.php?action=update';
                        </script>
                    <?php } elseif (!is_null($error)) { ?>
                        <div class="alert alert-danger">
                            Ha ocurrido un error durante la actualización de los archivos del sistema. Por favor, copie el error y comuníquese inmediatamente con el personal de GEMUS. 
                            <br/><strong>e-Correo:</strong> gemus@nauta.cu;   
                            <br/><strong>teléfono:</strong> 58200755 / 53740039
                        </div>
                    <?php } ?> 
                        
                </div> <!-- panel-body -->                      
            </div> <!-- panel -->
        </div>  <!-- container -->      
    </body>
</html>        
 