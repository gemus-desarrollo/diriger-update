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

require_once "repare_tproceso_eventos.inc";

set_time_limit(0);

$execute= $_GET['execute'];
$year= !empty($_POST['year']) ? $_POST['year'] : date('Y');

$current_year= date('Y');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>PURGAR TABLA tproceso_eventos</title>

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
            
            var url= "repare_tproceso_eventos.php?execute=1";
            form.action= url;
            form.submit();  
        }      
        </script>
    </head>

    <body>    
        <?php include "../../inc.php";?>
        
        <div class="container-fluid body">   
            <div class="card card-primary">
                <div class="card-header">PURGAR TABLA tproceso_eventos</div>
                
                <div class="card-body">              
                    <form class="form-horizontal" action="javascript:ejecutar()" method="POST" enctype="multipart/form-data">                        
                        <div class="form-group row">
                            <label class="col-form-label col-2 pull-left">
                                <strong>tproceso_evento(año):</strong> 
                            </label>
                            <div class="col-md-2 col-lg-2 pull-left">
                                <select class="form-control" id="year" name="year">
                                    <?php for ($i=$current_year-2; $i <= $current_year+1; $i++) { ?>
                                    <option value="<?=$i?>" <?php if ($year == $i) {?>selected="selected"<?php } ?>><?=$i?></option>
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
        $array_eventos= array();
        $array_id_eventos= array();
        $array_id_procesos= array();

        $nums_tb= _set_init_112($year, $array_eventos, $array_id_eventos, $array_id_procesos);
        _set_112_execute($year, $nums_tb, $array_eventos, $array_id_eventos, $array_id_procesos);
    } 
    $_SESSION['in_javascript_block']= null;
    ?> 

    
    