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
$ext= substr($obj->filename, (int)strrpos($obj->filename, '.')+1);
$type= GetFileType($ext);
$file = file_get_contents($url);

header("Content-Type: $type");
header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
header('Pragma: public');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Content-Length: '.strlen($file));
header('Content-Disposition: inline; filename="'.basename($url).'";');
ob_clean(); 
flush(); 
echo $file;
?>