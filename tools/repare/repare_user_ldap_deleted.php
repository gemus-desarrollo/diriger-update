<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 06/05/2020
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

set_time_limit(0);

$execute= is_null($execute) ? $_GET['execute'] : $execute;

function execute_repare_user_deleted($id_user) {
    global $clink;

    $sql= "update tusuarios set eliminado= null, user_ldap= null where id = $id_user";
    $clink->query($sql);
    $sql= "update tusuario_grupos set eliminado= null where id_usuario = $id_user";
    $clink->query($sql);
}

if ($execute) {
    execute_repare_user_deleted($_GET['id_user']);
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>RECUPERAR LOS USUARIOS ELIMINADOS</title>

        <?php 
        $dirlibs= "../../";
        require '../../form/inc/_page_init.inc.php'; 
        ?>    
        <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>

        <link rel="stylesheet" href="../../libs/windowmove/windowmove.css">
        <script type="text/javascript" src="../../libs/windowmove/windowmove.js"></script>  

        <script type="text/javascript" src="../../js/home.js"></script>

        <script type="text/javascript" src="../../js/string.js?version="></script>
        <script type="text/javascript" src="../../js/general.js?version="></script>
    
        <style type="text/css">
            /* DEFINITION LIST PROGRESS BAR */
            .progress-block .alert {
                margin-bottom: 6px!important;
            }  
            label.label {
                font-size: 1.2em!important;
                letter-spacing: 0.2em;
            }
            .textlog {
                font-size: 10px!important;
            }
            .body {
                margin: 40px 40px 0px 40px;
                background: white;
                padding: 20px;
                
                overflow-y: no-display;
            }
        </style>
        <script type="text/javascript">
            function reconectar(id_user) {
                self.location.href= 'repare_user_ldap_deleted.php?execute=1&id_user='+id_user;
            }
        </script>
    </head>    
    <body>
        <?php
        $sql= "select tusuarios.*, tprocesos.nombre as proceso from tusuarios, tprocesos ";
        $sql.= "where tusuarios.id_proceso = tprocesos.id and eliminado is not null order by eliminado desc, nombre asc";
        $result= $clink->query($sql);
        ?>
        <div class="app-body container-fluid table onebar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true" data-show-columns="true">
                <thead>
                    <th>id</th>
                    <th></th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Unidad Organizativa</th>
                    <th>Eliminado</th>
                </thead>
                <tbody>
                    <?php while ($row= $clink->fetch_array($result)) { ?>
                    <tr>
                        <td>
                        <?=$row['id']?>
                        </td>
                        <td>
                        <button class="btn btn-danger btn-sm" onclick="reconectar(<?=$row['id']?>)">Reconectar</button>
                        </td>
                        <td>
                        <?php if ($row['user_ldap']) { ?>
                        <i class="fa fa-windows ml-1 mr-2"></i>
                        <?php } ?>                        
                        <?=$row['usuario']?>
                        </td>
                        <td><?=textparse($row['nombre'])?></td>
                        <td><?=textparse($row['cargo'])?></td>
                        <td><?=textparse($row['proceso'])?></td>
                        <td><?=$row['eliminado']?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>        
        </div>
    </body>
</html>

