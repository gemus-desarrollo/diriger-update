<?php
/**
 * author Geraudis Mustelier
 * copyright 2019
 */

session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";

$nivel_user= _GLOBALUSUARIO;
require_once "connect.class.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../../php/config.inc.php";
require_once "../../common/file.class.php";
require_once "../../../php/class/proceso.class.php";

$_SESSION['id_usuario']= _USER_SYSTEM;
$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";

require_once "tools/lote/php/connect.class.php";
require_once "config.ini";
require_once "import.class.php";

require_once "tools/dbtools/clean.class.php";
require_once "tools/dbtools/cola.class.php";

if ($uplink) {
    $clink= $uplink;
    require_once "php/class/config.class.php";
}

$obj_sys= new Tclean($uplink);

$obj_lote= new Tlote($uplink);
$cant_prs= $obj_lote->set_chain_procesos(_SYNCHRO_AUTOMATIC_HTTP);
$array_proceso_conected= clone_array($obj_lote->array_procesos);

$continue_exect_read_write= false;
$n_proceso_conected= 0;
$error= null;

foreach ($array_proceso_conected as $id_destino => $prs) {    
    $origen= $prs['codigo'];
    $name_prs= utf8_decode($prs['nombre']);
    $name_prs= "{$name_prs}=>{$prs['protocolo']}://{$prs['url']}:{$prs['puerto']}\r\n";

    unset($obj_cola);
    $obj_cola= new Tcola($uplink);
    $obj_cola->action= "importLote";
    $obj_cola->protocol= "web";
    $obj_cola->origen= $origen;
    $obj_cola->SetIdProceso($prs['id']);

    $execute= $obj_cola->if_execute();
    $id_execute= $obj_cola->GetId();

    $error= null;
    if ($execute) {
        $obj_sys->init_system();
        $obj_sys->set_system('importLote', date('Y-m-d H:i:s'));    

        echo "----------- $name_prs --------------";
        outputmsg("  ----------------- $name_prs ----------------", false, false);

        include "read_write.interface.php";
        ++$n_proceso_conected;
        
        if (!is_null($error)) {
            echo "\nERROR:".$error;
            $obj_sys->error.= $error;
        } else 
            echo "\nAL parecer todo OK";

        if (!empty($id_execute))
            $obj_cola->delete($id_execute); 
        $obj_sys->set_system();        
    }    
}

if ($n_proceso_conected == 0)
    $error.= "\r\n No hay proceso con conexion por sevicio web \r\n";