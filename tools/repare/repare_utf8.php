<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 11/07/14
 * Time: 7:16
 */

session_start();

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

$execute= $_GET['execute'];
$code_utf8= !is_null($_POST['code_utf8']) ? $_POST['code_utf8'] : null;

$filename= null;
$size= 0;
$fp_source= null;
$fp_target= null;

$array_letter= array("á", "Á", "é", "É", "í", "Í", "ó", "Ó", "ú", "Ú", "ñ", "Ñ");
$array_utf8= array("Ã¡", "Ã", "Ã©", "Ã‰", "Ã­", "Ã", "Ã³", "Ã“", "Ãº", "Ã", "Ã±", "Ã");

function get_file() {
    global $filename;
    global $size;
    global $fp_source;
    
    $filename= $_FILES['textfile']['name'];
    $size= $_FILES['textfile']['size'];
    $tmp_name= $_FILES['textfile']['tmp_name'];
    
    move_uploaded_file($tmp_name, _DATA_DIRIGER_DIR."temp/".$filename);
    $fp_source= fopen(_DATA_DIRIGER_DIR."temp/".$filename, "r"); 
    return $fp_source ? true : false;
}

function open_files() {
    global $filename;
    global $fp_source;
    global $fp_target;
    
    $fp_target= fopen(_DATA_DIRIGER_DIR."temp/".$filename.".new", "w");
    fclose($fp_target);
    $fp_target= fopen(_DATA_DIRIGER_DIR."temp/".$filename.".new", "a"); 
    
    return $fp_target ? true : false;
}

function rewrite() {
    global $filename;
    global $array_letter;
    global $array_utf8;
    global $fp_source;
    global $fp_target;
    global $size;
    global $code_utf8;
    
    $size= count(file(_DATA_DIRIGER_DIR."temp/".$filename));
    
    $i= 0;
    $j= 0;
    while (($line = fgets($fp_source)) !== false) {
        ++$i;
        ++$j;
        
        if ($code_utf8)
            $new_line= str_replace($array_utf8, $array_letter, $line);
        else
            $new_line= str_replace($array_letter, $array_utf8, $line);
        fwrite($fp_target, $new_line);
        
        if ($j > 10) {
            $j= 0;
            $r= (float)$i/$size;
            $_r= number_format($r*100, 3);               
            bar_progressCSS(0, "Procesando ... $_r%", $r);        
        }        
    } 
    
    if ($j) {
        $j= 0;
        $r= (float)$i*4096/$size;
        $_r= number_format($r*100, 3);               
        bar_progressCSS(0, "Procesando ... $_r%", $r);        
    }      
    
    fclose($fp_source);
    fclose($fp_target);
}
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

            if (!Entrada(form.textfile.value)) {
               alert('Debe selecionar el fichero.');
               return;
            }

            var url= "repare_utf8.php?execute=1";
            form.action= url;
            form.submit();  
        }
        
        </script>
    </head>

    <body>    
        <div class="container body">   
            <form class="form-horizontal" action="javascript:ejecutar()" method="POST" enctype="multipart/form-data">
                <div class="form-group row">
                    <label class="col-form-label col-1 pull-left">
                        Fichero: 
                    </label>
                    <div class="col-md-5 col-lg-5 pull-left">
                        <input class="btn btn-success" type="file" name="textfile" id="textfile" />
                    </div>
                    <div class="col-md-3 col-lg-3 pull-left">
                        <select class="form-control" id="code_utf8" name="code_utf8">
                            <option value="1" <?php if ($code_utf8 == 1) echo "selected='selected'"?>>DESCODIFICAR</option>
                            <option value="0" <?php if ($code_utf8 == 0) echo "selected='selected'"?>>CODIFICAR</option>
                        </select>
                    </div>                    
                    <label class="col-form-label col-1 pull-left">
                        UTF8 
                    </label> 
                </div>

                <hr class="row"/>
                <h4>Avance del sistema </h4>
                <div id="progressbar-0" class="progress-block">
                    <div id="progressbar-0-alert" class="alert alert-warning">
                        En espera para iniciar
                    </div>            
                    <div id="progressbar-0-" class="progress progress-striped active">
                        <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                            <span class="sr-only"></span>
                        </div>
                    </div>                  
                </div> 

                <button class="btn btn-primary" type="submit" name="execute" id="exect">Ejecutar</button>                
                
            </form>

            <?php
            $_SESSION['execfromshell']= false;
            $_SESSION['in_javascript_block']= false;

            if ($execute) {  
                $result= get_file();
                if ($result)
                    $result= open_files();
                else {
                ?>
                    <div class="alert alert-danger" style="margin-top: 30px;">
                        Se ha producido un error leyendo el fichero origen.
                    </div>            
                <?php
                }
                
                if ($result) {
                    rewrite();
            ?>    
                <div class="alert alert-success" style="margin-top: 30px;">
                    <a href="<?="file:///"._DATA_DIRIGER_DIR."temp/".$filename.".new"?>"><?=$filename.".new"?></a>
                </div>
            <?php } } ?>            
            
        </div>
    </body>
</html>