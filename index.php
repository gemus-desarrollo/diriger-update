<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

// header('Location: http://nacional.gemus.local/index.php?ID=75');

// Para mostrar pagina de mantenimiento descomentaree la linea siguiente
// header('Location: support.html');

session_start();
$csfr_token='123abc';
$csfr_token_parent= "456def";
require_once "php/setup.ini.php";

require_once _PHP_DIRIGER_DIR."config.ini";
require_once "inc.php";

$_SESSION['update']= _UPDATE_DIRIGER;
$_SESSION['version']= _VERSION_DIRIGER;

$_SESSION["_DB_SYSTEM"]= defined('_DB_SYSTEM') ? _DB_SYSTEM : "mysql";
$signal= 'login';
$_SESSION['id_usuario']= -1;
$nivel_user= _SUPERUSUARIO;
$_SESSION['_ctime']= 1;

require_once "php/class/DBServer.class.php";
require_once "tools/lote/php/connect.class.php";
require_once "php/class/proceso.class.php";

$clink= $uplink;
$cant_prs= 1;

if ($clink) {
    $obj_prs= new Tproceso($clink);
    $result= $obj_prs->listar_entity(true);
    $cant_prs= $obj_prs->GetCantidad();
}

$id_index= !empty($_GET['id']) ? $_GET['id'] : null;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$jaja1= !empty($id_index) ? preg_match_all("/(<(.*?)>)/i", $id_index) : null;
$jaja2= !empty($error) ? preg_match_all("/(<(.*?)>)/i", $error) : null;

$id_index= !empty($id_index) ? strip_tags($id_index) : null;
$error= !empty($error) ? strip_tags($error) : null;

include 'index.inc';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="icon" type="image/png" href="img/gemus_logo.png">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

        <title>
            Diriger v<?= $_SESSION['version'] ?> CUADRO DE MANDO INTEGRAL - <?= $_SESSION['empresa'] ?>
        </title>

        <?php
        $dirlibs= ""; 
        require 'form/inc/_page_init.inc.php' 
        ?>

        <!-- Bootstrap core JavaScript
    ================================================== -->
        <script type="text/javascript" charset="utf-8" src="js/string.js?version="></script>
        <script type="text/javascript" charset="utf-8" src="js/general.js?version="></script>

        <style type="text/css">
            html, body {
                background: #203247;
                box-shadow: inset 0 0 100px rgba(0,0,0,.5);
                height: 100%;
                min-height: 100%;
            }
            .content-front {
                height: 100vh!important;
                min-height: 100vh!important;
                margin: 0px !important;
                padding: 0px!important;
            }
            .content-front .body {
                top: 0px;
                margin: 0px!important;
            }
            .content-front .left-side {
                height: 100vh!important;
                min-height: 100vh!important;
            }
            .content-front .right-side {
                height: 100vh!important;
                min-height: 100vh!important;
                background-color: #f2f2f2;
                padding: 2.5em;
                margin: 0px;
            }
            .content-front .right-side form {
                position: relative;
                top: 40%!important;
            }
            .content-front .right-side form .alert {
                margin: 10px -15px 20px -15px;
            }
            .content-front .footer {
                position: fixed;
                clear: both;
                bottom: 0px;
                margin: 0px;
                padding: 8px 0px 8px 0px;
                background: rgba(0,0,0,0.5);
                box-shadow: inset 0 0 100px rgba(0,0,0,0.8);
                width: 100%;
                letter-spacing: 0.2em;

                color: #fff;
                text-shadow: 0px -2px 0px #333, 0px 2px 3px #666;
                text-align: center;
            }
            .content-front .body .logo {
                font-family: "Avant Garde", Avantgarde, "Century Gothic", CenturyGothic, "AppleGothic", sans-serif;
                color: rgba(255,255,255,0.7);
            }
            .content-front .body .logo img {
                border: none;
                background: white;
                border: 1px inset rgba(0,0,0,.5), 1px outset rgba(0,0,0,.5);
                border-radius: 20%;
                flex: 1;
            }
            .content-front .body .text h1 {
                font-size: 8em;
                margin-top: 0px;
                text-decoration: none;
                letter-spacing: -5px;
                text-align: left;
                color: #999;
                text-shadow: 0px 3px 5px #ffcc;
                text-transform: uppercase;
                text-rendering: optimizeLegibility;
                line-height: 1;
            }
            .content-front .body text {
                font-size: 1.7em;
                color: #cccccc;
                text-shadow: 0px 2px 3px #555;
                margin-top: 0em;
                font-style: oblique;
                align-content: space-around;
            }
            .content-front .body .logo-cliente {
                top: 1%;
                right: 1%;
                position: absolute;
            }
            .content-front .body .logo-cliente .img-front {
                width: auto;
                height: auto;
                max-height: 100px;
                
            }
        </style>

        <script language="javascript">
            function validar() {
                var form= document.forms[0];

                if (validar_login(form) == false)
                    return;
                if (parseInt($('#cant_prs').val()) > 1 && $('#entity').val() == 0) {
                    alert("Debe especificar la Entidad a la que pretende acceder.");
                    return;
                }
                form.action= 'php/login.php?csfr_token=<?=$csfr_token?>&';
                form.submit();
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                $('#div-error').hide();
                if (Entrada($('#error').val())) {
                    $('#div-error').show();
                }

                $('form input').click(function() {
                    $('#div-error').hide();
                });
                $('#usuario').focus(function() {
                    $(this).val('');
                    $('#div-error').hide();
                });
                $('#clave').focus(function() {
                    $('#div-error').hide();
                });
                <?php if (!is_null($error)) { ?>
                    alert("<?=!empty($jaja1) || !empty($jaja2) ? "jajajaja!!!!!!" : $error?>");
                <?php } ?>
            });
        </script>
    </head>

    <body oncontextmenu="return false">
        <script type="text/javascript" src="libs/wz_tooltip/wz_tooltip.js"></script>

        <div class="content-front">
            <div class="body row align-middle">
                <div class="col-md-8 col-lg-9 left-side justify-content-center d-none d-md-flex clearfix">
                    <div class="row justify-content-center align-content-center">
                        <div class="col-md-11 col-lg-12 d-flex flex-wrap justify-content-center">
                            <div class="logo">
                                <img src="img/dirger.png" class="mr-1" />
                            </div>
                            <div class="text ml-1">
                                <h1>Diriger</h1>
                                <text>Sistema para la Gestión Integrada</text>
                            </div>
                        </div>                        
                    </div>
                </div>   
                
                <?php $base_dir= !empty($id) ? $_SESSION['virtualhost_base_dir'] : null; ?>

                <div class="col-xs-12 col-md-4 col-lg-3 right-side clearfix">
                    <div class="logo-cliente col-12">
                        <div class="row">
                            <div class="col-lg-6 justify-content-left d-none d-lg-inline-block">
                                <img class="img-fluid img-front" src="<?=$base_dir?>client_images/logo.png" width="130px">
                            </div>
                            <div class="col-xs-12 col-lg-6 justify-content-right">
                                <img class="img-fluid" src="img/gemus.png" width="130px">
                            </div>
                        </div>
                    </div>

                    <form action="javascript:validar()" class="form-horizontal" method="post">
                        <input type="hidden" id="error" name="error" value="<?=$error?>" />
                        <input type="hidden" id="cant_prs" name="cant_prs" value="<?=$cant_prs?>" />

                        <div id="div-error" class="alert alert-danger">
                           <?=!empty($jaja1) || !empty($jaja2) ? "jajajaja!!!!!!" : $error?>
                       </div>

                        <div class="form-group row">
                            <div class="input-group">
                                <input type="text" id="usuario" name="usuario" class="form-control" autocomplete="no" placeholder="Usuario">
                                <span class="input-group-text">
                                    <i class="fa fa-user"></i>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="input-group">
                                <input type="password" id="clave" name="clave" class="form-control" autocomplete="no" placeholder="Contraseña">
                                <span class="input-group-text">
                                    <i class="fa fa-unlock"></i>
                                </span>
                            </div>
                        </div>
                        <?php if ($cant_prs > 1) { ?>
                        <div class="form-group row">
                            <div class="input-group">
                                <select name="entity" id="entity" class="form-control">
                                    <option value="0">Seleccione... </option>
                                    <?php while ($row= $clink->fetch_array($result)) { ?>
                                    <option value="<?=$row['id']?>" <?php if ($row['id'] == $id_index) echo "selected='selected'"?>><?=$row['nombre']?></option>
                                    <?php } ?>
                                </select>
                                <span class="input-group-text">
                                    <i class="fa fa-building-o"></i>
                                </span>
                            </div>
                        </div>
                        <?php } else { ?>
                        <input type="hidden" name="entity" id="entity" value="<?=$_SESSION['local_proceso_id']?>" />
                        <?php } ?>
                        <div class="row">
                            <button class="btn btn-primary btn-lg btn-block">Entrar<i class="fa fa-sign-in" style="margin-left: 12px;"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row footer justify-content-center">
                Diriger versión <?= $_SESSION['version'] ?><br/>
                Copyright © Gemus, 2009 - <?=date('Y')?>. Todos los derechos Reservados
            </div>
        </div>

    </body>
</html>

