<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */
 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";
?>

<div id="alert-loading" class="center-block">
    <div class="alert alert-info">
        <img src="../img/loading.gif" width="25" height="25" alt="cargando..." />  
        Por favor espere.....
    </div>
</div>

<?php
require_once _PHP_DIRIGER_DIR."config.ini";

if (!extension_loaded("curl")) {
?>    
    <label class="alert alert-danger text">
        No est&aacute; instalada la librer&iacute;a CURL. No se podr&acute; realizar la sincronizaci&oacute;n por servivio WEB protocolo HTTP/HTTPS.
        <p>Ejecute <strong>apt-get install php7.x-curl</strong></p>
    </label>
<?php  
    die();
}

$url= urldecode($_GET['url']);
$puerto= $_GET['puerto'];
$protocolo= urldecode($_GET['protocolo']);

$cronos= date('Y-m-d H:i:s');
$url= "{$protocolo}://{$url}:{$puerto}/read_service.php";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, "test=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result= curl_exec ($ch);
if ($errnum= curl_errno($ch))
    $error= curl_strerror ($errornum);
else 
    $error= null;
    
curl_close($ch);

sleep(5);
?>

<script type="text/javascript">
    $("#alert-loading").hide();
</script>

<?php if (!is_null($error)) { ?>
    <div class="alert alert-danger">
        ERROR: El servidor <?=$url?> no responde. No se ha establecido la conexión. <p><?=$error?></p>
    </div>
<?php } else { ?>
    <div class="alert alert-success">
        <p><?=$result?></p>
    </div>
<?php } ?>

  <div id="_submit" class="submit btn-block" align="center">
      <button type="reset" class="btn btn-primary" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
  </div>
