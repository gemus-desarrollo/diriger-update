<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";


$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
}
else {
    $obj= new Tusuario($clink);
}

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$_SESSION['obj']= serialize($obj);
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>CAMBIAR CLAVE</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <script language='javascript' type="text/javascript">
        function validar() {
            var form= document.forms[0];

            if (!Entrada($('#pwd').val())) {
                $('#pwd').focus(focusin($('#pwd')));
                alert('Introduzca su clave actual.');
                return;
            }
            if (!Entrada($('#clave').val())) {
                $('#clave').focus(focusin($('#clave')));
                alert('Introduzca la nueva clave.');
                return;
            }

            if ($('#clave').val() != $('#clave2').val()) {
                $('#clave').focus(focusin($('#clave')));
                alert('No ha repetido la clave correctamente');
                limpiar();
                return;
            }

            if (validar_login(form, true) == false) 
                return;

            document.forms[0].action= '../php/clave.interface.php';
            document.forms[0].submit();
        }

        function limpiar() {
            $('#clave').val('');
            $('#clave2').val('');
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            <?php if (!is_null($error)) { ?>
                alert("<?=str_replace("\n", " ", addslashes($error))?>");
            <?php } ?>
        });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="col-sm-10 col-md-8 col-lg-6">
            
                <div class="card card-primary">
                    <div class="card-header">
                        CAMBIAR CLAVE DE ACCESO
                    </div>

                    <div class="card-body">
                        <form class="form-horizontal" name="fclave" id="fclave" action="javascript:validar()" method="POST">
                            <input type="hidden" name="exect" value="<?=$action?>" />
                            <input type="hidden" name="menu" value="clave" />
                            <input type="hidden" name="id" value="<?=$_SESSION['id_usuario'] ?>" />
                            <input type="hidden" name="usuario" value="<?=$_SESSION['usuario'] ?>" />

                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">
                                    Clave Actual:
                                </label>
                                <div class="col-md-8 col-lg-8">
                                    <input type="password" id="pwd" name="pwd" class="form-control" autocomplete="no">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">
                                    Nueva Clave:
                                </label>
                                <div class="col-md-8 col-lg-8">
                                    <input type="password" id="clave" name="clave" class="form-control" autocomplete="no" value="" onclick="limpiar()">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">
                                    Repita la Clave:
                                </label>
                                <div class="col-md-8 col-lg-8">
                                    <input type="password" id="clave2" name="clave2" class="form-control" autocomplete="no" value="">
                                </div>
                            </div>

                            <!-- buttom -->
                            <div id="_submit" class="btn-block btn-app">
                                <button class="btn btn-primary" type="submit">Aceptar</button>
                                <button class="btn btn-warning" type="reset" onclick="self.location.href='../html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&'">Cancelar</button>
                                <button class="btn btn-danger" type="button" onclick="open_help_window('../help/02_usuarios.htm#02_4.4')">Ayuda</button>
                            </div>

                            <div id="_submited" style="display:none">
                                <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                            </div>

                        </form>
                    </div> <!-- panel-body -->
                </div> <!-- panel -->
            </div>
        </div>

    </div>  <!-- container -->
 </body>
</html>
