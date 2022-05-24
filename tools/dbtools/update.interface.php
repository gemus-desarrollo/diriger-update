<?php

/**
 * @author muste
 * @copyright 2013
 */

$iroot = strrpos(__FILE__, "/");
define("__DIR__", substr(__FILE__, 0, $iroot) . "/");

if (!$execfromshell) {
    set_time_limit(0);
    session_cache_expire(0);    
    session_start(); 

    require_once __DIR__."/../../php/setup.ini.php";
    require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

    require_once _PHP_DIRIGER_DIR."config.ini";   
    require_once _ROOT_DIRIGER_DIR."inc.php";
    require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
    require_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";
    require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

    require_once _ROOT_DIRIGER_DIR."tools/dbtools/update.functions.php";
    require_once _ROOT_DIRIGER_DIR."tools/dbtools/update.class.php";

    set_time_limit(0);
    ini_set('max_input_time', '0');
    ini_set('mysql.connect_timeout', '2880000000000000000000');

    $execute= !empty($_GET['execute']) ? $_GET['execute'] : 0;
} else {     
    $execute= 1;     
}

$_SESSION['debug']= 'no';

global $clink;
?>

<?php if (!$execfromshell) { ?>
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

            <script type="text/javascript">
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
            <script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>
        
            <div class="app-body form">
                <div class="container">
                    <div class="card card-primary">
                    <div class="card-header">ACTUALIZANDO SISTEMA</div>
                        <div class="card-body"> 
                            <label class="text text-danger">
                                Actualización de Estructura de Base de Datos. Esta operación puede tardar varios minutos, por favor espere.....
                            </label>

                            <div id="progressbar-0" class="progress-block">
                                <div id="progressbar-0-alert" class="alert alert-success">

                                </div>            
                                <div id="progressbar-0-" class="progress progress-striped active">
                                    <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        <span class="sr-only"></span>
                                    </div>
                                </div>                  
                            </div>  

                            <div class="row col-12">
                                <label class="label label-info">Log generados por el sistema</label>
                                <div id="divlog" class="container-fluid winlog right">
                                    <div id="winlog" class="textlog">
                                        <table id='winlog-table'></table>
                                    </div>
                                </div> 
                            </div>

                            <div id='error-alert' class="alert alert-danger d-none"></div>

                            <hr />
                             <label class="text text-info">
                                 <strong>Funciones para la actualización de datos en BD .....</strong>
                            </label>

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

                            <div id="error-msg" class="alert alert-danger d-none">
                                Ha ocurrido al menos un error durante la actualización del sistema. Por favor, copie el error y comuníquese inmediatamente con el personal de GEMUS. <br/>
                                <strong>e-Correo:</strong> gemus@nauta.cu;    <strong>teléfono:</strong> 58200755 / 53740039
                            </div>                   

                            <!-- buttom -->
                            <div class="btn-block btn-app">
                                <button id="btn-close" class="btn btn-primary" style="display: none;" type="button" onclick="parent.location.href= '<?=_SERVER_DIRIGER?>html/home.php'">Cerrar</button>
                                <button class="btn btn-danger" type="button" onclick="open_help_window('../../help/18_herramientas.htm#18_30.1')">Ayuda</button>
                            </div>                
                        </div> <!-- panel-body -->                      
                    </div> <!-- panel -->
                </div>  <!-- container -->   
            </div>
           
        </body>
    </html>         
<?php } // excute from shell  ?>
    
<?php if (!$execfromshell) { ?>
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
<?php } // excute from shell ?>
            <?php 
            $_SESSION['in_javascript_block']= null;
            if (!$execfromshell) 
                $_SESSION['in_javascript_block']= true;
                
            $obj_sys= new Tclean($clink); 
            $occupied= $obj_sys->if_occuped_system();
            $if_locked= $obj_sys->if_lock_system();
            
            if ($execfromshell && $if_locked) 
                die ("Usuarios del sistema bloqueados. Parece que alguna actualización esta corriendo. ".date("Y-m-d H:i:s"));
                
            if (!$execfromshell) {
                if ($if_locked && $execute) {
                    $text= "El sistema esta bloqueado, al parecer ya hay corriendo un proceso de actualización. Por favor espere unos minutos ";
                    $text.= "e intentelo nuevamente. Sí el problema persiste desbloquee a los usuarios e intente actualizar nuevamente.";
                ?>   
                        /*
                    alert("<?=$text?>", function(ok) {
                        if (ok) 
                            self.location.href= '<?=_SERVER_DIRIGER?>html/home.php';
                    });
                    */
                <?php
                }
                
                if ($occupied[0] && !$execute) {
                    $text= "Por favor verifique. {$occupied[1]} Desea continuar?";
                 ?>
                     confirm("<?=$text?>", function(ok) {
                         if (!ok) 
                             self.location.href= '<?=_SERVER_DIRIGER?>html/home.php';
                         else 
                             self.location.href= 'update.interface.php?execute=1';
                     });

                 <?php 
                } else {
                     if (!$execute) { ?>
                         self.location.href= 'update.interface.php?execute=1';
            <?php } } ?> 
                     
            <?php
            } // excute from shell
            
            if ($execute) {
                 $filename= $_SESSION["_DB_SYSTEM"] == "mysql" ? "update_db.sql" : "update_db.pgsql.sql";
                 $filename= $execfromshell ? _ROOT_DIRIGER_DIR."tools/dbtools/$filename" : $filename;
                 if (isset($obj)) 
                    unset($obj);

                 $obj= new Tupdate_sys($clink, $filename);
                 $obj->last_update_time= $obj->get_system('update');
                 $obj->last_script_time= $obj->get_system('beginscript');

                 $error= "Al parecer usted no ha corrido ninguna actualización anterior a esta. Por favor, consulte al personal de GEMUS.";
                 $error= (empty($obj->last_update_time) && empty($obj->last_script_time)) ? $error : null;

                 if (is_null($error)) {
                    $obj->lock_system();
                    $nsql= 0; 
 
                     $error= $obj->readupdate(false);
                     if (!is_null($error)) {
                         if (!$execfromshell) {
                      ?>
                         $('#winlog').html("<?=$error?>"); 
                      <?php    
                        } else {
                           echo $error; 
                    }   }
                }    
            }       
            ?>   

            <?php
            if (is_null($error) && $execute) {
                $obj->read_sql();

                global $exec_functions;
                global $remote_functions;
                $obj->go_exec_funct= true;

                foreach ($exec_functions as $function) {
                    if (!is_null($function)) 
                        $obj->test_function($function, false);
                }

                foreach ($remote_functions as $function) {
                    if (!is_null($function)) 
                        $obj->test_function($function, true);
                }

                $obj->filename= null; 
                if (!$execfromshell) 
                    $_SESSION['in_javascript_block']= true;
                
                $error= $obj->init_list();
            }
            ?>   
            <?php if (!$execfromshell) { ?>        
                document.getElementById('btn-close').style.visibility= 'visible';
                document.getElementById('btn-close').style.display= 'inline-block';             
            });
            </script>  
            <?php } ?>
            
        <?php             
        if (is_null($error) && $execute) {
            if (!$execfromshell) 
                $_SESSION['in_javascript_block']= false;
            if (!$execfromshell) 
                $obj->divout= "winlog";
            else 
                $obj->divout= null;
            
            $_SESSION['debug']= 'update';         
            $obj->execute_list();

            $obj->end_list();
            $_SESSION['debug']= 'no';
            
            global $using_remote_functions;
            $using_remote_functions= null;

            if (!$execfromshell) {
                bar_progressCSS(0, "Operación terminada. Nada mas que hacer", 1);
                bar_progressCSS(1, "Operación terminada. Nada mas que hacer", 1);
            } else { 
                echo "\nOperación terminada. Nada mas que hacer  ".date("Y-m-d H:i:s");
            }
            
            if ($obj->go_exec_funct) {          
                if (strtotime($obj->last_script_time) <= strtotime(_UPDATE_DATE_DIRIGER)) {
                    $obj->init_system();
                    $obj->set_system('update', _UPDATE_DATE_DIRIGER);
                    $obj->set_system();
                }
            } 

            $obj->unlock_system();
            $_SESSION['in_javascript_block']= null;
        } 
        ?>

        <?php if (!$execfromshell) { ?>
            <script type="text/javascript">
            <?php if (!is_null($error)) { ?>  
                $('#error-alert').show();
                $('#error-alert').removeClass('d-none');
                $('#error-alert').addClass('d-block');
                $('#error-alert').html('<?=$error?>');

                $('#error-msg').show();
                $('#error-msg').removeClass('d-none');
                $('#error-msg').addClass('d-block');
            <?php } ?> 
            </script>
        <?php } ?>    
