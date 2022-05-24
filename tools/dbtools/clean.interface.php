<?php

/**
 * @author muste
 * @copyright 2013
 */
 
require_once "../../inc.php";

set_time_limit(432000);
session_cache_expire(720);
session_start(); 

include_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";
require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

require_once "clean.class.php";

$_SESSION['debug']= 'no';
$_SESSION['output_signal']= 'form';
$_SESSION['execfromshell']= false;

$execute= !is_null($_GET['execute']) ? (int)$_GET['execute'] : 0;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>PURGANDO BASE DE DATOS DEL SISTEMA </title>

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
                max-height: 250px;
                min-height: 150px;
            }            
        </style>  
        
        <script type="text/javascript">
            function _close() {
                parent.activeMenu = 'main';
                parent.location.href='../../html/home.php';
            }
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <div class="app-body form">
            <div class="container">
                <div class="card card-primary">
                    <div class="card-header">MANTEMIENTO A LA BASE DE DATOS</div>
                    <div class="card-body">

                         <div id="progressbar-0" class="progress-block">
                            <div id="progressbar-0-alert" class="alert alert-success">
                                Comenzando
                            </div>            
                            <div id="progressbar-0-" class="progress progress-striped active">
                                <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"></span>
                                </div>
                            </div>                  
                        </div>

                        <div id="progressbar-1" class="progress-block">
                            <div id="progressbar-1-alert" class="alert alert-danger">
                                Comenzando
                            </div>            
                            <div id="progressbar-1-" class="progress progress-striped active">
                                <div id="progressbar-1-bar" class="progress-bar bg-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"></span>
                                </div>
                            </div>                  
                        </div>                     

                        <div id="divlog" class="container-fluid winlog">
                            <div id="winlog" class="textlog">

                            </div>
                        </div> 

                        <div id="_submit" class="btn-block btn-app">
                            <button class="btn btn-warning" type="button" onclick="_close()">Cerrar</button>
                        </div>
                    </div> <!-- panel-body -->                      
                </div> <!-- panel -->
            </div>  <!-- container -->     
        </div>
         
    </body>
</html>

    <script language="javascript">
        _SERVER_DIRIGER= '<?= addslashes(_SERVER_DIRIGER)?>';
        
        function goend() {
            $('#divlog').scrollTop($('#winlog').height());
        }  
        
        $(document).ready(function() {
            <?php
            if ($_SESSION['output_signal'] == 'shell') 
                $execute= 1;
            
            if ($_SESSION['output_signal'] != 'shell') {
                $obj_sys= new Tclean($clink);
                $occupied= $obj_sys->if_occuped_system();

                if ($occupied[0] && !$execute) {
                    $text= "Por favor verifique. {$occupied[1]} Desea continuar?";
                ?>
                    confirm("<?=$text?>", function(ok) {
                        if (!ok) 
                            parent.location.href= '<?=_SERVER_DIRIGER?>html/home.php';
                        else 
                            self.location.href= 'clean.interface.php?execute=1';
                    });
            <?php 
                } else {
                    $execute= 1;
                }
            } 
            ?>
        });            
       </script> 
       
       
        <?php
        $_SESSION['in_javascript_block']= false;

        if ($execute) {
            if (empty($obj_sys))
                $obj_sys= new Tclean($clink);
            $obj_sys->set_date();
            $reg_fecha= $obj_sys->reg_fecha;   
            $obj_sys->init_system();
            $obj_sys->set_system('purge', $reg_fecha);

            $obj_sys->dbclean();

            $obj_sys->set_system(); 
        }

        $_SESSION['in_javascript_block']= null;
        ?>                    
        
    