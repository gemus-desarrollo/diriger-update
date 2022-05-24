<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start(); 
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";

include_once _PHP_DIRIGER_DIR."config.ini";
include_once "../../../php/config.inc.php";
include_once "../../common/file.class.php";

$exect= $_GET['exect'];
$action= !empty($_GET['action']) ? $_GET['action']: 'resume';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'home';

$file= !empty($_GET['file']) ? $_GET['file'] : null;
$destino= !empty($_GET['destino']) ? $_GET['destino'] : null;
$observacion= !empty($_GET['observacion']) ? $observacion : null;
$fecha= !empty($_GET['fecha']) ? $_GET['fecha'] : null;

$lote= !empty($_GET['lote']) ? $_GET['lote'] : null;

$tmp_name = urlencode($_FILES["lote"]["tmp_name"]);
$lote= urlencode($_FILES["lote"]["name"]);

$error= null;

if ($_FILES["lote"]["error"] == UPLOAD_ERR_OK) {
    $url= _IMPORT_DIRIGER_DIR.$_FILES["lote"]["name"];
    $ok= move_uploaded_file($_FILES["lote"]["tmp_name"], $url);
    
    preg_match('/[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{2}_[0-9]{2}/', $url, $array);
    $array= preg_split('(_)i', $array[0]);
    $fecha= "{$array[0]}-{$array[1]}-{$array[2]} {$array[3]}:{$array[4]}";    
}

if (empty($ok)) {
    $error= "El fichero lote no ha sido subido al servidor, por lo que no se puede iniciar su carga ";
    $error.= "y la actualizacion del sistema.";
    $action= 'resume';
}

$url= "?signal=$signal&action=$action&file=$file&lote=$lote&fecha=$fecha&observacion=$observacion";
$url.= "&exect=$exect&destino=$destino&tmp_name=$tmp_name&error=$error";
?>

<script language="javascript">
    // alert('<?=$url?>');
    parent.location.href= '../php/import.interface.php' + '<?=$url?>';
</script>
