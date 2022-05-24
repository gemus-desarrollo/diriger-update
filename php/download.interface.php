<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2016
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "class/DBServer.class.php";
require_once "class/connect.class.php";
require_once "class/document.class.php";

$id= $_GET['id'];
$obj= new Tdocumento($clink);
$obj->Set($id);

$url= _UPLOAD_DIRIGER_DIR.$obj->url;
$size= filesize($url);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$obj->filename.'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($url));

ob_clean();
flush();
readfile($url);
exit;
?>