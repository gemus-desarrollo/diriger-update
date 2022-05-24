<?php 
//header('Content-type: text/xml'); 

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start(); 
set_time_limit(0);
session_cache_expire(720);

@include_once "/../../php/setup.ini.php";
@include_once "php/setup.ini.php";

require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';

$_SESSION['execfromshell']= $execfromshell;
$nivel_user= $execfromshell ? _GLOBALUSUARIO : $_SESSION['nivel'];
if (!$signal)
    $signal= !empty($_GET['signal']) ? $_GET['signal'] : 'home';

$_SESSION['output_signal']= $signal;

require_once "connect.class.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/pop3/pop3.class.php";
require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";

require_once "config.ini";
require_once "import.class.php";
require_once "read.class.php";

if (empty($_SESSION["_max_register_block_db_input"])) 
    $_SESSION["_max_register_block_db_input"]= 1000;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;
if (empty($fecha))
    $fecha= !empty($_GET['fecha']) ? urldecode($_GET['fecha']) : null;
    
if (empty($file))     
    $file= !empty($_GET['lote']) ? $_GET['lote'] : null;
if (empty($observacion))    
    $observacion= !is_null($_GET['observacion']) ? urldecode($_GET['observacion']) : null;

$title= "IMPORTANDO LOTE DE DATOS";
$msg= "Iniciando la carga del lote de datos por el servidor....";

$config->empresa= !empty($config->empresa) ? $config->empresa : $_SESSION['empresa'];
$config->location= !empty($config->location) ? $config->location : $_SESSION['location'];
$config->local_proceso_tipo= !empty($config->local_proceso_tipo) ? $config->local_proceso_tipo : $_SESSION['local_proceso_tipo'];
$config->local_proceso_id= !empty($config->local_proceso_id) ? $config->local_proceso_id : $_SESSION['local_proceso_id'];
$config->local_proceso_id_code= !empty($config->local_proceso_id_code) ? $config->local_proceso_id_code : $_SESSION['local_proceso_id_code'];

global $cant_tables;

while (list($key,$table)= each($array_dbtable))
    if ($table['export']) 
        ++$cant_tables;
/* test */  //  ++$cant_tables;
?>

<?php 
if ($signal == 'form') {
    $obj_sys= new Tclean($uplink);
    $obj_sys->init_system();
    $obj_sys->set_system('importLote', $fecha); 
}

if (!$execfromshell && !$continue_exect_read_write) { 
    $continue_exect_read_write= true;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>IMPORTACIÓN O CARGA DE LOTES</title>

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
    parent.app_menu_functions = false;

    function closep() {
        var signal = document.getElementById('signal').value;
        var action = document.getElementById('action').value;
        var error = encodeURIComponent(document.getElementById('error').value);

        var url = '../form/resume.php?signal=' + signal + '&action=' + action + '&error=' + error;

        parent.activeMenu = 'main';
        parent.app_menu_functions = true;
        self.location.href = url;
    }

    function writeLog(date, line, divout) {
        if (Entrada(date))
            line = date + ' --->' + line;

        $('#' + divout).append(line);
    }
    </script>
</head>

<body>
    <script type="text/javascript" src="../../../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header"><?=$title?></div>

                <div class="card-body">
                    <img id="img-export" src="../img/import.gif" />

                    <label class="text" align="left" style="margin:3px; margin-bottom:5px; margin-top:5px;">
                        Esta operación puede tardar varios minutos, por favor espere…
                    </label>

                    <div id="progressbar-0" class="progress-block">
                        <div id="progressbar-0-alert" class="alert alert-success">
                            Para cada origen se importará un lote de datos.
                        </div>
                        <div id="progressbar-0-" class="progress progress-striped active">
                            <div id="progressbar-0-bar" class="progress-bar bg-success" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>

                    <div id="progressbar-1" class="progress-block">
                        <div id="progressbar-1-alert" class="alert alert-success">
                            <?=$msg?>
                        </div>
                        <div id="progressbar-1-" class="progress progress-striped active">
                            <div id="progressbar-1-bar" class="progress-bar bg-success" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>

                    <div id="progressbar-2" class="progress-block">
                        <div id="progressbar-2-alert" class="alert alert-success">
                            Tabla en proceso: ...esperando por comenzar..
                        </div>
                        <div id="progressbar-2-" class="progress progress-striped active">
                            <div id="progressbar-2-bar" class="progress-bar bg-success" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid">
                        <script language="javascript">
                        document.getElementById('img-export').src = '../img/import.png';
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
</body>

</html>

<input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
<input type="hidden" id="action" name="action" value="<?=$_action?>" />
<input type="hidden" id="error" name="error" value="<?=$error?>" />

<script type="text/javascript">
<?php 
if ($signal != 'home') {
    if ($action == 'import') 
        $_action= 'upload';
    if ($action == 'export') 
        $_action= 'download';
?>
    document.getElementById('btn-block').style.visibility = 'hidden';
<?php } ?>

_SERVER_DIRIGER = "<?= addslashes(_SERVER_DIRIGER)?>";
</script>
<?php } ?>

<?php
    $error= null;
    $obj_read= new Tread($uplink); 
    $obj_read->divout= "win-log";
    $_SESSION['in_javascript_block']= false;
    
    global $obj;
    global $array_dbtable;

    if ($signal != 'form' && $signal != 'webservice') {
        $error= $obj_read->read_from_mails();
        if (is_null($error))
            $error= $obj_read->import_attachment();
        $obj_read->delete_mails();
    }
    
    if ($file && ($signal == 'form' || $signal == 'webservice')) {
        bar_progressCSS(0, "Procesando fichero lote... ", 0.3);
        $error= $obj_read->import_lote($file);
    }
    
    bar_progressCSS(0,"Todas las operaciones terminadas ", 1);
    bar_progressCSS(1, "Todas las operaciones terminadas", 1);
    
    $_SESSION['in_javascript_block']= null;

    if ($signal != 'shell' && $signal != 'webservice') { 
        $obj_sys->error= $error;
        $obj_sys->set_system();
    } 

    if ($execfromshell) {
        if (!is_null($error)) 
            outputmsg("ERROR:".$error);
    } 
    ?>

<?php if (!$execfromshell) { ?>
<script type="text/javascript">
    document.getElementById('btn-block').style.visibility = 'visible';

    <?php
    if ($signal == 'login') {
        if (!is_null($error)) {
    ?>
    document.getElementById('signal').value = 'home';
    document.getElementById('action').value = 'resume';
    document.getElementById('error').value = "<?=$error?>";

    <?php } else { ?>
    document.getElementById('signal').value = 'home';
    document.getElementById('action').value = 'resume';
    <?php } } ?>

    <?php  if ($signal == 'home') { ?>
    document.getElementById('signal').value = 'home';
    document.getElementById('action').value = 'resume';
    document.getElementById('error').value = "<?=$error?>";

    <?php if (is_null($error)) { ?>
    close();
    <?php } } ?>

    <?php if ($signal == 'form') { ?>
    document.getElementById('signal').value = 'form';
    document.getElementById('action').value = 'resume';
    document.getElementById('error').value = "<?=$error?>";
    <?php } ?>
</script>
<?php } ?>