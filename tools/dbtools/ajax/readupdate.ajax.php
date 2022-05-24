<?php
/**
 * @author muste
 * @copyright 2018
 */


session_start(); 
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";

include_once _PHP_DIRIGER_DIR."config.ini";
include_once "../../../inc.php";
include_once "../../../php/config.inc.php";
include_once "../../../php/class/connect.class.php";
include_once "../../../php/class/time.class.php";

include_once "../update.class.php";

global $clink;

$filename= $_SESSION["_DB_SYSTEM"] == "mysql" ? "update_db.sql" : "update_db.pgsql.sql";
$filename= _ROOT_DIRIGER_DIR."/tools/dbtools/$filename";
$obj= new Tupdate_sys($clink, $filename);

$_html= $obj->readupdate();

if (is_null($obj->error)) {
    $html= "<table id=\"table-textlog\">";                    
    $html.= $_html;
    $html.= "</table>"; 
    
    echo $html;
} else {
    echo $_html;
}
?>

