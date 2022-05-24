<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 06/05/2020
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';

try {
    require_once "../../php/setup.ini.php";
    require_once _PHP_DIRIGER_DIR."config.ini";

    require_once "../../php/config.inc.php";
    require_once "../../php/class/connect.class.php";
    require_once "../../php/class/base.class.php";

    require_once "../../php/class/library.php";
    require_once "../../php/class/library_string.php";
    require_once "../../php/class/library_style.php";
    
} catch (Exception $e) {
    
}
set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;

require_once "repare_serie_archive.inc";
?>

<?php if (empty($execute_argv)) { ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

            <title>REPARAR LOS NUMEROS DE ARCHIVOS</title>

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
                .body {
                    margin: 40px 40px 0px 40px;
                    background: white;
                    padding: 20px;
                    
                    overflow-y: no-display;
                }
            </style>

    <body>
        
        <div class="container body">
            <h1>Avance del sistema </h1>
            <div id="progressbar-0" class="progress-block">
                <div id="progressbar-0-alert" class="alert alert-success">
                    En espera para iniciar
                </div>            
                <div id="progressbar-0-" class="progress progress-striped active">
                    <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only"></span>
                    </div>
                </div>                  
            </div>   
<?php } ?>
            
            <?php 
            if (!$execute_argv)
                $_SESSION['in_javascript_block']= false;
            
            if ($execute)
                execute_repare_archive();
            ?>        

            <?php if (empty($execute) && !$_SESSION['execfromshell']) { ?>
            <button class="btn btn-danger mt-3" onclick="self.location.href='repare_serie_archive.php?execute=1'">EJECUTAR</button>
            <?php } ?>
            
<?php if (empty($execute_argv)) { ?>            
        </div>

    </body>
</html>
<?php } ?>
