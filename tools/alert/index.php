<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/7/2017
 * Time: 11:13
 */

session_start();
$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";

defined('_TIME_ALARM') or define('_TIME_ALARM', 15);

require_once "php/base_alert.class.php";
require_once "php/alert.class.php";

$bell= !empty($_GET['bell']) ? $_GET['bell'] : false;
$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;

$cronos= date('Y-m-d H:i:s');
$date_cutover= add_date($cronos, 0, 0, 0, 0, (-1)*_TIME_ALARM, 0);

$obj_user= new Tusuario($clink);

$obj= new Talert($clink);
$obj->SetIdUsuario($_SESSION['id_usuario']);
$obj->minute_alarm= _TIME_ALARM;
$obj->set_cronos($cronos);

if (!empty($id_evento)) {
    $obj->SetActive($id_evento);
    exit;
}

// para pruebas
// $bell= true;
$obj->select_events();

$result= $obj->listar();
$cant= $obj->GetCantidad();
if (empty($cant)) 
    exit;

$i= 0;
while ($row= $clink->fetch_array($result)) {
    if (!boolean($row['active'])) 
        continue;

    if (strtotime($row['fecha_inicio_plan']) < strtotime($date_cutover)) {
        $obj->SetActive($row['id_evento']);
    }

    ++$i;
    $email= $obj_user->GetEmail($row['id_responsable']);
    $emisor= $email['nombre'];
    if (!empty($email['cargo'])) 
        $emisor.= ", {$email['cargo']}";
    if (!empty($row['funcionario'])) 
        $emisor.= "<p>{$row['funcionario']}</p>";

    $html.= odbc2ampm($row['fecha_inicio_plan']);
    $html.= "\n{$row['nombre']}";
    $array[]= array('num_msg' => $i, 'texto' => $html, 'emisor' => $emisor, 'id_evento' => $row['id_evento']);
} 

if ($i > 0) {
    $json= json_encode($array, JSON_PRETTY_PRINT); //lo codifica a json, JSON_PRETTY_PRINT lo hace agradable a la vista
    echo $json;
}
exit;