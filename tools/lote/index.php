<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
include_once "../../php/setup.ini.php";
include_once "../../php/class/config.class.php";
$_SESSION['debug']= 'no';

include_once "../../inc.php";
include_once _PHP_DIRIGER_DIR."config.ini";
include_once "../../php/config.inc.php";
include_once "../../php/class/base.class.php";

$opage= !empty($_GET['opage']) ? $_GET['opage'] : NULL;
$action= !empty($_GET['action']) ? $_GET['action'] : 'resume';
$signal= !empty($_GET['signal']) ? $_GET['signal']: 'home';
$file= !empty($_GET['file']) ? $_GET['file'] : NULL;
$email= !empty($_GET['email']) ? $_GET['email'] : null;
$sendmail= $_GET['sendmail'];

$destino= $_GET['destino'];
$observacion= $_GET['observacion'];
$fecha= $_GET['fecha'];
$lote= $_GET['lote'];
//if (!is_null) $lote= urldecode($lote);
$error= !empty($_GET["error"]) ? $_GET["error"] : null;
$tmp_name = $_GET["tmp_name"];

$exect= $_GET['exect'];

if ($action == 'export') {
    $url = "php/export.interface.php";
}
if ($action == 'import') {
    $url = "php/import.interface.php";
}
if ($action == 'upload' || $action == 'download') {
    $url = "form/flote.php";
}
if ($action == 'resume') {
    $url = "form/resume.php";
}

$url.= "?signal=$signal&action=$action&file=$file&email=$email&lote=$lote&fecha=$fecha&observacion=$observacion";
$url.= "&exect=$exect&destino=$destino&tmp_name=$tmp_name&error=$error&sendmail=$sendmail";

if (!is_null($error)) 
    $error= urldecode($error);
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        
        <title>MODULO PARA LA TRANSFERENCIA Y ACTUALIZACION DE DATOS</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>      
  
        <link rel="stylesheet" type="text/css" href="../../css/general.css?version=">

        <script type="text/javascript" src="../../js/string.js?version="></script>
        <script type="text/javascript" src="../../js/general.js?version="></script>

        <script type="text/javascript" src="../../js/form.js?version="></script> 

        <style type="text/css">
            body {
                height: 100vh!important;
                overflow: hidden;
            }
        </style>   
        
        <script language="javascript" type="text/javascript">
            function ejecutar(action) {
                var _action= $('#exect').val();
                var signal;
                var text;

                if (_action == 'import' || _action == 'export') {
                    text= "Se esta realizando una operación de transferencia de datos. Espere por favor...... ";
                    text+= "Sí detiene el proceso pueden ocurrir perjuicios a la integridad delos datos.";
                    alert(text);
                    return;
                }

                if (action == 'export' || action == 'import') 
                    signal='home';
                if (action == 'download' || action == 'upload') 
                    signal='form';

                if (action == 'resume') {
                    $('#mainWin').attr('src', "form/resume.php");
                } 
                if (action != 'resume' && signal == 'form') {
                    $('#mainWin').attr('src', "form/flote.php?signal="+signal+'&action='+action);
                }
                if (action == 'export' && signal == 'home') {
                    $('#mainWin').attr('src', "form/flote_full.php?signal="+signal+'&action='+action);
                }                
                if (action == 'import' && signal == 'home') {
                    $('#mainWin').attr('src', "php/import.interface.php?signal="+signal+'&action='+action);
                }                
            }
        </script>
        
        <script type="text/javascript">
            $(document).ready(function() {
                $('#mainWin').attr('src', "<?=$url?>");
               
                <?php if (!is_null($error)) {?>
                    alert('<?=$error?>');
                <?php } ?>
            });
        </script>        
    </head>


    <body>
        <script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <!-- Fixed navbar -->
        <div id="navbar-secondary">
            <nav class="navd-content">
                <div class="navd-container">
                    <div id="dismiss" class="dismiss">
                        <i class="fa fa-arrow-left"></i>
                    </div>  

                    <a href="#" class="navd-header">TRANSFERENCIA DE DATOS</a>

                    <div class="navd-menu" id="navbarSecondary">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item" id="btn_inicio">
                                <a href="#" onclick="ejecutar('resume')">
                                    <i class="fa fa-home"></i>Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a id="btn_salida" href="#" onclick="ejecutar('download')">
                                    <i class="fa fa-download"></i>Exportar (1)
                                </a>
                            </li>                        
                            <li class="nav-item">
                                <a id="btn_salida" href="#" onclick="ejecutar('upload')">
                                    <i class="fa fa-upload"></i>Importar (1)
                                </a>
                            </li>
                            <?php if ($config->type_synchro == _SYNCHRO_AUTOMATIC_EMAIL) { ?>                        
                            <li class="nav-item">
                                <a id="btn_send_all" href="#" onclick="ejecutar('export')">
                                    <i class="fa fa-wifi"></i>Enviar (todos)
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($config->type_synchro == _SYNCHRO_AUTOMATIC_EMAIL || $config->type_synchro == _SYNCHRO_AUTOMATIC_HTTP) { ?>                        
                            <li class="nav-item">
                                <a id="btn_send_all" href="#" onclick="ejecutar('import')">
                                    <i class="fa fa-wifi"></i>Recibir (Todos)
                                </a>
                            </li>  
                            <?php } ?>
                        </ul>
                        
                        <div class="navd-end">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item">
                                    <a href="#"  onclick="open_help_window('../../help/18_herramientas.htm#18_30.4')">
                                        <i class="fa fa-question"></i>Ayuda
                                    </a>
                                </li>
                            </ul>
                        </div>                    
                    </div><!--/.nav-collapse -->

                </div>
            </nav>
        </div>



        <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
        <input type="hidden" name="nivel" id="nivel" value="<?=$_SESSION['nivel']?>" />
        
        <iframe id="mainWin" class="app-body container-fluid eightbar"></iframe> <!-- /container -->

    </body>
</html>        
