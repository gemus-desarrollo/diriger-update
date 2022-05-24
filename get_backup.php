<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

$csfr_token='123abc';
$csfr_token_parent= "456def";

set_time_limit(0);
session_cache_expire(720);
session_start();

require_once "php/setup.ini.php";
require_once "php/class/config.class.php";


require_once "inc.php";
require_once "php/config.inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "php/class/DBServer.class.php";
require_once "php/class/base.class.php";
require_once "tools/dbtools/backup.class.php";
require_once "tools/common/file.class.php";

$action= !empty($_GET['exec']) ? $_GET['exec'] : 'form';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : null;
$type= !is_null($_GET['type']) ? (int)$_GET['type'] : null;

$db_user= !empty($_POST['db_user']) ? $_POST['db_user'] : null;
$db_pass= !empty($_POST['db_pass']) ? $_POST['db_pass'] : null;
$other_system= !is_null($_GET['other_system']) ? (int)$_GET['other_system'] : 0;
$show_mainmenu= !is_null($_GET['show_mainmenu']) ? $_GET['show_mainmenu'] : true;

defined('_DBSQL_DIRIGER_DIR') or define('_DBSQL_DIRIGER_DIR', _DATA_DIRIGER_DIR."sql"._SLASH_DIRIGER_DIR);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>IMPORTANDO BASE DE DATOS</title>

        <?php
        $dirlibs= ""; 
        require 'form/inc/_page_init.inc.php' 
        ?>

        <link rel="stylesheet" href="libs/windowmove/windowmove.css">
        <script type="text/javascript" src="libs/windowmove/windowmove.js"></script>

        <script type="text/javascript" src="js/string.js?version="></script>
        <script type="text/javascript" src="js/general.js?version="></script>

        <link rel="stylesheet" type="text/css" href="css/general.css?version=">

        <script type="text/javascript" src="js/form.js?version="></script>

        <style type="text/css">
            .panel-body .form-group {
                margin-left: 20px;
                margin-right: 20px;
            }
            .winlog {
                max-height: 450px;
                min-height: 200px;
            }
            .file {
                font-size:1em;
                border:none;
                background:none;
                padding:0;
                margin:0;
            }
        </style>

        <script type="text/javascript">
            function writeLog(date, line, divout) {
                $('#winlog').append(line);
            }

            function goend() {
                $('#divlog').scrollTop($('#winlog').height());
            }

            function validar() {
                var form= document.forms[0];

                if (!Entrada($('#db_user').val())) {
                    alert("Debe definir el usuario con nivel de administración para crear la base de datos");
                    return;
                }
                if (!Entrada($('#db_pass').val())) {
                    alert("Escriba la clave del usuario de Base de datos con nivel de administración para crear la base de datos");
                    return;
                }
                if (!Entrada(form.lote.value)) {
                   alert('Debe selecionar el fichero lote que desea cargar al sistema.');
                   return;
                }

                var verbose= $('#verbose').is(':checked') ? 2 : 0;
                var other_system= $('#other_system0').is(':checked') ? 0 : 1;

                if (!other_system) {
                     var file= form.lote.value;
                     var ext= file.substr(file.length-7,7);

                     if (ext != '.sql.gz') {
                         alert('Error en el formato del fichero lote. La extención del fichero indica que no fue generado por el sistema Diriger.');
                         return;
                     }
                }

                var text= "Importar una Base de Datos puede ser una opción muy destructiva si no está seguro de lo que pretende hacer. ";
                text+= "¿Está seguro de querer importar esta base de datos?";
                confirm(text, function(ok) {
                    if (ok)
                        this_1();
                    else
                        return;
                });

                function this_1() {
                    var signal= $('#signal').val();
                    var action= $('#exect').val();
                    var show_mainmenu= $('#show_mainmenu').val();

                    parent.app_menu_functions= false;
                    $('#_submit').hide();
                    $('#_submited').show();

                    var url= "get_backup.php?exec=import&signal="+signal+"&verbose="+verbose+'&other_system='+other_system;
                    url+= '&action='+action+'&show_mainmenu='+show_mainmenu;
                    form.action= url;
                    form.submit();
                }
            }
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <?php
        if ($show_mainmenu) {
            require_once "html/header.php";
        ?>

        <style type="text/css">
            body {
                margin: 0px;
                padding: 0px;
            }
            .app-body {
                margin: 52px 0px 0px 0px;
            }
        </style>
        <?php } ?>

        <div class="app-body form">
                <div class="container">
                <div class="card card-primary">
                    <div class="card-header">CARGAR BASE DE DATOS</div>

                    <div class="card-body">
                        <?php if ($action == 'form') { ?>
                        <form action='javascript:validar()' class="form-horizontal" method=post enctype="multipart/form-data" >
                            <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                            <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />
                            <input type="hidden" name="show_mainmenu" id="show_mainmenu" value="<?=$show_mainmenu?>" />

                             <fieldset>
                                <legend>Base de datos</legend>
                                <div class="form-group row">
                                    <label class="col-form-label col-2">
                                        Usuario de BD:
                                    </label>
                                    <div class="col-6">
                                        <input type="text" id="db_user" name="db_user" class="form-control" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-form-label col-2">
                                        Clave:
                                    </label>
                                    <div class="col-6">
                                        <input type="password" id="db_pass" name="db_pass" class="form-control" />
                                    </div>
                                </div>
                            </fieldset>
                            <?php } ?>

                            <?php
                            if ($action == 'import') {
                                $clink= new DBServer($_SESSION['db_ip'], null, $db_user, $db_pass);
                                $clink= empty($clink->error) ? $clink : false;

                                if (!$clink) { ?>
                                    <div class="alert alert-danger">
                                        No ha sido posible establecer comunicación con el servidor. Debera de revisar el fichero <strong>php/config.ini</strong>,
                                        Tambien es posible que el usuario y/o la clave de base de datos sea incorrecta.
                                    </div>
                                <?php
                                }

                                if ($other_system) {
                                    $db= $clink->select_db($_SESSION['db_name']);

                                    if ($db) {
                                        $sql = "select * from _config order by chronos desc limit 1";
                                        $result = $clink->query($sql);

                                        $row = $clink->fetch_array($result);
                                        $_SESSION['mac'] = $row['MAC'];
                                        $_SESSION['ip_app'] = $row['ip_app'];
                                    } else {
                                    ?>
                                        <div class="alert alert-danger">
                                            No existe la base de datos <strong><?=$_SESSION['db_name']?></strong> a ser actualizada.
                                        </div>
                                    <?php
                            }   }   }
                            ?>

                            <?php if ($action == 'form') { ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="other_system" id="other_system0" value="0" checked="checked" />
                                    La salva de la Base de Datos a cargar fue <strong>creada desde el sistema Diriger</strong>.
                                    El sistema validará su origen y codificación de caracteres.
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="other_system" id="other_system1" value="1" />
                                    La salva de la Base de Datos a cargar ha sido <strong>generada desde un Gestor de Base de Datos</strong>.
                                    Ejemplo phpMyAdmin. El fichero a utilizar esta descomprimido.
                                </label>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-3 col-lg-3 pull-left">
                                    Backup o salva de la base de Datos:
                                </label>
                                <div class="col-md-5 col-lg-5 pull-left">
                                    <input class="file" type="file" name="lote" id="lote" />
                                </div>
                            </div>
                            <?php } ?>

                            <?php
                            if ($action == 'import' && $clink && (!$other_system || ($other_system && $db))) {
                                $obj= new Tbackup($clink);
                                $file= new Tfile(null);
                                ?>

                                <div id="divlog" class="container-fluid winlog">
                                    <div id="winlog" class="textlog">
                                        <?php
                                        $error= $file->upload(_DBSQL_DIRIGER_DIR);
                                        if (is_null($error) && !$other_system)
                                            $error= $file->uncompress(_DBSQL_DIRIGER_DIR);

                                        if (!is_null($error)) { ?>
                                            <div id="backup-error" class="alert alert-danger"><?=$error?></div>
                                        <?php } else { ?>
                                            <?php if (!$other_system)  {?><div class="alert alert-success">Fichero descomprimido con exito</div><?php } ?>
                                            <img id="img-loading" src="img/loading.gif" width="25" height="25" style="display:block" />Cargando backup, leyendo del fichero <?="$file->filename\n\n"?>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($action == 'form') { ?>
                                <div id="_submit" class="btn-block btn-app">
                                    <button type="submit" class="btn btn-primary">Aceptar</button>
                                    <?php if (is_null($signal)) { ?>
                                        <button type="reset" class="btn btn-warning" onclick="self.location.href='index.php'">Cancelar</button>
                                    <?php } else  { ?>
                                        <button type="reset" class="btn btn-warning" onclick="self.location.href='html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&'">Cancelar</button>
                                    <?php } ?>
                                </div>

                                <div id="_submited" class="submited" align="center" style="display:none">
                                    <img src="img/loading.gif" alt="cargando" />     Por favor espere ..........................
                                </div>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $_SESSION['in_javascript_block']= false;

        if ($action == 'import' && $clink && (!$other_system || ($other_system && $db))) {
            bar_progressCSS(0, "Espere se esta borrando la Base de Datos....", 0.3);

            $error= $obj->get_backup(_DBSQL_DIRIGER_DIR.$file->filename);

            if (!is_null($error))
                bar_progressCSS(0, "ERROR: $error", 0);
            else {
                bar_progressCSS(0, "Liberando espacio en disco....", 0.9);
                $file->delete(_DBSQL_DIRIGER_DIR.$file->filename);

                bar_progressCSS(0, "Operacions terminda", 1);
            }
            $obj->unlock_system();

            $_SESSION['in_javascrpt_block']= null;
        ?>

            <script type="text/javascript">
                document.getElementById("img-loading").style.display= 'none';
            </script>
        <?php } ?>
    </body>
</html>