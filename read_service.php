<?php
/**
 * author Geraudis Mustelier
 * copyright 2019
 */

$csfr_token='123abc';
$csfr_token_parent= "456def";

session_start();
require_once "php/setup.ini.php";

require_once "inc.php";
require_once "php/config.inc.php";
require_once "php/class/base.class.php";
require_once "php/class/time.class.php";

require_once "php/class/code.class.php";
require_once "php/class/escenario.class.php";
require_once "php/class/proceso.class.php";

require_once "tools/common/file.class.php";
require_once "tools/lote/php/baseLote.class.php";

require_once _PHP_DIRIGER_DIR."config.ini";
require_once "php/setup.ini.php";

session_start();
ini_set('max_execution_time', 0);

$execfromshell= 'webservice';
$nivel_user= _GLOBALUSUARIO;
$uplink= null;
$_SESSION['_ctime']= 1;
$_SESSION['output_signal']= 'webservice';
$_SESSION['id_usuario']= _USER_SYSTEM;
$_SESSION['logfile']= "synchronization.log";

$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";

require_once "tools/lote/php/connect.class.php";

if ($uplink) {
    $clink= $uplink;
    require_once "php/class/config.class.php";
}

require_once "tools/lote/php/lote.class.php";
require_once "tools/lote/php/db.class.php";
require_once "tools/lote/php/bond.class.php";
require_once "tools/lote/php/upload.class.php";

require_once "php/class/base.class.php";
require_once "php/class/mail.class.php";

require_once "php/class/_config_prs.class.php";
require_once "php/class/pop3/pop3.class.php";

require_once "tools/lote/php/config.ini";
require_once "tools/lote/php/export.class.php";
require_once "tools/lote/php/import.class.php";

require_once "tools/dbtools/clean.class.php";
require_once "tools/dbtools/cola.class.php";

global $error;


$test= !is_null($_POST['test']) ? $_POST['test'] : $_GET['test'];
$test= !is_null($test) ? $test : 0;

if (!empty($test)) {
    echo "<p><strong>BIENVENIDO AL SISTEMA</strong></p>";
    echo "<strong>SERVIDOR:</strong> {$_SESSION['empresa']} <br/>";
    echo "<strong>HOST:</strong> {$_SERVER['SERVER_ADDR']}<br/>";
    echo "<strong>UPDATE:</strong> "._UPDATE_DATE_DIRIGER;
    echo "<p><strong>Fecha y Hora:</strong> ".date('Y-m-d H:i:s')."</p>";
    die();
}

$destino= !empty($_POST['destino']) ? $_POST['destino'] : $_GET['destino'];
$fecha= !empty($_POST['fecha']) ? urldecode($_POST['fecha']) : urldecode($_GET['fecha']);

$last_time_tables['_tdeletes']= $_POST['tdeletes'];
$last_time_tables['_treg_evento']= $_POST['treg_evento'];
$last_time_tables['_tproceso_eventos']= $_POST['tproceso_eventos'];
$last_time_tables['_tusuario_eventos']= $_POST['tusuario_eventos'];
$last_time_tables['_treg_objetivo']= $_POST['treg_objetivo'];
$last_time_tables['_treg_inductor']= $_POST['treg_inductor'];
$last_time_tables['_treg_perspectiva']= $_POST['treg_perspectiva'];
$last_time_tables['_treg_real']= $_POST['treg_real'];
$last_time_tables['_treg_plan']= $_POST['treg_plan'];
$last_time_tables['_tinductor_eventos']= $_POST['tinductor_eventos'];

if (empty($destino))
    die("No se ha recivido el origen de la peticion. ERROR_WEB:1");
if (!$uplink) 
    die("ERROR:No se ha establecido comunicacion con el servidor. ERROR_WEB:2");
if (!$config->http_access) 
    die("No esta configurado para la sincronizacion por acceso mediante servicio Web protocolo http. ERROR_WEB:3");

$obj_prs= new Tproceso($uplink);
$obj_prs->get_proceso_from_code($destino);
$id_destino= $obj_prs->GetId();
$id_destino_code= $obj_prs->get_id_proceso_code();

set_proceso();

$obj_cola= new Tcola($uplink);
$obj_cola->action= "exportLote";
$obj_cola->protocol= "web";
$obj_cola->origen= $destino;
$obj_cola->SetIdProceso($id_destino);

$execute= $obj_cola->if_execute();
$id_execute= $obj_cola->GetId();
if (!$execute)
    die("Servidor ocupado no puede responder ahora. ERROR_WEB:4");
if (!empty($id_execute))
    $obj_cola->delete($id_execute);

$error= null;
$signal= 'webservice';
$nivel_user == _SUPERUSUARIO;

$obj_sys= new Tclean($uplink);

$obj_sys->init_system();
$obj_sys->set_system('exportLote', $fecha);

require "tools/lote/php/export.interface.php";

$obj_sys->set_system();

/**************************************************************************
 * function set_tprocesos
 **************************************************************************/
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