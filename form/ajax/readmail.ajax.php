<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";

require_once "../../php/class/pop3/pop3.class.php";

$uid= $_GET['uid'];

$pop3= new POP3();
$maillink= $pop3->Connect();
$pop3->FetchMail($uid, false);

 if (is_null($maillink)) { ?>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" media="screen" href="../css/main.css?version=">
    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css?version=">
</head>

<body> <br /><br />
    <center>
        <div class=_msg_error>ERROR: El servidor de correo no responde. No se ha establecido la conexion</div>
    </center>
</body>

</html>

<?php }
    echo $pop3->html
?>