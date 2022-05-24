<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
include_once "setup.ini.php";
include_once "class/config.class.php";

include_once "class/base.class.php";
include_once "class/connect.class.php";
include_once "class/usuario.class.php";

$obj= new Tusuario($clink);

$obj->SetIdUsuario($_SESSION['id_usuario']);
$obj->SetConectado(false);

session_destroy();
?>

<script type="text/javascript">
    parent.location= '<?=_SERVER_DIRIGER?>index.php';
</script>
