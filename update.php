<?php
/**
 * @author muste
 * @copyright 2013
 */

$clink= null;
$not_load_config_class= null;

require_once "inc.php";
$csfr_token='123abc';
$csfr_token_parent= "456def";

set_time_limit(0);
session_cache_expire(0);
session_start();

require_once "php/setup.ini.php";
require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

ini_set('max_input_time', '43200000000000000');
ini_set('mysql.connect_timeout', '28800000000000000');

$_SESSION['trace_time']= 'no';
$_SESSION['debug']= 'no';

$execfromshell= true;
$nivel_user= _GLOBALUSUARIO;
$uplink= null;
$_SESSION['_ctime']= 1;
$_SESSION['output_signal']= 'shell';
$_SESSION['id_usuario']= _USER_SYSTEM;
$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";

require_once _DIRIGER_DIR."php/config.ini";
require_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/connect.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/usuario.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";

require_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";
require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/cola.class.php";

if ($uplink) {
    $clink= $uplink;
    require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";
}
require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";
require_once _ROOT_DIRIGER_DIR."tools/dbtools/backup.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/update.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/pop3/pop3.class.php";
?>

<?php
echo "/********************************************************************************************************************/\n";
echo "/*  Actualizaciones descargadas desde el correo electrónico \n";
echo "*********************************************************************************************************************/\n";

$cant_upgrades= 0;
$result= null;

$obj= new Tupdate_sys($clink);

if ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $obj->test_winrar()) || strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    $result= $obj->get_emails();
    $error= $obj->error;
}
elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $error= "\nEl ejecutable winrar.exe no ha sido encontrado. Por favor, asegurase que la aplicación WinRAR esté instalada en el ";
    $error.= "servidor y que la ruta este definida en el fichero de configuración config.ini.\n";
}

if (is_null($error) && $result) {
    foreach ($obj->array_files as $update) {
        $error= $obj->copy_src($update['pack']);
        if (!is_null($error)) 
            break;

        ++$cant_upgrades;
        $obj->DeleteMail($update['email_number']);
    }
    
    $obj->Close();
}

if (is_null($error)) 
    $obj->Close();
else 
    echo $error;

echo "\n\n/********************************************************************************************************************/\n";
echo "/*  Corriendo script de actualización \n";
echo "*********************************************************************************************************************/\n";

$obj_sys= new Tupdate_sys($clink);
$fecha_script= $obj_sys->get_system('update'); 

if (is_null($fecha_script) || (int)strtotime(_UPDATE_DATE_DIRIGER) > (int)strtotime($fecha_script)) { 
    $obj_cola= new Tcola($uplink);
    $obj_cola->action= "update";
    $obj_cola->protocol= "os";
    $obj_cola->origen= $_SESSION['location'];
    $obj_cola->SetIdProceso($_SESSION['local_proceso_id']);

    $execute= $obj_cola->if_execute_update();
    $id_execute= $obj_cola->GetId();
    if (!$execute)
        die();
    if (!empty($id_execute))
        $obj_cola->delete($id_execute);

    include _ROOT_DIRIGER_DIR."tools/dbtools/update.interface.php";
}

unset($obj_sys);
$obj_sys= null;