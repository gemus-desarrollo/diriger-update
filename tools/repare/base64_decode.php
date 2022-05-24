<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 11/07/14
 * Time: 7:16
 */

session_start();
/*
$_SESSION['output_signal']= 'form';

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
*/
$execute= $_GET['execute'];
$texto= !empty($_POST['texto']) ? $_POST['texto'] : null;

if ($execute && $texto)
    $texto= base64_decode($texto);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>CAMBIAR CODIFICACIÓN DE FICHERO TEXTO</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>

        <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>                   

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
        
        <script type="text/javascript">
        function ejecutar() {
            var form= document.forms[0];

            if (!Entrada(form.texto.value)) {
               alert('Debe especificar el texto a descodificar.');
               return;
            }

            var url= "base64_decode.php?execute=1";
            form.action= url;
            form.submit();  
        }
        
        </script>
    </head>

    <body>    
        <div class="container body">   
            <form class="form-horizontal" action="javascript:ejecutar()" method="POST">
                <div class="form-group row">
                    <label class="col-form-label col-1 pull-left">
                        Texto: 
                    </label>
                    <div class="col-md-10 pull-left">
                        <textarea class="form-control" name="texto" id="texto"></textarea>
                    </div>
                </div>

                <hr class="row"/>
                <h4>DESCODIFICADO</h4>
                <div class="alert alert-success">
                 <?=$texto?>
                </div> 

                <button class="btn btn-primary" type="submit" name="execute" id="exect">Ejecutar</button>                
            </form> 
        </div>
    </body>
</html>