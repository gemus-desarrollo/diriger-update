<?php

/**
 * @author muste
 * @copyright 2014
 */


session_start(); 
include_once "../../php/setup.ini.php";
require_once _ROOT_DIRIGER_DIR."php/class/config.class.php";

require_once _PHP_DIRIGER_DIR."config.ini";
require_once _ROOT_DIRIGER_DIR."php/config.inc.php";
require_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/time.class.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/backup.class.php";

set_time_limit(0);
session_cache_expire(720);

$action= !empty($_GET['action']) ? $_GET['action'] : 'form';
$save= !is_null($_GET['save']) ? (int)$_GET['save'] : 1;
$execute= !is_null($_GET['execute']) ? (int)$_GET['execute'] : 0;

$obj= new Tbackup($clink);
$error= null;

defined('_DBSQL_DIRIGER_DIR') or define('_DBSQL_DIRIGER_DIR', _DATA_DIRIGER_DIR."sql"._SLASH_DIRIGER_DIR);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>BACKUP DE BASE DE DATOS</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>

        <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
        <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>

        <script type="text/javascript" src="../../js/home.js"></script>

        <script type="text/javascript" src="../../js/string.js?version="></script>
        <script type="text/javascript" src="../../js/general.js?version="></script>

        <script language="javascript"  type="text/javascript" src="../../js/ajax_core.js"></script>

        <script type="text/javascript" src="../../js/form.js?version="></script>

        <style type="text/css">
            .panel-body .form-group {
                margin-left: 20px;
                margin-right: 20px;
            }
            .winlog {
                max-height: 450px;
                min-height: 200px;
            }
        </style>

        <script type="text/javascript">
            parent.app_menu_functions= false;
            $('#_submit > button').hide();
            $('#_submited').show();

            function _close() {
                parent.activeMenu = 'main';
                parent.location.href='../../html/home.php';
            }
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="app-body form">
            <div class="container">
                <div class="card card-primary">
                    <div class="card-header">BACKUP O SALVA DE BASE DE DATOS</div>
                    <div class="card-body">

                        <form class="form-horizontal" action="#" name="frm" method="get">
                            <div id="wait-alert" class="form-group row">
                                <div class="alert alert-danger">
                                    <img src="../../img/loading.gif" border="none" />
                                    Esta operación puede tardar varios minutos, por favor espere.....
                                </div>
                            </div>

                            <?php if ($execute) { ?>
                            <div id="divlog" class="container-fluid winlog">
                                <div id="winlog" class="textlog"></div>
                            </div>
                            <?php } ?>

                            <div id="error-alert" class="alert alert-danger hidden">
                                Ha ocurrido al menos un error durante la salva del sistema. Por favor, copie los errores y comuníquese inmediatamente con el personal de GEMUS.
                            </div>

                            <div id="download-alert" class="alert alert-success hidden">

                            </div>

                            <!-- buttom -->
                            <div id="_submit" class="btn-block btn-app">
                                <button id="btn-close" class="btn btn-warning" style="display: <?=$action == 'export' ? 'none' : 'inline-block' ?>" type="button" onclick="_close()">Cerrar</button>

                                <?php if ($action == 'form') { ?>
                                    <button class="btn btn-danger"type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                                <?php } ?>
                            </div>

                            <div id="_submited" style="display: none">
                               <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                           </div>
                        </form>

                        <div id="div-ajax-panel" class="">

                        </div>
                    </div> <!-- panel-body -->
                </div> <!-- panel -->
            </div>  <!-- container -->
        </div>
    </body>
</html>


<script type="text/javascript">
    _SERVER_DIRIGER= "<?=_SERVER_DIRIGER?>";

    function goend() {
        $('#divlog').scrollTop($('#winlog').height());
    }

    $(document).ready(function() {
        $('#winlog').mouseover(function() {
            _moveScroll= false;
        });
        $('#winlog').mouseleave(function() {
            _moveScroll= true;
        });

        <?php
        ob_flush();
        flush();
        ob_end_flush();
        ob_start();
              
        if ($_SESSION['output_signal'] == 'shell')
            $execute= 1;

        if ($action != 'form') {
            if ($_SESSION['output_signal'] != 'shell') {
                $obj_sys= new Tclean($clink);
                $occupied= $obj_sys->if_occuped_system();

                if ($occupied[0] && !$execute) {
                    $text= "Por favor verifique. {$occupied[1]} Desea continuar?";
                    ?>
                    confirm("<?=$text?>", function(ok) {
                        if (!ok)
                            parent.location.href= '<?=_SERVER_DIRIGER?>html/home.php';
                        else
                            self.location.href= 'gen_backup.interface.php?execute=1&save=<?=$save?>&verbose=<?=$verbose?>&type=<?=$type?>&action=<?=$action?>';
                    });
                    <?php
                } else {
                    $execute= 1;
        }   }   }
        ?>
   
        <?php
        $_SESSION['in_javascript_block']= true;

        if ($execute && ($action == 'export' && is_null($error))) {
            $obj_sys->init_system();
            $obj_sys->set_system('backupbd', date('Y-m-d H:i:s'));

            $error= $obj->export();
            $obj_sys->set_system();
        }

        if ($execute && $action != 'form') {
            ?>
            $('#winlog').append("<br/>Borrando backups anteriores<br/>");
            <?php
            $path= _DBSQL_DIRIGER_DIR;
            $afiles= $obj->dirfiles($path);
            $count= count($afiles);

            $sp = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '\\' : '/';

            $i= 0;
            foreach ($afiles as $file) {
                ++$i;
                $diff = s_datediff('m', date_create($file['date']), date_create(date('Y-m-d')));
                if ($diff > $config->monthpurge) {
                    ?>
                    $('#winlog').append("<br/>Borrando ficheros de salvas anteriores: {$file['name']} <?=(float)$i/$count?><br/>");
                    <?php
                    unlink("{$path}{$sp}{$file['name']}");
                }
            }
            ?>
            $('#winlog').append('<br/>Terminada la operación<br/>');
            
        <?php } ?>

        var html;

        <?php
        $go_exec= $obj->go_exec;

        if ($execute && ($action == 'export' && $save)) {
            $filename= $obj->filename;
            $url= addslashes(_DBSQL_DIRIGER_DIR).$obj->filename.'.gz';
            unset($obj);
            $clink->close();
        ?>
            $('#wait-alert').hide();

            $('#download-alert').removeClass('hidden');
            $('#download-alert').show();
            html= '<a href="../common/download.php?file=<?= urlencode($url)?>&send_file=1">'+
                '<img src="../../img/menu/pendrive.png" width="30" height="30" />'+
                'Para descargar el paquete <strong><?="{$filename}.gz"?></strong>, por favor, haga clic aquí …'+
            '</a>';
            $('#download-alert').html(html);

            $('#btn-close').show();
            document.open("../common/download.php?file=<?= urlencode($url)?>&send_file=1", "_blank", "width=600,height=300,toolbar=no,location=0, menubar=0, titlebar=0, scrollbars=yes");
        <?php } ?>

        <?php if ($execute && (($action == 'export' && $save == 0) && $go_exec)) { ?>
            parent.location.href= '../../html/home.php';
        <?php } ?>

        <?php if ($execute && ($action == 'export' && (!$go_exec || !empty($error)))) { ?>
            $('#error-alert').html("<?=$error?>");
            $('#error-alert').removeClass('hidden');
            $('#error-alert').show();
        <?php } ?>             
                
    });
</script>

