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

require_once "../dbtools/update.functions.php";
require_once "../dbtools/repare.class.php";

set_time_limit(0);

$execute= $_GET['execute'];
$texto= !empty($_POST['texto']) ? $_POST['texto'] : null;
$funcion= !empty($_POST['funcion']) ? $_POST['funcion'] : null;

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>EJECUTAR SQL Y FUNCIONES DE REPARACIÓN</title>

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
                margin: 10px 10px 0px 10px;
                background: white;
                padding: 20px;

                overflow-y: no-display;
            }
            body {
                background: white;
            }            
        </style> 
        
        <script type="text/javascript">
        function ejecutar() {
            var form= document.forms[0];

            if (!Entrada(form.texto.value) && form.funcion.value == 0) {
               alert('Debe insertar el código SQL o la función a ejecutar.');
               return;
            }

            var url= "repare_sql.php?execute=1";
            form.action= url;
            form.submit();  
        }
        
        function limpiar() {
            $('#texto').val('');
            $('#funcion').val(0);
        }
        
        var cicle;
        var noLine= 0;
        var _moveScroll= true;

        function writeLog(time, line) {
            ++noLine;

            var _tr= document.createElement('tr');
            var _td1= document.createElement('td');
            var _td2= document.createElement('td');

            _td1.innerHTML= '<strong>'+noLine+' ---><br/>  '+time+'</strong>';
            _td2.innerHTML= line;
            _tr.appendChild(_td1);
            _tr.appendChild(_td2);

            document.getElementById('winlog-table').appendChild(_tr);

            moveScrollLog();
        }
        function moveScrollLog() {
            if (!_moveScroll) 
                return;

            var _this= document.getElementById('divlog');
            var _this_inner= document.getElementById('winlog');

            _this.scrollTop= _this_inner.scrollHeight;
        }        
        </script>
    </head>

    <body>    
        <?php include "../../inc.php";?>
        
        <div class="container-fluid body">   
            <div class="card card-primary">
                <div class="card-header">EJECUTAR SQL Y FUNCIONES DE REPARACIÓN</div>
                
                <div class="card-body">              
                    <form class="form-horizontal" action="javascript:ejecutar()" method="POST" enctype="multipart/form-data">
                        <?php if (!$execute) { ?>
                        <div class="form-group row">
                            <label class="col-form-label col-md-12 col-lg-12 pull-left">
                                <strong>SQL:</strong> 
                            </label>
                            <div class="col-md-12 col-lg-12 pull-left">
                                <textarea class="form-control" type="text" name="texto" id="texto" rows="10"></textarea>
                            </div>
                            <div class="col-md-1 col-lg-1 pull-right" style="margin-top: 5px;">
                                <button class="btn btn-success" type="button" name="clean" id="clean" onclick="limpiar()">Limpiar</button>
                            </div>
                        </div>
                        <?php } ?>
                        
                        <?php if ($execute) { ?> 
                        <label class="label label-info">Log generados por el sistema</label>
                        
                        <div id="divlog" class="container-fluid winlog right">
                            <div id="winlog" class="textlog">
                                <table id='winlog-table'></table>
                            </div>
                        </div>                         
                        <div id="progressbar-0" class="progress-block">
                            <div id="progressbar-0-alert" class="alert alert-success">

                            </div>            
                            <div id="progressbar-0-" class="progress progress-striped active">
                                <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"></span>
                                </div>
                            </div>                  
                        </div>  
                        <?php } ?>
                        
                        <div class="form-group row">
                            <label class="col-form-label col-2 pull-left">
                                <strong>Función a ejecutar:</strong> 
                            </label>
                            <div class="col-md-10 col-lg-10 pull-left">
                                <select class="form-control" id="funcion" name="funcion">
                                    <option value="0">No ejecuta ... </option>
                                    <?php 
                                    foreach ($exec_functions as $function) {
                                        if (is_null($function))
                                            continue;
                                    ?>
                                    <option value="<?=$function[0]?>" <?php if ($function[0] == $funcion) { ?> selected="selected"<?php } ?>><?="{$function[0]} -->> {$function[1]} -- {$function[2]}"?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>  
                        
                        <?php if ($execute) { ?>
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
                        <?php } ?>
                        
                        <button class="btn btn-primary" type="submit" name="execute" id="exect">Ejecutar</button> 
                        <button class="btn btn-warning" type="reset" name="cancel" id="cancel" onclick="self.location.href='repare_sql.php'">Cancelar</button> 
                    </form> 
                </div>
            </div>    
        </div>
    </body>
</html>

    <script type="text/javascript">  
        _SERVER_DIRIGER= "<?= addslashes(_SERVER_DIRIGER)?>";
        var url;

        function goend() {
            $('#divlog').scrollTop($('#winlog').height());
        }

        $(document).ready(function() {
            $('#btn-close').hide();  

            $('#winlog').mouseover(function() {
                _moveScroll= false;
            });
            $('#winlog').mouseleave(function() {
                _moveScroll= true;
            });
        });            
    </script>     
    

    <?php
    $_SESSION['in_javascript_block']= false;
    if ($execute) {
        $obj= new Trepare_sys($clink); 

        $obj->read_sql($texto);
        $obj->exect_sql();
        
        if ($funcion) {
            $obj->exec_function($funcion);
        }
    } 
    $_SESSION['in_javascript_block']= null;
    ?> 

    
    