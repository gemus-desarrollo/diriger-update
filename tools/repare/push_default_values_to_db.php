<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 06/05/2020
 * Time: 7:16
 */

session_start();

require_once "../../php/setup.ini.php";
require_once _PHP_DIRIGER_DIR."config.ini";    

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/base.class.php";

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";

require_once "../../tools/dbtools/update.functions.php";

set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;

/*
 * Cargar la tabla ttipo_auditorias
 */
set_91();

/*
 * Cargar la tabla ttipo_reuniones
 */
set_92();
?>

    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

            <title>CARGAR VALORES POR DEFECTO</title>

            <?php 
            $dirlibs= "../../";
            require '../../form/inc/_page_init.inc.php'; 
            ?>    

            <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
            <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>  

            <script type="text/javascript" src="../../js/home.js"></script>

            <script type="text/javascript" src="../../js/string.js?version="></script>
            <script type="text/javascript" src="../../js/general.js?version="></script>
        </head>

    <body>
        <div class="container body">
            <div class="alert alert-success">
                Todos los datos cargados
            </div>  
        </div> 
    </body>
</html>