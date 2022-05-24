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

require_once "../../php/class/library.php";
require_once "../../php/class/library_string.php";
require_once "../../php/class/library_style.php";

require_once "repare_ldap.class.php";
require_once "repare_ldap_list.class.php";

set_time_limit(0);

$execute= $_GET['execute'];
$error= null;
$obj= new Trepare_ldap($clink);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>QUITAR LOS USUARIO DUPLICADOS POR EL LDAP</title>

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

    <script type="text/javascript">
    function ejecutar(index) {
        var id = $('#id' + index).val();
        var id_origen = $('#input_origen' + index).val();
        var id_new = $('#input_new' + index).val();
        var mix_cargo= $('#mix_cargo').is(':checked') ? 1 : 0;

        if ((id_origen == undefined || id_origen.length == 0) && (id_new == undefined || id_new.length == 0)) {
            id_origen = id;
            id_new = 0;
        }

        var url = 'repare_duplicate_user.php?execute=1&id_user_fix=' + id_origen + '&id_user_delete=' + id_new;
        url+= '&mix_cargo='+mix_cargo;
        self.location.href = url;
    }

    function execute_automatic() {
        var mix_cargo= $('#mix_cargo').is(':checked') ? 1 : 0;
        var url = "repare_duplicate_user_list.php?execute=1&mix_cargo="+mix_cargo;
        self.location.href = url;
    }

    $(document).ready(function() {
        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <div class="app-body container-fluid table" style="padding: 30px;">
        <?php if (!empty($error)) { ?>
        <div class="alert alert-danger container-fluid" style="margin-top: 20px;">
            <?=$error?>
        </div>
        <?php } ?>

        <div id="toolbar">
            <div class="btn-toolbar">
                <div class="row">
                    <div class="col-2">
                        <button class="btn btn-danger" onclick="execute_automatic();">
                            <i class="fa fa-recycle"></i>Reparación automatica
                        </button>                    
                    </div>
                    <div class="col-10">
                        <div class="form-check ml-2">
                            <input type="checkbox" class="form-check-input" id="mix_cargo" name="mix_cargo" value="1" />
                            Se transferirán el cargo, la pertenencia a las unidades organizativas y los permisos de usuario que sera eliminado hacia el usuario final.
                        </div>
                        <div class="alert alert-danger ml-1"> 
                            El resultado de la <strong>reparación automática</strong> debe ser revisado cuidadosamente, 
                            porque los usuarios pueden aparecer en entidades o unidades a la que no pertenecen
                        </div>                     
                    </div>
                </div>
            </div>
        </div>

        <table id="table" class="table table-striped" data-toolbar="#toolbar"
        data-toggle="table" data-search="true" data-show-columns="true">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Fecha y Hora</th>
                    <th>LDAP</th>
                    <th>Eliminado</th>
                    <th>Fijar</th>
                    <th>Eliminar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i= 0;
                $result= $obj->lista();
                while ($row= $clink->fetch_array($result)) {
                    ++$i;
                ?>
                <tr>
                    <td>
                        <?=$row['id']?>
                        <input type="hidden" id="id<?=$i?>" name="id<?=$i?>" value="<?=$row['id']?>" />
                    </td>
                    <td><?=$row['usuario']?></td>
                    <td><?=$row['nombre']?></td>
                    <td><?=$row['email']?></td>
                    <td><?=$row['cronos']?></td>
                    <td>
                        <?php if (!empty($row['user_ldap'])) { ?>
                        <i class="fa fa-windows"></i>
                        <?php } ?>
                    </td>
                    <td><?=$row['eliminado']?></td>
                    <td>
                        <input type="text" class="form-control" id="input_origen<?=$i?>" name="input_origen<?=$i?>"
                            value="" />
                    </td>
                    <td>
                        <input type="text" class="form-control" id="input_new<?=$i?>" name="input_new<?=$i?>"
                            value="" />
                    </td>
                    <td>
                        <button class="btn btn-danger" onclick="ejecutar(<?=$i?>)">Ejecutar</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>