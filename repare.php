<?php
/**
 * Created by Visual Studio Code.
 * User: Geraudis Mustelier Portuondo
 * Date: 06/05/2020
 * Time: 7:16
 *
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

require_once "php/class/library.php";
require_once "php/class/library_string.php";
require_once "php/class/library_style.php";

require_once "php/class/base.class.php";
require_once "php/class/config.class.php";

require_once "php/class/time.class.php";

set_time_limit(0);
ini_set('max_execution_time', 0);

$execfromshell= true;
$nivel_user= _GLOBALUSUARIO;
$uplink= null;
$_SESSION['_ctime']= 1;
$_SESSION['output_signal']= 'shell';
$_SESSION['id_usuario']= _USER_SYSTEM;

$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";

require_once "php/class/DBServer.class.php";
require_once "tools/lote/php/connect.class.php";

require_once "php/class/time.class.php";
require_once "php/class/usuario.class.php";
require_once "php/class/proceso.class.php";

require_once "tools/common/file.class.php";
require_once "tools/dbtools/base_clean.class.php";
require_once "tools/dbtools/clean.class.php";
require_once "tools/dbtools/update.class.php";  
require_once "tools/dbtools/backup.class.php";

$clink= $uplink;

if ($uplink) 
    require_once "php/class/config.class.php";

$file_exect= $argv[1];
$execute_argv= $argv[2];

if ($execute_argv) {
    $execute= 1;
    $_SESSION['execfromshell']= true;
    $_SESSION['output_signal']= 'shell';
}


if ($file_exect == "repare_id_code") {
    echo "\n Ejecutando repares_id_code.php ... \n"; 
    include_once "tools/repare/repare_id_code.php";
} 
if ($file_exect == "repare_serie_archive") {
    echo "\n Ejecutando repare_serie_archive.php ... \n"; 
    include_once "tools/repare/repare_serie_archive.php";
}
/*
 * argumento
 * union_db 1 code_origen db_origen code_target db_target / 
 */
if ($file_exect == 'union_db') {
    echo "\n Ejecutando union database ... \n";
    include_once "tools/repare/union_db/index.php";
}    
?>