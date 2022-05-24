<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 11/07/14
 * Time: 7:16
 */

session_start();

$csfr_token='123abc';
require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";

require_once "../../php/class/base.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";

require_once "repare_ldap.class.php";
require_once "repare_ldap_list.class.php";

$year= date('Y');
require_once "../../php/class/badger.class.php";

set_time_limit(0);

$execute= $_GET['execute'];
$mix_cargo=  !is_null($_GET['mix_cargo']) ? $_GET['mix_cargo'] : false;


function show_error($sql, $error) {
    echo "<div class=\"alert alert-danger\">";
    echo "$sql => ERROR: $error<br/><br/>";  
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>REPARAR USUARIOS DUPLICADOS AUTOMATICAMENTE</title>

    <?php 
    $dirlibs= "../../";
    require '../../form/inc/_page_init.inc.php'; 
    ?>

    <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" href="../../libs/alert-panel/alert-panel.css">
    <script type="text/javascript" src="../../libs/alert-panel/alert-panel.js"></script>

    <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
    <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" src="../../js/home.js"></script>

    <script type="text/javascript" src="../../js/string.js?version="></script>
    <script type="text/javascript" src="../../js/general.js?version="></script>

    <style type="text/css">
    /* DEFINITION LIST PROGRESS BAR */
    .progress-block .alert {
        margin-bottom: 6px !important;
    }

    label.label {
        font-size: 1.2em !important;
        letter-spacing: 0.2em;
    }

    .textlog {
        font-size: 10px !important;
    }

    body {
        overflow: scroll !important;
    }

    .body {
        margin: 40px 40px 0px 40px;
        background: white;
        padding: 20px;

        overflow-y: visible !important;
    }
    </style>

    <script type="text/javascript">

    function cerrar() {
        self.location.href = 'repare_id_user_ldap.php?execute=0&id_user_fix=0&id_user_delete=0';
    }
    </script>
</head>

<body>
    <div class="container body">
        <h4>Avance del sistema </h4>
        <div id="progressbar-1" class="progress-block">
            <div id="progressbar-1-alert" class="alert alert-warning">
                Esta operación puede durar varios minutos. Por favor espere ...
            </div>
            <div id="progressbar-1-" class="progress progress-striped active">
                <div id="progressbar-1-bar" class="progress-bar bg-warning" role="progressbar"
                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <span class="sr-only"></span>
                </div>
            </div>
        </div>

        <div class="row col-12 mt-3">
            <button type="button" class="btn btn-success ml-2" onclick="cerrar()">Cerrar</button>
        </div>
    </div>

    <div class="container body">
        <?php
        $_SESSION['execfromshell']= false;
        $_SESSION['in_javascript_block']= false;
        
        if ($execute) {  
            $obj= new Trepare_ldap_list($clink);
            $obj->mix_cargo= $mix_cargo;
            $obj->init_list();
            $obj->fix_lista_usuario();  
        }
        ?>    
    </div>

</body>

</html>