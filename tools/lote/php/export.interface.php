<?php
//header('Content-type: text/xml');

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

set_time_limit(0);
session_cache_expire(99999999999999999);
session_start();

@include_once "/../../php/setup.ini.php";
@include_once "php/setup.ini.php";

require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';
$_SESSION['execfromshell']= $execfromshell;

$nivel_user= $execfromshell ? _GLOBALUSUARIO : $_SESSION['nivel'];

global $signal;

if (empty($signal))
    $signal= !empty($_GET['signal']) ? $_GET['signal'] : 'home';

$_SESSION['output_signal']= $signal;

if (empty($fecha)) 
    $fecha= !empty($_GET['fecha']) ? time2odbc(urldecode($_GET['fecha'])) : null;
else 
    $fecha= time2odbc($fecha);

if (empty($id_destino))
    $id_destino= !empty($_GET['id_destino']) ? $_GET['id_destino'] : null;
if (empty(strlen($observacion)))
    $observacion= !is_null($_GET['observacion']) ? trim(urldecode($_GET['observacion'])) : null;

global $observacion;

require_once "connect.class.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/escenario.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/evento.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/_config_prs.class.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";

require_once "config.ini";
require_once "export.class.php";
require_once "send.class.php";

set_time_limit(0);

if (empty($_SESSION["_max_register_block_db_input"])) 
    $_SESSION["_max_register_block_db_input"]= 1000;

$title= "CREANDO LOTE DE DATOS A EXPORTAR";
$msg= "Iniciando la creaci&oacute;n de un lote....";

$config->empresa= !empty($config->empresa) ? $config->empresa : $_SESSION['empresa'];
$config->location= !empty($config->location) ? $config->location : $_SESSION['location'];
$config->local_proceso_tipo= !empty($config->local_proceso_tipo) ? $config->local_proceso_tipo : $_SESSION['local_proceso_tipo'];
$config->local_proceso_id= !empty($config->local_proceso_id) ? $config->local_proceso_id : $_SESSION['local_proceso_id'];
$config->local_proceso_id_code= !empty($config->local_proceso_id_code) ? $config->local_proceso_id_code : $_SESSION['local_proceso_id_code'];

global $cant_tables;

$tb_filter= null;
if ($_POST['tb_deletes'])
    $tb_filter= "(tdeletes)";
if ($_POST['tb_eventos'])
    $tb_filter.= "(teventos)";
if ($_POST['tb_objetivos'])
    $tb_filter.= "tobjetivos";
if ($_POST['tb_indicadores'])
    $tb_filter.= "(tindicadores)";
if ($_POST['tb_programas'])
    $tb_filter.= "(tprogramas)";
if ($_POST['tb_riesgo'])
    $tb_filter.= "(triesgos)";
if ($_POST['tb_notas'])
    $tb_filter.= "(tnotas)";

if ($_POST['tb_riesgo'] || $_POST['tb_notas']) {
    $array_tables_default[]= "tproceso_riesgos";
}
if ($_POST['tb_objetivos'] || $_POST['tb_indicadores'] || $_POST['tb_programas']) {
    $array_tables_default[]= "tproceso_indicadores";
}

$cant_tables= 0;
reset($array_dbtable);
while (list($key,$table)= each($array_dbtable)) {
    if ($signal != 'shell' && $table['export']) { 
        if ($tb_filter) {
            $array_dbtable[$key]['export']= 0;
            $table['export']= 0;
        }
        if ($_POST['tb_deletes'] && $key == "tdeletes") {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;        
        }    
        if ($_POST['tb_eventos'] && array_search($key, $tb_eventos) != false) {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;          
        }   
        if ($_POST['tb_objetivos'] && array_search($key, $tb_objetivos) != false) {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;          
        }       
        if ($_POST['tb_programas'] && array_search($key, $tb_programas) != false) {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;          
        }       
        if ($_POST['tb_indicadores'] && array_search($key, $tb_indicadores) != false) {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;          
        }
        if ($_POST['tb_notas'] && array_search($key, $tb_notas) != false) {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;          
        }
        if ($_POST['tb_riesgos'] && array_search($key, $tb_riesgos) != false) {
            $array_dbtable[$key]['export']= 1;
            $table['export']= 1;          
        }
    }
    
    if ($table['export']) 
       ++$cant_tables;
}

$obj_config_prs= new Tconfig_synchro($uplink);
$obj_config_prs->set_conectado();
$obj_config_prs->listar();
   
$obj_prs= new Tproceso($uplink);
$obj_prs->Set($_SESSION['local_proceso_id']);
$id_origen_chief_prs= $obj_prs->GetIdResponsable();

$obj_user= new Tusuario($uplink);
$obj_user->Set($id_origen_chief_prs);
$data_origen_chief_prs= $obj_user->GetNombre();
if (!empty($obj_user->GetCargo())) 
    $data_origen_chief_prs.= ", ".$obj_user->GetCargo();
?>

<?php 
if ($signal != 'shell' && $signal != 'webservice') { 
    $obj_sys= new Tclean($uplink);
    $obj_sys->init_system();
    $obj_sys->set_system('exportLote', $fecha);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>EXPORTACIÓN DE LOTES</title>

        <?php 
        $dirlibs= "../../../";
        require '../../../form/inc/_page_init.inc.php'; 
        ?>      

        <link rel="stylesheet" href="../../../libs/windowmove/windowmove.css">
        <script type="text/javascript" src="../../../libs/windowmove/windowmove.js"></script>  
   
        <script type="text/javascript" src="../../../js/home.js"></script>

        <script type="text/javascript" src="../../../js/string.js?version="></script>
        <script type="text/javascript" src="../../../js/general.js?version="></script>

        <style type="text/css">
            .winlog {
                max-height: 150px;
            }
        </style>
        
        <script type="text/javascript" language="JavaScript">
            parent.app_menu_functions= false;
            
            function closep() {
                var signal= document.getElementById('signal').value;
                var action= document.getElementById('action').value;
                var error= encodeURIComponent(document.getElementById('error').value);

                var file= encodeURIComponent(document.getElementById('file').value);
                var sendmail= document.getElementById('sendmail').value;
                var email= encodeURIComponent(document.getElementById('email').value);

                var url= '../form/resume.php?signal='+signal+'&action='+action+'&error='+error+'&file='+file;
                url+= '&sendmail='+sendmail+'&email='+email;

                parent.activeMenu = 'main';
                parent.app_menu_functions= true;
                self.location.href= url;
            }

            function writeLog(date, line, divout) {
                if (Entrada(date)) 
                    line= date + ' --->' + line;

                $('#'+divout).append(line);
            }             
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>
        
        <div class="app-body form">
            <div class="container">
                <div class="card card-primary">
                    <div class="card-header"><?=$title?></div>
                    
                    <div id="panel-body" class="card-body"> 
                        <img id="img-export" src="../img/export.gif" />

                        <label class="text" align="left" style="margin:3px; margin-bottom:5px; margin-top:5px;">
                            Esta operación puede tardar varios minutos, por favor espere…
                        </label>

                        <div id="progressbar-0" class="progress-block">
                            <div id="progressbar-0-alert" class="alert alert-success">
                                Para cada destino se creará un lote de datos
                            </div>            
                            <div id="progressbar-0-" class="progress progress-striped active">
                                <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"></span>
                                </div>
                            </div>                  
                        </div>  

                        <div id="progressbar-1" class="progress-block">
                            <div id="progressbar-1-alert" class="alert alert-success">
                                <?=$msg?>
                            </div>            
                            <div id="progressbar-1-" class="progress progress-striped active">
                                <div id="progressbar-1-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"></span>
                                </div>
                            </div>                  
                        </div>  

                        <div id="progressbar-2" class="progress-block">
                            <div id="progressbar-2-alert" class="alert alert-success">
                                Tabla en proceso: ...esperando por comenzar..
                            </div>            
                            <div id="progressbar-2-" id="progressbar-0" class="progress progress-striped active">
                                <div id="progressbar-2-bar" class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="sr-only"></span>
                                </div>
                            </div>                  
                        </div>  

                        <div class="container-fluid">
                            <script language="javascript">
                                document.getElementById('img-export').src='../img/export.png'; 
                            </script>
                        </div>
                        
                        <div id="divlog" class="winlog">
                            <div id="win-log" class="textlog"></div>
                        </div>

                        <div id="btn-block" class="btn-block btn-app">
                            <button class="btn btn-danger" onclick="closep()">Cerrar</button>  
                        </div> 

                    </div>
                </div> 
            </div>
        </div> 
        
        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
        <input type="hidden" id="action" name="action" value="<?=$_action?>" />
        <input type="hidden" id="error" name="error" value="<?=$error?>" />

        <input type="hidden" id="email" name="email" value="" />
        <input type="hidden" id="sendmail" name="sendmail" value="" />
        <input type="hidden" id="file" name="file" value="" />        
        
    </body>
</html>


<script type="text/javascript">
    <?php
    if ($signal != 'home') {
        if ($action == 'import') 
            $_action= 'upload';
        if ($action == 'export') 
            $_action= 'download';
    ?>   
        document.getElementById('btn-block').style.visibility= 'hidden';
    <?php } ?>
        
    _SERVER_DIRIGER= "<?= addslashes(_SERVER_DIRIGER)?>";     
</script>    
<?php } ?>
        
    <?php
    $_SESSION['in_javascript_block']= false;
    
    global $obj_lote;
    global $file_lote;
    global $email;
    global $sendmail;
    
    $obj_lote= new TLote($uplink);
    $obj_lote->action= 'export';
    $db= $obj_lote->GetDB();

    if ($signal != 'webservice')
        $observacion= (!empty($id_destino) ? "TRANSFERENCIA MANUAL\n" :  "TRANSFERENCIA AUTOMÃƒTICA.\n").$observacion;
    else
        $observacion= "TRANSFERENCIA POR ACCESO REMOTO DESDE SERVIDOR $destino";
    
    $type_synchro= null;
    if (is_null($signal) || $signal == 'form') 
        $type_synchro= _SYNCHRO_MANUAL;
    if ($signal == 'shell') 
        $type_synchro= _SYNCHRO_AUTOMATIC_EMAIL;
    if ($signal == 'webservice') 
        $type_synchro= _SYNCHRO_AUTOMATIC_HTTP;
    
    $obj_send= new TSend($uplink);
    $obj_send->signal= $signal;
    
    /*
    $_SESSION['debug']= 'yes';
    $_SESSION['trace_time']= 'yes'; 
    */
    if ($db) {
        $cant_prs= $obj_lote->set_chain_procesos($type_synchro);
        
        if (empty($id_destino)) {
           if (empty($cant_prs)) {
                $error= "No estan definidas las Unidades Organizativas externas, con las que se requiera la sincronizacion de datos.";
           } else {
                $i= 0;

                foreach ($obj_lote->array_procesos as $array) {
                    ++$i;
                    $ratio= ((float)$i/$cant_prs);

                    bar_progressCSS(0, "Procesando el destino {$array['nombre']}.... ", $ratio);
                    bar_progressCSS(1, "Procesando el destino {$array['nombre']}.... ", 0);
                    bar_progressCSS(2, "Procesando el destino {$array['nombre']}.... ", 0);

                    $error= $obj_send->export($array['id'], $fecha);
                    if (!is_null($error)) 
                        break;
           }    }

        } else {
            $obj_prs= new Tproceso($uplink);
            $obj_prs->Set($id_destino);

            $obj_lote->array_procesos[$id_destino]= array('id'=>$id_destino, 'nombre'=>$obj_prs->GetNombre(), 'email'=>$obj_prs->GetMail_address(),
                                       'tipo'=>$obj_prs->GetTipo(), 'codigo'=>$obj_prs->GetCodigo(), 'id_responsable'=>$obj_prs->GetIdResponsable(),
                                       'id_proceso'=>$obj_prs->GetIdProceso_sup(), 'id_proceso_code'=>$obj_prs->get_id_proceso_sup_code(),
                                       'manner'=>$obj_config_prs->array_procesos[$id_destino]['manner'], 
                                       'mcrypt'=>$obj_config_prs->array_procesos[$id_destino]['mcrypt']);

            bar_progressCSS(0, "Procesando el destino ".$obj_prs->GetNombre()." ... ", 0.5);

           $error= $obj_send->export($id_destino, $fecha);
        }
    }
    
    /*
     * Borrando archivos obsoletos
     */
    bar_progressCSS(0,"Borrando lotes anteriores", 0.7);
    $path= _EXPORT_DIRIGER_DIR;
    $afiles= $obj_lote->dirfiles($path);
    $count= count($afiles);
    $nfiles= $count;
    $sp= (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '\\' : '/';

    $i= 0;
    foreach ($afiles as $ifile) {
        ++$i;
        $diff = s_datediff('m', date_create($ifile['date']), date_create(date('Y-m-d')));
        if ($diff > $config->monthpurge || $nfiles > 2880) {
            --$nfiles;
            bar_progressCSS(1, "Borrando ficheros de lotes anteriores: {$ifile['name']}", (float)$i/$count);
            unlink("{$path}{$sp}{$ifile['name']}");
        }
    }
    
    bar_progressCSS(0, "Terminada la operación", 1);
    
    $_SESSION['in_javascript_block']= null;
      
    if ($signal != 'shell' && $signal != 'webservice') { 
        $obj_sys->error= $error;
        $obj_sys->set_system();
    }    

    if ($signal == 'shell') {
        if (!is_null($error)) 
            outputmsg("ERROR:".$error);
    } 
?>
    

    <?php if ($signal != 'shell' && $signal != 'webservice') { ?>
        <script type="text/javascript">
            document.getElementById('btn-block').style.visibility= 'visible';
        
            <?php
            if ($signal == 'login') {
                if (is_null($error)) {
            ?>
                    document.getElementById('signal').value= 'login';
                    document.getElementById('action').value= 'import';

            <?php } else { ?>
                    document.getElementById('signal').value= 'home';
                    document.getElementById('action').value= 'resume';
                    document.getElementById('error').value= "<?=$error?>";
   
            <?php } } ?>

            <?php if ($signal == 'home') { ?>
                document.getElementById('signal').value= 'home';
                document.getElementById('action').value= 'resume';
                document.getElementById('error').value= "<?=$error?>";
                
                <?php if (is_null($error)) { ?>
                    close();
            <?php } } ?>

            <?php if ($signal == 'form') { ?>
                document.getElementById('signal').value= 'form';
                document.getElementById('action').value= 'resume';
                document.getElementById('error').value= "<?=$error?>";
                
                document.getElementById('email').value= "<?=$email?>";
                document.getElementById('sendmail').value= "<?=$sendmail?>";
                document.getElementById('file').value= "<?=$file_lote?>";
            <?php } ?>
                         
        </script>    
    <?php } ?>
        
  