<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 11/07/2020
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/base.class.php";

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";

set_time_limit(0);

$execute= $_GET['execute'];

require_once "repare_id.inc";
?>

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

            <title>ACTUALIZANDO BASE DE DATOS</title>

            <?php 
            $dirlibs= "../../";
            require '../../form/inc/_page_init.inc.php'; 
            ?>    

            <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
            <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>  

            <script type="text/javascript" src="../../js/home.js"></script>

            <script type="text/javascript" src="../../js/string.js?version="></script>
            <script type="text/javascript" src="../../js/general.js?version="></script>
   
            <style type="text/css">
                /* DEFINITION LIST PROGRESS BAR */
                .progress-block .alert {
                    margin-bottom: 6px!important;
                }  
                label.label {
                    font-size: 1.2em!important;
                    letter-spacing: 0.2em;
                }
                .textlog {
                    font-size: 10px!important;
                }
            </style>

    <body>
        <div class="app-body container-fluid table" style="padding: 30px;">
            <h4>Avance del sistema </h4>
            <div id="progressbar-0" class="progress-block">
                <div id="progressbar-0-alert" class="alert alert-success">

                </div>            
                <div id="progressbar-0-" class="progress progress-striped active">
                    <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>                  
            </div>         

            <h4>Avance de la tabla:</h4>
            <div id="progressbar-1" class="progress-block">
                <div id="progressbar-1-alert" class="alert alert-warning">
                    Esta operación puede durar varios minutos. Por favor espere ... 
                </div>            
                <div id="progressbar-1-" class="progress progress-striped active">
                    <div id="progressbar-1-bar" class="progress-bar bg-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>                  
            </div>  

            <?php 
            if ($execute) {
                $_SESSION['in_javascript_block']= false;
                execute_rebuild();
            }    
            ?>        

            <?php if (empty($execute)) { ?>
            <button class="btn btn-danger mt-3" onclick="self.location.href='repare_id.php?execute=1'">EJECUTAR</button>
            <?php } ?>            
        </div>

    </body>
</html>
