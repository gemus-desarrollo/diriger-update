<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();

require_once "inc.php";
$csfr_token='123abc';
$csfr_token_parent= "456def";
require_once "php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";
require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/DBServer.class.php";

set_time_limit(0);

$execfromshell= $argv[1] ? true : false;
$show_mainmenu= !is_null($_GET['show_mainmenu']) ? $_GET['show_mainmenu'] : true;
$show_mainmenu= !$execfromshell ? $show_mainmenu : false; 
?>

<?php if (!$execfromshell) { ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>DESBLOQUEO DE ACCESO DE USUARIOS AL SISTEMA</title>

    <?php 
    $dirlibs= "";
    require 'form/inc/_page_init.inc.php'; 
    ?>

    <link rel="stylesheet" href="libs/windowmove/windowmove.css">
    <script type="text/javascript" src="libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" src="js/string.js?version="></script>
    <script type="text/javascript" src="js/general.js?version="></script>

    <link rel="stylesheet" type="text/css" href="css/general.css?version=">

    <script type="text/javascript" src="js/form.js?version="></script>

    <style type="text/css">
    body {
        background: white;
    }
    ._text {
        margin-left: 20px;
        padding: 5px;
        background-color: greenyellow;
        border: 1px solid goldenrod;
        color: #000000;
        width: 80%;
        position: relative;
    }
    ._msg {
        margin-left: 20px;
        padding: 5px;
        width: 80%;
    }
    #lote {
        color: #333;
    }
    #lote:hover {
        color: #800000;
        font-weight: bold;
        text-decoration: underline;
    }
    </style>

    <script type="text/javascript">
    function validar() {

    }
    </script>
</head>

<body>
    <script type="text/javascript" src="libs/wz_tooltip/wz_tooltip.js"></script>
    <?php } ?>

    <?php 
        if ($show_mainmenu) {
            include_once "html/header.php";
        ?>

    <style type="text/css">
    body {
        margin: 0px;
        padding: 0px;
    }
    .app-body {
        margin: 52px 0px 0px 0px !important;
    }
    </style>
    <?php } ?>

    <?php if (!$execfromshell) { ?>
    <div class="app-body container table justify-content-center">
    <?php } ?>

        <?php
        $clink = new DBServer($_SESSION['db_ip'], $_SESSION['db_name'], $_SESSION['db_user'], $_SESSION['db_pass']);
        $clink = empty($clink->error) ? $clink : false;

        if (!$clink) {
        ?>

        <?php if (!$execfromshell) { ?>
        <div class="alert alert-danger container" style="margin-top: 10px;">
            No hay conexión con el servidor. Consulte a su administrador.
        </div>
        <?php } else { ?>
        <?="No hay conexión con el servidor. Consulte a su administrador."?>
        <?php } ?>
        <?php
            die(); 
        }    
        ?>

        <?php if (!$execfromshell) { ?>
        <div class="alert alert-info container" style="margin-top: 10px;">Desbloqueo de funciones</div>

        <div class="container">
            <?php } else { ?>
            <?="\n\n ************************** Desbloqueo de funciones: ************************ "?>
            <?php } ?>
            <?php
                $sql= "select * from tsystem where fin is null";
                $result= $clink->query($sql);

                while ($row = $clink->fetch_array($result)) {
                    $sql= "update tsystem set cronos = now(), fin= now() where id = {$row['id']}";
                    $_result= $clink->query($sql);
                    $cant = $clink->affected_rows($_result);

                    if ($cant > 0) {
                        if (!$execfromshell) 
                            echo "<p class='text text-danger'>Desbloqueado la funcion: <strong>{$row['action']}</strong> iniciada:{$row['inicio']}  </p>";
                        else
                            echo "\nDesbloqueado la funcion: {$row['action']} iniciada:{$row['inicio']} \n";
                    }                
                }
                ?>
            <?php if (!$execfromshell) { ?>
        </div>

        <div class="alert alert-info container" style="margin-top: 10px;">Desbloqueo de usuarios</div>
        <br />

        <div class="container">
            <?php } else { ?>
            <?="\n\n ********************* Desbloqueo de usuarios: ******************************* "?>
            <?php } ?>
            <?php
                $sql = "select * from tusuarios where acc_sys >= 2";
                $result = $clink->query($sql);

                while ($row = $clink->fetch_array($result)) {
                    $sql = "update tusuarios set acc_sys= (acc_sys - 2) where acc_sys > 1 and id = " . $row['id'];
                    $_result= $clink->query($sql);
                    $cant = $clink->affected_rows($_result);

                    if ($cant > 0) {
                        $nombre = "{$row['nombre']}, {$row['cargo']}";
                    
                        if (!$execfromshell)
                            echo "<p class='text text-danger'>Desbloqueado el usuario: <strong>$nombre</strong> </p>";
                        else 
                            echo "\nDesbloqueado el usuario: $nombre";
                    }
                }

                $sql = "update tusuarios set acc_sys= 0 where acc_sys < 0 or acc_sys > 1";
                $result = $clink->query($sql);
                ?>

            <?php if (!$execfromshell) { ?>
        </div>

        <div class="btn-block btn-app">
            <button type="button" class="btn btn-success btn-md" onclick="parent.location.href='index.php'">
                Pagina de Inicio
            </button>
        </div>
    </div>
</body>

</html>
<?php } ?>