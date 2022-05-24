<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/DBServer.class.php";
require_once "class/connect.class.php";

/**
 * @param $image => imagen a codificar en base64
 * @return string
 * *----------------------------------------------------------------------------------------
 */
function pg_image($image) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? $image : base64_decode($image);
}
/*-----------------------------------------------------------------------------------------*/

$menu= $_GET['menu'];
$id= $_GET['id'];
$signal= $_GET['signal'];

if ($menu == 'escenario') {
    $sql= "select ".encodeBlob2pg("mapa").", ".encodeBlob2pg("proc_mapa").", ".encodeBlob2pg("org_mapa").", ";
    $sql.= "mapa_param, proc_param, org_param from tescenarios where id = $id";
}

if ($menu == 'usuario')
    $sql .= "select " . encodeBlob2pg("firma") . ", firma_param from tusuarios where id = $id";

$result= $clink->query($sql);
$row= $clink->fetch_array($result);

if ($menu == 'escenario') {
    switch ($signal) {
        case('strat') :
            $mapa = pg_image($row[0]);
            $param = $row['mapa_param'];
            break;
        case('proc') :
            $mapa = pg_image($row[1]);
            $param = $row['proc_param'];
            break;
        case('org') :
            $mapa = pg_image($row[2]);
            $param = $row['org_param'];
            break;
    }
}

if ($menu == 'usuario') {
    $mapa = pg_image($row[0]);
    $param = $row['firma_param'];
}

list($name,$type,$size,$dim)= preg_split("[:]",$param);

if (!empty($mapa)) {
    header("Content-type: $type");
    echo $mapa;
} else {
    echo "";
}

exit;
?>