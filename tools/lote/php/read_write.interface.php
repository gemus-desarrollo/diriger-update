<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */


session_start();
$csfr_token='123abc';
$csfr_token_parent= "456def";
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";

$nivel_user= _GLOBALUSUARIO;
require_once "connect.class.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../../php/config.inc.php";
require_once "../../common/file.class.php";
require_once "../../../tools/lote/php/baseLote.class.php";
require_once "../../../php/class/proceso.class.php";

if (is_null($origen)) {
    $origen= $_GET['origen'];
    $observacion= !empty($_GET['observacion']) ? $_GET['observacion'] : null;
    $fecha= !empty($_GET['fecha']) ? $_GET['fecha'] : null;
}

$cronos= date('Y-m-d H:i:s');
$file= null;
$error= null;
$errornum= null;

$obj_prs= new Tproceso($uplink);
$obj_prs->get_proceso_from_code($origen);
$id_origen= $obj_prs->GetId();
$proceso_name= $obj_prs->GetNombre();
$origen_code= $obj_prs->GetCodigo();
$puerto= $obj_prs->GetPuerto();

$obj_lote= new TbaseLote($uplink);
$obj_lote->id_origen= $id_origen;
$obj_lote->id_destino= null;
$obj_lote->get_last_date_synchronization('import');
$fecha= !empty($obj_lote->cronos_cut) ? $obj_lote->cronos_cut : $obj_lote->date_cutover;
if (empty($fecha)) 
    $fecha= date('Y')."-01-01 00:00";

$url= $obj_prs->GetProtocolo().'://'.$obj_prs->GetURL();
$url.= !empty($puerto) ? ":$puerto" : "";
$url.= "/read_service.php?";
$var= "destino={$config->location}&observacion=&signal=webservice&fecha=".urlencode($fecha);

$tdeletes= !empty($obj_lote->last_time_tables['_tdeletes'][0]) ? urlencode($obj_lote->last_time_tables['_tdeletes'][0]) :  urlencode($fecha);
$treg_evento= !empty($obj_lote->last_time_tables['_treg_evento'][0]) ? urlencode($obj_lote->last_time_tables['_treg_evento'][0]) :  null;
$tproceso_eventos= !empty($obj_lote->last_time_tables['_tproceso_eventos'][0]) ? urlencode($obj_lote->last_time_tables['_tproceso_eventos'][0]) :  null;
$tusuario_eventos= !empty($obj_lote->last_time_tables['_tusuario_eventos'][0]) ? urlencode($obj_lote->last_time_tables['_tusuario_eventos'][0]) :  null;
$treg_objetivo= !empty($obj_lote->last_time_tables['_treg_objetivo'][0]) ? urlencode($obj_lote->last_time_tables['_treg_objetivo'][0]) :  null;
$treg_inductor= !empty($obj_lote->last_time_tables['_treg_inductor'][0]) ? urlencode($obj_lote->last_time_tables['_treg_inductor'][0]) :  null;
$treg_perspectiva= !empty($obj_lote->last_time_tables['_treg_perspectiva'][0]) ? urlencode($obj_lote->last_time_tables['_treg_perspectiva'][0]) :  null;
$treg_real= !empty($obj_lote->last_time_tables['_treg_real'][0]) ? urlencode($obj_lote->last_time_tables['_treg_real'][0]) :  null;
$treg_plan= !empty($obj_lote->last_time_tables['_treg_plan'][0]) ? urlencode($obj_lote->last_time_tables['_treg_plan'][0]) :  null;
$tinductor_eventos= !empty($obj_lote->last_time_tables['_tinductor_eventos'][0]) ? urlencode($obj_lote->last_time_tables['_tinductor_eventos'][0]) :  null;

$var.= "&tdeletes=$tdeletes&treg_evento=$treg_evento&tproceso_eventos=$tproceso_eventos&tusuario_eventos=$tusuario_eventos";
$var.= "&treg_objetivo=$treg_objetivo&treg_inductor=$treg_inductor&treg_perspectiva=$treg_perspectiva&treg_real=$treg_real";
$var.= "&treg_plan=$treg_plan&tinductor_eventos=$tinductor_eventos";

echo "----------- {$url}{$var} --------------";
outputmsg("\n {$url}{$var}\n", false, false);
        
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $var);
curl_setopt($ch, CURLOPT_TIMEOUT, 9999999999999999999999999999);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$remote_output = curl_exec($ch);
curl_close($ch);

if (stripos($remote_output, "ERROR_WEB:") !== false) {
    $i= stripos($remote_output, "ERROR_WEB:");
    $errnum= substr($remote_output, $i+10);
    if (empty($remote_output)) {
        $error= "El servidor devolvió una cadena en blanco.";
        $errnum = 0;
    }    
    if ($errnum == 1)
        $error= "ERROR: No se ha recivido el origen de la peticion.";
    if ($errnum == 2)
        $arror= "ERROR:No se ha establecido comunicación con el servidor.";
    if ($errnum == 3)
        $error= "No esta configurado para la sincronización por acceso mediante servicio Web protocolo http.";
    if ($errnum == 6)
        $error= "ERROR: $remote_output";
} else {
    $date= date('Y-m-d-H-i', strtotime($cronos));
    $file= "lote_{$proceso_name}_{$origen_code}_{$config->location}_{$date}.xml.gz.mcrypt";
    $file= str_replace(":","_",$file);
    $file= str_replace("-","_",$file);
    $file= str_replace(" ","_",$file);    
    
    if (!empty($remote_output)) {
        $url= _IMPORT_DIRIGER_DIR.$file;
        $fp= fopen($url, 'w');
        $remote_output= trim($remote_output);
        fwrite($fp, $remote_output);
        fclose($fp);

        $signal= "webservice";
        if (!$execfromshell)
            $signal= 'form';

        include "import.interface.php";        
    } else 
        $error= "ERROR: $file esta vacio imposible leer el fichero";

}    
