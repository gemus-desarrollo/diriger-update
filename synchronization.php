<?php
/**
 * author Geraudis Mustelier
 * copyright 2015
 */

$csfr_token='123abc';
$csfr_token_parent= "456def";

session_start();
require_once "php/setup.ini.php";

require_once "inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/code.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/escenario.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";

require_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/baseLote.class.php";

ini_set('max_execution_time', 0);

$execfromshell= true;
$nivel_user= _GLOBALUSUARIO;
$uplink= null;
$_SESSION['_ctime']= 1;
$_SESSION['output_signal']= 'shell';
$_SESSION['id_usuario']= _USER_SYSTEM;

$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";

require_once _ROOT_DIRIGER_DIR."tools/lote/php/connect.class.php";

if ($uplink) {
    $clink= $uplink;
    require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";
}

require_once _ROOT_DIRIGER_DIR."tools/lote/php/lote.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/db.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/bond.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/upload.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/base.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/mail.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/_config_prs.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/pop3/pop3.class.php";

require_once _ROOT_DIRIGER_DIR."tools/lote/php/config.ini";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/export.class.php";
require_once _ROOT_DIRIGER_DIR."tools/lote/php/import.class.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";
require_once _ROOT_DIRIGER_DIR."tools/dbtools/cola.class.php";

global $error;

function set_proceso() {
    global $uplink;

    $obj_prs= new Tproceso($uplink);
    $obj_prs->Set($_SESSION['local_proceso_id']);

    $_SESSION['location']= $obj_prs->GetCodigo();
    $_SESSION['local_proceso_id_code']= $obj_prs->get_id_code();
    $_SESSION['local_proceso_tipo']= $obj_prs->GetTipo();
    $_SESSION['superior_proceso_id']= $obj_prs->GetIdProceso_sup();
    $_SESSION['superior_proceso_id_code']= $obj_prs->get_id_proceso_sup_code();

    $obj_esc= new Tescenario($uplink);
    $obj_esc->SetEscenario(date('Y'), $_SESSION['local_proceso_id'], _LOCAL);
    $obj_esc->Set();

    $_SESSION['inicio']= $obj_esc->GetInicio();
    $_SESSION['fin']= $obj_esc->GetFin();

    $_SESSION['id_escenario']= $obj_esc->GetIdEscenario();
    $_SESSION['id_escenario_code']= $obj_esc->get_id_escenario_code();
}

if (!$uplink) 
    die("ERROR:No se ha establecido comunicacion con el servidor\n");
if ($config->type_synchro != _SYNCHRO_AUTOMATIC_EMAIL && $config->type_synchro != _SYNCHRO_AUTOMATIC_HTTP) 
    die("No esta configurado para la sincronizacion automatica");

echo "Inicio: ".date('Y-m-d H:i:s')."\r\n";

$obj_synchro= new TbaseLote($uplink);
$fecha_synchro= $obj_synchro->get_last_date_synchronization('import');
$fecha_synchro= !is_null($fecha_synchro) ? $fecha_synchro : date('Y').'-01-01';
$seconds= s_datediff('s', date_create($fecha_synchro), date_create(date('Y-m-d H:i:s')));

if (!empty($config->time_synchro) && $obj_synchro->finalized) {
    if ((!is_null($fecha_synchro) && ((int)$seconds <= (int)$config->time_synchro))) {
        $seconds= (int)$config->time_synchro - (int)$seconds;
        die("No corresponde segun programacion de tiempo (time_synchro) faltan $seconds segundos");
}   }

$currtime= strtotime(date("H:i:s"));
if (!empty($config->timesynchro)) {
    $timesynchro= strtotime($config->timesynchro);
    if ($currtime > strtotime('06:00:00') && $currtime < strtotime('17:00:00')) 
        die("No corresponde segun programacion de tiempo");
    if ((($currtime >= strtotime('17:00:00') && $currtime < strtotime('23:59:59')) && ($timesynchro >= strtotime('17:00:00') && $timesynchro < strtotime('23:59:59'))) && $currtime < $timesynchro) 
        die("No corresponde segun programacion de tiempo");
    if ((($currtime >= strtotime('00:00:00') && $currtime < strtotime('06:00:00')) && ($timesynchro >= strtotime('00:00:00') && $timesynchro < strtotime('06:00:00'))) && $currtime < $timesynchro) 
        die("No corresponde segun programacion de tiempo");
}

$execfromshell= 'shell';
$signal= 'shell';

set_proceso();

$obj_config_prs= new Tconfig_synchro($uplink);
$obj_config_prs->listar();

if ($obj_config_prs->cant_smtp_prs > 0) {
    echo "------------------- EXPORTANDO EMAIL ----------------------------\r\n";
    outputmsg("------------------- EXPORTANDO EMAIL ----------------------------\r\n", false);

    $obj_cola= new Tcola($uplink);
    $obj_cola->action= "exportLote";
    $obj_cola->protocol= "smtp";
    $obj_cola->origen= $_SESSION['location'];
    $obj_cola->SetIdProceso($_SESSION['local_proceso_id']);

    $execute= $obj_cola->if_execute();
    $id_execute= $obj_cola->GetId();
 
    $obj_sys= new Tclean($uplink);

    $error= null;
    if (!empty($execute)) { 
        if (!empty($id_execute))
            $obj_cola->delete($id_execute);    

        $obj_sys->init_system();
        $obj_sys->set_system('exportLote', date('Y-m-d H:i:s'));

        include _ROOT_DIRIGER_DIR."tools/lote/php/export.interface.php";

        if (!is_null($error)) {
            echo "\nERROR:".$error;
            $obj_sys->error.= $error;
        } else 
            echo "\nAL parecer todo OK";  

        $obj_sys->set_system();
    }
}

if ($obj_config_prs->cant_smtp_prs > 0) {
    echo "------------------ DESCARGANDO EMAIL --------------------------------\r\n";
    outputmsg("------------------ DESCARGNDO EMAL --------------------------------\r\n", false, false);

    unset($obj_cola);
    $obj_cola= new Tcola($uplink);
    $obj_cola->action= "importLote";
    $obj_cola->protocol= "pop3";
    $obj_cola->origen= $_SESSION['location'];
    $obj_cola->SetIdProceso($_SESSION['local_proceso_id']);

    $execute= $obj_cola->if_execute();
    $id_execute= $obj_cola->GetId();

    $error= null;
    if (!empty($execute)) { 
        if (!empty($id_execute))
            $obj_cola->delete($id_execute); 

        $obj_sys->init_system();
        $obj_sys->set_system('importLote', date('Y-m-d H:i:s'));    

        include _ROOT_DIRIGER_DIR."tools/lote/php/import.interface.php";

        if (!is_null($error)) {
            echo "\nERROR:".$error;
            $obj_sys->error.= $error;
        } else 
            echo "\nAL parecer todo OK";    

        $obj_sys->set_system();
    }
}

echo "------------------ PETICION HTTP --------------------------------\r\n";
outputmsg("------------------ PETICION HTTP --------------------------------\r\n", true, true);

$error= null;
$signal= 'webservice';
$execfromshell= true;

include _ROOT_DIRIGER_DIR."tools/lote/php/read.interface.php";

echo "\nFin: ".date('Y-m-d H:i:s')."\r\n";
echo "========================== END ===========================\r\n";
?>