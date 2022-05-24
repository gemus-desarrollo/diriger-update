<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
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
require_once "../../php/class/ldap.class.php";

$ldap_domain= urldecode($_GET['domain']);
$ldap_ip= urldecode($_GET['servers']);
$ldap_port= urldecode($_GET['port']);
$ldap_admin= $_GET['admin'];
$ldap_passwd= urldecode($_GET['passwd']);
$ldap_chains= urldecode($_GET['cn']);
$ldap_tls= !empty($_GET['tls']) ? 1 : 0;
$ldap_ssl= !empty($_GET['ssl']) ? 1 : 0;


// Set up all options.
$options = [
    'account_suffix' => $ldap_domain,
    'base_dn' => $ldap_chains,
    'domain_controllers' => $ldap_ip,
    'admin_username' => $ldap_admin,
    'admin_password' => $ldap_passwd,
    'real_primarygroup' => '',
    'use_ssl' => $ldap_ssl ? true : false,
    'use_tls' => $ldap_tls ? true : false,
    'recursive_groups' => true,
    'ad_port' => $ldap_port,
    'sso' => ''
];

$cldap= new Tldap();
$error= $cldap->connect($options);

$array_users= null;
if (is_null($error)) {
    $array_users= $cldap->list_users();
    $array_groups= $cldap->list_groups();
}

if (is_null($error) && is_null($array_users)) {
    $error= "<span style='font-weight: bold; color: #002D00'>Ha sido correcta la conexión al Directorio Activo.</span> Pero no se ha podido leer la información de los usuarios. ";
    $error.= "Verifique las Unidades Organizativas definidas.";
}

$cldap->close();
?>

<script type="text/javascript">
    $('#espere').hide();
</script>

<?php if ($error) { ?>
    <div class="alert alert-danger"><?=$error?></div>
<?php } else { ?>
    <div class="alert alert-success">
        OK: El Servidor y la autenticación del usuario <?=$ldap_admin?>@<?=$ldap_domain?> han respondido correctamente.
        <?php if (!is_null($cldap->count_users)) { ?> 
            <br/>Identificados <strong style="color: #8A0000; font-weight: bold"><?=$cldap->count_users?></strong> usuarios del dominio.
            <br/>Identificados <strong style="color: #8A0000; font-weight: bold"><?=$cldap->count_groups?></strong> grupos en el dominio.
        <?php } ?>
    </div>
<?php }  ?>

  <div id="_submit" class="submit btn-block" align="center">
      <button type="reset" class="btn btn-primary" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
  </div>
