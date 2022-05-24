<?php
/**
 * author Geraudis Mustelier
 * copyright 2015
 */

$clink= null;
$not_load_config_class= null;

session_cache_expire(0);
session_start();

require_once "inc.php";
$csfr_token='123abc';
$csfr_token_parent= "456def";
require_once "php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "php/config.inc.php";
require_once "php/class/config.class.php";

require_once "php/class/time.class.php";

ini_set('max_execution_time', 0);

$execfromshell= true;
$nivel_user= _GLOBALUSUARIO;
$uplink= null;
$_SESSION['_ctime']= 1;
$_SESSION['output_signal']= 'shell';
$_SESSION['execfromshell']= 'shell';
$_SESSION['id_usuario']= _USER_SYSTEM;

$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";

require_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/connect.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/usuario.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";

require_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";
require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";

if ($uplink) {
    $clink= $uplink;
    require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";
}
require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";
require_once _ROOT_DIRIGER_DIR."tools/dbtools/backup.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/cola.class.php";

global $error;

if (!$uplink)
    die("ERROR:No se ha establecido comunicacion con el servidor\r\n");
if (empty($config->monthpurge))
    die("ERROR:Sistema no configurado para el mantenimiento automatico\r\n");

$obj_sys= new Tclean($uplink);

$fecha_clean= $obj_sys->get_system('purge');
$fecha_clean= $obj_sys->GetFecha();
$now= date('Y-m-d H:i:s');
$month= !empty($fecha_clean) ? (int)s_datediff('m', date_create($fecha_clean), date_create($now)) : 3;

if ((!is_null($fecha_clean) && $month < (int)$config->monthpurge)) {
    $days= (int)$config->monthpurge - $month;
    die();
}

$currtime= strtotime(date("H:i:s"));

if (!empty($config->timepurge)) {
    $timepurge= strtotime($config->timepurge);
    if ($currtime > strtotime('06:00:00') && $currtime < strtotime('17:00:00')) {
        if ($currtime < $timepurge)
            die();
    } else {
        if (($timepurge >= strtotime('17:00:00') && $timepurge < strtotime('23:59:59')) && $currtime < $timepurge)
            die();
        if (($timepurge >= strtotime('00:00:00') && $timepurge <= strtotime('06:00:00')) && $currtime < $timepurge)
            die();
}   }

$obj_cola= new Tcola($uplink);
$obj_cola->action= "purge";
$obj_cola->protocol= "os";
$obj_cola->origen= $_SESSION['location'];
$obj_cola->SetIdProceso($_SESSION['local_proceso_id']);

$execute= $obj_cola->if_execute();
$id_execute= $obj_cola->GetId();
if (!$execute)
    die();
if (!empty($id_execute))
    $obj_cola->delete($id_execute);

$obj_sys->set_date();
$reg_fecha= $obj_sys->reg_fecha;

$obj_sys->init_system();
$obj_sys->set_system('purge', $reg_fecha);

echo "------------------ INICIO --------------------------------\r\n";
echo "\nInicio: $now";
$error= null;
$error=  $obj_sys->dbclean();

$date= $obj_sys->get_cronos();

if (!is_null($error)) {
    echo "\nERROR:".$error;
    $obj->error.= $error;
} else {
    echo "\nAL parecer todo OK";
}

$obj_sys->set_system('purge', $date, $error);

echo "\nFin: ".date('Y-m-d H:i:s');
echo "\n========================== END ===========================\r\n";
?>