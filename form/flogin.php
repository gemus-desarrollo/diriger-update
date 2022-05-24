<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/inc.php";
require_once _PHP_DIRIGER_DIR."config.ini";

require_once "../php/config.inc.php";
require_once "../php/class/base.class.php";

$signal= !is_null($_GET['signal']) ? $_GET['signal'] : 'login'; // caso especial para la autenticación

require_once "../php/class/connect.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";

$index_page = ($signal == 'index' || $signal == 'login') ? '/index.php' : '/tools/lote/index.php';
$index_page = _SERVER_DIRIGER . $index_page;

if (isset($_SESSION['obj'])) {
    $obj = unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action = $obj->action;
} else {
    $obj = new Tusuario($clink);
}

$clave = $obj->GetClave();
$usuario = $obj->GetUsuario();
$cant = $obj->if_unique_username();

$id_usuario_array = array();
$array_procesos = array();

if (empty($cant))
    $id_usuario_array[] = $_SESSION['local_proceso_id'];
else
    foreach ($obj->array_procesos as $prs)
        $id_usuario_array[] = $prs['id_usuario'];

$obj_prs = new Tproceso($clink);
$obj_prs->SetYear(date('Y'));
$obj_prs->get_procesos_by_user(null, null, null, null, null, $id_usuario_array);

foreach ($obj_prs->array_procesos as $prs)
    $array_procesos[$prs['id']] = $prs['id'];

$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <title>LOGIN DEL SISTEMA</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <title>GRUPO DE USUARIOS</title>

        <?php require 'inc/_page_init.inc.php'; ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->
        <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
        <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

        <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
        <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

        <script type="text/javascript" src="../js/form.js"></script>

        <style type="text/css">

        </style>

        <script language='javascript' type="text/javascript" charset="utf-8">
            function validar() {
                var form = document.forms[0];

                if (validar_login(form) == false)
                    return;

                document.forms[0].action = '../php/login.php?signal=login';
                document.forms[0].submit();
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                <?php if (!is_null($error)) { ?>
                    alert("<?= str_replace("\n", " ", $error) ?>");
                <?php } ?>
            });
        </script>

    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

       <div class="app-body form">
                <div class="container" style="max-width: 60%;">
                 <div class="card card-danger">
                     <div class="card-header">IDENTIFICACIÓN DE USUARIO</div>
                     <div class="card-body">

                         <label class="alert alert-info">
                             Seleccione la unidad organizativa donde usted está registrado en el sistema. Si tiene alguna duda consulte al administrador del Sistema
                         </label>

                         <form class="form-horizontal" action='javascript:validar()' method=post>
                             <input type=hidden name=exect value=login />
                             <input type=hidden name=menu value='login' />

                             <div class="form-group row">
                                 <label class="col-form-label col-xs-2 col-1">
                                     Usuario:
                                 </label>
                                 <div class=" col-xs-10 col-11">
                                     <input id="usuario" name="usuario" class="form-control" value="<?= $usuario ?>">
                                 </div>
                             </div>
                             <div class="form-group row">
                                 <label class="col-form-label col-xs-2 col-1">
                                     Clave:
                                 </label>
                                 <div class="col-xs-10 col-11">
                                     <input type="password" id="clave" name="clave" class="form-control"  value="<?=$clave?>" />
                                 </div>
                             </div>
                             <div class="form-group row">
                                 <label class="col-form-label col-xs-2 col-1">
                                     Unidad:
                                 </label>
                                 <div class="col-xs-10 col-11">
                                     <?php
                                     if (is_array($obj_prs->array_procesos)) {
                                         reset($obj_prs->array_procesos);
                                         foreach ($obj_prs->array_procesos as $row) {
                                             ?>
                                             <input type="hidden" id="proceso_code_<?= $row['id'] ?>" name="proceso_code_<?= $row['id'] ?>" value="<?=$row['id_code']?>" />
                                             <?php
                                         }
                                     }

                                     if (is_array($obj_prs->array_procesos))
                                         reset($obj_prs->array_procesos);
                                     ?>

                                     <select name="proceso" id="proceso" class="form-control" onchange="refreshp()">
                                         <?php foreach ($obj_prs->array_procesos as $row) { ?>
                                             <option value="<?= $row['id'] ?>" <?php if ($row['id'] == $_SESSION['local_proceso_id']) echo "selected='selected'"; ?>><?=$row['nombre'] . ' (' . $Ttipo_proceso_array[$row['tipo']] . ')' ?></option>
                                         <?php } ?>
                                     </select>
                                 </div>
                             </div>
                             <hr></hr>

                             <!-- buttom -->
                             <div id="_submit" class="btn-block btn-app">
                                 <button class="btn btn-primary" type="submit">Aceptar</button>
                                 <button class="btn btn-warning" type="reset" onclick="self.location.href = '../index.php'">Cancelar</button>
                                 <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                             </div>

                             <div id="_submited" style="display:none">
                                 <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                             </div>

                         </form>

                     </div> <!-- panel-body -->
                 </div> <!-- panel -->
             </div>  <!-- container -->

       </div>

    </body>
</html>
