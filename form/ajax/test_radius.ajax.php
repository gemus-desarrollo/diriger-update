<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */
 
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";
?>

<div id="espere" class="alert alert-info">
    <img src="../img/loading.gif" width="25" height="25" alt="Conectando..." />  Por favor espere.....
</div>

<?php
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../php/class/radius.class.php";

$servers_radius= urldecode($_GET['servers']);
$domain= urldecode($_GET['domain']);
$secret= urldecode($_GET['secret']);
$admin= $_GET['admin'];
$passwd= urldecode($_GET['passwd']);

// Set up all options.
$options = [
    'servers_radius' => $servers_radius,
    'domain' => $domain,
    'secret' => $secret,
    'username' => $admin,
    'password' => $passwd
];

$radius= new Tradius();
$radius->debug= true;
$error= $radius->connect($options);

$radius->close();
?>

<script type="text/javascript">
    $('#espere').hide();
</script>

<?php if ($error) { ?>
    <div class="alert alert-danger"><?= utf8_encode($error)?></div>
<?php } else { ?>
    <div class="alert alert-success">
        OK: El Servidor y la validación RADIUS del usuario <?=$admin?> han respondido correctamente.
    </div>
<?php }  ?>

  <div id="_submit" class="submit btn-block" align="center">
      <button type="reset" class="btn btn-primary" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
  </div>
